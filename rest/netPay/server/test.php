<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: netPayResponse
* Path: /netPayResponse
* Método: POST
* Descripción: Recupera respuesta de netPay
*/

$app->post('/test', function (Request $request, Response $response){
	return json_encode( array( "response"=>"ok" ) );
});
?>
