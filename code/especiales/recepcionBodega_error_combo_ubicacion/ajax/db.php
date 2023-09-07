<?php
	include( '../../../../config.ini.php' );
	include( '../../../../conectMin.php' );
	include( '../../../../conexionMysqli.php' );

	$action = '';
	if( !isset( $_POST['fl'] ) ){
		$action = $_GET['fl'];
	}else{
		$action = $_POST['fl'];
	}

	switch ( $action ) {

	//validacion para que no se repita el folio de remisión
		case 'validateInvoiceNoExists':
			$key = $_POST['to_check'];
			$sql = "SELECT 
						id_recepcion_bodega 
					FROM ec_recepcion_bodega
					WHERE folio_recepcion = '{$key}'";
			$exc = $link->query( $sql ) or die( "Error validateInvoiceNoExists : " . $link->error );
			if( $exc->num_rows > 0 ){
				die('Este folio de Remisión ya esta registrado, verifique antes de continuar!');
			}
			die( 'ok' );
		break;

	//insertar cabecera de recepción
		case 'insertInvoice':
			$folio = $_POST['invoice_folio'];
			$parts = $_POST['parts_number'];
			$provider = $_POST['provider_id'];
			$sql = "SELECT 
						serie 
					FROM ec_series_recepciones_bodega
					WHERE recepcion_actual = 0
					ORDER BY serie ASC
					LIMIT 1";
			$exc = $link->query( $sql ) or die( "Error al consultar Serie disponible : " . $link->error );
			$serie = $exc->fetch_row();
			
			$sql = "INSERT INTO ec_recepcion_bodega ( id_proveedor, id_usuario, folio_recepcion, serie, numero_partidas, fecha_alta )
			VALUES( {$provider}, {$user_id}, '{$folio}', '{$serie[0]}', {$parts},  NOW() )";
			$exc = $link->query( $sql ) or die( "Error al insertar la recepción : " . $link->error );
			
		//recupera registro
			$sql = "SELECT id_recepcion_bodega, folio_recepcion, serie, numero_partidas FROM ec_recepcion_bodega WHERE serie = '{$serie[0]}'"; 
			$exc = $link->query( $sql ) or die( "Error al recuperar registro de recepcion bodega : " . $link->error );
			$r = $exc->fetch_row();

			$sql = "UPDATE ec_series_recepciones_bodega SET recepcion_actual = '{$r[0]}' WHERE serie = '{$serie[0]}'";
			$exc = $link->query( $sql ) or die( "Error al actualizar serie de recepcion : " . $link->error );
			
			echo "{$r[0]}~{$r[1]}~{$r[2]}~{$r[3]}";
		break;

	//busqueda de remisiones
		case 'seekInvoices' : 
			$provider = $_POST['provider_id'];
			$txt = $_POST['key'];
			$series = $_POST['current_series'];
			$sql = "SELECT 
						id_recepcion_bodega, 
						folio_recepcion, 
						serie, 
						numero_partidas 
					FROM ec_recepcion_bodega 
					WHERE id_proveedor = '{$provider}'
					AND ( folio_recepcion LIKE '%{$txt}%' )
					AND id_recepcion_bodega_status IN( 1, 2 )";
			if( sizeof($series) > 0 ){
				$sql .= " AND serie NOT IN(";
				foreach ($series as $key => $serie) {
					$sql .= ( $key > 0 ? " ," : "" ) . "'{$serie}'";
				}
				$sql .= ")";
			}
			//echo $sql;
			$exc = $link->query( $sql ) or die( "Error al buscar coincidencias de transferencias : " . $link->error );
			if( $exc->num_rows <= 0 ){
				die( '<div><p>Sin coincidencias!</p></div>' );
			}
			$resp = "";
			while ( $r = $exc->fetch_row() ) {
				$resp .= "<div class=\"invoice_seeker_options\" onclick=\"setInvoiceExistent( '{$r[0]}~{$r[1]}~{$r[2]}~{$r[3]}' );\">";
					$resp .= "<p>{$r[1]}</p>";
				$resp .= "</div>";
			}
			die( $resp );	
		break;

	//busqueda por producto
		case 'seekProduct' : 
			$provider = $_POST['provider_id'];
			$txt = strtoupper($_POST['key']);
			$is_scanner = $_POST['scanner'];
			$sql = "SELECT
					/*0*/p.id_productos,
					/*1*/CONCAT( p.nombre, '<br/>Caja con <b>', pp.presentacion_caja, '</b> pieza', 
						IF( pp.presentacion_caja > 0, 's ', ' ' ), 
						'<br/>MODELO : <b>' , pp.clave_proveedor, '</b>'
						) AS product_name,
					/*2*/IF(pp.clave_proveedor IS NULL, '', pp.clave_proveedor ),
					/*3*/IF(pp.id_proveedor_producto IS NULL, '', pp.id_proveedor_producto ),
					/*4*/IF(pp.codigo_barras_pieza_1 IS NULL, 
						'', 
						CONCAT( pp.codigo_barras_pieza_1, '~', pp.codigo_barras_pieza_2, '~', pp.codigo_barras_pieza_3 ) 
						) AS pieceBarcodes,
					/*5*/IF(pp.piezas_presentacion_cluces IS NULL, '', pp.piezas_presentacion_cluces ),
					/*6*/IF(pp.codigo_barras_presentacion_cluces_1 IS NULL, 
						'', 
						CONCAT( pp.codigo_barras_presentacion_cluces_1, '~', pp.codigo_barras_presentacion_cluces_1 )
						) AS packBarcodes,
					/*7*/IF(pp.presentacion_caja IS NULL, '', pp.presentacion_caja ),
					/*8*/IF(pp.codigo_barras_caja_1 IS NULL, 
						'', 
						CONCAT( pp.codigo_barras_caja_1, '~', pp.codigo_barras_caja_2 )
						) AS boxBarcodes,
					/*9*/p.ubicacion_almacen,
					/*10*/0
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp 
					ON p.id_productos = pp.id_producto
					AND pp.id_proveedor = '{$provider}'
					WHERE";
			//if( $is_scanner ){
				$sql .= " ( UPPER( pp.codigo_barras_pieza_1 ) = '{$txt}' OR UPPER( pp.codigo_barras_pieza_2 ) = '{$txt}'";
				$sql .= " OR UPPER( pp.codigo_barras_pieza_3 = '{$txt}' ) OR UPPER( pp.codigo_barras_presentacion_cluces_1 ) = '{$txt}'";
				$sql .= " OR UPPER( pp.codigo_barras_presentacion_cluces_2 ) = '{$txt}'";
				$sql .= " OR UPPER( pp.codigo_barras_caja_1 ) = '{$txt}'";
				$sql .= " OR UPPER( pp.codigo_barras_caja_2 ) = '{$txt}' )";
			//}else{
				$aux = explode(' ', $txt);
				$sql .= " OR (";
				foreach ($aux as $key => $value) {
					$sql .= ( $key > 0 ? " AND" : "" ) . " UPPER( p.nombre ) LIKE '%{$value}%'";
				}
				$sql .= " )";
				$sql .= " OR UPPER( p.clave ) LIKE '%{$txt}%'";
				$sql .= " OR UPPER( pp.clave_proveedor ) LIKE '%{$txt}%'";
				$sql .= " OR UPPER( p.orden_lista ) = '{$txt}'";
			//}
			$sql .= " GROUP BY p.id_productos, pp.id_proveedor_producto";
			//echo 'ok';
			//echo $sql;
			$exc = $link->query( $sql ) or die( "Error al buscar prodctos : " . $link->error );
			$resp = "";
			//echo 'here';
			if( $exc->num_rows <= 0 ){
				die( "<div><b>No se encontraron coincidencias para este proveedor</b></div>" );
			}
			while ( $r = $exc->fetch_row() ) {
				if( $r[1] != '' ){
					$resp .= "<div class=\"group_card\" onclick=\"setProduct( '{$r[0]}', '{$r[1]}', '{$r[2]}', '{$r[3]}',
					'{$r[4]}', '{$r[5]}', '{$r[6]}', '{$r[7]}', '{$r[8]}', '{$r[9]}', '{$r[10]}' );\">";
						$resp .= "<p>{$r[1]}</p>";
					$resp .= "</div>";
				}
			}
			echo $resp;
		break;

		case 'saveInvoiceDetail' :
			$link->autocommit( false );

			$observaciones = $_POST['notes'] . "\n";
			$product_id = $_POST['pk'];
			$product_provider_id = ( $_POST['pp'] == '' ? 'null' : $_POST['pp'] );
			//die( 'p_p : ' . $_POST['pp'] );
			$product_model = $_POST['model'];
			$observaciones .= ( $product_model == '' ? "El producto NO tiene modelo\n" : "" );

			$piece_barcode = $_POST['pz_bc'];
	//		$observaciones .= ( $piece_barcode == '' ? "El producto NO tiene código de barras de PIEZA\n" : "" );

			$pieces_per_pack = $_POST['pzs_x_pack']; 
			$observaciones .= ( $pieces_per_pack == '' ? "El producto NO tiene piezas por PAQUETE\n" : "" );

			$pack_barcode = $_POST['pack_bc'];
	//		$observaciones .= ( $pack_barcode == '' ? "El producto NO tiene código de barras de PAQUETE\n" : "" );

			$pieces_per_box = $_POST['pzs_x_box'];
			$observaciones .= ( $pieces_per_box == '' ? "El producto NO tiene piezas por CAJA\n" : "" );

			$box_barcode = $_POST['box_bc'];
	//		$observaciones .= ( $box_barcode == '' ? "El producto NO tiene código de barras de CAJA\n" : "" );			

			$box_recived = $_POST['box_rec']; 
			$pieces_recived = $_POST['pieces_rec']; 
			$product_part_number = $_POST['product_p_num']; 
			$product_serie = $_POST['product_serie']; 
			$is_new_row = $_POST['is_new'];

			$product_id_new = 'null';
			if( $is_new_row == 1 ){
				$product_id_new = $product_id;
				$product_id = 'null';
			}

			$measures_tmp_id = $_POST['tmp_measures_id'];
		//ubicacion del producto
			$product_location_status = $_POST['location_status'];
			$product_location = $_POST['location'];
		//id de detalle
			$detail_id = ( !isset( $_POST['detail_id'] ) ? '' : $_POST['detail_id'] );

			$block_id = $_POST['block_id'];
			//die( 'e :' . $detail_id );

			$observaciones = str_replace("'", "", $observaciones );

			$location_tmp_id = $_POST['location_tmp_id'];
			if( $product_location_status == 'new_location' ){
				$product_location_status = 3;
			}else if( $product_location_status == 'no_location' ){
				$product_location_status = 1;
			}else{
				$product_location = 2;
			}
			//actualiza un registro existente
				if( $detail_id == '' ){
					$sql = "INSERT INTO ";
				}else{
					$sql = "UPDATE ";
				}
				$sql .= "ec_recepcion_bodega_detalle SET 
							id_recepcion_bodega = (SELECT id_recepcion_bodega FROM ec_recepcion_bodega WHERE serie = '$product_serie' LIMIT 1),
							id_producto = {$product_id}, 
							id_producto_nuevo = {$product_id_new},
							id_proveedor_producto = {$product_provider_id}, 
							modelo = '{$product_model}', 
							piezas_por_caja = '{$pieces_per_box}',
							piezas_por_paquete = '{$pieces_per_pack}', 
							cajas_recibidas = '{$box_recived}', 
							piezas_sueltas_recibidas = '{$pieces_recived}', 
							c_b_pieza = '{$piece_barcode}', 
							c_b_paquete = '{$pack_barcode}',
							c_b_caja = '{$box_barcode}', 
							es_nuevo_modelo = '{$is_new_row}', 
							serie = '{$product_serie}', 
							numero_partida = '{$product_part_number}', 
							observaciones = '{$observaciones}', 
							validado = '0',
							ubicacion_almacen = '{$product_location}',
							id_status_ubicacion = '{$product_location_status}',
							id_bloque_recepcion = '{$block_id}'";
				if( $detail_id != '' ){
					$sql .= " WHERE id_recepcion_bodega_detalle = '{$detail_id}'"; 
				}
			//die( $sql );
			$exc = $link->query( $sql ) or die( "Error al insertar/actualizar el detalle de recepción : {$link->error} " . $sql  );
			//recupera el registro que se insertó
			$inserted_id = $link->insert_id;
			$detail = getRecepcionDetail( $link, $inserted_id, null );
			if( $product_location_status == 3 ){
		//actualiza la nueva ubicación del producto en la tabla de productos
				$sql = "UPDATE ec_productos SET ubicacion_almacen = '{$product_location}' 
				WHERE id_productos = '{$product_id}'";
				$exc = $link->query( $sql ) or die( "Error al actualizar la ubicación del almacen : " . $link->error );
			}
			if( $detail_id == '' && $location_tmp_id != ''  && $location_tmp_id != null ){
				$sql = "UPDATE ec_proveedor_producto_ubicacion_almacen_tmp 
							SET id_recepcion_bodega_detalle = {$inserted_id}
						WHERE id_ubicacion_matriz_tmp = {$location_tmp_id}";
				$stm = $link->query( $sql )or die( "Error al actualizar el id de recepcion en la ubicacion temporal: {$link->error} " );
			}
			if( $measures_tmp_id != '' && $measures_tmp_id != null && $measures_tmp_id != 0 && $detail_id == '' ){
				$sql = "UPDATE ec_proveedor_producto_medidas_tmp SET 
								id_recepcion_bodega_detalle = {$inserted_id}
						WHERE id_proveedor_producto_medida_tmp = {$measures_tmp_id}";
				$stm = $link->query( $sql )or die( "Error al actualizar el id de recepcion : {$link->error} " );
			}
			$link->autocommit( true );
			die( "ok|{$detail}" );
		break;

		case 'seekBarcode' : 
			$product_provider_id = $_POST['p_p'];
			$barcode = strtoupper( $_POST['code'] );
			$sql = "SELECT 
						CONCAT(p.nombre, ' ( MODELO : ', pp.clave_proveedor,' )')
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp ON p.id_productos = pp.id_producto
					WHERE pp.id_proveedor_producto NOT IN('{$product_provider_id}' )
					AND( UPPER( pp.codigo_barras_pieza_1 ) = '{$barcode}' 
						OR UPPER( pp.codigo_barras_pieza_2 ) = '{$barcode}'
						OR UPPER( pp.codigo_barras_pieza_3 ) = '{$barcode}' 
						OR UPPER( pp.codigo_barras_presentacion_cluces_1 ) = '{$barcode}'
						OR UPPER( pp.codigo_barras_presentacion_cluces_2 ) = '{$barcode}' 
						OR UPPER( pp.codigo_barras_caja_1 ) = '{$barcode}'
						OR UPPER( pp.codigo_barras_caja_2 ) = '{$barcode}'
					)";
			$exc = $link->query( $sql ) or die( "Error al validar código de barras : " . $link->error );
			if( $exc->num_rows > 0 ){
				$r = $exc->fetch_row();
				die( "El código de barras '{$barcode}' ya esta registrado en el producto : {$r[0]}" );
			}
			die('ok');
		break;

		case 'getRecepcionDetail' : 
			echo getRecepcionDetail( $link, $_POST['id'], null );
		break;

		case 'seekProductsLocations' : 
			echo seekProductsLocations( $link, $_POST['key'] );
		break;

		case 'changeInvoicesStatus' : 
			echo changeInvoicesStatus( $_POST['data'], $link );
		break;
		
		case 'changeProductLocation' :
			$location_id = $_POST['location_id'];
			$product_id = $_POST['product_id'];
			$product_provider_id = $_POST['product_provider_id'];
			$new_status = $_POST['new_status'];

			$letter_location_since = $_POST['letter_location_since'];
			$number_location_since = $_POST['number_location_since'];
			$aisle_from = $_POST['aisle_from'];
			$level_from = $_POST['level_from'];

			$letter_location_to = $_POST['letter_location_to'];
			$number_location_to = $_POST['number_location_to'];
			$aisle_to = $_POST['aisle_to'];
			$level_to = $_POST['level_to'];

			$type_row = $_POST['type_row'];
			$reception_detail_id = $_POST['reception_detail_id'];

