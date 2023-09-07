<?php
	include( '../../../../../conexionMysqli.php' );

	$action = $_POST['fl'];
	$GLOBALS['global_counter'] = 0;
	switch ( $action ) {
		case 'searchTransfer':
			echo searchTransfer( $_POST['txt'], $link );
		break;
		case 'getTransfer':
			echo getTransferDetail( $_POST['transfer_id'], $link );
		break;
		case 'setTransfer':
			echo getTransferDetail( $_POST['transfer_id'], $link );
		break;

		case 'getProductsProviders':
			echo getProductsProviders( null, $link );
		break;
		
		default:
			return 'No Action...';
		break;
	}


	function searchTransfer( $clave, $link ){
		$resp = "";
		$sql = "SELECT 
					id_transferencia, 
					folio
				FROM ec_transferencias
				WHERE /*id_estado IN ( 2, 3 )
				AND */folio LIKE '%$clave%'";
		$exc = $link->query( $sql ) or die( "Error searchTransfer : " . $link->error);
		$c = 0;
		if( $exc->num_rows <= 0 ){
			return '<div class="row">No hay Resultados!</div>';
		}
		while ( $r = $exc->fetch_row() ) {
			$c ++;
			$resp .= "<div id=\"result_{$c}\" class=\"row result\" onclick=\"get_transfer( {$r[0]}, this );\">$r[1]</div>";
		}
		return $resp;
	}

	function getTransferDetail( $transfer_id, $link, $just_data = null ){
		$resp;
		static $product_c = 0;
		$sql = "SELECT
					tp.id_transferencia_producto,
					tp.id_producto_or,
					tp.id_proveedor_producto,
					p.nombre,
					SUM(tp.cantidad),
					SUM(tp.cantidad_entrada)
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p ON p.id_productos = tp.id_producto_or
				LEFT JOIN ec_proveedor_producto pp ON pp.id_proveedor_producto = tp.id_proveedor_producto
				WHERE tp.id_transferencia = '{$transfer_id}'
				GROUP BY tp.id_producto_or
				ORDER BY p.orden_lista ASC";
		$exc = $link->query( $sql ) or die( "Error getTransfer : " . $link->error);
		$c = 0;
		while ( $r = $exc->fetch_row() ) {
			$c ++;
			$product_c ++;
			$GLOBALS['global_counter'] ++;

			$background_color = '#FADE6B';
			if( $product_c % 2 != 0 ){
				$background_color = '#BDBBB4';
			}

			if( $just_data == null ){
				$resp .= "<tr 
							id=\"row_{$product_c}\"
							onclick=\"gft_row_focus( this );\" 
							product=\"{$r[1]}\"
							tabindex=\"{$GLOBALS['global_counter']}\"
							tabindex=\"{$GLOBALS['global_counter']}\"
							style=\"background-color : {$background_color};\"
							color=\"{$background_color}\"
							is_parent=\"1\"
						>
							<td class=\"\" id=\"1_{$GLOBALS['global_counter']}\">{$c}</td>
							<td class=\"no_visible\" id=\"2_{$GLOBALS['global_counter']}\">{$r[1]}</td>
							<td class=\"no_visible\" id=\"3_{$GLOBALS['global_counter']}\">{$r[2]}</td>
							<td id=\"4_{$GLOBALS['global_counter']}\">{$r[3]}</td>
							<td id=\"5_{$GLOBALS['global_counter']}\">{$r[4]}</td>
							<td 
								onclick=\"gft_build_field_editable( this )\" 
								gft_type=\"text\"
								id=\"6_{$GLOBALS['global_counter']}\"
							>{$r[5]}</td>
							<td id=\"7_{$GLOBALS['global_counter']}\"><button 
								class=\"btn\"
								onclick=\"extend_accordion( this, {$r[1]}, 1 )\">
									<i class=\"icon-down-open\"></i>
								</button>
							</td>
						</tr>";
				$resp .= getTransferProductDetail( $transfer_id, $r[1], $product_c, $link, $just_data );
			}else{
		//forma los detalles del producto
				$r[6] = getTransferProductDetail( $transfer_id, $r[1], $product_c, $link, $just_data );
				array_push($resp, $r);
			}
		}
		return $resp;
	}

	function getTransferProductDetail( $transfer_id, $product_id, $product_counter, $link, $just_data = null ){
		$resp;
		$product_detail_c = 0;
		$sql = "SELECT
					tp.id_transferencia_producto,
					tp.id_producto_or,
					tp.id_proveedor_producto,
					CONCAT( p.nombre, '( MODELO : ', pp.clave_proveedor, ' )'),
					tp.cantidad,
					tp.cantidad_entrada
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p ON p.id_productos = tp.id_producto_or
				LEFT JOIN ec_proveedor_producto pp ON pp.id_proveedor_producto = tp.id_proveedor_producto
				WHERE tp.id_transferencia = '{$transfer_id}'
				AND tp.id_producto_or = '{$product_id}'
				GROUP BY tp.id_transferencia_producto
				ORDER BY p.orden_lista ASC";
		$exc = $link->query( $sql ) or die( "Error getTransferProductDetail : " . $link->error);
		$c = 0;
		while ( $r = $exc->fetch_row() ) {
			$c ++;				
			$product_detail_c ++;

			$background_color = '#FADE6B';
			if( $product_counter % 2 != 0 ){
				$background_color = '#BDBBB4';
			}
//{$product_counter}.{$c}
			$GLOBALS['global_counter'] ++;
			$resp .= "<tr class=\"no_visible {$r[1]}\"
						id=\"row_{$product_c}\"
						onclick=\"gft_row_focus( this );\"
						product=\"{$r[1]}\"
						tabindex=\"{$GLOBALS['global_counter']}\"
						style=\"background-color : {$background_color};\"
						color=\"{$background_color}\"
						is_parent=\"0\"
					>
						<td 
							class=\"\"
							id=\"1_{$GLOBALS['global_counter']}\"
							editable=\"0\"
						></td>
						<td 
							id=\"2_{$GLOBALS['global_counter']}\"
							class=\"no_visible\"
							editable=\"0\"
						>{$r[1]}</td>
						<td 
							id=\"3_{$GLOBALS['global_counter']}\"
							class=\"no_visible\"
						>{$r[2]}</td>
						<td 
							id=\"4_{$GLOBALS['global_counter']}\"
							editable=\"0\"
						>{$r[3]}</td>
						<td 
							id=\"5_{$GLOBALS['global_counter']}\"
							editable=\"0\"
						>{$r[4]}</td>
						<td
							gft_type=\"text\"
							id=\"6_{$GLOBALS['global_counter']}\"
							onclick=\"gft_build_field_editable( this )\"
							editable=\"1\"
						>{$r[5]}</td>
						<td
							id=\"7_{$GLOBALS['global_counter']}\"
							editable=\"0\"
						><button 
							class=\"btn btn-danger\"
							onclick=\"{$r[0]}\">X</button>
						</td>
					</tr>";
		//forma los detalles del producto
		}
		return $resp;
	}

	function getProductsProviders( $txt = null, $link ){
		$resp = [];
		$clave = explode( ' ', $txt );
		$sql = "SELECT
					pp.id_proveedor_producto,
					p.id_productos,
					CONCAT( p.nombre, ' ( MODELO : ', pp.clave_proveedor, ' - ', pp.presentacion_caja,' pzs )' ) AS nombre,
					pp.codigo_barras_pieza_1,
					pp.codigo_barras_pieza_2,
					pp.codigo_barras_pieza_3,
					pp.codigo_barras_presentacion_cluces_1,
					pp.codigo_barras_presentacion_cluces_2,
					pp.codigo_barras_caja_1,
					pp.codigo_barras_caja_2
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos
				WHERE 1
				GROUP BY pp.id_proveedor_producto";
		if( $txt != null ){
			foreach ($clave as $key => $value) {
				$sql .= ( $key == 0 ? ' AND (' : ' AND ' );
				$sql .= "CONCAT( p.nombre, ' ( MODELO : ', pp.clave_proveedor, ' - ', pp.presentacion_caja,' pzs )' )";
				$sql .= " LIKE '{$value}'";
			}
			$sql .= ' )';
		}
		$exc = $link->query( $sql ) or die( "Error getProductsProviders : " . $link->error);
		$c = 0;
		while ( $r = $exc->fetch_row() ) {
			array_push( $resp, $r );
		}
		return json_encode( $resp );
	}

	function refresh_current_transfer( $transfer_id, $link ){

	}
?>