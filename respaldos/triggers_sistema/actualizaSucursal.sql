DROP TRIGGER IF EXISTS actualizaSucursal|
DELIMITER $$
CREATE TRIGGER actualizaSucursal
BEFORE UPDATE ON sys_sucursales
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
	
	IF( old.id_estacionalidad != new.id_estacionalidad )
	THEN
		CALL actualizaEstacionalidadMinimoSurtir( new.id_estacionalidad, new.id_sucursal );
	END IF;
		

	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

	IF( new.sincronizar = 1 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "sys_sucursales",',
				'"action_type" : "update",',
				'"primary_key" : "id_sucursal",',
				'"primary_key_value" : "', new.id_sucursal, '",',
				'"nombre" : "', new.nombre, '",',
				'"telefono" : "', IF( new.telefono IS NULL, '', new.telefono ), '",',
				'"direccion" : "', IF( new.direccion IS NULL, '', new.direccion ), '",',
				'"descripcion" : "', IF( new.descripcion IS NULL, '', new.descripcion ), '",',
				'"id_razon_social" : "', IF( new.id_razon_social IS NULL, '', new.id_razon_social ), '",',
				'"id_encargado" : "', IF( new.id_encargado IS NULL, '', new.id_encargado ), '",',
				'"activo" : "', new.activo, '",',
				'"logo" : "', new.logo, '",',
				'"multifacturacion" : "', new.multifacturacion, '",',
				'"id_precio" : "', IF( new.id_precio IS NULL, '', new.id_precio ), '",',
				'"descuento" : "', IF( new.descuento IS NULL, '', new.descuento ), '",',
				'"prefijo" : "', IF( new.prefijo IS NULL, '', new.prefijo ), '",',
				'"usa_oferta" : "', new.usa_oferta, '",',
				'"alertas_resurtimiento" : "', new.alertas_resurtimiento, '",',
				'"id_estacionalidad" : "', IF( new.id_estacionalidad IS NULL, '', new.id_estacionalidad ), '",',
				'"alta" : "', new.alta, '",',
				'"min_apart" : "', new.min_apart, '",',
				'"dias_resurt" : "', new.dias_resurt, '",',
				'"factor_estacionalidad_minimo" : "', new.factor_estacionalidad_minimo, '",',
				'"factor_estacionalidad_medio" : "', new.factor_estacionalidad_medio, '",',
				'"factor_estacionalidad_final" : "', new.factor_estacionalidad_final, '",',
				'"lista_precios_externa" : "', new.lista_precios_externa, '",',
				'"sufijo_externo" : "', new.sufijo_externo, '",',
				'"almacen_externo" : "', new.almacen_externo, '",',
				'"mostrar_ubicacion" : "', new.mostrar_ubicacion, '",',
				'"verificar_inventario" : "', new.verificar_inventario, '",',
				'"verificar_inventario_externo" : "', new.verificar_inventario_externo, '",',
				'"requiere_info_cliente" : "', new.requiere_info_cliente, '",',
				'"ticket_venta" : "', new.ticket_venta, '",',
				'"ticket_reimpresion" : "', IF( new.ticket_reimpresion IS NULL, '', new.ticket_reimpresion ), '",',
				'"ticket_apartado" : "', new.ticket_apartado, '",',
				'"permite_transferencias" : "', new.permite_transferencias, '",',
				'"descripcion_sistema" : "', new.descripcion_sistema, '",',
				'"intervalo_sinc" : "', new.intervalo_sinc, '",',
				'"mostrar_alfanumericos" : "', new.mostrar_alfanumericos, '",',
				'"razon_social_actual" : "', new.razon_social_actual, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'actualizaSucursal',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
	SET new.sincronizar=1;
END $$