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
*/
/*oscar 2023/11/28 para depuracion de registos de sincronizacion y LOGS*/
	require( 'client/depurar_sincronizacion.php' );
	require( 'client/depurar_logs.php' );
	require( 'client/agrupacion_por_dia.php' );
	//require( 'client/obtenerInformacionSincronizacion.php' );
	require( 'client/consultar_registros_restantes.php' );


	require( 'server/obtener_registros_restantes.php' );
/*fin de cambio Oscar 2023/11/28*/

$app->run();

?>