DROP PROCEDURE IF EXISTS parametrosAgrupaMovimientosAlmacenPorDiaCron| 
DELIMITER $$
CREATE PROCEDURE parametrosAgrupaMovimientosAlmacenPorDiaCron( IN intervalo_dias INTEGER(11) )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE fecha_tmp DATE;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT DATE_FORMAT(fecha,'%Y-%m-%d') FROM ec_movimiento_almacen 
		WHERE fecha<=(SELECT date_add(CURRENT_DATE(), INTERVAL (intervalo_dias*-1) DAY))
		AND id_movimiento_almacen!=-1
		AND status_agrupacion = -1
		AND folio_unico IS NOT NULL
		GROUP BY fecha;    
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
-- Se abre el cursor
		OPEN recorre;
		SET fecha_tmp= "";/*reseteamos la fecha*/
		loop_recorre: LOOP  	
				FETCH recorre INTO fecha_tmp; 
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			CALL agrupaMovimientosAlmacenPorDiaCron(2,fecha_tmp);
		END LOOP;
-- cerramos el cursor
	CLOSE recorre;   
/*mandamos llamar procedure que recalcula inventario a nivel producto*/
	CALL recalculaInventariosAlmacen();
END $$