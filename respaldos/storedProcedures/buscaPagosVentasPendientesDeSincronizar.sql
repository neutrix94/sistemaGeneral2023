DROP PROCEDURE IF EXISTS buscaPagosVentasPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaPagosVentasPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE sale_payment_id BIGINT DEFAULT NULL;
	DECLARE sale_unique_folio VARCHAR(30);
	DECLARE teller_session_id INT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE teller_payment_unique_folio VARCHAR(30);
	DECLARE recorre CURSOR FOR
		SELECT 
			pp.id_pedido_pago,
			p.folio_unico,
			pp.id_sesion_caja,
			cc.folio_unico
		FROM ec_pedido_pagos pp
		LEFT JOIN ec_pedidos p
		ON p.id_pedido = pp.id_pedido
		LEFT JOIN ec_cajero_cobros cc
		ON cc.id_cajero_cobro = pp.id_cajero_cobro
		LEFT JOIN ec_cajero_cobros cc
		ON cc.id_cajero_cobro = pp.id_cajero_cobro
		WHERE p.id_sucursal = store_id
		AND p.folio_unico IS NOT NULL
		AND pp.folio_unico IS NULL
		GROUP BY pp.id_pedido_pago
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET sale_payment_id = NULL;/*resetea id de venta*/
		SET sale_unique_folio = NULL;
		SET teller_session_id = NULL;
		loop_recorre: LOOP  	
				FETCH recorre INTO sale_payment_id, sale_unique_folio, teller_session_id, teller_payment_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
				UPDATE ec_pedido_pagos pp
					SET pp.folio_unico = CONCAT( origin_store_prefix, '_VTAPAG_', pp.id_pedido_pago ),/*actualiza folio_unico de pago*/
					pp.sincronizar = 0
				WHERE pp.id_pedido_pago = sale_payment_id;
				CALL generaRegistroSincronizacionPagoVenta( sale_payment_id, teller_session_id, sale_unique_folio, origin_store_id, teller_payment_unique_folio );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$