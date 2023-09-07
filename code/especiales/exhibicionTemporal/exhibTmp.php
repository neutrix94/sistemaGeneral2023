<?php
	include("../../../conectMin.php");//incluimos el archivo de conexión
	include("../../../conexionMysqli.php");//incluimos el archivo de conexión
	include( 'ajax/exhibitionProducts.php' );
	$eP = new exhibitionProducts( $link );
?>
<!DOCTYPE html>
<html>
<head>
	<title>Exhibición</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/styles.css">
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
<script type="text/javascript" src="js/functions.js"></script>
</head>
<audio id="audio" controls style="display:none;">
		<source type="audio/wav" src="../../../files/scanner.mp3">
	</audio>
<body>
	<div class="emergent" tabindex="1">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display : none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content"></div>
	</div>

	<div class="emergent_2" tabindex="1">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display : none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content_2"></div>
	</div>

	<div class="global">
		<div class="row enc">
			<div class="col-6">
				<div class="input-group">
					<input 
						id="seeker_input"
						type="text" class="form-control"
						onkeyup="seekProductProvider( event )"
					>
					<button
						type="button"
						class="btn btn-warning"
					>
						<i class="icon-barcode"></i>
					</button>
					<div id="seeker_response"></div>
				</div>
			</div>
			<div class="col-2">
				<button
					class="btn btn-success"
					onclick="showNewProductForm();"
				>
					<i class="icon-ok-circle">Agregar nuevo</i>
				</button>
			</div>
		</div>
		<div class="contenido"><br>
			<table class="table table-bordered table-striped">
				<thead class="header_sticky">
					<tr>
						<th>Tipo</th>
						<th width="">Orden Lista</th>
						<th width="">Producto</th>
						<th width="">Inv Suc</th>
						<th width="">Tomado de Exh</th>
						<th width="">Piezas exh</th>
						<th width="">No exh</th>
						<th width="">Guardar</th>
						<th width="">No exhibir</th>
						<!--th width="">Notas</th-->
					</tr>
				</thead>
				<tbody id="contenidoTabla">
					<?php echo $eP->getPendingList( $user_sucursal );//include('cargaTmpExhib.php'); ?>
				</tbody>
			</table>
			<!--p align="right" style="width:90%">
				<button class="btn_save"><img src="../../../img/especiales/save.png" width="40px" onclick="guardar();"><br><b>Guardar</b></button>
			</p-->
			<div class="row">
				<div class="col-4 text-center">
					<i class="icon-ccw text-primary">Producto con devolucion</i>
				</div>
				<div class="col-4 text-center">
					<i class="icon-star text-info">Producto nuevo</i>
				</div>
				<div class="col-4 text-center">
					<i class="icon-bookmark text-danger">Producto en exclusion</i>
				</div>
			</div>
		</div>
		<div class="footer text-center">
			<button
				onclick="exit();"
				class="btn btn-warning"
			>
				<i class="icon-home">Regresar al Panel</i>
			</button>
			<a href="=" class="btn_footer"> </a>
		</div>
	</div>
</body>
</html>