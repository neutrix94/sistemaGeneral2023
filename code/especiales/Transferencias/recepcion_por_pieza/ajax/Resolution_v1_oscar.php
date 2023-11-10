<?php
	if( isset( $_POST['fl_r'] ) || isset( $_GET['fl_r'] ) ){
//echo '1_1';
		$flag = ( isset( $_GET['fl_r'] ) ? $_GET['fl_r'] : $_POST['fl_r'] );
		include( '../../../../../config.ini.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		switch ( $flag ) {
			case 'saveResolutionRow':
//echo '1_1';
				$Resolution = new Resolution( $link, $user_id, $sucursal_id );
//echo '1_2';
				echo $Resolution->saveResolutionRow( $_GET['product_id'], $_GET['product_provider_id'], $_GET['transfer_product_id'], $_GET['quantity'], 
					$_GET['type'], $user_id );
			break;	
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

		public function getProductProviderInventory( $product_provider_id ){
			$sql = "SELECT 
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
					)ax";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el inventario de movimientos de almacén : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			//echo "inventario : {$sql} {$row['current_inventory']}";
			return $row['current_inventory'];
		}

		public function getResolutionDetails( $transfer_product_id ){
			$sql = "SELECT 
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						tp.total_piezas_recibidas AS received_pieces,
						p.nombre AS product_name
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON tp.id_producto_or = p.id_productos
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar información de recepción mercancía : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			return $row;
		}
/**/
		public function getReceptionProductDetail( $transfers, $product_id, $product_provider_id ){
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
					tru.codigo_validacion AS validation_barcode,
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
		return buildReceptionProductDetail( $stm );
	}
/*detalle de escaneo*/
	public function buildReceptionProductDetail( $stm ){
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
			$aux = explode($row['validation_barcode'], ' ');
			if( sizeof( $aux ) == 4 ){
				$color = "green";
			}
		//si fue por nombre quita el código de barras
			$row['validation_barcode'] = ( $row['validation_barcode'] == 'Por nombre' ? '' : $row['validation_barcode'] );

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
		$resp .= '<div class="row">';
			$resp .= '<div class="col-2"></div>';
			$resp .= '<div class="col-8">';
				$resp .= '<button class="btn btn-success form-control" onclick="close_emergent();lock_and_unlock_focus( \'#barcode_seeker_lock_btn\', \'#barcode_seeker\');">';
					$resp .= 'Aceptar';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		return $resp;
	}
/**/

		public function getFormMissing( $difference, $transfer_product_id, $transfers ){
			//var_dump($transfers);
			$transfer_detail = $this->getResolutionDetails( $transfer_product_id );
			$inventory = $this->getProductProviderInventory( $transfer_detail['product_provider_id'] );
			$possible_inventory = $inventory + $transfer_detail['received_pieces'];
			$resp = "<div class=\"row group_card\" style=\"font-size : 70%;\">
						<h5 class=\"text-center\">{$transfer_detail['product_name']}</h5>
						<div class=\"col-1\"></div>
						<div class=\"col-2\">
							<label>Conteo <br>Físico</label>
							<input type=\"number\" id=\"resolution_field_count\" class=\"form-control text-end\" 
								onkeyup=\"change_missing_resolution( 1, 'missing', {$transfer_product_id}, {$transfer_detail['product_id']}, {$transfer_detail['product_provider_id']} );\">
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
							      	<button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#panelsStayOpen-resolutionCollapseOne\" 
							      	aria-expanded=\"\" aria-controls=\"panelsStayOpen-collapseOne\">
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
									onclick=\"save_resolution( 1, 'missing', 0, {$transfer_product_id}, {$transfer_detail['product_id']}, {$transfer_detail['product_provider_id']}  );\"
								>
									<i class=\"icon-ok-circle\"><br>Recibir Completo <b>{$difference}</b></i>
								</button>
							</div>

							<div class=\"col-4\">
								<br>
								<button 
									class=\"btn btn-warning form-control\"
									onclick=\"\"
									style=\"font-size : 100%;\"
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
								</button>
							</div>
						</div>
					</div>";
			return $resp;
		}

		public function saveResolutionRow( $product_id, $product_provider_id, $transfer_product_id, $quantity, $type, $user ) {
			$this->link->autocommit( false );
			$quantity_excedent = ( $type == 'excedent' ? $quantity : 0 );
			$quantity_return = ( $type == 'missing' ? $quantity : 0 );
			//echo 'here';
			if( $quantity == 0 ){
				$sql = "SELECT
							( total_piezas_surtimiento - total_piezas_recibidas ) AS pending_to_receive
						FROM ec_transferencia_productos tp
						WHERE tp.id_transferencia_producto = {$transfer_product_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar el restante por recibir ( Resolución ) : {$this->link->error}" );
				$row = $stm->fetch_assoc();
				$quantity = $row['pending_to_receive'];
			}
		//inserta el resgistro de escaneos de recepción
			$sql = "INSERT INTO ec_transferencias_recepcion_usuarios ( id_transferencia_recepcion, id_transferencia_producto,
						id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_recibidas, cantidad_paquetes_recibidos, cantidad_piezas_recibidas, 
						fecha_recepcion, id_status, validado_por_nombre, codigo_validacion )
						VALUES( NULL, '{$transfer_product_id}', '{$user}', '{$product_id}', '{$product_provider_id}', 
							'0', '0', '{$quantity}', NOW(), 1, '0', 'resolucion' )";
			$stm = $this->link->query( $sql ) or die( "error|Error al insertar el registro de recepción ( Resolución ) : " . $link->error );

		//actualiza la recepcion del producto en la transferencia
			$sql = "UPDATE ec_transferencia_productos tp 
					LEFT JOIN ec_proveedor_producto pp 
					ON tp.id_proveedor_producto = pp.id_proveedor_producto
				SET tp.cantidad_piezas_recibidas =  ( tp.cantidad_piezas_recibidas + {$quantity} ),
					tp.total_piezas_recibidas = ( tp.total_piezas_recibidas + {$quantity} )
				WHERE tp.id_transferencia_producto = '{$transfer_product_id}'";
			$stm = $this->link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia ( Resolución ) : {$this->link->error}" );
		
		//vuelve a consultar cuanto le falta a la transferencia por validar
			$sql = "SELECT
						( total_piezas_surtimiento - total_piezas_recibidas ) AS pending_to_receive
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia_producto = {$transfer_product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el restante por recibir ( Resolución ) : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$quantity = $row['pending_to_receive'];
			if( $quantity > 0 ){
			//inserta el registro de resolución de transferencias
				$sql = "INSERT INTO ec_transferencias_resolucion ( /*1*/id_transferencia_resolucion, /*2*/id_transferencia, /*3*/id_transferencia_producto, 
					/*4*/piezas_mantiene, /*5*/piezas_devuelve, /*6*/id_usuario )
					SELECT 
						/*1*/NULL,
						/*2*/id_transferencia,
						/*3*/id_transferencia_producto,
						/*4*/{$quantity_excedent},
						/*5*/{$quantity_return},
						/*6*/{$user}
					FROM ec_transferencia_productos 
					WHERE id_transferencia_producto IN( {$transfer_product_id} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el registro de resolución de transferencias : {$this->link->error}" );
			}
			$this->link->autocommit( true );
			return '<h5>El registro fue insertado exitosamenete!</h5><button onclick="close_emergent();" class="btn btn-success form-control">' 
			. '<i class="icon-ok-circle">Aceptar</i></button>';
	}


		public function getFormExcedent(){
			$resp = "<div class=\"row group_card\">
						<div class=\"col-3\">
							<label>Conteo Físico</label>
							<input type=\"number\" class=\"form-control\">
						</div>
						<div class=\"col-3\">
							<label>Inventario + Recibido</label>
							<input type=\"number\" class=\"form-control\">
						</div>
						<div class=\"col-3\">
							<label>Faltante Recibir</label>
							<input type=\"number\" class=\"form-control\">
						</div>
						<div class=\"col-3\">
							<label>Diferencia</label>
							<input type=\"number\" class=\"form-control\">
						</div>
						<div>
						</div>
					</div>";
			return $resp;
		}



	}
?>

<script type="text/javascript" src="js/resolution.js">
</script>