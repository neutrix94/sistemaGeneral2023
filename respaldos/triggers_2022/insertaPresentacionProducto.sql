DROP TRIGGER IF EXISTS insertaPresentacionProducto|
DELIMITER $$
CREATE TRIGGER insertaPresentacionProducto
AFTER INSERT ON ec_productos_presentaciones
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(id_suc=-1 AND new.sincronizar!=0)
    THEN

    	INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos_presentaciones',new.id_producto_presentacion,1,1,
        CONCAT("INSERT INTO ec_productos_presentaciones SET ",
                "id_producto_presentacion='",new.id_producto_presentacion,"',",
                "id_producto='",new.id_producto,"',",
                "nombre='",new.nombre,"',",          
                "cantidad='",new.cantidad,"',",
                "unidad_medida='",new.unidad_medida,"',",
                "sincronizar=0",
                "___UPDATE ec_productos_presentaciones SET sincronizar=0 WHERE id_producto_presentacion='",new.id_producto_presentacion,"'"
        ),
        1,0,CONCAT('Se agregó una presentación para el producto ',(SELECT nombre from ec_productos WHERE id_productos=new.id_producto)),now(),0,0,'id_producto_presentacion'
        FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
END $$