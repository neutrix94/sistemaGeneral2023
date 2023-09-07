DROP TRIGGER IF EXISTS insertaBloqueTransferenciaResolucionEscaneo|
DELIMITER $$
CREATE TRIGGER insertaBloqueTransferenciaResolucionEscaneo
BEFORE INSERT ON ec_bloques_transferencias_resolucion_escaneos
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE resolution_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			resolution_unique_folio
		FROM ec_bloques_transferencias_resolucion
		WHERE id_bloque_transferencia_resolucion = new.id_bloque_transferencia_resolucion;
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_bloques_transferencias_resolucion_escaneos'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_BTRESC_', row_id );
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_resolucion_escaneos",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_bloque_transferencia_resolucion" : "',
				'( SELECT id_bloque_transferencia_resolucion FROM ec_bloques_transferencias_resolucion WHERE folio_unico = \'',
					resolution_unique_folio, '\' LIMIT 1 )",',
				'"codigo_escaneado" : "', new.codigo_escaneado, '",',
				'"codigo_unico" : "', new.codigo_unico, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"es_caja" : "', new.es_caja, '",',
				'"es_paquete" : "', new.es_paquete, '",',
				'"es_pieza" : "', new.es_pieza, '",',
				'"cantidad_piezas" : "', new.cantidad_piezas, '",',
				'"resuelto" : "', new.resuelto, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaBloqueTransferenciaResolucionEscaneo',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$