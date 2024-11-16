DROP TRIGGER IF EXISTS actualizaProveedorProducto|
DELIMITER $$
CREATE TRIGGER actualizaProveedorProducto
BEFORE UPDATE ON ec_proveedor_producto
FOR EACH ROW
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE id_remision INTEGER(11);
	DECLARE store_id INTEGER;

	DECLARE recorre CURSOR FOR
	SELECT id_oc_recepcion FROM ec_oc_recepcion WHERE id_proveedor=new.id_proveedor AND status=1;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN recorre;
        loop_recorre: LOOP      
       		FETCH recorre INTO id_remision;
       			IF done THEN
               		LEAVE loop_recorre;
           		END IF;
	            UPDATE ec_oc_recepcion_detalle SET precio_pieza=new.precio_pieza WHERE id_producto=new.id_producto AND precio_pieza=0
	            AND id_oc_recepcion IN(id_remision);
        END LOOP;

	CLOSE recorre;

	IF( old.precio_pieza != new.precio_pieza OR old.precio != new.precio )
	THEN
		SET new.fecha_ultima_actualizacion_precio = NOW();
	END IF;

    UPDATE ec_oc_detalle SET precio=new.precio_pieza WHERE id_proveedor_producto=new.id_proveedor_producto 
    AND precio=0 AND id_producto=new.id_producto;
		 
	   	
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
				'"action_type" : "update",',
				'"primary_key" : "id_proveedor_producto",',
				'"primary_key_value" : "', new.id_proveedor_producto, '",',
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
				'"solo_pieza" : "', new.solo_pieza, '",',/* deshabilitado por Oscar 
				'"contador_cajas" : "', new.contador_cajas, '",',
				'"contador_paquetes" : "', new.contador_paquetes, '",',*/
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
			'actualizaProveedorProducto',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
	SET new.sincronizar=1;
   
END $$