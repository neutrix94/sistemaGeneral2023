<?php
	require('../../../../conect.php');
	$t1=0;
	$t2=0;
	$resAprox=0;
	extract($_POST);
	echo '<input type="hidden" id="fechaFinal" value="'.$fecha.'">';
	echo '<input type="hidden" id="horaFinal" value="'.$hrs.'">';
	//die($hrs);
	if($hrs==0){
		$h1='00:01:00';
		$h2='23:59:59';
	}else{
		$auxH=explode("~",$hrs);
		$auxH2=explode("|",$auxH[0]);
	//fijamos hora 1
		$h1=$auxH2[0].':'.$auxH2[1].':00';
		$auxH2=explode("|",$auxH[1]);
	//fijamos hora 2
		$h2=$auxH2[0].':'.$auxH2[1].':00';

	}
/*implementación Oscar 18.11.2018 para que las asistencias salgan con la fecha correcta en línea*/
  //sacamos la fecha desde el mysql
    $sql_fecha=mysql_query("SELECT current_date")or die("Error al consultar la fecha desde mysql!!!\n\n".mysql_error());
    $fecha_array=mysql_fetch_row($sql_fecha);
    //$fecha=$fecha_array[0];
    //$hora_reg=$fecha_array[1];
/*Fin de cambio Oscar 18.11.2018*/

	if($fecha==-1){
		$fecha1=$fecha_array[0];//aqui se cambia
		$ax=explode("-",$fecha1);
		//auxiliar de fecha 1
		$fechaLim1=$ax[0]."-".$ax[1]."-";
		if($ax[2]<=9){
			$fechaLim1.='0'.($ax[2]+1);
		}else{
			$fechaLim1.=($ax[2]+1);
		}
		if($fecha1=='2018-11-30'){
			$fechaLim1='2018-12-01';
		}
		//if($fecha=='2017-11-30'){
		//	$fechaLim1='2017-12-01';
		//}
		$condicion1=" WHERE pp.fecha='$fecha1' AND (pp.hora BETWEEN '$h1' AND '$h2') AND p.id_sucursal='".$user_sucursal."'";
		$condicion2=" WHERE dp.fecha='$fecha1' AND (dp.hora BETWEEN '$h1' AND '$h2') AND d.id_sucursal='".$user_sucursal."'";
	}
//cambio del 14-12-2017
	if($fecha==1){
	//declaramos fecha limite
		$fecha1=$fecha_array[0];
		$ax=explode("-",$fecha1);
	//declaramos fecha inicial
		$fechaLim1=$ax[0]."-".$ax[1]."-";
		if($ax[2]<=9){
			$fechaLim1.='0'.($ax[2]-1);
		}else{
			$fechaLim1.=($ax[2]-1);
		}
		if($fecha1=='2018-12-01'){
			$fechaLim1='2018-11-30';
		}
		$condicion1=" WHERE pp.fecha='$fechaLim1' AND (pp.hora BETWEEN '$h1' AND '$h2') AND p.id_sucursal='".$user_sucursal."'";
		$condicion2=" WHERE dp.fecha='$fechaLim1' AND (dp.hora BETWEEN '$h1' AND '$h2') AND d.id_sucursal='".$user_sucursal."'";
	}//fin del cambio
	if($fecha!=-1 && $fecha!=1){
		$aux=explode("|",$fecha);
		$fecha1=$aux[0];
	//auxiliar de fecha 1
		$ax=explode("-",$fecha1);
		$fechaLim1=$ax[0]."-".$ax[1]."-";
		if($ax[2]<=9){
			$fechaLim1.=($ax[2]);
		}else{
			$fechaLim1.=($ax[2]);
		}
		if($fecha1=='2018-11-30'){
			//$fechaLim1='2018-12-01';
		}
		$fecha2=$aux[1];
		$ax=explode("-",$fecha2);
		$fechaLim2=$ax[0]."-".$ax[1]."-";
		if($ax[2]<=9){
			$fechaLim2.=($ax[2]);
		}else{
			$fechaLim2.=($ax[2]);
		}
		if($fecha2=='2018-11-30'){
			$fechaLim2='2018-12-01';
		}
		$condicion1=" WHERE (CONCAT(pp.fecha,' ',pp.hora) BETWEEN '".$fecha1.' '.$h1."' AND '".$fechaLim2.' '.$h2."') AND p.id_sucursal='".$user_sucursal."'";
		$condicion2=" WHERE (CONCAT(dp.fecha,' ',dp.hora) BETWEEN '".$fecha1.' '.$h1."' AND '".$fechaLim2.' '.$h2."') AND d.id_sucursal='".$user_sucursal."'";
	}
	//die('fecha'.$fecha);

