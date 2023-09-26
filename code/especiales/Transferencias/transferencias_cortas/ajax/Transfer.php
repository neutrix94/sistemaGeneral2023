<?php
	if( isset( $_GET['fl_transfer'] ) || isset( $_POST['fl_transfer'] ) ){
		include( '../../../../../config.inc.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$Transfer = new Transfer( $link );
		$action = $_GET['fl_transfer'];

		switch ( $action ) {

			case 'changeComboContent' :
				echo $Transfer->getWarehouses( $_GET['type'], $_GET['store_id'] );
			break;

			case 'insertTransfer':
				if( $_GET['type_id'] != 6 ){
					echo $Transfer->insertTransfer( $_GET['origin_store'], $_GET['origin_warehouse'], 
						$_GET['destinity_store'], $_GET['destinity_warehouse'], $_GET['type_id'], $sucursal_id, 
						$user_id, $_GET['transfer_title'] );
				}else{
					echo $Transfer->inventory_dump( $_GET['origin_store'], $_GET['origin_warehouse'], 
						$_GET['destinity_store'], $_GET['destinity_warehouse'], $_GET['type_id'], $sucursal_id, 
						$user_id, $_GET['transfer_title'] );
				}
			break;

			case 'validateBarcode' :
				echo $Transfer->validateBarcode( $_GET['barcode'], $_GET['transfer_id'], $user_id, $sucursal_id, 
					( isset( $_GET['pieces_quantity'] ) ? $_GET['pieces_quantity'] : 1 ), 
					( isset( $_GET['was_find_by_name'] ) ? $_GET['was_find_by_name'] : 0 ),
					( isset( $_GET['pieces_form'] ) ? $_GET['pieces_form'] : 0 ),
					( isset($_GET['unique_code'] ) && $_GET['unique_code'] != '' ?  $_GET['unique_code'] : '' ),
					( isset($_GET['permission_box'] ) && $_GET['permission_box'] != '' ?  $_GET['permission_box'] : 0 ),
					( isset($_GET['destinity_store'] ) && $_GET['destinity_store'] != '' ?  $_GET['destinity_store'] : 0 ) );
			break;

			case 'getOptionsByProductId' :
				echo $Transfer->getOptionsByProductId( $_GET['product_id'] );
			break;

			case 'getTransferDetail' :
				echo $Transfer->getTransferDetail( $_GET['transfer_id'], $_GET['type'] );
			break;

			case 'removePiecesToDetail' :
				echo $Transfer->removePiecesToDetail( $_GET['transfer_product_id'], $_GET['quantity'] );
			break;

			case 'getPendingTransfers' :
			//die( 'here' );
				$Transfer->getPendingTransfers( $sucursal_id );
			break;

			case 'release_unique_code': 
				echo $Transfer->release_unique_code( $unique_code );
			break;

			case 'inventory_dump':
				echo $Transfer->inventory_dump( $origin_warehouse_id, $origin_store_id, $transfer_id );
			break;

			default : 
				die( "Permission denied" );
			break; 
		}
	}

	class Transfer
	{
		private $link;
		
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function inventory_dump( $origin_store, $origin_warehouse, $destinity_store, $destinity_warehouse, 
			$type_id, $sucursal, $user, $title ){
			$sql = "SELECT
						ax.product_id,
						ax.orden_lista,
						ax.clave_proveedor,
						ax.nombre,
						ax.nombre_almacen,
						ax.cantidad,
						ax.inventory,
						ax.product_provider_id
					FROM(
						SELECT 
							p.id_productos AS product_id,
							p.orden_lista,
							CONCAT( provProd.clave_proveedor, ' / ', provProd.presentacion_caja ) AS clave_proveedor,
							p.nombre,
							alm.nombre AS nombre_almacen,
							ipp.inventario AS cantidad,
							SUM( 
								IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL 
									OR mdpp.id_almacen != alm.id_almacen, 
									0, 
									( tm.afecta * mdpp.cantidad ) 
								) 
							) AS inventory,
							provProd.id_proveedor_producto AS product_provider_id
						FROM ec_productos p
						LEFT JOIN ec_inventario_proveedor_producto ipp 
						ON ipp.id_producto = p.id_productos
						LEFT JOIN ec_almacen alm 
						ON ipp.id_almacen = alm.id_almacen
						AND ipp.id_almacen = {$origin_warehouse}
						LEFT JOIN ec_proveedor_producto provProd 
						ON provProd.id_proveedor_producto = ipp.id_proveedor_producto
						LEFT JOIN sys_sucursales_producto sp 
						ON sp.id_producto = p.id_productos
						LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
						ON mdpp.id_proveedor_producto = provProd.id_proveedor_producto
						AND mdpp.id_sucursal = '{$origin_store}'
						AND mdpp.id_almacen = '{$origin_warehouse}'
						LEFT JOIN ec_tipos_movimiento tm
						ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
						WHERE sp.id_sucursal = '{$origin_store}'
						AND sp.estado_suc = 1
						AND ipp.id_sucursal = '{$origin_store}'
						AND ipp.id_almacen = '{$origin_warehouse}'
						GROUP BY provProd.id_proveedor_producto
						ORDER BY p.orden_lista ASC
					)ax
					WHERE ax.inventory != 0
					GROUP BY  ax.product_provider_id
					ORDER BY ax.orden_lista ASC";
	//die ( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los detalles de la transferencia para vaciar : {$this->link->error()}" );
			$this->link->autocommit( false );
			$insert_transfer = explode( '|', $this->insertTransfer( $origin_store, $origin_warehouse, $destinity_store, $destinity_warehouse, 
			$type_id, $sucursal, $user, $title ) );
			if( $insert_transfer[0] != 'ok' ){
				die( "Error al insertar cabecera de la transferencia: {$insert_transfer[0]}" );
			}
			$transfer_id = $insert_transfer[1];

			while( $data = $stm->fetch_assoc() ){
				$pieces_quantity = $data['inventory'];
				$sql = "INSERT INTO ec_transferencia_productos SET 
							id_transferencia = '{$transfer_id}', 
							id_producto_or = {$data['product_id']}, 
							id_presentacion = -1, 
							cantidad_presentacion = {$pieces_quantity},
							cantidad = {$pieces_quantity}, 
							id_producto_de = {$data['product_id']}, 
							referencia_resolucion = {$pieces_quantity},  
							cantidad_cajas = 0, 
							cantidad_paquetes = 0, 
							cantidad_piezas = {$pieces_quantity}, 
							id_proveedor_producto = {$data['product_provider_id']},
							cantidad_piezas_surtidas = {$pieces_quantity},
							total_piezas_surtimiento = {$pieces_quantity},
							cantidad_piezas_validacion = {$pieces_quantity},
							total_piezas_validacion = {$pieces_quantity}";/*", 
							cantidad_piezas_recibidas = {$pieces_quantity},
							total_piezas_recibidas = {$pieces_quantity}";*/
				//}
				$this->link->query( $sql ) or die( "Error al insertar el detalle de la transferencia : {$this->link->error}" );
			}
			$this->link->autocommit( true );
			return "ok|{$transfer_id}";			
		}

		public function getTransferDetail( $transfer_id ){
			$resp = "";
			$sql = "SELECT 
						tp.id_transferencia_producto AS transfer_product_id,
						p.orden_lista AS list_order,
						pp.clave_proveedor AS provider_clue,
						p.nombre AS product_name,
						tp.cantidad AS quantity,
						( SELECT IF( id_producto IS NULL, 0, 1 )
							FROM ec_productos_detalle 
							WHERE id_producto = p.id_productos
							OR id_producto_ordigen = p.id_productos
						) AS is_maquiled,
						p.id_productos AS product_id
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tp.id_proveedor_producto 
					WHERE tp.id_transferencia = {$transfer_id}
					GROUP BY tp.id_proveedor_producto
					ORDER BY p.orden_lista ASC";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de la transferencia : {$this->link->error}" );
			$counter = 0;
			while( $row = $stm->fetch_assoc() ){
				if( $row['is_maquiled'] == '' || $row['is_maquiled'] == null || $row['is_maquiled'] == 'null'  ){
					$row['is_maquiled'] = 0;
				}
				//$row['quantity'] = str_replace( '.0000', '', $row['quantity'] );
			//	$row['quantity'] = round($row['quantity'], 4);

				$resp .= "<tr>
							<td id=\"transfer_1_{$counter}\" class=\"no_visible\">{$row['transfer_product_id']}</td>
							<td id=\"transfer_2_{$counter}\" class=\"text-center\">{$row['list_order']}</td>
							<td id=\"transfer_4_{$counter}\" class=\"text-center\">{$row['provider_clue']}</td>
							<td id=\"transfer_5_{$counter}\" class=\"text-start\">{$row['product_name']}</td>
							<td id=\"transfer_3_{$counter}\" class=\"text-center\">{$row['quantity']}</td>
							<td class=\"text-center\">
								<button   
									type=\"button\"
									class=\"btn\"
									onclick=\"delete_scanns( {$counter}, {$row['transfer_product_id']}, {$row['is_maquiled']}, null, {$row['product_id']} );\"
								>
									<i class=\"icon-cancel-circled\" style=\"color : red;\"></i>
								</button>
							</td>
						<tr>";
				$counter ++;
			}
			$sql = "SELECT 
						id_transferencia AS transfer_id,
						id_sucursal_origen AS origin_store,
						id_sucursal_destino AS destinity_store,
						id_almacen_origen AS origin_warehouse,
						id_almacen_destino AS destinity_warehouse,
						id_tipo AS transfer_type_id,
						titulo_transferencia AS transfer_title,
						id_estado AS transfer_status
					FROM ec_transferencias
					WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar cabecera de transferencia : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return "{$resp}|{$row['origin_store']}|{$row['origin_warehouse']}|{$row['destinity_store']}|
			{$row['destinity_warehouse']}|{$row['transfer_type_id']}|{$row['transfer_id']}|{$row['transfer_title']}|{$row['transfer_status']}";
		}

		public function validateIsBoxSeal( $barcode, $unique_code ){
			$sql = "SELECT 
					id_codigo_validacion
				FROM ec_codigos_validacion_cajas
				WHERE codigo_barras = '{$barcode}'";
			
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar si es código de validación de caja : {$this->link->error}" );
			if( $stm->num_rows == 1 ){
				$resp = 'is_box_code|';
				$resp .= '<div>';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-2"></div>';
						$resp .= '<div class="col-8">';
							$resp .= '<label for="tmp_sell_barcode">El código de barras del sello es válido, para continuar escaneé el código de barras de la caja : </label>';
							$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control" onkeyup="validateBarcode( this, event, null, null, 1 );"><br>';
							$resp .= '<button type="button" class="btn btn-success form-control"';
							$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, null, 1 );">';
								$resp .= '<i class="icon-ok-circle">Aceptar</i>';
							$resp .= '</button><br><br>';
							$resp .= '<button type="button" class="btn btn-danger form-control"';
							$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
								$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
							$resp .= '</button>';
						$resp .= '</div>';
					$resp .= '</div>';
				$resp .= '</div>';
				return $resp;
			}
			return 'ok';
		}

		public function validateUniqueCode( $unique_code, $transfer_id ){
			$sql = "SELECT
						id_transferencia_codigo,
						id_transferencia AS transfer_id
					FROM ec_transferencia_codigos_unicos 
					WHERE codigo_unico = '{$unique_code}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el codigo unico ya esta registrado : {$this->link->error}" );
			
			

			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				if( $transfer_id == $row['transfer_id'] ){
					return "message_error|<div class=\"row\">
							<div class=\"col-2\"></div>
							<div class=\"col-8 text-center\">
								<h5>Este código único ({$unique_code}) ya fue utitlizado para esta Transferencia y no es posible volver a registrar estas piezas</h5>
								<button
									class=\"btn btn-danger\"
									onclick=\"close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\"
								>
									<i class=\"icon-cancel-circled\">Aceptar</i>
								</button>
							</div>
						</div>";
				}
				return "message_error|<div class=\"row\">
							<div class=\"col-2\"></div>
							<div class=\"col-8 text-center\">
								<h5>Este código único ({$unique_code}) ya fue utitlizado anteriormente</h5>
								<p>Si deseas liberar el codigo para usarlo en esta transferencia; escribe la palaba \"LIBERAR\"
								 para continuar : 
								</p>
								<input type=\"text\" class=\"form-control\" id=\"tmp_validation_word\">
								<br>
								<button
									class=\"btn btn-success\"
									onclick=\"release_unique_code( '{$unique_code}' );\"
								>
									<i class=\"icon-cancel-circled\">Aceptar y liberar</i>
								</button>
								<button
									class=\"btn btn-danger\"
									onclick=\"close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\"
								>
									<i class=\"icon-cancel-circled\">Cancelar</i>
								</button>
							</div>
						</div>";
			}
			return 'ok';
		}

		public function validateBarcode( $barcode, $transfer_id, $user, $sucursal, $pieces_quantity = 1, 
				$was_find_by_name = 0, $pieces_form, $unique_code = '', $permission_box  = 0, $destinity_store ){
			$unique_code_validation;
			if( $unique_code != '' && $unique_code != 0 ){
				$unique_code_validation = $this->validateUniqueCode( $unique_code, $transfer_id );
				if( $unique_code_validation != 'ok' ){
					return $unique_code_validation;
				}
			}
	/*inserta el registro de escaneo temporal
			$sql = "INSERT INTO validation_scan_tmp SET 
						id_scann_tmp = NULL,
						id_usuario = {$user},
						codigo_barras = '{$barcode}',
						codigo_unico = '{$unique_code}',
						bloque_recepcion = {$reception_block_id},
						fecha_alta = NOW()";
			$stm_tmp = $this->link->query( $sql ) or die( "Error al insertar el registro temporal : {$link->error}" );*/
			$val_1 = $this->validateIsBoxSeal( $barcode, $unique_code );
			if( $val_1 != 'ok' ){
				return $val_1;
			}
		//verifica si el código de barras existe
			$sql = "SELECT
						pp.id_proveedor_producto AS product_provider_id,
						pp.id_producto AS product_id,
						IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
							OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 
						) AS piece,
						IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 
							OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
							1, 0 
						) AS pack,
						pp.piezas_presentacion_cluces AS pieces_per_pack,
						IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
						1, 0 ) AS 'box',
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
					ON p.id_productos = pp.id_producto
