DROP PROCEDURE IF EXISTS buscaDevolucionesPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaDevolucionesPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE return_header_id BIGINT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT d.id_devolucion
		FROM ec_devolucion d
		LEFT JOIN ec_devolucion_detalle dd
		ON d.id_devolucion = dd.id_devolucion
		LEFT JOIN ec_pedidos p
		ON p.id_pedido = d.id_pedido
		WHERE d.id_sucursal = store_id
		AND d.folio_unico IS NULL 
		AND p.folio_unico IS NOT NULL
		GROUP BY d.id_devolucion
		LIMIT system_limit;
/*deshabilitado por Oscar 25-Agosto-2023 ( cuando se hicieron pruenas de restauracion ) ( d.folio_unico IS NULL OR d.folio_unico = 0 OR d.folio_unico = -1 )*/
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
			SET return_header_id = NULL;/*resetea id de venta*/
		loop_recorre: LOOP  	
				FETCH recorre INTO return_header_id;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
			/*Genera los folios unicos ( cabecera )*/
				UPDATE ec_devolucion d 
					SET d.folio_unico = CONCAT( origin_store_prefix, '_DEV_', d.id_devolucion )/*actualiza folio_unico de devolucion*/
				WHERE d.id_devolucion = return_header_id;

			/*Genera los folios unicos ( detalle )*/
				UPDATE ec_devolucion_detalle dd  
				JOIN ec_devolucion d
				ON d.id_devolucion = dd.id_devolucion 
					SET dd.folio_unico = CONCAT( origin_store_prefix, '_DEVDET_', dd.id_devolucion_detalle )/*actualiza folio_unico de detalle(s)*/
				WHERE dd.id_devolucion = return_header_id;
			/*Genera los folios unicos ( pagos )*/
				UPDATE ec_devolucion_pagos dp
				JOIN ec_devolucion d 
				ON d.id_devolucion = dp.id_devolucion
					SET dp.folio_unico = CONCAT( origin_store_prefix, '_DEVPAG_', dp.id_devolucion_pago )/*actualiza folio_unico de pago(s)*/
				WHERE dp.id_devolucion = return_header_id;

				CALL generaRegistroSincronizacionDevolucion( return_header_id, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$