DROP PROCEDURE IF EXISTS generaRegistroSincronizacionVenta| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionVenta( IN sale_header_id BIGINT, IN teller_session_id INT, IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE client_unique_folio VARCHAR( 30 );
    DECLARE teller_session_unique_folio VARCHAR( 30 );

	SELECT 
		c.folio_unico 
		INTO 
		client_unique_folio 
	FROM ec_pedidos p
	LEFT JOIN ec_clientes c
	ON p.id_cliente = c.id_cliente
	WHERE p.id_pedido = sale_header_id;

	IF( teller_session_id IS NOT NULL AND teller_session_id > 0 )
	THEN
		SELECT 
	        folio_unico INTO teller_session_unique_folio 
        FROM ec_sesion_caja 
        WHERE id_sesion_caja = teller_session_id;
	END IF;
	/*SET GLOBAL group_concat_max_len = 900000;*/
	INSERT INTO sys_sincronizacion_ventas ( id_sincronizacion_venta, json, tabla, registro_llave, id_sucursal_destino, id_status_sincronizacion )
	SELECT
		NULL,
		CONCAT(
			'{',
				'"folio_nv" : "', IF( ped.folio_nv IS NULL, '', ped.folio_nv ), '",\n',
				'"id_cliente" : "',
				IF( ped.id_cliente = 1 OR ped.id_cliente = -1, 
					ped.id_cliente, 
					CONCAT( '( SELECT id_cliente FROM ec_clientes WHERE folio_unico = \'', client_unique_folio ,'\' )' )  
				),
				'",\n',
				'"fecha_alta" : "', ped.fecha_alta, '",\n',
				'"subtotal" : "', ped.subtotal, '",\n',
				'"total" : "', ped.total, '",\n',
				'"pagado" : "', ped.pagado, '",\n',
				'"id_sucursal" : "', ped.id_sucursal, '",\n',
				'"id_usuario" : "', ped.id_usuario, '",\n',
				'"descuento" : "', ped.descuento, '",\n',
				'"folio_abono" : "', IF( ped.folio_abono IS NULL, '', ped.folio_abono ), '",\n',
				'"correo" : "', REPLACE( REPLACE( ped.correo, 'no aplica', '-' ), 'No aplica', '' ), '",\n',
				'"facebook" : "', REPLACE( REPLACE( ped.facebook, 'no aplica', '-' ), 'No aplica', '' ), '",\n',
				'"ultima_sincronizacion" : "', IF( ped.ultima_sincronizacion IS NULL, '', DATE_FORMAT( ped.ultima_sincronizacion, "%Y-%m-%d" ) ), '",\n',
				'"ultima_modificacion" : "', DATE_FORMAT( ped.ultima_modificacion, "%Y-%m-%d" ), '",\n',
				'"tipo_pedido" : "', ped.tipo_pedido, '",\n',
				'"id_status_agrupacion" : "', ped.id_status_agrupacion, '",\n',
				'"id_cajero" : "', ped.id_cajero, '",\n',
				'"id_devoluciones" : "', ped.id_devoluciones, '",\n',
				'"venta_validada" : "', ped.venta_validada, '",\n',
				'"tipo_sistema" : "', ped.tipo_sistema, '",\n',
				'"folio_unico" : "', ped.folio_unico, '"\n',
                IF( ped.id_sesion_caja != 0, 
                    CONCAT( ',"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )"\n' ),
                    ',"id_sesion_caja" : "0"'
                ),
				( SELECT 
					IF( ped_det.id_pedido_detalle IS NOT NULL,
						CONCAT( ', "sale_detail" : [\n',
							GROUP_CONCAT(
							DISTINCT( CONCAT( 
									'{',
										'"id_producto" : "', ped_det.id_producto, '",\n',
										'"cantidad" : "', ped_det.cantidad, '",\n',
										'"precio" : "', ped_det.precio, '",\n',
										'"monto" : "', ped_det.monto, '",\n',
										'"cantidad_surtida" : "', ped_det.cantidad_surtida, '",\n',
										'"descuento" : "', ped_det.descuento, '",\n',
										'"es_externo" : "', ped_det.es_externo, '",\n',
										'"id_precio" : "', ped_det.id_precio, '",\n',
										'"folio_unico" : "', ped_det.folio_unico, '"\n',
									'}\n'
								) )
								SEPARATOR ','
							),
							']\n'
						),
						''
					)
				FROM ec_pedidos_detalle ped_det
				WHERE ped_det.id_pedido = ped.id_pedido
			),
			(SELECT
				CONCAT(
				',"return_reference" : [{',
					'"id_pedido" : " ( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', (SELECT folio_unico FROM ec_pedidos WHERE id_pedido = sale_header_id LIMIT 1),'\' )",',
					'"total_venta" : "', prd.total_venta,'",',
					'"monto_venta_mas_ultima_devolucion" : "', prd.monto_venta_mas_ultima_devolucion, '",',
					'"saldo_a_favor" : "', prd.saldo_a_favor, '",',
					'"folio_unico" : "', prd.folio_unico, '",',
					'"sincronizar" : "0"',
				'}]'
				)
			FROM ec_pedidos_referencia_devolucion prd
			WHERE prd.id_pedido = sale_header_id
			),
			'}'
		),
		'ec_pedidos',
		ped.folio_unico,
		IF( origin_store_id = -1, ped.id_sucursal, -1 ),
		1
	FROM ec_pedidos ped
	WHERE ped.id_pedido = sale_header_id
	GROUP BY ped.id_pedido;
/*manda actualizar el temporal de exhibicion si es el caso*/
	UPDATE ec_temporal_exhibicion SET sincronizar = 1 WHERE id_pedido = sale_header_id;
END $$