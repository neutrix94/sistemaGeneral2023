DROP TRIGGER IF EXISTS actualizaSucursal|
DELIMITER $$
CREATE TRIGGER actualizaSucursal
BEFORE UPDATE ON sys_sucursales
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
	DECLARE id_equivalente_user INT(11);
	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	SELECT id_equivalente INTO id_equivalente_user FROM sys_users WHERE id_usuario=new.id_encargado;
	
	IF( old.id_estacionalidad != new.id_estacionalidad )
	THEN
		CALL actualizaEstacionalidadMinimoSurtir( new.id_estacionalidad, new.id_sucursal );
	END IF;
	
	IF(new.sincronizar=1)
	  THEN
		INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_sucursales',new.id_sucursal,2,6,
		      CONCAT("UPDATE sys_sucursales SET ",
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
		"mostrar_alfanumericos='",new.mostrar_alfanumericos,"' WHERE id_sucursal=",new.id_sucursal
		     ),
		      0,0,CONCAT('Se actualizó la sucursal ',old.nombre),now(),0,0,'id_sucursal'
		FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
	END IF;
	SET new.sincronizar=1;
END $$