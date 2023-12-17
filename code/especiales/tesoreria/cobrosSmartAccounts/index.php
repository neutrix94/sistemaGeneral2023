<?php
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');
	//verifica si esta habilitada la funcion de SmartAccounts
		$sql = "SELECT 
					habilitar_smartaccounts_netpay AS is_smart_accounts
				FROM sys_sucursales s
				WHERE id_sucursal = {$sucursal_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar si esta habilitado SmartAccounts : {$link->error}" );
		$row = $stm->fetch_assoc();
		$is_smart_accounts = $row['is_smart_accounts'];
		if( $row['is_smart_accounts'] == 0 ){
			die( "<script>location.href=\"../cobros/index.php\";</script>" );
		}
	include('ajax/db.php');
	$Payments = new Payments( $link );//instancia clase de pagos
	$Payments->checkAccess( $user_id );//verifica permisos
	$tarjetas_cajero = $Payments->getTerminals( $user_id );//afiliaciones por cajero
	$cajas = $Payments->getBoxesMoney( $sucursal_id );//cheque o transferencia 
	/*if($perfil_usuario!=7){
		die('<script>alert("Este tipo de usuario no puede acceder a esta pantalla!!!\nContacte al administrador desl sistema!!!");location.href="../../../../index.php?";</script>');
	}*/
	$sql="SELECT 
			CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) as user_name,
			s.nombre AS store_name,
			(SELECT 
				id_sesion_caja
			FROM ec_sesion_caja 
			WHERE id_cajero = u.id_usuario
			ORDER BY id_sesion_caja DESC
			LIMIT 1
			) AS session_id
		FROM sys_users u 
		LEFT JOIN sys_sucursales s 
		ON s.id_sucursal=u.id_sucursal
		WHERE u.id_usuario=$user_id";
	$eje_datos=mysql_query($sql)or die("Error al consultar los datos de usuario y sucursal");
	$r=mysql_fetch_row($eje_datos);
	$usuario = $r[0];
	$sucursal = $r[1];
	$session_id = $r[2];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cobrar | SmartAccounts</title>
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/apis.js"></script>
	<script type="text/javascript" src="js/builder.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body onload="document.getElementById('buscador').focus();">
<div class="global">
	<input type="hidden" id="session_id" value="<?php echo $session_id;?>">
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

	<div class="row header bg-primary" style="padding-top : 2px;">
		<div class="col-1 text-center text-light" style="padding-top : 1%;">
			<button type="button" class="btn btn-success" onclick="show_reprint_view();" >
				<i class="icon-print"></i>
			</button>
		</div>
		<div class="col-10 text-center text-light">
			<h3><b class="">Sucursal:</b> <?php echo $sucursal;?></h3>
			<h3><b class="">Cajero:</b> <?php echo $usuario;?></h3>
		</div>
	</div>
<!-- Cancelaciones /reimpresiones manuales -->
	<div class="reverse_form_btn">
		<button
			type="button"
			class="btn btn-success"
			onclick="get_reverse_form();"
		>
			<i class="icon-history"></i>
		</button>
	</div>
