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

require 'client/sendFile.php';
require 'server/getPrints.php';





require 'client/sendFileDirectly.php';
require 'server/getPrintsDirectly.php';

$app->run();
