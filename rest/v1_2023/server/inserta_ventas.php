<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_ventas
* Path: /inserta_ventas
* Método: GET
* Descripción: Insercion de ventas
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

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $salesSynchronization = new salesSynchronization( $link );//instancia clase de sincronizacion de movimientos
/*valida que las apis no esten bloqueadas
  $validation = $SynchronizationManagmentLog->validate_apis_are_not_locked();
  if( $validation != 'ok' ){
    return $validation;
  }*/

  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();//registros por descargar
  $resp["log_download"] = array();//log de registros por descargar
  $resp["status"] = "ok";
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $sales = $request->getParam( "sales" );
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
  if( sizeof( $sales ) > 0 || sizeof( $sales ) > 0 ){
    $insert_sales = $salesSynchronization->insertSales( $sales );
//return json_encode( $insert_sales );
    if( $insert_sales["error"] != '' && $insert_sales["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_sales["error"], $resp["log"]["unique_folio"] );
      $resp["status"] = "error";// : {$insert_sales["error"]}
    }else{
      $resp["ok_rows"] = $insert_sales["ok_rows"];
      $resp["error_rows"] = $insert_sales["error_rows"];
      $tmp_ok = $insert_sales->tmp_ok;
      $tmp_no = $insert_sales->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_sales["ok_rows"]} | {$insert_sales["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron ventas, posiblemente tengas que bajar el limite de registros de sincronizacion de ventas!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos' );
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
  $setMovements = $salesSynchronization->setNewSynchronizationSales( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }

//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $salesSynchronization->getSynchronizationSales( $log['origin_store'], $rows_limit );
//var_dump($req["movements"]);
//die( 'here' );
//return json_encode( $req["sales"] );
//Valida path
  if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $log['origin_store'], -1, $store_prefix, $initial_time, 'VENTAS DESDE LINEA' );
  }
  $SynchronizationManagmentLog->updateModuleResume( 'ec_pedidos', 'subida', $resp["status"], $log["origin_store"] );//actualiza el resumen de modulo/sucursal ( subida )
  
//desbloquea indicador de sincronizacion en tabla
$update_synchronization = $SynchronizationManagmentLog->updateSynchronizationStatus( $log['origin_store'], 2 );
  return json_encode( $resp );

});

?>
