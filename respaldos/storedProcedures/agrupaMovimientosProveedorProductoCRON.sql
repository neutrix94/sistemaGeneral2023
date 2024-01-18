DROP PROCEDURE IF EXISTS agrupaMovimientosProveedorProductoCRON|
DELIMITER $$
CREATE PROCEDURE agrupaMovimientosProveedorProductoCRON(IN tipo_agrupacion INTEGER(1),IN fecha_agrupacion VARCHAR(10))
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

/*SELECT fecha_agrupacion;*/
START TRANSACTION;

	SELECT COUNT(*) INTO tope_tipos_movimiento FROM ec_tipos_movimiento;/*extraemos el numero de tipos de movimiento*/
	SELECT MAX(id_almacen) INTO tope_almacenes FROM ec_almacen WHERE id_almacen > 0;/*Consulta el id maximo de almacenes*/

	SET contador_tipos_movimiento = 1;/*inicializa el contador en ceros*/

/*corremos while de tipos de movimiento*/
	WHILE contador_tipos_movimiento <= tope_tipos_movimiento DO

		UPDATE ec_movimiento_detalle_proveedor_producto 
			SET status_agrupacion=1 
		WHERE id_tipo_movimiento = contador_tipos_movimiento
		AND id_movimiento_detalle_proveedor_producto !=-1
		AND status_agrupacion=-1 
		AND fecha_registro LIKE CONCAT('%',fecha_agrupacion,'%')
		AND id_proveedor_producto IS NOT NULL
		AND folio_unico IS NOT NULL;

		SET num_almacenes=1;/*Declara en 1 id de almacen*/

	/*Recorre while anidado para el contador de almacenes*/
		WHILE num_almacenes<=tope_almacenes DO
			SET verif_almacen_detalle=0;
		
			SELECT count(*) INTO verif_almacen FROM ec_almacen WHERE id_almacen=num_almacenes;/*Verifica si el almacen existe y si sacamos su sucursal*/
		/**/
			SELECT 
				COUNT(mdpp.id_movimiento_detalle_proveedor_producto ) INTO verif_almacen_detalle
			FROM ec_movimiento_detalle_proveedor_producto mdpp 
			WHERE mdpp.id_tipo_movimiento = contador_tipos_movimiento
			AND mdpp.status_agrupacion=1
			AND mdpp.id_almacen=num_almacenes
			AND mdpp.id_proveedor_producto IS NOT NULL
			AND mdpp.fecha_registro LIKE CONCAT('%', fecha_agrupacion,'%')
			AND mdpp.folio_unico IS NOT NULL;

			SELECT id_almacen,id_sucursal INTO id_almacen_tmp,id_sucursal_tmp FROM ec_almacen WHERE id_almacen IN(num_almacenes);/*Consulta datos del almacen*/

			INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle, 
				id_proveedor_producto, cantidad, fecha_registro, id_sucursal, status_agrupacion, id_tipo_movimiento, id_almacen,
				id_pedido_validacion, sincronizar, folio_unico )
			SELECT
				NULL,
				NULL,
				mdpp.id_proveedor_producto,
				SUM( IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL, 0, mdpp.cantidad ) ),
				CONCAT( fecha_agrupacion, ' 00:00:01' ),
				id_sucursal_tmp,
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

		/*Inserta instruccion para eliminar movimientos de almacen y su detalle por folio unico Oscar 2023-01-11*/
			INSERT INTO sys_sincronizacion_registros_movimientos_almacen( id_sincronizacion_registro, sucursal_de_cambio, id_sucursal_destino, datos_json, fecha, tipo,
				status_sincronizacion )
			SELECT
				NULL, 
				-1,
				mdpp.id_sucursal,
				CONCAT('{',
	                '"action_type" : "sql_instruction",',
	                '"sql_instruction" : "DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE folio_unico IN( ', GROUP_CONCAT( CONCAT( '\'', mdpp.folio_unico, '\'' ) SEPARATOR ',' ), ' )"',
	            '}'
				),
				NOW(),
				'agrupaMovimientosProveedorProductoCRON',
				1
			FROM ec_movimiento_detalle_proveedor_producto mdpp
			WHERE mdpp.id_tipo_movimiento = contador_tipos_movimiento
			AND mdpp.status_agrupacion=1 
			AND mdpp.id_almacen=id_almacen_tmp		
			AND mdpp.id_proveedor_producto IS NOT NULL  
			AND mdpp.fecha_registro LIKE CONCAT( '%', fecha_agrupacion, '%' )
			AND mdpp.folio_unico IS NOT NULL;

			DELETE FROM ec_movimiento_detalle_proveedor_producto 
			WHERE id_tipo_movimiento = contador_tipos_movimiento
			AND status_agrupacion=1 
			AND id_almacen=id_almacen_tmp				
			AND id_proveedor_producto IS NOT NULL 
			AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion, '%' ) 
			AND folio_unico IS NOT NULL;
			
			SET num_almacenes=num_almacenes+1;
		
		END WHILE;
	/*aumentamos 1 al contador*/
		SET contador_tipos_movimiento = contador_tipos_movimiento+1;
	END WHILE;

	INSERT INTO sys_prueba_mantenimiento VALUES(null,( 10+tipo_agrupacion ),
		(SELECT COUNT(*) FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_detalle_proveedor_producto != -1 /*AND id_equivalente!=0 */AND fecha_registro LIKE CONCAT( '%', fecha_agrupacion, '%' ) ),
		(SELECT max(fecha_registro) FROM ec_movimiento_detalle_proveedor_producto WHERE fecha_registro like CONCAT('%',fecha_agrupacion,'%')),now());

COMMIT;
END $$