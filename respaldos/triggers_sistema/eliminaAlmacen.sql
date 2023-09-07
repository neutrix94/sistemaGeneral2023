DROP TRIGGER IF EXISTS eliminaAlmacen|
DELIMITER $$
CREATE TRIGGER eliminaAlmacen
AFTER DELETE ON ec_almacen
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
                '"table_name" : "ec_almacen",',
                '"action_type" : "delete",',
                '"primary_key" : "id_almacen",',
                '"primary_key_value" : "', old.id_almacen, '"',
                '}'
            ),
            NOW(),
            'eliminaAlmacen',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
   	/*elimina almacen producto*/
   	DELETE FROM ec_almacen_producto WHERE id_almacen = old.id_almacen;
   	/*elimina almacen proveedor producto*/
   	DELETE FROM ec_inventario_proveedor_producto WHERE id_almacen = old.id_almacen;
END $$