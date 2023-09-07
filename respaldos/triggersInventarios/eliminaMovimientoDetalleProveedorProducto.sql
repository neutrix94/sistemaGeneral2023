DROP TRIGGER IF EXISTS eliminaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER eliminaMovimientoDetalleProveedorProducto
AFTER DELETE ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
    DECLARE store_id INTEGER;
	DECLARE final_inventory FLOAT;
    
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	
	INSERT INTO sys_sincronizacion_registros_movimientos_proveedor_producto ( id_sincronizacion_registro, sucursal_de_cambio,
	id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT 
	    NULL,
	    store_id,
	    id_sucursal,
	    CONCAT('{',
	        '"table_name" : "ec_movimiento_detalle_proveedor_producto",',
	        '"action_type" : "delete",',
	        '"primary_key" : "folio_unico",',
	        '"primary_key_value" : "', old.folio_unico, '"',
	        '}'
	    ),
	    NOW(),
	    'eliminaMovimientoDetalleProveedorProducto',
	    1
	FROM sys_sucursales 
	WHERE id_sucursal = IF( store_id = -1, old.id_sucursal, -1 );

	SET final_inventory = ( ( old.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = old.id_tipo_movimiento ) ) );
	UPDATE ec_inventario_proveedor_producto SET inventario = ( inventario - final_inventory)
	WHERE id_proveedor_producto = old.id_proveedor_producto AND id_almacen = old.id_almacen;
END $$