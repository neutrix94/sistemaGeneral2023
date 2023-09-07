<!DOCTYPE html>
<head>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css"/>
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
	
var current_initial_date = '<?php echo $_GET["current_initial_date"];?>';
var current_initial_time = '<?php echo $_GET["current_initial_time"];?>';
var current_warehouses = '<?php echo $_GET["current_warehouses"];?>';

	function buildScannDetail_( transfer_id, product_provider_id ){
		var url = 'db.php?fl_db=getScannedDetail&transfer_id=' + transfer_id + "&product_provider_id=" + product_provider_id;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
			return false;
		}
		var tmp = JSON.parse( response[3] );
		var resp = `<div class="row">
			<h4>Detalle de escaneos : </h4>
			<div class="text-end">
				Códigos de validación de caja : <br>
				${tmp.boxes_validation_barcodes}
			</div>`;
		resp += `<br>` + buildScannDetail( JSON.parse( response[1] ), 'Validacion' );

		resp += `<br>` + buildScannDetail( JSON.parse( response[2] ), 'Recepcion' );
		resp += `</div>`;
		$( '#poput_container' ).html( resp );
	}
	function buildScannDetail( scanns, type ){
		var resp = `<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th colspan="5" class="text-center"><h5 class="text-center">${type}<h5></th>
						</tr>
						<tr>
							<th>Código</th>
							<th>Código Único</th>
							<th>Piezas</th>
							<th>Usuario</th>
							<th>Fecha</th>
						</tr>
					</thead>
					<tbody>`;
		for( var scann in scanns ){
			resp += `<tr>
						<td>${scanns[scann].code}</td>
						<td>${scanns[scann].unique_code}</td>
						<td>${scanns[scann].quantity}</td>
						<td>${scanns[scann].user_name}</td>
						<td>${scanns[scann].date_time}</td>
					</tr>`;
		}
		resp += `</tbody>
			</table>`;
		return resp;
	}

	function getMovements( transfer_id, product_provider_id ){
		//window.opener.show_scann_detail( transfer_id, product_provider_id );
		var url = 'movementsDetailPoput.php?product_provider_id=' + product_provider_id;
		url += '&current_initial_date=' + current_initial_date + "&current_initial_time=" + current_initial_time;
		url += "&current_warehouses=" + current_warehouses;
		//alert( url );
		location.href = url;
		
		//show_poput( url, 'movementsDetail' );
	}
	
	function ajaxR(url){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}
</script>
</head>
<body>	
	<div class="row" id="poput_container">
	</div>
	<div class="row">
		<div class="col-2"></div>
		<div class="col-8">
			<button
				class="btn btn-primary form-control"
				onclick="getMovements( <?php echo $_GET['transfer_id']?>, <?php echo $_GET['product_provider_id']?> );"
			>
				<i class="icon-left-big"> Regresar al detalle de Movmientos</i>
			</button>
		</div>

	</div>
	<script type="text/javascript">
		buildScannDetail_( <?php echo $_GET['transfer_id']?>, <?php echo $_GET['product_provider_id']?> );
	</script>	
		
</body>
</html>
