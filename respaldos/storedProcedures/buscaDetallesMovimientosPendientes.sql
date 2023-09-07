DROP PROCEDURE IF EXISTS buscaDetallesMovimientosPendientes|
DELIMITER $$
CREATE PROCEDURE buscaDetallesMovimientosPendientes( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE movement_detail_id BIGINT DEFAULT NULL;
	DECLARE movement_unique_folio VARCHAR(30);
	DECLARE recorre CURSOR FOR
		SELECT 
			md.id_movimiento_almacen_detalle,
			ma.folio_unico
		FROM ec_movimiento_detalle md
		LEFT JOIN ec_movimiento_almacen ma
		ON md.id_movimiento = ma.id_movimiento_almacen
		WHERE ma.id_sucursal = store_id
		AND ma.folio_unico IS NOT NULL
		AND md.folio_unico IS NULL
		GROUP BY md.id_movimiento_almacen_detalle
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET movement_detail_id = NULL;/*resetea id de venta*/
		SET movement_unique_folio = NULL;
		loop_recorre: LOOP  	
				FETCH recorre INTO movement_detail_id, movement_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
				UPDATE ec_movimiento_detalle md
					SET md.folio_unico = CONCAT( origin_store_prefix, '_MD_', md.id_movimiento_almacen_detalle ),/*actualiza folio_unico de detalle(s)*/
					md.sincronizar = 0
				WHERE md.id_movimiento_almacen_detalle = movement_detail_id;
				CALL generaRegistroSincronizacionDetalleMovimientoAlmacen( movement_detail_id, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$