DROP PROCEDURE IF EXISTS verificaInventariosCalculadoVsInventarioAcumuladoNivelProducto| 
DELIMITER $$
CREATE PROCEDURE verificaInventariosCalculadoVsInventarioAcumuladoNivelProducto()
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE warehouse_id INT( 11 );
	DECLARE recorre CURSOR FOR
		SELECT
			id_almacen 
		FROM ec_almacen
		WHERE id_almacen > 0;    
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET warehouse_id= "";/*reseteamos la fecha*/
		loop_recorre: LOOP  	
				FETCH recorre INTO warehouse_id;    
			IF done THEN
				LEAVE loop_recorre;
			END IF;	
			
	        SELECT 
	            ax.id_productos,
	            ax.nombre,
	            ax.inventario,
	            ax.nomAlmacen,
	            ax.id_almacen,
	            ax.InvCalculo
	        FROM(
	            SELECT
	                p.id_productos,
	                ROUND( ap.inventario, 4 ) AS inventario,
	                p.nombre,
	                alm.nombre as nomAlmacen,
	                ap.id_almacen,
	                ROUND( SUM( IF(
	                    ma.id_movimiento_almacen IS NULL,
	                    0.0000,
	                    ( md.cantidad * tm.afecta )
	                )
	            	), 4 ) AS InvCalculo
	            FROM ec_almacen_producto ap
	            LEFT JOIN ec_productos p 
	            ON p.id_productos = ap.id_producto
	            LEFT JOIN ec_almacen alm 
	            ON alm.id_almacen = ap.id_almacen
		        LEFT JOIN ec_movimiento_detalle md 
		        ON md.id_producto = p.id_productos
		        LEFT JOIN ec_movimiento_almacen ma 
		        ON ma.id_movimiento_almacen = md.id_movimiento
		        LEFT JOIN ec_tipos_movimiento tm 
		        ON tm.id_tipo_movimiento = ma.id_tipo_movimiento
		        AND ma.id_almacen = warehouse_id
	            WHERE ap.id_almacen = warehouse_id
	            AND p.id_productos > 0
	            GROUP BY p.id_productos, alm.id_almacen
	        )ax
			WHERE ax.inventario <> ax.InvCalculo
			GROUP BY ax.id_productos, ax.id_almacen;
		END LOOP;
	CLOSE recorre;  
END $$