DROP TRIGGER IF EXISTS eliminaAfiliacionSucursal|
DELIMITER $$
CREATE TRIGGER eliminaAfiliacionSucursal
AFTER DELETE ON ec_afiliacion_sucursal
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN    	
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliacion_sucursal',old.id_afiliacion_sucursal,3,1,
            CONCAT("DELETE FROM ec_afiliacion_sucursal WHERE id_afiliacion_sucursal='",old.id_afiliacion_sucursal,"'"),
            0,0,CONCAT('Se actualizo la afiliacion ',old.id_afiliacion,' para la sucursal ',
            (SELECT nombre FROM sys_sucursales WHERE id_sucursal=old.id_sucursal)),
            now(),0,0,'id_afiliacion_sucursal'
        FROM sys_sucursales WHERE id_sucursal=old.id_sucursal;
    END IF;
END $$