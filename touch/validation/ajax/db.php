<?php
	if( isset( $_GET['fl'] ) ){
		include( '../../../config.inc.php' );
		include( '../../../conect.php' );
		include( '../../../conexionMysqli.php' );

		$action = $_GET['fl'];
		$validationTicket = new validationTicket( $link );

		switch ( $action ) {
			case 'seekTicketBarcode' :
				echo $validationTicket->validateTicketBarcode( $_GET['barcode'], $sucursal_id );
			break;

			case 'seekProductBarcode' : 
				$pieces_form = ( isset( $_GET['pieces_form'] ) ? $_GET['pieces_form'] : 0 );
				$pieces_quantity = ( isset( $_GET['pieces_quantity'] ) ? $_GET['pieces_quantity'] : null );
				$was_found_by_name = ( isset( $_GET['found_by_name'] ) ? $_GET['found_by_name'] : null );
				echo $validationTicket->validateProductBarcode( $_GET['barcode'], $_GET['ticket_id'], $pieces_form, $user_id, 
					$sucursal_id, $pieces_quantity, $_GET['sale_detail_id'], $was_found_by_name );
			// $barcode, $ticket_id, $form_pieces, $user, $sucursal, $pieces_quantity = 0
			break;
//proveedores producto
			case 'getOptionsByProductId' :
				$is_by_name = ( $_GET['is_by_name'] == 1 ? 1 : "" );
				echo $validationTicket->getOptionsByProductId( $_GET['product_id'], $sucursal_id, null, 
					$_GET['sale_detail_id'], $is_by_name );
			break;
			
			case 'getTicketInfo' :
				echo $validationTicket->getTicketInfo( $_GET['ticket_id'] );
			break;

			case 'getTicketDetail' :
				echo $validationTicket->getTicketDetail( $_GET['p_k'],  $_GET['type'] );
			break;

			case 'finishValidation' : 
				echo $validationTicket->finishValidation( $_GET['p_k'], $_GET['ticket_has_changed'], $sucursal_id, $user_id );
			break;

			case 'getReturnPrevious': 
				echo $validationTicket->getReturnPrevious( $_GET['p_k'] );
			break;

			case  'getTicketDetailByProduct' :
				echo $validationTicket->getTicketDetailByProduct( $_GET['ticket_id'], $_GET['txt'], $sucursal_id, $user_id );
			break;

			case 'getValidationHistoric' :
				echo $validationTicket->getValidationHistoric( $_GET['product_id'], $_GET['ticket_id'], true, $_GET['sale_detail_id'] );
			break;

			case 'saveSaleReturn' :
				echo $validationTicket->saveSaleReturn( $_GET['ticket_id'], 
					$_GET['return_whith_validation'], $_GET['return_whithout_validation'], $user_id, $sucursal_id,
					$_GET['sale_detail_id'] );
			break;

			case 'validateMannagerPassword':
				echo $validationTicket->validateMannagerPassword( $_GET['pass'], $sucursal_id );
			break;

			case 'getProductProvidersToValidation' :
				echo $validationTicket->getOptionsByProductId( $_GET['product_id'], $sucursal_id, $_GET['ticket_id'], $_GET['sale_detail_id']  );
			break;

			case 'saveNewProductProviderValidation' :
				echo $validationTicket->saveNewProductProviderValidation( $_GET['product_id'], $_GET['product_provider_id'], 
					$_GET['ticket_id'], $sucursal_id, $user_id, $_GET['sale_detail_id'] );
			break;

			case 'change_sale_system_type' :
				echo $validationTicket->change_sale_system_type( $_GET['ticket_id'], $_GET['new_system_type'] );
			break;

			case 'reset_validation' :
				echo $validationTicket->reset_validation( $_GET['sale_id'] );
			break;

			default:
				die( "Permission Denied!" );
			break;
		}
	}
	/**
	* 
	*/
	class validationTicket
	{
		private $link;
		private $system_type;//implementacion Oscar 2023 para saber tipo de sistema
		function __construct( $connection ){
			$this->link = $connection;
			$this->system_type = $this->getSystemType();
		}

		public function getSystemType(){
			$sql = "SELECT IF( id_sucursal = -1, 'linea', 'local' ) AS system_type FROM sys_sucursales WHERE acceso = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el tipo de sistema : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row['system_type'];
		}

		public function validateMannagerPassword( $pass, $store_id ){
			$sql = "SELECT 
						u.id_usuario 
					FROM sys_sucursales s
					LEFT JOIN sys_users u
					ON u.id_usuario = s.id_encargado
					WHERE s.id_sucursal = {$store_id}
					AND u.contrasena = md5('{$pass}')";
			$stm = $this->link->query( $sql ) or die( "Error al validar la contraseña del encargado : {$this->link->error}" );
			if( $stm->num_rows == 1 ){
				return 'ok';
			}else{
				return 'La contraseña del encargado es incorrecta, verifica y vuelve a intentar';
			}
		}

		public function saveSaleReturn( $ticket_id, $return_whith_validation, $return_whithout_validation, 
			$user, $store_id, $sale_detail_id ){
			$this->link->autocommit( false );
//echo "here 1   ";

			if( $return_whith_validation != '' || $return_whithout_validation != '' ){
				/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
				$sql = "DELETE FROM ec_movimiento_detalle_proveedor_producto 
						WHERE id_pedido_validacion 
						IN( SELECT 
								id_pedido_validacion
							FROM ec_pedidos_validacion_usuarios WHERE id_pedido_detalle = {$sale_detail_id}
						)";
				$stm = $this->link->query( $sql ) or die( "Error al elminar los detalles de movimientos de validación : {$this->link->error}" );*/
				
		//elimina los registros previos para sobreescribirlos
				$sql = "DELETE FROM ec_pedidos_validacion_usuarios WHERE id_pedido_detalle = {$sale_detail_id}";
				$stm = $this->link->query( $sql ) or die( "Error al elminar la validacion para sobreescribirla : {$this->link->error}" );
//	
				if( $return_whith_validation != '' ){
//echo 'here 2';
					$validations = explode( '|~|' , $return_whith_validation );
					foreach ($validations as $key => $validation_det ) {
						$validation_detail = explode('~', $validation_det );
						$sql = "SELECT
									IF( p.es_maquilado = 0,
										{$validation_detail[2]},
										( {$validation_detail[2]} * prd.cantidad )
									) AS final_quantity
								FROM ec_productos p
								LEFT JOIN ec_pedidos_detalle pd
								ON pd.id_producto = p.id_productos
								LEFT JOIN ec_productos_detalle prd
								ON prd.id_producto = pd.id_producto
								WHERE pd.id_pedido_detalle = $validation_detail[0]";
						$stm_quantity = $this->link->query( $sql ) or die( "Error al consultar el equivalente en piezas : {$this->link->error}" );
						$result_quantity = $stm_quantity->fetch_assoc();

						$sql = "INSERT INTO ec_pedidos_validacion_usuarios SET 
									id_pedido_validacion = NULL,
									id_pedido_detalle = {$validation_detail[0]},
									id_producto = ( SELECT id_producto FROM ec_pedidos_detalle WHERE id_pedido_detalle = {$validation_detail[0]}),
									id_proveedor_producto = {$validation_detail[3]},
									piezas_validadas = {$result_quantity['final_quantity']},
									piezas_devueltas = 0,
									id_usuario = {$user},
									id_sucursal = {$store_id},
									fecha_alta = NOW(),
									validacion_finalizada = 0,
									tipo_sistema = '{$this->system_type}'";
						$stm = $this->link->query( $sql ) or die( "Error al insertar / actualizar el previo de devolución : {$sql} {$this->link->error}" );
						$validation_id = $this->link->insert_id;
				/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
					//inserta los movimientos de almacen a nivel proveedor producto
						$this->insertMovementProviderProduct( $ticket_id, $store_id, $validation_id, 1, $sale_detail_id, $result_quantity['final_quantity'] );
	*/
					}
				}
		//echo 'here 3';

		//echo "here 4: {$return_whithout_validation}";
			//inserta temporal de devolucion a nivel producto
				if( $return_whithout_validation != '' ){
					$validations = explode( '~' , $return_whithout_validation );
				
					$sql = "INSERT INTO ec_pedidos_validacion_usuarios SET 
								id_pedido_validacion = NULL,
								id_pedido_detalle = {$sale_detail_id},
								id_producto = {$validations[0]},
								id_proveedor_producto = NULL,
								piezas_validadas = 0,
								piezas_devueltas = {$validations[2]},
								id_usuario = {$user},
								id_sucursal = {$store_id},
								fecha_alta = NOW(),
								validacion_finalizada = 0,
								tipo_sistema = '{$this->system_type}'";
					$stm = $this->link->query( $sql ) or die( "Error al insertar / actualizar el previo de devolución : {$sql} {$this->link->error}" );
				}
			}
			$this->link->autocommit( true );
			return 'ok';
		}
			

		public function getTicketDetailByProduct( $ticket_id, $barcode, $sucursal, $user ){
			$this->link->autocommit( false );
			$sql = "SELECT
						( IF( pp.codigo_barras_caja_1 = '{$barcode}'
							OR pp.codigo_barras_caja_2 = '{$barcode}',
							pp.presentacion_caja,
							IF( pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
								OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}',
								pp.piezas_presentacion_cluces,
								1
							)
						) ) * ( IF( p.es_maquilado = 0, 1, prd.cantidad  ) ) AS quantity_factor,
						p.id_productos AS product_id
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON pp.id_producto = p.id_productos
					LEFT JOIN ec_productos_detalle prd
					ON prd.id_producto = p.id_productos
					WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}'
					OR pp.codigo_barras_pieza_2 = '{$barcode}'
					OR pp.codigo_barras_pieza_3 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
					OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}' )";
