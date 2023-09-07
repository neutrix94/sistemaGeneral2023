<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;
/*
* Instancia accesos BD
*/
require '../vendor/autoload.php';

$app = new \Slim\App;

/*
* Instancia servicios por exponer
*/

//client
require 'client/envia_cliente.php';

//server
require 'server/inserta_cliente.php';

$app->run();
