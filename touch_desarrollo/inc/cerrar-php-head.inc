<?php

	$id_pedido = $_GET["idp"];
	
	// Cargar tipos de pago 
	$tipos_pago = array();
	$cs = "SELECT id_tipo_pago AS id_tipo, nombre AS tipo FROM ec_tipos_pago WHERE id_sucursal = '{$user_sucursal}' ORDER BY NOT nombre LIKE '%efectivo%', nombre ";
	if ($rs = mysql_query($cs)) {
		while ($dr = mysql_fetch_assoc($rs)) {
			array_push($tipos_pago, $dr);
		} mysql_free_result($rs);
	}
	
	// Cargar datos de la venta
	$total = 0;
	$cs = "SELECT subtotal, iva, ieps, total FROM ec_pedidos WHERE id_pedido = '{$_GET["idp"]}' ";
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$total = $dr["total"];
		} mysql_free_result($rs);
	}
	
	// Cargar pagos actuales
	$pagos = array();
	$total_pagos = 0.0;
	$cs = "SELECT TP.id_tipo_pago AS id_tipo, TP.nombre AS tipo, PP.monto FROM ec_pedido_pagos PP " .
		"INNER JOIN ec_tipos_pago TP ON TP.id_tipo_pago = PP.id_tipo_pago " .
		"WHERE id_pedido = '{$_GET["idp"]}' ";
	if ($rs = mysql_query($cs)) {
		while ($dr = mysql_fetch_assoc($rs)) {
			$total_pagos += $dr["monto"];
			array_push($pagos, $dr);
		} mysql_free_result($rs);
	}
	
	if (!count($pagos) && count($tipos_pago)) {
		$total_pagos = $total;
		array_push($pagos, array("id_tipo" => $tipos_pago[0]["id_tipo"], "tipo" => $tipos_pago[0]["tipo"], "monto" => $total));
	}
?>