//die( 'Error|'. $sql );
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar coincidencias por código de barras : {$this->link->error}" );
			//if( $stm->num_rows <= 0 ){
			//	die( 'error|here' )
				return $this->getTicketDetailByProductName( $ticket_id, $barcode );
				//die( "error|El código de barras '{$barcode}' no fue encontrado." );
			//}
			$scanned_data = $stm->fetch_assoc();
		//consulta si coincide con algun proveedor producto
			$sql = "SELECT
							pvu.id_pedido_validacion AS validation_id,
							pvu.id_producto AS product_id,
							pvu.id_proveedor_producto AS product_provider_id,
							( pvu.piezas_validadas - pvu.piezas_devueltas ) AS validated_quantity,
							pd.id_pedido AS sale_id,
							pd.id_pedido_detalle AS sale_detail_id,
							IF( pp.codigo_barras_caja_1 = '{$barcode}'
								OR pp.codigo_barras_caja_2 = '{$barcode}',
								pp.presentacion_caja,
								IF( pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
									OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}',
									pp.piezas_presentacion_cluces,
									1
								)
							) AS quantity
						FROM ec_pedidos_validacion_usuarios pvu
						LEFT JOIN ec_pedidos_detalle pd
						ON pd.id_pedido_detalle = pvu.id_pedido_detalle
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_proveedor_producto = pvu.id_proveedor_producto
						WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}'
						OR pp.codigo_barras_pieza_2 = '{$barcode}'
						OR pp.codigo_barras_pieza_3 = '{$barcode}'
						OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
						OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
						OR pp.codigo_barras_caja_1 = '{$barcode}'
						OR pp.codigo_barras_caja_2 = '{$barcode}' )
						AND pd.id_pedido = {$ticket_id}";
					//die( $sql );
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar coincidencias por código de barras en el detalle de validación : {$sql} {$this->link->error}" );
			$rows_count = $stm->num_rows;
			/*if( $rows_count <= 0 ){
				return $this->getTicketDetailByProductName( $ticket_id, $barcode );
			}else{*/
				$quantity_to_assign = $scanned_data['quantity_factor'];
				$counter = 0;
				while( $r = $stm->fetch_assoc() ){
					$assign_quantity = 0;
					$counter ++;
					if( $quantity_to_assign > 0 ){
//echo 'here';
						if( $r['validated_quantity'] > $quantity_to_assign ){
							$assign_quantity = $quantity_to_assign;
//echo "Here 1 {$assign_quantity}";											
						}else if( $r['validated_quantity'] < $quantity_to_assign && $r['validated_quantity'] > 0 ){
							$assign_quantity = $r['validated_quantity'];
//echo "Here 2 {$assign_quantity}";					
						}else if( $r['validated_quantity'] == $quantity_to_assign && $r['validated_quantity'] > 0 ){
							$assign_quantity = $quantity_to_assign;
//echo "Here 3 {$assign_quantity}";					
						
						}
						/*if( $counter == $rows_count ){
echo "Here 1 {$assign_quantity}";					
							//$assign_quantity = $quantity_to_assign;
						
						}*/
//echo "Here 1 {$assign_quantity}";					
						if( $assign_quantity > 0 ){
							$sql = "UPDATE ec_pedidos_validacion_usuarios pvu
										SET pvu.piezas_devueltas = ( pvu.piezas_devueltas + {$assign_quantity} )
									WHERE pvu.id_pedido_validacion = {$r['validation_id']}";
							$stm_upd = $this->link->query( $sql ) or die( "Error al actualizar devolucion en las validaciones : {$this->link->error}" );
/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
$this->insertMovementProviderProduct( $ticket_id, $sucursal, $r['validation_id'], 1, $assign_quantity );
*/
						}
					}
					$quantity_to_assign -= $assign_quantity;
				}
				if( $quantity_to_assign > 0 ){
						return $this->returnProductById( $ticket_id, $scanned_data['product_id'], $barcode, $sucursal, $user );
				}
			//}
			$this->link->autocommit( true );
			return "quick_scann|<p style=\"color : red;\">La validación fue devuelta exitosamente</p>";
		}

		public function returnProductById( $ticket_id, $product_id, $barcode, $store_id, $user ){
		//consulta si coincide con algun producto pendiente de validar
			//
			$sql = "SELECT
						pvu.id_pedido_validacion AS validation_id,
						pvu.id_producto AS product_id,
						pvu.id_proveedor_producto AS product_provider_id,
						IF( pvu.id_pedido_validacion IS NULL,0, ( pvu.piezas_validadas - ( - pvu.piezas_devueltas ) ) )AS validated_quantity,
						pd.id_pedido AS sale_id,
						pd.cantidad AS total_quantity,
						pd.id_pedido_detalle AS sale_detail_id,
						IF( pp.codigo_barras_caja_1 = '{$barcode}'
							OR pp.codigo_barras_caja_2 = '{$barcode}',
							pp.presentacion_caja,
							IF( pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
								OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}',
								pp.piezas_presentacion_cluces,
								1
							)
						) AS quantity
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = pd.id_producto
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pd.id_pedido_detalle = pvu.id_pedido_detalle
					WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}'
					OR pp.codigo_barras_pieza_2 = '{$barcode}'
					OR pp.codigo_barras_pieza_3 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
					OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}' )
					AND pd.id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "error|Error al consultar si hay producto pendiente de validar para devolver : {$this->link->error}" );
			$row = $stm->fetch_assoc();
//return 'error|' . $sql;
		$quantity = $row['total_quantity'] - (  $row['validated_quantity'] + $row['quantity'] );
		//return 'error|piezas : ' . $test;
			if(  $quantity < 0 ){		
			$quantity = $quantity * -1;	
					return "error|<p class=\"text-center\" style=\"color : red;\">Las piezas escaneadas 
					para devolver superan la cantidad de escaneos de validación : Se pasa por {$quantity} pzs
					<p>";
			}else{

				$sql = "INSERT INTO ec_pedidos_validacion_usuarios SET 
							id_pedido_validacion = NULL,
							id_pedido_detalle = (SELECT 
								id_pedido_detalle 
								FROM ec_pedidos_detalle 
								WHERE id_pedido = {$ticket_id}
								AND id_producto = {$product_id}
							),
							id_producto = {$product_id},
							id_proveedor_producto = NULL,
							piezas_validadas = 0,
							piezas_devueltas = {$row['quantity']},
							id_usuario = {$user},
							id_sucursal = {$store_id},
							fecha_alta = NOW(),
							validacion_finalizada = 0,
							tipo_sistema = '{$this->system_type}'";
				$stm = $this->link->query( $sql ) or die( "Error al insertar la devolución a nivel producto : {$sql} {$this->link->error}" );
				$this->link->autocommit( true );
				return "quick_scann|<p style=\"color : red;\">Devuelto exitosamente</p>";
			}
		}

		public function getTicketDetailByProductName( $ticket_id, $barcode ){
			$barcode_array = explode(' ', $barcode );
			$condition = " OR (";
			foreach ($barcode_array as $key => $barcode_txt ) {
				$condition .= ( $condition == ' OR (' ? '' : ' AND' );
				$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
			}
			$condition .= " )";
			$sql = "SELECT
					pp.id_producto AS product_id,
					CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>', 
						IF( p.es_ultimas_piezas = '1', CONCAT(' <span class=\"color_red\">$ ', pd.precio , '</span>' ), '' )
					) AS name,
					IF( pp.codigo_barras_pieza_1 = '{$barcode}' 
					OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' 
					OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}' 
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' 
					OR pp.codigo_barras_caja_1 = '{$barcode}' 
					OR pp.codigo_barras_caja_2 = '{$barcode}', 'is_barcode', 'is_not_barcode' ) AS type,
					pd.id_pedido_detalle AS sale_detail_id
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_productos p
				ON p.id_productos = pd.id_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_producto = p.id_productos
				WHERE pd.id_pedido IN( {$ticket_id} ) 
				AND ( pp.clave_proveedor LIKE '%{$barcode}%'
				{$condition} 
				OR ( pp.codigo_barras_pieza_1 = '{$barcode}' 
					OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' 
					OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}' 
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' 
					OR pp.codigo_barras_caja_1 = '{$barcode}' 
					OR pp.codigo_barras_caja_2 = '{$barcode}' 
					)
				) AND pp.id_proveedor_producto IS NOT NULL
				GROUP BY p.id_productos, pd.id_pedido_detalle";
		//echo ( $sql );
			$stm_name = $this->link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$link->error}" );
			$rows_counter = $stm_name->num_rows;
			if( $rows_counter <= 0 ){
				return "error|<h3 class=\"inform_error\">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>";
			}

