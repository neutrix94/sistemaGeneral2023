<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_ventas
* Path: /inserta_ventas
* Método: POST
* Descripción: Insercion de ventas
* Version 2.1 Comprobacion y LOG
*/
$app->post('/inserta_ventas', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/salesSynchronization.php' ) ){
    die( 'no se incluyó libereria de Ventas' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/verification/SalesRowsVerification.php' ) ){
    die( "No se incluyó : SalesRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
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
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $salesSynchronization = new salesSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos
  $SalesRowsVerification = new SalesRowsVerification( $link, $Logger );//instancia clase de sincronizacion de movimientos
/*valida que las apis no esten bloqueadas
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked();
  if( $validation != 'ok' ){
    return $validation;
  }*/

  $body = $request->getBody();
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();//registros por descargar
  $resp["log_download"] = array();//log de registros por descargar
  $resp["status"] = "ok";
  $resp["verification_sales"] = array();
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $sales = $request->getParam( "sales" );
  $log = $request->getParam( "log" );
  $VERIFICATION = $request->getParam( 'verification' );

  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( $log['unique_folio'], 'sys_sincronizacion_ventas', $log['origin_store'], -1 );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Llega petición de local a linea', "{$body}" );
  }
/*COMPROBACION 2024*/
    $petition_log = $VERIFICATION["petition"];//recibe folio unico de la peticion
    //var_dump( $petition_log );
    $verification = $VERIFICATION["verification"];
    //$origin_store = $VERIFICATION->getParam( 'origin_store' );
    $pending_sales = $VERIFICATION["rows"];
    if( $verification == true ){
    //consulta si la peticion existe en linea
        $resp["verification_sales"]["log_response"] = $SalesRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
        $resp["verification_sales"]["rows_response"] = $SalesRowsVerification->SalesValidation( $pending_sales, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
    }
    $resp["verification_sales"]["rows_download"] = $SalesRowsVerification->getPendingSales( -1, $log['origin_store'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta las comprobaciones pendientes de linea a local
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
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  //$pending_petitions = $request->getParam( "pending_responses" );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( sizeof( $sales ) > 0 || sizeof( $sales ) > 0 ){
    $insert_sales = $salesSynchronization->insertSales( $sales, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//return json_encode( $insert_sales );
    if( $insert_sales["error"] != '' && $insert_sales["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_sales["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      $resp["status"] = "error";// : {$insert_sales["error"]}
    }else{
      $resp["ok_rows"] = $insert_sales["ok_rows"];
      $resp["error_rows"] = $insert_sales["error_rows"];
      $tmp_ok = $insert_sales->tmp_ok;
      $tmp_no = $insert_sales->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_sales["ok_rows"]} | {$insert_sales["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron ventas, posiblemente tengas que bajar el limite de registros de sincronizacion de ventas!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
  /*$resp["rows_download"] = $salesSynchronization->getSynchronizationSales( $system_store, $log['origin_store'], $rows_limit );//obtiene regstros para descargar
  if( sizeof( $resp["rows_download"] ) > 0 ){
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, $log['origin_store'], $store_prefix, $initial_time, 'VENTAS DESDE LINEA' );
  }*/
//valida que el origen sea linea
  if( $system_store != -1 ){
    return json_encode( array( "response"=>"La sucursal es local y no puede ser servidor." ) );
  }
//ejecuta el procedure para generar los movimientos de almacen
  $setSales = $salesSynchronization->setNewSynchronizationSales( $log['origin_store'], $system_store, $store_prefix, $rows_limit, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $setSales != 'ok' ){
    return json_encode( array( "response" => $setSales ) );
  }

  $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'], $store_prefix, $initial_time, 'VENTAS DESDE LINEA', 'sys_sincronizacion_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $salesSynchronization->getSynchronizationSales( $log['origin_store'], $rows_limit, $resp["log_download"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//var_dump($req["movements"]);
//die( 'here' );
//return json_encode( $req["sales"] );
//Valida path
  //if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
  //}
  $SynchronizationManagmentLog->updateModuleResume( 'ec_pedidos', 'subida', $resp["status"], $log["origin_store"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//actualiza el resumen de modulo/sucursal ( subida )
  
//desbloquea indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $LOGGER ){
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Respuesta de Linea a local : ', json_encode($resp) );
  }
  return json_encode( $resp );

});

?>
