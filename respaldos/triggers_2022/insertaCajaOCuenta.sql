DROP TRIGGER IF EXISTS insertaCajaOCuenta|
DELIMITER $$
CREATE TRIGGER insertaCajaOCuenta
AFTER INSERT ON ec_caja_o_cuenta
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    INSERT INTO ec_caja_o_cuenta_sucursal 
    SELECT
        null,
        new.id_caja_cuenta,
        id_sucursal,
        0,
        '0000-00-00 00:00:00',
        1
    FROM sys_sucursales WHERE id_sucursal>0;


    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar=1)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_caja_o_cuenta',new.id_caja_cuenta,1,1,
        CONCAT("INSERT INTO ec_caja_o_cuenta SET ",
                "id_caja_cuenta='",new.id_caja_cuenta,"',",
                "nombre='",new.nombre,"',",
                "id_tipo_caja='",new.id_tipo_caja,"',",          
                "no_cuenta='",new.no_cuenta,"',",     
                "clave_interna='",new.clave_interna,"',",
                "banco='",new.banco,"',",      
                "activo='",new.activo,"',", 
                "observaciones='",new.observaciones,"',",
                "fecha_alta='",new.fecha_alta,"',",  
                "sincronizar=0",
                "___UPDATE ec_caja_o_cuenta SET sincronizar=0 WHERE id_caja_cuenta='",new.id_caja_cuenta,"'"
        ),
        1,0,CONCAT('Se agregó la caja o cuenta ',new.nombre),now(),0,0,'id_caja_cuenta'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$