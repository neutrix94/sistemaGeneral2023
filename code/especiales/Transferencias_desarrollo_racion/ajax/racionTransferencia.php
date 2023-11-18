<?php
	/*if( ! include( '../../../../conexionMysqli.php' ) ){
		die( "Error al importar libreria" );
	}
	//var_dump($link); 
	$rT = new racionTransferencia( $link, true );
	echo $rT->calculate_ration();*/

	//var_dump( $test );
	class racionTransferencia
	{
		private $link;
		private $debug_mode;
		function __construct( $connection, $debug = false )
		{
			$this->link = $connection;
			$this->debug_mode = $debug;
		}

		public function getCurrentDate(){
			$sql = "SELECT DATE_FORMAT( NOW(), '%Y' ) AS current_year";
			$stm = $this->link->query( $sql );
			$row = $stm->fetch_assoc();

//return '2022';

			return $row['current_year'];
		}

		public function calculate_ration( $debug_mode = false ){
			$dont_ration = false;
			if( $this->debug_mode ){
				echo "<table class=\"table table-striped table-bordered\" id=\"ration_debug_table\">
						<thead>
							<tr>
								<td>ID PRODUCTO</td>
								<td>ORDEN LISTA</td>
								<td>PRODUCTO</td>
								<td>SUCURSAL</td>
								<td>INVENTARIO SUCURSAL</td>
								<td>ESTACIONALIDAD</td>
								<td>FALTANTE</td>
								<td>VENTAS SUCURSAL</td>
								<td>% VENTAS SUCURSAL</td>
								<td>RACION SUCURSAL</td>
								<td>TOTAL VENTAS SUCURSALES</td>
								<td>TOTAL INVENTARIO SUCURSALES</td>
								<td>INVENTARIO MATRIZ</td>
							</tr>
						</thead>
						<tbody>";
			}	
			$current_year = $this->getCurrentDate();
		//racion 1
			if( $this->debug_mode ){
				echo "<tr><td colspan=\"13\" class=\"text-center\">Primera racion</td></tr>";
			}
			
			$products = $this->getStoresRequerement( $current_year, 1 );
			if( sizeof( $products ) <= 0 ){
				$dont_ration = true;
			}
			foreach ( $products as $key => $product ) {
		//echo "<h2>Producto</h2>";
			//actualiza a stock bajo el producto en todas las sucursales
				$sql = "UPDATE sys_sucursales_producto SET stock_bajo = 1 WHERE id_producto = {$product['product_id']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar las sucursales a stock bajo : {$this->link->error}" );
				/*if( $this->debug_mode ){
					foreach ($product as $key2 => $val) {
						if( $this->debug_mode ){
							echo "{$key2} : {$val}<br>";
						}
					}
				}*/
				/*if( $this->debug_mode ){
					echo "<h3>Total</h3>";
				}*/
					$total = $this->getBaseData( $product['product_id'], $product['stores'], $current_year );
				/*if( $this->debug_mode ){
					foreach ($total as $key2 => $val) {
					echo "{$key2} : {$val}<br>";
					}
				}*/
					$product['totals'] = $total;
					$ration = $this->ration( $product['product_id'], $product['stores'], $total['salesTotal'], 
						$total['principal_warehouses_inventory'], $current_year, 1, $product['matriz_inventory'] );

				if( $this->debug_mode ){
					//echo "<h3>Racion</h3>";
					foreach ($ration as $key2 => $val) {
						echo "<tr>
								<td class=\"text-end\">{$val['product_id']}</td>
								<td class=\"text-end\">{$val['order_list']}</td>
								<td class=\"text-end\">{$val['product_name']}</td>
								<td class=\"text-end\">{$val['store_name']}</td>
								<td class=\"text-end\">{$val['store_inventory']}</td>
								<td class=\"text-end\">{$val['store_seasonality']}</td>
								<td class=\"text-end\">{$val['faltante_por_sucursal']}</td>
								<td class=\"text-end\">{$val['store_sales']}</td>
								<td class=\"text-end\">{$val['sales_porcent']}</td>
								<td class=\"text-end\">{$val['ration']}</td>
								<td class=\"text-end\">{$total['salesTotal']}</td>
								<td class=\"text-end\">{$total['principal_warehouses_inventory']}</td>
								<td class=\"text-end\">{$product['matriz_inventory']}</td>

						</tr>";
					/*	foreach ($val as $key3 => $dat) {
					echo "{$key3} : {$dat}<br>";
						}
					echo "<br>";*/
					}
				}
					$ration_1 = $this->save_ration( 1, $ration );
					if( $ration_1 != 'ok' ){
						die( "Error : {$ration_1}" );
					}
					//var_dump( $ration );
				
				/*if( $this->debug_mode ){
					echo '<br>///////////////////////////////////////////////////////////////////////////////////////////////<br>';
				}*/

				if( $this->debug_mode ){
					echo "<tr><td colspan=\"13\"></td></tr>";
				}
			}
		//racion 2
			if( ! $dont_ration ){
				if( $this->debug_mode ){
					echo "<tr><td colspan=\"13\" class=\"text-center\">Segunda racion</td></tr>";
					echo "<tr>
							<td>ID PRODUCTO</td>
							<td>ORDEN LISTA</td>
							<td>PRODUCTO</td>
							<td>SUCURSAL</td>
							<td>INVENTARIO SUCURSAL</td>
							<td>ESTACIONALIDAD</td>
							<td>FALTANTE</td>
							<td>VENTAS SUCURSAL</td>
							<td>% VENTAS SUCURSAL</td>
							<td>RACION SUCURSAL</td>
							<td>TOTAL VENTAS SUCURSALES</td>
							<td>TOTAL INVENTARIO SUCURSALES</td>
							<td>INVENTARIO MATRIZ</td>
						</tr>";
				}
				$products = $this->getStoresRequerement( $current_year, 2 );
				foreach ( $products as $key => $product ) {
			//echo "<h2>Producto</h2>";
				/*if( $this->debug_mode ){
					foreach ($product as $key2 => $val) {
					echo "{$key2} : {$val}<br>";
					}
				}*/

				/*if( $this->debug_mode ){
					echo "<h3>Total</h3>";
				}*/
				
				$total = $this->getBaseData( $product['product_id'], $product['stores'], $current_year );
				
				/*if( $this->debug_mode ){
					foreach ($total as $key2 => $val) {
					echo "{$key2} : {$val}<br>";
					}
				}*/
						$product['totals'] = $total;
						$ration = $this->ration( $product['product_id'], $product['stores'], $total['salesTotal'], 
							$total['principal_warehouses_inventory'], $current_year, 2, $product['matriz_inventory'] );
			
				if( $this->debug_mode ){
				//	echo "<h3>Racion</h3>";
					foreach ($ration as $key2 => $val) {
						echo "<tr>
								<td class=\"text-end\">{$val['product_id']}</td>
								<td class=\"text-end\">{$val['order_list']}</td>
								<td class=\"text-end\">{$val['product_name']}</td>
								<td class=\"text-end\">{$val['store_name']}</td>
								<td class=\"text-end\">{$val['store_inventory']}</td>
								<td class=\"text-end\">{$val['store_seasonality']}</td>
								<td class=\"text-end\">{$val['faltante_por_sucursal']}</td>
								<td class=\"text-end\">{$val['store_sales']}</td>
								<td class=\"text-end\">{$val['sales_porcent']}</td>
								<td class=\"text-end\">{$val['ration']}</td>
								<td class=\"text-end\">{$total['salesTotal']}</td>
								<td class=\"text-end\">{$total['principal_warehouses_inventory']}</td>
								<td class=\"text-end\">{$product['matriz_inventory']}</td>

						</tr>";
						/*foreach ($val as $key3 => $dat) {
					echo "{$key3} : {$dat}<br>";
						}
					echo "<br>";*/
					}
				}
				
				$ration_1 = $this->save_ration( 2, $ration );
				if( $ration_1 != 'ok' ){
					die( "Error : {$ration_1}" );
				}
				
				/*if( $this->debug_mode ){
					echo '<br>///////////////////////////////////////////////////////////////////////////////////////////////<br>';
				}*/
				if( $this->debug_mode ){
					echo "<tr><td colspan=\"13\"></td></tr>";
				}
			}
		//racion 3
			if( $this->debug_mode ){
				echo "<tr><td colspan=\"13\" class=\"text-center\">Tercera racion</td></tr>";
				echo "<tr>
						<td>ID PRODUCTO</td>
						<td>ORDEN LISTA</td>
						<td>PRODUCTO</td>
						<td>SUCURSAL</td>
						<td>INVENTARIO SUCURSAL</td>
						<td>ESTACIONALIDAD</td>
						<td>FALTANTE</td>
						<td>VENTAS SUCURSAL</td>
						<td>% VENTAS SUCURSAL</td>
						<td>RACION SUCURSAL</td>
						<td>TOTAL VENTAS SUCURSALES</td>
						<td>TOTAL INVENTARIO SUCURSALES</td>
						<td>INVENTARIO MATRIZ</td>
					</tr>";
			}
			$products = $this->getStoresRequerement( $current_year, 3 );
			foreach ( $products as $key => $product ) {
			
				/*if( $this->debug_mode ){
					foreach ($product as $key2 => $val) {
					echo "{$key2} : {$val}<br>";
					}
					echo "<h3>Total</h3>";
				}*/
				$total = $this->getBaseData( $product['product_id'], $product['stores'], $current_year );
				
				/*if( $this->debug_mode ){
					foreach ($total as $key2 => $val) {
						echo "{$key2} : {$val}<br>";
					}
				}*/
				
				$product['totals'] = $total;
				$ration = $this->ration( $product['product_id'], $product['stores'], $total['salesTotal'], 
						$total['principal_warehouses_inventory'], $current_year, 3, $product['matriz_inventory'] );
				
				/*if( $this->debug_mode ){
					echo "<h3 class=\"text-center\">Racion</h3>";
				}*/

				if( $this->debug_mode ){
					foreach ($ration as $key2 => $val) {
						echo "<tr>
								<td class=\"text-end\">{$val['product_id']}</td>
								<td class=\"text-end\">{$val['order_list']}</td>
								<td class=\"text-end\">{$val['product_name']}</td>
								<td class=\"text-end\">{$val['store_name']}</td>
								<td class=\"text-end\">{$val['store_inventory']}</td>
								<td class=\"text-end\">{$val['store_seasonality']}</td>
								<td class=\"text-end\">{$val['faltante_por_sucursal']}</td>
								<td class=\"text-end\">{$val['store_sales']}</td>
								<td class=\"text-end\">{$val['sales_porcent']}</td>
								<td class=\"text-end\">{$val['ration']}</td>
								<td class=\"text-end\">{$total['salesTotal']}</td>
								<td class=\"text-end\">{$total['principal_warehouses_inventory']}</td>
								<td class=\"text-end\">{$product['matriz_inventory']}</td>

						</tr>";
						/*foreach ($val as $key3 => $dat) {
							echo "{$key3} : {$dat}<br>";
						}
						echo "<br>";*/
					}

				}
				$ration_1 = $this->save_ration( 3, $ration );
				if( $ration_1 != 'ok' ){
					die( "Error : {$ration_1}" );
				}

				if( $this->debug_mode ){
					echo "<tr><td colspan=\"13\"></td></tr>";
				}
			}
		}

			if( $this->debug_mode ){
				echo "</tbody>
					</table>
				<br>
				<div class=\"row\">
					<div class=\"col-3\"></div>
					<div class=\"col-6\">
						<button class=\"btn btn-primary form-control\" onclick=\"download_ration_debug();\">
							<i class=\"icon-excel\">Descargar CSV</i>
						</button>
						<br><br>
					</div>
				</div>";
			}
		}

		public function getBaseData( $product_id, $stores_ids, $current_year ){
//consulta el inventario de los almacenes principales y ventas totales del año actual
			$sql="SELECT
					aux.id_producto AS product_id,
					(IF(aux.inventarioAlmacenesPrincipales IS NULL OR aux.inventarioAlmacenesPrincipales < 0,
						0,
						aux.inventarioAlmacenesPrincipales)
					) AS principal_warehouses_inventory,
					IF(aux.ventas_totales IS NULL,
						0,
						aux.ventas_totales
					) AS salesTotal
				FROM(
					SELECT
						p.id_productos as id_producto,
						SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
						SUM(IF(alm.es_almacen=1 AND ma.id_almacen NOT IN (1) AND tm.id_tipo_movimiento=2 
							AND alm.es_externo=0 AND ma.fecha LIKE '%{$current_year}%'
							AND IF( cfg.incluir_ventas_mayoreo_racion = 0 , ped.tipo_pedido = 0, 1 = 1 ),
							md.cantidad,
							0
							)
						) AS ventas_totales
					FROM ec_productos p
					LEFT JOIN ec_movimiento_detalle md 
					ON p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma 
					ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm 
					ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm 
					ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s 
					ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN ec_configuracion_sucursal cfg
					ON cfg.id_sucursal = s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp 
					ON s.id_sucursal=sp.id_sucursal 
					AND p.id_productos=sp.id_producto
					LEFT JOIN ec_pedidos ped
					ON ped.id_pedido = ma.id_pedido
					WHERE s.activo=1 
					AND s.id_sucursal IN( 1,{$stores_ids} )
					AND sp.estado_suc=1
					AND p.id_productos={$product_id}
				)aux";
				$stm = $this->link->query( $sql ) or die( "Error al calcular las cantidades totales para promediar : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				return $row;
		}

		function ration( $product_id, $stores_ids, $sales_total, $inventories_total, $current_year, $ration_number, $matriz_inventory ){
			$resp = array();
			$condition = "";
			if( $ration_number == 2 ){
				$condition = "AND ( sp.racion_1 > 0 OR sp.id_sucursal = 1 )";
			}else if( $ration_number == 3 ){
				$condition = "AND ( sp.racion_2 > 0 OR sp.id_sucursal = 1 )";
			}

			$sql="SELECT 
					aux.id_productos AS product_id,
					aux.id_sucursal AS store_id,
					aux.orden_lista AS order_list,
					aux.nombre AS product_name,
					aux.store_name,
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,
						0,
						aux.inventarioAlmacenPrincipalPorSucursal
					) AS store_inventory,
					ep.maximo AS store_seasonality,
					( ep.maximo - aux.inventarioAlmacenPrincipalPorSucursal ) AS faltante_por_sucursal,
					IF(aux.store_sales IS NULL,
						0,
						aux.store_sales
					) AS store_sales,
					( aux.store_sales / {$sales_total} ) AS store_sales_porcent,
					aux.id_estacionalidad
				FROM(
					SELECT
						p.id_productos,
						p.orden_lista,
						p.nombre,
						s.nombre AS store_name,
						s.id_sucursal,
						s.id_estacionalidad,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL 
								AND alm.id_sucursal=s.id_sucursal 
								AND alm.es_externo=0 
								AND tm.id_tipo_movimiento=2 
								AND ma.fecha LIKE '%$current_year%'
								AND IF( cfg.incluir_ventas_mayoreo_racion = 0 , ped.tipo_pedido = 0, 1 = 1 ),
								md.cantidad,
								0)
							) 
						))AS store_sales,
						( SUM(
							IF(alm.es_almacen = 1 
								AND ma.id_sucursal = s.id_sucursal,
								(md.cantidad * tm.afecta),
								0
							)
						)
						) AS inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md 
					ON p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma 
					ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm 
					ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_pedidos ped
					ON ped.id_pedido = ma.id_pedido/*union con pedidos*/
					LEFT JOIN ec_almacen alm 
					ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s 
					ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN ec_configuracion_sucursal cfg
					ON cfg.id_sucursal = s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 
					ON s.id_sucursal=sp_1.id_sucursal 
					AND sp_1.id_producto = {$product_id}
					WHERE p.id_productos = {$product_id}
					AND sp_1.estado_suc=1
					{$contition}
					AND s.id_sucursal IN ( {$stores_ids} )
					GROUP BY s.id_sucursal
				)aux
				LEFT JOIN ec_estacionalidad e
				ON e.id_estacionalidad = aux.id_estacionalidad
				LEFT JOIN ec_estacionalidad_producto ep
				ON ep.id_estacionalidad = e.id_estacionalidad
				AND ep.id_producto = aux.id_productos
				GROUP BY aux.id_sucursal";
		//die( $sql );
				$stm = $this->link->query( $sql ) or die( "Error al calcular las cantidades totales para promediar : {$sql} {$this->link->error}" );
				//$row = $stm->fetch_assoc();
				//echo $sql;
				$excedent = 0;
				$counter = 0;
				$tmp = 0;
				$bigger = 0;
				$store_sales = 0;
				$sales_porcent = 0;
				$matriz_inventory_tmp = $matriz_inventory; 
				while ( $row = $stm->fetch_assoc() ){
				//consulta si hay producto en transferencia
					$sql = "SELECT
								SUM( IF( tp.id_transferencia_producto IS NULL, 0, tp.cantidad ) ) AS transfer_quantity
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_transferencias t
							ON tp.id_transferencia = t.id_transferencia
							WHERE t.fecha LIKE '%{$current_year}%'
							AND t.id_sucursal = {$row['store_id']}
							AND ( t.id_estado >= 2 AND t.id_estado <= 8 )
							AND tp.id_producto_or = {$row['product_id']}";
					$stm_2 = $this->link->query( $sql ) or die( "Error al consultar las transferencias pendientes al racionar : {$sql} <br>{$this->link->error}" );
					if( $stm_2->num_rows > 0 ){
						$tmp_row = $stm_2->fetch_assoc();
						$row['store_inventory'] = ( $row['store_inventory'] + $tmp_row['transfer_quantity'] );
					}
					//echo "<br>Formula : ( {$row['store_sales']} / {$sales_total} * {$inventories_total} ) - {$row['store_inventory']} <br>";
					$row['ration'] = ( ( $row['store_sales'] / $sales_total ) * $inventories_total ) - $row['store_inventory'];
				//implementacion Oscar 2023 para llevar el registro de los productos que tienen excedente en racion
					if( $row['ration'] < 0 ){
						$sql = "INSERT INTO ec_transferencias_productos_racion_excedente( id_producto, id_sucursal, 
							inventario, racion, diferencia, fecha_alta ) VALUES ( {$row['product_id']}, {$row['store_id']}, 
							{$row['store_inventory']}, ( ( {$row['store_sales']} / {$sales_total} ) * {$inventories_total} ), 
							( {$row['store_inventory']} - ( ( {$row['store_sales']} / {$sales_total} ) * {$inventories_total} ) ),
							NOW() )";
						$stm_aux = $this->link->query( $sql ) or die( "Error al insertar el producto con racion excedente : {$this->link->error}" );
					}
				//
					$row['ration'] = round( $row['ration'] );
					$matriz_inventory_tmp -= $row['ration'];
					$sales_porcent = ( $row['store_sales'] / $sales_total );
					$row['sales_porcent'] = $sales_porcent;
					if( $sales_porcent > $tmp  ){
						$tmp = ( $row['store_sales'] / $sales_total );
						$bigger = $counter;
					}
					
					/*if( $this->debug_mode ){
						echo "<br> Racion : ( {$row['store_sales']} / {$sales_total} ) * {$inventories_total}<br>
						 = {$row['ration']}<br>";
					}*/
					$resp[] = $row;
					$counter ++;
				}
				if( $matriz_inventory_tmp > 0 ){//asigna el; restante si es el caso
					$resp[$bigger]['ration'] = ( $resp[$bigger]['ration'] + $matriz_inventory_tmp );
				}
				return $resp;
		}

		public function save_ration( $ration_type, $rations ){
			foreach ($rations as $key => $ration) {
				$ration['store_inventory'] = ( $ration['store_inventory'] <0 ? 0 : $ration['store_inventory'] );//- {$ration['store_inventory']}- {$ration['store_inventory']}
				/*if( ( $ration_type == 1 || $ration_type == 2 ) && $ration['store_inventory'] > $ration['ration'] ){
					$ration['ration'] = 0;
				}*/
				$sql="UPDATE sys_sucursales_producto
							SET racion_{$ration_type} = IF( ROUND( {$ration['ration']}  ) < 0, 0, ROUND( {$ration['ration']} ) )
						WHERE id_sucursal = {$ration['store_id']} 
						AND id_producto = {$ration['product_id']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar racion : {$sql} {$this->link->error}" );
				if( $this->debug_mode ){
				//	echo "{$sql}<br>";
				}
			}
			return 'ok';	
		}
/*1 Consulta productos que entran en racion*/
		public function getStoresRequerement( $current_year, $ration_type ){
			$resp = array();
			$condition = "";
			$ration_condition = "";
			$inventory_vs_req_per_product = "1";
			$inventory_vs_req = "1";
			if( $ration_type == 1 ){
				$ration_condition = "AND p.id_productos NOT IN( SELECT 
															DISTINCT( id_producto )
														FROM sys_sucursales_producto 
														WHERE stock_bajo =1  )";
				$inventory_vs_req_per_product = "ep.maximo > ax.inventario";
				$inventory_vs_req = "ax1.required_quantity > ax1.matriz_inventory";
			}else if( $ration_type == 2 ){
				$condition = "AND ( sp.racion_1 > 0 OR sp.id_sucursal = 1 )";
			}else if( $ration_type == 3 ){
				$condition = "AND ( sp.racion_2 > 0 OR sp.id_sucursal = 1 )";
			}
				$sql="SELECT
						ax1.product_id,
						ax1.product_name,
						ax1.id_sucursal,
						ax1.required_quantity - ax1.transfer_quantity AS required_quantity,
						ax1.matriz_inventory,
						ax1.required_quantity AS requirement_without_transfers,
						ax1.transfer_quantity,
						ax1.product_sales,
						ax1.stores
					FROM(
						SELECT
							ax.id_producto AS product_id,
							ax.product_name,
							ax.id_sucursal,
							/*GROUP_CONCAT( ax.id_sucursal SEPARATOR ',' ) AS stores,*/
							SUM( IF( ax.id_sucursal = 1, 0 , (ep.maximo - ( ax.inventario + ax.transfer_quantity ) ) ) ) AS required_quantity,
							( SELECT inventario FROM ec_almacen_producto WHERE id_almacen = 1 AND id_producto = ax.id_producto ) AS matriz_inventory,
							SUM( ax.transfer_quantity ) AS transfer_quantity,
							ax.product_sales,
							GROUP_CONCAT( DISTINCT( ax.stores ) SEPARATOR ',' ) AS stores,
							ax.orden_lista
						FROM(
							SELECT
								p.id_productos AS id_producto,
								p.orden_lista,
								p.nombre AS product_name,
								sp.id_sucursal,
								s.id_estacionalidad,
								alm.nombre,
								( IF( ap.id_almacen = 1, 0, ap.inventario ) ) AS inventario,
								SUM( IF ( t.id_sucursal = sp.id_sucursal  
										AND t.fecha LIKE '%{$current_year}%'
										AND ( t.id_estado >= 2 AND t.id_estado <= 8 ), 
										tp.cantidad, 
										0
									)
								) AS transfer_quantity,
								SUM( IF( ped.id_pedido IS NOT NULL
									AND IF( cfg.incluir_ventas_mayoreo_racion = 0 , ped.tipo_pedido = 0, 1 = 1 ), 0, 1 ) ) AS product_sales,
								/*GROUP_CONCAT( DISTINCT( ped.id_sucursal ) SEPARATOR ',' ) AS stores*/
								ped.id_sucursal AS stores
							FROM ec_productos p
								LEFT JOIN ec_pedidos_detalle pd
								ON pd.id_producto = p.id_productos
								LEFT JOIN ec_pedidos ped
								ON pd.id_pedido = ped.id_pedido
							LEFT JOIN sys_sucursales_producto sp 
							ON p.id_productos=sp.id_producto
							AND sp.id_sucursal = ped.id_sucursal
							LEFT JOIN sys_sucursales s 
							ON s.id_sucursal=sp.id_sucursal
							LEFT JOIN ec_configuracion_sucursal cfg
							ON cfg.id_sucursal = s.id_sucursal

							LEFT JOIN ec_almacen alm 
							ON s.id_sucursal = alm.id_sucursal
							AND alm.es_almacen = 1
								
							
							LEFT JOIN ec_almacen_producto ap
							ON ap.id_almacen=alm.id_almacen
							AND ap.id_producto = p.id_productos
								
								LEFT JOIN ec_transferencia_productos tp
								ON tp.id_producto_or = p.id_productos
								LEFT JOIN ec_transferencias t
								ON tp.id_transferencia = t.id_transferencia
								AND t.id_sucursal = s.id_sucursal
							
							WHERE s.activo=1 
							AND s.id_sucursal>1
							AND sp.estado_suc=1
							AND p.es_maquilado = 0
							AND p.muestra_paleta = 0
							AND p.habilitado = 1
							{$condition}
								AND ped.fecha_alta LIKE '%{$current_year}%'
								AND ped.id_sucursal > 1
								{$ration_condition} 
							
							/*AND p.id_productos={$product_id}*/
							GROUP BY ap.id_almacen_producto, ped.id_sucursal
						)ax
						LEFT JOIN ec_estacionalidad e
						ON e.id_estacionalidad = ax.id_estacionalidad
						LEFT JOIN ec_estacionalidad_producto ep
						ON ep.id_estacionalidad = e.id_estacionalidad
						AND ep.id_producto = ax.id_producto
						WHERE {$inventory_vs_req_per_product}
						GROUP BY ax.id_producto
					)ax1
					WHERE {$inventory_vs_req}
					AND ax1.matriz_inventory > 0
					GROUP BY ax1.product_id
					ORDER BY ax1.orden_lista ASC";
//echo( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar productos que entran en racion : {$this->link->error}" );
		//$row = $stm->fetch_assoc();
			while ( $row = $stm->fetch_assoc() ){
				//var_dump( $row );
				//echo "<br>";
				$resp[] = $row;
			}
			return $resp;
		}
	}
	
?>