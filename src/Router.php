<?php

namespace Router{

  interface IRequest{

  }

  interface IResponse{

    public function send(String $data,String $option);
    public function sendText(string $text);
    public function sendJSON(array $arrayJson);
    public function sendFile(string $pathToFile);
    public function toDownload(string $pathToFile);

  }

  /**
    *Name : Router
    *Type : Class
    *Description :
    *Use-case :
    *Sample :
  */
  class Request implements IRequest{
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
  class Response implements IResponse{
    /**
      *Description :
    */
    public function send($data,$option = "text"){
      if($option == "text")$this -> sendText($data);
      else if($option == "json")$this -> sendJSON($data);
      else if($option == "file")$this -> sendFile($data);
      else if($option == "ddl")$this -> toDownload($data);
      else throw "option not correct";
    }

    /**
      *Description :
    */
    public function sendText($text){
      header("Content-Type: text/plain; charset=UTF-8");
      echo $text;
    }

    /**
      *Description :
    */
    public function sendJSON($arrayJson){
      header('Content-Type: application/json');
      echo json_encode($arrayJson , JSON_PRETTY_PRINT );
    }

    /**
      *Description :
    */
    public function sendFile($pathToFile){
      if (file_exists($pathToFile)) {
        echo file_get_contents($pathToFile);
      }
    }

    public function toDownload($pathToFile){
      if (file_exists($pathToFile)) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="'.basename($pathToFile).'"');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($pathToFile));
          var_dump(readfile($pathToFile));
          exit;
      }
    }
  }

  class DynamicPath extends \stdClass{

