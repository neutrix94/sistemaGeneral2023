<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_comprobacion_devoluciones
* Path: /actualiza_comprobacion_devoluciones
* Método: POST
* Descripción: Comprobacion de devoluciones
*/
$app->post('/actualiza_comprobacion_devoluciones', function (Request $request, Response $response){  
    $resp = array();
    if ( ! include( '../../conexionMysqli.php' ) ){
        die( 'no se incluyó conexion' );
    }
    if( ! include( 'utils/verification/returnRowsVerification.php' ) ){
        die( "Error al incluir clase returnRowsVerification.php" );
    }
    $returnRowsVerification = new returnRowsVerification( $link );//instancia clase de comprobacion
    $petition_log = json_decode( json_encode( $request->getParam( 'log_response' ) ) );//recibe folio unico de la peticion
    $rows_response = json_decode( json_encode( $request->getParam( 'rows_response' ) ) );//recibe folio unico de la peticion
    if( $petition_log != null && $petition_log != '' ){
        $update_log = $returnRowsVerification->updateLogAndJsonsRows( $petition_log, $rows_response );
        if( $update_log != 'ok' ){
          die( "Hubo un error : {$update_log}" );
        }
    }
    $resp['status'] = 200;
    $resp['message'] = "Comprobacion de devoluciones actualizada exitosamente en linea.";
    return json_encode( $resp );
});

?>
