DROP PROCEDURE IF EXISTS generaRegistroSincronizacionCajeroCobros| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionCajeroCobros( IN payment_id BIGINT, IN teller_session_id INT, IN sale_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11), IN type VARCHAR( 30 ) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
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
	INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, p.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"action_type" : "insert",',
				'"table_name" : "ec_cajeros_cobros",\n',
				'"primary_key" : "folio_unico",\n',				
				'"primary_key_value" : "', cc.folio_unico, '",\n',				
				'"id_sucursal" : "', cc.id_sucursal, '",',
				'"id_pedido" : "', IF( cc.id_pedido > 0, CONCAT( '( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio ,'\' )' ), -1 ), '",',
				'"id_devolucion" : "', IF( cc.id_devolucion > 0, CONCAT( '( SELECT id_devolucion FROM ec_devolucion WHERE folio_unico = \'', sale_unique_folio ,'\' )' ), -1 ), '",',
				'"id_cajero" : "', cc.id_cajero, '",',
				'"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",',
				'"id_afiliacion" : "', cc.id_afiliacion, '",',
				'"id_terminal" : "', cc.id_terminal, '",',
				'"id_banco" : "', cc.id_banco, '",',
				'"id_tipo_pago" : "', cc.id_tipo_pago, '",',
				'"monto" : "', cc.monto, '",',
				'"fecha" : "', cc.fecha, '",',
				'"hora" : "', cc.hora, '",',
				'"observaciones" : "', cc.observaciones, '",',
				'"folio_unico" : "', cc.folio_unico, '",',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionCajeroCobros',
		1
	FROM ec_cajero_cobros cc
	LEFT JOIN ec_pedidos p
	ON p.id_pedido = cc.id_pedido
	LEFT JOIN ec_devolucion d
	ON d.id_devolucion = cc.id_devolucion
	WHERE cc.id_cajero_cobro = payment_id
	GROUP BY cc.id_cajero_cobro;
END $$