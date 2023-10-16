<?php
	if( isset( $_GET['fl'] ) ){
		include( '../../../../../config.inc.php' );
		include( '../../../../../conect.php' );
		include( '../../../../../conexionMysqli.php' );

		$action = $_GET['fl'];

		switch ( $action ) {
			case 'seekProduct' :
				echo seekProduct( $_GET['key'], $link );
			break;

			case 'generateBarcodes' :
				IF( !isset( $_GET['boxes'] ) ){
					$_GET['boxes'] = null;
				}
				IF( !isset( $_GET['packs'] ) ){
					$_GET['packs'] = null;
				}
				IF( !isset( $_GET['pieces'] ) ){
					$_GET['pieces'] = null;
				}
				echo generateBarcodes( $_GET['boxes'], $_GET['packs'], $_GET['pieces'], $_GET['type'], $link );
			break;

			case 'getConfigForm' :
				echo getConfigForm( $_GET['field'], null, $link );
			break;

			case 'saveSystemConf' : 
				echo saveSystemConf( $_GET['field'], $_GET['value'], $link );
			break;

			case 'validateBarcodesSeriesUpdate' : 
				echo validateBarcodesSeriesUpdate( $link, $_GET['since_content'] );
			break;

			case 'updateBarcodesPrefix': 
				echo updateBarcodesPrefix( $link );
			break;

			case 'updateBarcodesPrefixSinceContent': 
				echo updateBarcodesPrefix( $link, null, 1 );
			break;

			case 'download_csv' :	
					$name = $_GET['name'];
					$name_download = $_GET['output_name'];
				//genera descarga
					header('Content-Type: aplication/octect-stream');
					header('Content-Transfer-Encoding: Binary');
					header('Content-Disposition: attachment; filename="' . $name_download . '"');
					$file = fopen("../tmp_files/{$name}", "r");
					while( !feof($file) ) {
						$data = str_replace('~salto~', "\n", fgets($file) );
						echo( utf8_decode( $data ) );
					}
					fclose($file);
					chmod( "../tmp_files/{$name}", 0777 );
					unlink( "../tmp_files/{$name}" );
					die('');
					//echo( utf8_decode( $data ) );
			break;

			default:
				die( "Permission Denied!" );
			break;
		}
	}

	function seekProduct( $key, $link ){
		$resp = "";
		$array_key = explode( ' ', $key );
		$total_counter = 0;

		$sql = "SELECT 
					p.id_productos AS product_id,
					p.nombre AS name,
					GROUP_CONCAT( 
							CONCAT( pp.id_proveedor_producto, '~', 
								CONCAT(pp.clave_proveedor, ' (caja : ' , pp.presentacion_caja, ' pzas, paquete : ', pp.piezas_presentacion_cluces , ' pzas)'  ) 
							) SEPARATOR '|' 
					) AS product_providers
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p
				ON pp.id_producto = p.id_productos
				WHERE pp.id_proveedor_producto = '{$key}'
				AND pp.id_proveedor_producto IS NOT NULL";
//echo $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar coincidencias de buscador por id proveedor_producto : {$link->error}" );
		//if( $stm->num_rows > 0 ){	
			//echo 'here';
			while ( $row = $stm->fetch_assoc() ){
				$resp .= "<div 
								class=\"option_result\"
								id=\"seeker_response_{$total_counter}\"
								onclick=\"setProduct( this, '{$row['product_id']}', '{$row['product_providers']}' );\"
						>";
					$resp .= $row['name'];
				$resp .= "</div>";
				if( $row['product_id'] != null ){
					return $resp;
				}
				$total_counter ++;
			}
			//return $resp;
		//}else{
			$condition = " OR (";
			foreach ($array_key as $counter => $word) {
				$condition .= ( $counter > 0 ? ' AND' : '' );
				$condition .= " p.nombre LIKE '%{$word}%'"; 
			}
			$condition .= " )";
			$sql = "SELECT 
						p.id_productos AS product_id,
						p.nombre AS name,
						GROUP_CONCAT( 
								CONCAT( pp.id_proveedor_producto, '~', 
									CONCAT(pp.clave_proveedor, ' (caja : ' , pp.presentacion_caja, ' pzas, paquete : ', pp.piezas_presentacion_cluces , ' pzas)'  ) 
								) SEPARATOR '|' 
						) AS product_providers
					FROM ec_productos p
					LEFT JOIN ec_proveedor_producto pp
					ON pp.id_producto = p.id_productos
					WHERE p.id_productos > 0
					AND p.es_maquilado = 0
					AND p.muestra_paleta = 0
					AND p.orden_lista NOT IN ( 18000 )
					AND p.habilitado = 1
					AND ( ( p.clave LIKE '%{$key}%'
					OR p.orden_lista LIKE '%{$key}%'
					OR pp.clave_proveedor LIKE '%{$key}%')
					{$condition} )
					GROUP BY p.id_productos";
	//die( $sql );
			$stm = $link->query( $sql ) or die( "Error al consultar coincidencias de buscador : {$link->error}" );
			if( $stm->num_rows <= 0 ){
				return '<div>No hay concidencias, verifique y vuelva a intentar!</div>';
			}else{

				while ( $row = $stm->fetch_assoc() ){
					$resp .= "<div 
									class=\"option_result\"
									id=\"seeker_response_{$total_counter}\"
									onclick=\"setProduct( this, '{$row['product_id']}', '{$row['product_providers']}' );\"
							>";
						$resp .= $row['name'];
					$resp .= "</div>";
					$total_counter ++;
				}
			}
			return $resp;
		//}
	}

	function part_word( $txt ){
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

	function generateBarcodes( $boxes = null, $packs = null, $pieces = null, $type, $link ){
		$resp_box = "";
	//códigos de caja
		$box_file = "";
		if( $boxes != null ){ 
			$box_file = "box_" . date('Y-m-d H-i-s') . ".txt";
			$resp_box = "Codigo de caja,Tipo,Orden de lista,Descripcion 1,Descripcion 2,Cantidad de etiquetas";
			$box = explode( '|', $boxes );
			foreach ($box as $key => $box_dtl) {
				//die( $box_dtl );
				$box_detail = explode( '~', $box_dtl );//consulta el código de barras y contador
				$sql = "SELECT 
							pp.contador_cajas AS boxes_counter,
							pp.codigo_barras_caja_1 AS barcode,
							pp.prefijo_codigos_unicos AS prefix,
							p.orden_lista AS list_order,
							CONCAT( pp.clave_proveedor, ' ', p.nombre ) AS label_name
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_productos p
						ON p.id_productos = pp.id_producto
						WHERE pp.id_proveedor_producto = {$box_detail[0]}";
						//die( $sql );
				$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras caja : {$link->error} {$sql}" );
				$row = $stm->fetch_assoc();
				$initital_counter = ( (int)$row['boxes_counter'] + 1 );
				$final_counter = ( (int)$row['boxes_counter'] + (int)$box_detail[1] );
			//reseteo del contador
				if( $final_counter >= 1000 ){
					updateBarcodesPrefix( $link, 1 );
					$sql = "SELECT 
							pp.contador_cajas AS boxes_counter,
							pp.codigo_barras_caja_1 AS barcode,
							pp.prefijo_codigos_unicos AS prefix,
							p.orden_lista AS list_order,
							CONCAT( pp.clave_proveedor, ' ', p.nombre ) AS label_name
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_productos p
						ON p.id_productos = pp.id_producto
						WHERE pp.id_proveedor_producto = {$box_detail[0]}";
						//die( $sql );
					$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras caja : {$link->error} {$sql}" );
					$row = $stm->fetch_assoc();
					$initital_counter = ( (int)$row['boxes_counter'] + 1 );
					$final_counter = ( (int)$row['boxes_counter'] + (int)$box_detail[1] );
				}

				$name[0] = $row['label_name'];
				$name[1] = '';
				if( strlen( $row['label_name'] ) > 32 ){
					$name = part_word( $row['label_name'] );
				}
				if( $type == 'unique' ){
					for ($i = $initital_counter; $i <= $final_counter; $i++) { 
						$unic = $i;
						if( $unic <= 9 ){
							$unic = "00{$unic}";
						}else if( $unic <=99 ){
							$unic = "0{$unic}";
						}
						$resp_box .= "~salto~{$row['barcode']} {$row['prefix']}" . $unic . ",CAJA,{$row['list_order']},{$name[0]},{$name[1]},1";
						
					}
					$sql = "UPDATE ec_proveedor_producto 
								SET contador_cajas = {$final_counter}
							WHERE id_proveedor_producto = {$box_detail[0]}";
					$stm2 = $link->query( $sql ) or die( "Error al actualizar el contador de cajas : {$link->error}" );
				}else{
					$resp_box .= "~salto~{$row['barcode']},CAJA,{$row['list_order']},{$name[0]},{$name[1]},{$box_detail[1]}";
				}
			}
			$file = fopen("../tmp_files/{$box_file}", "w");
			fwrite($file, $resp_box);
			fclose($file);
		}
	//códigos de paquetes
		$resp_pack = "";
		$pack_file = "";
		if( $packs != null ){
			
			$pack_file = "pack_" . date('Y-m-d H-i-s') . ".txt";
			$resp_pack = "Codigo de paquete,Tipo,Orden de lista,Descripcion 1,Descripcion 2,Cantidad de etiquetas";
			
			$pack = explode( '|', $packs );
			foreach ($pack as $key => $pack_dtl) {
				$pack_detail = explode( '~', $pack_dtl );//consulta el código de barras y contador
				$sql = "SELECT 
							pp.contador_paquetes AS packs_counter,
							pp.codigo_barras_presentacion_cluces_1 AS barcode,
							pp.prefijo_codigos_unicos AS prefix,
							p.orden_lista AS list_order,
							CONCAT( pp.clave_proveedor, ' ', p.nombre ) AS label_name
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_productos p
						ON p.id_productos = pp.id_producto
						WHERE pp.id_proveedor_producto = {$pack_detail[0]}";
				$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras paquete : {$link->error}" );
				$row = $stm->fetch_assoc();
				$initital_counter = ( (int)$row['packs_counter'] + 1 );
				$final_counter = ( (int)$row['packs_counter'] + (int)$pack_detail[1] );
			//reseteo del contador
				if( $final_counter >= 10000 ){
					updateBarcodesPrefix( $link, 1 );
					$sql = "SELECT 
								pp.contador_paquetes AS packs_counter,
								pp.codigo_barras_presentacion_cluces_1 AS barcode,
								pp.prefijo_codigos_unicos AS prefix,
								p.orden_lista AS list_order,
								CONCAT( pp.clave_proveedor, ' ', p.nombre ) AS label_name
							FROM ec_proveedor_producto pp
							LEFT JOIN ec_productos p
							ON p.id_productos = pp.id_producto
							WHERE pp.id_proveedor_producto = {$pack_detail[0]}";
					$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras paquete : {$link->error}" );
					$row = $stm->fetch_assoc();
					$initital_counter = ( (int)$row['packs_counter'] + 1 );
					$final_counter = ( (int)$row['packs_counter'] + (int)$pack_detail[1] );
				}

				$name[0] = $row['label_name'];
				$name[1] = '';
				if( strlen( $row['label_name'] ) > 32 ){
					$name = part_word( $row['label_name'] );
				}
				if( $type == 'unique' ){
					for ($i = $initital_counter; $i <= $final_counter; $i++) { 
						$unic = $i;
						if( $unic <= 9 ){
							$unic = "000{$unic}";
						}else if( $unic <= 99 ){
							$unic = "00{$unic}";
						}else if( $unic <= 999 ){
							$unic = "0{$unic}";
						}
						$resp_pack .= "~salto~{$row['barcode']} {$row['prefix']}" . $unic . ",PAQUETE,{$row['list_order']},{$name[0]},{$name[1]},1";
						
					}
					$sql = "UPDATE ec_proveedor_producto 
								SET contador_paquetes = {$final_counter}
							WHERE id_proveedor_producto = {$pack_detail[0]}";
					$stm2 = $link->query( $sql ) or die( "Error al actualizar el contador de cajas : {$link->error}" );
				}else{
					$resp_pack .= "~salto~{$row['barcode']},PAQUETE,{$row['list_order']},{$name[0]},{$name[1]},{$pack_detail[1]}";
				}
			}
			$file = fopen("../tmp_files/{$pack_file}", "w");
			fwrite($file, $resp_pack);
			fclose($file);

		}
		$resp_piece = "";
		$piece_file = "";
	//códigos de piezas
		if( $pieces != null ){
			$piece_file = "piece_" . date('Y-m-d H-i-s') . ".txt";
			$resp_piece = "Código de pieza, cantidad de etiquetas";
			$piece = explode( '|', $pieces );
			foreach ($piece as $key => $piece_dtl) {
				$piece_detail = explode( '~', $piece_dtl );//consulta el código de barras y contador
				$sql = "SELECT 
							pp.codigo_barras_pieza_1 AS barcode
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_productos p
						ON p.id_productos = pp.id_producto
						WHERE pp.id_proveedor_producto = {$piece_detail[0]}";
				$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras paquete : {$link->error}" );
				$row = $stm->fetch_assoc();

				$resp_piece .= "~salto~{$row['barcode']},{$piece_detail[1]}";
				
			}
			$file = fopen("../tmp_files/{$piece_file}", "w");
			fwrite($file, $resp_piece);
			fclose($file);
		}

	//códigos de piezas (2)
		if( $pieces != null ){
			$piece_file_2 = "piece_" . date('Y-m-d H-i-s') . "_2.txt";
			$resp_piece_2 = "Nombre 1,Nombre 2,Código de pieza,Cantidad de etiquetas";
			$piece = explode( '|', $pieces );
			foreach ($piece as $key => $piece_dtl) {
				$piece_detail = explode( '~', $piece_dtl );//consulta el código de barras y contador
				$sql = "SELECT 
							CONCAT( p.orden_lista, ' ', p.nombre ) as label_name,
							pp.codigo_barras_pieza_1 AS barcode
						FROM ec_proveedor_producto pp
						LEFT JOIN ec_productos p
						ON p.id_productos = pp.id_producto
						WHERE pp.id_proveedor_producto = {$piece_detail[0]}";
				$stm = $link->query( $sql ) or die( "Error al consultar información del código de barras paquete : {$link->error}" );
				$row = $stm->fetch_assoc();
				$name[0] = $row['label_name'];
				$name[1] = '';
				if( strlen( $row['label_name'] ) > 32 ){
					$name = part_word( $row['label_name'] );
				}

				$resp_piece_2 .= "~salto~{$name[0]},{$name[1]},{$row['barcode']},{$piece_detail[1]}";
				
			}
			$file = fopen("../tmp_files/{$piece_file_2}", "w");
			fwrite($file, $resp_piece_2);
			fclose($file);
		}
		return "{$box_file}|{$pack_file}|{$piece_file}|{$piece_file_2}";
	}

	function getConfigForm( $field, $just_data= null, $link ){
		$sql = "SELECT
				 {$field}
				FROM sys_configuracion_sistema
				WHERE id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar configuración : {$link->error}" );
		$row = $stm->fetch_assoc();
		$resp = "<div>";
			$resp .= "<div class=\"row\">";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\" style=\"text-align: center;\">";
					$resp .= "<h5>Valor por defecto ( Calcular )</h5>";
					$resp .= "<input type=\"checkbox\" id=\"{$field}_field\" " . ( $row[$field] == 1 ? 'checked' : '' ) . "><br><br>";
					$resp .= "<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"save_system_conf('" . $field . "');close_emergent();\">";
						$resp .= "<i class=\"icon-ok-circle\">Guardar</i>";
					$resp .= "</button>";
				$resp .= "</div>";
			$resp .= "</div>";
		$resp .= "</div>";
		if( $just_data == 1 ){
			return $row[$field];
		}
		return $resp;
	}

	function getDefaultBarcodeType( $link ){
		$resp = "";
		$sql = "SELECT 
					default_tipos_codigos_barras AS default_type
				FROM sys_configuracion_sistema";
		$stm = $link->query( $sql ) or die( "Error al consultar el tipo de códigos de barras por default : {$link->error}" );
		$row = $stm->fetch_assoc();
		$resp .= "<div class=\"input-group\">";
			$resp .= "<select class=\"form-control\" id=\"barcode_type\">";
				$resp .= "<option value=\"1\" " . ( $row['default_type'] == 1 ? 'selected' : '' ) . ">Códigos estándar</option>";
				$resp .= "<option value=\"2\" " . ( $row['default_type'] == 2 ? 'selected' : '' ) . ">Códigos únicos</option>";
			$resp .= "<select>";
			$resp .= "<button class=\"btn btn-info\" onclick=\"show_helper('type_codes');\">";
				$resp .= "<i class=\"icon-question\"></i>";
			$resp .= "</button>";
		$resp .= "</div>";
		return $resp;
	}

	function saveSystemConf( $field, $value, $link ){
		$sql = "UPDATE sys_configuracion_sistema
				SET {$field} = '{$value}'
				WHERE id_configuracion_sistema = 1";
		//return $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar configuración : {$link->error}" );
		return "Actualizado correctamente.|{$value}";
	}

	function validateBarcodesSeriesUpdate( $link, $since_content = 0 ){
		$sql = "SELECT 
					DATE_FORMAT( ultima_actualizacion_prefijo_codigos_unicos , '%Y') AS last_update,
					DATE_FORMAT( NOW() , '%Y') AS current_year,
					prefijo_codigos_unicos AS current_prefix
				FROM sys_configuracion_sistema
				WHERE id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar la última actualización de series de códigos de barras : {$link->error}" );
		$row = $stm->fetch_assoc();
		$prefixes = generateNewPrefix( $link );
		$current_prefix = $prefixes[0];
		$final_prefix = $prefixes[1];
		if( $row['last_update'] < $row['current_year'] || $since_content == 1 ){
			$resp = "<div class=\"row\" style=\"background : white;\">";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\">";	
				

			if( $since_content == 1 ){
				$resp .= "<h5 class=\"orange\" style=\"text-align : justify;\">Al dar click en Aceptar se cambiarán todos los prefijos de todos los 
";
				$resp .= "proveedores productos, asi como, los contadores de cajas y paquetes de todos los proveedores producto.  <br><b class=\"red\">Aceptar</b> para actualizar el prefijo de los códigos de barras únicos</h5>
				<p>Escribe \"CAMBIAR PREFIJO\" para cambiar los prefijos y contadores</p>
				<div class=\"row\">
					<div class=\"col-2\"></div>
					<div class=\"col-8\">
						<input type=\"text\" class=\"form-control\" id=\"change_prefix_input_tmp\" placeholder=\"Escribe CAMBIAR PREFIJO\">
					</div>
				</div>
				";
			}else{
				$resp .= "<h5 class=\"orange\" style=\"text-align : justify;\">Es necesario actualizar el prefijo de los códigos de barras para este año 2022 ";
				$resp .= "da click en <b class=\"red\">Aceptar</b> para actualizar el prefijo de los códigos de barras únicos</h5>";
			}
					$resp .= "<table class=\"table table-striped table-bordered\" style=\"color : black;\">";
						$resp  .= "<thead>";
							$resp .= "<tr>";
								$resp .= "<th style=\"color : black; text-align : center;\">Prefijo Anterior</th>";
								$resp .= "<th style=\"color : black; text-align : center;\">Prefijo Actual</th>";
							$resp .= "</tr>";
						$resp  .= "</thead>";
						$resp  .= "<tbody>";
							$resp .= "<tr>";
								$resp .= "<td style=\"color : black; text-align : center;\">{$current_prefix}</td>";
								$resp .= "<td style=\"color : black; text-align : center;\">{$final_prefix}</td>";
							$resp .= "</tr>";
						$resp  .= "</tbody>";
					$resp .= "</table>";

					$resp .= "<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"update_barcodes_prefix( this );\">";
						$resp .= "<i class=\"icon-ok-circle\">Aceptar y actualizar los prefijos</i>";
					$resp .= "</button>";
				$resp .= "</div>";
			$resp .= "</div>";
			if( $since_content == 1 ){
				$resp .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\" />";	
			}
		}else{
			$resp = 'ok';
		}
		return $resp;
	}

	function updateBarcodesPrefix( $link, $no_reponse = null, $since_content = 0 ){
		$link->autocommit( false );
		$prefixes = generateNewPrefix( $link );
		$current_prefix = $prefixes[0];
		$final_prefix = $prefixes[1];
		
		/*$sql = "INSERT INTO log_codigos_unicos ( id_log, prefijo_anterior, prefijo_actual, fecha_alta )
		VALUES( null, '{$current_letter_1}{$current_letter_2}', '{$final_barcode}', NOW() )";
		$stm = $link->query( $sql ) or die( "Error al insertar log : {$link->error}" );*/
		
		$sql = "UPDATE ec_proveedor_producto pp
					SET pp.prefijo_codigos_unicos = '{$final_prefix}',
					pp.contador_cajas = 0,
					pp.contador_paquetes = 0
				WHERE 1";
		$stm = $link->query( $sql ) or die( "Error al actualizar el prefijo de los proveedores producto : {$link->error}" );

		$sql = "UPDATE sys_configuracion_sistema cs
					SET cs.prefijo_codigos_unicos = '{$final_prefix}',
					cs.ultima_actualizacion_prefijo_codigos_unicos = NOW()
				WHERE cs.id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al actualizar el prefijo de códigos únicos en la configuración del sistema : {$link->error}" );

		$link->autocommit( true );
		if( $no_reponse == null ){
			$resp = "<div class=\"row\" style=\"background : white;\">";
				$resp .= "<div class=\"col-2\"></div>";
				$resp .= "<div class=\"col-8\">";
					$resp .= "<h5 style=\"color : green;\">Los prefijos fueron actualizados exitosamente</h5>";
					$resp .= "<table class=\"table table-striped table-bordered\">";
						$resp  .= "<thead>";
							$resp .= "<tr>";
								$resp .= "<th style=\"color : black; text-align : center;\">Prefijo Anterior</th>";
								$resp .= "<th style=\"color : black; text-align : center;\">Prefijo Actual</th>";
							$resp .= "</tr>";
						$resp  .= "</thead>";
						$resp  .= "<tbody>";
							$resp .= "<tr>";
								$resp .= "<td style=\"color : black; text-align : center;\">{$current_prefix}</td>";
								$resp .= "<td style=\"color : black; text-align : center;\">{$final_prefix}</td>";
							$resp .= "</tr>";
						$resp  .= "</tbody>";
					$resp .= "</table>";
					$resp .= "<button class=\"btn btn-success form-control prefix_has_changed\" onclick=\"close_emergent();\">";
						$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
					$resp .= "</button>";
				$resp .= "</div>";
			$resp .= "</div>";
			if( $since_content == 1 ){
				$resp .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\" />";	
			}
			return $resp;
		}else{
			return 'ok';
		}
	}

	function generateNewPrefix( $link ){
		$numbers = array( "A" => 0, "B" => 1, "C" => 2, "D" => 3, "E" => 4, "F" => 5, "G" => 6, "H" => 7, "I" => 8, "J" => 9, 
			"K" => 10, "L" => 11, "M" => 12, "N" => 13, "O" => 14, "P" => 15, "Q" => 16, "R" => 17, "S" =>  18, "T" => 19, 
			"U" => 20, "V" => 21, "W" => 22, "X" => 23, "Y" => 24, "Z" => 25 );

		$letters = array( "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", 
			"O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" );

		$sql = "SELECT 
					MAX(prefijo_codigos_unicos) AS current_prefix
				FROM ec_proveedor_producto";
		$stm = $link->query( $sql ) or die( "Error al consultar las letras maximas de prefijo codigos barras únicos : {$link->error}" );
		$r = $stm->fetch_assoc();

		$current_letter_1 = $r['current_prefix'][0];
		$current_letter_2 = $r['current_prefix'][1];
		$new_letter_1 = '';
		$new_letter_2 = '';
		if( $current_letter_1 == '' && $current_letter_2 == '' ){
			$new_letter_1 = 'A';
			$new_letter_2 = '';
		}else if( $current_letter_1 != '' && $current_letter_2 == '' ){
			if( $current_letter_1 == 'Z' ){
				$new_letter_1 = 'A';
				$new_letter_2 = 'A';
			}else{
				$aux = ( (int)$numbers[$current_letter_1] ) + 1; 
				$new_letter_1 = $letters[$aux];
				$new_letter_2 = '';
			}
		}else if( $current_letter_1 != '' && $current_letter_2 != '' ){
			$was_limit = 0;
			$initial_position = (int) $numbers[$current_letter_1];
			$final_position = (int) $numbers[$current_letter_2];
			if( $current_letter_2 == 'Z' ){
				$aux = ( (int)$numbers[$current_letter_1] ) + 1; 
				$new_letter_1 = $letters[$aux];
				$new_letter_2 = "A";
			}else{
				$aux = ( (int)$numbers[$current_letter_2] ) + 1; 
				$new_letter_1 = $current_letter_1;
				$new_letter_2 = $letters[$aux];

			}
		}
		$current_prefix = $r['current_prefix'];
		$final_prefix = "{$new_letter_1}{$new_letter_2}";
		$resp = array( $current_prefix, $final_prefix );
		return $resp;
	}
?>