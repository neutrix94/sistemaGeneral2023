<?php
	if( isset( $_GET['inventory_fl'] ) ){
//die('here');
		$action = $_GET['inventory_fl'];
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$inventory = new Inventory( $link, $sucursal_id );

		switch ( $action ) {

			case 'unic_barcode_no_repeat_check' : 
				echo $inventory->unic_barcode_no_repeat_check( $_GET['barcode'], $_GET['warehouse_id'] );
			break;

			case  'getCategories' :
				echo $inventory->getCategories( );
			break;

			case  'getSubcategories' :
				echo $inventory->getSubcategories( $_GET['category_id'] );
			break;

			case  'getSubtypes' :
				echo $inventory->getSubtypes( $_GET['subcategory_id'] );
			break;

			case 'insertProductProvidersInTemporalCount': 
				echo $inventory->insertProductProvidersInTemporalCount( $sucursal_id, $_GET['warehouse_id'] );
			break;

			case 'getStoreWhareouses': 
				echo $inventory->getStoreWhareouses();
			break;
			case 'check_mannager_password' :
				echo $inventory->check_mannager_password( $_GET['password'] );
			break;

			case 'seekProduct':
				echo $inventory->seekProduct( $_GET['key'],  $_GET['warehouse_id'] );
			break;

			case 'getOptionsByProductId' : 
				echo $inventory->getOptionsByProductId( $_GET['product_id'] );
			break;

			case 'insertScannAndDetail' :
				echo $inventory->insertScannAndDetail( $_GET['type'], $_GET['product_id'],
														$_GET['product_provider_id'], $_GET['boxes'],
														$_GET['packs'], $_GET['pieces'],
														$_GET['total_pieces'], $_GET['user_id'],
														$_GET['date'], $_GET['barcode'], $_GET['warehouse_id'] );
			break;

			case 'saveProductCount' :
				echo $inventory->saveProductCount( $_GET['product_id'], $_GET['product_provider_id'], 
					$_GET['warehouse_id'], $user_id );
			break;

			case 'getProductCounterHistoric':
				echo $inventory->getProductCounterHistoric( $_GET['product_id'], $_GET['product_provider_id'], $_GET['warehouse_id'] );
			break;

			case 'getNextProductByRange' : 
				echo $inventory->getNextProductByRange( $_GET['range_letter_since'], $_GET['range_number_since'], 
									$_GET['range_letter_to'], $_GET['range_number_to'], $_GET['warehouse_id'],
									$_GET['category'], $_GET['subcategory'], $_GET['subtype'] );
			break;

			case 'ommit_product_provider' : 
				echo $inventory->ommit_product_provider( $_GET['product_provider'], $_GET['warehouse_id'] );
			break;

			case 'getOmitedProducts':
				echo $inventory->getOmitedProducts( $_GET['warehouse_id'] );	
			break;

			case 'remove_scann';
				echo $inventory->remove_scann( $_GET['row_id'], $_GET['warehouse_id'] );
			break;

			default :
				die( "Permission denied!" );
			break;
		}
	}

	class Inventory
	{
		private $link;
		private $store_id;
		function __construct( $connection, $store_id )
		{
			$this->link = $connection;
			$this->store_id = $store_id;
		}

		public function unic_barcode_no_repeat_check( $barcode, $warehouse_id ){
			$sql = "SELECT
						cit.id_conteo_inventario_tmp
					FROM ec_conteo_inventario_tmp cit
					LEFT JOIN ec_conteo_inventario_tmp_detalle citd
					ON cit.id_conteo_inventario_tmp = citd.id_conteo_inventario_tmp
					WHERE cit.id_almacen = {$warehouse_id}
					AND citd.codigo_barras = '{$barcode}'";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si el codigo unico ya fue escaneado : {$this->link->error}" );
			if( $stm->num_rows > 0 ){
				die( '<p>Este código único ya fue escaneado</p>' );
			}else{
				return 'ok';
			}
		}

		public function remove_scann( $row_id, $warehouse_id ){
			$this->link->autocommit( false );
		//reconsulta las cantidades para actualizar el reasumen del conteo
			$sql = "SELECT 
						id_proveedor_producto AS product_provider_id,
						caja AS box,
						paquete AS pack,
						pieza AS piece,
						total_piezas AS total_pieces
					FROM ec_conteo_inventario_tmp_detalle
					WHERE id_conteo_inventario_tmp_detalle = {$row_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el registro por eliminar : {$this->link->error}" );
			$row = $row = $stm->fetch_assoc();
		//elimina el registro
			$sql = "DELETE FROM ec_conteo_inventario_tmp_detalle WHERE id_conteo_inventario_tmp_detalle = {$row_id}";
			$stm = $this->link->query( $sql ) or die( "Error al eliminar el detalle de conteo temporal : {$this->link->error}" );
		//actualiza el registro de conteo
			$sql = "UPDATE ec_conteo_inventario_tmp
						SET cajas = ( cajas - {$row['box']} ),
						paquetes = ( paquetes - {$row['pack']} ),
						piezas = ( piezas - {$row['piece']} ),
						total_en_piezas = ( total_en_piezas - {$row['total_pieces']} ),
						pospuesto = '0',
						ya_realizo_movimientos = '0'
					WHERE id_proveedor_producto = {$row['product_provider_id']}
					AND id_almacen = {$warehouse_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar el resumen de conteo temporal : {$this->link->error}" );
		
			$this->link->autocommit( true );
			return 'ok';
		}

		public function getBoxesCeils(){
			$sql = "SELECT
						UPPER( codigo_barras )
					FROM ec_codigos_validacion_cajas";
			$stm = $this->link->query( $sql )  or die( "Error al consultar los sellos de  caja :  {$this->link->error}" );
			$resp = "";
			while ( $row = $stm->fetch_row() ) {
				$resp .= ( $resp  == '' ? '' : ',' );
				$resp .= $row[0];
				//$resp[] = $row;
			}
			return $resp;
		}

		public function getOmitedProducts( $warehouse_id ){
			$resp  = "ok|";
			$sql = "SELECT
						p.nombre AS name,
						pp.clave_proveedor AS provider_clue,
						pp.id_proveedor_producto AS product_provider_id
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_productos p
					ON p.id_productos = pp.id_producto
					LEFT JOIN ec_conteo_inventario_tmp cit
					ON cit.id_proveedor_producto = pp.id_proveedor_producto
					WHERE  cit.id_almacen = {$warehouse_id}
					AND cit.pospuesto = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar productos omitidos : {$this->link->error} {$sql}" );
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr onclick=\"seek_product( 'intro', {$row['product_provider_id']} )\">
							<td>{$row['name']}</td>
							<td>{$row['provider_clue']}</td>
						</tr>";
			}
			return $resp;
		}

		public function ommit_product_provider( $product_provider, $warehouse_id ){
			$sql = "UPDATE ec_conteo_inventario_tmp 
						SET pospuesto = '1' 
					WHERE id_proveedor_producto = {$product_provider}
					AND id_almacen = {$warehouse_id}";
			$stm = $this->link->query( $sql ) or die( "Error al omitir el registro del producto : {$this->link->error}" );
			return "ok|Producto pospuesto exitosamente!";
		}

		public function getNextProductByRange( $range_letter_since, $range_number_since, 
								$range_letter_to, $range_number_to, $warehouse_id, 
								$category, $subcategory, $subtype ){
			//die( intval( $range_since ) . " + " . intval( $range_to ) );
			$filter  = "";
			$filter .= ( $category != -1 ? " AND p.id_categoria = {$category}" : "" );
			$filter .= ( $subcategory != -1 ? " AND p.id_subcategoria = {$subcategory}" : "" );
			$filter .= ( $subtype != -1 ? " AND p.id_subtipo = {$subtype}" : "" );
			//die( $filter );
			$sql = "SELECT
						cit.id_proveedor_producto AS product_provider_id
					FROM ec_conteo_inventario_tmp cit
					LEFT JOIN ec_productos p
					ON p.id_productos = cit.id_producto
					LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
					ON ppua.id_proveedor_producto = cit.id_proveedor_producto
					/*WHERE ( ( CONCAT( ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde ) BETWEEN '{$range_since}' AND '{$range_to}')
					OR ( CONCAT( ppua.letra_ubicacion_hasta, ppua.numero_ubicacion_hasta ) BETWEEN '{$range_since}' AND '{$range_to}') )*/
					WHERE (  ppua.numero_ubicacion_desde BETWEEN '{$range_number_since}' AND '{$range_number_to}' )     
					AND (  ppua.letra_ubicacion_desde BETWEEN '{$range_letter_since}' AND '{$range_letter_to}' )	
					AND cit.ya_fue_contado = '0'
					AND cit.pospuesto = '0'
					AND ppua.es_principal = '1'
					AND cit.id_almacen = {$warehouse_id}
					{$filter}
					GROUP BY cit.id_proveedor_producto
					/*ORDER BY CONCAT( ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde ), p.orden_lista ASC*/
					ORDER BY ppua.letra_ubicacion_desde,ppua.numero_ubicacion_desde, 
					ppua.pasillo_desde, ppua.altura_desde, p.orden_lista ASC
					LIMIT 1";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el producto que sigue de acuerdo al rango de ubicaciones : {$this->link->error}" ); 
			if( $stm->num_rows == 0 ){
				return 'ok|withouth_rows';
			}else{
				$row = $stm->fetch_assoc();
				return "ok|{$row['product_provider_id']}";
			}
		}

		public function getProductCounterHistoric( $product_id, $product_provider_id, $warehouse_id ){
			//die( 'here' );
		//consulta el encabezado
			$sql = "SELECT 
						id_conteo_inventario_tmp AS id
					FROM ec_conteo_inventario_tmp
					WHERE id_producto = {$product_id}
					AND id_proveedor_producto = {$product_provider_id}
					AND id_almacen = {$warehouse_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el id de encabezado de conteo : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$count_id = $row['id'];
			$sql = "SELECT
						/*0*/tipo,
						/*1*/id_producto,
						/*2*/id_proveedor_producto,
						/*3*/caja,
						/*4*/paquete,
						/*5*/pieza,
						/*6*/total_piezas,
						/*7*/id_usuario,
						/*8*/fecha_escaneo,
						/*9*/codigo_barras,
						/*10*/id_conteo_inventario_tmp_detalle
					FROM ec_conteo_inventario_tmp_detalle
					WHERE id_conteo_inventario_tmp = {$count_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el id de encabezado de conteo : {$this->link->error}" );
			
			$resp = "";
			while( $row = $stm->fetch_row() ){
				$resp .= ( $resp == "" ? "" : "|~|" );
				$resp .= $row[0] . '|';
				$resp .= $row[1] . '|';
				$resp .= $row[2] . '|';
				$resp .= $row[3] . '|';
				$resp .= $row[4] . '|';
				$resp .= $row[5] . '|';
				$resp .= $row[6] . '|';
				$resp .= $row[7] . '|';
				$resp .= $row[8] . '|';
				$resp .= $row[9] . '|';
				$resp .= $row[10];
			}

			//die( "" );
			return "ok|~~|{$resp}";
		}

		public function saveProductCount( $product_id, $product_provider_id, $warehouse_id, $user_id ){
			$sql = "UPDATE ec_conteo_inventario_tmp SET 
							ya_fue_contado = '1',
							pospuesto = '0',
							ya_realizo_movimientos = '0',
							id_usuario_conteo = {$user_id}
					WHERE id_almacen = {$warehouse_id}
					AND id_producto = {$product_id}
					AND id_proveedor_producto = {$product_provider_id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar a contado el registro de conteo : {$this->link->error} {$sql}" );
			return 'ok|Registro guardado exitosamente!';
		}

		public function insertScannAndDetail( $type, $product_id,
											$product_provider_id, $boxes,
											$packs, $pieces,
											$total_pieces, $user_id,
											$date, $barcode, $warehouse_id ){
			$this->link->autocommit( false );
			$tmp_inventory_counter_id = "";
		//verifica si existe el registro de conteo de inventario y si no existe lo inserta
			$sql = "SELECT  
						id_conteo_inventario_tmp AS id
					FROM ec_conteo_inventario_tmp
					WHERE id_almacen = {$warehouse_id}
					AND id_sucursal = {$this->store_id}
					AND id_producto = {$product_id}
					AND id_proveedor_producto = {$product_provider_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar si existe el registro de conteo : {$this->link->error}" );
			
			//die( 'here' );
			if( $stm->num_rows == 0 ){
			//inserta el registro de conteo
				$sql = "INSERT INTO ec_conteo_inventario_tmp SET 
									id_conteo_inventario_tmp = NULL,
									id_producto = {$product_id},
									id_proveedor_producto = {$product_provider_id},
									id_sucursal = {$this->store_id},
									id_almacen = {$warehouse_id},
									cajas = 0,
									paquetes = 0,
									piezas = 0,
									total_en_piezas = 0,
									ya_fue_contado  = '0',
									pospuesto = '0',
									id_usuario_conteo = 0";
				$stm = $this->link->query( $sql ) or die( "Error al insertar registro de conteo de inventario : {$this->link->error}" );
				$tmp_inventory_counter_id = $this->link->insert_id;
			//	die( 'here : ' + $tmp_inventory_counter_id );
			}else{
				$row = $stm->fetch_assoc();
				$tmp_inventory_counter_id = $row['id'];
			}

			$sql = "INSERT INTO ec_conteo_inventario_tmp_detalle SET 
						id_conteo_inventario_tmp = {$tmp_inventory_counter_id}, 
						id_producto = {$product_id}, 
						id_proveedor_producto = {$product_provider_id}, 
						tipo = '{$type}', 
						codigo_barras = '{$barcode}', 
						caja = {$boxes}, 
						paquete = {$packs}, 
						pieza = {$pieces},
						total_piezas = {$total_pieces},
						id_usuario = {$user_id},
						fecha_escaneo = '{$date}'";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registro de detalle de conteo de inventario : {$this->link->error} {$sql}" );
			$id = $this->link->insert_id;
			$sql = "UPDATE ec_conteo_inventario_tmp SET 
							cajas = ( cajas + {$boxes} ), 
							paquetes = ( paquetes + {$packs} ), 
							piezas = ( piezas + {$pieces} ), 
							total_en_piezas = ( total_en_piezas + {$total_pieces} ),
							id_usuario_conteo = {$user_id}
					WHERE id_conteo_inventario_tmp = {$tmp_inventory_counter_id}";

			$stm_upd = $this->link->query( $sql ) or die( "Error al insertar registro de detalle de conteo de inventario : {$this->link->error}" );
			$this->link->autocommit( true );			
			return "ok|{$id}";
		}
		public function getStoreWhareouses(){
			$sql = "SELECT
						id_almacen AS id,
						nombre AS name
					FROM ec_almacen
					WHERE IF( '{$this->store_id}' = '-1',
							id_sucursal > 0,
							id_sucursal = {$this->store_id} )";
			$stm = $this->link->query( $sql ) or die( "Error al consultar almacenes de sucursal : {$this->link->error}" );
			$resp = "<select id=\"warehouse_id\" class=\"form-control\">";
				$resp .= "<option value=\"0\">-- Seleccionar Almacén --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
			}
			$resp .= "</select>"; 
			return $resp;
		}

		function getCategories(){
			$resp = "";
			$options = "";
			$sql = "SELECT
						id_categoria AS id,
						nombre AS name
					FROM ec_categoria";
			$stm = $this->link->query( $sql ) or die( "Error al consultar familias : {$this->link->error}" );
			$resp .= "<select class=\"form-control\" id=\"category_combo\" onchange=\"change_combo( 1 );\">";
			$options .= "<option value=\"-1\">-- Todas --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$options .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
			}
			$resp .= "{$options}</select>";
			return $resp;
		}

		function getSubcategories( $category = null ){
			$resp = "";
			$options = "";
			$sql = "SELECT
						id_subcategoria AS id,
						nombre AS name
					FROM ec_subcategoria";
			$sql .= ( $category == null ? "" : " WHERE id_categoria = {$category}" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar tipos : {$this->link->error}" );
			$resp .= "<select class=\"form-control\" id=\"subcategory_combo\" onchange=\"change_combo( 2 );\">";
			$options .= "<option value=\"-1\">-- Todas --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$options .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
			}
			$resp .= "{$options}</select>";
			return ( $category == null ? $resp : $options );
		}

		function getSubtypes( $subcategory = null ){
			$resp = "";
			$options = "";
			$sql = "SELECT
						id_subtipos AS id,
						nombre AS name
					FROM ec_subtipos";
			$sql .= ( $subcategory == null ? "" : " WHERE id_tipo = {$subcategory}" );
			$stm = $this->link->query( $sql ) or die( "Error al consultar subtipos : {$this->link->error}" );
			$resp .= "<select class=\"form-control\" id=\"subtype_combo\" onchange=\"change_combo( 3 );\">";
			$options .= "<option value=\"-1\">-- Todas --</option>";
			while ( $row = $stm->fetch_assoc() ) {
				$options .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
			}
			$resp .= "{$options}</select>";
			return ( $subcategory == null ? $resp : $options );
			
		}

		function check_mannager_password( $password ){
			$sql = "SELECT 
						u.id_usuario
					FROM sys_users u
					LEFT JOIN sys_sucursales s
					ON s.id_encargado = u.id_usuario
					WHERE s.id_sucursal = {$this->store_id}
					AND u.contrasena = md5( '{$password}' )";
			$stm =  $this->link->query( $sql ) or die( "Error al consultar la contraseña del encargado : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return "La contraseña es incorrecta";
			}else{
				return "ok";
			}
		}
	//inserta los temporales de proveedor producto
		public function insertProductProvidersInTemporalCount( $store_id, $warehouse_id ){

			//die( 'here' );
			$sql = "INSERT INTO ec_conteo_inventario_tmp ( id_conteo_inventario_tmp, id_producto, 
				id_proveedor_producto, id_sucursal, id_almacen, cajas, paquetes, piezas, total_en_piezas, ya_fue_contado, 
				pospuesto, id_usuario_conteo )
					SELECT
						NULL,/*1*/
						pp.id_producto,/*2*/
						pp.id_proveedor_producto,/*3*/
						{$store_id},
						{$warehouse_id},
						0,/*4*/
						0,/*5*/
						0,/*6*/
						0,/*7*/
						0,/*8*/
						0,/*9*/
						NULL/*10*/
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_conteo_inventario_tmp cit
					ON pp.id_proveedor_producto = cit.id_proveedor_producto
					LEFT JOIN sys_sucursales s ON s.id_sucursal = {$store_id}
					LEFT JOIN ec_almacen alm ON s.id_sucursal = alm.id_sucursal
					WHERE cit.id_proveedor_producto IS NULL
					AND alm.id_almacen = {$warehouse_id}
					AND pp.id_proveedor_producto > 0";
			$stm = $this->link->query( $sql ) or die( "Error al insertar los registros temporales de conteo : {$this->link->error} {$sql}" );
			return 'ok';
		}

	//consulta el proveedor producto que sigue
		public function getProductProvider( $category_filter, $subcategory_id, $subtype_id, $range_since, $range_to, $order_by ){
			
			$sql = "SELECT 
						pp.id_proveedor_producto AS product_provider_id,
						pp.clave_proveedor AS provide_clue,
						p.nombre AS product_name,
						p.orden_lista AS order_list,
						p.id_productos AS product_id
					FROM ec_conteo_inventario_tmp cit
					LEFT JOIN ec_proveedor_producto pp
					ON cit.id_proveedor_producto = pp.id_proveedor_producto
					WHERE cit.ya_fue_contado = 0
					LIMIT 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el siguiente producto : {$this->link->error}" );
			if( $stm->num_rows <= 0 ){
				return "Ya no hay productos por contar en el rango de ubicaciones seleccionadas : <b>{$range_since} a {$range_to}</b>";
			}else{
				$row = $stm->fetch_assoc();
				return "ok|" . json_encode( $row );
			}
		}

		public function seekProduct( $txt, $warehouse_id ){
			//$warehouse_id = 1;
		//busca por codigo de barras
			$sql = "SELECT
						ax.product_id,
						ax.product_provider_id,
						ax.provider_clue,
						ax.pieces_per_box,
						ax.pieces_per_pack,
						ax.product_name,
						ax.is_maquiled,
						ax.special_product,
						ax.codigo_barras_pieza_1,
						ax.codigo_barras_pieza_2,
						ax.codigo_barras_pieza_3,
						ax.codigo_barras_presentacion_cluces_1,
						ax.codigo_barras_presentacion_cluces_2,
						ax.codigo_barras_caja_1,
						ax.codigo_barras_caja_2,
						ax.is_without_tag,
						ax.print_box_tag,
						ax.print_pack_tag,
						ax.print_loose_parts_tag,
						ax.print_piece_tag,
						CONCAT( '<b>UBICACIÓN : </b><br>DESDE : ', ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde, 
							' HASTA : ', ppua.letra_ubicacion_hasta, ppua.numero_ubicacion_hasta,
							IF( ppua.pasillo_desde > 0, 
								CONCAT( '<br>PASILLO : ', ppua.pasillo_desde, ' - ', ppua.pasillo_hasta ),
								'' 
							),
							IF( ppua.altura_desde != '', 
								CONCAT( '<br>ALTURA : ', ppua.altura_desde, ' - ', ppua.altura_hasta ),
								'' 
							)
						) AS location,
						ax.inventory
					FROM(
					SELECT 
						p.id_productos AS product_id,
						pp.id_proveedor_producto AS product_provider_id,
						CONCAT( pp.clave_proveedor, ' <b>(', pp.presentacion_caja, ' pzs x caja)</b>' ) AS provider_clue,
						pp.presentacion_caja AS pieces_per_box,
						pp.piezas_presentacion_cluces AS pieces_per_pack,
						CONCAT( '( ', p.orden_lista, ' ) ', p.nombre ) AS product_name,
						(
							SELECT
							IF( epd.id_producto IS NULL,
								0,
								1
							)
							FROM ec_productos_detalle epd
							WHERE epd.id_producto = p.id_productos
							OR epd.id_producto_ordigen = p.id_productos 
						)AS is_maquiled,
						(SELECT 
							IF( pem.id_producto IS NULL,
								0,
								1
							)
							FROM ec_productos_etiquetado_maquila pem
							WHERE pem.id_producto = p.id_productos
						) AS special_product,
						pp.codigo_barras_pieza_1,
						pp.codigo_barras_pieza_2,
						pp.codigo_barras_pieza_3,
						pp.codigo_barras_presentacion_cluces_1,
						pp.codigo_barras_presentacion_cluces_2,
						pp.codigo_barras_caja_1,
						pp.codigo_barras_caja_2,
						pem.es_producto_sin_etiqueta AS is_without_tag,
						pem.imprimir_caja AS print_box_tag,
						pem.imprimir_paquete AS print_pack_tag,
						pem.imprimir_piezas_sueltas AS print_loose_parts_tag,
						pem.imprimir_etiqueta_de_pieza AS print_piece_tag,
						SUM( 
							IF( mdpp.id_proveedor_producto IS NULL OR mdpp.id_almacen != {$warehouse_id}, 
								0, 
								( tm.afecta * mdpp.cantidad ) 
							) 
						) AS inventory
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON p.id_productos = pp.id_producto
					LEFT JOIN ec_productos_etiquetado_maquila pem
					ON pem.id_producto = p.id_productos
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
					ON mdpp.id_proveedor_producto = pp.id_proveedor_producto
					LEFT JOIN ec_tipos_movimiento tm
					ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
					WHERE ( pp.codigo_barras_pieza_1 = '{$txt}'
					OR pp.codigo_barras_pieza_2 = '{$txt}'
					OR pp.codigo_barras_pieza_3 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
					OR pp.codigo_barras_caja_1 = '{$txt}'
					OR pp.codigo_barras_caja_2 = '{$txt}' 
					OR pp.id_proveedor_producto = '{$txt}')
					AND p.es_maquilado = 0
					GROUP BY pp.id_proveedor_producto
				)ax 
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ax.product_provider_id = ppua.id_proveedor_producto
				AND ppua.es_principal = 1";
			//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el producto por codigo de barras : " . $this->link->error );
			if( $stm->num_rows <= 0 ){
				return $this->seekByName( $txt );
			}else if( $stm->num_rows == 1 ){
				$row = $stm->fetch_assoc();
				return 'ok|' . json_encode( $row );
			}else{
				return $this->getProductProviderCatalogue( $stm );
			}
		}

		public function seekByName( $barcode ){
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
				WHERE ( pp.clave_proveedor LIKE '%{$barcode}%'
				{$condition} OR p.orden_lista = '{$barcode}' ) 
				AND pp.id_proveedor_producto IS NOT NULL
				AND p.id_productos > 0
				AND p.es_maquilado = 0
				GROUP BY p.id_productos";
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

		public function getOptionsByProductId( $product_id ){
			//die( 'here' );
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
					$resp .= "<h5>Selecciona el modelo del producto : </h5>";
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

	}
?>