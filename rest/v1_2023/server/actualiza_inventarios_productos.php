<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_inventarios_productos
* Path: /actualiza_inventarios_productos
* Método: GET
* Descripción: Actualizacion de inventarios a nivel producto
*/
$app->post('/actualiza_inventarios_productos', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/movementsSynchronization.php' ) ){
    die( 'No se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
  $resp = array();

  $movements = $request->getParam( "movements" );
  $log = $request->getParam( "log" );
//
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  if( sizeof( $movements ) > 0 ){
    $inventories_update = $movementsSynchronization->updateInventory( $movements );
    $resp["ok_rows"] = $inventories_update["ok_rows"];
    $resp["error_rows"] = $inventories_update["error_rows"];
  //inserta la respuesta
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$inventories_update["ok_rows"]} | {$inventories_update["error_rows"]}", $resp["log"]["unique_folio"] );
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron movimientos de almacen para sumar al inventario, posiblemente tengas que bajar el limite de registros de sincronizacion de movimientos de almacen!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

  /****************************************** Consulta / Envia ******************************************
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
/*valida que el origen sea linea
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
*/

  return json_encode( $resp );

});

?>
