<?php

    include("../../conectMin.php");
    extract($_GET);
    
    if($tipo == 1)
    {
        
    
        $sql="SELECT 
              id_productos
              FROM ec_productos
              WHERE orden_lista < (SELECT orden_lista FROM ec_productos WHERE id_productos=$id_producto)
              ORDER BY orden_lista DESC
              LIMIT 1";
              
        $res=mysql_query($sql) or die(mysql_error());
        
        if(mysql_num_rows($res) <= 0)
            die("NO");
        else
        {
            $row=mysql_fetch_row($res);
            die("exito|".base64_encode($row[0]));
        }        
    }
    
    else if($tipo == 2)
    {
         $sql="SELECT 
              id_productos
              FROM ec_productos
              WHERE orden_lista > (SELECT orden_lista FROM ec_productos WHERE id_productos=$id_producto)
              ORDER BY orden_lista
              LIMIT 1";
              
        $res=mysql_query($sql) or die(mysql_error());
        
        if(mysql_num_rows($res) <= 0)
            die("NO");
        else
        {
            $row=mysql_fetch_row($res);
            die("exito|".base64_encode($row[0]));
        }       
    }
?>