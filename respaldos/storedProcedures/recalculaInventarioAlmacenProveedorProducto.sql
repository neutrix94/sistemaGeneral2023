DROP PROCEDURE IF EXISTS recalculaInventarioAlmacenProveedorProducto|
DELIMITER $$
CREATE PROCEDURE recalculaInventarioAlmacenProveedorProducto()
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE warehouse_id INT;

	DECLARE recorre CURSOR FOR
		SELECT
			id_almacen
		FROM ec_almacen
		WHERE id_almacen > 0;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		loop_recorre: LOOP  	
			FETCH recorre INTO warehouse_id; 
			
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			DELETE FROM ec_inventario_proveedor_producto WHERE id_almacen = warehouse_id;
			INSERT INTO ec_inventario_proveedor_producto 
		   		( id_producto, id_proveedor_producto, id_sucursal, id_almacen, inventario, fecha_registro, ultima_actualizacion )
				SELECT
					aux.id_producto,
					aux.id_proveedor_producto,
					alm.id_sucursal,
					warehouse_id,
					aux.inventory,
					NOW(),
					'0000-00-00 00:00:00'
				FROM(
					SELECT 
						pp.id_producto,
						pp.id_proveedor_producto,
						SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 0, ( mdpp.cantidad * tm.afecta ) ) ) AS inventory
					FROM ec_proveedor_producto pp
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
					ON mdpp.id_proveedor_producto = pp.id_proveedor_producto
					AND mdpp.id_almacen IN( warehouse_id )
					LEFT JOIN ec_tipos_movimiento tm
					ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
					WHERE pp.id_proveedor_producto > 0
					GROUP BY pp.id_proveedor_producto
				)aux
				LEFT JOIN ec_almacen alm ON alm.id_almacen = warehouse_id
				GROUP BY aux.id_proveedor_producto, alm.id_almacen
				ORDER BY alm.id_almacen;
		END LOOP;
	CLOSE recorre;   
END $$