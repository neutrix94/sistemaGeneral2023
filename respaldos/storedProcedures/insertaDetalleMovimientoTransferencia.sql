DROP PROCEDURE IF EXISTS insertaDetalleMovimientoTransferencia|
DELIMITER $$
CREATE PROCEDURE insertaDetalleMovimientoTransferencia( IN id_transferencia INTEGER(11), IN id_movimiento_almacen BIGINT )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE id_producto INTEGER;
	DECLARE cantidad FLOAT(15,2);
	DECLARE id_proveedor_producto INTEGER;
	DECLARE recorre CURSOR FOR
		SELECT 
            tp.id_producto_or,
            tp.cantidad,
            tp.id_proveedor_producto
			FROM ec_transferencia_productos tp
			WHERE tp.id_transferencia = id_transferencia
			AND tp.omite_movimiento_origen = 0;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET id_producto = 0;/*resetea id de venta*/
		SET cantidad = 0;
		SET id_proveedor_producto = 0;
		loop_recorre: LOOP  	
				FETCH recorre INTO id_producto, cantidad, id_proveedor_producto;
			IF done THEN
				LEAVE loop_recorre;
			END IF;
            CALL spMovimientoAlmacenDetalle_inserta ( id_movimiento_almacen, id_producto, cantidad, cantidad, -1, -1, id_proveedor_producto, 4, NULL ); 
		END LOOP;
	CLOSE recorre;
END $$