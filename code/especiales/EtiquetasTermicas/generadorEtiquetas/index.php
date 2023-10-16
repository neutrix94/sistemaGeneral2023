<?php
	//include( '../../../../../config.ini.php' );
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
	//die( $user_id );
	$db = new Db( $link,  $sucursal_id );
	$permissions = $db->getPermissions( $user_id );
	$permission_read_only = "";
	if( $permissions['edit'] == 0 ){
		$permission_read_only = "readonly";
	}
?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<title>Generador de etiquetas</title>
</head>
<body>
<?php
	echo "<input type=\"hidden\" id=\"edition_permission\" value=\"{$permissions['edit']}\">";
?>
<!-- emergente -->
	<div class="emergent">
		<div tabindex="1" style="position: relative; top : 0 !important; left: 90%; z-index:1;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div>
<!-- encabezado -->
	<div class="row header">
		<div class="col-8 text-center">
			<div class="input-group">
				<input 
					type="text" 
					id="principal_seeker"
					class="form-control" 
					placeholder="Buscar Producto..."
					onkeyup="seek_product( event );"
				>
				<button 
					class="btn btn-primary"
					onclick="seek_product( 'intro' );"
					id="principal_seeker_search_btn"
				>
					<i class="icon-search"></i>
				</button>
			</div>
			<div id="seeker_response"></div>
		</div>
		<div class="col-4 text-center">
			<div class="input-group">
				<input 
					type="number" 
					class="form-control" 
					placeholder="# piezas"
					id="pieces_number"
				>
				<button 
					class="btn btn-primary hidden"
					id="pieces_number_edition_btn"
				>
					<i class="icon-pencil-neg"></i>
				</button>
				<button 
					class="btn btn-success"
					onclick="setPiecesNumber(  );"
					id="pieces_number_set_btn"
				>
					<i class="icon-right-big"></i>
				</button>
			</div>	
		</div>
	</div>
<!-- contenido -->
	<div class="row contentContainer">
		<div class="col-1"></div>
		<div class="col-11">
			<div class="group_card row">
				<br>
				<br>
				<br>

				<div class="col-12">
					<h4 id="product_description_header" class="text-center"></h4>
				</div>
				<div class="col-6">
					<label>Modelo</label>
					<input 
						type="text" 
						class="form-control" 
						style="background-color : white;"
						readonly
						id="product_model"
					>
				</div>

				<div class="col-6">
					<label>Pzs x caja</label>
					<input 
						type="number" 
						class="form-control" 
						readonly 
						style="background-color : white;"
						id="pieces_per_box"
					>
				</div>

				<div class="col-8">
					<label>Escanear</label>
					<div class="input-group">
						<input 
							type="text" 
							class="form-control" 
							placeholder="Escanear Productos"
							id="seeker_barcodes"
							onkeyup="scann_barcode( event );"
						>
						<button
							class="btn btn-primary"
							onclick="scann_barcode( 'intro' );"

						>
							<i class="icon-search"></i>
						</button>
					</div>
				</div>
				<div class="col-4">
					<label>Escaneado</label>
					<input 
						type="number" 
						class="form-control" 
						readonly 
						style="background-color : white;"
						id="scans_counter"
						onchange="calculate_barcodes_quantity()"
					>
				</div>
			<!-- resumen de etiquetas -->
				<div class="col-4 text-center">
					<br>
					<label for="boxes_quantity"><br>Caja</label>
					<input type="number" id="boxes_quantity" class="form-control" <?php echo $permission_read_only; ?>>
				</div>
				<div class="col-4 text-center">
					<br>
					<label for="packs_quantity"><br>Paquete</label>
					<input type="number" id="packs_quantity" class="form-control" <?php echo $permission_read_only; ?>>

				</div>
				<div class="col-4 text-center">
					<br>
					<label for="pieces_quantity">Presentacion<br>Incompleta</label>
					<input type="number" id="pieces_quantity" class="form-control" <?php echo $permission_read_only; ?>>
					Maquila incompleta
					<input type="number" id="decimal_quanity"  class=" form-control no_visible" value="0" <?php echo $permission_read_only; ?>>
				</div>
			<!-- resumen de etiquetas -->
				<div class="col-4 text-center" style="margin-top : 15px !important;">
					<button 
						class="btn btn-warning"
						onclick="printTags();"
					>
						<i class="icon-barcode">Imprimir</i><!--  -->
					</button>
				</div>

				<div class="col-4 text-center" style="margin-top : 15px !important;">
					<button 
						class="btn btn-danger"
						onclick="clean_current_product();"
					>
						<i class="icon-cw">Limpiar</i><!-- -->
					</button>
				<br>
				<br>

				</div>


				<div class="col-4 text-center" style="margin-top : 15px !important;">
					<button 
						class="btn btn-secondary"
						onclick="print_pieces();"
					>
						<i class="icon-barcode"> imp. Piezas</i><!-- -->
					</button>
				</div>

				<br>
				<br>
			</div>
			<br>
			<br>
			<div class="text-center">
				<h4>Detalles del Paquete : </h4>
				<div class="accordion" id="accordionExample">
					<div class="accordion-item">
						<h2 class="accordion-header" id="heading_1_0">
							<button 
								class="accordion-button collapsed" 
								type="button" data-bs-toggle="collapse" 
								data-bs-target="#collapse_1_0" 
								aria-expanded="true" 
								aria-controls="collapse_1_0" 
								onclick=""
								id="herramienta_1_0">
								<i class="icon-archive-1" style="font-size : 120%;"> Medidas de la caja</i>
							</button>
						</h2>
						<div 
							id="collapse_1_0" 
							class="accordion-collapse collapse description" 
							aria-labelledby="heading_1_0" 
							data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<table class="table table-striped table-bordered">
									<thead>
										<th>Largo cm</th>
										<th>Ancho cm</th>
										<th>Alto cm</th>
									</thead>
									<tbody id="meassures_container">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<h4 id="pack_type_description" class="text-start" style="color : red;"></h4>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Piezas Largo</th>
							<th>Piezas Ancho</th>
							<th>Piezas Alto</th>
						</tr>
					</thead>
					<tbody id="packs_details">

					</tbody>
				</table>
			</div>
		</div>
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
