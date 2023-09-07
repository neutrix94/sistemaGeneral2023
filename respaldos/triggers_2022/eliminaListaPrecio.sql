DROP TRIGGER IF EXISTS eliminaListaPrecio|
DELIMITER $$
CREATE TRIGGER eliminaListaPrecio
AFTER DELETE ON ec_precios
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios',old.id_precio,3,6,
        CONCAT("DELETE FROM ec_precios WHERE id_precio='",old.id_precio,"'"),
        0,0,CONCAT('Se eliminó lista de Precios ',old.nombre),now(),0,0,'id_precio'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$