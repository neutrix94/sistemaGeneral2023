DROP PROCEDURE IF EXISTS generaRegistroSincronizacionMovimientosProveedorProducto| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionMovimientosProveedorProducto( IN product_provider_movement_id BIGINT, detail_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	/*SET GLOBAL group_concat_max_len = 900000;*/
	INSERT INTO sys_sincronizacion_movimientos_proveedor_producto ( id_sincronizacion_movimiento_proveedor_producto, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	SELECT
		NULL,
		CONCAT(
			'{',
				'"id_movimiento_almacen_detalle" : ',
				IF(	detail_unique_folio IS NOT NULL,
					CONCAT( '"( SELECT id_movimiento_almacen_detalle FROM ec_movimiento_detalle WHERE folio_unico = \'', detail_unique_folio, '\' LIMIT 1)"' ),
					'"NULL"'
				), ',',
				'"id_proveedor_producto" : "', IF( mdpp.id_proveedor_producto IS NULL, '', mdpp.id_proveedor_producto ), '",',
				'"cantidad" : "', mdpp.cantidad, '",',
				'"fecha_registro" : "', mdpp.fecha_registro, '",',
				'"id_sucursal" : "', mdpp.id_sucursal, '",',
				'"status_agrupacion" : "', mdpp.status_agrupacion, '",',
				'"id_tipo_movimiento" : "', mdpp.id_tipo_movimiento, '",',
				'"id_almacen" : "', mdpp.id_almacen, '",',
				'"cantidad_surtida" : "',( mdpp.cantidad * tm.afecta ), '",',
				CONCAT( '"id_pedido_validacion" : "', 
					IF( mdpp.id_pedido_validacion = -1,
						-1,
						CONCAT( ' ( SELECT id_pedido_validacion FROM ec_pedidos_validacion_usuarios WHERE folio_unico = \'',
							( SELECT folio_unico FROM ec_pedidos_validacion_usuarios WHERE id_pedido_validacion = mdpp.id_pedido_validacion LIMIT 1 ),
							'\' )'
						)
					),
					'",'
				),
				'"folio_unico" : "', IF( mdpp.folio_unico IS NULL, '', mdpp.folio_unico ), '"',
			'}'
		),
		'ec_movimiento_detalle_proveedor_producto',
		mdpp.folio_unico,
		IF( origin_store_id = -1, mdpp.id_sucursal, -1 ),
		1
	FROM ec_movimiento_detalle_proveedor_producto mdpp
	LEFT  JOIN ec_tipos_movimiento tm
	ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
	WHERE mdpp.id_movimiento_detalle_proveedor_producto = product_provider_movement_id
	GROUP BY mdpp.id_movimiento_detalle_proveedor_producto;
END $$