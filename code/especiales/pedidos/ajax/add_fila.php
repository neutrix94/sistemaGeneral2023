<?php
	include("../../../../conectMin.php");
	extract($_POST);
//insertamos fila en tabla de proveedores
	if($flag==1){
		$sql="SELECT id_proveedor,nombre_comercial FROM ec_proveedor WHERE id_proveedor>0";
		$eje=mysql_query($sql)or die("Error al consultar lista de proveedores!!!\n\n".$sql."\n\n".mysql_error());
	//creamos opciones de proveedor
		$prov='<select id="nvo_pr_'.$c.'" onchange="cambia_list_prec(this,'.$c.',2);" style="padding:8px;width:90%">';
		$prov.='<option value="-1">Seleccionar</option>';
		while($r=mysql_fetch_row($eje)){
			$prov.='<option value="'.$r[0].'">'.$r[1].'</option>';
		}
		$prov.="</select>";

	//retornamos fila
			echo 'ok|<tr bgcolor="#FFF8BB" style="height:40px;width="100%" id="fila_prov_'.$c.'">';
				echo '<td id="id_prov_'.$c.'" style="display:none;">'.$r[1].'</td>';//id del proveedor (oculto)
				echo '<td width="40%" id="nom_prov_'.$c.'">'.$prov.'</td>';//nomre del proveedor
			//entradas de texto
				echo '<td width="20%"  align="center"><input type="text" value="'.$r[3].'" class="ent_txt" id="p_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');"></td>';
				echo '<td width="20%"  align="center"><input type="text" value="'.$r[4].'" class="ent_txt" id="c_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');"';
				echo ' onblur="verifica_prov_prod(this);"></td>';
				echo  '<td width="20%"  align="center"><input type="text" value="'.$r[5].'" class="ent_txt" id="clave_'.$c.'" onkeyup="activa_edic_prec(event,'.$c.',3);" onclick="resalta_grid(3,'.$c.');this.select();"></td>';
				//echo '<td width="10%" align="center"><input type="button" value="Editar" onclick="edita_prov('.$r[0].','.$c.')" id="edit_'.$c.'" disabled></td>';
				echo '<td width="10%" align="center"><input type="button" value="X" onclick="document.getElementById(\'fila_prov_'.$c.'\').remove();" id="edit_'.$c.'"></td>';
				echo '<td id="id_prov_prod_'.$c.'" style="display:none;">0</td>';
			echo '</tr>';
	}
	if($flag==2){
		/*tipo_ped:tipo_filtro,
				fpa_del:filt_ant_del,
				fpa_al:filt_ant_al,
				fpac_del:filt_act_del,
				fpac_al:filt_act_al}
		*/
		//sacamos el año anterior y actual
			$f=date('Y-m-d');
			$fe=explode("-",$f);
			$a_ant=$fe[0]-1;
			$a_act=$fe[0];

			$providers = $_POST['providers'];
			//die( $providers ) ;

		if($tipo_ped==2){//si el pedido es por resurtimiento
			$ventas="SUM(IF((pe.fecha_alta BETWEEN '".$fpac_del." 00:00:01' AND '".$fpac_al." 23:59:59') AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
			$entradas_por_pedido="SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_act."%' AND alm1.es_externo=0,md1.cantidad,0))";
			
			$condicion_ventas_maquila="IF((ped_1.fecha_alta BETWEEN '".$fpac_del." 00:00:01' AND '".$fpac_al." 23:59:59') AND pd_1.es_externo=0";
			//$entradas_anteriores="SUM(IF(ma1.fecha<='".$a_ant."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) AS historico_inv";
			/*$filtro_de_devoluciones_1=*/
		}else{//si es pedido libre o inicial
			$ventas="SUM(IF(pe.fecha_alta LIKE '%".$a_ant."%' AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
			$entradas_por_pedido="SUM(IF(ma1.id_tipo_movimiento=1 AND ma1.fecha LIKE '%".$a_ant."%' AND alm1.es_externo=0,md1.cantidad,0))";	
			
			$condicion_ventas_maquila="IF((ped_1.fecha_alta like '%".$a_ant."%') AND pd_1.es_externo=0";
			$a_ant=$a_ant-1;
				
		}	
		$entradas_anteriores="SUM(IF(ma1.fecha<='".$a_ant."-12-31' AND alm1.es_externo=0,md1.cantidad*tm1.afecta,0)) AS historico_inv";			
		if($id_oc==0){
		//si es nueva orden de compra (edición)
			$precio_prov="IF(CONCAT('$',MIN(pr.precio),':',prov.nombre_comercial) IS NULL,'Sin proveedor',CONCAT('$',MIN(pr.precio_pieza),':',prov.nombre_comercial)),";
		}else{
		//si es edición de orden de compra
			$precio_prov="CONCAT('$',MIN(pr.precio)),";
		}
//generamos base de consulta
	$sql="SELECT	
	/*0*/ax.id_productos,
	/*1*/ax.ordenLista,
    /*2*/ax.nombre,
	/*3*/ax.invCompras+IF(ax.historico_inv<=0 OR ax.historico_inv IS NULL,0,ax.historico_inv) as invCompras,/*IF(SUM(md1.cantidad*tm1.afecta) IS NULL,0,SUM(md1.cantidad*tm1.afecta)) AS invCompras,*/
	/*4*/ax.ventasTotales,
	/*5*/SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1,0,md.cantidad*tm.afecta)) AS invFinal,
	/*6*/IF(ax.ventasTotales-1 IS NULL,0,(ax.ventasTotales-1*1)),
	/*7*/$precio_prov
	/*8*/IF(pr.presentacion_caja IS NULL,'',pr.presentacion_caja) AS cajas,
	/*9*/IF(ax.precio*10 IS NULL,0,ax.precio*10),
	/*10*/IF(pr.id_proveedor IS NULL,'0',pr.id_proveedor) AS id_prov,
	/*11*/ax.es_resurtido,
	/*12*/pr.id_proveedor_producto,
	/*13*/SUM(IF(ma.id_movimiento_almacen IS NULL OR alm.es_externo=1 OR alm.id_almacen!=1,0,md.cantidad*tm.afecta)) AS invFinalMatriz
        FROM(
        /**/SELECT 
		/**/	ax1_1.id_productos,
        /**/ 	ax1_1.ordenLista,
		/**/	ax1_1.nombre,
		/**/	ax1_1.precio,
				ax1_1.es_resurtido,
        /**/    ax1_1.ventas/*-IF(ax1_1.devoluciones IS NULL,0,ax1_1.devoluciones) */ as ventasTotales,
		/**/	$entradas_por_pedido AS invCompras,
				$entradas_anteriores	
		/**/FROM(
        /**//**/SELECT
        /**//**/	ax1.id_productos,
        /**//**/	ax1.ordenLista,
		/**//**/	ax1.nombre,
		/**//**/	ax1.precio,
        /**//**/	(ax1.ventas+ax1.ventas_paquete) AS ventas,
        			ax1.es_resurtido
/*        			$devoluciones*/
		/**//**/	/*IF(dev.id_devolucion IS NULL,0,SUM(dd.cantidad)) AS devoluciones*/
		/**//**/FROM(
        /**//**//**/   	SELECT
        /**//**//**/		p.id_productos,
        					p.es_resurtido,
        /**//**//**/		p.orden_lista AS ordenLista,
		/**//**//**/		p.nombre AS nombre,	
		/**//**//**/		p.precio_compra AS precio,
							$ventas,
							IF((SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=$id LIMIT 1) IS NULL,
								0,
								(SELECT SUM(IF(ped_1.id_pedido IS NULL,
												0,
												$condicion_ventas_maquila
													,pd_1.cantidad*(SELECT cantidad FROM ec_productos_detalle WHERE id_producto_ordigen=$id LIMIT 1)
													,0
												)
												)
										)
								FROM
								ec_pedidos_detalle pd_1 /*ON ax2.id_productos=pd.id_producto*/
								LEFT JOIN ec_pedidos ped_1 on pd_1.id_pedido=ped_1.id_pedido
        						WHERE pd_1.id_producto=(SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=$id LIMIT 1)
								)
							)AS ventas_paquete
        /**//**//**/		/*IF(pd.id_pedido_detalle IS NULL,0,SUM(pd.cantidad)) AS ventas*/
		/**//**//**/	FROM ec_productos p
        /**//**//**/	LEFT JOIN ec_pedidos_detalle pd ON pd.id_producto=p.id_productos 
        /**//**//**/	LEFT JOIN ec_pedidos pe on pe.id_pedido=pd.id_pedido/*AND pe.fecha_alta LIKE '%{$a_ant}%'*/
        /**//**//**/	WHERE p.id_productos=$id
        /**//**//**/)ax1
		/**//**	LEFT JOIN ec_devolucion_detalle dd ON ax1.id_productos=dd.id_producto
		/**//**	LEFT JOIN ec_devolucion dev ON dd.id_devolucion=dev.id_devolucion
		/**//**/	WHERE 1 /*AND dd.id_producto=$id*/
        /**//**/	GROUP BY ax1.id_productos
		/**//**/)ax1_1
		/**/	LEFT JOIN ec_movimiento_detalle md1 ON ax1_1.id_productos=md1.id_producto
		/**/	LEFT JOIN ec_movimiento_almacen ma1 ON md1.id_movimiento = ma1.id_movimiento_almacen /*AND ma1.fecha LIKE '%{$a_ant}%' /*AND ma1.id_sucursal=4*/
		/**/	LEFT JOIN ec_tipos_movimiento tm1 ON ma1.id_tipo_movimiento = tm1.id_tipo_movimiento
        /**/  	LEFT JOIN ec_almacen alm1 ON ma1.id_almacen=alm1.id_almacen
        /**/	WHERE 1
        /**/	GROUP BY ax1_1.id_productos
        /**/)ax
			LEFT JOIN ec_movimiento_detalle md ON md.id_producto=ax.id_productos
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen /*AND ma.fecha LIKE '%{$a_ant}%' /*and ma.id_sucursal=4*/
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
			LEFT JOIN ec_proveedor_producto pr ON ax.id_productos=pr.id_producto
            LEFT JOIN ec_proveedor prov ON prov.id_proveedor=pr.id_proveedor 
            AND prov.id_proveedor IN( $providers )
            WHERE 1 
			GROUP BY ax.id_productos,pr.id_proveedor_producto
			LIMIT 1";
		$eje=mysql_query($sql)or die("Error al consultar datos para agregar columna!!!\n\n".mysql_error()."\n\n".$sql."\n\n".mysql_error());
			//die(mysql_num_rows($eje)."kdsf");
	//asignamos color
		if($c%2==0){
			$color="#E6E8AB";
		}else{
			$color="#BAD8E6";
		}
	//formamos fila
		$r=mysql_fetch_row($eje);
		$ch="";
		//die('Check: '.$r[10]);
		if($r[11]==1){
			$ch=" checked";
		}
		//print_r($r);
		//die($r[0]);
		echo 'ok|<tr style="background:'.$color.';height:40px;" id="f_'.$c.'" onclick="resalta('.$c.',\'click\');">';//fila
			echo '<td width="7%" align="center"><input type="checkbox" id="re_surt_'.$c.'" '.$ch.' onclick="resurtimiento('.$c.');"';
			echo '> <button onclick="graficar_inv_vtas('.$r[0].');" style="background:transparent;margin:5 5 5 15;" title="Gráfica de Ventas e Inventario">';
			echo '<img src="../../../img/especiales/grafica.png" width="20px"></button></td>';
		//orden de lista
			echo '<td width="8%">'.$r[1].'</td>';
		//id de producto
			echo '<input type="hidden" id="id_p_'.$c.'" value="'.$r[0].'">';
			if($id_oc!=0){	
			//agregamos variable que guarda numero de presentación para recalcular
				echo '<input type="hidden" id="pres_'.$c.'" value="'.$r[8].'">';
			}
		//nombre
			echo '<td width="15%" id="1_'.$c.'">'.$r[2].'</td>';//onclick="graficar_inv_vtas('.$r[0].');"
		//inventario total
			echo '<td width="8%" id="2_'.$c.'">'.$r[3].'</td>';
		//ventas del año anterior
			echo '<td width="8%" id="3_'.$c.'">'.$r[4].'</td>';
		//inventario final
			echo '<td width="8%" id="4_'.$c.'">'.$r[5].'</td>';
		//cantidad de piezas
			echo '<td width="8%"><input type="text" value="'.$n_piezas.'" id="cant_p_'.$c.'" class="entrada_txt" onkeyup="valida_camp_txt(event,'.$c.',0);" onclick="this.select();"></td>';//pedido$N_PRODS*$r[8
		//celda de proveedores, piezas por caja
			echo '<td width="8%" id="combo_prov_'.$c.'">';//precio   <input type="hidden" id="id_prov_prod_'.$c.'" value="'.$r[10].'">
 		
		//asignamos combo o caja de texto dependienedo si es órden de compra nueva o modificación
			if($id_oc==0){
				echo '<select onchange="muestra_prov(this,'.$c.',2);" onclick="carga_proveedor_prod('.$c.','.$r[0].');" id="c_p_'.$c.'" class="comb" style="width:100%;">'.
					'<option value="'.$r[10].'">'.$r[7].$r[14].':'.$r[8].'pzas//'.base64_encode($r[12]).'//</option></select>';
			}else{
				echo $r[7];//.' caja con '.$r[8].'pzas'; '<input type="text" title="caja con '.$r[8].'" value="'.$r[7].'" class="entrada_txt">'
			}
			echo '</td>';
		//metemos variable oculta para guardar la nota
			echo'<input type="hidden" id="nota_'.$c.'" value="">';
		//cajas
 			echo '<td width="8%" align="right" id="valor_cajas_'.$c.'">'.$n_cajas.'</td>';//cajas
		//total $
			echo '<td width="8%" id="valor_monto_'.$c.'" align="right">'.ROUND($r[9]*$n_piezas,2).'</td>';
		//onclick="adm_prov_prod('.$r[0].');"
			echo '<td width="7%" align="center"><input type="button" onclick="show_product_providers( ' . $r[0] . ',' . $c . ', \'recepcionPedidos/\' );" style="display:none;" id="b1_'.$c.'">'.
			'<img src="../../../img/especiales/config.png" height="20px" onclick="config_de_prod('.$r[0].');" id="b1_'.$c.'" class="bot" title="Configuración del producto"></td>';
			echo '<td width="7%" align="center"><img src="../../../img/especiales/del.png" height="20px" onclick="elimina_fila('.$c.');" title="Eliminar del pedido" class="bot"></td>';
		echo '</tr>';
	}
