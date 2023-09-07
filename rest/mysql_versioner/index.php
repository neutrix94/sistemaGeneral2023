<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Factory\AppFactory;
/*
* Instancia accesos BD
*/
require '../vendor/autoload.php';
require '../src/config/db.php';         // DB Connect CL
require '../src/config/dbFact.php';     // DB Connect Fact
$app = new \Slim\App;

/*
* Instancia utilities

die('here 1');
require 'utils/manageResponse.php';
require 'utils/validaToken.php';
/*
* Instancia servicios por exponer
*/

require 'client/updateScripts.php';

require 'server/getScripts.php';
/*require 'endpoints/token.php';      //Token
require 'endpoints/productos.php';  //Products
require 'endpoints/ventas.php';     //Ventas
require 'endpoints/emails.php';     //Correos
require 'endpoints/facturas.php';   //Facturas

//implementacion Oscar 2023 para sincronizacion ( Servidor )
require 'server/inserta_registros_sincronizacion.php';
require 'server/actualiza_inventarios_productos.php';
require 'server/inserta_ventas.php';
require 'server/inserta_devoluciones.php';
require 'server/inserta_movimientos_almacen.php';
require 'server/comprobacion_movimientos_almacen.php';
require 'server/inserta_validaciones_ventas.php';
require 'server/inserta_movimientos_proveedor_producto.php';
require 'server/actualiza_inventarios_proveedor_producto.php';
require 'server/actualiza_peticion.php';
	//server consultas predisenadas desarrollo ( 2023 )
	require 'server/ejecuta_consulta_en_servidor.php';
//implementacion Oscar 2023 para sincronizacion ( Clientes )
require 'client/registros_sincronizacion.php';
require 'client/ventas.php';
require 'client/devoluciones.php';
require 'client/movimientos_almacen.php';
require 'client/validaciones_ventas.php';
require 'client/movimientos_proveedor_producto.php';*/


$app->run();
