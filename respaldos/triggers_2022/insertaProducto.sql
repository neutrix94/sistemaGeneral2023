DROP TRIGGER IF EXISTS insertaProducto|
DELIMITER $$
CREATE TRIGGER insertaProducto
AFTER INSERT ON ec_productos
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);
    DECLARE valor bit(1);
  DECLARE id int(11);
     DECLARE id_dependiente int(11);
  SET valor=new.habilitado;
  SET id=new.id_productos;
  

   INSERT INTO sys_sucursales_producto(id_sucursal,id_producto,minimo_surtir,estado_suc)
   SELECT suc.id_sucursal,id,0,1 from sys_sucursales suc where suc.id_sucursal>0;

   INSERT INTO ec_estacionalidad_producto(id_estacionalidad,id_producto,minimo,medio,maximo)
   SELECT e.id_estacionalidad,id,0,0,0 FROM ec_estacionalidad e;

     SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(id_suc=-1 AND new.sincronizar!=0)
     THEN
      INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos',new.id_productos,1,1,
        CONCAT("INSERT INTO ec_productos (id_productos, clave, nombre, id_categoria, id_subcategoria, ",
                       "precio_venta_mayoreo, precio_compra, marca, min_existencia, imagen, observaciones, ",
                       "inventariado, es_maquilado, genera_iva, genera_ieps, porc_iva, porc_ieps, desc_gral, ",
                       "nombre_etiqueta, orden_lista, ubicacion_almacen, codigo_barras_1, codigo_barras_2, ",
                       "codigo_barras_3, codigo_barras_4, id_subtipo, maximo_existencia, id_numero_luces, ",
                       "id_color, id_tamano, habilitado, omitir_alertas, existencia_media, muestra_paleta, ",
                       "es_resurtido, alta, ultima_modificacion, id_tipo_producto, excluir_factores_por_categoria,\n                       es_ultimas_piezas, sincronizar) VALUES(",
                        new.id_productos,", ",
                        "'",new.clave,"', ",
                        "'",new.nombre,"', ",            
                        "'",new.id_categoria,"',",
                        "'",new.id_subcategoria,"',",          
                        "'",new.precio_venta_mayoreo,"',",
                        "'",new.precio_compra,"',",        
                        "'",new.marca,"',",
                        "'",new.min_existencia,"',",            
                        "'",new.imagen,"',",
                        "'",new.observaciones,"',",          
                        "'",new.inventariado,"',",
                        "'",new.es_maquilado,"',",            
                        "'",new.genera_iva,"',",
                        "'",new.genera_ieps,"',",        
                        "'",new.porc_iva,"',",
                        "'",new.porc_ieps,"',",          
                        "'",new.desc_gral,"',",
                        "'",new.nombre_etiqueta,"',",        
                        "'",new.orden_lista,"',",
                        "'",new.ubicacion_almacen,"',",          
                        "'",new.codigo_barras_1,"',",
                        "'",new.codigo_barras_2,"',",        
                        "'",new.codigo_barras_3,"',",
                        "'",new.codigo_barras_4,"',",        
                        "'",new.id_subtipo,"',",
                        "'",new.maximo_existencia,"',",          
                        "'",new.id_numero_luces,"',",
                        "'",new.id_color,"',",            
                        "'",new.id_tamano,"',",
                        "'",new.habilitado,"',",          
                        "'",new.omitir_alertas,"',",
                        "'",new.existencia_media,"',",            
                        "'",new.muestra_paleta,"',",
                        "'",new.es_resurtido,"',",            
                        "'",new.alta,"',",
                        "'",new.ultima_modificacion,"',",
                        "'",new.id_tipo_producto,"',",
                        "'",new.excluir_factores_por_categoria,"',",
                        "'",new.es_ultimas_piezas,"',",
                        "0)",
                        "___UPDATE ec_productos SET sincronizar=0 WHERE id_productos='",new.id_productos,"'"
        ),
        1,0,CONCAT('Se agrego nuevo producto ',new.nombre),now(),0,0,'id_productos'
        FROM sys_sucursales WHERE id_sucursal>0;
      END IF;
     
  END $$