//	$resp = ( ? "seeker" : "was_scanned" ) . "|";
			while ( $row_name = $stm_name->fetch_assoc() ) {
				$resp .= "<div class=\"group_card\" onclick=\"getValidationHistoric( {$row_name['product_id']}, {$ticket_id}, {$row_name['sale_detail_id']} );\">";
					$resp .= "<p>{$row_name['name']}</p>";
				$resp .= "</div>";
			}
			//echo $resp;
			return $resp;
		}

		public function getReturnPrevious( $ticket_id ){
			include( '../views/return.php' );
		}

		public function getOptionsByProductId( $product_id, $store_id, $ticket_id = null, $sale_detail_id = null, $is_by_name ){
//die( "parametros : {$product_id}, {$store_id}, {$ticket_id}, {$sale_detail_id}, {$is_by_name}" );
			$sql = "";
			$is_maquiled = 0;
			if( $ticket_id == null ){
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
			}else{
			//verifica si es maquilado para intercambiar el id
				$sql = "SELECT
							id_producto_ordigen AS product_id
						FROM ec_productos_detalle 
						WHERE id_producto = {$product_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar si el producto es maquilado : {$sql} {$this->link->error}" );
				if( $stm->num_rows > 0 ){
					$row = $stm->fetch_assoc();
					$product_id = $row['product_id'];
					$is_maquiled = 1;
				}
		//solo proveedores product=o que no han sido validados
				$sql = "SELECT
							pp.id_proveedor_producto AS product_provider_id,
							pp.clave_proveedor AS provider_clue,
							pp.piezas_presentacion_cluces AS pack_pieces,
							pp.presentacion_caja AS box_pieces,
							ipp.inventario AS inventory,
							pp.codigo_barras_pieza_1 AS piece_barcode_1
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_pedidos_validacion_usuarios pvu
						ON pvu.id_proveedor_producto = pp.id_proveedor_producto
						AND pvu.id_pedido_detalle IN( {$sale_detail_id} ) /*SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE id_pedido = {$ticket_id} AND id_producto = {$product_id}*/
						LEFT JOIN ec_inventario_proveedor_producto ipp
						ON ipp.id_producto = pp.id_producto 
						AND ipp.id_proveedor_producto = pp.id_proveedor_producto
						WHERE pp.id_producto = {$product_id}
						AND pvu.id_proveedor_producto IS NULL
						AND ipp.id_almacen IN ( SELECT id_almacen FROM ec_almacen WHERE es_almacen = 1 AND id_sucursal = {$store_id} )
						ORDER BY ipp.inventario DESC";
			}
			$stm_name = $this->link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$link->error}" ); 
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
					$resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', null, null, null, '{$is_by_name}' );\">
							<i class=\"icon-ok-circle\">Continuar</i>
						</button><br><br>
						<button class=\"btn btn-danger form-control\"
							onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
							<i class=\"icon-ok-circle\">Cancelar</i>
						</button>";
				}else{
					$resp .= "<button id=\"select_p_p_by_name_btn\" class=\"btn btn-success form-control\" onclick=\"setProductModel( '{$sale_detail_id}', 1, {$product_id}, {$ticket_id}, '{$is_by_name}' );\">
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

		public function validateTicketBarcode( $barcode, $user_sucursal ){
			$resp = "";
			$sql = "SELECT 
						id_pedido AS row_id,
						folio_nv,
						total,
						pagado
					FROM ec_pedidos
					WHERE id_sucursal = '{$user_sucursal}'
					AND folio_nv != 'agrupacion'
					AND folio_nv = '{$barcode}'
					ORDER BY id_pedido DESC
					LIMIT 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar código de barras del ticket : {$this->link->error} {$sql}" );
			if( $stm->num_rows <= 0 ){
				$resp = "<p align=\"center\" style=\"color: red; font-size : 200%;\">La nota de ventas con el folio : <b>{$barcode}</b> no fue encontrada.<br>Verifica y vuelve a intentar!</p>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-2\"></div>";
					$resp .= "<div class=\"col-8\">";
						$resp .= "<button class=\"btn btn-success form-control\"
						 onclick=\"close_emergent( null, '#barcode_seeker' );\">";
							$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
						$resp .= "</button>";
					$resp .= "</div>";
				$resp .= "</div><br><br>";
				return $resp;
			}else{
				$row = $stm->fetch_assoc();
			//consulta si el pedido tiene pagos
				$sql = "SELECT 
							SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ) AS payments_total
						FROM ec_pedido_pagos pp
						WHERE pp.id_pedido = {$row['row_id']}";
				$stm_aux = $this->link->query( $sql ) or die( "Error al consultar los pagos del pedido : {$this->link->error}" );
				$row_aux = $stm_aux->fetch_assoc();
			//consulta los pagos por devolucion
				$sql = "SELECT 
							SUM( dp.monto ) AS pagos_devolucion
						FROM ec_devolucion_pagos dp
						LEFT JOIN ec_devolucion d
						ON dp.id_devolucion = d.id_devolucion
						WHERE d.id_pedido IN( {$row['row_id']} )";
				$stm = $this->link->query( $sql ) or die( "Error al consultar pagos por devolucion para comprobacion : {$this->link->error}" );
				$devolucion_row = $stm->fetch_assoc();
				$pagos_dev = $devolucion_row['pagos_devolucion'];

				$difference = round( $row_aux['payments_total'] - $pagos_dev ) - round( $row['total'] );
				if( ( $difference != -1 && $difference != 0 && $difference != -1 ) && $row['pagado'] == 1 ){//venta no liquidada $row_aux['payments_total'] < $row['total']
					$resp = "<p align=\"center\" style=\"color: red; font-size : 200%;\">La nota de ventas con el folio : <b>{$barcode}</b> no ah sido liquidada<br>Verifica y vuelve a intentar!</p>";
					$resp .= "<h5>{$row_aux['payments_total']} VS {$row['total']} = {$difference}, {$row['pagado']}</h5>";
					$resp .= "<div class=\"row\">";
						$resp .= "<div class=\"col-2\"></div>";
						$resp .= "<div class=\"col-8\">";
							$resp .= "<button class=\"btn btn-success form-control\"
							 onclick=\"close_emergent( null, '#barcode_seeker' );\">";
								$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
							$resp .= "</button>";
						$resp .= "</div>";
					$resp .= "</div><br><br>";
					return $resp;
				}else if( $row_aux['payments_total'] == 0 && $row['pagado'] == 0 ){//apartado sin pagos
					$resp = "<p align=\"center\" style=\"color: red; font-size : 200%;\">La nota de ventas <b class=\"text-primary\">( apartado )</b> con el folio : <b>{$barcode}</b> no tiene pagos registrados<br>Verifica y vuelve a intentar!</p>";
					$resp .= "<div class=\"row\">";
						$resp .= "<div class=\"col-2\"></div>";
						$resp .= "<div class=\"col-8\">";
							$resp .= "<button class=\"btn btn-success form-control\"
							 onclick=\"close_emergent( null, '#barcode_seeker' );\">";
								$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
							$resp .= "</button>";
						$resp .= "</div>";
					$resp .= "</div><br><br>";
					return $resp;
				}
				return "ok|{$row['row_id']}|{$row['folio_nv']}|{$row['total']}";
			}
		}

		public function validateProductBarcode( $barcode, $ticket_id, $form_pieces, $user, $sucursal, 
			$pieces_number = null, $sale_detail_id, $found_by_name ){
			$pieces_quantity = ( $pieces_number == null ? 1 : $pieces_number );
			$resp = "";
		//verifica que el código de barras exista
			$sql = "SELECT
						pp.id_producto AS product_id,
						p.es_maquilado AS is_maquiled,
						IF(	pp.codigo_barras_caja_1 = '{$barcode}'
							OR pp.codigo_barras_caja_2 = '{$barcode}',
							pp.presentacion_caja,
							IF(	pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
								OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}',
								pp.piezas_presentacion_cluces,
								1
							) 
						) AS quantity_factor,
						p.es_ultimas_piezas AS is_last_pieces
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					WHERE pp.codigo_barras_pieza_1 = '{$barcode}'
					OR pp.codigo_barras_pieza_2 = '{$barcode}'
					OR pp.codigo_barras_pieza_3 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
					OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}'";//AND pp.presentacion_caja = 1 AND pp.presentacion_caja = 1
			$stm = $this->link->query( $sql ) or die( "Error al consultar que exista el código de barras del producto : {$this->link->error}");
			if( $stm->num_rows <= 0 ){
				return $this->seekProductByName( $barcode, $ticket_id, $form_pieces, $user, $sucursal, $pieces_number );
			}
			$scanned_data = $stm->fetch_assoc();
		//conversión de piezas de acuerdo a caja, paquete, pieza
			$pieces_quantity = ( $pieces_number == null ? 1 : $pieces_number );
			$pieces_quantity = ( $pieces_quantity * $scanned_data['quantity_factor'] );
		//detalle especifico
			$specific_detail = "";
			if( $sale_detail_id != '' && $sale_detail_id != null && $sale_detail_id != 'null' ){
				$specific_detail = "AND pd.id_pedido_detalle = {$sale_detail_id}";
			}

			$sql = "SELECT
						ax.id_pedido_detalle AS row_id,
						ax.product_id,
						ax.product_provider_id,
						ax.total_quantity - IF( pvu.id_pedido_validacion IS NULL, 
							0, 
							IF( ax.is_maquiled = 0,
								SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_devueltas /*- pvu.piezas_devueltas*/ ) ) ),
								(SELECT
									ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_devueltas /*- pvu.piezas_devueltas*/ ) ) ) / cantidad )
									FROM ec_productos_detalle
									WHERE id_producto = ax.product_id
								) 
							)
						) AS total_quantity, 
						IF( pvu.id_pedido_validacion IS NULL, 
							0, 
							IF( ax.is_maquiled = 0,
								SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas /*- pvu.piezas_devueltas*/ ) ) ),
								(SELECT
									ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas /*- pvu.piezas_devueltas*/ ) ) ) / cantidad )
									FROM ec_productos_detalle
									WHERE id_producto = ax.product_id
								) 
							)
						) AS validated_quantity,
						ax.is_maquiled,
						ax.is_last_pieces,
						ax.product_name,
						ax.codigo_barras_pieza_1 AS piece_barcode
					FROM(
						SELECT
							pd.id_producto AS product_id,
							pp.id_proveedor_producto AS product_provider_id,
							pd.cantidad AS total_quantity,
							pd.id_pedido_detalle,
							p.es_maquilado AS is_maquiled,
							p.es_ultimas_piezas AS is_last_pieces,
							CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>', 
								IF( p.es_ultimas_piezas = '1', CONCAT(' <span class=\"color_red\">$ ', pd.precio , '</span>' ), '' )
							) AS product_name,
							pp.codigo_barras_pieza_1
						FROM ec_pedidos_detalle pd
						LEFT JOIN ec_proveedor_producto pp
						ON pp.id_producto = pd.id_producto
						LEFT JOIN ec_productos p
						ON p.id_productos = pd.id_producto
						WHERE pd.id_pedido = {$ticket_id}
						AND ( pp.codigo_barras_pieza_1 = '{$barcode}'
							OR pp.codigo_barras_pieza_2 = '{$barcode}'
							OR pp.codigo_barras_pieza_3 = '{$barcode}' 
							OR pp.codigo_barras_caja_1 = '{$barcode}'
							OR pp.codigo_barras_caja_2 = '{$barcode}'
							OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
							OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
						)
						AND pp.id_producto = {$scanned_data['product_id']}
						{$specific_detail}
						GROUP BY pd.id_pedido_detalle
					)ax
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pvu.id_pedido_detalle = ax.id_pedido_detalle
					GROUP BY ax.id_pedido_detalle";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los códigos de barras del producto : {$this->link->error} <textarea>{$sql}</textarea>");
//die( $sql );
			if( $stm->num_rows <= 0 ){
				$resp = "separate_this_product|<p align=\"center\" style=\"color : red; font-size : 200%;\">
					El producto no esta en esta nota de venta.<br><b style=\"color : orange;\">Aparta este producto de los que se le van a entregar al cliente.</b></p>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-2\"></div>";
					$resp .= "<div class=\"col-8\">";
						$resp .= "<button class=\"btn btn-danger form-control\" onclick=\"close_emergent( null, '#barcode_seeker' );\">";
							$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
						$resp .= "</button>";
					$resp .= "</div><br><br>";
				$resp .= "</div>";
				return $resp;
			}
			if( $stm->num_rows > 1 && $found_by_name != 1 ){
				return $this->buildLastPiecesForm( $stm );
			}
			$row = $stm->fetch_assoc();
		//verifica que no se pase del numero por validar
			if( ( $row['validated_quantity'] + $pieces_quantity ) > $row['total_quantity'] ){
					if( $row['validated_quantity'] >= $row['total_quantity'] ){
						$resp .= "<p align=\"center\" style=\"color : orange; font-size : 200%;\">
						Este producto ya fue validado completamente en relación a la nota de venta. Vuelve a contar estos productos</p>";
					}
				//$resp .= "<p style=\"font-size : 150%;\">Piezas compradas : {$row['total_quantity']}</p>";
				//$resp .= "<p style=\"font-size : 150%;\">Piezas validadas : {$row['validated_quantity']}</p>";
				//}else if( $row['validated_quantity'] + $pieces_quantity >= $row['total_quantity'] ){
					
					$pending_to_validate = ( $row['total_quantity'] - $row['validated_quantity'] );
					$excedent_pieces = ( $pieces_quantity - $pending_to_validate );
					$resp .= "<p align=\"center\" style=\"color : orange; font-size : 200%;\">La cantidad de piezas supera la 
					cantidad que se tiene que validar :</p>
					<div class=\"row\">
						<div class=\"col-6 text-center\">
							Piezas validadas : <br>{$row['validated_quantity']}
						</div>
						<div class=\"col-6 text-center\">
							Piezas capturadas : <br>{$pieces_quantity}
						</div>
						<div class=\"col-6 text-center\">
							Se excede por  : <br><b style=\"color : red;\">{$excedent_pieces}</b>
						</div>
						<div class=\"col-6 text-center\">
							Pendientes de validar  : <br><b style=\"color : green;\">{$pending_to_validate}</b>
						</div>
					<div>";
				$resp .= $this->getMaquileIfExists( $ticket_id, $scanned_data['product_id'], $scanned_data['is_maquiled'] );
				$resp .= "<p style=\"color : red;\" align=\"center\">*** Si el producto está de más, apartalo de los que se le van a entregar al cliente ***</p>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-1\"></div>";
					$resp .= "<div class=\"col-10\">";
						$resp .= "<button class=\"btn btn-danger form-control\" onclick=\"close_emergent( null, '#product_barcode_seeker_pieces' );\">";
							$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
						$resp .= "</button>";
					$resp .= "</div><br><br>";
				$resp .= "</div>";
				return $resp;
			}

			if( $form_pieces == 1 && $pieces_number == null ){
				return $this->getPiecesForm( $barcode, $ticket_id, $sale_detail_id, $found_by_name );
			} 

