<?php

	header("Content-Type: text/plain;charset=utf-8");

	include("../../conectMin.php");
	
	extract($_GET);

	$sql = "SELECT
	        IF(ISNULL(PD.precio_oferta), 0, PD.precio_oferta) AS precio_oferta,
	        IF(ISNULL(PD.precio_venta), 0, PD.precio_venta) AS precio_venta,
	        IF(ISNULL(P.precio_venta), 0, P.precio_venta) AS precio_default,
	        P.nombre FROM ec_productos P  " .
		"left outer join ec_precios_detalle PD ON PD.id_producto = P.id_productos " .
		"left outer join sys_sucursales S ON S.id_precio = PD.id_precio AND S.id_sucursal = '{$user_sucursal}' " .
		"WHERE P.id_productos = '{$idp}' ";
	
    //echo $sql; exit;
	
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	if ($dr = mysql_fetch_assoc($res))
	{
		$precio = number_format($dr["precio_oferta"] > 0 ? $dr["precio_oferta"] : ($dr["precio_venta"] > 0 ? $dr["precio_venta"] : $dr["precio_default"]), 2);
		//if ($dr["precio_oferta"] == 0)
		echo "OK|PRECIO:{$precio}|NOMBRE:{$dr["nombre"]}";
	    exit;
	}
	mysql_free_result($rs);
	
	echo "ERROR";
?>