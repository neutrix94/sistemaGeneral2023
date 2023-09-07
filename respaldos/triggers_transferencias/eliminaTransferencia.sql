DROP TRIGGER IF EXISTS eliminaTransferencia|
DELIMITER $$
CREATE TRIGGER eliminaTransferencia
/*verificado 13-07-2023*/
AFTER DELETE ON ec_transferencias
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*Elimina movimientos de almacen*/
	DELETE FROM ec_movimiento_almacen WHERE id_transferencia = old.id_transferencia;
	INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_transferencias",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTransferencia',
		1
	FROM sys_sucursales 
	WHERE IF( store_id = -1, id_sucursal IN( old.id_sucursal_origen, old.id_sucursal_destino ), id_sucursal = -1 );
END $$