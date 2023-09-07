DROP TRIGGER IF EXISTS insertaBloqueTransferenciaResolucion|
DELIMITER $$
CREATE TRIGGER insertaBloqueTransferenciaResolucion
BEFORE INSERT ON ec_bloques_transferencias_resolucion
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE reception_block_unique_folio VARCHAR( 30 );
	
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			reception_block_unique_folio
		FROM ec_bloques_transferencias_recepcion
		WHERE id_bloque_transferencia_recepcion = new.id_bloque_transferencia_recepcion;
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_bloques_transferencias_resolucion'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_BTRES_', row_id );
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_resolucion",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_bloque_transferencia_recepcion" : "', 
				'( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'',
					reception_block_unique_folio, '\' LIMIT 1 )",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"piezas_faltantes" : "', new.piezas_faltantes, '",',
				'"piezas_sobrantes" : "', new.piezas_sobrantes, '",',
				'"piezas_no_corresponden" : "', new.piezas_no_corresponden, '",',
				'"piezas_se_quedan" : "', new.piezas_se_quedan, '",',
				'"piezas_se_regresan" : "', new.piezas_se_regresan, '",',
				'"piezas_faltaron" : "', new.piezas_faltaron, '",',
				'"conteo" : "', new.conteo, '",',
				'"conteo_excedente" : "', new.conteo_excedente, '",',
				'"diferencia" : "', new.diferencia, '",',
				'"id_producto_resolucion" : "', new.id_producto_resolucion, '",',
				'"resuelto" : "', new.resuelto, '",',
				'"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaBloqueTransferenciaResolucion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$