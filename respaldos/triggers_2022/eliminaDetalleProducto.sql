DROP TRIGGER IF EXISTS eliminaDetalleProducto|
DELIMITER $$
CREATE TRIGGER eliminaDetalleProducto
AFTER DELETE ON ec_productos_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1)
    THEN
    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos_detalle',old.id_producto_detalle,3,1,
        CONCAT("DELETE FROM ec_productos_detalle WHERE id_producto_detalle='",old.id_producto_detalle,"'"),
        0,0,CONCAT('Se eliminó el detalle de producto del producto',(SELECT nombre from ec_productos WHERE id_productos=old.id_producto)),now(),0,0,'id_producto_detalle'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$