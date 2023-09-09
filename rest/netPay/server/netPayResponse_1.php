<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: netPayResponse
* Path: /netPayResponse
* Método: POST
* Descripción: Recupera respuesta de netPay
*/

$app->post('/', function (Request $request, Response $response){
  die( 'here_' );
  if( ! include( '../../conexionMysqli.php' ) ){
    die( "Error al incluir libreria de conexion!" );
  }

  $resp = array(
    "code"=>"00",
    "message"=>$response_message
  );
  return json_encode( $resp );
});
?>
