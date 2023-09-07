DROP TRIGGER IF EXISTS actualizaProducto|
DELIMITER $$
CREATE TRIGGER actualizaProducto
BEFORE UPDATE ON ec_productos
FOR EACH ROW
BEGIN
   DECLARE id_suc INT(11);
   DECLARE id INT(11);
   SET id=new.id_productos;

   SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

   IF(id_suc=-1 AND new.sincronizar!=0)
   THEN
      INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos',new.id_productos,2,1,
         CONCAT("UPDATE ec_productos SET \n                        id_productos=",new.id_productos,",",
                         "clave='",new.clave,"',",
                         "nombre='",new.nombre,"',",            
                         "id_categoria='",new.id_categoria,"',",
                         "id_subcategoria='",new.id_subcategoria,"',",          
                         "precio_venta_mayoreo='",new.precio_venta_mayoreo,"',",
                         "precio_compra='",new.precio_compra,"',",        
                         "marca='",new.marca,"',",
                         "min_existencia='",new.min_existencia,"',",            
                         "imagen='",new.imagen,"',",
                         "observaciones='",new.observaciones,"',",          
                         "inventariado='",new.inventariado,"',",
                         "es_maquilado='",new.es_maquilado,"',",            
                         "genera_iva='",new.genera_iva,"',",
                         "genera_ieps='",new.genera_ieps,"',",        
                         "porc_iva='",new.porc_iva,"',",
                         "porc_ieps='",new.porc_ieps,"',",          
                         "desc_gral='",new.desc_gral,"',",
                         "nombre_etiqueta='",new.nombre_etiqueta,"',",        
                         "orden_lista='",new.orden_lista,"',",
                         "ubicacion_almacen='",new.ubicacion_almacen,"',",          
                         "codigo_barras_1='",new.codigo_barras_1,"',",
                         "codigo_barras_2='",new.codigo_barras_2,"',",        
                         "codigo_barras_3='",new.codigo_barras_3,"',",
                         "codigo_barras_4='",new.codigo_barras_4,"',",        
                         "id_subtipo='",new.id_subtipo,"',",
                         "maximo_existencia='",new.maximo_existencia,"',",          
                         "id_numero_luces='",new.id_numero_luces,"',",
                         "id_color='",new.id_color,"',",            
                         "id_tamano='",new.id_tamano,"',",
                         "habilitado='",new.habilitado,"',",          
                         "omitir_alertas='",new.omitir_alertas,"',",
                         "existencia_media='",new.existencia_media,"',",            
                         "muestra_paleta='",new.muestra_paleta,"',",
                         "es_resurtido='",new.es_resurtido,"',",            
                         "alta='",new.alta,"',",
                         "ultima_modificacion='",new.ultima_modificacion,"',",

                         "id_tipo_producto='",new.id_tipo_producto,"',",
                         "excluir_factores_por_categoria='",new.excluir_factores_por_categoria,"',",
                         "es_ultimas_piezas='",new.es_ultimas_piezas,"',",
                         
                         "sincronizar=0 WHERE id_productos=",new.id_productos
         ),
         0,0,CONCAT('Se actualizó producto ',new.nombre),now(),0,0,'id_productos'
         FROM sys_sucursales WHERE id_sucursal>0;
    END IF;
     SET new.sincronizar=1;

     IF(new.habilitado!=old.habilitado AND new.habilitado=0)
     THEN
         UPDATE sys_sucursales_producto SET estado_suc=new.habilitado,sincronizar=0 WHERE id_producto=new.id_productos;
     END IF;

     IF(new.habilitado!=old.habilitado AND new.habilitado=1)
     THEN
         UPDATE sys_sucursales_producto SET estado_suc=new.habilitado,sincronizar=0 WHERE id_producto=new.id_productos AND id_sucursal=1;
     END IF;
  END $$