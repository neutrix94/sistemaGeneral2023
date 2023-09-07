DROP TRIGGER IF EXISTS actualizaTiposCajaCuenta|
DELIMITER $$
CREATE TRIGGER actualizaTiposCajaCuenta
BEFORE UPDATE ON ec_tipo_banco_caja
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar=1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_tipo_banco_caja',new.id_tipo_banco_caja,2,1,
        CONCAT("UPDATE ec_caja_o_cuenta SET ",
                "nombre='",new.nombre,"',",
                "observaciones='",new.observaciones,"',",  
                "fecha_alta='",new.fecha_alta,"',",         
                "sincronizar=0 WHERE id_tipo_banco_caja='",new.id_tipo_banco_caja,"'"
        ),
        1,0,CONCAT('Se modifico tipo de caja ',new.nombre),now(),0,0,'id_tipo_banco_caja'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$