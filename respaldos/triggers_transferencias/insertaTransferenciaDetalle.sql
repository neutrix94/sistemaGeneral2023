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
				'"id_producto_or" : "', new.id_producto_or, '",',
				'"id_producto_de" : "', new.id_producto_de, '",',
				'"cantidad" : "', new.cantidad, '",',
				'"id_presentacion" : "', new.id_presentacion, '",',
				'"cantidad_cajas" : "', new.cantidad_cajas, '",',
				'"cantidad_paquetes" : "', new.cantidad_paquetes, '",',
				'"cantidad_piezas" : "', new.cantidad_piezas, '",',
				'"cantidad_cajas_surtidas" : "', new.cantidad_cajas_surtidas, '",',
				'"cantidad_paquetes_surtidos" : "', new.cantidad_paquetes_surtidos, '",',
				'"cantidad_piezas_surtidas" : "', new.cantidad_piezas_surtidas, '",',
				'"total_piezas_surtimiento" : "', new.total_piezas_surtimiento, '",',
				'"cantidad_cajas_validacion" : "', new.cantidad_cajas_validacion, '",',
				'"cantidad_paquetes_validacion" : "', new.cantidad_paquetes_validacion, '",',
				'"cantidad_piezas_validacion" : "', new.cantidad_piezas_validacion, '",',
				'"total_piezas_validacion" : "', new.total_piezas_validacion, '",',
				'"cantidad_cajas_recibidas" : "', new.cantidad_cajas_recibidas, '",',
				'"cantidad_paquetes_recibidos" : "', new.cantidad_paquetes_recibidos, '",',
				'"cantidad_piezas_recibidas" : "', new.cantidad_piezas_recibidas, '",',
				'"total_piezas_recibidas" : "', new.total_piezas_recibidas, '",',
				'"cantidad_presentacion" : "', new.cantidad_presentacion, '",',
				'"cantidad_salida" : "', new.cantidad_salida, '",',
				'"cantidad_salida_pres" : "', new.cantidad_salida_pres, '",',
				'"cantidad_entrada" : "', new.cantidad_entrada, '",',
				'"cantidad_entrada_pres" : "', new.cantidad_entrada_pres, '",',
				'"resolucion" : "', new.resolucion, '",',
				/*'"referencia_resolucion" : "', new.referencia_resolucion, '",',
				'"se_queda" : "', new.se_queda, '",',
				'"faltante" : "', new.faltante, '",',
				'"se_regresa" : "', new.se_regresa, '",',
				'"calculo_resolucion" : "', new.calculo_resolucion, '",',*/
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"agregado_en_surtimiento" : "', new.agregado_en_surtimiento, '",',
				'"agregado_en_validacion" : "', new.agregado_en_validacion, '",',
				'"id_caso_surtimiento" : "', new.id_caso_surtimiento, '",',
				'"numero_consecutivo" : "', new.numero_consecutivo, '",',
				/*'"consecutivo_orden_ubicacion" : "', new.consecutivo_orden_ubicacion, '",',*/
				'"omite_movimiento_origen" : "', new.omite_movimiento_origen, '",',
				'"omite_movimiento_destino" : "', new.omite_movimiento_destino, '",',
				'"resuelto" : "', new.resuelto, '",',
				'"id_producto_resolucion" : "', new.id_producto_resolucion, '",',
				'"fecha_actualizacion" : "', new.fecha_actualizacion, '",',
				'"folio_unico" : "', new.folio_unico, '",',
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