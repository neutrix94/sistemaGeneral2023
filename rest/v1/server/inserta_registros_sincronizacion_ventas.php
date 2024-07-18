<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_registros_sincronizacion
* Path: /inserta_registros_sincronizacion
* Método: POST
* Descripción: Insercion de registros de sincronizacion
*/
$app->post('/inserta_registros_sincronizacion_ventas', function (Request $request, Response $response){
//incluye librerias
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/rowsSynchronization.php' ) ){
    die( 'No se incluyó libereria de sincronizacion de registros de sincronizacion : ' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/salesSynchronization.php' ) ){
    die( "No se incluyó : salesSynchronization.php" );
  }
  if( ! include( 'utils/verification/generalRowsVerification.php' ) ){
    die( "No se incluyó : generalRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables que llegan
  $rows = $request->getParam( "rows" );
  $log = $request->getParam( "log" );
  $VERIFICATION = $request->getParam( "verification" );
//verifica si el log esta habilitado
  $sql = "SELECT
            log_habilitado AS log_is_enabled
          FROM sys_configuraciones_logs  
          WHERE id_configuracion_log = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar si el log esta habilitado : {$sql} : {$this->link->error}" );
  $row = $stm->fetch_assoc();
  $LOGGER = ( $row['log_is_enabled'] == 1 ? true : false );

  if( $LOGGER ){//instancia clase de Logs
    $Logger = new Logger( $link );
  }
//instancia de clases
  $generalRowsVerification = new generalRowsVerification( $link, $Logger );//instancia clase de comprobacion
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $rowsSynchronization = new rowsSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos
  $salesSynchronization = new salesSynchronization( $link, $Logger );
  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( "{$log['unique_folio']}", 'sys_sincronizacion_registros_ventas', $log['origin_store'], -1 );//inserta el log de sincronizacion
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Llega peticion de local a Linea : ', "{$body}" );
  }
//variables de respuesta
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();//registros por descargar
  $resp["log_download"] = array();//log de registros por descargar
  $resp["status"] = "ok";

/*COMPROBACION 2024*/
  $petition_log = $VERIFICATION["petition"];//recibe folio unico de la peticion
  $verification = $VERIFICATION["verification"];
  $pending_rows_validation = $VERIFICATION["rows"];
  if( $verification == true ){
  //consulta si la peticion existe en linea
    $resp["rows_validation"]["log_response"] = $generalRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $resp["rows_validation"]["rows_response"] = $generalRowsVerification->RowsValidation( $pending_rows_validation, 'sys_sincronizacion_registros_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
  }
  $resp["rows_validation"]["rows_download"] = $generalRowsVerification->getPendingRows( -1, $log['origin_store'], 'sys_sincronizacion_registros_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta las comprobaciones pendientes de linea a local
/*FIN DE COMPROBACION 2024*/

  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked( $log['origin_store'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );/*valida que las apis no esten bloqueadas*/
  if( $validation != 'ok' ){
    $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    return $validation;
  }

  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 3, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza indicador de sincronizacion en tabla
  if( $update_synchronization != 'ok' ){
    return $update_synchronization;
  } 

/****************************************** Recibe / Inserta ******************************************/
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//obtiene hora actual
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta response
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( sizeof( $rows ) > 0 ){
    $insert_rows = $rowsSynchronization->insertRows( $rows, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      $resp["status"] = "error";
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta respuesta exitosa
    }
  }else{
    $response_string = "No llegaron registros de sincronizacion, posiblemente tengas que bajar el limite de registros de sincronizacion de ventas!";//inserta excepcion controlada
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'sys_sincronizacion_registros', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];

  $setPayments = $salesSynchronization->setNewSynchronizationPayments( $log['origin_store'], $system_store, $store_prefix, $rows_limit, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//ejecuta el procedure para generar registros de sincronizacion de pagos
  if( $setPayments != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setPayments ) );
  }
  $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, $log['origin_store'], $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION', 'sys_sincronizacion_registros_ventas' );
  $resp["rows_download"] = $rowsSynchronization->getSynchronizationRows( $system_store, $log['origin_store'], 
    $rows_limit, 'sys_sincronizacion_registros_ventas', $resp["log_download"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//obtiene registros para descargar
  /*if( sizeof( $resp["rows_download"] ) > 0 ){
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, $log['origin_store'], $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION', 'sys_sincronizacion_registros_ventas' );
  }*/
  
//desbloquea indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
  if( $LOGGER ){
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Respuesta de Linea a local : ', json_encode($resp) );
  }
  return json_encode( $resp );

});

?>
