DROP TRIGGER IF EXISTS eliminaCorreoDestinatario|
DELIMITER $$
CREATE TRIGGER eliminaCorreoDestinatario
AFTER DELETE ON ec_correo_destinatarios
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_correo_destinatarios',old.id_correo_destinatario,3,6,
        CONCAT("DELETE FROM ec_correo_destinatarios WHERE id_correo_destinatario='",old.id_correo_destinatario,"'"),
        0,0,CONCAT('Se eliminódestinatario de envío de correos ',old.nombre_destinatario),now(),0,0,'id_correo_destinatario'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$