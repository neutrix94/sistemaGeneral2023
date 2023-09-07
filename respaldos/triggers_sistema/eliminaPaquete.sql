DROP TRIGGER IF EXISTS eliminaPaquete|
DELIMITER $$
CREATE TRIGGER eliminaPaquete
AFTER DELETE ON ec_paquetes
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_paquetes",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaPaquete',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, old.id_sucursal_creacion, -1 );
END $$