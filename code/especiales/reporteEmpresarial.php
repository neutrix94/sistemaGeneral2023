<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");
    
    //Conseguimos las ventas del mes
    
    $sql="SELECT
          SUM(monto)
          FROM ec_pedido_pagos
          WHERE fecha >= '".date("Y")."-".date("m")."-"."01'
          AND fecha <= '".date("Y")."-".date("m")."-"."31'";
     $res=mysql_query($sql);
    if(!$res)     
    {
        mysql_query("ROLLBACK");
        Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
    }
    $row=mysql_fetch_row($res); 
    //echo $row[0];
    $smarty->assign("ventasmes", $row[0]);
    
    //Gastos del mes
    
    $sql="SELECT
          SUM(monto)
          FROM
          (
            (
                SELECT
                SUM(monto) AS monto
                FROM ec_oc_pagos
                WHERE fecha >= '".date("Y")."-".date("m")."-"."01'
                AND fecha <= '".date("Y")."-".date("m")."-"."31'
            )    
            UNION
            (
                SELECT
                SUM(monto) AS monto
                FROM ec_gastos
                WHERE fecha >= '".date("Y")."-".date("m")."-"."01'
                AND fecha <= '".date("Y")."-".date("m")."-"."31'
            )    
          )aux  ";
     $res=mysql_query($sql);
    if(!$res)     
    {
        mysql_query("ROLLBACK");
        Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
    }
    $row=mysql_fetch_row($res); 
    //echo $row[0];
    $smarty->assign("egresosmes", $row[0]);
    
    
    //Valor Inventario
    $sql="SELECT
          FORMAT(SUM(
                (
                                SELECT
                                IF(SUM(md.cantidad_surtida*tm.afecta) IS NULL, 0, SUM(md.cantidad_surtida*tm.afecta))
                                FROM ec_movimiento_detalle md
                                JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen
                                JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
                                WHERE md.id_producto=p.id_productos
                                
                )*p.precio_compra
           ),2),
           FORMAT( SUM(
                (
                                SELECT
                                IF(SUM(md.cantidad_surtida*tm.afecta) IS NULL, 0, SUM(md.cantidad_surtida*tm.afecta))
                                FROM ec_movimiento_detalle md
                                JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen
                                JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
                                WHERE md.id_producto=p.id_productos
                                
                )*p.precio_venta
           ) ,2)    
           FROM ec_productos p";
    $res=mysql_query($sql);
    if(!$res)     
    {
        mysql_query("ROLLBACK");
        Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
    }
    $row=mysql_fetch_row($res); 
    //echo $row[0];
    $smarty->assign("valorinventario", $row[0]);
    $smarty->assign("valorinventariov", $row[1]);
    
    $sql="SELECT
          id_sucursal,
          nombre
          FROM sys_sucursales
          ORDER BY nombre";
          
    $res=mysql_query($sql);
    if(!$res)     
    {
        mysql_query("ROLLBACK");
        Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
    }   
    $num=mysql_num_rows($res);
    $sucval=array(-1);
    $suctxt=array('-Todas-');
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        array_push($sucval, $row[0]);
        array_push($suctxt, $row[1]);
    }
    $smarty->assign("sucval", $sucval);
    $smarty->assign("suctxt", $suctxt);
          
    $smarty->assign("multi", 1);
    
    
    $smarty->display("especiales/reporteEmpresarial.tpl");
    
?>