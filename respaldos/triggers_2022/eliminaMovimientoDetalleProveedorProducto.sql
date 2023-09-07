DROP TRIGGER IF EXISTS eliminaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER eliminaMovimientoDetalleProveedorProducto
AFTER DELETE ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
DECLARE final_inventory FLOAT;
SET final_inventory = ( ( old.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = old.id_tipo_movimiento ) ) );
UPDATE ec_inventario_proveedor_producto SET inventario = ( inventario - final_inventory)
WHERE id_proveedor_producto = old.id_proveedor_producto AND id_almacen = old.id_almacen;
END $$