<?php
	
	include('../../../conectMin.php');
	include('../../../conexionMysqli.php');

	$data = $_POST['file'];
	$folio = "IMPORTACION";
	$price = $_POST['list'];
	echo import_csv( $price, $data, $sucursal_id, $user_id, $link );

	function import_csv( $price, $data, $sucursal, $user_id, $link ){
		$resp = "";
		$header_id = insertHeader( $price, $sucursal, $user_id, $link );
		$products = explode('|', $data);
		foreach ($products as $count => $product) {
			$product = explode(',', $product);
			$resp .= ($count <= 0 ? "ok|{$header_id}|" : "|") . insertDetail( $header_id, $price, $product[0], $product[1], $link );
		}
		updateHeader( $header_id, $link );
		return $resp;
	}

//inserta detalle del pedido
	function insertDetail( $header_id, $price, $product, $quanity, $link ){
		$sql = "SELECT IF( precio_venta IS NULL, 0, precio_venta) FROM ec_precios_detalle WHERE id_producto = {$product} AND id_precio = {$price} LIMIT 1";
		$eje = $link->query( $sql ) or die( "Error al consultar precio del producto : {$link->error}" );
		$r = $eje->fetch_row();
		$price_value = ($r[0] == null ? 0 : $r[0]);
		$sql = "INSERT INTO ec_pedidos_detalle_back
					SET
					id_pedido = '{$header_id}',
					id_producto = '{$product}',
					cantidad_surtida = '0', ";
			$sql.="	cantidad = '{$quanity}',
					precio = $price_value,
					monto = {$quanity} * {$price_value},
					iva = '0',
					ieps = '0', 
					descuento='0',
					es_externo=0,
					id_precio='{$price}'";
		$eje = $link->query( $sql )or die( "Error al insertar detalle de venta : {$link->error}" );
		return getDetail( $link->insert_id, $link );
	}
	function getDetail( $key, $link ){
		$sql = "SELECT id_producto, cantidad, precio, monto FROM ec_pedidos_detalle_back WHERE id_pedido_detalle = '{$key}'";
		$eje = $link->query( $sql ) or die( "Error al obtener datos del detalle : {$link->error}");
		$r = $eje->fetch_row();
		return "{$key}~{$r[0]}~{$r[1]}~{$r[2]}~{$r[3]}";
	}
//inserta cabecera del pedido
	function insertHeader( $price, $sucursal, $user_id, $link ){
		//inserta cabecera
	$sql = "INSERT INTO ec_pedidos_back
				SET
				id_cliente = '1',
				id_estatus = '2',
				id_moneda = '1',
				fecha_alta = NOW(),
				id_direccion = '-1',
				id_razon_social = '-1',
				direccion='',
				subtotal = '0',
				iva = '0',
				ieps = '0',
				total = '0',
				pagado = '0',
				surtido = '0',
				enviado = '0',
				id_sucursal = '{$sucursal}',
				id_usuario = '{$user_id}', ";
		$sql .= "folio_nv = 'IMPORTACION',";
		
		$sql .=  "	fue_cot = '0',
				facturado = '0',
				id_tipo_envio = '1',
				descuento = '0',
				id_razon_factura = NULL, ";
		$sql .= "tipo_pedido={$price}";
		$eje = $link->query( $sql )or die( "Error al insertar cabecera de venta : {$link->error}" );
		return $link->insert_id;
	}
	function updateHeader( $header_id, $link ){
		$sql = "UPDATE ec_pedidos_back SET total = (SELECT SUM(monto) FROM ec_pedidos_detalle_back WHERE id_pedido = '{$header_id}' )";
		$eje = $link->query( $sql ) or die( "Error al actuaizar total de venta : {$link->error}" );
	}

	function deleteDetail( $detail_id, $link ){

	}

	function deleteHeader( $detail_id, $link ){

	}
?>