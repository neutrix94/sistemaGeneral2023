DROP TRIGGER IF EXISTS actualizaPedido|
DELIMITER $$
CREATE TRIGGER actualizaPedido
BEFORE UPDATE ON ec_pedidos
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	DECLARE teller_session_unique_folio VARCHAR( 30 );
	IF( old.folio_unico IS NOT NULL AND new.folio_unico IS NOT NULL AND new.modificado = 1 )
	THEN
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
		IF( new.id_sesion_caja != 0 )
		THEN
			SELECT 
				folio_unico INTO teller_session_unique_folio 
			FROM ec_sesion_caja 
			WHERE id_sesion_caja = new.id_sesion_caja;
		END IF;
		INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_pedidos",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"folio_pedido" : "', IF( new.folio_pedido IS NULL, '', new.folio_pedido ), '",',
				'"folio_nv" : "', IF( new.folio_nv IS NULL, '', new.folio_nv ), '",',
				'"folio_factura" : "', IF( new.folio_factura IS NULL, '', new.folio_factura ), '",',
				'"folio_cotizacion" : "', IF( new.folio_cotizacion IS NULL, '', new.folio_cotizacion ), '",',
				'"id_estatus" : "', new.id_estatus, '",',
				'"id_moneda" : "', new.id_moneda, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"fecha_factura" : "', IF( new.fecha_factura IS NULL, '', new.fecha_factura ), '",',
				'"id_direccion" : "', new.id_direccion, '",',
				'"direccion" : "', IF( new.direccion IS NULL, '', new.direccion ), '",',
				'"subtotal" : "', new.subtotal, '",',
				'"iva" : "', new.iva, '",',
				'"ieps" : "', new.ieps, '",',
				'"total" : "', new.total, '",',
				'"dias_proximo" : "', IF( new.dias_proximo IS NULL, '', new.dias_proximo ), '",',
				'"pagado" : "', new.pagado, '",',
				'"surtido" : "', new.surtido, '",',
				'"enviado" : "', new.enviado, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"fue_cot" : "', IF( new.fue_cot IS NULL, '', new.fue_cot ), '",',
				'"facturado" : "', IF( new.facturado IS NULL, '', new.facturado ), '",',
				'"id_tipo_envio" : "', new.id_tipo_envio, '",',
				'"descuento" : "', new.descuento, '",',
				'"folio_abono" : "', IF( new.folio_abono IS NULL, '', new.folio_abono ), '",',
				'"correo" : "', new.correo, '",',
				'"facebook" : "', new.facebook, '",',
				'"modificado" : "0",',
				'"ultima_sincronizacion" : "', IF( new.ultima_sincronizacion IS NULL, '', new.ultima_sincronizacion ), '",',
				'"ultima_modificacion" : "', new.ultima_modificacion, '",',
				'"tipo_pedido" : "', new.tipo_pedido, '",',
				'"id_status_agrupacion" : "', new.id_status_agrupacion, '",',
				'"id_cajero" : "', new.id_cajero, '",',
				'"id_devoluciones" : "', new.id_devoluciones, '",',
				'"venta_validada" : "', new.venta_validada, '",',
				'"tipo_sistema" : "', new.tipo_sistema, '"', 
				IF( new.id_sesion_caja != 0, 
					CONCAT( ',"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )"' ),
					''
				),
				'}'
			),
			NOW(),
			'actualizaPedido',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
	END IF;
	SET new.modificado = 1;
END $$