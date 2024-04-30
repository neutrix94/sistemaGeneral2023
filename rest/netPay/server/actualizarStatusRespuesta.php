<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: actualizar_status_transacciones
* Path: /actualizar_status_transacciones
* Método: POST
* Descripción: Actualizar capo de notificacion_vista de respuestas de NetPay
*/

$app->post('/actualizar_status_transacciones', function (Request $request, Response $response){
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    
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
	$transacciones = $request->getParam( "registros" );
	$link->autocommit(false);
	foreach ($transacciones as $key => $transaccion) {
		$sql = "UPDATE vf_transacciones_netpay SET notificacion_vista = 1 WHERE folio_unico = '{$transaccion['folio_unico']}'";
		$stm = $link->query( $sql );//or die( "error" );
		if( $link->error ){
			return json_encode( array( "status"=>400, "message"=>"Error al actualizar status de notificacion : {$link->error}" ) );
		}
	}
	$link->autocommit(true);
	return json_encode( array( "status"=>200, "message"=>"Registro(s) actualizados exitosamente." ) );
	//return json_encode( array( "response"=>"ok" ) );
});
?>
