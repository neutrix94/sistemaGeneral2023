DROP TRIGGER IF EXISTS eliminaConfigCorreo|
DELIMITER $$
CREATE TRIGGER eliminaConfigCorreo
AFTER DELETE ON ec_conf_correo
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_conf_correo',old.id_configuracion,3,6,'Se eliminó configuración del correo',now(),0,0
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    IF(id_suc>0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,-1,'ec_conceptos_gastos',old.id_configuracion,3,6,'Se eliminó configuración del correo',now(),0,0);
    END IF;
END $$