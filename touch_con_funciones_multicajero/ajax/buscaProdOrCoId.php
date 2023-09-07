<?php 
    include("../../conectMin.php");
  /*Modificación del 26.02.2018; hace cambio del campo precio_oferta por precio_etiqueta en todas las consultas que poseen este campo*/

    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    $t=0;
    extract($_GET);
  //inicializamos variable de tipo de venta en caso de no existir
    if($t==null||$t==''){
      $t=0;
    }
  
/*implementación Oscar 03.09.2018 para modificación de apartados*/
  if($id_transferencia!='' && $id_transferencia){  
 //  die('here');
/*    $sql="SELECT 
            p.orden_lista,md.cantidad 
            FROM ec_productos p
            RIGHT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
            LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen 
            WHERE ma.id_transferencia=$id_transferencia
            AND ma.id_tipo_movimiento=5";*/
      $sql="SELECT p.orden_lista,tp.cantidad_entrada FROM ec_transferencia_productos tp 
            LEFT JOIN ec_productos p ON tp.id_producto_or=p.id_productos
            WHERE tp.id_transferencia=$id_transferencia";
    $eje=mysql_query($sql)or die("Eror al consultar el detalle del pedido para su modificación!!!".$sql."\n\n".mysql_error());
    $datos='ok|';
    while($r=mysql_fetch_row($eje)){
      $datos.=$r[0]."~".$r[1]."|";
    }
    die($datos.'0');//regresamos la respuesta
  }
/*fin de cambio 03.09.2018*/

/*implementación Oscar 03.09.2018 para modificación de apartados*/
  if($id_pedido!='' && $id_pedido){  
    $sql="SELECT 
            p.orden_lista,pd.cantidad FROM ec_pedidos_detalle pd LEFT JOIN ec_productos p ON pd.id_producto=p.id_productos WHERE id_pedido=$id_pedido AND cantidad>0";
    $eje=mysql_query($sql)or die("Eror al consultar el detalle del pedido para su modificación!!!".$sql."\n\n".mysql_error());
    $datos='ok|';
    while($r=mysql_fetch_row($eje)){
      $datos.=$r[0]."~".$r[1]."|";
    }
  //sacamos el monto del descuento
    $sql="SELECT ( (descuento*100)/subtotal ) FROM ec_pedidos WHERE id_pedido=$id_pedido";
    $eje=mysql_query($sql)or die("Error al consultar el descuento original de la venta!!!".$sql."\n\n".mysql_error());
    $r=mysql_fetch_row($eje);
    die($datos.$r[0]);//regresamos la respuesta
  }
/*fin de cambio 03.09.2018*/

/*Implementación Oscar 04.03.2019 para consultar información de paquete en pantalla de ventas*/
  if($es_paquete!='' && $es_paquete!=0 && $flag=='info'){
    $sql="SELECT 
            CONCAT('~',p.id_paquete,'|',p.nombre) 
          FROM ec_paquetes p 
          LEFT JOIN sys_sucursales_paquete sp ON p.id_paquete=sp.id_paquete
          JOIN sys_sucursales s on sp.id_sucursal=s.id_sucursal
          WHERE p.id_paquete=$es_paquete
          AND p.activo=1
          AND sp.estado_suc=1
          AND s.id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar cabecera del paquete \n\n".mysql_error()."\n\n".$sql);
    if(mysql_num_rows($eje)<=0){
      die("no se encontró el paquete!!!");
    }
    $datos='exito||paquete';
    $r=mysql_fetch_row($eje);
    $datos.=$r[0];
    die($datos);//regresamos la respuesta
  }  
/*Fin de cambio Oscar 04.03.2019*/

/*Implementación Oscar 04.03.2019 para armar paquete en pantalla de ventas*/
  if($es_paquete!='' && $es_paquete!=0){
    $sql="SELECT 
          p.orden_lista,
          (pqd.cantidad_producto * $can)
        FROM ec_productos p 
        LEFT JOIN ec_paquete_detalle pqd ON p.id_productos=pqd.id_producto
        LEFT JOIN ec_paquetes pqt ON pqd.id_paquete=pqt.id_paquete
        LEFT JOIN sys_sucursales_paquete sp ON pqt.id_paquete=sp.id_paquete
        JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
        WHERE pqd.id_paquete=$es_paquete
        AND s.id_sucursal=$user_sucursal
        AND sp.estado_suc=1
        AND pqt.activo=1";
    $eje=mysql_query($sql)or die("Error al consultar detalle del paquete \n\n".mysql_error()."\n\n".$sql);
    if(mysql_num_rows($eje)<=0){
      die("no se encontró el paquete!!!");
    }
    $datos='exito|';
    while($r=mysql_fetch_row($eje)){
      $datos.=$r[0]."~".$r[1]."*";
    }
    die($datos);//regresamos la respuesta
  }  
