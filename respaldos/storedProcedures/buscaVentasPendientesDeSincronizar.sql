DROP PROCEDURE IF EXISTS buscaVentasPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaVentasPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE sale_header_id BIGINT DEFAULT NULL;
	DECLARE teller_session_id INT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT 
			p.id_pedido,
			p.id_sesion_caja
		FROM ec_pedidos p
		LEFT JOIN ec_pedidos_detalle pd
		ON p.id_pedido = pd.id_pedido
		WHERE p.id_sucursal = store_id
		AND p.folio_unico IS NULL
		AND pd.id_pedido_detalle IS NOT NULL
		GROUP BY p.id_pedido
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET sale_header_id = NULL;/*resetea id de venta*/
		SET teller_session_id = NULL;/*resetea id de venta*/
		loop_recorre: LOOP  	
				FETCH recorre INTO sale_header_id, teller_session_id;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
			/*Genera los folios unicos ( cabecera )*/
				UPDATE ec_pedidos p 
					SET p.folio_unico = CONCAT( origin_store_prefix, '_VTA_', p.id_pedido )/*actualiza folio_unico de pedido*/
				WHERE p.id_pedido = sale_header_id;
			/*Genera los folios unicos ( detalle )*/
				UPDATE ec_pedidos_detalle pd
					SET pd.folio_unico = CONCAT( origin_store_prefix, '_VTADET_', pd.id_pedido_detalle )/*actualiza folio_unico de detalle(s)*/
				WHERE pd.id_pedido = sale_header_id;
			/*Genera los folios unicos ( pagos )
				UPDATE ec_pedido_pagos pp
					SET pp.folio_unico = CONCAT( origin_store_prefix, '_VTAPAG_', pp.id_pedido_pago )actualiza folio_unico de pago(s)
				WHERE pp.id_pedido = sale_header_id;*/

				CALL generaRegistroSincronizacionVenta( sale_header_id, teller_session_id, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$