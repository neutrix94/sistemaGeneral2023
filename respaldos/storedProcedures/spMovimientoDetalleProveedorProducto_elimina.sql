DROP PROCEDURE IF EXISTS spMovimientoDetalleProveedorProducto_elimina|
DELIMITER $$
CREATE PROCEDURE spMovimientoDetalleProveedorProducto_elimina( IN id_movimiento_detalle BIGINT, IN product_provider_id INTEGER, IN cantidad_nueva FLOAT( 15, 4 ), IN sincronizar_registro INTEGER )
    BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE movement_store_id INTEGER;
	DECLARE final_inventory FLOAT;
	DECLARE product_provider_movement_id INTEGER;
	DECLARE product_provider_movement_unique_folio VARCHAR(30);
    DECLARE cantidad_anterior FLOAT( 15, 4 );
    DECLARE movement_type INTEGER;
    DECLARE warehouse_id INTEGER;
/*Consulta sucursal del sistema*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
/*Consulta datos antes de actualizar*/
    SELECT 
        mdpp.id_movimiento_detalle_proveedor_producto,
        mdpp.folio_unico,
        mdpp.cantidad,
        mdpp.id_sucursal,
        mdpp.id_almacen,
        tm.afecta
    INTO 
        product_provider_movement_id,
        product_provider_movement_unique_folio,
        cantidad_anterior,
        movement_store_id,
        warehouse_id,
        movement_type
    FROM ec_movimiento_detalle_proveedor_producto mdpp
    LEFT JOIN ec_movimiento_detalle md
    ON md.id_movimiento_almacen_detalle = mdpp.id_movimiento_almacen_detalle
    LEFT JOIN ec_tipos_movimiento tm
    ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
    WHERE mdpp.id_movimiento_almacen_detalle = id_movimiento_detalle;

/*elimina el detalle de movimiento proveedor producto*/
    DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_movimiento_almacen_detalle = id_movimiento_detalle
    AND id_proveedor_producto = product_provider_id;
/*Actualiza el inventario acumulado (resta)*/
    SET final_inventory = ( cantidad_anterior * movement_type ) ;
    UPDATE ec_inventario_proveedor_producto SET inventario = ( inventario - final_inventory)  
    WHERE id_proveedor_producto = product_provider_id AND id_almacen = warehouse_id;
    
	IF( product_provider_movement_unique_folio IS NOT NULL AND sincronizar_registro IS NOT NULL )
	THEN
		INSERT INTO sys_sincronizacion_registros_movimientos_proveedor_producto ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_movimiento_detalle_proveedor_producto",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', product_provider_movement_unique_folio, '",',
				'"cantidad" : "', cantidad_nueva, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'spMovimientoDetalleProveedorProducto_elimina',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, movement_store_id, -1 );
	END IF;
END $$