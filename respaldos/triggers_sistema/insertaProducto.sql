DROP TRIGGER IF EXISTS insertaProducto|
DELIMITER $$
CREATE TRIGGER insertaProducto
AFTER INSERT ON ec_productos
FOR EACH ROW
BEGIN
  DECLARE store_id INTEGER;
  DECLARE valor bit(1);
  DECLARE id int(11);
  DECLARE id_dependiente int(11);
  SET valor=new.habilitado;
  SET id=new.id_productos;
  

  INSERT INTO sys_sucursales_producto(id_sucursal,id_producto,minimo_surtir,estado_suc)
  SELECT suc.id_sucursal,id,0,1 from sys_sucursales suc where suc.id_sucursal>0;

  INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
  SELECT e.id_estacionalidad,id,0,0,0 FROM ec_estacionalidad e;

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
        '"table_name" : "ec_productos",',
        '"action_type" : "insert",',
        '"primary_key" : "id_productos",',
        '"primary_key_value" : "', new.id_productos, '",',
        '"id_productos" : "', new.id_productos, '",',
        '"clave" : "', IF( new.clave IS NULL, '', new.clave ), '",',
        '"nombre" : "', new.nombre, '",',
        '"id_categoria" : "', new.id_categoria, '",',
        '"id_subcategoria" : "', new.id_subcategoria, '",',
        '"precio_venta_mayoreo" : "', new.precio_venta_mayoreo, '",',
        '"precio_compra" : "', new.precio_compra, '",',
        '"marca" : "', IF( new.marca IS NULL, '', new.marca ), '",',
        '"min_existencia" : "', IF( new.min_existencia IS NULL, '', new.min_existencia ), '",',
        '"imagen" : "', IF( new.imagen IS NULL, '', new.imagen ), '",',
        '"observaciones" : "', IF( new.observaciones IS NULL, '', new.observaciones ), '",',
        '"inventariado" : "', new.inventariado, '",',
        '"es_maquilado" : "', new.es_maquilado, '",',
        '"genera_iva" : "', new.genera_iva, '",',
        '"genera_ieps" : "', new.genera_ieps, '",',
        '"porc_iva" : "', new.porc_iva, '",',
        '"porc_ieps" : "', new.porc_ieps, '",',
        '"desc_gral" : "', new.desc_gral, '",',
        '"nombre_etiqueta" : "', new.nombre_etiqueta, '",',
        '"orden_lista" : "', new.orden_lista, '",',
        '"ubicacion_almacen" : "', new.ubicacion_almacen, '",',
        '"codigo_barras_1" : "', new.codigo_barras_1, '",',
        '"codigo_barras_2" : "', new.codigo_barras_2, '",',
        '"codigo_barras_3" : "', new.codigo_barras_3, '",',
        '"codigo_barras_4" : "', new.codigo_barras_4, '",',
        '"id_subtipo" : "', new.id_subtipo, '",',
        '"maximo_existencia" : "', new.maximo_existencia, '",',
        '"id_numero_luces" : "', new.id_numero_luces, '",',
        '"id_color" : "', new.id_color, '",',
        '"id_tamano" : "', new.id_tamano, '",',
        '"habilitado" : "', new.habilitado, '",',
        '"omitir_alertas" : "', new.omitir_alertas, '",',
        '"existencia_media" : "', new.existencia_media, '",',
        '"muestra_paleta" : "', new.muestra_paleta, '",',
        '"es_resurtido" : "', new.es_resurtido, '",',
        '"alta" : "', new.alta, '",',
        '"ultima_modificacion" : "', new.ultima_modificacion, '",',
        '"sincronizar" : "', new.sincronizar, '",',
        '"id_tipo_producto" : "', new.id_tipo_producto, '",',
        '"excluir_factores_por_categoria" : "', new.excluir_factores_por_categoria, '",',
        '"es_ultimas_piezas" : "', new.es_ultimas_piezas, '"',
        '}'
      ),
      NOW(),
      'insertaProducto',
      1
    FROM sys_sucursales 
    WHERE id_sucursal > 0;
  END IF;
END $$