/*Oscar 2023/09/25 TRANSFERENCIAS RAPIDAS, QUE SOLO BUSQUE COINCIDENCIAS EN LOS PRODUCTOS HABILITADOS POR SUCURSAL*/
					LEFT JOIN sys_sucursales_producto sp
					ON sp.id_producto = pp.id_producto
					WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
					AND pp.id_proveedor_producto IS NOT NULL
					AND p.es_maquilado = 0
					AND sp.id_sucursal = {$destinity_store}
					AND sp.estado_suc = 1";
/*Fin de cambio Oscar 2023/09/25*/
	//die( 'errro| ' . $sql );
			$stm1 = $this->link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : 
				{$sql} {$this->link->error}" );
			$row = $stm1->fetch_assoc();
			if( $stm1->num_rows <= 0 ){
				return $this->seekByName( $barcode, $destinity_store );/*Oscar 2023/09/25 TRANSFERENCIAS RAPIDAS, QUE SOLO BUSQUE COINCIDENCIAS EN LOS PRODUCTOS HABILITADOS POR SUCURSAL*/
			}

			if( $pieces_form == 1 && $row['pack'] != 1 && $row['box'] != 1) {
				if( $row['is_maquiled'] == 1 || $row['is_maquiled'] == -1 ){
					include( '../../../plugins/maquile.php' );
					$Maquile = new maquile( $this->link );
					$function_js = "setPiecesQuantity( '{$barcode}', 1 );";
					return "pieces_form|" . $Maquile->make_form( $row['product_id'], 0, $function_js );					
				}
				if( $unique_code != '' ){
					$barcode = $unique_code;
				}

				$resp = "pieces_form|<div class=\"row\">
						<div><h5>Ingresa el número de Piezas : </h5></div>
					<div class=\"col-2\"></div>
						<div class=\"col-8\">
							<input 
								type=\"number\" 
								class=\"form-control\" 
								id=\"pieces_quantity_emergent\">
							<button 
								type=\"button\" class=\"btn btn-success form-control\"
								onclick=\"setPiecesQuantity( '{$barcode}' );\"
							>
								Aceptar
							</button>
							<button class=\"btn btn-danger form-control\" 
							onclick=\"close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\">
								<i class=\"icon-ok-circle\">Cancelar</i>
							</button>
						</div>
					</div>
				</div>";
				return $resp;
			}

			if( ( $row['box'] == 1 && $row['pieces_per_box'] > 1  ) ){ 
				$pieces_quantity = $row['pieces_per_box'];
				if( $permission_box == 0 ){
					$resp = 'scan_seil_barcode|<div class="row">';
					$resp .= '<div class="col-2"></div>';
					$resp .= '<div class="col-8"><h5>Para escanear la caja primero escanea el sello de caja, si este esta roto escanea los paquetes </h5>';
						$resp .= '<button type="button" class="btn btn-success form-control"';
						$resp .= ' onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\' );">';
							$resp .= 'Aceptar';
						$resp .= '</button>';
					$resp .= '</div>';
					$resp .= '</div>';
					return $resp;
				}
			}
			if( $row['pack'] == 1  ){
				$pieces_quantity = $row['pieces_per_pack'];
			}
		//validacion Osscar 2023 para no dejar pasar códigos estandar si es paquete o caja
			if( ( $unique_code == null || $unique_code == '' ) && ( $row['pack'] == 1 || $row['box'] == 1 ) ){
				return "message_error|
					<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10 text-center\">
							<h5>El código de barras que se escaneo es de caja o paquete y no cuenta con un 
							código único, envié una fotografía o captura de pantalla al encargado de sistemas :</h5>
							<p>Código escaneado : <b style=\"color : red;\">{$barcode}</b></p>
							<br>
							<!--p>Lleva este producto con el encargado y pidele que ingrese su 
							contraseña para continuar!</p-->
							<div class=\"row\">
								<div class=\"col-2 text-center\"></div>
								<div class=\"col-8 text-center\">
									<!-- input type=\"password\" class=\"form-control\" id=\"manager_password\" --><br>

									<button
										type=\"button\"
										class=\"btn btn-success form-control\"
										onclick=\"close_emergent();\"
									>
										<i class=\"icon-ok-circle\">Aceptar</i>
									</button>
								</div>
							</div> 
						</div>
					</div>";
			}

		/*Deshabilitado por oscar 2022

			if( ( $row['box'] == 1 && $row['pieces_per_box'] > 1  ) || $row['pack'] == 1 ){ 
				$resp = "message_error|
						<div class=\"text-center\">
							<h5>Esta transferencia solo permite escanear por pieza, Verifica y vuelve a intentar</h5>
							<button
								class=\"btn btn-success\"
								onclick=\"close_emergent();\"
							>
								<i>Aceptar</i>
							</button>
						</div>";
				return $resp;
			}//$pieces_quantity*/
			return $this->inserTransferProduct( $row, $barcode, $transfer_id, $user, $pieces_quantity, 
				$was_find_by_name, $sucursal, $unique_code );
		}

		public function inserTransferProduct( $data, $barcode, $transfer_id, $user, $pieces_quantity, 
				$was_find_by_name, $sucursal, $unique_code, $permission_box = 0 ){
		//consulta el tipo de transferencia 
			$sql = "SELECT id_tipo AS type FROM ec_transferencias WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el tipo de transferencia : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$transfer_type = $row['type'];
			$transfer_product_id = null;
		//inserta el detalle de transferencia
			$sql = "SELECT 
						id_transferencia_producto AS transfer_product_id
					FROM ec_transferencia_productos
					WHERE id_transferencia = {$transfer_id}
					AND id_proveedor_producto = {$data['product_provider_id']}";
			$stm = $this->link->query( $sql )or die( "error|Error al consultar si el proveedor producto existe en la transerencia : {$sql} {$this->link->error}" );
			//echo '|kjdfn';
			//var_dump( $data );
			$sql = "";
			if( $stm->num_rows <= 0 ){
				$sql = "INSERT INTO ec_transferencia_productos SET 
							id_transferencia = '{$transfer_id}', 
							id_producto_or = {$data['product_id']}, 
							id_presentacion = -1, 
							cantidad_presentacion = {$pieces_quantity},
							cantidad = {$pieces_quantity}, 
							id_producto_de = {$data['product_id']}, 
							referencia_resolucion = {$pieces_quantity},  
							cantidad_cajas = 0, 
							cantidad_paquetes = 0, 
							cantidad_piezas = {$pieces_quantity}, 
							id_proveedor_producto = {$data['product_provider_id']},
							cantidad_piezas_surtidas = {$pieces_quantity},
							total_piezas_surtimiento = {$pieces_quantity},
							cantidad_piezas_validacion = {$pieces_quantity},
							total_piezas_validacion = {$pieces_quantity}";
				if( $transfer_type == 10 ){
					$sql .= ", cantidad_piezas_recibidas = {$pieces_quantity},
							total_piezas_recibidas = {$pieces_quantity}";
				}
		//die( $sql );
				$stm_ins = $this->link->query( $sql ) or die( "error|Error al insertar el detalle de transferencia : {$sql} {$this->link->error}" );
				$transfer_product_id = $this->link->insert_id;
			}else{
				$row = $stm->fetch_assoc();
				$transfer_product_id = $row['transfer_product_id'];
				$sql = "UPDATE ec_transferencia_productos SET  
							id_producto_or = {$data['product_id']}, 
							cantidad_presentacion = ( cantidad_presentacion + {$pieces_quantity} ),
							cantidad = ( cantidad + {$pieces_quantity} ), 
							referencia_resolucion = ( referencia_resolucion + {$pieces_quantity} ),
							cantidad_piezas = ( cantidad_piezas + {$pieces_quantity} ), 
							cantidad_piezas_surtidas = ( cantidad_piezas_surtidas + {$pieces_quantity} ),
							total_piezas_surtimiento = ( total_piezas_surtimiento + {$pieces_quantity} ),
							cantidad_piezas_validacion = ( cantidad_piezas_validacion + {$pieces_quantity} ),
							total_piezas_validacion = ( total_piezas_validacion + {$pieces_quantity} )";
				if( $transfer_type == 10 ){
					$sql .= ", cantidad_piezas_recibidas = ( cantidad_piezas_recibidas + {$pieces_quantity} ),
							total_piezas_recibidas = ( total_piezas_recibidas + {$pieces_quantity} )";
				}
				$sql .= " WHERE id_transferencia_producto = {$transfer_product_id}";

				$stm_upd = $this->link->query( $sql ) or die( "error|Error al actualizar el detalle de transferencia : {$this->link->error}" );
			}
			if( $unique_code != '' && $unique_code != null && $unique_code != 'null' ){
			//elimina el codigo unico anterior
				$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE codigo_unico = '{$unique_code}'";
				$stm->ins = $this->link->query( $sql ) or die( "Error al ELIMINAR el codigo unico : {$this->link->error}" );


				$sql = "INSERT INTO ec_transferencia_codigos_unicos ( /*1*/id_transferencia_codigo, 
					/*2*/id_bloque_transferencia_validacion, /*3*/id_bloque_transferencia_recepcion, 
					/*4*/id_usuario_validacion, /*5*/id_usuario_recepcion, /*6*/id_status_transferencia_codigo, 
					/*7*/nombre_status, /*8*/fecha_alta, /*9*/codigo_unico, /*10*/piezas_contenidas, 
					/*11*/id_transferencia_validacion, /*12*/id_transferencia )
					SELECT 
						/*1*/NULL, 
						/*2*/NULL,
						/*3*/NULL,
						/*4*/{$user}, 
						/*5*/NULL, 
						/*6*/1, 
						/*7*/(SELECT nombre_status FROM ec_status_transferencias_codigos_unicos WHERE id_status_transferencia_codigo = 1), 
						/*8*/NOW(),
						/*9*/'{$unique_code}',
						/*10*/{$pieces_quantity},
						/*11*/NULL,
						/*12*/{$transfer_id}";
				$stm->ins = $this->link->query( $sql ) or die( "Error al insertar el codigo unico : {$this->link->error}" );
/*Implementacion Oscar 2023 para insertar el escaneo de validacion del usuario*/
				$sql = "INSERT INTO ec_transferencias_validacion_usuarios ( id_transferencia_validacion, id_transferencia_producto, 
					id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_validadas, cantidad_paquetes_validados, cantidad_piezas_validadas,
					fecha_validacion, id_status, validado_por_nombre, codigo_barras, codigo_unico )
				VALUES( NULL, {$transfer_product_id}, {$user}, {$data['product_id']}, {$data['product_provider_id']}, 0, 0, {$pieces_quantity}, 
					NOW(), 1, '0', '{$barcode}', '{$unique_code}' )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el escaneo de validación : {$this->link->error}" );
