DROP TRIGGER IF EXISTS eliminaSubcategoria|
DELIMITER $$
CREATE TRIGGER eliminaSubcategoria
AFTER DELETE ON ec_subcategoria
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_subcategoria',old.id_subcategoria,3,6,
        CONCAT("DELETE FROM ec_subcategoria WHERE id_subcategoria='",old.id_subcategoria,"'"),
        0,0,CONCAT('Se eliminó tipo ',old.nombre),now(),0,0,'id_subcategoria'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$