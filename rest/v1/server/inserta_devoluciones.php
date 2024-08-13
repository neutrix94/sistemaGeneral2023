<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_devoluciones
* Path: /inserta_devoluciones
* Método: GET
* Descripción: Insercion de devoluciones
* Version 2.1 Comprobacion y LOG
*/
$app->post('/inserta_devoluciones', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/returnsSynchronization.php' ) ){
    die( 'No se incluyó libereria de Devoluciones' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/verification/returnRowsVerification.php' ) ){
    die( "No se incluyó : returnRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  
  $Logger = false;
  $LOGGER = false;
//variables que llegan
  $body = $request->getBody();
  $returns = $request->getParam( "returns" );
  $log = $request->getParam( "log" );
  $VERIFICATION = $request->getParam( "verification" );
//consulta si el log esta habilitado
  $sql = "SELECT
    log_habilitado AS log_is_enabled
  FROM sys_configuraciones_logs  
  WHERE id_configuracion_log = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar si el log esta habilitado : {$sql} : {$this->link->error}" );
  $row = $stm->fetch_assoc();
  $LOGGER = ( $row['log_is_enabled'] == 1 ? true : false );

  if( $LOGGER ){
    $Logger = new Logger( $link );//instancia clase de Logs
  }
  $returnRowsVerification = new returnRowsVerification( $link, $Logger );//instancia clase de sincronizacion de movimientos
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $returnsSynchronization = new returnsSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos
  
  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( "{$log['unique_folio']}", 'sys_sincronizacion_devoluciones', $log['origin_store'], -1 );//inserta el log de sincronizacion
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Llega peticion de local a Linea : ', "{$body}" );
  }
//variables de respuesta
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();
  $resp["log_download"] = array();
  $resp["status"] = "ok";

  $tmp_ok = "";
  $tmp_no = "";

//

/*COMPROBACION 2024*/
  $petition_log = $VERIFICATION["petition"];//recibe folio unico de la peticion
  //var_dump( $petition_log );
  $verification = $VERIFICATION["verification"];
  //$origin_store = $VERIFICATION->getParam( 'origin_store' );
  $pending_returns_validation = $VERIFICATION["rows"];
  if( $verification == true ){
  //consulta si la peticion existe en linea
    $resp["returns_validation"]["log_response"] = $returnRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $resp["returns_validation"]["rows_response"] = $returnRowsVerification->returnsValidation( $pending_returns_validation, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
  }
  $resp["returns_validation"]["rows_download"] = $returnRowsVerification->getPendingValidationReturns( -1, $log['origin_store'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta las comprobaciones pendientes de linea a local
/*FIN DE COMPROBACION 2024*/

/*valida que las apis no esten bloqueadas*/
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked( $log['origin_store'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $validation != 'ok' ){
    $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    return $validation;
  } 
//actualiza indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 3, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $update_synchronization != 'ok' ){
    return $update_synchronization;
  } 
/**/



//inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  //$pending_petitions = $request->getParam( "pending_responses" );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( sizeof( $returns ) > 0 ){
    $insert_returns = $returnsSynchronization->insertReturns( $returns, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//return json_encode( $insert_returns );
    if( $insert_returns["error"] != '' && $insert_returns["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_returns["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      $resp["status"] = "error : {$insert_returns["error"]}";
    }else{
      $resp["ok_rows"] = $insert_returns["ok_rows"];
      $resp["error_rows"] = $insert_returns["error_rows"];
      $tmp_ok = $insert_returns->tmp_ok;
      $tmp_no = $insert_returns->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_returns["ok_rows"]} | {$insert_returns["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron devoluciones, posiblemente tengas que bajar el limite de registros de sincronizacion de devoluciones!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
  /****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_devolucion', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
//valida que el origen sea linea
  if( $system_store != -1 ){
    return json_encode( array( "response"=>"La sucursal es local y no puede ser servidor." ) );
  }
//ejecuta el procedure para generar los movimientos de almacen
  $setMovements = $returnsSynchronization->setNewSynchronizationReturns( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  //die( "detenido por prueba sincronizacion" );
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }
//consulta registros pendientes de sincronizar
  /*if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'], $store_prefix, $initial_time, 'DEVOLUCIONES DESDE LINEA', 'sys_sincronizacion_devoluciones', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }*/
  $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'], $store_prefix, $initial_time, 'DEVOLUCIONES DESDE LINEA', 'sys_sincronizacion_devoluciones', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $resp["rows_download"] = $returnsSynchronization->getSynchronizationReturns( $log['origin_store'], $rows_limit, $resp["log_download"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  
  $SynchronizationManagmentLog->updateModuleResume( 'ec_devolucion', 'subida', $resp["status"], $log["origin_store"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza el resumen de modulo/sucursal ( subida )
  
//desbloquea indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $LOGGER ){
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Respuesta de Linea a local : ', json_encode($resp) );
  }
  return json_encode( $resp );

});

?>
