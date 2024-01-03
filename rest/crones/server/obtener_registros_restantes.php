<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: obtener_registros_restantes
* Path: /obtener_registros_restantes
* Método: POST
* Descripción: Consulta parametros de sincronizacion
*/
$app->post('/obtener_registros_restantes', function (Request $request, Response $response){
  $store_id = $request->getParam( "store_id" );
  //die( "Store : {$origin_store_id}" );
  $resp = array();
//incluye librerias die('here');
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
/*consulta la sucursal del sistema
  $sql = "SELECT id_sucursal AS store_id FROM sys_sucursales WHERE acceso = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar la sucursal de acceso : {$link->error}" );
  $row = $stm->fetch_assoc();
  $store_id = $row['store_id'];*/
/*Subida*/
//registros de sincronizacion
  $sql = "( SELECT 'sys_sincronizacion_registros', COUNT( * ) FROM sys_sincronizacion_registros WHERE id_sucursal_destino = {$store_id} AND status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_ventas', COUNT( * ) FROM sys_sincronizacion_ventas WHERE id_sucursal_destino = {$store_id} AND id_status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_registros_ventas', COUNT( * ) FROM sys_sincronizacion_registros_ventas WHERE id_sucursal_destino = {$store_id} AND status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_devoluciones', COUNT( * ) FROM sys_sincronizacion_devoluciones WHERE id_sucursal_destino = {$store_id} AND id_status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_movimientos_almacen', COUNT( * ) FROM sys_sincronizacion_movimientos_almacen WHERE id_sucursal_destino = {$store_id} AND id_status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_registros_movimientos_almacen', COUNT( * ) FROM sys_sincronizacion_registros_movimientos_almacen WHERE id_sucursal_destino = {$store_id} AND status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_validaciones_ventas', COUNT( * ) FROM sys_sincronizacion_validaciones_ventas WHERE id_sucursal_destino = {$store_id} AND id_status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_movimientos_proveedor_producto', COUNT( * ) FROM sys_sincronizacion_movimientos_proveedor_producto WHERE id_sucursal_destino = {$store_id} AND id_status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_registros_movimientos_proveedor_producto', COUNT( * ) FROM sys_sincronizacion_registros_movimientos_proveedor_producto WHERE id_sucursal_destino = {$store_id} AND status_sincronizacion = 1)
          UNION
          ( SELECT 'sys_sincronizacion_registros_transferencias', COUNT( * ) FROM sys_sincronizacion_registros_transferencias WHERE id_sucursal_destino = {$store_id} AND status_sincronizacion = 1)";
  $stm = $link->query( $sql ) or die( "Error al consultar registros de sincronizacion : {$link->error}" );
  $counter = 0;
  while ( $row = $stm->fetch_row() ) {
    if( $counter == 1 || $counter == 2 ){//ventas / reg ventas
      if( $counter == 1 ){
        $resp[1] = (int)$row[1];
      }else{
        $resp[1] += (int)$row[1];
      }
    }else if( $counter == 4 || $counter == 5 ){//mov alm / reg mov alm
      if( $counter == 4 ){
        $resp[4] = (int)$row[1];
      }else{
        $resp[4] += (int)$row[1];
      }
    }else if( $counter == 7 || $counter == 8 ){//mov alm / reg mov alm
      if( $counter == 7 ){
        $resp[7] = (int)$row[1];
      }else{
        $resp[7] += (int)$row[1];
      }
    }else{
      $resp[$counter] = (int)$row[1];      
    }
    //echo "{$row[0]} : {$row[1]}\n";
    $counter ++;
  }
  $resp = implode( ",", $resp );
  //var_dump( $resp );
  die( $resp );
});

?>