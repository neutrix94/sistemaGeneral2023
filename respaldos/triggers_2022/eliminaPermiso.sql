DROP TRIGGER IF EXISTS eliminaPermiso|
DELIMITER $$
CREATE TRIGGER eliminaPermiso
AFTER DELETE ON sys_permisos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_permisos',old.id_permiso,3,6,
        CONCAT("DELETE FROM sys_permisos WHERE id_perfil='",old.id_perfil,"' AND id_menu='",old.id_menu,"'"),
        0,0,CONCAT('Se eliminó permiso '),now(),0,0,'id_permiso'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$