/*Fin de cambio Oscar 2023*/
			}
//echo $sql;
			return "ok|Producto registrado exitosamente";
		}


		public function getTransferTypes(){
		/*implementacion Oscar 2023 para mostrar/ocultar transferencia para vaciar almacen */
			$sql = "SELECT habilitar_transferencia_vaciar_almacen AS allow_transfers FROM sys_configuracion_sistema WHERE id_configuracion_sistema = 1";
			$stm = $this->link->query( $sql ) or die( "Errror al consultar si se permiten transferencias  para  vaciar alamcen : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$allow_transfers = $row['allow_transfers'];
			$union = "";
			if( $allow_transfers == 1 ){
				$union = "UNION
					(SELECT 
						id_tipo_transferencia AS transfer_type_id,
						nombre AS type
					FROM ec_tipos_transferencias
					WHERE id_tipo_transferencia IN( 6 ))";
			}
			$resp = "<select id=\"transfer_type\" class=\"combo\" onchange=\"setTransferType()\">";
			$resp .= "<option value=\"\">-- Selecionar -- </option>";
			$sql = "(SELECT 
						id_tipo_transferencia AS transfer_type_id,
						nombre AS type
					FROM ec_tipos_transferencias
					WHERE id_tipo_transferencia IN( 10, 11 ))
					{$union}";
		/*fin de cambio Oscar 2023*/
			$stm = $this->link->query( $sql ) or die( "Error al consultar tipos de transferencias : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<option value=\"{$row['transfer_type_id']}\">{$row['type']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}
		public function getStores( $type, $sucursal_id = null ){
			$resp = "<select id=\"transfer_store_{$type}\" class=\"combo\" onchange=\"change_warehouses_by_store( '{$type}' );\">";
			$resp .= "<option value=\"\">-- Selecionar -- </option>";
			$sql = "SELECT 
						id_sucursal AS store_id,
						nombre AS store_name
					FROM sys_sucursales
					WHERE id_sucursal > 0";
			$sql .= ( $sucursal_id != null ? " AND id_sucursal = {$sucursal_id}" : "" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar tipos de transferencias : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<option value=\"{$row['store_id']}\">{$row['store_name']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function getWarehouses( $type, $store_id = null, $warehouse_id = null ){
			$resp = "<select id=\"transfer_warehouse_{$type}\" class=\"combo\">";
			$resp .= "<option value=\"\">-- Selecionar -- </option>";
			$sql = "SELECT 
						id_almacen AS warehouse_id,
						nombre AS warehouse_name
					FROM ec_almacen
					WHERE id_almacen > 0";
			$sql .= ( $store_id != null ? " AND id_sucursal = {$store_id}" : "" );
			$sql .= ( $warehouse_id != null ? " AND id_almacen = {$warehouse_id}" : "" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar tipos de transferencias : {$this->link->error}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<option value=\"{$row['warehouse_id']}\">{$row['warehouse_name']}</option>";
			}
			$resp .= "</select>";
			return $resp;
		}

		public function insertTransfer( $origin_store, $origin_warehouse, $destinity_store, $destinity_warehouse, 
			$type_id, $sucursal, $user, $title ){
			$sql="INSERT INTO ec_transferencias SET 
					id_usuario = {$user},
					folio = '',
					fecha = NOW(),
					hora = NOW(),
					id_sucursal_origen = {$origin_store},
					id_sucursal_destino = {$destinity_store},
					observaciones = '',
					id_razon_social_venta = -1,
					id_razon_social_compra = 1,
					facturable = 0,
					porc_ganancia = 0,
					id_almacen_origen = {$origin_warehouse},
					id_almacen_destino = {$destinity_warehouse},
					id_tipo = {$type_id},
					id_estado = 1,
					id_sucursal = {$sucursal}, 
					titulo_transferencia = '{$title}'";
			$stm = $this->link->query( $sql ) or die( "Errror al insertar la cabecera de transferencia : {$this->link->error}" );
			$transfer_id = $this->link->insert_id;
		//arma el folio
			$sql_folio = "SELECT 
							CONCAT(s1.prefijo, s2.prefijo, ' ', t.id_transferencia ) AS folio
						FROM ec_transferencias t
						LEFT JOIN sys_sucursales s1
						ON s1.id_sucursal = t.id_sucursal_origen
						LEFT JOIN sys_sucursales s2
						ON s2.id_sucursal = t.id_sucursal_destino
						WHERE t.id_transferencia = '{$transfer_id}'";
			$stm_folio = $this->link->query( $sql_folio ) or die( "Error al consultar el folio : {$this->link->error}" );
			$row_folio = $stm_folio->fetch_assoc();
			$folio =  $row_folio['folio'];
			$sql="UPDATE ec_transferencias SET folio='{$folio}' WHERE id_transferencia = {$transfer_id}";
			$stm = $this->link->query( $sql ) or die( "Error al acrtualizar folio de la cabecera de transferencia : {$this->link->error}" );
			return "ok|{$transfer_id}";
		}
		
		public function seekByName( $barcode, $destinity_store ){
			//die('|here');
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
				FROM ec_productos p
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_producto = p.id_productos
/*Oscar 2023/09/25 TRANSFERENCIAS RAPIDAS, QUE SOLO BUSQUE COINCIDENCIAS EN LOS PRODUCTOS HABILITADOS POR SUCURSAL*/
				LEFT JOIN sys_sucursales_producto sp
				ON sp.id_producto = pp.id_producto
				WHERE ( pp.clave_proveedor LIKE '%{$barcode}%'
				{$condition} OR p.orden_lista = '{$barcode}' ) 
				AND pp.id_proveedor_producto IS NOT NULL
				AND p.id_productos > 0
				AND p.es_maquilado = 0
				AND sp.id_sucursal = {$destinity_store}
				AND sp.estado_suc = 1
				GROUP BY p.id_productos";
/*Fin de cambio Oscar 2023/09/25*/
			$stm_name = $this->link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$rhis->link->error}" );
			if( $stm_name->num_rows <= 0 ){
				return 'message_error|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
				. '<div class="row"><div class="col-2"></div><div class="col-8">'
				. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\' );lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">Aceptar</button></div><br/><br/>';
			}

			$resp = "seeker|";
			while ( $row_name = $stm_name->fetch_assoc() ) {
				$resp .= "<div class=\"group_card\" onclick=\"setProductByName( {$row_name['product_id']} );\">";
					$resp .= "<p>{$row_name['name']}</p>";
				$resp .= "</div>";
			}
			//echo $resp;
			return $resp;
		} 

		function getOptionsByProductId( $product_id ){
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
					AND ipp.id_almacen = 1";
			$stm_name = $this->link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$link->error}" ); 
			$resp = "<div class=\"row\">";
				//$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-12\">";
					$resp .= "<h5>Seleccione el modelo del producto : </h5>";
					$resp .= "<table class=\"table table-bordered table-striped table_70\">";
					$resp .= "<thead>
								<tr>
									<th>Clave Prov</th>
									<th>Inventario</th>
									<th>Pzs x caja</th>
									<th>Pzs x paquete</th>
									<th>Seleccionar</th>
								</tr>
							</thead><tbody id=\"model_by_name_list\" >";
					$counter = 0;
					while( $row_name = $stm_name->fetch_assoc() ){
						$resp .= "<tr>";
							$resp .= "<td id=\"p_m_1_{$counter}\" align=\"center\">{$row_name['provider_clue']}</td>";
							$resp .= "<td id=\"p_m_2_{$counter}\" align=\"center\">{$row_name['inventory']}</td>";
							$resp .= "<td id=\"p_m_3_{$counter}\" align=\"center\">{$row_name['box_pieces']}</td>";
							$resp .= "<td id=\"p_m_4_{$counter}\" align=\"center\">{$row_name['pack_pieces']}</td>";
							$resp .= "<td align=\"center\"><input type=\"radio\" id=\"p_m_5_{$counter}\" 
								value=\"{$row_name['piece_barcode_1']}\"  name=\"search_by_name_selection\"></td>";
						$resp .= "</tr>";
						$counter ++;
					}
					$resp .= "</tbody></table>";
				$resp .= "</div>";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\">
							<button class=\"btn btn-success form-control\" onclick=\"setProductModel();\">
								<i class=\"icon-ok-circle\">Continuar</i>
							</button><br><br>
							<button class=\"btn btn-danger form-control\"
								onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
								<i class=\"icon-ok-circle\">Cancelar</i>
							</button>
						</div>";
			$resp .= "</div>";
			return $resp;
		}

		public function removePiecesToDetail( $transfer_product_id, $quantity ){
			$sql = "SELECT t.id_tipo AS type 
					FROM ec_transferencia_productos tp 
					LEFT JOIN ec_transferencias t
					ON tp.id_transferencia = t.id_transferencia
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el tipo de transferencia : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$transfer_type = $row['type'];

			$sql = "UPDATE ec_transferencia_productos SET  
						cantidad_presentacion = ( cantidad_presentacion - {$quantity} ),
						cantidad = ( cantidad - {$quantity} ), 
						referencia_resolucion = ( referencia_resolucion - {$quantity} ),
						cantidad_piezas = ( cantidad_piezas - {$quantity} ), 
						cantidad_piezas_surtidas = ( cantidad_piezas_surtidas - {$quantity} ),
						total_piezas_surtimiento = ( total_piezas_surtimiento - {$quantity} ),
						cantidad_piezas_validacion = ( cantidad_piezas_validacion - {$quantity} ),
						total_piezas_validacion = ( total_piezas_validacion - {$quantity} )";
			
			if( $transfer_type == 10 ){
				$sql .= ", cantidad_piezas_recibidas = ( cantidad_piezas_recibidas - {$quantity} ),
						total_piezas_recibidas = ( total_piezas_recibidas - {$quantity} )";
			}

			$sql .= " WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm_upd = $this->link->query( $sql ) or die( "error|Error al restar piezas el detalle de transferencia : {$sql} {$this->link->error}" );
			$sql = "SELECT cantidad AS quantity FROM ec_transferencia_productos WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm_sel = $this->link->query( $sql ) or die( "error|Error al reconsultar el detalle de transferencia : {$this->link->error}" );
			$row = $stm_sel->fetch_assoc();
			if( $row['quantity'] <= 0 ){
				$sql = "DELETE FROM ec_transferencia_productos WHERE id_transferencia_producto = {$transfer_product_id}";
				$stm_del = $this->link->query( $sql ) or die( "error|Error al eliminar el detalle de transferencia : {$this->link->error}" );			
			}
			return 'ok';
		}

		public function getPendingTransfers( $store_id ){
		//armamos la consulta
			$sql="SELECT 
					t.id_transferencia,
					t.titulo_transferencia,
					CONCAT( alm1.nombre, '<br>a<br>', alm2.nombre )
					FROM ec_transferencias t
					LEFT JOIN ec_almacen alm1 on t.id_almacen_origen=alm1.id_almacen
					LEFT JOIN ec_almacen alm2 on t.id_almacen_destino=alm2.id_almacen
					LEFT JOIN ec_estatus_transferencia est ON t.id_estado=est.id_estatus
				WHERE t.id_estado<=4
				AND t.id_tipo IN( 10, 11 )
				AND IF(t.es_resolucion=0,(t.id_sucursal_origen = {$store_id} 
					OR t.id_sucursal_destino = {$store_id}),
					t.id_sucursal_destino = {$store_id} )";
			$stm = $this->link->query( $sql )or die("Error al consultar Transferencias Pendientes : {$sql} {$this->link->error}" );
		//regresamos respuesta si no hay transferencias pendientes
			if( $stm->num_rows < 1 ){
				die('ok|ok');
			}
		//creamos botón para cerrar emergente
		//	$res='<button type="button" class="bot_crra" style="position:absolute;top:11%;right:18.5%;" onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">X</button>';
		//creamos tabla de referencia de transferencias
			$res.='<p align="center" style="font-size:30px;color:red;"><b>Las siguientes Transferencias están Pendientes:</b></p>';
			$res.='<div style="height:300px;overflow:auto;background:white;width:90%; left : 10%; text-align: center;"><table class="table-striped" width="100%">';
				$res.='<tr>';
					$res.='<th align="center">FOLIO</th>';
					$res.='<th align="center">TÍTULO</th>';
					//$res.='<th align="center">Origen</th>';
					$res.='<th align="center">DE - A</th>';
				//	$res.='<th align="center">Estatus</th>';
				$res.='<tr/>';
				while($r = $stm->fetch_row() ){
					$res.='<tr class="tr_1" onclick="location.href=\'index.php?pk=' . $r[0] . '\';close_emergent();">';
						$res.='<td>'.$r[0].'</td>';
						$res.='<td>'.$r[1].'</td>';
						$res.='<td class="text-center">'.$r[2].'</td>';
					$res.='<tr>';
				}

			$res.='</table></div>';
		//generamos botón para continuar
			$res.='<br>
				<p class="text-center">
					<button 
						type="button" 
						class="btn btn-success" 
						onclick="close_emergent();">Continuar de todas formas</button>
				</p>';	
		//	$res.='<button type="button" class="bt_continua" onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">Cancelar</button><br><br>';
		//generamos el estilo
			$res.='<style>';
				$res.='th{padding:10px;background:red;color:white;}';//estilo del encabezado
				$res.='.fila{padding:6px;background:red;color:white;}';//estilo de las filas
				$res.='.fila:hover{padding:10px;background:rgba(0,0,225,.8);color:whie;}';//hover de las filas
				$res.='.bot_crra{padding:15px;border-radius:6px;background:red;color:white;position:absolute;top:20px;right:5%;}';//estilo de boton cerrar
				$res.='.bt_continua{padding:10px;border-radius:8px;}';//botón para continuar
				$res.='.tr_1{height:30px;}';
				$res.='.tr_1:hover{background:rgba(0,225,0,.6);}';
			$res.='</style>';
			echo 'ok|'.$res;
		}

		public function release_unique_code( $unique_code ){
			$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE codigo_unico = '{$unique_code}'";
			$stm = $this->link->query( $sql ) or die( "Error al liberar codigo unico : {$this->link->error}" );
		//obj = null, e, is_by_name = 0, barcode = null, pieces = null, permission_box = null
			return "<div class=\"row\">
						<h5>El codigo único fue liberado exitosamente.</h5>
						<button
							class=\"btn btn-danger\"
							onclick=\"validateBarcode( null, 'enter', 0, '{$unique_code}', null  );close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\"
						>
							<i class=\"icon-cancel-circled\">Aceptar</i>
						</button>
					</div>";
		}

	}
?>