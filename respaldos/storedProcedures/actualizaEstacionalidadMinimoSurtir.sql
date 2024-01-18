DROP PROCEDURE IF EXISTS actualizaEstacionalidadMinimoSurtir|
DELIMITER $$
CREATE PROCEDURE actualizaEstacionalidadMinimoSurtir(IN estActiva INTEGER(1),IN sucActiva INTEGER(11))
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE id_est_prod INT;
	DECLARE id_prod INT;
	DECLARE maximo_estacionalidad INT;
	DECLARE id_categoria_producto INT;
	DECLARE factor_minimo_surtir FLOAT( 8, 3 );

	DECLARE recorre CURSOR FOR
		SELECT
			ep.id_estacionalidad_producto,
			ep.id_producto,
			ep.maximo,
			p.id_categoria
		FROM ec_estacionalidad_producto ep
		LEFT JOIN ec_productos p
		ON p.id_productos = ep.id_producto
		WHERE ep.id_estacionalidad = estActiva;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	OPEN recorre;
	loop_recorre: LOOP  	
		FETCH recorre INTO id_est_prod, id_prod, maximo_estacionalidad, id_categoria_producto;
		IF done THEN
			LEAVE loop_recorre;
		END IF;
		
		SELECT factor INTO factor_minimo_surtir FROM ec_factores_estacionalidad_categorias WHERE id_categoria = id_categoria_producto AND id_tipo_factor = 4;

		UPDATE sys_sucursales_producto sp 
			SET sp.minimo_surtir=IF( FLOOR( factor_minimo_surtir * maximo_estacionalidad ) <=1 , 1,FLOOR( factor_minimo_surtir * maximo_estacionalidad ) ),sp.sincronizar=0
		WHERE sp.id_producto=id_prod
		AND sp.id_sucursal=sucActiva;

	END LOOP;
	CLOSE recorre;   
END $$