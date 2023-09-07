DROP TRIGGER IF EXISTS actualizaAfiliacionCajero|
DELIMITER $$
CREATE TRIGGER actualizaAfiliacionCajero
BEFORE UPDATE ON ec_afiliaciones_cajero
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
    DECLARE equivalente_usuario INT(11);
    DECLARE suc_cajero INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar=1)
    THEN
    
        SELECT id_equivalente,id_sucursal INTO equivalente_usuario,suc_cajero FROM sys_users WHERE id_usuario=new.id_cajero;
        
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones_cajero',new.id_afiliacion_cajero,2,1,
        CONCAT("UPDATE ec_afiliaciones_cajero SET ",
                "id_afiliacion_cajero='",new.id_afiliacion_cajero,"',",
                "id_cajero='",equivalente_usuario,"',",
                "id_afiliacion='",new.id_afiliacion,"',",  
                "no_afiliacion='",new.no_afiliacion,"',",         
                "activo='",new.activo,"',",
                "observaciones='",new.observaciones,"',",
                "alta='",new.alta,"',",
                "sincronizar=0 WHERE id_afiliacion_cajero='",new.id_afiliacion_cajero,"'"
        ),
        1,0,CONCAT('Se modifico la afiliacion ',new.no_afiliacion,' para el cajero ',
            (SELECT CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno) FROM sys_users WHERE id_usuario=new.id_cajero)),now(),0,0,'id_afiliacion_cajero'
        FROM sys_sucursales WHERE id_sucursal=suc_cajero;
    END IF;
    SET new.sincronizar=1;
END $$