<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: netPayResponse
* Path: /netPayResponse
* Método: POST
* Descripción: Recupera respuesta de netPay
*/

$app->get('/domain_test', function (Request $request, Response $response){
  //die( 'here_1' );
	if( ! include( '../../conexionMysqli.php' ) ){
		die( "Error al incluir libreria de conexion!" );
	}
//consulta la url del servidor local
	$sql = "SELECT
				dominio_sucursal AS store_domain
			FROM ec_configuracion_sucursal
			WHERE id_sucursal = ( SELECT id_sucursal FROM sys_sucursales WHERE acceso = 1 LIMIT 1 )";
	$stm = $link->query( $sql ) or die( "Error al consultar el dominio de la sucursal : {$link->error}" );
	$row = $stm->fetch_assoc();
	$url = "{$row['store_domain']}/rest/netPay/test";

	$crl = curl_init( $url );
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($crl, CURLINFO_HEADER_OUT, true);
	curl_setopt($crl, CURLOPT_POST, true);
	curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
	//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
	curl_setopt($crl, CURLOPT_HTTPHEADER, array(
	  'Content-Type: application/json',
	  'token: ' . $token)
	);
	$resp = curl_exec($crl);//envia peticion
	curl_close($crl);
	//die( 'here' );
	$resp = json_decode( $resp, true );
	//var_dump($resp);
	return $resp['response'];

  	$resp = array(
    	"code"=>"00",
    	"message"=>$url
  	);
  return json_encode( $resp );
});
?>
