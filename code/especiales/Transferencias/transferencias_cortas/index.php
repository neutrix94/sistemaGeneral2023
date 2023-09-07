<?php
	include( '../../../../conectMin.php' );
	include( '../../../../conexionMysqli.php' );
//verifica el permiso de transferencias locales
	$sql = "SELECT 
				IF( id_sucursal = -1, 'linea', 'local' ) AS system_type
			FROM sys_sucursales 
			WHERE acceso = 1";
	$stm = $link->query( $sql ) or die( "Errror al consultar si se permiten transferencias en local : {$this->link->error}" );
	$row = $stm->fetch_assoc();
	$system_type = $row['system_type'];

	$sql = "SELECT permite_transferencias AS allow_transfers FROM sys_sucursales WHERE id_sucursal = {$sucursal_id}";
	$stm = $link->query( $sql ) or die( "Errror al consultar si se permiten transferencias en local : {$this->link->error}" );
	$row = $stm->fetch_assoc();
	$allow_local_transfers = $row['allow_transfers'];
	//die( "{$system_type} && {$allow_local_transfers}" );
	if( $system_type == 'local' && $allow_local_transfers == 0 ){
		die( "<script>
				alert( 'No esta permitido hacer transferencias en local, si vas a hacer una transferencia se tiene que hacer desde el sistema en linea!' );
				location.href = '../../../../index.php';
			</script>" );
	}

	include( 'ajax/Transfer.php' );	
	$Transfer = new Transfer( $link );
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<link href="../../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	<link href="css/styles.css" rel="stylesheet" type="text/css"  media="all" />
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../plugins/js/barcodeValidationStructure.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<title>Transferencia</title>
</head>
<body>
	<audio id="ok" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/ok.mp3">
	</audio>
	<audio id="pieces_number_audio" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/pieces_number.mp3">
	</audio>
	<audio id="scan_again" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/transfers/scan_again.mp3">
	</audio>
	<audio id="error" controls style="display:none;">
		<source type="audio/wav" src="../../../../files/sounds/error.mp3">
	</audio>

	<div class="emergent">
		<div tabindex="1" style="position: relative; top : 0 !important; left: 90%; z-index:1; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div>

	<div class="emergent_2">
		<div tabindex="1" style="position: relative; top : 20px; left: 90%; z-index:3; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_2" tabindex="1"></div>
	</div>

	<div class="global_container">
		<div class="header">
			<input type="hidden" id="transfer_status" value="1">
			<div class="row">

				<div class="col-4">
					<label class="header_label">Tipo</label>
					<?php echo $Transfer->getTransferTypes(); ?>
				</div>

				<div class="col-8"><br>
					<h5>
						<input type="text" id="transfer_title" class="form-control" placeholder="Título de la transferencia">
					</h5>
				</div>

				<div class="col-6">
					<label class="header_label">Sucursal Origen</label><br>
					<?php echo $Transfer->getStores( 'origin', $sucursal_id ); ?>
				</div>
				<div class="col-6">
					<label class="header_label">Almacen Origen</label><br>
					<?php echo $Transfer->getWarehouses( 'origin' ); ?>
				</div>


				<div class="col-6">
					<label class="header_label">Sucursal Destino</label><br>
					<?php echo $Transfer->getStores( 'destinity' ); ?>
				</div>
				<div class="col-6">
					<label class="header_label">Almacen Destino</label><br>
					<?php echo $Transfer->getWarehouses( 'destinity' ); ?>
				</div>

				<div class="col-12">
					<button 
						type="button"
						class="btn btn-primary"
						id="create_transfer_btn"
						onclick="insertTransferHeader();">
						<i class="icon-ok-circle">Continuar</i>
					</button>
				</div>

			</div>
		</div>
		<div class="content">
			<div class="group_card" id="scanner_products_response"></div>
			<div class="input-group">
				<input type="text" class="form-control" 
					placeholder="Escanea codigo de barras"
					id="barcode_seeker"
					onkeyup="validateBarcode( this, event );"
					disabled
				>
				<button type="button" class="btn btn-warning"
					onclick="validateBarcode( '#barcode_seeker', 'enter' );"
				>
					<i  class="icon-barcode"></i>
				</button><!-- Botón de bloqueo -->
				<button 
					type="button" 
					id="barcode_seeker_lock_btn" 
					class="btn btn-danger" 
					onclick="lock_and_unlock_focus( this, '#barcode_seeker' )">
					<i class="icon-lock"></i>
				</button>
				<label for="multiple_pieces" class="btn">
					Form Pzs
					<input type="checkbox" id="multiple_pieces" checked style="margin : 5px;">
				</label>
			</div>

			<div id="seeker_response"></div>
				<table class="table table-striped">
					<thead class="header_sticky">
						<tr>
							<th class="text-center">Orden Lista</th>
							<th class="text-center">Clave Proveedor</th>
							<th class="text-center">Nombre</th>
							<th class="text-center">Cantidad</th>
							<th class="text-center">X</th>
						</tr>	
					</thead>
					<tbody id="transfer_products"></tbody>
				</table>
		</div>
	</div>
	<input type="hidden" id="current_store" value="<?php echo $sucursal_id; ?>">
	<div class="footer row">
		<div class="col-12 text-start">
			<button
				type="button"
				class="btn btn-success"
				onclick="save_transfer( 0 )"
			>
				<i class="icon-home">Guardar y salir</i>
			</button>
		</div>

		<!--div class="col-6 text-center">
			<button
				type="button"
				class="btn btn-success"
				onclick="save_transfer( 0 )"
			>
				<i class="">Finali y salir</i>
			</button>
		</div-->
	</div>
</body>
</html>


<?php
	if( isset( $_GET['pk'] ) ){
		echo "<script> getTransferDetail( {$_GET['pk']} ); </script>";

	}else{
		echo "<script> getPendingTransfers( {$sucursal_id} ); </script>";
	}

?>

<style type="text/css">
	.combo{
		border : 1px solid silver;
		padding: 6px;
		width:100%;
	}
	.header{
    	background-color: #718B1E;
    	padding : 10px ;
	}
	.header_label{
		color: white;
	}
</style>