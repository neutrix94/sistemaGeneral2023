DROP PROCEDURE IF EXISTS regresarProcesoRecepcionTransferenciaPorBloque| 
DELIMITER $$
CREATE PROCEDURE regresarProcesoRecepcionTransferenciaPorBloque( IN reception_block_id INTEGER(11) )
BEGIN
	DECLARE transfer_resolution_id_with_movements INT(11) DEFAULT 0;
	DECLARE transfer_resolution_id_without_movements INT(11) DEFAULT 0;

/**/	
	SELECT 
		t.id_transferencia 
	INTO transfer_resolution_id_with_movements
	FROM ec_bloques_transferencias_validacion_detalle btvd
	LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
	ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
	LEFT JOIN ec_transferencias t 
	ON btvd.id_transferencia = t.id_transferencia
	WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
	AND t.id_tipo = 9;
	IF( transfer_resolution_id_with_movements != '' AND transfer_resolution_id_with_movements IS NOT NULL AND transfer_resolution_id_with_movements !=  0 )
	THEN
	/*elimina movimientos de almacen a nivel proveedor producto*/
		DELETE FROM ec_movimiento_detalle_proveedor_producto 
		WHERE id_movimiento_almacen_detalle IN(
			SELECT
				id_movimiento_almacen_detalle
			FROM ec_movimiento_detalle
			WHERE id_movimiento IN(
				SELECT
					id_movimiento_almacen
				FROM ec_movimiento_almacen
				WHERE id_transferencia = transfer_resolution_id_with_movements
			)
		);
	/*elimina movimientos de almacen a nivel producto*/
		DELETE FROM ec_movimiento_detalle
		WHERE id_movimiento IN(
			SELECT
				id_movimiento_almacen
			FROM ec_movimiento_almacen
			WHERE id_transferencia = transfer_resolution_id_with_movements
		);
	/*elimina cabeceras de movimientos de almacen*/
		DELETE FROM ec_movimiento_almacen
			WHERE id_transferencia = transfer_resolution_id_with_movements;
	/*elimina el detalle de la transferencia*/
		DELETE FROM ec_transferencia_productos WHERE id_transferencia = transfer_resolution_id_without_movements;
	/*elimina la transferencia*/
		DELETE FROM ec_transferencias WHERE id_transferencia = transfer_resolution_id_without_movements;
	END IF;

/**/
	SELECT 
		t.id_transferencia 
	INTO transfer_resolution_id_without_movements
	FROM ec_bloques_transferencias_validacion_detalle btvd
	LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
	ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
	LEFT JOIN ec_transferencias t 
	ON btvd.id_transferencia = t.id_transferencia
	WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
	AND t.id_tipo = 12;
	IF( transfer_resolution_id_without_movements != '' AND transfer_resolution_id_without_movements IS NOT NULL AND transfer_resolution_id_without_movements !=  0 )
	THEN
	/*elimina movimientos de almacen a nivel proveedor producto*/
		DELETE FROM ec_movimiento_detalle_proveedor_producto 
		WHERE id_movimiento_almacen_detalle IN(
			SELECT
				id_movimiento_almacen_detalle
			FROM ec_movimiento_detalle
			WHERE id_movimiento IN(
				SELECT
					id_movimiento_almacen
				FROM ec_movimiento_almacen
				WHERE id_transferencia = transfer_resolution_id_without_movements
			)
		);
	/*elimina movimientos de almacen a nivel producto*/
		DELETE FROM ec_movimiento_detalle
		WHERE id_movimiento IN(
			SELECT
				id_movimiento_almacen
			FROM ec_movimiento_almacen
			WHERE id_transferencia = transfer_resolution_id_without_movements
		);
	/*elimina cabeceras de movimientos de almacen*/
		DELETE FROM ec_movimiento_almacen
			WHERE id_transferencia = transfer_resolution_id_without_movements;
	/*elimina el detalle de la transferencia*/
		DELETE FROM ec_transferencia_productos WHERE id_transferencia = transfer_resolution_id_without_movements;
	/*elimina la transferencia*/
		DELETE FROM ec_transferencias WHERE id_transferencia = transfer_resolution_id_without_movements;
	END IF;

/*elimina detalle de movimientos a nivel proveedor producto de entrada de las transferencias del bloque*/
	DELETE FROM ec_movimiento_detalle_proveedor_producto
	WHERE id_movimiento_almacen_detalle 
	IN( 
		SELECT
			id_movimiento_almacen_detalle
		FROM ec_movimiento_detalle
		WHERE id_movimiento 
		IN(
			SELECT
				ma.id_movimiento_almacen
			FROM ec_transferencias t 
			LEFT JOIN ec_movimiento_almacen ma
			ON ma.id_transferencia = t.id_transferencia
			RIGHT JOIN ec_bloques_transferencias_validacion_detalle btvd
			ON btvd.id_transferencia = t.id_transferencia
			RIGHT JOIN ec_bloques_transferencias_recepcion_detalle btrd
			ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
			WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
		)
	);

