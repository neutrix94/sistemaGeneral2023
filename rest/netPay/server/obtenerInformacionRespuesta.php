<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtenerInformacionRespuesta
* Path: /obtenerInformacionRespuesta
* Método: POST
* Descripción: Obtener datos de una respuesta de NetPay
*/

$app->post('/obtenerInformacionRespuesta', function (Request $request, Response $response){
	//return json_encode( array( "response"=>"ok" ) );
});
?>
