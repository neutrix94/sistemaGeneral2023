DROP TRIGGER IF EXISTS actualizaAfiliacionSucursal|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacionSucursal
BEFORE UPDATE ON ec_afiliacion_sucursal
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar=1)
    THEN    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliacion_sucursal',new.id_afiliacion_sucursal,2,1,
        CONCAT("UPDATE ec_afiliacion_sucursal SET ",
                "id_afiliacion='",new.id_afiliacion,"',",  
                "id_sucursal='",new.id_sucursal,"',",         
                "estado_suc='",new.estado_suc,"',",
                "sincronizar=0 WHERE id_afiliacion='",new.id_afiliacion,"' AND id_sucursal=",new.id_sucursal
        ),
        1,0,CONCAT('Se actualizo la afiliacion ',new.id_afiliacion,' para la sucursal ',
            (SELECT nombre FROM sys_sucursales WHERE id_sucursal=new.id_sucursal)),now(),0,0,'id_afiliacion_sucursal'
        FROM sys_sucursales WHERE id_sucursal=new.id_sucursal;
    END IF;
    SET new.sincronizar=1;
END $$