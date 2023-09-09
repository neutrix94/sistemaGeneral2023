<?php
	include('../../../../../conectMin.php');
	/*if($perfil_usuario!=7){
		die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
	}*/
	$sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
			LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
			WHERE p.id_menu=200
			AND u.id_usuario=$user_id";
	//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
	$es_cajero=mysql_fetch_row($eje);
	if($es_cajero[0]==0){
		die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
	}
//validamos que haya una sesion de caja iniciada con este cajero; de lo contrario avisamos que no hay sesión de caja y no dejamos acceder a esta pantalla
	$sql="SELECT count(id_sesion_caja) FROM ec_sesion_caja WHERE id_cajero=$user_id AND hora_fin='00:00:00' AND fecha=current_date()";
//	die($sql);
	$eje=mysql_query($sql)or die("Error al verificar si ya existe una sesion de caja para este cajero!!!\n".mysql_error());
	$r=mysql_fetch_row($eje);
	if($r[0]!=1){
		die('<script>alert("Es necesario abrir caja antes de cobrar!!!");location.href="../../../../code/especiales/tesoreria/abreCaja/abrirCaja.php?";</script>');
	}
//sacamos información de las afiliaciones
	$sql="SELECT a.id_afiliacion,a.no_afiliacion 
		FROM ec_afiliaciones a
		LEFT JOIN ec_afiliaciones_cajero ac ON ac.id_afiliacion=a.id_afiliacion
		WHERE ac.id_cajero='$user_id' AND ac.activo=1";
	$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
	//$afiliacion_1='<select id="tarjeta_1" class="filtro"><option value="0">--SELECCIONAR--</option>';
	$tarjetas_cajero='';
	$c=0;
	while($r=mysql_fetch_row($eje)){
		$c++;
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td colspan="2" class="subtitulo"><p style="font-size:20px;margin:0;" align="center">Tarjeta '.$c.':</p></td>';
		$tarjetas_cajero.='</tr>';
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td align="center">';
				$tarjetas_cajero.='<select id="tarjeta_'.$c.'" class="form-select"><option value="'.$r[0].'">'.$r[1].'</option>';
			$tarjetas_cajero.='</td>';
			$tarjetas_cajero.='<td>';
				$tarjetas_cajero .= "<div class=\"input-group\">
						<input type=\"number\" class=\"form-control\" id=\"t{$c}\" value=\"\" 
						onkeydown=\"prevenir(event);\" onkeyup=\"valida_tca(this,event,1,'.$c.');\">
						<button
							class=\"btn btn-primary\"
							onclick=\"sendTerminalPetition( {$c}, {$r[0]} );\"
						>
							<i class=\"icon-credit-card-alt\"></i>
						</button>
						</div>";
			$tarjetas_cajero.='</td>';
		$tarjetas_cajero.='</tr>';	
	}
	echo '<input type="hidden" id="cantidad_tarjetas" value="'.$c.'">';
