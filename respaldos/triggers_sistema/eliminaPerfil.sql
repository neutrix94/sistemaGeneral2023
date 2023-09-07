DROP TRIGGER IF EXISTS eliminaPerfil|
DELIMITER $$
CREATE TRIGGER eliminaPerfil
AFTER DELETE ON sys_users_perfiles
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
                '"table_name" : "sys_users_perfiles",',
                '"action_type" : "delete",',
                '"primary_key" : "id_perfil",',
                '"primary_key_value" : "', old.id_perfil, '"',
                '}'
            ),
            NOW(),
            'eliminaPerfil',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$