<?php
	include( '../../../../../config.ini.php' );
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/inventory.php' );
	$inventory = new Inventory( $link );

?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript">
		var boxes_ceils = '<?php echo $inventory->getBoxesCeils(); ?>'.split( ',' );
		//console.log( boxes_ceils );
	</script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/seeker.js"></script>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="../../plugins/js/barcodeValidationStructure.js"></script>
	<title>Conteo de Inventario</title>
</head>
<body>
<?php
	echo "<input type=\"hidden\" id=\"edition_permission\" value=\"{$permissions['edit']}\">";
	echo "<input type=\"hidden\" id=\"user_id\" value=\"{$user_id}\">";
?>
<!-- emergente -->
	<div class="emergent">
		<div tabindex="1" style="position: relative; top : 14% !important; left: 90%; z-index:1;">
			<button 
				class="btn btn-danger close_emergent_bnt"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div>
<!-- encabezado -->
	<div class="row header">
		<div class="col-12 text-center">
		<div class="row is_per_location_range_container">
			<div class="col-12">
				<label class="label_white">Ordenar Por:</label>
				<select class="form-control" id="order_by">
					<option value="">Ubicacion, Orden Lista</option>
					<option value="">Orden Lista</option>
				</select>
			</div>

			<div class="col-4">
				<label class="label_white">Familia:</label>
				<?php
					echo $inventory->getCategories();
				?>
			</div>

			<div class="col-4">
				<label class="label_white">Tipo:</label>
				<?php
					echo $inventory->getSubcategories();
				?>
			</div>

			<div class="col-4">
				<label class="label_white">Subtipo:</label>
				<?php
					echo $inventory->getSubtypes();
				?>
			</div>

		</div>
		<div class="row is_per_product_container">
			<br>
			<p class="label_white">Buscar / Escanear Producto</p>
			<div class="input-group">
				<input type="text" class="form-control" id="principal_seeker"
					onkeyup="seek_product( event );"
				>
				<button
					type="button"
					class="btn btn-primary"
					id="principal_seeker_search_btn"
					onclick="seek_product( 'intro' );"
				>
					<i class="icon-search"></i>
				</button>
				<button
					type="button"
					class="btn btn-warning"
					id="principal_seeker_reset_btn"
					onclick="clean_current_product();"
				>
					<i class="icon-spin3"></i>
				</button>
			</div>			
			<div id="seeker_response"></div>
		</div>
		</div>
		<div class="col-4 text-center">
			<div class="input-group">
				
			</div>	
		</div>
	</div>
<!-- contenido -->
	<div class="row contentContainer">
		<div class="col-1"></div>
		<div class="col-11">
			<div class="group_card row">
				<div>
					<h4 class="text-center" id="product_description_header"></h4><!-- Producto -->
					<h6 class="text-center" id="product_model"></h6><!-- Clave -->
					<p class="text-center" id="product_location"></p><!-- Ubicacion -->
				</div>
				<div class="input-group">
					<input 
						type="text" 
						class="form-control"
						id="product_seeker"
						placeholder="Escanear Producto"
						onkeyup="validate_barcode( event );"
						disabled
					>
					<button
						class="btn btn-primary"
						onclick="validate_barcode( 'intro' );"
					>
						<i class="icon-search"></i>
					</button>
				</div>
			</div>
			<br><br>
				<div class="row">
					<div class="col-8">
						<p class="text-end">Inventario del Sistema : </p>
					</div>
					<div class="col-4">
						<input type="number" class="form-control" id="product_provider_inventory" readonly>
					</div>
				</div>
			<br>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Cajas</th>
							<th>Paquetes</th>
							<th>Piezas</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody id="scanner_resume">
						<tr>
							<td id="boxes_scanned_quantity" class="text-end">0</td>
							<td id="packs_scanned_quantity" class="text-end">0</td>
							<td id="pieces_scanned_quantity" class="text-end">0</td>
							<td id="total_scanned_quantity" class="text-end">0</td>
						<tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="4">
								<button 
									type="button"
									class="btn btn-info form-control"
									onclick="showScannedHistoric();"
								>
									Ver historial de escaneos
								</button>
							</th>
						</tr>
					</tfoot>
				</table>
				<br><br>
				<button
					class="btn btn-success form-control"
					onclick="save_product_count();"
				>
					<i class="icon-ok-circled">Guardar</i>
				</button>
				<br><br>
				<button
					class="btn btn-warning form-control"
					onclick="ommit_product_provider();"
				>
					<i class="icon-ok-circled">Omitir</i>
				</button>
				<br><br>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th colspan="2" class="text-center">Productos Omitidos</th>
						</tr>
						<tr>
							<th>Producto</th>
							<th>Modelo</th>
						</tr>
					</thead>
					<tbody id="omited_products_list"></tbody>
				</table>
		</div>
		<br><br>
	</div>
<!-- pie de pagina -->
	<div class="row footer">
		<div class="col-4"></div>
		<div class="col-4 text-center">
			<button
				class="btn btn-light"
				onclick="redirect( 'home' );"
			>
				<i class="icon-home-1">Ir al panel</i>
			</button>
		</div>
		<div class="col-4"></div>
	</div>

</body>
</html>

<script type="text/javascript">
	show_initial_configuration();
//getDateTime();
</script>