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
		$db = new db();
		$db = $db->conectDB();
		$rs = new manageResponse();
		$vt = new tokenValidation();
		$token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
		if (empty($token) || strlen($token)<36 ) {
		//Define estructura de salida: Token requerido
			return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
		}else{
		  //Consulta vigencia
			try{
				$resultadoToken = $vt->validaToken($token);
			if ($resultadoToken->rowCount()==0) {
				return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
			}
			}catch (PDOException $e) {
				return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
			}
		}
		
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
