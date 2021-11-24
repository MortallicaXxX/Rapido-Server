<?php
include_once("src/Middleware.php");
use Middleware\{Routes,Sessions,BodyParser,Datastorage,Layout};
namespace Util{
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
}

namespace Report{

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Error{

    function __construct($msg){
      var_dump($msg);
    }

  }

}

namespace Datastorage{

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Error extends \Report\Error{}

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class fs extends \Util\FileSystem{}



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

namespace Rapido{

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Error extends \Report\Error{}

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class fs extends \Util\FileSystem{}

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Datastorage extends \Datastorage\DB{}

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

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Request{
    function __construct($router){
      foreach (array_keys($router) as $key) {
        $this->{$key} =  $router[$key];
      }
    }
  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Response{
    /**
      *Description :
    */
    public function send($data,$option){
      if($option == "text")$this -> sendText($data);
      else if($option == "json")$this -> sendJSON($data);
      else if($option == "file")$this -> sendFile($data);
      else throw "option not correct";
    }

    /**
      *Description :
    */
    private function sendText($text){
      echo $text;
    }

    /**
      *Description :
    */
    private function sendJSON($arrayJson){
      echo json_encode($arrayJson);
    }

    /**
      *Description :
    */
    private function sendFile($pathToFile,$ext = "json"){
      if (file_exists($pathToFile)) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="'.basename($pathToFile).'"');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($pathToFile));
          readfile($pathToFile);
          exit;
      }
    }
  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Router{

    private $routeur;
    private $_get = array(); /** @author contient la liste des get */
    private $_post = array(); /** @desc contient la liste des post */
    private $_middleware = array();

    function __construct($_server){
      $this -> routeur = $_server;
    }

    /**
      *Description :
    */
    function handle(){
      $this -> __handle_middleware();
      if ($this -> routeur["REQUEST_METHOD"] == "GET") {
        $this -> __delegate_get();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "POST") {
        $this -> __delegate_post();
      }
    }

    /**
      *Description :
    */
    private function __handle_middleware(){
      foreach ($this -> _middleware as $middleware) {
        foreach ($middleware as $name => $instance) {
          $this -> routeur = $instance -> Program($this -> routeur);
        }
      }
    }

    /**
      *Description :
    */
    private function __get_chanel(){
      $query = explode("&", $this -> routeur["QUERY_STRING"]);
      foreach ($query as $word) {
        $r = explode("=", $this -> routeur["QUERY_STRING"]);
        if($r[0] == "chanel")return $r[1];
      }
    }

    /**
      *Description :
    */
    private function __open_chanel($method){
      $chanel = $this -> __get_chanel();
      $path = array_filter(explode("/", $chanel),function($x){return $x;});
      function recursive_find( $p , $m , $iterator = 1 ){
        $key_Averif = $p[$iterator];
        if(is_array($m[$key_Averif]) && key_exists($key_Averif,$m) == true)return recursive_find($p , $m[$key_Averif] , $iterator+1 );
        else if(key_exists($key_Averif,$m) == true)return $m[$key_Averif];
        else echo "chanel not fund";
      }

      return recursive_find($path,$method);
    }

    /**
      *Description :
    */
    private function __delegate_get(){
      $chanelCallBack = $this -> __open_chanel($this -> _get); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description :
    */
    private function __delegate_post(){
      $chanelCallBack = $this -> __open_chanel($this -> _post); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description :
    */
    function get($chanel,$callback){
      $globalChanel = join("", explode("/", $GLOBALS["chanel"]));
      if(key_exists($globalChanel,$this -> _get) == false)$this -> _get[$globalChanel] = array();
      $this -> _get[$globalChanel][join("", explode("/", $chanel))] = $callback;
    }

    /**
      *Description :
    */
    function post($chanel,$callback){
      $globalChanel = join("", explode("/", $GLOBALS["chanel"]));
      if(key_exists($globalChanel,$this -> _post) == false)$this -> _post[$globalChanel] = array();
      $this -> _post[$globalChanel][join("", explode("/", $chanel))] = $callback;
    }

    function isUser($body){

      (new PHPClient()) -> Query("SELECT * FROM infoUsers WHERE login="."'".$body["user"]."'"." AND mdp="."'".sha1($body["mdp"])."'" , function($error,$result){
        if($error || count($result) == 0)echo json_encode(array("result" => "false"));
        else echo json_encode(array("result" => $result[0]["app_key"]));
      });

    }

    /**
      *Description :
    */
    function use($c,$option = null){
      $middleware_instance = new $c($option);
      if($middleware_instance -> get_type() == "middleware")array_push($this -> _middleware,array($c => $middleware_instance));
      else echo $c." is not a middleware";
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Middleware{
    /**
      *Description :
    */
    protected $type = "middleware";
    /**
      *Description :
    */
    public function get_type(){return $this -> type;}
    /**
      *Description :
    */
    public function Program($routeur){return $routeur;}
  }

  /**
    *Name : Routes
    *Type : Class extends Middleware
    *Description : Gestionaire des fichier routes et chanel's callback
    *Use-case : Middleware de rapido
    *Sample : rapido -> use ( Routes::class , [options] );
  */
  class Routes extends Middleware{
    private $__path;
    private $__fileroutes;

    function __construct($options){
      $this -> __path = array_keys($options)[0];
      $this -> __fileroutes = $options[array_keys($options)[0]];
      $this -> __verifyIntegrity();
      $this -> __include();
    }

    /**
      *Description :
    */
    private function __isFolderRouteExist(){
      return (is_dir($this -> __path) ? true : false);
    }

    /**
      *Description :
    */
    private function __isAllFileExist(){
      $result = array();
      foreach ($this -> __fileroutes as $key => $path){
        if(!file_exists($this -> __path."/route.".$path.".php"))array_push($result,false);
      }
      return (in_array(false, $result) ? false : true);
    }

    /**
      *Description :
    */
    private function __listeRoutesFiles(){
      return scandir($this -> __path);
    }

    /**
      *Description :
    */
    private function __verifyIntegrity(){
      $result = array();
      if($this -> __isFolderRouteExist() == false)array_push($result,$this -> __createMissingDirectory());
      if($this -> __isAllFileExist() == false)array_push($result,$this -> __createMissingFiles());
      return (in_array(false, $result) == true ? $this -> __verifyIntegrity() : true);

    }

    /**
      *Description :
    */
    private function __createMissingDirectory(){
      mkdir($this -> __path, 0777);
      return false;
    }

    /**
      *Description :
    */
    private function __createMissingFiles(){

      $template = "<?php\n\$Router = \$GLOBALS['App'];\n\n?>";

      foreach ($this -> __fileroutes as $key => $path){
        if(!file_exists($this -> __path."/route.".$path.".php")){
          $file = fopen($this -> __path."/route.".$path.".php", "w") or die("Unable to open file!");
          fwrite($file, $template);
          fclose($file);
        }
      }
      return false;
    }

    /**
      *Description :
    */
    private function __include(){
      foreach ($this -> __fileroutes as $key => $path){
        include $this -> __path."/route.".$path.".php";
      }
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Sessions extends Middleware{

    private $sessions_db;
    private $session_db_user;

    function __construct($sessions_path){
      $this -> __start_session();
      $this -> sessions_db = new Datastorage($sessions_path);
    }

    public function Program($routeur){
      $routeur["session"] = $_SESSION;
      $this -> session_db_user = $this -> sessions_db -> collection(session_id());

      $this -> session_db_user -> find(array("session_id" => session_id()) , function($error,$result,$collection){
        if($result !== null && count($result) == 0)$collection -> insert(array(
          "session_id" => session_id()
        ),function($error,$result,$collection){
          $collection -> save_file_integrity();
        });
      });
      $this -> __copy_session_var();

      return $routeur;
    }

    private function __copy_session_var(){
      $this -> session_db_user -> update(array("session_id" => session_id()),$_SESSION,function($error,$result,$collection){
        $collection -> save_file_integrity();
      });
    }


    /**
      *Description :
    */
    private function __start_session(){
      session_start();
    }

    /**
      *Description :
    */
    private function __destroy_session(){

    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class BodyParser extends Middleware{

    public function Program($routeur){
      if($routeur["REQUEST_METHOD"] == "POST")$routeur["body"] = $this -> __body();
      return $routeur;
    }

    /**
      *Description :
    */
    private function __body(){
      $data = json_decode(file_get_contents('php://input'), true);
      return $data;
    }

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Layout extends Middleware{

    private $__layout_path;
    private $ERROR = array(
      "NOT_A_FILE" => "Aucun layout portant le nom demandé.",
    );

    function __construct($layout_path){
      $this -> __layout_path = $layout_path;
      $this -> __verifyIntegrity();
    }

    function Program($routeur){

      $routeur["layout"] = function($fileName,$blockName){
        if($this -> __is_layout($fileName) == true){
          echo $this -> __load_layout($fileName,$blockName);
        }
        else new Error($this -> ERROR["NOT_A_FILE"]);
      };

      return $routeur;
    }

    /**
      *Description : fileName est-il un layout ? true : false.
    */
    private function __is_layout($fileName){
      return (in_array($fileName,$this -> __list_layout()) ? true : false);
    }

    /**
      *Description : retourne un tableau des fichiers .layout dans /layout
    */
    private function __list_layout(){

      $result = array();
      foreach (scandir($this -> __layout_path) as $filePath) {
        $path_info = pathinfo($filePath);
        if($path_info['extension'] == "layout")array_push($result,$path_info['filename']);
      }
      return $result;
    }

    /**
      *Description : retourne un string représentant le contenu du fichier .layout
    */
    private function __load_layout($fileName,$blockName){
      return $this -> __make_layout_block((new fs()) -> read_file($this -> __layout_path."/".$fileName.".layout"),$blockName);
    }

    /**
      *Description : Conversion du block de string en tableau suivant la structure ["block_name" => [line,...],...]
    */
    private function __make_layout_block($layout,$blockName){

      $result = array();
      $block_title = "";
      $data_block = array();

      foreach(explode("\n",$layout) as $line){
        $line = join("",preg_split('/\h{2,}/',$line));
        if(strlen($line) > 0 && $line[0] == "#"){
          if(count($data_block) > 0){
            $result[$block_title] = $data_block;
            $block_title = "";
            $data_block = array();
          }
          $block_title = trim(join("",explode("#",$line)));
        }
        else if($line != "")array_push($data_block,$line);

      }
      $result[$block_title] = $data_block;
      $block_title = "";
      $data_block = array();
      return $this -> __merge_layout_block($result,$blockName);

    }

    /**
      *Description : Résous les liens entre layout.
    */
    private function __merge_layout_block($result,$blockName){

      foreach ($result as $name => $lines) {
        for($i = 0 ; $i < count($lines) ; $i++){
          $line = $lines[$i];
          if($line[0] == "{"){
            $title = trim(join("",explode("}",join("",explode("{#",$line)))));
            $result[$name][$i] = $result[$title];
          }
        }
      }

      return $this -> __normalise($result[$blockName]);

    }

    /**
      *Description : Normalise les tableaux en une chaine de string.
    */
    private function __normalise($layout_block){
      for($i = 0 ; $i < count($layout_block) ; $i++){
        if(is_array($layout_block[$i]) == true)$layout_block[$i] = $this -> __normalise($layout_block[$i]);
      }
      return join("",$layout_block);
    }

    /**
      *Description :
    */
    private function __is_folder_exist(){
      return (is_dir($this -> __layout_path) ? true : false);
    }

    /**
      *Description :
    */
    private function __createMissingDirectory(){
      mkdir($this -> __layout_path, 0777);
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

  class Middleware{
    public $Routes = Middleware\Route;
    public $Sessions = Middleware\Sessions;
    public $BodyParser = Middleware\BodyParser;
    public $Datastorage = Middleware\Datastorage;
    public $Layout = Middleware\Layout;
  }

}
?>
