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
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $validations = $request->getParam( "validations" );
  $log = $request->getParam( "log" );
//inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  //$pending_petitions = $request->getParam( "pending_responses" );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $validations ) > 0 ){
    $insert_validations = $salesValidationSynchronization->insertSalesValidation( $validations );
//return json_encode( $insert_validations );
    if( $insert_validations["error"] != '' && $insert_validations["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_validations["error"], $resp["log"]["unique_folio"] );
    }else{
      $resp["ok_rows"] = $insert_validations["ok_rows"];
      $resp["error_rows"] = $insert_validations["error_rows"];
      $tmp_ok = $insert_validations->tmp_ok;
      $tmp_no = $insert_validations->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_validations["ok_rows"]} | {$insert_validations["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
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
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
//ejecuta el procedure para generar los movimientos proveedor producto
  $setMovements = $salesValidationSynchronization->setNewSynchronizationsalesValidation( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }
//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $salesValidationSynchronization->getSynchronizationsalesValidation( $log['origin_store'], $rows_limit );

  if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $log['origin_store'], -1, $store_prefix, $initial_time, 'VALIDACION VENTAS DESDE LINEA' );
  }
  return json_encode( $resp );

});

?>
