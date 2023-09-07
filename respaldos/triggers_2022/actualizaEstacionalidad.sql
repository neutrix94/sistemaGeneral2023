DROP TRIGGER IF EXISTS actualizaEstacionalidad|
DELIMITER $$
CREATE TRIGGER actualizaEstacionalidad
BEFORE UPDATE ON ec_estacionalidad
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_estacionalidad',new.id_estacionalidad,2,6,
        CONCAT("UPDATE ec_estacionalidad SET ",
                "nombre='",new.nombre,"',", 
                "id_periodo='",new.id_periodo,"',",    
                "observaciones='",new.observaciones,"',", 
                "id_sucursal='",new.id_sucursal,"',",   
                "es_alta='",new.es_alta,"',",
                "sincronizar=0 WHERE id_estacionalidad='",new.id_estacionalidad,"'"
        ),
        0,0,CONCAT('Se modificó la estacionalidad ',new.nombre),now(),0,0,'id_estacionalidad'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$