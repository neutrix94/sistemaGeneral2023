DROP TRIGGER IF EXISTS actualizaTransferenciaSurtimiento|
DELIMITER $$
CREATE TRIGGER actualizaTransferenciaSurtimiento
BEFORE UPDATE ON ec_transferencias_surtimiento
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE transfer_origin_store_id INTEGER;

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( new.sincronizar = 1 )
	THEN
    /*Consulta sucursal de transferencia y folio_unico*/
		SELECT 
			folio_unico,
			id_sucursal_origen
		INTO
			transfer_unique_folio,
			transfer_origin_store_id
		FROM ec_transferencias 
		WHERE id_transferencia = new.id_transferencia;
		
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_surtimiento",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia" : "( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'', transfer_unique_folio, '\' LIMIT 1 )",',
				'"id_encargado_bodega" : "', new.id_encargado_bodega, '",',
				'"id_usuario_asignado" : "', new.id_usuario_asignado, '",',
				'"total_partidas" : "', new.total_partidas, '",',
				'"fecha_asignacion" : "', new.fecha_asignacion, '",',
				'"id_status_asignacion" : "', new.id_status_asignacion, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferenciaSurtimiento',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$