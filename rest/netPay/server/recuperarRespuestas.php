<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: recuperar_respuestas
* Path: /recuperar_respuestas
* Método: POST
* Descripción: Obtener datos de respuestas de NetPay que no fueron entregadas al usuario
*/

$app->post('/recuperar_respuestas', function (Request $request, Response $response){
	//return json_encode( array( "response"=>"ok" ) );
});
?>
