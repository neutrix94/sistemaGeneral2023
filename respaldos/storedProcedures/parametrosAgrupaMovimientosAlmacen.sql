DROP PROCEDURE IF EXISTS parametrosAgrupaMovimientosAlmacen| 
DELIMITER $$
CREATE PROCEDURE parametrosAgrupaMovimientosAlmacen(IN tipo_agrupacion_movimientos INTEGER(1), IN minimo_dias INTEGER(11))
BEGIN
-- Declaramos las variables necesarias
-- La primera para saber cuando se detendra la consulta
	DECLARE done INT DEFAULT FALSE;
-- Esta variable son las que recibiran los elementos necesarios
	DECLARE fecha_tmp DATE;
-- La variable que declararemos para concatenar los resultados
	DECLARE fecha_base VARCHAR(10);
/*sacamos la fecha restando los días*/
/*Recorre se llma la variable CURSOR que recorre en base a la consulta*/
	DECLARE recorre CURSOR FOR
		SELECT DATE_FORMAT(fecha,'%Y-%m-%d') FROM ec_movimiento_almacen 
		WHERE fecha<=(SELECT date_add(CURRENT_DATE(), INTERVAL (minimo_dias*-1) DAY))
		AND id_movimiento_almacen!=-1
		AND status_agrupacion = -1/*Implemetado por Oscar 2023 para que solo tome las fechas con movimientos*/
		GROUP BY fecha;    
-- Se declara un manejador para saber cuando se tiene que detener
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
-- Se abre el cursor
		OPEN recorre;
		SET fecha_tmp= "";/*reseteamos la fecha*/
		loop_recorre: LOOP  	
				FETCH recorre INTO fecha_tmp; 
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			CALL agrupaMovimientosAlmacen(2,fecha_tmp);
		END LOOP;
-- cerramos el cursor
	CLOSE recorre;   
END $$