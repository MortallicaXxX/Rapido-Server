<?php

namespace Router{

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

}

?>
