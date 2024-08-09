DROP PROCEDURE IF EXISTS spMovimientoAlmacenDetalle_actualiza|
DELIMITER $$
CREATE PROCEDURE spMovimientoAlmacenDetalle_actualiza ( IN id_movimiento_detalle BIGINT, IN cantidad_nueva FLOAT(15,4), IN sincronizar_registro INTEGER )
    BEGIN
/*verificado 13-07-2023*/
	DECLARE cantidad_antes FLOAT( 15, 4);    
	DECLARE tipo_afecta INT;
	DECLARE new_inventory FLOAT( 15, 4 );
	DECLARE folio_unico_movimiento_almacen VARCHAR( 30 );
	DECLARE folio_unico_movimiento_detalle VARCHAR( 30 );
    DECLARE store_id INTEGER;
    DECLARE destinity_store_id INTEGER;
    DECLARE product_id INTEGER;
    DECLARE product_provider_id INTEGER;
    DECLARE warehouse_id INTEGER;
    DECLARE movement_detail_id BIGINT;
    
    /*SELECT cantidad, folio_unico FROM ec_movimiento_detalle WHERE id_movimiento_almacen_detalle = id_movimiento_detalle;*/

    SELECT 
		ma.id_almacen, 
        tm.afecta, 
        ma.folio_unico, 
        ma.id_sucursal,
        md.folio_unico,
        md.cantidad,
        md.id_producto,
        md.id_proveedor_producto,
        md.id_movimiento_almacen_detalle
	INTO 
        warehouse_id,
        tipo_afecta, 
        folio_unico_movimiento_almacen, 
        destinity_store_id,
        folio_unico_movimiento_detalle,
        cantidad_antes,
        product_id,
        product_provider_id,
        movement_detail_id
	FROM ec_movimiento_detalle md
    LEFT JOIN ec_movimiento_almacen ma
    ON ma.id_movimiento_almacen = md.id_movimiento
	LEFT JOIN ec_tipos_movimiento tm
    ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
	WHERE md.id_movimiento_almacen_detalle = id_movimiento_detalle;
   
    IF( folio_unico_movimiento_detalle IS NOT NULL AND sincronizar_registro IS NOT NULL )
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
				'"primary_key_value" : "', folio_unico_movimiento_detalle, '",',
				'"cantidad" : "', cantidad_nueva, '",',
				'"cantidad_surtida" : "', cantidad_nueva, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'spMovimientoAlmacenDetalle_actualiza',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );
	END IF;

	IF( cantidad_nueva != cantidad_antes )
	THEN
        UPDATE ec_movimiento_detalle SET cantidad = cantidad_nueva, cantidad_surtida = cantidad_nueva WHERE id_movimiento_almacen_detalle = id_movimiento_detalle;
	/*resta la cantidad anterior*/
		UPDATE ec_almacen_producto ap
			SET ap.inventario = ( ap.inventario - ( cantidad_antes * tipo_afecta ) ) + ( cantidad_nueva * tipo_afecta )
		WHERE ap.id_almacen = warehouse_id
		AND ap.id_producto = product_id;
/*Modifica el movimiento a nivel proveedor producto*/
	   IF( product_provider_id != '' AND product_provider_id IS NOT NULL AND product_provider_id != -1 )
		THEN
        /*actualiza el detalle a nivel proveedor producto*/
            CALL spMovimientoDetalleProveedorProducto_actualiza( id_movimiento_detalle, product_provider_id, cantidad_nueva );
			/*UPDATE ec_movimiento_detalle_proveedor_producto
			SET cantidad = cantidad_nueva
			WHERE id_movimiento_almacen_detalle = movement_detail_id
			AND id_proveedor_producto = product_provider_id;*/
	  	END IF;
	END IF;
END $$