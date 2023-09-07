<?php
//consultamos las afiliaciones por cajero
	$sql="SELECT ac.id_afiliacion,a.no_afiliacion 
			FROM ec_afiliaciones_cajero ac
			LEFT JOIN ec_afiliaciones a ON ac.id_afiliacion=a.id_afiliacion
			WHERE ac.id_cajero='$user_id' AND activo=1";	
	$eje_afiliaciones=mysql_query($sql)or die("Error al consultar las afiliaciones para el cajero!!!<br>".mysql_error());
	$tarjetas='';
//sumamos los pago registrados por tajeta
	$sql="SELECT ac.id_afiliacion,a.no_afiliacion,SUM(IF(cc.id_cajero_cobro IS NULL,0,cc.monto))
			FROM ec_afiliaciones_cajero ac
			LEFT JOIN ec_afiliaciones a ON ac.id_afiliacion=a.id_afiliacion
			LEFT JOIN ec_cajero_cobros cc ON cc.id_cajero ON cc.id_cajero=
			WHERE ac.id_cajero='$user_id' AND activo=1
			GROUP BY ac.id_afiliacion";
//sumamos los pagos registrados en efectivo
		
	while($r=mysql_fetch_row($eje_afiliaciones)){
		$tarjetas.='<tr>';
			$tarjetas.='<td>';
				$tarjetas.='';
			$tarjetas.='</td>';
		$tarjetas.='</tr>';
	}
?>

		<table width="100%;" border="1">
			<tr>
				<th colspan="2" align="center" class="titulo">Ingrese los pagos con Tarjeta</th>
				<th>Fecha</th>
				<th>Hora</th>
			</tr>
			<tr>
				<td align="center"><!--<p>-->
					<p style="font-size:20px;margin:0;">Tarjeta 1:</p> <input type="text" class="entrada" id="t1" value="0"></p>
				</td>
				<td align="center">
					<p style="font-size:20px;margin:0;">Tarjeta 2:</p><input type="text" class="entrada" id="t2" value="0"></p>
				</td>
				<td align="center">
					<p style="font-size:20px;margin:0;">Tarjeta 3:</p><input type="text" class="entrada" id="t3" value="0"></p>
				</td>
				<td align="center" style="display:none;">
					<!--<p>Seleccione un tipo de Arqueo</p>-->
					<p>
						<select class="filtro" id="f1">
							<option value="1">Simplificado</option>
							<option value="2">Completo</option>
						</select>
					</p>
				</td>
				<td align="center">
					<p>
						<select class="filtro" id="f2"  onchange="activaBusqueda();">
							<option value="-1">Hoy</option>
			<!--Cambio del 14-12-2017-->
							<option value="1">Ayer</option>

						
						<?php
							if($user_id==2||$user_id==35){
						?>
							<option value="2">Personalizado</option>
						<?php
							}
						?>
						</select>
					</p>
				</td>
				<td>
						<p>
							<p>DE LA:<select id="h1" class="hora">
									<?php
										for($i=0;$i<=23;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									<select id="m1" class="hora">
									<?php
										for($i=0;$i<=59;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>A LA:<select id="h2" class="hora">
									<?php
										for($i=0;$i<=23;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									<select id="m2" class="hora">
									<?php
										for($i=0;$i<=59;$i++){
											$hora="0".$i;
											if($i>9){
												$hora=$i;
											}
									?>
										<option value="<?php echo $hora;?>"><?php echo $hora;?></option>
									<?php
										}
									?>
									</select>
									</p>
						</p>
					<!--Calendario-->
						<p id="calend" style="display:none;">
							Del: <input type="text" class="fecha" id="fecdel" onclick="calendario(this);">
							Al:
							<input type="text" class="fecha" id="fecal" onclick="calendario(this);">
						</p>
					</p>
				</td>
				<td align="center">
					<input type="button" value="Gererar" onclick="llenaReporte();" class="boton">
				</td>
			</tr>	
		</table>