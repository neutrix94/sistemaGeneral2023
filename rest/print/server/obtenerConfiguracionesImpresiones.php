<?php
/*actualizado desde rama api_busqueda_archivos 2024-01-18*/
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: enviar actualizar_status_archivos
* Path: /actualizar_status_archivos
* Método: POST
* Descripción: Actualiza status de archivos
*/
$app->post('/obtener_configuracion_impresion', function (Request $request, Response $response){
	$resp = array();
	$resp['modulos'] = array();
	$resp['impresoras_especificas'] = array();
	$resp['message'] = "ok";
	$resp['status_code'] = 200;
	include( '../../conexionMysqli.php' );
	$file = array();
	$sucursal = $request->getParam( 'id_sucursal' );
	//$resp = array( "message"=>"ok", "status_code"=>200, "store_id"=>$sucursal );
	//return json_encode( $resp );
//consulta los modulos y sus rutas
	$sql = "SELECT 
				CONCAT( 'cache/', s.nombre, '/', mi.nombre_carpeta_modulo ) AS carpeta

			FROM sys_sucursales s
			JOIN sys_modulos_impresion mi
			ON s.id_sucursal = {$sucursal}";
	$sql = "SELECT
				mi.nombre_modulo AS nombre_modulo,
				REPLACE( s.nombre, ' ', '_' ) AS usuario,
			    CONCAT( c.path, '/', c.nombre_carpeta ) AS ruta,
			    i.nombre_impresora AS impresora,
			    mis.comando_impresion,
			    mis.extension_archivo,
			    '0' AS habilitado,
			    mis.endpoint_api_destino
			FROM sys_modulos_impresion_sucursales mis
			LEFT JOIN sys_modulos_impresion mi
			ON mis.id_modulo_impresion = mi.id_modulo_impresion
			LEFT JOIN sys_sucursales s
			ON s.id_sucursal = mis.id_sucursal
			LEFT JOIN sys_carpetas c
			ON c.id_carpeta = mis.id_carpeta
			LEFT JOIN sys_impresoras_sucursales i
			ON i.id_impresora_sucursal = mis.id_impresora_sucursal
			WHERE mis.id_sucursal = {$sucursal}";
	$stm = $link->query( $sql ) or die( "Error al consultar modulos y sus carpetas : {$link->error}" );
	while ( $row = $stm->fetch_assoc() ) {
		array_push( $resp['modulos'], $row );
	}
//consulta modulos por usuarios
	$sql = "SELECT
				mi.nombre_modulo AS nombre_modulo,
				CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS usuario,
			    CONCAT( c.path, '/', c.nombre_carpeta ) AS ruta,
			    i.nombre_impresora AS impresora,
			    miu.comando_impresion,
			    miu.extension_archivo,
			    '0' AS habilitado,
			    miu.endpoint_api_destino
			FROM sys_modulos_impresion_usuarios miu
			LEFT JOIN sys_modulos_impresion mi
			ON miu.id_modulo_impresion = mi.id_modulo_impresion
			LEFT JOIN sys_users u
			ON u.id_usuario = miu.id_usuario
			LEFT JOIN sys_carpetas c
			ON c.id_carpeta = miu.id_carpeta
			LEFT JOIN sys_impresoras_sucursales i
			ON i.id_impresora_sucursal = miu.id_impresora_sucursal";
	$stm = $link->query( $sql ) or die( "Error al consultar modulos de impresion por usuarios y sus carpetas : {$link->error}" );
	while ( $row = $stm->fetch_assoc() ) {
		array_push( $resp['modulos'], $row );
	}
	return json_encode( $resp );
});
?>