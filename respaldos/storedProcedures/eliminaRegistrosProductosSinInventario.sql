DROP PROCEDURE IF EXISTS eliminaRegistrosProductosSinInventario|
DELIMITER $$
CREATE PROCEDURE eliminaRegistrosProductosSinInventario(IN fecha_eliminar VARCHAR(10))
BEGIN
START TRANSACTION;
	SELECT date_add(CURRENT_DATE(), INTERVAL (fecha_eliminar*-1) DAY) INTO fecha_eliminar;
/*Eliminamos movimientos_temporales*/
	DELETE FROM ec_productos_sin_inventario WHERE alta<=CONCAT(fecha_eliminar,' 23:59:59');
COMMIT;
END $$