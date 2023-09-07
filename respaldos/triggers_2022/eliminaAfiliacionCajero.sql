DROP TRIGGER IF EXISTS eliminaAfiliacionCajero|
DELIMITER $$
CREATE TRIGGER eliminaAfiliacionCajero
AFTER DELETE ON ec_afiliaciones_cajero
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
    DECLARE suc_cajero INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    
        SELECT id_sucursal INTO suc_cajero FROM sys_users WHERE id_usuario=old.id_cajero;
    	
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones_cajero',old.id_afiliacion_cajero,3,1,
            CONCAT("DELETE FROM ec_afiliaciones_cajero WHERE id_afiliacion_cajero='",old.id_afiliacion_cajero,"'"),
            0,0,CONCAT('Se elimino la afiliacion ',old.no_afiliacion,' para el cajero ',
            (SELECT CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno) FROM sys_users WHERE id_usuario=old.id_cajero)),
            now(),0,0,'id_afiliacion_cajero'
        FROM sys_sucursales WHERE id_sucursal=suc_cajero;
    END IF;
END $$