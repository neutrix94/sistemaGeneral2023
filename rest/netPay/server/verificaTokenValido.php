<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: verifica_token
* Path: /verifica_token
* Método: POST
* Descripción: Verifica validez de Token
*/

$app->post('/valida_token', function (Request $request, Response $response){
    
    $db = new db();
    $db = $db->conectDB();
    $rs = new manageResponse();
    $vt = new tokenValidation();
    //$Encrypt = new Encrypt();

    //die("here");
//Valida token
    $token =  (empty($request->getHeader('Token'))) ? '' : implode(" ",$request->getHeader('Token'));
    //$token = $Encrypt->decryptText($token, 'CDLL2024');//desencripta token
    if (empty($token) || strlen($token)<36 ) {
      //Define estructura de salida: Token requerido
      return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Requerido', 'Se requiere el uso de un token', 400);
    }else{
      //Consulta vigencia
        try{
            $resultadoToken = $vt->verificaExistenciaToken($token);
        if ($resultadoToken->rowCount()==0) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'Token_Invalido', 'El token proporcionado no es válido', 400);
        }
        }catch (PDOException $e) {
            return $rs->errorMessage($request->getParsedBody(),$response, 'CL_Error', $e->getMessage(), 500);
        }
    }
//consulta el usuario al que corresponde el token
   // $db = $db->conectDB();
    include( '../../conexionMysqli.php' );
    $sqlToken = "SELECT id_user AS id_usuario FROM api_token WHERE token='{$token}'";
    $resultadoToken = $link->query($sqlToken) or die( "error : {$link->error}" );
    if( $link->error ){
        die( "Error al consultar usuario del token : {$link->error}" );
    }
    $row = $resultadoToken->fetch_assoc();
    return json_encode( array( "status"=>200, "message"=>"Token válido", "id_usuario"=>$row['id_usuario'] ) );
});
?>
