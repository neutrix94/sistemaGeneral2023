<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_movimientos_proveedor_producto
* Path: /inserta_movimientos_proveedor_producto
* Método: POST
* Descripción: Insercion de movimientos proveedor producto
*/
$app->post('/inserta_movimientos_proveedor_producto', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/productProviderMovementsSynchronization.php' ) ){
    die( 'No se incluyó libereria de Proveedores producto' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $productProviderMovementsSynchronization = new productProviderMovementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
/*valida que las apis no esten bloqueadas
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked();
  if( $validation != 'ok' ){
    return $validation;
  }*/

  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["log"] = array();
  $resp["status"] = "ok";
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $product_provider_movements = $request->getParam( "product_provider_movements" );
  $log = $request->getParam( "log" );



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
  if( sizeof( $product_provider_movements ) > 0 ){
    $insert_validations = $productProviderMovementsSynchronization->insertProductProviderMovements( $product_provider_movements );
//return json_encode( $insert_validations );
    if( $insert_validations["error"] != '' && $insert_validations["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_validations["error"], $resp["log"]["unique_folio"] );
      $resp["status"] = "error : {$insert_validations["error"]}";
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
    $response_string = "No llegaron movimientos proveedor producto, posiblemente tengas que bajar el limite de registros de sincronizacion de movimientos proveedor producto!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_detalle_proveedor_producto' );
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
  $setProductProviderMovements = $productProviderMovementsSynchronization->setNewSynchronizationProductProviderMovements( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  if( $setProductProviderMovements != 'ok' ){
    return json_encode( array( "response" => $setProductProviderMovements ) );
  }
//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $productProviderMovementsSynchronization->getSynchronizationProductProviderMovements( $log['origin_store'], $rows_limit );
  if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $log['origin_store'], -1, $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN DESDE LINEA' );
  }

  $SynchronizationManagmentLog->updateModuleResume( 'ec_movimiento_detalle_proveedor_producto', 'subida', $resp["status"], $log["origin_store"] );//actualiza el resumen de modulo/sucursal ( subida )
  
//desbloquea indicador de sincronizacion en tabla
$update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
  return json_encode( $resp );

});

?>
