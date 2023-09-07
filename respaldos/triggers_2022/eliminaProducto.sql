DROP TRIGGER IF EXISTS eliminaProducto|
DELIMITER $$
CREATE TRIGGER eliminaProducto
AFTER DELETE ON ec_productos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos',old.id_productos,3,1,CONCAT('Se eliminó producto ',old.nombre),now(),0,0
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    IF(id_suc>0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,-1,'ec_productos',old.id_productos,3,1,CONCAT('Se elimninó producto ',old.nombre),now(),0,0);
    END IF;
END $$