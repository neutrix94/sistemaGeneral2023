DROP PROCEDURE IF EXISTS agrupaMovimientosAlmacen|
DELIMITER $$
CREATE PROCEDURE agrupaMovimientosAlmacen(IN tipo_agrupacion INTEGER(1),IN fecha_agrupacion VARCHAR(10))
BEGIN
/*declaramos variables*/
	DECLARE contador INTEGER(11);
	DECLARE tope INTEGER(11);
	DECLARE num_almacenes INTEGER(11);
	DECLARE tope_almacenes INTEGER(11);
	DECLARE movimiento_insertado BIGINT;/*id de movimiento_insertado*/
	DECLARE movimiento_detalle_insertado BIGINT;/*id de movimiento_detalle_insertado*/
	DECLARE id_sucursal_tmp INTEGER(11);/*id de movimiento_insertado*/
	DECLARE id_almacen_tmp INTEGER(11);/*id de movimiento_insertado*/
	DECLARE verif_almacen INTEGER(11);/*id de movimiento_insertado*/
	DECLARE verif_almacen_detalle INTEGER(11);/*id de movimiento_insertado*/
	DECLARE fecha_agrupacion_auxiliar VARCHAR(10);
/*
	DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
	    ROLLBACK;
	END;
*/
START TRANSACTION;
	IF(tipo_agrupacion=3)/*por ano*/
	THEN
		SELECT max(fecha) INTO fecha_agrupacion_auxiliar FROM ec_movimiento_almacen WHERE fecha LIKE CONCAT('%',fecha_agrupacion,'%');
	END IF;

	IF(tipo_agrupacion=4)/*por todos los anteriores*/
	THEN
		SELECT date_add(CURRENT_DATE(), INTERVAL (fecha_agrupacion*-1) DAY) INTO fecha_agrupacion;
	/*	SELECT add(fecha) INTO fecha_agrupacion_auxiliar FROM ec_movimiento_almacen WHERE fecha LIKE CONCAT('%',fecha_agrupacion,'%');*/
	END IF;

/*extraemos el numero de tipos de movimiento*/
	SELECT COUNT(*) INTO tope FROM ec_tipos_movimiento;

/*extraemos el id maximo de almacenes*/
	SELECT MAX(id_almacen) INTO tope_almacenes FROM ec_almacen WHERE id_almacen>0;

/*inicializamos el contador en ceros*/
	SET contador=1;