/*Fin de cambio Oscar 04.03.2019*/

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

  //consultamos siu el producto es de Daniel
    $sql="SELECT sp.es_externo FROM ec_productos p 
           LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
           WHERE p.orden_lista='$val'
           AND sp.id_sucursal='$user_sucursal'";
    $eje=mysql_query($sql)or die("Error al consultar si el producto es externo!!!");
    $r=mysql_fetch_row($eje);
  
    $sql="SELECT 
            sp.es_externo 
          FROM ec_productos p 
          LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
          WHERE p.orden_lista='$val' AND sp.id_sucursal=$user_sucursal";
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
   
   if(!is_numeric($val))
        $val=-1;
   
    //Buscamos por orden de lista
   
    $sql="SELECT
          p.id_productos,/**/
          p.orden_lista,
        /*implementación Oscar 10.10.2018 para mostrar ubicacion y alfanumerico*/
          CONCAT(p.nombre,'<br><b style=\"font-size:11px;\">',
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
          ) as id_lista_precio
      /*Fin de Cambio Oscar 18.03.2019*/
        /*Fin de cambio*/
          FROM ec_productos p
          JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
          JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
          LEFT JOIN ec_precios_detalle pd ON /**/$lista_prec/**/ AND pd.id_producto = p.id_productos AND $can2 >= de_valor AND $can2 <= a_valor
          WHERE orden_lista='$val'
          AND id_productos > 0
          AND habilitado=1
          AND sp.id_sucursal=$user_sucursal
          AND sp.estado_suc=1";
          
    #die($sql);          
          
    $res=mysql_query($sql) or die("Error en 2:\n$sq\n\nDescripcion:\n".mysql_error());
    
    if(mysql_num_rows($res) > 0)
    {
        $row=mysql_fetch_row($res);
        
        #die(($row[4]*$can) . " y " . redondea05($row[4]*$can));
        
        $row[4]=round($row[4], 2);
        $row[3]='$'.number_format($row[4], 2);
        $row[6]=redondea05($row[4]*$can);
        $row[5]='$'.number_format($row[6], 2);
        die("exito|$row[0]|$row[1]|$row[2]|$row[3]|$row[4]|$row[5]|$row[6]|$row[7]|$row[8]|NO|$row[9]|$row[10]|$row[11]|$row[12]|$row[13]|$producto_externo");/*se mada la posición 11 para ver si se muestra paleta o el producto es maquilado Oscar 10.11.2018*/
        /*Se agrega las posición 13 para agregar el id de la lista de precios precios Oscar 18.03.2019*/
    }
    
   
    
    $sql="SELECT
          p.id_productos,
          p.orden_lista,
        /*implementación Oscar 10.10.2018 para mostrar ubicacion y alfanumerico*/
          CONCAT(p.nombre,'<br><b style=\"font-size:11px;\">',
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
          CONCAT('$',FORMAT(IF(pd.id_precio_detalle IS NULL,
                              CEIL(p.precio_venta_mayoreo),
                               IF(s.usa_oferta=1, CEIL(pd.precio_etiqueta), CEIL(pd.precio_venta))
                              ),0)),
          $prec,
          CONCAT('$',
                FORMAT(IF(pd.id_precio_detalle IS NULL,
                CEIL(p.precio_venta_mayoreo),
                IF(s.usa_oferta=1, CEIL(pd.precio_etiqueta), CEIL(pd.precio_venta))
                )*$can,0)),
          FLOOR(IF(pd.id_precio_detalle IS NULl,
                   CEIL(p.precio_venta_mayoreo),
                   IF(s.usa_oferta=1, CEIL(pd.precio_etiqueta), CEIL(pd.precio_venta)))*$can),p.id_subtipo,
                  (SELECT CONCAT(de_valor, ' a ', CONCAT('$', FORMAT(CEIL(precio_venta), 0))) 
                  FROM ec_precios_detalle WHERE id_producto = p.id_productos AND id_precio = s.id_precio AND de_valor > 1 LIMIT 1),
          p.muestra_paleta,
        /*implementación Oscar 07.05.2018*/
          IF($t=1,IF(p.precio_venta_mayoreo is NULL OR p.precio_venta_mayoreo=0,IF(sp.es_externo=0,'muestra_emergente','es_externo'),0),0)/*10*/
        /*Fin de cambio*/
          FROM ec_productos p
          JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
          JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
          LEFT JOIN ec_precios_detalle pd ON /**/$lista_prec/**/ AND pd.id_producto = p.id_productos AND $can2 >= de_valor AND $can2 <= a_valor
          WHERE id_producto='$val'
          AND id_productos > 0
          AND habilitado=1
          AND sp.id_sucursal=$user_sucursal
          AND sp.estado_suc=1";
    //die($sql);          
          
    $res=mysql_query($sql) or die("Error en 3:\n$sq\n\nDescripcion:\n".mysql_error());
    
    if(mysql_num_rows($res) > 0){
        $row=mysql_fetch_row($res);
      //capturamos resultados
        $row[4]=round($row[4],2);
        $row[3]='$'.number_format($row[4],2);
        $row[6]=redondea05($row[4]*$can);
        $row[5]='$'.number_format($row[6],2);        
        
        die("exito|$row[0]|$row[1]|$row[2]|$row[3]|$row[4]|$row[5]|$row[6]|$row[7]|$row[8]|NO|$row[9]|$row[10]");
    }
    die("No se encontro un producto que cumpla con los valores insertados, intente con otro!!!");
    
 ?>