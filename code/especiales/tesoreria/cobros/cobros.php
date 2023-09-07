<?php
	include('../../../../conectMin.php');
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
		//$tarjetas_cajero.='<tr>';
			//$tarjetas_cajero.='<td colspan="2" class="subtitulo"><p style="font-size:20px;margin:0;" align="left">Tarjeta '.$c.':</p></td>';
		//$tarjetas_cajero.='</tr>';
		$tarjetas_cajero.='<tr>';
			$tarjetas_cajero.='<td align="center">';
				$tarjetas_cajero.='<select id="tarjeta_'.$c.'" class="form-select"><option value="'.$r[0].'">'.$r[1].'</option>';
			$tarjetas_cajero.='</td>';
			$tarjetas_cajero.='<td><div class="input-group">';
				$tarjetas_cajero.='<input type="number" class="form-control text-end" id="t'.$c.'" value="0" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,1,'.$c.');">';
				$tarjetas_cajero .= "<button
					class=\"btn btn-warning\"
					onclick=\"api_petition( {$c} );\"
				>
					<i class=\"icon-credit-card-alt\"></i>
				</button>";
			$tarjetas_cajero.='</div></td>';
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
	<title>Cobrar</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,height=device-height, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<!-- JS -->
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/builder.js"></script>
<!-- estilos -->
<style type="text/css">
	.global{position: absolute;width: 100%;height: 100%;top:0;left: 0;background-image: url('../../../../img/img_casadelasluces/bg8.jpg');}
	.header{position: absolute;height: 60px;background:#83B141;width:100%;}
	.footer{position: fixed;bottom: 0; height: 60px;background:#83B141;width:100%; padding-top : 10px;}
	.contenido{position: absolute;top:50px;width: 100%;}
	/*th{color: white; font-size: 30px;align-items: center;}*/
	/*.pagos{background:#83B141;color: white;font-size: 20px;}*/
	.entrada_num{padding: 10px;width:80%;font-size: 25px;}
	/*#buscador{padding: 10px;width: 80%;font-size: 25px;}*/
	#res_busc{position: absolute;width: 40%;border: 1px solid;top:65px;background: white;height: 250px;overflow-y: auto;display:none;}
	/*.btn{padding: 10px;border-radius: 10px;}*/
	/*td{border:1px solid;}*/
	.mnu{text-decoration: none; position: absolute; padding: 10px;border:1px solid white;background: gray;top:15px;color: white;left: 45%;}
	.informativo{font-size: 20px;}
	.entrada{padding:10px;height:30px;width:100px;border-radius: 5px;font-size: 20px;}
	.filtro{padding:10px;border-radius: 5px;}
	#listado_cheque_transferencia{position: absolute;left: 3px;top: 50%;width: 24%;height: 250px;border: 1px solid #83B141;background: white;display: none;}
	.subtabla{font-size: 20px;background:#83B141; }
	#emergente{position: absolute; z-index:10;background: rgba(0,0,0,.7);width: 100%;height: 100%;top:0;left: 0;display: none;}
	#contenido_emergente{align-items: center;position: absolute;width: 60%;left:20%;height: 50%;top:20%;border-radius: 20px;border:1px solid white;background: rgba(0,0,0,.4);}
	.btn_cierra{border-radius: 50%;padding: 12px;color: white;background: red;position: absolute;top:18.5%;left:79%;z-index:100;}
	.td_oculto{display:none;}
	.opc_buscador{padding: 10px;}
	#referencia_cheque_transferencia{width: 50%; height: 150px;}
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
  	margin: 0; 
	}
	input[type=number] { -moz-appearance:textfield; }
	/*estilos de la ventana emergente*/
	.emergent_content{
		max-height: 100%;
		overflow: auto;
		background-color: white;
	}
	.emergent{
		position: fixed;
		top : 0;
		left: 0;
		width: 100% !important;
		height: 100%;
		background: rgba( 0,0,0,.7);
		z-index: 99999999;
		vertical-align: middle !important;
		/*display: none;*/
	}
/*	.deshabilita{width: 100%;height: 100%;background: red;position: absolute;top:0;}*/
</style>
</head>
<body onload="document.getElementById('buscador').focus();">
<!--  -->
	<div class="emergent">
			<br>
		<div class="row">
			<div class="col-12 emergent_content" tabindex="1">
			<?php
				include( './views/tickets_seeker.php' );
			//die( 'views/tickets_seeker.php' );
			?>
			</div>
		</div>
	</div>
<!-- -->
<div class="global">
<!--emergente-->
	<div id="emergente">
		<button class="btn_cierra" onclick="document.getElementById('emergente').style.display='none';">X</button>
		<div id="contenido_emergente"></div>
	</div>
<?php
			
		?>
	<div class="header">
		<table  width="100%" style="color:white;font-size: 25px;">
			<tr>
				<td>
					<b cass="icon-print-2">Cobro de Tickets</b>
				</td>
				<td align="right">
					<b>Sucursal:</b> <?php echo $sucursal;?><br>
					<b>Cajero:</b> <?php echo $usuario;?>					
				</td>
			</tr>
		</table>
	</div>
	<div class="contenido" align="center">
	<!--lista de tickets -->
		<div class="row" style="margin-top : 20px;">
			<table class="table table-striped table-bordered">
				<thead class="bg-danger">
					<tr>
						<th class="text-center text-light">Folio</th>
						<th class="text-center text-light">Monto</th>
						<th class="text-center text-light">Quitar</th>
					</tr>
				</thead>
				<tbody id="tickets_list"></tbody>
			</table>
			<div class="row">
				<div class="col-3"></div>
				<div class="col-6">
					<button
						class="btn btn-success form-control"
						onclick="setTickets();"
					>
						<i class="icon-ok-circle">Aceptar</i>
					</button>
				</div>
			</div>
		</div>

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

		<!--table width="50%" border="0">
			<tr>
				<td align="left" width="50%">
					<p class="informativo" align="left">
					<div class="input-group">
						<input type="text" class="form-control" id="buscador" placeholder="Escanea el ticket" onkeyup="busca(event);">
						<button 
							type="button"
							title="Buscar de nuevo" 
							onclick="busca( 'intro' );"
							class="btn btn-warning"
						>
							<i class="icon-barcode"></i>
						</button>
						<button 
							type="button"
							title="Buscar de nuevo" 
							onclick="link(2);"
							class="btn btn-danger"
						>
							<i class="icon-ccw-1"></i>
						</button>
					</div>
					<div id="res_busc"></div>
				</td>
				<td width="25%">
					<p class="informativo" align="center"><br>
						<input type="text" id="monto_total" class="form-control" style="background:white;" disabled>
					</p>
				</td>
				<td width="25%">
					<p class="informativo" align="center"><br>
						<input type="text" id="saldo_favor" class="form-control text-end" style="background:white;" disabled>
					</p>
				</td>
			</tr>	
		</table-->
		<input type="hidden" id="id_venta" value="0">
		<input type="hidden" id="venta_pagada" value="0">
	<!-->
		<table width="50%" class="">
			<tr>
				<th width="70%" class="text-primary text-center">
					Tipo de Pago
				</th>
				<th width="30%" class="text-primary text-center">
					Monto
				</th>
			</tr>
		<?php
			//echo $tarjetas_cajero;
		?>
	
			<tr>
				<td align="center"><b>Efectivo:</b></td>
				<td align="left">
					<input type="number" id="efectivo" class="form-control text-end"  onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,2);calcula_cambio();">
				</td>
			</tr>
			<tr>
				<td align="center"><b>Recibido:</b></td>
				<td align="left">
					<input type="number" id="efectivo_recibido" class="form-control text-end" onkeydown="prevenir(event);" onkeyup="valida_tca(this,event,3);calcula_cambio();">
				</td>
			</tr>
			<tr>
				<td align="center"><b>Cambio:</b></td>
				<td align="left">
					<input type="number" id="efectivo_devolver" class="form-control text-end" style="background: white;" disabled>
				</td>
			</tr>
			<tr>
				<td align="center">
					<b>Cheque o transferencia</b><br><?php //echo $cajas;?>
				</td>
				<td align="left">
					<br>
					<div class="input-group">
						<input type="number" id="monto_cheque_transferencia" class="form-control text-end"
						placeholder="Monto">
						<button 
							class="btn btn-warning"
							onclick="agrega_cheque_transferencia();">
							<i class="icon-plus"></i>
						</button>
					</div>	
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<br>
					<button 
						id="cobrar" 
						class="btn btn-primary form-control" 
						onclick="cobrar();"
					>
						<i class="icon-floppy-1">Cobrar e Imprimir</i>
					</button>
					<br>
				</td>
			</tr>
		</table-->
	</div>
	<div class="footer text-center">
		<button 
			type="button"
			onclick="link(1);" 
			class="btn btn-warning">
			<i class="icon-home-1">Regresar al panel</i>
		</button>
	</div>
</div>
</body>
</html>