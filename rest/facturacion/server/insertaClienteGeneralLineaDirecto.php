<?php
/* Version sin envio de cliente a sistemas de facturacion desde sistema general 2024-10-03*/
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
  * Endpoint: inserta_cliente_directo_general_linea
  * Path: /inserta_cliente_directo_general_linea
  * Método: POST
  * Descripción: Serviciond de Insercion de clientes (viene desde administracion de facturacion).
  * Version Oscar 2024-11-07 Para mandar ids de clientes procesados exitosamente en la respuesta del servicio hacia administracion de facturacion
*/

$app->post('/inserta_cliente_directo_general_linea', function (Request $request, Response $response){
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
  $resp["ok_rows"] = array();

  $tmp_ok = "";
  $tmp_no = "";
  $costumers = $request->getParam( "rows" );
  if( sizeof( $costumers ) > 0 ){
    $insert_costumers = $Bill->insertCostumers( $costumers );
      $resp["ok_rows"] = $insert_costumers;
    if( $insert_costumers["error"] != '' && $insert_costumers["error"] != null  ){
      return json_encode( array( "status"=>400, "message"=>$insert_costumers["error"] ) );
    }else{
      $resp["ok_rows"] = $insert_costumers;
    }
  }else{
    $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
    return json_encode( array( "status"=>200, "message"=>$response_string ) );
  }
  return json_encode( 
    array( 
                  "status"=>200, 
                  "ok_rows"=>$resp["ok_rows"], 
                  "message"=>"Registros procesados en General Linea exitosamente." 
                ) 
              );
});

?>