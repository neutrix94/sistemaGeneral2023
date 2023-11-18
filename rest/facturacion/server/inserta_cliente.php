<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: inserta_devoluciones
* Path: /inserta_devoluciones
* Método: GET
* Descripción: Insercion de devoluciones
*/

$app->post('/inserta_cliente', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  /*if ( ! include( 'utils/returnsSynchronization.php' ) ){
    die( 'No se incluyó libereria de Devoluciones' );
  }*/
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }

  if( ! include( 'utils/facturacion.php' ) ){
    die( "No se incluyó : facturacion.php" );
  }//die( 'here' );
  $Bill = new Bill( $link, $system_store, $store_prefix );
  //return json_encode( $request->getParam( "rows" ) );
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
 // $returnsSynchronization = new returnsSynchronization( $link );//instancia clase de sincronizacion de movimientos
  
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
//return json_encode( $insert_returns );
    if( $insert_returns["error"] != '' && $insert_returns["error"] != null  ){
    //inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_returns["error"], $resp["log"]["unique_folio"] );
    }else{
      $resp["ok_rows"] = $insert_returns["ok_rows"];
      $resp["error_rows"] = $insert_returns["error_rows"];
      $tmp_ok = $insert_returns->tmp_ok;
      $tmp_no = $insert_returns->tmp_no;
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_returns["ok_rows"]} | {$insert_returns["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }
//consume el webservice para insertar cliente en los sistemas de factureacion
  $sql = "SELECT value FROM api_config WHERE name = 'path' LIMIT 1";
  $stm = $link->query( $sql ) or die( "Error al consultar el path del api : {$link->error}" );
  $row = $stm->fetch_assoc();
  $api_path = $row['value'];
  die( "api_path : {$api_path}" );
});

?>
