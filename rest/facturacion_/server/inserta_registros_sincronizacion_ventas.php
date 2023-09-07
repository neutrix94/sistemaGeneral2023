<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_registros_sincronizacion
* Path: /inserta_registros_sincronizacion
* Método: GET
* Descripción: Insercion de registros de sincronizacion
*/
$app->post('/inserta_registros_sincronizacion_ventas', function (Request $request, Response $response){
//incluye librerias
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/rowsSynchronization.php' ) ){
    die( 'No se incluyó libereria de sincronizacion de registros de sincronizacion : ' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
//instanca de clases
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
  $rowsSynchronization = new rowsSynchronization( $link );//instancia clase de sincronizacion de movimientos

//variables de respuesta
  $resp = array();
  $resp["ok_rows"] = '';
  $resp["error_rows"] = '';
  $resp["rows_download"] = array();//registros por descargar
  $resp["log_download"] = array();//log de registros por descargar

//variables que llegan
  $rows = $request->getParam( "rows" );
  $log = $request->getParam( "log" );

/****************************************** Recibe / Inserta ******************************************/
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();//obtiene hora actual
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );//inserta response
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  if( sizeof( $rows ) > 0 ){
    $insert_rows = $rowsSynchronization->insertRows( $rows );
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"] );
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"] );
    }
  }else{
  //inserta excepcion controlada
    $response_string = "No llegaron registros de sincronizacion, posiblemente tengas que bajar el limite de registros de sincronizacion!";
    $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
  }

/****************************************** Consulta / Envia ******************************************/
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'sys_sincronizacion_registros' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];
  $resp["rows_download"] = $rowsSynchronization->getSynchronizationRows( $system_store, $log['origin_store'], 
    $rows_limit, 'sys_sincronizacion_registros_ventas' );//obtiene registros para descargar
  if( sizeof( $resp["rows_download"] ) > 0 ){
    $resp["log_download"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, $log['origin_store'], $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION' );
  }
  return json_encode( $resp );

});

?>
