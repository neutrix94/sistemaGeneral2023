DROP TRIGGER IF EXISTS eliminaSucursal|
DELIMITER $$
CREATE TRIGGER eliminaSucursal
BEFORE DELETE ON sys_sucursales
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

	DELETE FROM sys_sucursales_producto WHERE sys_sucursales_producto.id_sucursal=old.id_sucursal;

    DELETE FROM ec_estacionalidad WHERE id_sucursal=old.id_sucursal;
   
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_sucursales',old.id_sucursal,3,6,
        CONCAT("DELETE FROM sys_sucursales WHERE id_sucursal='",old.id_sucursal,"'"),
        0,0,CONCAT('Se eliminó sucursal ',old.nombre),now(),0,0,'id_sucursal'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$