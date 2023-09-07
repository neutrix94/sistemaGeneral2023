DROP PROCEDURE IF EXISTS buscaMovimientosPendientes|
DELIMITER $$
CREATE PROCEDURE buscaMovimientosPendientes( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE movement_header_id BIGINT DEFAULT NULL;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT ma.id_movimiento_almacen
		FROM ec_movimiento_almacen ma
		LEFT JOIN ec_movimiento_detalle md
		ON ma.id_movimiento_almacen = md.id_movimiento
		WHERE ma.id_sucursal = store_id
		AND ma.folio_unico IS NULL
		AND md.id_movimiento_almacen_detalle IS NOT NULL
		AND ma.id_pedido NOT IN( SELECT id_pedido FROM ec_pedidos WHERE folio_unico IS NULL )
		AND md.id_pedido_detalle NOT IN( SELECT id_pedido_detalle FROM ec_pedidos_detalle WHERE folio_unico IS NULL )
		GROUP BY ma.id_movimiento_almacen
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET movement_header_id = NULL;/*reseteamos la fecha*/
		loop_recorre: LOOP  	
				FETCH recorre INTO movement_header_id;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
		/*Genera los folios unicos*/
				UPDATE ec_movimiento_almacen ma 
				JOIN ec_movimiento_detalle md 
				ON md.id_movimiento = ma.id_movimiento_almacen 
					SET ma.folio_unico = CONCAT( origin_store_prefix, '_MA_', ma.id_movimiento_almacen ),
					md.folio_unico = CONCAT( origin_store_prefix, '_MD_', md.id_movimiento_almacen_detalle ),
					ma.sincronizar = 0, /*oscar*/
					md.sincronizar = 0 /*oscar*/
				WHERE ma.id_movimiento_almacen = movement_header_id;

				CALL generaRegistroSincronizacionMovimientoAlmacenPorSincronizacion( movement_header_id, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$