/*

		echo '<td width="8%" id="combo_prov_'.$c.'"><select onchange="muestra_prov(this,'.$c.',2);" onclick="carga_proveedor_prod('.$c.','.$r[0].');" id="c_p_'.$c.'" class="comb" style="width:100%;">'.
			'<option value="'.$r[7].$r[14].':'.$r[8].'">'.$r[7].$r[14].':'.$r[8].'pzas</option></select></td>';
*/
/*********************************************************************FILA EN BLANCO PARA PRECIOS DE VENTA******************************************************************/
	if($flag==3){
	//consultamos lista de precios
		$sql="SELECT id_precio,nombre FROM ec_precios WHERE id_precio>0";
	//formamos combo
		$eje=mysql_query($sql)or die("Error al consultar listas de precios!!!\n\n".$sql."\n\n".mysql_error());
		$l_p='<select id="nvo_prec_'.$c.'" style="padding:8px;width:90%" onchange="cambia_list_prec(this,'.$c.',1);"><option value="-1">---Seleccione una lista de precios---</option>';
		while($r=mysql_fetch_row($eje)){
			$l_p.='<option value="'.$r[0].'">'.$r[1].'</option>';
		}
		$l_p.="</select>";
	//retornamos fila
		echo 'ok|<tr bgcolor="#FFF8BB" id="fila_prec_'.$c.'" >';		
			echo '<td id="nom_lista_prec_'.$c.'">';
				echo'<input type="hidden" value="0" id="precio_'.$c.'">';//id del precio
				echo'<input type="hidden" value="0" id="id_lista_precio_'.$c.'">';//id de lista de precio
				echo $l_p;//nombre de lista de precio
			echo '</td>';//id del registro del precio
			echo '<td align="center"><input type="text" id="de_'.$c.'" value="'.$r[2].'" onkeyup="activa_edic_prec(event,'.$c.',1)" onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//cantidad minima
			echo '<td align="center"><input type="text" id="a_'.$c.'" value="'.$r[3].'" onkeyup="activa_edic_prec(event,'.$c.',1)"  onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//cantidad maxima
			echo '<td align="center"><input type="text" id="mont_'.$c.'" value="'.$r[4].'" onkeyup="activa_edic_prec(event,'.$c.',1)"  onclick="resalta_grid(1,'.$c.');" class="ent_prec"></td>';//precio de venta
			echo '<td align="center"><input type="checkbox" id="ofer_'.$c.'" '.$ch.' onclick="activa_edic_prec(event,'.$c.',1);"></td>';//oferta
			//echo '<td align="center"><input type="button" class="camb" id="edit_prec_'.$c.'" value="guardar" onclick="modifica_precio('.$c.',1);" disabled></td>';//editar
			echo '<td align="center"><input type="button" class="camb" value="x" onclick="elimina_fila('.$c.',2);"></td>';//quitar
		echo '</tr>';	
	}
?>