    public $var = array();
    function __construct(string $chanel , string $url){
      $c = \explode('/',$chanel);
      $u = \explode('/',$url);
      foreach ($u as $iterator => $value) {
        if(str_contains($c[$iterator],':') == true){
          $key = \explode(':',$c[$iterator])[1];
          $this -> var[$key] = $value;
        }
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

    public $routeur;
    private $_get = array(); /** @author contient la liste des get */
    private $_head = array(); /** @author contient la liste des get */
    private $_options = array(); /** @author contient la liste des get */
    private $_post = array(); /** @desc contient la liste des post */
    private $_put = array();
    private $_patch = array();
    private $_delete = array();
    private $_middleware = array();

    function __construct($_server){
      $this -> routeur = $_server;
    }

    /**
      *Description :
    */
    function handle(){
      $this -> __handle_navigate();
      $this -> __handle_middleware();
      if ($this -> routeur["REQUEST_METHOD"] == "GET") {
        $this -> __delegate_get();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "HEAD") {
        $this -> __delegate_head();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "OPTIONS") {
        $this -> __delegate_options();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "POST") {
        $this -> __delegate_post();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "PUT") {
        $this -> __delegate_put();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "PATCH") {
        $this -> __delegate_patch();
      }
      if ($this -> routeur["REQUEST_METHOD"] == "DELETE") {
        $this -> __delegate_delete();
      }
    }

    /**
      *Description :
    */
    private function __handle_navigate(){
      if (!function_exists('str_contains')) {
          function str_contains(string $haystack, string $needle): bool
          {
              return '' === $needle || false !== strpos($haystack, $needle);
          }
          if(str_contains($this -> routeur["REQUEST_URI"], "chanel") == false)header('Location: index.php?chanel=/');
      }
      else if(str_contains($this -> routeur["REQUEST_URI"], "chanel") == false)header('Location: index.php?chanel=/');
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
      $primaryChanel = $this -> __primary_chanel($chanel);
      $secondaryChanel = $this -> __secondary_chanel($chanel);


      if(key_exists($primaryChanel,$method) == true && key_exists($secondaryChanel,$method[$primaryChanel]) == true)return $method[$primaryChanel][$secondaryChanel];
      else if(key_exists($primaryChanel,$method) == true){
        $subChanels = $method[$primaryChanel];
        foreach ($subChanels as $chanel => $options) {
          if($this -> __isDynamicalChanel($chanel) && /*( \count(\explode('/',$secondaryChanel)) == \count(\explode('/',$chanel)))*/ $this -> __isSameDynamicChanelDNA($secondaryChanel , $chanel) ){
            $this -> routeur["dynamicPath"] = new DynamicPath($chanel ,$secondaryChanel);
            return $method[$primaryChanel][$chanel];
          }
        }
      }
      else echo "<p>chanel not fund<p>";

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
    private function __delegate_head(){
      $chanelCallBack = $this -> __open_chanel($this -> _head); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description :
    */
    private function __delegate_options(){
      $chanelCallBack = $this -> __open_chanel($this -> _options); // permet de charger le chanel
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
    private function __delegate_put(){
      $chanelCallBack = $this -> __open_chanel($this -> _put); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description :
    */
    private function __delegate_patch(){
      $chanelCallBack = $this -> __open_chanel($this -> _patch); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description :
    */
    private function __delegate_delete(){
      $chanelCallBack = $this -> __open_chanel($this -> _delete); // permet de charger le chanel
      $chanelCallBack(new Request($this -> routeur),new Response());
    }

    /**
      *Description : Permet de connaitre le chanel de base
      *Exemple : "localhost/test/blou/blou" = "test"
    */
    private function __primary_chanel($chanel){
      $expl = explode("/", $chanel);
      return (count($expl) > 1 ? $expl[1] : $expl[0]);
    }

    /**
      *Description : Permet de connaitre le chanel de base
      *Exemple : "localhost/test/blou/blou" = "blou/blou"
    */
    private function __secondary_chanel($chanel){
      $expl = explode("/", $chanel);
      $toReturn = (count($expl) > 2 ? $expl[2] : $expl[1]);
      for($i = 0 ; $i < count($expl) ; $i++){
        if($i > 2)$toReturn .= "/".$expl[$i];
      }
      return $toReturn;
    }

    /** Retourne true | false si il s'agit d'une url dynamique */
    private function __isDynamicalChanel(string $chanel):bool{
      return str_contains($chanel, ":");
    }

    /** Retourne true | false si il s'agit de deux même addresse url */
    private function __isSameDynamicChanelDNA(string $chanel1 , string $chanel2):bool{
      $dna = array();
      $c1 = \explode('/' , $chanel1);
      $c2 = \explode('/' , $chanel2);
      if( \count($c1) != \count($c2))return false;
      else foreach ($c1 as $iterator => $str) {
        $arn = ( $this -> __isDynamicalChanel($c1[$iterator]) == true || $this -> __isDynamicalChanel($c2[$iterator]) == true ? 1 : (
          $c1[$iterator] == $c2[$iterator] ? 1 : 0
        ));
        array_push($dna , $arn);
      }
      return !in_array(0,$dna);
    }

    /**
      *Description :
    */
    function get($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _get) == false)$this -> _get[$primaryChanel] = array();
      $this -> _get[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function head($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _head) == false)$this -> _head[$primaryChanel] = array();
      $this -> _head[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function options($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _options) == false)$this -> _options[$primaryChanel] = array();
      $this -> _options[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function post($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _post) == false)$this -> _post[$primaryChanel] = array();
      $this -> _post[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function put($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _put) == false)$this -> _put[$primaryChanel] = array();
      $this -> _put[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function patch($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _patch) == false)$this -> _patch[$primaryChanel] = array();
      $this -> _patch[$primaryChanel][$secondaryChanel] = $callback;
    }

    /**
      *Description :
    */
    function delete($chanel,$callback){
      $primaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __primary_chanel($GLOBALS["chanel"].$chanel) : $this -> __primary_chanel($chanel));
      $secondaryChanel = (key_exists("chanel",$GLOBALS) == true && $GLOBALS["chanel"] != null && $GLOBALS["chanel"] != "/" ? $this -> __secondary_chanel($GLOBALS["chanel"].$chanel) : $this -> __secondary_chanel($chanel));
      if(key_exists($primaryChanel,$this -> _delete) == false)$this -> _delete[$primaryChanel] = array();
      $this -> _delete[$primaryChanel][$secondaryChanel] = $callback;
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
