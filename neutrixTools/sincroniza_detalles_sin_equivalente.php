<?php

	$sql = "SELECT
		md.id_movimiento AS ' ID DE CABECERA',
		md.id_movimiento_almacen_detalle AS ' ID DE DETALLE',
		ma.id_transferencia AS 'ID TRANSFERNCIA',
		p.nombre AS PRODUCTO,
		( tm.afecta * md.cantidad ) AS CANTIDAD,
		tm.nombre AS 'TIPO DE MOVIMIENTO',
		alm.nombre AS 'ALMACEN',
		ma.fecha AS FECHA,
		ma.hora AS 'HORA',
		CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS USUARIO,
		ma.id_equivalente AS 'equivalente_cabecera',
		md.id_equivalente AS 'equivalente_detalle'
	FROM ec_movimiento_detalle md
	LEFT JOIN ec_productos p ON md.id_producto=p.id_productos
	LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
	LEFT JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento=ma.id_tipo_movimiento
	LEFT JOIN ec_almacen alm ON alm.id_almacen=ma.id_almacen
	LEFT JOIN sys_users u 
	ON ma.id_usuario = u.id_usuario
	WHERE ma.fecha BETWEEN '2022-11-11' AND '2022-11-23'
	AND md.id_equivalente = 0
	AND ma.id_sucursal = 4
	GROUP BY md.id_movimiento_almacen_detalle";
	$stm = mysql_query( $sql ) or die( "Error al consultar los movimientos de almacen pendientes de sincronizar : " . mysql_error() );

	while ( $row = mysql_fetch_assoc( $stm ) ) {
		$sql = "INSERT INTO ec_movimiento_detalle (
			 /*1*/id_movimiento_almacen_detalle,
			/*2*/id_movimiento,
			/*3*/id_producto,
			/*4*/cantidad,
			/*5*/cantidad_surtida,
			/*6*/id_pedido_detalle,
			/*7*/id_oc_detalle,
			/*8*/id_proveedor_producto,
			/*9*/id_equivalente,
			/*10*/sincronizar )
			VALUES ( 
			/*id_movimiento_almacen_detalle*/'{$id_movimiento_almacen_detalle}',
			/*id_movimiento*/'{$id_movimiento}',
			/*id_producto*/'{$id_producto}',
			/*cantidad*/'{$cantidad}',
			/*cantidad_surtida*/'{$cantidad_surtida}',
			/*id_pedido_detalle*/'{$id_pedido_detalle}',
			/*id_oc_detalle*/'{$id_oc_detalle}',
			/*id_proveedor_producto*/'{$id_proveedor_producto}',
			/*id_equivalente*/'{$id_equivalente}',
			/*sincronizar*/'{$sincronizar}' )";
		
	}

?>