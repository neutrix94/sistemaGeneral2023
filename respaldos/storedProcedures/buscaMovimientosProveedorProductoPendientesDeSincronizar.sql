DROP PROCEDURE IF EXISTS buscaMovimientosProveedorProductoPendientesDeSincronizar|
DELIMITER $$
CREATE PROCEDURE buscaMovimientosProveedorProductoPendientesDeSincronizar( IN store_id INTEGER(11), IN origin_store_id INTEGER(11), IN origin_store_prefix VARCHAR(10), IN system_limit INTEGER(11)  )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE product_provider_movement_id BIGINT DEFAULT NULL;
	DECLARE detail_unique_folio VARCHAR(30);
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT 
			mdpp.id_movimiento_detalle_proveedor_producto,
			IF( md.folio_unico IS NOT NULL, md.folio_unico, NULL ) 
		FROM ec_movimiento_detalle_proveedor_producto mdpp
		LEFT JOIN ec_movimiento_detalle md
		ON mdpp.id_movimiento_almacen_detalle = md.id_movimiento_almacen_detalle
		LEFT JOIN ec_pedidos_validacion_usuarios pvu
		ON mdpp.id_pedido_validacion = pvu.id_pedido_validacion
		WHERE mdpp.id_sucursal = store_id
		AND mdpp.folio_unico IS NULL
		AND IF( mdpp.id_movimiento_almacen_detalle IS NOT NULL, md.folio_unico IS NOT NULL, 1 = 1 )
		AND IF( mdpp.id_pedido_validacion IS NOT NULL, pvu.folio_unico IS NOT NULL, 1 = 1 )
		GROUP BY mdpp.id_movimiento_detalle_proveedor_producto
		LIMIT system_limit;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
			SET product_provider_movement_id = NULL;/*resetea id de venta*/
			SET detail_unique_folio = NULL;/*resetea folio unico*/
		loop_recorre: LOOP  	
				FETCH recorre INTO product_provider_movement_id, detail_unique_folio;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			START TRANSACTION;
			/*Genera los folios unicos de validacion*/
				UPDATE ec_movimiento_detalle_proveedor_producto mdpp
					SET mdpp.folio_unico = CONCAT( origin_store_prefix, '_MDPP_', 
						mdpp.id_movimiento_detalle_proveedor_producto )
				WHERE mdpp.id_movimiento_detalle_proveedor_producto = product_provider_movement_id;

				CALL generaRegistroSincronizacionMovimientosProveedorProducto( product_provider_movement_id, detail_unique_folio, origin_store_id );
			COMMIT;
		END LOOP;
	CLOSE recorre;   
END $$