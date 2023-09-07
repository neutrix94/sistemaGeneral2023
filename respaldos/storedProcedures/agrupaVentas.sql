DROP PROCEDURE IF EXISTS agrupaVentas|
DELIMITER $$
CREATE PROCEDURE agrupaVentas(IN id_tipo_agrupacion INTEGER(11),IN fecha_agrupacion VARCHAR(10))
BEGIN
	DECLARE cont_sucursales INTEGER(11);/*contador de sucursales*/
	DECLARE tope_sucursales INTEGER(11);/*tope de sucursales*/
	DECLARE verifica_sucursal INTEGER(11);/*tope de sucursales*/
	DECLARE id_cabecera_pedido INTEGER(11);/*auxiliar para id de cabecera de nueva venta agrupada*/
	DECLARE id_cabecera_devolucion_interna INTEGER(11);/*auxiliar para id de cabecera de nueva devolucion interna agrupada*/
	DECLARE id_cabecera_devolucion_externa INTEGER(11);/*auxiliar para id de cabecera de nueva devolucion externa agrupada*/
	DECLARE verif_ventas INTEGER(11);/*variable para verificar que haya ventas*/
	DECLARE verif_dev_int INTEGER(11);/*variable para verficar que haya devoluciones internas*/
	DECLARE verif_dev_ext INTEGER(11);/*variable para verficar que haya devoluciones externas*/
	DECLARE contador_detalles_dev_int INTEGER(11);
	DECLARE contador_detalles_dev_ext INTEGER(11);
	DECLARE contador_pagos_dev_int INTEGER(11);
	DECLARE contador_pagos_dev_ext INTEGER(11);
	DECLARE fecha_agrupacion_auxiliar VARCHAR(10);

START TRANSACTION;
	IF(id_tipo_agrupacion=3)/*por ano*/
	THEN
		SELECT DATE_FORMAT(max(fecha_alta),'%Y-%m-%d') INTO fecha_agrupacion_auxiliar FROM ec_pedidos WHERE fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%');
	END IF;

	IF(id_tipo_agrupacion=4)/*por todos los anteriores*/
	THEN
		SELECT date_add(CURRENT_DATE(), INTERVAL (fecha_agrupacion*-1) DAY) INTO fecha_agrupacion;
	END IF;

	SELECT MAX(id_sucursal) INTO tope_sucursales FROM sys_sucursales WHERE id_sucursal>0;
	SET cont_sucursales=1;

