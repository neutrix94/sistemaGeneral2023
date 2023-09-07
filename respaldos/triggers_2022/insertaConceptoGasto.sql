DROP TRIGGER IF EXISTS insertaConceptoGasto|
DELIMITER $$
CREATE TRIGGER insertaConceptoGasto
AFTER INSERT ON ec_conceptos_gastos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_conceptos_gastos',new.id_concepto,1,3,
        CONCAT("INSERT INTO ec_conceptos_gastos SET ",
                "id_concepto='",new.id_concepto,"',",
                "nombre='",new.nombre,"',",    
                "sincronizar=0",
                "___UPDATE ec_conceptos_gastos SET sincronizar=0 WHERE id_concepto='",new.id_concepto,"'"
        ),
        1,0,CONCAT('Se agregó el concepto de gasto ',new.nombre),now(),0,0,'id_concepto'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$