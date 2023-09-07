DROP TRIGGER IF EXISTS eliminaCajaOCuenta|
DELIMITER $$
CREATE TRIGGER eliminaCajaOCuenta
AFTER DELETE ON ec_caja_o_cuenta
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_caja_o_cuenta',old.id_caja_cuenta,3,1,
        CONCAT("DELETE FROM ec_caja_o_cuenta WHERE id_caja_cuenta='",old.id_caja_cuenta,"'"),
        0,0,CONCAT('Se eliminó la caja o cuenta ',old.nombre),now(),0,0,'id_caja_cuenta'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$