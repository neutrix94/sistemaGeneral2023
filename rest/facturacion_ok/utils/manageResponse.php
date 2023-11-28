<?php
class manageResponse{
  function errorMessage($request=null, $response, $error, $description, $code, $inserts = null){
      $resultadoError = [];
      $resultadoError['erorr']=$error;
      $resultadoError['description']=$description;
      $this->insertLog($request,$resultadoError);
      return $response->withStatus($code)
             ->withHeader('Content-Type', 'application/json')
             ->write(json_encode($resultadoError));
  }

  function successMessage($request=null, $response=null, $dataResult= null){
      $resultado = [];
      $resultado['status']='OK';
      $resultado['result']=$dataResult;
      $this->insertLog($request,$resultado);
      return $response->withStatus(200)
             ->withHeader('Content-Type', 'application/json')
             ->write(json_encode($resultado));
  }

  function insertLog($request, $response){
      $db = new db();
      $db = $db->conectDB();
      try {
        //1.- Insert log
        $insertLog = "
          INSERT INTO api_log (token,created_in,request,status,input,output)
          VALUES (:token,now(),:request,:status,:input,:output);
        ";
        $insertStmt = $db->prepare($insertLog);
        //Ejecuta insert
        $insertStmt->execute(array(
          //Valores Magento
          "token"=>'',
          "request"=>'',
          "status"=>1,
          "input"=>json_encode($request),
          "output"=>json_encode($response)
        ));
        //Recupera id_pedido
        $id_log = $db->lastInsertId();
      }catch(PDOExecption $e) {
          error_log($e->getMessage());
      }
  }
}
?>
