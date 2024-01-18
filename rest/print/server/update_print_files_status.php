<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: enviar archivos
* Path: /send_file
* Método: POST
* Descripción: Envia los archivos al servidor destino
*/
$app->post('/actualizar_status_archivos', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	$file = array();
	//die( 'one' );
	$files = $request->getParam( 'ok_rows' );
	$files_ok = explode(',', $files );
	foreach ($files_ok as $key => $file_id) {
		$sql = "UPDATE sys_archivos_descarga SET descargado = '1' WHERE id_archivo = {$file_id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar status de archivo : {$sql} {$link->error}" );
	}
	die('ok');
//recibe id de la sucursal
//	$store_id = $request->getParam( 'destinity_store_id' );
//obtiene los archivos pendientes de descargar
	$sql = "";
	//$stm = $link->query( $sql ) or die( "Error al consultar los archivos por descargar : {$sql} {$link->error}" );
	//while ( $row = $stm->fetch_assoc() ) {
	//	$files[] = $row;
	//}
//codifica el arreglo de jsons
	$post_data = json_encode( array( "files"=>$files ) );
	return $post_data;
});
?>