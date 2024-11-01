DROP TRIGGER IF EXISTS actualizaTransferencia|
DELIMITER $$
CREATE TRIGGER actualizaTransferencia
BEFORE UPDATE ON ec_transferencias
FOR EACH ROW
BEGIN
/*verificado 2024-10-22*/
	DECLARE store_id INTEGER;
	DECLARE idTransfer INT(11);
	DECLARE estado INT(11);
	DECLARE movAlmacen BIGINT;
	DECLARE sucActual INT(11);
	DECLARE transfer_type INT(11);
	DECLARE permiso_transfer INT(11);
	DECLARE row_counter INT(11);

	/*DECLARE var_id_tipo_movimiento INTEGER;
	DECLARE var_id_usuario INTEGER; 
	DECLARE var_id_sucursal INTEGER; 
	DECLARE var_fecha DATE; 
	DECLARE var_hora TIME; 
	DECLARE var_id_transferencia INTEGER; 
	DECLARE var_id_almacen INTEGER;*/

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	SELECT
		id_sucursal,
		permite_transferencias
	INTO 
		sucActual,
		permiso_transfer
	FROM sys_sucursales WHERE acceso=1;
	SELECT
		id_tipo
	INTO 
		transfer_type
	FROM ec_transferencias
	WHERE id_transferencia = old.id_transferencia;

	SET idTransfer=old.id_transferencia;
	SET estado=new.id_estado;
	IF(new.id_estado=2 AND new.id_estado!=old.id_estado AND (sucActual=-1 OR permiso_transfer=1))
	THEN
		UPDATE ec_transferencia_productos SET cantidad_salida=cantidad,
		cantidad_salida_pres=cantidad_presentacion WHERE id_transferencia=idTransfer;
	END IF;

	IF( new.id_estado=9 AND new.id_estado!=old.id_estado AND (sucActual=-1 OR permiso_transfer=1) )
	THEN
		SELECT COUNT( * ) INTO row_counter FROM ec_transferencia_productos WHERE id_transferencia=idTransfer
		AND omite_movimiento_destino = 0;
	END IF;

	IF( new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros_transferencias ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_transferencias",',
				'"action_type" : "update",',
				'"primary_key" : "folio_unico",',
				'"primary_key_value" : "', new.folio_unico, '",',
				IF( new.id_usuario IS NULL, 
					'',
					CONCAT( '"id_usuario" : "', new.id_usuario, '",' )
				),
				IF( new.folio IS NULL, 
					'',
					CONCAT( '"folio" : "', new.folio, '",' )
				),
				IF( new.fecha IS NULL,
					'', 
					CONCAT( '"fecha" : "', new.fecha, '",' )
				),
				IF( new.hora IS NULL,
					'', 
					CONCAT( '"hora" : "', new.hora, '",' )
				),
				IF( new.id_sucursal_origen IS NULL,
					'', 
					CONCAT( '"id_sucursal_origen" : "', new.id_sucursal_origen, '",' )
				),
				IF( new.id_sucursal_destino IS NULL,
					'', 
					CONCAT( '"id_sucursal_destino" : "', new.id_sucursal_destino, '",' )
				),
				IF( new.observaciones IS NULL,
					'', 
					CONCAT( '"observaciones" : "', new.observaciones, '",' )
				),
				IF( new.id_razon_social_venta IS NULL,
					'', 
					CONCAT( '"id_razon_social_venta" : "', new.id_razon_social_venta, '",' )
				),
				IF( new.id_razon_social_compra IS NULL,
					'', 
					CONCAT( '"id_razon_social_compra" : "', new.id_razon_social_compra, '",' )
				),
				IF( new.facturable IS NULL,
					'', 
					CONCAT( '"facturable" : "', new.facturable, '",' )
				),
				IF( new.porc_ganancia IS NULL,
					'', 
					CONCAT( '"porc_ganancia" : "', new.porc_ganancia, '",' )
				),
				IF( new.id_almacen_origen IS NULL,
					'', 
					CONCAT( '"id_almacen_origen" : "', new.id_almacen_origen, '",' )
				),
				IF( new.id_almacen_destino IS NULL,
					'', 
					CONCAT( '"id_almacen_destino" : "', new.id_almacen_destino, '",' )
				),
				IF( new.id_tipo IS NULL,
					'', 
					CONCAT( '"id_tipo" : "', new.id_tipo, '",' )
				),
				IF( new.id_estado IS NULL,
					'', 
					CONCAT( '"id_estado" : "', new.id_estado, '",' )
				),
				IF( new.id_sucursal IS NULL,
					'', 
					CONCAT( '"id_sucursal" : "', new.id_sucursal, '",' )
				),
				IF( new.es_resolucion IS NULL,
					'', 
					CONCAT( '"es_resolucion" : "', new.es_resolucion, '",' )
				),
				IF( new.impresa IS NULL,
					'', 
					CONCAT( '"impresa" : "', new.impresa, '",' )
				),
				IF( new.titulo_transferencia IS NULL,
					'', 
					CONCAT( '"titulo_transferencia" : "', new.titulo_transferencia, '",' )
				),
				IF( new.recibiendo_transferencia IS NULL,
					'', 
					CONCAT( '"recibiendo_transferencia" : "', new.recibiendo_transferencia, '",' )
				),
				IF( new.ultima_sincronizacion IS NULL,
					'', 
					CONCAT( '"ultima_sincronizacion" : "', new.ultima_sincronizacion, '",' )
				),
				IF( new.ultima_actualizacion IS NULL,
					'', 
					CONCAT( '"ultima_actualizacion" : "', new.ultima_actualizacion, '",' )
				),
				IF( new.folio_unico IS NULL,
					'', 
					CONCAT( '"folio_unico" : "', new.folio_unico, '",' )
				),
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaTransferencia',
			1
		FROM sys_sucursales 
		WHERE IF( store_id = -1, id_sucursal IN( new.id_sucursal_origen, new.id_sucursal_destino ), id_sucursal = -1 );
	END IF;
	SET new.sincronizar = 1;
END $$