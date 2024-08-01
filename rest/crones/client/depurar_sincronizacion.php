<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: depurar_sincronizacion 
* Path: /depurar_sincronizacion
* Método: POST
* Descripción: Depura registros de sincronizacion ( tablas de sincronizacion )
*/
$app->post('/depurar_sincronizacion', function (Request $request, Response $response){//die("here");

  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
//consulta el intervalo de depuracion
  $sql = "SELECT 
          cs.dias_retardo_limpieza_sincronizacion
        FROM sys_sucursales s 
        LEFT JOIN ec_configuracion_sucursal cs
        ON s.id_sucursal = cs.id_sucursal
        WHERE s.acceso = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar sucursal de logueo : {$link->error}" );
  $row = $stm->fetch_assoc();
//consulta fecha para registros de log
  $sql = "SELECT DATE_FORMAT( date_add(NOW(), INTERVAL -{$row['dias_retardo_limpieza_sincronizacion']} DAY), '%Y-%m-%d' ) AS limit_date";
  $stm = $link->query( $sql ) or die( "Error al consultar rango de fecha para depurar registros : {$link->error}" );
  $row = $stm->fetch_assoc();
  $limit_date = $row['limit_date'];
//consulta el intervalo de depuracion
  $sql = "SELECT minutos_antiguedad_depuracion FROM sys_configuracion_sistema";
  $stm = $link->query( $sql ) or die( "Error al consultar la antigüedad para eliminar registros de sincronizacion : {$sql} : {$link->error}" );
  $row = $stm->fetch_assoc();
  $minutos_antiguedad = $row['minutos_antiguedad_depuracion'];
  $sql = "SELECT NOW() AS fecha_hora_actual, DATE_SUB(NOW(), INTERVAL {$minutos_antiguedad} MINUTE) AS fecha_hora_modificada";
  $stm = $link->query( $sql ) or die( "Error al consultar fecha y hora para eliminar registros de sincronizacion : {$sql} : {$link->error}" );
  $row = $stm->fetch_assoc();
  $fecha_antiguedad = $row['fecha_hora_modificada'];
  $link->autocommit( false );//inicio de trasaccion
//comienza a ejecutar consultas
    $sql = "DELETE FROM sys_sincronizacion_registros WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";  //AND fecha <= '{$limit_date} 23:59:59'
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_devoluciones WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_devoluciones : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_movimientos_almacen WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_movimientos_almacen : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_movimientos_proveedor_producto WHERE id_status_sincronizacion = 3";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_movimientos_proveedor_producto : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_registros_facturacion WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_facturacion : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_registros_movimientos_almacen WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_movimientos_almacen : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_registros_movimientos_proveedor_producto WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_movimientos_proveedor_producto : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_registros_transferencias WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_transferencias : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_registros_ventas WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_ventas : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_validaciones_ventas WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_validaciones_ventas : {$link->error}" );
    $sql = "DELETE FROM sys_sincronizacion_ventas WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_ventas : {$link->error}" );

    $sql = "DELETE FROM sys_sincronizacion_peticion WHERE hora_comienzo <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_ventas : {$link->error}" );

/*Eliminacion de registros de tablas de log*/
    $sql = "DELETE FROM LOG_sincronizaciones WHERE fecha_alta <= '{$fecha_antiguedad}'";
    $link->query( $sql ) or die( "Error al eliminar en LOG_sincronizaciones : {$link->error}" );
    $sql = "DELETE FROM LOG_sincronizacion_pasos WHERE fecha_alta <= '{$fecha_antiguedad}'";
    $link->query( $sql ) or die( "Error al eliminar en LOG_sincronizacion_pasos : {$link->error}" );
    $sql = "DELETE FROM LOG_sincronizacion_pasos_errores WHERE fecha_alta <= '{$fecha_antiguedad}'";
    $link->query( $sql ) or die( "Error al eliminar en LOG_sincronizacion_pasos_errores : {$link->error}" );
/**/
  $link->autocommit( true );//autoriza transaccion
//cierra conexion Mysql
  $link->close();
//regresa respuesta
  die('ok');
  //return json_encode( array( "response" => "Ventas ok!" ) );
});

?>

