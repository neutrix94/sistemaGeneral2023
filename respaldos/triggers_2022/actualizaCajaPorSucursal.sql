DROP TRIGGER IF EXISTS actualizaCajaPorSucursal|
DELIMITER $$
CREATE TRIGGER actualizaCajaPorSucursal
BEFORE UPDATE ON ec_caja_o_cuenta_sucursal
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1 AND new.sincronizar=1)
    THEN        
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_caja_o_cuenta_sucursal',new.id_caja_o_cuenta_sucursal,2,1,
        CONCAT("UPDATE ec_caja_o_cuenta_sucursal SET ",
                "id_caja_o_cuenta='",new.id_caja_o_cuenta,"',",
                "id_sucursal='",new.id_sucursal,"',",  
                "estado_suc='",new.estado_suc,"',",         
                "ultima_modificacion='",new.ultima_modificacion,"',",
                "sincronizar=0 WHERE id_caja_o_cuenta='",new.id_caja_o_cuenta,"' AND id_sucursal=",new.id_sucursal
        ),
        1,0,CONCAT('Se modifico la caja ',new.id_caja_o_cuenta,' para la sucursal ',
            (SELECT nombre FROM sys_sucursales WHERE id_sucursal=new.id_sucursal)),now(),0,0,'id_caja_o_cuenta_sucursal'
        FROM sys_sucursales WHERE id_sucursal=new.id_sucursal;
    END IF;
    SET new.sincronizar=1;
END $$