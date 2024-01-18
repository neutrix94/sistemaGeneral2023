<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;
/*
* Instancia accesos BD
*/
require '../vendor/autoload.php';
require '../src/config/db.php';         // DB Connect CL
//require '../src/config/dbFact.php';     // DB Connect Fact
$app = new \Slim\App;

//die( 'here' );
require 'client/sendFile.php';
require 'client/obtener_archivos_desde_local.php';

require 'server/getPrints.php';
require 'server/obtener_archivos_desde_linea.php';
require 'server/update_print_files_status.php';
//require 'client/sendFileDirectly.php';
//require 'server/getPrintsDirectly.php';
$app->run();
