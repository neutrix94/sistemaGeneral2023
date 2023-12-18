<?php
/*version casa 1.0*/
//corte anterior
	$sql="SELECT id_sesion_caja FROM ec_sesion_caja WHERE id_cajero=$user_id AND hora_fin!='00:00:00' ORDER BY id_sesion_caja DESC LIMIT 1";
	$eje=mysql_query($sql)or die("Error al consultar el ultmio corte!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
	$id_corte_anterior=$r[0];
//afiliaciones
	$sql="SELECT a.id_afiliacion,a.no_afiliacion 
		FROM ec_afiliaciones a
		LEFT JOIN ec_afiliaciones_cajero ac ON ac.id_afiliacion=a.id_afiliacion
		WHERE ac.id_cajero='$user_id' AND ac.activo=1 AND a.id_afiliacion>0";
	$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
	//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
	$tarjetas_cajero='';
	$c=0;
	while($r=mysql_fetch_row($eje)){
	//sumamos los pagos del cajero en caso de tener pagos
		$sql="SELECT SUM(IF(id_cajero_cobro IS NULL,0,monto)) FROM ec_cajero_cobros WHERE id_cajero='$user_id' AND fecha='$fecha_sesion' AND hora>='$hora_inicio_sesion' AND hora<='$hora_cierre_sesion' 
		AND id_afiliacion='$r[0]'";
		$eje_tar=mysql_query($sql)or die("Error al consultar los pagos con tarjetas!!!<br>".mysql_error());
		$r1=mysql_fetch_row($eje_tar);
		$total=$r1[0];
		if($total==''){
			$total=0;
		}
		$c++;
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td colspan="2" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Tarjeta '.$c.':</p></td>';
		$tarjetas_cajero.='</tr>';
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td align="center">';
				$tarjetas_cajero.='<select id="tarjeta_'.$c.'" class="filtro" style="width:95%"><option value="'.$r[0].'">'.$r[1].'</option>';
			$tarjetas_cajero.='</td>';
			$tarjetas_cajero.='<td>';
				$tarjetas_cajero.='<input type="text" class="entrada" id="t'.$c.'" value="'.$total.'" onkeyup="cambia_valor(this,\'ta'.$c.'\');">';
			$tarjetas_cajero.='</td>';
		$tarjetas_cajero.='</tr>';	
	}
/*	$afiliacion_1.='</select>';
	$afiliacion_2=str_replace("tarjeta_1", "tarjeta_2", $afiliacion_1);
	$afiliacion_3=str_replace("tarjeta_2", "tarjeta_3", $afiliacion_2);*/
//cehque o transferencia 
	$sql="SELECT bc.id_caja_cuenta,bc.nombre 
		FROM ec_caja_o_cuenta bc
		LEFT JOIN ec_caja_o_cuenta_sucursal bcs ON bc.id_caja_cuenta=bcs.id_caja_o_cuenta 
		WHERE bcs.estado_suc=1
		AND bcs.id_sucursal='$user_sucursal'
		AND bc.id_tipo_caja IN(2)";
	$eje=mysql_query($sql)or die("Error al listar los bancos o cajas!!!<br>".mysql_error());
	$cajas='<select id="caja_o_cuenta" class="filtro" style="width:95%"><option value="0">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$cajas.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$cajas.='</select>';
//listamos los cheques/transferencias del corte de caja
		$sql="SELECT  
					cc.id_banco,
					coc.nombre,
					cc.monto,
					cc.observaciones
				FROM ec_cajero_cobros cc
				LEFT JOIN ec_caja_o_cuenta coc ON cc.id_banco=coc.id_caja_cuenta
				WHERE cc.id_cajero='$user_id' 
				AND cc.fecha='$fecha_sesion'
				AND cc.hora>='$hora_inicio_sesion' 
				AND cc.observaciones!=''";
		$eje_chq=mysql_query($sql)or die("Error al consultar los pagos con cheques y transferencias!!!<br>".mysql_error());
		$pagos_chqs='';
		$cont_chqs=0;
		while($r1=mysql_fetch_row($eje_chq)){
			$cont_chqs++;
			$pagos_chqs.='<tr id="fila_ch_'.$cont_chqs.'">';
        	$pagos_chqs.='<td id="caja_'.$cont_chqs.'" class="td_oculto">'.$r1[0].'</td>';
        	$pagos_chqs.='<td align="left">'.$r1[1].'</td>';
        	$pagos_chqs.='<td id="monto_'.$cont_chqs.'" align="center">'.$r1[2].'</td>';
        	$pagos_chqs.='<td id="referencia_'.$cont_chqs.'" align="left">'.$r1[3].'</td>';
      		$pagos_chqs.='</tr>';
		}
?>

		<table border="1" id="tarjetas">
		<!--efectivo
			<tr>
				<td colspan="3" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Efectivo</p></td>
			</tr>
			<tr>
				<td align="center"><?php //echo $cajas;?></td>
				<td align="center"><input type="number" class="entrada" id="monto_cheque_transferencia" placeholder="Monto">
					<button onclick="agrega_cheque_transferencia();">Agregar</button></td>
			</tr>-->
		<!---->
			<?php echo $tarjetas_cajero;?>
			<tr>
				<td colspan="3" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Cheque o Transferencia</p></td>
			</tr>
			<tr>
				<td align="center"><?php echo $cajas;?></td>
				<td><input type="number" class="entrada" id="monto_cheque_transferencia" placeholder="Monto">
					<button onclick="agrega_cheque_transferencia();" class="btn_add" title="Click para agregar">+</button></td>
			</tr>
		<!----->
			<tr>
				<td class="ceques_transferencias" border="1" colspan="3">
					<table width="100%" id="listado_cheque_transferencia">
						<tr style="height: 30px;">
							<th>Banco</th>
							<th>Monto</th>
							<th>observaciones</th>
						</tr>

			<?php echo $pagos_chqs; ?>
					</table>
				</td>
			</tr>
		<!----->
		</table>
	<input type="hidden" id="no_tarjetas" value="<?php echo $c;?>">
	<input type="hidden" id="no_cheque_transferencia" value="<?php echo $cont_chqs;?>">
		<table id="opciones_arqueo">
			<tr>
			<!---Buscador-->
				<td width="25%">
					<input type="text" id="buscador" class="entrada" style="width: 80%;" onkeyup="busca(event);" <?php echo $info_folio;?>>
					<div id="res_busc"></div>
				</td>
		<?php
			if(isset($id_sesion_caja)){
				echo '<td>';
					echo '<input type="text" id="log_cajero" class="entrada" style="background:white;color:black;" value="'.$login_cajero.'" disabled>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="password" onkeyDown="cambiar(this,event,\'password1\');" placeholder="**Password***" class="entrada" style="width:80%;">';
					echo '<input type="hidden" id="password1" value="">';
				echo '</td>';
			}
		?>
				<td align="center">
					<p>
		<?php
			if($llave=='0'){
		?>
						<select class="filtro" id="f2"  onchange="activaBusqueda();">
							<option value="-1">Actual</option>
			<!--Cambio del 14-12-2017-->
							<option value="<?php echo $id_corte_anterior;?>">Uno Anterior</option>
						
						<?php
							if($user_id==2||$user_id==35||$user_id==1){
						?>
							<option value="2">Personalizado</option>
						<?php
							}
						?>
						</select>
		<?php
			}else{
		?>
						<select class="filtro" id="f2"  onchange="activaBusqueda();">
			<!--Cambio del 14-12-2017-->
							<option value="<?php echo $id_corte_anterior;?>">Uno Anterior</option>
							<option value="-1">Actual</option>
						<?php
							if($user_id==2||$user_id==35||$user_id==1){
						?>
							<option value="2">Personalizado</option>
						<?php
							}
						?>
						</select>
		<?php
			}
		?>
					</p>
				</td>
								<td align="center">
					<input type="button" value="Generar" onclick="llenaReporte(1);" class="boton">
				</td>
			</tr>	
		</table>