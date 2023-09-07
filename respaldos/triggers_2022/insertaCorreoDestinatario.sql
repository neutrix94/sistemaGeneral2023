DROP TRIGGER IF EXISTS insertaCorreoDestinatario|
DELIMITER $$
CREATE TRIGGER insertaCorreoDestinatario
AFTER INSERT ON ec_correo_destinatarios
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_correo_destinatarios',new.id_correo_destinatario,1,6,
        CONCAT("INSERT INTO ec_correo_destinatarios SET ",
            "id_correo_destinatario='",new.id_correo_destinatario,"',",
            "id_modulo='",new.id_modulo,"',",            
            "nombre_destinatario='",new.nombre_destinatario,"',", 
            "correo='",new.correo,"',",         
            "activo='",new.activo,"',",
            "sincronizar=0",
            "___UPDATE ec_correo_destinatarios SET sincronizar=0 WHERE id_correo_destinatario='",new.id_correo_destinatario,"'"
        ),
        1,0,CONCAT('Se agregó destinatario de envío de correos ',new.nombre_destinatario),now(),0,0,'id_correo_destinatario'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$