<?php

	if( isset( $_GET['exhibition_flag'] ) || isset( $_POST['exhibition_flag'] ) ){
		include( '../../../../conect.php' );
		include( '../../../../conexionMysqli.php' );
		$eP = new exhibitionProducts( $link );
		$action = ( isset( $_GET['exhibition_flag'] ) ? $_GET['exhibition_flag'] : $_POST['exhibition_flag'] );
		switch ( $action ) {
			case 'seeker':
				$key = ( isset( $_GET['key'] ) ? $_GET['key'] : $_POST['key'] );
				echo $eP->validateBarcode( $key );
			break;

			case 'getOptionsByProductId' : 
				$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
				$type = ( isset( $_GET['type'] ) ? $_GET['type'] : $_POST['type'] );
				echo $eP->getOptionsByProductId( $product_id, $user_sucursal, 1, $type );
			break;

			case 'new_products_seeker' :
				$key = ( isset( $_GET['key'] ) ? $_GET['key'] : $_POST['key'] );
				echo $eP->productsSeeker( $key );
			break;

			case 'saveNewProduct' :
				$data = ( isset( $_GET['values'] ) ? $_GET['values'] : $_POST['values'] );
				//die( "data : {$data}" );
				echo $eP->saveNewProduct( $data, $user_id );
			break;

			case 'getStoresProducts':
				$product_provider_id = ( isset( $_GET['product_provider_id'] ) ? $_GET['product_provider_id'] : $_POST['product_provider_id'] );
				echo $eP->getStoresProducts( $product_provider_id );
			break; 
			case 'saveRow' : 
				$data = ( isset( $_GET['arr'] ) ? $_GET['arr'] : $_POST['arr'] );
				echo $eP->saveRow( $data, $user_id, $user_sucursal );
			break;
			case 'cancelRow' :
				$exhibition_id = ( isset( $_GET['exhibition_header_id'] ) ? $_GET['exhibition_header_id'] : $_POST['exhibition_header_id'] );
				echo $eP->cancelRow( $exhibition_id );
			break;
			case 'getPendingList' :
				echo $eP->getPendingList( $user_sucursal );
			break;

			case 'updateExhibitionRows' :
				$data = ( isset( $_GET['data'] ) ? $_GET['data'] : $_POST['data'] );
				echo $eP->updateExhibitionRows( $data );
			break;

			/*case 'getProductProviderNotes':
				$product_provider_id = ( isset( $_GET['product_provider_id'] ) ? $_GET['product_provider_id'] : $_POST['product_provider_id'] );
				echo $eP->getProductProviderNotes( $product_provider_id );
			break;*/
			
			default:
				die( "Access denied ON : {$action}" );
			break;
		}

	}
	class exhibitionProducts
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function cancelRow( $exhibition_id ){
			$this->link->autocommit( false );
		//actualiza el detalle
			$sql = "UPDATE ec_temporal_exhibicion_proveedor_producto 
						SET piezas_ya_no_se_exhiben = ( cantidad - piezas_exhibidas )
					WHERE id_temporal_exhibicion = {$exhibition_id}";
			$stm = $this->link->query($sql) or die("Error al actualizar piezas agotadas nivel provedor producto : {$sql} {$this->link->error} ");
			
		//actualiza cabecera
			$sql = "UPDATE ec_temporal_exhibicion 
						SET piezas_ya_no_se_exhiben = ( cantidad - piezas_exhibidas )
					WHERE id_temporal_exhibicion = {$exhibition_id}";
			$stm = $this->link->query($sql) or die("Error al actualizar piezas agotadas nivel producto : {$sql} {$this->link->error} ");
			$this->link->autocommit( true );
			return 'ok|Los cambios fueron guardados exitosamente!';
		}

		public function saveRow( $data, $user_id, $store_id ){
			//die( $data );
			$details = explode( "|~|", $data );
		//extraemos los almacenes
			$sql="(SELECT 
					id_almacen
				FROM ec_almacen WHERE id_sucursal={$store_id} 
				AND es_almacen=1 )
				UNION 
				(SELECT id_almacen
				FROM ec_almacen WHERE id_sucursal = {$store_id} 
				AND nombre LIKE '%exhibicion%' )";
		
			$stm=$this->link->query($sql) or die("Error al consultar los almacénes de la sucursal : {$sql} {$this->link->error} ");
			$row = $stm->fetch_row();
			$principal_warehouse = $row[0];
			$row = $stm->fetch_row();
			$exhibition_warehouse = $row[0];

			$this->link->autocommit( false );//inicio de la transacción
			$stm = $this->link->query( $sql ) or die( "Error al actualizar temporal de exhibicion a nivel producto : {$this->link->error} {$sql}" );	
			foreach ($details as $key => $exhibition_detail ) {
				$detail = explode( '|' , $exhibition_detail );
				if( $key > 0 ){
					$sql = "UPDATE ec_temporal_exhibicion_proveedor_producto
							SET piezas_exhibidas = {$detail[2]},
							piezas_ya_no_se_exhiben = {$detail[3]}
						WHERE id_temporal_exhibicion_proveedor_producto = {$detail[1]}";
					//echo $sql;
					$stm = $this->link->query( $sql ) or die( "Error al actualizar temporal de exhibicion a nivel proveedor producto : {$this->link->error} {$sql}" );			
				}else{
					//insertamos los detalles de movimientos
					$sql = "UPDATE ec_temporal_exhibicion 
								SET piezas_exhibidas = {$detail[1]},
								piezas_ya_no_se_exhiben = {$detail[2]}
							WHERE id_temporal_exhibicion = {$detail[0]}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar temporal de exhibicion a nivel producto : {$this->link->error} {$sql}" );	

				}
			}
			for( $i = 0; $i <= 1; $i++ ){
				$movement_type = 6;//salida
				$warehouse_id = $principal_warehouse;
				if( $i == 0 ){
					$movement_type = 5;//entrada
					$warehouse_id = $exhibition_warehouse;
				}
			//insertamos cabecera
				$sql="INSERT INTO ec_movimiento_almacen ( /*1*/id_tipo_movimiento, /*2*/id_usuario, 
					/*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/observaciones, /*7*/id_almacen, /*8*/id_pedido ) 
				VALUES ( /*1*/{$movement_type},/*2*/{$user_id},/*3*/{$store_id},/*4*/now(),/*5*/now(), 
					/*6*/'{$obs}', /*7*/{$warehouse_id}, /*9*/-1 )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar la cabecera del movimiento almacen : {$this->link->error} {$sql}" );			
				$sql = "SELECT LAST_INSERT_ID()";
				$stm_2 = $this->link->query( $sql ) or die( "Error al consultar id de cabecera de movimiento almacen : {$this->link->error} {$sql}" );
				$header_id = $stm_2->fetch_row();
				$header_id = $header_id[0];
				foreach ( $details as $key => $exhibition_detail ) {
					if( $key > 0 ){
						//die( $exhibition_detail );
						$detail = explode( '|' , $exhibition_detail );
						//consulta si el producto es maquilado
							$sql = "SELECT
										p.id_productos AS product_id,
										IF( pd.cantidad IS NULL, 0, pd.cantidad) AS quantity,
										IF( pd.id_producto_ordigen IS NULL, -1, pd.id_producto_ordigen ) AS origin_product_id,
										IF( pd.id_producto_ordigen IS NULL, 
											{$detail[5]},  
											(SELECT 
												ipp.id_proveedor_producto
											FROM ec_inventario_proveedor_producto ipp
											WHERE ipp.id_producto = pd.id_producto_ordigen
											AND ipp.id_almacen = {$principal_warehouse}
											ORDER BY ipp.inventario DESC 
											LIMIT 1 )
										) AS product_provider_id
									FROM ec_productos p
									LEFT JOIN ec_productos_detalle pd
									ON p.id_productos = pd.id_producto
									WHERE p.id_productos = {$detail[4]}";//die( $sql );
							$maquile_stm = $this->link->query( $sql ) or die( "Error al consultar si el producto es maquilado : {$sql} {$this->link->error}" );
							$maquile_row = $maquile_stm->fetch_assoc();
							if( $maquile_row['origin_product_id'] != -1 ){
								$detail[4] = $maquile_row['origin_product_id'];
								$detail[2] = ( $detail[2] * $maquile_row['quantity'] );
								$detail[5] = $maquile_row['product_provider_id'];
							}
					//inserta detalles de movimiento	
						//if( $detail[2] > 0 ){
							$sql = "INSERT INTO ec_movimiento_detalle( id_movimiento, id_producto, cantidad, cantidad_surtida, 
							id_proveedor_producto, id_pedido_detalle ) VALUES( {$header_id}, {$detail[4]}, {$detail[2]}, {$detail[2]}, {$detail[5]}, -1 )";
							$stm_2 = $this->link->query( $sql ) or die( "Error al insertar detalle de movimiento almacen : {$this->link->error} {$sql}" );
						//}
					}
				}
			}
			$this->link->autocommit( true );//autoriza transacción
			return 'ok|Los cambios fueron guardados exitosamente!';
		}

	//obtener lista de pendientes de exhibir
		public function getPendingList( $store_id ){
			$resp = "";
			$sql = "SELECT 
				id_almacen AS warehouse_id 
			FROM ec_almacen 
			WHERE id_sucursal = {$store_id} 
			AND es_almacen = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el almacen principal de la sucursal {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$principal_warehouse_id = $row['warehouse_id'];
		//consultamos los datos a nivel producto
			$sql="SELECT 
					/*0*/te.id_temporal_exhibicion,
					/*1*/p.orden_lista,
					/*2*/p.nombre,
					/*3*/ap.inventario,
					/*4*/(te.cantidad-te.piezas_exhibidas)-te.piezas_ya_no_se_exhiben,
					/*5*/0,
					/*6*/0,
					/*7*/p.id_productos,
					/*8*/( SELECT IF( et.id_exclusion_transferencia IS NULL, '', 'icon-bookmark text-danger' ) FROM ec_exclusiones_transferencia et WHERE et.id_producto = p.id_productos )
					/*9*/'-',
					/*10*/IF( te.es_nuevo = 1, 
							'icon-star text-info', 
							IF(te.tiene_devolucion = 1, 'icon-ccw text-primary', '')
						)
				FROM ec_temporal_exhibicion te
				LEFT JOIN ec_productos p ON te.id_producto=p.id_productos
				LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
				AND sp.id_sucursal IN($store_id)
				LEFT JOIN ec_almacen_producto ap
				ON ap.id_almacen = {$principal_warehouse_id}
				AND ap.id_producto = p.id_productos
				WHERE te.id_sucursal = {$store_id}
				AND (te.cantidad-te.piezas_exhibidas)-te.piezas_ya_no_se_exhiben>0 
				AND te.es_valido=1
				ORDER BY p.orden_lista, te.id_temporal_exhibicion ASC";
//die( $sql );
			$eje = $this->link->query($sql)or die("Error al consultar productos temporales en exhibición!!!\n\n{$sql}\n\n{$this->link->error}");
			$c=0;//declaramos contador en cero
			$counter = 0;
			while($r = $eje->fetch_row() ){
				$r[3] = str_replace('.0000', '', $r[3] );
				$r[4] = str_replace('.0000', '', $r[4] );
				if( $r[9] == 'icon-ccw text-primary' ){
					$r[4] = '';
				}
				$resp .= $this->build_row( $r, $c, 'p' );
			//consulta el nivel proveedor producto
				$sql = "SELECT
							/*0*/epp.id_temporal_exhibicion AS pp_exh_id,
							/*1*/epp.id_producto AS product_id,
							/*2*/CONCAT( pp.clave_proveedor , ' ( ', pp.id_proveedor_producto, ' )' ),
							/*3*/ipp.inventario,
							/*4*/epp.cantidad,
							/*5*/0,
							/*6*/epp.id_proveedor_producto AS product_provider_id,
							/*7*/epp.id_producto,
							/*8*/'',
							/*9*/epp.id_temporal_exhibicion_proveedor_producto
						FROM ec_temporal_exhibicion_proveedor_producto epp
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_proveedor_producto = epp.id_proveedor_producto
						LEFT JOIN ec_inventario_proveedor_producto ipp
						ON ipp.id_proveedor_producto = pp.id_proveedor_producto
						AND ipp.id_almacen IN ( {$principal_warehouse_id} )
						WHERE epp.id_temporal_exhibicion = {$r[0]}";
				$pp_stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de los proveedores producto : {$sql} {$this->link->error}"  );
				while ( $pp_row = $pp_stm->fetch_row() ) {
					$pp_row[3] = str_replace('.0000', '', $pp_row[3] );
					$pp_row[4] = str_replace('.0000', '', $pp_row[4] );
					if( $r[9] == 'icon-ccw text-primary' ){
						$pp_row[4] = '';
					}
					$resp .= $this->build_row( $pp_row, $c, 'p_p', $counter );
					$counter ++;//incrementamos el contador ( proveedor producto )
				}
				$c++;//incrementamos el contador ( producto )
			}
			return $resp;
		}
		public function build_row( $row, $c, $type, $secondary_counter = null ){
			$color = "";
			$principal = "0";
			if($c%2==0){
				$color='#E6E8AB';
			}else{
				$color='#BAD8E6';
			}
			$return_style = '';
			$return_onclick = '';
			if( $row[4] == '' ){
				$return_style = "style=\"background-color : red;\"";
				$return_onclick = "onclick=\"editExhibitionRow( {$row[0]} )\"";
			}
			$onclick = "";
			$onclick_save = "";
			$onclick_cancel = "";
			$detail_id = "";
			if( $type == 'p_p' ){
				$onclick = "onclick=\"editaCelda(5,{$secondary_counter});\"";
				$is_principal = "is_principal=\"0\"";
				$detail_id = "detail_id=\"{$row[9]}\"";
				//$onclick_2 = "onclick=\"editaCelda(8,{$secondary_counter});\"";
			}else{
				$onclick_save = "onclick=\"save_row( {$row[0]} );\"";
				$onclick_cancel = "onclick=\"cancel_row( {$row[0]} );\"";
				$is_principal = "is_principal=\"1\"";
			}
			$resp = "<tr id=\"{$type}_fila_{$c}\" exhibition_id=\"{$row[0]}\" {$is_principal} {$detail_id}
						style=\"background : {$color};\" onclick=\"resalta_fila( {$c} );\" tabindex=\"{$c}\">
						<td style=\"display:none;\" id=\"{$type}_1_{$c}\" value=\"{$row[6]}\">{$row[0]}</td>
						<td style=\"\" id=\"\" class=\"{$row[8]} text-center {$row[9]}\"></td>
						<td id=\"{$type}_2_{$c}\" width=\"10%\" style=\"padding:10px;\" align=\"right\">{$row[1]}</td>
						<td>{$row[2]}</td>
						<td id=\"{$type}_3_{$c}\" align=\"right\">{$row[3]}</td>
						<td id=\"{$type}_4_{$c}\" align=\"right\" {$return_style} {$return_onclick}>{$row[4]}</td>
						<td id=\"{$type}_5_{$c}\" align=\"right\" {$onclick}>{$row[5]}</td>
						<td style=\"display:none;\" id=\"{$type}_6_{$c}\" >{$row[6]}</td>
						<td style=\"display:none;\" id=\"{$type}_7_{$c}\" >{$row[3]}</td>
						<td style=\"\" id=\"{$type}_8_{$c}\" {$onclick_2}>0</td>";
	//botones
			if( $type == 'p' ){
				$resp .= "<td>
							<button class=\"btn btn-success\" {$onclick_save}>
								<i class=\"icon-ok-circle\"></i>
							</button>
						</td>";
				$resp .= "<td class=\"text-center\">
							<button class=\"btn btn-danger\" {$onclick_cancel}>
								<i class=\"icon-cancel-circled\"></i>
							</button>
						</td>";
			}else{
				$resp .= "<td colspan=\"2\" class=\"text-center\">
					<button 
						class=\"btn btn-info\"
						onclick=\"getProductProviderNotes( {$row[6]} );\"
					>
						<i class=\"icon-sticky-note-o\"></i>
					</button>
				</td>";
			}
			$resp .= '</tr>';
			return $resp;
		}

//buscador por codigo de barras
		public function validateBarcode( $barcode ){
		//verifica si el código de barras existe
			$sql = "SELECT
						pp.id_proveedor_producto AS product_provider_id,
						pp.id_producto AS product_id,
						pp.presentacion_caja AS pieces_per_box,
						( SELECT 
							IF( pd.id_producto IS NULL, 
								0, 
								IF( pd.id_producto = p.id_productos, 
									1, 
									-1  
								) 
							) 
						  FROM ec_productos_detalle pd
						  WHERE pd.id_producto = p.id_productos
						  OR pd.id_producto_ordigen = p.id_productos
						) AS is_maquiled
					FROM ec_temporal_exhibicion_proveedor_producto tepp
					LEFT JOIN ec_proveedor_producto pp
					ON tepp.id_proveedor_producto = pp.id_proveedor_producto
					LEFT JOIN ec_productos p 
					ON pp.id_producto = p.id_productos
					WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
					AND tepp.resuelto = 0
					GROUP BY tepp.id_temporal_exhibicion_proveedor_producto";
			$stm1 = $this->link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : " . $link->error );
//die( $sql );
			if( $stm1->num_rows <= 0 ){
				return $this->seekByName( $barcode, $link );
			}
			$scanned_data = $stm1->fetch_assoc();
			return "was_found|" . json_encode( $scanned_data );
		}
//buscador por nombre
		public function seekByName( $barcode ){
			$barcode_array = explode(' ', $barcode );
			$condition = " OR (";
			foreach ($barcode_array as $key => $barcode_txt ) {
				$condition .= ( $condition == ' OR (' ? '' : ' AND' );
				$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
			}
			$condition .= " )";
			$sql = "SELECT
					pp.id_producto AS product_id,
					CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>' ) AS name
				FROM ec_temporal_exhibicion_proveedor_producto tepp
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tepp.id_proveedor_producto
				LEFT JOIN ec_productos p
				ON pp.id_producto = p.id_productos
				WHERE p.muestra_paleta = 0
				AND p.es_maquilado = 0
				AND p.habilitado = 1 
				AND ( pp.clave_proveedor LIKE '%{$barcode}%'
				{$condition} OR p.orden_lista = '{$barcode}'  ) 
				AND tepp.id_proveedor_producto IS NOT NULL
				AND tepp.resuelto = 0
				GROUP BY p.id_productos";
			$stm_name = $this->link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$link->error}" );
			if( $stm_name->num_rows <= 0 ){
				return 'message_info|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
				. '<div class="row"><div class="col-2"></div><div class="col-8">'
				. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\' );">Aceptar</button></div><br/><br/>';
			}

			$resp = "seeker|";
			while ( $row_name = $stm_name->fetch_assoc() ) {
				$resp .= "<div class=\"group_card\" onclick=\"setProductByName( {$row_name['product_id']}, 'principal' );\">";
					$resp .= "<p>{$row_name['name']}</p>";
				$resp .= "</div>";
			}
			//echo $resp;
			return $resp;
		}

		public function getOptionsByProductId( $product_id, $store_id, $is_by_name, $type ){
			//die( 'here' );
			$sql = "";
			$is_maquiled = 0;
			//verifica si es maquilado para intercambiar el id
			$sql = "SELECT
						id_producto_ordigen AS product_id
					FROM ec_productos_detalle 
					WHERE id_producto = {$product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el producto es maquilado : {$sql} {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$is_maquiled = 1;
			}
		//todos los proveedores producto
			$sql = "SELECT
						pp.id_proveedor_producto AS product_provider_id,
						pp.clave_proveedor AS provider_clue,
						pp.piezas_presentacion_cluces AS pack_pieces,
						pp.presentacion_caja AS box_pieces,
						ipp.inventario AS inventory,
						pp.codigo_barras_pieza_1 AS piece_barcode_1
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_inventario_proveedor_producto ipp
					ON ipp.id_producto = pp.id_producto 
					AND ipp.id_proveedor_producto = pp.id_proveedor_producto
					WHERE pp.id_producto = {$product_id}
					AND ipp.id_almacen IN ( SELECT id_almacen FROM ec_almacen WHERE es_almacen = 1 AND id_sucursal = {$store_id} )";
			
			$stm_name = $this->link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$sql}<br>{$this->link->error}" ); 
			$resp = "<div class=\"row\">";
			//$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-12\">";
				$resp .= "<h5>Selecciona el modelo del producto : </h5>";
				$resp .= "<table class=\"table table-bordered table-striped table_70\">";
				$resp .= "<thead>
							<tr>
								<th>Modelo</th>
								<th>Inventario</th>
								<th class=\"no_visible\">Pzs x caja</th>
								<th>Seleccionar</th>
							</tr>
						</thead><tbody id=\"model_by_name_list\" >";
				$counter = 0;
				while( $row_name = $stm_name->fetch_assoc() ){
					$resp .= "<tr>";
						$resp .= "<td id=\"p_m_1_{$counter}\" align=\"center\">{$row_name['provider_clue']}</td>";
						$resp .= "<td id=\"p_m_2_{$counter}\" align=\"center\">{$row_name['inventory']}</td>";
						$resp .= "<td id=\"p_m_3_{$counter}\" class=\"no_visible\" align=\"center\">{$row_name['box_pieces']}</td>";
						//$resp .= "<td id=\"p_m_4_{$counter}\" align=\"center\">{$row_name['pack_pieces']}</td>";
						$resp .= "<td align=\"center\"><input type=\"radio\" id=\"p_m_5_{$counter}\" 
							value=\"{$row_name['piece_barcode_1']}\"  name=\"search_by_name_selection\"></td>";
						$resp .= "<td id=\"p_m_6_{$counter}\" class=\"no_visible\">{$row_name['product_provider_id']}</td>";
					$resp .= "</tr>";
					$counter ++;
				}
				$resp .= "</tbody></table>";
				$resp .= "</div>";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\">";
				if( $ticket_id == null ){	
					$resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', null, null, null, '{$is_by_name}', '{$type}' );\">
							<i class=\"icon-ok-circle\">Continuar</i>
						</button><br><br>
						<button class=\"btn btn-danger form-control\"
							onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
							<i class=\"icon-ok-circle\">Cancelar</i>
						</button>";
				}else{
					$resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', 1, {$product_id}, {$ticket_id}, '{$is_by_name}', '{$type}' );\">
							<i class=\"icon-ok-circle\">Continuar</i>
						</button><br><br>
						<button class=\"btn btn-danger form-control\"
							onclick=\"close_emergent_2();\">
							<i class=\"icon-ok-circle\">Cancelar</i>
						</button>";
				}

				$resp .= "	</div>
					</div>|{$is_maquiled}";
			return $resp;
		}

		public function productsSeeker( $barcode ){
		//verifica si el código de barras existe
			$sql = "SELECT
						pp.id_proveedor_producto AS product_provider_id,
						pp.id_producto AS product_id,
						pp.presentacion_caja AS pieces_per_box,
						( SELECT 
							IF( pd.id_producto IS NULL, 
								0, 
								IF( pd.id_producto = p.id_productos, 
									1, 
									-1  
								) 
							) 
						  FROM ec_productos_detalle pd
						  WHERE pd.id_producto = p.id_productos
						  OR pd.id_producto_ordigen = p.id_productos
						) AS is_maquiled
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p 
					ON pp.id_producto = p.id_productos
					WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}' )
					GROUP BY pp.id_proveedor_producto";
			$stm1 = $this->link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : " . $link->error );

			if( $stm1->num_rows > 0 ){
				$scanned_data = $stm1->fetch_assoc();
				return "was_found|" . json_encode( $scanned_data );
			}else{
			//busca por nombre
				$barcode_array = explode(' ', $barcode );
				$condition = " OR (";
				foreach ($barcode_array as $key => $barcode_txt ) {
					$condition .= ( $condition == ' OR (' ? '' : ' AND' );
					$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
				}
				$condition .= " )";
				$sql = "SELECT
						pp.id_producto AS product_id,
						CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>' ) AS name
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON pp.id_producto = p.id_productos
					WHERE p.muestra_paleta = 0
					AND p.es_maquilado = 0
					AND p.habilitado = 1 
					AND ( pp.clave_proveedor LIKE '%{$barcode}%'
					{$condition} OR p.orden_lista = '{$barcode}'  ) 
					GROUP BY p.id_productos";
				$stm_name = $this->link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$link->error}" );
				if( $stm_name->num_rows <= 0 ){
					return 'message_info|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
					. '<div class="row"><div class="col-2"></div><div class="col-8">'
					. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\' );">Aceptar</button></div><br/><br/>';
				}
				$resp = "seeker|";
				while ( $row_name = $stm_name->fetch_assoc() ) {
					$resp .= "<div class=\"group_card\" onclick=\"setProductByName( {$row_name['product_id']}, 'emergent' );\">";
						$resp .= "<p>{$row_name['name']}</p>";
					$resp .= "</div>";
				}
				//echo $resp;
				return $resp;
			}
		}
