DROP TRIGGER IF EXISTS actualizaBloqueTransferenciaResolucion|
DELIMITER $$
CREATE TRIGGER actualizaBloqueTransferenciaResolucion
BEFORE UPDATE ON ec_bloques_transferencias_resolucion
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE reception_block_unique_folio VARCHAR( 30 );
	
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema*/
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso = 1;
	/*Consulta folio unico de bloque validacion*/
		SELECT 
			folio_unico
		INTO
			reception_block_unique_folio
		FROM ec_bloques_transferencias_recepcion
		WHERE id_bloque_transferencia_recepcion = new.id_bloque_transferencia_recepcion;
	/*inserta registros de sincronizacion*/
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_bloques_transferencias_resolucion",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( reception_block_unique_folio IS NULL,
					'',
					CONCAT( '"id_bloque_transferencia_recepcion" : "', 
					'( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'',
					reception_block_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_usuario IS NULL,
					'',	
					CONCAT( '"id_usuario" : "', new.id_usuario, '",' ) 
				),
				IF( new.id_producto IS NULL,
					'',
					CONCAT( '"id_producto" : "', new.id_producto, '",' )
				),
				IF( new.id_proveedor_producto IS NULL,
					'',
					CONCAT( '"id_proveedor_producto" : "', new.id_proveedor_producto, '",' )
				),
				IF( new.piezas_faltantes IS NULL,
					'',
					CONCAT( '"piezas_faltantes" : "', new.piezas_faltantes, '",' )
				),
				IF( new.piezas_sobrantes IS NULL,
					'',
					CONCAT( '"piezas_sobrantes" : "', new.piezas_sobrantes, '",' )
				),
				IF( new.piezas_no_corresponden IS NULL,
					'',
					CONCAT( '"piezas_no_corresponden" : "', new.piezas_no_corresponden, '",' )
				),
				IF( new.piezas_se_quedan IS NULL,
					'',
					CONCAT( '"piezas_se_quedan" : "', new.piezas_se_quedan, '",' )
				),
				IF( new.piezas_se_regresan IS NULL,
					'',
					CONCAT( '"piezas_se_regresan" : "', new.piezas_se_regresan, '",' )
				),
				IF( new.piezas_faltaron IS NULL,
					'',
					CONCAT( '"piezas_faltaron" : "', new.piezas_faltaron, '",' )
				),
				IF( new.conteo IS NULL,
					'',
					CONCAT( '"conteo" : "', new.conteo, '",' )
				),
				IF( new.conteo_excedente IS NULL,
					'',
					CONCAT( '"conteo_excedente" : "', new.conteo_excedente, '",' )
				),
				IF( new.diferencia IS NULL,
					'',
					CONCAT( '"diferencia" : "', new.diferencia, '",' )
				),
				IF( new.id_producto_resolucion IS NULL,
					'',
					CONCAT( '"id_producto_resolucion" : "', new.id_producto_resolucion, '",' )
				),
				IF( new.resuelto IS NULL,
					'',
					CONCAT( '"resuelto" : "', new.resuelto, '",' )
				),
				IF( new.folio_unico IS NULL,
					'',
					CONCAT( '"folio_unico" : "', new.folio_unico, '",' )
				),
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaBloqueTransferenciaResolucion',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$