<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: obtener_registros_sincronizacion
* Path: /obtener_registros_sincronizacion
* Método: POST
* Descripción: Recupera y envia los registros de sincronizacion que no se han sincronizado ( local a linea )
* Version 2.1  Log y comprobacion )
*/
$app->get('/obtener_registros_sincronizacion', function (Request $request, Response $response){
 //die( 'here' );
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  if ( ! include( 'utils/rowsSynchronization.php' ) ){
    die( 'No se incluyó libereria de registros de sincronizacion' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  if( ! include( 'utils/generalRowsVerification.php' ) ){
    die( "No se incluyó : generalRowsVerification.php" );
  }
  if( !include( 'utils/Logger.php' ) ){
    die( "No se pudo incluir la clase Logger.php" );
  }
  $Logger = false;
  $LOGGER = false;
//variables
  $req = [];
  $req["rows"] = array(); 
  $result = "";
//verifica si el log esta habilitado
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
//instancia de clases
  $generalRowsVerification = new generalRowsVerification( $link, $Logger );//instancia clase de comprobacion
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link, $Logger );//instancia clase de Peticiones Log
  $rowsSynchronization = new rowsSynchronization( $link, $Logger );//instancia clase de sincronizacion de registros generales

/*verfifica que no este sincronizando / marca sincronizando
  $check = $SynchronizationManagmentLog->block_sinchronization_module( 'sys_sincronizacion_registros' );
  if( $check != 'ok' ){
    return json_encode( array( "response"=>$check ) );
  }*/
//consulta path del sistema central
  $config = $SynchronizationManagmentLog->getSystemConfiguration( 'sys_sincronizacion_registros' );
  $path = trim ( $config['value'] );
  $system_store = $config['system_store'];
  $store_prefix = $config['store_prefix'];
  $initial_time = $config['process_initial_date_time'];
  $rows_limit = $config['rows_limit'];

  if( $LOGGER ){
    $LOGGER = $Logger->insertLoggerRow( '', 'sys_sincronizacion_registros', $system_store, -1, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta el log de sincronizacion $LOGGER['id_sincronziacion']
  }

  if( $system_store == -1 ){//valida que el origen no sea linea
  //liberar el modulo de sincronizacion
    $SynchronizationManagmentLog->release_sinchronization_module( 'sys_sincronizacion_registros', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    return json_encode( array( "response"=>"La sucursal es linea y no puede ser cliente." ) );
  }
  $req["log"] = $SynchronizationManagmentLog->insertPetitionLog( $system_store, -1, $store_prefix, $initial_time, 'REGISTROS DE SINCRONIZACION', 'sys_sincronizacion_registros', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta request
  $req["rows"] = $rowsSynchronization->getSynchronizationRows( $system_store, -1, $rows_limit, 'sys_sincronizacion_registros', $req["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//consulta registros pendientes de sincronizar

   
    $post_data = json_encode($req, JSON_PRETTY_PRINT);//forma peticion//
  //return $post_data;
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/inserta_registros_sincronizacion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
//return $result_1;
    $result = json_decode( $result_1 );//decodifica respuesta
    if( $result == '' || $result == null ){  
      if( $result_1 == '' || $result_1 == null ){
        $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
      }
      $time = $SynchronizationManagmentLog->getCurrentTime( ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      $SynchronizationManagmentLog->updatePetitionLog( $time, $time, $result_1, $req["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    //liberar el modulo de sincronizacion
      $SynchronizationManagmentLog->release_sinchronization_module( 'sys_sincronizacion_registros', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
    }

    if( $result->ok_rows != '' && $result->ok_rows != null ){//actualiza registros exitosos
      $rowsSynchronization->updateRowSynchronization( $result->ok_rows, $req["log"]["unique_folio"], 'sys_sincronizacion_registros', 3, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    }
    if( $result->error_rows != '' && $result->error_rows != null ){//actualiza erores
      $rowsSynchronization->updateRowSynchronization( $result->error_rows, $req["log"]["unique_folio"], 'sys_sincronizacion_registros', 2, false, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    }
    if( $result->log != '' && $result->log != null ){//actualiza respuesta
      $SynchronizationManagmentLog->updatePetitionLog( $result->log->destinity_time, $result->log->response_time, $result->log->response_string, 
        $result->log->unique_folio, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    }
  /**************************************************Inserta lo que viene de linea**************************************************/
  if( $result->rows_download != '' && $result->rows_download != null ){
    $rows_download = json_decode(json_encode($result->rows_download), true);//json_encode($result->rows_download);
    $log_download = json_decode(json_encode($result->log_download), true );
    $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log_download, $request_initial_time, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//inserta response
    $insert_rows = $rowsSynchronization->insertRows( $rows_download, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//return json_encode($insert_rows);
    if( $insert_rows["error"] != '' && $insert_rows["error"] != null  ){//inserta error si es el caso
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_rows["error"], $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
      //return json_encode( $resp );
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"], "table"=>"sys_sincronizacion_registros", "status"=>"error" ), JSON_PRETTY_PRINT);//forma peticion
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
      //return $result_1;
    }else{
      $resp["ok_rows"] = $insert_rows["ok_rows"];
      $resp["error_rows"] = $insert_rows["error_rows"];
    //inserta respuesta exitosa
      $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$insert_rows["ok_rows"]} | {$insert_rows["error_rows"]}", $resp["log"]["unique_folio"], ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
    //envia peticion para actualiza log de registros descargados
      $resp["log"]["type_update"] = "rowsSynchronization";
      $post_data = json_encode(array( "log"=>$resp["log"], "ok_rows"=>$insert_rows["ok_rows"], "table"=>"sys_sincronizacion_registros", "status"=>"ok"  ), JSON_PRETTY_PRINT);//forma peticion//
     // return $post_data;
      $result_1 = $SynchronizationManagmentLog->sendPetition( "{$path}/rest/v1/actualiza_peticion", $post_data, ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );//envia petición
      //return $result_1;
    }
  }
//liberar el modulo de sincronizacion
  $SynchronizationManagmentLog->release_sinchronization_module( 'sys_sincronizacion_registros', ( $LOGGER['id_sincronizacion'] ? $LOGGER['id_sincronizacion'] : false ) );
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  return 'ok';
  //return json_encode( array( "response" => "Registros ok" ) );
});

?>
