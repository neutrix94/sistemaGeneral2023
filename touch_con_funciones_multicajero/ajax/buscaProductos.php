<?php     
    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    mysql_set_charset("utf8");
    
    extract($_GET);
    
    $clave=trim($clave);
    
    $noms=explode(" ", $clave);

//implementado por Oscar(28-10-2017)
    $pedido1="";
    $pedido2="";
    if(isset($p)){
    	$pedido1=" JOIN ec_pedidos_detalle epd ON epd.id_producto=p.id_productos";
    	$pedido2=" AND epd.id_pedido=".$p;
    }

$sql_1_1="SELECT 
    			p.id_paquete,
    			CONCAT('paquete',p.id_paquete,'|',p.nombre) 
          	FROM ec_paquetes p 
          	LEFT JOIN sys_sucursales_paquete sp ON p.id_paquete=sp.id_paquete
          	LEFT JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
          	WHERE p.activo=1
          	AND sp.id_sucursal=$user_sucursal
          	AND sp.estado_suc=1";
if($t!=null){
	//die('here');//IF(s.mostrar_ubicacion=1,IF(sucP.ubicacion_almacen_sucursal!='',CONCAT(sucP.ubicacion_almacen_sucursal,' ',p.nombre),p.nombre),p.nombre)
	$sql="SELECT
			p.orden_lista,
			CONCAT(p.orden_lista,' | ',p.nombre,' | ','<span class=\'txtNegrita\'>',
					IF(pd.id_precio_detalle IS NULL OR sucP.es_externo=1,CONCAT('<b style=\"color:yellow;\">No aplica Precio de mayoreo',' $',

/*Implementación Oscar 28.02.2018 para que si el producto es externo y no entra en mayoreo tome el precio de venta de la lista externa*/
					IF(sucP.es_externo=0,
						(SELECT IF(pd_1.precio_venta IS NULL,'Sin precio',pd_1.precio_venta) 
						FROM ec_precios_detalle pd_1 
						WHERE pd_1.id_producto=p.id_productos 
						AND pd_1.id_precio=s.id_precio LIMIT 1),
					/*si el producto es externo consultamos el precio de venta en la lista de precios externa configurada en la sucursal*
						(SELECT IF(pd_1.precio_venta IS NULL,'Sin precio',pd_1.precio_venta) 
						FROM ec_precios_detalle pd_1 
						WHERE pd_1.id_producto=p.id_productos 
						AND pd_1.id_precio=s.lista_precios_externa LIMIT 1)*/''),
					'</b>'),
/*Fin de cambio Oscar 28.02.2018*/

						CONCAT('precio de mayoreo $',pd.precio_venta)),'</span>',
			/*implementación de Oscar 09.10.2018 para agregar ubicación y código afanumérico*/
					CONCAT('<p style=\"font-size:15px;color:red;\">',
						IF(sucP.ubicacion_almacen_sucursal IS NULL OR sucP.ubicacion_almacen_sucursal='','',CONCAT('Ubicación: ',sucP.ubicacion_almacen_sucursal,'  |  ')),
						CONCAT('CLAVE: ',p.clave),
					'</p>')
			)/*fin del concat*/	
		/*fin de cambio*/
			FROM ec_productos p
			RIGHT JOIN sys_sucursales_producto sucP ON p.id_productos=sucP.id_producto AND sucP.id_sucursal=$user_sucursal and sucP.estado_suc=1
			JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
			LEFT JOIN ec_precios_detalle pd ON p.id_productos=pd.id_producto AND pd.id_precio=$id_lista_precio
			WHERE p.id_productos > -1
		/*modisficación Oscar 12.11.2018 para que también se pueda buscar por alfanumérico*/
			AND p.habilitado=1 $pedido2 AND((";
		//agudizamos la búsqeda
			for($i=0;$i<sizeof($noms);$i++){
       		 
       		 	if($i>0){
       		 		$sql.=" AND";
       		 	}
       		 	$sql.=" p.nombre LIKE '%".$noms[$i]."%'";
    		}
    		$sql.=") OR(p.clave LIKE '%".$clave."%'))";
    /*Fin de cambio Oscar 12.11.2018*/
}else{

    $sql="SELECT
				aux.orden_lista,
				IF(aux.es_externo=0,descripcion,
					CONCAT(
					aux.orden_lista,
					' | ',
					aux.nombre,
					' | ', 
					IF(pv1.precio_venta IS NULL,
					CONCAT('<span class=\'txtNegrita\'>','sin precio','</span>'),
						CONCAT(
							'<span class=\'txtNegrita\'>$',
							FORMAT(pv1.precio_venta, 0),
							'</span>'
						)
						),
						' | <span class=\'txtVerde\'>',
						IF(
						(
							SELECT
							1
							FROM ec_precios_detalle
							WHERE de_valor > 1
							AND id_precio=-1/**/
							AND id_producto=aux.id_productos
							LIMIT 1
						) IS NULL,
						'',	
					(
						SELECT
						GROUP_CONCAT(
							de_valor,
							' X ',
							ROUND(precio_venta*de_valor)
						)
						FROM ec_precios_detalle
						WHERE de_valor > 1
						AND id_precio=-1/**/
						AND id_producto=aux.id_productos
					)
				),	
				'</span>'))
			FROM(	
/*IF(s.mostrar_ubicacion=1,IF(sucP.ubicacion_almacen_sucursal!='',CONCAT(sucP.ubicacion_almacen_sucursal,' ',p.nombre),p.nombre),p.nombre) */
    		SELECT
			p.orden_lista,
			p.id_productos,
			p.nombre AS nombre,
			CONCAT(
				p.orden_lista,
				' | ',
				p.nombre,
				' | ', 
				IF(pv.precio_venta IS NULL,
					CONCAT('<span class=\'txtNegrita\'>','sin precio','</span>'),
					CONCAT(
						'<span class=\'txtNegrita\'>$',
						FORMAT(pv.precio_venta, 0),
						'</span>'
					)
				),
				' | <span class=\'txtVerde\'>',
				IF(
					(
						SELECT
						1
						FROM ec_precios_detalle
						WHERE de_valor > 1
						AND id_precio=s.id_precio
						AND id_producto=p.id_productos
						LIMIT 1
					) IS NULL,
					'',	
					(
						SELECT
						GROUP_CONCAT(
							de_valor,
							' X ',
							ROUND(precio_venta*de_valor)
						)
						FROM ec_precios_detalle
						WHERE de_valor > 1
						AND id_precio=s.id_precio
						AND id_producto=p.id_productos
					)
				),	
				'</span>',
			/*implementación de Oscar 09.10.2018 para agregar ubicación y código afanumérico*/
				CONCAT('<br><span style=\"font-size:15px;color:red;\">',
					IF(s.mostrar_ubicacion=1, 
						IF($user_sucursal=1,
							CONCAT('Ubicación: ',p.ubicacion_almacen,'  |  '),
							IF(sucP.ubicacion_almacen_sucursal IS NULL OR sucP.ubicacion_almacen_sucursal='',
								'',
								CONCAT('Ubicación: ',sucP.ubicacion_almacen_sucursal,'  |  ')
							)
						),
						''	
					),
					IF(s.mostrar_alfanumericos=0,'',CONCAT('CLAVE: ',p.clave)),
					'</span>')
			/*fin de cambio 09.10.2018*/
			)/*fin de concat*/ AS descripcion,
			sucP.es_externo,
			s.lista_precios_externa	
			FROM ec_productos p
			RIGHT JOIN sys_sucursales_producto sucP ON p.id_productos=sucP.id_producto AND sucP.id_sucursal=$user_sucursal and sucP.estado_suc=1
			JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
			LEFT JOIN ec_precios_detalle pv ON s.id_precio=pv.id_precio AND pv.id_producto = p.id_productos /*AND pv.de_valor = 1*/
			$pedido1
			WHERE p.id_productos > -1
	/*modificación Oscar 12.11.2018 para que también se pueda buscar por alfanumérico*/
			AND p.habilitado=1 $pedido2 AND((";
		//agudizamos la búsqeda
			for($i=0;$i<sizeof($noms);$i++){
       		 
       		 	if($i>0){
       		 		$sql.=" AND";
       		 	}
       		 	$sql.=" p.nombre LIKE '%".$noms[$i]."%'";
    		}
    		$sql.=") OR(p.clave LIKE '%".$clave."%'))";
    /*Fin de cambio Oscar 12.11.2018*/

		//complementamos búsqueda
			$sql.=")aux
			LEFT JOIN ec_precios_detalle pv1 ON pv1.id_precio=aux.lista_precios_externa AND pv1.id_producto=aux.id_productos";
}//fin de else
    $tamano=sizeof($noms);
    //echo 'tamaño'.$tamano;      
    if($t==null||$t==''){
    	$sql.=" GROUP BY aux.id_productos 
    		ORDER BY aux.orden_lista";
    } else{
    	$sql.=" GROUP BY p.id_productos 
    		ORDER BY p.orden_lista";
    }
//    die($sql_1_1);
    //$sql="(".$sql_1_1.") UNION (".$sql.")";     
    //die($sql);
    $res=mysql_query($sql) or die("Error en:\n$sq\n\nDescripción:".mysql_error());
    $num=mysql_num_rows($res);
/*implementación de Oscar 03.04.2018 parq búsqueda de paquetes*/
	//else{//buscamos en paquetes
    	$sql="SELECT 
    			p.id_paquete,
    			CONCAT('paquete',p.id_paquete,'|',p.nombre) 
          	FROM ec_paquetes p 
          	LEFT JOIN sys_sucursales_paquete sp ON p.id_paquete=sp.id_paquete
          	LEFT JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal
          	WHERE p.activo=1
          	AND sp.id_sucursal=$user_sucursal
          	AND sp.estado_suc=1";
    //afinamos busqueda
    	for($i=0;$i<sizeof($noms);$i++){
        	$sql.=" AND p.nombre LIKE '%".$noms[$i]."%'";
    	}
    	$eje=mysql_query($sql) or die("Error al buscar coincidencias en paquetes!!!\n\n".$sql."\n\n".mysql_error());
    echo "exito";    
    	$num_1=mysql_num_rows($eje);
    //regresamos resulados de paquetes
    	if($num_1>0 && $t==0){
    		for($i=0;$i<$num_1;$i++){
        		$row=mysql_fetch_row($eje);
        		echo "←";
        		echo $row[0]."~".$row[1];
    		}
    	}
    //regresamos resultados de productos
    	if($num>0){
		for($i=0;$i<$num;$i++){
        	$row=mysql_fetch_row($res);
        	echo "←";
        	echo $row[0]."~".$row[1];
    	}          
    }/*
    if($num+$num_1<=0){
    	echo "←~Sin coincidencias!!!";
    }*/
    //}
?>