<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_comprobacion_movimientos_almacen_proveedor_producto
* Path: /actualiza_comprobacion_movimientos_almacen_proveedor_producto
* Método: POST
* Descripción: Comprobacion de movimientos almacen a nivel proveedor producto
*/
$app->post('/actualiza_comprobacion_movimientos_proveedor_producto', function (Request $request, Response $response){  
    $resp = array();
    //$resp['rows_response'] = array();
    //$resp['rows_download'] = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/verification/warehouseProductProviderMovementsRowsVerification.php' ) ){
        die( "Error al incluir clase warehouseProductProviderMovementsRowsVerification.php" );
    }
    $warehouseProductProviderMovementsRowsVerification = new warehouseProductProviderMovementsRowsVerification( $link );//instancia clase de comprobacion
    $petition_log = json_decode( json_encode( $request->getParam( 'log_response' ) ) );//recibe folio unico de la peticion
    $rows_response = json_decode( json_encode( $request->getParam( 'rows_response' ) ) );//recibe folio unico de la peticion
    //$verification = $request->getParam( 'verification' );
    //$origin_store = $request->getParam( 'origin_store' );
    if( $petition_log != null && $petition_log != '' ){
        $update_log = $warehouseProductProviderMovementsRowsVerification->updateLogAndJsonsRows( $petition_log, $rows_response );
        if( $update_log != 'ok' ){
          die( "Hubo un error : {$update_log}" );
        }
    }
    $resp['status'] = 200;
    $resp['message'] = "Comprobacion de movimientos almacen (proveedor producto) actualizada exitosamente en linea.";
    return json_encode( $resp );
});

?>
