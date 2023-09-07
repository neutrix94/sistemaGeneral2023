<?php
	include('../../../../conexionMysqli.php');

	$no_loged = $_POST['no_log'];
	$count = $_POST['counter'];
	$wholesale1 = $_POST['wholesale_1'];
	$wholesale2 = $_POST['wholesale_2'];
	$types_prices = array( array('Not Logged In', $no_loged), 
						array('Mostrador', $count), 
						array('Mayoreo 1', $wholesale1), 
						array('Mayoreo 2', $wholesale2)
	);
	/*var_dump( $types_prices );
	die('');*/
//actualiza lista de precios
	foreach ($types_prices as $key => $price) {
	//quita la lista anterior
		$sql = "UPDATE ec_precios SET grupo_cliente_magento = NULL WHERE grupo_cliente_magento = '{$price[0]}'";
		$link->query( $sql ) or die( "Error al actualizar lista de precios (paso 1) : {$link->error}");
	//asigna la nueva lista
		$sql = "UPDATE ec_precios SET grupo_cliente_magento = '{$price[0]}' WHERE id_precio = '{$price[1]}'";
		$link->query( $sql ) or die( "Error al actualizar lista de precios (paso 1) : {$link->error}");
	//crea los registros para sincronizacion de Magento
		$sql = "INSERT INTO ec_sync_magento
				SELECT 
					NULL,
					'Producto',
					pd.id_producto,
					'1',
					'update'
				FROM ec_precios_detalle pd
				LEFT JOIN ec_precios p ON p.id_precio = pd.id_precio
				LEFT JOIN ec_producto_tienda_linea ptl ON pd.id_producto = ptl.id_producto
				WHERE ptl.habilitado = 1
				AND pd.id_precio = '{$price[1]}'
				GROUP BY pd.id_producto";
		$link->query( $sql ) or die( "Error al crear registros para actualizar precios en Magento (paso 3) : {$link->error}");
	}
	die('Las listas de precios para magento fueron actualizadas y los registros de sincronizacion para Magento fueron creados exitosamente!');
?>