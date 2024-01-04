<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_validaciones_ventas
* Path: /inserta_validaciones_ventas
* Método: POST
* Descripción: Insercion de validaciones de ventas
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

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $salesValidationSynchronization = new salesValidationSynchronization( $link );//instancia clase de sincronizacion de movimientos

  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["status"] = "ok";
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $validations = $request->getParam( "validations" );
  $log = $request->getParam( "log" );

  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked( $log['origin_store'] );/*valida que las apis no esten bloqueadas*/
  if( $validation != 'ok' ){
    $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
    return $validation;
  } 

  $update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 3 );//actualiza indicador de sincronizacion en tabla
  if( $update_synchronization != 'ok' ){
    return $update_synchronization;
  }

  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();//inserta request
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $validations ) > 0 ){
    $insert_validations = $salesValidationSynchronization->insertSalesValidation( $validations );
    if( $insert_validations["error"] != '' && $insert_validations["error"] != null  ){
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_validations["error"], $resp["log"]["unique_folio"] );//inserta error si es el caso
    }else{
      $resp["ok_rows"] = $insert_validations["ok_rows"];
      $resp["error_rows"] = $insert_validations["error_rows"];
      $tmp_ok = $insert_validations->tmp_ok;
      $tmp_no = $insert_validations->tmp_no;
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_validations["ok_rows"]} | {$insert_validations["error_rows"]}", $resp["log"]["unique_folio"] );//inserta respuesta exitosa
    }
  }else{//inserta excepcion controlada
    $response_string = "No llegaron validaciones de ventas, posiblemente tengas que bajar el limite de registros de sincronizacion de validaciones de ventas!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];

  if( $system_store != -1 ){//valida que el origen sea linea
    return json_encode( array( "response"=>"La sucursal es local y no puede ser servidor." ) );
  }

  $setMovements = $salesValidationSynchronization->setNewSynchronizationsalesValidation( $log['origin_store'], $system_store, $store_prefix, $rows_limit );//ejecuta el procedure para generar los movimientos proveedor producto
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }

  $resp["rows_download"] = $salesValidationSynchronization->getSynchronizationsalesValidation( $log['origin_store'], $rows_limit );//consulta registros pendientes de sincronizar
  if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $log['origin_store'], -1, $store_prefix, $initial_time, 'VALIDACION VENTAS DESDE LINEA' );
  }
  
//desbloquea indicador de sincronizacion en tabla
$update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
  return json_encode( $resp );
});

?>