//sacamos total de pagos
	$sql="SELECT 
			SUM(IF(pp.es_externo=0,pp.monto,0)) as pagosPedro,
			SUM(IF(pp.es_externo=1,pp.monto,0)) as pagosExternos
			FROM ec_pedido_pagos pp
			JOIN ec_pedidos p on pp.id_pedido=p.id_pedido".$condicion1;
	$eje=mysql_query($sql) or die("Error1!!!\n".mysql_error().$sql);
	$rw=mysql_fetch_row($eje);
//guardamos las cantidades de pagos
	$entrada=round($rw[0],2);
	$entrada_externa=round($rw[1],2);//implementado por Oscar 15.08.2018 para guardar monto de productos externos
 //echo 'enttradas $ '.$sql."<br><br>";

	$sql="SELECT 
			SUM(IF(dp.es_externo=0,dp.monto,0)) as devolucionesPedro,
			SUM(IF(dp.es_externo=1,dp.monto,0)) as devolucionesExternas
			FROM ec_devolucion_pagos dp
			JOIN ec_devolucion d ON dp.id_devolucion=d.id_devolucion".$condicion2;
	$eje=mysql_query($sql) or die("Error1!!!\n".mysql_error());
	$rw=mysql_fetch_row($eje);
//restamos las devoluciones
	$entrada-=round($rw[0],2);
	$entrada_externa-=round($rw[1],2);//implementado por Oscar 15.08.2018 para guardar monto de productos externos
//echo 'devoluciones $ '.$sql."<br><br>";

//sacamos Gastos
	$sql="SELECT g.id_usuario,g.fecha,g.hora,cg.nombre,g.observaciones,g.monto
			FROM ec_gastos g 
			JOIN ec_conceptos_gastos cg ON cg.id_concepto=g.id_concepto";
	if($fecha==-1){
		$condicion=" WHERE fecha='$fecha1' AND (hora BETWEEN '$h1' AND '$h2')";
	}
	if($fecha==1){
		$condicion=" WHERE fecha='$fechaLim1' AND (hora BETWEEN '$h1' AND '$h2')";
	}
	if($fecha!=-1 && $fecha!=1){
		$condicion=" WHERE (CONCAT(g.fecha,' ',g.hora) BETWEEN '".$fecha1.' '.$h1."' AND '".$fechaLim2.' '.$h2."')";
	}
	$sql.=$condicion." AND g.id_sucursal='".$user_sucursal."'";

//echo $sql."<br>";
	$eje=mysql_query($sql) or die("Error...!!!".mysql_error().$sql);
	$res=mysql_num_rows($eje);
	$resAprox+=$res;
	$gastoTotal=0;
//sacamos los descuentos
	$sql="SELECT fecha_alta,folio_nv,total,descuento FROM ec_pedidos WHERE descuento!=0";
	if($fecha==-1){
		$condicion2=" AND fecha_alta BETWEEN '".$fecha1.' '.$h1."' AND '".$fechaLim1.' '.$h2."'";
	}
	if($fecha==1){
		$condicion2=" AND fecha_alta BETWEEN '".$fechaLim1.' '.$h1."' AND '".$fecha1.' '.$h2."'";
	}
	if($fecha!=-1 && $fecha!=1){
		$condicion2=" AND fecha_alta BETWEEN '".$fecha1.' '.$h1."' AND '".$fechaLim2.' '.$h2."'";
	}
	$sql.=$condicion2." AND id_sucursal='".$user_sucursal."'";
//echo 'Descuentos $ '.$sql."<br>";
	$eje1=mysql_query($sql)or die("Error!!!\n".mysql_error().$sql);
	$resAprox+=mysql_num_rows($eje1);
