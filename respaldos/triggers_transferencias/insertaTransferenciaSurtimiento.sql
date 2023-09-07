DROP TRIGGER IF EXISTS insertaTransferenciaSurtimiento|
DELIMITER $$
CREATE TRIGGER insertaTransferenciaSurtimiento
BEFORE INSERT ON ec_transferencias_surtimiento
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE prefix VARCHAR( 30 );
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE row_id BIGINT;
	DECLARE transfer_origin_store_id INTEGER;

	SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
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
		
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_transferencias_surtimiento'
	    AND table_schema = database();

        SET new.folio_unico = CONCAT( prefix, '_TSURT_', row_id );
		
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_surtimiento",',
				'"action_type" : "insert",',
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
			'insertaTransferenciaSurtimiento',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$