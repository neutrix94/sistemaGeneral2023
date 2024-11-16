<?php
    use \Psr\Http\Message\ResponseInterface as Response;
    use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: actualiza_comprobacion_registros
* Path: /actualiza_comprobacion_registros
* Método: POST
* Descripción: Comprobacion de registros de sincronizacion
*/
    $app->post('/actualiza_comprobacion_registros', function (Request $request, Response $response){  
        $resp = array();
        if ( ! include( '../../conexionMysqli.php' ) ){
            die( 'no se incluyó conexion' );
        }
        if( ! include( 'utils/verification/generalRowsVerification.php' ) ){
            die( "Error al incluir clase generalRowsVerification.php" );
        }
        $generalRowsVerification = new generalRowsVerification( $link );//instancia clase de comprobacion
        $petition_log = json_decode( json_encode( $request->getParam( 'log_response' ) ) );//recibe folio unico de la peticion
        $rows_response = json_decode( json_encode( $request->getParam( 'rows_response' ) ) );//recibe folio unico de la peticion
        $table_name = $request->getParam( 'table_name' );//recibe folio unico de la peticion
        //$verification = $request->getParam( 'verification' );
        //$origin_store = $request->getParam( 'origin_store' );
        if( $petition_log != null && $petition_log != '' ){
            $update_log = $generalRowsVerification->updateLogAndJsonsRows( $petition_log, $rows_response, $table_name );
            if( $update_log != 'ok' ){
            die( "Hubo un error : {$update_log}" );
            }
        }
        $resp['status'] = 200;
        $resp['message'] = "Comprobacion de {$table_name} actualizada exitosamente en linea.";
        return json_encode( $resp );
    });

?>
