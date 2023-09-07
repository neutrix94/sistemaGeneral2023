<?php

    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    
    extract($_GET);
    
    
    //Buscamos el producto
    $sql="SELECT
          id_producto,
          cantidad AS canO,
          id_pedido
          FROM ec_pedidos_detalle
          WHERE id_pedido_detalle=$id_detalle";
          
    //die($sql);      
    $res = mysql_query($sql) or die("Error en:$sql");
    $row=mysql_fetch_assoc($res);
    
    extract($row);
    
    //Buscamos en los devueltos
    $sql="SELECT
          IF(SUM(cantidad) IS NULL, 0, SUM(cantidad))
          FROM ec_devolucion_detalle dd
          JOIN ec_devolucion d ON dd.id_devolucion = d.id_devolucion
          WHERE d.id_pedido=$id_pedido
          AND dd.id_producto=$id_producto";
          
    //die($sql);                        
    $res = mysql_query($sql) or die("Error en:$sql");
    $row=mysql_fetch_row($res);
    
    $canD=$row[0];
    
    $resul=(int)($canO-$canD)."";
    //die($canO." - ".$canD." = ".($canO-$canD).":".$resul);
    
    
    
    //die($resul);
    
    if($cantidad > ($resul))
        die("No puede devolver esta cantidad de productos");
    die("exito");

?>