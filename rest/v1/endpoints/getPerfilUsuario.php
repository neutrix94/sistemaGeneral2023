<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: surteMuestra
* Path: /surte/Muestra
* Método: POST
* Descripción: Servicio para solicitar muestra
*/
$app->post('/surte/GetPerfilUsuario', function (Request $request, Response $response){
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
    $array_surtidor = array();
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

        $resultado['resultado']='OK';
        $resultado['descripcion']= "Vendedor";

      }else if( $perfilUsuario == 14 ){

        //Sección para usuario Surtidor
        $resultado['resultado']='OK';
        $resultado['descripcion']= "Surtidor";


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

?>
