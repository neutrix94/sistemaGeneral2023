<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: enviar archivos
* Path: /send_file
* Método: POST
* Descripción: Envia los archivos al servidor destino
*/
$app->post('/enviar_archivo_red_local', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	$file = array();
//recibe id de archivo
    $id_archivo = $request->getParam( 'id_archivo' );
	$store_id = $request->getParam( 'id_sucursal' );
	$id_usuario = $request->getParam( 'id_usuario' );
	$id_modulo_impresion = $request->getParam( 'id_modulo_impresion' );
//obtiene los archivos pendientes de descargar
	$sql = "SELECT
				id_archivo AS file_id,
				tipo_archivo AS file_type,
				nombre_archivo AS file_name,
				ruta_origen AS file_origin,
				ruta_destino AS file_destinity,
				id_sucursal AS file_store 
			FROM sys_archivos_descarga
			WHERE id_archivo = {$id_archivo}
			AND id_sucursal = {$store_id}";//die($sql);
	$stm = $link->query( $sql ) or die( "Error al consultar archivo por descargar : {$sql} {$link->error}" );
	while ( $row = $stm->fetch_assoc() ) {
		$files[] = $row;
	}
//codifica el arreglo de jsons
	$post_data = json_encode( array( "files"=>$files ) );
//consulta si tiene endpoint especifico local por usuario
    $url_base = "";
    $sql = "SELECT
                miu.endpoint_api_destino_local
            FROM sys_modulos_impresion_usuarios miu
            WHERE miu.id_modulo_impresion = {$id_modulo_impresion}
            AND miu.id_usuario = {$id_usuario}";//die($sql);
	$stm = $link->query( $sql ) or die( "Error al consultar el endpoint por usuario : {$sql}" );
    $conteo = $stm->num_rows;
    $row = $stm->fetch_assoc();
    if( $conteo <= 0 || $row['endpoint_api_destino_local'] == '' ){
//consulta si tiene endpoint especifico local por sucursal
        $sql = "SELECT
            mis.endpoint_api_destino_local
        FROM sys_modulos_impresion_sucursales mis
        WHERE mis.id_modulo_impresion = {$id_modulo_impresion}
        AND mis.id_sucursal = {$store_id}";//die($sql);
    	$stm = $link->query( $sql ) or die( "Error al consultar el endpoint por sucursal : {$sql}" );
        $row = $stm->fetch_assoc();
        $url_base = $row['endpoint_api_destino_local'];
    }//else{
        $url_base = $row['endpoint_api_destino_local'];
    //}
    if( $url_base == "" || $url_base == null ){
        die( "No hay APIS destino para este modulo : {$url_base}. {$sql}" );
    }
//consume api en el servidor destino//$row = $stm->fetch_assoc();
	$url = "{$url_base}";//url
	//die("{$url}");
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
	//return $resp . $url_base;
//decodifica el json de respuesta
	$result = json_decode(json_encode($resp), true);
	$result = json_decode( $result );
	if( $result->ok_rows != '' ){
	//ejecuta actualizacion a descargados y elimina archivo
		$sql = "SELECT id_archivo, ruta_destino, nombre_archivo FROM sys_archivos_descarga WHERE id_archivo IN( {$result->ok_rows} )";
		$stm_oks = $link->query( $sql ) or die( "Error al consultar datos del archivo" ); 
		while( $ok_row = $stm_oks->fetch_assoc() ){
			$sql = "UPDATE sys_archivos_descarga SET descargado = '1' WHERE id_archivo IN( {$ok_row['id_archivo']} )";//{$result->ok_rows}
			$stm = $link->query( $sql ) or die( "Error al actualizar los registros de archivos descargados : {$link->error}" );
	//eliminar archivo
			try{ 
				//die( "../../{$ok_row['ruta_destino']}{$ok_row['nombre_archivo']}" );
				unlink( "../../{$ok_row['ruta_destino']}{$ok_row['nombre_archivo']}" );
			}catch( Exception $e ){
				die( "Error al eliminar el archivo ../..{$ok_row['ruta_destino']}{$ok_row['nombre_archivo']} : {$e->getMessage()}" );
			}
		}
		
		//die( $sql );
	}else{
		die( "Error {$result}" );
	}
	return 'ok';
	//return $result;
	//return $resp;
});

?>
