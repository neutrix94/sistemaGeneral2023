<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: valida_movimientos_almacen
* Path: /valida_movimientos_almacen
* Método: POST
* Descripción: Insercion de movimeintos de almacen a nivel producto
*/
$app->post('/valida_movimientos_almacen', function (Request $request, Response $response){  
    $resp = array();
    $resp['rows_response'] = array();
    $resp['rows_download'] = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/warehouseMovementsRowsVerification.php' ) ){
        die( "Error al incluir clase warehouseMovementsRowsVerification.php" );
    }
    $warehouseMovementsRowsVerification = new warehouseMovementsRowsVerification( $link );//instancia clase de comprobacion
    $petition_log = $request->getParam( 'petition' );//recibe folio unico de la peticion
    $verification = $request->getParam( 'verification' );
    $origin_store = $request->getParam( 'origin_store' );
    $movements = $request->getParam( 'rows' );
    if( $verification == true ){
    //consulta si la peticion existe en linea
        $resp['log_response'] = $warehouseMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log );
        $resp ['rows_response'] = $warehouseMovementsRowsVerification->warehouseMovementsValidation( $movements );//realiza proceso de comprobacion
    }
    $resp['rows_download'] = $warehouseMovementsRowsVerification->getPendingWarehouseMovement( -1, $origin_store );//consulta las comprobaciones pendientes de linea a local
    $resp['status'] = 200;
    return json_encode( $resp );
});

?>
