DROP TRIGGER IF EXISTS insertaExclusion|
DELIMITER $$
CREATE TRIGGER insertaExclusion
AFTER INSERT ON ec_exclusiones_transferencia
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( store_id = -1 AND new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_exclusiones_transferencia",',
				'"action_type" : "insert",',
				'"primary_key" : "id_exclusion_transferencia",',
				'"primary_key_value" : "', new.id_exclusion_transferencia, '",',
				'"id_exclusion_transferencia" : "', new.id_exclusion_transferencia, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"observaciones" : "', new.observaciones, '",',
				'"fecha" : "', new.fecha, '",',
				'"hora" : "', new.hora, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaExclusion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
END $$