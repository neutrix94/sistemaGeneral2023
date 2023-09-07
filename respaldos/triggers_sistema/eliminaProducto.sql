DROP TRIGGER IF EXISTS eliminaProducto|
DELIMITER $$
CREATE TRIGGER eliminaProducto
AFTER DELETE ON ec_productos
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);

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
                '"table_name" : "ec_productos",',
                '"action_type" : "update",',
                '"primary_key" : "id_productos",',
                '"primary_key_value" : "', old.id_productos, '"',
                '}'
            ),
            NOW(),
            'eliminaProducto',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$