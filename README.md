# rapido

⛔️ It's a student project ⛔️

## What is Rapido ?
Rapido is a PHP framework that allow you to use your php server like nodejs one.

## How that take place ?
Rapido provide two concept.

### - Chanel and chanel-callback
Rapido provide a chanel handler that allow you to attribute a callback to a chanel. It thinked to be use as variable in your url.

Sample of chanel use.
```PHP
<?php
// include rapido
include_once("path/to/Rapido-Server/rapido.php");

use rapido\{Router,SQLI,Fetch};

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: application/json; text/plain;');

// Set router
$App = new Router($_SERVER);

// Set of chanel who catch 'GET' request and reply by an html file.
$App -> get('/',function($req , $res){
  $res -> send('./public/index.html','file');
});

// Set of chanel who catch 'POST' request and reply by and resource like 'text'.
$App -> post('/ping',function($req , $res){
  $res -> send('pong','text');
});


$App -> handle();
?>
```

In the navigator go to 'path/to/your/site.html' to load your index.html.

You could also try this javascript code in the dev-tools to try the post chanel. 

```javascript
const response = await fetch('path/to/site.html?chanel=/ping',{
  method : 'POST'
});
console.log(await response.text());
```

### - Middleware
Rapido provide a middleware engine that allow to any developper to create sub program that is executed by the server and to change the way that the server is running. Through middleware you can do what you want.
