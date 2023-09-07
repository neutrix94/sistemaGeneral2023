DROP PROCEDURE IF EXISTS generaRegistroSincronizacionValidacionVentas|
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionValidacionVentas( IN user_validation_id BIGINT, detail_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	/*SET GLOBAL group_concat_max_len = 900000;*/
	INSERT INTO sys_sincronizacion_validaciones_ventas ( id_sincronizacion_validacion, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	SELECT
		NULL,
		CONCAT(
			'{',
				IF( detail_unique_folio IS NOT NULL,
					CONCAT( '"id_pedido_detalle" : "( SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE folio_unico = \'', detail_unique_folio, '\' )",' ),
					''
				),
				'"id_producto" : "', IF( pvu.id_producto IS NULL, '', pvu.id_producto ), '",',
				'"id_proveedor_producto" : "', IF( pvu.id_proveedor_producto IS NULL, '', pvu.id_proveedor_producto ), '",',
				'"piezas_validadas" : "', pvu.piezas_validadas, '",',
				'"piezas_devueltas" : "', pvu.piezas_devueltas, '",',
				'"id_usuario" : "', IF( pvu.id_usuario IS NULL, '', pvu.id_usuario ), '",',
				'"id_sucursal" : "', IF( pvu.id_sucursal IS NULL, '', pvu.id_sucursal ), '",',
				'"fecha_alta" : "', pvu.fecha_alta, '",',
				'"tipo_sistema" : "', pvu.tipo_sistema, '",',
				'"validacion_finalizada" : "', pvu.validacion_finalizada, '",',
				'"folio_unico" : "', IF( pvu.folio_unico IS NULL, '', pvu.folio_unico ), '"',
			'}'
		),
		'ec_pedidos_validacion_usuarios',
		pvu.folio_unico,
		IF( origin_store_id = -1, pvu.id_sucursal, -1 ),
		1
	FROM ec_pedidos_validacion_usuarios pvu
	WHERE pvu.id_pedido_validacion = user_validation_id
	GROUP BY pvu.id_pedido_validacion;
END $$