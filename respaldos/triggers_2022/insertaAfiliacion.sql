DROP TRIGGER IF EXISTS insertaAfiliacion|
DELIMITER $$
CREATE TRIGGER insertaAfiliacion
AFTER INSERT ON ec_afiliaciones
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);	

	INSERT INTO ec_afiliacion_sucursal
	SELECT 
	null,
	new.id_afiliacion,
	id_sucursal,
	0,
	1
	FROM sys_sucursales
	WHERE id_sucursal>0;

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar=1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones',new.id_afiliacion,1,1,
        CONCAT("INSERT INTO ec_afiliaciones SET ",
                "id_afiliacion='",new.id_afiliacion,"',",
                "id_banco='",new.id_banco,"',",
                "no_afiliacion='",new.no_afiliacion,"',",          
                "observaciones='",new.observaciones,"',",     
                "fecha_alta='",new.fecha_alta,"',",
                "sincronizar=0",
                "___UPDATE ec_afiliaciones SET sincronizar=0 WHERE id_afiliacion='",new.id_afiliacion,"'"
        ),
        1,0,CONCAT('Se agregó la afiliacion ',new.no_afiliacion),now(),0,0,'id_afiliacion'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$