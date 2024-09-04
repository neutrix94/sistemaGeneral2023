<?php
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
//implementacion Oscar 2024-09-03 para no dejar usar la pantalla en local
	$sql = "SELECT
				id_sucursal AS store_id
			FROM sys_sucursales
			WHERE acceso = 1";
	$stm = $link->query( $sql ) or die( "Error al consultar el tipo de sistema : {$link->error}" );
	$row = $stm->fetch_assoc();
	if( $row['store_id'] >=1 ){
		die( "<script>alert( \"Esta pantalla solo puede ser usada en el sistema en linea.\" );location.href=\"../../../../index.php?\";</script>" );
	}
	
//busca nombre de sucursal y almacen principal
	$sql = "SELECT
				s.nombre AS store_name,
				a.id_almacen AS warehouse_id
			FROM sys_sucursales s
			LEFT JOIN ec_almacen a
			ON s.id_sucursal = a.id_sucursal
			AND a.es_almacen = 1
			WHERE s.id_sucursal = {$sucursal_id}";
	$stm = $link->query( $sql ) or die( "Error al consultar el nombre de la sucursal : {$link->error}" );
	$row = $stm->fetch_assoc();
	$store_name = $row['store_name'];
	$warehouse_id = $row['warehouse_id'];
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,height=device-height, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>Ubicaciones <?php echo $store_name;?></title>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
</head>
<body style="padding:20px !important;">
	<input type="hidden" id="warehouse_id" value="<?php echo $warehouse_id;?>">
	<div class="group_card" id="<?php echo "location_form_container{$specific_id}";?>">

		<div class="row" style="padding:20px !important;">	

			<div class="input-group">
				<input 
					type="text" 
					id="seeker_product_location"
					class="form-control"
					placeholder="Buscar / Escanear Productos"
					onkeyup="seekProductsLocations( this, event );"
				>

				<button 
					type="button"
					class="input-group-text btn btn-primary"
					id="product_seeker_location_form_btn"
					onclick="seekProductsLocations( '#seeker_product_location', 'enter' );"
				>
					<i class="icon-plus-circle"></i>
				</button>
				<button 
					type="button"
					class="input-group-text btn btn-warning"
					id="product_reset_location_form_btn"
					onclick="cleanProductLocationForm();"
				>
					<i class="icon-spin3"></i>
				</button>
			</div>
			<div class="product_location_seeker_response"></div>
		</div>

	<!-- Informacion del producto -->
		<div class="text-center">
			<label 
				id="product_name"
				class="product_name_location_form"
			></label>
			<input type="hidden" id="product_id">
			<input type="hidden" id="store_location_id">
		</div>
		<br>
		<div
			id="<?php echo $hidden_form_id;?>" 
			class="row new_location_form"
			style="display : flex !important; height : auto;"
		>
		<!-- ubicacion de -->
			<div class="col-12">
			<!--h3>Piso <b id="floor_from<?php echo $specific_id; ?>">0</b></h3-->
				<!--h4 class="text-primary text-center">Desde : </h4-->
				<div class="row">
					<div class="col-6 text-center">
						<label>Ubicación</label>
					</div>
					<div class="col-4 text-center no_visible">
						<label>Pasillo</label>
					</div>
					<div class="col-6 text-center">
						<label>Altura</label>
					</div>
				</div>
			</div>

			<div class="col-6">
				<input 
					type="number"
					id="location_number_from"
					class="form-control"
					onkeyup="character_filter( this, 'numeric' );detect_location_change();"
					placeholder="# desde"
				>
			</div>
		<!-- Pasillo de -->
			<div class="col-4 no_visible">
				<!--center><label for="">fila / pasillo</label></center-->
					<input 
						type="number"
						id="aisle_from"
						class="form-control"
						onkeyup="character_filter( this, 'numeric' );detect_location_change();"
						placeholder="desde"
					>
			</div>
			<div class="col-6">
				<input 
					type="text"
					id="level_from"
					class="form-control"
					onkeyup="character_filter( this, 'lower_case' );detect_location_change();"
					placeholder="desde"
				>
			</div>


		<!-- Ubicación hasta -->

		<!-- ubicacion de -->
			<div class="col-12 no_visible">
				<h4 class="text-primary text-center">Hasta : </h4>
			</div>
			<div class="col-4 no_visible">
				<input 
					type="number"
					id="location_number_until"
					class="form-control bg-light"
					onkeyup="character_filter( this, 'numeric' );detect_location_change();"
					placeholder="# hasta"
					readonly
				>
			</div>

			<div class="col-4 no_visible">
				<input 
					type="number"
					id="aisle_until"
					class="form-control bg-light"
					onkeyup="character_filter( this, 'numeric', '#aisle_from<?php echo $specific_id; ?>' );detect_location_change();"
					placeholder="al"
					readonly
				>
			</div>

			<div class="col-4 no_visible">
				<input 
					type="text"
					id="level_until"
					class="form-control bg-light"
					onkeyup="character_filter( this, 'lower_case', '#level_from<?php echo $specific_id; ?>' );detect_location_change();"
					placeholder="al"
					readonly
				>
			</div>

			<div class="row">
				<div class="col-6 text-center no_visible">
					<br>
					<label>Habilitado</label><br>
					<input type="checkbox" id="is_enabled" 
					style="transform : scale( 2 );"
					onclick="detect_location_change();">
				</div>

				<div class="col-6 text-center">
					<br>
					<label>Es Ubicacion Principal</label><br>
					<input type="checkbox" id="is_principal" style="transform : scale( 2 );"
					onclick="detect_location_change();">
				</div>
				<div class="col-6 text-center">
					<br>
					<label>Se surte en Ventas</label><br>
					<input type="checkbox" id="is_supplied" style="transform : scale( 2 );" disabled>
				</div>
			</div>

			<div>
				<br>
				<button 
					type="button" 
					class="btn btn-success form-control"
					id="save_location_btn_source"
					onclick="saveLocation();"
				>
					<i class="icon-floppy">Guardar Ubicación</i>
				</button>
			</div>
		</div>
	</div>
	<br>
	<div class="row" style="max-height : 250px; overflow : auto;">
		<table class="table table-bordered">
			<thead class="text-center bg-danger text-light" style="position:sticky;top : 0px;">
				<tr>
					<th>#Ubicacion</th>
					<th class="no_visible">Pasillo</th>
					<th>Nivel</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody id="locations_list">
			</tbody>
		</table>
	</div>
	<br><br><br><br>
	<div class="footer text-center" style="position : fixed; bottom : 0; left : 0% !important; width : 10%;">
		<button
		onclick="if( confirm( 'Salir?' ) ){location.href='../../../../index.php?';}";
		class="btn btn-light"
		
	>
		<i class="icon-home"></i>
	</button>
	</div>
</body>
</html>