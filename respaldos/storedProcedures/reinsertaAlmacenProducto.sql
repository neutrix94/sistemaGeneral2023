DROP PROCEDURE IF EXISTS reinsertaAlmacenProducto|
DELIMITER $$
CREATE PROCEDURE reinsertaAlmacenProducto()
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
			INSERT INTO ec_almacen_producto ( id_almacen_producto, id_almacen, id_producto, inventario )
			SELECT
				NULL,
				warehouse_id,
				p.id_productos,
				0
			FROM ec_productos p
			LEFT JOIN ec_almacen_producto ap
			ON ap.id_producto = p.id_productos
			AND ap.id_almacen = warehouse_id
			WHERE ap.id_producto IS NULL
			AND p.id_productos > 0; 
		END LOOP;
	CLOSE recorre;   
END $$