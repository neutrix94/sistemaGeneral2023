DROP TRIGGER IF EXISTS insertaProductosSinInventario|
DELIMITER $$
CREATE TRIGGER insertaProductosSinInventario
AFTER INSERT ON ec_productos_sin_inventario
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_productos_sin_inventario",',
				'"action_type" : "insert",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"alta" : "', new.alta, '",',
				'"observaciones" : "', new.observaciones, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaProductosSinInventario',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
	END IF;
END $$