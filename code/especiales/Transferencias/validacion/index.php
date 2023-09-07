<?php
	include( '../../../../config.inc.php' );
	include( '../../../../conectMin.php' );//sesión
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
//die( getPermissionToMAkeBlocks( $user_id, $link ) );
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
	<title>Validación de Tansferencias</title>
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

	<input type="hidden" id="blocks_permission" value="<?php echo getPermissionToMAkeBlocks( $user_id, $link ); ?>">
	<!--audio id="audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/scanner.mp3">
	</audio>
	<audio id="no_focus_audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/no_focus.mp3">
	</audio>
	<audio id="pieces_number_audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/pieces_number.mp3">
	</audio-->


	<div class="emergent">
		<div class="btn_close_container">
			<button
				class="btn btn-danger"
				onclick="close_emergent();"
			>
				X
			</button>
		</div>
		<div class="emergent_content"></div>
	</div>


	<div class="emergent_2">
		<div class="btn_close_container">
			<button
				class="btn btn-danger"
				onclick="close_emergent();"
			>
				X
			</button>
		</div>
		<div class="emergent_content_2"></div>
	</div>
	
	<div class="header">
		<div class="row">
			<div class="mnu_item invoices active" onclick="show_view( this, '.transfers_lists');">
				<i class="icon-menu"></i><br>
				Transferencias por validar
			</div>
			<div class="mnu_item source" onclick="show_view( this, '.transfers_products');">
				<i class="icon-snowflake-o"></i><br>
				Validación de productos
			</div>
			<div class="mnu_item source" onclick="show_view( this, '.resume');">
				<i class="icon-chart-line"></i><br>
				Resumen
			</div>
		</div>
	</div>

	<div class="global_container">
<?php
	$pending_adjustments = getInventoryAdjudments( $user_id, $link );
	if( $pending_adjustments != 'ok' && !isset( $_GET['ommitInvAdj'] ) ){
		echo $pending_adjustments;
	}else{
		echo '<div class="content_item transfers_lists">';
				include( 'views/transfers_lists.php' );
		echo '</div>';

		echo '<div class="content_item transfers_products hidden">';
				include( 'views/transfers_products.php' );
		echo '</div>';

		echo '<div class="content_item resume hidden">';
				include( 'views/resume.php' );
		echo '</div>';
	}

?>		
	</div>

	<div class="footer">
		<div class="row">
			<div class="col-1"></div>
			<div class="col-10"><!-- txt_alg_left -->
				<center>
					<button 
						class="btn btn-light"
						onclick="redirect('home');"
					>
						<i class="icon-home-1"></i>
					</button>

				<button
					type="button"
					class="btn btn-success"
					onclick="finish_validation();"
					id="btn_finish_validation"
				>
					Finalizar Revisión
				</button>
				</center>
			</div>
		</div>
	</div>
<?php
	echo getBarcodesTypes( $link );
?>
	<!--/div-->

</body>
</html>

<script type="text/javascript">
	function validateTokenIsValid(){
		var url = "ajax/db.php?fl=validateTokenIsValid&validation_token=" + localStorage.getItem( 'validation_token' );
		var response = ajaxR( url ).split( '|' );
		if( response[0] == 'ok' ){
			localStorage.setItem( 'is_principal_validation_session', response[1] );
			return true;
		}
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
		
	}
	function finish_login_session(){
		location.href = '../../../../index.php?cierraSesion=YES';
	}
	function remove_validation_token(){
		localStorage.removeItem( 'validation_token' );
		localStorage.removeItem( 'current_validation_block_id' );
		localStorage.removeItem( 'is_principal_validation_session' );
		location.reload();
	}
/*implementacion Oscar 2023 para la sesion de validacion*/
	if( localStorage.getItem( 'validation_token' ) != null ){
		validateTokenIsValid();
	}
</script>