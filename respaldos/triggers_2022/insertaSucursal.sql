DROP TRIGGER IF EXISTS insertaSucursal|
DELIMITER $$
CREATE TRIGGER insertaSucursal
AFTER INSERT ON sys_sucursales
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
    DECLARE id_alta INT(11);
	DECLARE id_final INT(11);
	DECLARE id_equivalente_user INT(11);

	INSERT INTO sys_sucursales_producto(id_sucursal,id_producto, minimo_surtir,estado_suc) 
	SELECT new.id_sucursal,id_productos, 0,1 FROM ec_productos WHERE id_productos>2;

	INSERT INTO ec_estacionalidad(nombre,id_periodo,id_sucursal,es_alta) VALUES(CONCAT('ALTA ',new.nombre),'1',new.id_sucursal,1);
	SELECT MAX(id_estacionalidad) INTO id_alta FROM ec_estacionalidad;

	INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
	SELECT id_alta,p.id_productos,0,0,0 FROM ec_productos p
	WHERE id_productos>2;

	INSERT INTO ec_estacionalidad(nombre,id_periodo,id_sucursal) VALUES(CONCAT('FINAL ',new.nombre),'1',new.id_sucursal);
	SELECT MAX(id_estacionalidad) INTO id_final FROM ec_estacionalidad;

	INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
	SELECT id_final,p.id_productos,0,0,0 FROM ec_productos p
	WHERE id_productos>2; 

	INSERT INTO ec_caja_o_cuenta_sucursal(id_caja_o_cuenta_sucursal,id_caja_o_cuenta,id_sucursal,estado_suc,ultima_modificacion,sincronizar)
	SELECT null,id_caja_cuenta,new.id_sucursal,0,'0000-00-00 00:00:00',1 FROM ec_caja_o_cuenta WHERE id_caja_cuenta>0;

	INSERT INTO ec_afiliacion_sucursal 
	SELECT null,id_afiliacion,new.id_sucursal,0,1 FROM ec_afiliaciones WHERE id_afiliacion>0;

    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

    SELECT id_equivalente INTO id_equivalente_user FROM sys_users WHERE id_usuario=new.id_encargado;

    IF(id_suc=-1 AND new.sincronizar!=0)
    THEN
    
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_sucursales',new.id_sucursal,1,6,
        CONCAT("INSERT INTO sys_sucursales SET ",
        			"id_sucursal='",new.id_sucursal,"',",
        			"nombre='",new.nombre,"',",
					"telefono='",new.telefono,"',",
					"direccion='",new.direccion,"',",
					"descripcion='",new.descripcion,"',",
					"id_razon_social='",new.id_razon_social,"',",
					"id_encargado='",id_equivalente_user,"',",
					"activo='",new.activo,"',",
					"logo='",new.logo,"',",
					"multifacturacion='",new.multifacturacion,"',",
					"id_precio='",new.id_precio,"',",
					"descuento='",new.descuento,"',",
					"prefijo='",new.prefijo,"',",
					"usa_oferta='",new.usa_oferta,"',",
					"alertas_resurtimiento='",new.alertas_resurtimiento,"',",
					"id_estacionalidad='",new.id_estacionalidad,"',",
					"alta='",new.alta,"',",
					"min_apart='",new.min_apart,"',",
					"dias_resurt='",new.dias_resurt,"',",
					"factor_estacionalidad_minimo='",new.factor_estacionalidad_minimo,"',",
					"factor_estacionalidad_medio='",new.factor_estacionalidad_medio,"',",
					"factor_estacionalidad_final='",new.factor_estacionalidad_final,"',",
					"sincronizar=0,",
					"lista_precios_externa='",new.lista_precios_externa,"',",
					"sufijo_externo='",new.sufijo_externo,"',",
					"almacen_externo='",new.almacen_externo,"',",
					"mostrar_ubicacion='",new.mostrar_ubicacion,"',",
					"verificar_inventario='",new.verificar_inventario,"',",
					"verificar_inventario_externo='",new.verificar_inventario_externo,"',",
					"requiere_info_cliente='",new.requiere_info_cliente,"',",
					"ticket_venta='",new.ticket_venta,"',",
					"ticket_reimpresion='",new.ticket_reimpresion,"',",
					"ticket_apartado='",new.ticket_apartado,"',",
					"permite_transferencias='",new.permite_transferencias,"',",
					"intervalo_sinc='",new.intervalo_sinc,"',",
					"descripcion_sistema='",new.descripcion_sistema,"',",
					"mostrar_alfanumericos='",new.mostrar_alfanumericos,"'",
					"___UPDATE sys_sucursales SET sincronizar=0 WHERE id_sucursal='",new.id_sucursal,"'"
       	),
        1,0,CONCAT('Se agregó nueva sucursal ',new.nombre),now(),0,0,'id_sucursal'
        FROM sys_sucursales WHERE id_sucursal>0 AND id_sucursal!=new.id_sucursal 
        ORDER BY id_sucursal ASC;
    END IF;
END $$