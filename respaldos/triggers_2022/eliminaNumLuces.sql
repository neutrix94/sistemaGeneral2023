DROP TRIGGER IF EXISTS eliminaNumLuces|
DELIMITER $$
CREATE TRIGGER eliminaNumLuces
AFTER DELETE ON ec_numero_luces
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_numero_luces',old.id_numero_luces,3,6,
        CONCAT("DELETE FROM ec_numero_luces WHERE id_numero_luces='",old.id_numero_luces,"'"),
        0,0,CONCAT('Se eliminó el numero de luces ',old.nombre),now(),0,0,'id_numero_luces'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$