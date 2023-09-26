<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//use Slim\Http\Request;
//use Slim\Http\Response;
//ini_set('max_execution_time', 1);
/*
* Endpoint: 
* Path: /recibir_archivo
* Método: POST
* Descripción: 
*/
$app->post('/recibir_archivo', function (Request $request, Response $response){
	include( '../../conexionMysqli.php' );
	include( './utils/Print.php' );
	
	$Print = new PrintApi( $link, '../../' );//instancia de la clase
	$files = $request->getParam( "files" );
	$resp = array();
	$resp['ok_rows'] = '';
	$resp['error_rows'] = '';
	foreach( $files AS $key => $file ){
		$resultado = $Print->files_download( $file['file_origin'], $file['file_destinity'], $file['file_name'] );
		if( $resultado == 'ok' ){
			$resp['ok_rows'] .= ( $resp['ok_rows'] == '' ? '' : ',' );
			$resp['ok_rows'] .= $file['file_id'];
		}
	}
	return json_encode($resp, true);
	//var_dump($files);
	//die( 'here' );
  /*if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  return 'ok';*/
  //return json_encode( array( "response" => "Movimientos ok" ) );
});

?>
