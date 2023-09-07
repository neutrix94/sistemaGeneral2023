DROP TRIGGER IF EXISTS after_update_almacen_producto|
DELIMITER $$
CREATE TRIGGER after_update_almacen_producto
AFTER UPDATE ON ec_almacen_producto
FOR EACH ROW
BEGIN
    IF new.id_almacen = '1' and new.id_producto is not null THEN
        INSERT INTO ec_sync_magento(tipo,id_registro,estatus,detalle)
        
        select distinct 'Producto' tipo, almacen.id_producto id_registro, '1' estatus, 'update' detalle
			from ec_almacen_producto almacen
			inner join ec_producto_tienda_linea linea on linea.id_producto=almacen.id_producto
			where almacen.id_producto=new.id_producto
			and almacen.id_almacen=1
            and linea.habilitado=1
		;
    END IF;
END $$