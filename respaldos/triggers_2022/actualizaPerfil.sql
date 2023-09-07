DROP TRIGGER IF EXISTS actualizaPerfil|
DELIMITER $$
CREATE TRIGGER actualizaPerfil
BEFORE UPDATE ON sys_users_perfiles
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_users_perfiles',new.id_perfil,2,6,
        CONCAT("UPDATE sys_users_perfiles SET ",
                "nombre='",new.nombre,"',",
                "admin='",new.admin,"',",
                "observaciones='",new.observaciones,"',",
                "sincronizar=0 WHERE id_perfil=",new.id_perfil
        ),
        0,0,CONCAT('Se actualizó el perfil ',new.nombre),now(),0,0,'id_perfil'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$