<!-- -->
	<div class="contenido" align="center">
		<div class="row" style="padding : 20px;">
			<div class="col-12">
				<p class="informativo"></p>
				<div class="input-group">
					<input type="text" id="buscador" class="form-control" placeholder="Folio..." onkeyup="busca(event);">
					<button 
						title="Buscar" 
						onclick="busca('intro');"
						class="btn btn-primary"
						id="seeker_btn"
					>
						<i class="icon-search"></i>
					</button>
					<button 
						title="Buscar de nuevo" 
						onclick="link(2);"
						class="btn btn-danger no_visible"
						id="seeker_reset_btn"
					>
						<i class="icon-erase"></i>
					</button>
					<!--<img src="../../../../img/especiales/buscar.png" width="50px"></p>-->
					<div id="res_busc"></div>
				</div>
			</div>
			<div class="col-4">
					<p class="informativo" align="center">Total:<br>
						<input type="text" id="monto" class="form-control text-end" style="background:white;" disabled></p>
			</div>
			<div class="col-4">
					<p class="informativo" align="center">Pagado:<br>
					<input type="text" id="saldo_favor" class="form-control text-end" style="background:white;" disabled></p>
			</div>

			<div class="col-4">
					<p class="informativo" align="center" ><b id="payment_description">Cobrar:</b>
					<input type="text" id="monto_total" class="form-control text-end" style="background:white;" disabled></p>
			</div>

			<input type="hidden" id="id_venta" value="0">
			<input type="hidden" id="venta_pagada" value="0">
			<input type="hidden" id="id_devolucion" value="0">
		</div>
	<!-- historico dfe pagos -->
		<div class="row" id="historic_payments"></div>
	<!-- -->
		<div class="row" id="card_qr_container">
			<div class="input-group">
				<input type="text" id="terminal_qr_input" class="form-control" 
					placeholder="Escanear /codigo QR de la terminal"
					onkeyup="seekTerminalByQr( event );"
				>
				<button
					type="button"
					class="btn btn-success"
					onclick="seekTerminalByQr( 'intro' );"
				>
					<i class="icon-qrcode"></i>
				</button>
			</div>		
		</div>
		<div class="row" id="cards_container">
			<h3>Tarjetas 
				<button
					type="button"
					id="add_card_btn"
					class="btn btn-primary"
					onclick="addPaymetCard( <?php echo $user_id;?> );"
					style="font-size : 100% !important; padding : 2px !important;"
				>
					<i class="icon-plus-circle"></i>
				</button>
			</h3>
			<table class="table table-striped">
				<tbody id="payments_list">
					<?php
						echo $tarjetas_cajero;
					?>
				</tbody>
			</table>
			<div class="col-2"></div>
			<div class="col-8" style="padding : 5px;">
				<button
					type="button"
					class="btn btn-warning form-control"
					onclick="enable_payments();"
					id="start_payments_btn"
				>
					<i class="icon-play">Comenzar a cobrar</i>
				</button>
			</div>
		</div>

		<div class="row">
			<h3>Efectivo</h3>
			<div class="col-12 input-group">
				<!--div align="center"><b>Efectivo:</b></div-->
				<!--button type="button">Efectivo </button-->
				<!--onkeydown="prevenir(event);" 
					onkeyup="valida_tca(this,event,2);calcula_cambio();"-->
				<input 
					type="number" 
					id="efectivo" 
					class="form-control text-end" >
				<button 
					type="button"
					class="btn btn-primary"
					onclick="getCashPaymentForm();"
					id="add_form_btn"
				>
					<i class="icon-plus"></i>
				</button>
			</div>
			<!--div class="col-6  input-group">
				<button type="button">Recibido</button>
				<input 
					type="number" 
					id="efectivo_recibido" 
					class="form-control" 
					onkeydown="prevenir(event);" 
					onkeyup="valida_tca(this,event,3);calcula_cambio();"
				>
			</div-->
			<div class="col-6 input-group">
				<!--button type="button">Cambio </button-->
			</div>
		</div>
		
		<div class="row" align="center" id="transferencias_cheques_contenedor">
			<h3>Cheque o transferencia</h3>
			<div class="col-7">
				<?php echo $cajas;?>
			</div>
			<div class="col-5">
				<div class=" input-group">
					<input type="number" id="monto_cheque_transferencia" class="form-control">
					<button 
						class="btn btn-primary"
						onclick="agrega_cheque_transferencia();">
						<i class="icon-plus"></i>
					</button>
				</div>
			</div>
		</div>

		<br>
	<!---->
		<!--div class="col-12" id="card_payments">
			<table class="table table-bordered table-striped">
				<thead class="bg-danger text-light">
					<tr>
						<th class="text-center">Tipo Pago</th>
						<th class="text-center">Caja</th>
						<th class="text-center">Monto</th>
						<th class="text-center">Reimpresion</th>
						<th class="text-center">Cancelacion</th>
					</tr>
				</thead>
				<tbody id="payments_list"></tbody>
				<tfoot>
					<tr>
						<td></td>
						<td>Total</td>
						<td>0</td>
					</tr>
				</tfoot>
			</table>
		</div-->

	<!---->
	<div class="row" id="finalizar_cobro_contenedor">
		<table id="listado_cheque_transferencia" class="table table-striped" style="">
			<thead>
				<tr>
					<th class="text-center">Banco</th>
					<th class="text-center">Monto</th>
					<th class="text-center">Observaciones</th>
				</tr>
			</thead>
		</table>
		<input type="hidden" id="no_cheque_transferencia" value="0">
			<div class="col-2"></div>
			<div class="col-8">
				<button 
					type="button"
					id="cobrar" 
					class="btn btn-primary form-control"  onclick="cobrar(1);">
					<i class="icon-floppy">Finalizar cobro</i>
				</button>
			</div>
			<div class="col-2"></div>
		</div>
		<div class="row" id="finalizar_cobro_devolucion_contenedor">
			<div class="col-2"></div>
			<div class="col-8">
				<button 
					type="button"
					id="devolver" 
					class="btn btn-danger form-control"  onclick="cobrar(-1);">
					<i class="icon-floppy">Devolver Efectivo</i>
				</button>
			</div>
			<div class="col-2"></div>
		</div>
	</div>
	<div class="footer text-center bg-primary">
		<button
			class="btn btn-light"
			onclick="link(1);"
		>
			<i class="icon-home">Regresar al panel</i>
		</button>
		<!--a href="javascript:link(1);" class="mnu"></a-->
		
	</div>
</div>
</body>
</html>