DROP TRIGGER IF EXISTS eliminaTemporalExhibicion|
DELIMITER $$
CREATE TRIGGER eliminaTemporalExhibicion
AFTER DELETE ON ec_temporal_exhibicion
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
/*obtiene sucursal*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*inserta registro de sincronizacion*/
	INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
		NULL,
		store_id,
		id_sucursal,
		CONCAT('{',
			'"table_name" : "ec_temporal_exhibicion",',
			'"action_type" : "delete",',
			'"primary_key" : "folio_unico",',
			'"primary_key_value" : "', old.folio_unico, '"',
			'}'
		),
		NOW(),
		'eliminaTemporalExhibicion',
		1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, old.id_sucursal, -1 );
END $$