<?php
	include( '../../conect.php' );
	$txt = $_POST['barcode'];
	$pk = $_POST['key'];
	$flag = $_POST['type'];

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
		case '11':
			$condition = "( pp.codigo_barras_presentacion_cluces_1 = '{$txt}' AND p.id_productos != '{$pk}' )
			OR pp.codigo_barras_pieza_1 = '{$txt}'
			OR pp.codigo_barras_pieza_2 = '{$txt}'
			OR pp.codigo_barras_pieza_3 = '{$txt}'
			OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
			OR pp.codigo_barras_caja_1 = '{$txt}'
			OR pp.codigo_barras_caja_2 = '{$txt}'";
			break;
		case '12':
			$condition = "( pp.codigo_barras_presentacion_cluces_2 = '{$txt}' AND p.id_productos != '{$pk}' )
			OR pp.codigo_barras_pieza_1 = '{$txt}'
			OR pp.codigo_barras_pieza_2 = '{$txt}'
			OR pp.codigo_barras_pieza_3 = '{$txt}'
			OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
			OR pp.codigo_barras_caja_1 = '{$txt}'
			OR pp.codigo_barras_caja_2 = '{$txt}'";
			break;
		case '15':
			$condition = "( pp.codigo_barras_caja_1 = '{$txt}' AND p.id_productos != '{$pk}' )
			OR pp.codigo_barras_pieza_1 = '{$txt}'
			OR pp.codigo_barras_pieza_2 = '{$txt}'
			OR pp.codigo_barras_pieza_3 = '{$txt}'
			OR pp.codigo_barras_presentacion_cluces_1 = '{$txt}'
			OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
			OR pp.codigo_barras_caja_2 = '{$txt}'";
			break;
		case '16':
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
	$stm = mysql_query( $sql ) or die( "Error al validar el código de barras del proveedor : " . mysql_error() );
	if( mysql_num_rows( $stm ) > 0 ){
		$results = mysql_fetch_row( $stm );
		die( 'El código de barras ya existe para el producto ' . $results[0] );
	}else{
		echo 'ok';
	}
?>