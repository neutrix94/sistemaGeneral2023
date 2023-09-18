<?php 
    include("../../conectMin.php");
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
   
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    $t=0;
    extract($_GET);
  //inicializamos variable de tipo de venta en caso de no existir
    if($t==null||$t==''){
      $t=0;
    }


    if($t!=null AND $l_normal!=1){
     
      $prec="IF(pd.precio_venta IS NULL OR pd.precio_venta=0,0,pd.precio_venta)";//modificado por Oscar 07.05.2018
      $prec_2="IF(aux.precio_venta_mayoreo IS NULL OR p.precio_venta_mayoreo=0,pd1.precio_venta,aux.precio_venta_mayoreo)";//modificado por Oscar 11.08.2018
      //$lista_de_precios=$id_lista_precio;
     // die("mayoreo");
    }else{
     // die('entra en normal');
      $t=0;
      $prec='IF(pd.id_precio_detalle IS NULL,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta))';
      $prec_2='IF(pd1.id_precio_detalle IS NULL,0,IF(sucs.usa_oferta=1, pd1.precio_etiqueta, pd1.precio_venta))';//modificado por Oscar 11.08.2018
     // $lista_de_precios="";
    }

  /*consulta si el producto es externo
    $sql="SELECT 
            sp.es_externo 
          FROM ec_productos p 
          LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
          LEFT JOIN ec_proveedor_producto pp
            ON pp.id_producto = p.id_productos
          WHERE ( p.orden_lista='$val' 
            OR pp.codigo_barras_pieza_1 = '{$val}'
            OR pp.codigo_barras_pieza_2 = '{$val}'
            OR pp.codigo_barras_pieza_3 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_1 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_2 = '{$val}'
            OR pp.codigo_barras_caja_1 = '{$val}'
            OR pp.codigo_barras_caja_2 = '{$val}' )
          AND sp.id_sucursal='$user_sucursal'";
    $eje=mysql_query($sql)or die("Error al consultar si el producto es externo!!!");
    $r=mysql_fetch_row($eje);*/
  
    $sql="SELECT 
            sp.es_externo 
          FROM ec_productos p 
          LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
          LEFT JOIN ec_proveedor_producto pp
          ON pp.id_producto = p.id_productos
          WHERE ( p.orden_lista='{$val}' 
            OR p.id_productos = '{$val}'
            OR pp.codigo_barras_pieza_1 = '{$val}'
            OR pp.codigo_barras_pieza_2 = '{$val}'
            OR pp.codigo_barras_pieza_3 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_1 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_2 = '{$val}'
            OR pp.codigo_barras_caja_1 = '{$val}'
            OR pp.codigo_barras_caja_2 = '{$val}') 
          AND sp.id_sucursal=$user_sucursal";
    //die($sql);
    $eje_aux=mysql_query($sql)or die("Error al consultar si el producto es interno o externo\n\n".mysql_error());
    $resultado=mysql_fetch_row($eje_aux);
    $producto_externo=$resultado[0];
//si es producto externo;
    if($producto_externo==1){
     //die("es externo");
      $lista_prec="s.lista_precios_externa=pd.id_precio";
    }else{
    //si es producto de Pedro
      $lista_prec="s.id_precio=pd.id_precio";
    }
  
/*Implementación Oscar 03.03.2019 para tomar precio de mayoreo de lista de precios externa*/
    if($id_lista_precio!="" && $id_lista_precio!=0  && $l_normal!=1){
        $lista_prec=$id_lista_precio.'=pd.id_precio';
    }
    //die($lista_prec); 

