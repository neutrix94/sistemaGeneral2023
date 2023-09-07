<?php 

    header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    mysql_set_charset("utf8");
    
    extract($_GET);
    $sql="SELECT
          p.id_productos,
          p.nombre
          FROM ec_productos p
          RIGHT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos 
          WHERE p.orden_lista = '$llave'
          AND sp.id_sucursal=$user_sucursal
          AND sp.estado_suc=1
          ORDER BY p.orden_lista";
          
    $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
    
    $num=mysql_num_rows($res);
    
    echo "exito";
    
    
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        echo "|";
        echo $row[0]."->".$row[1];
    }
                

?>