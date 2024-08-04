<?php
/* Version 1.1 Para depurar sincronizacion ( 2024-08-03 )*/
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
		require( 'client/depurar_sincronizacion_1.php' );
		require( 'client/depurar_logs_1.php' );
		require( 'client/agrupacion_por_dia.php' );
		require( 'client/consultar_registros_restantes.php' );

	//server
		require( 'server/obtener_registros_restantes.php' );
		//require( 'client/obtenerInformacionSincronizacion.php' );
	/*fin de cambio Oscar 2023/11/28*/

	$app->run();
?>