/*Fin de Cambio Oscar 03.03.2019*/

    function truncate($val, $f="0"){
    	if(($p = strpos($val, '.')) !== false) {
    		$val = floatval(substr($val, 0, $p + 1 + $f));
    	}
    	return $val;
    }
    
    function redondea05($val){
    	$aux = truncate($val);
    	$dif = $val - $aux;
    	
    	#die ("val {$val} | aux {$aux} | dif {$dif}");
    	#val 109.99979782104 | aux 109 | dif 0.99979782104492
    	
    	if ($dif == 0) return $val;
    	elseif ($dif < 0.25) return $aux;
    	elseif ($dif < 0.75) return $aux+0.5;
    	else return $aux+1;
    }
   
   /*
deshabilitado por Oscar 2022
      if(!is_numeric($val))
        $val=-1;

    */
   
    //Buscamos por orden de lista
    $sql="SELECT
          p.id_productos,/**/
          p.orden_lista,
        /*implementación Oscar 10.10.2018 para mostrar ubicacion y alfanumerico  style=\"font-size:11px;\"*/
          CONCAT(p.nombre,'<br><b>',
            IF(s.mostrar_ubicacion=1,
                IF($user_sucursal=1,
                  CONCAT('Ubicación: ',p.ubicacion_almacen,'  -  '),
                  IF(sp.ubicacion_almacen_sucursal!='',
                      CONCAT('Ubicacion: ',sp.ubicacion_almacen_sucursal,'  '),
                    ''
                  )
                ),
                ''
              ),
            IF(s.mostrar_alfanumericos=0,'',CONCAT('Clave: ',p.clave)),'</b>'
          ),
        /*fin de cambio*/
          CONCAT('$',FORMAT(IF(pd.id_precio_detalle IS NULL,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta)),0)),
          $prec,
          CONCAT('$',FORMAT(IF(pd.id_precio_detalle IS NULL,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta))*$can,0)),
          FLOOR(IF(pd.id_precio_detalle IS NULl,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta))*$can),
          p.id_subtipo,
          (SELECT CONCAT(de_valor, ' X ', CONCAT('$', FORMAT(de_valor*precio_venta, 0))) FROM ec_precios_detalle WHERE id_producto=p.id_productos
          AND id_precio =IF(sp.es_externo=0,s.id_precio,s.lista_precios_externa) AND de_valor > 1 ORDER BY NOT a_valor >= $can LIMIT 1),
          p.muestra_paleta,
        /*implementación Oscar 07.05.2018*/
          IF($t=1,IF(pd.precio_venta is NULL OR pd.precio_venta=0,IF(sp.es_externo=0,'muestra_emergente','es_externo'),0),0),/*10*/
        /*Fin de cambio*/
        /*implementación Oscar 10.11.2018 para no contar los productos que muestran paleta y/o son maquilados*/
          IF(p.es_maquilado=1 OR p.muestra_paleta=1,1,0) as noVerificaInventario,/*11*/
        /*Fin de cambio*/
        /*implementación Oscar 12.03.2019 para Sacar precio de lista en caso de que no haya mayoreo*/
          IF($t=0 OR pd.id_precio_detalle IS NOT NULL,'0',
              (SELECT pd_1.precio_venta 
              FROM ec_precios_detalle pd_1 
              LEFT JOIN ec_precios p1 on pd_1.id_precio=p1.id_precio
              LEFT JOIN sys_sucursales s1 ON IF($producto_externo=1,p1.id_precio=s1.lista_precios_externa,p1.id_precio=s1.id_precio)
              WHERE pd_1.id_producto=p.id_productos AND s1.id_sucursal=$user_sucursal LIMIT 1) 
          ) as precio_auxiliar,/*11*/
      /*implementación Oscar 18.03.2019 para saber de que lista de precios se está tomando el precio*/
           IF($t=0 OR pd.id_precio_detalle IS NOT NULL,pd.id_precio,
              (SELECT pd_1.id_precio 
              FROM ec_precios_detalle pd_1 
              LEFT JOIN ec_precios p1 on pd_1.id_precio=p1.id_precio
              LEFT JOIN sys_sucursales s1 ON IF($producto_externo=1,p1.id_precio=s1.lista_precios_externa,p1.id_precio=s1.id_precio)
              WHERE pd_1.id_producto=p.id_productos AND s1.id_sucursal=$user_sucursal LIMIT 1) 
          ) as id_lista_precio,
          p.es_ultimas_piezas AS is_last_pieces
      /*Fin de Cambio Oscar 18.03.2019*/
        /*Fin de cambio*/
          FROM ec_productos p
          JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
          JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
          LEFT JOIN ec_precios_detalle pd ON pd.id_producto = p.id_productos
          AND /**/$lista_prec/**/ 
          AND pd.id_producto = p.id_productos AND $can2 >= de_valor AND $can2 <= a_valor
          LEFT JOIN ec_proveedor_producto pp
          ON pp.id_producto = p.id_productos
          WHERE p.id_productos > 0
          AND p.habilitado=1
          AND sp.id_sucursal=$user_sucursal
          AND sp.estado_suc=1
          AND( p.orden_lista = '{$val}'
            OR pp.codigo_barras_pieza_1 = '{$val}'
            OR pp.codigo_barras_pieza_2 = '{$val}'
            OR pp.codigo_barras_pieza_3 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_1 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_2 = '{$val}'
            OR pp.codigo_barras_caja_1 = '{$val}'
            OR pp.codigo_barras_caja_2 = '{$val}'
          )
          /*AND $lista_prec*/";
