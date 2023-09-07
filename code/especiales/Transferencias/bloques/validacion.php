<?php
	include( '../../../../config.ini.php' );
	include( '../../../../conectMin.php' );//sesión
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
	$Blocks = new Blocks( $link );
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/function.js"></script>
	<script type="text/javascript" src="js/validation_scanner.js"></script>
	<title>Edición de bloques de Validación de Transferencias</title>
</head>
<body>
<!-- emergente -->
	<div class="emergent">
		<div class="emergent_content" tabindex="1"></div>
	</div>
	<div class="emergent_2">
		<div class="emergent_content_2" tabindex="1"></div>
	</div>
	<div class="emergent_3">
		<div class="emergent_content_3" tabindex="1"></div>
	</div>
<!-- header -->
	<div class="header">
		<div class="row">
			<div class="col-1">
				<button class="btn btn-light" style="margin-top:-10px;padding-bottom : 16px;" 
					onclick="if( confirm('¿Salir al panel principal?') ){ location.href='../../../../index.php?'; }">
					<img src="../../../../img/img_casadelasluces/Logo.png" width="80%">
				</button>
			</div>
			<div class="col-4">
				<label>
					Buscar por folio
				</label>
				<input type="text" id="seeker" class="form-control" onkeyup="seek_transfer( event, this );">
				<div class="seeker_response"></div>
			</div>
			<div class="col-2"></div>
			<div class="col-4">
				<label>
					Sucursal Destino :
				</label>
				<br>
				<select class="combo" id="sucursal_id" onchange="reload_transfer_list('validation');">
					<option value="0"> -- Ver todo -- </option>
					<?php
						echo $Blocks->getSucursales( $sucursal_id );
					?>
				</select>
			</div>
		</div>
	</div>
<!-- contenido -->
	<div class="container">
		<br>
		<div class="row">
			<div class="col-1"></div>
			<div class="col-10 table_container">
				<table class="table table-bordered" style="width : 100%;">
					<thead class="header_sticky">
						<tr>
							<th class="text-center">Bloque Validación</th>
							<th class="text-center">Transferencia</th>
							<th class="text-center">Tipo</th>
							<th class="text-center">X</th>
						</tr>
					</thead>
					<tbody id="blocks_list">
					<?php
						echo $Blocks->getTransfers( $sucursal_id, 'validation' );//
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="footer">

	</div>
</body>
</html>

<style type="text/css">
	
</style>