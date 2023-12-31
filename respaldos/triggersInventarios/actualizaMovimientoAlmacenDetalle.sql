DROP TRIGGER IF EXISTS actualizaMovimientoAlmacenDetalle|
DELIMITER $$
CREATE TRIGGER actualizaMovimientoAlmacenDetalle
BEFORE UPDATE ON ec_movimiento_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE warehouse_id INT;    
	DECLARE tipo_afecta INT;
	DECLARE new_inventory FLOAT( 15, 4 );
	DECLARE folio_unico_movimiento_almacen VARCHAR( 30 );
    DECLARE store_id INT(11);
    DECLARE destinity_store_id INT(11);
    
    SELECT 
		ma.id_almacen, tm.afecta, ma.folio_unico, ma.id_sucursal
		INTO warehouse_id,tipo_afecta, folio_unico_movimiento_almacen, destinity_store_id
	FROM ec_movimiento_almacen ma
	LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
	WHERE ma.id_movimiento_almacen=new.id_movimiento;   
   
    IF( new.sincronizar = 1 )/* old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND */
    THEN
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
 	/*inserta registro sincronizacion de movimiento*/
 		INSERT INTO sys_sincronizacion_registros_movimientos_almacen ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_movimiento_detalle",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"cantidad" : "', new.cantidad, '",',
				'"cantidad_surtida" : "', new.cantidad_surtida, '",',
				'"insertado_por_sincronizacion" : "1",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaMovimientoAlmacenDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );

	   IF( new.id_proveedor_producto != '' AND new.id_proveedor_producto IS NOT NULL AND new.id_proveedor_producto != -1 )
		THEN
			UPDATE ec_movimiento_detalle_proveedor_producto
			SET cantidad = new.cantidad
			WHERE id_movimiento_almacen_detalle = new.id_movimiento_almacen_detalle
			AND id_proveedor_producto = new.id_proveedor_producto;
	  	END IF;
	END IF;
	SET new.sincronizar = 1;

	IF( new.cantidad != old.cantidad )
	THEN
	/*resta la cantidad anterior*/
		UPDATE ec_almacen_producto ap
			SET ap.inventario = ( ap.inventario - ( old.cantidad * tipo_afecta ) ) + ( new.cantidad * tipo_afecta )
		WHERE ap.id_almacen = warehouse_id
		AND ap.id_producto = new.id_producto;
	END IF;
END $$