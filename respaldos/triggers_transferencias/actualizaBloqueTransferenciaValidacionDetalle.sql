DROP TRIGGER IF EXISTS actualizaBloqueTransferenciaValidacionDetalle|
DELIMITER $$
CREATE TRIGGER actualizaBloqueTransferenciaValidacionDetalle
BEFORE UPDATE ON ec_bloques_transferencias_validacion_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_origin_store_id INTEGER;
	DECLARE transfer_validation_block_unique_folio VARCHAR( 30 );
	DECLARE transfer_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	/*Consulta sucursal origen y folio_unico de transferencia*/
		SELECT 
			t.folio_unico,
			t.id_sucursal_origen
		INTO
			transfer_unique_folio,
			transfer_origin_store_id
		FROM ec_transferencias t
		WHERE t.id_transferencia = new.id_transferencia;
	/*Consulta folio unico de bloque de validacion*/
		SELECT 
			folio_unico
		INTO
			transfer_validation_block_unique_folio
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
				'"table_name" : "ec_bloques_transferencias_validacion_detalle",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_bloque_transferencia_validacion" : "', 
				'( SELECT id_bloque_transferencia_validacion FROM ec_bloques_transferencias_validacion WHERE folio_unico = \'',
					transfer_validation_block_unique_folio, '\' LIMIT 1 )",',
				'"id_transferencia" : "',  
				'( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'',
					transfer_unique_folio, '\' LIMIT 1 )",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"invalidado" : "', new.invalidado, '",',
				'"folio_unico" : "', new.folio_unico , '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaBloqueTransferenciaValidacionDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$