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
require 'client/envia_cliente_facturacion.php';
require 'client/descarga_clientes.php';
require 'client/barre_y_envia_clientes_a_administracion_facturacion.php';//barrido y envio de ventas a administracion facturacion

//server
require 'server/inserta_cliente.php';
require 'server/insertaClienteGeneralLineaDirecto.php';
//die( 'here' );

$app->run();
