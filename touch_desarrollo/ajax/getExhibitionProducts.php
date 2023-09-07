<?php
	include( '../../conectMin.php' );
	include( '../../conexionMysqli.php' );
	$key = trim( $_GET['txt'] );
	$p_p_used = explode(',', $_GET['p_p_used'] );
//consulta el almacen exhibicion de la sucursal
	$sql = "SELECT id_almacen AS warehouse_id FROM ec_almacen WHERE id_sucursal = {$sucursal_id} AND nombre LIKE '%exhibicion%'";
	$stm = $link->query( $sql ) or die( "error|Error al consultar el id del almacen exhibicion : {$link->error}" );
	if( $stm->num_rows <= 0 ){
		die( "exception|La sucursal no tiene almacen de exhibicion!" );
	}
	$row = $stm->fetch_assoc();
//busca si el producto es maquilado
	$sql = "SELECT
				p.id_productos AS product_id,
				IF( pd.cantidad IS NULL, 0, pd.cantidad) AS quantity,
				IF( pd.id_producto_ordigen IS NULL, -1, pd.id_producto_ordigen ) AS origin_product_id
			FROM ec_productos p
			LEFT JOIN ec_productos_detalle pd
			ON p.id_productos = pd.id_producto
			WHERE p.id_productos = {$_GET['product_id']}";
	$maquile_stm = $link->query( $sql ) or die( "Error al consultar si el producto es maquilado : {$this->link->error}" );
	$maquile_row = $maquile_stm->fetch_assoc();

