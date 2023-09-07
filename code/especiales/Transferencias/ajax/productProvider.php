
<?php
//detecta si es una petición asincrona
	if( isset($_GET['fl']) || isset($_POST['fl']) ){
		include( '../../../../config.ini.php' );
		include( '../../../../conect.php' );
		include( '../../../../conexionMysqli.php' );
	//echo calculateProductProvider( 1821, 1000, $link );
		switch ( $action ) {
			case '':
			
			break;
			
			default:
				die( "Permission Denied!" );
			break;
		}
	}
	
	//echo 'ok';

	function getProductProviders( $product_id, $link ){
		$resp = "";
		$sql = "";
		
		return $resp;
	}

	function calculateProductProvider( $product_id, $quantity, $link, $counter = null ){
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
					pp.piezas_presentacion_cluces AS piecer_per_pack,
					MAX( rec.fecha_recepcion ) AS last_date,
					0 AS 'boxes',
					0 AS 'packs',
					0 AS 'pieces'
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
		while ( $r = $stm->fetch_assoc() ) {
			$auxiliar_inventory = 0;
			if( $r['inventory'] > 0 ){//si hay inventario
				$auxiliar_inventory = $r['inventory'];
				$boxes = round( $auxiliar_quantity / $r['pieces_per_box'] );//piezas entre cajas
			//reparte por caja
				if(  $boxes >= 1 && $auxiliar_inventory >= $r['pieces_per_box'] ){
					while ( $auxiliar_quantity >= $r['pieces_per_box'] && $auxiliar_inventory >= $r['pieces_per_box'] ) {
						$r['boxes'] += 1;
						$auxiliar_quantity -= $r['pieces_per_box'];
						$auxiliar_inventory -= $r['pieces_per_box'];
					}
				}
				$packs = round( $auxiliar_quantity / $r['pieces_per_box'] );//piezas entre paquetes
			//reparte por paquete
				if(  $packs >= 1 && $auxiliar_inventory >= $r['pieces_per_pack'] && $r['pieces_per_pack'] > 0 ){
					while ( $auxiliar_quantity >= $r['pieces_per_pack'] && $auxiliar_inventory >= $r['pieces_per_pack'] ) {
						$r['packs'] += 1;
						$auxiliar_quantity -= $r['pieces_per_pack'];
						$auxiliar_inventory -= $r['pieces_per_pack'];
					}
				}
			//reparte por pieza
				if(  ( $auxiliar_quantity >= 1 && $auxiliar_inventory >= 1 
						&& $r['boxes'] == 0 && $r['packs'] == 0 ) 
					|| ( $auxiliar_inventory / $r['pieces_per_pack'] >= 1 )
				){
					$r['pieces'] = ( $auxiliar_inventory >= $auxiliar_quantity ? $auxiliar_quantity : $auxiliar_inventory  );
					$auxiliar_quantity -= $r['pieces'];
					$auxiliar_inventory -= $r['pieces'];
				}
			}/*else{
				$r['boxes'] = 0;
				$r['packs'] = 0;
				$r['pieces'] = 0;
			}*/
			
			array_push( $resp, $r );
		}//fin de while
		if( $auxiliar_quantity > 0 ){
			saveTransferProductException( $product_id, "El inventario en el sistema no es suficiente para llenar la transferencia \n
				cantidad pedidda : {$quantity}, cantidad faltante : {$auxiliar_quantity} ", $link );
		}
		return ( $counter == null ? json_encode( $resp ) : buildProductProviderDetail( $counter, $resp ) );
	}

	function saveTransferProductException ( $product_id, $description, $link ){
		$resp = "";
		$sql = "";
		//echo $description;
		return $resp;
	}

//construye registro para la tabla de transferencias
	function buildProductProviderDetail( $counter, $rows ){
		//var_dump($rows);
		$resp = "";
		$c1 = 0;
		foreach ($rows as $key => $row) {
			$resp .= ( $c1 > 0 ? ' |~|' : '' );
			$c2 = 0;
			foreach ($row as $key2 => $r) {
				$resp .= ( $c2 > 0 ? '~' : '' );
				$resp .= $r;
				$c2 ++;
			}
			$c1 ++;
		}
		return $resp;
	}
?>