//$this->link->autocommit( false );
			if( $scanned_data['is_maquiled'] == 1 ){
//echo 'here';
			//consulta el producto origen
				$sql = "SELECT 
							pd.id_producto_ordigen AS product_id,
							pd.cantidad AS quantity
						FROM ec_productos_detalle pd
						WHERE pd.id_producto = {$scanned_data['product_id']}";
//echo $sql;
				$stm_maq = $this->link->query( $sql ) or die( "Error al consultar el origen de la maquila : {$sql} {$this->link->error}" );
				if( $stm_maq->num_rows <= 0 ){
					$resp = "<h5>El producto escaneado es maquilado y no cuenta con un producto origen,
					toma una captura de pantalla / fotografía  y envíala al encargado de sistemas</h5>";
					$resp .= "<br><button
									type=\"button\" 
									class=\"btn btn-danger\"
									onclick=\"close_emergent();\"
								>
								<p>Pide al encargado que ingrese su contraseña para continuar :<p>
								<input type=\"password\" class=\"form-control\">
							<i class=\"\">Aceptar</i>
						</button>";
				}else{
					$maquile_row = $stm_maq->fetch_assoc();
					//$equivalent_quantity =round( $maquile_row['quantity'] * $pieces_quantity , 2 );
					$equivalent_quantity = $maquile_row['quantity'] * $pieces_quantity;
//echo "Here : " . $equivalent_quantity;
				//consulta los proveedores productos y su inventario en la sucursal
					$sql = "SELECT
								ax.id_proveedor_producto AS product_provider_id,
								IF( ax.inventory IS NULL, 0, ax.inventory ) AS inventory
							FROM(
								SELECT
									pp.id_proveedor_producto,
									ipp.inventario AS inventory
									/*SUM( IF( mdpp.cantidad IS NULL, 0, ( tm.afecta * mdpp.cantidad ) ) ) AS inventory*/
								FROM ec_proveedor_producto pp 
								/*LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
								ON pp.id_proveedor_producto = mdpp.id_proveedor_producto
								AND mdpp.id_almacen */
								LEFT JOIN ec_inventario_proveedor_producto ipp
								ON ipp.id_proveedor_producto = pp.id_proveedor_producto
								AND ipp.id_almacen IN( 
									SELECT 
										id_almacen 
									FROM ec_almacen 
									WHERE id_sucursal = {$sucursal} 
									AND es_almacen = 1
									)
								/*LEFT JOIN ec_tipos_movimiento tm
								ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento*/
								WHERE pp.id_producto = {$maquile_row['product_id']}
								GROUP BY pp.id_proveedor_producto
							)ax
							GROUP BY ax.id_proveedor_producto
							ORDER BY ax.inventory DESC";//Oscar 2023 cambiado a inventario DESC
					$stm_maquile = $this->link->query( $sql ) or die( "Error al consultar inventarios : {$sql} {$this->link->error}" );
//die( $sql );
					$counter = 0;
					$inventories_number = $stm_maquile->num_rows;
					//return 'here';
					while ( $product_provider = $stm_maquile->fetch_assoc() ) {
						$counter ++;
//var_dump( $product_provider );
						$assign_quantity = 0;
						if( $equivalent_quantity > 0 ){
							if( $product_provider['inventory'] > $equivalent_quantity && $product_provider['inventory'] > 0 ){
								$assign_quantity = $equivalent_quantity;
//echo "here_1_1 {$assign_quantity}<br>";
							}else if( $product_provider['inventory'] < $equivalent_quantity && $product_provider['inventory'] > 0 ){
								$assign_quantity = ( $product_provider['inventory'] > 0 ? $product_provider['inventory'] : 0 );
//echo "here_2_1 {$assign_quantity}<br>";
							}else if( $product_provider['inventory'] == $equivalent_quantity ){
								$assign_quantity = $equivalent_quantity;
//echo "here_3_1 {$assign_quantity}<br>";
							}
							if( $counter == $inventories_number ){
								$assign_quantity = $equivalent_quantity;
//echo "here_4_1 {$assign_quantity}<br>";
							}
							if( $assign_quantity > 0 ){
//echo "here : {$product_provider['product_provider_id']}";
								$sql = "INSERT INTO ec_pedidos_validacion_usuarios ( /*1*/id_pedido_validacion, /*2*/id_pedido_detalle, /*3*/id_producto, 
								/*4*/id_proveedor_producto, /*5*/piezas_validadas, /*6*/id_usuario, /*7*/id_sucursal, /*8*/fecha_alta,
								/*9*/validacion_finalizada, /*10*/tipo_sistema )
								VALUES( /*1*/NULL, /*2*/{$row['row_id']}, /*3*/{$maquile_row['product_id']}, /*4*/{$product_provider['product_provider_id']}, 
									/*5*/{$assign_quantity}, /*6*/{$user}, /*7*/{$sucursal}, /*8*/NOW(), /*9*/0, /*10*/'{$this->system_type}' )";
						//return "{$sql}";
								$stm_upd = $this->link->query( $sql ) or die( "Error al insertar el registro de validación de la venta en validación : {$sql} {$this->link->error}" );
								$validation_id = $this->link->insert_id;
/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
$movement = $this->insertMovementProviderProduct( $ticket_id, $sucursal, $validation_id, 1, $row['row_id'], $assign_quantity );
	
								if( $movement != 'ok' ){
									die( "Error : {$movement}" );
								}
*/
						/*inserta detalle de movimiento de almacen
								$sql = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, 
								id_movimiento_almacen_detalle, id_proveedor_producto, cantidad, fecha_registro, id_sucursal, id_equivalente,
								status_agrupacion, id_tipo_movimiento, id_almacen ) VALUES(  )";*/
							}
						}
						$equivalent_quantity -= $assign_quantity;
					}

				}
			}else{
//echo 'pasa a producto que no es maquilado';
			//consulta los proveedores productos y su inventario en la sucursal
				$sql = "SELECT
							ax.id_proveedor_producto AS product_provider_id,
							IF( ax.inventory IS NULL, 0, ax.inventory ) AS inventory
						FROM(
							SELECT
								pp.id_proveedor_producto,
								/*SUM( IF( mdpp.cantidad IS NULL, 0, ( tm.afecta * mdpp.cantidad ) ) ) AS inventory*/
								ipp.inventario AS inventory
							FROM ec_proveedor_producto pp 
							/*LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
							ON pp.id_proveedor_producto = mdpp.id_proveedor_producto
							AND mdpp.id_almacen */
							LEFT JOIN ec_inventario_proveedor_producto ipp
							ON ipp.id_proveedor_producto = pp.id_proveedor_producto
							AND ipp.id_almacen IN( 
								SELECT 
									id_almacen 
								FROM ec_almacen 
								WHERE id_sucursal = {$sucursal} 
								AND es_almacen = 1
								)
							/*LEFT JOIN ec_tipos_movimiento tm
							ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento*/
							WHERE pp.id_producto = {$scanned_data['product_id']}
							AND ( pp.codigo_barras_pieza_1 = '{$barcode}'
								OR pp.codigo_barras_pieza_2 = '{$barcode}'
								OR pp.codigo_barras_pieza_3 = '{$barcode}' 
								OR pp.codigo_barras_caja_1 = '{$barcode}'
								OR pp.codigo_barras_caja_2 = '{$barcode}'
								OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
								OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}'
							)
							GROUP BY pp.id_proveedor_producto
						)ax
						GROUP BY ax.id_proveedor_producto
						ORDER BY ax.inventory ASC";//Oscar 2023 cambiado a inventario ASC
//die( $sql );
//echo $sql;
				$counter = 0;
				$equivalent_quantity = $pieces_quantity;
				$inventory_stm = $this->link->query( $sql ) or die( "Error al consultar proveedores producto del inventario : {$this->link->error}" );
				$inventories_number = $inventory_stm->num_rows;
		//inserta el registro de validación
				while ( $product_provider = $inventory_stm->fetch_assoc() ) {
//var_dump( $product_provider );
					$counter ++;
					$assign_quantity = 0;
					if( $equivalent_quantity > 0 ){
						if( $product_provider['inventory'] > $equivalent_quantity  && $product_provider['inventory'] > 0 ){
							$assign_quantity = $equivalent_quantity;
//echo "here_1_2 {$assign_quantity}<br>";
						}else if( $product_provider['inventory'] < $equivalent_quantity  && $product_provider['inventory'] > 0 ){
							//$assign_quantity = $product_provider['inventory'];
							$assign_quantity = ( $product_provider['inventory'] > 0 ? $product_provider['inventory'] : 0 );
//echo "here_2_2 {$assign_quantity}<br>";
						}else if( $product_provider['inventory'] == $equivalent_quantity ){
							$assign_quantity = $equivalent_quantity;
//echo "here_3_2 {$assign_quantity}<br>";
						}
						if( $counter == $inventories_number ){
							$assign_quantity = $equivalent_quantity;
//echo "here_4_1 {$assign_quantity}<br>";
						}
						if( $assign_quantity > 0 ){
			//echo "here : {$product_provider['product_provider_id']}";
							$sql = "INSERT INTO ec_pedidos_validacion_usuarios ( /*1*/id_pedido_validacion, /*2*/id_pedido_detalle, /*3*/id_producto, 
							/*4*/id_proveedor_producto, /*5*/piezas_validadas, /*6*/id_usuario, /*7*/id_sucursal, /*8*/fecha_alta,
							/*9*/validacion_finalizada, /*10*/tipo_sistema )
							VALUES( /*1*/NULL, /*2*/{$row['row_id']}, /*3*/{$scanned_data['product_id']}, /*4*/{$product_provider['product_provider_id']}, 
								/*5*/{$assign_quantity}, /*6*/{$user}, /*7*/{$sucursal}, /*8*/NOW(), /*9*/0, /*10*/'{$this->system_type}' )";
//echo "<br>{$sql}<br>";
							$stm_upd = $this->link->query( $sql ) or die( "Error al insertar el registro de validación de la venta en validación : {$sql} {$this->link->error}" );		
							$validation_id = $this->link->insert_id;
				//die( "parametros : {$ticket_id}, {$sucursal}, {$validation_id}, 1, {$row['row_id']}, {$assign_quantity}" );
/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR								
$movement = $this->insertMovementProviderProduct( $ticket_id, $sucursal, $validation_id, 1, 

								$row['row_id'], $assign_quantity );
							if( $movement != 'ok' ){
								die( "Error : {$movement}" );
							}*/
						}
					}
					$equivalent_quantity -= $assign_quantity;
				}
			}
