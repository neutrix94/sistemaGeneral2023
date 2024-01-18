<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: enviar archivos
* Path: /send_file
* Método: POST
* Descripción: Envia los archivos al servidor destino
*/
$app->post('/send_file', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	$file = array();
	//die( 'one' );
	//$file_id = $request->getParam( 'file_id' );
//recibe id de la sucursal
	$store_id = $request->getParam( 'destinity_store_id' );
//obtiene los archivos pendientes de descargar
	$sql = "SELECT
				id_archivo AS file_id,
				tipo_archivo AS file_type,
				nombre_archivo AS file_name,
				ruta_origen AS file_origin,
				ruta_destino AS file_destinity,
				id_sucursal AS file_store 
			FROM sys_archivos_descarga
			WHERE descargado = 0
			AND id_sucursal = {$store_id}
			ORDER BY id_archivo DESC
			LIMIT 10";
	$stm = $link->query( $sql ) or die( "Error al consultar los archivos por descargar : {$sql} {$link->error}" );
	while ( $row = $stm->fetch_assoc() ) {
		$files[] = $row;
	}
//codifica el arreglo de jsons
	$post_data = json_encode( array( "files"=>$files ) );
//obtiene los datos principales de la sucursal y el / los archivos
	$sql = "SELECT 
				endpoint_impresion_remota AS store_print_dns
			FROM ec_configuracion_sucursal
			WHERE id_sucursal = {$store_id}";
	$stm = $link->query( $sql ) or die( "Error al consultar el dominio de la sucursal destino" );
//consume api en el servidor destino
	$row = $stm->fetch_assoc();
	$url = "{$row['store_print_dns']}/rest/print/recibir_archivo";//url
	
	$resp = "";
	$crl = curl_init( $url );
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($crl, CURLINFO_HEADER_OUT, true);
	curl_setopt($crl, CURLOPT_POST, true);
	curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
	//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
	curl_setopt($crl, CURLOPT_HTTPHEADER, array(
	  'Content-Type: application/json',
	  'token: ' . $token)
	);
	$resp = curl_exec($crl);//envia peticion
	curl_close($crl);
//decodifica el json de respuesta
	$result = json_decode(json_encode($resp), true);
	$result = json_decode( $result );
	
	if( $result->ok_rows != '' ){
	//ejecuta actualizacion a descargados
		$sql = "UPDATE sys_archivos_descarga SET descargado = '1' WHERE id_archivo IN( {$result->ok_rows} )";
		$stm = $link->query( $sql ) or die( "Error al actualizar los registros de archivos descargados : {$link->error}" );
				
		//die( $sql );
	}else{
		die( "Algo salio mal : " );
	}
	return 'ok';
	//return $result;
	//return $resp;
});

?>
