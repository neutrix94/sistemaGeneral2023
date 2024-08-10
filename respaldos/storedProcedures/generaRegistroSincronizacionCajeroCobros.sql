DROP PROCEDURE IF EXISTS generaRegistroSincronizacionCajeroCobros| 
DELIMITER $$
CREATE PROCEDURE generaRegistroSincronizacionCajeroCobros( IN payment_id BIGINT, IN teller_session_id INT, IN sale_unique_folio VARCHAR( 30 ), IN origin_store_id INTEGER(11), IN type VARCHAR( 30 ) )/*IN folio_unico_movimiento_almacen VARCHAR( 30 )*/
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE client_unique_folio VARCHAR( 30 );
    DECLARE teller_session_unique_folio VARCHAR( 30 );

    DECLARE netpay_transaction_id BIGINT DEFAULT NULL;

    IF( teller_session_id IS NOT NULL AND teller_session_id > 0 )
	THEN
		SELECT 
	        folio_unico INTO teller_session_unique_folio 
        FROM ec_sesion_caja 
        WHERE id_sesion_caja = teller_session_id;
	END IF;
	INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
	SELECT
		NULL,
		origin_store_id,
		IF( origin_store_id = -1, p.id_sucursal, -1 ),
		CONCAT(
			'{',
				'"action_type" : "insert",',
				'"table_name" : "ec_cajero_cobros",\n',
				'"primary_key" : "folio_unico",\n',				
				'"primary_key_value" : "', cc.folio_unico, '",\n',				
				'"id_sucursal" : "', cc.id_sucursal, '",',
				'"id_pedido" : "', IF( cc.id_pedido > 0, CONCAT( '( SELECT id_pedido FROM ec_pedidos WHERE folio_unico = \'', sale_unique_folio ,'\' )' ), -1 ), '",',
				'"id_devolucion" : "', IF( cc.id_devolucion > 0, CONCAT( '( SELECT id_devolucion FROM ec_devolucion WHERE folio_unico = \'', sale_unique_folio ,'\' )' ), -1 ), '",',
				'"id_cajero" : "', cc.id_cajero, '",',
				'"id_sesion_caja" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",',
				'"id_afiliacion" : "', cc.id_afiliacion, '",',
				'"id_terminal" : "', cc.id_terminal, '",',
				'"id_banco" : "', cc.id_banco, '",',
				'"id_tipo_pago" : "', cc.id_tipo_pago, '",',
				'"monto" : "', cc.monto, '",',
				'"fecha" : "', cc.fecha, '",',
				'"hora" : "', cc.hora, '",',
				'"cobro_cancelado" : "', cc.cobro_cancelado, '",',
				'"observaciones" : "', cc.observaciones, '",',
				'"folio_unico" : "', cc.folio_unico, '",',
				'"sincronizar" : "0"',
			'}'
		),
		NOW(),
		'generaRegistroSincronizacionCajeroCobros',
		1
	FROM ec_cajero_cobros cc
	LEFT JOIN ec_pedidos p
	ON p.id_pedido = cc.id_pedido
	LEFT JOIN ec_devolucion d
	ON d.id_devolucion = cc.id_devolucion
	WHERE cc.id_cajero_cobro = payment_id
	GROUP BY cc.id_cajero_cobro;
/*Transaccion netPay
	SELECT id_transaccion_netpay INTO netpay_transaction_id FROM vf_transacciones_netpay WHERE id_cajero_cobro = payment_id;
	IF( netpay_transaction_id IS NOT NULL AND netpay_transaction_id > 0 AND netpay_transaction_id != '' )
	THEN
		INSERT INTO sys_sincronizacion_registros_ventas ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT
			NULL,
			origin_store_id,
			IF( origin_store_id = -1, t.id_sucursal, -1 ),
			CONCAT(
				'{',
					'"action_type" : "insert",',
					'"table_name" : "vf_transacciones_netpay",\n',	
					'"affiliation" : "', t.affiliation, '",',
					'"applicationLabel" : "', t.applicationLabel, '",',
					'"arqc" : "', t.arqc, '",',
					'"aid" : "', t.aid, '",',
					'"amount" : "', t.amount, '",',
					'"authCode" : "', t.authCode, '",',
					'"bin" : "', t.bin, '",',
					'"bankName" : "', t.bankName, '",',
					'"cardExpDate" : "', t.cardExpDate, '",',
					'"cardType" : "', t.cardType, '",',
					'"cardTypeName" : "', t.cardTypeName, '",',
					'"cityName" : "', t.cityName, '",',
					'"responseCode" : "', t.responseCode, '",',
					'"folioNumber" : "', t.folioNumber, '",',
					'"hasPin" : "', t.hasPin, '",',
					'"hexSign" : "', t.hexSign, '",',
					'"isQps" : "', t.isQps, '",',
					'"message" : "', t.message, '",',
					'"isRePrint" : "', t.isRePrint, '",',
					'"moduleCharge" : "', t.moduleCharge, '",',
					'"moduleLote" : "', t.moduleLote, '",',
					'"customerName" : "', t.customerName, '",',
					'"terminalId" : "', t.terminalId, '",',
					'"orderId" : "', t.orderId, '",',
					'"preAuth" : "', t.preAuth, '",',
					'"preStatus" : "', t.preStatus, '",',
					'"promotion" : "', t.promotion, '",',
					'"rePrintDate" : "', t.rePrintDate, '",',
					'"rePrintMark" : "', t.rePrintMark, '",',
					'"rePrintModule" : "', t.rePrintModule, '",',
					'"cardNumber" : "', t.cardNumber, '",',
					'"storeName" : "', t.storeName, '",',
					'"streetName" : "', t.streetName, '",',
					'"ticketDate" : "', t.ticketDate, '",',
					'"tipAmount" : "', t.tipAmount, '",',
					'"tipLessAmount" : "', t.tipLessAmount, '",',		
					'"transDate" : "', t.transDate, '",',
					'"transType" : "', t.transType, '",',
					'"transactionCertificate" : "', t.transactionCertificate, '",',
					'"transactionId" : "', t.transactionId, '",',
					'"id_sucursal" : "', t.id_sucursal, '",',
					'"id_cajero" : "', t.id_cajero, '",',
					'"folio_venta" : "', t.folio_venta, '",',		
					'"id_sesion_cajero" : "( SELECT id_sesion_caja FROM ec_sesion_caja WHERE folio_unico = \'', teller_session_unique_folio, '\' LIMIT 1 )",',
					'"id_cajero_cobro" : "', 
					CONCAT( '( SELECT id_cajero_cobro FROM ec_cajero_cobros WHERE folio_unico = \'',( SELECT folio_unico FROM ec_cajero_cobros WHERE id_cajero_cobro = payment_id ), '\' )' ), '",',
					'"store_id_netpay" : "', t.store_id_netpay, '",',
					'"fecha_alta" : "', t.fecha_alta, '",',
					'"fecha_actualizacion" : "', t.fecha_actualizacion, '"',
				'}'
			),
			NOW(),
			'generaRegistroSincronizacionCajeroCobros_vf_transacciones_netpay',
			1
		FROM vf_transacciones_netpay t
		WHERE t.id_cajero_cobro = payment_id;
	END IF;*/
/**/
END $$