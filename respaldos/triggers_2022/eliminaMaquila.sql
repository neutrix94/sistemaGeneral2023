DROP TRIGGER IF EXISTS eliminaMaquila|
DELIMITER $$
CREATE TRIGGER eliminaMaquila
AFTER DELETE ON ec_maquila
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_maquila',old.id_maquila,3,1,
        CONCAT("DELEET FROM ec_maquila WHERE id_maquila='",old.id_maquila,"'"),
        0,0,CONCAT('Se eliminó maquila ',old.folio),now(),0,0,'id_maquila'
        FROM sys_sucursales WHERE id_sucursal=old.id_sucursal;
    END IF;
    IF(id_suc>0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,-1,'ec_maquila',old.id_maquila,3,1,CONCAT('Se eliminó maquila ',old.folio),now(),0,0);
    END IF;
END $$