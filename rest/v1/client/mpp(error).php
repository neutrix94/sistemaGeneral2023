<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_movimientos_proveedor_producto
* Path: /obtener_movimientos_proveedor_producto
* Método: POST
* Descripción: Recupera y envia los movimientos proveedor producto que no se han sincronizado
* Versión : 2.1
*/
$app->get('/obtener_movimientos_proveedor_producto', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/productProviderMovementsSynchronization.php' ) ){
    die( 'No se incluyó libereria de movimientos proveedor producto' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( !include( 'utils/warehouseProductProviderMovementsRowsVerification.php' ) ){ 
    die( "No se pudo incluir la clase warehouseProductProviderMovementsRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables
  $req = [];
  $req["product_provider_movements"] = array();
  $result = "";
  $sql = "SELECT
              log_habilitado AS log_is_enabled
      FROM sys_configuraciones_logs  
      WHERE id_configuracion_log = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar si el log esta habilitado : {$sql} : {$this->link->error}" );
  $row = $stm->fetch_assoc();
  $LOGGER = ( $row['log_is_enabled'] == 1 ? true : false );
  //return $row['log_is_enabled'];
  if( $LOGGER ){
    $Logger = new Logger( $link );//instancia clase de Logs
  }

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $warehouseProductProviderMovementsRowsVerification = new warehouseProductProviderMovementsRowsVerification( $link, $Logger );
  $productProviderMovementsSynchronization = new productProviderMovementsSynchronization( $link, $Logger );//instancia clase de sincronizacion de movimientos

  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_detalle_proveedor_producto' );//consulta path del sistema central
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $product_provider_movements_limit = $config['rows_limit'];

    if( $LOGGER ){
      $LOGGER = $Logger->insertLoggerRow( '', 'sys_sincronizacion_movimientos_proveedor_producto', $system_store, -1 );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
      $Logger->insertLoggerSteepRow( $LOGGER['id_sincronizacion'], 'Se consulta la configuracion de la sucursal y modulo', $config['logger_sql'] );
    }

  if( $system_store == -1 ){//valida que el origen no sea linea
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto', 
      ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
/*Comprobacion de movimientos proveedor producto ( peticiones anteriores ) 2024*/
  $req['verification'] = $warehouseProductProviderMovementsRowsVerification->getPendingWarehouseProductProviderMovement( $system_store, -1, 
  ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//obtiene los registros de comprobacion de movimientos de almacen
/*Fin de comprobacion de movimientos de almacen*/

  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 
    'MOVIMIENTOS PROVEEDOR PRODUCTO', 'sys_sincronizacion_movimientos_proveedor_producto', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta request
  $setProductProviderMovements = $productProviderMovementsSynchronization->setNewSynchronizationProductProviderMovements( $system_store, $system_store, $store_prefix, 
  $product_provider_movements_limit, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//ejecuta el procedure para generar los movimientos de almacen
  if( $setProductProviderMovements != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setProductProviderMovements ) );
  }
  $req["product_provider_movements"] = $productProviderMovementsSynchronization->getSynchronizationProductProviderMovements( -1, $product_provider_movements_limit, $req["log"]['unique_folio'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta registros pendientes de sincronizar

  $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion
//echo $post_data; die( '');//. $post_data .''
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_movimientos_proveedor_producto", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
var_dump( $result_1 );die( 'here' );
  $result = json_decode( $result_1 );//decodifica respuesta
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
  $response_time = $result->log->response_time;
/*Respuesta de comprobacion*/
  if( $result->verification_movements->log_response != null && $result->verification_movements->log_response != '' ){
    $update_log = $warehouseMovementsRowsVerification->updateLogAndJsonsRows( $resultado->verification_movements->log_response, $resultado->verification_movements->rows_response, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $update_log != 'ok' ){
      die( "Hubo un error : {$update_log}" );
    }
  }
  $verification_req = array();
  if( $result->verification_movements->rows_download != null && $result->verification_movements->rows_download != '' ){
    $download = $result->verification_movements->rows_download;
    $petition_log = json_decode(json_encode($download->petition), true);
    $movements = json_decode(json_encode($download->rows), true);
    if( $download->verification == true ){
      if( sizeof($petition_log) > 0 ){
        $verification_req['log_response'] = $warehouseMovementsRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta si la peticion existe en local
        $verification_req['rows_response'] = $warehouseMovementsRowsVerification->warehouseMovementsValidation( $movements, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
    
        $post_data = json_encode( $verification_req );
        $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_comprobacion_movimientos_proveedor_producto", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consume servicio para actualizar la comprobacion en linea
      }
    }
  }
/*Fin de Respuesta de Comprobacion*/
  $local_response_log = array();
  if( $result->ok_rows != '' && $result->ok_rows != null ){//actualiza registros exitosos
    $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
  if( $result->error_rows != '' && $result->error_rows != null ){//actualiza erores
    $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
  if( $result->log != '' && $result->log != null ){//actualiza respuesta
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
/**************************************************Inserta lo que viene de linea**************************************************/
  $rows_download = json_decode(json_encode($result->rows_download), true);
  $log_download = json_decode(json_encode($result->log_download), true );
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta response
  $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );  
  $resp["ok_rows"] = "";
  $resp["error_rows"] = "";

  if( $result->rows_download != '' && $result->rows_download != null ){
    $insert_rows = $productProviderMovementsSynchronization->insertProductProviderMovements( $rows_download, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      /*$post_data_1 = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data_1, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia peticion para actualiza log de registros descargados*/
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
      //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      //envia peticion para actualiza log de registros descargados
      //$post_data_1 = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      //$result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/sincronizacion/actualiza_peticion", $post_data_1, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
    }
  }
  $resp["log"]["type_update"] = "productProviderMovementsSynchronization";  
  $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );    
  $resp["log"]["destinity_time"] = $response_time;

  $post_data = json_encode(array( "log"=>$resp["log"], 
      "ok_rows"=>$insert_rows["ok_rows"], 
      "error_rows"=>$insert_rows["error_rows"],
      "local_response_log"=>$local_response_log
    ), JSON_PRETTY_PRINT);//forma peticion
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql
  return 'ok';//regresa respuesta
});

?>
