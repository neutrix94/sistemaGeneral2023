DROP PROCEDURE IF EXISTS generaRegistroSincronizacionDetalleMovimientoAlmacen| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionDetalleMovimientoAlmacen( IN movement_detail_id BIGINT, IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	INSERT INTO sys_sincronizacion_registros_movimientos_almacen ( id_sincronizacion_registro, sucursal_de_cambio, 
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, ma.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"table_name" : "ec_movimiento_detalle",',
				'"action_type" : "insert",',
				'"id_movimiento" : "( SELECT id_movimiento_almacen FROM ec_movimiento_almacen WHERE folio_unico = \'', ma.folio_unico , '\' )",',
				'"id_producto" : "', md.id_producto, '",',
				'"cantidad" : "', md.cantidad, '",',
				'"cantidad_surtida" : "', md.cantidad_surtida, '",',
	/*cambiar aqui
				'"id_pedido_detalle" : "', IF( md.id_pedido_detalle IS NULL, '', md.id_pedido_detalle ), '",',*/

				IF( md.id_proveedor_producto IS NULL, 
					'', 
					CONCAT( '"id_proveedor_producto" : "', md.id_proveedor_producto, '",' )
				),
				'"folio_unico" : "', IF( md.folio_unico IS NULL, '', md.folio_unico ), '",',
				'"insertado_por_sincronizacion" : "1",',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionDetalleMovimientoAlmacen',
		1
	FROM ec_movimiento_detalle md
	LEFT JOIN ec_movimiento_almacen ma
	ON ma.id_movimiento_almacen = md.id_movimiento
	LEFT JOIN ec_tipos_movimiento tm
	ON  tm.id_tipo_movimiento = ma.id_tipo_movimiento
	WHERE md.id_movimiento_almacen_detalle = movement_detail_id
	GROUP BY md.id_movimiento_almacen_detalle;
/*inserta la instruccion para sumar el inventario*/
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio, id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, ma.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"table_name" : "ec_almacen_producto",',
				'"action_type" : "update",',
				'"primary_key" : "id_producto",',
				'"primary_key_value" : "', md.id_producto, '",',
				'"secondary_key" : "id_almacen",',
				'"secondary_key_value" : "', ma.id_almacen, '",',
				'"inventario" : "( inventario + ',( md.cantidad * tm.afecta ), ' )"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionDetalleMovimientoAlmacen',
		1
	FROM ec_movimiento_detalle md
	LEFT JOIN ec_movimiento_almacen ma
	ON ma.id_movimiento_almacen = md.id_movimiento
	LEFT JOIN ec_tipos_movimiento tm
	ON  tm.id_tipo_movimiento = ma.id_tipo_movimiento
	WHERE md.id_movimiento_almacen_detalle = movement_detail_id
	GROUP BY md.id_movimiento_almacen_detalle;
END $$