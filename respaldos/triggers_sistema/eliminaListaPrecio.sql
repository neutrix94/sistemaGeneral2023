DROP TRIGGER IF EXISTS eliminaListaPrecio|
DELIMITER $$
CREATE TRIGGER eliminaListaPrecio
AFTER DELETE ON ec_precios
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
                '"table_name" : "ec_precios",',
                '"action_type" : "delete",',
                '"primary_key" : "id_precio",',
                '"primary_key_value" : "', old.id_precio, '"',
                '}'
            ),
            NOW(),
            'eliminaListaPrecio',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$