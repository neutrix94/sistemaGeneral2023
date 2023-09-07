DROP TRIGGER IF EXISTS insertaPerfil|
DELIMITER $$
CREATE TRIGGER insertaPerfil
AFTER INSERT ON sys_users_perfiles
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);

    INSERT INTO sys_permisos ( id_permiso, id_perfil, id_menu, ver, modificar, eliminar, nuevo, 
        imprimir, generar, sincronizar )
    SELECT 
        NULL,
        new.id_perfil,
        mnu.id_menu,
        0,
        0,
        0,
        0,
        0,
        0,
        1 
    FROM sys_menus mnu
    WHERE mnu.en_permisos = 1
    AND mnu.id_menu != mnu.menu_padre
    AND mnu.menu_padre > 0;

    INSERT INTO sys_permisos_bitacora ( id_permiso_bitacora, id_submodulo, id_perfil, acceso, activo )
    SELECT 
        NULL,
        sm.id_submodulo,
        new.id_perfil,
        0,
        1 
    FROM sys_submodulos_sincronizacion sm;

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
                '"action_type" : "insert",',
                '"primary_key" : "id_perfil",',
                '"primary_key_value" : "', new.id_perfil, '",',
                '"id_perfil" : "', new.id_perfil, '",',
                '"nombre" : "', new.nombre, '",',
                '"admin" : "', new.admin, '",',
                '"observaciones" : "', new.observaciones, '",',
                '"logueo_perfil" : "', new.logueo_perfil, '",',
                '"sincronizar" : "0"',
                '}'
            ),
            NOW(),
            'insertaPerfil',
            1
        FROM sys_sucursales 
        WHERE id_sucursal > 0;
    END IF;
END $$