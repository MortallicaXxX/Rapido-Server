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
    $res -> send('test "/test/:ressource" dynamic route','text');
});

$App -> get('/test/:ressource/by/:option' , function($req , $res){
    $res -> send('test "/test/:ressource/by/:option" dynamic route','text');
});

$App -> get('/test/:ressource/:option/details' , function($req , $res){
    $res -> send('test "/test/:ressource/:option/details" dynamic route','text');
});

$App -> handle();
// ob_end_flush();
?>
