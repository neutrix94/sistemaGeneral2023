<?php
    //php_track_vars;
    
    extract($_GET);
    extract($_POST);
    
//CONECCION Y PERMISOS A LA BASE DE DATOS
    include("../../conect.php");
   
   
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        //header("Content-type: atachment-download");
        //header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        //header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        //header("Content-type: atachment/vnd.ms-excel");
        header("Content-type: application/csv");
        
        
 
        header("Content-Disposition: atachment; filename=\"precios.csv\";");
        header("Content-transfer-encoding: binary\n"); 
    
        $ano_act=date("Y");
        $ano_antes=$ano_act-1;
        //echo "ID Detalle,ID Lista,ID Producto,Orden de lista,Nombre,De,A,Precio venta,Precio de oferta,Precio compra,Inventario en Matriz\n";
        echo "ID PRODUCTO (OBLIGATORIO),ORDEN LISTA,ALFANUMERICO,NOMBRE,INVENTARIO MATRIZ,PRECIO COMPRA,ID DETALLE (OBLIGATORIO O PONER EN CERO SI ES NUEVO)";
        echo " DEJAR VACIO PARA QUE NO SEA TOMANDO EN CUENTA DURANTE LA IMPORTACION,";
        echo "ID LISTA PRECIO (OBLIGATORIO),DE (OBLIGATORIO),A (OBLIGATORIO),PRECIO VENTA (OBLIGATORIO),PRECIO ETIQUETA (OBLIGATORIO),ES OFERTA(OBLIGATORIO 0 o 1),";
        echo "DESCUENTO,PRECIO COMPRA - DESCUENTO,";
        if($para_mayoreo==1){
          echo "PORCENTAJE DE UTILIDAD,";
        }else{
          echo "VENTAS TOTALES ".$ano_antes.",";
        }
        echo "INVENTARIO DE TODAS LAS SUCURSALES,PRECIO COMPRA ANTERIOR,ENTRADAS AÑO ACTUAL,OBSERVACIONES,NOTAS DE PRECIO, NOTAS DE DECORACIÓN, NOTAS DE EXHIBICIÓN, UBICACION\n";

        $sql="SELECT
              ax1.id_productos,
              ax1.orden_lista,
              ax1.clave,
              ax1.nombre,
              ax1.invMatriz,
              ax1.precio_compra,
              ax1.id_detalle,
              ax1.id_precio,              
              ax1.de,
              ax1.a,
              ax1.precio_venta,
              ax1.precio_etiqueta,
              ax1.es_oferta,
              ax1.descuento,
              ax1.precio_neto,
              SUM(IF(ped.id_pedido IS NULL OR pdet.es_externo=1,0,pdet.cantidad)) as ventasTotalesAnoAntes,
              ax1.invTotal,
              IF(hpc.id_historico_precio_compra IS NULL,0,hpc.precio)as precioCompraAnterior,
              ax1.totalEntradas,
              ax1.observaciones,
              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 1
              ) AS 'NOTAS PRECIO',

              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 2
              ) AS 'NOTAS DECORACIÓN',

              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 3
              ) AS 'NOTAS EXHIBICIÖN',
              (SELECT
                IF( ppua.id_ubicacion_matriz IS NULL, 
                  '-',
                  CONCAT( ppua.letra_ubicacion_desde, '', ppua.numero_ubicacion_desde, 
                    IF( ppua.pasillo_desde = 0, '', CONCAT( ' Pasillo : ', ppua.pasillo_desde ) ), 
                    IF( ppua.altura_desde = '', '', CONCAT( ' Altura : ', ppua.altura_desde ) )
                  )
                )
              FROM ec_inventario_proveedor_producto ipp
              LEFT JOIN ec_proveedor_producto pp 
              ON ipp.id_proveedor_producto = pp.id_proveedor_producto
              LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
              ON ppua.id_proveedor_producto = pp.id_proveedor_producto
              WHERE pp.id_producto = ax1.id_productos
              ORDER BY ipp.inventario DESC
              LIMIT 1
              ) AS Ubicacion
            FROM(
              SELECT
                ax.id_productos,
                ax.orden_lista,
                ax.clave,
                ax.nombre,
                ax.invMatriz,
                ax.precio_compra,
                IF(pd.id_precio_detalle IS NULL,'',pd.id_precio_detalle) as id_detalle,
                IF(pd.id_precio IS NULL,'',pd.id_precio) as id_precio,              
                IF(pd.de_valor IS NULL,'',pd.de_valor) as de,
                IF(pd.a_valor IS NULL,'',pd.a_valor) as a,
                IF(pd.precio_venta IS NULL,'',pd.precio_venta) as precio_venta,
                IF(pd.precio_etiqueta IS NULL,'',pd.precio_etiqueta) as precio_etiqueta,
                IF(pd.es_oferta IS NULL,'',pd.es_oferta) as es_oferta,
                ax.descuento,
                (ax.precio_compra*(1-ax.descuento))as precio_neto,
                ax.invTotal,
                ax.totalEntradas,
                ax.observaciones
            FROM(
              SELECT
                p.id_productos,
                p.orden_lista,
                REPLACE(p.nombre,',',' ')as nombre,
                p.precio_compra,
                SUM( IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=1,0,(md.cantidad*tm.afecta))) as invMatriz,
                SUM( IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))) as invTotal,
                SUM( 
                  IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_tipo_movimiento=1 AND ma.fecha LIKE '%$ano_act%',
                    (md.cantidad*tm.afecta)
                    ,0
                    )
                ) as totalEntradas,
                REPLACE(p.clave,',','*') as clave,
                p.precio_venta_mayoreo as descuento,
                REPLACE(p.observaciones,',','') as observaciones 
              FROM ec_productos p 
              LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
              LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
              LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
              WHERE id_productos>0
              GROUP BY p.id_productos
              ORDER BY orden_lista ASC
            )ax
            LEFT JOIN ec_precios_detalle pd ON ax.id_productos=pd.id_producto AND pd.id_precio='$id_precio'
            GROUP BY ax.id_productos,pd.id_precio_detalle
            ORDER BY ax.orden_lista
          )ax1
          LEFT JOIN ec_pedidos_detalle pdet ON ax1.id_productos=pdet.id_producto
          LEFT JOIN ec_pedidos ped ON pdet.id_pedido=ped.id_pedido
          AND ped.fecha_alta like '%$ano_antes%'
          LEFT JOIN ec_historico_precio_compra hpc ON ax1.id_productos=hpc.id_producto
          GROUP BY ax1.id_productos,ax1.id_detalle
          ORDER BY ax1.orden_lista";
      //die($sql);

      if($para_mayoreo==1){
          $sql="SELECT
              ax1.id_productos,
              ax1.orden_lista,
              ax1.clave,
              ax1.nombre,
              ax1.invMatriz,
              ax1.precio_compra,
              IF(pd2.id_precio_detalle IS NULL,'',pd2.id_precio_detalle) as id_detalle,
              IF(pd2.id_precio_detalle IS NULL,'',pd2.id_precio),              
              IF(pd2.id_precio_detalle IS NULL,'',pd2.de_valor) as de,
              IF(pd2.id_precio_detalle IS NULL,'',pd2.a_valor) as a,
              ax1.precio_venta,
              ax1.precio_etiqueta,
              IF(pd2.id_precio_detalle IS NULL,'',pd2.es_oferta) as es_oferta,
              ax1.descuento,
              ax1.precio_neto,
              /*SUM(IF(ped.id_pedido IS NULL OR pdet.es_externo=1,0,pdet.cantidad)) as ventasTotalesAnoAntes,*/
              ((ax1.precio_venta-ax1.precio_neto)/ax1.precio_neto) as porcentajeUtilidad,
              ax1.invTotal,
              IF(hpc.id_historico_precio_compra IS NULL,0,hpc.precio)as precioCompraAnterior,
              ax1.totalEntradas,
              ax1.observaciones,
              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 1
              ) AS 'NOTAS PRECIO',

              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 2
              ) AS 'NOTAS DECORACIÓN',

              (SELECT
                  IF( pr_n.id_producto_nota IS NULL, 
                      '',
                      GROUP_CONCAT( CONCAT( pvn.nombre_valor_nota, ' : ', pr_n.nota ) SEPARATOR '<br>')
                  )
              FROM ec_productos_notas pr_n
              LEFT JOIN ec_productos_categorias_notas pcn
              ON pr_n.id_categoria_nota = pcn.id_categoria_nota
              LEFT JOIN ec_productos_valores_notas pvn
              ON pvn.id_valor_nota = pr_n.id_valor_nota
              WHERE id_producto = ax1.id_productos
              AND pr_n.id_categoria_nota = 3
              ) AS 'NOTAS EXHIBICIÖN',
              (SELECT
                IF( ppua.id_ubicacion_matriz IS NULL, 
                  '-',
                  CONCAT( ppua.letra_ubicacion_desde, '', ppua.numero_ubicacion_desde, 
                    IF( ppua.pasillo_desde = 0, '', CONCAT( ' Pasillo : ', ppua.pasillo_desde ) ), 
                    IF( ppua.altura_desde = '', '', CONCAT( ' Altura : ', ppua.altura_desde ) )
                  )
                )
              FROM ec_inventario_proveedor_producto ipp
              LEFT JOIN ec_proveedor_producto pp 
              ON ipp.id_proveedor_producto = pp.id_proveedor_producto
              LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
              ON ppua.id_proveedor_producto = pp.id_proveedor_producto
              WHERE pp.id_producto = ax1.id_productos
              ORDER BY ipp.inventario DESC
              LIMIT 1
              ) AS Ubicacion
            FROM(
              SELECT
                ax.id_productos,
                ax.orden_lista,
                ax.clave,
                ax.nombre,
                ax.invMatriz,
                ax.precio_compra,
                IF(pd.id_precio_detalle IS NULL,'',pd.id_precio_detalle) as id_detalle,
                IF(pd.id_precio IS NULL,'',pd.id_precio) as id_precio,              
                IF(pd.de_valor IS NULL,'',pd.de_valor) as de,
                IF(pd.a_valor IS NULL,'',pd.a_valor) as a,
                IF(pd.precio_venta IS NULL,'',MIN(pd.precio_venta)) as precio_venta,
                IF(pd.precio_etiqueta IS NULL,'',MIN(pd.precio_etiqueta)) as precio_etiqueta,
                IF(pd.es_oferta IS NULL,'',pd.es_oferta) as es_oferta,
                ax.descuento,
                (ax.precio_compra*(1-ax.descuento))as precio_neto,
                ax.invTotal,
                ax.totalEntradas,
                ax.observaciones
            FROM(
              SELECT
                p.id_productos,
                p.orden_lista,
                REPLACE(p.nombre,',',' ')as nombre,
                p.precio_compra,
                SUM( IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=1,0,(md.cantidad*tm.afecta))) as invMatriz,
                SUM( IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))) as invTotal,
                SUM( 
                  IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_tipo_movimiento=1 AND ma.fecha LIKE '%$ano_act%',
                    (md.cantidad*tm.afecta)
                    ,0
                    )
                ) as totalEntradas,
                REPLACE(p.clave,',','*') as clave,
                p.precio_venta_mayoreo as descuento,
                REPLACE(p.observaciones,',','') as observaciones 
              FROM ec_productos p 
              LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
              LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
              LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
              WHERE id_productos>0
              GROUP BY p.id_productos
              ORDER BY orden_lista ASC
            )ax
            LEFT JOIN ec_precios_detalle pd ON ax.id_productos=pd.id_producto AND pd.id_precio='$id_precio'
            GROUP BY ax.id_productos/*,pd.id_precio_detalle*/
            ORDER BY ax.orden_lista
          )ax1
          LEFT JOIN ec_precios_detalle pd2 ON pd2.id_producto=ax1.id_productos
          AND pd2.precio_venta=ax1.precio_venta AND pd2.id_precio IN($id_precio) 
          LEFT JOIN ec_historico_precio_compra hpc ON ax1.id_productos=hpc.id_producto
          GROUP BY ax1.id_productos/*,ax1.id_detalle*/
          ORDER BY ax1.orden_lista";
      //die($sql);
      }

      $res=mysql_query($sql) or die(mysql_error());
       
      $num=mysql_num_rows($res);        
    
      for($i=0;$i<$num;$i++)
      {
          $row=mysql_fetch_row($res);
          
          for($j=0;$j<sizeof($row);$j++)
          {//echo 'tam: '.sizeof($row);
              if($j > 0)
                echo ",";
              echo $row[$j];
          }
          echo "\n";
      }
      
      
      if($num == 0)
      {
            $sql="SELECT
                  'NO',
                  $id_precio,
                  id_productos,
                  orden_lista,
				  nombre,
                  1,
                  10000,
                  precio_venta,
                  precio_venta
                  FROM ec_productos
                  WHERE id_productos > 0
                  ORDER BY orden_lista";  
                  
            $res=mysql_query($sql) or die(mysql_error());
       
              $num=mysql_num_rows($res);        
            
              for($i=0;$i<$num;$i++)
              {
                  $row=mysql_fetch_row($res);
                  
                  for($j=0;$j<sizeof($row);$j++)
                  {
                      if($j > 0)
                        echo ",";
                      echo $row[$j];
                  }
                  echo "\n";
                  
              }                   
      }
    
    
?>