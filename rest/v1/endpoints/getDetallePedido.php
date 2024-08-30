<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: GetDetallePedido
* Path: /surte/GetDetallePedido
* Método: POST
* Descripción: Servicio para obtener el detalle de un pedido
*/
$app->post('/surte/GetDetallePedido', function (Request $request, Response $response){
  //Init
  $db = new db();           //Instancia BD General
  $db = $db->conectDB();
  // $dbFact = new dbFact();   //Instancia a BD Fact
  // $dbFact = $dbFact->conectDB();
  $rs = new manageResponse();
  $vt = new tokenValidation();

  //Valida token
  // $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
  // if (empty($token) || strlen($token)<36 ) {
  //   //Define estructura de salida: Token requerido
  //   return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
  // }else{
  //   //Consulta vigencia
  //   try{
  //     $resultadoToken = $vt->validaToken($token);
  //     if ($resultadoToken->rowCount()==0) {
  //         return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
  //     }
  //   }catch (PDOException $e) {
  //     return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
  //   }
  // }

  //Recuperar parámetros de entrada
  $idPedido = $request->getParam('idPedido');
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($idPedido)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de idPedido', 400);
  }

  try {

    //Obtener puesto de usuario
    $queryDetallePedido = "SELECT 
    sd.id_producto, 
    sd.cantidad_solicitada, 
    sd.cantidad_surtida
FROM 
    ec_surtimiento s
JOIN 
    ec_surtimiento_detalle sd 
ON 
    s.id = sd.id_surtimiento
WHERE 
    s.no_pedido = '{$idPedido}'";

    $result = $db->query($queryDetallePedido);
    $count = $result->rowCount();
    if( $count > 0 ){

      $arraProductos = array();
      $estado = "Completo";

      foreach($result as $row) {
        $idProducto =$row['id_producto'];
        $cantidadSolicitada =$row['cantidad_solicitada'];
        $cantidadSurtida =$row['cantidad_surtida'];

        if ($cantidadSurtida === null || $cantidadSolicitada != $cantidadSurtida) {
          $estado = "Incompleto"; // Si hay diferencia, el estado será Incompleto
        }

        $productoDetalle =  array(
          "idProducto"=>$idProducto,
          "cantidadSolicitada"=>$cantidadSolicitada,
          "cantidadSurtida"=>$cantidadSurtida
        );

        array_push( $arraProductos, $productoDetalle );
      }

      $resultado['idPedido'] = $idPedido;
      $resultado['estado'] = "Surtido ".$estado;
      $resultado['descripcion'] = $arraProductos;

    }else{
      $resultado['resultado']='NOT_FOUND';
      $resultado['descripcion']= "No hay productos para el pedido ".$idPedido;
    }

    
  }catch (PDOException $e) {
    $resultado['resultado']='Error';
    $resultado['descripcion']= $e->getMessage();
  }


  //Limpia variables
  $db = null;
  //Regresa resultado
  return $rs->successMessage($request->getParsedBody(),$response, $resultado);

});

?>