//echo 'pp : ' . $aisle_seeker_since;

			echo changeProductLocation( $type_row, $location_id, $product_id, $product_provider_id, $reception_detail_id, $new_status, 
				$letter_location_since, $number_location_since, $aisle_from, $level_from, 
				$letter_location_to, $number_location_to, $aisle_to, $level_to, $link );
		break;

		default:
			die( 'Permission Denied!' );
		break;

		case 'getInvoiceParts' : 
			echo getInvoiceParts( $_POST['reference'], $link );
		break;

		case 'validateSerie' :
			echo validateSerie( $_GET['serie'], $_GET['serie_number'], $link );
		break;

		case 'seekProvider' : 
			echo seekProvider( $_GET['txt'], $link );
		break;

		case 'setBlockSession' :
			echo setBlockSession( $user_id, $link );
		break;
		
		case 'validateRemoveInvoice' :
			echo validateRemoveInvoice( $_GET['pk'], $_GET['block_id'], $link );
		break;

		case 'measuresForm' :

			$home_path = '../../../';
		    $include_jquery = 0;

		    $path_camera_plugin = '../../';
		    //$path_files = $_GET['path_files'];
		   // $product_provider_id = $_GET['product_provider_id'];
		    $save_img_path = '';
		    //$type = $_GET['type'];

			$row = array();
			if( isset( $_GET['tmp_meassure_id'] ) ){
				$row = getMeassures( $_GET['tmp_meassure_id'], $link );
				//var_dump($row);
			}
			include( '../views/measuresForm.php' );	
			return;
		break;

		case 'savePhoto' :

			$imagenCodificada = file_get_contents("php://input"); //Obtener la imagen
			if(strlen($imagenCodificada) <= 0) exit("No se recibió ninguna imagen");
			//La imagen traerá al inicio data:image/png;base64, cosa que debemos remover
			$imagenCodificadaLimpia = str_replace("data:image/png;base64,", "", urldecode($imagenCodificada));

			//Venía en base64 pero sólo la codificamos así para que viajara por la red, ahora la decodificamos y
			//todo el contenido lo guardamos en un archivo
			$imagenDecodificada = base64_decode($imagenCodificadaLimpia);
			$home_path = $_GET['home_path'];
			$nombreImagenGuardada = '';
			if( isset($_GET['type']) && $_GET['type'] == 1 ){
				$sql = "SELECT 
							CONCAT(
								REPLACE( codigo_barras_presentacion_cluces_1, ' ', '_' ),
								'_',
								DATE_FORMAT(NOW(), '%Y_%m_%d_%h_%i_%s')
							) AS name
						FROM ec_proveedor_producto
						WHERE id_proveedor_producto = {$_GET['product_provider_id']}";
				$stm = $link->query( $sql ) or die( "Error al formar nombre de fotografía : {$link->error}");
				$row = $stm->fetch_assoc();
				$dir = 'packs_img';
				$nombreImagenGuardada = "../../../../files/{$dir}/{$row['name']}.png";
			}else{
				$dir = 'packs_img_tmp';
				$nombreImagenGuardada = "../../../../files/{$dir}/foto_" . uniqid() . ".png";
			}
			//$home_path = $_GET['home_path'] . '../';
			//Calcular un nombre único
			//Escribir el archivo
			file_put_contents( $nombreImagenGuardada, $imagenDecodificada );
			/*if( !isset( $_GET['type']) ){
				echo str_replace( '../../../../', '../../../', $nombreImagenGuardada );
			}else{
				echo $nombreImagenGuardada;
			}*/
			echo str_replace( '../../../../', $home_path, $nombreImagenGuardada );
			//echo $nombreImagenGuardada;
		break;

		case 'saveMeasures' : 
		//medidas de caja
			$box_lenght = ( isset( $_GET['box_lenght'] ) ? $_GET['box_lenght'] : 0 );
			$box_width = ( isset( $_GET['box_width'] ) ? $_GET['box_width'] : 0 );
			$box_height = ( isset( $_GET['box_height'] ) ? $_GET['box_height'] : 0 );
		//medidas de paquete
			$pack_lenght = ( isset( $_GET['pack_lenght'] ) ? $_GET['pack_lenght'] : 0 );
			$pack_width = ( isset( $_GET['pack_width'] ) ? $_GET['pack_width'] : 0 );
			$pack_height = ( isset( $_GET['pack_height'] ) ? $_GET['pack_height'] : 0 );
			$bag_type_id = ( isset( $_GET['bag_type'] ) ? $_GET['bag_type'] : 'null' );
		//imágenes de paquete
			$photo_1 = ( isset( $_GET['photo_1'] ) ? $_GET['photo_1'] : '' );
			$photo_2 = ( isset( $_GET['photo_2'] ) ? $_GET['photo_2'] : '' );
			$photo_3 = ( isset( $_GET['photo_3'] ) ? $_GET['photo_3'] : '' );
		//medidas de la pieza
			$piece_lenght = ( isset( $_GET['piece_lenght'] ) ? $_GET['piece_lenght'] : 0 );
			$piece_width = ( isset( $_GET['piece_width'] ) ? $_GET['piece_width'] : 0 );
			$piece_height = ( isset( $_GET['piece_height'] ) ? $_GET['piece_height'] : 0 );
			$piece_weight = ( isset( $_GET['piece_weight'] ) ? $_GET['piece_weight'] : 0 );

			$product_id = ( isset( $_GET['product_id'] ) && $_GET['product_id'] != null ? $_GET['product_id'] : 'null' );
			$product_provider_id = ( isset( $_GET['product_provider_id'] ) && $_GET['product_provider_id'] != null ? $_GET['product_provider_id'] : 'null' );
			$is_new_product = $_GET['is_new_product'];
		//id de medidas
			$measures_id = ( isset( $_GET['measures_id'] ) && $_GET['measures_id'] != null ? $_GET['measures_id'] : 'null' );
			
			$reception_detail_id = ( $_GET['reception_detail_id'] != '' && $_GET['reception_detail_id'] != null ? $_GET['reception_detail_id'] : 'null' );
			//echo 'rec : ' .$_GET['reception_detail_id'] ;
			$new_product_id = 'null';
			if( $is_new_product == 1 ){
				$new_product_id = $product_id;
				$product_id = 'null';
				$product_provider_id = 'null';
			}

			echo saveMeasures( $measures_id, $product_id, $product_provider_id, $new_product_id, $box_lenght, $box_width, $box_height, 
				$pack_lenght, $pack_width, $pack_height, $bag_type_id, $piece_lenght, $piece_width, $piece_height,
				$piece_weight, $photo_1, $photo_2, $photo_3, $reception_detail_id, $link );
		break;

		case 'saveNewProduct' : 
			echo saveNewProduct( $_GET['product_name'], $_GET['model'], $user_id, $link );
		break;

		case 'getSystemConfig' :
			echo getSystemConfig( $link );
		break;

		case 'getProductLocationOptions':
			$reception_detail_id = ( isset( $_GET['reception_detail_id'] ) ? $_GET['reception_detail_id'] : null  );
			echo getProductLocationOptions( $_GET['product_provider_id'], $reception_detail_id, $link );
		break;

		case 'getLocationDetail': 
		$is_tmp = ( isset( $_GET['is_tmp'] ) ?  $_GET['is_tmp'] : 0 );
			echo getLocationDetail( $_GET['location_id'], $is_tmp, $link );
		break;

		default : 
			die( 'Permission denied!' );
		break;
	}

	function getProductLocationOptions( $product_provider_id, $reception_detail_id = null,  $link ){
		$resp = "";
		$resp .= "<option value=\"0\">-- Seleccionar --</option>";
		$resp .= "<option value=\"no_location\">Sin acomodar</option>";
		$sql = "SELECT 
					id_ubicacion_matriz AS location_id, 
					CONCAT( 'Desde : ' , letra_ubicacion_desde, numero_ubicacion_desde ) AS description,
					es_principal AS id_principal
				FROM ec_proveedor_producto_ubicacion_almacen
				WHERE id_proveedor_producto = {$product_provider_id}";
		if( $reception_detail_id != null ){
			$sql = "SELECT 
					id_ubicacion_matriz_tmp AS location_id, 
					CONCAT( 'Desde : ' , letra_ubicacion_desde, numero_ubicacion_desde ) AS description
				FROM ec_proveedor_producto_ubicacion_almacen_tmp
				WHERE id_recepcion_bodega_detalle_tmp = {$reception_detail_id}";
		}
		$stm = $link->query( $sql ) or die( "Error al consultar las ubicaciones de proveedor_producto : {$link->error()}" );
		//<option value="2">Ubicación : </option>
		while ( $row = $stm->fetch_assoc() ) {
			$selected = '';
			$description = 'Ubicación secundaria : ';  
			if( $row['is_principal'] == 1 || $reception_detail_id != null ){
				$selected = "selected";
				$description = 'Ubicación principal : ';
			}
			if( $reception_detail_id != null ){
				$description= 'Ubicación al recibir :';
			}
			$resp .= "<option value=\"{$row[location_id]}\" {$selected}>{$description} {$row['description']}</option>";
		}
		$resp .= "<option value=\"new_location\">Nueva ubicación</option>";
		return $resp;
	}

	function getLocationDetail( $location_id, $is_tmp, $link ){
		$tmp = "";
		if( $is_tmp != 0 ){
			$tmp = "_tmp";
		}
		$sql = "SELECT 
				/*5*/ppua.letra_ubicacion_desde AS 'location_letter_from',
				/*6*/ppua.numero_ubicacion_desde AS 'location_number_from',
				/*7*/ppua.letra_ubicacion_hasta AS 'location_letter_to',
				/*8*/ppua.numero_ubicacion_hasta AS 'location_number_to',
				/*9*/ppua.pasillo_desde AS 'aisle_from',
				/*10*/ppua.pasillo_hasta AS 'aisle_to',
				/*11*/ppua.altura_desde AS 'level_from',
				/*12*/ppua.altura_hasta AS 'level_to',
				/*15*/(SELECT 
						nivel_bodega 
					FROM ec_rangos_ubicaciones 
					WHERE ppua.letra_ubicacion_desde BETWEEN desde AND hasta) AS 'floor_from',
				/*16*/(SELECT 
						nivel_bodega 
					FROM ec_rangos_ubicaciones 
					WHERE ppua.letra_ubicacion_hasta BETWEEN desde AND hasta) AS 'floor_to'
			FROM ec_proveedor_producto_ubicacion_almacen{$tmp} ppua
			WHERE ppua.id_ubicacion_matriz{$tmp} = {$location_id}";
			//echo $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar detalle de la ubicación {$link->error} {$sql}" );
		$row = $stm->fetch_assoc();
		return 'ok|' . json_encode( $row );
	}

	function getSystemConfig( $link ){
		$sql = "SELECT no_solicitar_medidas_recepcion AS do_not_request_reception_measures FROM sys_configuracion_sistema WHERE 1 LIMIT 1";
		$stm = $link->query( $sql ) or die( "Error al consultar la configuración del sistema : {$link->error}" );
		$row = $stm->fetch_assoc();
		return $row['do_not_request_reception_measures'];
	}

	function saveNewProduct( $product_name, $product_model, $user_id, $link ){
		$sql = "INSERT INTO ec_productos_nuevos_temporal ( /*1*/id_producto_nuevo, /*2*/nombre, /*3*/modelo, /*4*/id_usuario, /*5*/id_recepcion, /*6*/fecha_alta )
		VALUES ( /*1*/NULL, /*2*/'{$product_name}', /*3*/'{$product_model}', /*4*/{$user_id}, /*5*/NULL, /*6*/NOW() )";
		$stm = $link->query( $sql ) or die( "Error al insertar el registro de nuevo producto : {$link->error}");
		$last_id = $link->insert_id;
		return "ok|{$last_id}";
	}

	function getMeassures( $tmp_meassure_id, $link ){
		$resp = "";
		$sql = "SELECT 
				/*1*/id_proveedor_producto_medida_tmp AS 'tmp_id',
				/*6*/largo_caja AS 'box_lenght',
				/*7*/ancho_caja AS 'box_width',
				/*8*/alto_caja AS 'box_height',
				/*9*/largo_paquete AS 'pack_lenght',
				/*10*/ancho_paquete AS 'pack_width',
				/*11*/alto_paquete AS 'pack_height',
				/*12*/id_bolsa_paquete AS 'pack_bag_id',
				/*13*/imagen_paquete_superior AS 'image_1',
				/*14*/imagen_paquete_frontal AS 'image_2',
				/*15*/imagen_paquete_lateral AS 'image_3',
				/*16*/largo_pieza AS 'piece_lenght',
				/*17*/ancho_pieza AS 'piece_width',
				/*18*/alto_pieza AS 'piece_height',
				/*19*/peso_pieza AS 'piece_weight'
			FROM ec_proveedor_producto_medidas_tmp
			WHERE id_proveedor_producto_medida_tmp = '{$tmp_meassure_id}'";
			//die($sql);
		$stm = $link->query( $sql ) or die( "Error al obtener el registro de medidas proveedor producto : {$link->error}");
		$row = $stm->fetch_assoc();
		return $row;
	}
	
	function saveMeasures( $measures_id = null, $product_id, $product_provider_id, $new_product_id, $box_lenght, $box_width, $box_height, 
				$pack_lenght, $pack_width, $pack_height, $bag_type_id, $piece_height, $piece_lenght, $piece_width,
				$piece_weight, $photo_1, $photo_2, $photo_3, $reception_detail_id = 'null', $link ){
		$photo_1 = str_replace( '../../../files/packs_img_tmp/', '', $photo_1 );
		$photo_2 = str_replace( '../../../files/packs_img_tmp/', '', $photo_2 );
		$photo_3 = str_replace( '../../../files/packs_img_tmp/', '', $photo_3 );
		//if( $reception_detail_id 
		if( $measures_id == null || $measures_id == 0 ){
			$sql = "INSERT INTO ec_proveedor_producto_medidas_tmp (
					/*1*/id_proveedor_producto_medida_tmp, /*2*/id_proveedor_producto, /*3*/id_producto, /*4*/id_producto_nuevo,
					/*5*/id_recepcion_bodega_detalle, /*6*/largo_caja, /*7*/ancho_caja, /*8*/alto_caja, /*9*/largo_paquete,
					/*10*/ancho_paquete, /*11*/alto_paquete, /*12*/id_bolsa_paquete, /*13*/imagen_paquete_superior,
					/*14*/imagen_paquete_frontal, /*15*/imagen_paquete_lateral, /*16*/largo_pieza, /*17*/ancho_pieza, /*18*/alto_pieza,
					/*19*/peso_pieza, /*20*/fecha_alta, /*21*/sincronizar )
				 VALUES ( 
					/*id_proveedor_producto_medida_tmp*/NULL,
					/*id_proveedor_producto*/{$product_provider_id},
					/*id_producto*/{$product_id},
					/*id_producto_nuevo*/{$new_product_id},
					/*id_recepcion_bodega_detalle*/{$reception_detail_id},
					/*largo_caja*/'{$box_lenght}',
					/*ancho_caja*/'{$box_width}',
					/*alto_caja*/'{$box_height}',
					/*largo_paquete*/'{$pack_lenght}',
					/*ancho_paquete*/'{$pack_width}',
					/*alto_paquete*/'{$pack_height}',
					/*id_bolsa_paquete*/{$bag_type_id},
					/*imagen_paquete_superior*/'{$photo_1}',
					/*imagen_paquete_frontal*/'{$photo_2}',
					/*imagen_paquete_lateral*/'{$photo_3}',
					/*largo_pieza*/'{$piece_height}',
					/*ancho_pieza*/'{$piece_lenght}',
					/*alto_pieza*/'{$piece_width}',
					/*peso_pieza*/'{$piece_weight}',
					/*fecha_alta*/NOW(),
					/*sincronizar*/1 )";
			$stm = $link->query( $sql ) or die( "Error al insertar medidas de proveedor_producto : {$link->error} {$sql}" );
			return "ok|{$link->insert_id}";
		}else{
			$sql = "UPDATE ec_proveedor_producto_medidas_tmp SET
					/*2*/id_proveedor_producto={$product_provider_id},
					/*3*/id_producto={$product_id},
					/*4*/id_producto_nuevo={$new_product_id},
					/*6*/largo_caja='{$box_lenght}',
					/*7*/ancho_caja='{$box_width}',
					/*8*/alto_caja='{$box_height}',
					/*9*/largo_paquete='{$pack_lenght}',
					/*10*/ancho_paquete='{$pack_width}',
					/*11*/alto_paquete='{$pack_height}',
					/*12*/id_bolsa_paquete={$bag_type_id},
					/*13*/imagen_paquete_superior='{$photo_1}',
					/*14*/imagen_paquete_frontal='{$photo_2}',
					/*15*/imagen_paquete_lateral='{$photo_3}',
					/*16*/largo_pieza='{$piece_height}',
					/*17*/ancho_pieza='{$piece_lenght}',
					/*18*/alto_pieza='{$piece_width}',
					/*19*/peso_pieza='{$piece_weight}'
				WHERE id_proveedor_producto_medida_tmp = {$measures_id}";
			$stm = $link->query( $sql ) or die( "Error al actualizar medidas de proveedor_producto : {$link->error} {$sql}" );
			return "ok|{$measures_id}";
		}
	}

	function getComboPackBags( $link, $option_selected = null ){
		$sql= "SELECT 
				bp.id_bolsa_paquete AS pack_bag_id,
				p.nombre AS name
			FROM ec_bolsas_paquetes bp
			LEFT JOIN ec_productos p
			ON p.id_productos = bp.id_producto_relacionado";
		$stm = $link->query( $sql ) or die( "Error al consultar bolsas de paquetes : {$link->error}" );
		$resp = "<select id=\"pack_bag\" class=\"form-control\">";
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= "<option value=\"{$row['pack_bag_id']}\"";
			$resp .= ( $option_selected != null && $option_selected == $row['pack_bag_id'] ? ' selected' : '' );
			$resp .= ">{$row['name']}</option>";
		}
		$resp .= "</select>";
		return $resp;
	}

	function seekProvider( $txt, $link ){
		$resp = '';
		$sql = "SELECT 
					id_proveedor AS provider_id, 
					nombre_comercial AS name
				FROM ec_proveedor
				WHERE id_proveedor > 1";
		if( $txt != '' ){
			$sql .= ' AND( ';
			$arr_txt = explode( ' ', $txt );
			foreach ($arr_txt as $key => $value) {
				$sql .= ( $key > 0 ? ' AND' : '' );
				$sql .= " nombre_comercial LIKE '%{$value}%'";
			}
			$sql .= " )";
		}
		$stm = $link->query( $sql ) or die( "Error al buscar proveedores : " . $link->error );
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= '<div class="row provider_response" onclick="setProvider( ' . $row['provider_id'] . ', \'' . $row['name'] . '\' )">';
				$resp .= '<b>' . $row['name'] . '</b>'; 
			$resp .= '</div>';
		}
		//return $sql;
		return $resp;
	}

	function getRecepcionDetail( $link, $id = null, $recepcion_id = null ){
		$resp = array();
		$sql = "SELECT
					rd.id_recepcion_bodega_detalle,
					rd.id_recepcion_bodega,
					rd.id_producto,
					rd.id_proveedor_producto,
					rd.piezas_por_caja,
					rd.piezas_por_paquete,
					rd.cajas_recibidas,
					rd.piezas_sueltas_recibidas,
					rd.c_b_pieza,
					rd.c_b_paquete,
					rd.c_b_caja,
					rd.es_nuevo_modelo,
					rd.observaciones,
					rd.serie,
					rd.numero_partida,
					rd.modelo,
					p.nombre,
					rd.id_status_ubicacion,
					rd.ubicacion_almacen,
					(SELECT
						IF( id_proveedor_producto_medida_tmp IS NULL, 0, id_proveedor_producto_medida_tmp )
						FROM ec_proveedor_producto_medidas_tmp
						WHERE id_recepcion_bodega_detalle = rd.id_recepcion_bodega_detalle
					) AS measures_id,
					(SELECT
						IF( id_producto_nuevo IS NULL, 0, id_producto_nuevo )
					FROM ec_productos_nuevos_temporal
					WHERE id_producto_nuevo = rd.id_producto_nuevo
					) AS new_product_id,
					(SELECT 
						IF( id_ubicacion_matriz_tmp IS NULL, '', id_ubicacion_matriz_tmp )
						FROM ec_proveedor_producto_ubicacion_almacen_tmp
						WHERE id_recepcion_bodega_detalle = rd.id_recepcion_bodega_detalle 
					) AS location_tmp_id
				FROM ec_recepcion_bodega_detalle rd
				LEFT JOIN ec_productos p ON p.id_productos = rd.id_producto
				WHERE 1";
		$sql .= ( $id != null ? " AND rd.id_recepcion_bodega_detalle = '{$id}'" : "" );
		$sql .= ( $recepcion_id != null ? " AND rd.id_recepcion_bodega = '{$recepcion_id}'" : "" );
		$exc = $link->query( $sql ) or die( "Error al obtener datos de detalle de recepcion : " . $link->error );
		//die( $sql );
		while( $r = $exc->fetch_assoc() ){
			array_push($resp, $r);
		}
		return json_encode( $resp );
	}

	function seekProductsLocations( $link, $txt ){
		$resp = '';
		$sql = "SELECT 
					p.id_productos,
					CONCAT( p.nombre, ' <b>MODELO : ', pp.clave_proveedor , '</b>' ) AS nombre,
					ap.inventario,
					SUM( rd.piezas_sueltas_recibidas + ( rd.piezas_por_caja * rd.cajas_recibidas ) ),
					rd.id_status_ubicacion,
					p.ubicacion_almacen,
					pp.id_proveedor_producto
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p ON p.id_productos = pp.id_producto
				LEFT JOIN ec_almacen_producto ap ON ap.id_producto = p.id_productos
				AND ap.id_almacen = 1
				LEFT JOIN ec_recepcion_bodega_detalle rd ON p.id_productos = rd.id_producto
				WHERE p.orden_lista LIKE '%{$txt}%'
				OR p.clave LIKE '%{$txt}%'
				OR ( ";
		$words = explode(' ', $txt);
		foreach ($words as $key => $word ) {
			$sql .= ( $key > 0 ? " AND " : "") . " p.nombre LIKE '%{$word}%'";
		}
		$sql .= " )
				GROUP BY pp.id_proveedor_producto
				ORDER BY p.orden_lista";
		//return $sql;

		$exc = $link->query( $sql ) or die( "Error al consultar productos recibidos : " . $link->error );
		while( $r = $exc->fetch_row() ){
			$resp .= "<div class=\"group_card\" onclick=\"setProductLocation('{$r['0']}~{$r['1']}~{$r['2']}~{$r['3']}~{$r['4']}~{$r['5']}~{$r['6']}');\">{$r[1]}</div>";
		}
		return $resp;
	}

	/*function save_temporal_location(){

	}*/
	
	function changeProductLocation( $type_row, $location_id, $product_id, $product_provider_id, $reception_detail_id, $new_status, 
				$letter_location_since, $number_location_since, $aisle_from, $level_from, 
				$letter_location_to, $number_location_to, $aisle_to, $level_to, $link ){
		/*$sql = "UPDATE ec_productos SET ubicacion_almacen = '{$location}' WHERE id_productos = '{$product_id}'";
		$stm = $link->query( $sql ) or die( "Error al actualizar la ubicación del producto : " . $link->error );*/
		$sql = "";
		$tmp = "";
		if( $type_row == '_source' ){
			$tmp = "_tmp";
		}
		if( $location_id != 'no_location' && $location_id != 'new_location' && $location_id != 0 ){
			
			$second_field = "id_proveedor_producto={$product_provider_id}"; 
			if( $product_provider_id == '' ){
				$product_provider_id = 'null';
				$second_field = "id_recepcion_bodega_detalle=" . ( $reception_detail_id == '' ? 'NULL' : $reception_detail_id );
			}

			$sql = "UPDATE ec_proveedor_producto_ubicacion_almacen{$tmp} SET
					/*3*/id_producto='{$product_id}',
					/*4*/{$second_field},
					/*5*/letra_ubicacion_desde='{$letter_location_since}',
					/*6*/numero_ubicacion_desde='{$number_location_since}',
					/*7*/letra_ubicacion_hasta='{$letter_location_to}',
					/*8*/numero_ubicacion_hasta='{$number_location_to}',
					/*9*/pasillo_desde='{$aisle_from}',
					/*10*/pasillo_hasta='{$aisle_to}',
					/*11*/altura_desde='{$level_from}',
					/*12*/altura_hasta='{$level_to}'
				WHERE id_ubicacion_matriz{$tmp} = {$location_id}";
		}else if( $location_id == 'new_location' ) {
			
			$second_field = "id_proveedor_producto";
				$second_field_value = $product_provider_id; 
			if( $product_provider_id == '' ){
				$product_provider_id = 'null';
				$second_field = "id_recepcion_bodega_detalle"; 
				$second_field_value = ( $reception_detail_id == '' ? 'NULL' : $reception_detail_id );
			}
			$sql = "INSERT INTO ec_proveedor_producto_ubicacion_almacen{$tmp} (
						 /*1*/id_ubicacion_matriz{$tmp},
						/*2*/id_almacen,
						/*3*/id_producto,
						/*4*/{$second_field},
						/*5*/letra_ubicacion_desde,
						/*6*/numero_ubicacion_desde,
						/*7*/letra_ubicacion_hasta,
						/*8*/numero_ubicacion_hasta,
						/*9*/pasillo_desde,
						/*10*/pasillo_hasta,
						/*11*/altura_desde,
						/*12*/altura_hasta )
					 VALUES ( 
						/*id_ubicacion_matriz*/NULL,
						/*id_almacen*/'1',
						/*id_producto*/'{$product_id}',
						/*id_proveedor_producto*/{$second_field_value},
						/*letra_ubicacion_desde*/'{$letter_location_since}',
						/*numero_ubicacion_desde*/'{$number_location_since}',
						/*letra_ubicacion_hasta*/'{$letter_location_to}',
						/*numero_ubicacion_hasta*/'{$number_location_to}',
						/*pasillo_desde*/'{$aisle_from}',
						/*pasillo_hasta*/'{$aisle_to}',
						/*altura_desde*/'{$level_from}',
						/*altura_hasta*/'{$level_to}' )";
		}
		if( $sql != '' ){
			$stm = $link->query( $sql ) or die( "Error al actualizar / insertar ubicacion : {$link->error} { $sql }" );
		}
		if( $new_status == 'new_location' && $type == '_seeker' ){
			$new_status = 3;
			$sql = "UPDATE ec_recepcion_bodega_detalle SET ubicacion_almacen = '{$location}', id_status_ubicacion = '{$new_status}' 
						WHERE id_proveedor_producto = '{$product_provider_id}'";
			$stm = $link->query( $sql ) or die( "Error al actualizar la ubicación del producto : {$link->error} {$sql}");
		}
		return 'ok|' . ( $link->insert_id );
	}

	function changeInvoicesStatus( $data, $link ){
		$dat = explode( '|~|', $data );
		foreach ($dat as $key => $value) {
			$val = explode('~', $value );
			$sql = "UPDATE ec_recepcion_bodega SET id_recepcion_bodega_status = '{$val[1]}' WHERE id_recepcion_bodega = '{$val[0]}'";
			$stm = $link->query( $sql ) or die( "Error al actualizar las recepciones de bodega : " . $link->error );
		}
		return 'ok|Los cambios fueron guardados existosamente!';
	} 

	

	function getInvoiceParts( $serie, $link ){
		$resp ='<option value="">-</option>';
		$sql = "SELECT 
					rb.numero_partidas, 
					GROUP_CONCAT( rbd.numero_partida SEPARATOR ',' )
				FROM ec_recepcion_bodega rb
				LEFT JOIN  ec_recepcion_bodega_detalle rbd 
				ON rb.id_recepcion_bodega = rbd.id_recepcion_bodega
				WHERE rb.serie = '{$serie}'";
		//die($sql);
		$stm = $link->query( $sql ) or die( "Error al consultar partidas utilizadas : " . $link->error );
		$row = $stm->fetch_row();
		$parts_limit = $row[0];
		$parts = explode(',', $row[1] );
		for( $i = 1; $i <= $parts_limit; $i++ ){
			$exists = 0;
			foreach ($parts as $key => $number_part) {
				if( $number_part == $i ){
					$exists = 1;
				}
			}
			if( $exists == 0 ){
				$resp .= '<option value="' . $i . '">' . $i . '</option>';
			}
		}
		return $resp;
	}

	function validateSerie( $serie, $serie_number, $link ){
		$sql = "SELECT ";
	}

	function setBlockSession( $user, $link ){
		$sql = "INSERT INTO ec_bloques_recepcion_mercancia ( id_usuario, fecha ) VALUES ( {$user}, NOW() )";
		$stm = $link->query( $sql ) or die( "Error al insertar bloque de Recepción de Mercancía : {$link->error}" );
		$last_id = $link->insert_id;
		return "ok|{$last_id}";
	}

	function validateRemoveInvoice( $invoice_id, $block_id, $link ){
		$resp = "";
		$sql = "SELECT 
					COUNT( * ) AS products_recived
				FROM ec_recepcion_bodega_detalle
				WHERE id_bloque_recepcion = {$block_id}
				AND id_recepcion_bodega = {$invoice_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar los detalles validados" );
		$num_rows = $stm->fetch_assoc();
		if( $num_rows['products_recived'] == 0 ){
			$resp = "ok|<div class=\"group_card\"><br>
						<h5>La remision fue quitada de la recepción</h5>
						<button class=\"btn btn-success form-control\" onclick=\"close_emergent();\">
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div><br>";
		}else{
			$resp = "<div class=\"group_card\"><br>
						<h5>No se puede quitar la remision porque ya se recibieron productos</h5>
						<button class=\"btn btn-danger form-control\" onclick=\"close_emergent();\">
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>
					</div><br>";
		}
		return $resp;
	}

?>