<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_peticion
* Path: /actualiza_peticion
* Método: POST
* Descripción: Actualizacion de peticion de servidor a cliente
*/
$app->post('/actualiza_peticion', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
/*valida que las apis no esten bloqueadas
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked();
  if( $validation != 'ok' ){
    return $validation;
  }
  */
  $resp = array();
  $local_log = $request->getParam( "local_response_log" );
  $log = $request->getParam( "log" );

  $ok_rows = $request->getParam( "ok_rows" );
  $table = $request->getParam( "table" );
  $status = $request->getParam( "status" );
  //$resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $log["response_string"], $log["unique_folio"] );
  if( $ok_rows != "" && $ok_rows != null ){
    switch ( $log["type_update"] ) {

      case 'rowsSynchronization':
        if ( ! include( 'utils/rowsSynchronization.php' ) ){
          die( 'No se incluyó libereria de registros de sincronizacion : ' );
        }
        $rowsSynchronization = new rowsSynchronization( $link );//instancia clase de sincronizacion de movimientos
        $rowsSynchronization->updateRowSynchronization( $ok_rows, $log["unique_folio"], $table, 3, false );
      break;
      
      case 'salesSynchronization' :
        if ( ! include( 'utils/salesSynchronization.php' ) ){
          die( 'No se incluyó libereria de sincronizacion de ventas : ' );
        }
        $salesSynchronization = new salesSynchronization( $link );//instancia clase de sincronizacion de movimientos
        $salesSynchronization->updateSaleSynchronization( $ok_rows, $log["unique_folio"], 3, false );

      break;

      case 'returnsSynchronization' :
        if ( ! include( 'utils/returnsSynchronization.php' ) ){
          die( 'No se incluyó libereria de sincronizacion de devoluciones : ' );
        }
        $returnsSynchronization = new returnsSynchronization( $link );//instancia clase de sincronizacion de movimientos
        $returnsSynchronization->updateReturnSynchronization( $ok_rows, $log["unique_folio"], 3, false );
      break;

      case 'movementsSynchronization' :
        if ( ! include( 'utils/movementsSynchronization.php' ) ){
          die( 'No se incluyó libereria de sincronizacion de movimientos de almacen : ' );
        }
        $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
        $movementsSynchronization->updateMovementSynchronization( $ok_rows, $log["unique_folio"], 3, false );
        $movementsSynchronization->updateMovementSynchronization( $ok_rows, $log["unique_folio"], null, true );
      break;

      case 'salesValidationSynchronization':
        if ( ! include( 'utils/salesValidationSynchronization.php' ) ){
          die( 'No se incluyó libereria de sincronizacion de validacion de venta : ' );
        }
          $salesValidationSynchronization = new salesValidationSynchronization( $link );//instancia clase de sincronizacion de movimientos
          $salesValidationSynchronization->updateSalesValidationSynchronization( $ok_rows, $log["unique_folio"], 3 );
      break;

      case 'productProviderMovementsSynchronization':
        if ( ! include( 'utils/productProviderMovementsSynchronization.php' ) ){
          die( 'No se incluyó libereria de sincronizacion de validacion de movimientos proveedor producto : ' );
        }
          $productProviderMovementsSynchronization = new productProviderMovementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
          $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $ok_rows, $log["unique_folio"], 3 );
      break;
      
      default:
        die( 'Permission denied while trying update Petition on ' . $log["type_update"] . '.' );
      break;
    }
    $SynchronizationManagmentLog->updateModuleResume( $table, 'bajada', $download_status, $log["destinity_store"] );//actualiza el resumen de modulo/sucursal 
  }
 //actualiza el registro de peticion de local a linea
  if( $local_log != '' && $local_log != null ){
    $resp["log"] = $SynchronizationManagmentLog->updatePetitionLog( $local_log['destinity_time'], $local_log['response_time'], $local_log['response_string'], $local_log['unique_folio'] );
  }
  //actualiza el registro de peticion de linea a local
  if( $log != '' && $log != null ){
    $resp["log"] = $SynchronizationManagmentLog->updatePetitionLog( $log['destinity_time'], $log['response_time'], $log['response_string'], $log['unique_folio'] );
  }
  return json_encode( $resp );

});

?>
