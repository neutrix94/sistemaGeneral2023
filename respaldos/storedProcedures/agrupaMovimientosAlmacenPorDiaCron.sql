DROP PROCEDURE IF EXISTS agrupaMovimientosAlmacenPorDiaCron|
DELIMITER $$
CREATE PROCEDURE agrupaMovimientosAlmacenPorDiaCron(IN tipo_agrupacion INTEGER(1),IN fecha_agrupacion VARCHAR(10))
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

START TRANSACTION;
	SELECT COUNT(*) INTO tope FROM ec_tipos_movimiento;
	SELECT MAX(id_almacen) INTO tope_almacenes FROM ec_almacen WHERE id_almacen>0;
	SET contador=1;

/*corremos while de tipos de movimiento*/
	WHILE contador<=tope DO
	/*Ponemos las cabeceras en status de agrupacion "agrupando"*/
		UPDATE ec_movimiento_almacen 
			SET status_agrupacion=1, sincronizar = 0
		WHERE id_tipo_movimiento=contador 
		AND id_movimiento_almacen!=-1 /*AND id_equivalente!=0*/
		AND status_agrupacion=-1 
		AND folio_unico IS NOT NULL
		AND fecha LIKE CONCAT('%',fecha_agrupacion,'%');

		SET num_almacenes=1;/*declaramos en 1 id de almacen*/

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
				AND ma.id_almacen=num_almacenes
				AND ma.folio_unico IS NOT NULL;
	
			IF(verif_almacen=1 AND verif_almacen_detalle>0 AND contador != 1 )/*si el almacen existe*/			
			THEN		
			/*extraemos datos del almacen*/
				SELECT id_almacen,id_sucursal INTO id_almacen_tmp,id_sucursal_tmp FROM ec_almacen WHERE id_almacen IN(num_almacenes);
			/*insertamos la cabecera del movimiento de almacen*/
				INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, 
				id_almacen, status_agrupacion, id_equivalente, ultima_sincronizacion, ultima_actualizacion, folio_unico )
				VALUES( NULL, contador, 1, id_sucursal_tmp,
					IF(tipo_agrupacion=3,fecha_agrupacion_auxiliar,fecha_agrupacion)/*now()*/,now(),'AGRUPACION DE MOVIMIENTOS DE ALMACEN',-1,-1,'',-1,-1,
					id_almacen_tmp,tipo_agrupacion, -1, '0000-00-00 00:00:00', now(), NULL );

				SELECT LAST_INSERT_ID() INTO movimiento_insertado;
				/*SET movimiento_insertado=251641;*/
			
			/*insertamos el detalle del movimiento de almacen*/
				INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, cantidad, cantidad_surtida, 
				id_pedido_detalle, id_oc_detalle, id_proveedor_producto, id_equivalente, sincronizar, folio_unico )
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
					0,
					NULL
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				WHERE ma.id_tipo_movimiento=contador
				AND ma.status_agrupacion=1
				AND ma.id_almacen=id_almacen_tmp
				AND ma.folio_unico IS NOT NULL
				GROUP BY md.id_producto;
/*aqui insertar instruccion para eliminar movimientos de almacen y su detalle por folio unico Oscar 2023-01-11*/
					INSERT INTO sys_sincronizacion_registros_movimientos_almacen( id_sincronizacion_registro, sucursal_de_cambio, id_sucursal_destino, datos_json, fecha, tipo,
						folio_unico_peticion, status_sincronizacion )
					SELECT
						NULL, 
						-1,
						ma.id_sucursal,
						CONCAT('{',
			                '"action_type" : "sql_instruction",',
			                '"sql_instruction" : "DELETE FROM ec_movimiento_almacen WHERE folio_unico IN( ', GROUP_CONCAT( CONCAT( '\'', ma.folio_unico, '\'' ) SEPARATOR ',' ), ' )"',
		                '}'
						),
						NOW(),
						'agrupaMovimientosProveedorProductoCRON',
						1
					FROM ec_movimiento_almacen ma
					WHERE ma.id_almacen = id_almacen_tmp 
					AND ma.status_agrupacion = 1 
					AND ma.id_tipo_movimiento = contador_tipos_movimiento
					AND ma.fecha LIKE CONCAT( '%', fecha_agrupacion, '%' )
					AND ma.folio_unico IS NOT NULL;

				DELETE FROM ec_movimiento_almacen WHERE id_almacen=id_almacen_tmp AND status_agrupacion=1 AND id_tipo_movimiento=contador
				AND fecha LIKE CONCAT('%',fecha_agrupacion,'%') AND ma.folio_unico IS NOT NULL;

			END IF;
			
			SET num_almacenes = num_almacenes+1;
		
		END WHILE;
	/*aumentamos 1 al contador*/
		SET contador=contador+1;
	END WHILE;
	INSERT INTO sys_prueba_mantenimiento ( id_mantenimiento, tipo, mov_por_agrupar, fecha, fecha_alta ) VALUES(null,tipo_agrupacion,
		(SELECT COUNT(*) FROM ec_movimiento_almacen WHERE id_movimiento_almacen!=-1 /*AND id_equivalente!=0 */AND fecha=fecha_agrupacion),
		(SELECT max(fecha) FROM ec_movimiento_almacen WHERE fecha like CONCAT('%',fecha_agrupacion,'%')),now());
COMMIT;
END $$