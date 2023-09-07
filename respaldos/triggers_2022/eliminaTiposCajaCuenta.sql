DROP TRIGGER IF EXISTS eliminaTiposCajaCuenta|
DELIMITER $$
CREATE TRIGGER eliminaTiposCajaCuenta
AFTER DELETE ON ec_tipo_banco_caja
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_tipo_banco_caja',old.id_tipo_banco_caja,3,1,
        CONCAT("DELETE FROM ec_tipo_banco_caja WHERE id_tipo_banco_caja='",old.id_tipo_banco_caja,"'"),
        0,0,CONCAT('Se elimino tipo de caja ',old.nombre),now(),0,0,'id_tipo_banco_caja'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$