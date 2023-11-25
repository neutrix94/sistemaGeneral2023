<?php
  class tokenValidation{
      public function validaToken($token){

        $db = new db();
        $db = $db->conectDB();
        $sqlToken = "SELECT token FROM api_token WHERE token='{$token}' AND expired_in>now();";
        $resultadoToken = $db->query($sqlToken);

        return $resultadoToken;
      }
  }
?>
