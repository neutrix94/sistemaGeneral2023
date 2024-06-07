<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_comprobacion_movimientos_almacen
* Path: /actualiza_comprobacion_movimientos_almacen
* Método: POST
* Descripción: Insercion de movimeintos de almacen a nivel producto
*/
$app->post('/actualiza_comprobacion_movimientos_almacen', function (Request $request, Response $response){  
    $resp = array();
    $resp['rows_response'] = array();
    $resp['rows_download'] = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/RowsVerification.php' ) ){
        die( "Error al incluir clase RowsVerification.php" );
    }
    $RowsVerification = new RowsVerification( $link );//instancia clase de comprobacion
    $petition_log = json_decode( json_encode( $request->getParam( 'log_response' ) ) );//recibe folio unico de la peticion
    $rows_response = json_decode( json_encode( $request->getParam( 'rows_response' ) ) );//recibe folio unico de la peticion
    //$verification = $request->getParam( 'verification' );
    //$origin_store = $request->getParam( 'origin_store' );
    if( $log_response != null && $log_response != '' ){
        $update_log = $RowsVerification->updateLogAndJsonsRows( $log_response, $rows_response );
        if( $update_log != 'ok' ){
          die( "Hubo un error : {$update_log}" );
        }
    }
    $resp['status'] = 200;
    $resp['message'] = "Comprobacion actualizada exitosamente en linea.";
    return json_encode( $resp );
});

?>