//$this->link->autocommit( true );
//return "{$sql}";
			//$resp = "ok|<p align=\"center\" style=\"font-size : 200%; color : green;\"></p>";
			//$resp .= "<div class=\"row\">";
			//	$resp .= "<div class=\"col-2\"></div>";
			//	$resp .= "<div class=\"col-8\">";
					$resp = "ok|<h5 style=\"color : white;\">Producto validado!</h5>";
					//$resp .= "<button class=\"btn btn-success form-control\" onclick=\"close_emergent( null, '#barcode_seeker' );\">";
					//	$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
					//$resp .= "</button>";
				//$resp .= "</div><br><br>";
			//$resp .= "</div>";
			return $resp;
		}

		public function buildLastPiecesForm( $stm ){
			$resp = "<div class=\"text-center\">
					<h5 class=\"color_red\">Selecciona el producto que deseas validar : </h5>
					<table class=\"table\">
						<thead>
							<tr>
								<th>Producto / Precio</th>
								<th>Seleccionar</th>
							</tr>
						</thead>
						<tbody id=\"last_pieces_list\">";
			$counter = 0;
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
							<td id=\"last_pieces_0_{$counter}\" class=\"no_visible\">{$row['row_id']}</td>
							<td id=\"last_pieces_1_{$counter}\" class=\"no_visible\">{$row['product_provider_id']}</td>
							<td id=\"last_pieces_2_{$counter}\">{$row['product_name']}</td>
							<td class=\"text-center\">
								<input type=\"radio\" 
									id=\"last_pieces_3_{$counter}\" 
									value=\"{$row['row_id']}\"
									name=\"last_pieces_catalogue\">
							<td>
							<td id=\"last_pieces_4_{$counter}\" class=\"no_visible\">{$row['piece_barcode']}</td>
						</tr>";				
				$counter ++;
			}

			$resp .= "</tbody>
					</table>
					<br>
					<button 
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"setLastPiecesModel();\"

					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>
					<br><br>
					<button 
						type=\"button\"
						class=\"btn btn-danger\"
						onclick=\"close_emergent();\"
					>
						<i class=\"icon-cancel-circled\">Cancelar</i>
					</button>
				</div>";
			return $resp;
		}
	//verifica si existe la maquila 
		public function getMaquileIfExists( $ticket_id, $product_id, $is_maquiled ){
			if( $is_maquiled == 0 ){
				$sql = "SELECT 
						ax.origin_quantity,
						ax.origin_name,
						ax.origin_product_id,
						ax.validated_origin,
						pd.cantidad AS destinity_quantity,
						p.nombre AS destinity_name,
						ax.id_pedido,
						ax.id_pedido_detalle,
						(SELECT 
							SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ) / ax.cantidad
							FROM ec_pedidos_validacion_usuarios pvu
							WHERE pvu.id_pedido_detalle = pd.id_pedido_detalle 
							AND pvu.id_producto = ax.destinity_product_id
						) AS validated_destinity
					FROM(
						SELECT
							pd.cantidad AS origin_quantity,
							p.nombre AS origin_name,							
							IF( prd.id_producto IS NULL, 0, prd.id_producto ) AS origin_product_id,
							IF( prd.id_producto_ordigen IS NULL, 0, prd.id_producto_ordigen ) AS destinity_product_id,
							pd.id_pedido,
							pd.id_pedido_detalle,
							prd.cantidad,
							(SELECT 
								SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) )
								FROM ec_pedidos_validacion_usuarios pvu
								WHERE pvu.id_pedido_detalle = pd.id_pedido_detalle 
								AND pvu.id_producto = {$product_id}
							) AS validated_origin
						FROM ec_productos_detalle prd
						LEFT JOIN ec_productos p
						ON p.id_productos = prd.id_producto_ordigen
						LEFT JOIN ec_pedidos_detalle pd
						ON prd.id_producto_ordigen = pd.id_producto
						WHERE pd.id_pedido = {$ticket_id}
						AND prd.id_producto_ordigen = {$product_id}
					)ax
					LEFT JOIN ec_pedidos_detalle pd
					ON pd.id_pedido = ax.id_pedido
					AND pd.id_producto = ax.origin_product_id
					LEFT JOIN ec_productos p
					ON p.id_productos = ax.origin_product_id";
		//echo "<textarea>{$sql}</textarea>";
			}else{
				$sql = "SELECT 
						ax.origin_quantity,
						ax.origin_name,
						ax.origin_product_id,
						pd.cantidad AS destinity_quantity,
						p.nombre AS destinity_name,
						ax.id_pedido,
						ax.validated_origin,
						(SELECT 
							SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ) * ax.cantidad
							FROM ec_pedidos_validacion_usuarios pvu
							WHERE pvu.id_pedido_detalle = pd.id_pedido_detalle 
							AND pvu.id_producto = ax.origin_product_id
						) AS validated_destinity
					FROM(
						SELECT
							pd.cantidad AS origin_quantity,
							p.nombre AS origin_name,							
							IF( prd.id_producto_ordigen IS NULL, 0, prd.id_producto_ordigen ) AS origin_product_id,					
							IF( prd.id_producto IS NULL, 0, prd.id_producto ) AS destinity_product_id,
							pd.id_pedido,
							prd.cantidad,
							(SELECT 
								SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) )
								FROM ec_pedidos_validacion_usuarios pvu
								WHERE pvu.id_pedido_detalle = pd.id_pedido_detalle 
								AND pvu.id_producto = prd.id_producto
							) AS validated_origin
						FROM ec_productos_detalle prd
						LEFT JOIN ec_productos p
						ON p.id_productos = prd.id_producto
						LEFT JOIN ec_pedidos_detalle pd
						ON prd.id_producto = pd.id_producto
						WHERE pd.id_pedido = {$ticket_id}
						AND prd.id_producto = {$product_id}
					)ax
					LEFT JOIN ec_pedidos_detalle pd
					ON pd.id_pedido = ax.id_pedido
					AND pd.id_producto = ax.origin_product_id
					LEFT JOIN ec_productos p
					ON p.id_productos = ax.origin_product_id";
			}
			$stm = $this->link->query( $sql ) or die( "Error al consultar el origen de la maquila : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resp = "";
			if( $row['destinity_quantity'] > 0 || $row['origin_quantity'] > 0 ){
				$resp .= "<div class=\"row\">
							<div class=\"col-1\"></div>
							<div class=\"col-10\">
								<p>Este producto fue vendido de la siguiente manera, verifica y vuelve a intentar : </p>
								<table class=\"table table-striped table-bordered\">
									<thead>
										<tr>
											<th class=\"text-center\">Producto</th>
											<th class=\"text-center\">Vendido</th>
											<th class=\"text-center\">Validado</th>
											<th class=\"text-center\">Por validar</th>
										</tr>
									</thead>
									<tbody>";

				if( $row['destinity_quantity'] > 0 ){
					$resp .= "<tr>
								<td class=\"text-left\">{$row['origin_name']}</td> 
								<td class=\"text-center\"><b style=\"color : red;\">{$row['origin_quantity']}</b></td>
								<td class=\"text-center\"><b style=\"color : green;\">{$row['validated_origin']}</b></td>
								<td class=\"text-center\"><b style=\"color : orange;\">" . ( $row['origin_quantity'] - $row['validated_origin'] ) . "</b></td>
							</tr>";
				}
				if( $row['destinity_quantity'] > 0 ){
					$resp .= "<tr>
								<td class=\"text-left\">{$row['destinity_name']}</td> 
								<td class=\"text-center\"><b style=\"color : red;\">{$row['destinity_quantity']}</b></td>
								<td class=\"text-center\"><b style=\"color : green;\">{$row['validated_destinity']}</b></td>
								<td class=\"text-center\"><b style=\"color : orange;\">" . ( $row['destinity_quantity'] - $row['validated_destinity'] ) . "</b></td>
							</tr>";
				}
				$resp .= "</tbody>
						</table>
					</div>
				</div>";
			}
			return $resp;
		}

		public function seekProductByName( $barcode, $ticket_id, $form_pieces, $user, $sucursal, $pieces_number ){
			/*$resp = "<p align=\"center\" style=\"color : red; font-size : 200%;\">
					El código de barras '<b>{$barcode}</b>' no pertenece a ningún producto.<br>Verifica y vuelve a intentar</p>";
			$resp .= "<div class=\"row\">";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\">";
					$resp .= "<button class=\"btn btn-danger form-control\" onclick=\"close_emergent( null, '#barcode_seeker' );\">";
						$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
					$resp .= "</button>";
				$resp .= "</div><br><br>";
			$resp .= "</div>";
			return $resp;*/
			$barcode_array = explode(' ', $barcode );
			$condition = " OR (";
			foreach ($barcode_array as $key => $barcode_txt ) {
				$condition .= ( $condition == ' OR (' ? '' : ' AND' );
				$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
			}
			$condition .= " )";
			$sql = "SELECT
					pd.id_pedido_detalle AS sale_detail_id,
					pp.id_producto AS product_id,
					CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>', 
						IF( p.es_ultimas_piezas = '1', CONCAT(' <span class=\"color_red\">$ ', pd.precio , '</span>' ), '' )
					) AS name,
					IF( pp.codigo_barras_pieza_1 = '', 
						IF( pp.codigo_barras_pieza_2 = '',
							pp.codigo_barras_pieza_3,
							pp.codigo_barras_pieza_2
						),
						pp.codigo_barras_pieza_1 
					) AS barcode/*oscar 2023 para emergente de ultimas piezas*/
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_productos p
				ON p.id_productos = pd.id_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_producto = p.id_productos
				WHERE pd.id_pedido IN( {$ticket_id} ) AND ( pp.clave_proveedor LIKE '%{$barcode}%'
				{$condition} OR p.orden_lista = '{$barcode}' ) AND pp.id_proveedor_producto IS NOT NULL
				GROUP BY p.id_productos, pd.id_pedido_detalle";
			$stm_name = $this->link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / clave proveedor : {$this->link->error}" );
			if( $stm_name->num_rows <= 0 ){
				return 'message_info|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
				. '<div class="row"><div class="col-2"></div><div class="col-8">'
				. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\' );lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">Aceptar</button></div><br/><br/>';
			}
 
			$resp = "seeker|";
			$c = 0;
	/*//aqui esta el detalle de ultimas piezas
			while ( $row_name = $stm_name->fetch_assoc() ) {
				/*$resp .= "<div class=\"group_card\" id=\"response_seeker_{$c}\" onclick=\"setProductByName( {$row_name['product_id']}, {$row_name['sale_detail_id']}, 1 );\">";
					$resp .= "<p>{$row_name['name']}</p>";
				$resp .= "</div>";
				$c ++;	
			}
		se puede arreglar con esto :	
			if( $stm->num_rows > 1 && $found_by_name != 1 ){
				return $this->buildLastPiecesForm( $stm );
			}
		*/	
		/*oscar 2023 para emergente de ultimas piezas*/
			if( $stm_name->num_rows > 1 ){
				$row = $stm_name->fetch_assoc();
				$resp = $this->validateProductBarcode( $row['barcode'], $ticket_id, $form_pieces, $user, $sucursal, 
				$pieces_number = null, '', '' );
			}else{
				$row_name = $stm_name->fetch_assoc();
				$resp .= "{$row_name['product_id']}|{$row_name['sale_detail_id']}";
			}
		/**/
			//echo $resp;
			return $resp;
		}

		public function getPiecesForm( $barcode, $ticket_id, $sale_detail_id = null, $found_by_name = null ){
		//busqueda por nombre
			$sql = "SELECT
						pp.id_producto AS product_id,
						p.es_maquilado AS is_maquiled
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					WHERE pp.clave_proveedor LIKE '%{$barcode}%'
					OR p.nombre LIKE '%{$barcode}%'
					OR p.orden_lista = '{$barcode}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar que exista el código de barras del producto : {$this->link->error}");
			
			$resp = "pieces_form|<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-10 text-center\">
							<h5>Ingresa el número de piezas : </h5>
							<br>
						</div>
						<div class=\"col-1\"></div>
						<div class=\"col-4\"></div>
						<div class=\"col-4\">
							<input 
								type=\"number\" 
								class=\"form-control\"
								id=\"pieces_quantity_tmp\"
								onkeyup=\"validate_no_decimals( this );\"
							>
							<br>
							<button
								type=\"button\"
								class=\"btn btn-success form-control\"
								onclick=\"setPiecesQuanity( '{$barcode}', {$ticket_id}, '{$sale_detail_id}', '{$found_by_name}' );\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
							<br>
							<br>
							<button
								type=\"button\"
								class=\"btn btn-danger form-control\"
								onclick=\"close_emergent();\"
							>
								<i class=\"icon-cancel-circled\">Cancelar</i>
							</button>
						</div>
					</div>";
			return $resp;
		}

		public function getValidationHistoric( $product_id, $ticket_id, $is_editable = true, $sale_detail_id = null ){
			$prefix = ( $is_editable != true ? '_no_edit' : '' ); 
			$resp = "<div class=\"row\">
						<div class=\"col-12\">
							<table class=\"table table-bordered\" style=\"width: 100%; position : relative;\">
								<thead class=\"header_sticky\">";
			if( $is_editable == true ){
				//datos del producto 
				$sql = "SELECT 
						p.nombre AS product_name,
						pd.cantidad AS product_quantity
					FROM ec_productos p
					LEFT JOIN ec_pedidos_detalle pd
					ON p.id_productos = pd.id_producto
					WHERE /*pd.id_producto = {$product_id}
					AND */pd.id_pedido = {$ticket_id}";
				if( $sale_detail_id != null ){
					$sql .= " AND pd.id_pedido_detalle = {$sale_detail_id}";
				}
				$sql .= " GROUP BY pd.id_pedido_detalle";
				$stm = $this->link->query( $sql ) or die( "Error al consultar datos generales del producto :  {$sql} {$this->link->error}" );
				$product_info = $stm->fetch_assoc();
			
				$resp .= "<tr>
							<th colspan=\"2\" class=\"text-center btn-warning\">
								{$product_info['product_name']} 
								(<b id=\"sale_product_total_quantity\">{$product_info['product_quantity']}</b> pzs)
							</th>
						</tr>";
			}
		//<th class=\"text-center\">Devolver</th>
							$resp .= "<tr>
										<th class=\"text-center\">Modelo / Nombre</th>
										<th class=\"text-center\"><h5 style=\"font-size : 200%; color : green;\">Se lleva</h5></th>
									</tr>
								</thead>
								<tbody id=\"{$prefix}validation_resumen_list\">"; 

		//consulta productos validados
			$sql = "SELECT 
						p.id_productos AS product_id,
						pd.id_pedido_detalle AS sale_detail_id,
						p.nombre AS product_name,
						pp.id_proveedor_producto AS product_provider_id,
						CONCAT( pp.clave_proveedor, ' / ', pp.presentacion_caja, '  <span class=\"color_red\">$ ', pd.precio, '</span>' ) AS provider_clue,
						IF( p.es_maquilado = 0,
							SUM( pvu.piezas_validadas - pvu.piezas_devueltas ),
							(SELECT
								SUM( pvu.piezas_validadas - pvu.piezas_devueltas ) / cantidad
							FROM ec_productos_detalle
							WHERE id_producto = p.id_productos
							)
						) AS validated_quantity
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pd.id_pedido_detalle = pvu.id_pedido_detalle
					LEFT JOIN ec_productos p
					ON p.id_productos = pd.id_producto
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = pvu.id_proveedor_producto
					WHERE pd.id_pedido = {$ticket_id}
					/*AND p.id_productos = {$product_id} deshabilitado Oscar 2023 para maquilados*/
					AND pvu.id_pedido_validacion IS NOT NULL
					AND pvu.id_proveedor_producto IS NOT NULL";
					if( $sale_detail_id != null ){
						$sql .= " AND pd.id_pedido_detalle = {$sale_detail_id}";
					}
					$sql .= " GROUP BY pd.id_pedido_detalle, pp.id_proveedor_producto";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de validación : {$this->link->error}" );
			$counter = 0;
//$resp .= "<br>{$sql}</br>";
			$return_total = 0;
			$validated_total = 0;
			while( $row = $stm->fetch_assoc() ){
				//<td id=\"{$prefix}vrs_row_2_{$counter}\" class=\"text-end\" width=\"33.3%\">{$row['validated_quantity']}</td>
				$resp .= "<tr>
							<td id=\"{$prefix}vrs_row_0_{$counter}\" class=\"no_visible\">{$row['sale_detail_id']}</td>
							<td id=\"{$prefix}vrs_row_1_{$counter}\" class=\"text-center\" width=\"33.3%\">{$row['provider_clue']}</td>
							<td class=\"text-end\" width=\"33.3%\">
								<input 
									type=\"number\" 
									id=\"{$prefix}vrs_row_2_{$counter}\"
									value=\"\"
									min=\"0\"
									onkeyup=\"prevent_negative_number( this, event );\"
									class=\"form-control text-end\"
									" . ( $is_editable != true ? ' readonly' : '' ) . 
									( $is_editable == true ? "onchange=\"recalculateReturnProduct();\"" : "" ) 
								. ">
							</td>
							<td id=\"{$prefix}vrs_row_3_{$counter}\" class=\"text-center no_visible\" width=\"33.3%\">{$row['product_provider_id']}</td>
						</tr>";
				$counter ++;
				$validated_total += $row['validated_quantity']; 
				$return_total += 0; 
			}
		//consulta la cantidad que no se ah validado

			$sql = "SELECT 
						pd.id_pedido_detalle AS sale_detail_id,
						p.id_productos AS product_id,
						p.nombre AS product_name,
						pd.cantidad AS total_quantity,
						pp.id_proveedor_producto AS product_provider_id,
						pp.clave_proveedor AS provider_clue,
						IF( p.es_maquilado = 0,
							SUM( pvu.piezas_validadas - pvu.piezas_devueltas ),
							(SELECT
								SUM( pvu.piezas_validadas - pvu.piezas_devueltas ) / cantidad
							FROM ec_productos_detalle
							WHERE id_producto = p.id_productos
							)
						) AS validated_quantity
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pd.id_pedido_detalle = pvu.id_pedido_detalle
					LEFT JOIN ec_productos p
					ON p.id_productos = pd.id_producto
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_proveedor_producto = pvu.id_proveedor_producto
					WHERE pd.id_pedido = {$ticket_id}
					/*AND p.id_productos = {$product_id} deshabilitado por Oscar 2023*/";
					if( $sale_detail_id != null ){
						$sql .= " AND pd.id_pedido_detalle = {$sale_detail_id}";
					}
					$sql .= " GROUP BY pd.id_pedido_detalle, p.id_productos";
//echo $sql;
			$stm = $this->link->query( $sql ) or die( "Error al consultar el pendiente  de validar en el histórico : {$this->link->error}" );
			
			
			while( $row = $stm->fetch_assoc() ){
				$validated = $row['total_quantity'] - ( $row['validated_quantity'] < 0 ? ( $row['validated_quantity'] * - 1 ) : $row['validated_quantity'] );
//<td id=\"{$prefix}vrs_row_2_{$counter}\" class=\"text-end color_red\" width=\"33.3%\">{$validated}</td>
				$resp .= "<tr>
							<td id=\"{$prefix}vrs_row_0_{$counter}\" class=\"no_visible\">{$row['product_id']}</td>
							<td id=\"{$prefix}vrs_row_1_{$counter}\" class=\"text-center\" width=\"33.3%\">
							<h5 style=\"\">Devolver</h5></td>
							<td class=\"text-end color_red\" width=\"33.3%\" id=\"row_without_validation\"
									style=\"font-size : 120%; background-color : transparent; border : 0;\"
							>
								0
							</td>
							<td id=\"{$prefix}vrs_row_3_{$counter}\" class=\"no_visible\" width=\"33.3%\">{$row['sale_detail_id']}</td>
						</tr>";
				//" . ( $is_editable != true ? ' readonly' : '' ) . "
			
				$counter ++;
				$validated_total += $validated; 
				$return_total += 0; 
			}
/*
<input 
									type=\"number\" 
									 
									value=\"{$validated}\" 
									class=\"form-control text-end color_red\"
									style=\"font-size : 120%; background-color : transparent; border : 0;\"
									readonly
								>
	<tfoot>	
							<tr>
								<td class=\"text-end\">Total</td>
								<td class=\"text-end\">{$validated_total}</td>
								<td class=\"text-end\">{$return_total}</td>
							</tr>
						</tfoot>	
*/
//save_return_product( {$ticket_id} );			
			$resp .= "	</tbody>";
			if( $is_editable == true ){
					$resp .= "<tfoot>
								<th class=\"text-center\">
									<button 
										type=\"button\"
										class=\"btn btn-info\"
										onclick=\"add_product_provider_in_validation( {$product_id}, {$ticket_id}, {$sale_detail_id} );\"
									>
										<i class=\"icon-plus-circle\">Agregar Fila</i>
									</button>
								</th>
								<th class=\"text-center\">
									<button 
										type=\"button\"
										class=\"btn btn-success\"
										onclick=\"save_return_product( {$ticket_id}, {$row['sale_detail_id']} );\"
									>
										<i class=\"icon-ok-circle\">Guardar</i>
									</button>
								</th>
							</tfoot>";
				}
			$resp .= "		</table>
						   </div>
						<div class=\"col-1\"></div>
					</div>";
			return $resp;
		}

		public function getTicketDetail( $ticket_id, $type ){
			$resp = "";
			if( $type == "pending" ){
				$sql = "SELECT
							ax.row_id,
							ax.name,
							ax.original_quantity AS original_quantity,
							ax.quantity_to_cancel,
							ax.quantity
							
						FROM(
							SELECT
								pd.id_pedido_detalle AS row_id,
								p.nombre AS name,
								pd.cantidad AS original_quantity, 
								IF( p.es_maquilado = 0, 
									SUM( IF( pvu.id_pedido_validacion IS NULL OR pvu.id_proveedor_producto IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ),
									(SELECT
										ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL OR pvu.id_proveedor_producto IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ) / cantidad )
										FROM ec_productos_detalle
										WHERE id_producto = p.id_productos
									)
								) AS quantity,
								IF( p.es_maquilado = 0, 
									SUM( IF( pvu.id_pedido_validacion IS NULL OR pvu.id_proveedor_producto IS NOT NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ),
									(SELECT
										ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL OR pvu.id_proveedor_producto IS NOT NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ) / cantidad )
										FROM ec_productos_detalle
										WHERE id_producto = p.id_productos
									)
								) AS quantity_to_cancel
							FROM ec_pedidos_detalle pd
							LEFT JOIN ec_productos p
							ON p.id_productos = pd.id_producto
							LEFT JOIN ec_pedidos_validacion_usuarios pvu
							ON pvu.id_pedido_detalle = pd.id_pedido_detalle
							WHERE pd.id_pedido = {$ticket_id}
							GROUP BY pd.id_pedido_detalle
						)ax
						/*WHERE ax.quantity > 0*/
						GROUP BY ax.row_id";
//die( $sql );
			}else if( $type == "validated" ){
				$sql = "SELECT 
							pvu.id_pedido_detalle AS row_id,
							p.nombre AS name, 
							(IF( p.es_maquilado = 0,
								IF( pvu.id_pedido_validacion IS NULL, 0, SUM( pvu.piezas_validadas - pvu.piezas_devueltas ) ),
								(SELECT
										ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL, 0, ( pvu.piezas_validadas - pvu.piezas_devueltas ) ) ) /  cantidad )
										FROM ec_productos_detalle
										WHERE id_producto = p.id_productos
								)
							) )AS original_quantity
						FROM ec_pedidos_validacion_usuarios pvu
						LEFT JOIN ec_pedidos_detalle pd 
						ON pd.id_pedido_detalle = pvu.id_pedido_detalle
						LEFT JOIN ec_productos p
						ON p.id_productos = pd.id_producto
						WHERE pd.id_pedido = {$ticket_id}
						AND pvu.id_proveedor_producto IS NOT NULL
						GROUP BY pvu.id_pedido_detalle";
	//die( $sql );
			}
			
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle del pedido : {$this->link->error}" );
			/*if( $stm->num_rows <= 0 ){
				return '<tr><td colspan="3">Sin registros!</td></tr>';
			}*/
			while( $row = $stm->fetch_assoc() ){
				$quantity = $row['original_quantity'] - ( $row['quantity'] < 0 ? ( $row['quantity'] * -1 ) : $row['quantity'] );
				$quantity = $quantity - ( $row['quantity_to_cancel'] < 0 ? ( $row['quantity_to_cancel'] * -1 ) : $row['quantity_to_cancel'] );
				if( $quantity > 0 ){
					$resp .= "<tr>";
						$resp .= "<td class=\"no_visible\">{$row['row_id']}</td>";
						$resp .= "<td>{$row['name']}</td>";
						$resp .= "<td>{$quantity}</td>";//{$row['quantity']}
					$resp .= "</tr>";
				}
			}
			//$resp .= '|';
			return $resp;
		}

		public function insertMovementProviderProduct( $ticket_id, $sucursal, $validation_id, $movement_type = 1, 
			$sale_detail_id, $quantity = 0 ){
		//consulta los movimientos almacen		
			$sql = "SELECT
					NULL,
					ax.id_movimiento_almacen_detalle AS movement_detail_id,
					pvu.id_proveedor_producto AS product_provider_id,
					IF( {$quantity} = 0,
			    		IF( pvu.id_pedido_validacion IS NULL, 0, SUM( pvu.piezas_validadas - pvu.piezas_devueltas ) ),
			    		{$quantity}
			    	) AS quantity,
					NOW() AS date_time,
					'{$sucursal}' AS store_id,
					-1 AS group_status,
					IF( {$movement_type} = 1, ax.id_tipo_movimiento, 12 ) AS movement_type,
					ax.id_almacen AS warehouse_id,
					'{$validation_id}' AS validation_id
				FROM
				(
				    SELECT 
						md.id_movimiento_almacen_detalle,
				    	pd.id_pedido_detalle,
				    	ma.id_tipo_movimiento,
				    	ma.id_almacen
					FROM  ec_pedidos_detalle pd
					LEFT JOIN ec_movimiento_detalle md
					ON md.id_pedido_detalle = pd.id_pedido_detalle
				    LEFT JOIN ec_movimiento_almacen ma
				    ON ma.id_movimiento_almacen = md.id_movimiento
					WHERE pd.id_pedido = '{$ticket_id}'
					AND pd.id_pedido_detalle = {$sale_detail_id}
					GROUP BY pd.id_pedido_detalle
				)ax
				LEFT JOIN ec_pedidos_validacion_usuarios pvu
				ON pvu.id_pedido_detalle = ax.id_pedido_detalle
				WHERE pvu.id_pedido_validacion = '{$validation_id}'
				GROUP BY pvu.id_proveedor_producto";
//		die( $sql );
//echo ( "<br>" . $sql );
			$stm = $this->link->query( $sql ) or die( "Error al insertar los detalles de movimiento almacen proveedor producto : {$this->link->error}" );
			while( $row = $stm->fetch_assoc() ){
				$sql = "CALL spMovimientoDetalleProveedorProducto_inserta( {$row['movement_detail_id']}, {$row['product_provider_id']}, {$row['quantity']}, 
				{$row['store_id']}, {$row['movement_type']}, {$row['warehouse_id']}, {$row['validation_id']}, 12 )";
				$procedure_stm = $this->link->query( $sql ) or die( "Error al ejecutar procedure spMovimientoDetalleProveedorProducto_inserta : {$this->link->error} {$sql}" );
			}
			return 'ok';
		}

		public function finishValidation( $ticket_id, $ticket_has_changed, $store_id, $user ){
			$resp = "";
//die( "tkt_id : " . $ticket_id );
			$url = '';
			$ticket_has_changed = 0;
			$sql = "SELECT 
						id_pedido_validacion 
					FROM ec_pedidos_validacion_usuarios 
					WHERE id_pedido_detalle 
					IN( SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE id_pedido = {$ticket_id} )
					AND id_proveedor_producto IS NULL";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si hay productos por devolver : {$this->link->error}" );
			if( $stm->num_rows > 0 ){//si encuentra registros por devolver entra en proceso de devolución
				$ticket_has_changed = 1;
			}
			$this->link->autocommit( false );
			if( $ticket_has_changed == 1 ){
				include( 'SaleReturn.php' );
				$SaleReturn = new SaleReturn( $this->link, $store_id, $user );
				$data = $SaleReturn->getProductsToReturnSinceValidation( $ticket_id );
				//if( sizeof( $data ) > 0 ){
				$SaleReturn->setTicketData( $ticket_id );
				$return_id = $SaleReturn->insertReturnHeader( $ticket_id );
				$SaleReturn->insertReturnMovementHeader();
				$SaleReturn->insertReturnDetail();
				$SaleReturn->insertReturnPayment();
				$url = $SaleReturn->finishReturn();
				//}
			//verifica si la venta fue pagada
				/*$sql = "SELECT pagado FROM ec_pedidos WHERE id_pedido = {$ticket_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar si el pedido esta pagado : {$this->link->error}" );
				$stm_row = $stm->fetch_row();
				if( $stm_row[0] == 0  ){
					$url = '';
				}*/
			}
		//actualiza el status de la cabecera de pedidos
			$sql = "UPDATE ec_pedidos SET venta_validada = '1' WHERE id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar la venta a validada : {$this->link->error}" );
		//consulta ids de pedidos detalle
			$sql = "SELECT 
						GROUP_CONCAT( id_pedido_detalle SEPARATOR ',' ) AS details_ids
					FROM ec_pedidos_detalle 
					WHERE id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar ids de pedido detalle : {$this->link->error}" );
			$sale_details_ids = $stm->fetch_assoc();
		//actualiza las validaciones de pedidos 
			$sql = "UPDATE ec_pedidos_validacion_usuarios SET validacion_finalizada = '1' WHERE id_pedido_detalle IN( {$sale_details_ids['details_ids']} )";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar al finalizar validaciones : {$this->link->error}" );
			
		//consulta detalles de validacion 
			$sql = "SELECT 
						id_pedido_validacion AS validation_id,
						id_pedido_detalle AS sale_detail_id,
						piezas_validadas AS validated_quantity							
					FROM ec_pedidos_validacion_usuarios 
					WHERE id_pedido_detalle IN( {$sale_details_ids['details_ids']} )
					AND id_proveedor_producto IS NOT NULL";
			$stm = $this->link->query( $sql ) or die( "Error al consultar ids de pedido validacion : {$this->link->error}" );
		
			//$validations_details_ids = $stm->fetch_assoc();
			while ( $sale_validations = $stm->fetch_assoc() ) {
				$ax = $this->insertMovementProviderProduct( $ticket_id, $store_id, $sale_validations['validation_id'], 1, 
				$sale_validations['sale_detail_id'], $sale_validations['validated_quantity'] );
				if( $ax != 'ok' ){
					die( "Erorr : {$ax}" );
				}
			}
		//consulta ids de validacion
