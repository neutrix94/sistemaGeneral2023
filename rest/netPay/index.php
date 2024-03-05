<?php
header('Content-Type: text/html; charset=utf-8');

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;
/*
* Instancia accesos BD
*/
require '../vendor/autoload.php';
//require '../src/config/db.php';         // DB Connect CL
//require '../src/config/dbFact.php';     // DB Connect Fact
$config = [
    'settings' => [
        'displayErrorDetails' => true, // Solo para desarrollo
        'determineRouteBeforeAppMiddleware' => true,
        'addContentLengthHeader' => false,
        'outputBuffering' => 'append',
        'charset' => 'UTF-8' // Asegúrate de configurar la codificación aquí
    ],
];
$app = new \Slim\App( $config );

//cliente
//require 'client/domain_test.php';

//servidordie('okok');
require 'server/netPayResponse.php';
//require 'server/test.php';

$app->run();
