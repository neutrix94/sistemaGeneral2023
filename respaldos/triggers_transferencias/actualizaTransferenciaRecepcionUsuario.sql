DROP TRIGGER IF EXISTS actualizaTransferenciaRecepcionUsuario|
DELIMITER $$
CREATE TRIGGER actualizaTransferenciaRecepcionUsuario
BEFORE UPDATE ON ec_transferencias_recepcion_usuarios
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_detail_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			transfer_detail_unique_folio
		FROM ec_transferencia_productos
		WHERE id_transferencia_producto = new.id_transferencia_producto;
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_recepcion_usuarios",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia_producto" : "( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE folio_unico = \'', 
					transfer_detail_unique_folio, '\' LIMIT 1 )",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"cantidad_cajas_recibidas" : "', new.cantidad_cajas_recibidas, '",',
				'"cantidad_paquetes_recibidos" : "', new.cantidad_paquetes_recibidos, '",',
				'"cantidad_piezas_recibidas" : "', new.cantidad_piezas_recibidas, '",',
				'"fecha_recepcion" : "', new.fecha_recepcion, '",',
				'"id_status" : "', new.id_status, '",',
				'"validado_por_nombre" : "', new.validado_por_nombre, '",',
				'"codigo_validacion" : "', new.codigo_validacion, '",',
				'"codigo_unico" : "', new.codigo_unico, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferenciaRecepcionUsuario',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$