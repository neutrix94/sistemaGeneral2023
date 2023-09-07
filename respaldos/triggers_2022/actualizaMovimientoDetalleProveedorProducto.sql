DROP TRIGGER IF EXISTS actualizaMovimientoDetalleProveedorProducto|
DELIMITER $$
CREATE TRIGGER actualizaMovimientoDetalleProveedorProducto
BEFORE UPDATE ON ec_movimiento_detalle_proveedor_producto
FOR EACH ROW
BEGIN
	DECLARE final_inventory FLOAT;
	DECLARE store_id INTEGER;
	DECLARE movement_detail_unique_code VARCHAR( 30 ) DEFAULT NULL;
	DECLARE sale_validation_unique_code VARCHAR( 30 ) DEFAULT NULL;
	IF( new.insertado_por_sincronizacion = 0 )
	THEN
		SET final_inventory = ( ( new.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = new.id_tipo_movimiento ) ) -
		( old.cantidad * (SELECT afecta FROM ec_tipos_movimiento WHERE id_tipo_movimiento = old.id_tipo_movimiento ) ) );
		UPDATE ec_inventario_proveedor_producto SET inventario = ( inventario + final_inventory)  
		WHERE id_proveedor_producto = new.id_proveedor_producto AND id_almacen = new.id_almacen;
		IF( new.sincronizar = 1 )
		THEN
			IF( new.id_movimiento_almacen_detalle IS NOT NULL )
			THEN
				SET movement_detail_unique_code = ( SELECT folio_unico FROM ec_movimiento_detalle WHERE id_movimiento_almacen_detalle = new.id_movimiento_almacen_detalle );
			END IF;
			IF( new.id_pedido_validacion IS NOT NULL )
			THEN
				SET sale_validation_unique_code = ( SELECT folio_unico FROM ec_pedidos_validacion_usuarios WHERE id_pedido_validacion = new.id_pedido_validacion );
			END IF;
			SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;

			INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
			id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
			SELECT 
				NULL,
				store_id,
				id_sucursal,
				CONCAT('{',
					'"table_name" : "ec_movimiento_detalle_proveedor_producto",',
					'"action_type" : "update",',
					'"primary_key" : "folio_unico",',
					'"primary_key_value" : "', new.folio_unico, '",',
					IF( new.id_proveedor_producto IS NULL, 
						'', 
						CONCAT( '"id_proveedor_producto" : "', new.id_proveedor_producto , '",' )
					),
					'"cantidad" : "', new.cantidad, '",',
					'"fecha_registro" : "', new.fecha_registro, '",',
					'"id_sucursal" : "', new.id_sucursal, '",',
					'"status_agrupacion" : "', new.status_agrupacion, '",',
					'"id_tipo_movimiento" : "', new.id_tipo_movimiento, '",',
					'"id_almacen" : "', new.id_almacen, '",',
					IF( sale_validation_unique_code IS NULL, 
						'', 
						CONCAT( '"id_pedido_validacion" : "( SELECT id_pedido_validacion FROM ec_pedidos_validacion_usuarios WHERE folio_unico = \'', sale_validation_unique_code, '\' )",')
					),
					'"folio_unico" : "', IF( new.folio_unico IS NULL, '', new.folio_unico ), '",',
					'"insertado_por_sincronizacion" : "0",',
					'"sincronizar" : "0"'
					'}'
				),
				NOW(),
				0,
				1
			FROM sys_sucursales 
			WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );	
		END IF;
	END IF;
	SET new.sincronizar = 1;
END $$