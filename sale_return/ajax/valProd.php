<?php 

    header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    
    extract($_GET);
  
  
    $sql="SELECT
          id_productos,
          nombre
          FROM ec_productos
          WHERE orden_lista = '$val'
          OR codigo_barras_1='$val'
          OR codigo_barras_2='$val'
          OR codigo_barras_3='$val'
          OR codigo_barras_4='$val'
          ORDER BY orden_lista";
          
    $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    if(mysql_num_rows($res) > 0)
    {
        $row=mysql_fetch_row($res);
    
        echo utf8_decode("exito|$row[0]|$row[1]");
    }
    else
         die("Producto no encontrado");              
    
    
?> 