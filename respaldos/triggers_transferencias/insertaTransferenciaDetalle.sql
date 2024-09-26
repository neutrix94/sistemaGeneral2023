DROP TRIGGER IF EXISTS insertaTransferenciaDetalle|
DELIMITER $$
CREATE TRIGGER insertaTransferenciaDetalle
BEFORE INSERT ON ec_transferencia_productos
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	DECLARE transfer_unique_folio VARCHAR( 30 );
	DECLARE transfer_origin_store_id INTEGER;
	DECLARE transfer_destinity_store_id INTEGER;
	

	
	IF( new.sincronizar = 1 )
	THEN
		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
	/*Consulta sucursal de transferencia y folio_unico*/
		SELECT 
			folio_unico,
			id_sucursal_origen,
			id_sucursal_destino
		INTO
			transfer_unique_folio,
			transfer_origin_store_id,
			transfer_destinity_store_id
		FROM ec_transferencias 
		WHERE id_transferencia = new.id_transferencia;
		
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_transferencia_productos'
	    AND table_schema = database();

        SET new.folio_unico = CONCAT( prefix, '_TRDT_', row_id );
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencia_productos",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_transferencia" : "( SELECT id_transferencia FROM ec_transferencias WHERE folio_unico = \'', transfer_unique_folio, '\' LIMIT 1 )",',
				IF( new.id_producto_or IS NULL,
					'',
					CONCAT( '"id_producto_or" : "', new.id_producto_or, '",' )
				),
				IF( new.id_producto_de IS NULL,
					'',
					CONCAT( '"id_producto_de" : "', new.id_producto_de, '",' )
				),
				IF( new.cantidad IS NULL,
					'',
					CONCAT( '"cantidad" : "', new.cantidad, '",' )
				),
				IF( new.id_presentacion IS NULL,
					'',
					CONCAT( '"id_presentacion" : "', new.id_presentacion, '",' )
				),
				IF( new.cantidad_cajas IS NULL,
					'',
					CONCAT( '"cantidad_cajas" : "', new.cantidad_cajas, '",' )
				),
				IF( new.cantidad_paquetes IS NULL,
					'',
					CONCAT( '"cantidad_paquetes" : "', new.cantidad_paquetes, '",' )
				),
				IF( new.cantidad_piezas IS NULL,
					'',
					CONCAT( '"cantidad_piezas" : "', new.cantidad_piezas, '",' )
				),
				IF( new.cantidad_cajas_surtidas IS NULL,
					'',
					CONCAT( '"cantidad_cajas_surtidas" : "', new.cantidad_cajas_surtidas, '",' )
				),
				IF( new.cantidad_paquetes_surtidos IS NULL,
					'',
					CONCAT( '"cantidad_paquetes_surtidos" : "', new.cantidad_paquetes_surtidos, '",' )
				),
				IF( new.cantidad_piezas_surtidas IS NULL,
					'',
					CONCAT( '"cantidad_piezas_surtidas" : "', new.cantidad_piezas_surtidas, '",' )
				),
				IF( new.total_piezas_surtimiento IS NULL,
					'',
					CONCAT( '"total_piezas_surtimiento" : "', new.total_piezas_surtimiento, '",' )
				),
				IF( new.cantidad_cajas_validacion IS NULL,
					'',
					CONCAT( '"cantidad_cajas_validacion" : "', new.cantidad_cajas_validacion, '",' ) 
				),
				IF( new.cantidad_paquetes_validacion IS NULL,
					'',
					CONCAT( '"cantidad_paquetes_validacion" : "', new.cantidad_paquetes_validacion, '",' )
				),
				IF( new.cantidad_piezas_validacion IS NULL,
					'',
					CONCAT( '"cantidad_piezas_validacion" : "', new.cantidad_piezas_validacion, '",' )
				),
				IF( new.total_piezas_validacion IS NULL,
					'',
					CONCAT( '"total_piezas_validacion" : "', new.total_piezas_validacion, '",' )
				),
				IF( new.cantidad_cajas_recibidas IS NULL,
					'',
					CONCAT( '"cantidad_cajas_recibidas" : "', new.cantidad_cajas_recibidas, '",' )
				),
				IF( new.cantidad_paquetes_recibidos IS NULL,
					'',
					CONCAT( '"cantidad_paquetes_recibidos" : "', new.cantidad_paquetes_recibidos, '",' )
				),
				IF( new.cantidad_piezas_recibidas IS NULL,
					'',
					CONCAT( '"cantidad_piezas_recibidas" : "', new.cantidad_piezas_recibidas, '",' )
				),
				IF( new.total_piezas_recibidas IS NULL,
					'',
					CONCAT( '"total_piezas_recibidas" : "', new.total_piezas_recibidas, '",' )
				),
				IF( new.cantidad_presentacion IS NULL,
					'',
					CONCAT( '"cantidad_presentacion" : "', new.cantidad_presentacion, '",' ) 
				),
				IF( new.cantidad_salida IS NULL,
					'',
					CONCAT( '"cantidad_salida" : "', new.cantidad_salida, '",' )
				),
				IF( new.cantidad_salida_pres IS NULL,
					'',
					CONCAT( '"cantidad_salida_pres" : "', new.cantidad_salida_pres, '",' )
				),
				IF( new.cantidad_entrada IS NULL,
					'',
					CONCAT( '"cantidad_entrada" : "', new.cantidad_entrada, '",' )
				),
				IF( new.cantidad_entrada_pres IS NULL,
					'',
					CONCAT( '"cantidad_entrada_pres" : "', new.cantidad_entrada_pres, '",' )
				),
				IF( new.resolucion IS NULL,
					'',
					CONCAT( '"resolucion" : "', new.resolucion, '",' )
				),
				IF( new.id_proveedor_producto IS NULL,
					'',
					CONCAT( '"id_proveedor_producto" : "', new.id_proveedor_producto, '",' )
				),
				IF( new.agregado_en_surtimiento IS NULL,
					'',
					CONCAT( '"agregado_en_surtimiento" : "', new.agregado_en_surtimiento, '",' )
				),
				IF( new.agregado_en_validacion IS NULL,
					'',
					CONCAT( '"agregado_en_validacion" : "', new.agregado_en_validacion, '",' )
				),
				IF( new.id_caso_surtimiento IS NULL,
					'',
					CONCAT( '"id_caso_surtimiento" : "', new.id_caso_surtimiento, '",' )
				),
				IF( new.numero_consecutivo IS NULL,
					'',
					CONCAT( '"numero_consecutivo" : "', new.numero_consecutivo, '",' )
				),
				IF( new.omite_movimiento_origen IS NULL,
					'',
					CONCAT( '"omite_movimiento_origen" : "', new.omite_movimiento_origen, '",' )
				),
				IF( new.omite_movimiento_destino IS NULL,
					'',
					CONCAT( '"omite_movimiento_destino" : "', new.omite_movimiento_destino, '",' )
				),
				IF( new.resuelto IS NULL,
					'',
					CONCAT( '"resuelto" : "', new.resuelto, '",' )
				),
				IF( new.id_producto_resolucion IS NULL,
					'',
					CONCAT( '"id_producto_resolucion" : "', new.id_producto_resolucion, '",' )
				),
				IF( new.fecha_actualizacion IS NULL,
					'',
					CONCAT( '"fecha_actualizacion" : "', new.fecha_actualizacion, '",' )
				),
				IF( new.folio_unico IS NULL,
					'',
					CONCAT( '"folio_unico" : "', new.folio_unico, '",' )
				),
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaTransferenciaDetalle',
			1
		FROM sys_sucursales 
		WHERE IF( store_id = -1, id_sucursal IN( transfer_origin_store_id, transfer_destinity_store_id ), id_sucursal = -1 );
	END IF;
	SET new.sincronizar = 1;
END $$