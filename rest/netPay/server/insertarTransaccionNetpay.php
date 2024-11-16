<?php
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Psr\Http\Message\ServerRequestInterface as Request;
	/*
	* Endpoint: subir_peticion_netPay
	* Path: /subir_peticion_netPay
	* Método: POST
	* Descripción: Subir peticion de NetPay
	*/
	$app->post('/insertar_peticion_transaccion', function (Request $request, Response $response){
		$db = new db();
		$db = $db->conectDB();
		$rs = new manageResponse();
		$vt = new tokenValidation();
		//$Encrypt = new Encrypt();
		$token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
		//$token = $Encrypt->decryptText($token, 'CDLL2024');//desencripta token
		if (empty($token) || strlen($token)<36 ) {
		//Define estructura de salida: Token requerido
			return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
		}else{
		  //Consulta vigencia
			try{
				$resultadoToken = $vt->verificaExistenciaToken($token);
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
		$id_cajero = $request->getParam( 'id_usuario' );
		if( $id_cajero == '' || $id_cajero == null ){
			return json_encode( array( "status"=>400, "message"=>"El atributo 'id_usuario' es requerido" ) );
		}
		$id_sucursal = $request->getParam( 'id_sucursal' );
		if( $id_sucursal == '' || $id_sucursal == null ){
			return json_encode( array( "status"=>400, "message"=>"El atributo 'id_sucursal' es requerido" ) );
		}
		$terminalId = $request->getParam( 'terminal_id' );
		if( $terminalId == '' || $terminalId == null ){
			return json_encode( array( "status"=>400, "message"=>"El atributo 'id_terminal' de la terminal es requerido" ) );
		}
		$store_id_netpay = $request->getParam( 'store_id_netpay' );
		if( $store_id_netpay == '' || $store_id_netpay == null ){
			return json_encode( array( "status"=>400, "message"=>"El atributo 'store_id_netpay' es requerido" ) );
		}
		$sale_folio = $request->getParam( 'sale_folio' );
		if( $sale_folio == '' || $sale_folio == null ){
			return json_encode( array( "status"=>400, "message"=>"El atributo 'sale_folio' es requerido" ) );
		}

	//consulta folio unico de la sucursal
		$sql = "SELECT 
				prefijo,
				(SELECT value FROM api_config WHERE `name` = 'path' ) AS api_path
			FROM sys_sucursales 
			WHERE acceso = 1";
		$stm = $link->query( $sql );
		if( $link->error ){
			return json_encode( array( "status"=>400, "message"=>"Error al consultar prefijo de sucural para generar el folio unico : {$link->error}" ) );
		}
		$row = $stm->fetch_assoc();
		$prefix = $row['prefijo'];
		$path_api = $row['api_path'];
	
		$link->autocommit(false);
	//inserta la peticion de la transaccion
		$sql = "INSERT INTO vf_transacciones_netpay ( id_cajero, id_sucursal, terminalId, store_id_netpay, folio_venta ) 
				VALUES ( {$id_cajero}, {$id_sucursal}, '{$terminalId}', '{$store_id_netpay}', '{$sale_folio}' )";
		$stm = $link->query( $sql );
		if( $link->error ){
			return json_encode( array( "status"=>400, "message"=>"Error al insertar transaccion netPay : {$link->error}" ) );
		}
	//recupera id insertado
		$sql = "SELECT LAST_INSERT_ID() AS id";
		$stm = $link->query( $sql );
		if( $link->error ){
			return json_encode( array( "status"=>400, "message"=>"Error al consultar el ultimo id insertado : {$link->error}" ) );
		}
		$row = $stm->fetch_assoc();
		$id_registro = $row['id'];
	//actualiza el folio unico
		$sql = "UPDATE vf_transacciones_netpay SET folio_unico = CONCAT( '{$prefix}_TNP_', {$id_registro} ) WHERE id_transaccion_netpay = {$id_registro}";
		$stm = $link->query( $sql );
		if( $link->error ){
			return json_encode( array( "status"=>400, "message"=>"Error al actualizar el folio unico de la transaccion : {$link->error}" ) );
		}
		$link->autocommit(true);
		
		return json_encode( array( "status"=>200, "folio_unico_transaccion"=>"{$prefix}_TNP_{$id_registro}" ) );
	});
?>
