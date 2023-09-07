DROP TRIGGER IF EXISTS insertaPermisoBitacora|
DELIMITER $$
CREATE TRIGGER insertaPermisoBitacora
AFTER INSERT ON sys_submodulos_sincronizacion
FOR EACH ROW
BEGIN
	INSERT INTO sys_permisos_bitacora ( id_permiso_bitacora, id_submodulo, id_perfil, acceso, activo ) 
	SELECT 
		null,
		new.id_submodulo,
		id_perfil,
		0,
		1
    FROM sys_users_perfiles WHERE 1;
END $$