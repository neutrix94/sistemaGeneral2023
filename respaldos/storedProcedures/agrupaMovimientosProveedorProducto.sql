DROP PROCEDURE IF EXISTS agrupaMovimientosProveedorProducto|
DELIMITER $$
CREATE PROCEDURE agrupaMovimientosProveedorProducto(IN tipo_agrupacion INTEGER(1),IN fecha_agrupacion VARCHAR(10))
BEGIN
/*declaramos variables*/
	DECLARE contador_tipos_movimiento INTEGER(11);
	DECLARE tope_tipos_movimiento INTEGER(11);
	DECLARE num_almacenes INTEGER(11);
	DECLARE tope_almacenes INTEGER(11);
	DECLARE id_sucursal_tmp INTEGER(11);/*id de movimiento_insertado*/
	DECLARE id_almacen_tmp INTEGER(11);/*id de movimiento_insertado*/
	DECLARE verif_almacen INTEGER(11);/*id de movimiento_insertado*/
	DECLARE verif_almacen_detalle INTEGER(11);/*id de movimiento_insertado*/
	DECLARE fecha_agrupacion_auxiliar VARCHAR(10);

/*SELECT fecha_agrupacion;*/
START TRANSACTION;
	IF(tipo_agrupacion=3)/*por ano*/
	THEN
		SELECT max(fecha_registro) INTO fecha_agrupacion_auxiliar FROM ec_movimiento_detalle_proveedor_producto WHERE fecha_registro LIKE CONCAT('%',fecha_agrupacion,'%');
	END IF;

	IF(tipo_agrupacion=4)/*por todos los anteriores*/
	THEN
		SELECT date_add(CURRENT_DATE(), INTERVAL (fecha_agrupacion*-1) DAY) INTO fecha_agrupacion;
	/*	SELECT add(fecha) INTO fecha_agrupacion_auxiliar FROM ec_movimiento_almacen WHERE fecha LIKE CONCAT('%',fecha_agrupacion,'%');*/
	END IF;

/*extraemos el numero de tipos de movimiento*/
	SELECT COUNT(*) INTO tope_tipos_movimiento FROM ec_tipos_movimiento;
/*SELECT tope_tipos_movimiento;*/
/*extraemos el id maximo de almacenes*/
	SELECT MAX(id_almacen) INTO tope_almacenes FROM ec_almacen WHERE id_almacen > 0;
/*SELECT tope_almacenes;*/
/*inicializamos el contador en ceros*/
	SET contador_tipos_movimiento = 1;

/*corremos while de tipos de movimiento*/
	WHILE contador_tipos_movimiento <= tope_tipos_movimiento DO

		IF(tipo_agrupacion=2)/*por día*/
		THEN
			UPDATE ec_movimiento_detalle_proveedor_producto 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento = contador_tipos_movimiento
			AND id_movimiento_detalle_proveedor_producto !=-1
			AND status_agrupacion=-1 
			AND fecha_registro LIKE CONCAT('%',fecha_agrupacion,'%')
			AND folio_unico IS NOT NULL;
		END IF;

		IF(tipo_agrupacion=3)/*por ano*/
		THEN
			UPDATE ec_movimiento_detalle_proveedor_producto 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento = contador_tipos_movimiento
			AND id_movimiento_detalle_proveedor_producto !=-1
			AND status_agrupacion = 2 
			AND fecha_registro LIKE CONCAT('%',fecha_agrupacion,'%')
			AND folio_unico IS NOT NULL;
		END IF;

		IF(tipo_agrupacion=4)/*por historico*/
		THEN
			UPDATE ec_movimiento_detalle_proveedor_producto 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento = contador_tipos_movimiento
			AND id_movimiento_detalle_proveedor_producto !=-1
			AND status_agrupacion = 3 
			AND fecha_registro LIKE CONCAT('%',fecha_agrupacion,'%')
			AND folio_unico IS NOT NULL;
		END IF;	

	/*declaramos en 1 id de almacen*/
		SET num_almacenes=1;

	/*corremos while anidado para el contador de almacenes*/
		WHILE num_almacenes<=tope_almacenes DO
			SET verif_almacen_detalle=0;
		/*vemos si el almacen existe y si sacamos su sucursal*/
			SELECT count(*) INTO verif_almacen FROM ec_almacen WHERE id_almacen=num_almacenes;
		/**/
			SELECT COUNT(mdpp.id_movimiento_detalle_proveedor_producto ) INTO verif_almacen_detalle
				FROM ec_movimiento_detalle_proveedor_producto mdpp 
				WHERE mdpp.id_tipo_movimiento = contador_tipos_movimiento
				AND mdpp.status_agrupacion=1
				AND mdpp.id_almacen=num_almacenes
				AND mdpp.fecha_registro LIKE CONCAT('%', fecha_agrupacion,'%')
				AND mdpp.folio_unico IS NOT NULL;
	
			/*IF(verif_almacen=1 AND verif_almacen_detalle>0)	
			THEN/*	
			/*extraemos datos del almacen*/
				SELECT id_almacen,id_sucursal INTO id_almacen_tmp,id_sucursal_tmp FROM ec_almacen WHERE id_almacen IN(num_almacenes);
