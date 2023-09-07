DROP TRIGGER IF EXISTS insertaTransferenciaRecepcionActual|
DELIMITER $$
CREATE TRIGGER insertaTransferenciaRecepcionActual
BEFORE INSERT ON ec_transferencias_recepcion_actual
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
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
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
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_bloques_transferencias_recepcion_detalle'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_BTRD_', row_id );
	/*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_recepcion_actual",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				/*'', */
				IF( new.id_bloque_transferencia_recepcion IS NOT NULL 
					AND new.id_bloque_transferencia_recepcion != 0,
					CONCAT( '"id_bloque_transferencia_recepcion" : "( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'', 
						reception_block_unique_folio,'\' LIMIT 1 )",'),
					""
				),
				IF( new.id_bloque_transferencia_validacion IS NOT NULL 
					AND new.id_bloque_transferencia_validacion != 0,
					CONCAT( '"id_bloque_transferencia_validacion" : "( SELECT id_bloque_transferencia_validacion FROM ec_bloques_transferencias_validacion WHERE folio_unico = \'', 
					validation_block_unique_folio,'\' LIMIT 1 )",' ),
					""
				),
				/*'',*/ 
				'"id_usuario_alta" : "', new.id_usuario_alta, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaTransferenciaRecepcionActual',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$