<?php
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');
	include( 'getDatosCombo.php' );

	$combos = new Combos();
	$product_provider_price_query = "AND pr.precio_pieza in(ax.precioMinimo)";
	if( $_POST['test_query_2022'] == 'omitir_condicion' ){
		$product_provider_price_query = "";
	}
//extraemos datos por POST
	extract($_POST);
	//die( 'p|rovs: ' . $provs );
//sacamos el año anterior
	$f=date('Y-m-d');
	$fe=explode("-",$f);
	$a_act=$fe[0];
	$a_ant=$fe[0]-1;
	$a_ant_2=$fe[0]-2;//sacamos 2 años atrás
	$a_ant_3=$fe[0]-3;//sacamos 3 años atrás
	$providers = $_POST['providers'];
	//die( '|<P>product_providers : </P>' . $_POST['providers'] );
	$product_provider_order = '';

	$provs = "";
	if( $_POST['providers'] != '' ){
		$provs = "AND prov.id_proveedor IN ( {$_POST['providers']} )";
	}

//	die('año anterior: '.$a_ant." id_acc:".$acc.", id_orden_Compra:".$id_oc);
if($id_oc!=0){
	//armamos consulta para sacar info de orden de compra
	$sql="SELECT	
		/*0*/ax.id_producto,/*id de producto se arrastra desde ax3*/
		/*1*/ax.ordenLista,/*orden de lista se arrastra desde ax3*/
	    /*2*/ax.nombre,/*nombre se arrastra desde ax3*/
		/*3*/IF(ax.invCompras IS NULL,0,ax.invCompras),/*inventario de compras se arrastra desde ax1*/
		/*4*/IF(ax.ventasTotales='' OR ax.ventasTotales IS NULL,0,ax.ventasTotales) as ventas,/*este registro se arrastra desde ax2*/
		/*5*/ax.invFinal,/*el inventario final se arrastra desde ax3*/
		/*6*/IF(ax.ventasTotales IS NULL OR ax.ventasTotales=0 OR ax.invFinal>ax.ventasTotales,0,(ax.ventasTotales-ax.invFinal))AS pedido,/* este no se usa*/
		/*7*/IF(CONCAT('$',pr.precio_pieza,':') IS NULL,'Sin proveedor:',CONCAT('$',pr.precio_pieza))AS provProd,/*se consulta desde la principal*/
		/*8*/IF(pr.presentacion_caja IS NULL,'1',pr.presentacion_caja) AS Presentacioncaja,/*presentación por caja se consulta en la principal*/
		/*9*/IF(MIN(pr.precio/pr.presentacion_caja) IS NULL,0,MIN(pr.precio/pr.presentacion_caja))AS precio,/*percio se consulta e n la prncipal*/
		/*10*/IF(prov.id_proveedor IS NULL,'0',prov.id_proveedor) AS id_prov,/*id de proveedor se consulta en la principal*/
	    /*11*/ax.resurtible,/*campo resurtible se arrastra desde ax3*/
	    /*12*/'vtasAnt',/*- este no se usa...*/
	    /*13*/ROUND(ax.pedidoPzas*$factor),/*-ax.ventas; lo multiplicamos por -1 para cconvertirlo en positivo*/
	    /*14*/prov.nombre_comercial,
	    /*15*/ax.notas,/*este no se usa*/
	    /*16es_nuevo*/ax.pedidoPzas
        FROM(
        /**/SELECT
        /**/  	ax1.id_producto,
        /**/	ax1.ordenLista,
		/**/	ax1.nombre,
		/**/	ax1.resurtible,
		/**/	ax1.precioMinimo,
        /**/	ax1.invFinal,
        /**/	ax1.es_nuevo,
        /**/   	ax1.ventasTotales,
        /**/	ax1.notas,
        /**/	ax1.cantidad_pedido AS pedidoPzas,/*Calculo en piezas*/
		/**/	IF(ma1.id_movimiento_almacen IS NULL,0,SUM(md1.cantidad*tm1.afecta)) AS invCompras	
		/**/	
		FROM(
			SELECT
        /**/	ax2_1.id_producto,
        /**/	ax2_1.ordenLista,
		/**/	ax2_1.nombre,
		/**/	ax2_1.resurtible,
		/**/	ax2_1.precioMinimo,
        /**/	ax2_1.invFinal,
        /**/	ax2_1.es_nuevo,
        /**/	ax2_1.cantidad_pedido,
        /**/	ax2_1.notas,
        /**/	ax2_1.ventas-IF(dev.id_devolucion IS NULL,0,SUM(dd.cantidad)) AS ventasTotales
        /**/	FROM(
        /**/	/**/SELECT/*calculamos las ventas*/
        /**/    /**/	ax2.id_producto,
    	/**/	/**/	ax2.ordenLista,
        /**/    /**/   	ax2.nombre,
        /**/    /**/	ax2.resurtible,
		/**/	/**/	ax2.precioMinimo,
		/**/	/**/    ax2.invFinal,
		/**/	/**/ 	ax2.es_nuevo,
		/**/	/**/ 	ax2.cantidad_pedido,
		/**/	/**/	ax2.notas,
		/**/	/**/	IF(pd.id_pedido_detalle IS NULL,0,SUM(pd.cantidad)) AS ventas
		/**/	/**/	/*IF(SUM(pd.cantidad) IS NULL,0,SUM(pd.cantidad)) AS ventas*/
        /**/    /**/    FROM(
        /**/    /**/   	/**/SELECT/*calculamos el inventario actual del año corriendo*/
        /**/    /**/   	/**/	ax3.id_producto,
        /**/    /**/	/**/	ax3.ordenLista,
		/**/	/**/	/**/	ax3.nombre,	
		/**/	/**/	/**/	ax3.resurtible,
		/**/	/**/	/**/	ax3.precioMinimo,
		/**/	/**/	/**/	ax3.cantidad_pedido,
		/**/	/**/	/**/	ax3.notas,
		/**/	/**/	/**/	SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1,0,md.cantidad*tm.afecta)) AS invFinal,
		/**/	/**/	/**/	IF(md.id_movimiento_almacen_detalle IS NULL,1,0) AS es_nuevo
		/**/	/**/	/**/	FROM(
		/**/	/**/	/**/	/**/SELECT
										p.id_productos AS id_producto,
		/**/	/**/	/**/			p.orden_lista AS ordenLista,/*orden de lista desde tabla de productos*/
		/**/	/**/	/**/			p.nombre AS nombre,/*nombre del producto desde la tabla de productos*/
		/**/	/**/	/**/			p.es_resurtido AS resurtible,/*Resurtible/no resurtible desde tabla de productos*/
		/**/	/**/	/**/			ocd.cantidad AS cantidad_pedido,
		/**/	/**/	/**/			ocd.observaciones AS notas,
										1 AS precioMinimo
		/**/	/**/	/**/			FROM ec_productos p
		/**/	/**/	/**/			LEFT JOIN ec_oc_detalle ocd ON p.id_productos=ocd.id_producto
		/**/	/**/	/**/			LEFT JOIN ec_ordenes_compra oc ON ocd.id_producto=oc.id_orden_Compra
		/**/	/**/	/**/			LEFT JOIN ec_proveedor_producto pr_prod ON oc.id_proveedor=pr_prod.id_proveedor
		/**/	/**/	/**/			WHERE ocd.id_orden_compra=$id_oc 
		/**/	/**/	/**/			GROUP BY ocd.id_producto
		/**/	/**/	/**/	/**/)ax3
		/**/	/**/	/**/	LEFT JOIN ec_movimiento_detalle md ON ax3.id_producto=md.id_producto
		/**/	/**/	/**/	LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
		/**/	/**/	/**/	LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
		/**/	/**/	/**/	LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
        /**/	/**/	/**/	WHERE 1
        /**/	/**/	/**/	GROUP BY ax3.id_producto
		/**/	/**/	/**/)ax2
		/**/	/**/	LEFT JOIN ec_pedidos_detalle pd ON ax2.id_producto=pd.id_producto
		/**/	/**/	LEFT JOIN ec_pedidos ped on pd.id_pedido=ped.id_pedido
        /**/	/**/	WHERE 1 AND pd.es_externo=0/*filtro implementado por Oscar 15-08-2018 para no contar ventas externas*//*$f_ventas_resurt$f_ventas_inicial*/
        /**/   	/**/	GROUP BY ax2.id_producto 
		/**/   	/**/	)ax2_1
		/**/   	/**/	LEFT JOIN ec_devolucion_detalle dd ON ax2_1.id_producto=dd.id_producto
		/**/   	/**/ LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
		/**/   	/**/	WHERE 1
		/**/   	/**/	GROUP BY ax2_1.id_producto 
        /**/   	/**/)ax1
		/**/	LEFT JOIN ec_movimiento_detalle md1 ON ax1.id_producto=md1.id_producto
		/**/	LEFT JOIN ec_movimiento_almacen ma1 ON md1.id_movimiento = ma1.id_movimiento_almacen
		/**/	LEFT JOIN ec_tipos_movimiento tm1 ON ma1.id_tipo_movimiento = tm1.id_tipo_movimiento AND tm1.id_tipo_movimiento=1
		/**/	JOIN ec_almacen alm1 ON ma1.id_almacen=alm1.id_almacen
        /**/	WHERE 1
        /**/   	GROUP BY ax1.id_producto
        /**/)ax
		LEFT JOIN ec_proveedor_producto pr ON ax.id_producto=pr.id_producto /*AND pr.precio_pieza in(ax.precioMinimo)*/
        LEFT JOIN ec_proveedor prov ON pr.id_proveedor=prov.id_proveedor 
		WHERE ax.id_producto>1 GROUP BY ax.id_producto";
}else{
/*implementado por Oscar 29.08.2019 para mostrar o no los proveedores productos sin precio*/
	$excluye_sin_precio='';
	/*if($pov_sin_precio==1){
		$excluye_sin_precio=' AND pr_prod.precio>0';
	}*/
	switch ( $_POST['product_provider_type'] ) {
		case '1':
			$product_provider_order = "ORDER BY pr_prod.fecha_ultima_actualizacion_precio ASC";
		break;
		case '2':
			$product_provider_order = "ORDER BY pr_prod.fecha_ultima_compra ASC";
		break;
		case '3':
			$product_provider_order = "ORDER BY pr_prod.precio_pieza ASC";
		break;
	}
/*fin de cambio Oscar 29.08.2019*/
	include("consultaCalculaPedido.php");
}
	$eje=mysql_query($sql)or die("ok|Error al calcular pedido\n\n".mysql_error()."\n\n".$sql);		
