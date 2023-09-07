<?php
/*
  Creado por: AF
  Fecha: 2020-10-10
  Funcionalidad: Proceso para para actualizar cantidad de producto en Magento
*/

  // Instancia database and object files
  require "../classes/database.php";
  $db = new db();
  $db = $db->conectDB();

  #####################################
  ##  Sincroniza productos a Magento
  #####################################
  try{
    error_log('Log CL - Job Init: Actualización productos en Magento');
    //Actualiza producto: Procesando
    // $updateProductoBK="update ec_sync_magento set estatus=1 where tipo='Producto' and estatus=2;";
    // $updateStmtBK = $db->prepare($updateProductoBK);

    try {
      //Recupera skus por procesar
      // $updateProducto="update ec_sync_magento set estatus=2 where tipo='Producto' and estatus=1 limit 50;";
      // $updateStmt = $db->prepare($updateProducto);
      $consultaSKU = "select group_concat(id_producto) skus
          from
          (
            select distinct id_registro id_producto, 'Producto' tipo
            from ec_sync_magento syncP
            where syncP.tipo='Producto'
            and syncP.estatus=1
            /*union
            select distinct id_producto, sync.tipo
            from ec_productos_configurables conf
            inner join ec_sync_magento sync on sync.id_registro=conf.id_producto_configurable
            where sync.tipo='Producto'
            and sync.estatus=1*/
          ) skus
          group by tipo
          ;";
      $skusProcesar = $db->query($consultaSKU);
      $resultRow = $skusProcesar->fetch();
      $skuList = $resultRow['skus'];
      error_log('Log CL - Job SKUS: '. $skuList);
      // $updateStmtBK->execute();
      // $updateStmt->execute();
    }catch (PDOException $e) {
      error_log('Error integación Magento: ' . $e->getMessage());
    }

    if (!empty($skuList)) {
      //Recupera productos: Por procesar
      $consultaProductoMagento="SELECT prod.id_productos sku,'Default' attribute_set_code,tProd.tipo_tienda_linea product_type,
              CONCAT('Default Category/',cat.nombre,'/',sub.nombre,
            	CASE WHEN subt.id_subtipos=-1 then '' ELSE CASE WHEN prod.id_subcategoria=10 THEN '' ELSE '/' END END,
                CASE WHEN subt.id_subtipos=-1 then '' ELSE CASE WHEN prod.id_subcategoria=10 THEN '' ELSE subt.nombre END END) categories,
              'base' product_websites,prod.nombre as name,
              IFNULL(prodTL.descripcion,'') description,IFNULL(prodTL.breve_descripcion,'') short_description,
              IFNULL(Peso.Valor,'') weight,IFNULL(altoEmpaque.Valor,'') alto_empaque,IFNULL(largoEmpaque.Valor,'') largo_empaque,
              IFNULL(anchoEmpaque.Valor,'') ancho_empaque,
              prodTL.habilitado_magento as 'product_online','None' tax_class_name,
              CASE WHEN prod.id_productos in (2621,2620,2619,2239) THEN '1' ELSE '4' END visibility,IFNULL(prodPrecioCantMinima.precio_venta,0) price,
              IFNULL(prodTL.monto_precio_especial,'') special_price,IFNULL(prodTL.precio_especial_desde,'') special_price_from_date,
              IFNULL(prodTL.precio_especial_hasta,'') special_price_to_date,IFNULL(prodTL.producto_nuevo_desde,'') new_from_date,
              IFNULL(prodTL.producto_nuevo_hasta,'') new_to_dato,IFNULL(prodTL.MetaTitulo,'')meta_title,IFNULL(prodTL.MetaDescripcion,'') meta_description,
              IFNULL(prodTL.Palabras_clave_busqueda,'') meta_keywords,IFNULL(prodTL.imagen_principal,'') base_image,IFNULL(prodTL.imagen_principal,'') small_image,
              IFNULL(prodTL.imagen_principal,'') thumbnail_image,  IFNULL(prodTL.imagen_principal,'') swatch_image,
              IFNULL(ImagenesAdicionales.ImagenesAdi,'') additional_images,  CASE WHEN color.id_colores=-1 THEN '' ELSE color.nombre END color,
              IFNULL(colorCable.Valor,'') color_de_cable,IFNULL(ProductosRelacionados,'') related_skus,
              IFNULL(ventaCruzada,'') crosssell_skus,IFNULL(SimilaresMasCaros,'') upsell_skus,
              CASE WHEN tProd.id_tipo_producto=2 THEN prodConfVar.VariacionesConfigurables ELSE '' END configurable_variations,
              prod.orden_lista orden_lista,
              IFNULL(CASE WHEN nl.id_numero_luces=-1 THEN '' ELSE nl.nombre END,'') numero_luces,
              IFNULL(CASE WHEN tam.id_tamanos=-1 THEN '' ELSE tam.nombre END,'') tamanio,
              IFNULL(codSAT.codigo_sat,'') codigo_sat,
              IFNULL(prodTL.stock_minimo,'') out_of_stock_qty,1 is_in_stock,prod.clave clave_provedor,
              CASE WHEN tProd.id_tipo_producto=3 THEN 'fixed' ELSE '' END bundle_price_type,
              CASE WHEN tProd.id_tipo_producto=3 THEN 'dynamic' ELSE '' END bundle_sku_type,
              CASE WHEN tProd.id_tipo_producto=3 THEN 'price range' ELSE '' END bundle_price_view,
              CASE WHEN tProd.id_tipo_producto=3 THEN 'dynamic' ELSE '' END bundle_weight_type,
              CASE WHEN tProd.id_tipo_producto=3 THEN prodAgrupado.productosAgrupados ELSE '' END bundle_values,
              CASE WHEN tProd.id_tipo_producto=3 THEN 'Together' ELSE '' END bundle_shipment_type,
              prod.ubicacion_almacen ubicacion_matriz,
              CASE WHEN tProd.id_tipo_producto<>2 THEN CASE WHEN alm.inventario<0 THEN 0 ELSE alm.inventario END ELSE '' END qty,
              CASE WHEN tProd.id_tipo_producto<>2 THEN IFNULL(prodPrecioCantMinima.cantidadMinima,0) ELSE '' END min_cart_qty
            FROM ec_productos prod
              INNER JOIN ec_tipos_producto tProd ON prod.id_tipo_producto=tProd.id_tipo_producto
              LEFT JOIN ec_productos_configurables prodConf ON prod.id_productos=prodConf.id_producto_configurable
              INNER JOIN ec_categoria cat ON prod.id_categoria=cat.id_categoria
              INNER JOIN ec_subcategoria sub ON prod.id_subcategoria=sub.id_subcategoria
              INNER JOIN ec_subtipos subt ON prod.id_subtipo=subt.id_subtipos
              LEFT JOIN ec_producto_tienda_linea prodTL on prod.id_productos=prodTL.id_producto
              LEFT JOIN ec_colores color ON prod.id_color=color.id_colores
              LEFT JOIN ec_numero_luces nl ON prod.id_numero_luces=nl.id_numero_luces
              LEFT JOIN ec_admin_codigos_sat codSAT ON prod.id_subcategoria=codSAT.id_subcategoria
              LEFT JOIN ec_tamanos tam ON prod.id_tamano=tam.id_tamanos
              LEFT JOIN ec_almacen_producto alm ON prod.id_productos=alm.id_producto and id_almacen=1
              LEFT JOIN (SELECT prodConf.id_producto, GROUP_CONCAT(CONCAT('sku=',prodConf.id_producto_configurable,',color=',color.nombre)SEPARATOR '|') VariacionesConfigurables
                        FROM ec_productos_configurables prodConf
                          INNER JOIN ec_producto_tienda_linea prodTL ON prodConf.id_producto_configurable=prodTL.id_producto
                          INNER JOIN ec_productos prod ON prodTL.id_producto=prod.id_productos
                          INNER JOIN ec_colores color ON prod.id_color=color.id_colores
                        WHERE prodTL.habilitado=1
                        GROUP BY prodConf.id_producto) prodConfVar ON prod.id_productos=prodConfVar.id_producto
              LEFT JOIN (SELECT prodAgru.id_producto,group_concat(CONCAT('name=',cat.nombre,',type=select,required=1,sku=',prodAgru.id_producto_ordigen,
            									   ',price=0,default=0,default_qty=',prodAgru.cantidad,',price_type=fixed,can_change_qty=0')SEPARATOR '|') productosAgrupados
            			FROM ec_productos_detalle prodAgru
            			  INNER JOIN ec_producto_tienda_linea prodTL ON prodAgru.id_producto_ordigen=prodTL.id_producto
                          INNER JOIN ec_productos prod ON prodTL.id_producto=prod.id_productos
                          INNER JOIN ec_categoria cat ON prod.id_categoria=cat.id_categoria
                        WHERE prodTL.habilitado=1
            			GROUP BY prodAgru.id_producto) prodAgrupado ON prod.id_productos=prodAgrupado.id_producto
              LEFT JOIN (SELECT prodVC.id_producto,GROUP_CONCAT(CONCAT(prodVC.id_producto_vc)) ventaCruzada
                        FROM ec_productos_venta_cruzada prodVC
                        GROUP BY prodVC.id_producto) prodVCruzada ON prod.id_productos=prodVCruzada.id_producto
              LEFT JOIN (SELECT prodSMC.id_producto,CAST(GROUP_CONCAT(prodSMC.id_producto_sim_mas_caro) as char) SimilaresMasCaros
                        FROM ec_productos_sim_mas_caros prodSMC
                        GROUP BY prodSMC.id_producto) prodSMCaros ON prod.id_productos=prodSMCaros.id_producto
              LEFT JOIN (SELECT prodRel.id_producto,CAST(GROUP_CONCAT(prodRel.id_producto_relacionado) as char) ProductosRelacionados
                        FROM ec_productos_relacionados prodRel
                        GROUP BY prodRel.id_producto) prodRelacionados ON prod.id_productos=prodRelacionados.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo like 'Peso%') Peso ON prod.id_productos=Peso.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Alto Empaque%') altoEmpaque ON prod.id_productos=altoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Largo Empaque%') largoEmpaque ON prod.id_productos=largoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Ancho Empaque%') anchoEmpaque ON prod.id_productos=anchoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE '%color%cable%') colorCable ON prod.id_productos=colorCable.id_producto
              LEFT JOIN (SELECT id_producto,GROUP_CONCAT(nombre_completo) ImagenesAdi
            			FROM ec_productos_imagenes_adicionales
                        GROUP BY id_producto) ImagenesAdicionales ON prod.id_productos=ImagenesAdicionales.id_producto
              LEFT JOIN (select prodCantMin.id_producto,cantidadMinima,predet.precio_venta
            			FROM ec_precios_detalle predet
                          INNER JOIN ec_precios pre on pre.id_precio=predet.id_precio and grupo_cliente_magento='Mostrador'
                          INNER JOIN (SELECT id_producto, min(de_valor) cantidadMinima
            						 FROM ec_precios pre
            						   INNER JOIN ec_precios_detalle preDet ON pre.id_precio=preDet.id_precio
            						   WHERE grupo_cliente_magento='Mostrador'
            						   GROUP BY id_producto) prodCantMin
            			    ON predet.id_producto=prodCantMin.id_producto and predet.de_valor=prodCantMin.cantidadMinima) prodPrecioCantMinima
            			ON prod.id_productos=prodPrecioCantMinima.id_producto
            WHERE prodConf.id_producto_configurable IS NULL AND prodTL.habilitado=1 AND prod.id_productos in ({$skuList})/*AND  prod.id_tipo_producto=2 AND prod.id_productos in (2349)*/
               /* AND CASE WHEN prod.id_tipo_producto=1 THEN IFNULL(Peso.Valor,'') ELSE 1 END <> CASE WHEN prod.id_tipo_producto=1 THEN '' ELSE 2 END
                AND CASE WHEN prod.id_tipo_producto=1 THEN IFNULL(prodPrecioCantMinima.precio_venta,0) ELSE 1 END <> CASE WHEN prod.id_tipo_producto=1 THEN 0 ELSE 2 END*/

            UNION ALL
            /* Productos Configurables Detalle */
            SELECT prod.id_productos sku,'Default' attribute_set_code,tProd.tipo_tienda_linea product_type,
              CONCAT('Default Category/',cat.nombre,'/',sub.nombre,
            	CASE WHEN subt.id_subtipos=-1 then '' ELSE CASE WHEN prod.id_subcategoria=10 THEN '' ELSE '/' END END,
                CASE WHEN subt.id_subtipos=-1 then '' ELSE CASE WHEN prod.id_subcategoria=10 THEN '' ELSE subt.nombre END END) categories,
              'base' product_websites,prod.nombre as name,
              IFNULL(prodTL.descripcion,'') description,IFNULL(prodTL.breve_descripcion,'') short_description,
              IFNULL(Peso.Valor,'') weight,IFNULL(altoEmpaque.Valor,'') alto_empaque,IFNULL(largoEmpaque.Valor,'') largo_empaque,
              IFNULL(anchoEmpaque.Valor,'') ancho_empaque,
              prodTL.habilitado_magento as product_online,'None' tax_class_name,'Not Visible Individually' visibility,IFNULL(prodPrecioCantMinima.precio_venta,0) price,
              IFNULL(prodTL.monto_precio_especial,'') special_price,IFNULL(prodTL.precio_especial_desde,'') special_price_from_date,
              IFNULL(prodTL.precio_especial_hasta,'') special_price_to_date,IFNULL(prodTL.producto_nuevo_desde,'') new_from_date,
              IFNULL(prodTL.producto_nuevo_hasta,'') new_to_dato,IFNULL(prodTL.MetaTitulo,'') meta_title,IFNULL(prodTL.MetaDescripcion,'') meta_description,
              IFNULL(prodTL.Palabras_clave_busqueda,'') meta_keywords,IFNULL(prodTL.imagen_principal,'') base_image,IFNULL(prodTL.imagen_principal,'') small_image,
              IFNULL(prodTL.imagen_principal,'') thumbnail_image,IFNULL(prodTL.imagen_principal,'') swatch_image,
              IFNULL(ImagenesAdicionales.ImagenesAdi,'') additional_images,CASE WHEN color.id_colores=-1 THEN '' ELSE color.nombre END color,
              '' color_de_cable,'' related_skus,
              '' crosssell_skus,'' upsell_skus,'' configurable_variations,prod.orden_lista orden_lista,
              IFNULL(CASE WHEN nl.id_numero_luces=-1 THEN '' ELSE nl.nombre END,'') numero_luces,
              IFNULL(CASE WHEN tam.id_tamanos=-1 THEN '' ELSE tam.nombre END,'') tamanio,
              IFNULL(codSAT.codigo_sat,'') codigo_sat,
              IFNULL(prodTL.stock_minimo,'') out_of_stock_qty,1 is_in_stock,prod.clave clave_provedor,'' bundle_price_type,'' bundle_sku_type,'' bundle_price_view,'' bundle_weight_type,
              '' bundle_values,'' bundle_shipment_type,prod.ubicacion_almacen ubicacion_matriz,

              CASE WHEN alm.inventario<0 THEN 0 ELSE alm.inventario END qty,
              IFNULL(prodPrecioCantMinima.cantidadMinima,0) min_cart_qty
             /* alm.inventario qty,prodPrecioCantMinima.cantidadMinima min_cart_qty*/
            FROM ec_productos_configurables prodConf
              INNER JOIN ec_productos prod ON prodConf.id_producto_configurable=prod.id_productos
              INNER JOIN ec_tipos_producto tProd ON prod.id_tipo_producto=tProd.id_tipo_producto
              INNER JOIN ec_categoria cat ON prod.id_categoria=cat.id_categoria
              INNER JOIN ec_subcategoria sub ON prod.id_subcategoria=sub.id_subcategoria
              INNER JOIN ec_subtipos subt ON prod.id_subtipo=subt.id_subtipos
              LEFT JOIN ec_producto_tienda_linea prodTL on prod.id_productos=prodTL.id_producto
              LEFT JOIN ec_colores color ON prod.id_color=color.id_colores
              LEFT JOIN ec_numero_luces nl ON prod.id_numero_luces=nl.id_numero_luces
              LEFT JOIN ec_admin_codigos_sat codSAT ON prod.id_subcategoria=codSAT.id_subcategoria
              LEFT JOIN ec_tamanos tam ON prod.id_tamano=tam.id_tamanos
              LEFT JOIN ec_almacen_producto alm ON prod.id_productos=alm.id_producto and id_almacen=1
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo like 'Peso%') Peso ON prod.id_productos=Peso.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Alto Empaque%') altoEmpaque ON prod.id_productos=altoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Largo Empaque%') largoEmpaque ON prod.id_productos=largoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,valor_atributo Valor
                        FROM ec_atributo_producto atrPro
                          INNER JOIN ec_atributos atr ON atrPro.id_atributo=atr.id_atributo
                          INNER JOIN ec_atributo_catalogo atrCat ON atrPro.id_atributo_catalogo=atrCat.id_atributo_catalogo
            			WHERE nombre_atributo LIKE 'Ancho Empaque%') anchoEmpaque ON prod.id_productos=anchoEmpaque.id_producto
              LEFT JOIN (SELECT id_producto,GROUP_CONCAT(nombre_completo) ImagenesAdi
            			FROM ec_productos_imagenes_adicionales
                        GROUP BY id_producto) ImagenesAdicionales ON prod.id_productos=ImagenesAdicionales.id_producto
              LEFT JOIN (select prodCantMin.id_producto,cantidadMinima,predet.precio_venta
            			FROM ec_precios_detalle predet
                          INNER JOIN ec_precios pre on pre.id_precio=predet.id_precio and grupo_cliente_magento='Mostrador'
                          INNER JOIN (SELECT id_producto, min(de_valor) cantidadMinima
            						 FROM ec_precios pre
            						   INNER JOIN ec_precios_detalle preDet ON pre.id_precio=preDet.id_precio
            						   WHERE grupo_cliente_magento='Mostrador'
            						   GROUP BY id_producto) prodCantMin
            			    ON predet.id_producto=prodCantMin.id_producto and predet.de_valor=prodCantMin.cantidadMinima) prodPrecioCantMinima
            			ON prod.id_productos=prodPrecioCantMinima.id_producto
            WHERE prodTL.habilitado=1 AND prod.id_productos in ({$skuList})/*AND prodConf.id_producto in (2349)2049*/
                /*AND CASE WHEN prod.id_tipo_producto=1 THEN IFNULL(Peso.Valor,'') ELSE 1 END <> CASE WHEN prod.id_tipo_producto=1 THEN '' ELSE 2 END
                AND CASE WHEN prod.id_tipo_producto=1 THEN IFNULL(prodPrecioCantMinima.precio_venta,0) ELSE 1 END <> CASE WHEN prod.id_tipo_producto=1 THEN 0 ELSE 2 END*/
            ;";
      $productosProcesar = $db->prepare($consultaProductoMagento);
      $productosProcesar->execute();
      if($productosProcesar->rowCount()){
        //Recupera token
        $token = getToken();
        error_log('token: '. $token['token']);
        //Itera productos
        $skuProductos = '';
        if(!empty($token['token'])) {
          foreach ($productosProcesar as $row) {
            error_log($row['sku']);
            updateProductoMagento($token,$row);
            $skuProductos .= (!empty($skuProductos)) ? ',' . $row['sku'] : $row['sku'];
          }
          //Procesa tier-price de productos
          try{
            //Recupera TierPrice: Por procesar
            $consultaTPMagento="select distinct pro.nombre,pro.orden_lista,preDet.id_producto sku,'base' tier_price_website,grupo_cliente_magento tier_price_customer_group,
                    de_valor/*a_valor*/ tier_price_qty,precio_venta tier_price,'fixed' tier_price_value_type
                  from ec_precios pre
                    inner join ec_precios_detalle preDet ON pre.id_precio=preDet.id_precio
                    inner join ec_producto_tienda_linea proTL on preDet.id_producto=proTL.id_producto
                    inner join ec_productos pro on proTL.id_producto=pro.id_productos
                  where grupo_cliente_magento is not null
                    and grupo_cliente_magento !=''
                    and proTL.habilitado=1 and preDet.id_producto in ({$skuProductos});
              ;";
            $tpProcesar = $db->prepare($consultaTPMagento);
            $tpProcesar->execute();
            if($tpProcesar->rowCount()){
              //Envia tier-price
              $resultTP = updateTierPriceMagento($token,$tpProcesar);
            }
          }catch (PDOException $e) {
            error_log('Error integación  - TP: ' . $e->getMessage());
          }
        }
      }
    }
    error_log('Log CL - Job End: Actualización productos en Magento');
  }catch (PDOException $e) {
    error_log('Error integación  - Productos: ' . $e->getMessage());
  }

  #####################################
  ##  Sincroniza tier-price a Magento
  #####################################
  try{
    error_log('Log CL - Job Init: Actualización tier-price en Magento');

    //Actualiza tier-price: Procesando
    $updateProducto="update ec_sync_magento set estatus=2 where tipo='TierPrice' and estatus=1 limit 50;";
    $updateStmt = $db->prepare($updateProducto);
    try {
      $updateStmt->execute();
    }catch (PDOException $e) {
      error_log('Error integación Magento: ' . $e->getMessage());
    }

    //Recupera TierPrice: Por procesar
    $consultaTPMagento="select distinct pro.nombre,pro.orden_lista,preDet.id_producto sku,'base' tier_price_website,grupo_cliente_magento tier_price_customer_group,
              de_valor/*a_valor*/ tier_price_qty,precio_venta tier_price,'fixed' tier_price_value_type
            from ec_precios pre
              inner join ec_precios_detalle preDet ON pre.id_precio=preDet.id_precio
              inner join ec_producto_tienda_linea proTL on preDet.id_producto=proTL.id_producto
              inner join ec_productos pro on proTL.id_producto=pro.id_productos
              inner join ec_sync_magento sync on sync.id_registro = preDet.id_precio
            where
            	sync.tipo = 'TierPrice'
            	and sync.estatus = '2'
            	and proTL.habilitado=1
              and grupo_cliente_magento is not null
				      and grupo_cliente_magento !=''
      ;";
    $tpProcesar = $db->prepare($consultaTPMagento);
    $tpProcesar->execute();
    if($tpProcesar->rowCount()){
      //Recupera token
      if (empty($token)) {
          $token = getToken();
          error_log('token: '. $token['token']);
      }
      //Envia tier-price
      $resultTP = updateTierPriceMagento($token,$tpProcesar);

    }else{
      $resultTP = '3';
    }
    //Actualiza tier-price: Resultado
    $updateTP="update ec_sync_magento set estatus={$resultTP} where tipo='TierPrice' and estatus=2;";
    $updateStmtTP = $db->prepare($updateTP);
    try {
      $updateStmtTP->execute();
    }catch (PDOException $e) {
      error_log('Error integación Magento: ' . $e->getMessage());
    }
    error_log('Log CL - Job End: Actualización tier-price en Magento');
  }catch (PDOException $e) {
    error_log('Error integación  - TP: ' . $e->getMessage());
  }

  //Función para obtener token de Magento
  function getToken() {
    try {
      //Declara variables de api_config
      $db = new db();
      $db = $db->conectDB();
      $result = '';
      $hostMagento = '';
      $usuarioMagento = '';
      $contrasenaMagento = '';
      $tokenMagento = '';
      $sqlAPIConfig="select c.name, c.value FROM api_config c WHERE c.key='magento' and c.value is not null";
      //Recupera variables
      $resultadoAPIConf = $db->prepare($sqlAPIConfig);
      $resultadoAPIConf->execute();
      foreach ($resultadoAPIConf as $row) {
        $hostMagento= ($row['name'] == 'url') ? $row['value'] : $hostMagento;
        $usuarioMagento=($row['name'] == 'usuario') ? $row['value'] : $usuarioMagento;
        $contrasenaMagento=($row['name'] == 'contrasena') ? $row['value'] : $contrasenaMagento;
        $tokenMagento=($row['name'] == 'token') ? $row['value'] : $tokenMagento;
      }
      //Recupera token Magento
      try {
        //Valida token
        if (!empty($tokenMagento)) {
          $url = $hostMagento.'/rest/V1/categories';
          $ch = curl_init();
          curl_setopt($ch,CURLOPT_URL,$url);
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$tokenMagento)
          );
          $output=curl_exec($ch);
          $response = json_decode($output,true);
          $tokenMagento = (empty($response['message'])) ? $tokenMagento : '';
          error_log('token response validation : '. $response['message']);
          //error_log('CL - LOG Magento token: '.$output);
          curl_close($ch);
        }

        if (!empty($hostMagento) && !empty($usuarioMagento) && !empty($contrasenaMagento) && empty($tokenMagento)) {
            //Prepar petición
            $data = array(
                'username' => $usuarioMagento,
                'password' => $contrasenaMagento
            );
            $post_data = json_encode($data);
            // Inicializa curl request
            $crl = curl_init($hostMagento.'/rest/V1/integration/admin/token');
            //error_log('CL - Log Magento token request: ' .$hostMagento.'/rest/V1/integration/admin/token');
            //error_log($post_data);
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($crl, CURLINFO_HEADER_OUT, true);
            curl_setopt($crl, CURLOPT_POST, true);
            curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
              )
            );
            // Ejecuta petición
            $tokenMagento = curl_exec($crl);
            $tokenMagento = str_replace('"','',$tokenMagento);
            //error_log('CL - LOG Magento token result: '.$tokenMagento);
            //Estructura de resultado
            $result = array(
                'token' => $tokenMagento,
                'host' => $hostMagento
            );
            // Cierra curl sesión
            curl_close($crl);
            //Update Token
            try {
              $updateToken="update api_config c set c.value='{$tokenMagento}' where c.key='magento' and c.name='token';";
              $updateStmtTk = $db->query($updateToken);
              //$updateStmtTk->execute();
            }catch (PDOException $e) {
              error_log('Error actualización token BD: ' . $e->getMessage());
            }

        }else{
          $result = array(
              'token' => $tokenMagento,
              'host' => $hostMagento
          );
        }
      } catch (Exception $e) {
        error_log('CL - LOG Respuesta token: Error - ' . $e->getMessage());
      }
    } catch (Exception $e) {
      error_log('CL - LOG Respuesta token: Error - ' . $e->getMessage());
    }
    return $result;
  }

  //Función para enviar producto a Magento
  function updateProductoMagento($magento, $row) {
    //Genera petición para actualizar producto en Magento
    $tokenMagento = $magento['token'];
    $hostMagento = $magento['host'];
    if (!empty($tokenMagento)) {
      $db = new db();
      $db = $db->conectDB();
      try{
        //Prepar petición
        $dataProd = [];
        $dataProd['product'] = [];
        $dataProd['product']['sku']=$row['sku'];
        $dataProd['product']['name']=$row['name'];
        $dataProd['product']['price']=$row['price'];
        $dataProd['product']['status']=$row['product_online'];
        $dataProd['product']['type_id']=$row['product_type'];
        $dataProd['product']['attribute_set_id']=4;
        $dataProd['product']['visibility']=4;//$row['visibility'];4 = Catalog, Search , 1 = Not Visible Individually
        $dataProd['product']['extension_attributes'] = [];
        $dataProd['product']['extension_attributes']['stock_item'] = [];
        $row['qty'] = ($row['qty']<0)? 0: $row['qty'];
        if ($row['qty']>=0) {
          $dataProd['product']['extension_attributes']['stock_item']['qty'] = $row['qty'];
        }
        $dataProd['product']['extension_attributes']['stock_item']['is_in_stock'] = true;

        $dataProd['product']['custom_attributes'] = [];
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "description","value"=> $row['description']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "short_description","value"=> $row['short_description']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "weight","value"=> $row['weight']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "alto_empaque","value"=> $row['alto_empaque']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "largo_empaque","value"=> $row['largo_empaque']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "ancho_empaque","value"=> $row['ancho_empaque']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "meta_title","value"=> $row['meta_title']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "meta_description","value"=> $row['meta_description']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "meta_keywords","value"=> $row['meta_keywords']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "codigo_sat","value"=> $row['codigo_sat']);
        $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "orden_lista","value"=> $row['orden_lista']);
        if ($row['color_de_cable']) {
          $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "color_de_cable","value"=> $row['color_de_cable']);
        }
        if ($row['numero_luces']) {
          $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "numero_luces","value"=> $row['numero_luces']);
        }
        if ($row['tamanio']) {
          $dataProd['product']['custom_attributes'][] = array("attribute_code"=> "tamanio","value"=> $row['tamanio']);
        }

        // $dataProd = array(
        //     "product" => array(
        //       "sku"=>$row['sku'],
        //       "name"=>$row['name'],
        //       "price"=>$row['price'],
        //       "status"=> 1,
        //       "type_id"=> $row['product_type'],
        //       "attribute_set_id"=> 4,
        //       "visibility"=> 4,
        //       "extension_attributes" => array(
        //         "stock_item" => array(
        //           "qty"=>$row['qty'],
        //           "is_in_stock"=>true
        //         )
        //       ),
        //       "custom_attributes" => array(
        //         0 => array(
        //          "attribute_code"=> "sku",
        //          "value"=> $row['sku']
        //         ),
        //         1 => array(
        //          "attribute_code"=> "description",
        //          "value"=> $row['description']
        //         ),
        //         2 => array(
        //          "attribute_code"=> "short_description",
        //          "value"=> $row['short_description']
        //         ),
        //         3 => array(
        //          "attribute_code"=> "weight",
        //          "value"=> $row['weight']
        //         ),
        //         4 => array(
        //          "attribute_code"=> "alto_empaque",
        //          "value"=> $row['alto_empaque']
        //         ),
        //         5 => array(
        //          "attribute_code"=> "largo_empaque",
        //          "value"=> $row['largo_empaque']
        //         ),
        //         6 => array(
        //          "attribute_code"=> "ancho_empaque",
        //          "value"=> $row['ancho_empaque']
        //         ),
        //         7 => array(
        //          "attribute_code"=> "meta_title",
        //          "value"=> $row['meta_title']
        //         ),
        //         8 => array(
        //          "attribute_code"=> "meta_description",
        //          "value"=> $row['meta_description']
        //         ),
        //         9 => array(
        //          "attribute_code"=> "meta_keywords",
        //          "value"=> $row['meta_keywords']
        //         ),
        //         10 => array(
        //          "attribute_code"=> "color_de_cable",
        //          "value"=> $row['color_de_cable']
        //         ),
        //         11 => array(
        //          "attribute_code"=> "orden_lista",
        //          "value"=> $row['orden_lista']
        //         ),
        //         12 => array(
        //          "attribute_code"=> "numero_luces",
        //          "value"=> $row['numero_luces']
        //         ),
        //         13 => array(
        //          "attribute_code"=> "tamanio",
        //          "value"=> $row['tamanio']
        //         ),
        //         14 => array(
        //          "attribute_code"=> "codigo_sat",
        //          "value"=> $row['codigo_sat']
        //         )
        //       )
        //     )
        // );
        //error_log('CL - pet: '. print_r($dataProd,true));
        $post_dataProd = json_encode($dataProd);
        // Inicializa curl request
        error_log('CL - Request: '. $post_dataProd);
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        /*
        if ($row['action']=='update') {
          error_log('CL - Log Magento update request: ' .$hostMagento.'/rest/V1/products/'.$row['sku']);
          curl_setopt($crl, CURLOPT_CUSTOMREQUEST, "PUT");
          curl_setopt($crl, CURLOPT_URL, $hostMagento.'/rest/V1/products/'.$row['sku']);
        }else{ //if ($row['action']=='insert') {*/
          error_log('CL - Log Magento insert request: ' .$hostMagento.'/rest/V1/products');
          curl_setopt($crl, CURLOPT_POST, 1);
          curl_setopt($crl, CURLOPT_URL, $hostMagento.'/rest/V1/products');
        //}

        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_dataProd);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$tokenMagento)
        );
        $result = curl_exec($crl);
        $curl_info = curl_getinfo($crl);
        $http_status = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        error_log('CL - LOG Magento result: '. print_r($result,true));
        // Cierra curl sesión
        curl_close($crl);
        //actualiza estatus realizado
        try {
          $updateProducto="update ec_sync_magento set estatus=3 where tipo='Producto' and id_registro='".$row['sku']."';";
          $updateStmt = $db->query($updateProducto);

          $updateProductoC="update ec_sync_magento sync inner join ec_productos_configurables conf on conf.id_producto_configurable=sync.id_registro set sync.estatus='3' where sync.id_registro='".$row['sku']."';";
          $updateStmtC = $db->query($updateProductoC);
          //          $updateStmt->execute();
        }catch (PDOException $e) {
          error_log('Error actualización BD: ' . $e->getMessage());
        }
      } catch (Exception $e) {
        error_log('CL - LOG Respuesta Actualiza Prod: Error - ' . $e->getMessage());
        try {
          $updateProducto="update ec_sync_magento set estatus=4 where tipo='Producto' and id_registro='".$row['id_registro']."';";
          $updateStmt = $db->query($updateProducto);
        }catch (PDOException $e) {
          error_log('Error actualización BD: ' . $e->getMessage());
        }
      }
    }else{
        error_log('CL - LOG No token');
    }
  }

  //Función para enviar tier-price a Magento
  function updateTierPriceMagento($magento, $records) {
    //Genera petición para actualizar producto en Magento
    $tokenMagento = $magento['token'];
    $hostMagento = $magento['host'];
    $result = '';
    if (!empty($tokenMagento)) {
      $db = new db();
      $db = $db->conectDB();
      try{
        //Prepar petición
        $data = array(
            'prices' => array()
        );
        //Agrega registros a petición
        foreach ($records as $row) {
          $data['prices'][] = array(
            'price' => $row['tier_price'],
            'price_type' => $row['tier_price_value_type'],
            'website_id' => 0,
            'sku' => $row['sku'],
            'customer_group' => $row['tier_price_customer_group'],
            'quantity' => $row['tier_price_qty']
          );
        }
        $post_data = json_encode($data);
        // Inicializa curl request
        error_log($post_data);
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        error_log('CL - Log Magento tier-price request: ' .$hostMagento.'/rest/V1/products/tier-prices');
        curl_setopt($crl, CURLOPT_POST, 1);
        curl_setopt($crl, CURLOPT_URL, $hostMagento.'/rest/V1/products/tier-prices');
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$tokenMagento)
        );
        $result = curl_exec($crl);
        $curl_info = curl_getinfo($crl);
        $http_status = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        error_log('CL - LOG Magento result: '. print_r($result,true));
        $result = '3';
        // Cierra curl sesión
        curl_close($crl);
      } catch (Exception $e) {
        error_log('CL - LOG Respuesta Actualiza Prod: Error - ' . $e->getMessage());
        $result = '4';
      }
    }else{
        error_log('CL - LOG No token');
        $result = '1';
    }
    return $result;
  }

?>
