<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: actualiza_inventarios_proveedor_producto
* Path: /actualiza_inventarios_proveedor_producto
* Método: GET
* Descripción: Actualizacion de inventarios a nivel proveedor producto
*/
$app->post('/actualiza_inventarios_proveedor_producto', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/productProviderMovementsSynchronization.php' ) ){
    die( 'No se incluyó libereria de movimientos' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $productProviderMovementsSynchronization = new productProviderMovementsSynchronization( $link );//instancia clase de sincronizacion de movimientos
  $resp = array();

  $product_provider_movements = $request->getParam( "product_provider_movements" );
  $log = $request->getParam( "log" );
//
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  if( sizeof( $product_provider_movements ) > 0 ){
    $inventories_update = $productProviderMovementsSynchronization->updateProductProviderInventory( $product_provider_movements );
    $resp["ok_rows"] = $inventories_update["ok_rows"];
    $resp["error_rows"] = $inventories_update["error_rows"];
  //inserta la respuesta
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$inventories_update["ok_rows"]} | {$inventories_update["error_rows"]}", $resp["log"]["unique_folio"] );
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron movimientos de proveedor producto para sumar al inventario, posiblemente tengas que bajar el limite de registros de sincronizacion de movimientos de proveedor producto!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

  return json_encode( $resp );

});

?>
