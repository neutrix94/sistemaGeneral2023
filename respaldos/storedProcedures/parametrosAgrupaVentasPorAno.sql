DROP PROCEDURE IF EXISTS parametrosAgrupaVentasPorAno|
DELIMITER $$
CREATE PROCEDURE parametrosAgrupaVentasPorAno(IN tipo_agrupacion_ventas INTEGER(1), IN minimo_dias INTEGER(11))
BEGIN
-- Declaramos las variables necesarias
-- La primera para saber cuando se detendra la consulta
	DECLARE done INT DEFAULT FALSE;
-- Esta variable son las que recibiran los elementos necesarios
	DECLARE fecha_tmp VARCHAR(10);
-- La variable que declararemos para concatenar los resultados
	DECLARE fecha_base VARCHAR(10);
/*sacamos la fecha restando los días*/
/*Recorre se llma la variable CURSOR que recorre en base a la consulta*/
	DECLARE recorre CURSOR FOR
		SELECT DATE_FORMAT(fecha_alta,'%Y') FROM ec_pedidos
		WHERE fecha_alta<=(SELECT date_add(CURRENT_DATE(), INTERVAL (minimo_dias*-1) DAY))
		AND id_pedido!=-1
		GROUP BY DATE_FORMAT(fecha_alta,'%Y');
-- Se declara un manejador para saber cuando se tiene que detener
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
-- Se abre el cursor
		OPEN recorre;
		SET fecha_tmp= "";/*reseteamos la fecha*/
		loop_recorre: LOOP  	
			-- Fetch lo utilizamos para leer cada uno de los registros
				FETCH recorre INTO fecha_tmp; 
				/*SET fecha_tmp=DATE_FORMAT(fecha_tmp,'%Y-%m-%d');*/     
			-- If que permite salir del ciclo
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			CALL agrupaVentas(3,fecha_tmp);
		/*INSERT INTO prueba_dias_movimientos VALUES(null,fecha_tmp);*/
			INSERT INTO sys_prueba_mantenimiento VALUES(null,tipo_agrupacion_ventas,
				(SELECT COUNT(*) FROM ec_pedidos WHERE id_pedido!=-1 /*AND id_equivalente!=0*/AND fecha_alta=fecha_tmp),
				(SELECT DATE_FORMAT(max(fecha_alta),'%Y-%m-%d') FROM ec_pedidos WHERE fecha_alta like CONCAT('%',fecha_tmp,'%')),now());
		END LOOP;
-- cerramos el cursor
	CLOSE recorre;   
END $$