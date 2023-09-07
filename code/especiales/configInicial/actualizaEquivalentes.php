<?php
		//separamos hora y fecha
		$aux=explode(" ", $fecha_rsp);
		$fecha=$aux[0];
		$hora=$aux[1];

//14 Actualizar folios unicos de movimientos proveedor producto de la sucursal en linea y local en linea y local
	$sql = "UPDATE ec_movimiento_detalle_proveedor_producto 
			SET folio_unico = CONCAT( '{$store_prefix}_MDPP_', id_movimiento_detalle_proveedor_producto )
		WHERE id_sucursal = {$id_suc} 
		AND folio_unico IS NULL 
		AND fecha_registro <= '{$fecha_rsp}'";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al actualizar los id´s equivalentes de movimientos proveedor producto local : {$restauration_stm} ");
	}
	$sendPetition = send_petition( $api_path, $sql );
	if( $sendPetition != 'ok' ){
		die( "Error al actualizar los id´s equivalentes de movimientos proveedor producto linea : {$sendPetition}" );
	}
//15 Actualizar folios unicos de validacion de pedidos usuarios en linea y local
	$sql="UPDATE ec_pedidos_validacion_usuarios 
			SET folio_unico = CONCAT( '{$store_prefix}_VALID_', id_pedido_validacion ) 
		WHERE id_sucursal = {$id_suc} 
		AND folio_unico IS NULL
		AND fecha_alta <= '{$fecha_rsp}'";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al actualizar los id´s equivalentes de validacion de ventas en local : {$restauration_stm} ");
	}
	$sendPetition = send_petition( $api_path, $sql );
	if( $sendPetition != 'ok' ){
		die( "Error al actualizar los id´s equivalentes de validacion de ventas en linea : {$sendPetition}" );
	}
//16 Actualizar folios unicos de movimientos de almacen en linea y local
	$sql="UPDATE ec_movimiento_almacen ma
			LEFT JOIN ec_movimiento_detalle md
			ON md.id_movimiento = ma.id_movimiento_almacen
			SET ma.folio_unico = CONCAT( '{$store_prefix}_MA_', id_movimiento_almacen ),
			md.folio_unico = CONCAT( '{$store_prefix}_MD_', id_movimiento_almacen_detalle ) 
		WHERE ma.id_sucursal = {$id_suc} 
		AND ( ma.folio_unico IS NULL OR md.folio_unico IS NULL )
		AND CONCAT( ma.fecha, ' ', ma.hora ) <= '{$fecha_rsp}'";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al actualizar los id´s equivalentes de movimientos de almacen en local : {$restauration_stm} ");
	}
	$sendPetition = send_petition( $api_path, $sql );
	if( $sendPetition != 'ok' ){
		die( "Error al actualizar los id´s equivalentes de movimientos de almacen en linea : {$sendPetition}" );
	}
//17 Actualizar folios unicos de devoluciones de almacen en linea y local
	$sql="UPDATE ec_devolucion d
			LEFT JOIN ec_devolucion_detalle dd
			ON dd.id_devolucion = d.id_devolucion
			LEFT JOIN ec_devolucion_pagos dp
			ON dp.id_devolucion = d.id_devolucion
			SET d.folio_unico = CONCAT( '{$store_prefix}_DEV_', d.id_devolucion ), 
			dd.folio_unico = CONCAT( '{$store_prefix}_DEVDET_', dd.id_devolucion_detalle ), 
			dd.folio_unico = CONCAT( '{$store_prefix}_DEVPAG_', dp.id_devolucion_pago ) 
		WHERE id_sucursal = {$id_suc} 
		AND ( d.folio_unico IS NULL OR dd.folio_unico IS NULL OR dp.folio_unico IS NULL )
		AND CONCAT( d.fecha, ' ', d.hora ) <= '{$fecha_rsp}'";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al actualizar los id´s equivalentes de devolucion en local : {$restauration_stm} ");
	}
	$sendPetition = send_petition( $api_path, $sql );
	if( $sendPetition != 'ok' ){
		die( "Error al actualizar los id´s equivalentes de devolucion en linea : {$sendPetition}" );
	}
//18 Actualizar folios unicos de ventas de almacen en linea y local	
	$sql="UPDATE ec_pedidos p
			LEFT JOIN ec_pedidos_detalle pd
			ON pd.id_pedido = p.id_pedido
			LEFT JOIN ec_pedido_pagos pp
			ON pp.id_pedido = p.id_pedido
			SET p.folio_unico = CONCAT( '{$store_prefix}_VTA_', p.id_pedido ),
			pd.folio_unico = CONCAT( '{$store_prefix}_VTADET_', pd.id_pedido_detalle ),
			pp.folio_unico = CONCAT( '{$store_prefix}_VTAPAG_', pp.id_pedido_pago ),
			p.modificado = 0 
		WHERE p.id_sucursal = {$id_suc} 
		AND ( p.folio_unico IS NULL OR pd.folio_unico IS NULL OR pp.folio_unico IS NULL )
		AND p.fecha_alta <= '{$fecha_rsp}'";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al actualizar los id´s equivalentes de ventas en local : {$restauration_stm} ");
	}
	$sendPetition = send_petition( $api_path, $sql );
	if( $sendPetition != 'ok' ){
		die( "Error al actualizar los id´s equivalentes de ventas en linea : {$sendPetition}" );
	}				