echo'ok|<table border="0" width="99.99%" style="margin:0;padding:0;" id="lista_de_prods">';
	$c=0;
	//$d=0;//prueba
	while($r=mysql_fetch_row($eje)){
		$c++;$d++;
		if($c%2==0){
			$color="#E6E8AB";
		}else{
			$color="#BAD8E6";
		}
	//aqui pintamos de amarillo la fila si ya se pidió el producto anteriormente
		if($filt_pend==-1 AND $r[19]!=0 AND $id_oc==0){//si es mostrar todos
			$color="#FFFF00";
		}
		//die('check: '.$ch);
		if($r[11]==1){
			$ch="checked";
		}else{
			$ch="";
		}
	//marcamos en rojo productos nuevos
		$fuente_registro="";
		if($r[16]==1){
			$fuente_registro='style="color:red;"';
		}
	//convertimos a cajas dependiendo si es orden de compra nueva o ya existe
		if($id_oc==0){
			$n_cajas=($r[13]-$r[5])/$r[8];
		}else{
			$n_cajas=($r[16]/$r[8]);
		}
	/*implementación Oscar 28.08.2018 para no mostrar cajas en negativo*/
		if($n_cajas<=0){
			$n_cajas=0;
		}
	/*fin de cambio 28.08.2018*/

	//echo 'pedido '.$r[2].' : '.$r[13]."/".$r[9]."=".$n_cajas.'<br>';
		//if($n_cajas>0||$prod_nuevos==1&&$r[16]==1){
		if($n_cajas>0||$prod_nuevos==1 && $r[16]==1||$filt_invalidos==1){
			$n_piezas=round($n_cajas*$r[8]);//-($r[5])
			/*if($r[4]<$r[5]){//si las ventas son menores al inventario
				$n_piezas=0;
				$n_cajas=0;
			}*/
			/*if($r[0]==2202){
				echo $r[13]."/".r[8]."<br>";
				die($n_cajas."*".$r[8]."-".$r[5]);
			}*/
		echo '<tr style="background:'.$color.';height:40px;" id="f_'.$c.'" onclick="resalta('.$c.',\'click\');" tabindex="'.$c.'">';//fila
		//resurtible/no Resurtible'
			echo '<td width="7%" align="center"><input type="checkbox" id="re_surt_'.$c.'" '.$ch;
			if($id_oc==0){
				echo ' onclick="resurtimiento('.$c.');"';
			}
		//botón de la gráfica
			echo '> <button id="grafica_btn_' . $c . '" onclick="graficar_inv_vtas('.$r[0].', ' . $c . ');" style="background:transparent;margin:5 5 5 15;" title="Gráfica de Ventas e Inventario">';
			echo '<img src="../../../img/especiales/grafica.png" width="20px"></button></td>';
		//orden de lista
			echo '<td width="8%" '.$fuente_registro.' id="0_'.$c.'">'.$r[1].'</td>';
		//id de producto
			echo '<input type="hidden" id="id_p_'.$c.'" value="'.$r[0].'">';
		//agregamos variable que guarda numero de presentación para recalcular
			if($id_oc!=0){	
				echo '<input type="hidden" id="pres_'.$c.'" value="'.$r[8].'">';
			}
		//nombre
			echo '<td width="15%" id="1_'.$c.'" '.$fuente_registro.'>'.$r[2].'</td>';
		//inventario compras
			echo '<td width="8%" title="Año '.$a_tool.' stock total='.$r[20].', Inventario Final='.($r[21]).'" id="2_'.$c.'" '.$fuente_registro.'>'.$r[3].'</td>';
		//ventas del año anterior
			echo '<td width="8%" id="3_'.$c.'" '.$fuente_registro.'>'.$r[4].'</td>';
		//inventario final
			echo '<td width="8%" id="4_'.$c.'" '.$fuente_registro.' title="Inventario total: '.$r[22].' piezas '."\n".'Inventario Matriz: '.$r[25].'">'.$r[5].'</td>';
			echo '<td width="8%"><input type="text" id="cant_p_'.$c.'" class="entrada_txt"';
		//asignamos el valor de la caja de texto de acuerdo a asi existe un id de orden de compra
			if($id_oc==0){
//				if($n_piezas>=0){
					echo ' value="'.$n_piezas.'"';
//				}else{
//					echo ' value="0"';
//				}
			}else{
				echo ' value="'.$r[16].'"';
			}
			echo 'title="';
		//le agregamos el tooltip en caso de que lo requiera
			if($filt_pend==-1 AND $r[19]!=0 AND $id_oc==0){
				echo 'Se pidieron '.$r[17].' piezas, se han recibido '.$r[18].' piezas y faltan por recibir '.$r[19].' piezas';
			}
			echo '" onkeyup="valida_camp_txt(event,'.$c.','.$id_oc.');"';
			echo ' onclick="this.select();"></td>';//pedido$N_PRODS*$r[8
			echo '<td width="8%" id="combo_prov_'.$c.'">';
		//asignamos combo o caja de texto dependienedo si es órden de compra nueva o modificación
			if($id_oc==0){
				/*echo '<select onchange="muestra_prov(this,'.$c.',2);" onclick="carga_proveedor_prod('.$c.','.$r[0].');" id="c_p_'.$c.'" class="comb" style="width:100%;">'.
					'<option value="'.$r[10].'">'.$r[7].$r[14].':'.$r[8].'pzas//'.base64_encode($r[24]).'//</option></select>';*/
				$provider_combo = str_replace('ok|', '', $combos->productProviderCombo( $r[0], $c, 0, $_POST['product_provider_type'], null, $providers, $link  ) );
				echo $provider_combo;
			}else{
				//echo 'here';
				echo $r[7];//.' caja con '.$r[8].'pzas'; '<input type="text" title="caja con '.$r[8].'" value="'.$r[7].'" class="entrada_txt">'
			}
			'</td>';//precio   <input type="hidden" id="id_prov_prod_'.$c.'" value="'.$r[10].'">
 			echo '<td width="8%" id="valor_cajas_'.$c.'" align="right" '.$fuente_registro.'>'.$n_cajas.'</td>';//cajas
			echo '<td width="8%" id="valor_monto_'.$c.'" align="right" '.$fuente_registro.'>'.ROUND($r[9]*$n_piezas).'</td>';
			if($id_oc==0||$id_oc!=0){
				echo '<td width="7%" align="center" id="config_'.$c.'"><input type="button" ';//'onclick="adm_prov_prod('.$r[0].');"'
				echo ' onclick="show_product_providers( ' . $r[0] . ',' . $c . ', \'recepcionPedidos/\' )" style="display:none;" id="b1_'.$c.'">';
				echo'<img src="../../../img/especiales/config';
			//si hay una nota en la orden de compra por modificar cambiamos el icono
				if($id_oc!=0||($r[23]!=''&&$r[23]!=null)){
					echo '_2';
				}
				echo '.png" height="20px" onclick="config_de_prod('.$r[0].','.$c.');" id="b1_'.$c.'" class="bot" title="';
			//si hay una nota y es modificación se cambia el title del icono de configuración
				if($id_oc!=0||($r[23]!=''&&$r[23]!=null)){
					echo $r[23];
				}else{
				//de lo contrario solo se deja el mensaje por default
					echo 'Configuración del producto';
				}
				echo '">';//cerramos el img src
			//metemos variable oculta para guardar la nota
				echo'<input type="hidden" id="nota_'.$c.'" value="';
			//si es modificación asignamos nota en el valor de la variable oculta de la nota por detalle de oc
				if($id_oc!=0){
					echo $r[15];
				}
				echo '"></td>';//cerramos variable oculta y celda
			}
		//ag//echo '<td width="0" style="display:none;"></td';

			echo '<td width="7%" align="center"><img src="../../../img/especiales/del.png" height="20px" onclick="elimina_fila('.$c.');" title="Eliminar del pedido" class="bot"></td>';
		echo '</tr>';
		}//fin de if prod_nvos=1
		else{
			$c-=1;
		}
	}
	echo '</table>';
	echo '<input type="hidden" id="filas_totales" value="'.$c.'">';
		//echo mysql_num_rows($eje);
//validamos que haya registros
	if($c==0){	
		die("|no se encontraron registros en la Base de Datos para calcular el pedido");
	}
?>


