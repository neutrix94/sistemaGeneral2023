DROP PROCEDURE IF EXISTS generaRegistroSincronizacionPagoDevolucion| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionPagoDevolucion( IN return_payment_id BIGINT, IN teller_session_id INT, IN return_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11), IN teller_payment_unique_folio VARCHAR(30) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
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
		IF( origin_store_id = -1, d.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"action_type" : "insert",',
				'"table_name" : "ec_devolucion_pagos",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', dp.folio_unico, '",',
				'"id_devolucion" : "( SELECT id_devolucion FROM ec_devolucion WHERE folio_unico = \'', return_unique_folio ,'\' )",',
				'"id_cajero_cobro" : "( SELECT id_cajero_cobro FROM ec_cajero_cobros WHERE folio_unico = \'', teller_payment_unique_folio ,'\' )",',
				'"id_tipo_pago" : "', dp.id_tipo_pago, '",',
				'"monto" : "', dp.monto, '",',
				'"referencia" : "', dp.referencia, '",',
				'"es_externo" : "', dp.es_externo, '",',
				'"fecha" : "', dp.fecha, '",',
				'"hora" : "', dp.hora, '",',
				'"id_cajero" : "', dp.id_cajero, '",',
				'"folio_unico" : "', dp.folio_unico, '",',
				'"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio ,'\' )",',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionPagoDevolucion',
		1
	FROM ec_devolucion_pagos dp
	LEFT JOIN ec_devolucion d
	ON d.id_devolucion = dp.id_devolucion
	WHERE dp.id_devolucion_pago = return_payment_id
	GROUP BY dp.id_devolucion_pago;
END $$