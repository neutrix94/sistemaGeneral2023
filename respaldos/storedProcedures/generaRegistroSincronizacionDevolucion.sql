DROP PROCEDURE IF EXISTS generaRegistroSincronizacionDevolucion| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionDevolucion( IN return_header_id BIGINT, IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
    DECLARE teller_session_unique_folio VARCHAR( 30 );
   	SELECT
   		sc.folio_unico INTO teller_session_unique_folio
   	FROM ec_devolucion d
   	LEFT JOIN ec_devolucion_pagos dp
   	ON d.id_devolucion = dp.id_devolucion
   	LEFT JOIN ec_sesion_caja sc
   	ON sc.id_sesion_caja = dp.id_sesion_caja
   	WHERE d.id_devolucion = return_header_id
   	LIMIT 1;
	/*SET GLOBAL group_concat_max_len = 900000;*/
	INSERT INTO sys_sincronizacion_devoluciones ( id_sincronizacion_devolucion, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	SELECT
		NULL,
		CONCAT(
			'{',
				'"id_usuario" : "', d.id_usuario, '",\n',
				'"id_sucursal" : "', d.id_sucursal, '",\n',
				'"fecha" : "', d.fecha, '",\n',
				'"hora" : "', d.hora, '",\n',
				'"monto_devolucion" : "', d.monto_devolucion, '",\n',
				'"id_pedido" : "( SELECT IF( id_pedido IS NULL, 0, id_pedido) FROM ec_pedidos WHERE folio_unico = \'', ( SELECT IF( id_pedido IS NULL, '0', folio_unico ) FROM ec_pedidos WHERE id_pedido = d.id_pedido LIMIT 1 ), '\' LIMIT 1 )",\n',
				'"folio" : "', d.folio, '",\n',
				'"es_externo" : "', d.es_externo, '",\n',
				'"status" : "', d.status, '",\n',
				'"observaciones" : "', d.observaciones, '",\n',
				'"tipo_sistema" : "', d.tipo_sistema, '",\n',
				'"id_status_agrupacion" : "', d.id_status_agrupacion, '",\n',
				'"folio_unico" : "', IF( d.folio_unico IS NULL, '', d.folio_unico ), '"\n',
			(SELECT
				IF( dd.id_devolucion_detalle IS NOT NULL,
					CONCAT( ', "return_detail" : [\n',
						GROUP_CONCAT(
						DISTINCT( CONCAT( 
								'{',
									IF( dd.id_pedido_detalle != 0 AND pd.id_pedido_detalle IS NOT NULL,
										CONCAT( '"id_pedido_detalle" : "( SELECT IF( id_pedido_detalle IS NULL, 0, id_pedido_detalle ) FROM ec_pedidos_detalle WHERE folio_unico = \'', 
											IF( pd.id_pedido_detalle IS NULL, '', pd.folio_unico ) , 
											'\' LIMIT 1 )",\n' 
										),
										''
									),
									'"id_producto" : "', dd.id_producto, '",\n',
									'"id_proveedor_producto" : "', dd.id_proveedor_producto, '",\n',
									'"cantidad" : "', dd.cantidad, '",\n',
									'"folio_unico" : "', IF( dd.folio_unico IS NULL, '', dd.folio_unico ), '"\n',
								'}\n'
							) )
							SEPARATOR ','
						),
						']\n'
					),
					''
				)
			FROM ec_devolucion_detalle dd
			LEFT JOIN ec_pedidos_detalle pd
			ON pd.id_pedido_detalle = dd.id_pedido_detalle
			WHERE dd.id_devolucion = d.id_devolucion
			),
			(SELECT
				IF( dp.id_devolucion_pago IS NOT NULL,
					CONCAT( ', "return_payments" : [\n',
						GROUP_CONCAT(
						DISTINCT( CONCAT(
								'{',
									'"id_tipo_pago" : "', dp.id_tipo_pago, '",',
									'"monto" : "', dp.monto, '",',
									'"referencia" : "', dp.referencia, '",',
									'"es_externo" : "', dp.es_externo, '",',
									'"fecha" : "', dp.fecha, '",',
									'"hora" : "', dp.hora, '",',
									'"id_cajero" : "', dp.id_cajero, '",',
									'"id_sesion_caja" : "', dp.id_cajero, '",',
									IF( teller_session_unique_folio IS NOT NULL AND teller_session_unique_folio != '',
										CONCAT( '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio ,'\' LIMIT 1 )",\n' ),
										'"id_sesion_caja" : "0",'
									),
									'"folio_unico" : "', IF( dp.folio_unico IS NULL, '', dp.folio_unico ), '"',
								'}\n'
							) )
							SEPARATOR ','
						),
						']\n'
					),
					''
				)
			FROM ec_devolucion_pagos dp
			WHERE dp.id_devolucion = d.id_devolucion
			),
			'}'
		),
		'ec_devolucion',
		d.folio_unico,
		IF( origin_store_id = -1, d.id_sucursal, -1 ),
		1
	FROM ec_devolucion d
	WHERE d.id_devolucion = return_header_id
	GROUP BY d.id_devolucion;
END $$