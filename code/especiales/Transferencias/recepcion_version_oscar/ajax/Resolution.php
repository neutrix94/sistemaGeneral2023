<?php
	if( isset( $_POST['fl_r'] ) || isset( $_GET['fl_r'] ) ){
//echo '1_1';
		$flag = ( isset( $_GET['fl_r'] ) ? $_GET['fl_r'] : $_POST['fl_r'] );
		include( '../../../../../config.inc.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$Resolution = new Resolution( $link, $user_id, $sucursal_id );
		switch ( $flag ) {
			case 'saveResolutionMissingRow':
//echo '1_1';
//echo '1_2';
				echo $Resolution->saveResolutionMissingRow( $_GET['product_id'], $_GET['product_provider_id'], 
					$_GET['quantity'], $_GET['type'], $_GET['transfers'], $_GET['reception_block_id'], $user_id,
					$_GET['pieces_faltant'], $_GET['user_count'], $_GET['difference'] );	
			break;

			case 'saveResolutionDoesntCorrespondRow';
				echo $Resolution->saveResolutionDoesntCorrespondRow( $_GET['quantity'], $_GET['type'], $_GET['block_resolution_id'] );

			break;

			case 'saveResolutionExcedentRow' :
				echo $Resolution->saveResolutionExcedentRow( $_GET['block_resolution_id'], $_GET['product_id'], 
					$_GET['product_provider_id'], $_GET['quantity'], $_GET['type'], 
					$_GET['transfers'], $_GET['reception_block_id'], $_GET['user_count'] );
			break;
			
			default:
				//die( "Permission Denied on {$flag} : " );
			break;
		}
	}
	class Resolution
	{
		private $link;
		private $user_id;
		private $sucursal_id;
		private $sucursal_warehouse;
		function __construct( $connection, $user_id, $sucursal_id ){	
			$this->link = $connection;	
			$this->user_id = $user_id;	
			$this->sucursal_id = $sucursal_id;
			$sql = "SELECT 
						id_almacen AS warehouse_id
					FROM ec_almacen 
					WHERE es_almacen = 1 
					AND id_sucursal = {$this->sucursal_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el almacén principal de la sucursal : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$this->sucursal_warehouse = $row['warehouse_id'];
		}

		public function saveResolutionExcedentRow( $block_resolution_id, $product_id, $product_provider_id,
					$quantity, $type, $transfers, $reception_block_id, $user_count ){
			/*die( 'here' . "$block_resolution_id, $product_id, $product_provider_id,
					$quantity, $type, $transfers, $reception_block_id,
					$user_count, $pieces_faltant, $difference" );*/
		//actualiza el detalle de resolución por excedente
			$sql = "UPDATE ec_bloques_transferencias_resolucion
						SET 
							piezas_se_quedan = IF({$type} = 1 , {$quantity}, 0),
							piezas_se_regresan = IF({$type} = -1 , {$quantity}, 0),
						resuelto = 1
					WHERE id_bloque_transferencia_resolucion = {$block_resolution_id}";
			$this->link->query( $sql ) or die( "Error al actualizar la resolución por excedente : {$this->link->error}" );
			return "<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10 text-center\">
							<h5>Registro resuelto exitosamente</h5>
							<button class=\"btn btn-success\" onclick=\"close_emergent();\">
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
		}

		public function saveResolutionDoesntCorrespondRow( $quantity, $type, $block_resolution_id ){
			$sql = "UPDATE ec_bloques_transferencias_resolucion 
						SET piezas_se_quedan = IF({$type} = 1 , {$quantity}, 0),
						piezas_se_regresan = IF({$type} = -1 , {$quantity}, 0),
						resuelto = '1'
					WHERE id_bloque_transferencia_resolucion = {$block_resolution_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar resolución de producto que no corresponde : </br>{$sql}<br> {$this->link->error}" );
			return "<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10 text-center\">
							<h5>Registro resuelto exitosamente</h5>
							<button class=\"btn btn-success\" onclick=\"close_emergent();\">
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";
		}

		public function insertBlockResolution( $type, $block_id, $transfers, $user, $quantity, $detail, $barcode, $unique_code = '',  $return_id = null ){
	//die( 'here|here' );
			$header = explode( '|', $this->getBlockResolutionHeader( $type, $block_id, $transfers, $user, 
				$quantity, $detail, $unique_code ) );
			if($header[0] != 'ok' ){
				return "Error : {$header[0]}";
			}else{
				if( $header[1] == 'exists' ){
					$sql = "UPDATE ec_bloques_transferencias_resolucion SET 
							piezas_faltantes = IF( '{$type}' = 'missing', ( {$quantity} + piezas_faltantes ), piezas_faltantes ),
							piezas_sobrantes = IF( '{$type}' = 'excedent', ( {$quantity} + piezas_sobrantes ), piezas_sobrantes ),
							piezas_no_corresponden = IF( '{$type}' = 'does_not_correspond', ( piezas_no_corresponden + {$quantity} ), piezas_no_corresponden )
						WHERE id_bloque_transferencia_resolucion = '{$header[2]}'";
					$stm = $this->link->query( $sql )or die( "Error al actualizar las piezas de referencia para resolución : {$this->link->error}" );
				}
			//inserta el detalle de escaneo
				$insert_detail = explode( '|', $this->insertResolutionScannerDetail( $header[2], $barcode, $unique_code, $user, $detail, $quantity ) );
				if( $insert_detail[0] != 'ok' ){
					die( "error|Error al insertar el detalle del escaneo de resolución : {$insert_detail[0]}" );
				}
			//inserta el código único
				if( $unique_code != '' && $unique_code != null ){
//die( 'error|header_id : ' . $header[2] );
					$insert_uniqueCode = explode( '|', $this->insertResolutionUniqueCode( $block_id, $user, $unique_code, $detail, $header[2] ) );
					if( $insert_uniqueCode[0] != 'ok' ){
						die( "error|Error al insertar el detalle del escaneo de resolución : {$insert_uniqueCode[0]}" );
					}
				}
			}
		
		//regresa respuesta
			if( $return_id != null ){
				return "ok|{$header[2]}";
			}else{
				return "message_info|<div class=\"row text-center\">
					Producto registrado con exito, 
					<span style=\"font-size:150%; color:red;\">¡RECUERDA SEPARARLO!</span>
					<button type=\"button\" class=\"btn btn-success\" onclick=\"close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );\">
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
				</div>";
			}
		//inserta el detalle del escaneo
		//ec_bloques_transferencias_resolucion_escaneos
		//inserta los detalles si es el caso
		}

		public function insertResolutionScannerDetail( $resolution_block_id, $barcode, $unique_code, $user, $detail, $quantity ){
			$sql = "INSERT INTO ec_bloques_transferencias_resolucion_escaneos SET 
					/*1*/id_bloque_transferencia_resolucion_escaneo = NULL,
					/*2*/id_bloque_transferencia_resolucion = {$resolution_block_id},
					/*3*/codigo_escaneado = '{$barcode}',
					/*4*/codigo_unico = '{$unique_code}',
					/*5*/id_usuario = {$user},
					/*6*/id_producto = {$detail['product_id']},
					/*7*/id_proveedor_producto = {$detail['product_provider_id']},
					/*8*/cantidad_piezas = {$quantity}, 
					/*12*/resuelto = 0";
			$stm = $this->link->query( $sql ) or die( "error|Error al insertar el detalle de escaneo en la resolución : {$this->link->error}" );
		//obtiene el id insertado
					return 'ok|' . $sql;
			$inserted_id = $this->link->insert_id;
			return "ok|{$inserted_id}";
		}

		public function insertResolutionUniqueCode( $reception_block_id, $user, $unique_code, $detail, $resolution_header ){
		//inserta el código único en el detalle 
			$sql = "INSERT INTO ec_transferencia_codigos_unicos SET
					/*1*/id_transferencia_codigo = NULL,
					/*2*/id_bloque_transferencia_recepcion = {$reception_block_id},
					/*3*/id_bloque_transferencia_validacion = ( SELECT 
								id_bloque_transferencia_validacion
							FROM ec_bloques_transferencias_recepcion_detalle 
							WHERE id_bloque_transferencia_recepcion = {$reception_block_id} LIMIT 1 ),
					/*4*/id_usuario_validacion = {$user},
					/*5*/id_usuario_recepcion = {$user},
					/*6*/id_status_transferencia_codigo = 2,
					/*7*/codigo_unico = '{$unique_code}',
					/*8piezas_contenidas,*/
					/*9*/id_transferencia_validacion = NULL,
					/*10*/id_transferencia_recepcion = NULL,
					/*11*/nombre_status = 'Insertado en Resolucion',
					/*12*/fecha_alta = NOW(),
					/*13*/insertado_por_resolucion = '1',
					/*14*/id_bloque_transferencia_resolucion = {$resolution_header}";
//die( 'error|'. $sql );
			$stm = $this->link->query( $sql ) or die( "error|Error al insertar el código único por resolución : {$sql} {$this->link->error}" );
		//obtiene el id insertado
			$inserted_id = $this->link->insert_id;
			return "ok|{$inserted_id}";
		}

		public function getBlockResolutionHeader( $type, $block_id, $transfers, $user, 
			$quantity, $detail, $unique_code = '' ){
			//verifica si ya hay un detalle en el bloque con el mismo proveedor producto
			$sql = "SELECT 
						id_bloque_transferencia_resolucion AS transfer_block_resolution_id
					FROM ec_bloques_transferencias_resolucion
					WHERE id_bloque_transferencia_recepcion = {$block_id}
					AND id_proveedor_producto = {$detail['product_provider_id']}";
//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar si ya existe una cabecera 
				para el bloque y proveedor producto : {$sql} {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				return "ok|exists|{$row['transfer_block_resolution_id']}";
			}
			if( $detail['pieces_stay'] == null || $detail['pieces_stay'] == '' ){
				$detail['pieces_stay'] = 0;
			}
			if( $detail['pieces_return'] == null || $detail['pieces_return'] == '' ){
				$detail['pieces_return'] = 0;
			}
			if( $detail['pieces_faltant'] == null || $detail['pieces_faltant'] == '' ){
				$detail['pieces_faltant'] = 0;
			}
			if( $detail['user_count'] == null || $detail['user_count'] == '' ){
				$detail['user_count'] = 0;
			}
			if( $detail['excedent_count'] == null || $detail['excedent_count'] == '' ){
				$detail['excedent_count'] = 0;
			}
			if( $detail['difference'] == null || $detail['difference'] == '' ){
				$detail['difference'] = 0;
			}
			if( $detail['difference'] == null || $detail['difference'] == '' ){
				$detail['difference'] = 0;
			}
			if( $detail['resolved'] == null || $detail['resolved'] == '' ){
				$detail['resolved'] = 0;
			}
			$sql = "INSERT INTO ec_bloques_transferencias_resolucion SET 
					/*2*/id_bloque_transferencia_recepcion = {$block_id},
					/*3*/id_usuario = {$user},
					/*4*/id_producto = {$detail['product_id']},
					/*5*/id_proveedor_producto = {$detail['product_provider_id']},
					/*6*/piezas_faltantes = IF( '{$type}' = 'missing', {$detail['pieces_faltant']}, 0),
					/*7*/piezas_sobrantes = IF( '{$type}' = 'excedent', {$quantity}, 0),
					/*8*/piezas_no_corresponden = IF( '{$type}' = 'does_not_correspond', $quantity, 0 ),
					/*10*/piezas_se_quedan = {$detail['pieces_stay']},
					/*11*/piezas_se_regresan = {$detail['pieces_return']},
					/*12*/piezas_faltaron = {$detail['pieces_faltant']},
					/*13*/conteo = {$detail['user_count']},
					/*14*/conteo_excedente = {$detail['excedent_count']},
					/*15*/diferencia = {$detail['difference']},
					/*16*/resuelto = '{$detail['resolved']}'";
			$stm = $this->link->query( $sql ) or die( "Error al insertar cabecera de resolución del bloque : {$this->link->error} " );
			$header_id = $this->link->insert_id;
			return "ok|doesnt_exists|{$header_id}";
		}

		public function getProductInventory( $product_id ){
			/*$sql = "SELECT 
						IF( ax.current_inventory IS NULL, 0, ax.current_inventory ) AS current_inventory
					FROM(
						SELECT
							SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL,
								0,
								( mdpp.cantidad * tm.afecta ) )
							) AS current_inventory
						FROM ec_movimiento_detalle_proveedor_producto mdpp
						LEFT JOIN ec_tipos_movimiento tm
						ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
						WHERE mdpp.id_proveedor_producto = {$product_provider_id}
						AND mdpp.id_almacen = {$this->sucursal_warehouse}
						AND mdpp.id_proveedor_producto = {$product_provider_id}
					)ax";*/
			$sql = "SELECT
						IF( ax.current_inventory IS NULL, 0, ax.current_inventory ) AS current_inventory
					FROM(
						SELECT 
							SUM( IF( md.id_movimiento_almacen_detalle IS NULL, 
									0, 
									( md.cantidad * tm.afecta ) 
								) 
							) AS current_inventory
						FROM ec_movimiento_detalle md
						LEFT JOIN ec_movimiento_almacen ma 
						ON ma.id_movimiento_almacen = md.id_movimiento
						LEFT JOIN ec_almacen alm
						ON alm.id_almacen = ma.id_almacen
						LEFT JOIN ec_tipos_movimiento tm 
						ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
						WHERE ma.id_almacen IN ( 
							SELECT 
								id_almacen 
							FROM ec_almacen 
							WHERE id_sucursal = {$this->sucursal_id} 
							AND es_almacen = 1
						)
						AND md.id_producto = {$product_id}
					)ax";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el inventario de movimientos de almacén : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			//echo "inventario : {$sql} {$row['current_inventory']}";
			return $row['current_inventory'];
		}

		public function getResolutionDetails( $transfer_product_id, $reception_block_id ){
			$transfer_product_id = str_replace( '-', ',', $transfer_product_id );
			$sql = "SELECT 
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						SUM( tp.total_piezas_recibidas ) AS received_pieces, 
						p.nombre
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON tp.id_producto_or = p.id_productos
					WHERE tp.id_transferencia_producto IN ( {$transfer_product_id} )";

					/*
					,

					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = p.id_productos
						CONCAT( p.nombre, ' <b style=\"color : green;\">( Clave Proveedor : ', pp.clave_proveedor, ' )</b>' ) AS product_name
					ON pp.id_proveedor_producto = tp.id_proveedor_producto*/
		
//echo $sql;			

			$stm = $this->link->query( $sql ) or die( "Error al consultar información de recepción mercancía : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
/**/
		public function getReceptionProductDetail( $transfers, $product_id, $product_provider_id, $can_delete = 0 ){
		$sql = "SELECT
					tru.id_transferencia_recepcion AS row_id,
					( ( tru.cantidad_cajas_recibidas * pp.presentacion_caja )
					+ ( tru.cantidad_paquetes_recibidos * pp.piezas_presentacion_cluces )
					+ tru.cantidad_piezas_recibidas ) AS pieces_recived,
					CONCAT( u.nombre, 
							IF( u.apellido_paterno = '', '', CONCAT(' ', u.apellido_paterno) ), 
							IF( u.apellido_materno = '', '', CONCAT(' ', u.apellido_materno) )
					) AS user_name,
					tru.fecha_recepcion AS dateTime,
					tru.codigo_validacion AS reception_barcode,
					IF( tru.cantidad_cajas_recibidas != 0, 'box', 
						IF( tru.cantidad_piezas_recibidas != 0, 'pack', 'piece' )
					) AS type_barcode 
				FROM ec_transferencias_recepcion_usuarios tru
				LEFT JOIN ec_transferencia_productos tp
				ON tru.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t
				ON t.id_transferencia = tp.id_transferencia
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tru.id_proveedor_producto
				LEFT JOIN sys_users u 
				ON u.id_usuario = tru.id_usuario
				WHERE tru.id_producto = '{$product_id}'
				AND tru.id_proveedor_producto = '{$product_provider_id}'
				AND t.id_transferencia IN( {$transfers} )";
		//	echo $sql;
		$stm = $this->link->query( $sql ) or die( "Error al consultar historial de productos recibidos : {$this->link->error}" );
		return $this->buildReceptionProductDetail( $stm, 'reception', $can_delete );
	}

	public function getResolutionScansDetail( $block_resolution_id, $product_id, $product_provider_id, $can_delete = 0 ){
		$sql="SELECT
					btre.id_bloque_transferencia_resolucion_escaneo AS row_id,
					btre.cantidad_piezas AS pieces_recived,
					CONCAT( u.nombre, 
							IF( u.apellido_paterno = '', '', CONCAT(' ', u.apellido_paterno) ), 
							IF( u.apellido_materno = '', '', CONCAT(' ', u.apellido_materno) ) 
					) AS user_name,
					btre.fecha_alta AS dateTime,
					IF( btre.codigo_unico != '' AND codigo_unico IS NOT NULL, 
						btre.codigo_unico, 
						btre.codigo_escaneado ) AS reception_barcode,
					IF( btre.es_caja != 0, 'box', 
						IF( btre.es_paquete != 0, 'pack', 'piece' )
					) AS type_barcode 
				FROM ec_bloques_transferencias_resolucion_escaneos btre
				LEFT JOIN sys_users u 
				ON btre.id_usuario = u.id_usuario
				WHERE btre.id_producto = '{$product_id}'
				AND btre.id_proveedor_producto = '{$product_provider_id}'
				AND btre.id_bloque_transferencia_resolucion IN( {$block_resolution_id} )";	
		$stm = $this->link->query( $sql ) or die( "Error al consultar historial de productos recibidos : {$this->link->error}" );
		return $this->buildReceptionProductDetail( $stm, 'resolution', $can_delete );
	}

/*detalle de escaneo*/
	public function buildReceptionProductDetail( $stm, $case, $can_delete ){
		$user_name = '';
		$resp = "<div class=\"row group_card\">";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : green;\"></i>Códigos Únicos";
			$resp .= "</div>";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : yellow;\"></i>Caja / Paquete";
			$resp .= "</div>";
			$resp .= "<div class=\"col-4 text-center\">";
				$resp .= "<i class=\"icon-bookmark\" style=\"color : red;\"></i>Pieza";
			$resp .= "</div>";
		$resp .= "</div>";
		$resp .= '<table class="table table-bordered table-striped">';
			$resp .= '<thead>';
				$resp .= '<tr>';
					$resp .= '<th>Piezas Recibidas</th>';
					$resp .= '<th>Escaneo</th>';
					$resp .= '<th>Fecha / hora</th>';
					//$resp .= '<th class="text-center">X</th>';
					$resp .= ( $can_delete == 1 ? '<th class="text-center">X</th>' : '' );
				$resp .= '</tr>';
			$resp .= '<thead>';
			$resp .= '<tbody>';
		while( $row = $stm->fetch_assoc() ){
			$color = '';
			if( $user_name != $row['user_name'] ){
				$resp .= '<tr>';
					$resp .= "<td colspan=\"3\">{$row['user_name']}</td>";
				$resp .= '</tr>';
			}
			$resp .= '<tr';
		//color de la fila
			if( $row['type_barcode'] == 'box' || $type_barcode == 'pack' ){
				$color = "yellow";
			}else{
				$color = "red";
			}
			$aux = explode($row['reception_barcode'], ' ');
			if( sizeof( $aux ) == 4 ){
				$color = "green";
			}
		//si fue por nombre quita el código de barras
			$row['validation_barcode'] = ( $row['validation_barcode'] == 'Por nombre' ? '' : $row['reception_barcode'] );

			$resp .= " style=\"background : {$color};\"";
			$resp .= '>';
				$resp .= '<td class="text-center">' . $row['pieces_recived'] . '</td>' ;
				$resp .= '<td class="text-center">' . $row['validation_barcode'] . '</td>' ;
				$resp .= '<td class="text-center">' . $row['dateTime'] . '</td>' ;
			$resp .= '</tr>';

			$user_name = $row['user_name'];
		}
			$resp .= '</tbody>';
		$resp .= '</table> <br />';
		/*$resp .= '<div class="row">';
			$resp .= '<div class="col-2"></div>';
			$resp .= '<div class="col-8">';
				$resp .= '<button class="btn btn-success form-control" onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">';
					$resp .= 'Aceptar';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';*/
		return $resp;
	}
/**/
		public function getTransferDetailInfoToResolve( $difference, $transfer_product_id, $type ){
			$sql = "SELECT 
						id_transferencia_producto AS transfer_product_id,
						total_piezas_validacion AS validation_pieces_quantity,
						total_piezas_recibidas AS received_pieces_quantity
					FROM ec_transferencia_productos
					WHERE id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar detalle para llenar botones de resolución : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resp = "<table class=\"table table-bordered\">";
			$resp .= "<thead>
						<tr>
							<th>Enviado</th>
							<th>{$row['validation_pieces_quantity']}</th>
						</tr>
						<tr>
							<th>Recibido</th>
							<th>{$row['received_pieces_quantity']}</th>
						</tr>
					<thead>
					<tbody>";
			switch ( $type ) {
				case 1:
					$receive = $row['validation_pieces_quantity'] - $row['received_pieces_quantity'];
					$resp .= "<tr>
								<td id=\"btn_info_1\" >Recibir</td>
								<td id=\"btn_info_2\" >{$receive}</td>
							<tr>";
				break;
				case 2:
					$description = "Sobra y se queda : ";
					$receive = $row['received_pieces_quantity'] - $row['validation_pieces_quantity'];
					if( $receive < 0 ){
						$description = "Faltante : ";
					}
					$resp .= "<tr>
								<td id=\"btn_info_1\" >{$description}</td>
								<td id=\"btn_info_2\" >{$receive}</td>
							<tr>";
				break;
				case 3:
					$receive = $row['validation_pieces_quantity'] - $row['received_pieces_quantity'];
					$resp .= "<tr>
								<td id=\"btn_info_1\" >Recibir</td>
								<td id=\"btn_info_2\" >{$receive}</td>
							<tr>";
				break;
				
				default:
				break;
			}
			$resp .= "</tbody></table>";
			return $resp;
		}

		public function getFormMissing( $difference, $transfer_product_id, $transfers, $reception_block_id, $info_btn ){
			//var_dump($transfers);
			echo $info_btn;
			$transfer_product_id = str_replace( '-', ',', $transfer_product_id );
			$transfer_detail = $this->getResolutionDetails( $transfer_product_id, $reception_block_id );
			$inventory = $this->getProductInventory( $transfer_detail['product_id'] );
			$possible_inventory = $inventory + $transfer_detail['received_pieces'];
			$resp = "<div class=\"row group_card\" style=\"font-size : 70%;\">
						<h5 class=\"text-center\">{$transfer_detail['product_name']}</h5>
						<div class=\"col-1\"></div>
						<div class=\"col-2\">
							<label>Conteo <br>Físico</label>
							<input type=\"number\" id=\"resolution_field_count\" class=\"form-control text-end\" 
								onkeyup=\"change_missing_resolution( 1, 'missing', '{$transfers}', {$transfer_detail['product_id']}, {$transfer_detail['product_provider_id']} );\">
						</div>
						<div class=\"col-2\">
							<label>Inventario <br>+ Recibido</label>
							<input type=\"number\" id=\"resolution_field_inventory\" class=\"form-control text-end\" value=\"{$possible_inventory}\" readonly>
						</div>
						<div class=\"col-2\">
							<label>Faltante <br>Recibir</label>
							<input type=\"number\" id=\"resolution_field_missing\" class=\"form-control text-end\" value=\"{$difference}\" readonly>
						</div>
						<div class=\"col-2\"><br>
							<label>Diferencia</label>
							<input type=\"number\" id=\"resolution_field_difference\" value=\"0\" class=\"form-control text-end\" readonly>
						</div>
						<div class=\"col-2\">
							<label>Por <br>escanear</label>
							<input type=\"number\" id=\"resolution_field_to_scan\" value=\"0\" class=\"form-control text-end\" readonly>
						</div>
						<div class=\"col-1\"></div>
						
						<div class=\"accordion group_card\" id=\"accordionPanelsResolutionDetail\">
							  <div class=\"accordion-item\">
							    <h2 class=\"accordion-header\" id=\"panelsStayOpen-resolutionHeadingOne\">
							      	<button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#panelsStayOpen-resolutionCollapseOne\" 
							      	aria-expanded=\"false\" aria-controls=\"panelsStayOpen-collapseOne\">
							        			Detalle de escaneos
							  		</button>
							    </h2>
							    <div id=\"panelsStayOpen-resolutionCollapseOne\" class=\"accordion-collapse collapse\" aria-labelledby=\"panelsStayOpen-headingOne\">
							    	<div class\"accordion-body\">"
							    	. $this->getReceptionProductDetail( $transfers, $transfer_detail['product_id'], $transfer_detail['product_provider_id'] ) .

							    	"</div>
							    </div>
							</div>
						</div>

						</div>
						<div class=\"row\">
							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-success form-control\"
									style=\"font-size : 100%;\"
									id=\"resolution_receive_complete_button\"
									onclick=\"save_missing_resolution( 1, 'missing_ok', 0, '{$transfer_product_id}', {$transfer_detail['product_id']}, {$transfer_detail['product_provider_id']}  );\"
									disabled
								>
									<i class=\"icon-ok-circle\"><br>Recibir Completo <b>{$difference}</b></i>
									{$info_btn}
								</button>
							</div>

							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-warning form-control\"
									onclick=\"\"
									style=\"font-size : 100%; display : none;\"
									id=\"resolution_receive_partial_button\"
								>
									<i class=\"icon-ok-circle\"><br>Recibir <b id=\"resolution_to_scan_button\"></b></i>
								</button>
							</div>

							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-danger form-control\"
									onclick=\"close_emergent();\"
									style=\"font-size : 100%;\"
								>
									<i class=\"icon-cancel-circled\"><br>Cancelar</i>
									{$info_btn}
								</button>
							</div>
						</div>
					</div>
					<script type=\"text/javascript\" src=\"js/resolution.js\"></script>";
			return $resp;
		}

		public function saveResolutionMissingRow( $product_id, $product_provider_id, $quantity, $type, $transfers, 
			$block_id, $user, $pieces_faltant, $user_count, $difference ) {
			/*die( " {$product_id}, {$product_provider_id}, {$quantity}, {$type}, {$transfers}, 
			{$block_id}, {$user}, {$pieces_faltant}, {$user_count}, {$difference}" );
			/*$quantity_excedent = ( $type == 'excedent' ? $quantity : 0 );
			$quantity_return = ( $type == 'missing' ? $quantity : 0 );*/
			//echo 'here';
			$block_resolution_header = null;
		//si se recibe completo; inserta los escaneos faltantes
			if( $type == 'missing_ok' ){
				$this->link->autocommit( false );		
				$sql = "SELECT 
							tp.id_transferencia_producto AS transfer_product_id,
							( tp.total_piezas_validacion - tp.total_piezas_recibidas ) AS pieces_to_receive
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_transferencias t
						ON tp.id_transferencia = t.id_transferencia
						WHERE tp.id_proveedor_producto IN( {$product_provider_id} )
						AND tp.id_producto_or IN( {$product_id} )
						AND tp.id_transferencia IN( {$transfers} )
						AND ( tp.total_piezas_validacion - tp.total_piezas_recibidas ) > 0
						GROUP BY tp.id_transferencia_producto
						ORDER BY tp.id_transferencia_producto";
				$stm = $this->link->query( $sql ) or die( "Error al consultar las transferencias pendientes de recibir: {$this->link->error}" );
//echo "<br><br><br>{$sql}<br>";
				while( $row_assign = $stm->fetch_assoc() ){
				//inserta el resgistro de escaneos de recepción
					$sql = "INSERT INTO ec_transferencias_recepcion_usuarios ( id_transferencia_recepcion, id_transferencia_producto,
								id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_recibidas, cantidad_paquetes_recibidos, 
								cantidad_piezas_recibidas, fecha_recepcion, id_status, validado_por_nombre, codigo_validacion )
								VALUES( NULL, '{$row_assign['transfer_product_id']}', '{$user}', '{$product_id}', '{$product_provider_id}', 
									'0', '0', '{$row_assign['pieces_to_receive']}', NOW(), 1, '0', 'resolucion_recibe_completo' )";
					$stm_ins = $this->link->query( $sql ) or die( "error|Error al insertar el registro de recepción ( Resolución ) : {$this->link->error}" );
//echo "<br><br><br>{$sql}<br>";				
				//actualiza la recepcion del producto en la transferencia
					$sql = "UPDATE ec_transferencia_productos tp 
							LEFT JOIN ec_proveedor_producto pp 
							ON tp.id_proveedor_producto = pp.id_proveedor_producto
						SET tp.cantidad_piezas_recibidas =  ( tp.cantidad_piezas_recibidas + {$row_assign['pieces_to_receive']} ),
							tp.total_piezas_recibidas = ( tp.total_piezas_recibidas + {$row_assign['pieces_to_receive']} )
						WHERE tp.id_transferencia_producto = '{$row_assign['transfer_product_id']}'";
					$stm_upd = $this->link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia ( Resolución ) : {$this->link->error}" );
//echo "<br><br><br>{$sql}<br>";
				}
				$this->link->autocommit( true );
				return "<div class=\"row\">
							<div class=\"col-1\"></div>
							<div class=\"col-10 text-center\">
								<h5>Las piezas faltantes fueron recibidas exitosamenente.</h5>
								<button 
									class=\"btn btn-success\" 
									type=\"button\"
									onclick=\"close_emergent();\"
								>
									<i class=\"icon-ok-circle\">Aceptar</i>
								</button>
							</div>
						</div>";
			}else if( $type == 'missing' ){
			//inserta cabecera de resolución del bloque
				$detail = array( 'product_id'=>$product_id, 'product_provider_id'=>$product_provider_id,
						'pieces_faltant'=>$difference, 'user_count'=>$user_count, 'difference'=>$pieces_faltant, 
						'resolved'=>'1' );
				$header = explode( '|', $this->insertBlockResolution( 'missing', $block_id, $transfers, 
					$user, $quantity, $detail, 'insertado_por_resolucion', $unique_code, 'return_id' ) );
				if( $header[0] != 'ok' ){
					die( "Error : {$header[0]}" );
				}

			//id de cabecera de resolución
				$header_id = $header[1];

				$this->link->autocommit( false );		
				$sql = "SELECT 
							tp.id_transferencia_producto AS transfer_product_id,
							( tp.total_piezas_validacion - tp.total_piezas_recibidas ) AS pieces_to_receive,
							tp.id_producto_or AS product_id,
							tp.id_proveedor_producto AS product_provider_id
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_transferencias t
						ON tp.id_transferencia = t.id_transferencia
						WHERE tp.id_proveedor_producto IN( {$product_provider_id} )
						AND tp.id_producto_or IN( {$product_id} )
						AND tp.id_transferencia IN( {$transfers} )
						AND ( tp.total_piezas_validacion - tp.total_piezas_recibidas ) > 0
						GROUP BY tp.id_transferencia_producto
						ORDER BY tp.id_transferencia_producto";
				$stm = $this->link->query( $sql ) or die( "Error al consultar las transferencias pendientes de recibir: {$this->link->error}" );
//echo "<br>here<br><br>{$sql}<br>";
			//inserta piezas que resultaron no ser escaneadas
				while( $row_assign = $stm->fetch_assoc() ){
					$assign_quantity = 0;
					//if( $row_assign['pieces_to_receive'] > 0 && $quantity > 0 ){
						if( $row_assign['pieces_to_receive'] > $quantity ){
							$assign_quantity = $quantity;
						}
						if( $row_assign['pieces_to_receive'] == $quantity ){
							$assign_quantity = $quantity;
						}
						if( $row_assign['pieces_to_receive'] < $quantity ){
							$assign_quantity = $row_assign['pieces_to_receive'];
						}
						if( $assign_quantity > 0 ){
						//inserta el resgistro de escaneos de recepción
							$sql = "INSERT INTO ec_transferencias_recepcion_usuarios ( id_transferencia_recepcion, id_transferencia_producto,
										id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_recibidas, cantidad_paquetes_recibidos, 
										cantidad_piezas_recibidas, fecha_recepcion, id_status, validado_por_nombre, codigo_validacion )
										VALUES( NULL, '{$row_assign['transfer_product_id']}', '{$user}', '{$product_id}', '{$product_provider_id}', 
											'0', '0', '{$assign_quantity}', NOW(), 1, '0', 'recibido_en_resolucion' )";
							$stm_ins = $this->link->query( $sql ) or die( "error|Error al insertar el registro de recepción ( Resolución ) : {$this->link->error}" );
//echo "<br><br><br>{$sql}<br>";
						//actualiza la recepcion del producto en la transferencia
							$sql = "UPDATE ec_transferencia_productos tp 
									LEFT JOIN ec_proveedor_producto pp 
									ON tp.id_proveedor_producto = pp.id_proveedor_producto
								SET tp.cantidad_piezas_recibidas =  ( tp.cantidad_piezas_recibidas + {$assign_quantity} ),
									tp.total_piezas_recibidas = ( tp.total_piezas_recibidas + {$assign_quantity} )
								WHERE tp.id_transferencia_producto = '{$row_assign['transfer_product_id']}'";
							$stm_upd = $this->link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia ( Resolución ) : {$this->link->error}" );
//echo "{$sql}<br>";
						}
							
					//inserta el detalle de resolución
						$sql = "INSERT INTO ec_bloques_transferencias_resolucion_detalle SET 
								/*1*/id_bloque_transferencia_resolucion_detalle = NULL,
								/*2*/id_bloque_transferencia_resolucion = {$header_id},
								/*3*/id_transferencia_producto = '{$row_assign['transfer_product_id']}',
								/*4*/id_usuario = {$user},
								/*5*/id_producto = {$product_id},
								/*6*/id_proveedor_producto = {$product_provider_id},
								/*7*/piezas_faltantes = {$row_assign['pieces_to_receive']},
								/*8*/piezas_sobrantes = 0,
								/*9*/piezas_no_corresponden = 0,
								/*10*/piezas_se_quedan = 0,/**/
								/*11*/piezas_se_regresan = 0,
								/*12*/piezas_faltaron = ( {$row_assign['pieces_to_receive']} - {$assign_quantity} ),
								/*13*/resuelto = 1";
						$stm_upd = $this->link->query( $sql ) or die( "error|Error al insertar el detalle de resolución de Transferencia : {$sql} {$this->link->error}" );						
						$quantity -= $assign_quantity;
//echo "{$sql}<br>";
						//}
					//}
				}
				if( $difference > 0 ){
					$sql = "UPDATE ec_bloques_transferencias_resolucion 
								SET piezas_faltaron = {$difference}
							WHERE id_bloque_transferencia_resolucion = {$header_id}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar las piezas que faltaron en la cabecera de Remisión : {$this->link->error}" );
				}
				$this->link->autocommit( true );		
				return "<div class=\"row\">
							<div class=\"col-2\"></div>
							<div class=\"col-8\" class=\"text-center\">
								<h5>El registro fue resuelto exitosamente!</h5>
								<br>
								<button 
									onclick=\"close_emergent();\" 
									class=\"btn btn-success form-control\"
								> 
						 			<i class=\"icon-ok-circle\">Aceptar</i>
						 		</button>
				 			</div>
				 		</div>";		
			}
	}

		/*public function getReceptionUsersScans(){
			//$sql = "SELECT\"";
		}*/

		public function getFormExcedent( $difference, $block_resolution_id, $transfers, $reception_block_id ){
			$resolution_data = $this->getResolutionData( $block_resolution_id, $reception_block_id );
			//var_dump( $resolution_data );
			$inventory = $this->getProductInventory( $resolution_data['product_id'] );
			$sql = "SELECT 
						GROUP_CONCAT( id_transferencia_producto SEPARATOR '-' ) AS details_ids
					FROM ec_transferencia_productos
					WHERE id_transferencia IN ( {$transfers} )
					AND id_producto_or = {$resolution_data['product_id']}";
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar ids de detalle de transferencia : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$transfer_product_id = $row['details_ids'];
			$transfer_detail = $this->getResolutionDetails( $transfer_product_id, $reception_block_id );
			//$received_ok = $this->getReceptionUsersScans( $transfers, $resolution_data['product_id'] );
			$possible_inventory = $inventory + $transfer_detail['received_pieces'];//+ $resolution_data['excedent_pieces']
			//var_dump($transfers);
			/*$transfer_product_id = str_replace( '-', ',', $transfer_product_id );*/
			/*$resolution_detail = $this->getBlockResolutionDetail( $block_resolution_id );*/
			$resp = "<div class=\"row group_card\" style=\"font-size : 70%;\">
						<h5 class=\"text-center\">{$transfer_detail['product_name']}</h5>
						<div class=\"col-1\"></div>
						<div class=\"col-2\">
							<label>Conteo <br>Físico</label>
							<input type=\"number\" 
								id=\"resolution_field_count\" 
								class=\"form-control text-end\" 
								onkeyup=\"change_excedent_resolution( 1, 'excedent', {$block_resolution_id}, {$resolution_data['product_id']}, {$resolution_data['product_provider_id']} );\"
								onblur=\"compare_excedent( this );\"
							>
						</div>
						<div class=\"col-2\">
							<label>Inventario <br>+ Recibido</label>
							<input 
								type=\"text\"
								id=\"resolution_field_inventory_excedent\" 
								class=\"form-control text-end\" 
								value=\"{$possible_inventory}\" readonly>
						</div>
						<div class=\"col-2\"><br>
							<label>Cont Exced</label>
							<input 
								type=\"text\" 
								id=\"resolution_field_excedent_count\" 
								value=\"\" 
								class=\"form-control text-end\"
								onkeyup=\"change_excedent_resolution( 1, 'excedent', {$block_resolution_id}, {$resolution_data['product_id']}, {$resolution_data['product_provider_id']} );\"
								onblur=\"compare_excedent( this );\"
							>
						</div>
						<div class=\"col-2\">
							<label>Excedente</label>
							<input type=\"number\" 
								id=\"resolution_field_excedent\" 
								class=\"form-control text-end\" 
								value=\"{$difference}\" 
								readonly>
						</div>
						<div class=\"col-2\">
						
						</div>
						<div class=\"col-1\"></div>
						
						<div class=\"accordion group_card\" id=\"accordionPanelsResolutionDetail\">
							  <div class=\"accordion-item\">
							    <h2 class=\"accordion-header\" id=\"panelsStayOpen-resolutionHeadingOne\">
							      	<button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#panelsStayOpen-resolutionCollapseOne\" 
							      	aria-expanded=\"false\" aria-controls=\"panelsStayOpen-collapseOne\">
							        			Detalle de escaneos
							  		</button>
							    </h2>
							    <div id=\"panelsStayOpen-resolutionCollapseOne\" class=\"accordion-collapse collapse\" aria-labelledby=\"panelsStayOpen-headingOne\">
							    	<div class\"accordion-body\">"
							    	. $this->getResolutionScansDetail( $block_resolution_id, $resolution_data['product_id'], $resolution_data['product_provider_id'], 1 ) .

							    	"</div>
							    </div>
							</div>
						</div>

						</div>
						<div class=\"row\">
							<div class=\"col-3\">
								<br>
								<button 
									class=\"btn btn-success form-control\"
									style=\"font-size : 100%;\"
									id=\"resolution_receive_complete_button\"
									onclick=\"save_excedent_resolution( 1, 'excedent', 0, {$block_resolution_id}, {$resolution_data['product_id']}, {$resolution_data['product_provider_id']}  );\"
									disabled
								>
									<i class=\"icon-ok-circle\"><br>Recibir Completo <b>{$difference}</b></i>
								</button>
							</div>

							<div class=\"col-3\">
								<br>
								<button 
									class=\"btn btn-warning form-control\"
									onclick=\"\"
									style=\"font-size : 100%; display : none;\"
									id=\"resolution_receive_partial_button\"
								>
									<i class=\"icon-ok-circle\"><br>Recibir <b id=\"resolution_to_scan_button\"></b></i>
								</button>
							</div>

							<div class=\"col-3\">
								<br>
								<button 
									class=\"btn btn-info form-control\"
									onclick=\"\"
									style=\"font-size : 100%; display : none;\"
									id=\"resolution_receive_partial_button_excedent\"
								>
									<i class=\"icon-ok-circle\"><br>Regresar <b id=\"resolution_to_scan_button_return\"></b></i>
								</button>
							</div>

							<div class=\"col-3\">
								<br>
								<button 
									class=\"btn btn-danger form-control\"
									onclick=\"close_emergent();\"
									style=\"font-size : 100%;\"
								>
									<i class=\"icon-cancel-circled\"><br>Cancelar</i>
								</button>
							</div>
						</div>
					</div>
					<script type=\"text/javascript\" src=\"js/resolution.js\"></script>";
			return $resp;
		}

		public function getResolutionData( $block_resolution_id, $reception_block_id ){
			//die( 'here : ' . $block_resolution_id );
			$sql = "SELECT
					/*1*/id_bloque_transferencia_resolucion AS block_resolution_id,
					/*2*/id_bloque_transferencia_recepcion AS reception_block_id,
					/*3*/id_usuario AS user_id,
					/*4*/id_producto AS product_id,
					/*5*/id_proveedor_producto AS product_provider_id,
					/*6*/piezas_faltantes AS missing_pieces,
					/*7*/piezas_sobrantes AS excedent_pieces,
					/*8*/piezas_no_corresponden AS pieces_doesnt_correspond,
					/*9*/piezas_se_quedan AS pieces_stay,
					/*10*/piezas_se_regresan AS pieces_return,
					/*11*/piezas_faltaron AS pieces_missed,
					/*12*/conteo AS user_count,
					/*13*/conteo_excedente AS excedent_count,
					/*14*/diferencia AS difference,
					/*15*/resuelto AS was_resolved
				FROM ec_bloques_transferencias_resolucion
				WHERE id_bloque_transferencia_resolucion = {$block_resolution_id}";
			//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de resolución de bloque : {$sql} {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( "El detalle del bloque no fue encontrado" );
			}
			$row = $stm->fetch_assoc();
			return $row;
		}

		public function getFormDoesntCorrespond( $difference, $block_resolution_id, $transfers, $reception_block_id ){
			$resolution_data = $this->getResolutionData( $block_resolution_id, $reception_block_id );
			$inventory = $this->getProductInventory( $resolution_data['product_id'] );
			$possible_inventory = $inventory + $resolution_data['received_pieces'];
			$resp = "<div class=\"row group_card\" style=\"font-size : 70%;\">
						<h5 class=\"text-center\">{$resolution_data['pieces_doesnt_correspond']}</h5>

						<div class=\"col-2\">
							<label>Recibido</label>
							<input type=\"number\" id=\"resolution_field_missing\" class=\"form-control text-end\" value=\"{$difference}\" readonly>
						</div>

						
						<div class=\"col-10\">
							<div class=\"accordion group_card\" id=\"accordionPanelsResolutionDetail\">
							  <div class=\"accordion-item\">
							    <h2 class=\"accordion-header\" id=\"panelsStayOpen-resolutionHeadingOne\">
							      	<button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#panelsStayOpen-resolutionCollapseOne\" 
							      	aria-expanded=\"false\" aria-controls=\"panelsStayOpen-collapseOne\">
							        			Detalle de escaneos
							  		</button>
								    </h2>
								    <div id=\"panelsStayOpen-resolutionCollapseOne\" class=\"accordion-collapse collapse\" aria-labelledby=\"panelsStayOpen-headingOne\">
								    	<div class\"accordion-body\">"
								    	. $this->getResolutionScansDetail( $block_resolution_id, $resolution_data['product_id'], $resolution_data['product_provider_id'] ) .

								    	"</div>
								    </div>
								</div>
							</div>

						</div>
						<div class=\"row\">
							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-success form-control\"
									style=\"font-size : 100%;\"
									id=\"resolution_receive_complete_button\"
									onclick=\"save_doesnt_correspond_resolution( 1, {$block_resolution_id}, {$difference}  );\"
									
								>
									<i class=\"icon-ok-circle\"><br>Recibir <b>{$difference}</b></i>
								</button>
							</div>

							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-warning form-control\"
									style=\"font-size : 100%; display : block;\"
									id=\"resolution_receive_partial_button\"
									onclick=\"save_doesnt_correspond_resolution( -1, {$block_resolution_id}, {$difference}  );\"
								>
									<i class=\"icon-ok-circle\"><br>Regresar <b id=\"resolution_to_scan_button\">{$difference}</b></i>
								</button>
							</div>

							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-danger form-control\"
									onclick=\"close_emergent();\"
									style=\"font-size : 100%;\"
								>
									<i class=\"icon-cancel-circled\"><br>Cancelar</i>
								</button>
							</div>
						</div>
					</div>
					<script type=\"text/javascript\" src=\"js/resolution.js\"></script>";
			return $resp;
		}
	}
?>
