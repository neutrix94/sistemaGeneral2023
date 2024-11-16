<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_registros_sincronizacion_ventas
* Path: /obtener_registros_sincronizacion_ventas
* Método: POST
* Descripción: Recupera y envia los registros de sincronizacion de ventas que no se han sincronizado
*/
$app->get('/obtener_registros_sincronizacion_ventas', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/rowsSynchronization.php' ) ){
    die( 'No se incluyó libereria de registros de sincronizacion' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/salesSynchronization.php' ) ){
    die( "No se incluyó : salesSynchronization.php" );
  }
//variables
  $req = [];
  $req["rows"] = array(); 
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $rowsSynchronization = new rowsSynchronization( $link );//instancia clase de sincronizacion de movimientos
  $salesSynchronization = new salesSynchronization( $link );
  
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos' );//consulta path del sistema central
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];

  if( $system_store == -1 ){//valida que el origen no sea linea
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );//liberar el modulo de sincronizacion
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }

  $setPayments = $salesSynchronization->setNewSynchronizationPayments( $system_store, $system_store, $store_prefix, $rows_limit );//ejecuta el procedure para generar registros de sincronizacion de pagos
  if( $setPayments != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setPayments ) );
  }

  $req["rows"] = $rowsSynchronization->getSynchronizationRows( $system_store, -1, $rows_limit, 'sys_sincronizacion_registros_ventas' );//consulta registros pendientes de sincronizar
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION' );//inserta request
  $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion//
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_registros_sincronizacion_ventas", $post_data );//envia petición
  $result = json_decode( $result_1 );//decodifica respuesta
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime();
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }

  if( $result->ok_rows != '' && $result->ok_rows != null ){//actualiza registros exitosos
    $rowsSynchronization->updateRowSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 'sys_sincronizacion_registros_ventas', 3, false );
  }
  if( $result->error_rows != '' && $result->error_rows != null ){//actualiza erores
    $rowsSynchronization->updateRowSynchronization( $result->error_rows, $req["log"]["unique_folio"], 'sys_sincronizacion_registros_ventas', 2, false );
  }
  if( $result->log != '' && $result->log != null ){//actualiza respuesta
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio );
  }

  /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);
    $log_download = json_decode(json_encode($result->log_download), true );
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time );//inserta response
  $insert_rows = $rowsSynchronization->insertRows( $rows_download );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"], "table"=>"sys_sincronizacion_registros_ventas" ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "rowsSynchronization";
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"], "table"=>"sys_sincronizacion_registros_ventas" ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición para actualiza log de registros descargados
    }
  }
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql

  return 'ok';//regresa respuesta
});

?>
