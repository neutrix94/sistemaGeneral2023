DROP TRIGGER IF EXISTS eliminaConfigCorreo|
DELIMITER $$
CREATE TRIGGER eliminaConfigCorreo
AFTER DELETE ON ec_conf_correo
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
                '"table_name" : "ec_conf_correo",',
                '"action_type" : "delete",',
                '"primary_key" : "id_configuracion",',
                '"primary_key_value" : "', old.id_configuracion, '"',
                '}'
            ),
            NOW(),
            'eliminaConfigCorreo',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$