<?php
/*
Version 1.1 con resolucion de Transferencias seccionada por procesos 2024-08-16*/
//conexiones a la base de datos
	include( '../../../../config.inc.php' );
	include( '../../../../conectMin.php' );//sesión
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
//consulta si estan habilitadas las alertas
	$sql = "SELECT
			ald.alertas_habilitadas AS alert_log_enabled
		FROM sys_alertas_log al
		LEFT JOIN sys_alertas_log_detalle ald
		ON ald.id_alerta_log = al.id_alerta_log
		WHERE al.id_alerta_log = 2";
	$stm = $link->query($sql) or die( "Error al consultar el log de alerta : {$sql} : {$link->error}" );
	$row = $stm->fetch_assoc();
	$alert_log_enabled = $row['alert_log_enabled'];
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<link href="../../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/blocks.js"></script>
	<script type="text/javascript" src="../../plugins/js/barcodeValidationStructure.js"></script>
	<script src="../../../../js/highlight/highlight.min.js"></script>
	<link rel="stylesheet" href="../../../../js/highlight/styles/default.min.css">
    <script>hljs.highlightAll();</script>

	<title>Recepción de transferencias</title>
</head>
<body>
	<audio id="audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/scanner.mp3">
	</audio>
	<audio id="no_focus_audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/no_focus.mp3">
	</audio>
	<audio id="pieces_number_audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/pieces_number.mp3">
	</audio>

	<audio id="ok" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/ok.mp3">
	</audio>
	<audio id="error" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/error.mp3">
	</audio>
	<audio id="scan_box_barcode" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/scan_box_barcode.mp3">
	</audio>
	<audio id="scan_seil_barcode" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/scan_seil_barcode.mp3">
	</audio>
	<audio id="unic_code_is_repeat" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/unic_code_is_repeat.mp3">
	</audio>



	<!--audio id="code_was_scaned" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/code_was_scaned.mp3">
	</audio-->
<?php
	echo '<input type="hidden" id="user_id" value="' . $user_id . '" >';
	echo '<input type="hidden" id="alert_log_enabled" value="' . $alert_log_enabled . '" >';
	
	echo getSpecialPermissions( $user_id, $sucursal_id, $link );
?>
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
	
	<div class="emergent_3" style="z-index : 40;">
		<div style="position: relative; top : 120px; left: 90%; z-index:3; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_3" tabindex="3"></div>
	</div>

	<div class="emergent_4" style="z-index : 50;">
		<div style="position: relative; top : 120px; left: 90%; z-index:4; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_4" tabindex="4"></div>
	</div>

	<div class="global_container">
		<div class="header">
			<div class="row">
				<div class="mnu_item invoices active" onclick="show_view( this, '.transfers_list');">
					<i class="icon-menu"></i><br>
					Transferencias
				</div>
				<div class="mnu_item source" onclick="show_view( this, '.receive_transfers');">
					<i class="icon-truck"></i><br>
					Recibir
				</div>
				<div class="mnu_item validate" onclick="show_view( this, '.validate_transfers');">
					<i class="icon-ok-circle"></i><br>
					Verificar
				</div>
				<div class="mnu_item validate" onclick="show_view( this, '.finish_transfers');">
					<i class="icon-ok-circle"></i><br>
					Finalizar
				</div>
			</div>
		</div>

		<div class="content_container">
			<div class="content_item transfers_list">
				<?php 
					include( 'views/transfers_list.php' );
				?>
			</div>

			<div class="content_item receive_transfers hidden">
				<?php 
					include( 'views/receive_transfers.php' );
				?>
			</div>


			<div class="content_item validate_transfers hidden">
				<?php 
					include( 'views/validate_transfers.php' );
				?>
			</div>


			<div class="content_item finish_transfers hidden">
				<?php 
					include( 'views/resolution_view.php' );
				?>
			</div>

		</div>

		<div class="footer">
			<div class="row">
				<div class="col-3 txt_alg_left">
					<button 
						class="btn btn-light"
						onclick="redirect('home');"
					>
						<i class="icon-home-1"></i>
					</button>
				</div>

				<div class="col-6 text-center">
					<button
						type="button"
						class="btn btn-success form-control no_visible"
						id="btn_finish_reception"
						onclick="finish_transfers_reception();"
					>
						<i class="icon-truck" style="font-size : 80%;">Poner transferencia(s) en tránsito</i>
					</button>
				</div>

				<div class="col-3 txt_alg_right">
					<button class="btn btn-light">
						<i class="icon-off"></i>
					</button>
				</div>
			</div>
		</div>
	</div>

<?php
	echo getBarcodesTypes( $link );
?>

</body>
</html>

<script>
	function validateSpecialPermission(){
		if( $( '#show_reception_blocks_permission' ).val() != '1' ){
			//alert();
			$( '#blocks_resolution_container' ).remove();
		}	
	}

	validateSpecialPermission();
</script>

<script type="text/javascript">
	function validateTokenIsValid(){
		var url = "ajax/db.php?fl=validateTokenIsValid&reception_token=" + localStorage.getItem( 'reception_token' );
		//url += "&reception_block_id=" + global_transfers_block_reception_to_set;
		//alert( url );
		var response = ajaxR( url ).split( '|' );
		//alert( response );
		if( response[0] == 'ok' ){
			localStorage.setItem( 'is_principal_reception_session', response[1] );
			return true;
		}
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
		
	}
	function finish_login_session(){
		location.href = '../../../../index.php?cierraSesion=YES';
	}
	function remove_reception_token(){
		localStorage.removeItem( 'current_reception_block_id' );
		localStorage.removeItem( 'reception_token' );
		localStorage.getItem( 'is_principal_reception_session' );

		location.reload();
	}
/*implementacion Oscar 2023 para la sesion de validacion*/
	if( localStorage.getItem( 'reception_token' ) != null ){
		validateTokenIsValid();
	}
</script>