<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: obtener_validaciones_ventas
* Path: /obtener_validaciones_ventas
* Método: POST
* Descripción: Recupera y envia las validaciones de ventas que no se han sincronizado
*/
$app->get('/obtener_movimientos_proveedor_producto', function (Request $request, Response $response){
//die( 'here' );
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/productProviderMovementsSynchronization.php' ) ){
    die( 'No se incluyó libereria de movimientos proveedor producto' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//variables
  $req = [];
  $req["product_provider_movements"] = array();
  $result = "";

  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $productProviderMovementsSynchronization = new productProviderMovementsSynchronization( $link );//instancia clase de sincronizacion de movimientos

/*verfifica que no este sincronizando / marca sincronizando
  $check = $SynchronizationManagmentLog->block_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
  if( $check != 'ok' ){
    return json_encode( array( "response"=>$check ) );
  }*/

//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'ec_movimiento_detalle_proveedor_producto' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $product_provider_movements_limit = $config['rows_limit'];
  //die( $product_provider_movements_limit );

//return json_encode( $config );

//valida que el origen no sea linea
  if( $system_store == -1 ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
//ejecuta el procedure para generar los movimientos de almacen
  $setProductProviderMovements = $productProviderMovementsSynchronization->setNewSynchronizationProductProviderMovements( $system_store, $system_store, $store_prefix, $product_provider_movements_limit );
  //var_dump( $setProductProviderMovements ); 
  if( $setProductProviderMovements != 'ok' ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
    return json_encode( array( "response" => $setProductProviderMovements ) );
  }
//consulta registros pendientes de sincronizar
  $req["product_provider_movements"] = $productProviderMovementsSynchronization->getSynchronizationProductProviderMovements( -1, $product_provider_movements_limit );
//var_dump($req["product_provider_movements"]);
//die( 'here' );
//return json_encode( $req["product_provider_movements"] );
//Valida path
//if ( sizeof( $req["product_provider_movements"] ) > 0 ) {
  //inserta request
    $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'MOVIMIENTOS PROVEEDOR PRODUCTO' );
  //forma peticion
    $post_data = json_encode($req, JSON_PRETTY_PRINT);//
//return $post_data;
  //envia petición
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_movimientos_proveedor_producto", $post_data );
//return $result_1;
  
    $result = json_decode( $result_1 );//decodifica respuesta
    if( $result == '' || $result == null ){  
      if( $result_1 == '' || $result_1 == null ){
        $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
      }
      $time = $SynchronizationManagmentLog->getCurrentTime();
      $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"] );
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
      return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
    }
  //actualiza registros exitosos
    if( $result->ok_rows != '' && $result->ok_rows != null ){
      $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 3, false );
    }
  //actualiza erores
    if( $result->error_rows != '' && $result->error_rows != null ){
      $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->error_rows, $req["log"]["unique_folio"], 2, false );
    }
  //actualiza respuesta
    if( $result->log != '' && $result->log != null ){
    //var_dump( $result->log );
      $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
        $result->log->unique_folio );
    }

    /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $post_data_1 = "";
    $rows_download = json_decode(json_encode($result->rows_download), true);//json_encode($result->rows_download);
    $log_download = json_decode(json_encode($result->log_download), true );
  //$request_initial_time = $SynchronizationManagmentLog->getCurrentTime();//obtiene hora actual
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time );//inserta response
    $insert_rows = $productProviderMovementsSynchronization->insertProductProviderMovements( $rows_download );
     //return json_encode($insert_rows);
    //echo 'here';
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
      //return json_encode( $resp );//envia peticion para actualiza log de registros descargados
      $post_data_1 = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data_1 );//envia petición
      //return $result_1;
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
      $productProviderMovementsSynchronization->updateProductProviderInventory( $rows_download );//suma el inventario
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
      $resp["log"]["type_update"] = "productProviderMovementsSynchronization";
    //envia peticion para actualiza log de registros descargados
      $post_data_1 = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"] ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data_1 );//envia petición
      //return $result_1;

    }
    //echo 'here';
  }

  $initial_time_2 = $SynchronizationManagmentLog->getCurrentTime();
//consume API para actualizar los inventarios de productos
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time_2, 'ACTUALIZACION DE INVENTARIOS PROVEEDOR PRODUCTO' );
  
  $post_data = json_encode($req, JSON_PRETTY_PRINT);//
  $result_2 = $SynchronizationManagmentLog->sendPetition( $path.'/rest/v1/actualiza_inventarios_proveedor_producto', $post_data );
//return $result_2;
  $result = json_decode( $result_2 );
  if( $result->error ){
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
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
    $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
//actualiza registros exitosos
  if( $result->ok_rows != '' && $result->ok_rows != null ){
    $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->ok_rows, $req["log"]["unique_folio"], null, true );
  }
//actualiza erores
  if( $result->error_rows != '' && $result->error_rows != null ){
    $productProviderMovementsSynchronization->updateProductProviderMovementsSynchronization( $result->error_rows, $req["log"]["unique_folio"], null, true );
  }
  if( $result->log != '' && $result->log != null ){
//var_dump($result->log );
    $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
      $result->log->unique_folio );
  }
//liberar el modulo de sincronizacion
  $SynchronizationManagmentLog->release_sinchronization_module( 'ec_movimiento_detalle_proveedor_producto' );
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  return 'ok';
  //return json_encode( array( "response" => "Movimientos pp ok" ) );
});

?>
