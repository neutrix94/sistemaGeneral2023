<?php

     header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    
    extract($_GET);
    
    
    $sql="SELECT autorizado, tipo_regalo, IF(tipo_regalo = 2, descuento, descuento) FROM ec_autorizacion WHERE id_autorizacion=$id AND tipo_regalo IS NOT NULL";
    
    //echo $sql;
    
    $res=mysql_query($sql);
    
    $row=mysql_fetch_row($res);
    
    if($row[0] == '1')
    {
        echo "SI|";
        
        if($row[1] == '1')
            echo "Se ha autorizado el regalo, el próximo artículo será gratis";
        if($row[1] == '2')
            echo "Se ha autorizado un descuento del $row[2]%";
        if($row[1] == '3')
            echo "Se ha autorizado un descuento de $ $row[2]";
        
        echo "|$row[1]|$row[2]";        
        
         die();
    }     
    else if($row[0] == '0')
        echo die("NO");

?>