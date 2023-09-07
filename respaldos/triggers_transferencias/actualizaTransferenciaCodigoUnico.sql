DROP TRIGGER IF EXISTS actualizaTransferenciaCodigoUnico|
DELIMITER $$
CREATE TRIGGER actualizaTransferenciaCodigoUnico
BEFORE UPDATE ON ec_transferencia_codigos_unicos
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE transfer_origin_store_id INTEGER;
/*folios unicos*/
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE transfer_validation_block_unique_folio VARCHAR( 30 );
	DECLARE transfer_reception_block_unique_folio VARCHAR( 30 );
	DECLARE transfer_validation_unique_folio VARCHAR( 30 );
	DECLARE transfer_reception_unique_folio VARCHAR( 30 );
	DECLARE transfer_resolution_unique_folio VARCHAR( 30 );
	DECLARE transfer_resolution_detail_unique_folio VARCHAR( 30 );
	
	IF( new.sincronizar = 1 )
	THEN
	/*consulta sucursal del sistema, prefijo*/
		SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	/*consulta folio unico de bloque de validacion*/
		IF( new.id_bloque_transferencia_validacion IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_validation_block_unique_folio
			FROM ec_bloques_transferencias_validacion
			WHERE id_bloque_transferencia_validacion = new.id_bloque_transferencia_validacion;
		END IF;
	/*consulta folio unico de bloque de recepcion*/
		IF( new.id_bloque_transferencia_recepcion IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_reception_block_unique_folio
			FROM ec_bloques_transferencias_recepcion
			WHERE id_bloque_transferencia_recepcion = new.id_bloque_transferencia_recepcion;
		END IF;
	/*consulta folio unico de validacion de usuario*/
		IF( new.id_transferencia_validacion IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_validation_unique_folio
			FROM ec_transferencias_validacion_usuarios
			WHERE id_transferencia_validacion = new.id_transferencia_validacion;
		END IF;
	/*consulta folio unico de recepcion de usuario*/
		IF( new.id_transferencia_recepcion IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_reception_unique_folio
			FROM ec_transferencias_recepcion_usuarios
			WHERE id_transferencia_recepcion = new.id_transferencia_recepcion;
		END IF;
	/*consulta folio unico de bloque de resolucion de Transferencia*/
		IF( new.id_bloque_transferencia_resolucion IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_resolution_unique_folio
			FROM ec_bloques_transferencias_resolucion
			WHERE id_bloque_transferencia_resolucion = new.id_bloque_transferencia_resolucion;
		END IF;
	/*consulta folio unico de detalle de bloque de resolucion de Transferencia*/
		IF( new.id_bloque_transferencia_resolucion_detalle IS NOT NULL )
		THEN
			SELECT
				folio_unico
			INTO
				transfer_resolution_detail_unique_folio
			FROM ec_bloques_transferencias_resolucion_detalle
			WHERE id_bloque_transferencia_resolucion_detalle = new.id_bloque_transferencia_resolucion_detalle;
		END IF;
	/*consulta sucursal origen y folio_unico de transferencia*/
		IF( new.id_transferencia IS NOT NULL )
		THEN
			SELECT 
				t.folio_unico,
				t.id_sucursal_origen
			INTO
				transfer_unique_folio,
				transfer_origin_store_id
			FROM ec_transferencias t
			WHERE t.id_transferencia = new.id_transferencia;
		END IF;
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencia_codigos_unicos",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( new.id_bloque_transferencia_validacion IS NULL, 
					'',
					CONCAT( '"id_bloque_transferencia_validacion" : "( SELECT id_bloque_transferencia_validacion FROM ec_bloques_transferencias_validacion WHERE folio_unico = \'',  
						transfer_validation_block_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_bloque_transferencia_recepcion IS NULL, 
					'',
					CONCAT( '"id_bloque_transferencia_recepcion" : "( SELECT id_bloque_transferencia_recepcion FROM ec_bloques_transferencias_recepcion WHERE folio_unico = \'',  
						transfer_reception_block_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_usuario_validacion IS NULL, 
					'', 
					CONCAT( '"id_usuario_validacion" : "', new.id_usuario_validacion, '",' )
				),
				IF( new.id_usuario_recepcion IS NULL, 
					'', 
					CONCAT( '"id_usuario_recepcion" : "', new.id_usuario_recepcion, '",' )
				),
				IF( new.id_status_transferencia_codigo IS NULL, 
					'', 
					CONCAT( '"id_status_transferencia_codigo" : "', new.id_status_transferencia_codigo, '",' )
				),
				'"codigo_unico" : "', new.codigo_unico, '",',
				'"piezas_contenidas" : "', new.piezas_contenidas, '",',
				IF( new.id_transferencia_validacion IS NULL, 
					'',
					CONCAT( '"id_transferencia_validacion" : "( SELECT id_transferencia_validacion FROM ec_transferencias_validacion_usuarios WHERE folio_unico = \'',  
						transfer_validation_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_transferencia_recepcion IS NULL, 
					'',
					CONCAT( '"id_transferencia_recepcion" : "( SELECT id_transferencia_recepcion FROM ec_transferencias_recepcion_usuarios WHERE folio_unico = \'',  
						transfer_reception_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.nombre_status IS NULL, 
					'',
					CONCAT( '"nombre_status" : "', new.nombre_status, '",' )
				),
				'"fecha_alta" : "', new.fecha_alta, '",',
				IF( new.fecha_modificacion IS NULL, 
					'', 
					CONCAT( '"fecha_modificacion" : "', new.fecha_modificacion, '",' )
				),
				'"insertado_por_resolucion" : "', new.insertado_por_resolucion, '",',
				IF( new.id_bloque_transferencia_resolucion IS NULL, 
					'',
					CONCAT( '"id_bloque_transferencia_resolucion" : "( SELECT id_bloque_transferencia_resolucion FROM ec_bloques_transferencias_resolucion WHERE folio_unico = \'',  
						transfer_resolution_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_bloque_transferencia_resolucion_detalle IS NULL, 
					'',
					CONCAT( '"id_bloque_transferencia_resolucion_detalle" : "( SELECT id_bloque_transferencia_resolucion_detalle FROM ec_bloques_transferencias_resolucion_detalle WHERE folio_unico = \'',  
						transfer_resolution_detail_unique_folio, '\' LIMIT 1 )",' )
				),
				IF( new.id_transferencia IS NULL, 
					'',
					CONCAT( '"id_transferencia" : "( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'',  
						transfer_unique_folio, '\' LIMIT 1 )",' )
				),
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferenciaCodigoUnico',
			1
		FROM sys_sucursales 
		WHERE id_sucursal = IF( store_id = -1, 1, -1 );
	END IF;
	SET new.sincronizar = 1;
END $$