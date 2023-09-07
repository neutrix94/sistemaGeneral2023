<?php
	include('../../../../conectMin.php');//incluimos libreria de conexion
//extraemos datos por POST
	extract($_POST);

//sacamos el año anterior y actual
	$f=date('Y-m-d');
	$fe=explode("-",$f);
	/*$a_act=$fe[0];*/
	$a_ant=$fe[0]-1;
	$a_act=$fe[0]-1;
	/*
		fmax_del:fecha_max_del,
				fmax_al:fecha_max_al,
				fprom_del:fecha_prom_del,
				fprom_al:fecha_prom_al
	*/
	/*******************************************************Cálculos de Pronóstico***********************************************Deshabilitado por Oscar 27.08.2018
	*/if($tipo_ped==2){//si es resurtimiento
	//calculamos el monto total de las ventas en el año anterior
		$sql="SELECT SUM(pp.monto)
				FROM ec_pedido_pagos pp
				LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE pe.fecha_alta LIKE '%$a_ant%'";
		$eje=mysql_query($sql)or die("Error al consultar suma ventas del año anterior!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_anterior=$r[0];//aqui guardamos el 100% de las ventas anteriores
//die($sql);
	//calculamos el monto de las ventas an base al filtro del año anterior
		$sql="SELECT IF(SUM(pp.monto) IS NULL,0,SUM(pp.monto))
			FROM ec_pedido_pagos pp
			LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE pp.fecha BETWEEN '$fpa_del 00:00:01' AND '$fpa_al 23:59:59'";
		$eje=mysql_query($sql)or die("Error al consultar suma filtrada de ventas del año anterior!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_anterior_filtrado=$r[0];//aqui guardamos el 100% de las ventas anteriores
	//calculamos porcentaje de avance
		$porcentaje=ROUND(($monto_anterior_filtrado*100)/$monto_anterior,2);//aqui guardamos porcentaje
		//die($sql);

	//calculamos ventas del año actual
		$sql="SELECT SUM(pp.monto)
			FROM ec_pedido_pagos pp
			LEFT JOIN ec_pedidos pe ON pp.id_pedido=pe.id_pedido
			WHERE pe.fecha_alta LIKE '%$a_act%'";
		$eje=mysql_query($sql)or die("Error al consultar suma ventas del año actual!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$monto_actual_filtrado=$r[0];//aqui guardamos suma de ventas año actual
	//calculamos pronótico total de año actual
		$pronostico_actual=ROUND(($monto_actual_filtrado*100)/$porcentaje);//guardamos el monto total pronosticado
	//armamos condiciones
		$ventas="SUM(IF((ped.fecha_alta BETWEEN '".$fpac_del." 00:00:01' AND '".$fpac_al." 23:59:59') AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
		$devoluciones="SUM(IF((dev.fecha BETWEEN '".$fpa_del."' AND '".$fpa_al."')AND dev.es_externo=0,dd.cantidad,0)) AS devoluciones";
	}
//si es pedido inicial
	else{
		$monto_actual_filtrado=1;
		$pronostico_actual=1;
	//armamos condiciones
		$ventas="SUM(IF(ped.fecha_alta LIKE '%".$a_ant."%' AND pd.es_externo=0,pd.cantidad,0)) AS ventas";
		$devoluciones="SUM(IF(dev.fecha LIKE '%".$a_ant."%' AND dev.es_externo=0,dd.cantidad,0)) AS devoluciones";
		/*
		$f_ventas_inicial=" AND (ped.fecha_alta LIKE '%".$a_ant."%')";//ventas del año anterior
		$filtro_dev_actual=" AND dev.fecha like '%$a_ant%'";
		$f_compras=" AND (ma1.fecha LIKE '%".$a_ant."%')";//compras del año anterior
		$f_fech_ma3="";*/
	}

	$pron="IF(SUM(md.cantidad*tm.afecta) IS NULL,0,(SUM(md.cantidad*tm.afecta)*-1))";
/*Implementación Oscar 13.02.2019 paar el título de la configuración*/
	$sql="SELECT CONCAT('(',orden_lista,') ',nombre) FROM ec_productos WHERE id_productos='$id'";
	$eje=mysql_query($sql)or die("Error al consultar el nombre del producto para la configuracion\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$titulo=$r[0];
//
	//die("FACTOR: ".$fact_prom);
	$sql="SELECT
			ax3.nombre,/*0*/
			ax3.id_productos,/*1*/
			ax3.estado_suc,/*2*/
			ax3.Ventas,/*3*//*máximoFiltrado*/
			ax3.ventasPorFactor,/*4*$fact_prom*/
			ax3.maximo,/*5*/
			ax3.id,/*6*/
			ax3.id_estacionalidad_producto,/*7*/
			ax3.fecha_alta,/*8*/
			ax3.Ventas2,/*9*/
			ax3.id_sucursal,/*10*/
			IF(psi.id_prod_sin_inv IS NULL,'',COUNT(psi.id_prod_sin_inv))/**/
		FROM(
			SELECT
				ax2.nombre,/*0*/
			    ax2.id_productos,/*1*/
   				ax2.estado_suc,/*2*/
				MAX(ax2.ventas) as Ventas,/*3*//*máximoFiltrado*/
				MAX(ax2.ventas)*$fact_prom AS ventasPorFactor,/*4*$fact_prom*/
    			ax2.maximo,/*5*/
				ax2.id,/*6*/
				ax2.id_estacionalidad_producto,/*7*/
				ax2.fecha_alta,/*8*/
				SUM(ax2.ventas) as Ventas2,/*9*/
				ax2.id_sucursal/*10*/
				FROM(
            	    SELECT
						ax1.id_sucursal,
						ax1.nombre,
    					ax1.id_productos,
    					ax1.estado_suc,
    					SUM(IF(ped.id_sucursal=ax1.id_sucursal AND pd.es_externo=0 AND (ped.fecha_alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59'),pd.cantidad,0)) AS ventas,
    					ax1.maximo,
    					ax1.id,
    					ax1.id_estacionalidad_producto,
    					ped.fecha_alta
					FROM(
						SELECT
							s.id_sucursal,
					    	s.nombre,
    					    p.id_productos,
   					   		sp.estado_suc,
    		    			ep.maximo,
        					sp.id,
        					ep.id_estacionalidad_producto
      					FROM ec_productos p
	  					LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
	  					LEFT JOIN sys_sucursales s on s.id_sucursal=sp.id_sucursal
	  					JOIN ec_estacionalidad_producto ep ON s.id_estacionalidad=ep.id_estacionalidad AND p.id_productos=ep.id_producto
      					WHERE s.id_sucursal>1 AND p.id_productos=$id
     					GROUP BY s.id_sucursal
					)ax1
    				LEFT JOIN ec_pedidos_detalle pd ON ax1.id_productos=pd.id_producto
   					LEFT JOIN ec_pedidos ped ON ax1.id_sucursal=ped.id_sucursal AND pd.id_pedido=ped.id_pedido 
   					WHERE 1 
    				GROUP BY ax1.id_sucursal,DATE_FORMAT(ped.fecha_alta, '%Y%m%d'),ax1.id_productos
    			)ax2
			JOIN sys_sucursales sucs1 ON ax2.id_sucursal=sucs1.id_sucursal
    		GROUP BY ax2.id_sucursal
    	)ax3
		LEFT JOIN ec_productos_sin_inventario psi ON ax3.id_sucursal=psi.id_sucursal
		AND psi.id_producto IN($id) AND (psi.alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59')
		GROUP BY ax3.id_sucursal";

    	//die($sql);

	//ejecutamos consulta
		$eje=mysql_query($sql)or die("Error al obtener configuraciones del producto!!!\n\n".$sql_no."\n\n".mysql_error());
		echo 'ok|';
?>	
	<center>
		<p style="position:absolute;top:10px;color:white;font-size:25px;width:80%;left:10%;" align="center"><b id="info_cambios"></b></p>
	</center>
	<p align="right">
		<input type="button" value="X" onclick="cierra_eme_prod();"
		style="padding:10px;top:20px;right:20px;position:relative;background:red;color:white;border-radius:5px;font-size:25px;">
	</p>
<!--Creamos entrada de texto de notas-->
	<!--<p align="center" style="font-size: 25px;color: white;">

	</p><br>-->
<?php

	/*flechas para avanzar entre productos*/
//	die('numeros:'.$num_ant.$num_sig);
		if($num_ant!=0){
			$tmp=explode("~", $num_ant);
			$flecha_ant='<button style="padding:10px;border-radius:50%;" id="btn_get_-1" onclick="document.getElementById(\'f_'.$tmp[0].'\',7).click();config_de_prod('.$tmp[1].','.$tmp[0].')"><img src="../../../img/grid/first.png"></button>';
		}
		if($num_sig!=0){
			$tmp=explode("~", $num_sig);
			$flecha_sig='<button style="padding:10px;border-radius:50%;" id="btn_get_1" onclick="document.getElementById(\'f_'.$tmp[0].'\').click();config_de_prod('.$tmp[1].','.$tmp[0].')"><img src="../../../img/grid/last.png"></button>';
		}

	/**/
?>
	
	<div align="center" style="position: absolute;right:-3%;width: 35%;top:15%;">
	<?php
		include( 'notas.php' );
	?>
		
	</div>

<!--Creamos cuerpo de grid-->
<div style="top:-12%;position:relative;width:70%;left:1%;overflow-y: scroll;max-height: 570px;background:white;" id="config_adic_container">
	<table bgcolor="white" class="table-bordered"><!-- style="border : 2px solid white !important;" -->
	<thead style="position : sticky; top: 0; z-index : 2;">
		<tr>
			<?php 
				$sql="SELECT es_resurtido,habilitado,omitir_alertas FROM ec_productos WHERE id_productos=$id";
				//die($sql);
				$eje_prd=mysql_query($sql)or die("Error al consultar si el producto es resurtido!!!\n".mysql_error());
				$r_res=mysql_fetch_row($eje_prd);
				
				if($r_res[0]==1){
					$ch="checked";
				}
				
				$check_prd="";
				if($r_res[1]==1){
					$check_prd='checked';
				}

				if($r_res[2]==1){
					$check_web='checked';
				}
			?>
			<th width="10%" style="text-align : center;"><?php echo $flecha_ant; ?><br>Habilitado<br>
				<input type="checkbox" id="check_multi_deshabilita" onclick="habilita_deshabilita_prd(<?php echo $id;?>);" <?php echo $check_prd;?>>
			</th>

			<?php

				echo '<th width="80%" colspan="5">Res.. <input type="checkbox" id="re_surtir" '.$ch;
				if($id_oc==0){
					echo ' onclick="resurtimiento('.$id.',2);"';
				}
				echo 'title="Resurtimiento">';
			?>
			<b style="font-size: 23px;/*color: black;*/"><?php echo $titulo;?></b>
				Proyección ( <?php echo getProyectionDetail( $id, $proy_date, $proy_date_from, $proy_date_to, $link, 1 ); ?> pzas )
				<button type="button" class="btn btn-light" onclick="get_proyection_by_product( <?php echo $id; ?>);">
					<i id="proyection_btn_txt" class="icon-down-open">Detalle</i>
				</button>
				<div id="proyection_container" style="position: relative; width : 100%; max-height : 200px; overflow-y: auto;">
					<?php
						echo getProyectionDetail( $id, $link );
					?>
				</div>
			</th>
			
			<th width="10%" colspan="2" style="text-align : center;"><?php echo $flecha_sig; ?><br>Omitir Web<br>
				<input type="checkbox" id="check_omit_web" onclick="habilita_deshabilita_web(<?php echo $id;?>);" <?php echo $check_web;?>>
			</th>
		</tr>
		<tr>
			<th width="20%">Sucursal</th>
			<th width="10%">Estado Sucursal</th>
			<th width="12%">Máximo ventas</th>
			<th width="12%">Ventas por factor</th>
			<th width="10%">Total Ventas</th>
			<th width="10%">Estacionalidad Actual</th>
			<th width="10%">Nueva estacionalidad</th>
			<th width="6%">Inv<br>Insuficiente</th>
			<!--<th width="6%">Guardar</th>-->	
		</tr>
	</thead>
	<tbody>
	<?php
		$c=0;
		$sales_total = 0;
		while($r=mysql_fetch_row($eje)){
			$c++;
			if($r[2]==1){
				$ch="checked";
			}else{
				$ch="";
			}

/*sumamos los productos maquilados*/
	$tooltip_maximo="";
	$tooltip_maximo_factor="";
//consultamos si el producto es origen de una maquila
	$sql="SELECT id_producto,cantidad FROM ec_productos_detalle WHERE id_producto_ordigen=$r[1] LIMIT 1";
	$eje_1=mysql_query($sql)or die("Error al consultar si el producto es origen de alguna maquila!!!".mysql_error());
	if(mysql_num_rows($eje_1)==1){
		$r_2=mysql_fetch_row($eje_1);
		$sql="SELECT
				SUM(ax.ventasMaquilado),
				MAX(ax.ventasMaquilado)
			FROM(
				SELECT
					SUM(IF(ped.id_sucursal=$r[10] AND pd.es_externo=0 
					AND (ped.fecha_alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59'), (pd.cantidad*$r_2[1]),0)) AS ventasMaquilado,
					DATE_FORMAT(ped.fecha_alta, '%Y%m%d') as fecha_alta,
					p.id_productos
				FROM ec_productos p
				LEFT JOIN ec_pedidos_detalle pd ON p.id_productos=pd.id_producto
   				LEFT JOIN ec_pedidos ped ON pd.id_pedido=ped.id_pedido AND ped.id_sucursal=$r[10] AND ped.id_sucursal=$r[10]
   				WHERE p.id_productos=$r_2[0]
    			GROUP BY DATE_FORMAT(ped.fecha_alta, '%Y%m%d'),p.id_productos
    		)ax
			GROUP BY ax.id_productos";
//die($sql);

    	$eje_2=mysql_query($sql)or die("Error al consultar las ventas del producto maquilado!!!\n".mysql_error());
		if(mysql_num_rows($eje_2)==1){
			$r1=mysql_fetch_row($eje_2);
			$tooltip_maximo=' title="Máximo de Ventas producto origen= '.$r[3]."\n".'Máximo de ventas maquila= '.($r1[1])."\nTotal= ".($r[3]+$r1[1]).'"';
			$tooltip_maximo_factor=' title="Máximo de Ventas producto origen= '.$r[3]."\n".'Máximo de ventas maquila= '.($r1[1])."\nTotal: ".($r[3]+$r1[1]);
			$tooltip_maximo_factor.=' x factor='.($r[3]+$r1[1])*$fact_prom.'"';
			$r[9]=$r[9]+$r1[0];
			//$r[4]=($r[4])*$fact_prom;			
		}
	}
/*	$sql="SELECT
			IF(
				(SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=$r[1] LIMIT 1) IS NULL,
				0,
				(SELECT 
					SUM(IF(ped_1.id_pedido IS NULL,
						0,
						IF(ped_1.id_sucursal=$r[10] AND pd_1.es_externo=0 AND (ped_1.fecha_alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59'),
							pd_1.cantidad*(SELECT cantidad FROM ec_productos_detalle WHERE id_producto_ordigen=$r[1] LIMIT 1),
							0
						)
					)
					)
				FROM
				ec_pedidos_detalle pd_1
				LEFT JOIN ec_pedidos ped_1 on pd_1.id_pedido=ped_1.id_pedido
        		WHERE pd_1.id_producto=(SELECT id_producto FROM ec_productos_detalle WHERE id_producto_ordigen=$r[1] LIMIT 1)
				)
				)AS ventas";
		$eje_1=mysql_query($sql)or die("Error al consultar las ventas del producto maquilado!!!\n".mysql_error());
		if(mysql_num_rows($eje_1)==1){
			$r1=mysql_fetch_row($eje_1);
			$r[9]=$r[9]+$r1[0];
			$r[4]=($r[4])*$fact_prom;			
		}
	/*

		$sql="SELECT 
				SUM(IF(dev.id_sucursal=$r[10],dd.cantidad,0)) as dev
			FROM ec_productos p 
			LEFT JOIN ec_devolucion_detalle dd ON p.id_productos=dd.id_producto 
			LEFT JOIN ec_devolucion dev ON dev.id_devolucion=dd.id_devolucion 
			WHERE p.id_productos=$id AND dev.fecha BETWEEN '$fmax_del' AND '$fmax_al'
			AND dev.id_sucursal=$r[10]
			GROUP BY p.id_productos";
		$eje_1=mysql_query($sql)or die("Error al consultar las devoluciones!!!\n".mysql_error());
		$r1=mysql_fetch_row($eje_1);

		$r[9]=$r[9]-$r1[0];
		$r[4]=($r[4])*$fact_prom;
//fin de resta de devoluciones*/

//echo $r1[0];
		echo '<tr bgcolor="#FFF8BB" id="fil_gr_'.$c.'" tabindex="'.$c.'" onfocus="colorea('.$c.');" onblur="descolorea('.$c.');">';
			echo '<td id="reg_gr_1_'.$c.'" onclick="simulador_tooltip(this,'.$r[10].');">'.$r[0].'</td>';//nombre de sucursal
			/*onmouseout="esconde_tooltip();"*/
			echo '<td align="center"><input type="checkbox" id="reg_gr_2_'.$c.'" '.$ch.' style="padding:10px;" value="'.$r[6].'" onclick="activa_edic_prec(event,'.$c.',2);"></td>';//hab/deshab en sucursal
			echo '<td id="reg_gr_3_'.$c.'" align="right"'.$tooltip_maximo.'>'.$r[3].'</td>';//maximo ventas
			echo '<td id="reg_gr_4_'.$c.'" align="right"'.$tooltip_maximo_factor.'>'.$r[4].'</td>';//ventas por factor
			echo '<td align="right">'.$r[9].'</td>';//total ventas
			echo '<td id="id_estac_'.$c.'" style="display:none;">'.$r[7].'</td>';//id del registro de estacionalidad
			echo '<td id="estacionalidad_'.$c.'" align="right">'.$r[5].'</td>';//estacionalidad máxima actual
			echo '<td><input type="text" id="nva_estac_'.$c.'" class="camb" onkeyup="activa_edic_prec(event,'.$c.',2);" onclick="resalta_grid(2,'.$c.');"></td>';//caja para nueva estacionalidad máxima
			$color_extra="";
			$boton_emerge="";
			if($r[11]!='' AND $r[11]!=null){
				$color_extra=' style="background:orange;"';
				$boton_emerge='<button type="button" onclick="detalle_sin_inventario('.$id.','.$r[10].');"'.$color_extra.'>'.$r[11].'</button>';
			}
			echo '<td align="center">';
				echo $boton_emerge;
			echo '</td>';
			$sales_total += $r[9];
			//echo '<td><input type="button" value="guardar" id="bot_conf_'.$c.'" disabled onclick="cambia_estacionalidad('.$r[1].','.$r[7].','.$c.');"></td>';
		echo '</tr>';	
		}
	?>
		<tr>
			<td colspan="7" align="center">
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5" style="text-align : right;">
				<?php
					echo $sales_total;
				?>
			</td>
		</tr>
	</tfoot>
	</table>
</div>
	<input type="hidden" id="fil_tot_conf_adic" value="<?php echo $c;?>">
	<!--p align="center" class="bot_submnu" style="left:10%;"-->
	<button 
		class="btn btn-light bot_submnu" 
		title="Cambiar filtros de Fecha" 
		onclick="carga_filtros_prom(<?php echo $id;?>);" 
		style="bottom:0;position : absolute;left:10%;"
	>
		<img src="../../../img/especiales/calendario.png" height="40px">
		<span><b>Filtros</b></span>
	</button>


	<button  
		class="btn btn-light bot_submnu" 
		title="Ver precios de venta"  
		style="bottom:0;position : absolute;left:30%;"
		onclick="carga_precios(<?php echo $id.','.$num_act;?>)">
		<img src="../../../img/especiales/precio.png" title="Ver Precios" height="40px">
		<span><b>Precios</b></span>
	</button>
	<button 
		class="btn btn-light bot_submnu" 
 		id="bot_guarda_conf"
		style="bottom:0;position : absolute;left:50%;"
		onclick="guarda_cambios_config(<?php echo $num_act;?>);">
		<img src="../../../img/especiales/save.png" title="Guardar" class="bot_1" height="40px">
		<span><b>Guardar</b></span>
	</button>
	<style>
		/*.bot_submnu{
			position:relative;
			top:5%;
			border:1px solid blue;border-radius:100%;
			width:95px;
			height:95px;
			background:white;
		}*/
		.bot_submnu:hover{
			background:gray;
			color:white;
		}
		.camb{
			padding:5px;
			width: 89.5%;
		}
		.bot_1{
			padding: 10px;
			border-radius:5px;
		}
	</style>