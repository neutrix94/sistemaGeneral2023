<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: recuperar_respuesta_por_folio_unico
* Path: /recuperar_respuesta_por_folio_unico
* Método: POST
* Descripción: Obtener datos de respuestas de NetPay por folio
*/

$app->post('/recuperar_respuesta_por_folio_unico', function (Request $request, Response $response){
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    //$Encrypt = new Encrypt();
//validacion de token
    $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
    //$token = $Encrypt->decryptText($token, 'CDLL2024');//desencripta token
    if (empty($token) || strlen($token)<36 ) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
    }else{//Consulta vigencia
        try{
            $resultadoToken = $vt->verificaExistenciaToken($token);
        if ($resultadoToken->rowCount()==0) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
        }
        }catch (PDOException $e) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
        }
    }
    
//recibe el folio unico de la transaccion
    $folio_unico_transaccion = $request->getParam( "folio_unico" );
    if( $folio_unico_transaccion == '' || $folio_unico_transaccion == null ){
        return json_encode( array( "status"=>400, "error"=>"El atributo folio_unico es requerido." ) );
    }
//recibe el id de la sucursal
    $sucursal = $request->getParam( "id_sucursal" );
    if( $sucursal == '' || $sucursal == null ){
        return json_encode( array( "status"=>400, "error"=>"El atributo id_sucursal es requerido." ) );
    }
//incluye conexion mysqli
    if( !include( '../../conexionMysqli.php' ) ){
        die( "No se pudo incluir el archivo de conexion!" );
    }
//incluye libreria de api de netPay
    if( !include( '../../code/especiales/netPay/apiNetPay.php' ) ){
        die( "No se pudo incluir libreria de netPay!" );
    }
    $apiNetPay = new apiNetPay( $link, $sucursal );
//envia peticion
    $apiNetPay->getStatusByFolio( $folio_unico_transaccion );
   // die('here');
    $message = '';
    $row = null;
    $vueltas = 0;
    while( $message == '' || $message == null ){
        sleep( 2 );
        $sql = "SELECT * FROM vf_transacciones_netpay WHERE folio_unico = '{$folio_unico_transaccion}'";
        $stm = $link->query( $sql ) or die( "Error al consultar estado de la transaccion : {$link->error}" );
        $row = $stm->fetch_assoc();
        $message = $row['message'];
        $vueltas ++;
        if( $vueltas == 10 && ( $message == '' || $message == null ) ){
            $row['status'] = 400;
            $message = "No fue posible encontrar la peticion; verifica y vuelve a intentar.";
            $row['message'] = $message;
        }
    }
//actualiza la respuesta el la base de datos
    //return json_encode( array( "status"=>200, "transacciones"=>$transacciones) );
    return json_encode( $row );
});
?>
