DROP TRIGGER IF EXISTS eliminaTamano|
DELIMITER $$
CREATE TRIGGER eliminaTamano
AFTER DELETE ON ec_tamanos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_tamanos',old.id_tamanos,3,6,
        CONCAT("DELETE FROM ec_tamanos WHERE id_tamanos='",old.id_tamanos,"'"),
        0,0,CONCAT('Se eliminó el tamaño ',old.nombre),now(),0,0,'id_tamanos'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$