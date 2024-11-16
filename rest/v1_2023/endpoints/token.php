<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: getToken
* Path: /getToken
* Método: POST
* Descripción: Servicio para autenticación
*/
$app->post('/token', function (Request $request, Response $response){
    //Init
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();

    //Validar parámetros de entrada
    if (empty($request->getParam('user')) || empty($request->getParam('password'))) {
        //Define estructura de salida: Datos faltantes
        return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Faltantes', 'Usuario y contraseña son requeridos', 400);
    }

    //Recuperar parámetros de petición entrada
    $user = $request->getParam('user');
    $pass = md5($request->getParam('password'));

    //Genera sentencia para consultar usuario
    $sqlUsuario="SELECT u.id_usuario FROM sys_users u WHERE u.contrasena='{$pass}' and u.login='{$user}' limit 1";

    //Ejecuta consulta a bd
    try {
      $db = new db();
      $db = $db->conectDB();
      $resultadoUsuario = $db->query($sqlUsuario);
      //Generación de token
      if ($resultadoUsuario->rowCount()>0) {
          //Variables para insert
          $usuario = $resultadoUsuario->fetch();
          $tk = gen_uuid();
          //Genera sentencia recuperar tiem_value
          $sqlAPIConfig="SELECT value FROM api_config c WHERE c.key='token' and name='time_value' limit 1";
          $resultadoConfig = $db->query($sqlAPIConfig);
          $time_value = $resultadoConfig->fetch();

          //Insert token
          $sqlInsert = "INSERT INTO api_token
                        (id_user, token, created_in, expired_in)
                        VALUES (
                          '{$usuario['id_usuario']}',
                          '{$tk}',
                          now(),
                          TIMESTAMPADD(SECOND,{$time_value['value']},NOW())
                        );";
          $db->exec($sqlInsert);
          //Regresa token
          $resultado = [];
          $resultado['access_token']=$tk;
          $resultado['expires_in']=$time_value['value'];
          $resultado['token_type']='bearer';
          return $rs->successMessage($request->getParsedBody(),$response, $resultado);
      }
      //Datos no validos
      else{
          //Define estructura de salida: Usuario/Contraseña no valido
          return $rs->errorMessage($request->getParsedBody(),$response, 'Datos_Invalidos', 'Usuario y/o contraseña no válidos', 400);
      }
      $resultado = null;
      $db = null;
    } catch (PDOException $e) {
      return '{"error":"'.$e->getMessage().'"}';
    }

});

//Función UUID
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

?>
