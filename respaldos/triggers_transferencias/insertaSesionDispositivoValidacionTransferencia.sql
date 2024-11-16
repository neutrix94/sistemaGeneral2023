DROP TRIGGER IF EXISTS insertaSesionDispositivoValidacionTransferencia|
DELIMITER $$
CREATE TRIGGER insertaSesionDispositivoValidacionTransferencia
BEFORE INSERT ON ec_sesiones_dispositivos_validacion_transferencias
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE validation_block_unique_folio VARCHAR( 30 );
	DECLARE reception_block_unique_folio VARCHAR( 30 );
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			validation_block_unique_folio
		FROM ec_bloques_transferencias_validacion
		WHERE id_bloque_transferencia_validacion = new.id_bloque_validacion;
	/*Consulta folio unico de bloque recepcion*/
		SELECT 
			folio_unico
		INTO
			reception_block_unique_folio
		FROM ec_bloques_transferencias_recepcion
		WHERE id_bloque_transferencia_recepcion = new.id_bloque_recepcion;
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_sesiones_dispositivos_validacion_transferencias'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_SDVT_', row_id );
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_sesiones_dispositivos_validacion_transferencias",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( new.id_bloque_recepcion IS NULL, 
					'',
					CONCAT( '"id_bloque_recepcion" : "', 
						'( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'',
						reception_block_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_bloque_validacion IS NULL, 
					'',
					CONCAT( '"id_bloque_validacion" : "', 
						'( SELECT id_bloque_transferencia_validacion FROM ec_bloques_transferencias_validacion WHERE folio_unico = \'',
						validation_block_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.token_unico_dispositivo IS NULL,
					'', 
					CONCAT( '"token_unico_dispositivo" : "', new.token_unico_dispositivo, '",' )
				),
				IF( new.id_usuario IS NULL,
					'', 
					CONCAT( '"id_usuario" : "', new.id_usuario, '",' )
				),
				IF( new.fecha_sesion IS NULL,
					'', 
					CONCAT( '"fecha_sesion" : "', new.fecha_sesion, '",' )
				),
				IF( new.fecha_modificacion IS NULL,
					'', 
					CONCAT( '"fecha_modificacion" : "', new.fecha_modificacion, '",' )
				),
				IF( new.bloqueada IS NULL,
					'', 
					CONCAT( '"bloqueada" : "', new.bloqueada, '",' ) 
				),
				IF( new.finalizada IS NULL,
					'', 
					CONCAT( '"finalizada" : "', new.finalizada, '",' ) 
				),
				IF( new.folio_unico IS NULL,
					'', 
					CONCAT( '"folio_unico" : "', new.folio_unico, '",' ) 
				),
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaSesionDispositivoValidacionTransferencia',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$