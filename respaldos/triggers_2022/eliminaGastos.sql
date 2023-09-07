DROP TRIGGER IF EXISTS eliminaGastos|
DELIMITER $$
CREATE TRIGGER eliminaGastos
AFTER DELETE ON ec_gastos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_gastos',old.id_gastos,3,3,
    CONCAT("DELETE FROM ec_gastos WHERE id_equivalente='",old.id_gastos,"' AND id_sucursal='",old.id_sucursal,"'"),
    0,0,CONCAT('Se eliminó un gasto por concepto de ',(SELECT nombre FROM ec_conceptos_gastos WHERE id_concepto=old.id_concepto)),
    now(),0,0,'id_gastos'
    FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=old.id_sucursal,id_sucursal=-1); 

END $$