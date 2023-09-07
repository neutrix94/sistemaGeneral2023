<?php
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
//inserta cabecera de movimientos de almacen
	$sql="INSERT INTO ec_movimiento_almacen ( /*1*/id_tipo_movimiento, /*2*/id_usuario, 
		/*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/observaciones, /*7*/id_almacen, /*8*/id_pedido ) 
	VALUES ( /*1*/8,/*2*/{$user_id},/*3*/{$store_id},/*4*/now(),/*5*/now(), 
		/*6*/'AJUSTE DE INVENTARIO CONSUMIBLES DESDE TRANSFERENCIA', /*7*/{$warehouse_id}, /*8*/-1 )";
	$stm = $link->query( $sql ) or die( "Error al insertar la cabecera del movimiento almacen : {$link->error} {$sql}" );			
	$sql = "SELECT LAST_INSERT_ID()";
	$stm_2 = $link->query( $sql ) or die( "Error al consultar id de cabecera de movimiento almacen : {$link->error} {$sql}" );
	$header_id = $stm_2->fetch_row();
	$header_id = $header_id[0];
//inserta detalles de movimientos de almacen
	foreach ($consumables as $key => $consumable) {
		$consumable = explode( "|", $consumable );
		$sql = "INSERT INTO ec_movimiento_detalle( id_movimiento, id_producto, cantidad, cantidad_surtida, 
		id_proveedor_producto, id_pedido_detalle ) VALUES ( {$header_id}, {$consumable[0]}, {$consumable[2]},
		{$consumable[2]}, {$consumable[1]}, /*8*/-1 )";
		$stm_2 = $link->query( $sql ) or die( "Error al insertar detalles de movimiento almacen por exhibicion : {$link->error} {$sql}" );
	}
	$link->autocommit( true );
	die( 'ok|Actualizacion de inventario guardada exitosamente!' );
?>