//19 Eliminar codigos unicos de transferencias en local
	$sql="DELETE FROM ec_transferencia_codigos_unicos";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar los codigos unicos de las transferencias en local : {$restauration_stm} ");
	}
//20 Eliminar surtimiento de transferencias en local
	$sql="DELETE FROM ec_transferencias_surtimiento_usuarios";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar registros de surtimiento de transferencias en local : {$restauration_stm} ");
	}
//21 Eliminar validacion de transferencias en local
	$sql="DELETE FROM ec_transferencias_validacion_usuarios";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar registros de validacion de transferencias en local : {$restauration_stm} ");
	}
//22 Eliminar recepcion de transferencias en local
	$sql="DELETE FROM ec_transferencias_recepcion_usuarios";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar registros de recepcion de transferencias en local : {$restauration_stm} ");
	}
//23 Eliminar bloques de recepcion de mercancia en local
	$sql="DELETE FROM ec_bloques_recepcion_mercancia";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar bloques de recepcion de mercancia en local : {$restauration_stm} ");
	}
//24 Eliminar bloques de recepcion de mercancia en local
	$sql="DELETE FROM ec_bloques_transferencias_recepcion_detalle";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar detalle de bloques de recepcion de transferencias en local : {$restauration_stm} ");
	}
//25 Eliminar bloques de recepcion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_recepcion";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar bloques de recepcion de transferencias en local : {$restauration_stm} ");
	}
//26 Eliminar detalle de bloques de resolucion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_resolucion_detalle";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar detalle de bloques de resolucion de transferencias en local : {$restauration_stm} ");
	}
//27 Eliminar bloques de resolucion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_resolucion";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar bloques de resolucion de transferencias en local : {$restauration_stm} ");
	}
//28 Eliminar escaneos de resolucion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_resolucion_escaneos";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar escaneos de resolucion de transferencias en local : {$restauration_stm} ");
	}
//29 Eliminar detalles de bloques de validacion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_validacion_detalle";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar detalles de bloques de validacion de transferencias en local : {$restauration_stm} ");
	}
//30 Eliminar bloques de validacion de transferencias en local
	$sql="DELETE FROM ec_bloques_transferencias_validacion";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar bloques de validacion de transferencias en local : {$restauration_stm} ");
	}
//31 Eliminar transferencias en local
	$sql="DELETE FROM ec_transferencias WHERE (id_sucursal_origen != {$id_suc} AND id_sucursal_destino != {$id_suc} ) AND id_transferencia!=-1";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar transferencias en local : {$restauration_stm} ");
	}	
//ELIMINAR REGISTROS DE SINCRONIZACION DE TABLAS DE SINCRONIZACION;
//32 Vaciar tabla de sincronizacion de ventas en local
	$sql="TRUNCATE sys_sincronizacion_ventas";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_ventas en local : {$restauration_stm} ");
	}
//33 Vaciar tabla de sincronizacion de validaciones de ventas en local
	$sql="TRUNCATE sys_sincronizacion_validaciones_ventas";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_validaciones_ventas en local : {$restauration_stm} ");
	}
//34 Vaciar tabla de registros de sincronizacion de ventas en local
	$sql="TRUNCATE sys_sincronizacion_registros_ventas";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_registros_ventas en local : {$restauration_stm} ");
	}
//35 Vaciar tabla de registros de sincronizacion de transferencias en local
	$sql="TRUNCATE sys_sincronizacion_registros_transferencias";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_registros_transferencias en local : {$restauration_stm} ");
	}
//36 Vaciar tabla de registros de sincronizacion de movimientos proveedor producto en local
	$sql="TRUNCATE sys_sincronizacion_registros_movimientos_proveedor_producto";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_registros_movimientos_proveedor_producto en local : {$restauration_stm} ");
	}
//37 Vaciar tabla de registros de sincronizacion de movimientos almacen en local
	$sql="TRUNCATE sys_sincronizacion_registros_movimientos_almacen";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_registros_movimientos_almacen en local : {$restauration_stm} ");
	}
	$sql="TRUNCATE sys_sincronizacion_peticion";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_peticion en local : {$restauration_stm} ");
	}
//38 Vaciar tabla de registro de peticiones en local
	$sql="TRUNCATE sys_sincronizacion_movimientos_proveedor_producto";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_movimientos_proveedor_producto en local : {$restauration_stm} ");
	}
//39 Vaciar tabla de sincronizacion de movimientos de almacen en local
	$sql="TRUNCATE sys_sincronizacion_movimientos_almacen";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_movimientos_almacen en local : {$restauration_stm} ");
	}
//40 Vaciar tabla de sincronizacion de devoluciones en local
	$sql="TRUNCATE sys_sincronizacion_devoluciones";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar sys_sincronizacion_devoluciones en local : {$restauration_stm} ");
	}
/*
	$sql="TRUNCATE sys_sincronizacion_registros_facturacion";
	$restauration_stm = excecuteQuery( $sql, $link );
	if( $restauration_stm != "ok" ){
		die("Error al eliminar bloques sys_sincronizacion_registros_facturacion en local : {$restauration_stm} ");
	}
*/	





/*$sql="UPDATE ec_transferencias SET id_global=id_transferencia WHERE (id_sucursal_origen=$id_suc OR id_sucursal_destino=$id_suc) AND id_global=0";*/
/*$sql="UPDATE ec_sesion_caja SET id_equivalente=id_sesion_caja,sincronizar=0";*/
/*$sql="UPDATE sys_respaldos SET realizado=1";*/
?>