//busca por proveedor producto ( codigo de barras )
	$sql = "SELECT
			pp.id_proveedor_producto AS product_provider_id,
			pp.clave_proveedor AS provider_clue,
			IF( {$maquile_row['origin_product_id']} = -1,
				ROUND( ipp.inventario ),
				( SELECT 
					ROUND( SUM( IF( id_inventario_proveedor_producto IS NULL, 0, inventario ) ) / {$maquile_row['quantity']} ) 
				FROM ec_inventario_proveedor_producto 
				WHERE id_producto = {$maquile_row['origin_product_id']}
				AND id_almacen = {$row['warehouse_id']}
				GROUP BY id_producto 
				)
			) AS inventory,
			p.es_maquilado AS is_maquiled
		FROM ec_proveedor_producto pp
		LEFT JOIN ec_productos p
		ON p.id_productos = pp.id_producto
		LEFT JOIN ec_inventario_proveedor_producto ipp
		ON ipp.id_proveedor_producto = pp.id_proveedor_producto
		AND ipp.id_almacen = '{$row['warehouse_id']}'
		WHERE ( ( pp.codigo_barras_pieza_1 = '{$key}' AND pp.codigo_barras_pieza_1 != '' )
			OR ( pp.codigo_barras_pieza_2 = '{$key}' AND pp.codigo_barras_pieza_2 != '' )
			OR ( pp.codigo_barras_pieza_3 = '{$key}' AND pp.codigo_barras_pieza_3 != '' )
			OR ( pp.codigo_barras_presentacion_cluces_1 = '{$key}' AND pp.codigo_barras_presentacion_cluces_1 != '' )
			OR ( pp.codigo_barras_presentacion_cluces_2 = '{$key}' AND pp.codigo_barras_presentacion_cluces_2 != '' )
			OR ( pp.codigo_barras_caja_1 = '{$key}' AND pp.codigo_barras_caja_1 != '' )
			OR ( pp.codigo_barras_caja_2 = '{$key}' AND pp.codigo_barras_caja_2 != '' )
			)
		AND pp.id_producto = {$_GET['product_id']}
		GROUP BY IF( {$maquile_row['origin_product_id']} = -1, pp.id_proveedor_producto, p.id_productos )
		ORDER BY ipp.inventario DESC";
			//die( $sql );
	$stm = $link->query( $sql ) or die( "error|Error al consultar coincidencias por codigos barras : {$link->error}" );
	if( $stm->num_rows <= 0 ){
		$condition = '';
		$key_array = explode( ' ', $key );
		foreach ($key_array as $key => $value) {
			$condition .= ( $condition == '' ? '' : ' AND' );
			$condition .= " p.nombre LIKE '%{$value}%'";
		}
	//busca por nombre
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					CONCAT( p.nombre, '<br>MODELO : <b>', pp.clave_proveedor, '</b>' ) AS product_description,
					IF( {$maquile_row['origin_product_id']} = -1,
						ROUND( ipp.inventario ),
						( SELECT 
							ROUND( SUM( IF( id_inventario_proveedor_producto IS NULL, 0, inventario ) ) / {$maquile_row['quantity']} ) 
						FROM ec_inventario_proveedor_producto 
						WHERE id_producto = {$maquile_row['origin_product_id']}
						AND id_almacen = {$row['warehouse_id']}
						GROUP BY id_producto 
						)
					) AS inventory,
					p.es_maquilado AS is_maquiled
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_proveedor_producto = pp.id_proveedor_producto
				AND ipp.id_almacen = '{$row['warehouse_id']}'
				WHERE ( p.id_productos = '{$key}' 
					OR p.orden_lista = '{$key}'
					OR pp.clave_proveedor = '{$key}'
					OR ( $condition ) 
				)
				AND pp.id_producto = {$_GET['product_id']}
				GROUP BY IF( {$maquile_row['origin_product_id']} = -1, pp.id_proveedor_producto, p.id_productos )
				ORDER BY ipp.inventario DESC";//die( $sql );
		$stm = $link->query( $sql ) or die( "error|Error al consultar coincidencias por nombre : {$link->error}" );
		//die( "exception|La sucursal no tiene almacen de exhibicion!" );
		$resp = "seeker|";
		if( $stm->num_rows <= 0 ){
			die( "No se encontraron coincidencias!" );
		}
			$c = 0;
			$valids = 0;
			while ( $row_name = $stm->fetch_assoc() ) {
				if( $_GET['p_p_used'] != '' ){
					for( $i = 0; $i < sizeof( $p_p_used ); $i++ ){
						$aux = ($i+1);
						//echo "{$p_p_used[$i]} == {$row['product_provider_id']}";
						if( $p_p_used[$i] == $row_name['product_provider_id'] ){
							$row_name['inventory'] = $row_name['inventory'] - $p_p_used[$aux];
						}
						$i++;
					}
				}
				if( $row_name['inventory'] > 0 ){
					$resp .= "<div class=\"group_card text-start\" id=\"response_seeker_{$c}\" onclick=\"setTemporalMovement( {$row_name['product_provider_id']}, {$row_name['inventory']}, 1, {$row_name['is_maquiled']} );\">";
						$resp .= "<p>{$row_name['product_description']} <br>INVENTARIO : {$row_name['inventory']} pzs</p>";
					$resp .= "</div>";
					$valids ++;
				}	
				$c ++;	
				//$resp .= "{$row_name['product_id']}|{$row_name['sale_detail_id']}";
			}
			if( $valids == 0 ){
				die( "Este producto no tiene inventario en el almacen exhibicion, si lo vas a vender anotalo en el campo de Bodega!" );
			}
			die( $resp );
	}else{
		//die( "here" );
		$results_number = $stm->num_rows;
		if( $results_number == 1 ){
			$row = $stm->fetch_assoc();
			if( $_GET['p_p_used'] != '' ){
				for( $i = 0; $i < sizeof( $p_p_used ); $i++ ){
					$aux = ($i+1);
					//echo "{$p_p_used[$i]} == {$row['product_provider_id']}";
					if( $p_p_used[$i] == $row['product_provider_id'] ){
						$row['inventory'] = $row['inventory'] - $p_p_used[$aux];
					}
					$i++;
				}
			}
			if( $row['inventory'] > 0 ){
				die( "ok|{$row['product_provider_id']}|{$row['inventory']}|1" );
			}
			if( $row['inventory'] <= 0 ){
				die( "Este producto ya no tiene inventario en exhibicion, si lo vas a vender anotalo en el campo de Bodega!" );
			}
		}else{
//valida numero de piezas
			while ( $row = $stm->fetch_assoc() ){
				if( $_GET['p_p_used'] != '' ){
					for( $i = 0; $i < sizeof( $p_p_used ); $i++ ){
						$aux = ($i+1);
						if( $p_p_used[$i] == $row['product_provider_id'] ){
							$row['inventory'] = $row['inventory'] - $p_p_used[$aux];
						}
						$i++;
					}
				}
				if( $row['inventory'] > 0 ){
					die( "ok|{$row['product_provider_id']}|{$row['inventory']}|1" );
				}
			}
		}
		die( "Este producto ya no tiene mas inventario en el almacen exhibicion, si vas a vender mas anotalo en el campo de Bodega!" );
	}
?>