DROP TRIGGER IF EXISTS actualizaSesionDispositivoRecepcionTransferencia|
DELIMITER $$
CREATE TRIGGER actualizaSesionDispositivoRecepcionTransferencia
BEFORE UPDATE ON ec_sesiones_dispositivos_recepcion_transferencias
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE reception_block_unique_folio VARCHAR( 30 );
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque recepcion*/
		SELECT 
			folio_unico
		INTO
			reception_block_unique_folio
		FROM ec_bloques_transferencias_recepcion
		WHERE id_bloque_transferencia_recepcion = new.id_bloque_recepcion;
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_sesiones_dispositivos_recepcion_transferencias",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( new.id_bloque_recepcion IS NULL, 
					'',
					CONCAT( '"id_bloque_recepcion" : "', 
						'( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'',
						reception_block_unique_folio, '\' LIMIT 1 )",' )
				),
				'"token_unico_dispositivo" : "', new.token_unico_dispositivo, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"fecha_sesion" : "', new.fecha_sesion, '",',
				'"fecha_modificacion" : "', new.fecha_modificacion, '",',
				'"bloqueada" : "', new.bloqueada, '",',
				'"finalizada" : "', new.finalizada, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaSesionDispositivoRecepcionTransferencia',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$