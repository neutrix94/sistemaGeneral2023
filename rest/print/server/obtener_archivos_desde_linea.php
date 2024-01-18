<?php
/*actualizado desde rama api_busqueda_archivos 2024-01-18*/
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: enviar archivos
* Path: /get_print_files
* Método: POST
* Descripción: Consulta archivos pendientes de descargar
*/
$app->post('/get_print_files', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	$file = array();
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
	if( $stm->num_rows <= 0 ){
		return 'ok';
	}
	while ( $row = $stm->fetch_assoc() ) {
		$files[] = $row;
	}
//codifica el arreglo de jsons
	$post_data = json_encode( array( "files"=>$files ) );
	return $post_data;
});

?>
