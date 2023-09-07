DROP TRIGGER IF EXISTS eliminaTransferenciaCodigoUnico|
DELIMITER $$
CREATE TRIGGER eliminaTransferenciaCodigoUnico
AFTER DELETE ON ec_transferencia_codigos_unicos
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_origin_store_id INTEGER;
/*folios unicos*/
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE transfer_validation_block_unique_folio VARCHAR( 30 );
	DECLARE transfer_reception_block_unique_folio VARCHAR( 30 );
	DECLARE transfer_validation_unique_folio VARCHAR( 30 );
	DECLARE transfer_reception_unique_folio VARCHAR( 30 );
	DECLARE transfer_resolution_unique_folio VARCHAR( 30 );
	DECLARE transfer_resolution_detail_unique_folio VARCHAR( 30 );
/*inserta registro de sincronizacion*/
	INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_transferencia_codigos_unicos",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTransferenciaCodigoUnico',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, 1, -1 );
END $$