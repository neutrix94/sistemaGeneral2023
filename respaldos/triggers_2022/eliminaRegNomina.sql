DROP TRIGGER IF EXISTS eliminaRegNomina|
DELIMITER $$
CREATE TRIGGER eliminaRegNomina
AFTER DELETE ON ec_registro_nomina
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	 	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_registro_nomina',old.id_registro_nomina,3,4,
        CONCAT("DELETE FROM ec_registro_nomina WHERE id_equivalente='",old.id_registro_nomina,"' AND id_sucursal='",old.id_sucursal,"'"),
        0,0,'Se eliminó registro de nómina',now(),0,0,'id_registro_nomina'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=old.id_sucursal,id_sucursal=-1);
END $$