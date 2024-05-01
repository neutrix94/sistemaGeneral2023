<?php
  class tokenValidation{
      public function validaToken($token){

        $db = new db();
        $db = $db->conectDB();
        $sqlToken = "SELECT token FROM api_token WHERE token='{$token}' AND expired_in>now();";
        $resultadoToken = $db->query($sqlToken);

        return $resultadoToken;
      }

      public function verificaExistenciaToken( $token ){
        $db = new db();
        $db = $db->conectDB();
        $sqlToken = "SELECT token FROM api_token WHERE token='{$token}'";
        $resultadoToken = $db->query($sqlToken);
        if( $resultadoToken->rowCount() == 0 ){
          return $resultadoToken;
        }else{
          $valida_caducidad = $this->validaToken($token);
          if( $valida_caducidad->rowCount() == 0 ){
            return $this->refrescar_token( $token, $db );
          }else{
            return $valida_caducidad;
          }
        }
      }

      public function refrescar_token( $token, $db ){
        $sql = "UPDATE api_token SET expired_in = ( select TIMESTAMPADD(SECOND,3600,NOW()) ) WHERE token = '{$token}'";
        $stm = $db->query($sqlToken);
        if( !$stm ){
          return "Error al renovar token : {$db->errorInfo()}";
        }else{
          return $this->validaToken($token);
        }
      }
  }
?>
