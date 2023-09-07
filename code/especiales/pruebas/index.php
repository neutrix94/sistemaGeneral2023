<?php
	
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../css/icons/css/fontello.css">
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/components.js"></script>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<title>Pantalla de pruebas</title>
</head>
<body>
<!-- emergente -->
	<div class="emergent" style="z-index : 20;">
		<div style="position: relative; top : 120px; left: 90%; z-index:1; display:none;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div>
<!-- boton de configuracion de fecha y hora -->
<button
	type="button"
	class="btn btn-info rounded-circle config_btn"
	onclick="show_date_time_options();"
>
	<i class="icon-calendar-7"></i>
</button>
<!-- contenido -->
	<div class="global_container">
		<div class="row">
			<div class="col-2">
				<div class="row">
					<div class="input-group">
						<button 
							class="btn form-control warehouses_menu_container"
							onclick="show_and_hidde_warehouse_container();"
							
						>
							Almacén<i class="icon-down-open" id="warehouses_menu_btn"></i>
						</button>
					</div> 
				</div>
				<div class="row warehouses_container no_visible"></div>
			</div>
<!-- boton de actualizacion 
<div class="col-2">
	<button type="button"
	class="btn btn-warning form-control">
		Actualizar ( 2023-02-13 18:49:00 )
	</button>
</div>-->
			<div class="col-6" id="dinamic_header"></div>
			<div class="col-4">
				<button
					class="btn btn-danger form-control"
					onclick="reset_test();"
				>
					<i class="icon-warning">Resetear prueba</i>
				</button>
				<br><br>
			<!-- 
					onclick="updateProductInformation()" -->
				<button type="button"
					class="btn btn-warning form-control"
					onclick="update_display_data();"
				>
						Actualizar ( <b id="last_update_info">2023-02-13 18:49:00</b> )
				</button>
			</div>
		</div>

		<div class="row">
			<div class="col-6">
				<br>
				<div class="input-group">
					<input 
						type="search" 
						id="seeker" 
						onkeyup="search_menu( this, event );" 
						onsearch="search_menu( this, event );"
						class="form-control" 
						placeholder="Buscar producto"
					>
					<button
						class="btn btn-primary"
					>
						<i class="icon-search"></i>
					</button>
					<button
						class="btn btn-secondary"
						onclick="hidde_seeker_response();"
						id="close_btn_seeker_response_btn"
					>
						<i class="icon-eye-5"></i>
					</button>
				</div>
				<div class="seeker_response no_visible"></div>
			</div>
			<div class="col-4 text-end">
				<br>
				<button
					type="button"
					class="btn"
					onclick="getResolutionProducts();"
				>
					<i class="icon-warning">Resolucion</i>
				</button>
			</div>
		</div>
		<br>
		<div class="row table_container">
			<h4 class="header_sticky">Productos</h4>
			<table class="table table-striped table-bordered">
				<thead id="products_header">

				</thead>
				<tbody id="current_products_container">
					<?php
						include( 'test_rows.php' );//products_header
					?>
				</tbody>
			</table>
		</div>
		<br>
		<div class="row">
			<div class="col-3 text-center">
				<i class="icon-stop color_orange">Diferencia entre inventario acumulado y calculado</i>
			</div>
			<div class="col-3 text-center">
				<i class="icon-stop color_yellow">Diferencia entre inventario Producto y Proveedor-Producto</i>
			</div>
			<div class="col-3 text-center ">
				<i class="icon-stop color_red">Diferencia entre inventario linea y local</i>
			</div>
			<div class="col-3 text-center ">
				<i class="icon-stop color_blue">Diferencia entre inventarios</i>
			</div>
			<div class="row text-center">
				<button
					class="btn btn-light"
					onclick="if( confirm( 'Salir de esta pantalla?' )  ){location.href='../../../index.php?';}"

				>
					<i class="icon-home-1">Regresar al panel</i>
				</button>
			</div>
		</div>
	</div>
	<div id="trash_container" class="no_visible">

	</div>
	<div class="trash_btn_container">
		<button
			type="button"
			class="btn btn-light"
			id="trash_btn"
			onclick="show_and_hidde_trash_container( true );"
		>
			<i class="icon-trash"></i>
		</button>
	</div>
</body>
</html>


<style type="text/css">
	