//die( $sql );
/*implementacion Oscar para busqueda codigo barras y orden lista en devolucion*/
  if( isset($_GET['sale_id']) ){
    $sql="SELECT
          p.id_productos,/**/
          p.orden_lista,
        /*implementación Oscar 10.10.2018 para mostrar ubicacion y alfanumerico  style=\"font-size:11px;\"*/
          CONCAT(p.nombre,'<br><b>',
            IF(s.mostrar_ubicacion=1,
                IF($user_sucursal=1,
                  CONCAT('Ubicación: ',p.ubicacion_almacen,'  -  '),
                  IF(sp.ubicacion_almacen_sucursal!='',
                      CONCAT('Ubicacion: ',sp.ubicacion_almacen_sucursal,'  '),
                    ''
                  )
                ),
                ''
              ),
            IF(s.mostrar_alfanumericos=0,'',CONCAT('Clave: ',p.clave)),'</b>'
          ),
        /*fin de cambio*/
          CONCAT('$',FORMAT(IF(pd.id_precio_detalle IS NULL,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta)),0)),
          $prec,
          CONCAT('$',FORMAT(IF(pd.id_precio_detalle IS NULL,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta))*$can,0)),
          FLOOR(IF(pd.id_precio_detalle IS NULl,0,IF(s.usa_oferta=1, pd.precio_etiqueta, pd.precio_venta))*$can),
          p.id_subtipo,
          (SELECT CONCAT(de_valor, ' X ', CONCAT('$', FORMAT(de_valor*precio_venta, 0))) FROM ec_precios_detalle WHERE id_producto=p.id_productos
          AND id_precio =IF(sp.es_externo=0,s.id_precio,s.lista_precios_externa) AND de_valor > 1 ORDER BY NOT a_valor >= $can LIMIT 1),
          p.muestra_paleta,
        /*implementación Oscar 07.05.2018*/
          IF($t=1,IF(pd.precio_venta is NULL OR pd.precio_venta=0,IF(sp.es_externo=0,'muestra_emergente','es_externo'),0),0),/*10*/
        /*Fin de cambio*/
        /*implementación Oscar 10.11.2018 para no contar los productos que muestran paleta y/o son maquilados*/
          IF(p.es_maquilado=1 OR p.muestra_paleta=1,1,0) as noVerificaInventario,/*11*/
        /*Fin de cambio*/
        /*implementación Oscar 12.03.2019 para Sacar precio de lista en caso de que no haya mayoreo*/
          IF($t=0 OR pd.id_precio_detalle IS NOT NULL,'0',
              (SELECT pd_1.precio_venta 
              FROM ec_precios_detalle pd_1 
              LEFT JOIN ec_precios p1 on pd_1.id_precio=p1.id_precio
              LEFT JOIN sys_sucursales s1 ON IF($producto_externo=1,p1.id_precio=s1.lista_precios_externa,p1.id_precio=s1.id_precio)
              WHERE pd_1.id_producto=p.id_productos AND s1.id_sucursal=$user_sucursal LIMIT 1) 
          ) as precio_auxiliar,/*11*/
      /*implementación Oscar 18.03.2019 para saber de que lista de precios se está tomando el precio*/
           IF($t=0 OR pd.id_precio_detalle IS NOT NULL,pd.id_precio,
              (SELECT pd_1.id_precio 
              FROM ec_precios_detalle pd_1 
              LEFT JOIN ec_precios p1 on pd_1.id_precio=p1.id_precio
              LEFT JOIN sys_sucursales s1 ON IF($producto_externo=1,p1.id_precio=s1.lista_precios_externa,p1.id_precio=s1.id_precio)
              WHERE pd_1.id_producto=p.id_productos AND s1.id_sucursal=$user_sucursal LIMIT 1) 
          ) AS id_lista_precio,
          pedd.id_pedido_detalle,
          p.es_ultimas_piezas
      /*Fin de Cambio Oscar 18.03.2019*/
        /*Fin de cambio*/
          FROM ec_productos p
          JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
          JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
          LEFT JOIN ec_precios_detalle pd ON pd.id_producto = p.id_productos
          AND /**/$lista_prec/**/ 
          AND pd.id_producto = p.id_productos AND $can2 >= de_valor AND $can2 <= a_valor
          LEFT JOIN ec_proveedor_producto pp
          ON pp.id_producto = p.id_productos
          LEFT JOIN ec_pedidos_detalle pedd
          ON pedd.id_producto = p.id_productos
          AND pedd.id_pedido = {$_GET['sale_id']}
          WHERE p.id_productos > 0
          AND pedd.id_pedido = {$_GET['sale_id']}
          AND p.habilitado=1
          AND sp.id_sucursal=$user_sucursal
          AND sp.estado_suc=1
          /*AND p.es_ultimas_piezas = 0Oscar 2023 para omitir ultimas piezas*/
          AND( p.orden_lista = '{$val}'
            OR p.id_productos = '{$val}'
            OR pp.codigo_barras_pieza_1 = '{$val}'
            OR pp.codigo_barras_pieza_2 = '{$val}'
            OR pp.codigo_barras_pieza_3 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_1 = '{$val}'
            OR pp.codigo_barras_presentacion_cluces_2 = '{$val}'
            OR pp.codigo_barras_caja_1 = '{$val}'
            OR pp.codigo_barras_caja_2 = '{$val}'
          )
        /*AND $lista_prec*/";
  }
          