/*corremos while de tipos de movimiento*/
	WHILE contador<=tope DO

		IF(tipo_agrupacion=2)/*por día*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_movimiento_almacen 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento=contador 
			AND id_movimiento_almacen!=-1 /*AND id_equivalente!=0*/
			AND status_agrupacion=-1 
			AND fecha LIKE CONCAT('%',fecha_agrupacion,'%');

		END IF;

		IF(tipo_agrupacion=3)/*por ano*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_movimiento_almacen 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento=contador 
			AND id_movimiento_almacen!=-1 /*AND id_equivalente!=0*/
			AND status_agrupacion=2 
			AND fecha LIKE CONCAT('%',fecha_agrupacion,'%');
		END IF;

		IF(tipo_agrupacion=4)/*por historico*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_movimiento_almacen 
				SET status_agrupacion=1 
			WHERE id_tipo_movimiento=contador 
			AND id_movimiento_almacen!=-1 /*AND id_equivalente!=0*/
			AND status_agrupacion IN( 3,4 ) 
			AND fecha<=fecha_agrupacion;
		END IF;	

	/*declaramos en 1 id de almacen*/
		SET num_almacenes=1;

	/*corremos while anidado para el contador de almacenes*/
		WHILE num_almacenes<=tope_almacenes DO
			SET verif_almacen_detalle=0;
		/*vemos si el almacen existe y si sacamos su sucursal*/
			SELECT count(*) INTO verif_almacen FROM ec_almacen WHERE id_almacen=num_almacenes;
		/**/
			SELECT COUNT(md.id_movimiento_almacen_detalle) INTO verif_almacen_detalle
				FROM ec_movimiento_detalle md 
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				WHERE ma.id_tipo_movimiento=contador
				AND ma.status_agrupacion=1
				AND ma.id_almacen=num_almacenes;
	
			IF(verif_almacen=1 AND verif_almacen_detalle>0)/*si el almacen existe*/			
			THEN		
			/*extraemos datos del almacen*/
				SELECT id_almacen,id_sucursal INTO id_almacen_tmp,id_sucursal_tmp FROM ec_almacen WHERE id_almacen IN(num_almacenes);
			/*insertamos la cabecera del movimiento de almacen*/
				INSERT INTO ec_movimiento_almacen VALUES(null,contador,1,id_sucursal_tmp,
					IF(tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion)/*now()*/,now(),'AGRUPACION DE MOVIMIENTOS DE ALMACEN',-1,-1,'',-1,-1,
					id_almacen_tmp,tipo_agrupacion,-1,'0000-00-00 00:00:00',now());

				SELECT LAST_INSERT_ID() INTO movimiento_insertado;
				/*SET movimiento_insertado=251641;*/
			
			/*insertamos el detalle del movimiento de almacen*/
				INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, cantidad, cantidad_surtida, 
				id_pedido_detalle, id_oc_detalle, id_proveedor_producto, id_equivalente, sincronizar )
					SELECT
						null,
						movimiento_insertado,
						p.id_productos,
						SUM( IF( ma.id_movimiento_almacen IS NULL,0,md.cantidad ) ),
						SUM( IF( ma.id_movimiento_almacen IS NULL,0,md.cantidad ) ),
						-1,
						-1,
						NULL,/*md.id_proveedor_producto*/
						0,
						0
					FROM ec_productos p
					LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					WHERE ma.id_tipo_movimiento=contador
					AND ma.status_agrupacion=1
					AND ma.id_almacen=id_almacen_tmp
					GROUP BY md.id_producto;

				/*INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle, 
					id_proveedor_producto, cantidad, fecha_registro, id_sucursal, id_equivalente, status_agrupacion, id_tipo_movimiento, id_almacen,
					id_pedido_validacion, sincronizar )
				SELECT
					NULL,
					NULL,
					mdpp.id_proveedor_producto,
					SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 0, mdpp.cantidad ) ),
					CONCAT( fecha_agrupacion, ' 00:00:01' ),
					id_sucursal_tmp,
					0,
					tipo_agrupacion,
					mdpp.id_tipo_movimiento,
					id_almacen_tmp,
					-1,
					0
				FROM ec_movimiento_detalle_proveedor_producto mdpp
				WHERE mdpp.id_tipo_movimiento = contador
				AND mdpp.status_agrupacion = 1
				AND mdpp.id_almacen = id_almacen_tmp
				AND mdpp.id_proveedor_producto IS NOT NULL
				AND mdpp.fecha_registro LIKE CONCAT('%', fecha_agrupacion ,'%')
				GROUP BY mdpp.id_proveedor_producto;*/

			/*eliminamos los movimientos de almacen despues de haberlos agrupado*/
				IF(tipo_agrupacion=4)
				THEN
				/*QUE BORRE EN CASCADA*/
					/*DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento = contador
					AND fecha_registro <= CONCAT( fecha_agrupacion, ' 23:59:59' );AND id_equivalente!=0*/

					DELETE FROM ec_movimiento_almacen WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento=contador
					AND fecha <=fecha_agrupacion /*AND id_equivalente!=0*/;

				ELSE

					/*DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento = contador
					AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion, '%' );AND id_equivalente!=0*/

					DELETE FROM ec_movimiento_almacen WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento=contador
					AND fecha LIKE CONCAT('%',fecha_agrupacion,'%') /*AND id_equivalente!=0*/;

				END IF;
			END IF;
			
			SET num_almacenes=num_almacenes+1;
		
		END WHILE;
	/*aumentamos 1 al contador*/
		SET contador=contador+1;
	END WHILE;
	IF(tipo_agrupacion=3)
	THEN
		INSERT INTO sys_prueba_mantenimiento VALUES(null,tipo_agrupacion,
				(SELECT COUNT(*) FROM ec_movimiento_almacen WHERE id_movimiento_almacen!=-1 /*AND id_equivalente!=0 */AND fecha=fecha_agrupacion_auxiliar),
				(SELECT max(fecha) FROM ec_movimiento_almacen WHERE fecha like CONCAT('%',fecha_agrupacion_auxiliar,'%')),now());
	ELSE
		INSERT INTO sys_prueba_mantenimiento VALUES(null,tipo_agrupacion,
				(SELECT COUNT(*) FROM ec_movimiento_almacen WHERE id_movimiento_almacen!=-1 /*AND id_equivalente!=0 */AND fecha=fecha_agrupacion),
				(SELECT max(fecha) FROM ec_movimiento_almacen WHERE fecha like CONCAT('%',fecha_agrupacion,'%')),now());
	END IF;
COMMIT;
END $$