<?php
/*version 1.1 2024-06-21*/
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');
	//consultamos si esta habiliado el logger
	$Logger = null;
	$sql = "SELECT log_habilitado AS log_enabled FROM sys_configuraciones_logs WHERE id_configuracion_log = '2'";
	$stm = $link->query( $sql ) or die( "Error al consultar si el log de cobros esta habilitado : {$sql} : {$link->error}" );
	$log_enabled = $stm->fetch_assoc();
	echo "<input type=\"hidden\" id=\"log_status\" value=\"{$log_enabled['log_enabled']}\">";

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
//consulta si esta habilitado el log del JSON
	$sql = "SELECT
				p.ver AS permission
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles up
			ON up.id_perfil = p.id_perfil
			LEFT JOIN sys_users u
			ON u.tipo_perfil = up.id_perfil
			WHERE p.id_menu = 310
			AND u.id_usuario = {$user_id}";
	$stm = $link->query( $sql ) or die( "Error al consultar permiso de LOG JSON : {$sql} : {$link->error}" );
	$json_log = $stm->fetch_assoc();

	$Payments = new Payments( $link, $user_sucursal );//instancia clase de pagos
	$token = $Payments->checkAccess( $user_id );//verifica permisos
	//var_dump( $token );die('');
	echo "<input type=\"hidden\" id=\"max_execution_time\" value=\"{$token['max_execution_time']}\">";
	$tarjetas_cajero = $Payments->getTerminals( $user_id, 0, $user_sucursal, $session_id );//afiliaciones por cajero
	$cajas = $Payments->getBoxesMoney( $sucursal_id );//cheque o transferencia 
//configuracion del Websocket
// $url_websocket = "ws://localhost:3005/";//"ws://localhost:3000";
// $url_websocket = "ws://192.168.1.223:3005/";//"ws://localhost:3000";
	$url_websocket = $Payments->getWebSocketURL();// getenv('WEBSOCKET_URL') ?: "ws://192.168.1.223:3005/";
	if( $url_websocket == '' || $url_websocket == NULL || $url_websocket == null ){
		die( "<center>
			<h2>La url del websocket no esta configurada, configurala desde configuracion del sistema!</h2>
			<br>
			<a href=\"../../../../index.php?\" class=\"btn btn-success\">Aceptar y Salir</a></center>" );
	}
//aqui encriptar en token 
	if( !include( '../../../../rest/netPay/utils/encriptacion_token.php' ) ){
		die( "no se incluyo libreria Encrypt" );
	}
	$Encrypt = new Encrypt();
	$token_websocket = $Encrypt->encryptText( "{$token['token']}", "" );//hay que recuperar de DB7dff3c34-faee-11ea-a7be-3d014d7f956c // d4186cb3-7400-4e0f-bbea-55ebc8739b23
//$token_websocket = "";
	//die( "Token : {$token_websocket}" );
	$usuario_websocket = $user_id;
	$sucursal_websocket = $sucursal_id;
	echo "<script type=\"text/JavaScript\">
		var \$url_websocket = '{$url_websocket}';
		var \$token_websocket = '{$token_websocket}';
		var \$usuario_websocket = '{$usuario_websocket}';
		var \$sucursal_websocket = '{$sucursal_websocket}';
		var ws;
	</script>";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cobrar | SmartAccounts</title>
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/builder.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">    
	<script src="../../../../js/highlight/highlight.min.js"></script>
<!--Websockets (Eugenio) -->
	<script src="./websocket_client/websocket_client.js" type="module"></script>
	<script type="text/javascript" src="js/apis.js"></script>
<!-- -->
	<link rel="stylesheet" href="../../../../js/highlight/styles/default.min.css">
    <script>hljs.highlightAll();</script>

</head>
<body onload="document.getElementById('buscador').focus();">
<div class="global">
	<input type="hidden" id="session_id" value="<?php echo $session_id;?>">
<!--emergentes -->
	<div class="emergent" style="z-index : 20;" tabindex="1">
		<!--div class="text-end" style=" position: relative; top : 120px;right: 1%;z-index:1;">position: relative; top : 120px; left: 90%; z-index:1; display:none; 
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>-->
		<div class="emergent_content" tabindex="1"></div>
	</div>

	<div class="emergent_2" style="z-index : 30;">
		<div style="position: relative; top : 120px; left: 90%; z-index:2; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent_2();"
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

			<!--button type="button" class="btn btn-secondary" onclick="show_pending_payment_responses();" >
				<i class="icon-money-1"></i>
			</button-->
		</div>
		<div class="col-10 text-center text-light">
			<h3><b class="">Sucursal:</b> <?php echo $sucursal;?></h3>
			<h3><b class="">Cajero:</b> <?php echo $usuario;?></h3>
		</div>
	</div>
<!-- Cancelaciones /reimpresiones manuales -->
	<div class="reverse_form_btn">
	<?php
		if( $json_log['permission'] == 1 ){
	?>
			<button
				type="button"
				class="btn btn-info"
				onclick="show_debug_json();"
			>
				<i class="icon-file-code"></i>
			</button>
	<?php
		}
	?>
		<button
			type="button"
			class="btn btn-success"
			onclick="send_sale_by_api();"
		>
			<i class="icon-ok-circled"></i>
		</button>

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
			<input type="hidden" id="id_venta_origen" value="0">
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
				<button
					type="button"
					class="btn btn-warning"
					onclick="getAfiliacionesForm();"
				>
					<i class="icon-tools"></i>
				</button>
			</div>		
		</div>
		<div class="row" id="cards_container">
			<h3>
				<!--button
					type="button"
					class="btn btn-warning"
-->
					<i class="icon-tools text-secondary"
						onclick="getTerminalesForm();"
					></i>
				<!--/button-->
				Tarjetas 
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
					<input type="text" id="monto_cheque_transferencia" class="form-control" onkeyup="validateNumberInput( this );">
					<button 
						class="btn btn-primary"
						onclick="agrega_cheque_transferencia();">
						<i class="icon-plus"></i>
					</button>
				</div>
				<p class="text-center text-danger hidden" id="monto_cheque_transferencia_alerta">Campo numérico*</p>
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