/*SELECT id_almacen,id_sucursal FROM ec_almacen WHERE id_almacen IN(num_almacenes);*/

				INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle, 
					id_proveedor_producto, cantidad, fecha_registro, id_sucursal, id_equivalente, status_agrupacion, id_tipo_movimiento, id_almacen,
					id_pedido_validacion, sincronizar, folio_unico )
				SELECT
					NULL,
					NULL,
					mdpp.id_proveedor_producto,
					SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 0, mdpp.cantidad ) ),
					CONCAT( fecha_agrupacion, ' 00:00:01' ),
					id_sucursal_tmp,
					-1,
					tipo_agrupacion,
					mdpp.id_tipo_movimiento,
					id_almacen_tmp,
					-1,
					1,
					NULL
				FROM ec_movimiento_detalle_proveedor_producto mdpp
				WHERE mdpp.id_tipo_movimiento = contador_tipos_movimiento
				AND mdpp.status_agrupacion = 1
				AND mdpp.id_almacen = id_almacen_tmp
				AND mdpp.id_proveedor_producto IS NOT NULL
				AND mdpp.fecha_registro LIKE CONCAT('%', fecha_agrupacion ,'%')
				AND mdpp.folio_unico IS NOT NULL
				GROUP BY mdpp.id_proveedor_producto;

			/*eliminamos los movimientos de almacen despues de haberlos agrupado*/
				IF(tipo_agrupacion=4)
				THEN
					DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento = contador_tipos_movimiento
					AND fecha_registro <= CONCAT( fecha_agrupacion, ' 23:59:59' ) AND mdpp.folio_unico IS NOT NULL;

				ELSE
					DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento = contador_tipos_movimiento
					AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion, '%' ) AND mdpp.folio_unico IS NOT NULL;
				END IF;
			/*END IF;*/
			
			SET num_almacenes=num_almacenes+1;
		
		END WHILE;
	/*aumentamos 1 al contador*/
		SET contador_tipos_movimiento = contador_tipos_movimiento+1;
	END WHILE;
	IF(tipo_agrupacion=3)
	THEN
		INSERT INTO sys_prueba_mantenimiento VALUES(null,( 10+tipo_agrupacion ),
				(SELECT COUNT(*) FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_detalle_proveedor_producto != -1 /*AND id_equivalente!=0 */AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion_auxiliar, '%' ) ),
				(SELECT max(fecha_registro) FROM ec_movimiento_detalle_proveedor_producto WHERE fecha_registro like CONCAT('%',fecha_agrupacion_auxiliar,'%')),now());
	ELSE
		INSERT INTO sys_prueba_mantenimiento VALUES(null,( 10+tipo_agrupacion ),
				(SELECT COUNT(*) FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_detalle_proveedor_producto != -1 /*AND id_equivalente!=0 */AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion_auxiliar, '%' ) ),
				(SELECT max(fecha_registro) FROM ec_movimiento_detalle_proveedor_producto WHERE fecha_registro like CONCAT('%',fecha_agrupacion,'%')),now());
	END IF;
COMMIT;
END $$