<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteFaltante
* Path: /surte/Faltante
* Método: POST
* Descripción: Servicio para solicitar faltantes de Pedido
*/
$app->post('/surte/Faltante', function (Request $request, Response $response){
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
  $pedido = $request->getParam('pedido');
  $user_id = $request->getParam('user_id');
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($pedido)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de pedido para solicitar pedido', 400);
  }
  
  //Ejecuta lógica para consultar faltante de surtimiento
  try {
      $solicitudActual = [];
      $solicitudActual['surtidoParcial'] = [];
      $solicitudActual['noSurtido'] = [];
      $solicitudProducto = [];
      $sqlConsultaSol="SELECT s.id, s.no_pedido, s.id_vendedor, concat(u.nombre , ' ' , u.apellido_paterno, ' ', u.apellido_materno) as vendedor, 
      sd.id_producto, p.nombre as producto, ifnull(sd.cantidad_solicitada,0) as solicitado, ifnull(sd.cantidad_surtida,0) as surtido, (ifnull(sd.cantidad_solicitada,0) - ifnull(sd.cantidad_surtida,0)) as faltante,
      concat( u_asignado.nombre , ' ' , u_asignado.apellido_paterno, ' ', u_asignado.apellido_materno ) as surtidor
      FROM ec_surtimiento s
      inner join ec_surtimiento_detalle sd on sd.id_surtimiento = s.id
      inner join sys_users u on u.id_usuario = s.id_vendedor
      inner join ec_productos p on p.id_productos = sd.id_producto
      LEFT JOIN sys_users u_asignado ON u_asignado.id_usuario = sd.id_asignado
      where 
      s.id='{$pedido}'
      and (ifnull(sd.cantidad_solicitada,0) - ifnull(sd.cantidad_surtida,0)) > 0
      and sd.id_asignado = '{$user_id}';";

      $resultQuery = $db->query($sqlConsultaSol);
      $num_filas = $resultQuery->rowCount();
      error_log( "FALTANTE, SE ENCONTRARON : " .$num_filas );
      $folioPedido = "";
      if( $num_filas > 0 ){
        foreach ($resultQuery as $row) {
            $solicitudActual['folioPedido'] = $row['no_pedido'];
            $solicitudActual['vendedor'] = $row['vendedor'];
            $solicitudActual['surtidor'] = $row['surtidor'];
            $solicitudProducto['nombre'] = $row['producto'];
            $solicitudProducto['solicitado'] = $row['solicitado'];
            $solicitudProducto['surtido'] = $row['surtido'];
            $solicitudProducto['faltante'] = $row['faltante'];
            
            if( $solicitudProducto['surtido'] == 0){
                $solicitudActual['noSurtido'][] = $solicitudProducto;
            } else {
                $solicitudActual['surtidoParcial'][] = $solicitudProducto;
            }
        }
      }else{
        //QUERY PARA PEDIDOS COMPLETOS, SE GENERA PARA OBTENER DATOS DE VENDEDOR Y SURTIDOR
        $sqlConsultaSolCompleto="SELECT s.id, s.no_pedido, s.id_vendedor, concat(u.nombre , ' ' , u.apellido_paterno, ' ', u.apellido_materno) as vendedor, 
        sd.id_producto, p.nombre as producto, ifnull(sd.cantidad_solicitada,0) as solicitado, ifnull(sd.cantidad_surtida,0) as surtido, (ifnull(sd.cantidad_solicitada,0) - ifnull(sd.cantidad_surtida,0)) as faltante,
        concat( u_asignado.nombre , ' ' , u_asignado.apellido_paterno, ' ', u_asignado.apellido_materno ) as surtidor
        FROM ec_surtimiento s
        inner join ec_surtimiento_detalle sd on sd.id_surtimiento = s.id
        inner join sys_users u on u.id_usuario = s.id_vendedor
        inner join ec_productos p on p.id_productos = sd.id_producto
        LEFT JOIN sys_users u_asignado ON u_asignado.id_usuario = sd.id_asignado
        where 
        s.id='{$pedido}'
        and sd.id_asignado = '{$user_id}';";
  
        $resultQueryCompleto = $db->query($sqlConsultaSolCompleto);

        foreach ($resultQueryCompleto as $row) {
          //$solicitudActual['folioPedido'] = $row['no_pedido'];
          $folioPedido = $row['no_pedido'];
          $solicitudActual['vendedor'] = $row['vendedor'];
          $solicitudActual['surtidor'] = $row['surtidor'];
          $solicitudProducto['nombre'] = $row['producto'];
          $solicitudProducto['solicitado'] = $row['solicitado'];
          $solicitudProducto['surtido'] = $row['surtido'];
          $solicitudProducto['faltante'] = $row['faltante'];
          
          if( $solicitudProducto['surtido'] == 0){
              $solicitudActual['noSurtido'][] = $solicitudProducto;
          } else {
              $solicitudActual['surtidoParcial'][] = $solicitudProducto;
          }
      }
    }       
      
      //Valida respuesta
      if( isset($solicitudActual['folioPedido'])) {
          //Regrsa resultado
          $insertsProd['resultado']="Faltante";
          $insertsProd['descripcion']='Se han identificado los siguientes productos faltantes de surtir';
          $insertsProd['detalle'] = $solicitudActual;
      }else{
          //Regresa resultado
          if( $folioPedido !== "" ){
            $solicitudActual['folioPedido'] = $folioPedido;
          }
          $insertsProd['resultado']='Completo';
          $insertsProd['descripcion']='No hay productos faltantes por surtir para este pedido';
          $insertsProd['detalle'] = $solicitudActual;
      }
      
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
