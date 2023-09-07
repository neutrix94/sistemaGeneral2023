DROP TRIGGER IF EXISTS actualizaDetalleProducto|
DELIMITER $$
CREATE TRIGGER actualizaDetalleProducto
BEFORE UPDATE ON ec_productos_detalle
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos_detalle',new.id_producto_detalle,2,1,
        CONCAT("UPDATE ec_productos_detalle SET ",
                "id_producto='",new.id_producto,"',",
                "id_producto_ordigen='",new.id_producto_ordigen,"',",           
                "cantidad='",new.cantidad,"',",
                "alta='",new.alta,"',",          
                "ultima_modificacion='",new.ultima_modificacion,"',",
                "sincronizar=0 WHERE id_producto_detalle='",new.id_producto_detalle,"'"
        ),
        0,0,CONCAT('Se actualizó el detalle de producto del producto',(SELECT nombre from ec_productos WHERE id_productos=new.id_producto)),now(),0,0,'id_producto_detalle'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
    SET new.sincronizar=1;
END $$