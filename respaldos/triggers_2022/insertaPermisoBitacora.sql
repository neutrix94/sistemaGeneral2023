DROP TRIGGER IF EXISTS insertaPermisoBitacora|
DELIMITER $$
CREATE TRIGGER insertaPermisoBitacora
AFTER INSERT ON sys_submodulos_sincronizacion
FOR EACH ROW
BEGIN
	INSERT INTO sys_permisos_bitacora SELECT null,new.id_submodulo,id_perfil,0,1
    FROM sys_users_perfiles where 1;
END $$