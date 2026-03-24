<?php
require '../vendor/autoload.php';

use db\connection;

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


connection::createConn();

// Initialisation de Slim
$app = AppFactory::create();

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/../template');
$twig   = new Environment($loader);

// Ajout d'un middleware pour le trailing slash
$app->add(function (Request $request, $handler) {
    $uri  = $request->getUri();
    $path = $uri->getPath();

    if ($path == '/') {
        return $handler->handle($request);
    }

    if(str_ends_with($path, '/')){
        $uri = $uri->withPath(substr($path, 0, -1));
    }else{
        return $handler->handle($request);
    }

    if ($request->getMethod() !== 'GET') {
        $request = $request->withUri($uri);
        return $handler->handle($request);
    }

    $response = new \Slim\Psr7\Response();
    return $response->withHeader('Location', (string)$uri)->withStatus(301);
});


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token                  = md5(uniqid(rand(), TRUE));
    $_SESSION['token']      = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './index.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname($_SERVER['SCRIPT_NAME']);

$routes = require 'routes.php';
$routes($app, $twig, $menu, $chemin);

$app->run();