//cehque o transferencia 
	$sql="SELECT bc.id_caja_cuenta,bc.nombre 
		FROM ec_caja_o_cuenta bc
		LEFT JOIN ec_caja_o_cuenta_sucursal bcs ON bc.id_caja_cuenta=bcs.id_caja_o_cuenta 
		WHERE bcs.estado_suc=1
		AND bcs.id_sucursal='$user_sucursal'";
	$eje=mysql_query($sql)or die("Error al listar los bancos o cajas!!!<br>".mysql_error());
	$cajas='<select id="caja_o_cuenta" class="form-select"><option value="0">--SELECCIONAR--</option>';
	while($r=mysql_fetch_row($eje)){
		$cajas.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$cajas.='</select>';
	$eje=mysql_query($sql)or die("Error al consultar afiliaciones del id_cajero!!!<br>".mysql_error());
	$sql="SELECT CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) as nombre,s.nombre
		FROM sys_users u 
		LEFT JOIN sys_sucursales s ON s.id_sucursal=u.id_sucursal
		WHERE u.id_usuario=$user_id";
	$eje_datos=mysql_query($sql)or die("Eror al consultar los datos de usuario y sucursal");
	$r=mysql_fetch_row($eje_datos);
	$usuario=$r[0];
	$sucursal=$r[1];
/*informacion de bancos
	$sql="SELECT cc.id_caja_cuenta,cc.nombre
		FROM ec_caja_o_cuenta cc
		LEFT JOIN ec_caja_o_cuenta_sucursal cs ON cs.id_caja_o_cuenta=cc.id_caja_cuenta
		WHERE cs.id_sucursal='$user_sucursal'
		AND cs.estado_suc=1";
	$eje_bancos=mysql_query($sql)or die("Error al consultar las cajas por susucrsal!!!<br>".mysql_error());
	$bancos='<select id="baco_o caja" class="entrada_num"><option value="0">--SELECCIONAR--</option>';
	if(mysql_num_rows($eje_bancos)<=0){
		$bancos.='<option value="0">No hay cajas o cuentas par esta sucursal</option>';
	}
	while($r=mysql_fetch_row($eje_bancos)){
		$bancos.='<option value="'.$r[0].'">'.$r[1].'</option>';
	}
	$bancos.='</option>';*/
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cobrar</title>
	<script type="text/javascript" src="../../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/apis.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body onload="document.getElementById('buscador').focus();">
<div class="global">
<!--emergentes -->
	<div class="emergent" style="z-index : 20;">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div>

	<div class="emergent_2" style="z-index : 30;">
		<div style="position: relative; top : 120px; left: 90%; z-index:2; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_2" tabindex="2"></div>
	</div>
<!--/emergentes-->

	<div id="emergente">
		<button class="btn_cierra" onclick="document.getElementById('emergente').style.display='none';">X</button>
		<div id="contenido_emergente">	
		</div>
	</div>

	<div class="row header">
		<!--div class="col-4">
			<b>Cobro de Tickets</b>	
		</div-->
		<div class="col-6 text-center">
			<b>Sucursal:</b> <?php echo $sucursal;?>
		</div>
		<div class="col-6 text-center">
			<b>Cajero:</b> <?php echo $usuario;?>	
		</div>
	</div>
	<div class="contenido" align="center">
	<!---->
		<table id="listado_cheque_transferencia">
			<tr style="height: 30px;">
				<th class="subtabla">Banco</th>
				<th class="subtabla">Monto</th>
				<th class="subtabla">observaciones</th>
			</tr>
			<tr></tr>
		</table>
		<input type="hidden" id="no_cheque_transferencia" value="0">
<br>	
		<div class="row" style="padding : 20px;">
			<div class="col-6">
					<p class="informativo"></p>
				<div class="input-group">
						<input type="text" id="buscador" class="form-control" placeholder="Folio..." onkeyup="busca(event);">
						<button title="Buscar de nuevo" onclick="link(2);">
							<i class="icon-search"></i>
						</button>
					<!--<img src="../../../../img/especiales/buscar.png" width="50px"></p>-->
					<div id="res_busc"></div>
				</div>
			</div>
			<div class="col-3">
					<p class="informativo" align="center">Monto:<br>
						<input type="text" id="monto_total" class="form-control" style="background:white;" disabled></p>
			</div>
			<div class="col-3">
					<p class="informativo" align="center">Saldo a favor:<br>
					<input type="text" id="saldo_favor" class="form-control" style="background:white;" disabled></p>
			</div>

			<input type="hidden" id="id_venta" value="0">
			<input type="hidden" id="venta_pagada" value="0">
		</div>
	<!---->
		<table ><!--width="50%" class="pagos"-->
			<tr>
				<th width="70%">
					Tipo de Pago
				</th>
				<th width="30%">
					Monto
				</th>
			</tr>
		<?php
			echo $tarjetas_cajero;
		?>
	
			<tr>
				<td align="center"><b>Efectivo:</b></td>
				<td align="left"><input type="number" id="efectivo" class="form-control" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,2);calcula_cambio();"></td>
			</tr>
			<tr>
				<td align="center"><b>Recibido:</b></td>
				<td align="left"><input type="number" id="efectivo_recibido" class="form-control" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,3);calcula_cambio();"></td>
			</tr>
			<tr>
				<td align="center"><b>Cambio:</b></td>
				<td align="left"><input type="number" id="efectivo_devolver" class="form-control" style="background: white;" disabled></td>
			</tr>
			<tr>
				<td align="center"><b>Cheque o transferencia</b><br><?php echo $cajas;?></td>
				<td align="left">
				<div class="input-group">
					<input type="number" id="monto_cheque_transferencia" class="form-control">
					<button 
						class="btn btn-success"
						onclick="agrega_cheque_transferencia();">
						<i class="icon-plus"></i>
					</button>
				</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<button id="cobrar" class="btn btn-success"  onclick="cobrar();">
						<i class="icon-floppy">Cobrar e Imprimir</i>
					</button>
				</td>
			</tr>
		</table>
	</div>
	<div class="footer">
		<a href="javascript:link(1);" class="mnu">
			Regresar al panel
		</a>
		
	</div>
</div>
</body>
</html>
<script type="text/javascript">
</script>