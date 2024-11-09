DROP TRIGGER IF EXISTS actualizaPedidoReferenciaDevolucion|
DELIMITER $$
CREATE TRIGGER actualizaPedidoReferenciaDevolucion
BEFORE UPDATE ON ec_pedidos_referencia_devolucion
FOR EACH ROW
BEGIN
    DECLARE store_id INT(11);
    DECLARE destinity_store_id INT(11);
    DECLARE sale_unique_folio VARCHAR(30);

    IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND new.folio_unico != 0 )
    THEN
    
        SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
		INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_pedidos_referencia_devolucion",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"total_venta" : "', new.total_venta, '",',
				'"monto_venta_mas_ultima_devolucion" : "', new.monto_venta_mas_ultima_devolucion, '",',
				'"saldo_a_favor" : "', new.saldo_a_favor, '"',
				'}'
			),
			NOW(),
			'actualizaPedidoDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, destinity_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$