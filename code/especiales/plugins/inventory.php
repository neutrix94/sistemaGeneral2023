<?php
	function insert_madpp( $detail_id ){
		$sql = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto,
				id_movimiento_almacen_detalle, id_proveedor_producto, cantidad, fecha_registro, id_sucursal,
				id_equivalente, status_agrupacion, id_tipo_movimiento, id_almacen)
				SELECT 
					NULL, 
					md.id_movimiento_almacen_detalle,
					md.id_proveedor_producto,
					md.cantidad,
					NOW(),
					ma.id_sucursal,
					0,
					-1,
					ma.id_tipo_movimiento,
					ma.id_almacen
				FROM ec_movimiento_detalle md
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
				WHERE md.id_movimiento_almacen_detalle = '{$detail_id}'";
		if( ! mysql_query($sql) ){
			return mysql_error();
		}else{
			return 'success';
		}
	}

	function update_madpp( $detail_id ){
		
		if( ! mysql_query($sql) ){
			return mysql_error();
		}else{
			return 'success';
		}
	}
	function delete_madpp( $detail_id ){

		if( ! mysql_query($sql) ){
			return mysql_error();
		}else{
			return 'success';
		}
	}
?>