/*fin de cambio Oscar 2023*/

//die( "<textarea>{$sql}</textarea>" );          
          
    $res=mysql_query($sql) or die("Error en 2:\n$sql\n\nDescripcion:\n".mysql_error());
    
    if(mysql_num_rows($res) > 0)
    {
        $row=mysql_fetch_row($res);
        
        #die(($row[4]*$can) . " y " . redondea05($row[4]*$can));
        
        $row[4]=round($row[4], 2);
        $row[3]='$'.number_format($row[4], 2);
        $row[6]=redondea05($row[4]*$can);
        $row[5]='$'.number_format($row[6], 2);
      /*implementacion Oscar 2023 para que salgan las opciones de ultimas piezas*/
        if( $row[15] == 1 && isset( $_GET['sale_id'] ) ){ 
          die( "is_last_pieces|" );
        }
      /*fin de cambio Oscar 2023*/
        die("exito|$row[0]|$row[1]|$row[2]|$row[3]|$row[4]|$row[5]|$row[6]|$row[7]|$row[8]|NO|$row[9]|$row[10]|$row[11]|$row[12]|$row[13]|$producto_externo|$row[14]");/*se mada la posición 11 para ver si se muestra paleta o el producto es maquilado Oscar 10.11.2018*/
        /*Se agrega las posición 13 para agregar el id de la lista de precios precios Oscar 18.03.2019*///$row[14]modificado por Oscar 2023 para busqueda codigo barras y orden lista
    }
    
   

    die("No se encontro un producto que cumpla con los valores insertados, intente con otro!!!");
    
 ?>