DROP TRIGGER IF EXISTS eliminaAlmacen|
DELIMITER $$
CREATE TRIGGER eliminaAlmacen
AFTER DELETE ON ec_almacen
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    IF(id_suc=-1)
    THEN
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_almacen',old.id_almacen,3,1,
        CONCAT("DELETE FROM ec_almacen WHERE id_almacen='",old.id_almacen,"'"),
        0,0,CONCAT('Se eliminó el almacen ',old.nombre),now(),0,0,'id_almacen'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$