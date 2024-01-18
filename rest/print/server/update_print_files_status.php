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
$app->post('/actualizar_status_archivos', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	$file = array();
	$files = $request->getParam( 'ok_rows' );
	$files_ok = explode(',', $files );
	foreach ($files_ok as $key => $file_id) {
		$sql = "UPDATE sys_archivos_descarga SET descargado = '1' WHERE id_archivo = {$file_id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar status de archivo : {$sql} {$link->error}" );
	}
	die('ok');
});
?>