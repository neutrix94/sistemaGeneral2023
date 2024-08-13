DROP PROCEDURE IF EXISTS buscaValidacionesProveedorProductoPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaValidacionesProveedorProductoPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE user_validation_id BIGINT DEFAULT NULL;
	DECLARE detail_unique_folio VARCHAR(30);
	DECLARE fecha_base VARCHAR(10);
	DECLARE validation_store INT;
	DECLARE recorre CURSOR FOR
		SELECT 
			pvu.id_pedido_validacion,
			IF( pd.folio_unico IS NULL, NULL, pd.folio_unico )
		FROM ec_pedidos_validacion_usuarios pvu
		LEFT JOIN ec_pedidos_detalle pd
		ON pvu.id_pedido_detalle = pd.id_pedido_detalle
		WHERE pvu.id_sucursal = store_id
		AND ( pvu.folio_unico IS NULL )
		AND pvu.validacion_finalizada = 1/*3-Julio se agrega validacion_finalizada*/
		AND IF( pd.id_pedido_detalle IS NOT NULL, pd.folio_unico IS NOT NULL, 1 = 1 )
		GROUP BY pvu.id_pedido_validacion
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
			SET user_validation_id = NULL;/*resetea id de venta*/
			SET detail_unique_folio = NULL;/*resetea folio unico*/
		loop_recorre: LOOP  	
				FETCH recorre INTO user_validation_id, detail_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
			/*Genera los folios unicos de validacion*/
				UPDATE ec_pedidos_validacion_usuarios pvu
					SET pvu.folio_unico = CONCAT( origin_store_prefix, '_VALID_', pvu.id_pedido_validacion )
				WHERE pvu.id_pedido_validacion = user_validation_id;

				CALL generaRegistroSincronizacionValidacionVentas( user_validation_id, detail_unique_folio, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$