?>
		<div style="height:510px;">
		<div style="border:1px solid;width:60%;height:90%;background:white;overflow:auto;">
		<center>
			<br><hr width="90%;"><p style="font-size:20px;">Ingresos<hr width="90%"></p>
			<table width="95%" border="0">
				<tr>
					<td align="right"><b>Ingresos internos: </b></td>
					<td align="right" id="ing_int"><?php echo $entrada;?></th>
				</tr>
				<?php
					if($entrada_externa>0){
				?>
						<tr>			
							<td align="right"><b>Ingresos Externos: </b></td>
							<td align="right" id="efe_ext"><?php echo $entrada_externa;?></td>
						</tr>
				<?php
					}//fin de if $entrada_externa>0
				?>
				<tr>
					<td></td><td></td>
				</tr>
				<tr>
					<td align="right"><b>Total de Ingresos:</b></td>
					<td align="right"><?php echo $entrada+$entrada_externa;?></td>
				</tr>
				<tr>
					<td align="right" colspan="1">
							Pagos con tarjeta:
					</td>
				</tr>
				<tr>
					<td align="right">Tarjeta 1:</td>
					<td align="right"><?php echo $t1;?></td>
				</tr>
				<tr>
					<td align="right">Tarjeta 2:</td>
					<td align="right"><u><?php echo $t2;?></u></td>
				</tr>
				<tr><td><br></td></tr>
				<tr>
					<td align="right"><b>Ingresos en Efectivo:</b></td>
					<td align="right"><b><?php
											$subT=($entrada+$entrada_externa)-($t1+$t2);	
											 echo $subT;?></b></td>
				</tr>
			</table>


	<!--Tabla de gastos-->
		<br>
			<table id="gastos" width="90%" border="0" style="margin:0;">
				<tr>
					<td colspan="5" align="center" style="font-size:20px;"><hr>Gastos<hr></td>
				</tr>
				<tr>
					<th width="20%">Fecha</th>
					<th>Tipo</th>
					<th>Observaciones</th>
					<th align="right">Monto</th>	
				</tr>
			<?php
				$c=0;
				while($r=mysql_fetch_row($eje)){
					$c++;
					$gastoTotal+=$r[5];
			?>
				<tr>
					<td id="<?php echo '1_'.$c;?>" align="center"><?php echo $r[1];?></td>
					<td id="<?php echo '3_'.$c;?>"><?php echo $r[3];?></td>
					<td id="<?php echo '4_'.$c;?>"><?php echo $r[4];?></td>
					<td id="<?php echo '5_'.$c;?>"align="right"><?php echo $r[5];?></td>
				</tr>
			<?php
				}
			?>
				<tr><td><br></td></tr>
				<tr>
					<td align="right" colspan="4">Total de Gastos:</td>
					<td align="right"><?php echo '-'.$gastoTotal;?></td>
				</tr>
				<tr>
					<td align="right" colspan="4"><b>Efectivo en caja:</b></td>
					<td align="right"><b><?php echo round($subT-$gastoTotal,2);?></b></td>
				</tr>
			</table>

	<!--Tabla de descuentos-->
			<br>
			<hr width="90%">
			<p align="center" style="font-size:20px;">Descuentos</p>
			<hr width="90%">
			<table width="90%">
				<tr><th align="center">
						Fecha
					</th>
					<th align="center">
						Folio
					</th>
					<th align="right">
						Monto
					</th>
					<th align="right">
						Descuento
					</th>
				</tr>		
		<?php
			while($desc=mysql_fetch_row($eje1)){
				//echo $desc[0]."|".$desc[1]."|".$desc[2]."~";
		?>
				<tr>
					<td align="center"><?php echo $desc[0];?></td>
					<td align="center"><?php echo $desc[1];?></td>
					<td align="right"><?php echo $desc[2];?></td>
					<td align="right"><?php echo $desc[3];?></td>
				</tr>
		<?php	
			}
		?>
			</table>
	<!--Termina tabla detalle de Descuentos-->
			<br>
			<input type="hidden" id="tI" value="<?php echo $entrada+$entrada_externa;?>">
			<input type="hidden" id="ta1" value="<?php echo $t1;?>">
			<input type="hidden" id="ta2" value="<?php echo $t2;?>">
			<input type="hidden" id="i1" value="<?php echo $subT;?>">
			<input type="hidden" id="tG" value="<?php echo $gastoTotal;?>">
			<input type="hidden" id="efeF" value="<?php echo round($subT-$gastoTotal,2);?>">
			<input type="hidden" id="regist" value="<?php echo $resAprox;?>">
		</center>
		</div>
		<input type="button" value="Imprimir" class="boton" onclick="generaTicket();">
		</div>
	</center>