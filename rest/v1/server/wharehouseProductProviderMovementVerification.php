<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: valida_movimientos_almacen_proveedor_producto
* Path: /valida_movimientos_almacen_proveedor_producto
* Método: POST
* Descripción: Insercion de movimientos de almacen a nivel proveedor producto
*/
$app->post('/valida_movimientos_almacen_proveedor_producto', function (Request $request, Response $response){  
    $resp = array();
    $resp['rows_response'] = array();
    $resp['rows_download'] = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/warehouseProductProviderMovementsRowsVerification.php' ) ){
        die( "Error al incluir clase warehouseProductProviderMovementsRowsVerification.php" );
    }
    $warehouseProductProviderMovementsRowsVerification = new warehouseProductProviderMovementsRowsVerification( $link );//instancia clase de comprobacion
    $petition_log = $request->getParam( 'petition' );//recibe folio unico de la peticion
    $verification = $request->getParam( 'verification' );
    $origin_store = $request->getParam( 'origin_store' );
    $movements = $request->getParam( 'rows' );
    if( $verification == true ){
    //consulta si la peticion existe en linea
        $resp['log_response'] = $warehouseProductProviderMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log );
        $resp ['rows_response'] = $warehouseProductProviderMovementsRowsVerification->warehouseProductProviderMovementsValidation( $movements );//realiza proceso de comprobacion
    }
    $resp['rows_download'] = $warehouseProductProviderMovementsRowsVerification->getPendingWarehouseProductProviderMovement( -1, $origin_store );//consulta las comprobaciones pendientes de linea a local
    $resp['status'] = 200;
    return json_encode( $resp );
});

?>
