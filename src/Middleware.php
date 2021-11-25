<?php

namespace Middleware{
  include_once("Datastorage.php");
  include_once("Tools.php");
  include_once("Models.php");

  class Error extends \Models\Error{}

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
      $this -> sessions_db = new \Datastorage\DB($sessions_path);
    }

    public function Program($routeur){
      $routeur["session"] = function($key,$value){
        $_SESSION[$key] = $value;
        $this -> __copy_session_var();
      };
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

      $routeur["layout"] = function($fileName,$blockName,$variables){
        if($this -> __is_layout($fileName) == true){
          echo $this -> __load_layout($fileName,$blockName,$variables);
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
    private function __load_layout($fileName,$blockName,$variables){
      return $this -> __make_layout_block((new \Tools\FileSystem()) -> read_file($this -> __layout_path."/".$fileName.".layout"),$blockName,$variables);
    }

    /**
      *Description : Conversion du fichier .layout en tableau ayant identifier chaque block et leurs titre.
      *$result {array} contient le résultat de la conversion du fichier en un array ayant identifier chaque block et leurs titre.
      *$blockName {string} représente le nom du block souaité.
      *$variables {array} contient les variables à injecter dans le layout.
    */
    private function __make_layout_block($layout,$blockName,$variables){

      $result = array();
      $block_title = "";
      $data_block = array();

      foreach(explode("\n",$layout) as $line){
        $line = join("",preg_split('/\h{2,}/',$line)); // suppression des espaces
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
      return $this -> __merge_layout_block($result,$blockName,$variables);

    }

    /**
      *Description : Permet de modifier une partie de chaine de char dans un string par une valeur.
      *$line {string} ligne contenant la chaine de char à modifier.
      *$str_start {int} position de début de char.
      *$str_end {int} position de fin de char.
      *$value {string} varibale devant modifier la chaine de char.
    */
    private function __modify_string_range($line,$str_start,$str_end,$value){
      $new_line = "";
      $value_is_insert = false;
      for($i = 0 ; $i < strlen($line) ; $i++){
        if(($i > $str_start && $i < $str_end) == false)$new_line .= $line[$i];
        else if ($value_is_insert == false){
          $new_line .= "{$value}";
          $value_is_insert = true;
        }
      }
      return $new_line;
    }

    /**
      *Description : Résous les injection de variable dans une ligne.
      *$line {string} Chaine de string à modifier si contient des variables.
      *$variables {array} contient les variables à injecter dans la ligne.
    */
    private function __add_variables_in_line($line,$variables){
      if(strpos($line, "{@") !== false){
        $start = strpos($line, "{"); // début de la première variable détectée
        $end = strpos($line, "}"); // fin de la première variable détectée
        $value_key = trim(join("",explode("@",join("",array_slice(str_split($line, 1), $start +1 , ($end - $start) - 1))))); // extraction du nom de variable
        $line = $this -> __modify_string_range($line,$start-1,$end+1,$variables[$value_key]); // modification de la ligne
      }
      if(strpos($line, "{@") !== false)return $this -> __add_variables_in_line($line,$variables); // si une autre variable est détectée après modification
      else return $line; // sinon retour de la ligne
    }

    /**
      *Description : Résous les injection de variable et liens entre block.
      *$result {array} contient le résultat de la conversion du fichier en un array ayant identifier chaque block et leurs titre.
      *$blockName {string} représente le nom du block souaité.
      *$variables {array} contient les variables à injecter dans le layout.
    */
    private function __merge_layout_block($result,$blockName,$variables){

      foreach ($result as $name => $lines) {
        for($i = 0 ; $i < count($lines) ; $i++){
          $line = $this -> __add_variables_in_line($lines[$i],$variables);
          $result[$name][$i] = $line;
          if($line[0] == "{"){
            $title = trim(join("",explode("}",join("",explode("{#",$line)))));
            $result[$name][$i] = $result[$title];
          }
        }
      }

      return $this -> __normalise($result[$blockName]);

    }

    /**
      *Description : Normalise les tableaux et sous tableaux en une chaine de string.
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

  class Cors extends Middleware{
    function __construct(){

    }

    public function Program($routeur){
      return $routeur;
    }
  }
}

?>
