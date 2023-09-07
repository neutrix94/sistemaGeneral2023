<?php

    include("../../conectMin.php");
    
    extract($_GET);
    
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    
    $sql="SELECT
          IF(SUM(monto) IS NULL, 0, SUM(monto)),
          c.nombre,
          CONCAT(
                    '$',
                    FORMAT(
                            ABS(
                                IF(SUM(monto) IS NULL, 0, SUM(monto))-$totAct
                            ),
                            2
                    )
          ),
          ABS(IF(SUM(monto) IS NULL, 0, SUM(monto))-$totAct)
          FROM ec_pedidos_detalle pd
          JOIN ec_pedidos p ON pd.id_pedido = p.id_pedido
          JOIN ec_clientes c ON p.id_cliente = c.id_cliente
          WHERE p.id_pedido=$id_pedido";
          
    //die($sql);      
    
    $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    
    $row=mysql_fetch_row($res);
    if($row[0] > $totAct)
    {
        die("exito|$row[1]|$row[3]|$row[2]");
    }
    else
    {
        die("exito|Casa de las luces|$row[3]|$row[2]");
    }
        

?>