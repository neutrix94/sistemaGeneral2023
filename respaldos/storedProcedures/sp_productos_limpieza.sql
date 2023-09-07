DROP PROCEDURE IF EXISTS sp_productos_limpieza|
DELIMITER $$
CREATE PROCEDURE sp_productos_limpieza (IdProducto INT) 
BEGIN 
	SET SQL_SAFE_UPDATES = 0;
	update ec_productos 
	set clave='',orden_lista=0,
	    nombre="Libre",ubicacion_almacen="",
	    nombre_etiqueta="",observaciones="",
	    precio_compra=0,precio_venta_mayoreo=0,
	    es_resurtido=0,id_numero_luces=-1,
	    id_color=-1,id_tamano=-1,
	    id_categoria=39,id_subcategoria=145,
	    id_subtipo=393,es_maquilado=0,
	    id_tipo_producto=1,
	    habilitado = '0'
	where id_productos=IdProducto;

	update ec_estacionalidad_producto
	set minimo=0,medio=0,maximo=0
	where id_producto=IdProducto;

	update sys_sucursales_producto
	set minimo_surtir=0,ubicacion_almacen_sucursal="",
		es_externo=0,estado_suc=0
	where id_producto=IdProducto;

	delete from ec_atributo_producto
	where id_producto=IdProducto;

	delete from ec_productos_venta_cruzada
	where id_producto=IdProducto;

	delete from ec_productos_imagenes_adicionales
	where id_producto=IdProducto;

	delete from ec_productos_relacionados
	where id_producto=IdProducto;

	delete from ec_productos_sim_mas_caros
	where id_producto=IdProducto;

	delete from ec_precios_detalle
	where id_producto=IdProducto;

	delete from ec_producto_tienda_linea
	where id_producto=IdProducto;

	delete from ec_productos_configurables
	where id_producto=IdProducto;

	delete from ec_maquila
	where id_producto=IdProducto;

	delete from ec_productos_detalle
	where id_producto=IdProducto;

	delete from ec_productos_presentaciones
	where id_producto=IdProducto;

	delete from ec_proveedor_producto
	where id_producto=IdProducto;

END $$