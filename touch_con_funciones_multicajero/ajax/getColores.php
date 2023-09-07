<?php 

    
    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
	
	extract($_GET);
	
	
	$sql="SELECT
	      p.orden_lista,
	      c.id_colores,
	      c.nombre,
	      p.nombre
	      FROM ec_productos p
	      LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal=$user_sucursal/*implementación Oscar 09.05.2018*/
	      JOIN ec_colores c ON p.id_color = c.id_colores
	      WHERE p.id_subtipo=(SELECT id_subtipo FROM ec_productos WHERE id_productos=$id_producto)
	      AND muestra_paleta = 0
		  AND c.id_colores <> -1
		  AND sp.estado_suc=1/*implementación Oscar 09.05.2018*/
	      GROUP BY c.id_colores";

	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripción:".mysql_error());
    echo "exito";
    $num=mysql_num_rows($res);
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        echo "|";
        echo $row[2]."~".$row[0];
    }  	
	
	
?>