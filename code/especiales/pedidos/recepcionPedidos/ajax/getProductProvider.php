<?php
	include( '../../../../../config.ini.php' );
	include( '../../../../../conexionMysqli.php' );

	$action = $_POST['fl'];
	if( !isset( $_POST['fl'] ) ){
		$action = $_GET['fl'];
	}
	switch ( $action ) {
		case 'getProviders':
			$id = $_POST['p_k'];
			$counter = $_POST['c'];
			echo build_table_product_provider( $id , $counter, $_POST['reception_detail_id'], $path, $link );
		break;

		case 'getRow' : 
			$counter = $_POST['current_counter'];
			$id = $_POST['p_k'];
		//consulta indormacion del producto
			$sql  = "SELECT '',id_productos, '', '', '', '', '','', '', '', ''  FROM ec_productos
			WHERE id_productos = '{$id}'";
			$exc = $link->query( $sql ) or die( "Error al consultar datos de la nueva fila de proveedor producto : " . $link->error );
			$r = $exc->fetch_row();
			echo getRow( $r, $counter, $link );
		break;
		
		case 'saveProductProviders' : 
			echo saveProductProviders( $_POST['pp'], $_POST['p_k'], $link );
		break;

		case 'omitProductProvider' : 
			echo omitProductProvider( $_POST['p_k'], $link );
		break;

		case 'validateNoRepeatBarcode':
		//validateNoRepeatBarcode( $txt, $pk, $flag, $link){
			$txt = $_POST['barcode'];
			$pk = $_POST['key'];
			$flag = $_POST['type'];
			echo validateNoRepeatBarcode( $txt, $pk, $flag, $link );
		break;

		case 'getProductProviderMeasures':
			echo getProductProviderMeasures( $_GET['product_provider_id'], $_GET['reception_detail_id'], $_GET['home_path'], $link );
		break; 

		case 'comboProductProvider':
			echo comboProductProvider( $_POST['product_id'], $_POST['provider_id'], $_POST['option'], $link );
		break;
		case 'deleteMeasure':
			echo deleteMeasure( $_POST['id'], $_POST['is_temporal'], $link );
		break;

		case 'deleteImg':
			echo deleteImg( $_GET['img_name'], $link );
		break;

		case 'saveProductProviderMeassures' :
		//echo 'here : ' . $_POST['product_provider_id'];
			echo saveProductProviderMeassures( $_POST['meassures'], $_POST['img_path'], $_POST['product_provider_id'], $link );
		break;

		case 'reassignMeassure':
			echo reassignMeassure( $_POST['p_k'], $_POST['product_provider'], $link );
		break;

		case 'showProductProviderMeassuresForm' :
			//$product_provider_id = $_GET[''];
			/*$save_img_path = '../../recepcionBodega/ajax/db.php?fl=savePhoto&type=final';
			$response_img_path = '../../../../';
			$path = '../../../';
			$frames_path = '../../../../';	
			$files_path = '';
			$include_jquery = 0;//no incluir jquery*/

			$home_path = $_GET['home_path'];
		    $include_jquery = $_GET['include_jquery'];
		   // $frames_path = $_GET['frames_path'];

		    $path_camera_plugin = $_GET['path_camera_plugin'];
		    //$path_files = $_GET['path_files'];
		    $product_provider_id = $_GET['product_provider_id'];
		    $save_img_path = $_GET['save_img_path'];
		    $type = $_GET['type'];

			include( '../../../recepcionBodega/views/measuresForm.php' );
			die( '<style type="text/css"> #options_buttons{margin-top : 20% !important;} </style>' );
			//echo 'ok';
			//echo showProductProviderMeassuresForm(  );
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
			$bag_type_id = ( isset( $_GET['bag_type'] ) ? $_GET['bag_type'] : 0 );
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
			/*$new_product_id = 'null';
			if( $is_new_product == 1 ){
				$new_product_id = $product_id;
				$product_id = 'null';
				$product_provider_id = 'null';
			}*/

			echo saveMeasures( $measures_id, $product_id, $product_provider_id, $box_lenght, $box_width, $box_height, 
				$pack_lenght, $pack_width, $pack_height, $bag_type_id, $piece_lenght, $piece_width, $piece_height,
				$piece_weight, $photo_1, $photo_2, $photo_3, $link );

		default:
			die( "Permission Denied!" );
		break;
	}

	/*function getComboPackBags( $link, $option_selected = null ){
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
	}*/

	function showProductProviderMeassuresForm(){
	}

	function saveMeasures( $measures_id = null, $product_id, $product_provider_id, $box_lenght, $box_width, $box_height, 
				$pack_lenght, $pack_width, $pack_height, $bag_type_id, $piece_height, $piece_lenght, $piece_width,
				$piece_weight, $photo_1, $photo_2, $photo_3, $link ){
		$photo_1 = str_replace( '../../../files/packs_img_tmp/', '', $photo_1 );
		$photo_2 = str_replace( '../../../files/packs_img_tmp/', '', $photo_2 );
		$photo_3 = str_replace( '../../../files/packs_img_tmp/', '', $photo_3 );
		//if( $reception_detail_id 
		//if( $measures_id == null || $measures_id == 0 ){
		$sql = "INSERT INTO ec_proveedor_producto_medidas(
				/*1*/id_proveedor_producto_medida, /*2*/id_proveedor_producto, /*3*/id_producto, /*4id_producto_nuevo,*/
				/*5*/largo_caja, /*7*/ancho_caja, /*8*/alto_caja, /*9*/largo_paquete,
				/*10*/ancho_paquete, /*11*/alto_paquete, /*12*/id_bolsa_paquete, /*13*/imagen_paquete_superior,
				/*14*/imagen_paquete_frontal, /*15*/imagen_paquete_lateral, /*16*/largo_pieza, /*17*/ancho_pieza, /*18*/alto_pieza,
				/*19*/peso_pieza, /*20*/fecha_alta, /*21*/sincronizar )
			 VALUES ( 
				/*id_proveedor_producto_medida_tmp*/NULL,
				/*id_proveedor_producto*/{$product_provider_id},
				/*id_producto*/( SELECT IF(id_producto IS NULL, NULL, id_producto) FROM ec_proveedor_producto WHERE id_proveedor_producto = {$product_provider_id} ),
				/*id_producto_nuevo{$new_product_id},*/
				/*id_recepcion_bodega_detalle{$reception_detail_id},*/
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
	}

	function reassignMeassure( $id, $product_provider_id, $link ){
		$sql = "UPDATE ec_proveedor_producto_medidas_tmp 
					SET id_proveedor_producto = '{$product_provider_id}',
					id_recepcion_bodega_detalle = NULL
				WHERE id_proveedor_producto_medida_tmp = {$id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar el registro de medidas temporales : {$link->error} {$sql}" );
		return 'ok|La medida temporal fue reasignada exitosamente.';
	}

	function saveProductProviderMeassures( $meassures, $path, $product_provider_id, $link ){
		//echo $measures; return;
		$link->autocommit( false );
		$ids = '';
	//elimina las medidas de proveedor producto
		$sql_delete = "DELETE FROM ec_proveedor_producto_medidas WHERE id_proveedor_producto = {$product_provider_id}";
		//die( $meassures );
		$meassures_array = explode( '|~|', $meassures );
		foreach ( $meassures_array as $key => $meassure_array ) {
			$meassure = explode( '~', $meassure_array );
			if( $meassure[16] == 1 ){//si es un registro temporal
			//echo $meassure_array;

				//mueve las imágenes a la carpeta correspondiente
				$tmp_counter = 1;
				for( $i = 9; $i <= 11; $i ++ ){
					$currentLocation = "../../../../../files/packs_img_tmp/{$meassure[$i]}";
					if( file_exists( $currentLocation ) ){
							$sql = "SELECT 
									CONCAT(
										REPLACE( codigo_barras_presentacion_cluces_1, ' ', '_' ),
										'_',
										DATE_FORMAT(NOW(), '%Y%m%d_%h%i%s_'),
										'{$tmp_counter}.png'
									) AS name
								FROM ec_proveedor_producto
								WHERE id_proveedor_producto = {$product_provider_id}";
						$stm_img_name = $link->query( $sql ) or die( "Error al formar nombre de fotografía : {$link->error}");
						$row_img_name = $stm_img_name->fetch_assoc();
						$newLocation = "../../../../../files/packs_img/{$row_img_name['name']}";
						$moved = rename($currentLocation, $newLocation);
						if(!$moved){
							return "No se pudo mover la imágen {$counter} de '{$currentLocation}' a '{$newLocation}'";
						}
						$meassure[$i] = $row_img_name['name'];
					}else{
						//die( "njo existe : {$currentLocation}" );
					}
					$tmp_counter ++;
				}
				$sql = "INSERT INTO ec_proveedor_producto_medidas (
						 /*1*/id_proveedor_producto_medida, /*2*/id_proveedor_producto, /*3*/id_producto,
						/*4*/largo_caja, /*5*/ancho_caja,/*6*/alto_caja, /*7*/largo_paquete, /*8*/ancho_paquete, /*9*/alto_paquete,
						/*10*/imagen_paquete_superior, /*11*/imagen_paquete_frontal, /*12*/imagen_paquete_lateral, /*13*/id_bolsa_paquete,
						/*14*/largo_pieza, /*15*/ancho_pieza, /*16*/alto_pieza, /*17*/peso_pieza, /*19*/fecha_alta, /*20*/sincronizar )
						 VALUES ( 
						/*id_proveedor_producto_medida*/NULL,
						/*id_proveedor_producto*/{$product_provider_id},
						/*id_producto*/( SELECT id_producto FROM ec_proveedor_producto WHERE id_proveedor_producto = {$product_provider_id} ),
						/*largo_caja*/{$meassure[2]},
						/*ancho_caja*/{$meassure[3]},
						/*alto_caja*/{$meassure[4]},
						/*largo_paquete*/{$meassure[5]},
						/*ancho_paquete*/{$meassure[6]},
						/*alto_paquete*/{$meassure[7]},
						/*imagen_paquete_superior*/'{$meassure[9]}',
						/*imagen_paquete_frontal*/'{$meassure[10]}',
						/*imagen_paquete_lateral*/'{$meassure[11]}',
						/*id_bolsa_paquete*/{$meassure[8]},
						/*largo_pieza*/{$meassure[12]},
						/*ancho_pieza*/{$meassure[13]},
						/*alto_pieza*/{$meassure[14]},
						/*peso_pieza*/{$meassure[15]},
						/*fecha_alta*/NOW(),
						/*sincronizar*/1 )";
//return $sql;

				$stm = $link->query( $sql ) or die( "Error al insertar las medidas de proveedor_producto : {$link->error}" );
			//recupera el id insertado
				$ids .= ( $ids == '' ? '' : ',' );
				$ids .= $link->insert_id;
			//elimina el registro de la tabla temporal
				$sql = "DELETE FROM ec_proveedor_producto_medidas_tmp WHERE id_proveedor_producto_medida_tmp = {$meassure[0]}";
				$stm = $link->query( $sql ) or die("Error al eliminar el registro temporal de medidas de proveedor_producto : {$link->error}");
			}else{//si es un registro de la tabla final
				$sql = "UPDATE ec_proveedor_producto_medidas SET
						/*2*/id_proveedor_producto='{$product_provider_id}',
						/*4*/largo_caja={$meassure[2]},
						/*5*/ancho_caja={$meassure[3]},
						/*6*/alto_caja={$meassure[4]},
						/*7*/largo_paquete={$meassure[5]},
						/*8*/ancho_paquete={$meassure[6]},
						/*9*/alto_paquete={$meassure[7]},
						/*10*/imagen_paquete_superior='{$meassure[9]}',
						/*11*/imagen_paquete_frontal='{$meassure[10]}',
						/*12*/imagen_paquete_lateral='{$meassure[11]}',
						/*13*/id_bolsa_paquete={$meassure[8]},
						/*14*/largo_pieza={$meassure[12]},
						/*15*/ancho_pieza={$meassure[13]},
						/*16*/alto_pieza={$meassure[14]},
						/*17*/peso_pieza={$meassure[15]},
						/*20*/sincronizar='1' 
						WHERE id_proveedor_producto_medida = {$meassure[0]}";
					$stm = $link->query( $sql ) or die( "Error al actualizar las medidas de proveedor_producto : {$link->error} {$sql}" );
				
				$ids .= ( $ids == '' ? '' : ',' );
				$ids .= $meassure[0];	
			}
		}
		$sql_delete .= " AND id_proveedor_producto_medida NOT IN( {$ids} )";
		$stm = $link->query( $sql ) or die( "error al eliminar las medidas de proveedores_producto : {$link->error}" );
		$link->autocommit( true );
		echo 'ok|Los cambios fueron guardados exitosamente.';
	}

	function saveMeassuresImagesChanges( $meassures_ids, $meassures_types, $meassures_img_1, $meassures_img_2, $meassures_img_3, $link ){
		//$
	}

	function deleteImg( $img_name, $link ){
		$img_name = str_replace('packs_img_tmp/', '', $img_name);
		$img_name = str_replace('packs_img/', '', $img_name);
		$path_img = '../../../../../files/';
		$path_img_tmp = '../../../../../files/';
		//echo $img_name;
	//busca en tabla temporal
		$sql = "SELECT
					id_proveedor_producto_medida_tmp AS tmp_product_provider_measure_id,
					IF( '{$img_name}' = imagen_paquete_superior, 
						'imagen_paquete_superior',
						IF( '{$img_name}' = imagen_paquete_frontal,  
							'imagen_paquete_frontal',
							IF( '{$img_name}' = imagen_paquete_lateral, 
								'imagen_paquete_lateral',
								'none'
							)
						) 
					) AS field
				FROM ec_proveedor_producto_medidas_tmp
				WHERE imagen_paquete_superior = '{$img_name}'
				OR imagen_paquete_frontal = '{$img_name}'
				OR imagen_paquete_lateral = '{$img_name}'";
			//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar el registro de la Imágen" );
		if( $stm->num_rows > 0 ){
			if( unlink( "{$path_img}packs_img_tmp/{$img_name}" ) ){
				$row = $stm->fetch_assoc();
				$sql = "UPDATE ec_proveedor_producto_medidas_tmp 
							SET {$row['field']} = '' 
						WHERE id_proveedor_producto_medida_tmp = {$row['tmp_product_provider_measure_id']}";
				$stm = $link->query( $sql ) or die( "Error al resetear el campo {$row['field']} de fotografía : {$link->error}" );
				return 'ok|Imágen temporal eliminada exitosamente';
			}else{
				return "Error al eliminar la imágen de la carpeta temporal! {$path_img}img_pack_tmp/{$img_name}";
			}
		}else{
	//busca en tabla final
			$sql = "SELECT
					id_proveedor_producto_medida AS product_provider_measure_id,
					IF( '{$img_name}' = imagen_paquete_superior, 
						'imagen_paquete_superior',
						IF( '{$img_name}' = imagen_paquete_frontal,  
							'imagen_paquete_frontal',
							IF( '{$img_name}' = imagen_paquete_lateral, 
								'imagen_paquete_lateral',
								'none'
							)
						) 
					) AS field
				FROM ec_proveedor_producto_medidas
				WHERE imagen_paquete_superior = '{$img_name}'
				OR imagen_paquete_frontal = '{$img_name}'
				OR imagen_paquete_lateral = '{$img_name}'";
			$stm = $link->query( $sql ) or die( "Error al consultar el registro de la Imágen" );
			if( $stm->num_rows > 0 ){
				if( unlink( "{$path_img}packs_img/{$img_name}" ) ){
					$row = $stm->fetch_assoc();
					$sql = "UPDATE ec_proveedor_producto_medidas 
								SET {$row['field']} = '' 
							WHERE id_proveedor_producto_medida = {$row['product_provider_measure_id']}";
					$stm = $link->query( $sql ) or die( "Error al resetear el campo {$row['field']} de fotografía : {$link->error}" );
					return 'ok|Imágen eliminada exitosamente';
				}else{
					return "Error al eliminar la imágen de la carpeta!";
				}
			}
		}
	}

	function deleteMeasure( $meassure_id, $is_temporal, $link ){
	//elimina las imágenes
		if( $is_temporal == 1 ){
			$sql = "SELECT 
						imagen_paquete_superior AS image_1, 
						imagen_paquete_frontal AS image_2,
						imagen_paquete_lateral AS image_3
					FROM ec_proveedor_producto_medidas_tmp
					WHERE id_proveedor_producto_medida_tmp = {$meassure_id}";
			$stm = $link->query( $sql ) or die( "Error al consultar las imágenes por eliminar : {$link->error} {$sql}" );
			$row = $stm->fetch_assoc();
			if( $row['image_1'] != '' ){
				if( file_exists( '../../../../../files/packs_img_tmp/' . $row['image_1'] ) ){
					if( !unlink( '../../../../../files/packs_img_tmp/' . $row['image_1'] ) ){
						die( "Error al eliminar imágen 1 : ./../../../../files/packs_img_tmp/{$row['image_1']}" );
					}
				}
			}
			if( $row['image_2'] != '' ){
				if( file_exists( '../../../../../files/packs_img_tmp/' . $row['image_2'] ) ){
					if( !unlink('../../../../../files/packs_img_tmp/' . $row['image_2'] ) ){
						die( "Error al eliminar imágen 2 : ./../../../../files/packs_img_tmp/{$row['image_2']}" );
					}
				}
			}
			if( $row['image_3'] != '' ){
				if( file_exists( '../../../../../files/packs_img_tmp/' . $row['image_3'] ) ){
					if( !unlink( '../../../../../files/packs_img_tmp/' . $row['image_3'] ) ){
						die( "Error al eliminar imágen 3 : ./../../../../files/packs_img_tmp/{$row['image_3']}" );
					}
				}
			}
			$sql = "DELETE FROM ec_proveedor_producto_medidas_tmp WHERE id_proveedor_producto_medida_tmp = {$meassure_id}";
			$stm = $link->query( $sql ) or die( "Error al eliminar las medidas temporales de proveedor_producto : {$link->error} {$sql}" );
		}else{
			$sql = "SELECT 
						imagen_paquete_superior AS image_1, 
						imagen_paquete_frontal AS image_2, 
						imagen_paquete_lateral AS image_3 
					FROM ec_proveedor_producto_medidas
					WHERE id_proveedor_producto_medida = {$meassure_id}";
			$stm = $link->query( $sql ) or die( "Error al consultar las imágenes por eliminar : {$link->error}" );
			$row = $stm->fetch_assoc();
			if( $row['image_1'] != '' ){
				if( file_exists( '../../../../../files/packs_img/' . $row['image_1'] ) ){
					if( !unlink( '../../../../../files/packs_img/' . $row['image_1'] ) ){
						die( "Error al eliminar imágen 1 : ./../../../../files/packs_img_tmp/{$row['image_1']}" );
					}
				}
			}
			if( $row['image_2'] != '' ){
				if( file_exists( '../../../../../files/packs_img/' . $row['image_2'] ) ){
					if( !unlink('../../../../../files/packs_img/' . $row['image_2'] ) ){
						die( "Error al eliminar imágen 2 : ./../../../../files/packs_img_tmp/{$row['image_2']}" );
					}
				}
			}
			if( $row['image_3'] != '' ){
				if( file_exists( '../../../../../files/packs_img/' . $row['image_3'] ) ){
					if( !unlink('../../../../../files/packs_img/' . $row['image_3'] ) ){
						die( "Error al eliminar imágen 3 : ./../../../../files/packs_img_tmp/{$row['image_3']}" );
					}
				}
			}
			$sql = "DELETE FROM ec_proveedor_producto_medidas WHERE id_proveedor_producto_medida = {$meassure_id}";
			$stm = $link->query( $sql ) or die( "Error al eliminar las medidas temporales de proveedor_producto : {$link->error}" );
		}
		return 'ok|Las medidas fueron eliminadas exitosamente!';
	}

	function comboProductProvider( $product_id, $provider_id, $option, $link ){
		$resp = "";
		$sql = "SELECT 
					id_proveedor_producto AS product_provider_id, 
				/*oscar 2023*/
					CONCAT( clave_proveedor, ' ( ', presentacion_caja, ' ) ' )  AS provider_clue,
					CONCAT( clave_proveedor, '|', presentacion_caja, '|', piezas_presentacion_cluces ) AS provider_clue_2,
					precio_pieza AS piece_price,
					id_producto AS product_id
				/**/
				FROM ec_proveedor_producto
				WHERE id_producto = {$product_id}
				AND id_proveedor = {$provider_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar los modelos para formar combo proveedor - producto : {$link->error}" );
		
		$resp .= "<option value=\"0\">-- Seleccionar --</option>";
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= "<option value=\"{$row['product_provider_id']}\"";//oscar 2023
			$resp .= ( $row['provider_clue_2'] == $option ? " selected" : "" );//oscar 2023
//implementacion Oscar 2023/09/25 ( Que se actualize el precio del producto en relacion al proveedor producto seleccionado en la emergente de proveedor producto )
			if( $row['provider_clue_2'] == $option ){
				$sql = "UPDATE ec_productos 
							SET precio_compra = {$row['piece_price']} 
						WHERE id_productos = {$row['product_id']}";
				$product_update = $link->query( $sql ) or die( "Error al actualizar el precio de compra del producto : {$link->error}" );
			}
//fin de cambio Oscar 2023/09/25
			$resp .= ">{$row['provider_clue']}</option>";
		}	
		$resp .= "<option value=\"-1\">Administrar Proveedores</option>";
		return $resp;	
	}

	function getProductProviderMeasures( $product_provider_id = null,  $reception_detail_id = null, $imgs_path, $link ){
	//echo "{$product_provider_id} , {$reception_detail_id}";
		$resp = "";
		$counter = 0;
		$resp .= "<div class=\"\">";
				$resp .= "<table class=\"table table-striped table-bordered\">";
					$resp .= "<thead>";
						$resp .= "<tr>";
							$resp .= "<th style=\"color:black;\">Largo Caja</th>";
							$resp .= "<th style=\"color:black;\">Ancho Caja</th>";
							$resp .= "<th style=\"color:black;\">Alto Caja</th>";
							$resp .= "<th style=\"color:black;\">Largo paquete</th>";
							$resp .= "<th style=\"color:black;\">Ancho paquete</th>";
							$resp .= "<th style=\"color:black;\">Alto paquete</th>";
							$resp .= "<th style=\"color:black;\">Tipo de Bolsa</th>";
							$resp .= "<th style=\"color:black;\">Imágen Caja</th>";
							$resp .= "<th style=\"color:black;\">Imágen Fontal</th>";
							$resp .= "<th style=\"color:black;\">Imágen Lateral</th>";
							//$resp .= "<th style=\"color:black;\">Alto Caja</th>";
							$resp .= "<th style=\"color:black;\">Largo pieza</th>";
							$resp .= "<th style=\"color:black;\">Ancho pieza</th>";
							$resp .= "<th style=\"color:black;\">Alto pieza</th>";
							$resp .= "<th style=\"color:black;\">Peso pieza</th>";
	//						$resp .= "<th>Imágenes</th>";
						$resp .= "</tr>";
					$resp .= "</thead>";
					$resp .= "<tbody id=\"meassures_tbody\">";
			if( $product_provider_id != 'null' && $product_provider_id != null ){
			//echo "<p>Product_provider {$product_provider_id}</p>";
				$sql = "SELECT 
							id_proveedor_producto_medida AS tmp_product_provider_measure_id,
							id_proveedor_producto AS product_provider_id,
							largo_caja AS box_lenght,
							ancho_caja AS box_width,
							alto_caja AS box_height,
							largo_paquete AS pack_lenght,
							ancho_paquete AS pack_width,
							alto_paquete AS pack_height,
							imagen_paquete_superior AS pack_foto_1,
							imagen_paquete_frontal AS pack_foto_2,
							imagen_paquete_lateral AS pack_foto_3,
							id_bolsa_paquete AS pack_bag_id,
							largo_pieza AS piece_lenght,
							ancho_pieza AS piece_width,
							alto_pieza AS piece_height,
							peso_pieza AS piece_weight
						FROM ec_proveedor_producto_medidas
						WHERE id_proveedor_producto = '{$product_provider_id}'";
				$stm = $link->query( $sql ) or die( "Error al consultar las medidas de proveedor_producto : {$link->error}" );
				while ( $row = $stm->fetch_assoc() ) {
					$resp .= buildMeassureRow( $row, $counter, $imgs_path . 'files/packs_img/', false, $link );
					$counter ++;
				}
				//echo $sql;
			}
			//if( $reception_detail_id != 'null' ){
				$sql = "SELECT 
							id_proveedor_producto_medida_tmp AS tmp_product_provider_measure_id,
							id_proveedor_producto AS product_provider_id,
							largo_caja AS box_lenght,
							ancho_caja AS box_width,
							alto_caja AS box_height,
							largo_paquete AS pack_lenght,
							ancho_paquete AS pack_width,
							alto_paquete AS pack_height,
							imagen_paquete_superior AS pack_foto_1,
							imagen_paquete_frontal AS pack_foto_2,
							imagen_paquete_lateral AS pack_foto_3,
							id_bolsa_paquete AS pack_bag_id,
							largo_pieza AS piece_lenght,
							ancho_pieza AS piece_width,
							alto_pieza AS piece_height,
							peso_pieza AS piece_weight
						FROM ec_proveedor_producto_medidas_tmp
						WHERE id_recepcion_bodega_detalle = '{$reception_detail_id}'
						OR id_proveedor_producto = '{$product_provider_id}'";
				$stm = $link->query( $sql ) or die( "Error al consultar las medidas de proveedor_producto_temporal : {$link->error}" );
				while ( $row = $stm->fetch_assoc() ) {
					$resp .= buildMeassureRow( $row, $counter, $imgs_path . 'files/packs_img_tmp/', true, $link );
					$counter ++;
				}
			//echo '<p>Reception_detail</p><br>' . $sql;
			//}

			$resp .= "</tbody>";
			$resp .= "</table>";
			$resp .= "<div class=\"row\">
						<div class=\"col-1\"></div>
						<div class=\"col-3\">
							<button class=\"btn btn-success form-control\" onclick=\"saveProductProviderMeassures();\">
								<i class=\"icon-floppy\">Guardar</i>
							</button>
						</div>
						<div class=\"col-1\"></div>
						<div class=\"col-3\">
							<button class=\"btn btn-success form-control\" onclick=\"show_product_provider_meassures_form( {$product_provider_id} );\">
								<i class=\"icon-floppy\">Agregar</i>
							</button>
						</div>
						<div class=\"col-1\"></div>
						<div class=\"col-3\">
							<button class=\"btn btn-danger form-control\" onclick=\"close_emergent_2();\">
								<i class=\"icon-cancel-circled-1\">Cerrar</i>
							</button>
						</div>
					</div>";
		$resp .= "</div>";
		return $resp;
	}

	function buildMeassureRow( $row, $counter, $imgs_path, $is_tmp = false, $link ){
	//	die('here');
		$resp = "";
		$combo = getComboPackBags( $link, $row['pack_bag_id'], $counter );
		$resp .= "<tr id=\"measure_row_{$counter}\"";
		if( $is_tmp == true ){	
			$resp .= " style=\"background : red;\"";
		}
		$resp .= ">";
			$resp .= "<td id=\"measures_1_{$counter}\" class=\"no_visible\">{$row['tmp_product_provider_measure_id']}</td>";
			$resp .= "<td id=\"measures_2_{$counter}\" class=\"no_visible\">{$row['product_provider_id']}</td>";
			$resp .= "<td id=\"measures_4_{$counter}\" onclick=\"editaCelda(4, {$counter}, 'measures_');\" class=\"\">{$row['box_lenght']}</td>";
			$resp .= "<td id=\"measures_5_{$counter}\" onclick=\"editaCelda(5, {$counter}, 'measures_');\"class=\"\">{$row['box_width']}</td>";
			$resp .= "<td id=\"measures_6_{$counter}\" onclick=\"editaCelda(6, {$counter}, 'measures_');\" class=\"\">{$row['box_height']}</td>";
			$resp .= "<td id=\"measures_7_{$counter}\" onclick=\"editaCelda(7, {$counter}, 'measures_');\" class=\"\">{$row['pack_lenght']}</td>";
			$resp .= "<td id=\"measures_8_{$counter}\" onclick=\"editaCelda(8, {$counter}, 'measures_');\" class=\"\">{$row['pack_width']}</td>";
			$resp .= "<td id=\"measures_9_{$counter}\" onclick=\"editaCelda(9, {$counter}, 'measures_');\" class=\"\">{$row['pack_height']}</td>";
			$resp .= "<td id=\"measures_10_{$counter}\" class=\"\">" . $combo . "</td>";//$row['pack_height']
		//imágen 1
			if( $row['pack_foto_1'] == '' ){
				$imgs_path_1 = str_replace( 'files/packs_img_tmp', 'img/frames', $imgs_path );
				$imgs_path_1 = str_replace( 'files/packs_img', 'img/frames', $imgs_path_1 );
				$row['pack_foto_1'] = 'no_image.png';
			}else{
				$imgs_path_1 = $imgs_path;
			}
			$resp .= "<td id=\"measures_11_{$counter}\" class=\"\">
							<img src=\"{$imgs_path_1}{$row['pack_foto_1']}\" width=\"100%\" onclick=\"expand_img( this );\">
					</td>";
		//imágen 2
			if( $row['pack_foto_2'] == '' ){
				$imgs_path_2 = str_replace( 'files/packs_img_tmp', 'img/frames', $imgs_path );
				$imgs_path_2 = str_replace( 'files/packs_img', 'img/frames', $imgs_path_2 );
				$row['pack_foto_2'] = 'no_image.png';
			}else{
				$imgs_path_2 = $imgs_path;
			}
			$resp .= "<td id=\"measures_12_{$counter}\" class=\"\">
							<img src=\"{$imgs_path_2}{$row['pack_foto_2']}\" width=\"100%\" onclick=\"expand_img( this );\">
					</td>";
		//imágen 3
			if( $row['pack_foto_3'] == '' ){
				$imgs_path_3 = str_replace( 'files/packs_img_tmp', 'img/frames', $imgs_path );
				$imgs_path_3 = str_replace( 'files/packs_img', 'img/frames', $imgs_path_3 );
				$row['pack_foto_3'] = 'no_image.png';
			}else{
				$imgs_path_3 = $imgs_path;
			}
			$resp .= "<td id=\"measures_13_{$counter}\" class=\"\">
							<img src=\"{$imgs_path_3}{$row['pack_foto_3']}\" width=\"100%\" onclick=\"expand_img( this );\">
					</td>";
			$resp .= "<td id=\"measures_14_{$counter}\" onclick=\"editaCelda(14, {$counter}, 'measures_');\" class=\"\">{$row['piece_lenght']}</td>";
			$resp .= "<td id=\"measures_15_{$counter}\" onclick=\"editaCelda(15, {$counter}, 'measures_');\" class=\"\">{$row['piece_width']}</td>";
			$resp .= "<td id=\"measures_16_{$counter}\" onclick=\"editaCelda(16, {$counter}, 'measures_');\" class=\"\">{$row['piece_height']}</td>";
			$resp .= "<td id=\"measures_17_{$counter}\" onclick=\"editaCelda(17, {$counter}, 'measures_');\" class=\"\">{$row['piece_weight']}</td>";
			$resp .= "<td id=\"measures_18_{$counter}\" style=\"display : none;\">" . ( $is_tmp == true ? 1 : 0 ) . "</td>";
			$resp .= "<td id=\"measures_19_{$counter}\">
						<button class=\"btn btn-danger\" onclick=\"quitar_fila( {$counter}, 'measure' );\">
							<i class=\"icon-cancel-alt-filled\"></i>
						</button>
					</td>";
			//$resp .= "<td></td>";
		$resp .= "</tr>";//remove_measure( this, {$row['product_provider_measure_id']});
		return $resp;
	}

	function getComboPackBags( $link, $pack_bag_id, $counter = 0 ){
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
			if( $row['pack_bag_id'] == $pack_bag_id ){
				$resp .= " selected";
			}
			$resp .= ">{$row['name']}</option>";
		}
		$resp .= "</select>";
		return $resp;
	}
	
	function validateNoRepeatBarcode( $txt, $pk, $flag, $link){
		switch ( $flag ) {
			case '6':
				$condition = "( pp.codigo_barras_pieza_1 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '7':
				$condition = "( pp.codigo_barras_pieza_2 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '8':
				$condition = "( pp.codigo_barras_pieza_3 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '9':
				$condition = "( pp.codigo_barras_presentacion_cluces_1 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '10':
				$condition = "( pp.codigo_barras_presentacion_cluces_2 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '11':
				$condition = "( pp.codigo_barras_caja_1 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_2 = '{$txt}'";
				break;
			case '12':
				$condition = "( pp.codigo_barras_caja_2 = '{$txt}' AND p.id_productos != '{$pk}' )
				OR pp.codigo_barras_pieza_1 = '{$txt}'
				OR pp.codigo_barras_pieza_2 = '{$txt}'
				OR pp.codigo_barras_pieza_3 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
				OR pp.codigo_barras_caja_1 = '{$txt}'";
				break;
			default:
				die( 'No Action!' );
				break;
		}

		$sql = "SELECT 
					p.nombre, 
					pp.id_proveedor_producto 
				FROM ec_proveedor_producto pp 
				LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos
				WHERE {$condition}";
				//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al validar el código de barras del proveedor : " . mysql_error() );
		if( $stm->num_rows > 0 ){
			$results = $stm->fetch_row();
			return "El código de barras ya existe para el producto {$results[0]}";
		}else{
			return 'ok';
		}
	}

	function getRow( $r, $counter, $link, $is_temporal = null ){
		$providers_combo = getComboProviders( $r[2], $counter, $link );
	/*implementacion Oscar 2023*/
		$proyection = 0;
		$proyection += ( $r[21] != null && $r[21] != '' ? $r[21] : 0 );
		$proyection += ( $r[22] != null && $r[22] != '' ? $r[22] : 0 );
		$proyection += ( $r[23] != null && $r[23] != '' ? $r[23] : 0 );
	/*fin de cambio Oscar 2023*/
		$resp = "<tr id=\"product_provider_{$counter}\"" . ( $is_temporal != null ? "class=\"prod_prov_tmp_row\"" : "" ) . " >";
			$resp .= "<td><input type=\"radio\" id=\"pp_-1_{$counter}\" name=\"product_provider_selected\"></td>";
			$resp .= "<td id=\"pp_0_{$counter}\" class=\"no_visible\">{$r[0]}</td>";
			$resp .= "<td id=\"pp_1_{$counter}\" class=\"no_visible\" >{$r[1]}</td>";
			if ( $r[2] == '' || $r[2] == null || $r[2] == undefined ){
				$r[2] = 2;
			}
			$resp .= "<td id=\"p_p_2_1_{$counter}\" value=\"{$r[2]}\">" . $providers_combo . "</td>";
			
			$resp .= "<td id=\"pp_3_{$counter}\" onclick=\"editaCelda(3, {$counter}, 'pp_', 'modelsDepuration( this, {$counter} )');\">{$r[3]}</td>";
			$resp .= "<td id=\"pp_4_{$counter}\" onclick=\"editaCelda(4, {$counter}, 'pp_');\" onkeyup=\"change_product_provider_price( {$counter}, 1 );\">{$r[11]}</td>";
			$resp .= "<td id=\"pp_5_{$counter}\" onclick=\"editaCelda(5, {$counter}, 'pp_');\">{$r[12]}</td>";

			$resp .= "<td id=\"pp_6_{$counter}\" onclick=\"editaCelda(6, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[4]}</td>";
			$resp .= "<td id=\"pp_7_{$counter}\" onclick=\"editaCelda(7, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[5]}</td>";
			$resp .= "<td id=\"pp_8_{$counter}\" onclick=\"editaCelda(8, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[6]}</td>";
			$resp .= "<td id=\"pp_9_{$counter}\" onclick=\"editaCelda(9, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[7]}</td>";
			$resp .= "<td id=\"pp_10_{$counter}\" onclick=\"editaCelda(10, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[8]}</td>";
			$resp .= "<td id=\"pp_11_{$counter}\" onclick=\"editaCelda(11, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[9]}</td>";
			$resp .= "<td id=\"pp_12_{$counter}\" onclick=\"editaCelda(12, {$counter}, 'pp_', 'validateNoRepeatBarcode( this );');\">{$r[10]}</td>";
			$resp .= "<td id=\"pp_13_{$counter}\" class=\"no_visible\">{$r[13]}</td>";
			$resp .= "<td id=\"pp_14_{$counter}\" align=\"center\" value=\"$r[14]\"><input type=\"checkbox\"" . ( $r[14] == 1 ? ' checked' : '' ) 
			. " onchange=\"just_piece( this, {$counter} );\"></td>";
			//$resp .= "<td id=\"pp_11_{$counter}\" onclick=\"editaCelda(11, {$counter}, 'pp_')\">{$r[11]}</td>";
			$resp .= "<td id=\"pp_16_{$counter}\"  onclick=\"editaCelda(16, {$counter}, 'pp_');\"  onchange=\"change_product_provider_price( {$counter}, 2 );\">{$r[15]}</td>";
			$resp .= "<td id=\"pp_17_{$counter}\"  onclick=\"editaCelda(17, {$counter}, 'pp_');\"  onchange=\"change_product_provider_price( {$counter}, 3 );\">{$r[16]}</td>";			
		/*implementacion Oscar 2023 para agregar la prioridad de proveedor producto*/
			$resp .= "<td id=\"pp_21_{$counter}\" class=\"\" onclick=\"editaCelda(21, {$counter}, 'pp_');\">{$r[20]}</td>";
			$resp .= "<td id=\"pp_22_{$counter}\" class=\"text-end\">{$proyection}</td>";
		
		/*fin de cambio Oscar 2023*/
			$resp .= "<td id=\"pp_15_{$counter}\">
					<button 
						type=\"button\"
						class=\"btn btn-info\"";
			if( $is_temporal == null ){
				$resp .= "onclick=\"show_measures( {$r[0]} );\"";
			}else{
				$resp .= "onclick=\"show_measures( null, {$r[13]} );\"";
			}
			$resp .= ">
						<i class=\"icon-picture-outline\">ver</i>
					</td>";
			$resp .= "<td id=\"pp_19_{$counter}\">
					<button 
						type=\"button\"
						onclick=\"remove_product_provider( {$counter} );\"
						class=\"btn btn-danger\"
					>
						X
					</td>";
			$resp .= "<td class=\"no_visible\" id=\"pp_18_{$counter}\">{$r[17]}</td>";
			$resp .= "<td class=\"no_visible\" id=\"pp_20_{$counter}\" value=\"{$r[18]}\">{$r[19]}</td>";
			
			$check = ( $r[25] == 1 ? 'checked' : '' );
		/*Implementacion Oscar 2024-08-26 para meter campos de unidad medida pieza / codigo repetido*/
			$resp .= "<td id=\"pp_23_{$counter}\" onclick=\"editaCelda( 23, {$counter}, 'pp_' );\" class=\"text-start\">{$r[24]}</td>";
			$resp .= "<td id=\"\" class=\"text-center\"><input type=\"checkbox\" id=\"pp_24_{$counter}\" {$check}></td>";
		/*Fin de cambio Oscar 2024-08-26*/
		$resp .= '</tr>';
		return $resp;
	}

	function getComboProviders( $current_provider, $counter, $link ){
		$sql = "SELECT id_proveedor, nombre_comercial FROM ec_proveedor WHERE id_proveedor > 1";
		$exc = $link->query( $sql ) or die( "Error al consultar proveedores : " . $link->error );
		$resp = "<select id=\"pp_2_{$counter}\" onchange=\"changeProvider( this, {$counter} );\" class=\"form-select\">";
		while ( $r = $exc->fetch_row() ) {
			$resp .= "<option value=\"{$r[0]}\"";
			$resp .= ( $r[0] == $current_provider ? ' selected' : '' );
			$resp .= ">{$r[1]}</option>";
		}
		$resp .= "</select>";
		return $resp;
	}

	function build_table_product_provider( $id, $current_count, $reception_detail_id, $path, $link ){
		$resp = '';
		$sql = "SELECT 
					/*0*/pp.id_proveedor_producto,
					/*1*/p.id_productos,
					/*2*/pp.id_proveedor,/**/
					/*3*/pp.clave_proveedor,
					/*4*/pp.codigo_barras_pieza_1,
					/*5*/pp.codigo_barras_pieza_2,
					/*6*/pp.codigo_barras_pieza_3,
					/*7*/pp.codigo_barras_presentacion_cluces_1,
					/*8*/pp.codigo_barras_presentacion_cluces_2,
					/*9*/pp.codigo_barras_caja_1,
					/*10*/pp.codigo_barras_caja_2, 
					/*11*/pp.presentacion_caja,
					/*12*/pp.piezas_presentacion_cluces,
					/*13*/'',
					/*14*/pp.solo_pieza,
					/*15*/pp.precio_pieza,
					/*16*/pp.precio AS precio_caja,
					/*17*/( SELECT 
								IF( id_proveedor_producto_medida_tmp IS NULL, 0, id_proveedor_producto_medida_tmp )
							FROM ec_proveedor_producto_medidas_tmp WHERE id_proveedor_producto = pp.id_proveedor_producto
							LIMIT 1
						),
					/*18*/'' AS tmp_location,
					/*19*/'' AS tmp_location_description,
					/*20*/pp.prioridad_surtimiento AS priority,
					/*21*/(SELECT
								SUM( 
									IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 
										0, 
										( tm.afecta * mdpp.cantidad ) 
									) 
								)
							FROM ec_movimiento_detalle_proveedor_producto mdpp
							LEFT JOIN ec_tipos_movimiento tm
							ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
							WHERE mdpp.id_proveedor_producto = pp.id_proveedor_producto
						) AS all_inventories,
					/*22*/(SELECT 
							SUM( IF( ocd.id_oc_detalle IS NOT NULL, 
								( ocd.cantidad - ocd.cantidad_surtido ), 
								0 ) 
							)
							FROM ec_oc_detalle ocd
						    LEFT JOIN ec_ordenes_compra oc
						    ON oc.id_orden_compra = ocd.id_orden_compra
						    WHERE ocd.id_proveedor_producto = pp.id_proveedor_producto
						) AS 'pedido',
					/*23*/(SELECT
							IF( rbd.id_recepcion_bodega_detalle IS NULL, 
			                    0,
			                	SUM( pp.presentacion_caja * rbd.cajas_recibidas ) + SUM( rbd.piezas_sueltas_recibidas )
			                    ) AS recibidoRecepcion
							FROM ec_recepcion_bodega_detalle rbd
			                WHERE rbd.id_proveedor_producto = pp.id_proveedor_producto
			                AND rbd.validado IN( 0 )
						) AS receptionReceived,
					/*24*/pp.unidad_medida_pieza,
					/*25*/pp.es_modelo_codigo_repetido
				FROM ec_productos p
				LEFT JOIN ec_proveedor_producto pp ON pp.id_producto = p.id_productos
				WHERE p.id_productos = '{$id}'
				ORDER BY pp.id_proveedor_producto ASC";//ORDER BY pp.prioridad_surtimiento ASC

		$exc = $link->query( $sql ) or die( "Error al consultar la lista de proveedores para este producto : " . $link->error );

		$sql = "SELECT nombre, orden_lista FROM ec_productos WHERE id_productos = '{$id}'";
		$stm = $link->query( $sql ) or die( "Error al consultar el nombre del producto : " . $link->error );
		$product_name = $stm->fetch_row();
		$product_nam = "<b>({$product_name[1]})</b> {$product_name[0]}";
		$resp .= '<table class="table table-striped table-bordered product_provider_detail">';
		$resp .= '<thead>';
			$resp .= '<tr><th colspan="19" style="font-size : 200%; color : gray; text-align : text-center;">' . $product_nam . '</tr>';
			$resp .= '<tr>';
				$resp .= '<th class="no_visible">Id_p</th>';
				$resp .= '<th class="no_visible">Id_pp</th>';
				/*$resp .= '<th>Nombre</th>';*/
				$resp .= '<th style="color:black;">Usar</th>';
				$resp .= '<th style="color:black;">Proveedor</th>';
				$resp .= '<th style="color:black;">Modelo</th>';
				$resp .= '<th style="color:black;">Pzs x caja</th>';
				$resp .= '<th style="color:black;">Pzs x paquete</th>';
				$resp .= '<th style="color:black;">C_B_PZA_1</th>';
				$resp .= '<th style="color:black;">C_B_PZA_2</th>';
				$resp .= '<th style="color:black;">C_B_PZA_3</th>';
				$resp .= '<th style="color:black;">C_B_PQ_1</th>';
				$resp .= '<th style="color:black;">C_B_PQ_2</th>';
				$resp .= '<th style="color:black;">C_B_CJ_1</th>';
				$resp .= '<th style="color:black;">C_B_CJ_2</th>';
				$resp .= '<th style="color:black;">Solo Pza</th>';
				$resp .= '<th style="color:black;">Precio pieza</th>';
				$resp .= '<th style="color:black;">Precio caja</th>';
		/*implementacion Oscar 2023 para agregar la prioridad de proveedor producto*/
				$resp .= '<th style="color:black;">Prioridad</th>';
				$resp .= '<th style="color:black;">Proyeccion</th>';
		/*fin de cambio Oscar 2023*/
				$resp .= '<th style="color:black;">Medidas</th>';
				$resp .= '<th style="color:black;">Quitar</th>';
				$resp .= '<th style="color:black;">Unidad Pieza</th>';
				$resp .= '<th style="color:black;">Repetido</th>';
			$resp .= '</tr>';
		$resp .= '</thead>';
		$resp .= '<tbody id="product_provider_list">';
		$counter = 0;
		while ( $r = $exc->fetch_row() ) {
			$counter ++;
			
			$resp .= getRow( $r, $counter, $link );
		}
	//busca si tiene un nuevo proveedor para agregar
		$sql = "SELECT 
					/*0*/'',
					/*1*/rbd.id_producto,
					/*2*/rb.id_proveedor,
					/*3*/rbd.modelo,
					/*4*/'',
					/*5*/rbd.c_b_pieza,
					/*6*/'',
					/*7*/'',
					/*8*/rbd.c_b_paquete,
					/*9*/'', 
					/*10*/rbd.c_b_caja,
					/*11*/rbd.piezas_por_caja,
					/*12*/rbd.piezas_por_paquete,
					/*13*/rbd.id_recepcion_bodega_detalle,
					/*14*/0,
					/*15*/ 0 AS precio_pieza,
					/*16*/0 AS precio_caja,
					/*17*/( SELECT 
								IF( id_proveedor_producto_medida_tmp IS NULL, 0, id_proveedor_producto_medida_tmp )
							FROM ec_proveedor_producto_medidas_tmp WHERE id_recepcion_bodega_detalle = rbd.id_recepcion_bodega_detalle
							LIMIT 1
						),
					/*18*/(SELECT 
								IF( id_ubicacion_matriz_tmp IS NULL, '', id_ubicacion_matriz_tmp )
								FROM ec_proveedor_producto_ubicacion_almacen_tmp
							WHERE id_recepcion_bodega_detalle = rbd.id_recepcion_bodega_detalle
						) AS tmp_location,
					/*19*/(SELECT 
								IF( id_ubicacion_matriz_tmp IS NULL, 
									'',
									CONCAT( 'DESDE : ', letra_ubicacion_desde, numero_ubicacion_desde ) 
								)
								FROM ec_proveedor_producto_ubicacion_almacen_tmp
							WHERE id_recepcion_bodega_detalle = rbd.id_recepcion_bodega_detalle
						) AS tmp_location_description,
					/*20*/100 AS priority
				FROM ec_recepcion_bodega_detalle rbd
				LEFT JOIN ec_recepcion_bodega rb ON rb.id_recepcion_bodega = rbd.id_recepcion_bodega
				WHERE rbd.id_proveedor_producto IS NULL
				AND rbd.omitir_p_p = 0
				AND rbd.id_recepcion_bodega_detalle IN( {$reception_detail_id} )
				AND rbd.id_producto = '{$id}'";
		$stm = $link->query( $sql ) or die("Error al consutar proveedores productos capturados en la recepcion : " . $link->error );
		while ( $r = $stm->fetch_row() ) {
			$counter ++;
			$resp .= getRow( $r, $counter, $link, 1 );
		}
		$resp .= '</tbody>';

		$resp .= '</table>';

	/*configuración de proveedor_producto
		$resp .= "<br><div class=\"row\">";
			$resp .= "<div class=\"col-3\"></div>";
			$resp .= "<div class=\"col-6\">";
				$resp .= "<button type=\"button\" class=\"btn btn-info form-control\" onclick=\"show_measures();\">";
					$resp .= "<i class=\"icon-wrench-outline\">Medidas de caja y paquetes</i>";
				$resp .= "</button>";
			$resp .= "</div>";
		$resp .= "</div>";*/
	//boton de agregar
		$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-6\">";
				$resp .= "<button 
							type=\"button\"
							class=\"btn btn-primary\"
							onclick=\"add_row( 'provider', '.product_provider_detail', {$id}, '{$path}' );\"
						>
							<i class=\"icon-plus\">Agregar Fila</i>
						</button>";
			$resp .= "</div>";
			$resp .= "<div class=\"col-6\">";
			//boton de guardar
				$resp .= "<button 
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"save_product_providers( 'providers', '.product_provider_detail', {$id}, {$current_count}, '{$path}' );\"
						>
							<i class=\"icon-floppy\">Guardar Cambios</i>
						</button>";
			$resp .= "</div>";
		$resp .= "</div>";


		return $resp;
	}	

	function saveProductProviders( $product_providers, $product_id, $link ){
		if( !$link ){
			include( '../../../../../conexionMysqli.php' );
		}
		$link->autocommit( false );
		$providers = explode( '|', $product_providers );
		//die( $product_providers);
		$sql = array();
		
		$sql[0] = "DELETE FROM ec_proveedor_producto 
		WHERE id_producto = '{$product_id}'";
	//generacion de las consultas

//$file = fopen("archivo.txt", "w");
		foreach ($providers as $key => $product_provider) {
			//if( $key > 1 ){
				$provider = explode('~', $product_provider);
				$sql[0] .= " AND id_proveedor_producto != '{$provider[0]}'";
				
				$sql_auxiliar = ($provider[0] != '' && $provider[0] != null ? "UPDATE" : "INSERT INTO") . " ec_proveedor_producto SET ";
				
				$sql_auxiliar .= "clave_proveedor = '{$provider[3]}',";
				$sql_auxiliar .= "id_proveedor = '{$provider[2]}',";
				$sql_auxiliar .= "presentacion_caja = '{$provider[4]}',";
				$sql_auxiliar .= "piezas_presentacion_cluces = '{$provider[5]}',";
				$sql_auxiliar .= "codigo_barras_pieza_1 = '{$provider[6]}',";
				$sql_auxiliar .= "codigo_barras_pieza_2 = '{$provider[7]}',";
				$sql_auxiliar .= "codigo_barras_pieza_3 = '{$provider[8]}',";
				$sql_auxiliar .= "codigo_barras_presentacion_cluces_1 = '{$provider[9]}',";
				$sql_auxiliar .= "codigo_barras_presentacion_cluces_2 = '{$provider[10]}',";
				$sql_auxiliar .= "codigo_barras_caja_1 = '{$provider[11]}',";
				$sql_auxiliar .= "codigo_barras_caja_2 = '{$provider[12]}',";
				$sql_auxiliar .= "solo_pieza = '{$provider[14]}',";
				$sql_auxiliar .= "precio_pieza = '{$provider[15]}',";

				$provider[16] = ( $provider[15] * $provider[4] );
				
				$sql_auxiliar .= "precio = '{$provider[16]}',";
				$sql_auxiliar .= "prioridad_surtimiento = '{$provider[17]}',";//implementacion Oscar 2023
				$sql_auxiliar .= "unidad_medida_pieza = '{$provider[18]}',";//implementacion Oscar 2024-08-26
				$sql_auxiliar .= "es_modelo_codigo_repetido = '{$provider[19]}'";//implementacion Oscar 2024-08-26
				$sql_auxiliar .= ( $provider[0] != '' ? " WHERE id_proveedor_producto = '{$provider[0]}'" : ", id_producto = '{$product_id}'" );
				//return $sql_aux;
				echo $sql_aux;
				$stm = $link->query( $sql_auxiliar )or die( "{$key} : Error al guardar los proveedores del producto : " . $link->error );
				
			//se agrega el nuevo registro a la exclusion de porveedores producto por eliminar
				$sql[0] .= " AND id_proveedor_producto != '{$link->insert_id}'";
//fwrite($file, $sql_aux . "\n" );

				if( $provider[13] != null && $provider[13]!= '' && ( $provider[0] == '' || $provider[0] == null) ){
					$new_poduct_provider_insert = $link->insert_id;
					$sql[0] .= " AND id_proveedor_producto != '{$new_poduct_provider_insert}'";
				//actualiza el proveedor_producto en la recepcion de bodega si aplica
					$sql_aux = "UPDATE ec_recepcion_bodega_detalle SET id_proveedor_producto = '{$new_poduct_provider_insert}' 
						WHERE id_recepcion_bodega_detalle = '{$provider[13]}'";
					//echo $sql_aux;
					//array_push( $sql, $sql_aux);
					$stm = $link->query( $sql_aux ) or die( "{$key} : Error al actualizar los proveedores del producto en relación a la recepción: " . $link->error );
				//inserta ubicacion de proveedor producto 
					if( $provider[0] == '' || $provider[0] == null ){
						$sql_2 = "INSERT INTO ec_proveedor_producto_ubicacion_almacen ( /*1*/id_ubicacion_matriz,/*2*/id_almacen,
							/*3*/id_producto,/*4*/id_proveedor_producto,/*5*/letra_ubicacion_desde,/*6*/numero_ubicacion_desde,
							/*7*/letra_ubicacion_hasta,/*8*/numero_ubicacion_hasta,/*9*/pasillo_desde,/*10*/pasillo_hasta,
							/*11*/altura_desde,/*12*/altura_hasta,/*13*/habilitado,/*14*/es_principal,/*15*/fecha_alta,
							/*16*/sincronizar )
							SELECT
								/*1*/NULL,
								/*2*/1,
								/*3*/id_producto,
								/*4*/{$new_poduct_provider_insert},
								/*5*/letra_ubicacion_desde,
								/*6*/numero_ubicacion_desde,
								/*7*/letra_ubicacion_hasta,
								/*8*/numero_ubicacion_hasta,
								/*9*/pasillo_desde,
								/*10*/pasillo_hasta,
								/*11*/altura_desde,
								/*12*/altura_hasta,
								/*13*/'1',
								/*14*/'1',
								/*15*/NOW(),
								/*16*/1
							FROM ec_proveedor_producto_ubicacion_almacen_tmp
							WHERE id_recepcion_bodega_detalle = '{$provider[13]}'";
						$stm_2 = $link->query( $sql_2 ) or die( "Error al insertar la ubicación principal del nuevo registro de proveedor producto : {$link->error} {$sql_2}" );			
					}
				}
			//}
		}
