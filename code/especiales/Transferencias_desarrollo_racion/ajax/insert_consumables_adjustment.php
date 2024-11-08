<?php
/* 
	* Version Oscar 2024-11-08 para implementar movimeintos de almacen por store Procedures 2024
*/
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	$consumables = explode("|~|", ( isset( $_POST['consumables'] ) ? $_POST['consumables'] : $_GET['consumables'] ) );
	$store_id = ( isset( $_POST['store_id'] ) ? $_POST['store_id'] : $_GET['store_id'] );
//consulta el almacen principal de la sucursal destino
	$sql = "SELECT 
				id_almacen AS warehouse_id 
			FROM ec_almacen 
			WHERE id_sucursal = {$store_id} 
			AND es_almacen = 1";
	$stm = $link->query( $sql ) or die( "Error al consultar el almacen principal : {$link->error}" );
	$row = $stm->fetch_assoc();
	$warehouse_id = $row['warehouse_id'];
	$link->autocommit( false );
//se implementa store procedure para insertar cabecera movimiento de almacen Oscar 2024-11-08
	$sql = "CALL spMovimientoAlmacen_inserta ( {$user_id}, 'AJUSTE DE INVENTARIO CONSUMIBLES DESDE TRANSFERENCIA', {$sucursal_id}, {$warehouse_id}, 8, -1, -1, -1, -1, 22, NULL )";
	$stm = $link->query( $sql ) or die( "Error al insertar la cabecera del movimiento almacen : {$link->error} {$sql}" );
	$sql = "SELECT LAST_INSERT_ID()";
	$stm_2 = $link->query( $sql ) or die( "Error al consultar id de cabecera de movimiento almacen : {$link->error} {$sql}" );
	$header_id = $stm_2->fetch_row();
	$header_id = $header_id[0];
//inserta detalles de movimientos de almacen
	foreach ($consumables as $key => $consumable) {
	//se implementa store procedure para insertar detalle de movimiento de almacen Oscar 2024-11-08
		$consumable = explode( "|", $consumable );
		$sql = "CALL spMovimientoAlmacenDetalle_inserta( {$header_id}, {$consumable[0]}, {$consumable[2]}, {$consumable[2]}, -1, -1, {$consumable[1]}, 22, NULL );";
		$stm_2 = $link->query( $sql ) or die( "Error al insertar detalles de movimiento almacen por exhibicion : {$link->error} {$sql}" );
	}
	$link->autocommit( true );
	die( 'ok|Actualizacion de inventario guardada exitosamente!' );
?>