</style>
<script type="text/javascript">
	getWarehousesCatalogue();
	getProductsCatalogue();
	if( localStorage.getItem( 'current_test_type' ) != null ){
		//alert( localStorage.getItem( 'current_test_type' ) );
		rebuildTestByLocalStorage();
		hidde_seeker_response();
	}else{
		show_emergent( type_test_component, false, false );
	}
	function rebuildTestByLocalStorage(){
		change_screen_header( localStorage.getItem( 'current_test_type' ) );
		loadWarehousesByLocalStorage();
		loadDateByLocalStorage();
		buildTrashProducts();
		if( localStorage.getItem( 'current_test_type' ) == 'transfer' ){
			setTransfers( 3, localStorage.getItem( 'current_transfers'), 'no' );//actualiza los bloques de transferencias
			loadTransferByLocalStorage();
		}else if( localStorage.getItem( 'current_test_type' ) == 'datetime' ){
			$( '#date_time_input' ).val( localStorage.getItem( 'current_date_time' ) );
		}
		loadProductsByLocalStorage();
		loadProductProviderNotesByLocalStorage();//carga notas de proveedor producto
		setTimeout( 'hidde_seeker_response()', 300 );
		$( '#close_btn_seeker_response_btn' ).click();
	}

	function loadProductProviderNotesByLocalStorage(){
		if( localStorage.getItem( 'current_product_provider_note' ) != null ){
			current_product_provider_note = localStorage.getItem( 'current_product_provider_note' ).split(',');
		}
	}
	function loadWarehousesByLocalStorage(){
		if( localStorage.getItem( 'current_warehouses' ) == null ){
			return false;
		}
		current_warehouses = localStorage.getItem( 'current_warehouses' ).split(',');
		for( var i = 0; i < current_warehouses.length; i++ ){
			$( "#warehouse_" + current_warehouses[i] ).prop( 'checked', true );
		}
		block_warehouses_checks( true );
		show_and_hidde_warehouse_container();

	}

	function loadTransferByLocalStorage(){
		current_products = new Array();
		current_transfers = localStorage.getItem( 'current_transfers' ).split( ',' );
		current_transfers_folios = localStorage.getItem( 'current_transfers_folios' ).split( ',' );
		current_transfers_validation_block = localStorage.getItem( 'current_transfers_validation_block' ).split( ',' );
		current_transfers_reception_block = localStorage.getItem( 'current_transfers_reception_block' ).split( ',' );
		buildTransferRows();
		loadLastUpdateByLocalStorage();
		$( '#block_warehouse_btn_container' ).addClass( 'no_visible' );
	}

	function loadDateByLocalStorage(){
	//	alert(1);
		$( '#date_time_input' ).val( localStorage.getItem( 'current_initial_date' ), localStorage.getItem( 'current_initial_time' ) );
		if( localStorage.getItem( 'current_initial_date' ) != null ){
			current_initial_date = localStorage.getItem( 'current_initial_date' );
		//alert( current_initial_date );
		}
		if( localStorage.getItem( 'current_initial_time' ) != null ){
		//alert(3);
			current_initial_time = localStorage.getItem( 'current_initial_time' );
		}
	}
	function loadProductsByLocalStorage(){
		if( localStorage.getItem( 'current_test_type' ) == 'transfer' ){
			return false;
		}
		if( localStorage.getItem( 'current_products' ) == null ){
			return false;
		}
		var products = localStorage.getItem( 'current_products' ).split(',');
		//alert( products );
		for( var i = 0; i < products.length; i++ ){
			//alert(  );
			if( localStorage.getItem( 'deletedProducts' ) != null ){
				var tmp = localStorage.getItem( 'deletedProducts' ).split( ',' );
				var exists = false;
				for( var j = 0; j < tmp.length; j++ ){
					if( tmp[j] == products[i] ){
						exists = true;
					}
				}
				if( ! exists ){
					insertProductRow( products[i] );
				}
			}else{
				insertProductRow( products[i] );
			}
		}
	}
	function loadLastUpdateByLocalStorage(){
		/*if( localStorage.getItem( 'last_update_date' ) != null ){
			last_update_date = localStorage.getItem( 'last_update_date' );
		}
		if( localStorage.getItem( 'last_update_time' ) != null ){
			last_update_time = localStorage.getItem( 'last_update_time' );
		}else{
		}*/
		update_display_data( false );
		$( '#last_update_info' ).html( `${last_update_date} ${last_update_time}` );
	}
</script>
<script type="text/javascript">
	setTimeout( function(){
		hidde_seeker_response();
	}, 1000 );
</script>