//sucursal producto
		public function getStoresProducts( $product_provider_id ){
			$resp = array();
			$sql = "SELECT
						p.id_productos AS product_id,
						pp.id_proveedor_producto AS product_provider_id,
						CONCAT( p.nombre, ' MODELO : ', pp.clave_proveedor, ' ', pp.presentacion_caja, 
							' PZAS X CJ' ) AS product_name,
						sp.id_sucursal AS store_id,
						s.nombre AS store_name,
						sp.estado_suc AS store_product_status	
					FROM ec_productos p
					LEFT JOIN sys_sucursales_producto sp
					ON sp.id_producto = p.id_productos
					LEFT JOIN ec_proveedor_producto pp
					ON p.id_productos = pp.id_producto
					LEFT JOIN sys_sucursales s
					ON s.id_sucursal = sp.id_sucursal
					WHERE pp.id_proveedor_producto = {$product_provider_id}
					AND s.id_sucursal NOT IN( -1, 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar sucursales producto : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$tmp = $this->check_if_exists_new_product_row( $row['store_id'], $row['product_id'], 
					$row['product_provider_id'] );
				if( $tmp != 'no' ){
					$row['detail'] = $tmp;
				}
				$resp[] = $row;
			}
			return 'ok|' . json_encode( $resp );
		}

		public function check_if_exists_new_product_row( $store_id, $product_id, $product_provider_id ){
			$sql = "SELECT
						tepp.id_temporal_exhibicion_proveedor_producto AS product_provider_exhibition_id,
						tepp.id_temporal_exhibicion AS temporal_exhibition_id,
						tepp.piezas_muro AS wall_pieces,
						tepp.notas_muro AS wall_notes,
						tepp.piezas_colgar AS hang_pieces,
						tepp.notas_colgar AS hang_notes,
						tepp.piezas_adicional AS aditional_pieces,
						tepp.notas_adicionales AS aditional_notes
					FROM ec_temporal_exhibicion_proveedor_producto tepp
					LEFT JOIN ec_temporal_exhibicion te
					ON tepp.id_temporal_exhibicion = te.id_temporal_exhibicion
					WHERE te.es_nuevo = 1 
					AND te.id_sucursal = {$store_id}
					AND te.id_producto = {$product_id}
					AND tepp.id_proveedor_producto = {$product_provider_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar sucursales producto : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return 'no';
			}
			$row = $stm->fetch_assoc();
			return $row;
		}
