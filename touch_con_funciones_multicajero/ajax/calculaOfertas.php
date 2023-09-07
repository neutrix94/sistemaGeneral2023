<?php
	#header("HTTP/1.0 404 Not Found"); exit;
	header("Content-Type: text/plain;charset=utf-8");
	
	include("../../conectMin.php");
	
	$es_regalo = isset($_GET["re"]) ? $_GET["re"] : "0";
	$es_pedido = isset($_GET["pe"]) ? $_GET["pe"] : "0";
	$es_paquete = isset($_GET["pa"]) ? $_GET["pa"] : "0";
	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	$folio = "0";
	$nitems = $_GET["nitems"];
	$descuento = 0;
	$prefijo = "";
	$es_nuevo_registro = !($id_pedido > 0);
	$tipo_folio = $es_pedido ? "pedido" : "nv";
	
	try {
		mysql_query("BEGIN");
		
		// Si el ID del pedido = 0, generar nuevo ID
		if ($es_nuevo_registro) {
			$cs = "SELECT IF(ISNULL(MAX(id_pedido)), 1, MAX(id_pedido)+1) AS maxid FROM ec_pedidos WHERE id_pedido > 0 ";
			if ($rs = mysql_query($cs)) {
				if ($dr = mysql_fetch_assoc($rs)) {
					$id_pedido = $dr["maxid"];
				} mysql_free_result($rs);
			} else {
				throw new Exception ("No se consiguió un nuevo ID de pedido.");
			}
		}
		
		// Conseguir algunos datos de la sucursal
		$cs = "SELECT descuento, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
		if ($rs = mysql_query($cs)) {
			if ($dr = mysql_fetch_assoc($rs)) {
				$descuento = $es_paquete ? $dr["descuento"] : 0;
				$prefijo = $dr["prefijo"];
			} mysql_free_result($rs);
		} else {
			throw new Exception ("No se consiguió descuento/prefijo de la sucursal.");
		}
		
		// Conseguir un nuevo folio para la venta/pedido
		$cs = "SELECT CONCAT('{$prefijo}', IF(ISNULL(MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))), 1, MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))+1)) AS folio " .
			"FROM ec_pedidos " .
			"WHERE REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') REGEXP ('[0-9]') " .
			"AND id_sucursal='{$user_sucursal}' " .
			"AND id_pedido <> '{$_GET["idp"]}' ";
		if ($rs = mysql_query($cs)) {
			if ($dr = mysql_fetch_assoc($rs)) {
				$folio = $dr["folio"];
			} mysql_free_result($rs);
		} else {
			throw new Exception ("No se consiguió un nuevo folio ({$tipo_folio}).");
		}
		
		# Guardar el encabezado
		if ($es_nuevo_registro) $cs = "INSERT INTO ec_pedidos SET id_pedido = '{$id_pedido}', id_cliente = '-1', id_estatus = '2', id_moneda = '1', fecha_alta = NOW(), id_direccion = '-1', id_razon_social = '-1', subtotal = '0', iva = '0', ieps = '0', total = '0', pagado = '0', surtido = '0', enviado = '0', id_sucursal = '{$user_sucursal}', id_usuario = '{$user_id}', ";
		else $cs = "UPDATE ec_pedidos SET ";
		
		$cs .= "folio_pedido = " . ($es_pedido ? "'{$folio}'" : "NULL") . ", " .
			"folio_nv = " . ($es_pedido ? "NULL" : "'{$folio}'") . ", " .
			"fue_cot = '0', " .
			"facturado = '0', " .
			"id_tipo_envio = '1', " .
			"descuento = '0', " .
			"id_razon_factura = NULL ";
		
		if (!$es_nuevo_registro) $cs .= "WHERE id_pedido = '{$id_pedido}' ";
			
		//if (!mysql_query($cs)) throw new Exception("Imposible almacenar este registro (pedidos). " . mysql_error());
		
		# Conseguir el IVA del sistema
		$iva = 0.16;
		$ieps = 0.30;
		$cs = "SELECT iva/100 AS iva, ieps/100 AS ieps FROM ec_conf_gral ";
		if ($rs = mysql_query($cs)) {
			if ($dr = mysql_fetch_assoc($rs)) {
				$iva = $dr["iva"];
				$ieps = $dr["ieps"];
			} mysql_free_result($rs);
		} else {
			throw new Exception ("No se consiguió el parámetro IVA/IEPS del sistema.");
		}
		
		$pedido_subtotal = 0.0;
		$pedido_iva = 0.0;
		$pedido_ieps = 0.0;
		$pedido_total = 0.0;
		$pedido_descuento = 0.0;
		
		# Guardar el detalle
		for ($ix=0; $ix<$nitems; ++$ix) {
			$existe_detalle = false;
			$id_detalle = "0";
			
			$cs = "SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE id_pedido = '{$id_pedido}' AND id_producto = '{$_GET["idp{$ix}"]}' ";
			if ($rs = mysql_query($cs)) {
				if ($dr = mysql_fetch_assoc($rs)) {
					$existe_detalle = true;
					$id_detalle = $dr["id_pedido_detalle"];
				} mysql_free_result($rs);
			} else {
				throw new Exception ("No se consiguió el detalle del pedido (pedido: {$id_pedido}, producto: {$_GET["idp{$ix}"]}).");
			}
			
			if (!$existe_detalle) {
				$cs = "SELECT IF(ISNULL(MAX(id_pedido_detalle)), 1, MAX(id_pedido_detalle)+1) AS maxid FROM ec_pedidos_detalle ";
				if ($rs = mysql_query($cs)) {
					if ($dr = mysql_fetch_assoc($rs)) {
						$id_detalle = $dr["maxid"];
					} mysql_free_result($rs);
				} else {
					throw new Exception ("No se consiguió un nuevo ID para el detalle del pedido.");
				}
			}
			
			#Consigue el precio
			$precio = 0.0;
			$cs = "SELECT IF(ISNULL(PD.precio_oferta), 0, PD.precio_oferta) AS precio_oferta, IF(ISNULL(PD.precio_venta), 0, PD.precio_venta) AS precio_venta, IF(ISNULL(P.precio_venta), 0, P.precio_venta) AS precio_default FROM ec_productos P  " .
				"left outer join ec_precios_detalle PD ON PD.id_producto = P.id_productos " .
				"left outer join sys_sucursales S ON S.id_precio = PD.id_precio AND S.id_sucursal = '{$user_sucursal}' " .
				"WHERE P.id_productos = '{$_GET["idp{$ix}"]}' ";
			
			if ($rs = mysql_query($cs)) {
				if ($dr = mysql_fetch_assoc($rs)) {
					$precio = number_format($dr["precio_oferta"] > 0 ? $dr["precio_oferta"] : ($dr["precio_venta"] > 0 ? $dr["precio_venta"] : $dr["precio_default"]), 2);
				} mysql_free_result($rs);
			} else {
				throw new Exception ("No se consiguió el precio de un producto del detalle del pedido.");
			}
			
			$precio_original = $precio;
			$precio_descuento = $precio * $descuento;
			$precio_final = $precio_original - $precio_descuento;
			
			$monto = $precio_final * $_GET["can{$ix}"];
			$monto_descuento = $precio_descuento * $_GET["can{$ix}"];
			
			$subtotal_iva = $monto * $iva;
			$subtotal_ieps = $monto * $ieps;
				
			$pedido_subtotal += $monto;
			$pedido_iva += $subtotal_iva;
			$pedido_ieps += $subtotal_ieps;
			$pedido_total += ($monto + $subtotal_iva);
			$pedido_descuento += $monto_descuento;
			
			# Guardar el registro del detalle 
			if (!$existe_detalle) $cs = "INSERT INTO ec_pedidos_detalle SET id_pedido_detalle = '{$id_detalle}', id_pedido = '{$id_pedido}', id_producto = '{$_GET["idp{$ix}"]}', cantidad_surtida = '0', ";
			else $cs = "UPDATE ec_pedidos_detalle SET ";
			
			$cs .= "cantidad = '{$_GET["can{$ix}"]}', " .
				"precio = '{$precio_final}', " .
				"monto = '{$monto}', " .
				"iva = '{$subtotal_iva}', " .
				"ieps = '{$subtotal_ieps}' ";
			
			if ($existe_detalle) $cs .= "WHERE id_pedido_detalle = '{$id_detalle}' ";
				
			//if (!mysql_query($cs)) throw new Exception("Imposible almacenar registro (detalle de pedido). " . mysql_error());			
		}
		
		# Actualizar los valores pendientes del encabezado
		$cs = "UPDATE ec_pedidos SET " .
			"subtotal = '{$pedido_subtotal}', " .
			"iva = '{$pedido_iva}', " .
			"ieps = '{$pedido_ieps}', " .
			"total = '{$pedido_total}', " .
			"descuento = '{$pedido_descuento}' " .
			"WHERE id_pedido = '{$id_pedido}' ";
		
		if (!mysql_query($cs)) throw new Exception("Imposible actualizar la segunda parte del pedido. " . mysql_error());
		
		mysql_query("COMMIT");
		
		echo "OK|IDP:{$id_pedido}|FOLIO:{$folio}";
		
	} catch (Exception $e) {
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK");
		mysql_close();
		exit ();
	}
?>