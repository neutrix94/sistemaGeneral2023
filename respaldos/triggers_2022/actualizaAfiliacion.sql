DROP TRIGGER IF EXISTS actualizaAfiliacion|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacion
BEFORE UPDATE ON ec_afiliaciones
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);	

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar=1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones',new.id_afiliacion,2,1,
        CONCAT("UPDATE ec_afiliaciones SET ",
                "id_banco='",new.id_banco,"',",
                "no_afiliacion='",new.no_afiliacion,"',",          
                "observaciones='",new.observaciones,"',",     
                "fecha_alta='",new.fecha_alta,"',",
                "sincronizar=0 WHERE id_afiliacion='",new.id_afiliacion,"'"
        ),
        1,0,CONCAT('Se actualizó la afiliacion ',new.no_afiliacion),now(),0,0,'id_afiliacion'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$