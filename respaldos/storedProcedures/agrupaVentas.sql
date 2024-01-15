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
			UPDATE ec_pedidos SET id_status_agrupacion=1 
			WHERE id_sucursal=cont_sucursales 
			AND id_pedido != -1 
			AND id_status_agrupacion=-1 
			AND pagado=1 
			AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
			AND folio_unico IS NOT NULL;
		END IF;

		IF(id_tipo_agrupacion=3)/*por ano*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_pedidos SET id_status_agrupacion=1 
			WHERE id_sucursal=cont_sucursales 
			AND id_pedido!=-1 /*AND id_equivalente!=0*/
			AND id_status_agrupacion=2 
			AND pagado=1 
			AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')
			AND folio_unico IS NOT NULL;
		END IF;

		IF(id_tipo_agrupacion=4)/*por historico*/
		THEN
		/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
			UPDATE ec_pedidos SET id_status_agrupacion=1 
			WHERE id_sucursal=cont_sucursales 
			AND id_pedido!=-1 /*AND id_equivalente!=0*/
			AND id_status_agrupacion IN(3,4) 
			AND pagado=1 
			AND fecha_alta<=CONCAT(fecha_agrupacion,' 23:59:59')
			AND folio_unico IS NOT NULL;
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
			INSERT INTO ec_pedidos ( /*1*/id_pedido, /*2*/folio_pedido, /*3*/folio_nv, /*4*/folio_factura, /*5*/folio_cotizacion, /*6*/id_cliente, /*7*/id_estatus,
			/*8*/id_moneda, /*9*/fecha_alta, /*10*/fecha_factura, /*11*/id_direccion, /*12*/direccion, /*13*/id_razon_social, /*14*/subtotal, /*15*/iva, /*16*/ieps, 
			/*17*/total, /*18*/dias_proximo, /*19*/pagado, /*20*/surtido, /*21*/enviado, /*22*/id_sucursal, /*23*/id_usuario, /*24*/fue_cot, /*25*/facturado, 
			/*26*/id_tipo_envio, /*27*/descuento, /*28*/id_razon_factura, /*29*/folio_abono, /*30*/correo, /*31*/facebook, /*32*/modificado, /*33*/ultima_sincronizacion, 
			/*34*/ultima_actualizacion, /*35*/tipo_pedido, /*36*/id_status_agrupacion, /*37*/id_cajero, /*38*/id_devoluciones, /*39*/venta_validada, /*40*/folio_unico,
			/*41*/id_sesion_caja, /*42*/tipo_sistema, /*43*/monto_pago_inicial, /*44*/monto_venta_mas_ultima_devolucion )
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
				/*32*/0,
				/*33*/'0000-00-00 00:00:00',
				/*34*/NOW(),
				/*35*/0,
				/*36*/id_tipo_agrupacion,
				/*37*/id_cajero,
				/*38*/id_devoluciones,
				/*39*/'1',
				/*40*/'Agrupacion',
				/*41*/-1,
				/*42*/-1,
				/*43*/0,
				/*44*/0
				FROM ec_pedidos
				WHERE id_sucursal=cont_sucursales
				/*AND fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
				AND id_status_agrupacion=1
				AND folio_unico IS NOT NULL
/*				AND id_equivalente!=0*/
				GROUP BY id_sucursal;
		/*obtenemos el id insertado en la cabecera de pedido*/
			SELECT LAST_INSERT_ID() INTO id_cabecera_pedido;
		/*agrupamos el detalle*/
			INSERT INTO ec_pedidos_detalle ( /*1*/id_pedido_detalle, /*2*/id_pedido, /*3*/id_producto, /*4*/cantidad, /*5*/precio,
				/*6*/monto, /*7*/iva, /*8*/ieps, /*9*/cantidad_surtida, /*10*/descuento, /*11*/modificado, /*12*/es_externo, /*13*/id_precio, /*14*/folio_unico )
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
					/*14*/'AGRUPACION'
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_pedidos ped ON pd.id_pedido=ped.id_pedido
				WHERE ped.id_status_agrupacion=1
				AND ped.id_sucursal=cont_sucursales
				/*AND ped.fecha_alta LIKE CONCAT('%',fecha_agrupacion,'%')*/
				GROUP BY pd.id_producto,pd.es_externo;

		/*agrupamos los pagos*/
			INSERT INTO ec_pedido_pagos ( /*1*/id_pedido_pago, /*2*/id_pedido, /*3*/id_cajero_cobro, /*4*/id_tipo_pago, /*5*/fecha, /*6*/hora, /*7*/monto, /*8*/referencia, 
				/*9*/id_moneda, /*10*/tipo_cambio, /*11*/id_nota_credito, /*12*/id_cxc, /*13*/exportado, /*14*/es_externo, /*15*/id_cajero, /*16*/folio_unico,
				/*17*/sincronizar, /*18*/id_sesion_caja )
				SELECT 
					/*1*/NULL,
					/*2*/id_cabecera_pedido,
					/*3*/-1,
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
					/*15*/-1,
					/*16*/'AGRUPACION',
					/*17*/1,
					/*18*/-1
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
				INSERT INTO ec_devolucion ( /*1*/id_devolucion, /*2*/id_usuario, /*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/id_pedido, /*7*/folio, 
					/*8*/monto_devolucion, /*9*/es_externo, /*10*/id_cajero, /*11*/sesion_caja, /*12*/status, /*13*/observaciones, /*14*/tipo_sistema, 
					/*15*/id_status_agrupacion, /*16*/folio_unico, /*17*/sincronizar )
					SELECT
						null,/*1*/
						-1,/*2*/
						cont_sucursales,/*3*/
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),/*4*/
						now(),/*5*/
						id_cabecera_pedido,/*6*/
						'AGRUP',/*7*/
						SUM( dev.monto_devolucion ),/*8*/
						dev.es_externo,/*9*/
						-1,/*10*/
						-1,/*11*/
						dev.status,/*12*/
						'AGRUPACION',/*13*/
						dev.tipo_sistema,/*14*/
						id_tipo_agrupacion,/*15*/
						'AGRUPACION',/*16*/
						1/*17*/
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
				INSERT INTO ec_devolucion_detalle ( /*1*/id_devolucion_detalle, /*2*/id_devolucion, /*3*/id_pedido_detalle, /*4*/id_producto, 
					/*5*/id_proveedor_producto, /*6*/cantidad, /*7*/folio_unico )
					SELECT 
						/*1*/null,
						/*2*/id_cabecera_devolucion_externa,
						/*3*/0,
						/*4*/dd.id_producto,
						/*5*/0,
						/*6*/SUM(dd.cantidad),
						/*7*/'AGRUPACION'
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
			LEFT JOIN ec_devolucion dev 
			ON dp.id_devolucion = dev.id_devolucion
			LEFT JOIN ec_pedidos ped 
			ON dev.id_pedido = ped.id_pedido
			WHERE ped.id_sucursal = cont_sucursales
			AND ped.id_status_agrupacion = 1
			AND dp.es_externo = 1;
			IF(contador_pagos_dev_ext>0)
			THEN
			/*agrupammos  pago de devoluciones externas*/
				INSERT INTO ec_devolucion_pagos ( /*1*/id_devolucion_pago, /*2*/id_devolucion, /*3*/id_cajero_cobro, /*4*/id_tipo_pago, /*5*/monto, 
					/*6*/referencia, /*7*/es_externo, /*8*/fecha, /*9*/hora, /*10*/id_cajero, /*11*/folio_unico, /*12*/sincronizar, /*13*/id_sesion_caja )
					SELECT 
						/*1*/null,
						/*2*/id_cabecera_devolucion_externa,
						/*3*/-1,
						/*4*/1,
						/*5*/SUM(dp.monto),
						/*6*/'',
						/*7*/dp.es_externo,
						/*8*/IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),
						/*9*/now(),
						/*10*/dp.id_cajero,
						/*11*/'AGRUPACION',
						/*12*/1,
						/*13*/-1
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
				INSERT INTO ec_devolucion ( /*1*/id_devolucion, /*2*/id_usuario, /*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/id_pedido, /*7*/folio, 
					/*8*/monto_devolucion, /*9*/es_externo, /*10*/id_cajero, /*11*/sesion_caja, /*12*/status, /*13*/observaciones, /*14*/tipo_sistema, 
					/*15*/id_status_agrupacion, /*16*/folio_unico, /*17*/sincronizar )
					SELECT
						null,/*1*/
						-1,/*2*/
						cont_sucursales,/*3*/
						IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),/*4*/
						now(),/*5*/
						id_cabecera_pedido,/*6*/
						'AGRUP',/*7*/
						SUM( dev.monto_devolucion ),/*8*/
						dev.es_externo,/*9*/
						-1,/*10*/
						-1,/*11*/
						dev.status,/*12*/
						'AGRUPACION',/*13*/
						dev.tipo_sistema,/*14*/
						id_tipo_agrupacion,/*15*/
						'AGRUPACION',/*16*/
						1/*17*/
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
					id_proveedor_producto, cantidad, folio_unico )
					SELECT 
						null,
						id_cabecera_devolucion_interna,
						0,
						dd.id_producto,
						0,
						SUM(dd.cantidad),
						'AGRUPACION'
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
				INSERT INTO ec_devolucion_pagos ( /*1*/id_devolucion_pago, /*2*/id_devolucion, /*3*/id_cajero_cobro, /*4*/id_tipo_pago, /*5*/monto, 
					/*6*/referencia, /*7*/es_externo, /*8*/fecha, /*9*/hora, /*10*/id_cajero, /*11*/folio_unico, /*12*/sincronizar, /*13*/id_sesion_caja )
					SELECT 
						/*1*/null,
						/*2*/id_cabecera_devolucion_interna,
						/*3*/-1,
						/*4*/1,
						/*5*/SUM(dp.monto),
						/*6*/'',
						/*7*/dp.es_externo,
						/*8*/IF(id_tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion),
						/*9*/now(),
						/*10*/dp.id_cajero,
						/*11*/'AGRUPACION',
						/*12*/1,
						/*13*/-1
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