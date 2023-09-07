DROP TRIGGER IF EXISTS eliminaUsuario|
DELIMITER $$
CREATE TRIGGER eliminaUsuario
AFTER DELETE ON sys_users
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
	THEN
   		INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_users',old.id_usuario,3,6,
    	CONCAT("DELETE FROM sys_users WHERE id_equivalente='",old.id_usuario,"' AND id_sucursal='",old.id_sucursal,"'"),
    	0,0,CONCAT('Se eliminó el usuario ',old.nombre,' ',old.apellido_paterno,' ',old.apellido_materno),now(),0,0,'id_usuario'
    	FROM sys_sucursales WHERE IF(id_suc=-1,IF(old.id_sucursal=-1,id_sucursal>0,id_sucursal=old.id_sucursal),id_sucursal=-1);
    END IF;
END $$