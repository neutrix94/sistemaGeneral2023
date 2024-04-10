<?php
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Psr\Http\Message\ServerRequestInterface as Request;
	/*
	* Endpoint: subir_peticion_netPay
	* Path: /subir_peticion_netPay
	* Método: POST
	* Descripción: Subir peticion de NetPay
	*/
	$app->post('/subir_peticion_netPay', function (Request $request, Response $response){
		if( !include( '../../conexionMysqli.php' ) ){
			die( "No se pudo incluir el archivo de conexion!" );
		}
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
