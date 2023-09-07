DROP TRIGGER IF EXISTS actualizaBloqueTransferenciaRecepcionDetalle|
DELIMITER $$
CREATE TRIGGER actualizaBloqueTransferenciaRecepcionDetalle
BEFORE UPDATE ON ec_bloques_transferencias_recepcion_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE reception_block_unique_folio VARCHAR( 30 );
	DECLARE validation_block_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
	/*Consulta folio unico de bloque recepcion*/
		SELECT 
			folio_unico
		INTO
			reception_block_unique_folio
		FROM ec_bloques_transferencias_recepcion
		WHERE id_bloque_transferencia_recepcion = new.id_bloque_transferencia_recepcion;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			validation_block_unique_folio
		FROM ec_bloques_transferencias_validacion
		WHERE id_bloque_transferencia_validacion = new.id_bloque_transferencia_validacion;
    /*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_recepcion_detalle",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_bloque_transferencia_recepcion" : "', 
				'( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'', 
					reception_block_unique_folio,'\' LIMIT 1 )",',
				'"id_bloque_transferencia_validacion" : "', 
				'( SELECT id_bloque_transferencia_validacion FROM ec_bloques_transferencias_validacion WHERE folio_unico = \'', 
					validation_block_unique_folio,'\' LIMIT 1 )",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"invalidado" : "', new.invalidado, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaBloqueTransferenciaRecepcionDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$