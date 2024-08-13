<?php
/*version 2.0 2024-06-21*/
//corte anterior
	$id_corte_anterior=$Arqueo->getSessionBefore( $user_id );
//afiliaciones
	$tarjetas_cajero = $Arqueo->getAfiliaciones( $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja );
//terminales SmartAccounts
	$terminales_cajero_smartAccounts = $Arqueo->getSmartAccountsTerminals( $user_sucursal, $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja );
//afiliaciones para cheque o transferencia 
	$cajas = $Arqueo->getAccounts( $user_sucursal );
//cheques/transferencias del corte de caja
	$pagos_chqs = $Arqueo->getAdittionalPayments( $user_id, $fecha_sesion, $hora_inicio_sesion, $id_sesion_caja );
?>

		<table border="1" id="tarjetas">
			<tr class="informative_row">
				<th colspan="3" class="text-center">Terminales Sin SmartAccounts</th>
			</tr>
			<?php echo $tarjetas_cajero;?>
			<tr class="informative_row">
				<th colspan="3" class="text-center bg-primary">Terminales NetPay</th>
			</tr>
			<?php echo $terminales_cajero_smartAccounts;?>
			<tr>
				<td colspan="3" class="bg-warning">
					<p style="font-size:20px;margin:0;" align="center">Cheque o Transferencia</p>
				</td>
			</tr>
			<tr>
				<td align="center"><?php echo $cajas;?></td>
				<td>
					<div class="input-group">
						<input type="number" class="form-control" id="monto_cheque_transferencia" placeholder="Monto">
						<button onclick="agrega_cheque_transferencia();" 
						class="btn btn-success" title="Click para agregar">
							<i class="icon-plus"></i>
						</button>
				</td>
				</div>
			</tr>
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
		</table>
		<input type="hidden" id="no_tarjetas" value="<?php echo $c;?>">
		<input type="hidden" id="no_cheque_transferencia" value="<?php echo $cont_chqs;?>">
		<table id="opciones_arqueo" style="padding : 20px !important;">
			<tr>
			<!---Buscador-->
				<td width="25%">
					<input 
						type="text" 
						id="buscador" 
						class="form-control"
						onkeyup="busca(event);" 
						<?php echo $info_folio;?>>
					<div id="res_busc"></div>
				</td>
		<?php
			if(isset($id_sesion_caja)){
				echo '<td>';
					echo '<input type="text" id="log_cajero" class="form-control" style="background:white;color:black;" value="'.$login_cajero.'" disabled>';
				echo '</td>';
				echo '<td>';
					//echo '<input type="text" id="password" onkeyDown="cambiar(this,event,\'password1\');" placeholder="**Password***" class="form-control" style="width:80%;">';
					echo '<input type="password" id="password1" class="form-control">';
				echo '</td>';
			}
		?>
				<td align="center">
		<?php
			if($llave=='0'){
		?>
						<select class="form-select" id="f2"  onchange="activaBusqueda();">
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
				</td>
				<td align="center">
					<button type="button" class="btn btn-success" onclick="llenaReporte(1);">
						<i>Generar</i>
					</button>
				</td>
				</td>
			</tr>	
		</table>