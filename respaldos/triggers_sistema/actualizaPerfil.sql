DROP TRIGGER IF EXISTS actualizaPerfil|
DELIMITER $$
CREATE TRIGGER actualizaPerfil
BEFORE UPDATE ON sys_users_perfiles
FOR EACH ROW
BEGIN
	DECLARE store_id INT(11);

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
                '"table_name" : "sys_users_perfiles",',
                '"action_type" : "update",',
                '"primary_key" : "id_perfil",',
                '"primary_key_value" : "', new.id_perfil, '",',
                '"nombre" : "', new.nombre, '",',
                '"admin" : "', new.admin, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"logueo_perfil" : "', new.logueo_perfil, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'actualizaPerfil',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
    SET new.sincronizar=1;
END $$