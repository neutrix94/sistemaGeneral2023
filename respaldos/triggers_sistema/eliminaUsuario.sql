DROP TRIGGER IF EXISTS eliminaUsuario|
DELIMITER $$
CREATE TRIGGER eliminaUsuario
AFTER DELETE ON sys_users
FOR EACH ROW
BEGIN
    DECLARE store_id INTEGER;
    SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
    id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
    SELECT 
        NULL,
        store_id,
        id_sucursal,
        CONCAT('{',
            '"table_name" : "sys_users",',
            '"action_type" : "delete",',
            '"primary_key" : "id_usuario",',
            '"primary_key_value" : "', old.id_usuario, '"',
            '}'
        ),
        NOW(),
        'eliminaUsuario',
        1
    FROM sys_sucursales 
    WHERE IF( store_id = -1, id_sucursal > 0, -1 );
END $$
