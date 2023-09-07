DROP TRIGGER IF EXISTS insertaTransferencia|
DELIMITER $$
CREATE TRIGGER insertaTransferencia
BEFORE INSERT ON ec_transferencias
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE row_id BIGINT;
	DECLARE prefix VARCHAR( 30 );
	
	IF( new.sincronizar = 1 )
	THEN

		SELECT id_sucursal, prefijo INTO store_id, prefix FROM sys_sucursales WHERE acceso=1;
	
		SELECT 
	        auto_increment into row_id
	    FROM information_schema.tables
	    WHERE table_name = 'ec_transferencias'
	    AND table_schema = database();

        SET new.folio_unico = CONCAT( prefix, '_TRNSF_', row_id );
		
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias",',
				'"action_type" : "insert",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				'"id_usuario" : "', new.id_usuario, '",',
				'"folio" : "', new.folio, '",',
				'"fecha" : "', new.fecha, '",',
				'"hora" : "', new.hora, '",',
				'"id_sucursal_origen" : "', new.id_sucursal_origen, '",',
				'"id_sucursal_destino" : "', new.id_sucursal_destino, '",',
				'"observaciones" : "', new.observaciones, '",',
				'"id_razon_social_venta" : "', new.id_razon_social_venta, '",',
				'"id_razon_social_compra" : "', new.id_razon_social_compra, '",',
				'"facturable" : "', new.facturable, '",',
				'"porc_ganancia" : "', new.porc_ganancia, '",',
				'"id_almacen_origen" : "', new.id_almacen_origen, '",',
				'"id_almacen_destino" : "', new.id_almacen_destino, '",',
				'"id_tipo" : "', new.id_tipo, '",',
				'"id_estado" : "', new.id_estado, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
				'"es_resolucion" : "', IF( new.es_resolucion IS NULL, '', new.es_resolucion ), '",',
				'"impresa" : "', new.impresa, '",',
				'"titulo_transferencia" : "', new.titulo_transferencia, '",',
				'"recibiendo_transferencia" : "', new.recibiendo_transferencia, '",',
				'"folio_unico" : "', new.folio_unico,'",',
				'"ultima_sincronizacion" : "', new.ultima_sincronizacion, '",',
				'"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaTransferencia',
			1
		FROM sys_sucursales 
		WHERE IF( store_id = -1, id_sucursal IN( new.id_sucursal_origen, new.id_sucursal_destino ), id_sucursal = -1 );
	END IF;
	SET new.sincronizar = 1;
END $$