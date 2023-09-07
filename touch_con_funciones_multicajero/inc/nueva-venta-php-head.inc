<?php

	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	
	$productos = array();
	$subtotal = 0.0;
	$es_paquete = false;
	$es_pedido = false;
	$folio = null;
	$descuento = 0;
	
	$sql = "SELECT  IF(ISNULL(descuento), 0, descuento) AS descuento FROM sys_sucursales " .
		"WHERE id_sucursal = '{$user_sucursal}' " .
		"LIMIT 1 ";
	
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	if ($dr = mysql_fetch_assoc($res)) {
		$descuento = $dr["descuento"];
	} mysql_free_result($res);
?>