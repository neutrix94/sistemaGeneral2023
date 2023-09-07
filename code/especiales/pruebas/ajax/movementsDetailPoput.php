<!DOCTYPE html>
<head>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css"/>
	<link rel="stylesheet" type="text/css" href="../css/styles.css">
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
var movementsDetailPoput_ = 0,
	productProviderPoput_ = 0,
	scannsDetailPoput_ = 0;
var current_initial_date = '<?php echo $_GET["current_initial_date"];?>';
var current_initial_time = '<?php echo $_GET["current_initial_time"];?>';
var current_warehouses = '<?php echo $_GET["current_warehouses"];?>';

	/*function show_scann_detail_( transfer_id, product_provider_id ){
		//alert();
		window.opener.show_scann_detail( transfer_id, product_provider_id );
	}*/

	function buildMovementsDetail( product_provider_id, current_initial_date, current_initial_time, current_warehouses ){
		var resp = ``;
		var url = "db.php?fl_db=show_movements_details&product_provider_id=" + product_provider_id;
		url += "&initial_date=" + current_initial_date + "&initial_hour=" + current_initial_time;
		url += "&current_warehouses=" + current_warehouses;
//alert( url );
		var response = ajaxR( url ).split( '|' );
//console.log( response );
//alert( response );
		//response = JSON.parse( response[0] );
//console.log( response );
		resp = `<table class="table table-bordered table-striped">
			<thead class="products_header">
				<tr>
					<th>Almacén</th>
					<th>Tipo Mov</th>
					<th>Usuario</th>
					<th>Inventario</th>
					<th>Cantidad</th>
					<th>Folio transferencia</th>
					<th>Bloque Val</th>
					<th>Bloque Rec</th>
					<th>Fecha del movimiento</th>
					<th>Ver</th>
				</tr>
			</thead>
			<tbody>`;
		resp += buildMoventsDetail( JSON.parse( response[1] ) );
		resp += `</tbody>
		</table>`;

		resp += `<br><h5 class="text-center">Movimiento por Venta</h5>
		<table class="table table-bordered table-striped">
			<thead class="products_header">
				<tr>
					<th>Almacén</th>
					<th>Tipo Mov</th>
					<th>Usuario</th>
					<th>Inventario</th>
					<th>Cantidad</th>
					<th>Folio transferencia</th>
					<th>Bloque Val</th>
					<th>Bloque Rec</th>
					<th>Fecha del movimiento</th>
					<th>Ver</th>
				</tr>
			</thead>
			<tbody>`;
		resp += buildMoventsDetail( JSON.parse( response[2] ) );
		resp += `</tbody>
		</table>`;
//alert( resp );
		$( '#poput_container' ).html( resp );
	}

	function buildMoventsDetail( movements ){
		var resp = ``;
		for( var movement in movements ){
			resp += `<tr>
					<th>${movements[movement].warehouse_name}</th>
					<th>${movements[movement].movement_name}</th>
					<th>${movements[movement].user_name}</th>
					<th>${movements[movement].inventory}</th>
					<th>${movements[movement].movement_quantity}</th>
					<th>${movements[movement].transfer_folio}</th>
					<th>${movements[movement].transfer_validation_block}</th>
					<th>${movements[movement].transfer_recepcion_block}</th>
					<th>${movements[movement].date} ${movements[movement].hour}</th>
					<th>
						<button
							class="btn"
							onclick="show_scann_detail( ${movements[movement].transfer_id}, ${movements[movement].product_provider_id} );"
						>
							<i class="icon-eye"></i>
						</button>
					</th>
				</tr>`;
		}
		return resp;
	}

	function show_scann_detail( transfer_id, product_provider_id ){
		var url = 'scannsDetailPoput.php?product_provider_id=' + product_provider_id;
		url += '&current_initial_date=' + current_initial_date + "&current_initial_time=" + current_initial_time;
		url += "&current_warehouses=" + current_warehouses + "&transfer_id=" + transfer_id;
		//alert( url );
		show_poput_( url, 'scannsDetail' );
	}
	function show_poput_( url, type ){
		switch( type ){
			case 'movementsDetail':
				movementsDetailPoput_ = window.open( url, 'Detalle de escaneos', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
			break;
			case 'productProvider':
		//var url = "ajax/notas.php?fl=getProducNotesBefore&product_id=" + product_id;
				productProviderPoput_ = window.open( url, 'Movimientos de almacen', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
				//global_show_historic_notes.innerHTML = '<button>here</button>';
			break;
			case 'scannsDetail':
				scannsDetailPoput_ = window.open( url, 'Detalle de escaneos', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
			break;
		}
	}
	function hide_poput_( type ){
		switch( type ){
			case 'movementsDetail':
				movementsDetailPoput_.close();
				movementsDetailPoput_ = 0;
			break;
			case 'productProvider':
				productProviderPoput_.close();
				productProviderPoput_ = 0;
			break;
			case 'scannsDetail':
				scannsDetailPoput_.close();
				scannsDetailPoput_ = 0;
			break;
		}
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

	<script type="text/javascript">
		buildMovementsDetail( '<?php echo $_GET['product_provider_id']?>', '<?php echo $_GET['current_initial_date']?>', '<?php echo $_GET['current_initial_time']?>', '<?php echo $_GET['current_warehouses']?>' );
	</script>	
		
</body>
</html>
