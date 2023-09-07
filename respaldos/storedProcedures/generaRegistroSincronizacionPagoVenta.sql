DROP PROCEDURE IF EXISTS generaRegistroSincronizacionPagoVenta| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionPagoVenta( IN sale_payment_id BIGINT, IN teller_session_id INT, IN sale_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE client_unique_folio VARCHAR( 30 );
    DECLARE teller_session_unique_folio VARCHAR( 30 );

    IF( teller_session_id IS NOT NULL AND teller_session_id > 0 )
	THEN
		SELECT 
	        folio_unico INTO teller_session_unique_folio 
        FROM ec_sesion_caja 
        WHERE id_sesion_caja = teller_session_id;
	END IF;
	/*SET GLOBAL group_concat_max_len = 900000;*/
	/*INSERT INTO sys_sincronizacion_ventas ( id_sincronizacion_venta, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	*/
	INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, p.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"action_type" : "insert",',
				'"table_name" : "ec_pedido_pagos",\n',
				'"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio ,'\' )",\n',
				'"id_tipo_pago" : "', pp.id_tipo_pago, '",',
				'"fecha" : "', pp.fecha, '",',
				'"hora" : "', pp.hora, '",',
				'"monto" : "', pp.monto, '",',
				'"referencia" : "', pp.referencia, '",',
				'"id_moneda" : "', pp.id_moneda, '",',
				'"tipo_cambio" : "', pp.tipo_cambio, '",',
				'"id_nota_credito" : "', pp.id_nota_credito, '",',
				'"id_cxc" : "', pp.id_cxc, '",',
				'"exportado" : "', pp.exportado, '",',
				'"es_externo" : "', pp.es_externo, '",',
				'"id_cajero" : "', pp.id_cajero, '",',
				'"folio_unico" : "', IF( pp.folio_unico IS NULL, '', pp.folio_unico ), '",',
                IF( pp.id_sesion_caja != 0, 
                    CONCAT( '"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",\n' ),
                    ''
                ),
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionPagoVenta',
		1
	FROM ec_pedido_pagos pp
	LEFT JOIN ec_pedidos p
	ON p.id_pedido = pp.id_pedido
	WHERE pp.id_pedido_pago = sale_payment_id
	GROUP BY pp.id_pedido_pago;
END $$