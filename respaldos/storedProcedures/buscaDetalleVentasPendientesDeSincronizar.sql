DROP PROCEDURE IF EXISTS buscaDetalleVentasPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaDetalleVentasPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE sale_detail_id BIGINT DEFAULT NULL;
	DECLARE sale_unique_folio VARCHAR(30);
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT 
			pd.id_pedido_detalle,
			p.folio_unico
		FROM ec_pedidos_detalle pd
		LEFT JOIN ec_pedidos p
		ON p.id_pedido = pd.id_pedido
		WHERE p.id_sucursal = store_id
		AND p.folio_unico IS NOT NULL
		AND pd.folio_unico IS NULL
		GROUP BY pd.id_pedido_detalle
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET sale_detail_id = NULL;/*resetea id de venta*/
		SET sale_unique_folio = NULL;
		loop_recorre: LOOP  	
				FETCH recorre INTO sale_detail_id, sale_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
				UPDATE ec_pedidos_detalle pd
					SET pd.folio_unico = CONCAT( origin_store_prefix, '_VTADET_', pd.id_pedido_detalle )/*actualiza folio_unico de detalle(s)*/
				WHERE pd.id_pedido_detalle = sale_detail_id;
				CALL generaRegistroSincronizacionDetalleVenta( sale_detail_id, sale_unique_folio, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$