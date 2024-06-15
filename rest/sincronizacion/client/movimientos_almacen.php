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
  if( !include( 'utils/warehouseMovementsRowsVerification.php' ) ){
    die( "No se pudo incluir la clase warehouseMovementsRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables
  $req = [];
  $req["movements"] = array(); 
  $result = "";
  $sql = "SELECT
              log_habilitado AS log_is_enabled
      FROM sys_configuraciones_logs  
      WHERE id_configuracion_log = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar si el log esta habilitado : {$sql} : {$this->link->error}" );
  $row = $stm->fetch_assoc();
  $LOGGER = ( $row['log_is_enabled'] == 1 ? true : false );
  //return $row['log_is_enabled'];
  if( $LOGGER ){
    $Logger = new Logger( $link );//instancia clase de Logs
  }
  //die( "LOGGER : {$LOGGER}" );
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $warehouseMovementsRowsVerification = new warehouseMovementsRowsVerification( $link, $Logger );//instancia de clase de comprobacion de movimientos de almacen
  $movementsSynchronization = new movementsSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos
//die('ok2');
//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $movements_limit = $config['rows_limit'];

  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( '', 'sys_sincronizacion_movimientos_almacen', $system_store, -1 );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Se consulta la configuracion de la sucursal y modulo', $config['logger_sql'] );
  }
//var_dump( $LOGGER );return '';
  if( $system_store == -1 ){//valida que el origen no sea linea
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen', 
      ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
//die( $LOGGER['id_sincronizacion'] );
/*Comprobacion de movimientos de almacen ( peticiones anteriores ) 2024*/
  $req['verification'] = $warehouseMovementsRowsVerification->getPendingWarehouseMovement( $system_store, -1, 
    ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//obtiene los registros de comprobacion de movientos de almacen
  //$verification['origin_store'] = $system_store;//id sucursal origen de verificacion
  //var_dump( $req );
  /*$post_data = json_encode( $verification );//codifica validacion en JSON
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/valida_movimientos_almacen", $post_data );//envia peticion
  $resultado = json_decode( $result_1 );//procesa respuesta de comprobacion
  if( $resultado->log_response != null && $resultado->log_response != '' ){
    $update_log = $warehouseMovementsRowsVerification->updateLogAndJsonsRows( $resultado->log_response, $resultado->rows_response );
    if( $update_log != 'ok' ){
      die( "Hubo un error : {$update_log}" );
    }
  }*/
/*ejecuta la comprobacion de linea a local
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
  }*/
/*Fin de comprobacion de movimientos de almacen*/

  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN', 
    'sys_sincronizacion_movimientos_almacen', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta log de request
  $setMovements = $movementsSynchronization->setNewSynchronizationMovements( $system_store, $system_store, $store_prefix, $movements_limit, 
    ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//ejecuta el procedure para generar los movimientos de almacen
  if( $setMovements != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setMovements ) );
  }
  $req["movements"] = $movementsSynchronization->getSynchronizationMovements( -1, $movements_limit, 1, $req['log']['unique_folio'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta registros pendientes de sincronizar
  $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion
  //return $post_data;
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/inserta_movimientos_almacen", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
  //return( $result_1 );
  //return '';
  $result = json_decode( $result_1 );//decodifica respuesta
  //return $result;
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
  $response_time = $result->log->response_time;
  //die( "response_time : {$response_time}" );
/*Respuesta de comprobacion*/
  if( $result->verification_movements->log_response != null && $result->verification_movements->log_response != '' ){
    //die( "here 1" );
    $update_log = $warehouseMovementsRowsVerification->updateLogAndJsonsRows( $resultado->verification_movements->log_response, $resultado->verification_movements->rows_response, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $update_log != 'ok' ){
      die( "Hubo un error : {$update_log}" );
    }
  }
  $verification_req = array();
  if( $result->verification_movements->rows_download != null && $result->verification_movements->rows_download != '' ){//echo 'here';
    
  //die( "here 3" );
    $download = $result->verification_movements->rows_download;
    $petition_log = json_decode(json_encode($download->petition), true);//$array = json_decode(json_encode($object), true);
    
    $movements = json_decode(json_encode($download->rows), true);//$download->rows;
    //var_dump($movements);die('herre2');
    if( $download->verification == true ){
      if( sizeof($petition_log) > 0 ){
        //var_dump( $resultado );
        $verification_req['log_response'] = $warehouseMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta si la peticion existe en local
        $verification_req['rows_response'] = $warehouseMovementsRowsVerification->warehouseMovementsValidation( $movements, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
    
        $post_data = json_encode( $verification_req );
        $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_comprobacion_movimientos_almacen", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consume servicio para actualizar la comprobacion en linea
      }
    }
  }
  //die( "here 2" );
/*Fin de Respuesta de Comprobacion*/
  $local_response_log = array();
  if( $result->ok_rows != '' && $result->ok_rows != null ){
    $local_response_log = $movementsSynchronization->updateMovementSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza registros exitosos
  }
  if( $result->error_rows != '' && $result->error_rows != null ){
    $local_response_log = $movementsSynchronization->updateMovementSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza registros con erores
  }
  if( $result->log != '' && $result->log != null ){
    $local_response_log = $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza respuesta en servidor local
  }
/**************************************************Inserta lo que viene de linea**************************************************/
  $rows_download = json_decode(json_encode($result->rows_download), true);
  $log_download = json_decode(json_encode($result->log_download), true );
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta response  
  $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );  
  $resp["ok_rows"] = "";
  $resp["error_rows"] = "";

  if( $result->rows_download != '' && $result->rows_download != null ){
    $insert_rows = $movementsSynchronization->insertMovements( $rows_download, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
     // $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      //$result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data );//envia petición
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      
    //envia peticion para actualiza log de registros descargados

    }
  }
  $resp["log"]["type_update"] = "movementsSynchronization";
  $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );    
  $resp["log"]["destinity_time"] = $response_time;
  
  $post_data = json_encode(array( "log"=>$resp["log"], 
      "ok_rows"=>$insert_rows["ok_rows"], 
      "error_rows"=>$insert_rows["error_rows"],
      "local_response_log"=>$local_response_log
    ), JSON_PRETTY_PRINT);//forma peticion
//return $post_data;
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
 
//var_dump( $result_1 );
//die( "here : " );
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql
  return 'ok';//regresa respuesta
});

?>
