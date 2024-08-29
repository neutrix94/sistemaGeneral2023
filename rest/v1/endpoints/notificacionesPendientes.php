<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteMuestra
* Path: /surte/Muestra
* Método: POST
* Descripción: Servicio para solicitar muestra
*/
$app->post('/surte/Pendientes', function (Request $request, Response $response){
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
  $idUsuario = $request->getParam('idUsuario');
  
  //Validar elementos requeridos para crear surtimiento
  if (empty($idUsuario)) {
    return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Hace falta información de idUsuario', 400);
  }

  try {

    /*
    -- 2 Ventas, Validacion y Devolucio
    -- 4 Encargado
    -- 7 Cajero
    -- 8 Encargado y Cajero
    -- 12 Cajero Sin Devoluciones
    -- 18 Solo Ventas
    -- 19 Solo Ventas y Validación
    -- 14 Surtimiento
    */
    $arrayPuestosVendedor = array("2","4","7","8","12","18","19");
    $perfilUsuario = "";
    //Obtener puesto de usuario
    $queryPerfilUsuario = "SELECT tipo_perfil FROM sys_users WHERE id_usuario = ".$idUsuario;

    $result = $db->query($queryPerfilUsuario);
    $count = $result->rowCount();
    if( $count > 0 ){

      foreach($result as $row) {
        $perfilUsuario =$row['tipo_perfil'];
      }

      //Es ventas?
      if( in_array($perfilUsuario, $arrayPuestosVendedor) ){
        //Obtiene todos los registros de ec_surtimiento, ya que ventas recibe todas las notificaciones
        $queryGetSurtimientoVendedor = "SELECT id id_surtimiento FROM ec_surtimiento WHERE vendedor_notificado IS NULL";
        $resultHeaderSurtimiento = $db->query($queryGetSurtimientoVendedor);
        $countVendedorNoNotificado = $resultHeaderSurtimiento->rowCount();

        if( $countVendedorNoNotificado > 0 ){
          //Por cada pedido, armar objeto
          $arrayPedidos = [];
          foreach($resultHeaderSurtimiento as $row) {
            $idSurtimiento =$row['id_surtimiento'];

            $queryDetallePorPedido = "SELECT
              s.tipo,
              s.no_pedido,
              sd.id_surtimiento,
              s.id_vendedor,
              sd.id_asignado id_surtidor,
              s.vendedor_notificado,
              s.surtidor_notificado
              FROM ec_surtimiento s
              INNER JOIN ec_surtimiento_detalle sd ON s.id = sd.id_surtimiento
              WHERE s.id= '{$idSurtimiento}'";

              $resultDetalleSurtimiento = $db->query($queryDetallePorPedido);
              $countDetallePorPedido = $resultDetalleSurtimiento->rowCount();

              if( $countDetallePorPedido > 0 ){
                
                foreach($resultDetalleSurtimiento as $row) {
                  $idSurtimiento = $row['id_surtimiento'];
                  $arrayPedido = array(
                    "tipo_pedido" => $row['tipo'],
                    "id_surtidor" => $row['id_surtidor'],
                    "id_vendedor" => $row['id_vendedor'],
                    "no_pedido" => $row['no_pedido'],
                    "id_surtimiento" => $row['id_surtimiento']
                  );                    

                  //Solo agregar al arreglo, en caso de que sea un pedido diferente
                  if( !existeEnArreglo($arrayPedidos,$idSurtimiento) ){
                    array_push($arrayPedidos, $arrayPedido);
                  }
                }

              }

            }

            $resultado['resultado']='OK';
            $resultado['descripcion']= $arrayPedidos;

        }

      }else if( $perfilUsuario == 14 ){

        //Sección para usuario Surtidor
        //Únicamente se obtienen los pedidos que tienen surtidor_notificado y además el usuario pasado como parámetro es el surtidor asignado
        $queryGetSurtimientoSurtidor = "SELECT
          DISTINCT(sd.id_surtimiento),
          s.tipo,
          s.no_pedido,
          s.id_vendedor,
          sd.id_asignado,
          s.vendedor_notificado,
          s.surtidor_notificado
          FROM ec_surtimiento s
          INNER JOIN ec_surtimiento_detalle sd ON s.id = sd.id_surtimiento
          WHERE surtidor_notificado IS NULL
          AND sd.id_asignado = {$idUsuario}";
        $resultSurtidor = $db->query($queryGetSurtimientoSurtidor);
        $countSurtidorNoNotificado = $resultSurtidor->rowCount();

        if( $countSurtidorNoNotificado > 0 ){
          //Por cada pedido, armar objeto
          $arrayPedidos = [];
          foreach($resultSurtidor as $row) {

            $idSurtimiento = $row['id_surtimiento'];
            $arrayPedido = array(
              "tipo_pedido" => $row['tipo'],
              "id_surtidor" => $row['id_asignado'],
              "id_vendedor" => $row['id_vendedor'],
              "no_pedido" => $row['no_pedido'],
              "id_surtimiento" => $idSurtimiento
            );                    

            //Solo agregar al arreglo, en caso de que sea un pedido diferente
            if( !existeEnArreglo($arrayPedidos,$idSurtimiento) ){
              array_push($arrayPedidos, $arrayPedido);
            }

          }


          $resultado['resultado']='OK';
          $resultado['descripcion']= $arrayPedidos;

        }else{

          $resultado['resultado']='OK';
          $resultado['descripcion']= "El usuario con el id ".$idUsuario." no cuenta con notificaciones pendientes";

        }


      }else{
        //El usuario no cuenta con perfil para obtener notificaciones
        $resultado['resultado']='OK';
        $resultado['descripcion']= "El usuario con el id ".$idUsuario." no cuenta con un perfil para recibir notificaciones";
      }

    }else{
      $resultPerfil = "No existe usuario con el id ".$idUsuario;
      $resultado['resultado']='NOT_FOUND';
      $resultado['descripcion']= $resultPerfil;
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

function existeEnArreglo( $arreglo, $valor ){

    $pos = "";
    for ($i=0; $i < count($arreglo); $i++) { 
      if( $arreglo[$i]['id_surtimiento'] == $valor ){
        $pos = $i;
      }
    }

    return ($pos != "" ) ? true : false;

}

?>
