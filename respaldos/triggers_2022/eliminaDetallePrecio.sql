DROP TRIGGER IF EXISTS eliminaDetallePrecio|
DELIMITER $$
CREATE TRIGGER eliminaDetallePrecio
AFTER DELETE ON ec_precios_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_precios_detalle',old.id_precio_detalle,3,6,
        CONCAT("DELETE FROM ec_precios_detalle WHERE id_precio_detalle='",old.id_precio_detalle,"'"),
        0,0,CONCAT('Se eliminó precio del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=old.id_producto)),now(),0,0,'id_precio_detalle'
        FROM sys_sucursales WHERE id_sucursal>0 ;
    END IF;
END $$