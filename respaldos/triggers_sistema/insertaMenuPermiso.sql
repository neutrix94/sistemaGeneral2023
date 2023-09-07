DROP TRIGGER IF EXISTS insertaMenuPermiso|
DELIMITER $$
CREATE TRIGGER insertaMenuPermiso
AFTER INSERT ON sys_menus
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
	INSERT INTO sys_permisos ( id_permiso, id_perfil, id_menu, ver, modificar, eliminar, nuevo, imprimir, generar, sincronizar ) 
	SELECT 
		null,
		prf.id_perfil,
		new.id_menu,
		0,
		0,
		0,
		0,
		0,
		0,
		1 
	FROM sys_users_perfiles prf;
	
	UPDATE sys_permisos SET sincronizar=0;
   
	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

  IF(id_suc=-1)
  THEN
    UPDATE sys_permisos SET sincronizar=0 WHERE id_menu=new.id_menu;
  END IF;
END $$