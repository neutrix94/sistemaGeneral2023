<?php
	if( isset( $_GET['inventory_fl'] ) ){
		include('../../../../../conect.php');//incluimos libreria de conexion
		include('../../../../../conexionMysqli.php');//incluimos libreria de conexion
		$inventory = new inventoryAdjustment( $link, $sucursal_id );
		$action = $_GET['inventory_fl'];
		switch ( $action ) {
			case 'getOmmitedRows':
			//die("almacen : {$_GET['warehouse_id']}");
				echo $inventory->getOmmitedRows_( $_GET['warehouse_id'] );
				//echo $inventory->getStoreWharehouses();
			break;

			case 'reset_all_products' :
			//die( "pass  :  " . $_GET['mannager_password'] );
				$password_validation = $inventory->validateMannagerPassword( $_GET['mannager_password'] );


				if( $password_validation != 'ok' ){
					die( $password_validation );
				}
				echo $inventory->reset_all_products( $_GET['warehouse_id'] );
			break;
			
			default:
				# code...
			break;
		}

	}
	class inventoryAdjustment
	{
		private $link;
		private $store_id;
		function __construct( $connection, $store_id )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
		}
		
		public function getStoreWharehouses( $warehouse_id = null ){
			$sql = "SELECT
						id_almacen AS id,
						nombre AS name
					FROM ec_almacen
					WHERE IF( '{$this->store_id}' = '-1',
							id_sucursal > 0,
							id_sucursal = {$this->store_id} )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar almacenes de sucursal : {$this->link->error}" );
			$disabled = ( $warehouse_id != null ? 'disabled'  : '' );
			$resp = "<select id=\"warehouse_id\" class=\"form-control\" {$disabled}>";
				$resp .= "<option value=\"0\">-- Seleccionar Almacén --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$selected = ( $warehouse_id == $row['id'] ? 'selected' : '' );
				$resp .= "<option value=\"{$row['id']}\" {$selected}>{$row['name']}</option>";
			}
			$resp .= "</select>"; 
			return $resp;
		}

		public function getOmmitedRows_( $warehouse_id ){
			//die('here 1');
			$resp = "";
			$sql = "SELECT
									/*0*/ax.id_productos,
									/*1*/ax.nombre,
									/*2*/ax.inventario,
									/*3*/ax.orden_lista,
									/*4*/IF({$this->store_id}=1,
										CONCAT( ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde ,
											' - ', ppua.letra_ubicacion_hasta, ppua.numero_ubicacion_hasta  ),
										ax.ubicacion_almacen_sucursal) AS location,
									/*5*/ax.id_proveedor_producto,
									/*6*/ax.is_maquiled,
									/*7*/ax.total_en_piezas,
									/*8*/ax.id_conteo_inventario_tmp,
									/*9*/ax.pospuesto,
									/*10*/ax.ya_fue_contado
								FROM(
									SELECT 
										/*0*/p.id_productos,
										/*1*/CONCAT(p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' - ', pp.clave_proveedor, ' ( ', pp.presentacion_caja, ' pzas por caja )', ' <b>id_p_p : ', pp.id_proveedor_producto, '</b>' ) ) ) AS nombre,
										/*2*/FORMAT( SUM( IF( mdp.id_movimiento_almacen_detalle IS NULL, 0, ( mdp.cantidad * tm.afecta) ) ), 2) AS inventario,
										/*3*/p.orden_lista,
										/*4*/sp.ubicacion_almacen_sucursal,
										/*5*/cit.id_proveedor_producto,
										/*6*/(SELECT IF( id_producto IS NULL, 0, id_producto ) FROM ec_productos_detalle WHERE id_producto_ordigen = p.id_productos LIMIT 1) as is_maquiled,
										/*7*/cit.total_en_piezas,
										/*8*/cit.id_conteo_inventario_tmp,
										/*9*/cit.pospuesto,
										/*10*/cit.ya_fue_contado
									FROM ec_productos p 
									LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
									AND sp.id_sucursal IN({$this->store_id}) AND sp.estado_suc=1
									LEFT JOIN ec_proveedor_producto pp ON pp.id_producto = p.id_productos
									LEFT JOIN ec_movimiento_detalle_proveedor_producto mdp 
									ON mdp.id_proveedor_producto = pp.id_proveedor_producto
									AND mdp.id_almacen = '{$warehouse_id}'
									LEFT JOIN ec_tipos_movimiento tm ON mdp.id_tipo_movimiento = tm.id_tipo_movimiento
									LEFT JOIN ec_conteo_inventario_tmp cit
									ON cit.id_proveedor_producto = pp.id_proveedor_producto 
									WHERE cit.pospuesto = '1'
									OR cit.ya_fue_contado = '0'
									GROUP BY cit.id_proveedor_producto
								) ax
								LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
								ON ppua.id_proveedor_producto = ax.id_proveedor_producto
								GROUP BY ax.id_proveedor_producto";
