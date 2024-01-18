<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: agrupacion_por_dia
* Path: /agrupacion_por_dia
* Método: POST
* Descripción: Agrupacion por dia desde CRON
*/
$app->post('/agrupacion_por_dia', function (Request $request, Response $response){
 
//incluye librerias die('here');
  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'No se incluyó conexion' );
  }
//bloqueo de APIS de sincronizacion
  $sql = "UPDATE sys_configuracion_sistema SET bloquear_apis_sincronizacion = 1";
  $stm = $link->query( $sql ) or die( "Error al bloquear APIS : {$link->error}" );
//elimina triggers cabeceras movimientos almacen
  $sql = "DROP TRIGGER IF EXISTS insertaMovimientoAlmacen";
  $stm = $link->query( $sql ) or die( "Error al eliminar insertaMovimientoAlmacen : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS actualizaMovimientoAlmacen";
  $stm = $link->query( $sql ) or die( "Error al eliminar actualizaMovimientoAlmacen : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS eliminaMovimientoAlmacen";
  $stm = $link->query( $sql ) or die( "Error al eliminar eliminaMovimientoAlmacen : {$link->error}" );

//elimina triggers detalles movimientos almacen
  $sql = "DROP TRIGGER IF EXISTS insertaMovimientoAlmacenDetalle";
  $stm = $link->query( $sql ) or die( "Error al eliminar insertaMovimientoAlmacenDetalle : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS actualizaMovimientoAlmacenDetalle";
  $stm = $link->query( $sql ) or die( "Error al eliminar actualizaMovimientoAlmacenDetalle : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS eliminaMovimientoAlmacenDetalle";
  $stm = $link->query( $sql ) or die( "Error al eliminar eliminaMovimientoAlmacenDetalle : {$link->error}" );

//elimina triggers cabeceras detalles movmiento almacen proveedor producto
  $sql = "DROP TRIGGER IF EXISTS insertaMovimientoDetalleProveedorProducto";
  $stm = $link->query( $sql ) or die( "Error al eliminar insertaMovimientoDetalleProveedorProducto : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS actualizaMovimientoDetalleProveedorProducto";
  $stm = $link->query( $sql ) or die( "Error al eliminar actualizaMovimientoDetalleProveedorProducto : {$link->error}" );
  $sql = "DROP TRIGGER IF EXISTS eliminaMovimientoDetalleProveedorProducto";
  $stm = $link->query( $sql ) or die( "Error al eliminar eliminaMovimientoDetalleProveedorProducto : {$link->error}" );

//consulta los dias de intervalo
  $sql = "SELECT minimo_dias_agrupacion_movimientos_dia_cron FROM sys_configuracion_sistema WHERE id_configuracion_sistema = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar los dias de intervalo de agrupacion por dia en el CRON : {$link->error}" );
  $row = $stm->fetch_assoc();
  $interval_days = $row['minimo_dias_agrupacion_movimientos_dia_cron'];
  //die( "Intervalo : {$interval_days}" );
//ejecuta el procedure por dia proveedor producto
  $sql = "CALL parametrosAgrupaMovimientosAlmacenProveedorProductoPorDiaCRON( 2, {$interval_days} )";
  $stm = $link->query( $sql ) or die( "Error al llamar parametrosAgrupaMovimientosAlmacenProveedorProductoPorDiaCRON : {$link->error}" );
  
//ejecuta el procedure por dia producto
  $sql = "CALL parametrosAgrupaMovimientosAlmacenPorDiaCron( {$interval_days} )";
  $stm = $link->query( $sql ) or die( "Error al llamar parametrosAgrupaMovimientosAlmacenPorDiaCron : {$link->error}" );


//desbloqueo de APIS de sincronizacion
  $sql = "UPDATE sys_configuracion_sistema SET bloquear_apis_sincronizacion = 0";
  $stm = $link->query( $sql ) or die( "Error al bloquear APIS : {$link->error}" );

  return 'ok';
  die( 'ok' );
});

?>