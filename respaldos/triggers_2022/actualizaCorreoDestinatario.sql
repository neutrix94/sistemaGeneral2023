DROP TRIGGER IF EXISTS actualizaCorreoDestinatario|
DELIMITER $$
CREATE TRIGGER actualizaCorreoDestinatario
BEFORE UPDATE ON ec_correo_destinatarios
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_correo_destinatarios',new.id_correo_destinatario,2,6,
        CONCAT("UPDATE ec_correo_destinatarios SET ",
            "id_correo_destinatario='",new.id_correo_destinatario,"',",
            "id_modulo='",new.id_modulo,"',",            
            "nombre_destinatario='",new.nombre_destinatario,"',", 
            "correo='",new.correo,"',",         
            "activo='",new.activo,"',",
            "sincronizar=0 WHERE id_correo_destinatario='",new.id_correo_destinatario,"'"
        ),
        0,0,CONCAT('Se actualizó destinatario de envío de correos ',new.nombre_destinatario),now(),0,0,'id_correo_destinatario'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$