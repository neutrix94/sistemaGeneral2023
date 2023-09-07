<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	mysql_query("BEGIN"); 
	
	//Buscamos la OC
	$sql="SELECT
	      id_estatus_oc
	      FROM ec_ordenes_compra
	      WHERE id_orden_compra=$id";
		  
	$res=mysql_query($sql);
	if(!$res)
	{
		mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}	
	
	if(mysql_num_rows($res) <= 0)
		die(utf8_encode("No se encontro la requisición"));
	
	$row=mysql_fetch_row($res);
	
	if($row[0] != 1)
		die("No es posible autorizar la requisición en su estatus actual.");
	
	//Actualizamos el estatus
	
	$sql="UPDATE ec_ordenes_compra SET id_estatus_oc=3 WHERE id_orden_compra=$id";
	if(!mysql_query($sql))
	{
		mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}	
	
	
	//Actualizamos precios
	$sql="UPDATE ec_oc_detalle
	      JOIN ec_productos ON ec_oc_detalle.id_producto = ec_productos.id_productos
	      SET
	      ec_oc_detalle.precio = ec_productos.precio_compra,
	      ec_oc_detalle.iva = ec_productos.precio_compra*(ec_productos.porc_iva/100),
	      ec_oc_detalle.ieps = ec_productos.precio_compra*(ec_productos.porc_ieps/100)
	      WHERE ec_oc_detalle.id_orden_compra=$id";
	
	
	if(!mysql_query($sql))
	{
		mysql_query("ROLLBACK"); 
		die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}
		
	mysql_query("COMMIT");
	
	die("Se ha actualizado correctamente el estatus de la requisición");  	  
	
?>