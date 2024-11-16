DROP TRIGGER IF EXISTS actualizaMovimientoAlmacen|
DELIMITER $$
CREATE TRIGGER actualizaMovimientoAlmacen
BEFORE UPDATE ON ec_movimiento_almacen
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
    DECLARE store_id INT(11);

    IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND new.sincronizar = 1 )
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

		INSERT INTO sys_sincronizacion_registros_movimientos_almacen ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_movimiento_almacen",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_tipo_movimiento" : "', new.id_tipo_movimiento, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"fecha" : "', new.fecha, '",',
				'"hora" : "', new.hora, '",',
				'"observaciones" : "', new.observaciones, '",',
				'"id_maquila" : "', new.id_maquila, '",',
				'"id_transferencia" : ',
				IF( new.id_transferencia = -1,
					'"-1", ',
					CONCAT( '"( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'', 
							( SELECT
								folio_unico
							FROM ec_transferencias
							WHERE id_transferencia = new.id_transferencia
							LIMIT 1 ), 
						'\' LIMIT 1 )",' 
					)
				),	
				'"id_almacen" : "', new.id_almacen, '",',
				'"status_agrupacion" : "', new.status_agrupacion, '",',
				'"id_equivalente" : "', new.id_equivalente, '",',
				'"insertado_por_sincronizacion" : "', new.insertado_por_sincronizacion, '",',
				'"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaMovimientoAlmacen',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
		IF( new.id_tipo_movimiento != old.id_tipo_movimiento )
		THEN
			UPDATE ec_movimiento_detalle_proveedor_producto
			SET id_tipo_movimiento = new.id_tipo_movimiento
			WHERE id_movimiento_almacen_detalle IN( 
				SELECT id_movimiento_almacen_detalle 
				FROM ec_movimiento_detalle 
				WHERE id_movimiento = new.id_movimiento_almacen 
			);  
		END IF;
	END IF;
	SET new.sincronizar = 1;
END $$