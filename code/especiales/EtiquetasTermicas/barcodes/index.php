<?php
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
	//echo updateBarcodesPrefix( $link );
?>
<!DOCTYPE html>
<head>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link href="../../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<title>Etiquetas de Códigos de Barras</title>
</head>
<body>
	<div class="emergent">
		<div class="emergent_content" tabindex="1"></div>
	</div>
	<div>
		<div class="row">

			<div class="col-1">
				<img 
					src="../../../../img/img_casadelasluces/Logo.png" 
					onclick="redirect( 'home' );"
					width="100%">
			</div>

			<div class="col-6">
				<p align="left"><b>Producto</b></p>
				<div class="input-group">
					<input 
						type="text" 
						id="products_seeker"
					 	class="form-control"
					 	placeholder="Buscar producto"
					 	onkeyup="seek_product( this, event );"
					>
					<button 
						class="btn btn-danger" 
						id="reset_seeker_btn"
						onclick="resetSeekerButton();"
					>
						<i class="icon-cancel-alt-filled"></i>
					</button>
				</div>
				<div id="seeker_response"></div>
			</div>
			
			<div class="col-2">
				<p align="left"><b>Presentación</b></p>
				<select 
					id="product_provider_model" 
					class="form-control"
					onchange="setProductProvider();"
				>
					<option value="0">--Seleccionar--</option>
				</select>	
			</div>

			<div class="col-2">
				<p align="left"><b>Tipo de Código</b></p>
				<?php
					echo getDefaultBarcodeType( $link );
				?>	
			</div>

			<div class="col-1"></div>
			<div class="col-1"></div>
			
			<div class="col-2" id="quantity_container">
				<p align="left"><b>Calcular</b>
					<button 
						class="btn btn-warning"
						onclick="show_config( 'default_calcular_etiquetas_cb' );"
					>
						<i class="icon-cog"></i>
					</button>
				</p>
				<input 
					type="checkbox"
					id="automatic_calculate"
				<?php 
					if( getConfigForm( 'default_calcular_etiquetas_cb', 1, $link ) == 1 ){
						echo 'checked';
					}
				?>
				>
			</div>

			<div class="col-2" id="quantity_container">
				<p align="left"><b>Cajas</b></p>
				<input 
					type="number"
					id="boxes_quantity"
					class="form-control"
					placeholder="#cantidad"
					onkeyup="calculate_quantities_barcodes( this );"
					onchange="calculate_quantities_barcodes( this );"
				>
				<input 
					type="checkbox"
					id="boxes_check"
					onchange="change_field_status( this );"
					checked
				>
			</div>

			<div class="col-2" id="quantity_container">
				<p align="left"><b>Paquetes</b></p>
				<input 
					type="number"
					id="packs_quantity"
					class="form-control"
					placeholder="#cantidad"
					onkeyup="calculate_quantities_barcodes( this );"
					onchange="calculate_quantities_barcodes( this );"
				>
				<input 
					type="checkbox"
					id="packs_check"
					onchange="change_field_status( this );"
					checked
				>
			</div>

			<div class="col-2" id="quantity_container">
				<p align="left"><b>Piezas</b></p>
				<input 
					type="number"
					id="pieces_quantity"
					class="form-control"
					placeholder="#cantidad"
					onkeyup="calculate_quantities_barcodes( this );"
					onchange="calculate_quantities_barcodes( this );"
					disabled
				>
				<input 
					type="checkbox"
					id="pieces_check"
					onchange="change_field_status( this );"
				>
			</div>

			<div class="col-2" id="confirm_button_container">
				<p align="left"><b>Agregar</b></p>
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="build_ceil();"
				>
					<i class="icon-ok-circle"></i>
				</button>
			</div>

		</div>
		
		<br>

		<div class="row">
			<div class="col-1"></div>
			<div class="col-10">
				<!--h4>Códigos por generar</h4-->
				<div class="barcodes_resume">
					<table class="table table-striped table-bordered">
						<thead class="head_resume_fixed">
							<tr>
								<th>#</th>
								<th>Producto</th>
								<th>Modelo</th>
								<th>Etiquetas<br>Cajas</th>
								<th>Etiquetas<br>Paquetes</th>
								<th>Etiquetas<br>Piezas</th>
								<th>Quitar</th>
							</tr>
						</thead>
						<tbody id="barcodes_list">
						</tbody>
					</table>
				</div>
				
				<br>
				
				<button 	
					class="btn btn-success form-control"
					onclick="generate_barcodes();"
				>
					<i class="icon-barcode">Generar archivo</i>
				</button>
			</div>
		</div>
	</div>
	<!--implementación Oscar 29.06.2018 para exportación en Excel-->
	<form id="TheForm" method="get" action="ajax/db.php" target="TheWindow">
			<input type="hidden" id="fl" name="fl" value="download_csv" />
			<input type="hidden" id="flag" name="flag" value="" />
			<input type="hidden" id="name" name="name" value="" />
			<input type="hidden" id="data" name="data" value=""/>
	</form>
</body>
</html>
<!-- Validacion de última actualización de prefijos de códigos de barras únicos -->
<script type="text/javascript">
	validate_barcodes_series_update();
</script>

<script type="text/javascript">
<?php
	if( isset( $_GET['product_provider'] ) ){
		echo "setProductProviderSinceGet( {$_GET['product_provider']}, {$_GET['boxes_quantity']}, {$_GET['pieces_quantity']} );";
	}
?>
</script>