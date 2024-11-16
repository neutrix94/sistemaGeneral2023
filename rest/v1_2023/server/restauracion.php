<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: restauracion
* Path: /
* Método: POST
* Descripción: ejecuta consultas de restauracion
*/
$app->post('/restauracion', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
//recibe el JSON
  $resp = 'ok';
  $sql = $request->getParam( "QUERY" );
  $link->autocommit( false );
  $stm = $link->query( $sql ) or die( "Error al ejecutar consulta de restauración : {$sql} {$link->error}" );
  $link->autocommit( true );
  return $resp;
});

?>
