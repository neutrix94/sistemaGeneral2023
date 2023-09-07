<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: obtener_ventas
* Path: /obtener_ventas
* Método: POST
* Descripción: Recupera y envia las ventas que no se han sincronizado
*/
$app->get('/obtener_ventas', function (Request $request, Response $response){

  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/salesSynchronization.php' ) ){
    die( 'no se incluyó libereria de ventas' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//variables
  $req = [];
  $req["sales"] = array();
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $salesSynchronization = new salesSynchronization( $link );//instancia clase de sincronizacion de movimientos

/*verfifica que no este sincronizando / marca sincronizando
  $check = $SynchronizationManagmentLog->block_sinchronization_module( 'ec_pedidos' );
  if( $check != 'ok' ){
    return json_encode( array( "response"=>$check ) );
  }*/
  
//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_pedidos' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $movements_limit = $config['rows_limit'];

//return json_encode( $config );

//valida que el origen no sea linea
  if( $system_store == -1 ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
//ejecuta el procedure para generar los movimientos de almacen
  $setMovements = $salesSynchronization->setNewSynchronizationSales( $system_store, $system_store, $store_prefix, $movements_limit );
  if( $setMovements != 'ok' ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );
    return json_encode( array( "response" => $setMovements ) );
  }

//consulta registros pendientes de sincronizar
  $req["sales"] = $salesSynchronization->getSynchronizationSales( -1, $movements_limit );
//var_dump($req["movements"]);
//die( 'here' );
//return json_encode( $req["sales"] );
//Valida path
//if ( sizeof( $req["sales"] ) > 0 ) {
  //inserta request
    $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'VENTAS' );
  //forma peticion
    $post_data = json_encode($req, JSON_PRETTY_PRINT);//
//return $post_data;
  //envia petición
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_ventas", $post_data );
//return $result_1;
  
    $result = json_decode( $result_1 );//decodifica respuesta
    if( $result == '' || $result == null ){  
      if( $result_1 == '' || $result_1 == null ){
        $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
      }
      $time = $SynchronizationManagmentLog->getCurrentTime();
      $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );
      return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
    }
  //actualiza registros exitosos
    if( $result->ok_rows != '' && $result->ok_rows != null ){
      $salesSynchronization->updateSaleSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false );
    }
  //actualiza erores
    if( $result->error_rows != '' && $result->error_rows != null ){
      $salesSynchronization->updateSaleSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false );
    }
  //actualiza respuesta
    if( $result->log != '' && $result->log != null ){
    //var_dump( $result->log );
      $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio );
    }

  /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);//json_encode($result->rows_download);
    $log_download = json_decode(json_encode($result->log_download), true );
  //$request_initial_time = $SynchronizationManagmentLog->getCurrentTime();//obtiene hora actual
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time );//inserta response
    $insert_rows = $salesSynchronization->insertSales( $rows_download );
     //return json_encode($insert_rows);
    //echo 'here';
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
      //return json_encode( $resp );//envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición
      //return $result_1;
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "salesSynchronization";
    //envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición
      //return $result_1;
    }
    //echo 'here';
  }
//liberar el modulo de sincronizacion
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_pedidos' );
  /*}else if( sizeof( $req["sales"] ) <= 0 ){
  //regresa respuesta vacía
    return json_encode( array( "response" => "No hay ventas por sincronizar" ) );
  }*/
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  return 'ok';
  //return json_encode( array( "response" => "Ventas ok!" ) );
});

?>
