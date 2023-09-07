DROP TRIGGER IF EXISTS actualizaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER actualizaMovimientoDetalleProveedorProducto
BEFORE UPDATE ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE final_inventory FLOAT;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( new.insertado_por_sincronizacion = 0 )
	THEN
		SET final_inventory = ( ( new.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = new.id_tipo_movimiento ) ) -
		( old.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = old.id_tipo_movimiento ) ) );
		UPDATE ec_inventario_proveedor_producto SET inventario = ( inventario + final_inventory)  
		WHERE id_proveedor_producto = new.id_proveedor_producto AND id_almacen = new.id_almacen;	
	END IF;

	IF( new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros_movimientos_proveedor_producto ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_movimiento_detalle_proveedor_producto",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_proveedor_producto" : "', IF( new.id_proveedor_producto IS NULL, '', new.id_proveedor_producto ), '",',
				'"cantidad" : "', new.cantidad, '",',
				'"fecha_registro" : "', new.fecha_registro, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"status_agrupacion" : "', new.status_agrupacion, '",',
				'"id_tipo_movimiento" : "', new.id_tipo_movimiento, '",',
				'"id_almacen" : "', new.id_almacen, '",',
				'"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
				'"sincronizar" : "0",',
				'"insertado_por_sincronizacion" : "0"',
				'}'
			),
			NOW(),
			'actualizaMovimientoDetalleProveedorProducto',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$