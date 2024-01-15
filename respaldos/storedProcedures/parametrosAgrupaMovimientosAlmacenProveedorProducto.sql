DROP PROCEDURE IF EXISTS parametrosAgrupaMovimientosAlmacenProveedorProducto|
DELIMITER $$
CREATE PROCEDURE parametrosAgrupaMovimientosAlmacenProveedorProducto(IN tipo_agrupacion_movimientos INTEGER(1), IN minimo_dias INTEGER(11))
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE fecha_tmp DATE;
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT 
			DATE_FORMAT(fecha_registro,'%Y-%m-%d') 
		FROM ec_movimiento_detalle_proveedor_producto
		WHERE fecha_registro<=(SELECT date_add(CURRENT_DATE(), INTERVAL (minimo_dias*-1) DAY))
		AND id_movimiento_detalle_proveedor_producto!=-1
		AND status_agrupacion = -1/*Implemetado por Oscar 2023 para que solo tome las fechas con movimientos*/
		AND DATE_FORMAT(fecha_registro,'%Y-%m-%d') IS NOT NULL
		AND folio_unico IS NOT NULL
		GROUP BY DATE_FORMAT(fecha_registro,'%Y-%m-%d');    
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
			CALL agrupaMovimientosProveedorProducto(2,fecha_tmp);
		END LOOP;
-- cerramos el cursor
	CLOSE recorre;   
	CALL recalculaInventarioAlmacenProveedorProducto();
END $$