/*recorremos con while*/
	WHILE cont_sucursales<=tope_sucursales DO
		
		IF(id_tipo_agrupacion=2)/*por día*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_pedidos SET id_status_agrupacion=1 WHERE id_sucursal=cont_sucursales AND id_pedido!=-1 /*AND id_equivalente!=0*/
			AND id_status_agrupacion=-1 AND pagado=1 AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%');
		END IF;

		IF(id_tipo_agrupacion=3)/*por ano*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_pedidos SET id_status_agrupacion=1 WHERE id_sucursal=cont_sucursales AND id_pedido!=-1 /*AND id_equivalente!=0*/
			AND id_status_agrupacion=2 AND pagado=1 AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%');
		END IF;

		IF(id_tipo_agrupacion=4)/*por historico*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_pedidos SET id_status_agrupacion=1 WHERE id_sucursal=cont_sucursales AND id_pedido!=-1 /*AND id_equivalente!=0*/
			AND id_status_agrupacion IN(3,4) AND pagado=1 AND fecha_alta<=CONCAT(fecha_agrupacion,' 23:59:59');
		END IF;	
	/*reseteamos variables de ids nuevos*/
		SET id_cabecera_pedido=0,id_cabecera_devolucion_interna=0,id_cabecera_devolucion_externa=0,verif_dev_int=0,verif_dev_ext=0,contador_detalles_dev_int=0,
		contador_detalles_dev_ext=0,contador_pagos_dev_int=0,contador_pagos_dev_ext=0;
	/*verificamos que la sucursal exista*/
		SELECT COUNT(id_sucursal) into verifica_sucursal FROM sys_sucursales WHERE id_sucursal=cont_sucursales;
	/*verificamos que haya ventas para agrupar*/
		SELECT COUNT(id_pedido) INTO verif_ventas 
		FROM ec_pedidos 
		WHERE id_sucursal=cont_sucursales 
		AND id_status_agrupacion=1;

	/*comenzamos el proceso de agrupacion*/
		IF(verifica_sucursal=1 AND verif_ventas>0)
		THEN
		/*ponemos en status de agrupacion temporal las ventas que pertenencen al día y sucursal*
			UPDATE ec_pedidos SET id_status_agrupacion=1 WHERE fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%') AND id_sucursal=cont_sucursales AND id_equivalente!=0;
*/
			INSERT INTO ec_pedidos 
				SELECT
				/*1*/null,
				/*2*/'agrupacion',
				/*3*/'agrupacion',
				/*4*/'agrupacion',
				/*5*/'agrupacion',
				/*6*/1,
				/*7*/2,
				/*8*/1,
				/*9*/IF(id_tipo_agrupacion=3,CONCAT(fecha_agrupacion_auxiliar,' ',current_time),CONCAT(fecha_agrupacion,' ',current_time)),
				/*10*/null,
				/*11*/-1,
				/*12*/null,
				/*13*/-1,
				/*14*/SUM(subtotal),
				/*15*/0,
				/*16*/0,
				/*17*/SUM(total),
				/*18*/null,
				/*19*/1,
				/*20*/0,
				/*21*/0,
				/*22*/cont_sucursales,
				/*23*/1,
				/*24*/0,
				/*25*/0,
				/*26*/1,
				/*27*/SUM(descuento),
				/*28*/null,
				/*29*/null,
				/*30*/'-',
				/*31*/'-',
				/*32*/-1,
				/*33*/0,
				/*34*/'0000-00-00 00:00:00',
				/*35*/NOW(),
				/*36*/0,
				/*37*/id_tipo_agrupacion,
				/*38*/id_cajero,
				/*39*/id_devoluciones,
				/*40*/'1'
				FROM ec_pedidos
				WHERE id_sucursal=cont_sucursales
				/*AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
				AND id_status_agrupacion=1
/*				AND id_equivalente!=0*/
				GROUP BY id_sucursal;
		/*obtenemos el id insertado en la cabecera de pedido*/
			SELECT LAST_INSERT_ID() INTO id_cabecera_pedido;
		/*agrupamos el detalle*/
			INSERT INTO ec_pedidos_detalle
				SELECT
					/*1*/null,
					/*2*/id_cabecera_pedido,
					/*3*/pd.id_producto,
					/*4*/SUM(pd.cantidad),
					/*5*/pd.precio,
					/*6*/SUM(pd.monto),
					/*7*/0,
					/*8*/0,
					/*9*/0,
					/*10*/SUM(pd.descuento),
					/*11*/0,
					/*12*/pd.es_externo,
					/*13*/pd.id_precio,
					/*14*/0
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_pedidos ped ON pd.id_pedido=ped.id_pedido
				WHERE ped.id_status_agrupacion=1
				AND ped.id_sucursal=cont_sucursales
				/*AND ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
				GROUP BY pd.id_producto,pd.es_externo;

		/*agrupamos los pagos*/
			INSERT INTO ec_pedido_pagos
				SELECT 
					/*1*/NULL,
					/*2*/-1,
					/*3*/id_cabecera_pedido,
					/*4*/1,
					/*5*/IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),
					/*6*/now(),
					/*7*/SUM(pp.monto),
					/*8*/'',
					/*9*/1,
					/*10*/1,
					/*11*/-1,
					/*12*/-1,
					/*13*/0,
					/*14*/pp.es_externo,
					/*15*/pp.id_cajero
				FROM ec_pedido_pagos pp
				LEFT JOIN ec_pedidos ped ON pp.id_pedido=ped.id_pedido
				WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
				AND */ped.id_sucursal=cont_sucursales
				AND ped.id_status_agrupacion=1
				GROUP BY ped.id_sucursal,pp.es_externo;

		/*verificamos si hay devolucones externas*/
					/*verificamos si hay devolucones internas*/
			SELECT COUNT(dev.id_devolucion) INTO verif_dev_ext
			FROM /*ec_devolucion_pagos dp
			LEFT JOIN */ec_devolucion dev /*ON dp.id_devolucion=dev.id_devolucion*/
			LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
			WHERE dev.es_externo=1
			AND ped.id_sucursal=cont_sucursales
			/*AND ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
			AND ped.id_status_agrupacion=1;


			IF(verif_dev_ext>0)
			THEN
			/*agrupamos las devoluciones internas*/
				INSERT INTO ec_devolucion 
					SELECT
						null,/*1*/
						-1,/*2*/
						1,/*3*/
						cont_sucursales,/*4*/
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),/*5*/
						now(),/*6*/
						id_cabecera_pedido,/*7*/
						'AGRUP',/*8*/
						dev.es_externo,/*9*/
						dev.status,/*10*/
						'AGRUPACION',/*11*/
						dev.tipo_sistema,/*12*/
						id_tipo_agrupacion/*13*/
					FROM ec_devolucion dev
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=1
					GROUP BY dev.id_sucursal;

			/*obtenemos el id insertado en la cabecera de la devolucion*/
				SELECT LAST_INSERT_ID() INTO id_cabecera_devolucion_externa;

		/*verificamos si hay detalles de devolucion internos*/
			SELECT count(dd.id_devolucion_detalle) INTO contador_detalles_dev_ext
				FROM ec_devolucion_detalle dd
					LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=1;

			IF(contador_detalles_dev_ext>0)
			THEN
			/*agrupamos detalle de las devoluciones internas*/
				INSERT INTO ec_devolucion_detalle ( id_devolucion_detalle, id_devolucion, id_pedido_detalle, id_producto, 
					id_proveedor_producto, cantidad )
					SELECT 
						null,
						id_cabecera_devolucion_externa,
						0,
						dd.id_producto,
						0,
						SUM(dd.cantidad)
					FROM ec_devolucion_detalle dd
					LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=1
					GROUP BY dd.id_producto;
			END IF;

		/*verificamos si hay pagos de devolucion externos*/
			SELECT COUNT(dp.id_devolucion_pago) INTO contador_pagos_dev_ext
			FROM ec_devolucion_pagos dp
					LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dp.es_externo=1;
			IF(contador_pagos_dev_ext>0)
			THEN
			/*agrupammos  pago de devoluciones externas*/
				INSERT INTO ec_devolucion_pagos
					SELECT 
						null,
						id_cabecera_devolucion_externa,
						1,
						SUM(dp.monto),
						'',
						dp.es_externo,
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),
						now(),
						dp.id_cajero
					FROM ec_devolucion_pagos dp
					LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dp.es_externo=1;
			END  IF;
			
		END IF;/*fin de si hay devoluciones internas*/