//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al consultar los proveedores productos oimitidos durante el conteo : {$this->link->error}" );
			$resp = "<div class=\"row\" style=\"position : relative; max-height : 300px !important; overflow : auto;\">
						<table class=\"table table-striped table-bordered\">
							<thead style=\"position : sticky; top : -10px; background-color : white; z-index : 3;\">
								<tr>
									<th>Ord List</th>
									<th>
										Producto
										<i class=\"icon-bookmark\" style=\"color : orange;\">Pospuesto</i>
										<i class=\"icon-bookmark\" style=\"color gray: ;\">Sin contar</i>
									</th>
									<th>Ubicacion</th>
								</tr>
							</thead>
							<tbody id=\"pending_rows_list\">";
			while ( $row = $stm->fetch_assoc() ) {
				$style = "style=\"color : orange;\"";
				if( $row['pospuesto'] == '0' ){
					$style = "style=\"color : gray;\"";
				}
				$resp .= "<tr>
							<td {$style}>{$row['orden_lista']}</td>
							<td {$style}>{$row['nombre']}</td>
							<td {$style}>{$row['location']}</td>
						</tr>";
			}
			$resp .= "</table>
					</div>
					<br>
					<h4 class=\"text-center\">Ingresa la contraseña del encargado para continuar : </h4>
					<div class=\"row\">
						<div class=\"col-3\"></div>
						<div class=\"col-6\">
							<input type=\"password\" id=\"mannager_password\" class=\"form-control\">
							<br>
							<button
								class=\"btn btn-success form-control\"
								onclick=\"reset_all_products();\"
							>
								<i class=\"icon-ok-circle\">Aceptar</i>
							</button>
						<br>
						<br>
						<button
							class=\"btn btn-danger form-control\"
							onclick=\"close_emergent();\"
						>
							<i class=\"icon-cancel-circled\">Cancelar y salir</i>
						</button>
						</div>
					</div>";
			return $resp;

		}

		public function validateMannagerPassword( $password ){
			$sql="SELECT s.id_encargado 
			FROM sys_sucursales s
			LEFT JOIN sys_users u on s.id_encargado=u.id_usuario
			WHERE s.id_sucursal={$this->store_id} and u.contrasena=md5('{$password}')";
			$stm = $this->link->query($sql)or die("Error al verificar el password del encargado : {$this->link->error}");
			if( $stm->num_rows == 1 ){
				return 'ok';
			}else{
				return "La contraseña es incorrecta!";
			}
		}

		public function reset_all_products( $warehouse_id ){
			$sql = "UPDATE ec_conteo_inventario_tmp 
						SET cajas = 0,
						paquetes = 0,
						piezas = 0,
						total_en_piezas = 0,
						ya_fue_contado = '0',
						pospuesto = '0',
						ya_realizo_movimientos = 0
					WHERE id_almacen = {$warehouse_id}";
					//die($sql);
			$stm = $this->link->query( $sql ) or die( "Error al resetear los proveedores productos : {$this->link->error}" );
			return "ok|<h4 class=\"text-center\">Los proveedores producto fueron reseteados exitosamente</h4>
					<br>
					<button type=\"button\" class=\"btn btn-success\" onclick=\"location.reload();\">
						<i class=\"icon-ok-circled\">Aceptar</i>
					</button>";
		}
	
	}

?>