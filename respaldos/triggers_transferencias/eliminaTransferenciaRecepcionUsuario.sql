DROP TRIGGER IF EXISTS eliminaTransferenciaRecepcionUsuario|
DELIMITER $$
CREATE TRIGGER eliminaTransferenciaRecepcionUsuario
AFTER DELETE ON ec_transferencias_recepcion_usuarios
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
/*consulta sucursal del sistema*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
/*inserta registros de sincronizacion*/
	INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_transferencias_recepcion_usuarios",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTransferenciaRecepcionUsuario',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, 1, -1 );
END $$