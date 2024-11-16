<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: emails
* Path: /emails
* Método: GET
* Descripción: Recupera correos para notificación
*/
$app->get('/emails', function (Request $request, Response $response){
    //Init
    $db = new db();
    $db = $db->conectDB();
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
        //return '{"error":"'.$e->getMessage().'"}';
        return $rs->errorMessage($request->getParsedBody(),$response, 'Error_CL', $e->getMessage(), 500);

      }
    }

    //Define estructura salida
    $emails = [];

    //Ejecuta consulta a bd
    try {
      $db = new db();
      $db = $db->conectDB();
      //Genera sentencia recuperar correos
      $sqlAPIConfig="SELECT value FROM api_config c WHERE c.key='notification' and name='email'";
      foreach ($db->query($sqlAPIConfig) as $row) {
          $emails[]= $row['value'];
      }
      $db = null;
      return $rs->successMessage($request->getParsedBody(),$response, $emails);

    } catch (PDOException $e) {
      return $rs->errorMessage($request->getParsedBody(),$response, 'Error_CL', $e->getMessage(), 500);

    }

});

?>
