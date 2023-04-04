/**

Tout d'abord, il inclut le fichier "rapido.php" qui contient les classes nécessaires pour la création de routes. Ensuite, il définit deux en-têtes de réponse HTTP qui permettent l'accès à l'API depuis n'importe quelle origine et autorisent les en-têtes "application/json" et "text/plain".

Ensuite, il crée une nouvelle instance de la classe Router de Rapido en passant l'objet $_SERVER en paramètre. Cela permet à Rapido de récupérer les informations de requête et de réponse pour traiter les routes.

Ensuite, il définit plusieurs routes à l'aide de la méthode get() de l'instance Router créée précédemment. Chaque route est définie par une URL et une fonction de rappel qui sera exécutée lorsque la route est appelée. La fonction de rappel prend deux paramètres : $req et $res. $req contient les informations de requête et $res est une instance de la classe Response de Rapido qui permet de renvoyer la réponse à l'utilisateur.

Les routes définies dans ce code incluent des routes statiques ("/" et "/test/ressource") ainsi que des routes dynamiques qui permettent de passer des paramètres dans l'URL ("/test/:ressource", "/test/:x/by/:y", et "/test/:ressource/:option/details").

Enfin, la méthode handle() est appelée pour que Rapido traite la requête et exécute la fonction de rappel correspondante en fonction de l'URL demandée.

Voici quelques exemples d'URLs qui peuvent être utilisées avec ce code :

http://example.com/ : renvoie le texte "WELCOME ON RAPIDO"
http://example.com/test/ressource : renvoie le texte "test "/test/ressource" route"
http://example.com/test/123 : renvoie le texte "dynamic route test at /test/123"
http://example.com/test/123/by/456 : renvoie le texte "dynamic route test at /test/123/by/456"
http://example.com/test/123/abc/details : renvoie le texte "dynamic route test at /test/123/abc/details"

*/

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

$App -> get('/test/:x/by/:y' , function($req , $res){

    $ressource = $req -> dynamicPath -> var["x"];
    $option = $req -> dynamicPath -> var["y"];

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
