<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_validaciones_ventas
* Path: /inserta_validaciones_ventas
* Método: POST
* Descripción: Insercion de validaciones de ventas
* Version 2.1 Comprobacion y LOG
*/
$app->post('/inserta_validaciones_ventas', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/salesValidationSynchronization.php' ) ){
    die( 'No se incluyó libereria de Validaciones de venta' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/verification/SalesValidationRowsVerification.php' ) ){
    die( "No se incluyó : SalesValidationRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables
  $body = $request->getBody();
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["status"] = "ok";
  
  $tmp_ok = "";
  $tmp_no = "";

  $validations = $request->getParam( "validations" );
  $log = $request->getParam( "log" );
  $VERIFICATION = $request->getParam( "verification" );
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
  $SalesValidationRowsVerification = new SalesValidationRowsVerification( $link, $Logger );//instancia clase de verificacion de validacion de ventas
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $salesValidationSynchronization = new salesValidationSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos

  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( "{$log['unique_folio']}", 'sys_sincronizacion_validacion_ventas', $log['origin_store'], -1 );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Llega peticion de local a Linea : ', "{$body}" );
  }

/*COMPROBACION 2024*/
  $petition_log = $VERIFICATION["petition"];//recibe folio unico de la peticion
  //var_dump( $petition_log );
  $verification = $VERIFICATION["verification"];
  //$origin_store = $VERIFICATION->getParam( 'origin_store' );
  $pending_sales_validation = $VERIFICATION["rows"];
  if( $verification == true ){
  //consulta si la peticion existe en linea
    $resp["sales_validation"]["log_response"] = $SalesValidationRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $resp["sales_validation"]["rows_response"] = $SalesValidationRowsVerification->validationSalesValidation( $pending_sales_validation, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
  }
  $resp["sales_validation"]["rows_download"] = $SalesValidationRowsVerification->getPendingValidationSales( -1, $log['origin_store'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta las comprobaciones pendientes de linea a local
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

  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta request
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $validations ) > 0 ){
    $insert_validations = $salesValidationSynchronization->insertSalesValidation( $validations, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $insert_validations["error"] != '' && $insert_validations["error"] != null  ){
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_validations["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta error si es el caso
    }else{
      $resp["ok_rows"] = $insert_validations["ok_rows"];
      $resp["error_rows"] = $insert_validations["error_rows"];
      $tmp_ok = $insert_validations->tmp_ok;
      $tmp_no = $insert_validations->tmp_no;
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_validations["ok_rows"]} | {$insert_validations["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta respuesta exitosa
    }
  }else{//inserta excepcion controlada
    $response_string = "No llegaron validaciones de ventas, posiblemente tengas que bajar el limite de registros de sincronizacion de validaciones de ventas!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];

  if( $system_store != -1 ){//valida que el origen sea linea
    return json_encode( array( "response"=>"La sucursal es local y no puede ser servidor." ) );
  }

  $setMovements = $salesValidationSynchronization->setNewSynchronizationsalesValidation( $log['origin_store'], -1,  $store_prefix, $rows_limit, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//ejecuta el procedure para generar los movimientos proveedor producto
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }
  $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'], $store_prefix, $initial_time, 'VALIDACION VENTAS DESDE LINEA', 'sys_sincronizacion_validaciones_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  $resp["rows_download"] = $salesValidationSynchronization->getSynchronizationsalesValidation( $log['origin_store'], $rows_limit, $resp["log_download"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta registros pendientes de sincronizar
  /*if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( -1, $log['origin_store'], $store_prefix, $initial_time, 'VALIDACION VENTAS DESDE LINEA', 'sys_sincronizacion_validaciones_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }*/
  
//desbloquea indicador de sincronizacion en tabla
  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $LOGGER ){
    $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Respuesta de Linea a local : ', json_encode($resp) );
  }
  return json_encode( $resp );
});

?>
