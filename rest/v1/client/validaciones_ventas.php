<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_validaciones_ventas
* Path: /obtener_validaciones_ventas
* Método: POST
* Descripción: Recupera y envia las validaciones de ventas que no se han sincronizado
*/
$app->get('/obtener_validaciones_ventas', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/salesValidationSynchronization.php' ) ){
    die( 'No se incluyó libereria de devoluciones' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/SalesValidationRowsVerification.php' ) ){
    die( "No se incluyó : SalesValidationRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables
  $req = [];
  $req["validations"] = array();
  $result = "";

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
  $salesValidationSynchronization = new salesValidationSynchronization( $link, $Logger );//instancia clase de sincronizacion validaciones de ventas
  $SalesValidationRowsVerification = new SalesValidationRowsVerification( $link, $Logger );//instancia clase de sincronizacion de validaciones de ventas

//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos_validacion_usuarios' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $validation_limits = $config['rows_limit'];

  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( '', 'sys_sincronizacion_validaciones_ventas', $system_store, -1 );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
  }
//valida que el origen no sea linea
  if( $system_store == -1 ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos_validacion_usuarios', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }

/*Comprobacion de movimientos de almacen ( peticiones anteriores ) 2024*/
  $req['verification'] = $SalesValidationRowsVerification->getPendingValidationSales( $system_store, -1, 
    ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//obtiene los registros de comprobacion de movientos de almacen
/*Fin de comprobacion de movimientos de almacen*/

//ejecuta el procedure para generar las validaciones de ventas
  $setValidations = $salesValidationSynchronization->setNewSynchronizationSalesValidation( $system_store, $system_store, $store_prefix, $validation_limits, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  if( $setValidations != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos_validacion_usuarios', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setValidations ) );
  }
  //var_dump($req['log']);return'';
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'VALIDACION VENTAS', 'sys_sincronizacion_validaciones_ventas', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta request
  $req["validations"] = $salesValidationSynchronization->getSynchronizationSalesValidation( -1, $validation_limits, $req['log']['unique_folio'], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false )  );//consulta registros pendientes de sincronizar
  $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion
//echo $post_data;//return $post_data;
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_validaciones_ventas", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false )  );//envia petición
  $result = json_decode( $result_1 );//decodifica respuesta
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos_validacion_usuarios', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
  $response_time = $result->log->response_time;
/*Procesa Respuesta de comprobacion*/
  if( $result->sales_validation->log_response != null && $result->sales_validation->log_response != '' ){
    //var_dump( $result->verification_movements->log_response );
    $update_log = $SalesValidationRowsVerification->updateLogAndJsonsRows( $result->sales_validation->log_response, $result->sales_validation->rows_response, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $update_log != 'ok' ){
      die( "Hubo un error : {$update_log}" );
    }
  }
  $verification_req = array();
/*Procesa comprobaciones de linea a local*/
  if( $result->sales_validation->rows_download != null && $result->sales_validation->rows_download != '' ){
    $download = $result->sales_validation->rows_download;
    $petition_log = json_decode(json_encode($download->petition), true);
    $movements = json_decode(json_encode($download->rows), true);
    if( $download->verification == true ){
      if( sizeof($petition_log) > 0 ){
        $verification_req['log_response'] = $SalesValidationRowsVerification->validateIfExistsPetitionLog( $petition_log, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta si la peticion existe en local 
        $verification_req['rows_response'] = $SalesValidationRowsVerification->warehouseMovementsValidation( $movements, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//realiza proceso de comprobacion
        $post_data = json_encode( $verification_req );
        $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_comprobacion_validacion_ventas", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consume servicio para actualizar la comprobacion en linea
      }
    }
  }
  
  //actualiza registros exitosos
  if( $result->ok_rows != '' && $result->ok_rows != null ){
    $salesValidationSynchronization->updateSalesValidationSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
  //actualiza erores
  if( $result->error_rows != '' && $result->error_rows != null ){
    $salesValidationSynchronization->updateSalesValidationSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }
  //actualiza respuesta
  if( $result->log != '' && $result->log != null ){
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
  }

 /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);//json_encode($result->rows_download);
    $log_download = json_decode(json_encode($result->log_download), true );
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta response
    $insert_rows = $salesValidationSynchronization->insertSalesValidation( $rows_download, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      //return json_encode( $resp );//envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta respuesta exitosa
      $resp["log"]["type_update"] = "salesValidationSynchronization";
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia peticion para actualiza log de registros descargados
    }
  }
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos_validacion_usuarios', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql
  return 'ok';//regresa respuesta
});

?>
