DROP TRIGGER IF EXISTS eliminaCategoria|
DELIMITER $$
CREATE TRIGGER eliminaCategoria
AFTER DELETE ON ec_categoria
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_categoria',old.id_categoria,3,6,
        CONCAT("DELETE FROM ec_categoria WHERE id_categoria='",old.id_categoria,"'"),
        0,0,CONCAT('Se eliminó familia ',old.nombre),now(),0,0,'id_categoria'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$