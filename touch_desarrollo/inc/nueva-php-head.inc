<?php

	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	
	$productos = array();
	$subtotal = 0.0;
	$es_paquete = false;
	$es_pedido = false;
	$folio = null;	
	
	if ($id_pedido > 0) {
		$cs = "SELECT subtotal, iva, ieps, total, descuento, IF(ISNULL(folio_pedido), folio_nv, folio_pedido) AS folio, ISNULL(folio_nv) AS es_pedido FROM ec_pedidos " .
				"WHERE id_pedido = '{$id_pedido}' ";
		if ($rs = mysql_query($cs)) {
			if ($dr = mysql_fetch_assoc($rs)) {
				$subtotal = $dr["subtotal"];
				$es_paquete = $dr["descuento"] > 0;
				$es_pedido = $dr["es_pedido"];
				$folio = $dr["folio"];
			} mysql_free_result($rs);
		}
		
		$cs = "SELECT P.id_productos AS id_producto, P.nombre AS producto, PD.cantidad, PD.precio, PD.monto, IF(PD.precio = 0, 1, 0) AS es_regalo  FROM ec_productos P " .
			"INNER JOIN ec_pedidos_detalle PD ON PD.id_producto = P.id_productos " .
			"WHERE PD.id_pedido = '{$id_pedido}' ";
		if ($rs = mysql_query($cs)) {
			while ($dr = mysql_fetch_assoc($rs)) {
				array_push($productos, $dr);
			} mysql_free_result($rs);
		}		
	}
	
	$sql = "SELECT  IF(ISNULL(descuento), 0, descuento) AS descuento FROM sys_sucursales " .
		"WHERE id_sucursal = '{$user_sucursal}' " .
		"LIMIT 1 ";
	
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	if ($dr = mysql_fetch_assoc($res)) {
		$descuento = $dr["descuento"];
	} mysql_free_result($rs);
?>