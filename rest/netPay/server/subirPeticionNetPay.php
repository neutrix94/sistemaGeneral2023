<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtenerInformacionRespuesta
* Path: /obtenerInformacionRespuesta
* Método: POST
* Descripción: Obtener datos de una respuesta de NetPay
*/

$app->post('/subir_peticion_netPay', function (Request $request, Response $response){
	
	$folio_unico = $request->getParam("folio_unico");
	if( $folio_unico == null || $folio_unico == '' ){
		return json_encode( array( "status"=>400, "message"=>"Error, folio único inválido" ) );
	}
	$sql = "INSERT INTO vf_transacciones_netpay ( folio_unico ) VALUES ( '{$folio_unico}' )";
	$stm = $link->query( $sql );
	if( !$stm ){
		return json_encode( array( "status"=>400, "message"=>"Error al insertar el registro de la transaccion : {$link->error}" ) );
	}
	return json_encode( array( "status"=>200, "message"=>"ok" ) );
});
?>
