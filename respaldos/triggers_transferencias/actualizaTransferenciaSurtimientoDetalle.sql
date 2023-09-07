DROP TRIGGER IF EXISTS actualizaTransferenciaSurtimientoDetalle|
DELIMITER $$
CREATE TRIGGER actualizaTransferenciaSurtimientoDetalle
BEFORE UPDATE ON ec_transferencias_surtimiento_detalle
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_product_unique_folio VARCHAR( 30 );
	DECLARE transfer_supply_unique_folio VARCHAR( 30 );
	DECLARE transfer_origin_store_id INTEGER;

	IF( new.sincronizar = 1 )
	THEN
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
    /*Consulta sucursal de transferencia y folio_unico*/
		SELECT 
			tp.folio_unico,
			t.id_sucursal_origen
		INTO
			transfer_product_unique_folio,
			transfer_origin_store_id
		FROM ec_transferencia_productos tp
		LEFT JOIN ec_transferencias t
		ON tp.id_transferencia = t.id_transferencia
		WHERE tp.id_transferencia_producto = new.id_transferencia_producto;
	/*consulta el folio unico de cabecera de surtimiento ( asignacion )*/
		SELECT
			folio_unico
		INTO
			transfer_supply_unique_folio
		FROM ec_transferencias_surtimiento
		WHERE id_transferencia_surtimiento = new.id_transferencia_surtimiento;
	/*inserta registro de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_surtimiento_detalle",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia_surtimiento" : "',
				'( SELECT id_transferencia_surtimiento FROM ec_transferencias_surtimiento WHERE folio_unico = \'', 
					transfer_supply_unique_folio , '\' LIMIT 1 )",',
				'"id_transferencia_producto" : "',
				'( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE folio_unico = \'', 
					transfer_product_unique_folio , '\' LIMIT 1 )",',
				'"id_status_surtimiento" : "', new.id_status_surtimiento, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferenciaSurtimientoDetalle',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$