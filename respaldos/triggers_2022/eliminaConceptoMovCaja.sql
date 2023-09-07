DROP TRIGGER IF EXISTS eliminaConceptoMovCaja|
DELIMITER $$
CREATE TRIGGER eliminaConceptoMovCaja
AFTER DELETE ON ec_concepto_movimiento
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_afiliaciones',old.id_concepto_movimiento,3,1,
        CONCAT("DELETE FROM ec_concepto_movimiento WHERE id_concepto_movimiento='",old.id_concepto_movimiento,"'"),
        0,0,CONCAT('Se elimino concepto caja ',old.nombre),now(),0,0,'id_concepto_movimiento'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$