/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
			$sql = "UPDATE ec_pedidos_validacion_usuarios 
					SET sincronizar = '1'
					WHERE id_pedido_detalle 
					IN( SELECT 
							id_pedido_detalle 
						FROM ec_pedidos_detalle 
						WHERE id_pedido = {$ticket_id}
					)";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar los registros de validacion para sincronizar : {$this->link->error}" );
*/

/*DESHABILITADO POR OSCAR 2023 PARA HACER MOVS PP HASTA DAR CLICK EN FINALIZAR
		//actualiza el indicador para sincronizar inventario proveedor producto
			$sql = "UPDATE ec_movimiento_detalle_proveedor_producto mdpp
						SET mdpp.sincronizar = '1'
					WHERE mdpp.id_pedido_validacion
					IN ( SELECT 
						id_pedido_validacion 
					FROM ec_pedidos_validacion_usuarios 
					WHERE id_pedido_detalle 
					IN( SELECT 
							id_pedido_detalle 
						FROM ec_pedidos_detalle 
						WHERE id_pedido = {$ticket_id} )
					)";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar los registros movimientos a nivel proveedor producto para sincronizar: {$this->link->error}" );
*/
/**/
		//oscar 2023
			$makeExhibitionMovements = $this->makeExhibitionMovements( $ticket_id, $store_id, $user );
