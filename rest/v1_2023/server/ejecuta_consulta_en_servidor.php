<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: 
* Path: /
* Método: GET
* Descripción: Actualizacion de peticion de servidor a cliente
*/
$app->post('/', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
  /*if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }*/
  //recibe el JSON
  $resp =array();
  $sql = $request->getParam( "QUERY" );
  //die( $request->getParam( "QUERY" ) );
  $stm = $link->query( $sql ) or die( "Error : {$sql}" );
  //die( 'here' );
  while ( $row = $stm->fetch_assoc() ){ 
    array_push( $resp, $row );
  }
  return json_encode( $resp );
});

?>
