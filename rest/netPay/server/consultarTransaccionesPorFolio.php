<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: consultar_transacciones_por_folio
* Path: /consultar_transacciones_por_folio
* Método: POST
* Descripción: Consultar datos de respuestas de NetPay que no fueron entregadas al usuario en local
*/

$app->post('/consultar_transacciones_por_folio', function (Request $request, Response $response){
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    //$Encrypt = new Encrypt();
    
    /*$token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
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
    }*/

    if( !include( '../../conexionMysqli.php' ) ){
        die( "No se pudo incluir el archivo de conexion!" );
    }
    $transactions = $request->getParam( "transactions" );//recibimos transacciones
    if( $transactions == null || $transactions == '' ){
        return json_encode( array( "status"=>200, "transacciones"=>"") );
    }
    $uniques_folios = '';
//concatena transacciones
    foreach ($transactions as $key => $transaction) {
        $uniques_folios .= ( $uniques_folios == '' ? '' : ',' );
        $uniques_folios .= "'{$transaction['unique_folio']}'";
    }
//consulta los datos por folio unico
    $transacciones = array();
    $sql = "SELECT * FROM vf_transacciones_netpay WHERE folio_unico IN( {$uniques_folios} )";
    $stm = $link->query( $sql ) or die( "Error al consultar respuesta de la transaccion : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
        $row['traceability'] = array( "folio_unico_transaccion"=>$row['folio_unico'], "id_sucursal"=>$row['id_sucursal'], "id_cajero"=>$row['id_cajero'], 
            "folio_venta"=>$row['folio_venta'], "id_sesion_cajero"=>$row['id_sesion_cajero'], "store_id_netpay"=>$row['store_id_netpay'], "smart_accounts"=>true );
        $transacciones[] = $row;
    }  
    return json_encode( array( "status"=>200, "transacciones"=>$transacciones) );
});
?>
