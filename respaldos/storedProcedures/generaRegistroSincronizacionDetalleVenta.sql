/*Se agregan los atributos de llave primary y su valor para comprobacion en sincronizacion (2024-08-08) */
DROP PROCEDURE IF EXISTS generaRegistroSincronizacionDetalleVenta| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionDetalleVenta( IN sale_detail_id BIGINT, IN sale_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE client_unique_folio VARCHAR( 30 );
    DECLARE teller_session_unique_folio VARCHAR( 30 );
    
	/*SET GLOBAL group_concat_max_len = 900000;*/
	INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, p.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', pd.folio_unico,'",',
				'"table_name" : "ec_pedidos_detalle",\n',
				'"id_pedido" : "( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio ,'\' )",\n',
				'"id_producto" : "', pd.id_producto, '",\n',
				'"cantidad" : "', pd.cantidad, '",\n',
				'"precio" : "', pd.precio, '",\n',
				'"monto" : "', pd.monto, '",\n',
				'"cantidad_surtida" : "', pd.cantidad_surtida, '",\n',
				'"descuento" : "', pd.descuento, '",\n',
				'"es_externo" : "', pd.es_externo, '",\n',
				'"id_precio" : "', pd.id_precio, '",\n',
				'"folio_unico" : "', pd.folio_unico, '"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionDetalleVenta',
		1
	FROM ec_pedidos_detalle pd
	LEFT JOIN ec_pedidos p
	ON pd.id_pedido = p.id_pedido
	WHERE pd.id_pedido_detalle = sale_detail_id
	GROUP BY pd.id_pedido_detalle;
END $$