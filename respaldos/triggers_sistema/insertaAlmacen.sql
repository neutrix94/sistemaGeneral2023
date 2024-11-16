DROP TRIGGER IF EXISTS insertaAlmacen|
DELIMITER $$
CREATE TRIGGER insertaAlmacen
AFTER INSERT ON ec_almacen
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
				'"table_name" : "ec_almacen",',
				'"action_type" : "insert",',
				'"primary_key" : "id_almacen",',
				'"primary_key_value" : "', new.id_almacen, '",',
				'"id_almacen" : "', new.id_almacen, '",',
				'"nombre" : "', new.nombre, '",',
				'"es_almacen" : "', new.es_almacen, '",',
				'"prioridad" : "', new.prioridad, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"es_externo" : "', new.es_externo, '",',
				'"ultima_sincronizacion" : "', new.ultima_sincronizacion, '",',
				'"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaAlmacen',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
	
	INSERT INTO ec_almacen_producto ( id_almacen_producto, id_almacen, id_producto, inventario )
	SELECT 
		NULL,
		new.id_almacen,
		p.id_productos,
		0
	FROM ec_productos p
	WHERE p.id_productos > 0;

	INSERT INTO ec_inventario_proveedor_producto 
	( id_producto, id_proveedor_producto, id_sucursal, id_almacen, inventario, fecha_registro, ultima_actualizacion )
	SELECT 
		pp.id_producto,
		pp.id_proveedor_producto,
		new.id_sucursal,
		new.id_almacen,
		'insertaAlmacen',
		NOW(),
		'0000-00-00 00:00:00'
	FROM ec_proveedor_producto pp
	WHERE pp.id_proveedor_producto > 0;
END $$