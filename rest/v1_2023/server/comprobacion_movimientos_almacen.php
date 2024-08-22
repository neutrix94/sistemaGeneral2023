<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: comprobacion_movimientos_almacen
* Path: /comprobacion_movimientos_almacen
* Método: GET
* Descripción: Comprobacion de registros que no recibieron respuesta en Cliente
*/
$app->post('/comprobacion_movimientos_almacen', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  $movementsSynchronization = new SynchronizationManagmentLog( $link );
  $resp = array();
  $resp["before_petitions"] = array();
  $pending_petitions = $request->getParam( "pending_responses" );
  if( sizeof( $pending_petitions ) > 0 ){
    $resp["before_petitions"] = $SynchronizationManagmentLog->validatePendingPetition( $pending_petitions );
  }
  return json_encode( $resp );

});

?>
