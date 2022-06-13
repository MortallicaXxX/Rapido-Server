# rapido

⛔️ It's a student project ⛔️

## What is Rapido ?
Rapido is a PHP framework that allow you to use your php server like express with nodejs.

## How that take place ?
Rapido provide two concept.

### - Chanel and chanel-callback
Rapido provide a chanel handler that allow you to attribute a callback to a chanel. It thinked to be use as variable in your url.

Sample of chanel use.
```PHP
<?php
// include rapido
include_once("path/to/Rapido-Server/rapido.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: application/json; text/plain;');

// Set rapido router
$App = new rapido\Router($_SERVER);

// Set of chanel who catch 'GET' request and reply by an html file.
$App -> get('/',function($req , $res){
  $res -> send('./public/index.html','file');
});

// Set of chanel who catch 'POST' request and reply by and resource like 'text'.
$App -> post('/ping',function($req , $res){
  $res -> send('pong','text');
});

// Handle the request
$App -> handle();
?>
```

In the navigator go to `path/to/your/index.html` to load your index.html.

You could also try this javascript code in the dev-tools to try the post chanel.

```javascript
const response = await fetch('path/to/site.html?chanel=/ping',{
  method : 'POST'
});
console.log(await response.text());
```

### - Middleware

Rapido provide a middleware engine that allow to any developper to create sub program that is executed by the server and to change the way that the router is running. Through middleware you can do what you want.

How use middleware.
```PHP
<?php
include_once("path/to/Rapido-Server/rapido.php");
include_once("path/to/middlewares/Rapido@BodyParser/index.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: application/json; text/plain;');

// Set rapido router
$App = new rapido\Router($_SERVER);

// Define that rapido router use BodyParser who will affect $App
$App -> use( BodyParser::class );

// Handle the request
$App -> handle();
?>
```

https://github.com/MortallicaXxX/Rapido-Middleware for more details about middleware.
