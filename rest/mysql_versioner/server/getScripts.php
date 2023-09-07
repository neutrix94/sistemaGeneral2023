<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: getScripts
* Path: /getScripts
* Método: POST
* Descripción: Recupera y envia scripts
*/
//die( 'here' );
$app->post('/getScripts', function (Request $request, Response $response){
  $resp = array();
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  if ( ! include( '../../code/especiales/development/versionador_sql/ajax/scriptVersioner.php' ) ){
    die( 'No se incluyó libereria de versionamiento' );
  }/*
  if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
    die( "No se incluyó : SynchronizationManagmentLog.php" );
  }*/
  $branch = $request->getParam( "versioner_config" );
  //return 'here : '. $versioner['mysql_database'];
  $sV = new scriptVersioner( $link );
  $resp["pending_scripts"] = $sV->getBranchPendingScripts( $branch['branch_name'], $branch['last_script_id'] );
  if( $resp["pending_scripts"] == null ){
    $resp["pending_scripts"] = "No hay actualizaciones por descargar!";
  }
  return json_encode( $resp );
});

?>
