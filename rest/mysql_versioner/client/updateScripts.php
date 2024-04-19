<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: obtener_devoluciones
* Path: /obtener_devoluciones
* Método: POST
* Descripción: Recupera y envia las devoluciones que no se han sincronizado
*/
//die( 'here' );
$app->post('/updateScripts', function (Request $request, Response $response){
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( '../../code/especiales/development/versionador_sql/ajax/scriptVersioner.php' ) ){
    die( 'No se incluyó libereria de versionamiento' );
  }
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }
  $sV = new scriptVersioner( $link );
  $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );
  $req = array();
  $update = array();
  //obtener configuracion y ultimo sscript descargado
  $req['versioner_config'] = $sV->getVersionerConfig();
//return json_encode( $req['versioner_config'] );
  $path = trim ( $req['versioner_config']['server_path'] );
  $branch_name = trim ( $req['versioner_config']['branch_name'] );
  $branch_id = trim ( $req['versioner_config']['current_branch_id'] );
  //die( $path );
  //die( $sV->executeScript( ) );

  //$req["returns"] = $returnsSynchronization->getSynchronizationReturns( -1, $movements_limit );
  //buscarCambios en repositorio central
  $post_data = json_encode( $req );
  //return $post_data;
  /*$sql = "SELECT 
            TRIM(value) AS path
          FROM api_config WHERE name = 'path'";
  $stm = $link->query( $sql ) or die( "Error al consultar path de api : {$link->error}" );
  //die( $sql );
  $config_row = $stm->fetch_assoc();*/
  $api_path = $path . "/rest/mysql_versioner/getScripts";
  //die( $api_path );
  $result_1 = $SynchronizationManagmentLog->sendPetition( "{$api_path}", $post_data );
  $result = json_decode( $result_1 );//decodifica respuesta
//return $result_1; 
//return $branch_name;
  if( $result == '' || $result == null ){  
    if( $result_1 == '' || $result_1 == null ){
      $result_1 = "Posiblemente no hay conexion con el servidor de Linea";
    }
    return json_encode( array( "response" => "Respuesta Erronea : {$result_1}" ) );
  }
  //guarda y ejecuta cambios
  if( $result->pending_scripts != '' && $result->pending_scripts != null ){ 
    if( trim( $result->pending_scripts ) == 'No hay actualizaciones por descargar!' ){
      return json_encode( array( "response"=>"No hay actualizaciones por descargar!" ) );
    }else{
      $update = $sV->updateDatabase( $result->pending_scripts, $branch_name, $branch_id );
    }
    //return json_encode($result->pending_scripts);
  }
  //die( $result->pending_scripts );
 
  return json_encode( array( "response"=>"Scripts Actualizados", "scripts_results"=>$update) );
});

?>
