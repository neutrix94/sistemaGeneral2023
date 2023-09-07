DROP TRIGGER IF EXISTS insertaTransferenciaValidacionUsuario|
DELIMITER $$
CREATE TRIGGER insertaTransferenciaValidacionUsuario
BEFORE INSERT ON ec_transferencias_validacion_usuarios
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE transfer_origin_store_id INTEGER;
	DECLARE transfer_detail_unique_folio VARCHAR( 30 );

	IF( new.sincronizar = 1 )
	THEN
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
	/*Consulta sucursal origen y folio_unico de transferencia*/
		SELECT 
			tp.folio_unico,
			t.id_sucursal_origen
		INTO
			transfer_detail_unique_folio,
			transfer_origin_store_id
		FROM ec_transferencia_productos tp
		LEFT JOIN ec_transferencias t
		ON tp.id_transferencia = t.id_transferencia
		WHERE tp.id_transferencia_producto = new.id_transferencia_producto;
	/*consulta el siguiente id*/
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_transferencias_validacion_usuarios'
	    AND table_schema = database();
	/*actualiza folio unico*/
        SET new.folio_unico = CONCAT( prefix, '_TVU_', row_id );
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias_validacion_usuarios",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia_producto" : "', 
				'( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE folio_unico = \'',
					transfer_detail_unique_folio, '\' LIMIT 1 )",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"cantidad_cajas_validadas" : "', new.cantidad_cajas_validadas, '",',
				'"cantidad_paquetes_validados" : "', new.cantidad_paquetes_validados, '",',
				'"cantidad_piezas_validadas" : "', new.cantidad_piezas_validadas, '",',
				'"fecha_validacion" : "', new.fecha_validacion, '",',
				'"id_status" : "', new.id_status, '",',
				'"validado_por_nombre" : "', new.validado_por_nombre, '",',
				'"codigo_barras" : "', new.codigo_barras, '",',
				'"codigo_unico" : "', new.codigo_unico, '",',
				'"folio_unico" : "', new.folio_unico, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaTransferenciaValidacionUsuario',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, transfer_origin_store_id, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$