<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Surtir Transferencia</title>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- funciones js -->
	<script type="text/javascript" src="js/packOffFunctions.js"></script>
	<script type="text/javascript" src="js/fields_types.js"></script>
<!-- estilos css -->
	<link rel="stylesheet" type="text/css" href="css/packOffStyles.css">
	<link href="../../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
</head>
<body>
	<div class="row">
		<div class="row header">
			<div class="col-2">
				<img src="../../../../img/img_casadelasluces/Logo.png" width="60px">
			</div>
			<div class="col-6">
				<input 
					type="text"
					id="transfer_detail_seeker"  
					class="form-control" 
					placeholder="Escanear Código de Barras.."
					onkeyup="search_transfer_detail( this, event );"
					readonly
				>
				<div class="resBusc"></div>
			</div>
			<div class="col-4">
				<div class="input-group">
					<input 
						type="text" 
						id="transfer_folio" 
						class="form-control" 
						placeholder="Ingrese la Transferencia"
						
					><!-- onkeyup="search_transfer( this, event );" -->
					<button 
						class="input-group-text btn btn-primary"
						id="btn-search-transfer"
						onclick="search_transfer( '#transfer_folio', event );"
					>
						<i class="icon-right-big"></i>
					</button>
					<button 
						id="btn-refresh-transfer"
						class="input-group-text btn btn-primary no_visible"
					>
						<i class="icon-cw"></i>
					</button>
				</div>
				<div class="resBusc transfer"></div>
				<input type="hidden" id="transfer_id" value="0">
			</div>
		</div>
		<br>
		<h4>Surtimiento de Transferencia</h4>
		<br>
		<div class="row container">
			<div class="col-1"></div>
			<div class="col-10">
				<table class="table" id="table_bodys_">
					<thead>
						<tr>
							<th class="col-1">#</th>
							<th class="col-6">Producto</th>
							<th class="col-2">Pedido</th>
							<th class="col-2">Surtido</th>
							<th class="col-1">Acciones</th>
						</tr>
					</thead>
					<tbody class="table_body">
					</tbody>
					<tfoot>
						<tr>
							<td></td>
							<td>Total : </td>
							<td></td>
							<td></td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="col-1"></div>
		</div>
	</div>
	<div class="row btn_save_d_g">
		<button 
			onclick="gft_save_data( 'table_body' );"
			class="btn btn-success"
		>Guardar</button>
	</div>
</body>
</html>