DROP TRIGGER IF EXISTS actualizaConfigCorreo|
DELIMITER $$
CREATE TRIGGER actualizaConfigCorreo
BEFORE UPDATE ON ec_conf_correo
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_conf_correo',new.id_configuracion,2,6,
        CONCAT("UPDATE ec_conf_correo SET ",
                "smtp_server='",new.smtp_server,"',",
                "puerto='",new.puerto,"',",
                "smtp_user='",new.smtp_user,"',",
                "smtp_pass='",new.smtp_pass,"',",
                "correo_envios='",new.correo_envios,"',",
                "nombre_correo='",new.nombre_correo,"',",
                "sincronizar=0 WHERE id_configuracion=",new.id_configuracion
        ),
        0,0,CONCAT('Se actualizó la configuracion del correo de envios'),now(),0,0,'id_configuracion'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$