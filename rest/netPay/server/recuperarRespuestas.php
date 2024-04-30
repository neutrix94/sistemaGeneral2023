<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: recuperar_respuestas
* Path: /recuperar_respuestas
* Método: POST
* Descripción: Obtener datos de respuestas de NetPay que no fueron entregadas al usuario
*/

$app->post('/recuperar_respuestas_transacciones', function (Request $request, Response $response){
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
    $user_id = $request->getParam( "id_usuario" );
//consulta los datos de folio unico
    $transacciones = array();
    $sql = "SELECT * FROM vf_transacciones_netpay WHERE id_cajero = '{$user_id}' AND `message` != '' AND notificacion_vista = 0";
    $stm = $link->query( $sql ) or die( "Error al consultar respuesta de la transaccion : {$link->error}" );
    //if( $stm->num_rows <= 0 ){
      //  return json_encode( array( "status"=>400, "message"=>"La transaccion con el folio unico {$user_id} no existe, verifica y vuelve a intentar!" ) );
    //}else{
    while( $row = $stm->fetch_assoc() ){
        $transacciones[] = $row;
    }  
    return json_encode( array( "status"=>200, "transacciones"=>$transacciones) );
    //}
    //die('ok');
});
?>
