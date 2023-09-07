DROP TRIGGER IF EXISTS actualizaFactorEstacionalidadCategoria|
DELIMITER $$
CREATE TRIGGER actualizaFactorEstacionalidadCategoria
BEFORE UPDATE ON ec_factores_estacionalidad_categorias
FOR EACH ROW
BEGIN
	IF( new.factor != old.factor )
	THEN
		IF( new.id_tipo_factor = 1 )
		THEN
			UPDATE ec_estacionalidad_producto ep
			LEFT JOIN ec_productos p 
			ON p.id_productos = ep.id_producto
			LEFT JOIN ec_categoria c
			ON c.id_categoria = c.id_categoria 
			LEFT JOIN ec_estacionalidad e
			ON e.id_estacionalidad = ep.id_estacionalidad
			RIGHT JOIN sys_sucursales s
			ON e.id_estacionalidad = s.id_estacionalidad
			SET ep.minimo = IF(FLOOR( new.factor * ep.maximo ) <= 0, 0, FLOOR( new.factor * ep.maximo ) )
			WHERE c.id_categoria = new.id_categoria
			AND p.excluir_factores_por_categoria = 0;
		END IF;

		IF( new.id_tipo_factor = 2 )
		THEN
			UPDATE ec_estacionalidad_producto ep
			LEFT JOIN ec_productos p 
			ON p.id_productos = ep.id_producto
			LEFT JOIN ec_categoria c
			ON c.id_categoria = c.id_categoria 
			LEFT JOIN ec_estacionalidad e
			ON e.id_estacionalidad = ep.id_estacionalidad
			RIGHT JOIN sys_sucursales s
			ON e.id_estacionalidad = s.id_estacionalidad
			SET ep.medio = ROUND( ep.maximo * new.factor )
			WHERE c.id_categoria = new.id_categoria
			AND p.excluir_factores_por_categoria = 0;
		END IF;

		IF( new.id_tipo_factor = 4 )
		THEN
			UPDATE sys_sucursales_producto sp
				LEFT JOIN ec_productos p
				ON p.id_productos = sp.id_producto
				LEFT JOIN sys_sucursales s
				ON s.id_sucursal = sp.id_sucursal
				LEFT JOIN ec_estacionalidad e
				ON e.id_estacionalidad = s.id_estacionalidad
				LEFT JOIN ec_estacionalidad_producto ep
				ON ep.id_estacionalidad = e.id_estacionalidad
				AND ep.id_producto = sp.id_producto
				SET sp.minimo_surtir = IF( FLOOR( new.factor * ep.maximo ) <= 0, 0, FLOOR( new.factor * ep.maximo ) ),
				sp.sincronizar = 0
	        WHERE p.id_categoria = new.id_categoria
	        AND p.excluir_factores_por_categoria = 0;
		END IF;

	END IF;
END $$