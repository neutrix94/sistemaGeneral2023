DROP TRIGGER IF EXISTS eliminaConceptoGasto|
DELIMITER $$
CREATE TRIGGER eliminaConceptoGasto
AFTER DELETE ON ec_conceptos_gastos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_conceptos_gastos',old.id_concepto,3,3,
        CONCAT("DELETE FROM ec_conceptos_gastos WHERE id_concepto='",old.id_concepto,"'"),
        0,0,CONCAT('Se eliminó el concepto de gasto ',old.nombre),now(),0,0,'id_concepto'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$