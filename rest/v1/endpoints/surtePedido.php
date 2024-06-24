<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteMuestra
* Path: /surte/Muestra
* Método: POST
* Descripción: Servicio para solicitar muestra
*/
$app->post('/surte/Pedido', function (Request $request, Response $response){
  //Init
  $db = new db();           //Instancia BD General
  $db = $db->conectDB();
  // $dbFact = new dbFact();   //Instancia a BD Fact
  // $dbFact = $dbFact->conectDB();
  $rs = new manageResponse();
  $vt = new tokenValidation();

  //Valida token
  $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
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
  }

  //Recuperar parámetros de entrada
  $productos = $request->getParam('productos');
  $vendedor = $request->getParam('vendedor');
  $pedido = $request->getParam('pedido');
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($vendedor)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de vendedor para solicitar muestras', 400);
  }
  if (empty($pedido)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de pedido para solicitar muestras', 400);
  }
  if (empty($productos)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de productos para solicitar muestras', 400);
  }
  //Validar elementos requerido para nodo productos
  if (count($productos)>0) {
    //Itera y valida productos
    $productRow=0;
    $idProductos = "'0'";
    foreach($productos as $producto) {
      if (empty($producto['id'])) {
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información para crear producto(s)', 400);
      }
      $idProductos = $idProductos . ",'".$producto['idProducto']."'";
      $productRow ++;
    }
  }
  
  //Ejecuta lógica para surtimiento
  try {
    $insertsProd['resultado']='Solicitado';
    $insertsProd['descripcion']='Se ha solicitado el pedido de '. $productRow . ' producto(s)';
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
