DROP TRIGGER IF EXISTS insertaPerfil|
DELIMITER $$
CREATE TRIGGER insertaPerfil
AFTER INSERT ON sys_users_perfiles
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    INSERT INTO sys_permisos SELECT null,new.id_perfil,mnu.id_menu,0,0,0,0,0,0,1 FROM sys_menus mnu
    WHERE mnu.en_permisos = 1
    AND mnu.id_menu != mnu.menu_padre
    AND mnu.menu_padre > 0;

    INSERT INTO sys_permisos_bitacora SELECT NULL,sm.id_submodulo,new.id_perfil,0,1 FROM sys_submodulos_sincronizacion sm;


    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_users_perfiles',new.id_perfil,1,6,
        CONCAT("INSERT INTO sys_users_perfiles SET ",
                "id_perfil='",new.id_perfil,"',",
                "nombre='",new.nombre,"',",
                "admin='",new.admin,"',",
                "observaciones='",new.observaciones,"',",
                "sincronizar=0",
                "___UPDATE sys_users_perfiles SET sincronizar=0 WHERE id_perfil='",new.id_perfil,"'"
        ),
        1,0,CONCAT('Se agregó el perfil ',new.nombre),now(),0,0,'id_perfil'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$