/************************Devoluciones internas*************************/

		/*verificamos si hay devolucones internas*/
			SELECT COUNT(dev.id_devolucion) INTO verif_dev_int 
			FROM /*ec_devolucion_pagos dp
			LEFT JOIN */ec_devolucion dev /*ON dp.id_devolucion=dev.id_devolucion*/
			LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
			WHERE dev.es_externo=0
			AND ped.id_sucursal=cont_sucursales
			/*AND ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
			AND ped.id_status_agrupacion=1;

			IF(verif_dev_int>0)
			THEN
			/*agrupamos las devoluciones internas*/
				INSERT INTO ec_devolucion 
					SELECT
						null,/*1*/
						-1,/*2*/
						1,/*3*/
						cont_sucursales,/*4*/
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),/*5*/
						now(),/*6*/
						id_cabecera_pedido,/*7*/
						'AGRUP',/*8*/
						dev.es_externo,/*9*/
						dev.status,/*10*/
						'AGRUPACION',/*11*/
						dev.tipo_sistema,/*12*/
						id_tipo_agrupacion/*13*/
					FROM ec_devolucion dev
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=0
					GROUP BY dev.id_sucursal;

			/*obtenemos el id insertado en la cabecera de la devolucion*/
				SELECT LAST_INSERT_ID() INTO id_cabecera_devolucion_interna;

		/*verificamos si hay detalles de devolucion internos*/
			SELECT count(dd.id_devolucion_detalle) INTO contador_detalles_dev_int
				FROM ec_devolucion_detalle dd
					LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=0;

			IF(contador_detalles_dev_int>0)
			THEN
			/*agrupamos detalle de las devoluciones internas*/
				INSERT INTO ec_devolucion_detalle ( id_devolucion_detalle, id_devolucion, id_pedido_detalle, id_producto, 
					id_proveedor_producto, cantidad )
					SELECT 
						null,
						id_cabecera_devolucion_interna,
						0,
						dd.id_producto,
						0,
						SUM(dd.cantidad)
					FROM ec_devolucion_detalle dd
					LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dev.es_externo=0
					GROUP BY dd.id_producto;
			END IF;


		/*verificamos si hay pagos de devolucion externos*/
			SELECT COUNT(dp.id_devolucion_pago) INTO contador_pagos_dev_int
			FROM ec_devolucion_pagos dp
					LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dp.es_externo=0;
			IF(contador_pagos_dev_int>0)
			THEN
			/*agrupammos  pago de devoluciones externas*/
				INSERT INTO ec_devolucion_pagos
					SELECT 
						null,
						id_cabecera_devolucion_interna,
						1,
						SUM(dp.monto),
						'',
						dp.es_externo,
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),
						now(),
						dp.id_cajero
					FROM ec_devolucion_pagos dp
					LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
					LEFT JOIN ec_pedidos ped ON dev.id_pedido=ped.id_pedido
					WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
					AND */ped.id_sucursal=cont_sucursales
					AND ped.id_status_agrupacion=1
					AND dp.es_externo=0;
			END IF;
		END IF;/*fin de si hay devoluciones internas*/

			IF(id_tipo_agrupacion=4)
			THEN
			/*eliminamos las devoluciones que pertenecen a las ventas agrupadas*/
				DELETE dev.*
				FROM ec_devolucion dev
				LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
				WHERE /*ped.fecha_alta<=CONCAT(fecha_agrupacion,' 23:59:59')
				AND */ped.id_sucursal=cont_sucursales
				AND ped.id_status_agrupacion=1;
			/*eliminamos las ventas agrupadas*/
				DELETE FROM ec_pedidos 
				WHERE /*fecha_alta<=CONCAT(fecha_agrupacion,' 23:59:59')
				AND */id_sucursal=cont_sucursales
				AND id_status_agrupacion=1;
			ELSE
			/*eliminamos las devoluciones que pertenecen a las ventas agrupadas*/
				DELETE dev.*
				FROM ec_devolucion dev
				LEFT JOIN ec_pedidos ped ON ped.id_pedido=dev.id_pedido
				WHERE /*ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
				AND */ped.id_sucursal=cont_sucursales
				AND ped.id_status_agrupacion=1;
			/*eliminamos las ventas agrupadas*/
				DELETE FROM ec_pedidos 
				WHERE /*fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
				AND */id_sucursal=cont_sucursales
				AND id_status_agrupacion=1;

			END IF;

		END IF;
		SET cont_sucursales=cont_sucursales+1;
	END WHILE;
COMMIT;
END $$