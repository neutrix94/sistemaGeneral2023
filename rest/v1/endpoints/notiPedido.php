<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteNotifica
* Path: /surte/notifica
* Método: POST
* Descripción: Servicio para notificar sutimiento
*/
$app->post('/surte/notifica', function (Request $request, Response $response){
  //Init
  $db = new db();           //Instancia BD General
  $db = $db->conectDB();
  // $dbFact = new dbFact();   //Instancia a BD Fact
  // $dbFact = $dbFact->conectDB();
  $rs = new manageResponse();
  $vt = new tokenValidation();

  //Valida token
  /*$token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
  if (empty($token) || strlen($token)<36 ) {
    //Define estructura de salida: Token requerido
    return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
  }else{
    //Consulta vigencia
    try{
      $resultadoToken = $vt->validaToken($token);
      if ($resultadoToken->rowCount()==0) {
          return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
      }
    }catch (PDOException $e) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
    }
  }*/

  //Recuperar parámetros de entrada
  $idVendedor = $request->getParam('idVendedor');
  $idSurtidor = $request->getParam('idSurtidor');
  $pedido = $request->getParam('pedido');
  $tipoNotificacion = $request->getParam('tipoNotificacion'); //muestra/pedido/surtido
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($tipoNotificacion)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información del tipo de notificación', 400);
  }
  if (empty($pedido)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de pedido para solicitar muestras', 400);
  }
  if (empty($idVendedor) && empty($idSurtidor)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información del surtidor/vendedor a notificar', 400);
  }
  
  //Ejecuta lógica para surtimiento
  try {
    if($tipoNotificacion == 'surtido'){
      $sqlUpdate = "UPDATE ec_surtimiento SET vendedor_notificado = '1' WHERE (no_pedido = '{$pedido}' AND id_vendedor = '{$idVendedor}' );";
      $db->exec($sqlUpdate);
    }
    if($tipoNotificacion == 'muestra' || $tipoNotificacion == 'pedido'){
      $sqlUpdate = "UPDATE ec_surtimiento SET surtidor_notificado = '1' WHERE (no_pedido = '{$pedido}');";
      $db->exec($sqlUpdate);
    }
    
    $insertsProd['resultado']='Exito';
    $insertsProd['descripcion']='Se ha actualizado la notificación del pedido';
  }catch (PDOException $e) {
    $insertsProd['resultado']='Error';
    $insertsProd['descripcion']= $e->getMessage();
  }

  //Limpia variables
  $db = null;
  //Regresa resultado
  return $rs->successMessage($request->getParsedBody(),$response, $insertsProd);

});

?>
