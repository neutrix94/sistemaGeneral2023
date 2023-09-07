DROP TRIGGER IF EXISTS eliminaAfiliacion|
DELIMITER $$
CREATE TRIGGER eliminaAfiliacion
AFTER DELETE ON ec_afiliaciones
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones',old.id_afiliacion,3,1,
        CONCAT("DELETE FROM ec_afiliaciones WHERE id_afiliacion='",old.id_afiliacion,"'"),
        0,0,CONCAT('Se eliminó la afiliacion ',old.no_afiliacion),now(),0,0,'id_afiliacion'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$