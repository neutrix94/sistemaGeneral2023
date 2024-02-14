DROP PROCEDURE IF EXISTS buscaPagosDevolucionesPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaPagosDevolucionesPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE return_payment_id BIGINT DEFAULT NULL;
	DECLARE return_unique_folio VARCHAR(30);
	DECLARE teller_session_id INT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE teller_payment_unique_folio VARCHAR(30);
	DECLARE recorre CURSOR FOR
		SELECT 
			dp.id_devolucion_pago,
			d.folio_unico,
			dp.id_sesion_caja,
			cc.folio_unico
		FROM ec_devolucion_pagos dp
		LEFT JOIN ec_devolucion d
		ON d.id_devolucion = dp.id_devolucion
		LEFT JOIN ec_cajero_cobros cc
		ON cc.id_cajero_cobro = dp.id_cajero_cobro
		WHERE d.id_sucursal = store_id
		AND d.folio_unico IS NOT NULL
		AND dp.folio_unico IS NULL
/*implementacion Oscar 2024-02-12 para solo sincronizar devoluciones con pago de cajero sincronizado*/
		AND cc.id_cajero_cobro != ''
/*fin de cambio Oscar 2024-02-12*/
		GROUP BY dp.id_devolucion_pago
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET return_payment_id = NULL;/*resetea id de venta*/
		SET return_unique_folio = NULL;
		SET teller_session_id = NULL;
		SET teller_payment_unique_folio = '';
		loop_recorre: LOOP  	
				FETCH recorre INTO return_payment_id, return_unique_folio, teller_session_id, teller_payment_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
				UPDATE ec_devolucion_pagos dp
					SET dp.folio_unico = CONCAT( origin_store_prefix, '_DEVPAGO_', dp.id_devolucion_pago ),/*actualiza folio_unico de pago*/
					dp.sincronizar = 0
				WHERE dp.id_devolucion_pago = return_payment_id;
				CALL generaRegistroSincronizacionPagoDevolucion( return_payment_id, teller_session_id, return_unique_folio, origin_store_id, teller_payment_unique_folio );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$