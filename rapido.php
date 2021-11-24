<?php
// include_once("src/Middleware.php");
// use Middleware\{Routes,Sessions,BodyParser,Datastorage,Layout};
namespace Rapido{

  include_once("src/Middleware.php");
  include_once("src/Router.php");
  include_once("src/Tools.php");
  include_once("src/Models.php");

  // Router
  class Router extends \Router\Router{}
  class Request extends \Router\Router{}
  class Response extends \Router\Router{}
  // Middleware
  class Routes extends \Middleware\Routes{}
  class Sessions extends \Middleware\Sessions{}
  class BodyParser extends \Middleware\BodyParser{}
  class Layout extends \Middleware\Layout{}
  // Datastorage
  class Datastorage extends \Datastorage\DB{}
  // Tools
  class fs extends \Tools\FileSystem{}
  // Models
  class Error extends \Models\Error{}


}
?>
