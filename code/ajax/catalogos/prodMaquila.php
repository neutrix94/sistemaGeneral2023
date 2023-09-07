<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	$sql="SELECT
	      d.id_producto_ordigen,
	      p.nombre,
	      cantidad
	      FROM ec_productos_detalle d
	      JOIN ec_productos p ON d.id_producto_ordigen = p.id_productos
	      WHERE d.id_producto=$id";
	$res=mysql_query($sql);
	if(!$res)
	{
		//mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}
	
	$num=mysql_num_rows($res);
	echo "exito";
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		echo "|".$row[0]."~".utf8_encode($row[1])."~".$row[2];
	}
	
?>