<?php
		
	if( isset( $_GET['fl'] ) ){
		$action = $_GET['fl'];
			include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );
		$db = new Db( $link );

		switch ( $action ) {
			case 'seekProduct' :
				echo $db->seekProduct( $_GET['key'] );
			break;

			case 'makeBarcodes' :
				$product_provider_id = $_GET['product_provider_id'];
				$boxes = ( isset( $_GET['boxes_number'] ) ? $_GET['boxes_number'] : 0 );
				$packs = ( isset( $_GET['packs_number'] ) ? $_GET['packs_number'] : 0 );
				$pieces = ( isset( $_GET['pieces_number'] ) ? $_GET['pieces_number'] : 0 );
				echo $db->make_barcode( $product_provider_id, $user_id, $sucursal_id, $boxes, $packs, $pieces );
			break;

			case 'getImages' :
				echo $db->getImages( $_GET['product_provider_id'] );
			break;

			case 'getOptionsByProductId' : 
				echo $db->getOptionsByProductId( $_GET['product_id'] );
			break;
			
			default :
				die( "Permission denied!" );
			break;
		}
	}


	class Db
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}

		public function getPermissions( $user_id ){
			$sql = "SELECT 
						p.ver AS view,
						p.modificar AS edit,
						p.eliminar AS del,
						p.nuevo AS new,
						p.imprimir AS print,
						p.generar AS make
					FROM sys_permisos p
					LEFT JOIN sys_users_perfiles up
					ON up.id_perfil = p.id_perfil
					LEFT JOIN sys_users u
					ON u.tipo_perfil = p.id_perfil
					WHERE p.id_menu = 262
					AND u.id_usuario = {$user_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar los permisos de usuario : {$this->link->error} {$sql}" );
			$row = $stm->fetch_assoc();
			return $row;
		}

		public function seekProduct( $txt ){
		//busca por codigo de barras
			$sql = "SELECT 
						p.id_productos AS product_id,
						pp.id_proveedor_producto AS product_provider_id,
						pp.clave_proveedor AS provider_clue,
						pp.presentacion_caja AS pieces_per_box,
						pp.piezas_presentacion_cluces AS pieces_per_pack,
						p.nombre AS product_name,
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
						pem.es_producto_sin_etiqueta AS is_without_tag
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON p.id_productos = pp.id_producto
					LEFT JOIN ec_productos_etiquetado_maquila pem
					ON pem.id_producto = p.id_productos
					WHERE ( pp.codigo_barras_pieza_1 = '{$txt}'
					OR pp.codigo_barras_pieza_2 = '{$txt}'
					OR pp.codigo_barras_pieza_3 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
					OR pp.codigo_barras_caja_1 = '{$txt}'
					OR pp.codigo_barras_caja_2 = '{$txt}' 
					OR pp.id_proveedor_producto = '{$txt}')
					AND p.es_maquilado = 0";
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

		public function getProductProviderCatalogue( $stm ){
			//echo '|here';
			$resp = "multiProductProvider|<table class=\"table table-striped\">
						<thead class=\"header_top_0\">
							<tr>
								<th>Modelo</th>
								<th>Pzs x caja</th>
								<th>Pzs x paquete</th>
								<th>Imagen superior</th>
								<th>Imagen frontal</th>
								<th>Imagen lateral</th>
								<th>Seleccionar</th>
							</tr>
						</thead>
						<tbody>";
			while ( $row = $stm->fetch_assoc() ) {
				$resp .= "<tr>
							<td class=\"hidden\" style=\"vertical-align : middle ;\">{$row['product_provider_id']}</td>
							<td style=\"vertical-align : middle ;\">{$row['provider_clue']}</td>
							<td style=\"vertical-align : middle ;\">{$row['pieces_per_box']}</td>
							<td style=\"vertical-align : middle ;\">{$row['pieces_per_pack']}</td>";
				$tmp = $this->getImages( $row['product_provider_id'], 1 );
				$resp .= $tmp == "" ? "<td colspan=\"3\" class=\"text-center\">Sin imágenes</td>" : $tmp;
				$resp .= "<td class=\"text-center\" style=\"vertical-align : middle ;\">
							<button 
								type=\"button\" 
								class=\"btn btn-success form-control\"
								onclick=\"seek_product( 'intro', '{$row['product_provider_id']}' );\"
								>
								<i class=\"icon-ok-circle\">Seleccionar</i>
							</button>
						</td>";
				$resp .= "</tr>";
			}
			$resp .= "</table>";

			return $resp;
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

		public function getImages( $product_provider_id, $preview = '' ){
			//echo 'here';
			$resp = "";

			$sql = "SELECT 
						ppm.imagen_paquete_superior AS img_1,
						ppm.imagen_paquete_frontal AS img_2,
						ppm.imagen_paquete_lateral AS img_3,
						ppm.largo_paquete AS pack_lenght,
						ppm.ancho_paquete AS pack_width,
						ppm.alto_paquete AS pack_height,
						p.nombre AS product_name,
						ppm.largo_caja AS box_length,
						ppm.ancho_caja AS box_width,
						ppm.alto_caja AS box_height
					FROM ec_proveedor_producto_medidas ppm
					LEFT JOIN ec_bolsas_paquetes bp
					ON bp.id_bolsa_paquete = ppm.id_bolsa_paquete
					LEFT JOIN ec_productos p
					ON p.id_productos = bp.id_producto_relacionado
					WHERE ppm.id_proveedor_producto = {$product_provider_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar la medidas del proveedor producto : {$this->link->error}" );
	//	die ($sql);
				$sql = "";
			if( $preview == '' ){
				$bag = "";
				$box = "";
				while( $row = $stm->fetch_assoc() ){
					$bag = $row['product_name'];
					$box = "<tr>
								<td>{$row['box_length']}</td>
								<td>{$row['box_width']}</td>
								<td>{$row['box_height']}</td>
							<tr>";
					$resp .= "<tr>
								<td>{$row['pack_lenght']}</td>
								<td>{$row['pack_width']}</td>
								<td>{$row['pack_height']}</td>
							</tr>

							<tr>
								<td colspan=\"3\">
									<div class=\"row\">
										<div class=\"col-sm-4 text-center\">
											<img src=\"../../../../files/packs_img/{$row['img_1']}\" width=\"90%\">
											<p>Imagen superior</p>
										</div>
										<div class=\"col-sm-4 text-center\">
											<img src=\"../../../../files/packs_img/{$row['img_2']}\" width=\"90%\">
											<p>Imagen frontal</p>
										</div>
										<div class=\"col-sm-4 text-center\">
											<img src=\"../../../../files/packs_img/{$row['img_3']}\" width=\"90%\">
											<p>Imagen lateral</p>
										</div>
									</div>
								</td>
							</tr>";
				}
				return "{$resp}|{$bag}|{$box}";
			}else{
				while( $row = $stm->fetch_assoc() ){
					$resp .= "<td colspan=\"3\">
								<div class=\"row\">
									<div class=\"col-sm-4 text-center\">
										<img src=\"../../../../files/packs_img/{$row['img_1']}\" width=\"90%\">
										<p>Imagen superior</p>
									</div>
									<div class=\"col-sm-4 text-center\">
										<img src=\"../../../../files/packs_img/{$row['img_2']}\" width=\"90%\">
										<p>Imagen frontal</p>
									</div>
									<div class=\"col-sm-4 text-center\">
										<img src=\"../../../../files/packs_img/{$row['img_3']}\" width=\"90%\">
										<p>Imagen lateral</p>
									</div>
								</div>
							</td>";
				}
			}
			return $resp;
		}



		public function make_barcode( $product_provider_id, $user_id, $store_id, $boxes = 0, $packs = 0, $pieces = 0  ){
			
			//$file_name = "2022_12_19_13_22_47_63a0ba07120a5.txt";
			//die( $file_name );
			//include( '../../../../../conectMin.php' );
			$resp = "\n";
			$archivo_path = "../../../../../conexion_inicial.txt";
			if(file_exists($archivo_path)){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
			    $config=explode("<>",$line);
			    $tmp=explode("~",$config[2]);
			    $ruta_or=$tmp[0];
			    $ruta_des=$tmp[1];
			}else{
				die("No hay archivo de configuración!!!");
			}
		//busca datos del usuario
			$sql = "SELECT 
						CONCAT( nombre, ' ', apellido_paterno, ' ', apellido_materno ) AS name
					FROM sys_users WHERE id_usuario = {$user_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre de usuario : {$this->link->error} {$sql}" );
			$row = $stm->fetch_assoc();
			$user = $row['name'];
		//busca datos de la sucursal
			$sql = "SELECT 
						nombre AS store_name,
						prefijo AS store_prefix
					FROM sys_sucursales WHERE id_sucursal = {$store_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el nombre de la sucursal : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$store = $row['store_name'];
			$store_prefix = $row['store_prefix'];
			$sql = "SELECT
						ax.product_provider_id,
						( ax.boxes_counter + 1 ) AS boxes_counter,
						( ax.packs_counter + 1 ) AS packs_counter,
						ax.prefix,
						ax.order_list,
						ax.product_name,
						ax.pack_barcode,
						ax.tag_date,
						ax.box_barcode,
						ax.piece_barcode,
						IF( ppua.id_ubicacion_matriz IS NULL,
							'Sin Ubicacion',
							CONCAT( 'DE ', ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde,
							' - ',  ppua.letra_ubicacion_hasta, ppua.numero_ubicacion_hasta,
							IF( ppua.pasillo_desde <> '', CONCAT( ', PASILLO ', ppua.pasillo_desde, ' - ', ppua.pasillo_hasta  ), '' ),
							IF( ppua.altura_desde <> '', CONCAT( ', ALTURA ', ppua.altura_desde, ' - ', ppua.altura_hasta  ), '' )
						)
					 ) AS product_location,
						ax.piece_unit,
						ax.product_id,/*agrego a partir de aqui*/
						IF( ax.pieces_per_pack = 0, CONCAT( 'CJ ', ax.pieces_per_box ), CONCAT( 'PQ ', ax.pieces_per_pack) ) AS presentation,
						( ( ax.pieces_per_box * {$boxes} ) + ( ax.pieces_per_pack * {$packs} ) ) AS pieces_pack_and_boxes
					FROM(
						SELECT 
							pp.id_proveedor_producto AS product_provider_id,
							pp.contador_cajas AS boxes_counter,
							pp.contador_paquetes AS packs_counter,
							pp.prefijo_codigos_unicos AS prefix,
							p.orden_lista AS order_list,
							CONCAT( pp.clave_proveedor, ' ', p.nombre ) AS product_name,
							pp.codigo_barras_presentacion_cluces_1 AS pack_barcode,
							current_date() AS tag_date,
							pp.codigo_barras_caja_1 AS box_barcode,
							pp.codigo_barras_pieza_1 AS piece_barcode,
							pp.unidad_medida_pieza AS piece_unit,
							p.id_productos AS product_id,
							pp.piezas_presentacion_cluces AS pieces_per_pack,
							pp.presentacion_caja AS pieces_per_box
						FROM ec_proveedor_producto pp 
						LEFT JOIN ec_productos p
						ON pp.id_producto = p.id_productos
						WHERE pp.id_proveedor_producto = {$product_provider_id}
					)ax
					LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
					ON ppua.id_proveedor_producto = ax.product_provider_id
					GROUP BY ax.product_provider_id";
			$stm = $this->link->query( $sql ) or die( "Errror al consultar los consecutivos de codigos de barras : {$this->link->error}");
			$row = $stm->fetch_assoc();
		//cajas
			$boxes_limit = $row['boxes_counter'] + $boxes;
			$product_name = $this->part_word( $row['product_name'] );			
			for( $i = $row['boxes_counter']; $i < $boxes_limit; $i++ ){
				$unic = $i;
				if( $unic <= 9 ){
					$unic = "00{$unic}";
				}else if( $unic <=99 ){
					$unic = "0{$unic}";
				}
				$barcode = $row['box_barcode'] . " " . $row['prefix'] . $unic;
				$box_barcode = array( 'barcode'=>$barcode, 'order_list'=>$row['order_list'], 'tag_date'=>$row['tag_date'], 
					'product_location'=>$row['product_location'], 'tag_type'=>"CAJA", 'store'=>$store, 'store_prefix' =>$store_prefix, 
					'user_name'=>$user, 'product_name_1'=>$product_name[0], 'product_name_2'=>$product_name[1] );
				$resp .= $this->make_barcode_file( $box_barcode, $store_id, $user_id, $system_type[0], 1 );
			//actualiza el contador de paquetes
				$sql = "UPDATE ec_proveedor_producto SET contador_cajas = {$i} WHERE id_proveedor_producto =  {$product_provider_id}";
				$upd = $this->link->query( $sql ) or die( "Error al actualizar el contador de cajas : {$this->link->error}" );
			}

		//paquetes
			$packs_limit = $row['packs_counter'] + $packs;
			for( $i = $row['packs_counter']; $i < $packs_limit; $i++ ){

				$unic = $i;
				if( $unic <= 9 ){
					$unic = "000{$unic}";
				}else if( $unic <= 99 ){
					$unic = "00{$unic}";
				}else if( $unic <= 999 ){
					$unic = "0{$unic}";
				}
				$barcode = $row['pack_barcode'] . " " . $row['prefix'] . $unic;
				$pack_barcode = array( 'barcode'=>$barcode, 'order_list'=>$row['order_list'], 'tag_date'=>$row['tag_date'], 
					'product_location'=>$row['product_location'], 'tag_type'=>"PAQUETE", 'store'=>$store, 'store_prefix' =>$store_prefix,
					'user_name'=>$user, 'product_name_1'=>$product_name[0], 'product_name_2'=>$product_name[1] );
				$resp .= $this->make_barcode_file( $pack_barcode, $store_id, $user_id, $system_type[0], 2 );
			//echo 'here';
		//actualiza el contador de paquetes
				$sql = "UPDATE ec_proveedor_producto SET contador_paquetes = {$i} WHERE id_proveedor_producto =  {$product_provider_id}";
				$upd = $this->link->query( $sql ) or die( "Error al actualizar el contador de paquetes : {$this->link->error}" );
			}
		//piezas
			//for( $i = 0; $i < $pieces; $i++ ){
			//die( 'piezas : ' . $pieces );
			if( $pieces > 0 ){
				$pieces = str_replace('.00', '', $pieces );
				$piece_barcode = array( 'barcode'=>$row['piece_barcode'], 'order_list'=>$row['order_list'], 'tag_date'=>$row['tag_date'], 
					'product_location'=>$row['product_location'], 'tag_type'=>"{$pieces}+  +   +   +   +   +", 'store'=>$store, 'store_prefix' =>$store_prefix, 
					'user_name'=>$user, 'product_name_1'=>$product_name[0], 'product_name_2'=>$product_name[1], 'piece_unit'=>$row['piece_unit'], 'presentation'=>$row['presentation'] );
				$resp .= $this->make_barcode_file( $piece_barcode, $store_id, $user_id, $system_type[0], 3 );
			}

			$file_name = date('Y_m_d_H_i_s_') . uniqid() . '.txt';
		//genera archivo
			$fh = fopen("../../../../../cache/ticket/{$file_name}", 'w') or die("Se produjo un error al crear el archivo");
			fwrite($fh, $resp) or die("No se pudo escribir en el archivo");
			fclose($fh);

			$sql = "SELECT 
						id_sucursal
					FROM sys_sucursales
					WHERE acceso = 1";
			$stm = $this->link->query( $sql ) or die( "Error al consultar el tipo de sistema : {$this->link->error}" );
			$system_type = $stm->fetch_row();
		//genera registro de descarga
			if( $system_type[0] == -1 ){
		//die( 'Here' );
			/*cambio Oscar 2024-02-01*/
				$ruta_or .= '/cache/ticket/';
				$ruta_or = str_replace( '//', '/', $ruta_or );
			/*fin de cambio Oscar 2024-02-01*/
				$sql_arch="INSERT INTO sys_archivos_descarga SET 
						id_archivo=null,
						tipo_archivo='txt',
						nombre_archivo='{$file_name}',
						ruta_origen='{$ruta_or}',
						ruta_destino='cache/ticket/',/*cambio Oscar 2024-02-01*/
						id_sucursal=(SELECT sucursal_impresion_local FROM ec_configuracion_sucursal WHERE id_sucursal='$store_id'),
						id_usuario='$user_id',
						observaciones=''";
				$inserta_reg_arch=$this->link->query( $sql_arch )or die( "Error al guardar el registro de sincronización del ticket de reimpresión!!!\n\n". $this->link->error . "\n\n" . $sql_arch );
			}
			return 'ok|Impresion Generada exitosamente!';
		}

		public function make_barcode_file( $data, $store_id, $user_id, $system_type, $type_id ){
//echo " type : {$type_id}";

			$tag = $this->getTagTemplate( $data, $store_id, $type_id );
			$resp = $tag;
			return $resp;

		}

		public function part_word( $txt ){
			$size = strlen( $txt );
			$half = round( $size / 2 );
			$words = explode(' ', $txt );
			$resp = array( '','');
			$chars_counter = 0;
			$middle_word = "";
			foreach ($words as $key => $word) {
				$is_middle = 0;
				if( $key > 0 ){
					$chars_counter ++;//espacio
					if( $chars_counter == $half ){
						$is_middle = 1;
					}
				}
				for( $i = 0; $i < strlen( $word ); $i ++ ){
					$chars_counter ++;//palabras
					if( $chars_counter == $half || $is_middle == 1){
						$middle_word = $word;
						$is_middle = 1;
					}
				}
				if( $middle_word == '' ){
					$resp[0] .= ( $resp[0] != '' ? ' ' : '' );
					$resp[0] .= $word;
				}else if( $middle_word != '' && $is_middle == 0 ){
					$resp[1] .= ( $resp[1] != '' ? ' ' : '' );
					$resp[1] .= $word;
				}
				$is_middle = 0;
			}
			if( strlen( "{$resp[0]} {$middle_word}" ) < strlen( "{$middle_word} {$resp[1]}" )  ){//asigna palabra intermedia a primera parte
				$resp[0] = "{$resp[0]} {$middle_word}";
			}else{//asigna palabra intermedia a segunda parte
				$resp[1] = "{$middle_word} {$resp[1]}";
			}
			return $resp;
		}


		public function getOptionsByProductId( $product_id ){
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

		public function getTagTemplate( $data, $store_id, $type_id ){
			$sql = "SELECT
						IF( spe.tipo_codigo_plantilla = 'EPL',  pe.codigo_epl, pe.codigo_zpl ) AS template
					FROM sys_sucursales_plantillas_etiquetas spe
					LEFT JOIN sys_plantillas_etiquetas pe
					ON pe.id_plantilla_etiquetas = spe.id_plantilla
					WHERE spe.id_sucursal = {$store_id}
					AND  spe.id_plantilla = {$type_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar base de etiqueta : {$this->link->error} {$sql}" );
			if( $stm->num_rows < 0 ){
				die( "No existe plantilla para la etiqueta!" );
			}else{
				$row = $stm->fetch_assoc();

				/*$row['template'] = str_replace('$_barcode', $data['barcode'], $row['template'] );
				$row['template'] = str_replace('$_product_location', $data['product_location'], $row['template'] );
				$row['template'] = str_replace('$_tag_date', $data['tag_date'], $row['template'] );
				$row['template'] = str_replace('$_user_name', $data['user_name'], $row['template'] );
				$row['template'] = str_replace('$_store', $data['store'], $row['template'] );
				$row['template'] = str_replace('$_tag_type', $data['tag_type'], $row['template'] );
				$row['template'] = str_replace('$_product_name_1', $data['product_name_1'], $row['template'] );
				$row['template'] = str_replace('$_product_name_2', $data['product_name_2'], $row['template'] );
				$row['template'] = str_replace('$_order_list', $data['order_list'], $row['template'] );
				if( isset( $data['piece_unit'] ) ){
					$row['template'] = str_replace('$_piece_unit', $data['piece_unit'], $row['template'] );
				}*/
				$row['template'] = str_replace('$_barcode', $data['barcode'], $row['template'] );
				$row['template'] = str_replace('$_product_location', $data['product_location'], $row['template'] );
				$row['template'] = str_replace('$_tag_date', $data['tag_date'], $row['template'] );
				$row['template'] = str_replace('$_user_name', $data['user_name'], $row['template'] );
				
				if( isset( $data['presentation'] ) ){
					$row['template'] = str_replace('$_presentation', $data['presentation'], $row['template'] );
				}
				
				if ( isset( $data['store_prefix'] ) ){
					$row['template'] = str_replace('$_store_prefix', $data['store_prefix'], $row['template'] );
				} 
				if ( isset( $data['store'] ) ){
					$row['template'] = str_replace('$_store', $data['store'], $row['template'] );
				}
				//if( isset( $_data['prints_number'] ) ){
					//die( 'here' );
					$row['template'] = str_replace('$_prints_number', $data['prints_number'], $row['template'] );
				//}
				
				$row['template'] = str_replace('$_tag_type', $data['tag_type'], $row['template'] );
				$row['template'] = str_replace('$_product_name_1', $data['product_name_1'], $row['template'] );
				$row['template'] = str_replace('$_product_name_2', $data['product_name_2'], $row['template'] );
				$row['template'] = str_replace('$_order_list', $data['order_list'], $row['template'] );
				if( isset( $data['piece_unit'] ) ){
					$row['template'] = str_replace('$_piece_unit', $data['piece_unit'], $row['template'] );
				}
				if( isset( $data['maquile_unit'] ) ){
					$row['template'] = str_replace('$_maquile_unit', $data['maquile_unit'], $row['template'] );
				}
				return $row['template'] . "\n";
			}
		}

	}
	
?>