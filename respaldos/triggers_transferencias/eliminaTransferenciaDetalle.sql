DROP TRIGGER IF EXISTS eliminaTransferenciaDetalle|
DELIMITER $$
CREATE TRIGGER eliminaTransferenciaDetalle
AFTER DELETE ON ec_transferencia_productos
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE prefix VARCHAR( 30 );
	DECLARE transfer_origin_store_id INTEGER;
	DECLARE transfer_destinity_store_id INTEGER;

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*Consulta sucursal de transferencia y folio_unico*/
	SELECT 
		id_sucursal_origen,
		id_sucursal_destino
	INTO
		transfer_origin_store_id,
		transfer_destinity_store_id
	FROM ec_transferencias 
	WHERE id_transferencia = old.id_transferencia;

	INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_transferencia_productos",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTransferenciaDetalle',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, CONCAT( transfer_origin_store_id, ',', transfer_destinity_store_id ), -1 );
END $$