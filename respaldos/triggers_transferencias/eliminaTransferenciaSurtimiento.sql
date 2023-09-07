DROP TRIGGER IF EXISTS eliminaTransferenciaSurtimiento|
DELIMITER $$
CREATE TRIGGER eliminaTransferenciaSurtimiento
AFTER DELETE ON ec_transferencias_surtimiento
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_origin_store_id INTEGER;

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    /*Consulta sucursal de transferencia y folio_unico*/
	SELECT 
		id_sucursal_origen
	INTO
		transfer_origin_store_id
	FROM ec_transferencias 
	WHERE id_transferencia = old.id_transferencia;
	
	INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_transferencias_surtimiento",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '",'
			'}'
		),
		NOW(),
		'eliminaTransferenciaSurtimiento',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
END $$