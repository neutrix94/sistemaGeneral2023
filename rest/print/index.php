<?php
/*actualizado desde rama api_busqueda_archivos 2024-01-18*/
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require '../src/config/db.php';         // DB Connect CL
$app = new \Slim\App;

//cliente
require 'client/sendFile.php';
require 'client/obtener_archivos_desde_local.php';
//servidor
require 'server/getPrints.php';
require 'server/obtener_archivos_desde_linea.php';
require 'server/update_print_files_status.php';

$app->run();
