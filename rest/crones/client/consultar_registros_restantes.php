<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: consultar_registros_restantes
* Path: /consultar_registros_restantes
* Método: POST
* Descripción: Consulta parametros de sincronizacion
*/
$app->get('/consultar_registros_restantes', function (Request $request, Response $response){
  $origin_store_id = $request->getParam( "store_id" );
  //die( "Store : {$origin_store_id}" );
  $resp = array();
//archivo de conexion para sacar ruta local
  $local_path = "";
  $archivo_path = "../../conexion_inicial.txt";
  if(file_exists($archivo_path)){
    $file = fopen($archivo_path,"r");
    $line=fgets($file);
    fclose($file);
      $config=explode("<>",$line);
      $tmp=explode("~",$config[0]);
      $local_path = base64_decode( $tmp[1] );
      //$ruta_or = $tmp[0];
      //$ruta_des = $tmp[1];
  }else{
    die("No hay archivo de configuración!!!");
  }
  //die( "OR : {$local_path}" );
//incluye librerias die('here');
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
//consulta la sucursal del sistema
  $sql = "SELECT id_sucursal AS store_id FROM sys_sucursales WHERE acceso = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar la sucursal de acceso : {$link->error}" );
  $row = $stm->fetch_assoc();
  $store_id = $row['store_id'];
//consulta la url de la API
  $sql = "SELECT `value` AS api_path FROM `api_config` WHERE `name` = 'path'";
  $stm = $link->query( $sql ) or die( "Error al consultar el path del API : {$link->error}" );
  $row = $stm->fetch_assoc();
  $api_path = $row['api_path'];
//consulta registros locales
  $crl = curl_init( "localhost/{$local_path}/rest/crones/obtener_registros_restantes?store_id=-1" );
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
  $resp[0] = curl_exec($crl);//envia peticion
  curl_close($crl);

//consulta registros linea
  $crl = curl_init( "{$api_path}/rest/crones/obtener_registros_restantes?store_id={$store_id}" );
//die( "{$api_path}/rest/crones/obtener_registros_restantes?store_id=1" );
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
  $resp[1] = curl_exec($crl);//envia peticion
  curl_close($crl);
//consulta limite de registros por modulo
  $sql = "( SELECT 'sys_sincronizacion_registros', limite FROM sys_limites_sincronizacion WHERE tabla = 'sys_sincronizacion_registros' )
          UNION
          ( SELECT 'ec_pedidos', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_pedidos' )
          UNION
          ( SELECT 'ec_devolucion', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_devolucion' )
          UNION
          ( SELECT 'ec_movimiento_almacen', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_movimiento_almacen' )
          UNION
          ( SELECT 'ec_pedidos_validacion_usuarios', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_pedidos_validacion_usuarios' )
          UNION
          ( SELECT 'ec_movimiento_detalle_proveedor_producto', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_movimiento_detalle_proveedor_producto' )
          UNION
          ( SELECT 'ec_transferencias', limite FROM sys_limites_sincronizacion WHERE tabla = 'ec_transferencias' )";
  $stm = $link->query( $sql ) or die( "Error al consultar los limites de sinbcronizacion : {$link->error}" );
  $resp[2] = "";
  while ( $row = $stm->fetch_assoc() ) {
    $resp[2] .= ( $resp[2] == "" ? "" : "," );
    $resp[2] .= $row['limite'];
  }
  die( "{$resp[0]},"."{$resp[1]},{$api_path},{$resp[2]}" );
});

?>