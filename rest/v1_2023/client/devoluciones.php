<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_devoluciones
* Path: /obtener_devoluciones
* Método: POST
* Descripción: Recupera y envia las devoluciones que no se han sincronizado
*/
$app->get('/obtener_devoluciones', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/returnsSynchronization.php' ) ){
    die( 'no se incluyó libereria de devoluciones' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//variables
  $req = [];
  $req["movements"] = array();
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $returnsSynchronization = new returnsSynchronization( $link );//instancia clase de sincronizacion de movimientos

  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_devolucion' );//consulta path del sistema central
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $movements_limit = $config['rows_limit'];

  if( $system_store == -1 ){//valida que el origen no sea linea
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_devolucion' );//liberar el modulo de sincronizacion
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }

  $setMovements = $returnsSynchronization->setNewSynchronizationReturns( $system_store, $system_store, $store_prefix, $movements_limit );//ejecuta el procedure para generar los movimientos de almacen
  if( $setMovements != 'ok' ){
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_devolucion' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => $setMovements ) );
  }

  $req["returns"] = $returnsSynchronization->getSynchronizationReturns( -1, $movements_limit );//consulta registros pendientes de sincronizar
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'DEVOLUCIONES' );//forma peticion
  $post_data = json_encode($req, JSON_PRETTY_PRINT);
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_devoluciones", $post_data );//envia petición

  $result = json_decode( $result_1 );//decodifica respuesta
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    $time = $SynchronizationManagmentLog->getCurrentTime();
    $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_devolucion' );//liberar el modulo de sincronizacion
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
//actualiza registros exitosos
  if( $result->ok_rows != '' && $result->ok_rows != null ){
    $returnsSynchronization->updateReturnSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false );
  }
//actualiza erores
  if( $result->error_rows != '' && $result->error_rows != null ){
    $returnsSynchronization->updateReturnSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false );
  }
//actualiza respuesta
  if( $result->log != '' && $result->log != null ){
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio );
  }

 /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);//json_encode($result->rows_download);
    $log_download = json_decode(json_encode($result->log_download), true );
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time );//inserta response
    $insert_rows = $returnsSynchronization->insertReturns( $rows_download );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia peticion para actualiza log de registros descargados
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "returnsSynchronization";
    //envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición
    }
  }
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_devolucion' );//liberar el modulo de sincronizacion
  $link->close();//cierra conexion Mysql
  return 'ok';//regresa respuesta
});

?>
