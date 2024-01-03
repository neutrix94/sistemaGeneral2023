<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_movimientos_almacen
* Path: /obtener_movimientos_almacen
* Método: POST
* Descripción: Recupera y envia los movimientos de almacen que no se han sincronizado
*/
$app->get('/obtener_movimientos_almacen', function (Request $request, Response $response){

  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( 'utils/movementsSynchronization.php' ) ){
    die( 'no se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//variables
  $req = [];
  $req["movements"] = array(); 
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $movementsSynchronization = new movementsSynchronization( $link );//instancia clase de sincronizacion de movimientos

//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_almacen' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $movements_limit = $config['rows_limit'];

//return json_encode( $config );

//valida que el origen no sea linea
  if( $system_store == -1 ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }

/*comprobacion de movimientos de almacen ( peticiones anteriores )*
  $pending_responses['pending_responses'] = $SynchronizationManagmentLog->getPendingResponses( $system_store );
 // return json_encode( $pending_responses );
  if( sizeof( $pending_responses ) > 0 ){//consulta respuestas pendientes
    $pending_responses = json_encode( $pending_responses );
    $validate_before_petition = $SynchronizationManagmentLog->sendPetition( $path.'/rest/v1/comprobacion_movimientos_almacen', $pending_responses );
  //return json_encode( $validate_before_petition );
    $validate_before_petition_decoded = json_decode( $validate_before_petition );
    
    if( $validate_before_petition_decoded == '' || $validate_before_petition_decoded == null ){
      if( $validate_before_petition == '' || $validate_before_petition == null ){
        $validate_before_petition = "Posiblemente no hay conexion con el servidor de Linea";
      }
      return json_encode( array( "response" => "Respuesta Erronea : {$validate_before_petition}" ) );
    }  //before_petitions
//return json_encode($validate_before_petition_decoded );
    if( $validate_before_petition_decoded->before_petitions != '' && $validate_before_petition_decoded->before_petitions != null ){
        $SynchronizationManagmentLog->updatePendingPetitions( $validate_before_petition_decoded->before_petitions );
    }
  }
//die( 'ok' );*/

//ejecuta el procedure para generar los movimientos de almacen
  $setMovements = $movementsSynchronization->setNewSynchronizationMovements( $system_store, $system_store, $store_prefix, $movements_limit );
  if( $setMovements != 'ok' ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
    return json_encode( array( "response" => $setMovements ) );
  }

//consulta registros pendientes de sincronizar
  $req["movements"] = $movementsSynchronization->getSynchronizationMovements( -1, $movements_limit, 1 );
//var_dump($req["movements"]);
//die( 'here' );
//return json_encode( $req["movements"] );
//Valida path
//if ( sizeof( $req["movements"] ) > 0 ) {
  //inserta request
    $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'MOVIMIENTOS DE ALMACEN' );
  //forma peticion
    $post_data = json_encode($req, JSON_PRETTY_PRINT);//
//return $post_data;
  //envia petición
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_movimientos_almacen", $post_data );
//return $result_1;
  //decodifica respuesta
   $result = json_decode( $result_1 );
    /*if( $result->error ){
        return json_encode( array( "response" => $result->error ) );    
    }*/
    if( $result == '' || $result == null ){  
      if( $result_1 == '' || $result_1 == null ){
        $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
      }
      $time = $SynchronizationManagmentLog->getCurrentTime();
      $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
      return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
    /*guarda el error de la respuesta
      $sql = "UPDATE sys_sincronizacion_peticion 
                SET contenido_respuesta = 'Error en linea : {$result_1}' 
              WHERE folio_unico = '{$req['log']["unique_folio"]}'";
      $link->query( $sql ) or die( "Error al insertar sincronización de petición : {$link->error}" );*/
    }
  //actualiza registros exitosos
    if( $result->ok_rows != '' && $result->ok_rows != null ){
      $movementsSynchronization->updateMovementSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false );
    }
  //actualiza erores
    if( $result->error_rows != '' && $result->error_rows != null ){
      $movementsSynchronization->updateMovementSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false );
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
    $insert_rows = $movementsSynchronization->insertMovements( $rows_download );
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
    //aqui actualiza inventario de lo que viene de linea  
      $movementsSynchronization->updateInventory( $rows_download );//suma el inventario
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "movementsSynchronization";
    //envia peticion para actualiza log de registros descargados
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data );//envia petición
      //return $result_1;

    }
    //echo 'here';
  }




    $initial_time_2 = $SynchronizationManagmentLog->getCurrentTime();
//consulta registros pendientes de sincronizar
    $req["movements"] = $movementsSynchronization->getSynchronizationMovements( -1, $movements_limit, 2 );
//consume API para actualizar los inventarios de productos
    $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time_2, 'ACTUALIZACION DE INVENTARIOS PRODUCTOS' );
    
    $post_data = json_encode($req, JSON_PRETTY_PRINT);//
    $result_2 = $SynchronizationManagmentLog->sendPetition( $path.'/rest/v1/actualiza_inventarios_productos', $post_data );
//return $result_2;
    $result = json_decode( $result_2 );
    if( $result->error ){
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
      return json_encode( array( "response" => $result->error ) );    
    }
    if( $result == '' || $result == null ){  
      if( $result_2 == '' || $result_2 == null ){
        $result_2 = "Posiblemente no hay conexion con el servidor de Linea";
      }
    //guarda el error de la respuesta
      $time = $SynchronizationManagmentLog->getCurrentTime();
      $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_2, $req["log"]["unique_folio"] );
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
      return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
    }
  //actualiza registros exitosos
    if( $result->ok_rows != '' && $result->ok_rows != null ){
      $movementsSynchronization->updateMovementSynchronization( $result->ok_rows, $req["log"]["unique_folio"], null, true );
    }
  //actualiza erores
    if( $result->error_rows != '' && $result->error_rows != null ){
      $movementsSynchronization->updateMovementSynchronization( $result->error_rows, $req["log"]["unique_folio"], null, true );
    }
    if( $result->log != '' && $result->log != null ){
//var_dump($result->log );
      $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
        $result->log->unique_folio );
    }
//liberar el modulo de sincronizacion
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_almacen' );
//return $result_2;
/*  }else if( sizeof( $req["movements"] ) <= 0 ){
  //regresa respuesta vacía
    return json_encode( array( "response" => "No hay movimientos por sincronizar" ) );
  }*/
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  return 'ok';
  //return json_encode( array( "response" => "Movimientos ok" ) );
});

?>
