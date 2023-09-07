
<?php
//detecta si es una petición asincrona
	if( isset($_GET['fl']) || isset($_POST['fl']) ){
		include_once( '../../../../config.ini.php' );
		include_once( '../../../../conect.php' );
		include_once( '../../../../conexionMysqli.php' );
	//echo calculateProductProvider( 1821, 1000, $link );
		$action = ( isset( $_GET['fl']) ? $_GET['fl'] : $_POST['fl'] );
		switch ( $action ) {
			case 'calculateProductProvider':
			/*die( $_GET['product_id'] . ',' . $_GET['quantity'] . ',' .
$_GET['counter'] );*/
				if( !isset( $_GET['permission'] ) ){
					$_GET['permission'] = 0;
				}
				echo calculateProductProvider( $_GET['product_id'], $_GET['quantity'], $link, $_GET['counter'], $_GET['permission'] );
			break;
			
			default:
			//	die( "Permission Denied!" );
			break;
		}
	}else if( isset( $_POST['action'] ) ){
		include_once( '../../../../config.ini.php' );
		include_once( '../../../../conect.php' );
		include_once( '../../../../conexionMysqli.php' );
	}
	
	//echo 'ok';

	function getProductProviders( $product_id, $link ){
		$resp = "";
		$sql = "";
		
		return $resp;
	}

	function calculateProductProvider( $product_id, $quantity, $link, $counter = null, $permission = 0 ){
		$resp = array();
		$sql = "SELECT id_regla_transferencias FROM sys_configuracion_sistema";
		$stm = $link->query( $sql ) or die( "Error al consultar la regla de las transferencias : " . $link->error );
		$row = $stm->fetch_row();
		//$type = 'Primeras entradas Primeras salidas';
		$date =" ";
		$order_by = " ASC ";
		if( $row[0] == 2 ){
		//	$type = 'Últimas entradas Primeras salidas';
			$order_by = " DESC ";
		}

		$sql = "SELECT
					p.id_proveedor AS provider_id,
					p.nombre_comercial AS provider_name,
					pp.id_proveedor_producto AS product_provider_id,
					ipp.inventario AS inventory,
					pp.clave_proveedor AS provider_model,
					pp.presentacion_caja AS pieces_per_box,
					pp.piezas_presentacion_cluces AS pieces_per_pack,
					0 AS 'boxes',
					0 AS 'packs',
					0 AS 'pieces',
					0 AS 'total_pieces',
					IF( rec.fecha_recepcion IS NOT NULL, MAX( rec.fecha_recepcion ), '' ) AS last_date,
					pp.solo_pieza AS just_piece
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_inventario_proveedor_producto ipp 
				ON ipp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_proveedor p ON p.id_proveedor = pp.id_proveedor
				LEFT JOIN ec_oc_recepcion_detalle rd 
				ON rd.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_oc_recepcion rec ON rec.id_oc_recepcion = rd.id_oc_recepcion
				WHERE pp.id_producto = '{$product_id}'
				GROUP BY pp.id_proveedor_producto
				ORDER BY MAX( rec.fecha_recepcion ) {$order_by} ";
		$stm = $link->query( $sql ) or die( "Error al consultar productos para surtir : " . $link->error );
		$r_final = array();
		$auxiliar_quantity = $quantity;
		$total_quantity = 0;
		$dont_recalculate = 0;
		while ( $r = $stm->fetch_assoc() ) {
			$row_total_quantity = 0;//cantidad por registro
			$auxiliar_inventory = 0;
			if( $r['inventory'] > 0 && $dont_recalculate == 0){//si hay inventario
				$auxiliar_inventory = $r['inventory'];
				$boxes = round( $auxiliar_quantity / $r['pieces_per_box'] );//piezas entre cajas
			//reparte por caja
				if(  $boxes >= 1 && $auxiliar_inventory >= $r['pieces_per_box'] && $r['pieces_per_box'] > 0  ){
					while ( $auxiliar_quantity >= $r['pieces_per_box'] && $auxiliar_inventory >= $r['pieces_per_box'] ) {
						$r['boxes'] += 1;
						$auxiliar_quantity -= $r['pieces_per_box'];
						$auxiliar_inventory -= $r['pieces_per_box'];
						$total_quantity += $r['pieces_per_box'];
						$row_total_quantity += $r['pieces_per_box'];
					}
				}
				$packs = round( $auxiliar_quantity / $r['pieces_per_pack'] );//piezas entre paquetes
			//reparte por paquete
				if(  $packs >= 1 && $auxiliar_inventory >= $r['pieces_per_pack'] && $r['pieces_per_pack'] > 0 ){
					while ( $auxiliar_quantity >= $r['pieces_per_pack'] && $auxiliar_inventory >= $r['pieces_per_pack'] ) {
						$r['packs'] += 1;
						$auxiliar_quantity -= $r['pieces_per_pack'];
						$auxiliar_inventory -= $r['pieces_per_pack'];
						$total_quantity += $r['pieces_per_pack'];
						$row_total_quantity += $r['pieces_per_pack'];
					}
				}
			//reparte por pieza
				 /*( 
						( $auxiliar_quantity >= 1 && $auxiliar_inventory >= 1 && ( $r['boxes'] > 0 || $r['packs'] > 0 ) && ( $r['pieces_per_pack'] > 0 && $r['pieces_per_box'] > 0 ) ) 
						||* ( ( ( $auxiliar_inventory / $r['pieces_per_pack'] ) < 1 && $auxiliar_inventory / $r['pieces_per_box'] ) < 1 ) && ( $r['boxes'] > 0 && $r['packs'] > 0 ) )*/ 
						//) /*&& $dont_recalculate == 1 */
				if(
					( $r['pieces_per_pack'] == 0 && $r['pieces_per_box'] == 0 ) 
					|| ( ( ( $auxiliar_inventory / $r['pieces_per_pack'] ) < 1 && $auxiliar_quantity > 0 )
					&& ( ( $auxiliar_inventory / $r['pieces_per_box'] ) < 1 && $auxiliar_quantity > 0 ) )
					|| $r['just_piece'] == 1
					|| $permission == 1
				){
					$r['pieces'] = ( $auxiliar_inventory >= $auxiliar_quantity ? $auxiliar_quantity : $auxiliar_inventory  );
					$auxiliar_quantity -= $r['pieces'];
					$auxiliar_inventory -= $r['pieces'];
					$total_quantity += $r['pieces'];
					$row_total_quantity += $r['pieces'];
				}
			//implementacion para que no ajuste con otro proveedor_peoducto
				if( $auxiliar_inventory >= $auxiliar_quantity && $r['pieces_per_pack'] > 0 && $r['pieces_per_box'] > 0 /*&& $permission == 0*/ ){
					$dont_recalculate = 1;
				}
			}else{
				$r['boxes'] = 0;
				$r['packs'] = 0;
				$r['pieces'] = 0;
			}
			$r['total_pieces'] = $row_total_quantity;
			array_push( $resp, $r );
		}//fin de while
		if( $auxiliar_quantity > 0 ){
			saveTransferProductException( $product_id, "El inventario en el sistema no es suficiente para llenar la transferencia \n
				cantidad pedida : {$quantity}, cantidad faltante : {$auxiliar_quantity} ", $link );
		}
		return ( $counter == null ? json_encode( $resp ) : buildProductProviderDetail( $counter, $resp, $total_quantity ) );
	}

	function saveTransferProductException ( $product_id, $description, $link ){
		$resp = "";
		$sql = "";
		//echo $description;
		return $resp;
	}

//construye registro para la tabla de transferencias
	function buildProductProviderDetail( $counter, $rows, $total ){
		//var_dump($rows);
		$resp = "";
		$c1 = 0;
		foreach ($rows as $key => $row) {
			$resp .= ( $c1 > 0 ? ' ||' : '' );
			$c2 = 0;
			foreach ($row as $key2 => $r) {
				$resp .= ( $c2 > 0 ? '' : '' );
				$resp .= $r;
				$c2 ++;
			}
			$c1 ++;
		}
		return $total . '||' . $resp;
	}
?>