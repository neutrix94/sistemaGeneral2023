DROP TRIGGER IF EXISTS eliminaMovimientoAlmacen|
DELIMITER $$
CREATE TRIGGER eliminaMovimientoAlmacen
BEFORE DELETE ON ec_movimiento_almacen
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_almacen_detalle IN(
	SELECT id_movimiento_almacen_detalle FROM ec_movimiento_detalle WHERE id_movimiento = old.id_movimiento_almacen
	);
END $$
