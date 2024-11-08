<?php
/* Version sin envio de cliente a sistemas de facturacion desde sistema general 2024-10-03*/
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_cliente
* Path: /inserta_cliente
* Método: GET
* Descripción: Insercion de clientes de facturacion
*/

$app->post('/inserta_cliente', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  $link->set_charset("utf8mb4");
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/facturacion.php' ) ){
    die( "No se incluyó : facturacion.php" );
  }
  $Bill = new Bill( $link, $system_store, $store_prefix );
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  if( ! include( 'utils/rowsSynchronization.php' ) ){
    die( "No se incluyó : rowsSynchronization.php" );
  }
  $rowsSynchronization = new rowsSynchronization( $link );

  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();
  $resp["log_download"] = array();

  $tmp_ok = "";
  $tmp_no = "";

  $log = $request->getParam( "log" );
  $costumers = $request->getParam( "rows" );
  //inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
  if( sizeof( $costumers ) > 0 ){
    $insert_returns = $Bill->insertCostumers( $costumers );
      $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
//return json_encode( $insert_returns );
    if( $insert_returns["error"] != '' && $insert_returns["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_returns["error"], $resp["log"]["unique_folio"] );
    }else{
      $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
   // die( "ok_rows : {$insert_returns}" );
      
      //$resp["error_rows"] = $insert_returns["error_rows"];
      //$tmp_ok = $insert_returns->tmp_ok;
      //$tmp_no = $insert_returns->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$resp["ok_rows"]} | {$insert_returns["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }
/*deshabilitado por Oscar 2024-10-13 porque esto ya no aplica
//consulta las cliemtes que se tiene que descargar 
  //$costumers_limit = 1000;
  //$resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, $log['origin_store'], $costumers_limit, 'sys_sincronizacion_registros_facturacion' );
*/
  return 'ok';
});

?>