//fclose($file);

		foreach ($sql as $key2 => $query) {
			$stm = $link->query( $query )or die( "{$key2} : Error al actualizar los proveedores del producto en relación a la recepción: " . $link->error );
		}
	//14. Actualiza los códigos de barras de acuerdo al proveedor-producto ( Oscar 2022 )
		$sql_aux = "UPDATE ec_proveedor_producto pp
				LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos SET 
				pp.codigo_barras_pieza_1 = 
					IF( pp.codigo_barras_pieza_1 = '' OR pp.codigo_barras_pieza_1 IS NULL,
					CONCAT(
						IF( pp.id_proveedor_producto <= 9,
							CONCAT( '0000', pp.id_proveedor_producto ),
							IF( pp.id_proveedor_producto <= 99,
								CONCAT( '000', pp.id_proveedor_producto ),
								IF( pp.id_proveedor_producto <= 999,
									CONCAT( '00', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 9999,
										CONCAT( '0', pp.id_proveedor_producto ),
										pp.id_proveedor_producto
									)
								)
							)
						),  
						' ', 
						p.orden_lista 
					), 
					pp.codigo_barras_pieza_1 ),
				pp.codigo_barras_presentacion_cluces_1 = 
					IF( (pp.codigo_barras_presentacion_cluces_1 = '' OR pp.codigo_barras_presentacion_cluces_1 IS NULL ) /*AND pp.solo_pieza = 0*/,
						IF( pp.piezas_presentacion_cluces > 1,
							CONCAT( IF( pp.id_proveedor_producto <= 9,
									CONCAT( '0000', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 99,
										CONCAT( '000', pp.id_proveedor_producto ),
										IF( pp.id_proveedor_producto <= 999,
											CONCAT( '00', pp.id_proveedor_producto ),
											IF( pp.id_proveedor_producto <= 9999,
												CONCAT( '0', pp.id_proveedor_producto ),
												pp.id_proveedor_producto
											)
										)
									)
								), 

								' PQ', pp.piezas_presentacion_cluces, ' ', p.orden_lista ),
							''
						), 
						IF(/* pp.solo_pieza = 0 AND*/ pp.piezas_presentacion_cluces > 0 AND pp.piezas_presentacion_cluces != '', pp.codigo_barras_presentacion_cluces_1, '') 
					),
				pp.codigo_barras_caja_1 = 
					IF( (pp.codigo_barras_caja_1 = '' OR pp.codigo_barras_caja_1 IS NULL)/*AND pp.solo_pieza = 0*/,
						IF(	pp.presentacion_caja > 0,
							CONCAT(  IF( pp.id_proveedor_producto <= 9,
									CONCAT( '0000', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 99,
										CONCAT( '000', pp.id_proveedor_producto ),
										IF( pp.id_proveedor_producto <= 999,
											CONCAT( '00', pp.id_proveedor_producto ),
											IF( pp.id_proveedor_producto <= 9999,
												CONCAT( '0', pp.id_proveedor_producto ),
												pp.id_proveedor_producto
											)
										)
									)
								),  
									' CJ', pp.presentacion_caja, ' ', p.orden_lista),
							''
						),
						IF(/*pp.solo_pieza = 0 AND*/ pp.presentacion_caja > 0 AND pp.presentacion_caja != '', pp.codigo_barras_caja_1, '' )
					)
				WHERE pp.id_producto = '{$product_id}'";
		$stm = $link->query( $sql_aux )or die( "3 : Error al actualizar los proveedores del producto en relación a la recepción: " . $link->error );
		//return 'ojkasfb';
	/*consulta si hace commit o no*/
		$sql = "SELECT 
					guardar_proveedores_producto AS save_product_provider
				FROM sys_configuracion_sistema 
				WHERE id_configuracion_sistema = 1";
		$stm_save = $link->query( $sql ) or die( "error|Error al consultar si guarda proveedores o no : {$link->error}" );
		$row = $stm_save->fetch_assoc();
		if( $row['save_product_provider'] == '1' ){
			$link->autocommit( true );
			return 'Proveedores del producto guardados correctamente!';
		}else{
			return "No se pudo guardar porque el guardado de Proveedores Producto esta deshabilitado desde la configuración del sistema!";
		}
	}
	function omitProductProvider( $id, $link ){
		$sql = "UPDATE ec_recepcion_bodega_detalle SET omitir_p_p = 1 WHERE id_recepcion_bodega_detalle = '{$id}'";
		$stm = $link->query( $sql ) or die( "Error al omitir proveedor producto : " . $link->error );
		return 'Registro omitido exitosamente!';
	}
?>