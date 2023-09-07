DROP TRIGGER IF EXISTS actualizaPedidoDetalle|
DELIMITER $$
CREATE TRIGGER actualizaPedidoDetalle
BEFORE UPDATE ON ec_pedidos_detalle
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE destinity_store_id INT(11);
    DECLARE sale_unique_folio VARCHAR(30);

    IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND new.folio_unico != 0 AND new.modificado = 1 )
    THEN
    
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
        SELECT folio_unico, id_sucursal INTO sale_unique_folio, destinity_store_id FROM ec_pedidos WHERE id_pedido = new.id_pedido;
		INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_pedidos_detalle",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
                '"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio, '\' LIMIT 1 )",',
				'"id_producto" : "', new.id_producto, '",',
				'"cantidad" : "', new.cantidad, '",',
				'"precio" : "', new.precio, '",',
				'"monto" : "', new.monto, '",',
				'"iva" : "', new.iva, '",',
				'"ieps" : "', new.ieps, '",',
				'"cantidad_surtida" : "', new.cantidad_surtida, '",',
				'"descuento" : "', new.descuento, '",',
				'"modificado" : "0",',
				'"es_externo" : "', new.es_externo, '",',
				'"id_precio" : "', new.id_precio, '"',
				'}'
			),
			NOW(),
			'actualizaPedidoDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );
	END IF;
	SET new.modificado = 1;
END $$