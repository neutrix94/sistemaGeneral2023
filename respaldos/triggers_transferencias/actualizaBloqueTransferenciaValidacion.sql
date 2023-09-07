DROP TRIGGER IF EXISTS actualizaBloqueTransferenciaValidacion|
DELIMITER $$
CREATE TRIGGER actualizaBloqueTransferenciaValidacion
BEFORE UPDATE ON ec_bloques_transferencias_validacion
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE validation_session_unique_folio VARCHAR( 30 );
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	/*consulta id equivalente de sesion de validacion*/
		IF( new.id_sesion_principal != 0 )
		THEN
			SELECT
				id_sesion_dispositivo_validacion
			INTO
				validation_session_unique_folio
			FROM ec_sesiones_dispositivos_validacion_transferencias
			WHERE id_sesion_dispositivo_validacion = new.id_sesion_principal;
		END IF;
	/*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_validacion",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"validado" : "', new.validado, '",',
				'"bloqueado" : "', new.bloqueado, '",',
				IF( new.id_sesion_principal != 0, 
					CONCAT( '"id_sesion_principal" : "', 
					'( SELECT id_sesion_dispositivo_validacion FROM ec_sesiones_dispositivos_validacion_transferencias WHERE folio_unico = \'', 
						validation_session_unique_folio ,'\' LIMIT 1 )",'),
					''
				),
				'"folio_unico" : "', new.folio_unico , '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaBloqueTransferenciaValidacion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$