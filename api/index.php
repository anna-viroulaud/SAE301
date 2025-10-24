<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "src/Controller/ProductController.php";
require_once "src/Controller/UserController.php";
require_once "src/Controller/AuthController.php";
require_once "src/Controller/CartController.php";
require_once "src/Class/HttpRequest.php";

session_start();



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** IMPORTANT
 * 
 *  De part le .htaccess, toutes les requêtes seront redirigées vers ce fichier index.php
 * 
 *  On pose le principe que toutes les requêtes, pour être valides, doivent être dee la forme :
 * 
 *  http://.../api/ressources ou  http://.../api/ressources/{id}
 * 
 *  Par exemple : http://.../api/products ou  http://.../api/products/3
 */



/**
 *  $router est notre "routeur" rudimentaire.
 * 
 *  C'est un tableau associatif qui associe à chaque nom de ressource 
 *  le Controller en charge de traiter la requête.
 *  Ici ProductController est le controleur qui traitera toutes les requêtes ciblant la ressource "products"
 *  On ajoutera des "routes" à $router si l'on a d'autres ressource à traiter.
 */
$router = [
    "products" => new ProductController(),
    "users" => new UserController(),
    "auth" => new AuthController(),
    "carts" => new CartController()
];

// objet HttpRequest qui contient toutes les infos utiles sur la requêtes (voir class/HttpRequest.php)
$request = new HttpRequest();

// gestion des requêtes preflight (CORS)
if ($request->getMethod() == "OPTIONS"){
    http_response_code(200);
    exit();
}

// on récupère la ressource ciblée par la requête
$route = $request->getRessources();

try {
    $route = $request->getRessources();

    if ( isset($router[$route]) ){ 
        $ctrl = $router[$route];
        $json = $ctrl->jsonResponse($request);
        if ($json){
            header("Content-type: application/json;charset=utf-8");
            echo $json;
        } else {
            http_response_code(404);
        }
        die();
    }
    http_response_code(404);
    die();
} catch (Throwable $e) {
    // En dev : renvoyer l'erreur en JSON pour debug
    http_response_code(500);
    header("Content-type: application/json;charset=utf-8");
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
    error_log("API ERROR: ".$e->getMessage()." in ".$e->getFile()." on line ".$e->getLine());
    exit();
}

?>