/**/
			$this->link->autocommit( true );
			$aditional_message = "";
			$no_visible = "";
			if( $url != '' ){
				$aditional_message = "<h6>Esta pantalla te redireccionara a la url : {$url}</h6>"; 
				$no_visible = "style=\"display : none;\"";
			}
			$response = array();
			$response['status'] = 'ok';
			$response['url'] = $url;
			$response['ticket_has_changed'] = $ticket_has_changed;
			$response['mensaje'] = "<div class=\"row\">
						<p align=\"center\" style=\"color : green;\">Nota de venta validada exitosamente.
						<br><b style=\"color : orange;\">Entrega los productos al cliente.</b></p>	
						{$aditional_message}
						<div class=\"col-2\"></div>
						<div class=\"col-8 text-center\">
						<!--
							<button class=\"btn btn-success\" id=\"btn_reload_final\" {$no_visible} onclick=\"location.reload();\">
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button-->
							<button class=\"btn btn-success\" id=\"btn_reload_final\" onclick=\"print_return_ticket( '{$url}' );\">
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						</div>
					</div>";//{$url}
			return json_encode( $response );
		}

		public function makeExhibitionMovements( $ticket_id, $store_id, $user_id ){
			$principal_warehouse;
			$exhibition_warehouse;
		//busca si tiene registros de exibicion relacionados
			/*$sql = "SELECT 
						te.id_temporal_exhibicion AS exhibition_temporal_id,
						tepp.id_producto AS product_id, 
						tepp.id_proveedor_producto AS product_provider_id,
						tepp.cantidad AS quantity
					FROM ec_temporal_exhibicion_proveedor_producto tepp 
					LEFT JOIN ec_temporal_exhibicion te 
					ON tepp.id_temporal_exhibicion = te.id_temporal_exhibicion
					WHERE te.id_pedido = {$ticket_id}";*/
			$sql = "SELECT 
						tepp.id_producto AS product_id, 
						tepp.cantidad AS quantity,
						tepp.id_proveedor_producto AS product_provider_id
					FROM ec_temporal_exhibicion_proveedor_producto tepp 
					LEFT JOIN ec_temporal_exhibicion te 
					ON tepp.id_temporal_exhibicion = te.id_temporal_exhibicion
					WHERE te.id_pedido = {$ticket_id}
					GROUP BY tepp.id_temporal_exhibicion_proveedor_producto";
			$prods_stm = $this->link->query( $sql ) or die( "Error al consultar registros de exhibicion temporal : {$this->link->error}" );
			if( $prods_stm->num_rows <= 0 ){
				return 'ok';
			}else{
				//$row_1 = $stm->fetch_assoc();
				//$row_2 = $row_1;
		//busca almacen origen
				$sql = "SELECT
							id_almacen AS warehouse_id
					FROM ec_almacen 
					WHERE id_sucursal = $store_id
					AND es_almacen = 1";
				$stm = $this->link->query( $sql ) or die( "Error al consultar almacen principal : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$principal_warehouse = $row['warehouse_id'];
			//busca almacen exhibicion
				$sql = "SELECT
							id_almacen As warehouse_id
					FROM ec_almacen 
					WHERE id_sucursal = $store_id
					AND nombre like '%EXHIBICION%'";
				$stm = $this->link->query( $sql ) or die( "Error al consultar almacen exhibicion : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$exhibition_warehouse = $row['warehouse_id'];
				while ( $prods_row = $prods_stm->fetch_assoc() ){
					for( $i = 0; $i <= 1; $i++ ){
						$movement_type = 5;//entrada
						$warehouse_id = $principal_warehouse;
						if( $i == 0 ){
							$movement_type = 6;//salida
							$warehouse_id = $exhibition_warehouse;
						}
						if( $i == 0 ){
						//consulta si el producto es maquilado
							$sql = "SELECT
										p.id_productos AS product_id,
										IF( pd.cantidad IS NULL, 0, pd.cantidad) AS quantity,
										IF( pd.id_producto_ordigen IS NULL, -1, pd.id_producto_ordigen ) AS origin_product_id,
										IF( pd.id_producto_ordigen IS NULL, 
											{$prods_row['product_provider_id']},  
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
									WHERE p.id_productos = {$prods_row['product_id']}";
							$maquile_stm = $this->link->query( $sql ) or die( "Error al consulttar si el producto es maquilado : {$sql} {$this->link->error}" );
							$maquile_row = $maquile_stm->fetch_assoc();
							if( $maquile_row['origin_product_id'] != -1 ){
								$prods_row['product_id'] = $maquile_row['origin_product_id'];
								$prods_row['quantity'] = ( $prods_row['quantity'] * $maquile_row['quantity'] );
								$prods_row['product_provider_id'] = $maquile_row['product_provider_id'];
							}
						}
					//insertamos cabecera
						//$sql="INSERT INTO ec_movimiento_almacen ( /*1*/id_tipo_movimiento, /*2*/id_usuario, 
						//	/*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/observaciones, /*7*/id_almacen, /*8*/id_pedido ) 
						//VALUES ( /*1*/{$movement_type},/*2*/{$user_id},/*3*/{$store_id},/*4*/now(),/*5*/now(), 
						//	/*6*/'{$obs}', /*7*/{$warehouse_id}, /*8*/-1 )";
						
						$sql = "CALL spMovimientoAlmacen_inserta ( {$user_id}, '{$obs}', {$store_id}, {$warehouse_id}, {$movement_type}, -1, -1, -1, -1, 12 )";
						$stm = $this->link->query( $sql ) or die( "Error al insertar la cabecera del movimiento almacen : {$this->link->error} {$sql}" );			
						/*$sql = "SELECT LAST_INSERT_ID()";
						$stm_2 = $this->link->query( $sql ) or die( "Error al consultar id de cabecera de movimiento almacen : {$this->link->error} {$sql}" );
						$header_id = $stm_2->fetch_row();*/
						$ma_stm = $this->link->query( "SELECT max( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen" ) or die( "Error al recuperar id ma insertado : " . mysql_error() );
						$id_mov = $ma_stm->fetch_assoc();
						$header_id = $id_mov['id_movimiento_almacen'];
					//inserta detalles	
						/*$sql = "INSERT INTO ec_movimiento_detalle( id_movimiento, id_producto, cantidad, cantidad_surtida, 
						id_proveedor_producto, id_pedido_detalle ) VALUES ( {$header_id}, {$prods_row['product_id']}, {$prods_row['quantity']},
						{$prods_row['quantity']}, {$prods_row['product_provider_id']}, -1 )";/*8*/
						$sql = "CALL spMovimientoAlmacenDetalle_inserta( {$header_id}, {$prods_row['product_id']}, {$prods_row['quantity']}, {$prods_row['quantity']}, 
						-1, -1, {$prods_row['product_provider_id']}, 12 );";
						$stm_2 = $this->link->query( $sql ) or die( "Error al insertar detalles de movimiento almacen por exhibicion : {$this->link->error} {$sql}" );
					}
				}
			}
			return 'ok';
		}

/*implementacion Oscar 2023 para validar que se este validando en el mismo sistema*/
		public function change_sale_system_type( $ticket_id, $new_sale_system_type ){
			$this->link->autocommit( false );
		//actualiza el tipo de sistema de la venta
			$sql = "UPDATE ec_pedidos 
						SET tipo_sistema = '{$this->system_type}', modificado = 1 
					WHERE id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el tipo de sistema del pedido : {$this->link->error}" );
		//consulta los folios unicos del pedido detalle
			$sql = "SELECT 
						GROUP_CONCAT( CONCAT( \"\\\'\",pd.folio_unico,\"\\\'\" ) SEPARATOR ', ' ) AS uniques_folios,
						p.id_sucursal AS store_id
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos p
					ON pd.id_pedido = p.id_pedido
					WHERE pd.id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar folios unicos : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$old_sale_system_type = ( $this->system_type == 'linea' ? 'local' : 'linea' );
			//var_dump( $row );
			//die( $sql );
		//envia eliminar registros que ya habian sido validados
			$sql = "INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
					SELECT 
						NULL,
						IF( '{$this->system_type}' = 'linea', -1, {$row['store_id']} ),
						id_sucursal,
						CONCAT('{',
							'\"action_type\" : \"sql_instruction\",',
							'\"sql\" : \"DELETE FROM ec_pedidos_validacion_usuarios WHERE id_pedido_detalle IN ( SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE folio_unico IN( {$row['uniques_folios']} ) ) AND tipo_sistema = \'{$old_sale_system_type}\'\"',
							'}'
						),
						NOW(),
						'EliminarRegistrosValidacionDesdeCodigoValidacion',
						1
					FROM sys_sucursales 
					WHERE id_sucursal = IF( '{$this->system_type}' = 'linea', {$row['store_id']}, -1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros para eliminar en origen validacion : {$this->link->error} {$sql}" );
			$this->link->autocommit( true );
			return 'ok';
		}

		public function validateSystemType( $ticket_id, $sale_system_type ){
			$resp = "ok";
			if( $sale_system_type != $this->system_type ){
			//consulta 
				$sql = "SELECT 
							COUNT( pvu.id_pedido_validacion ) AS validation_counter,
							pvu.tipo_sistema AS validation_system_type
						FROM ec_pedidos_validacion_usuarios pvu
						LEFT JOIN ec_pedidos_detalle pd
						ON pvu.id_pedido_detalle = pd.id_pedido_detalle
						WHERE pd.id_pedido_detalle = {$ticket_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar si ya hay validaciones previas : {$this->link->error} {$sql}" );
				$row = $stm->fetch_assoc();
				return "<div class=\"row\">
					<h5>Esta venta fue hecha en sistema <b>{$sale_system_type}.</b></h5>
					<p class=\"text-center\">Si deseas validarla en <b>{$this->system_type} escribe la palabra '{$this->system_type}'</b></p>
					<h5 style=\"color : green;\">IMPORTANTE : DEBERAS DE VOLVER A DESEMPACAR TODO Y VOLVER A VALIDAR TODOS LOS PRODUCTOS DE ESTA VENTA!</h5>
					<div class=\"col-4\"></div>
					<div class=\"col-4\">
						<input type=\"text\" id=\"validation_tmp_word\">
						<br><br>
						<input type=\"password\" id=\"mannager_password\">
						<br><br>
						<button
							class=\"btn btn-success\"
							onclick=\"change_sale_system_type( {$ticket_id}, '{$sale_system_type}' )\"
						>
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div>
				</div>";
			}
		//consulta si ya se habia empezado en local
			return 'ok';
		}
/**/

		public function getTicketInfo( $ticket_id ){
			//echo 'here';
			$resp = "";
			$sql = "SELECT
						p.folio_nv AS folio,
						CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS employee_name,
						p.total AS amount,
						p.venta_validada AS was_validated,
						p.tipo_sistema AS sale_system_type,
						p.pagado AS was_payed
					FROM ec_pedidos p
					LEFT JOIN sys_users u
					ON p.id_usuario = u.id_usuario
					WHERE p.id_pedido = {$ticket_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la cabecera de la venta : {$this->link->error}" );
			
			if( $stm->num_rows <= 0 ){
				$resp .= "<h5>El ticket escaneado no fue encontrado, verifica y vuelve a intentar</h5>
					<br>
					<button 
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"close_emergent();\"
					>
						<i class=\"icon-ok-circle\">Aceptar</i>
					</button>"; 
			}else{
				$row = $stm->fetch_assoc();
/*implementacion Oscar 2023 para validar que se este validando en el mismo sistema*/
				$system_validation = $this->validateSystemType( $ticket_id, $row['sale_system_type'] );
				if( $system_validation != 'ok' ){
					die( $system_validation );
				}
/*fin de cambio Oscar 2023*/
				$row['employee_name'] = strtoupper( $row['employee_name'] );
				$resp .= "<div class=\"col-6\">
							<h5>Vendedor : <br><b>{$row['employee_name']}</b></h5>
						</div>
						<div class=\"col-6\">
							<h5>Monto : <br><b>$ {$row['amount']}<b></h5>
						</div>";
				if( $row['was_validated'] == 1 ){
					$resp .= "<h5 style=\"color : red;\">Esta nota de venta ya fue validada, verifica y vuelve a intentar.</h5>";
					$resp .= "<br><button 
						type=\"button\"
						class=\"btn btn-danger\"
						onclick=\"close_emergent();\"
					>
						<i class=\"icon-cancel-circled\">Cerrar</i>
					</button>";
				}else{
					$resp .= "<br><button 
						type=\"button\"
						class=\"btn btn-success\"
						onclick=\"setTicket( {$ticket_id}, {$row['was_payed']} );close_emergent();\"
					>
						<i class=\"icon-right-big\">Validar</i>
					</button>";
				}
				/*$resp = "<div class=\"row\">

						</div>";*/
			}

			return "<div class=\"row text-center\">
						{$resp}
					</div>";
		}

		public function saveNewProductProviderValidation( $product_id, $product_provider_id, $ticket_id, $store_id, 
			$user, $sale_detail_id ){
			$sql = "INSERT INTO ec_pedidos_validacion_usuarios ( id_pedido_validacion, id_pedido_detalle, id_producto, 
			id_proveedor_producto, piezas_validadas, piezas_devueltas, id_usuario, id_sucursal, fecha_alta, 
			validacion_finalizada, tipo_sistema )
			SELECT 
				NULL,
				pd.id_pedido_detalle,
				{$product_id},
				{$product_provider_id},
				0,
				0,
				{$user},
				{$store_id},
				NOW(),
				0,
				'{$this->system_type}'
			FROM ec_pedidos_detalle pd
			WHERE pd.id_pedido_detalle = {$sale_detail_id}";/*pd.id_pedido = {$ticket_id}
			AND pd.id_producto = {$product_id}*/
			$stm = $this->link->query( $sql ) or die( "Error al insertar el registro de proveedor producto : {$sql} {$this->link->error}" );
			return 'ok'; 
		}
/*implementacion Oscar 2023 para redireccionar siu hay devolucion*/
		public function get_url_from_return( $sale_id ){
			$sql = "SELECT 
						ROUND( 1 - (total/subtotal), 6 ) AS discount
					FROM ec_pedidos
					WHERE id_pedido = {$this->sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar descuento del ticket : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$porcDesc = $row['discount'];
		//Actualizamos el monto del pedio anterior y generamos el ticket...
		    $subTotal="SELECT 
		    			SUM( monto ),
		    			SUM( descuento ) 
		    		FROM ec_pedidos_detalle 
		    		WHERE id_pedido = '{$sale_id}'";//consultamos las sumas de los productos del pedido
		    $calc = $this->link->query($subTotal) or die("Error al calcular el nuevo monto del pedido : {$this->link->error}");
		    $subTotal = $calc->fetch_row();    
		//checamos si hay descuento
		    if($subTotal[1]==0){
		        $descFinal=$subTotal[0]*$porcDesc;
		        $auxz=$subTotal[0];
		        $subTotal[0]=$auxz;
		    }else{
		        $descFinal=$subTotal[1];
		    }
//url+='&extra=*es_apart='+es_apartado+'*id_ped='+$("#id_pedido_apartado").val();
		//recupera datos de la devolución
			$sql = "SELECT 
						p.id_pedido AS sale_id,
						SUM( devP.monto ) AS amount,
						IF( p.pagado = 1, 0, 1 ) AS is_not_payed,
					/*implementacion Oscar 2023 para que respete el precio de lista si es mayoreo*/
						IF( p.tipo_pedido = 0 , '', CONCAT( '&tv=1&aWRfcHJlY2lv=', p.tipo_pedido ) ) AS sale_type,
						p.descuento AS discount/*,
						p.id_devoluciones AS returns_ids*/
					FROM ec_pedidos p
					LEFT JOIN ec_devolucion dev
					ON dev.id_pedido = p.id_pedido
					LEFT JOIN ec_devolucion_pagos devP
					ON devP.id_devolucion = dev.id_devolucion
					WHERE p.id_pedido = {$this->sale_id}";
			$stm_url = $this->link->query( $sql ) or die( "Error al consultar detalles finales de la devolución : {$this->link->error}" );
			$row = $stm_url->fetch_assoc();
		//oscar 2023 para enviar datos de la devolucion
			$row['returns_ids'] = "{$this->internal_return_id}~{$this->external_return_id}";
	
			$extra = "&es_apart={$row['is_not_payed']}&id_ped={$row['sale_id']}&dsc={$row['discount']}"; 
			$extra .= "&id_dev=" . $row['returns_ids'] . $row['sale_type'];//implementacion Oscar 2023 para que respete el precio de lista si es mayoreo

			$extra=str_replace("*", "&", $extra);
    		$url_recarga = '../../touch_desarrollo/index.php?scr=nueva-venta&s_f_c=' . $row['amount'];
    		$url_recarga .= $extra . "&abonado=".$this->total_abonado;

    		$url_db = '../touch_desarrollo/index.php?scr=nueva-venta&s_f_c=' . $row['amount'];
    		$url_db .= $extra . "&abonado=".$this->total_abonado;

		    $sql="UPDATE ec_devolucion SET observaciones='$url_db' WHERE id_pedido = {$this->sale_id}";
		    $eje = $this->link->query($sql)or die("Error al actualizar observaciones en las devoluciones : {$this->link->error}" );

		//actualizamos monto del pedido y marcamos que este fue modificado
		    $actPed="UPDATE ec_pedidos SET descuento = '{$descFinal}',subtotal='$subTotal[0]',total=($subTotal[0]-descuento),modificado=1 WHERE id_pedido = '{$this->sale_id}'";
		    $actualiza = $this->link->query( $actPed ) or die( "Error al actualizar cabecera de Pedido : {$this->link->error}" );
		    
		    //if(mysql_query("COMMIT")){//autorizamos transacción
		       // if($es_completa==1){
		        //imprimimos el ticket de la devolución
	            if( !include('imprimeDev.php') ){
	    		  die("Error al generar ticket de devolución");
	    	   	}
		//regresa el id de la devolución 
		    //return 'ok|'.$id_dev."|".$total_abonado."|".$url_recarga."&id_dev=".$id_dev_interna."~".$id_dev_externa;
			return $url_recarga;
		}

		public function reset_validation( $sale_id ){
		//consulta los ids de los detalles
			$sql = "SELECT 
						GROUP_CONCAT( id_pedido_detalle SEPARATOR ',' ) AS sales_ids
					FROM ec_pedidos_detalle
					WHERE id_pedido = {$sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los ids de detalles de venta : {$this->link->error}" );
			$row = $stm->fetch_assoc();

			$this->link->autocommit( false );
		//elimina las validaciones de la nota de venta
			$sql = "DELETE FROM ec_pedidos_validacion_usuarios WHERE id_pedido_detalle IN ( {$row['sales_ids']} )";
			$this->link->query( $sql ) or die( "Error al eliminar validaciones de venta : {$this->link->error}" );
			$this->link->autocommit( true );
			die( 'ok' );
		}
	}
?>