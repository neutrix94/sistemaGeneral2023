DROP TRIGGER IF EXISTS insertaProveedorProducto|
DELIMITER $$
CREATE TRIGGER insertaProveedorProducto
BEFORE INSERT ON ec_proveedor_producto
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	DECLARE new_product_provider_id INT(11);/*estra va a ser el nuevo id proveedor producto*/
	DECLARE current_prefix VARCHAR(2);
/*consultamos el nuevo id de proveedor producto*/
	SELECT ( MAX( id_proveedor_producto ) + 1 ) INTO new_product_provider_id FROM ec_proveedor_producto; 
	SET new.id_proveedor_producto = new_product_provider_id;
/*consulta el prefijo actual*/
	SELECT prefijo_codigos_unicos INTO current_prefix FROM sys_configuracion_sistema LIMIT 1;
	SET new.prefijo_codigos_unicos = current_prefix;
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;
	IF( store_id = -1 AND new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "ec_proveedor_producto",',
				'"action_type" : "insert",',
				'"primary_key" : "id_proveedor_producto",',
				'"primary_key_value" : "', new.id_proveedor_producto, '",',
				'"id_proveedor_producto" : "', new.id_proveedor_producto, '",',
				'"id_proveedor" : "', new.id_proveedor, '",',
				'"id_producto" : "', new.id_producto, '",',
				'"clave_proveedor" : "', IF( new.clave_proveedor IS NULL, '', new.clave_proveedor ), '",',
				'"unidad_medida_pieza" : "', new.unidad_medida_pieza, '",',
				'"precio_pieza" : "', new.precio_pieza, '",',
				'"codigo_barras_pieza_1" : "', new.codigo_barras_pieza_1, '",',
				'"codigo_barras_pieza_2" : "', new.codigo_barras_pieza_2, '",',
				'"codigo_barras_pieza_3" : "', new.codigo_barras_pieza_3, '",',
				'"unidad_medida_presentacion_cluces" : "', new.unidad_medida_presentacion_cluces, '",',
				'"piezas_presentacion_cluces" : "', new.piezas_presentacion_cluces, '",',
				'"codigo_barras_presentacion_cluces_1" : "', new.codigo_barras_presentacion_cluces_1, '",',
				'"codigo_barras_presentacion_cluces_2" : "', new.codigo_barras_presentacion_cluces_2, '",',
				'"unidad_medida_caja" : "', new.unidad_medida_caja, '",',
				'"presentacion_caja" : "', new.presentacion_caja, '",',
				'"codigo_barras_caja_1" : "', new.codigo_barras_caja_1, '",',
				'"codigo_barras_caja_2" : "', new.codigo_barras_caja_2, '",',
				'"precio" : "', new.precio, '",',
				'"solo_pieza" : "', new.solo_pieza, '",',
				'"contador_cajas" : "', new.contador_cajas, '",',
				'"contador_paquetes" : "', new.contador_paquetes, '",',
				'"prefijo_codigos_unicos" : "', new.prefijo_codigos_unicos, '",',
				'"es_modelo_codigo_repetido" : "', new.es_modelo_codigo_repetido, '",',
				'"fecha_alta" : "', new.fecha_alta, '",',
				'"ultima_actualizacion" : "', new.ultima_actualizacion, '",',
				'"prioridad_surtimiento" : "', IF( new.prioridad_surtimiento IS NULL, '', new.prioridad_surtimiento ), '",',
				'"id_usuario_modifica" : "', new.id_usuario_modifica, '",',
				'"pantalla_modificacion" : "', new.pantalla_modificacion, '",',
				'"fecha_ultima_actualizacion_precio" : "', new.fecha_ultima_actualizacion_precio, '",',
				'"fecha_ultima_compra" : "', IF( new.fecha_ultima_compra IS NULL, '', new.fecha_ultima_compra ), '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaProveedorProducto',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;

	INSERT INTO ec_inventario_proveedor_producto 
		( id_producto, id_proveedor_producto, id_sucursal, id_almacen, inventario, fecha_registro, ultima_actualizacion )
	SELECT 
		new.id_producto,
		new.id_proveedor_producto,
		alm.id_sucursal,
		alm.id_almacen,
		0,
		NOW(),
		'0000-00-00 00:00:00'
	FROM ec_almacen alm
	WHERE alm.id_almacen > 0;
END $$