DROP TRIGGER IF EXISTS insertaBloqueTransferenciaResolucionDetalle|
DELIMITER $$
CREATE TRIGGER insertaBloqueTransferenciaResolucionDetalle
BEFORE INSERT ON ec_bloques_transferencias_resolucion_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE transfer_detail_unique_folio VARCHAR( 30 );
	DECLARE transfer_resolution_detail_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			tp.folio_unico
		INTO
			transfer_detail_unique_folio
		FROM ec_transferencia_productos tp
		WHERE tp.id_transferencia_producto = new.id_transferencia_producto;
	/*Consulta folio unico de bloque transferencia resolucion*/
		SELECT 
			folio_unico
		INTO
			transfer_resolution_detail_unique_folio
		FROM ec_bloques_transferencias_resolucion
		WHERE id_bloque_transferencia_resolucion = new.id_bloque_transferencia_resolucion;
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_bloques_transferencias_resolucion_detalle'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_BTRESD_', row_id );
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_resolucion_detalle",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",', 
				'"id_bloque_transferencia_resolucion" : "',
				'( SELECT id_bloque_transferencia_resolucion FROM ec_bloques_transferencias_resolucion WHERE codigo_unico = \'', 
					transfer_resolution_detail_unique_folio,'\' LIMIT 1 )",',
				'"id_transferencia_producto" : "', new.id_transferencia_producto, '",',
				'( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE codigo_unico = \'', 
					transfer_detail_unique_folio,'\' LIMIT 1 )",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"piezas_faltantes" : "', new.piezas_faltantes, '",',
				'"piezas_sobrantes" : "', new.piezas_sobrantes, '",',
				'"piezas_no_corresponden" : "', new.piezas_no_corresponden, '",',
				'"piezas_se_quedan" : "', new.piezas_se_quedan, '",',
				'"piezas_se_regresan" : "', new.piezas_se_regresan, '",',
				'"piezas_faltaron" : "', new.piezas_faltaron, '",',
				'"resuelto" : "', new.resuelto, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaBloqueTransferenciaResolucionDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$