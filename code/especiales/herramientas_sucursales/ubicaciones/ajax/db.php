<?php
	
	if( isset( $_GET['location_fl'] ) || isset( $_POST['location_fl'] ) ){
	//	die( 'here' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$action = ( isset( $_GET['location_fl'] ) ? $_GET['location_fl'] : $_POST['location_fl'] );
		$Locations = new Locations( $link );
		switch ( $action ) {
			case 'seekProductsLocations':
				$txt = ( isset( $_GET['key'] ) ? $_GET['key'] : $_POST['key'] );
				$is_scanner = ( isset( $_GET['is_scanner'] ) ? $_GET['is_scanner'] : $_POST['is_scanner'] );
				echo $Locations->seekProductsLocations( $txt, $is_scanner );
			break;

			case 'saveLocation':
				$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
				$location_number_from = ( isset( $_GET['location_number_from'] ) ? $_GET['location_number_from'] : $_POST['location_number_from'] );
				$aisle_from = ( isset( $_GET['aisle_from'] ) ? $_GET['aisle_from'] : $_POST['aisle_from'] );
				$level_from = ( isset( $_GET['level_from'] ) ? $_GET['level_from'] : $_POST['level_from'] );
				$warehouse_id = ( isset( $_GET['warehouse_id'] ) ? $_GET['warehouse_id'] : $_POST['warehouse_id'] );
				$is_enabled = ( isset( $_GET['is_enabled'] ) ? $_GET['is_enabled'] : $_POST['is_enabled'] );
				$is_principal = ( isset( $_GET['is_principal'] ) ? $_GET['is_principal'] : $_POST['is_principal'] );
				$store_location_id = ( isset( $_GET['store_location_id'] ) ? $_GET['store_location_id'] : $_POST['store_location_id'] );

				echo $Locations->saveLocation( $product_id, $location_number_from, $aisle_from, $level_from, 
					$is_enabled, $is_principal, $warehouse_id, $sucursal_id, $store_location_id );
			break;

			case 'getProductLocations': 
				$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
				$warehouse_id = ( isset( $_GET['warehouse_id'] ) ? $_GET['warehouse_id'] : $_POST['warehouse_id'] );
				echo $Locations->getProductLocations( $sucursal_id, $warehouse_id, $product_id );
			break;

			case 'getStoreProductLocation': 
				$store_location_id = ( isset( $_GET['store_location_id'] ) ? $_GET['store_location_id'] : $_POST['store_location_id'] );
				echo $Locations->getStoreProductLocation( $store_location_id );
			break;

			case 'deteleProductLocation' :
				$store_location_id = ( isset( $_GET['store_location_id'] ) ? $_GET['store_location_id'] : $_POST['store_location_id'] );
				echo $Locations->deteleProductLocation( $store_location_id );
			break;

			case 'validate_only_one' :
				$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
				$warehouse_id = ( isset( $_GET['warehouse_id'] ) ? $_GET['warehouse_id'] : $_POST['warehouse_id'] );
				$store_location_id = ( isset( $_GET['store_location_id'] ) ? $_GET['store_location_id'] : $_POST['store_location_id'] );
				echo $Locations->validate_only_one( $store_location_id, $product_id, $warehouse_id );
			break;

			case 'disabled_principal_location':
				$product_id = ( isset( $_GET['product_id'] ) ? $_GET['product_id'] : $_POST['product_id'] );
				$warehouse_id = ( isset( $_GET['warehouse_id'] ) ? $_GET['warehouse_id'] : $_POST['warehouse_id'] );
				echo $Locations->disabled_principal_location( $product_id , $warehouse_id );
			break;
			
			default:
				die( "Access denied on : '{$action}'" );
			break;
		}
	}

	class Locations
	{	
		private $link;
		
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function disabled_principal_location( $product_id , $warehouse_id ){
			$sql = "UPDATE ec_sucursal_producto_ubicacion_almacen
						SET es_principal = 0
					WHERE id_producto = {$product_id}
					AND id_almacen = {$warehouse_id}
					AND es_principal = 1";
					//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al deshabilitar ubicacion principal del producto : {$link->error}" );
			die( 'ok' );
		}

		public function validate_only_one( $store_location_id, $product_id, $warehouse_id ){
			$sql = "SELECT 
						id_ubicacion_sucursal
					FROM ec_sucursal_producto_ubicacion_almacen
					WHERE id_producto = {$product_id}
					AND id_almacen = {$warehouse_id}
					AND es_principal = 1";
			//		die( $sql );
			$sql .= ( $store_location_id != null && $store_location_id != '' ? " AND id_ubicacion_sucursal != {$store_location_id}" : "" );
			$stm = $this->link->query( $sql ) or die( "Error al verificar si hay ubicacion primaria existente para este producto : {$sql} {$this->link->error}" );
			if( $stm->num_rows >0 ){
				die( 'no' );
			}
			die( 'ok' );
		}

		public function seekProductsLocations( $txt, $is_scanner ){
			$resp = '';
			$sql = "SELECT 
						p.id_productos,
						CONCAT( p.nombre ) AS nombre,/*, '<br><span class_black>Clave Proveedor : <span class_tam>', pp.clave_proveedor , '</span> Caja con ' , pp.presentacion_caja , ' pzas</span>'*/
						ap.inventario,
						SUM( rd.piezas_sueltas_recibidas + ( rd.piezas_por_caja * rd.cajas_recibidas ) ),
						rd.id_status_ubicacion,
						p.ubicacion_almacen,
						pp.id_proveedor_producto,
						p.orden_lista,
						s.tiene_ubicacion_prinicipal
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p ON p.id_productos = pp.id_producto
					LEFT JOIN ec_almacen_producto ap ON ap.id_producto = p.id_productos
					AND ap.id_almacen = 1
					LEFT JOIN ec_recepcion_bodega_detalle rd ON p.id_productos = rd.id_producto
					LEFT JOIN ec_subcategoria s 
					ON s.id_subcategoria = p.id_subcategoria
					WHERE p.orden_lista LIKE '%{$txt}%'
					OR p.clave LIKE '%{$txt}%'
					OR ( ";
			$words = explode(' ', $txt);
			foreach ($words as $key => $word ) {
				$sql .= ( $key > 0 ? " AND " : "") . " p.nombre LIKE '%{$word}%'";
			}
			$sql .= " )
					OR pp.codigo_barras_pieza_1 = '{$txt}'
					OR pp.codigo_barras_pieza_2 = '{$txt}'
					OR pp.codigo_barras_pieza_3 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
					OR pp.codigo_barras_caja_1 = '{$txt}'
					OR pp.codigo_barras_caja_2 = '{$txt}'
					OR pp.clave_proveedor = '{$txt}'
					GROUP BY p.id_productos
					ORDER BY p.orden_lista";
			//return $sql;

			$exc = $this->link->query( $sql ) or die( "Error al consultar productos recibidos : " . $this->link->error );
			$counter = 0;
			while( $r = $exc->fetch_row() ){
				$r[1] = str_replace( 'class_tam', ' style=\\\'font-size : 150%;\\\'', $r[1] );
				$r[1] = str_replace( 'class_black', ' style=\\\'color : black;\\\'', $r[1] );
				$resp .= "<div class=\"group_card\" id=\"location_response_{$counter}\" 
							onclick=\"setProductLocation('{$r['0']}~<b style=\'color:black;\'>( {$r['7']} ) </b> {$r['1']}~{$r['2']}~{$r['3']}~{$r['4']}~{$r['5']}~{$r['6']}~{$r['8']}');\"
						>{$r[1]}</div>";
				$counter ++;
			}

			return $resp;
		}

		public function saveLocation( $product_id, $location_number_from, $aisle_from, $level_from, $is_enabled, $is_principal, 
			$warehouse_id, $sucursal_id, $store_location_id ){
			$this->link->autocommit( false );
			if( $store_location_id == '' || $store_location_id == null ){
				$sql = "INSERT INTO ec_sucursal_producto_ubicacion_almacen ( id_sucursal, id_almacen, id_producto, numero_ubicacion_desde, 
					numero_ubicacion_hasta, pasillo_desde, pasillo_hasta, altura_desde, altura_hasta, habilitado, es_principal ) 
					VALUES ( {$sucursal_id}, '{$warehouse_id}', '{$product_id}', '{$location_number_from}', '{$location_number_from}', 
						'{$aisle_from}', '{$aisle_from}', '{$level_from}', '{$level_from}', 1, {$is_principal} )";
				$stm = $this->link->query( $sql ) or die( "Error al insertar ubicacion : {$this->link->error}" );
				$store_location_id = $this->link->insert_id;
				$sinchronization = $this->insertStoreLocationSinchronization( $store_location_id, 'insert' );
			}else{
				$sql = "UPDATE ec_sucursal_producto_ubicacion_almacen 
						SET id_producto = '{$product_id}',
							numero_ubicacion_desde = '{$location_number_from}',
							numero_ubicacion_hasta = '{$location_number_from}',
							pasillo_desde = '{$aisle_from}',
							pasillo_hasta = '{$aisle_from}',
							altura_desde = '{$level_from}',
							altura_hasta = '{$level_from}',
							habilitado = '1', 
							es_principal = '{$is_principal}'
						WHERE id_ubicacion_sucursal = {$store_location_id}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar ubicacion : {$this->link->error}" );
				$sinchronization = $this->insertStoreLocationSinchronization( $store_location_id, 'update' );
			}
			$this->link->autocommit( true );
			return 'ok';
			
		}
		public function deteleProductLocation( $store_location_id ){
			$this->link->autocommit( false );
			$sinchronization = $this->insertStoreLocationSinchronization( $store_location_id, 'delete' );
			$sql = "DELETE FROM ec_sucursal_producto_ubicacion_almacen WHERE id_ubicacion_sucursal = {$store_location_id}";
			$stm = $this->link->query( $sql ) or die( "Error al eliminar ubicacion de producto : {$this->link->error}" );
			$this->link->autocommit( true );
			return 'ok';

		}
		public function getProductLocations( $store_id, $warehouse_id, $product_id ){
			$resp = array();
			$sql = "SELECT
						id_ubicacion_sucursal AS store_location_id,
						id_producto AS product_id, 
						numero_ubicacion_desde AS number_from,
						pasillo_desde AS aisle_from,
						altura_desde AS level_from
					FROM ec_sucursal_producto_ubicacion_almacen
					WHERE id_sucursal = {$store_id}
					AND id_almacen = {$warehouse_id}
					AND id_producto = {$product_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar ubicaciones del producto : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( 'empty' );
			}
			while ( $row = $stm->fetch_assoc() ) {
				$resp[] = $row;
			}
			return "ok|" . json_encode( $resp );
		}

		public function getStoreProductLocation( $store_location_id ){
			$resp = array();
			$sql = "SELECT
						id_ubicacion_sucursal AS store_location_id,
						id_producto AS product_id, 
						numero_ubicacion_desde AS number_from,
						pasillo_desde AS aisle_from,
						altura_desde AS level_from,
						habilitado AS is_enabled,
						es_principal AS is_principal
					FROM ec_sucursal_producto_ubicacion_almacen
					WHERE id_ubicacion_sucursal = {$store_location_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar detalle de ubicaciones del producto en sucursal : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				die( 'empty' );
			}
			$resp = $stm->fetch_assoc();
			return "ok|" . json_encode( $resp );
		}
//metodo para sincronizar
		public function insertStoreLocationSinchronization( $store_location_id, $type ){
		//consulta la sucursal del sistema
			$system_store_id;
			$sql = "SELECT id_sucursal AS system_store_id, prefijo AS store_prefix FROM sys_sucursales WHERE acceso=1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de sucursal sucursal para sincronizacion: {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$system_store_id = $row['system_store_id'];
			$store_prefix = $row['store_prefix'];
		//actualiza el folio unico
			$sql = "UPDATE ec_sucursal_producto_ubicacion_almacen 
						SET folio_unico = '{$store_prefix}_UBIC_{$store_location_id}' 
					WHERE id_ubicacion_sucursal = '{$store_location_id}'";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico de ubicacion de sucursal : {$this->link->error}" );

		//recupera el registro
			$sql = "SELECT * FROM ec_sucursal_producto_ubicacion_almacen WHERE id_ubicacion_sucursal = {$store_location_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar detalle de ubicaciones del producto en sucursal : {$this->link->error}" );
			$row = $row = $stm->fetch_assoc();
		//inserta los registros de sincronizacion
			if( $type == 'insert' || $type == 'update' ){
				$sql = "INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
						id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
						SELECT 
							NULL,
							{$system_store_id},
							id_sucursal,
							CONCAT('{',
								'\"table_name\" : \"ec_sucursal_producto_ubicacion_almacen\",',
								'\"action_type\" : \"{$type}\",',
								'\"primary_key\" : \"folio_unico\",',
								'\"primary_key_value\" : \"', '{$row['folio_unico']}', '\",',
								'\"id_sucursal\" : \"', {$row['id_sucursal']}, '\",',
								'\"id_almacen\" : \"', {$row['id_almacen']}, '\",',
								'\"id_producto\" : \"', {$row['id_producto']}, '\",',
								'\"inventario_acumulado\" : \"', {$row['inventario_acumulado']}, '\",',
								'\"numero_ubicacion_desde\" : \"', {$row['numero_ubicacion_desde']}, '\",',
								'\"numero_ubicacion_hasta\" : \"', {$row['numero_ubicacion_hasta']}, '\",',
								'\"pasillo_desde\" : \"', {$row['pasillo_desde']}, '\",',
								'\"pasillo_hasta\" : \"', {$row['pasillo_hasta']}, '\",',
								'\"altura_desde\" : \"', '{$row['altura_desde']}', '\",',
								'\"altura_hasta\" : \"', '{$row['altura_hasta']}', '\",',
								'\"habilitado\" : \"', {$row['habilitado']}, '\",',
								'\"es_principal\" : \"', {$row['es_principal']}, '\",',
								'\"fecha_alta\" : \"', '{$row['fecha_alta']}', '\",',
								'\"folio_unico\" : \"', '{$row['folio_unico']}', '\",',
								'\"sincronizar\" : \"', 1, '\"',
								'}'
							),
							NOW(),
							'{$type}_from_insertStoreLocationSinchronization',
							1
						FROM sys_sucursales 
						WHERE id_sucursal = IF( {$system_store_id} = -1, {$row['id_sucursal']}, -1 )";
				}else if( $type == 'delete' ){
					$sql = "INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
						id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
						SELECT 
							NULL,
							{$system_store_id},
							id_sucursal,
							CONCAT('{',
								'\"table_name\" : \"ec_sucursal_producto_ubicacion_almacen\",',
								'\"action_type\" : \"{$type}\",',
								'\"primary_key\" : \"folio_unico\",',
								'\"primary_key_value\" : \"', '{$row['folio_unico']}', '\"',
								'}'
							),
							NOW(),
							'{$type}_from_insertStoreLocationSinchronization',
							1
						FROM sys_sucursales 
						WHERE id_sucursal = IF( {$system_store_id} = -1, {$row['id_sucursal']}, -1 )";
				}
				$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de ubicaciones del producto en sucursal : {$sql} {$this->link->error}" );
		}
	}
?>