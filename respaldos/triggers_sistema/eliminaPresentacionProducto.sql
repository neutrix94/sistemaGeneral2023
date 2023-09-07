DROP TRIGGER IF EXISTS eliminaPresentacionProducto|
DELIMITER $$
CREATE TRIGGER eliminaPresentacionProducto
AFTER DELETE ON ec_productos_presentaciones
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
                '"table_name" : "ec_productos_presentaciones",',
                '"action_type" : "delete",',
                '"primary_key" : "id_producto_presentacion",',
                '"primary_key_value" : "', old.id_producto_presentacion, '"',
                '}'
            ),
            NOW(),
            'eliminaPresentacionProducto',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$