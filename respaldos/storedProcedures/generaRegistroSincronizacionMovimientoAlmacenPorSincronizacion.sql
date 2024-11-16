DROP PROCEDURE IF EXISTS generaRegistroSincronizacionMovimientoAlmacenPorSincronizacion| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionMovimientoAlmacenPorSincronizacion( IN movement_header_id BIGINT, IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;

	INSERT INTO sys_sincronizacion_movimientos_almacen ( id_sincronizacion_movimiento_almacen, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	SELECT
		NULL,
		CONCAT(
			'{ "id_movimiento_almacen" : "NULL", ',
				'"id_tipo_movimiento" : "', ma.id_tipo_movimiento ,'", ',
				'"id_usuario" : "', ma.id_usuario ,'", ',
				'"id_sucursal" : "', ma.id_sucursal ,'", ',
				'"fecha" : "', ma.fecha ,'", ',
				'"hora" : "', ma.hora ,'", ',
				'"observaciones" : "', ma.observaciones ,'", ',
				'"id_pedido" : "-1", ',
				'"id_orden_compra" : "', ma.id_orden_compra ,'", ',
				'"lote" : "-1", ',
				'"id_maquila" : "-1", ',
				'"id_transferencia" :',
				IF( ma.id_transferencia = -1,
					'"-1", ',
					CONCAT( '"( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'', 
							( SELECT
								folio_unico
							FROM ec_transferencias
							WHERE id_transferencia = ma.id_transferencia
							LIMIT 1 ), 
						'\' LIMIT 1 )",' 
					)
				),	
				'"id_almacen" : "', ma.id_almacen ,'", ',
				'"status_agrupacion" : "', ma.status_agrupacion ,'", ',
				'"id_equivalente" : "-1", ',
				'"ultima_sincronizacion" : "', IF( ma.ultima_sincronizacion IS NULL, '', ma.ultima_sincronizacion ) ,'", ',
				'"ultima_actualizacion" : "', IF( ma.ultima_actualizacion IS NULL, '', ma.ultima_actualizacion ) ,'", ',
				'"id_pantalla" : "', ma.id_pantalla,'",',
				'"folio_unico" : "', ma.folio_unico ,'" ',
			IF( md.id_movimiento_almacen_detalle IS NOT NULL,
				CONCAT( ', "movimiento_detail" : [',
					GROUP_CONCAT(
						CONCAT( 
							'{ "id_movimiento_almacen_detalle" : "NULL", ',
								'"id_producto" : "', md.id_producto ,'", ',
								'"cantidad" : "', md.cantidad ,'", ',
								'"cantidad_surtida" : "', ( tm.afecta * md.cantidad ) ,'", ',
								CONCAT( '"id_pedido_detalle" : ',
									IF( md.id_pedido_detalle = -1 OR md.id_pedido_detalle IS NULL,
										'"-1",',
										CONCAT( '"( SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE folio_unico = \'',
											( SELECT folio_unico FROM ec_pedidos_detalle WHERE id_pedido_detalle = md.id_pedido_detalle ),
											'\' LIMIT 1 )",'
										)
									)
								),
								'"id_oc_detalle" : "-1", ',
								'"id_proveedor_producto" : "', IF( md.id_proveedor_producto IS NOT NULL, md.id_proveedor_producto, 'NULL' ) ,'", ',
								'"id_equivalente" : "-1", ',
								'"sincronizar" : "', md.sincronizar ,'", ',
								'"folio_unico" : "', md.folio_unico ,'" ',

							'}'
						)
						SEPARATOR ','
					),
					']'
				),
				''
			),
			'}'
		),
		'ec_movimiento_almacen',
		ma.folio_unico,
		IF( origin_store_id = -1, ma.id_sucursal, -1 ),
		1
	FROM ec_movimiento_almacen ma
	LEFT JOIN ec_movimiento_detalle md
	ON ma.id_movimiento_almacen = md.id_movimiento
	LEFT JOIN ec_tipos_movimiento tm
	ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
	WHERE ma.id_movimiento_almacen = movement_header_id
	GROUP BY ma.id_movimiento_almacen;

END $$