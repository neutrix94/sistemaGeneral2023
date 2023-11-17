<?php
	include( '../../conectMin.php' );
	include( '../../conexionMysqli.php' );
	$barcode = $_GET['barcode'];
	$barcode = trim( $barcode );
	if( $barcode == '' ){
		die( "El codigo de barras no puede ir vacio!" );
	}
	$sql = "SELECT 
			    p.id_productos As product_id, 
			    CONCAT( IF(pp.id_proveedor_producto IS NOT NULL, pp.id_proveedor_producto, '' ), ' - ( ', p.orden_lista ,' ) ', p.nombre, IF(pp.id_proveedor_producto IS NOT NULL, CONCAT( ' (MODELO : ', pp.clave_proveedor, ' - ', pp.presentacion_caja, ' pzs x caja)'), '' ), '°',pp.id_proveedor_producto ) As view
			FROM ec_productos p 
			LEFT JOIN ec_proveedor_producto pp 
			ON pp.id_producto = p.id_productos
			LEFT JOIN sys_sucursales_producto sp
			ON sp.id_producto = p.id_productos
			WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' OR pp.codigo_barras_pieza_3 = '{$barcode}' )
			AND p.es_maquilado=0 
			AND p.muestra_paleta=0
			AND sp.estado_suc = 1
			AND sp.id_sucursal = {$user_sucursal}
			AND p.habilitado = 1
			GROUP BY p.id_productos, pp.id_proveedor_producto";
	$stm = $link->query( $sql ) or die( "Error al consultar coincidencias de proveedor producto con codigo de barras : {$link->error}" );
	if( $stm->num_rows <= 0 ){
		die( "No fue encontrado ningun producto con el codigo de barras : '{$barcode}'" );
	}
	$row = $stm->fetch_assoc();
	//var_dump( $row );die( '' );
	die( "ok|" . json_encode( $row ) );
	//die( $sql );
?>