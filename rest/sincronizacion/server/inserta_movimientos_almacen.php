<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_movimientos_almacen
* Path: /inserta_movimientos_almacen
* Método: GET
* Descripción: Insercion de movimeintos de almacen a nivel producto
*/
$app->post('/inserta_movimientos_almacen', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/movementsSynchronization.php' ) ){
    die( 'no se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/warehouseMovementsRowsVerification.php' ) ){
    die( "No se incluyó : warehouseMovementsRowsVerification.php" );
  }
  $warehouseMovementsRowsVerification = new warehouseMovementsRowsVerification( $link );
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
/*valida que las apis no esten bloqueadas
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked();
  if( $validation != 'ok' ){
    return $validation;
  }*/

  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["status"] = "ok";
  $resp["verification_movements"] = array();
  
  $tmp_ok = "";
  $tmp_no = "";
//RECIBE VARIABLES DE ENTRADA
  $log = $request->getParam( "log" );
  $VERIFICATION = $request->getParam( 'verification' );
  $movements = $request->getParam( "movements" );
 /* var_dump( $movements );
  return '';*/
//
/*COMPROBACION 2024*/
  $petition_log = $VERIFICATION["petition"];//recibe folio unico de la peticion
  //var_dump( $petition_log );
  $verification = $VERIFICATION["verification"];
  //$origin_store = $VERIFICATION->getParam( 'origin_store' );
  $pending_movements = $VERIFICATION["rows"];
  if( $verification == true ){
  //consulta si la peticion existe en linea
      $resp["verification_movements"]["log_response"] = $warehouseMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log );
      $resp["verification_movements"]["rows_response"] = $warehouseMovementsRowsVerification->warehouseMovementsValidation( $pending_movements );//realiza proceso de comprobacion
  }
  $resp["verification_movements"]["rows_download"] = $warehouseMovementsRowsVerification->getPendingWarehouseMovement( -1, $log['origin_store'] );//consulta las comprobaciones pendientes de linea a local
  //var_dump( $resp );
  //return json_encode( $resp );
/*Comprobacion*/
  /*$petition_log = json_decode( json_encode( $request->getParam( 'log_response' ) ) );//recibe folio unico de la peticion
  $rows_response = json_decode( json_encode( $request->getParam( 'rows_response' ) ) );//recibe folio unico de la peticion
  //$verification = $request->getParam( 'verification' );
  //$origin_store = $request->getParam( 'origin_store' );
  if( $petition_log != null && $petition_log != '' ){
      $update_log = $warehouseMovementsRowsVerification->updateLogAndJsonsRows( $petition_log, $rows_response );
      if( $update_log != 'ok' ){
        die( "Hubo un error : {$update_log}" );
      }
  }*/
/*fin de la comprobacion*/
  //$resp['status'] = 200;
  //$resp['message'] = "Comprobacion de movimientos almacen (producto) actualizada exitosamente en linea.";

/*valida que las apis no esten bloqueadas*/
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked( $log['origin_store'] );
  if( $validation != 'ok' ){
    $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
    return $validation;
  } 
//actualiza indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 3 );
  if( $update_synchronization != 'ok' ){
    return $update_synchronization;
  } 
/**/



//inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  //$pending_petitions = $request->getParam( "pending_responses" );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $movements ) > 0 ){
    $insert_movements = $movementsSynchronization->insertMovements( $movements );
//return json_encode( $insert_movements );
    if( $insert_movements["error"] != '' && $insert_movements["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_movements["error"], $resp["log"]["unique_folio"] );
      $resp["status"] = "error : {$insert_movements["error"]}";
    }else{
      $resp["ok_rows"] = $insert_movements["ok_rows"];
      $resp["error_rows"] = $insert_movements["error_rows"];
      $tmp_ok = $insert_movements->tmp_ok;
      $tmp_no = $insert_movements->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_movements["ok_rows"]} | {$insert_movements["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron movimientos de almacen para sumar al inventario, posiblemente tengas que bajar el limite de registros de sincronizacion de movimientos de almacen!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }
  /****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
//valida que el origen sea linea
  if( $system_store != -1 ){
    return json_encode( array( "response"=>"La sucursal es local y no puede ser servidor." ) );
  }
  $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'],  $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN DESDE LINEA', 'sys_sincronizacion_movimientos_almacen' );
//ejecuta el procedure para generar los movimientos de almacen
  $setMovements = $movementsSynchronization->setNewSynchronizationMovements( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }
//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $movementsSynchronization->getSynchronizationMovements( $log['origin_store'], $rows_limit, 1, $resp["log_download"]["unique_folio"] );
  $SynchronizationManagmentLog->updateModuleResume( 'ec_movimiento_almacen', 'subida', $resp["status"], $log["origin_store"] );//actualiza el resumen de modulo/sucursal ( subida )
  
//desbloquea indicador de sincronizacion en tabla
$update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
  return json_encode( $resp );

});

?>
