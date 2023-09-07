<?php
	include( '../../../conexionMysqli.php' );
	//die('ok');
	$arr_ok = array();
	$arr_no = array();
	$link->autocommit( false );
//obtener productos con códigos de barras de decoración
	$sql = "SELECT 
				p.nombre AS name, 
				pp.id_proveedor_producto AS product_provider_id,
				pp.codigo_barras_pieza_1 AS piece_barcode_1,
				pp.codigo_barras_pieza_2 AS piece_barcode_2,
				pp.codigo_barras_pieza_3 AS piece_barcode_3,
				pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1,
				pp.codigo_barras_presentacion_cluces_2 AS pack_barcode_2,
				pp.codigo_barras_caja_1 AS box_barcode_1,
				pp.codigo_barras_caja_2 AS box_barcode_2
			FROM ec_proveedor_producto 
			pp LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos 
			WHERE pp.codigo_barras_pieza_1 != '' 
			AND id_categoria IN( SELECT id_categoria FROM ec_categoria WHERE nombre = 'Decoracion' )";
	$stm = $link->query( $sql ) or die( "Error al consultar las decoraciones por cambiar : {$link->error}");
	while ( $row = $stm->fetch_assoc() ) {
	/*	var_dump( $row );
	die('ok');*/
		if( $row['piece_barcode_3'] == '' && $row['pack_barcode_2'] == '' && $row['box_barcode_2'] == '' ){//$row['piece_barcode_3'] == ''
			$sql = "UPDATE ec_proveedor_producto 
						SET codigo_barras_pieza_3 = codigo_barras_pieza_1,
						codigo_barras_pieza_1 = '', 
						codigo_barras_presentacion_cluces_2 = codigo_barras_presentacion_cluces_1,
						codigo_barras_presentacion_cluces_1 = '',
						codigo_barras_caja_2 = codigo_barras_caja_1,
						codigo_barras_caja_1 = ''
					WHERE id_proveedor_producto = '{$row['product_provider_id']}'";
			$stm2 = $link->query( $sql );
			if( !$stm2 ){
				//echo ( "<div style=\"border:1px solid red;\">Error al mover los códigos de barras {$row['name']} : {$link->error} <br>" );
				$row['error2'] = "<div style=\"border:1px solid red;\">Error al mover los códigos de barras {$row['name']} : {$link->error} <br>";
				array_push( $arr_no, $row );
			}else{
				$sql = "UPDATE ec_proveedor_producto pp
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
						IF( (pp.codigo_barras_presentacion_cluces_1 = '' OR pp.codigo_barras_presentacion_cluces_1 IS NULL ),
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
							IF(pp.piezas_presentacion_cluces > 0 AND pp.piezas_presentacion_cluces != '', pp.codigo_barras_presentacion_cluces_1, '') 
						),
					pp.codigo_barras_caja_1 = 
						IF( (pp.codigo_barras_caja_1 = '' OR pp.codigo_barras_caja_1 IS NULL),
							IF(	pp.presentacion_caja > 1,
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
							IF( pp.presentacion_caja > 0 AND pp.presentacion_caja != '', pp.codigo_barras_caja_1, '' )
						)
					WHERE pp.id_proveedor_producto = {$row['product_provider_id']}";
				$stm3 = $link->query( $sql );
				if( !$stm3 ){
					//echo ( "<div style=\"border:1px solid red;\">Error al actualizar los códigos de barras {$row['name']} : {$link->error} <br>" );
					$row['error2'] = "<div style=\"border:1px solid red;\">Error al actualizar los códigos de barras {$row['name']} : {$link->error} <br>";
					array_push( $arr_no, $row );
				}else{
					array_push( $arr_ok, $row );
				}
			}
		}else{	
			$row['error1'] = "Elcódigo de barras no está libre; CP3 : {$row['piece_barcode_3']}  CPAQ_2 :  {$row['pack_barcode_2']}  CCJ_2 : {$row['pack_barcode_3']}";
			array_push( $arr_no, $row );
		}
	}
	$link->autocommit( true );

	//imprime los resultados en tablas

	echo "<div>";
		echo "<p align=\"center\">Cambiados Exitosamene</p>";
		echo build_table_response( $arr_ok , true );
	echo "</div>";
	echo '<br><br>';

	echo "<div>";
		echo "<p align=\"center\">Errores : </p>";
		echo build_table_response( $arr_no , false );
	echo "</div>";



	function build_table_response( $array , $success ){
		$resp =  "<table>";
			$resp .= "<tr>";
				$resp .= "<th>Producto</th>";
				$resp .= "<th>Pieza 1</th>";
				$resp .= "<th>Pieza 2</th>";
				$resp .= "<th>Pieza 3</th>";
				$resp .= "<th>Paquete 1</th>";
				$resp .= "<th>Paquete 2</th>";
				$resp .= "<th>Caja 1</th>";
				$resp .= "<th>Caja 2</th>";
				if( $success == false ){
					$resp .= "<th>Eror 1</th>";
					$resp .= "<th>Error 2</th>";
				}
			$resp .= "</tr>";
		foreach ( $array as $key => $val ) {
			$resp .= "<tr>";
			foreach ( $val as $key2 => $value ) {
				$resp .= "<td>{$value}</td>";
			}
			$resp .= "</tr>";
		}
		$resp .=  "</table>";
		return $resp;
	}
//regresa los resultados

	/*
	SELECT p.orden_lista, p.nombre AS name, pp.id_proveedor_producto AS product_provider_id, pp.codigo_barras_pieza_1 AS piece_barcode_1, pp.codigo_barras_pieza_2 AS piece_barcode_2, pp.codigo_barras_pieza_3 AS piece_barcode_3, pp.codigo_barras_presentacion_cluces_1 AS pack_barcode_1, pp.codigo_barras_presentacion_cluces_2 AS pack_barcode_2, pp.codigo_barras_caja_1 AS box_barcode_1, pp.codigo_barras_caja_2 AS box_barcode_2 FROM ec_proveedor_producto pp LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos WHERE pp.codigo_barras_pieza_1 != '' AND id_categoria IN( SELECT id_categoria FROM ec_categoria WHERE nombre = 'Decoracion' ) ORDER BY `piece_barcode_3` DESC

	
	*/

?>