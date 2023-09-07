DROP TRIGGER IF EXISTS actualizaTransferencia|
DELIMITER $$
CREATE TRIGGER actualizaTransferencia
BEFORE UPDATE ON ec_transferencias
FOR EACH ROW
BEGIN
/*verificado 13-07-2023*/
	DECLARE store_id INTEGER;
	DECLARE idTransfer INT(11);
	DECLARE estado INT(11);
	DECLARE movAlmacen BIGINT;
	DECLARE sucActual INT(11);
	DECLARE transfer_type INT(11);
	DECLARE permiso_transfer INT(11);
	DECLARE row_counter INT(11);

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
		SELECT COUNT( * ) INTO row_counter FROM ec_transferencia_productos WHERE id_transferencia=idTransfer
		AND omite_movimiento_origen = 0;
	IF( row_counter > 0 )
	THEN
	INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, 
		id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
	SELECT 6,t.id_usuario, t.id_sucursal_origen, NOW(), NOW(), 'SALIDA DE TRANSFERENCIA', -1, 
	-1, '', -1,t.id_transferencia, t.id_almacen_origen
	FROM ec_transferencias t where t.id_transferencia=idTransfer;

	SELECT MAX(id_movimiento_almacen) INTO movAlmacen FROM ec_movimiento_almacen;

	UPDATE ec_transferencia_productos SET cantidad_salida=cantidad,
	cantidad_salida_pres=cantidad_presentacion WHERE id_transferencia=idTransfer;

	INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida,
	id_pedido_detalle, id_oc_detalle, id_proveedor_producto)
	SELECT movAlmacen,tP.id_producto_or,tP.cantidad,tP.cantidad,-1,-1, tP.id_proveedor_producto
	FROM ec_transferencia_productos tP
	WHERE tP.id_transferencia=idTransfer
	AND tP.omite_movimiento_origen = 0;
	END IF;
	END IF;

	IF( new.id_estado=9 AND new.id_estado!=old.id_estado AND (sucActual=-1 OR permiso_transfer=1) )
	THEN
		SELECT COUNT( * ) INTO row_counter FROM ec_transferencia_productos WHERE id_transferencia=idTransfer
		AND omite_movimiento_destino = 0;
		IF( row_counter > 0 )
		THEN
			INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora,
			observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
			SELECT 5,t.id_usuario, t.id_sucursal_destino, NOW(), NOW(), 'ENTRADA DE TRANSFERENCIA',
			-1, -1, '', -1,t.id_transferencia, t.id_almacen_destino
			FROM ec_transferencias t where t.id_transferencia=idTransfer;

			SELECT MAX(id_movimiento_almacen) INTO movAlmacen FROM ec_movimiento_almacen;

			INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida,
			id_pedido_detalle, id_oc_detalle, id_proveedor_producto, id_equivalente )
			SELECT movAlmacen,tP.id_producto_or,tP.total_piezas_recibidas,tP.total_piezas_recibidas,-1,-1,tP.id_proveedor_producto,0
			FROM ec_transferencia_productos tP
			WHERE tP.id_transferencia=idTransfer
			AND tP.total_piezas_recibidas != 0
			AND tP.omite_movimiento_destino = 0;
		END IF;
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
				'"ultima_sincronizacion" : "', new.ultima_sincronizacion, '",',
				'"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
				'"folio_unico" : "', new.folio_unico, '",',
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