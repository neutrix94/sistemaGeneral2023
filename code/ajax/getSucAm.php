<?php

	include("../../conectMin.php");
	
	extract($_GET);
	
	
	
	$sql="	SELECT
			id_sucursal_origen,
			id_almacen_origen
			FROM ec_transferencias
			WHERE id_transferencia=$id_trans";
			
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	
	$row=mysql_fetch_row($res);
	
	echo "exito|$row[0]|$row[1]";		


?>