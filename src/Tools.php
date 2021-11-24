<?php

namespace Tools{
  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class FileSystem{

    /** @source https://www.w3schools.com/PHP/php_ref_filesystem.asp*/

    /**
      *Description :
    */
    function extension($path){
      $path_info = pathinfo($path);
      return $path_info['extension'];
    }

    /**
      *Description :
    */
    function filename($path){
      $path_info = pathinfo($path);
      return $path_info['filename'];
    }

    /**
      *Description :
    */
    function write_file($full_path,$data){
      $file = fopen($full_path, "w") or die("Unable to open file!");
      fwrite($file, $data);
      fclose($file);
    }

    /**
      *Description :
    */
    function write_in_file($full_path,$data){

    }

    /**
      *Description :
    */
    function read_file($full_path){
      return file_get_contents($full_path);
    }

    /**
      *Description :
    */
    function print_file($full_path){
      echo readfile($full_path);
    }

    /**
      *Description :
    */
    function add_folder($full_path){
      mkdir($full_path,0777);
    }

    /**
      *Description :
    */
    function add_file($full_path){
      $file = fopen($full_path, "w") or die("Unable to open file!");
      fclose($file);
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class SQLIClient{

    private $_host = "dl200022-001.dbaas.ovh.net";
    private $_port = "35748";
    private $_user = "guigui";
    private $_password = "PtW143kjkS3F";
    private $_dbname = "userInfoBd";

    private $mysqli;

    function __construct(){
      $this -> Connect();
    }

    /**
      *Description :
    */
    function Connect(){
      $this -> mysqli = new mysqli( "dl200022-001.dbaas.ovh.net:35748" , $this->_user , $this->_password , $this->_dbname);
      if($this -> mysqli -> connect_errno)$this -> onError("Erreur de connection à la base de donnée");
    }

    /**
      *Description :
    */
    function Query($query,$callback){
      $resultQuery = $this -> mysqli -> query($query); // exécution query
      $error = (!$resultQuery ? $this -> mysqli -> error : null); // gestion erreur
      $callback($error,$this -> Normalize($resultQuery)); // exécution du callBack
    }

    /**
      *Description :
    */
    function Normalize($result){
      $toReturn = [];
      for ($i = 1; $i <= $result -> num_rows; $i++) {
        $row = $result->fetch_assoc(); //
        array_push($toReturn, $row);
      }
      return $toReturn;
    }

    /**
      *Description :
    */
    function Close(){

    }

    /**
      *Description :
    */
    function onError($message){
      echo $message;
    }

  }
}

?>
