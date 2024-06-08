<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_movimientos_almacen
* Path: /obtener_movimientos_almacen
* Método: POST
* Descripción: Recupera y envia los movimientos de almacen que no se han sincronizado ( local a linea )
*/
$app->get('/obtener_movimientos_almacen', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/movementsSynchronization.php' ) ){
    die( 'no se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//variables
  $req = [];
  $req["movements"] = array(); 
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos

//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $movements_limit = $config['rows_limit'];

/*Comprobacion de movimientos de almacen ( peticiones anteriores ) 2024*/
  if( !include( 'utils/warehouseMovementsRowsVerification.php' ) ){ 
    die( "No se pudo incluir la clase warehouseMovementsRowsVerification.php" );
  }
  $warehouseMovementsRowsVerification = new warehouseMovementsRowsVerification( $link );
  $verification = $warehouseMovementsRowsVerification->getPendingWarehouseMovement( $system_store, -1 );//obtiene los registros de comprobacion de movientos de almacen
  $verification['origin_store'] = $system_store;//id sucursal origen de verificacion
  $post_data = json_encode( $verification );//codifica validacion en JSON
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/valida_movimientos_almacen", $post_data );//envia peticion
  $resultado = json_decode( $result_1 );//procesa respuesta de comprobacion
  if( $resultado->log_response != null && $resultado->log_response != '' ){
    $update_log = $warehouseMovementsRowsVerification->updateLogAndJsonsRows( $resultado->log_response, $resultado->rows_response );
    if( $update_log != 'ok' ){
      die( "Hubo un error : {$update_log}" );
    }
  }
  //ejecuta la comprobacion de linea a local
  $verification_req = array();
  if( $resultado->rows_download != null && $resultado->rows_download != '' ){//echo 'here';
    $download = $resultado->rows_download;
    $petition_log = json_decode(json_encode($download->petition), true);//$array = json_decode(json_encode($object), true);
    $movements = json_decode(json_encode($download->rows), true);//$download->rows;
    if( $download->verification == true ){
      if( sizeof($petition_log) > 0 ){
        //var_dump( $resultado );
        $verification_req['log_response'] = $warehouseMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log );//consulta si la peticion existe en local
        $verification_req['rows_response'] = $warehouseMovementsRowsVerification->warehouseMovementsValidation( $movements );//realiza proceso de comprobacion
    
        $post_data = json_encode( $verification_req );
        $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_comprobacion_movimientos_almacen", $post_data );//consume servicio para actualizar la comprobacion en linea
      }
    }
  }
/*Fin de comprobacion de movimientos de almacen*/

  if( $system_store == -1 ){//valida que el origen no sea linea
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );//liberar el modulo de sincronizacion
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }

  
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN', 'sys_sincronizacion_movimientos_almacen' );//inserta log de request
  $setMovements = $movementsSynchronization->setNewSynchronizationMovements( $system_store, $system_store, $store_prefix, $movements_limit );//ejecuta el procedure para generar los movimientos de almacen
  if( $setMovements != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setMovements ) );
  }
  $req["movements"] = $movementsSynchronization->getSynchronizationMovements( -1, $movements_limit, 1, $req['log']['unique_folio'] );//consulta registros pendientes de sincronizar
  $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/inserta_movimientos_almacen", $post_data );//envia petición
  $result = json_decode( $result_1 );//decodifica respuesta
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime();
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
  if( $result->ok_rows != '' && $result->ok_rows != null ){
    $movementsSynchronization->updateMovementSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false );//actualiza registros exitosos
  }
  if( $result->error_rows != '' && $result->error_rows != null ){
    $movementsSynchronization->updateMovementSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false );//actualiza registros con erores
  }
  if( $result->log != '' && $result->log != null ){
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio );//actualiza respuesta en servidor local
  }

/**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);
    $log_download = json_decode(json_encode($result->log_download), true );
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time );//inserta response
    $insert_rows = $movementsSynchronization->insertMovements( $rows_download );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data );//envia petición
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "movementsSynchronization";
    //envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data );//envia petición

    }
  }
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql
  return 'ok';//regresa respuesta
});

?>
