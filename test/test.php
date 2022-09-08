<?php

include_once("../rapido.php");

use rapido\{Router,SQLI,Fetch};

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: application/json; text/plain;');

$App = new Router($_SERVER);
$GLOBAL["App"] = $App;

$App -> get('/index' , function($req , $res){
    $res -> send('WELCOME ON RAPIDO','text');
});

$App -> get('/test/ressource' , function($req , $res){
    $res -> send('test ressource','text');
});

$App -> get('/test/:ressource' , function($req , $res){
    $res -> send('test dynamical ressource','text');
});

$App -> handle();
// ob_end_flush();
?>