//
		public function saveNewProduct( $data, $user_id ){
			//die( $data );
			$exhibitions = explode( '|~|', $data );
			$this->link->autocommit( false );
			foreach ( $exhibitions as $key => $exhibition ) {
			//die($exhibition );
				$value = explode( '|', $exhibition );
				$total = ( $value[3] ) + ( $value[5] ) + ( $value[7] );
				if( $value[9] != '' && $value[9] != null &&$value[10] != '' && $value[10] != null ){
					$sql = "UPDATE ec_temporal_exhibicion SET 
								id_producto = {$value[1]}, 
								cantidad = {$total}, 
								id_sucursal = {$value[0]}, 
								id_usuario = {$user_id}, 
								fecha_alta = NOW(), 
								es_nuevo = 1
							WHERE id_temporal_exhibicion = {$value[9]}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar cabecera de exhibicion producto nuevo : {$this->link->error}" );
				//inserta detalle
					$sql = "UPDATE ec_temporal_exhibicion_proveedor_producto SET 
								id_producto = '{$value[1]}', 
								id_proveedor_producto = '{$value[2]}', 
								cantidad = '{$total}', 
								piezas_muro = '{$value[3]}', 
								notas_muro = '{$value[4]}', 
								piezas_colgar = '{$value[5]}', 
								notas_colgar = '{$value[6]}', 
								piezas_adicional = '{$value[7]}',
								notas_adicionales = '{$value[8]}'
							WHERE id_temporal_exhibicion_proveedor_producto = '{$value[10]}'";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar detalle de exhibicion producto nuevo : {$this->link->error}" );		
				
				}else{
				//inserta cabecera
					$sql = "INSERT INTO ec_temporal_exhibicion ( id_producto, cantidad, id_sucursal, id_usuario, fecha_alta, es_nuevo ) 
					VALUES ( {$value[1]}, {$total}, {$value[0]}, {$user_id}, NOW(), 1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar cabecera de exhibicion producto nuevo : {$this->link->error}" );
				//consulta el id_insertado
					$sql = "SELECT LAST_INSERT_ID()";
					$id_stm = $this->link->query( $sql ) or die( "Error al consultar el ultimo id insertado : {$this->link->error}" );
					$last_id = $id_stm->fetch_row();
					$last_id = $last_id[0];
				//inserta detalle
					$sql = "INSERT INTO ec_temporal_exhibicion_proveedor_producto ( id_temporal_exhibicion, id_producto, 
					id_proveedor_producto, cantidad, piezas_muro, notas_muro, piezas_colgar, notas_colgar, piezas_adicional,
					notas_adicionales ) VALUES ( '{$last_id}', '{$value[1]}', '{$value[2]}', '{$total}', '{$value[3]}', '{$value[4]}', 
					'{$value[5]}', '{$value[6]}', '{$value[7]}', '{$value[8]}'  )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar detalle de exhibicion producto nuevo : {$this->link->error}" );		
				}
			}
			$this->link->autocommit( true );
			return 'ok|Registros insertados!';
		}

		public function getProductProviderNotes( $product_provider_id, $store_id ){
			$resp = "";
			$sql = "SELECT
						CONCAT( p.nombre, ' MODELO : ', pp.clave_proveedor, '<br>', pp.presentacion_caja, ' PZS X CAJA' ) AS product_description,
						tepp.cantidad AS total_quantity,
						tepp.piezas_muro AS wall_pieces,
						tepp.notas_muro AS wall_notes,
						tepp.piezas_colgar AS hang_pieces,
						tepp.notas_colgar AS hang_notes,
						tepp.piezas_adicional AS aditional_pieces,
						tepp.notas_adicionales AS aditional_notes
					FROM ec_temporal_exhibicion_proveedor_producto tepp
					LEFT JOIN ec_temporal_exhibicion te
					ON tepp.id_temporal_exhibicion = te.id_temporal_exhibicion
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = tepp.id_proveedor_producto
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					WHERE tepp.id_proveedor_producto = {$product_provider_id}
					AND te.id_sucursal = {$store_id}
					AND te.es_nuevo = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar las notas : {$this->link->error}" );
			return $stm->fetch_assoc();
		}

		public function getExhibitionProductProviderToEdit( $exhibition_id ){
			$resp = array();
			$sql = "SELECT 
					tepp.id_temporal_exhibicion_proveedor_producto AS product_provider_exhibition_id,
					tepp.id_temporal_exhibicion AS exhibition_id,
					tepp.cantidad AS quantity,
					pp.clave_proveedor AS provider_clue
			FROM ec_temporal_exhibicion_proveedor_producto tepp
			LEFT JOIN ec_proveedor_producto pp
			ON pp.id_proveedor_producto = tepp.id_proveedor_producto
			WHERE tepp.id_temporal_exhibicion = {$exhibition_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar temporal exhibicion : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return $resp;
		}

		public function updateExhibitionRows( $data ){
			$data_array = explode( '|~|', $data );
			$details_ids = '';
			$total = 0;
			$this->link->autocommit( false );
			foreach ($data_array as $key => $dat) {
				$dat = explode( '|', $dat );
		//actualiza detalle
				$sql = "UPDATE ec_temporal_exhibicion_proveedor_producto 
							SET cantidad = {$dat[1]},
							tiene_devolucion = 0
						WHERE id_temporal_exhibicion_proveedor_producto = {$dat[0]}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar temporal exhibicion Proveedor Producto : {$this->link->error}" );
				$details_ids .= ( $details_ids == '' ? '' : ',' );
				$details_ids .= $dat[0];
				$total += $dat[1];
			}
		//actualiza cabecera
			$sql = "UPDATE ec_temporal_exhibicion te
					INNER JOIN ec_temporal_exhibicion_proveedor_producto  tepp
					ON te.id_temporal_exhibicion = tepp.id_temporal_exhibicion
							SET te.cantidad = {$total},
							te.tiene_devolucion = 0
					WHERE tepp.id_temporal_exhibicion_proveedor_producto IN( {$details_ids} )";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar temporal exhibicion Producto : {$this->link->error}" );
			$this->link->autocommit( true );
			return 'ok|Registros actualizados Exitosamente!';
		}
	}
?>