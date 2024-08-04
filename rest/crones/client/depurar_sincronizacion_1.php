<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
//ini_set('max_execution_time', 1);
/*
* Endpoint: depurar_sincronizacion 
* Path: /depurar_sincronizacion
* Método: POST
* Descripción: Depura registros de sincronizacion ( tablas de sincronizacion )
* Version 1.1 Para depurar sincronizacion ( 2024-08-03 )
*/
$app->post('/depurar_sincronizacion', function (Request $request, Response $response){//die("here");

  if ( ! include( '../../conexionMysqli.php' ) ){
    die( 'no se incluyó conexion' );
  }
  $is_complete = false;
  if( $request->getParam('is_complete') != null && $request->getParam('is_complete') != false  ){
      $is_complete = true;
  }
//consulta el tipo de sistema y path de linea
  $sql = "SELECT 
            id_sucursal,
            ( SELECT value FROM api_config WHERE name = 'path' ) AS api_path
          FROM sys_sucursales WHERE acceso = 1";
  $stm = $link->query( $sql ) or die( "Error al consultar configuraciones del sistema : {$link->error}" );
  $row = $stm->fetch_assoc();
  $system_store = $row['id_sucursal'];
  $api_path = $row['api_path'];
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
  if( $is_complete ){
    $minutos_antiguedad = 0;
  }
  $sql = "SELECT NOW() AS fecha_hora_actual, DATE_SUB(NOW(), INTERVAL {$minutos_antiguedad} MINUTE) AS fecha_hora_modificada";
  $stm = $link->query( $sql ) or die( "Error al consultar fecha y hora para eliminar registros de sincronizacion : {$sql} : {$link->error}" );
  $row = $stm->fetch_assoc();
  $fecha_antiguedad = $row['fecha_hora_modificada'];
  $peticiones_pendientes = "";

  $link->autocommit( false );//inicio de trasaccion
//elimina registros de sincronizacion
    $sql = "DELETE FROM sys_sincronizacion_registros WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";  //AND fecha <= '{$limit_date} 23:59:59'
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros sr
            ON sp.folio_unico = sr.folio_unico_peticion
            WHERE sr.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de devoluciones
    $sql = "DELETE FROM sys_sincronizacion_devoluciones WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_devoluciones : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_devoluciones sd
            ON sp.folio_unico = sd.folio_unico_peticion
            WHERE sd.id_status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de ventas pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de movimientos de almacen
    $sql = "DELETE FROM sys_sincronizacion_movimientos_almacen WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_movimientos_almacen : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_movimientos_almacen sa
            ON sp.folio_unico = sa.folio_unico_peticion
            WHERE sa.id_status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de movimientos de almacen pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de movimientos de almacen a nivel proveedor producto
    $sql = "DELETE FROM sys_sincronizacion_movimientos_proveedor_producto WHERE id_status_sincronizacion = 3";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_movimientos_proveedor_producto : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_movimientos_proveedor_producto smpp
            ON sp.folio_unico = smpp.folio_unico_peticion
            WHERE smpp.id_status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de movimientos de almacen a nivel proveedor producto pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de facturación
    $sql = "DELETE FROM sys_sincronizacion_registros_facturacion WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_facturacion : {$link->error}" );
    /*$sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros_facturacion srf
            ON sp.folio_unico = srf.folio_unico_peticion
            WHERE srf.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de facturación pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }*/
//elimina registros de sincronizacion de actualizaciones de movimientos de almacen
    $sql = "DELETE FROM sys_sincronizacion_registros_movimientos_almacen WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_movimientos_almacen : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros_movimientos_almacen srma
            ON sp.folio_unico = srma.folio_unico_peticion
            WHERE srma.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de actualización de movimientos de almacen pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de actualizaciones de movimientos de almacen proveedor producto
    $sql = "DELETE FROM sys_sincronizacion_registros_movimientos_proveedor_producto WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_movimientos_proveedor_producto : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros_movimientos_proveedor_producto srmpp
            ON sp.folio_unico = srmpp.folio_unico_peticion
            WHERE srmpp.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de actualización de movimientos de almacen proveedor producto pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de transferencias
    $sql = "DELETE FROM sys_sincronizacion_registros_transferencias WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_transferencias : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros_transferencias srt
            ON sp.folio_unico = srt.folio_unico_peticion
            WHERE srt.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros de sincronizacion de transferencias pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de ventas
    $sql = "DELETE FROM sys_sincronizacion_registros_ventas WHERE status_sincronizacion = 3 AND fecha <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_registros_ventas : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_registros_ventas srv
            ON sp.folio_unico = srv.folio_unico_peticion
            WHERE srv.status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros actualización de ventas pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de validacion de ventas
    $sql = "DELETE FROM sys_sincronizacion_validaciones_ventas WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_validaciones_ventas : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_validaciones_ventas svv
            ON sp.folio_unico = svv.folio_unico_peticion
            WHERE svv.id_status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros validación de ventas pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
//elimina registros de sincronizacion de ventas
    $sql = "DELETE FROM sys_sincronizacion_ventas WHERE id_status_sincronizacion = 3 AND fecha_alta <= '{$fecha_antiguedad}'";   
    $link->query( $sql ) or die( "Error al eliminar en sys_sincronizacion_ventas : {$link->error}" );
    $sql = "SELECT 
              folio_unico
            FROM sys_sincronizacion_peticion sp
            LEFT JOIN sys_sincronizacion_ventas sv
            ON sp.folio_unico = sv.folio_unico_peticion
            WHERE sv.id_status_sincronizacion < 3";
    $stm = $link->query( $sql ) or die( "Error al consultar registros de petición con registros actualización de ventas pendientes : {$sql} : {$link->error}" );
    while( $row = $stm->fetch_assoc() ){
      $peticiones_pendientes .= ( $peticiones_pendientes == "" ? "" : "," );
      $peticiones_pendientes .= "'{$row['folio_unico']}'";
    }
    
//elimina las cabeceras de peticiones
    $sql = "DELETE FROM sys_sincronizacion_peticion WHERE hora_comienzo <= '{$fecha_antiguedad}'";   
    if( $peticiones_pendientes != "" ){//excluye las peticiones con registros pendientes
      $sql .= " AND folio_unico NOT IN( {$peticiones_pendientes} )";
    }
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
  if( $system_store != -1 ){//envia peticion a linea
    $resp = "";
    $post_data = json_encode( array( "is_complete"=>$is_complete ) );
    $url = "{$api_path}/rest/crones/depurar_sincronizacion";
    $crl = curl_init( $url );
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLINFO_HEADER_OUT, true);
    curl_setopt($crl, CURLOPT_POST, true);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
    //curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
      curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
    curl_setopt($crl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'token: ' . $token)
    );
    $resp = curl_exec($crl);//envia peticion
    curl_close($crl);
    if( $logger_id ){
      $log_steep_id = $this->LOGGER->insertLoggerSteepRow( $logger_id, "Envia peticion a {$url}", $post_data );
    }
    //var_dump( $resp );
    //return $resp;
  }
//regresa respuesta
  die('ok');
  //return json_encode( array( "response" => "Ventas ok!" ) );
});

?>