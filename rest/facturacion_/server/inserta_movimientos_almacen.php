<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_movimientos_almacen
* Path: /inserta_movimientos_almacen
* Método: GET
* Descripción: Insercion de movimeintos de almacen a nivel producto
*/
$app->post('/inserta_movimientos_almacen', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/movementsSynchronization.php' ) ){
    die( 'no se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
  
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  
  $tmp_ok = "";
  $tmp_no = "";

//
  $movements = $request->getParam( "movements" );
  $log = $request->getParam( "log" );
//inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  //$pending_petitions = $request->getParam( "pending_responses" );
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $movements ) > 0 ){
    $insert_movements = $movementsSynchronization->insertMovements( $movements );
//return json_encode( $insert_movements );
    if( $insert_movements["error"] != '' && $insert_movements["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_movements["error"], $resp["log"]["unique_folio"] );
    }else{
      $resp["ok_rows"] = $insert_movements["ok_rows"];
      $resp["error_rows"] = $insert_movements["error_rows"];
      $tmp_ok = $insert_movements->tmp_ok;
      $tmp_no = $insert_movements->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_movements["ok_rows"]} | {$insert_movements["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron movimientos de almacen para sumar al inventario, posiblemente tengas que bajar el limite de registros de sincronizacion de movimientos de almacen!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }
  /****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
//valida que el origen sea linea
  if( $system_store != -1 ){
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
//ejecuta el procedure para generar los movimientos de almacen
  $setMovements = $movementsSynchronization->setNewSynchronizationMovements( $log['origin_store'], $system_store, $store_prefix, $rows_limit );
  if( $setMovements != 'ok' ){
    return json_encode( array( "response" => $setMovements ) );
  }
//consulta registros pendientes de sincronizar
  $resp["rows_download"] = $movementsSynchronization->getSynchronizationMovements( $log['origin_store'], $rows_limit );
  if ( sizeof( $resp["rows_download"] ) > 0 ) {//inserta request
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $log['origin_store'], -1, $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN DESDE LINEA' );
  }
  return json_encode( $resp );

});

?>
