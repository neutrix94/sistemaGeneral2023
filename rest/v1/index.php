<?php
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
	/*
	* Version 2.1 Comprobacion y LOG
	* Instancia accesos BD
	*/
	require '../vendor/autoload.php';
	require '../src/config/db.php';         // DB Connect CL
	require '../src/config/dbFact.php';     // DB Connect Fact
	$app = new \Slim\App;
	$app->add(function (Request $request, Response $response, $next) {
		$response = $next($request, $response);
		return $response
			->withHeader('Access-Control-Allow-Origin', '*') // Permite solicitudes de cualquier origen
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS'); // Métodos HTTP permitidos
	});

	/*
	* Instancia utilities
	*/
	require 'utils/manageResponse.php';
	require 'utils/validaToken.php';

  /*
  * Instancia servicios por exponer
  */
  require 'endpoints/token.php';      //Token
  require 'endpoints/productos.php';  //Products
  require 'endpoints/ventas.php';     //Ventas
  require 'endpoints/emails.php';     //Correos
  require 'endpoints/facturas.php';   //Facturas
  require 'endpoints/facturaReceptor.php';   //Facturación: Valida contribuyente
  require 'endpoints/surteMuestra.php';   //Surtimeinto: Solicitud de muestra
  require 'endpoints/surtePedido.php';   //Surtimeinto: Solicitud para pedido
  require 'endpoints/notiPedido.php';   //Surtimeinto: Notifica surtimiento
  require 'endpoints/notificacionesPendientes.php';   //Surtimeinto: Obtiene notificaciones pendientes


  //Servicios de Servidor
	require 'server/verificacion_registros.php';//comprobacion de los registros pendientes
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
	/*implementacion Osacr 2023 para insertar modificaciones en ventas y movimientos de almacen (servidor)*/
	require 'server/inserta_registros_sincronizacion_ventas.php';
	require 'server/inserta_registros_sincronizacion_mov_almacen.php';
	require 'server/inserta_registros_sincronizacion_mov_p_p.php';
	require 'server/inserta_registros_sincronizacion_transferencias.php';

	require 'server/obtener_movimientos_por_sumar_en_local.php';
	//server consultas predisenadas desarrollo ( 2023 )
	require 'server/ejecuta_consulta_en_servidor.php';
	//server consultas predisenadas desarrollo ( 2023 )
	require 'server/restauracion.php';


//comprobaciones 2024
	require 'server/wharehouseMovementVerification.php';
	require 'server/warehouseMovementVerificationServer.php';

	require 'server/wharehouseProductProviderMovementVerification.php';
	require 'server/warehouseProductProviderMovementVerificationServer.php';
	require 'server/salesVerificationServer.php';
	require 'server/ReturnsVerificationServer.php';
	require 'server/RowsVerificationServer.php';


//Servicios de Cliente
	require 'client/verificacion_registros_pendientes.php';//comprobacion de los registros pendientes
require 'client/registros_sincronizacion.php';
	require 'client/ventas.php';
	require 'client/devoluciones.php';
	require 'client/movimientos_almacen.php';
	require 'client/validaciones_ventas.php';
	require 'client/movimientos_proveedor_producto.php';
	/*implementacion Oscar 2023 para insertar modificaciones en ventas y movimientos de almacen (cliente)*/
require 'client/registros_sincronizacion_ventas.php';
	require 'client/registros_sincronizacion_mov_almacen.php';
	require 'client/registros_sincronizacion_mov_p_p.php';
	require 'client/registros_sincronizacion_transferencias.php';

$app->run();
