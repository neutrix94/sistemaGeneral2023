DROP TRIGGER IF EXISTS actualizaTransferenciaResolucion|
DELIMITER $$
CREATE TRIGGER actualizaTransferenciaResolucion
BEFORE UPDATE ON ec_transferencias_resolucion
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE transfer_detail_unique_folio VARCHAR( 30 );
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			t.folio_unico,
			tp.folio_unico
		INTO
			transfer_unique_folio,
			transfer_detail_unique_folio
		FROM ec_transferencia_productos tp
		LEFT JOIN ec_transferencias t 
		ON tp.id_transferencia = t.id_transferencia
		WHERE tp.id_transferencia_producto = new.id_transferencia_producto;
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_resolucion",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia" : "', 
				'( SELECT id_transferencia FROM ec_transferencias WHERE codigo_unico = \'', 
					transfer_unique_folio,'\' LIMIT 1 )",',
				'"id_transferencia_producto" : "', 
				'( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE codigo_unico = \'', 
					transfer_detail_unique_folio,'\' LIMIT 1 )",',
				'"piezas_mantiene" : "', new.piezas_mantiene, '",',
				'"piezas_devuelve" : "', new.piezas_devuelve, '",',
				'"piezas_faltantes" : "', new.piezas_faltantes, '",',
				'"id_usuario" : "', IF( new.id_usuario IS NULL, '', new.id_usuario ), '",',
				'"resuelto" : "', new.resuelto, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferenciaResolucion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$