DROP TRIGGER IF EXISTS eliminaPerfil|
DELIMITER $$
CREATE TRIGGER eliminaPerfil
AFTER DELETE ON sys_users_perfiles
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_users_perfiles',old.id_perfil,3,6,
        CONCAT("DELETE FROM sys_users_perfiles WHERE id_perfil='",old.id_perfil,"'"),
        0,0,CONCAT('Se eliminó el perfil ',old.nombre),now(),0,0,'id_perfil'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$