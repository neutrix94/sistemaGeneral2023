DROP TRIGGER IF EXISTS actualizaBloqueTransferenciaRecepcion|
DELIMITER $$
CREATE TRIGGER actualizaBloqueTransferenciaRecepcion
BEFORE UPDATE ON ec_bloques_transferencias_recepcion
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE transfer_origin_store_id INTEGER;
	DECLARE reception_session_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
	/**/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

		IF( new.id_sesion_principal != 0 )
		THEN
			SELECT 
				folio_unico
			INTO
				reception_session_unique_folio
			FROM ec_sesiones_dispositivos_recepcion_transferencias
			WHERE id_sesion_dispositivo_recepcion = new.id_sesion_principal;
		END IF;
    /*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_recepcion",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"recibido" : "', new.recibido, '",',
				'"bloqueado" : "', new.bloqueado, '",',
				IF( new.id_sesion_principal != 0,
					CONCAT( '"id_sesion_principal" : "', 
						'( SELECT id_sesion_dispositivo_recepcion FROM ec_sesiones_dispositivos_recepcion_transferencias WHERE folio_unico = \'',
							reception_session_unique_folio, '\' LIMIT 1 )",' ),
					''
				),
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaBloqueTransferenciaRecepcion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$