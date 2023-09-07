DROP TRIGGER IF EXISTS actualizaMaquila|
DELIMITER $$
CREATE TRIGGER actualizaMaquila
BEFORE UPDATE ON ec_maquila
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_maquila',new.id_maquila,2,1,CONCAT('Se actualizó maquila ',new.folio),now(),0,0
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    IF(id_suc>0 AND new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,-1,'ec_maquila',new.id_maquila,2,1,CONCAT('Se actualizó maquila ',new.folio),now(),0,0);
    END IF;
    SET new.sincronizar=1;
END $$