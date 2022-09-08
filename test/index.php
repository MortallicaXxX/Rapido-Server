<?php

include_once("../rapido.php");

use rapido\{Router,SQLI,Fetch};

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: application/json; text/plain;');

$App = new Router($_SERVER);
$GLOBAL["App"] = $App;

$App -> get('/' , function($req , $res){
    $res -> send('WELCOME ON RAPIDO','text');
});

$App -> get('/test/ressource' , function($req , $res){
    $res -> send('test "/test/ressource" route','text');
});

$App -> get('/test/:ressource' , function($req , $res){

    $ressource = $req -> dynamicPath -> var["ressource"];

    $res -> send("dynamic route test at /test/$ressource",'text');
});

$App -> get('/test/:ressource/by/:option' , function($req , $res){

    $ressource = $req -> dynamicPath -> var["ressource"];
    $option = $req -> dynamicPath -> var["option"];

    $res -> send("dynamic route test at  /test/$ressource/by/$option",'text');
});

$App -> get('/test/:ressource/:option/details' , function($req , $res){

    $ressource = $req -> dynamicPath -> var["ressource"];
    $option = $req -> dynamicPath -> var["option"];

    $res -> send("dynamic route test at /test/$ressource/$option/details",'text');
});

$App -> handle();
// ob_end_flush();
?>
