DROP PROCEDURE IF EXISTS buscaCajeroCobrosPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaCajeroCobrosPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11) )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE payment_id BIGINT DEFAULT NULL;
	DECLARE sale_unique_folio VARCHAR(30);
	DECLARE type VARCHAR(30);
	DECLARE teller_session_id INT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT 
			cc.id_cajero_cobro,
			IF( cc.id_pedido > 0, p.folio_unico, d.folio_unico ),
			cc.id_sesion_caja,
			IF( cc.id_pedido > 0, 'ec_pedido_pagos', 'ec_devolucion_pagos' )
		FROM ec_cajero_cobros cc
		LEFT JOIN ec_pedidos p
		ON p.id_pedido = cc.id_pedido
		LEFT JOIN ec_devolucion d
		ON d.id_devolucion = cc.id_devolucion
		WHERE cc.id_sucursal = store_id
		AND ( ( cc.id_pedido > 0 AND p.folio_unico IS NOT NULL ) OR ( cc.id_devolucion > 0 AND d.folio_unico IS NOT NULL ) )
		AND ( cc.folio_unico IS NULL OR cc.folio_unico = '' )
		GROUP BY cc.id_cajero_cobro
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET payment_id = NULL;/*resetea id de venta*/
		SET sale_unique_folio = NULL;
		SET teller_session_id = NULL;
		SET type = '';
		loop_recorre: LOOP  	
				FETCH recorre INTO payment_id, sale_unique_folio, teller_session_id, type;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
				UPDATE ec_cajero_cobros cc
					SET cc.folio_unico = CONCAT( origin_store_prefix, '_COBRO_', cc.id_cajero_cobro ),/*actualiza folio_unico de pago*/
					cc.sincronizar = 0
				WHERE cc.id_cajero_cobro = payment_id;
				CALL generaRegistroSincronizacionCajeroCobros( payment_id, teller_session_id, sale_unique_folio, origin_store_id, type );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$