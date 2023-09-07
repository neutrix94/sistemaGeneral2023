DROP TRIGGER IF EXISTS actualizaConceptoMovCaja|
DELIMITER $$
CREATE TRIGGER actualizaConceptoMovCaja
BEFORE UPDATE ON ec_concepto_movimiento
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar=1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_concepto_movimiento',new.id_concepto_movimiento,2,1,
        CONCAT("UPDATE ec_concepto_movimiento SET ",
                "nombre='",new.nombre,"',",
                "afecta='",new.afecta,"',",           
                "sincronizar=0 WHERE id_concepto_movimiento='",new.id_concepto_movimiento,"'"
        ),
        1,0,CONCAT('Se modifico el concepto movimiento ',new.nombre),now(),0,0,'id_concepto_movimiento'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$