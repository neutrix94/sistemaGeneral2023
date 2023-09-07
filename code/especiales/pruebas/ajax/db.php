<?php
//die( 'here' );
	
	if( isset( $_GET['fl_db'] ) ){
		$action = $_GET['fl_db'];
//
		include( "../../../../conexionMysqli.php" );
		$db = new dB( $link );
		switch ( $action ) {
		//obtener productos
			case 'getProductsCatalogue' :
				echo $db->getProductsCatalogue();
			break;
		//obtener almacenes
			case 'getWarehousesCatalogue' :
				echo $db->getWarehousesCatalogue();
			break;
		//obtener detalles del producto
			case 'getProductDetail': 
				echo $db->getProductDetail( $_GET['product_id'], $_GET['warehouses'] );
			break;
		//
			case 'seekTransfer':
				echo $db->seekTransfer( $_GET['key'] );
			break;
		//
			case 'getTransfers':
				echo $db->getTransfers( $_GET['type'], $_GET['value'] );
			break;

			case 'getTransfersProducts':
				echo $db->getTransfersProducts( $_GET['reception_id'], $_GET['validation_id'], $_GET['transfer_ids'] );
			break; 

			case 'getDinamicHeader' :
				echo $db->getDinamicHeader( $_GET['warehouses'] );
			break;

			case 'getProductProviderDetail':
				echo $db->getProductProviderDetail( $_GET['product_provider_id'] );
			break;

			case 'show_movements_details' : 
				echo $db->show_movements_details( $_GET['product_provider_id'], $_GET['initial_date'], 
					$_GET['initial_hour'], $_GET['current_warehouses'] );
			break;

			case 'getScannedDetail' :
				echo $db->getScannedDetail( $_GET['transfer_id'], $_GET['product_provider_id'], $_GET['validation_blocks_id'] );
			break;

			case 'getProductsToRemove' :
				echo $db->getProductsToRemove( $_GET['products_ids'] );
			break;

			case 'getCurrentTime' :
				echo $db->getCurrentTime();
			break;

			case 'getResolutionProducts' :
				echo $db->getResolutionProducts( $_GET['reception_block_id'] );
			break;

			default:
				die( "Permission denied on '{$action}'!" );
			break;
		}
	}

	class dB
	{
		private $link;
	//	private $api_path;
		function __construct( $connection )
		{
			$this->link = $connection;
			$this->getApiConfig();
		}

		function getCurrentTime(){
			$sql = "SELECT
					DATE_FORMAT(NOW(), \"%Y-%m-%d\") AS current_date, 
					DATE_FORMAT(NOW(), \"%H:%i:%m\") AS current_time";
			$stm = $this->link->query( $sql ) or die( "Error al consultar fecha y hora : {$this->link->error}" );
			$resp = $stm->fetch_assoc();
			return "ok|{$resp}";
		}

		function getResolutionProducts( $reception_block_id ){
			$resp = array();
			$resp2 = array();

			$sql = "SELECT
						prt.id_producto_resolucion AS product_resolution_id,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						CONCAT( ' ( ', p.orden_lista, ' ) ', p.nombre ) AS product_name,
						prt.cantidad_faltante AS missing_pieces,
						prt.cantidad_excedente AS excedent_pieces,
						'N/A' AS doesnt_correspond_pieces,
						'N/A' AS pieces_stayed,
						'N/A' AS pieces_returned,
						'N/A' AS pieces_missed,
						prt.conteo_fisico AS fisic_count,
						prt.conteo_excedente AS excedent_count,
						'N/A' AS difference,
						prt.inventario AS inventory
					FROM ec_productos_resoluciones_tmp prt
					LEFT JOIN sys_users u
					ON u.id_usuario = prt.id_usuario
					LEFT JOIN ec_productos p
					ON p.id_productos = prt.id_producto
					WHERE prt.id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar registros en la tabla ec_bloques_transferencias_resolucion : {$sql} {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push($resp, $row);
			}
			$sql = "SELECT
						btr.id_bloque_transferencia_resolucion AS resolution_transfer_block_id,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						CONCAT( ' ( ', p.orden_lista, ' ) ', p.nombre, ' ', pp.clave_proveedor ) AS product_name,
						btr.piezas_faltantes AS missing_pieces,
						btr.piezas_sobrantes AS excedent_pieces,
						btr.piezas_no_corresponden AS doesnt_correspond_pieces,
						btr.piezas_se_quedan AS pieces_stayed,
						btr.piezas_se_regresan AS pieces_returned,
						btr.piezas_faltaron AS pieces_missed,
						btr.conteo AS fisic_count,
						btr.conteo_excedente AS excedent_count,
						btr.diferencia AS difference,
						btr.id_producto_resolucion AS product_resolution_id
					FROM ec_bloques_transferencias_resolucion btr
					LEFT JOIN sys_users u
					ON u.id_usuario = btr.id_usuario
					LEFT JOIN ec_productos p
					ON p.id_productos = btr.id_producto
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = btr.id_proveedor_producto
					WHERE btr.id_bloque_transferencia_recepcion = {$reception_block_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar registros en la tabla ec_bloques_transferencias_resolucion : {$sql} {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				array_push($resp2, $row);
			}
			return "ok|" . json_encode( $resp ) . "|" . json_encode( $resp2 );
		}

		function getProductsToRemove( $products_ids ){
			$resp = array();
			$sql = "SELECT
						CONCAT( '( ', p.orden_lista, ' ) ', p.nombre ) AS product_name,
						p.id_productos AS product_id
					FROM ec_productos p
					WHERE p.id_productos IN( $products_ids )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar productos eliminados : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
			return "ok|" . json_encode( $resp );
		}
//detalle del escaneo
		function getScannedDetail( $transfer_id, $product_provider_id, $validation_blocks_id = '' ){
			$resp1 = array();
		//consulta escaneos de validacion
			$sql = "SELECT
						tvu.id_transferencia_validacion AS id,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						tvu.cantidad_piezas_validadas AS quantity,
						tvu.codigo_barras AS code,
						tvu.codigo_unico AS unique_code,
						tvu.fecha_validacion AS date_time
					FROM ec_transferencias_validacion_usuarios tvu
					LEFT JOIN ec_transferencia_productos tp
					ON tp.id_transferencia_producto = tvu.id_transferencia_producto
					LEFT JOIN sys_users u
					ON u.id_usuario = tvu.id_usuario
					WHERE tp.id_transferencia = {$transfer_id}
					AND tp.id_proveedor_producto = {$product_provider_id}
					/*AND tvu.codigo_unico IS NOT NULL
					AND tvu.codigo_unico != ''*/";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de escaneos : {$this->link->error}" );
//$codes_existings = "";
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp1, $row );
//$codes_existings .= $codes_existings == '' ? '' : ',';
			}
		//consulta codigos de validacion directo de la tabla de codigos unicos
//$sql = "";
			$resp2 = array();

		//consulta escaneos de recepcion
			$sql = "SELECT
						tru.id_transferencia_recepcion AS id,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						tru.cantidad_piezas_recibidas AS quantity,
						tru.codigo_validacion AS code,
						tru.codigo_unico AS unique_code,
						tru.fecha_recepcion AS date_time
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_transferencias_recepcion_usuarios tru
					ON tp.id_transferencia_producto = tru.id_transferencia_producto
					LEFT JOIN sys_users u
					ON u.id_usuario = tru.id_usuario
					WHERE tp.id_transferencia = {$transfer_id}
					AND tp.id_proveedor_producto = {$product_provider_id}
					/*AND tru.codigo_unico IS NOT NULL
					AND tru.codigo_unico != ''*/";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de escaneos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp2, $row );
			}
		//consulta codigos de validacion de caja
			$sql = "SELECT 
						GROUP_CONCAT( codigo_barras SEPARATOR '<br>' ) AS boxes_validation_barcodes
					FROM ec_codigos_validacion_cajas
					WHERE id_codigo_validacion > 0";
			$stm = $this->link->query( $sql )or die( "Error al consultar los codigos de validacion : {$this->link->error}" );
			$row = $stm->fetch_assoc();
		
		//busca los codigos unicos de resolucion
			$sql = "SELECT
						btvd.id_bloque_transferencia_validacion AS validation_blocks_id
					FROM ec_bloques_transferencias_validacion_detalle btvd
					WHERE btvd.id_transferencia = {$transfer_id}";
			$stm_1 = $this->link->query( $sql ) or die( "Error al consultar el id de bloque de validación : {$this->link->error}" ); 
			if( $stm_1->num_rows > 0 ){
				$row_1 = $stm_1->fetch_assoc();
				$validation_blocks_id = $row_1['validation_blocks_id'];
				$sql = "SELECT
							tcu.id_transferencia_codigo AS id,
							CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
							tcu.piezas_contenidas AS quantity,
							tcu.codigo_unico AS code,
							tcu.codigo_unico AS unique_code,
							tcu.fecha_alta AS date_time
						FROM ec_transferencia_codigos_unicos tcu
						LEFT JOIN sys_users u
						ON u.id_usuario = IF( tcu.id_usuario_validacion IS NULL, tcu.id_usuario_recepcion, tcu.id_usuario_validacion )
						WHERE tcu.id_bloque_transferencia_validacion IN( {$validation_blocks_id} )
						AND tcu.codigo_unico LIKE '%{$product_provider_id}%'
						AND tcu.insertado_por_resolucion = '1'";
				$stm = $this->link->query( $sql ) or die( "Error al consultar escaneos por resolucion : {$this->link->error}" );
				while ( $row1 = $stm->fetch_assoc() ) {
					array_push( $resp2, $row1 );
				}
			}
			return 'ok|' . json_encode( $resp1 ) . '|'. json_encode( $resp2 ) . '|' . json_encode( $row );
		}

		function show_movements_details( $product_provider_id, $initial_date, $initial_hour, $current_warehouses ){
			$resp = array();
			$resp2 = array();
			$resp3= array();
			//include( '../../../../conexionDoble.php' );
			$sql = "SELECT
						alm.nombre AS warehouse_name,
						tm.nombre AS movement_name,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						(SELECT
								SUM( IF( emdpp.id_movimiento_detalle_proveedor_producto IS NULL, 0, ( etm.afecta * emdpp.cantidad ) ) )
							FROM ec_movimiento_detalle_proveedor_producto emdpp
							LEFT JOIN ec_tipos_movimiento etm
							ON etm.id_tipo_movimiento = emdpp.id_tipo_movimiento
							WHERE emdpp.id_proveedor_producto = {$product_provider_id}
							AND emdpp.id_almacen = alm.id_almacen
							GROUP BY emdpp.id_proveedor_producto
						)AS inventory,
						md.cantidad AS movement_quantity,
						ma.id_transferencia AS transfer_id,
						md.id_proveedor_producto AS product_provider_id,
						IF( ma.id_transferencia = -1,
							'N/A',
							(SELECT
								folio
							FROM ec_transferencias 
							WHERE id_transferencia = ma.id_transferencia )
						) AS transfer_folio,
						IF( ma.id_transferencia = -1,
							'N/A',
							(SELECT
								IF( btvd.id_bloque_transferencia_validacion IS NULL,
									'N/A',
									btvd.id_bloque_transferencia_validacion
								)
							FROM ec_bloques_transferencias_validacion_detalle btvd 
							WHERE btvd.id_transferencia = ma.id_transferencia 
							LIMIT 1)
						) AS transfer_validation_block,
						IF( ma.id_transferencia = -1,
							'N/A',
							(SELECT
								IF( btrd.id_bloque_transferencia_recepcion IS NULL,
									'N/A',
									btrd.id_bloque_transferencia_recepcion
								)
							FROM ec_bloques_transferencias_recepcion_detalle btrd
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON btrd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
							WHERE btvd.id_transferencia = ma.id_transferencia 
							LIMIT 1)
						) AS transfer_recepcion_block,
						CONCAT( ma.fecha, ' ', ma.hora ) AS movement_date_time,
						md.id_movimiento_almacen_detalle AS movement_detail_id,
						ma.fecha AS date,
						ma.hora AS hour
					FROM ec_movimiento_detalle md
					LEFT JOIN ec_movimiento_almacen ma
					ON ma.id_movimiento_almacen = md.id_movimiento
					LEFT JOIN ec_tipos_movimiento tm
					ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON alm.id_almacen = ma.id_almacen 
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
					ON mdpp.id_movimiento_almacen_detalle = md.id_movimiento_almacen_detalle
					LEFT JOIN ec_proveedor_producto pp
					ON md.id_proveedor_producto = pp.id_proveedor_producto
					LEFT JOIN sys_users u 
					ON u.id_usuario = ma.id_usuario 
					WHERE ma.id_almacen IN( {$current_warehouses} )
					AND md.id_proveedor_producto = {$product_provider_id}
					AND ( CONCAT( ma.fecha, ' ', ma.hora ) >= CONCAT( '{$initial_date} ', '{$initial_hour}' ) 
					OR mdpp.fecha_registro >= CONCAT( '{$initial_date} ', '{$initial_hour}' ) )";
//die( $sql );//condicion de fecha y hora
			$stm = $this->link->query( $sql ) or die( "Error al consultar detalle de movimientos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
	//consulta las validaciones por venta
			$sql = "SELECT 
						alm.nombre AS warehouse_name,
						tm.nombre AS movement_name,
						CONCAT( u.nombre, ' ', u.apellido_paterno ) AS user_name,
						1 AS inventory,
						mdpp.cantidad AS movement_quantity,
						'N/A' AS transfer_id,
						mdpp.id_proveedor_producto AS product_provider_id,
						'N/A' AS transfer_folio,
						'N/A' AS transfer_validation_block,
						'N/A' AS transfer_recepcion_block,
						mdpp.fecha_registro AS movement_date_time,
						mdpp.id_movimiento_detalle_proveedor_producto AS movement_detail_id,
						mdpp.fecha_registro AS date,
						'' AS hour
					FROM ec_movimiento_detalle_proveedor_producto mdpp
					LEFT JOIN ec_tipos_movimiento tm
					ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
					LEFT JOIN ec_almacen alm
					ON alm.id_almacen = mdpp.id_almacen
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pvu.id_pedido_validacion = mdpp.id_pedido_validacion
					LEFT JOIN sys_users u
					ON u.id_usuario = pvu.id_usuario
					WHERE mdpp.fecha_registro >= CONCAT( '{$initial_date} ', '{$initial_hour}' )
					AND mdpp.id_almacen IN( {$current_warehouses} )
					AND mdpp.id_proveedor_producto = {$product_provider_id}
					AND mdpp.id_pedido_validacion != -1";
				//echo $sql;
			//$stm = $this->link->query( $sql ) or die( "Error al consultar los movimientos por venta : {$this->link->error}" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar detalle de movimientos : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp2, $row );
			}
			//$resp = uasort($resp, 'movement_date_time');
			//var_dump( $resp );
//echo $sql;
			return 'ok|' . json_encode( $resp ) . '|' . json_encode( $resp2 );
		}

		function getProductProviderDetail( $product_provider_id ){
			$sql = "SELECT
						CONCAT( '( ', p.orden_lista, ' ) ', p.nombre ) AS product_description,
						pp.id_proveedor_producto AS product_provider_id,
						pp.clave_proveedor AS provider_clue,
						pp.codigo_barras_pieza_1 AS piece_barcode_1,
						pp.codigo_barras_pieza_2 AS piece_barcode_2,
						pp.codigo_barras_pieza_3 AS piece_barcode_3,
						pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1,
						pp.codigo_barras_presentacion_cluces_2 AS pack_barcode_2,
						pp.codigo_barras_caja_1 AS box_barcode_1,
						pp.codigo_barras_caja_2 AS box_barcode_2,
						pp.contador_cajas AS boxes_counter,
						pp.contador_paquetes AS packs_counter,
						( SELECT 
								GROUP_CONCAT( codigo_barras SEPARATOR '<br>' )
						FROM ec_codigos_validacion_cajas
						WHERE id_codigo_validacion > 0
						) AS boxes_validation_barcodes
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON pp.id_producto = p.id_productos
					WHERE pp.id_proveedor_producto = {$product_provider_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos del proveedor producto : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return json_encode( $row );
		}


		function getDinamicHeader( $warehouses ){
			$resp = "<tr>
						<td class=\"text-center\">Producto / Proveedor Producto 
							<button class=\"btn\">
								<i class=\"icon-eye-1\" onclick=\"show_and_hidde_calculated_rows( this, true )\"></i>
							</button>
						</td>";
			$warehouses = explode( ',',  $warehouses );
			//busca los nombre de los almacenes
			for( $i = 0; $i < sizeof( $warehouses ); $i++ ){
				$sql = "SELECT
							id_almacen AS warehouse_id,
							nombre AS warehouse_name
						FROM ec_almacen
						WHERE id_almacen IN( {$warehouses[$i]} )";
				$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de almacenes : {$this->link->error}" );
				while( $row = $stm->fetch_assoc() ){
					$resp .= "<th>Local {$row['warehouse_name']}</th>";
					$resp .= "<th class=\"calculate_row no_visible calculated_inventory\">Local {$row['warehouse_name']} acumulado</th>";
					$resp .= "<th>Linea {$row['warehouse_name']}</th>";
					$resp .= "<th class=\"calculate_row no_visible calculated_inventory\">Linea {$row['warehouse_name']} acumulado</th>";
				}
			}
			$resp .= "<th width=\"50px\"></th>
			</tr>";
			return "ok|{$resp}";
		}

		function getTransfersProducts( $reception_id, $validation_id, $transfer_ids ){
			$resp = array();
			$sql = "SELECT
						tp.id_producto_or AS product_id
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia IN( {$transfer_ids} )
					GROUP BY tp.id_producto_or";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos en la transferencia : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
			return json_encode( $resp );
		}	

		function getTransfers( $type, $value ){
//die( "type : {$type} , value : {$value}" );
			$transfers_block_reception_id = "";//array()
			$transfers_block_validation_id = "";//array()
			$transfers_id = "";//array();
			$transfers = "";//array();
			$warehouses = "";
			$sql = "";
			if( $type == 1 ){
				$sql = "SELECT
							GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids,
							GROUP_CONCAT( DISTINCT( btvd.id_bloque_transferencia_validacion ) SEPARATOR ',' ) AS validation_blocks,
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_recepcion ) SEPARATOR ',' ) AS reception_blocks,
							GROUP_CONCAT( t.folio SEPARATOR ',' ) AS transfers
						FROM ec_bloques_transferencias_recepcion_detalle btrd
						LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
						ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
						LEFT JOIN ec_transferencias t
						ON t.id_transferencia = btvd.id_transferencia
						WHERE btrd.id_bloque_transferencia_recepcion = '{$value}'";
						//die( $sql );
			}else if( $type == 2 ){
				$sql = "SELECT
							GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids,
							GROUP_CONCAT( DISTINCT( btvd.id_bloque_transferencia_validacion ) SEPARATOR ',' ) AS validation_blocks,
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_recepcion ) SEPARATOR ',' ) AS reception_blocks,
							GROUP_CONCAT( t.folio SEPARATOR ',' ) AS transfers
						FROM ec_bloques_transferencias_validacion_detalle btvd
						LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
						ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
						LEFT JOIN ec_transferencias t
						ON t.id_transferencia = btvd.id_transferencia
						WHERE btvd.id_bloque_transferencia_validacion = '{$value}'";
			//die( $sql );
			}else if( $type == 3 ){
			//consulta si tiene bloque de recepcion
				/*$sql = "SELECT

						FROM ec_bloques_transferencias_validacion btvd
						WHERE id_bloque_transferencia_validacion";*/
				$sql = "SELECT
							GROUP_CONCAT( t.id_transferencia SEPARATOR ',' ) AS transfers_ids,
							GROUP_CONCAT( DISTINCT( btvd.id_bloque_transferencia_validacion ) SEPARATOR ',' ) AS validation_blocks,
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_recepcion ) SEPARATOR ',' ) AS reception_blocks,
							GROUP_CONCAT( t.folio SEPARATOR ',' ) AS transfers
						FROM ec_transferencias t
						LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd 
						ON t.id_transferencia = btvd.id_transferencia
						LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
						ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
						WHERE t.id_transferencia IN ( '{$value}' )";
