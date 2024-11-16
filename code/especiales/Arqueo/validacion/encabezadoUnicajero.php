<?php
/*version 2.0 2024-06-21*/
	$id_sesion_caja = $_GET['id_corte'];
//selecciona el id de usuario del corte
	$sql = "SELECT id_cajero AS user_id FROM ec_sesion_caja WHERE id_sesion_caja = '{$id_sesion_caja}'";
	$stm = $link->query($sql) or die( "Error al consultar el id de cajero : {$sql} : {$link->error}" );
	$user_row = $stm->fetch_assoc();
	$user_id = $user_row['user_id'];
//corte anterior
	//$id_corte_anterior;//$Arqueo->getSessionBefore( $user_id );
//afiliaciones
	$tarjetas_cajero = $Arqueo->getAfiliaciones( $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja );
//terminales SmartAccounts
	$terminales_cajero_smartAccounts = $Arqueo->getSmartAccountsTerminals( $user_sucursal, $user_id, $fecha_sesion, $hora_inicio_sesion, $hora_cierre_sesion, $id_sesion_caja );
//afiliaciones para cheque o transferencia 
	$cajas = $Arqueo->getAccounts( $user_sucursal );
//cheques/transferencias del corte de caja
	$pagos_chqs = $Arqueo->getAdittionalPayments( $user_id, $fecha_sesion, $hora_inicio_sesion, $id_sesion_caja );
	
//cargamos el monto en efectivo
	$pagos_efe='';
	$sql="SELECT scd.id_sesion_caja_detalle,'Efectivo entregado',scd.monto 
		FROM ec_sesion_caja_detalle scd
		LEFT JOIN ec_afiliaciones a ON scd.id_afiliacion=a.id_afiliacion
		WHERE scd.id_corte_caja='$llave' AND scd.observaciones='efectivo'";//die( $sql );
	$eje=mysql_query($sql)or die("Error al consultar el monto del pago en efectivo!!!<br>".mysql_error());
	while ($r=mysql_fetch_row($eje)){	
		$pagos_efe.='<tr>';
			$pagos_efe.='<td colspan="3" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Efectivo</p></td>';
		$pagos_efe.='</tr>';
		$pagos_efe.='<tr>';
			$pagos_efe.='<td align="center"><select id="efectivo_pagos" class="filtro" style="width:95%"><option value="'.$r[0].'">'.$r[1].'</option></select></td>';
			$pagos_efe.='<td align="left"><input type="number" class="entrada" id="monto_en_efectivo" onkeyup="llenaReporte();" value="'.$r[2].'" placeholder="Monto">';
		$pagos_efe.='</tr>';
	} 
?>

		<table id="tarjetas">
			<?php
				echo $pagos_efe;
			?>
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
				<td class="ceques_transferencias" colspan="3">
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
	//			echo '<td>';
					//echo '<input type="text" id="password" onkeyDown="cambiar(this,event,\'password1\');" placeholder="**Password***" class="form-control" style="width:80%;">';
	//				echo '<input type="password" id="password1" class="form-control">';
	//			echo '</td>';
			}
		?>
				
				<td align="center">
					<button type="button" class="btn btn-success" onclick="llenaReporte();">
						<i>Generar</i>
					</button>
				</td>
				<td align="center">
					<button type="button" class="btn btn-warning" onclick="if( confirm( 'Relamente deseas salir de la validación actual' ) ){location.href='./validacion_arqueo.php?';}">
						<i>Buscar nuevo</i>
					</button>
				</td>
			</tr>	
		</table>