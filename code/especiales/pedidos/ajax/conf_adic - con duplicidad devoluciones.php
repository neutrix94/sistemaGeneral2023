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
	$sql="SELECT nombre FROM ec_productos WHERE id_productos='$id'";
	$eje=mysql_query($sql)or die("Error al consultar el nombre del producto para la configuracion\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$titulo=$r[0];
//
	//die("FACTOR: ".$fact_prom);
		$sql="SELECT
				ax2.nombre,/*0*/
			    ax2.id_productos,/*1*/
   				ax2.estado_suc,/*2*/
				MAX(ax2.ventas),/*3*//*máximoFiltrado*/
				MAX(ax2.ventas) AS ventasPorFactor,/*4*$fact_prom*/
    			/*SUM(IF(ax2.id_sucursal,ventas,0)),
    			(SUM(IF(sucs1.id_sucursal=ax2.id_sucursal,ax2.ventasPromedio,0)))/(DATEDIFF('$fprom_al','$fprom_del')+1), promedio de la fecha filtrada*/
    			ax2.maximo,/*5*/
				ax2.id,/*6*/
				ax2.id_estacionalidad_producto,/*7*/
				ax2.fecha_alta,/*8*/
				SUM(ax2.ventas),/*9*/
				ax2.id_sucursal/*10*/
				FROM(
            	    SELECT
						ax1.id_sucursal,
						ax1.nombre,
    					ax1.id_productos,
    					ax1.estado_suc,
    					SUM(IF(ped.id_sucursal=ax1.id_sucursal AND pd.es_externo=0 AND (ped.fecha_alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59'),pd.cantidad,0)) AS ventas,
    					SUM(IF(ped.id_sucursal=ax1.id_sucursal AND pd.es_externo=0 AND (ped.fecha_alta BETWEEN '$fprom_del 00:00:01' AND '$fprom_al 23:59:59'),pd.cantidad,0)) AS ventasPromedio,
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
    		GROUP BY ax2.id_sucursal";
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
		$sql="SELECT observaciones FROM ec_productos WHERE id_productos=$id";
		$eje_nta=mysql_query($sql)or die("Error al consultar las notas de venta!!!".mysql_error());
		$nota=mysql_fetch_row($eje_nta);

	/*flechas para avanzar entre productos*/
//	die('numeros:'.$num_ant.$num_sig);
		if($num_ant!=0){
			$tmp=explode("~", $num_ant);
			$flecha_ant='<button style="padding:10px;border-radius:50%;" onclick="document.getElementById(\'f_'.$tmp[0].'\',7).click();config_de_prod('.$tmp[1].','.$tmp[0].')"><img src="../../../img/grid/first.png"></button>';
		}
		if($num_sig!=0){
			$tmp=explode("~", $num_sig);
			$flecha_sig='<button style="padding:10px;border-radius:50%;" onclick="document.getElementById(\'f_'.$tmp[0].'\').click();config_de_prod('.$tmp[1].','.$tmp[0].')"><img src="../../../img/grid/last.png"></button>';
		}

	/**/
?>
	<p align="center" style="position: fixed;right:-5%;width: 35%;top:15%;">
		<textarea id="campo_nota" style="width:60%;height:300px;" onkeyup="activa_edic_prec(event,0,'nota');" placeholder="Notas..."><?php echo $nota[0];?></textarea>
		<br><button onclick="guarda_nota(<?php echo $id;?>);" id="guardar_nota_prods" style="padding:10px;display:none;">Guardar</button>
	</p>

<!--Creamos cuerpo de grid-->
<div style="top:1%;position:relative;width:70%;left:5%;overflow-y: scroll;height: 400px;background:white;">
	<table bgcolor="white">
		<tr>
			<th width="10%"><?php echo $flecha_ant; ?></th>
			<?php 
				$sql="SELECT es_resurtido FROM ec_productos WHERE id_productos=$id";
				$eje_prd=mysql_query($sql)or die("Error al consultar si el producto es resurtido!!!\n".mysql_error());
				$r_res=mysql_fetch_row($eje_prd);
				if($r_res[0]==1){
					$ch="checked";
				}
				echo '<th width="80%" colspan="5"><input type="checkbox" id="re_surtir" '.$ch;
				if($id_oc==0){
					echo ' onclick="resurtimiento('.$id.',2);"';
				}
				echo 'title="Resurtimiento">';
			?>
			<b style="font-size: 23px;/*color: black;*/"><?php echo $titulo;?></b></th>
			<th width="10%"><?php echo $flecha_sig; ?></th>
		</tr>
		<tr>
			<th width="20%">Sucursal</th>
			<th width="10%">Estado Sucursal</th>
			<th width="12%">Máximo ventas</th>
			<th width="12%">Ventas por factor</th>
			<th width="12%">Total Ventas</th>
			<th width="12%">Estacionalidad Actual</th>
			<th width="12%">Nueva estacionalidad</th>
			<!--<th width="6%">Guardar</th>-->	
		</tr>
	<?php
		$c=0;
		while($r=mysql_fetch_row($eje)){
			$c++;
			if($r[2]==1){
				$ch="checked";
			}else{
				$ch="";
			}
//restamos las devoluciones
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
//fin de resta de devolucione

//echo $r1[0];
		echo '<tr bgcolor="#FFF8BB" id="fil_gr_'.$c.'" tabindex="'.$c.'" onfocus="colorea('.$c.');" onblur="descolorea('.$c.');">';
			echo '<td id="reg_gr_1_'.$c.'" onclick="simulador_tooltip(this,'.$r[10].');">'.$r[0].'</td>';//nombre de sucursal
			/*onmouseout="esconde_tooltip();"*/
			echo '<td align="center"><input type="checkbox" id="reg_gr_2_'.$c.'" '.$ch.' style="padding:10px;" value="'.$r[6].'" onclick="activa_edic_prec(event,'.$c.',2);"></td>';//hab/deshab en sucursal
			echo '<td id="reg_gr_3_'.$c.'" align="right">'.$r[3].'</td>';//maximo ventas
			echo '<td id="reg_gr_4_'.$c.'" align="right">'.$r[4].'</td>';//ventas por factor
			echo '<td align="right">'.$r[9].'</td>';//total ventas
			echo '<td id="id_estac_'.$c.'" style="display:none;">'.$r[7].'</td>';//id del registro de estacionalidad
			echo '<td id="estacionalidad_'.$c.'" align="right">'.$r[5].'</td>';//estacionalidad máxima actual
			echo '<td><input type="text" id="nva_estac_'.$c.'" class="camb" onkeyup="activa_edic_prec(event,'.$c.',2);" onclick="resalta_grid(2,'.$c.');"></td>';//caja para nueva estacionalidad máxima
			//echo '<td><input type="button" value="guardar" id="bot_conf_'.$c.'" disabled onclick="cambia_estacionalidad('.$r[1].','.$r[7].','.$c.');"></td>';
		echo '</tr>';	
		}
	?>
		<tr>
			<td colspan="7" align="center">
			</td>
		</tr>
	</table>
</div>
	<input type="hidden" id="fil_tot_conf_adic" value="<?php echo $c;?>">
	<p align="center" class="bot_submnu" style="left:20%;">
		<img src="../../../img/especiales/calendario.png" title="Cambiar filtros de Fecha" onclick="carga_filtros_prom(<?php echo $id;?>);" class="bot_1" height="50px">
		<span><b>Filtros</b></span>
	</p>
	<p align="center" class="bot_submnu" style="left:45%;top:-55px;">
		<img src="../../../img/especiales/precio.png" title="Ver Precios" onclick="carga_precios(<?php echo $id.','.$num_act;?>)" class="bot_1" height="50px">
		<span><b>Precios</b></span>
	</p>
	<p align="center" id="bot_guarda_conf" class="bot_submnu" style="left:70%;top:-22%;" onclick="guarda_cambios_config(<?php echo $num_act;?>);">
		<img src="../../../img/especiales/save.png" title="Guardar" class="bot_1" height="50px">
		<span><b>Guardar</b></span>
	</p>
	<style>
		.bot_submnu{
			position:relative;
			top:5%;
			border:1px solid blue;border-radius:100%;
			width:95px;
			height:95px;
			background:white;
		}
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