//die( $sql );
			}
			$stm = $this->link->query( $sql ) or die( "Error al consultar bloques de transferencias : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$transfers_block_reception_id = $row['reception_blocks'];//array()
			$transfers_block_validation_id = $row['validation_blocks'];
			$transfers_id = $row['transfers_ids'];//array();
			$transfers = $row['transfers'];//array();
			
		/*obtiene los datos asociados al bloque*/
			if( $transfers_block_reception_id != null && $transfers_block_reception_id != '' ){
				$sql = "SELECT
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_recepcion ) ) AS reception_blocks,
							GROUP_CONCAT( DISTINCT( btvd.id_transferencia ) SEPARATOR ',' ) AS transfers_ids,
							GROUP_CONCAT( DISTINCT( t.folio ) SEPARATOR ',' ) AS transfers,
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_validacion ) SEPARATOR ',' ) AS validation_blocks 
						FROM ec_bloques_transferencias_recepcion_detalle btrd
						LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
						ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
						LEFT JOIN ec_transferencias t 
						ON t.id_transferencia = btvd.id_transferencia
						WHERE btrd.id_bloque_transferencia_recepcion IN ( {$transfers_block_reception_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al consultar los bloques de validacion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$transfers_block_reception_id = $row['reception_blocks'];//array()
				$transfers_block_validation_id = $row['validation_blocks'];
				$transfers_id = $row['transfers_ids'];//array();
				$transfers = $row['transfers'];//array();
			}else if( $transfers_block_validation_id != null && $transfers_block_validation_id != ''  ){
				$sql = "SELECT
							GROUP_CONCAT( DISTINCT( btrd.id_bloque_transferencia_recepcion ) ) AS reception_blocks,
							GROUP_CONCAT( DISTINCT( btvd.id_transferencia ) SEPARATOR ',' ) AS transfers_ids,
							GROUP_CONCAT( DISTINCT( t.folio ) SEPARATOR ',' ) AS transfers,
							GROUP_CONCAT( DISTINCT( btvd.id_bloque_transferencia_validacion ) SEPARATOR ',' ) AS validation_blocks 
						FROM ec_bloques_transferencias_validacion_detalle btvd
						LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
						ON btvd.id_bloque_transferencia_validacion = btrd.id_bloque_transferencia_validacion
						LEFT JOIN ec_transferencias t 
						ON t.id_transferencia = btvd.id_transferencia
						WHERE btvd.id_bloque_transferencia_validacion IN ( {$transfers_block_validation_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al consultar los bloques de validacion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$transfers_block_reception_id = $row['reception_blocks'];//array()
				$transfers_block_validation_id = $row['validation_blocks'];
				$transfers_id = $row['transfers_ids'];//array();
				$transfers = $row['transfers'];//array();
		//die( $sql );
			}
			//obtiene almacen origen y destino
			$sql = "SELECT 
						id_almacen_origen AS warehouse_origin,
						id_almacen_destino AS warehouse_destinity
					FROM ec_transferencias 
					WHERE id_transferencia IN( {$transfers_id} )
					GROUP BY id_almacen_origen, id_almacen_destino";	
			$stm = $this->link->query( $sql ) or die( "Error al consultar almacenes : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$warehouses = "{$row['warehouse_origin']},{$row['warehouse_destinity']}";//array();

			return "ok|" . $transfers_block_reception_id . "|" . $transfers_block_validation_id . "|" . $transfers_id . "|" . $transfers . "|" . $warehouses;
		}
		
		function seekTransfer( $key ){
			$resp = "";
		//busqueda en bloque de recepcion
			$sql = "SELECT
						btr.id_bloque_transferencia_recepcion AS id,
						CONCAT( 'BLOQUE DE RECEPCION : ', btr.id_bloque_transferencia_recepcion ) AS description
					FROM ec_bloques_transferencias_recepcion btr
					WHERE id_bloque_transferencia_recepcion = '{$key}'";
			$stm = $this->link->query( $sql ) or die( "Error al buscar en bloques de recepcion : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<div onclick=\"setTransfers( 1, {$row['id']} )\">
					{$row['description']}
				</div>";
			}
		//busqueda en bloque de validacion
			$sql = "SELECT
						btr.id_bloque_transferencia_validacion AS id,
						CONCAT( 'BLOQUE DE VALIDACION : ', btr.id_bloque_transferencia_validacion ) AS description
					FROM ec_bloques_transferencias_validacion btr
					WHERE id_bloque_transferencia_validacion = '{$key}'";
			$stm = $this->link->query( $sql ) or die( "Error al buscar en bloques de validacion : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<div onclick=\"setTransfers( 2, {$row['id']} )\">
					{$row['description']}
				</div>";
			}
		//busqueda en transferencias
			$sql = "SELECT
						id_transferencia AS id,
						folio AS description
					FROM ec_transferencias
					WHERE folio LIKE '%{$key}%'";
		//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al buscar en transferencias : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<div onclick=\"setTransfers( 3, {$row['id']} )\">
					{$row['description']}
				</div>";
			}
			return $resp;
		}

		function getProductsCatalogue(){
			$resp = array();
			$sql = "SELECT
						p.id_productos AS product_id,
						CONCAT( '( ', p.orden_lista, ' ) ', p.nombre ) AS product_name,
						pp.codigo_barras_pieza_1, 
						pp.codigo_barras_pieza_2,
						pp.codigo_barras_pieza_3,
						pp.codigo_barras_presentacion_cluces_1,
						pp.codigo_barras_presentacion_cluces_2,
						pp.codigo_barras_caja_1,
						pp.codigo_barras_caja_2
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = p.id_productos
					WHERE p.id_productos > 0
					AND p.nombre NOT IN( 'Libre', 'ERROR ESTACIONALIDAD X2', 'ERROR ESTACIONALIDA X2', 'Error', 'Error ', 'Producto De Ajuste' )
					AND p.id_categoria !=1
					ORDER BY p.orden_lista";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los productos : {$link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row);
			}
			return json_encode( $resp );
		}


		function getWarehousesCatalogue(){
			$resp = array();
			$sql = "SELECT 
						id_almacen AS warehouse_id,
						nombre AS warehouse_name
					FROM ec_almacen
					WHERE id_almacen > 0";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los almacenes {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				array_push( $resp, $row );
			}
			//die( json_encode( $resp ) );
			return json_encode( $resp );
		}

		function getProductDetail( $product_id, $warehouses ){
			//die( $warehouses );
			$resp = array();
		//obtiene datos generales del producto
			$sql = "SELECT 
						p.id_productos AS product_id,
						CONCAT( '( ',p.orden_lista, ' ) ', p.nombre ) AS product_name
					FROM ec_productos p
					LEFT JOIN ec_movimiento_detalle md
					ON md.id_producto = p.id_productos
					LEFT JOIN ec_movimiento_almacen ma
					ON ma.id_movimiento_almacen = md.id_movimiento
					LEFT JOIN ec_tipos_movimiento tm
					ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
					WHERE p.id_productos = {$product_id}
					GROUP BY p.id_productos";
			$stm = $this->link->query( $sql ) or die( "Error al consultar informacion del producto : {$this->link->error}" );
			$resp = $stm->fetch_assoc();
			$resp['product_name'] = str_replace('ñ', 'n', $resp['product_name'] );

			$warehouses = explode( ",", $warehouses );
			$resp['product_info'] = $this->getProductInventories(  $product_id, $warehouses );
			$resp['product_providers_info'] = $this->getProductProvidersInvetories(  $product_id, $warehouses );
			//var_dump( $resp );
			return "ok|".json_encode( $resp );
		}	
	//informacion del producto
		function getProductInventories( $product_id, $warehouses ){
			/*if( ! include( '../../../../conexionDoble.php' ) ){
				die( 'Error al cargar archivo de conexion' );
			}*/

			$resp = array();
			for( $i = 0; $i < sizeof( $warehouses ); $i++ ){
				$temp = array();
				for( $j = 0; $j <= 1; $j++ ){
					$prefix = ( $j == 0 ? 'local' : 'online' );
					$sql = "SELECT
								SUM( IF( 
										ma.id_movimiento_almacen IS NULL, 
										0, ( md.cantidad * tm.afecta ) 
									) 
								) AS {$prefix}_product_inventory,
								ap.inventario AS {$prefix}_inventory,
								{$warehouses[$i]} AS warehouse_id
						FROM ec_productos p
						LEFT JOIN ec_movimiento_detalle md
						ON md.id_producto = p.id_productos
						LEFT JOIN ec_movimiento_almacen ma
						ON md.id_movimiento = ma.id_movimiento_almacen
						AND ma.id_almacen = {$warehouses[$i]}
						LEFT JOIN ec_tipos_movimiento tm
						ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
						LEFT JOIN ec_almacen_producto ap
						ON ap.id_almacen = {$warehouses[$i]}
						AND ap.id_producto = p.id_productos
						WHERE p.id_productos = {$product_id}";

					if( $j == 0 ){//consulta en local
						$stm = $this->link->query( $sql ) or die( "Error al consultar los inventarios generales en local : {$this->link->error}" );
						while( $row = $stm->fetch_assoc() ){
							$temp["{$prefix}_inventory"] = $row["{$prefix}_inventory"];
							$temp["{$prefix}_product_inventory"] = $row["{$prefix}_product_inventory"];
						}
					}else{//consulta en linea por api
						//echo( $sql );
						$petition_data = array( "QUERY"=>$sql );
						$post_data = json_encode( $petition_data );
						$petition = $this->sendApiPetition( $post_data );

						//var_dump($petition); die('');
					//itera los resultados
						$result = json_decode($petition);
						//var_dump( $result );
						foreach ( $result as $key => $row ) {
						//	echo 'here';die('');
						//var_dump( $row );
							$pba = "{$prefix}_inventory";
							$temp["{$prefix}_inventory"] = $row->$pba;
							//die( $temp["{$prefix}_inventory"] );
							$pba = "{$prefix}_product_inventory";
							$temp["{$prefix}_product_inventory"] = $row->$pba;
							//$temp["{$prefix}_product_inventory"] = $row["{$prefix}_product_inventory"];
						}

					}
				}
				array_push( $resp, $temp );
			}
			//var_dump( $resp );
			return $resp;
		}	

	//oscar 2023 para enviar peticion a api
		function getApiConfig(){
			$sql = "SELECT 
	        	TRIM(value) AS path
	        FROM api_config WHERE name = 'path'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar path de api : {$this->link->error}" );
			$config_row = $stm->fetch_assoc();
			$api_path = $config_row['path']."/rest/v1/";
			$this->api_path = $api_path;
			//echo $this->api_path;
			//return $api_path;
		}

		function sendApiPetition( $post_data ){
			$response = "";
			//var_dump( $post_data );
			//die( "|" . $api_path );
			$crl = curl_init( $this->api_path );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$response = curl_exec($crl);//envia peticion
			//var_dump($response);
			//die('');
			curl_close($crl);
			//$response = json_decode($resp);
			return $response;

		}

	//informacion de proveedores producto	
		function getProductProvidersInvetories( $product_id, $warehouses ){
			$resp = array();
			$sql = "SELECT 
						id_proveedor_producto AS product_provider_id,
						clave_proveedor AS provider_clue
					FROM ec_proveedor_producto 
					WHERE id_producto = {$product_id}";
			$stm_pp = $this->link->query( $sql ) or die( "Error al consultar los proveedores producto : {$this->link->error}" ); 
			
			while( $row_tmp = $stm_pp->fetch_assoc() ){
				$row_tmp['pp_inventories'] = array();
				$tmp = array();
				$tmp_warehouses_inventories = array();
				for( $i = 0; $i < sizeof( $warehouses ); $i++ ){//
					$tmp["pp_inventories"] = array();
					for( $j = 0; $j < 2; $j++ ){
						$prefix = ( $j == 0 ? 'local' : 'online' );
						$sql = "SELECT 
									pp.id_proveedor_producto AS product_provider_id,
									pp.clave_proveedor AS provider_clue,
									SUM( 
										IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 
											0, 
											( mdpp.cantidad * tm.afecta ) 
										) 
									) AS {$prefix}_calculated_inventory,
									IF( ipp.id_inventario_proveedor_producto is null, '0.0000', ipp.inventario ) AS {$prefix}_resumen_inventory
								FROM ec_proveedor_producto pp
								LEFT JOIN ec_productos p
								ON p.id_productos = pp.id_producto
								LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
								ON mdpp.id_proveedor_producto = pp.id_proveedor_producto
								AND mdpp.id_almacen = {$warehouses[$i]}
								LEFT JOIN ec_tipos_movimiento tm
								ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
								LEFT JOIN ec_inventario_proveedor_producto ipp
								ON ipp.id_proveedor_producto = pp.id_proveedor_producto
								AND ipp.id_almacen = {$warehouses[$i]}
								WHERE pp.id_proveedor_producto = {$row_tmp['product_provider_id']}
								AND ipp.id_almacen = {$warehouses[$i]}
								GROUP BY pp.id_proveedor_producto";
//	echo $sql;
						$stm = null;
						if( $j == 0 ){
							$stm = $this->link->query( $sql ) or die( "Error al consultar los proveedores producto en local : {$this->link->error}" );
							while( $row = $stm->fetch_assoc() ){
								if( $j == 0 ){
									$tmp["product_provider_id"] = $row['product_provider_id'];
									$tmp["provider_clue"] = $row['provider_clue']; 
								}
								$tmp_warehouses_inventories["{$prefix}_calculated_inventory"] = $row["{$prefix}_calculated_inventory"];
								//$tmp_warehouses_inventories["{$prefix}_calculated_inventory_sum"] += $row["{$prefix}_calculated_inventory"];
								$tmp_warehouses_inventories["{$prefix}_resumen_inventory"] = $row["{$prefix}_calculated_inventory"];
								//$tmp_warehouses_inventories["{$prefix}_resumen_inventory_sum"] += $row["{$prefix}_calculated_inventory"];
								$tmp_warehouses_inventories["warehouse_id"] = $warehouses[$i];
							}
						}else{//consulta en linea por api
							//die( $sql );
							$petition_data = array( "QUERY"=>$sql );
							$post_data = json_encode( $petition_data );
							$petition = $this->sendApiPetition( $post_data );

						//itera los resultados
							$result = json_decode( $petition );
							//var_dump($result);
							foreach ( $result as $key => $row ) {
								//echo $row->online_product_inventory;
								$pba = "{$prefix}_calculated_inventory";
								$tmp_warehouses_inventories["{$prefix}_calculated_inventory"] = $row->$pba;

								$pba = "{$prefix}_resumen_inventory";//online_resumen_inventory
								//echo "here";
								$tmp_warehouses_inventories["{$prefix}_resumen_inventory"] = $row->$pba;
								/*if( $tmp_warehouses_inventories["{$prefix}_resumen_inventory"] == null ){
									$tmp_warehouses_inventories["{$prefix}_resumen_inventory"] = '0.0000';
								}*/
								$tmp_warehouses_inventories["warehouse_id"] = $warehouses[$i];
							}
						}
						//$tmp_warehouses_inventories["{$prefix}_calculated_inventory_sum"] = 0;
						//$tmp_warehouses_inventories["{$prefix}_resumen_inventory_sum"] = 0;
						
						//var_dump( $tmp_warehouses_inventories );
					}
					array_push( $row_tmp['pp_inventories'], $tmp_warehouses_inventories );//$tmp["pp_inventories"]
				}//die('');
				array_push( $resp, $row_tmp );
			}
			return $resp;
		}
	}
	
?>