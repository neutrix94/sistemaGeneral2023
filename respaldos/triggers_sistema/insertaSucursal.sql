DROP TRIGGER IF EXISTS insertaSucursal|
DELIMITER $$
CREATE TRIGGER insertaSucursal
AFTER INSERT ON sys_sucursales
FOR EACH ROW
BEGIN
	DECLARE store_id INTEGER;
    DECLARE id_alta INT(11);
	DECLARE id_final INT(11);
	DECLARE id_equivalente_user INT(11);
	DECLARE folder_name VARCHAR(100);

/*Consulta tipo de sistema*/
	SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso=1;

	IF( store_id = -1 AND new.sincronizar != 0 )
	THEN
		INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio,
		id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
		SELECT 
			NULL,
			store_id,
			id_sucursal,
			CONCAT('{',
				'"table_name" : "sys_sucursales",',
				'"action_type" : "insert",',
				'"primary_key" : "id_sucursal",',
				'"primary_key_value" : "', new.id_sucursal, '",',
				'"id_sucursal" : "', new.id_sucursal, '",',
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
				'"habilitar_smartaccounts_netpay" : "', new.habilitar_smartaccounts_netpay, '",',
				'"sincronizar" : "0"',
				'}'
			),
			NOW(),
			'insertaSucursal',
			1
		FROM sys_sucursales 
		WHERE id_sucursal > 0;
	END IF;
/*inserta productos por sucursal*/
	INSERT INTO sys_sucursales_producto(id_sucursal,id_producto, minimo_surtir,estado_suc) 
	SELECT new.id_sucursal,id_productos, 0,1 FROM ec_productos WHERE id_productos>2;
/*inserta productos por estacionalidad alta*/
	INSERT INTO ec_estacionalidad(nombre,id_periodo,id_sucursal,es_alta) VALUES(CONCAT('ALTA ',new.nombre),'1',new.id_sucursal,1);
	SELECT MAX(id_estacionalidad) INTO id_alta FROM ec_estacionalidad;
	INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
	SELECT id_alta,p.id_productos,0,0,0 FROM ec_productos p
	WHERE id_productos>2;
/*inserta productos de estacionalidad final*/
	INSERT INTO ec_estacionalidad(nombre,id_periodo,id_sucursal) VALUES(CONCAT('FINAL ',new.nombre),'1',new.id_sucursal);
	SELECT MAX(id_estacionalidad) INTO id_final FROM ec_estacionalidad;
	INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
	SELECT id_final,p.id_productos,0,0,0 FROM ec_productos p
	WHERE id_productos>2; 
/*inserta cajas o cuentas por sucursal*/
	INSERT INTO ec_caja_o_cuenta_sucursal(id_caja_o_cuenta_sucursal,id_caja_o_cuenta,id_sucursal,estado_suc,ultima_modificacion,sincronizar)
	SELECT null,id_caja_cuenta,new.id_sucursal,0,'0000-00-00 00:00:00',1 FROM ec_caja_o_cuenta WHERE id_caja_cuenta>0;
/*inserta afiliaciones por sucursal*/
	INSERT INTO ec_afiliacion_sucursal 
	SELECT null,id_afiliacion,new.id_sucursal,0,1 FROM ec_afiliaciones WHERE id_afiliacion>0;
/*inserta terminales por sucursal*/
	INSERT INTO ec_terminales_sucursales_smartaccounts ( id_terminal_sucursal, id_terminal, id_sucursal, estado_suc, id_razon_social, sincronizar )
	SELECT null, id_caja_cuenta, new.id_sucursal, 0, -1, 1 FROM ec_terminales_integracion_smartaccounts WHERE id_terminal_integracion>0;
/*inserta la configuracion adicional de la sucursal*/
	INSERT INTO ec_configuracion_sucursal ( id_sucursal, no_tickets_resolucion, sucursal_impresion_local, ofrecer_productos, dominio_sucursal, endpoint_impresion_remota, tiempo_vida_token_clientes ) 
	VALUES ( new.id_sucursal, 1, new.id_sucursal, 1, '', '', 3600 );
/*inserta carpetas por sucursal*/
	SELECT ( REPLACE( ( SELECT nombre FROM sys_sucursales WHERE id_sucursal = new.id_sucursal ), ' ', '_' ) ) INTO folder_name;/*forma nombre de la carpeta*/
	INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion ) 
		VALUES ( new.id_sucursal, 'path_sucursal', new.id_sucursal, 'cache', folder_name, NOW() );/*inserta carpeta principal*/
	INSERT INTO sys_carpetas ( id_carpeta, tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion ) 
		VALUES ( ( 100 + new.id_sucursal ), 'carpeta_generica', new.id_sucursal, CONCAT( 'cache/', folder_name ), 'ticket', NOW() );/*inserta carpeta generica de ticket*/
	INSERT INTO sys_carpetas ( tipo_carpeta, id_sucursal, `path`, nombre_carpeta, fecha_creacion ) 
	SELECT
		'modulo',
		new.id_sucursal,
		CONCAT( 'cache/', folder_name ),
		ax.nombre_carpeta, 
		NOW()
	FROM(
		SELECT 
			c.nombre_carpeta, 
			NOW()
		FROM sys_carpetas c
		WHERE c.tipo_carpeta = 'modulo' 
		AND c.id_sucursal = 1
	)ax;/*inserta carpetas de modulo*/
/*inserta modulos por sucursal*/
	INSERT INTO sys_modulos_impresion_sucursales ( id_sucursal, id_modulo_impresion )
	SELECT
		ax.id_sucursal,
		ax.id_modulo_impresion
	FROM(
		SELECT
			new.id_sucursal,
			mis.id_modulo_impresion
		FROM sys_modulos_impresion_sucursales mis
		WHERE mis.id_sucursal = 1
	)ax;
/*inserta configuracion de plantillas de etiquetas por sucursal*/
	INSERT INTO sys_sucursales_plantillas_etiquetas ( id_sucursal, id_plantilla, tipo_codigo_plantilla, ruta_destino, habilitado  )
	SELECT
		ax.id_sucursal,
		ax.id_plantilla,
		ax.tipo_codigo_plantilla,
		0,
		1
	FROM(
		SELECT
			new.id_sucursal,
			spe.id_plantilla,
			spe.tipo_codigo_plantilla
		FROM sys_sucursales_plantillas_etiquetas spe
		WHERE spe.id_sucursal = 1
	)ax;
END $$