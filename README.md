# Rapido

Rapido est un micro-framework PHP pour le développement d'applications web légères et rapides. Il est conçu pour les développeurs qui ont besoin de construire rapidement des applications web simples sans se soucier de la complexité des grands frameworks. Rapido utilise des concepts tels que les canaux statiques et dynamiques pour gérer les requêtes et les middlewares pour gérer les flux de traitement des requêtes.

### **Installation**

Pour utiliser Rapido, vous devez télécharger le code source depuis le repository de l'utilisateur. Ensuite, vous pouvez inclure le fichier "Rapido.php" dans votre code PHP et utiliser les classes et les méthodes fournies par le framework.

## **Utilisation**

### Routing

Le routing est un élément clé de tout framework web, et Rapido ne fait pas exception. Pour définir une route dans Rapido, vous pouvez utiliser la méthode $App->get(), $App->post(), $App->put() ou $App->delete(), selon la méthode HTTP souhaitée. Par exemple :

```php
<?php

	$app->get('/hello', function($req, $res) {
	  $res->send('Hello, World!');
	});

?>
```

Cette définition de route répondra à toutes les requêtes GET envoyées à "/hello" en renvoyant la réponse "Hello, World!".

Vous pouvez également utiliser des routes dynamiques pour capturer des paramètres dans l'URL, en utilisant le format ":nom_parametre". Par exemple :

```php
<?php

	$app->get('/users/:id', function($req, $res) {
	  $id = $req->dynamicPath->var['id'];
	  $res->send("User ID: $id");
	});

?>
```

Cette définition de route répondra à toutes les requêtes GET envoyées à "/users/123", en renvoyant la réponse "User ID: 123".

## **Canaux statiques et dynamiques**

Rapido fournit deux types de canaux : statiques et dynamiques.

### **Canaux statiques**

Les canaux statiques sont utilisés pour capturer des parties spécifiques de l'URL. Ils sont définis en utilisant un chemin d'accès statique qui correspond exactement à l'URL demandée. Par exemple :

```php
<?php

	// Set of static chanel who catch 'GET' request and reply by an html file.
	$app -> get('/about',function($req , $res){
	  $res -> send('./public/about.html','file');
	});

?>
```

Ici, si une requête GET est effectuée sur l'URL "/about", le canal sera capturé et la réponse sera un fichier HTML renvoyé au client.

### **Canaux dynamiques**

Les canaux dynamiques sont utilisés pour capturer des parties variables de l'URL. Ils sont définis en utilisant une syntaxe de type « marqueur de position » pour indiquer les parties variables. Par exemple :

```php
<?php

	// Set of dynamic chanel who catch 'GET' request and reply by an html file.
	$app -> get('/post/:id',function($req , $res){
	  $id = $req -> dynamicPath-> var["id"];
	  $res -> send("You requested post with id : $id ",'text');
	});

?>
```

Ici, si une requête GET est effectuée sur l'URL "/post/123", le canal sera capturé et la réponse sera "You requested post with id : 123" renvoyée au client. Dans cet exemple, la partie variable de l'URL est capturée par le marqueur de position ":id" et est stockée dans le tableau associatif **`$req->params`**.

Il est important de noter que les canaux dynamiques peuvent capturer n'importe quel type de caractères, y compris les caractères d'espacement et les caractères spéciaux, ce qui peut être utile dans certaines situations.

## Middleware

Un middleware est une fonction ou une classe qui peut être utilisée pour intercepter et modifier les requêtes HTTP entrantes avant qu'elles ne soient traitées par le code de l'application. Dans le contexte de Rapido, un middleware est une classe qui implémente une méthode **`Program`** qui prend en paramètre le routeur de l'application. Le middleware peut alors manipuler la requête du client et la réponse du serveur avant de la passer à la prochaine fonction ou middleware dans la chaîne.

La méthode **`Program`** doit retourner le routeur pour permettre à la chaîne de middleware de se poursuivre. Le middleware peut également être utilisé pour effectuer des tâches telles que la validation de la requête, l'authentification de l'utilisateur, la gestion des erreurs, l'ajout de données à la réponse ou la modification des en-têtes HTTP.

Les middlewares peuvent être utilisés pour gérer des tâches récurrentes et ainsi améliorer la modularité de l'application. Ils peuvent également être utilisés pour appliquer des règles de sécurité, de validation ou de gestion d'erreurs de manière cohérente dans toute l'application.

## Start with Rapido

Exemple de fichier **`index.php`** utilisant Rapido avec un setup de base :

```php
<?php

	// Inclusion de Rapido
	require_once 'Rapido.php';
	
	// Instanciation de l'application
	$app = new Rapido\App();
	
	// Définition d'une route statique
	$app->get('/', function ($req, $res) {
	    $res->send('Hello, world!');
	});
	
	// Définition d'une route dynamique
	$app->get('/hello/:name', function ($req, $res) {
	    $name = $req->dynamicPath->var['name'];
	    $res->send("Hello, $name!");
	});
	
	// Utilisation d'un middleware
	class MyMiddleware extends Rapido\Middleware {
			public __constructor(){
					// Faire quelque chose à la construction du middleware
			}
	    public function Program($routeur) {
	        // Faire quelque chose avant le traitement de la requête
	        return $routeur;
	    }
	}

	$app->use(new MyMiddleware());
	
	// Lancement de l'application
	$app->handle();

?>

```

Dans cet exemple, nous avons inclus Rapido en incluant le fichier **`Rapido.php`**. Ensuite, nous avons instancié l'application en créant une nouvelle instance de la classe **`Rapido\App`**.

Nous avons ensuite défini deux routes : une route statique pour la page d'accueil (**`/`**) et une route dynamique pour la page **`/hello`** avec un paramètre **`name`**. Lorsque l'utilisateur accède à la page **`/hello/world`**, le middleware va être exécuté avant et après le traitement de la requête.

Enfin, nous avons lancé l'application en appelant la méthode **`listen()`** sur l'instance de l'application.

## Conclusion

En conclusion, Rapido est un framework PHP simple et efficace pour la création rapide d'applications web. Il offre une architecture MVC facile à utiliser ainsi que des fonctionnalités telles que les canaux statiques et dynamiques pour faciliter la gestion des routes et des paramètres d'URL. Les middlewares sont également disponibles pour gérer des tâches courantes telles que l'authentification ou la validation des entrées.

Si vous cherchez un framework PHP simple et efficace pour le développement d'applications web, Rapido est un choix solide.