<?php

namespace Datastorage{
  include_once("Tools.php");
  include_once("Models.php");
  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Error extends \Models\Error{}

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class fs extends \Tools\FileSystem{}



  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class GUID{

    private $guid = array();

    function __construct($storage){
      $this -> __load_guid($storage);
    }

    /**
      *Description :
    */
    public function guid(){
      $guid = rand(10000000,19999999);
      if(array_key_exists($guid, $this -> guid))return $this -> guid();
      else {
        $this -> guid[$guid] = $guid;
        return hash('ripemd160', $guid);
      }
    }

    /**
      *Description :
    */
    private function __load_guid($storage){
      if(gettype($storage) == "object" && count(array_keys(get_object_vars($storage))) > 0)foreach($storage as $key => $document) {
        $this -> guid[$key] = $key;
      };
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Collection{

    private $path_storage;
    private $collection_name;
    private $storage;
    private $keyGen;
    private $parsed = false;
    private $ERROR = array(
      "PARSE" => "Erreur de parse du fichier source.",
      "SAVE_PARSE" => "Impossible de sauvegarder, le fichier ne semble pas correspondre à un format json.",
      "UPDATE_FIND_LENGTH" => "Aucun document ne correspond au filtre."
    );

    function __construct($collection_path){

      $this -> path_storage = $collection_path;
      $this -> __verifyIntegrity();
      $this -> __load_file();
      $this -> keyGen = new GUID($this -> storage);

    }

    /**
      *Description :
    */
    public function insert($data,$callback = null){

      if($this -> parsed == true){
        try{
          $guid = $this -> keyGen -> guid();
          $data["_id"] = $guid;
          $this -> storage -> {$guid} = $this -> __array_to_object($data);
          if($callback)$callback(null,$this -> storage -> {$guid},$this);
          else return $this -> storage -> {$guid};
        }
        catch(Exception $err){
          if($callback)$callback(new Error($err),null,$this);
          else new Error($err);
        }
      }
      else{
        if($callback)$callback(new Error($this -> ERROR["PARSE"]),null,$this);
        else new Error($this -> ERROR["PARSE"]);
      }
    }

    /**
      *Description :
    */
    public function find($filter,$callback = null){

      if($this -> parsed == true){
        try{
          $filter = $this -> __array_to_object($filter);
          $result = array();

          foreach (array_keys(get_object_vars($this -> storage)) as $key) {
            if($this -> compare_object($filter,$this -> storage -> {$key}) == true)array_push($result,$this -> storage -> {$key});
          }
          if($callback)$callback(null,$result,$this);
          else return $result;
        }catch(Exception $err){
          if($callback)$callback(new Error($err),null,$this);
          else new Error($err);
        }
      }
      else{
        if($callback)$callback(new Error($this -> ERROR["PARSE"]),null,$this);
        else new Error($this -> ERROR["PARSE"]);
      }

    }

    /**
      *Description :
    */
    public function update($filter,$data,$callback=null){

      if($this -> parsed == true){
        try {
          $filter = $this -> __array_to_object($filter);
          $data = $this -> __array_to_object($data);
          $result = $this -> find($filter);
          if(count($result) > 0){
            $this -> storage -> {$result[0] -> _id} = $this -> __object_assign($result[0],$data);
            if($callback)$callback(null,$result,$this);
            else return $this -> storage -> {$result[0] -> _id};
          }
          else{
            if($callback)$callback(new Error($this -> ERROR["UPDATE_FIND_LENGTH"]),null,$this);
            else new Error($err);
          }
        } catch (Exception $e) {
          if($callback)$callback(new Error($err),null,$this);
          else new Error($err);
        }
      }
      else{
        if($callback)$callback(new Error($this -> ERROR["PARSE"]),null,$this);
        else new Error($this -> ERROR["PARSE"]);
      }
    }

    /**
      *Description :
    */
    public function delete($filter,$callback=null){
      $result = $this -> find($filter);
      if(count($result) > 0)unset($this->storage->{$result[0] -> _id});
      if($callback)$callback("Error",in_array((array_keys(get_object_vars($this -> storage)))),$this);
      else return in_array((array_keys(get_object_vars($this -> storage))));
    }

    /**
      *Description :
    */
    public function dump(){
      $path_info = pathinfo($this -> path_storage);
      $file = fopen($this -> path_storage.".store", "w") or die("Unable to open file!");
      fwrite($file, "{}");
      fclose($file);
    }

    /**
      *Description :
    */
    private function __array_to_object($array){
      $object = new \stdClass();
      foreach ($array as $key => $value) {
          if (is_array($value)) {
              $value = convertToObject($value);
          }
          $object->$key = $value;
      }
      return $object;
    }

    /**
      *Description :
    */
    private function __object_assign($obj1,$obj2){
      foreach (array_keys(get_object_vars($obj2)) as $key) {
        $obj1 -> {$key} = $obj2 -> {$key};
      }
      return $obj1;
    }

    /**
      *Description :
    */
    private function compare_value($filter_value,$source_value){
      if(is_object($filter_value))return $this -> compare_object($filter_value,$source_value);
      else return ($filter_value == $source_value ? true : false);
    }

    /**
      *Description :
    */
    private function compare_object($filter,$source){
      $filter_keys = array_keys(get_object_vars($filter));
      $source_keys = array_keys(get_object_vars($source));

      $result = array();

      foreach ($filter_keys as $key) {
        if(in_array($key,$source_keys))array_push($result , $this -> compare_value($filter -> {$key} , $source -> {$key}));
        else array_push($result , false);
      }

      return !in_array(false,$result);

    }

    /**
      *Description :
    */
    private function __load_file(){
      $this -> storage = json_decode(file_get_contents($this -> path_storage.".store"));
      if(gettype($this -> storage) == "object")$this -> parsed = true;
    }

    /**
      *Description :
    */
    private function __is_file_exist(){
      return (file_exists($this -> path_storage.".store") ? true : false);
    }

    /**
      *Description :
    */
    private function __create_missing_file(){
      $path_info = pathinfo($this -> path_storage);
      $file = fopen($this -> path_storage.".store", "w") or die("Unable to open file!");
      fwrite($file, "{}");
      fclose($file);
    }

    /**
      *Description :
    */
    private function __verifyIntegrity(){
      if($this -> __is_file_exist() == false){
        $this -> __create_missing_file();
        $this -> __verifyIntegrity();
      }
    }

    /**
      *Description :
    */
    function save_file_integrity(){
      if($this -> parsed == true){
        $path_info = pathinfo($this -> path_storage);
        $file = fopen($this -> path_storage.".store", "w") or die("Unable to open file!");
        fwrite($file, json_encode($this -> storage));
        fclose($file);
      }
      else new Error($this -> ERROR["SAVE_PARSE"]);
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class DB{

    private $guid;
    private $db_path;

    function __construct($db_path){
      $this -> db_path = $db_path;
      $this -> __verifyIntegrity();
    }

    /**
      *Description :
    */
    public function collection($collectionName){
      return new Collection($this -> db_path."/".$collectionName);
    }

    /**
      *Description :
    */
    public function collection_list(){
      return array_filter(scandir($this -> db_path),function($filePath){
        $path_info = pathinfo($filePath);
        if($path_info['extension'] == "storage")return $filePath['filename'];
      });
    }

    /**
      *Description :
    */
    private function __is_folder_exist(){
      return (is_dir($this -> db_path) ? true : false);
    }

    /**
      *Description :
    */
    private function __createMissingDirectory(){
      mkdir($this -> db_path, 0777);
    }

    /**
      *Description :
    */
    private function __verifyIntegrity(){
      if($this -> __is_folder_exist() == false){
        $this -> __createMissingDirectory();
        $this -> __verifyIntegrity();
      }
    }

  }

}

?>
