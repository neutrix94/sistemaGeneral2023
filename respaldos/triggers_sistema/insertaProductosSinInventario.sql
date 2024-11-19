DROP TRIGGER IF EXISTS insertaProductosSinInventario|
DELIMITER $$
CREATE TRIGGER insertaProductosSinInventario
BEFORE INSERT ON ec_productos_sin_inventario
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
    DECLARE prefix VARCHAR(20);
    DECLARE row_id INT( 11 );
	SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
	/*obtiene el siguiente id*/
	SELECT 
		auto_increment into row_id
	FROM information_schema.tables
	WHERE table_name = 'ec_productos_sin_inventario'
	AND table_schema = database();
    SET new.folio_unico = CONCAT( prefix, '_PSI_', row_id );/*, row_id*/
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
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico,'",',
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