/*elimina detalle de movimientos a nivel producto de entrada de las transferencias del bloque*/
	DELETE FROM ec_movimiento_detalle
	WHERE id_movimiento 
	IN(
		SELECT
			ma.id_movimiento_almacen
		FROM ec_transferencias t 
		LEFT JOIN ec_movimiento_almacen ma
		ON ma.id_transferencia = t.id_transferencia
		RIGHT JOIN ec_bloques_transferencias_validacion_detalle btvd
		ON btvd.id_transferencia = t.id_transferencia
		RIGHT JOIN ec_bloques_transferencias_recepcion_detalle btrd
		ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
		WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
	);

/*elimina cabeceras de movimientos de entrada de las transferencias del bloque*/
	DELETE FROM ec_movimiento_almacen
	WHERE id_movimiento_almacen
	IN( SELECT
			ax.id_movimiento_almacen
		FROM(
			SELECT
				ma.id_movimiento_almacen
			FROM ec_transferencias t 
			LEFT JOIN ec_movimiento_almacen ma
			ON ma.id_transferencia = t.id_transferencia
			RIGHT JOIN ec_bloques_transferencias_validacion_detalle btvd
			ON btvd.id_transferencia = t.id_transferencia
			RIGHT JOIN ec_bloques_transferencias_recepcion_detalle btrd
			ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
			WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
		)ax
	);
/*resetea los campos de cantidades recibidas en el detalle de la transferencia*/
	UPDATE ec_transferencia_productos SET 
		cantidad_cajas_recibidas = 0,
		cantidad_paquetes_recibidos = 0,
		cantidad_piezas_recibidas= 0,
		total_piezas_recibidas = 0,
		resuelto = '0'
	WHERE id_transferencia IN( 
		SELECT
			btvd.id_transferencia
		FROM ec_bloques_transferencias_validacion_detalle btvd
		LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
		ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
		WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
	);
/*regresa el status de la transferencia*/
	UPDATE ec_transferencias 
		SET id_estado = 8 
	WHERE id_transferencia IN(
		SELECT
			btvd.id_transferencia
		FROM ec_bloques_transferencias_validacion_detalle btvd
		LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
		ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
		WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
	);
/*elimina los escaneos*/
	DELETE FROM ec_transferencias_recepcion_usuarios 
	WHERE id_transferencia_producto IN(
		SELECT
			tp.id_transferencia_producto
		FROM ec_bloques_transferencias_validacion_detalle btvd
		LEFT JOIN ec_bloques_transferencias_recepcion_detalle btrd
		ON btrd.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
		LEFT JOIN ec_transferencia_productos tp ON tp.id_transferencia = btvd.id_transferencia
		WHERE btrd.id_bloque_transferencia_recepcion = reception_block_id
		AND tp.id_transferencia_producto IS NOT NULL

	);
/*resetea los codigos unicos del bloque a no recibidos*/
	UPDATE ec_transferencia_codigos_unicos 
		SET id_bloque_transferencia_recepcion = NULL
	WHERE id_bloque_transferencia_recepcion = reception_block_id;
/*elimina los productos de la resolucion temporal*/
	DELETE FROM ec_productos_resoluciones_tmp 
	WHERE id_bloque_transferencia_recepcion = reception_block_id;
/*elimina los escaneos del bloque de transferencias*/
	DELETE FROM ec_bloques_transferencias_resolucion_escaneos 
	WHERE id_bloque_transferencia_resolucion 
	IN(
		SELECT 
			id_bloque_transferencia_resolucion 
		FROM ec_bloques_transferencias_resolucion 
		WHERE id_bloque_transferencia_recepcion = reception_block_id
	);
	
/*elimina detalle de la resolucion*/
	DELETE FROM ec_bloques_transferencias_resolucion_detalle 
	WHERE id_bloque_transferencia_resolucion 
	IN(
		SELECT 
			id_bloque_transferencia_resolucion 
		FROM ec_bloques_transferencias_resolucion 
		WHERE id_bloque_transferencia_recepcion = reception_block_id
	);
/*elimina cabecera de la resolucion*/
	DELETE FROM ec_bloques_transferencias_resolucion 
		WHERE id_bloque_transferencia_recepcion = reception_block_id;
/*elimina los codigos unicos en resolucion*/
	DELETE FROM ec_transferencia_codigos_unicos 
	WHERE id_bloque_transferencia_recepcion = reception_block_id
	AND insertado_por_resolucion = '1';
/*actualiza el bloque a no recibido*/
	UPDATE ec_bloques_transferencias_recepcion 
		SET recibido = '0' 
	WHERE id_bloque_transferencia_recepcion = reception_block_id;

END $$