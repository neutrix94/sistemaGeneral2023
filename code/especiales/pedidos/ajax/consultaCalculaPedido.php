<?php
/*oscar sept-2022*/
/*******************************************************Cálculos de Pronóstico***********************************************/
	if($fechas!=""){//si es resurtimiento
	//separamos fechas
		$ax=explode("|",$fechas);
		$ant_inic=$ax[0];
		$ant_fin=$ax[1];
		$act_inic=$ax[2];
		$act_fin=$ax[3];
	
	//calculamos el monto total de las ventas en el año anterior
		$sql="SELECT SUM(pp.monto)
				FROM ec_pedido_pagos pp
				LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE pe.fecha_alta LIKE '%$a_ant%' AND pp.es_externo=0";
		$eje=mysql_query($sql)or die("Error al consultar suma ventas del año anterior!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_anterior=$r[0];//aqui guardamos el 100% de las ventas anteriores
echo '<br>mont_ant:'.$monto_anterior;

	//calculamos el monto de las ventas an base al filtro del año anterior
		$sql="SELECT IF(SUM(pp.monto) IS NULL,0,SUM(pp.monto))
			FROM ec_pedido_pagos pp
			LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE (pp.fecha BETWEEN '$ant_inic' AND '$ant_fin') AND pp.es_externo=0";
		$eje=mysql_query($sql)or die("Error al consultar suma filtrada de ventas del año anterior!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_anterior_filtrado=$r[0];//aqui guardamos el 100% de las ventas anteriores
echo '<br>mont_ant_filt:'.$monto_anterior_filtrado;

	//calculamos porcentaje de avance
		$porcentaje=ROUND(($monto_anterior_filtrado*100)/$monto_anterior,2);//aqui guardamos porcentaje
echo '<br>porc: '.$porcentaje;

	//calculamos ventas del año actual
		$sql="SELECT SUM(pp.monto)
			FROM ec_pedido_pagos pp
			LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE pe.fecha_alta LIKE '%$a_act%' AND pp.es_externo=0";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al consultar suma ventas del año actual!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_actual_filtrado=$r[0];//aqui guardamos suma de ventas año actual
echo '<br>mont_act_filt:'.$monto_actual_filtrado;

	//calculamos pronótico total de año actual
		$pronostico_actual=ROUND(($monto_actual_filtrado*100)/$porcentaje);//guardamos el monto total pronosticado
	//ventas
		$ventas="SUM(IF((ped.fecha_alta BETWEEN '".$act_inic." 00:00:01' AND '".$act_fin." 23:59:59') AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
		$condicion_ventas_maquila="IF((ped_1.fecha_alta BETWEEN '".$act_inic." 00:00:01' AND '".$act_fin." 23:59:59') AND pd_1.es_externo=0";
	//devolcuiones
		$devoluciones="SUM(IF((dev.fecha BETWEEN '".$act_inic."' AND '".$act_fin."')AND dev.es_externo=0,dd.cantidad,0)) AS devoluciones";//dev/dd
		$condicion_devoluciones_maquila="IF((dev_1.fecha BETWEEN '".$act_inic."' AND '".$act_fin."')AND dev_1.es_externo=0";
	//entradas
		$entradas_por_pedido="SUM(IF(ma1.fecha<='".$a_ant."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) AS historico_inv,
		SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_act."%' AND alm1.es_externo=0,md1.cantidad,0)) AS EntradasAnteriores";		
		$ref_a_ant="SUM(IF(ma1.fecha<='".$a_ant_2."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0))+SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_ant."%' AND alm1.es_externo=0,md1.cantidad,0)) as invAnterior,";
		$ref_a_ant.="SUM(IF(ma1.fecha<= '".$a_ant."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) as entradasAnterior";
		$a_tool=$a_ant;
	}
//si es pedido inicial
	else{
		$fechas="n/a";//declaramos fechas vacías
		//die($fechas);
		$a_tool=$a_ant_2;
		$pronostico_actual=1;
		$monto_actual_filtrado=1;
	//ventas
		$ventas="SUM(IF(ped.fecha_alta LIKE '%".$a_ant."%' AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
		$condicion_ventas_maquila="IF(ped_1.fecha_alta like '%".$a_ant."%' AND pd_1.es_externo=0";
	//devoluciones
		$devoluciones="SUM(IF(dev.fecha LIKE '%".$a_ant."%' AND dev.es_externo=0,dd.cantidad,0)) AS devoluciones";
		$condicion_devoluciones_maquila="IF(dev_1.fecha LIKE '%".$a_ant."%' AND dev_1.es_externo=0";
	//entradas
		$entradas_por_pedido="SUM(IF(ma1.fecha<='".$a_ant_2."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) AS historico_inv,
		SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_ant."%' AND alm1.es_externo=0,md1.cantidad,0)) AS EntradasAnteriores";
		$ref_a_ant="SUM(IF(ma1.fecha<='".$a_ant_3."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0))+SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_ant_2."%' AND alm1.es_externo=0,md1.cantidad,0)) as invAnterior,";
		$ref_a_ant.="SUM(IF(ma1.fecha<= '".$a_ant_2."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) as entradasAnterior";
	}

/*Implementación Oscar 02.04.2019 para sumar productos dependientes*/
		$ventas_2="SELECT ".str_replace("ped.", "ped_1.", $ventas).")";
		$devoluciones_2="SELECT ".str_replace("dev.", "dev_1.", $ventas).")";
/*Fin de Cambio Oscar 02..04.2019*/

/********************************************************Fin de cálculos de pronóstico*************************************************/
//generamos base de consulta 
/********************Nota: la siguiente consulta se basa en 4 consultas anidadas y la principal, se lee de el centro (ax3) hacia afuera (ax)***********************/
/*Filtro de pendientes por recibir*/
	if($filt_pend==1){
		$pendientes.=" AND (ax.cantPedAnt=0 OR ax.cantPedAnt='' OR ax.cantPedAnt=null)";
	}
	if($filt_pend==2){
		/*$sql.=" AND ax.cantPedAnt!=0";*/
		$pendientes.=" AND (ax.cantPedAnt-ax.cantPedRecib)>0";
	}
/*implementación Oscar 23.08.2018 para filtrar los productos nuevos*/
	if($prod_nuevos==0){
		$nuevos_prod.=" AND ax.es_nuevo=0";
	}	
/*Fin de cambio 23.08.2018*/

/*implementación Oscar 02.09.2019 para filtro de productos habilitados/deshabilitados*/
	$status_producto="AND p.habilitado=1";
	if($st_prd==1){
		$status_producto="";//"AND (p.habilitado=0 OR p.habilitado=1)";
	}
	//die($status_producto);
/*Fin de cambio Oscar 02.09.2019*/

$sql="SELECT
	/*0*/ax_4.id_productos,/*id de producto se arrastra desde ax3*/
	/*1*/ax_4.ordenLista,/*orden de lista se arrastra desde ax3*/
    /*2*/ax_4.nombre,/*nombre se arrastra desde ax3*/
	/*3*/ax_4.invCompras_1,/*inventario de compras se arrastra desde ax1*/
	/*4*/ax_4.ventas,/*este registro se arrastra desde ax2*/
	/*5*/ax_4.invFinal_1,/*el inventario final se arrastra desde ax3*/
	/*6*/ax_4.pedido,/* este no se usa*/
	/*7*/ax_4.provProd,/*se consulta desde la principal*/
	/*8*/ax_4.Presentacioncaja,/*presentación por caja se consulta en la principal*/
	/*9*/ ax_4.precio,/*percio se consulta e n la prncipal*/
	/*10*/ax_4.id_prov,/*id de proveedor se consulta en la principal*/
    /*11*/ax_4.resurtible,/*campo resurtible se arrastra desde ax3*/
    /*12*/'vtasAnt',/*- este no se usa...*/
    /*13*/ax_4.pedidoPiezas,/*-ax.ventas; lo multiplicamos por -1 para cconvertirlo en positivo*/
    /*14*/ax_4.nombre_comercial,
    /*15*/ax_4.precioMinimo,/*este no se usa*/
    /*16*/ax_4.es_nuevo,/*marca si es nuevo el producto(cuando no tiene movimientos de almacen)*/
	/*17*/ax_4.cantPedAnt,
	/*18*/ax_4.cantidadPedidaRecibida,
	/*19*/ax_4.cantidadPedidaAnetriormente,
	/*20*/ax_4.invAnterior,
	/*21*/ax_4.entradasAnterior,
	/*22*/ax_4.invFinal,
	/*23*/ax_4.observaciones,
	/*24*/ax_4.id_proveedor_producto,
	/*25*/ax_4.invFinalMatriz	
FROM(
	SELECT
	/*0*/ax.id_productos,/*id de producto se arrastra desde ax3*/
	/*1*/ax.ordenLista,/*orden de lista se arrastra desde ax3*/
    /*2*/ax.nombre,/*nombre se arrastra desde ax3*/
	/*3*/SUM(IF(ax.historico_inv<0,0,ax.historico_inv)+ax.EntradasAnteriores) AS invCompras_1,
	/*4*/IF(ax.ventasTotales='' OR ax.ventasTotales IS NULL,0,ax.ventasTotales) AS ventas,/*este registro se arrastra desde ax2*/
	/*5*/IF(ax.invFinal<0,0,ax.invFinal) AS invFinal_1,/*el inventario final se arrastra desde ax3*/
	/*6-*/IF(ax.ventasTotales IS NULL OR ax.ventasTotales=0 OR ax.invFinal>ax.ventasTotales,0,(ax.ventasTotales-ax.invFinal))AS pedido,/* este no se usa*/
	/*7*/IF(CONCAT('$',pr.precio_pieza,':') IS NULL,'Sin proveedor:',CONCAT('$',pr.precio_pieza,':'))AS provProd,/*se consulta desde la principal*/
	/*8*/IF(pr.presentacion_caja IS NULL,'1',pr.presentacion_caja) AS Presentacioncaja,/*presentación por caja se consulta en la principal*/
	/*9*/ IF(MIN(pr.precio/pr.presentacion_caja) IS NULL,0,MIN(pr.precio/pr.presentacion_caja))AS precio,/*percio se consulta e n la prncipal*/
	/*10*/IF(prov.id_proveedor IS NULL,'0',prov.id_proveedor) AS id_prov,
    /*11*/ax.resurtible,
    /*12*/'vtasAnt',/*- este no se usa...*/
    /*13*/ROUND(IF((ax.pedidoPzas)<0,0,(ax.pedidoPzas))) AS pedidoPiezas,
    /*14*/prov.nombre_comercial,
    /*15*/ax.precioMinimo,/*este no se usa*/
    /*16*/ax.es_nuevo,
	/*17*/ax.cantPedAnt,
	/*18*/IF(ax.cantPedAnt>0,ax.cantPedRecib,0) AS cantidadPedidaRecibida,
	/*19*/IF(ax.cantPedAnt>0,(ax.cantPedAnt-ax.cantPedRecib),0) AS cantidadPedidaAnetriormente,
	/*20*/ax.invAnterior,
	/*21*/ax.entradasAnterior,
	/*22*/ax.invFinal,
	/*23*/ax.observaciones,
	/*24*/IF(pr.id_proveedor_producto IS NULL,0,pr.id_proveedor_producto) AS id_proveedor_producto,
	/*25*/ax.invFinalMatriz
    FROM(
    	SELECT
    	  	ax1.id_productos,
    		ax1.ordenLista,
			ax1.nombre,
			ax1.resurtible,
			ax1.precioMinimo,
    		ax1.invFinal,
			ax1.es_nuevo,
    	   	ax1.ventasTotales,
			ax1.cantPedAnt,
			ax1.cantPedRecib,
			IF('$fechas'='n/a',
				((((ax1.ventasTotales*$factor)*$pronostico_actual)/$monto_actual_filtrado)-(ax1.cantPedAnt-ax1.cantPedRecib)),
				((((ax1.ventasTotales*$factor)*$pronostico_actual)/$monto_actual_filtrado)-(ax1.cantPedAnt-ax1.cantPedRecib))-ventasTotales) AS pedidoPzas,
			$entradas_por_pedido,
			$ref_a_ant,
			ax1.observaciones,
			ax1.invFinalMatriz
		
		FROM(
			SELECT
				ax2_3.id_productos,
       			ax2_3.ordenLista,
				ax2_3.nombre,
				ax2_3.resurtible,
				ax2_3.precioMinimo,
        		ax2_3.invFinal,
				ax2_3.es_nuevo,
				ax2_3.ventasTotales,
				ax2_3.cantPedRecib,
			
				ax2_3.cantPedAnteriormente as cantPedAnt,
				ax2_3.observaciones,
				ax2_3.invFinalMatriz
			FROM(
				SELECT
					ax2_2.id_productos,
       				ax2_2.ordenLista,
					ax2_2.nombre,
					ax2_2.resurtible,
					ax2_2.precioMinimo,
        			ax2_2.invFinal,
					ax2_2.es_nuevo,
					ax2_2.ventas AS ventasTotales,
					SUM(IF(ocd.id_oc_detalle IS NULL OR oc_1.id_estatus_oc>=4,0,ocd.cantidad)) AS cantPedAnteriormente,
					SUM(IF(ocd.id_oc_detalle IS NULL OR oc_1.id_estatus_oc>=4,0,ocd.cantidad_surtido)) AS cantPedRecib,
					ax2_2.observaciones,
					ax2_2.invFinalMatriz
				FROM(
					SELECT
					ax2_1.id_productos,
       				ax2_1.ordenLista,
					ax2_1.nombre,
					ax2_1.resurtible,
					ax2_1.precioMinimo,
        			ax2_1.invFinal,
					ax2_1.es_nuevo,
					(ax2_1.ventas+ax2_1.ventas_paquete)as ventas,
					$devoluciones,
					ax2_1.observaciones,
					ax2_1.invFinalMatriz
					FROM(
        				SELECT/*calculamos las ventas*/
        		    		ax2.id_productos,
    						ax2.ordenLista,
        		    	   	ax2.nombre,
        		    		ax2.resurtible,
							ax2.precioMinimo,
						    ax2.invFinal,
							ax2.es_nuevo,
							$ventas
							,IF((SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=ax2.id_productos LIMIT 1) IS NULL,
								0,
								(SELECT SUM(IF(ped_1.id_pedido IS NULL,
												0,
												$condicion_ventas_maquila
													,pd_1.cantidad*(SELECT cantidad FROM ec_productos_detalle WHERE id_producto_ordigen=ax2.id_productos LIMIT 1)
													,0
												)
												)
										)
								FROM
								ec_pedidos_detalle pd_1
								LEFT JOIN ec_pedidos ped_1 on pd_1.id_pedido=ped_1.id_pedido
        						WHERE pd_1.id_producto=(SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=ax2.id_productos LIMIT 1)
								)
							)AS ventas_paquete,
							ax2.observaciones,
							ax2.invFinalMatriz
    	    	    	    FROM(
    	    	    	   		SELECT
        		    	   			ax3.id_productos,
        		    				ax3.ordenLista,
									ax3.nombre,	
									ax3.resurtible,
									ax3.precioMinimo,
									SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1,0,md.cantidad*tm.afecta)) AS invFinal,
									IF(ma.id_movimiento_almacen IS NULL,1,0) AS es_nuevo,
									ax3.observaciones,
									SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1 OR alm.id_almacen!=1,0,md.cantidad*tm.afecta)) AS invFinalMatriz
									FROM(
										SELECT
											p.id_productos,
        		  							p.orden_lista AS ordenLista,
											p.nombre AS nombre,	
											p.es_resurtido AS resurtible,
											IF((pr_prod.id_producto)IS NULL OR pr_prod.precio<=-1,'1.00',MIN(pr_prod.precio_pieza)) as precioMinimo,
											p.observaciones
											FROM ec_productos p
											LEFT JOIN ec_proveedor_producto pr_prod ON p.id_productos=pr_prod.id_producto
											$prov_sin_precio/*implementado por Oscar 29.08.2019 para mostrar o no los proveedores productos sin precio*/
											WHERE 1 
											{$filtro1}
											{$f_resurtimiento}
											AND p.es_maquilado = 0/*Implementacion Oscar 01.04.2019 para excluir productos maquilados*/
											AND p.muestra_paleta = 0
											{$status_producto}
											GROUP BY p.id_productos {$product_provider_order}
										)ax3
									LEFT JOIN ec_movimiento_detalle md ON ax3.id_productos=md.id_producto
									LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
									LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
        							LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
        							WHERE 1
        							GROUP BY ax3.id_productos
								)ax2
								LEFT JOIN ec_pedidos_detalle pd ON ax2.id_productos=pd.id_producto
								LEFT JOIN ec_pedidos ped on pd.id_pedido=ped.id_pedido
        						WHERE 1
        		   				GROUP BY ax2.id_productos
        					)ax2_1
							LEFT JOIN ec_devolucion_detalle dd ON ax2_1.id_productos=dd.id_producto
							LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
							WHERE 1 
							GROUP BY ax2_1.id_productos 
						)ax2_2
						LEFT JOIN ec_oc_detalle ocd ON ax2_2.id_productos=ocd.id_producto
						LEFT JOIN ec_ordenes_compra oc_1 ON ocd.id_orden_compra=oc_1.id_orden_compra
						GROUP BY ax2_2.id_productos
        			)ax2_3
					GROUP BY ax2_3.id_productos
        		)ax1
				LEFT JOIN ec_movimiento_detalle md1 ON ax1.id_productos=md1.id_producto
				LEFT JOIN ec_movimiento_almacen ma1 ON md1.id_movimiento = ma1.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm1 ON ma1.id_tipo_movimiento = tm1.id_tipo_movimiento
        	   	LEFT JOIN ec_almacen alm1 ON ma1.id_almacen=alm1.id_almacen
        		WHERE 1
        		GROUP BY ax1.id_productos
        	)ax
			LEFT JOIN ec_proveedor_producto pr 
			ON ax.id_productos=pr.id_producto 
			{$product_provider_price_query}
        	LEFT JOIN ec_proveedor prov ON pr.id_proveedor=prov.id_proveedor 
			WHERE ax.id_productos>1
			$provs 
			$pendientes 
			$nuevos_prod 
			GROUP BY ax.id_productos ORDER BY ax.ordenLista ASC
		)ax_4
		GROUP BY ax_4.id_productos ORDER BY ax_4.ordenLista ASC";
//die( "|<textarea>{$sql}</textarea>" );

?>