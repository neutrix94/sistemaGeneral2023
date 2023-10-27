<?php
	include( '../../.../../config.inc.php' );
	include( '../../../../conexionMysqli.php' );

	//descarga de csv
	if(isset($_POST['fl']) && $_POST['fl']==1){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="exportacion_tabla.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		die('');
	}

	$product_list = str_replace( '|', ',', $_POST['products'] );
	$price_list = $_POST['list'];
	$sql = "SELECT 
				p.id_productos,
				p.orden_lista,
				p.nombre,
				pd.de_valor,
				pd.a_valor,
				pd.precio_venta
			FROM ec_precios_detalle pd
			LEFT JOIN ec_productos p ON p.id_productos = pd.id_producto
			WHERE pd.id_precio = '{$price_list}'
			AND pd.id_producto IN( {$product_list} )";
	$eje = $link->query( $sql )or die( "Error al consultar los precios de venta : {$link->error}");

	echo '<table class="table table-striped" id="grid_resultado">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>ID PRODUCTO</th>';
				echo '<th>ORDEN LISTA</th>';
				echo '<th>PRODUCTO</th>';
				echo '<th>DE</th>';
				echo '<th>A</th>';
				echo '<th>PRECIO</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
	while ( $r = $eje->fetch_row() ) {
		echo '<tr>';
			echo '<td>' . $r[0] . '</td>';
			echo '<td>' . $r[1] . '</td>';
			echo '<td>' . $r[2] . '</td>';
			echo '<td>' . $r[3] . '</td>';
			echo '<td>' . $r[4] . '</td>';
			echo '<td>' . $r[5] . '</td>';
		echo '<tr>';
	}
	echo '</tbody>';
	echo '</table>';

?>