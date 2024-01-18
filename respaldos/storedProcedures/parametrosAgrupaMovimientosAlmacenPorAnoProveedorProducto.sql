DROP PROCEDURE IF EXISTS parametrosAgrupaMovimientosAlmacenPorAnoProveedorProducto| 
DELIMITER $$
CREATE PROCEDURE parametrosAgrupaMovimientosAlmacenPorAnoProveedorProducto( IN tipo_agrupacion_movimientos INTEGER(1), IN minimo_dias INTEGER(11) )
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE fecha_tmp VARCHAR(10);
	DECLARE fecha_base VARCHAR(10);
	DECLARE recorre CURSOR FOR
		SELECT DATE_FORMAT(fecha_registro,'%Y') 
		FROM ec_movimiento_detalle_proveedor_producto
		WHERE fecha_registro <= ( SELECT date_add(CURRENT_DATE(), INTERVAL (minimo_dias*-1) DAY) )
		AND id_movimiento_detalle_proveedor_producto!=-1/*'2019-05-15'*/
		AND folio_unico IS NOT NULL
		GROUP BY DATE_FORMAT(fecha_registro,'%Y');    
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN recorre;
		SET fecha_tmp= "";/*reseteamos la fecha*/
		loop_recorre: LOOP  	
				FETCH recorre INTO fecha_tmp;    
			IF done THEN
				LEAVE loop_recorre;
			END IF;
			CALL agrupaMovimientosProveedorProducto(3,fecha_tmp);
		END LOOP;
	CLOSE recorre;  
	CALL recalculaInventarioAlmacenProveedorProducto();
END $$