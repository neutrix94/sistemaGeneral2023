DROP TRIGGER IF EXISTS eliminaDetallePrecio|
DELIMITER $$
CREATE TRIGGER eliminaDetallePrecio
AFTER DELETE ON ec_precios_detalle
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( store_id = -1 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_precios_detalle",',
				'"action_type" : "delete",',
				'"primary_key" : "id_precio_detalle",',
				'"primary_key_value" : "', old.id_precio_detalle, '"',
				'}'
			),
			NOW(),
			'eliminaDetallePrecio',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
END $$