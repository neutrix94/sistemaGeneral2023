var productsCatalogue = null;
var warehousesCatalogue = null;

var current_warehouses = new Array();//
var current_products = new Array();//

var current_test_type = null;//
var current_initial_date = null;//
var current_initial_time = null;//
var current_date_time = null;

var current_transfers = new Array();//
var current_transfers_folios = new Array();//
var current_transfers_validation_block = new Array();//
var current_transfers_reception_block = new Array();//

var warehouses_are_blocked = false;

var productProviderPoput = 0;
var scannsDetailPoput = 0;
var movementsDetailPoput = 0;

var deletedProducts = new Array();

//auxiliar de actualizacion
var last_update_date = null;
var last_update_time = null;

var current_product_provider_note = new Array();

	function getResolutionProducts(){
		var block_resolution, product_resolution;
		var resp = ``;
		if( current_transfers_reception_block == null ){
			alert( "No se pude visualizar el detalle de resolucion ya que no hay un bloque de recepcion!" );
			return false;
		}
		var url = "ajax/db.php?fl_db=getResolutionProducts&reception_block_id=" + current_transfers_reception_block;
		//console.log( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}
		if( response[1] != '' ){
			block_resolution = JSON.parse( response[1] );
			resp += build_resolution_rows( 1, block_resolution );
		}
		if( response[1] != '' ){
			product_resolution = JSON.parse( response[2] );
			resp += build_resolution_rows( 2, product_resolution );
		}
		resp += `<div class="row text-center">
			<div class="col-4"></div>
			<div class="col-4 text-center">
				<button
					type="button"
					class="btn btn-success"
					onclick="close_emergent();"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>
			</div>
		</div>`;
		/*console.log( block_resolution );
		console.log( product_resolution );*/
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function build_resolution_rows( type, data ){
		console.log( data );
		var resp = `<div class="row">
			<h5 class="text-center">Productos en resolucion <b>${type}</b></h5>
			<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>Producto / Proveedor Producto</th>
					<th>Faltante</th>
					<th>Sobrante</th>
					<th>No corresponde</th>
					<th>Se quedan</th>
					<th>Se regresan</th>
					<th>Faltaron</th>
					<th>Conteo</th>
					<th>Conteo Excedente</th>
					<th>Diferencia</th>
					<th>Prod Res</th>
				</tr>
			</thead>
			<tbody>`;
		for( var resolution in data ){
			resp += `<tr>
				<td>${data[resolution].product_name}</td>
				<td class="text-end">${data[resolution].missing_pieces}</td>
				<td class="text-end">${data[resolution].excedent_pieces}</td>
				<td class="text-end">${data[resolution].doesnt_correspond_pieces}</td>
				<td class="text-end">${data[resolution].pieces_stayed}</td>
				<td class="text-end">${data[resolution].pieces_returned}</td>
				<td class="text-end">${data[resolution].pieces_missed}</td>
				<td class="text-end">${data[resolution].fisic_count}</td>
				<td class="text-end">${data[resolution].excedent_count}</td>
				<td class="text-end">${data[resolution].difference}</td>
				<td class="text-end">${data[resolution].product_resolution_id}</td>
			</tr>`;
		}
		resp += `</tbody>
			</table>
		</div>`;
		return resp;
	}

	function show_date_time_options(){
		var resp = date_time_component.replace( '__initial_date__', localStorage.getItem( 'current_initial_date' ) );
		resp = resp.replace( '__initial_time__', localStorage.getItem( 'current_initial_time' ) );
		//alert( resp );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}
	function setInitialDateTime(){
		localStorage.setItem( 'current_initial_date', $( '#config_initial_date' ).val() );
		current_initial_date = localStorage.getItem( 'current_initial_date' );
		localStorage.setItem( 'current_initial_time', $( '#config_initial_time' ).val() );
		current_initial_time = localStorage.getItem( 'current_initial_time' );

		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function update_display_data( reload = true ){
		var tmp = getDateTime( 'return' );
		//alert( tmp );
		localStorage.setItem( 'last_update_date', tmp[0] );
		localStorage.setItem( 'last_update_time', tmp[1] );
		last_update_date = tmp[0];
		last_update_time = tmp[1];
		if( reload ){
			location.reload();
		}
	}

	function deleteProduct( product_id, position ){
		//var response = ajaxR( url );
		deletedProducts.push( product_id );
		localStorage.setItem( 'deletedProducts', deletedProducts );
	}
//funciones de encabezado
	function change_screen_header( value = null ){
		var type = ( value == null ? $( '#test_type_combo' ).val() : value ); 
		( type == 'transfer' ? $( '#transfer_seeker_container' ).removeClass( 'no_visible' ) : $( '#transfer_seeker_container' ).addClass( 'no_visible' ) );
		( type == 'datetime' ? $( '#date_time_tmp_container' ).removeClass( 'no_visible' ) : $( '#date_time_tmp_container' ).addClass( 'no_visible' ) );
		if(type == 0 ){
			alert( "Selecciona un tipo de prueba válido para continuar!" );
			$( '#test_type_combo' ).focus();
			return false;
		}
		$( '#dinamic_header' ).html( type == 'transfer' ? header_transfer_test_component : header_date_test_component );
			
	}

//
	function getProductsCatalogue(){
		var url = "ajax/db.php?fl_db=getProductsCatalogue";
		var response = ajaxR( url );
		productsCatalogue = JSON.parse( response );
		//console.log( productsCatalogue );			
		buildProductsCatalogue();
	}
//
	function buildProductsCatalogue(){
		resp = `<button 
					class="btn rounded-circle hidde_seeker_response_btn"
					onclick="hidde_seeker_response();"
				>
					<i class="icon-up-big"></i>
				</button>`;
		for (var product in productsCatalogue){
			resp += `<div class="seeker_item"
				onclick="insertProductRow( ${productsCatalogue[product].product_id} );"
			>
				${productsCatalogue[product].product_name}
			</div>`;
		}
		$( '.seeker_response' ).html( resp );
	}
/****************************************************** Almacenes ***********************************************/
	function getWarehousesCatalogue(){
		var url = "ajax/db.php?fl_db=getWarehousesCatalogue";
		var response = ajaxR( url );
		//alert( response );
		warehousesCatalogue = JSON.parse( response );
		//console.log( warehousesCatalogue );
		buildWarehouseCatalogue();
	}

	function buildWarehouseCatalogue(){
		resp = ``;
		for (var warehouse in warehousesCatalogue){//${warehousesCatalogue[warehouse].warehouse_id}
			resp += `<div class="col-3 accordion_item_container">
						<input type="checkbox" 
							id="warehouse_${warehousesCatalogue[warehouse].warehouse_id}"
							value="${warehousesCatalogue[warehouse].warehouse_id}"
							onclick="setWarehouses( this );"
							class="warehouse_check"
						>
						<label for="warehouse_${warehousesCatalogue[warehouse].warehouse_id}">${warehousesCatalogue[warehouse].warehouse_name}</label>
					</div>`;
		}
		if( warehouses_are_blocked == false ){
			resp += `<div class="row text-center" id="block_warehouse_btn_container">
					<button
						class="btn btn-success form-control"
						onclick="block_warehouses_checks( true );"
					>
						<i class="icon-ok-circle">Aceptar</i>
					</button>
			</div>`;
		}else{
			resp += `<div class="row text-center" id="block_warehouse_btn_container">
					<button
						class="btn btn-warning"
						onclick="block_warehouses_checks( false );"
					>
						<i class="icon-pencil">Editar</i>
					</button>
			</div>`;
		}
		$( '.warehouses_container' ).html( resp );
	}

	function block_warehouses_checks( value ){
		if( current_warehouses.length == 0 && value == true ){
			alert( "Debes de seleccionar almenos un almacen!" );
			return false;
		}
		//alert( value );
		if( value == false ){
			$( '.warehouse_check' ).removeAttr( 'disabled' );
			$( '#block_warehouse_btn_container' ).html( `<button
						class="btn btn-success"
						onclick="block_warehouses_checks( true );"
					>
						<i class="icon-ok-circle">Aceptar</i>
					</button>` );
		}else{
			$( '.warehouse_check' ).attr( 'disabled', true );
			$( '#block_warehouse_btn_container' ).html( `<button
						class="btn btn-warning"
						onclick="block_warehouses_checks( false );"
					>
						<i class="icon-pencil">Editar</i>
					</button>` );
			show_and_hidde_warehouse_container();
		}
	}

	function setWarehouses( obj ){
		var warehouse_id = $( obj ).attr( 'value' ).trim();
		/*if( current_warehouses.length == 2 && $( obj ).prop( 'checked' ) == true ){	
			alert( "Solo se pueden seleccionar dos almacenes como máximo!" );
			$( obj ).removeAttr( 'checked' );
			return false;
		}*/
		if( $( obj ).prop( 'checked' ) == true ){//agregar almacen
			current_warehouses.push( warehouse_id );
		}else{//quitar almacen
			for ( var i = 0; i < current_warehouses.length; i++ ) {
				if(  warehouse_id == current_warehouses[i] ){
					current_warehouses.splice( i, 1 );
				}	
			}
		}
	//guarda almacenes en el localStorage
		localStorage.setItem( 'current_warehouses', current_warehouses );
	}

	function show_and_hidde_warehouse_container(){
		//alert( $( '.warehouses_container' ).hasClass( '.no_visible' ) );
		if( $( '.warehouses_container' ).hasClass( 'no_visible' )  ){
			$( '.warehouses_container' ).removeClass( 'no_visible' );
			$( '#warehouses_menu_btn' ).removeClass( 'icon-down-open' );
			$( '#warehouses_menu_btn' ).addClass( 'icon-up-open' );
			
		}else{
			$( '.warehouses_container' ).addClass( 'no_visible' );
			$( '#warehouses_menu_btn' ).removeClass( 'icon-up-open' );
			$( '#warehouses_menu_btn' ).addClass( 'icon-down-open' );
		}
	}

	function show_movements_details( product_provider_id ){
		var url = 'ajax/movementsDetailPoput.php?product_provider_id=' + product_provider_id;
		url += '&current_initial_date=' + current_initial_date + "&current_initial_time=" + current_initial_time;
		url += "&current_warehouses=" + current_warehouses;
		show_poput( url, 'movementsDetail' );
	}

	function show_scann_detail( transfer_id, product_provider_id ){
		var url = 'ajax/scannsDetailPoput.php?product_provider_id=' + product_provider_id;
		url += '&current_initial_date=' + current_initial_date + "&current_initial_time=" + current_initial_time;
		url += "&current_warehouses=" + current_warehouses + "&transfer_id=" + transfer_id;
		//alert( url );
		show_poput( url, 'scannsDetail' );
		//show_poput( 'ajax/scannsDetailPoput.php?&transfer_id=' + transfer_id + "&product_provider_id=" + product_provider_id, 'scannsDetail' );
	}

	

	function show_poput( url, type ){
		switch( type ){
			case 'movementsDetail':
				movementsDetailPoput = window.open( url, 'Detalle de escaneos', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
			break;
			case 'productProvider':
		//var url = "ajax/notas.php?fl=getProducNotesBefore&product_id=" + product_id;
				productProviderPoput = window.open( url, 'Movimientos de almacen', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
				//global_show_historic_notes.innerHTML = '<button>here</button>';
			break;
			case 'scannsDetail':
				scannsDetailPoput = window.open( url, 'Detalle de escaneos', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
			break;
		}
	}
	function hide_poput( type ){
		switch( type ){
			case 'movementsDetail':
				movementsDetailPoput.close();
				movementsDetailPoput = 0;
			break;
			case 'productProvider':
				productProviderPoput.close();
				productProviderPoput = 0;
			break;
			case 'scannsDetail':
				scannsDetailPoput.close();
				scannsDetailPoput = 0;
			break;
		}
	}

//ver emergente
	function show_emergent( content, render_accept_btn = true, render_close_btn = true ){
		if( render_accept_btn == true ){
			content += `<br><div class="row">
				<div class="col-4"></div>
				<div class="col-4">
					<button 
						class="btn btn-success form-control"
						onclick="close_emergent();"
					>
						<i class="icon-ok-circled">Aceptar</i>
					</button>
				</div>
			</div>`;
		}
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
		
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

//buscador
	function hidde_seeker_response( hidde = false ){
		if( $( '.seeker_response' ).hasClass( 'no_visible' ) || hidde == true  ){
			$( '.seeker_response' ).removeClass( 'no_visible' );
			$( '.icon-eye-5' ).addClass( 'icon-eye-off' );
			$( '.icon-eye-5' ).removeClass( 'icon-eye-5' );
		}else{
			$( '.seeker_response' ).addClass( 'no_visible' );
			$( '.icon-eye-off' ).addClass( 'icon-eye-5' );
			$( '.icon-eye-off' ).removeClass( 'icon-eye-off' );
		}
	}

	function search_menu(obj, e){
		if( e.keyCode == undefined ){
			hidde_seeker_response();
			return false;
		}
		var txt_orig = $( obj ).val().trim().toUpperCase();
		var txt = txt_orig.split(' ');
		var size = productsCatalogue.length;
		var resp;
		if( $( obj ).val().length <= 3 ){
			$( '.seeker_item' ).css( 'display', 'block' );
			return false;
		}
		var ref_comp = txt.length;
		var was_finded_by_barcode = null;
		if( e.keyCode == 13 ){
	//busca por codigo de barras
			for (var product in productsCatalogue){
				if( ( productsCatalogue[product].codigo_barras_pieza_1 == txt_orig && productsCatalogue[product].codigo_barras_pieza_1 != '' )
				 || ( productsCatalogue[product].codigo_barras_pieza_2 == txt_orig  && productsCatalogue[product].codigo_barras_pieza_2 != '' )
				 || ( productsCatalogue[product].codigo_barras_pieza_3 == txt_orig  && productsCatalogue[product].codigo_barras_pieza_3 != '' )
				 || ( productsCatalogue[product].codigo_barras_presentacion_cluces_1 == txt && productsCatalogue[product].codigo_barras_presentacion_cluces_1 != '' )
				 || ( productsCatalogue[product].codigo_barras_presentacion_cluces_2 == txt && productsCatalogue[product].codigo_barras_presentacion_cluces_2 != '' )
				 || ( productsCatalogue[product].codigo_barras_caja_1 == txt_orig && productsCatalogue[product].codigo_barras_caja_1 != '' )
				 || ( productsCatalogue[product].codigo_barras_caja_2 == txt_orig && productsCatalogue[product].codigo_barras_caja_2 != '' ) ){
					was_finded_by_barcode = product;
    				break;//detiene busqueda
				}
			}
		}
		//alert( was_finded_by_barcode );
		if( was_finded_by_barcode != null ){
			//alert( 'here' );
			insertProductRow( productsCatalogue[was_finded_by_barcode].product_id );
			$( obj ).val( '' );
			//hidde_seeker_response( true );
			return false;
		}
		//alert();
	//seeker_response
		$( '.seeker_response' ).children( 'div' ).each( function( index ){
			var txt_comp = $( this ).html().toUpperCase().trim();
			var matches  = 0;
			for (var j = 0;j < ref_comp; j++) {//comparacion de cadena de texto
				txt_comp.includes(txt[j]) ? matches ++ : null;
			}			
			$( this ).css('display', matches ==  ref_comp ? 'block' : 'none');
		});
		$( '.seeker_response' ).removeClass( 'no_visible' );
		$( '.icon-eye-5' ).addClass( 'icon-eye-off' );
		$( '.icon-eye-5' ).removeClass( 'icon-eye-5' );

		if( was_finded_by_barcode != null ){
			hidde_seeker_response( true );
		}

	}

	function insertProductRow( product_id ){
		//alert();
		if( current_warehouses.length <= 0 ){
			alert( "Es necesario que primero elijas los almacenes" );
			hidde_seeker_response();
			$( '#seeker' ).val( '' );
			show_and_hidde_warehouse_container();
			return false;
		}
	//verifica que el producto no exista en el local storage
		var tmp = ( localStorage.getItem( 'current_products' ) ? localStorage.getItem( 'current_products' ).split( ',' ) : '' );
		var exists = false;
		for( var i = 0; i < tmp.length; i++ ){
			if( tmp[i] == product_id ){
				exists = true;
			}
		}
		if( exists == false ){
			localStorage.setItem( 'current_products', ( localStorage.getItem( 'current_products' ) ? localStorage.getItem( 'current_products' )  + ',' : '' )  + product_id );
		}
		var url = "ajax/db.php?fl_db=getProductDetail&product_id=" + product_id;
		url += "&warehouses=" + current_warehouses;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			console.log( response );
			alert( "Error : \n" + response );
			return false;
		}
		//SE ELIMINA DEL SESSION STORAGE DE PRODUCTOS ELIMINADOS

			if( localStorage.getItem( 'deletedProducts' ) != null ){
				var tmp = localStorage.getItem( 'deletedProducts' ).split( ',' );
				var exists = false;
				var final_deleted_array = '';
				for( var j = 0; j < tmp.length; j++ ){
					if( tmp[j] == product_id ){
						exists = true;
					}
					if( ! exists ){
						final_deleted_array += ( final_deleted_array == '' ? '' : ',' );
						final_deleted_array += tmp[j];
					}
				}
				localStorage.setItem( 'deletedProducts', final_deleted_array );
			}
			var product = JSON.parse( response[1] );
			current_products.push( product );

			buildProductRow( current_products.length - 1 );
			//console.log( current_products );
			//console.log( localStorage.getItem( 'current_products' ) );
			$( '#seeker' ).val( '' );
			hidde_seeker_response();
	}

	function buildProductRow( position ){
		if( $( '#products_header' ).html().trim() == '' ){
			buildTableHeader();
		}
		var resp = `<tr id="product_row_${position}" onclick="show_product_detail( ${position} );">
				<td width="200px" class="text-start">
					${current_products[position].product_name} 
					<i id="icon_row_${position}" class="icon-up-open"></i>
				</td>`;
		resp += buildProductInventory( position );
		resp += `<td width="50px">
					<button
						class="btn"
						onclick="removeProduct( this, ${current_products[position].product_id}, ${position} )"
					>
						<i class="icon-cancel-alt-filled"></i>
					</button>
				</td>
			</tr>`;
		var colspan = ( current_warehouses.length * 4 ) + ( 2 );
		resp += `<tr id="product_row_detail_${position}">
					<td colspan="${colspan}" style="margin : 0; padding : 0;">`;
			resp += buildProductRowDetail( position );
		resp += `</td>
				</tr>`;
		$( '#current_products_container' ).append( resp );
	}

	function buildProductInventory( position ){
		resp = ``;
		for( var row in current_products[position].product_info ){
			var local_class = "",
				online_class = "";
			if( current_products[position].product_info[row]['local_product_inventory'] != current_products[position].product_info[row]['local_inventory'] ){
				local_class += " color_orange";
			}
			if( current_products[position].product_info[row]['online_product_inventory'] != current_products[position].product_info[row]['online_inventory'] ){
				online_class += " color_orange";
			}
			resp +=`<td class="text-end ${local_class}">${current_products[position].product_info[row]['local_product_inventory']}</td>
					<td class="text-end calculate_row no_visible ${local_class}">${current_products[position].product_info[row]['local_inventory']}</td>
					<td class="text-end">${current_products[position].product_info[row]['online_product_inventory']}</td>
					<td class="text-end calculate_row no_visible">${current_products[position].product_info[row]['online_inventory']}</td>`;
		}
		return resp;
	}

//mostrar / ocultar resultados ocultos
	function show_and_hidde_calculated_rows( obj, show = false ){
		if( show == true ){
			$( '.calculate_row' ).removeClass( 'no_visible' );
			$( obj ).attr( 'onclick', 'show_and_hidde_calculated_rows( this, false );' );
			$( obj ).removeClass( 'icon-eye-1' );
			$( obj ).addClass( 'icon-eye-off' );
		}else{
			$( '.calculate_row' ).addClass( 'no_visible' );
			$( obj ).attr( 'onclick', 'show_and_hidde_calculated_rows( this, true );' );
			$( obj ).removeClass( 'icon-eye-off' );
			$( obj ).addClass( 'icon-eye-1' );
		}
	}

	function show_product_provider_note( product_provider_id, product_provider_clue ){
		var note_content = getProductProviderNote( product_provider_id );
		var note = product_provider_note.replace( '__note__', note_content );
		note = note.replace( '__product_provider_id__', product_provider_id );
		note = note.replace( '__product_provider_clue__', product_provider_clue );

		$( '.emergent_content' ).html( note );
		$( '.emergent' ).css( 'display', 'block' );
	}
	function getProductProviderNote( product_provider_id ){
		if( current_product_provider_note[product_provider_id] != null ){
			return current_product_provider_note[product_provider_id];
		}else{
			return '';
		}
	}
	function set_product_provider_note( product_provider_id ){
		current_product_provider_note[product_provider_id] = $( '#note_input' ).val();
		localStorage.setItem( 'current_product_provider_note', current_product_provider_note );
		close_emergent();
	}

	function buildProductRowDetail( position ){
		resp = `<table class="table table-bordered">`;//
		for( var row in current_products[position].product_providers_info ){
			resp += `<tr>
				<td width="200px" class="text-start">
					${current_products[position].product_providers_info[row]['provider_clue']}
					<button 
						class="btn"
						onclick="show_movements_details( ${current_products[position].product_providers_info[row].product_provider_id} );"
						placeholder="ver movimientos de almacén"
					>
						<i class="icon-eye"></i>
					</button>
					<button
						class="btn"
						onclick="show_product_provider_note( ${current_products[position].product_providers_info[row].product_provider_id}, 
							'${current_products[position].product_providers_info[row].provider_clue}' );"
						placeholder="notas del proveedor producto"
					>
						<i class="icon-sticky-note"></i>
					</button>
				</td>`;


			for( var row2 in current_products[position].product_providers_info[row].pp_inventories ){
				var local_class = "",
					online_class = "";
				if( current_products[position].product_providers_info[row].pp_inventories[row2]['local_calculated_inventory'] != current_products[position].product_providers_info[row].pp_inventories[row2]['local_resumen_inventory'] ){
					local_class += "color_orange";
				}
				if( current_products[position].product_providers_info[row].pp_inventories[row2]['online_calculated_inventory'] != current_products[position].product_providers_info[row].pp_inventories[row2]['online_resumen_inventory'] ){
					online_class += "color_orange";
				}
				resp += `<td class="text-end ${local_class}">${current_products[position].product_providers_info[row].pp_inventories[row2]['local_calculated_inventory']}</td>
				<td class="text-end calculate_row no_visible ${local_class}">${current_products[position].product_providers_info[row].pp_inventories[row2]['local_resumen_inventory']}</td>
				<td class="text-end ${online_class}">${current_products[position].product_providers_info[row].pp_inventories[row2]['online_calculated_inventory']}</td>
				<td class="text-end calculate_row no_visible ${online_class}">${current_products[position].product_providers_info[row].pp_inventories[row2]['online_resumen_inventory']}</td>`;
			}
			resp += `<td class="text-center" width="50px">
			<button class="btn"
						onclick="show_product_provider_barcodes( ${current_products[position].product_providers_info[row].product_provider_id} );"
					>
						<i class="icon-barcode"></i>
					</button>
			</td>
			</tr>`;
		}
		resp += `</table>`;
		return resp;
	}

	function show_product_provider_barcodes( product_provider_id ){
		var url = "ajax/db.php?fl_db=getProductProviderDetail&product_provider_id=" + product_provider_id;
		var response = ajaxR( url );
		response = JSON.parse( response );
		console.log( response );
		//buildProductProvider( response );
		var resp = `<div class="row">
						<h3 class="text-center">${response.product_description}
							<br>
							MODELO : <b>${response.provider_clue}</b>
						</h3>
						<br>
						<div class="row">
							<h4>CB Pieza</h4>
							<div class="col-4 text-center">${response.piece_barcode_1}</div>
							<div class="col-4 text-center">${response.piece_barcode_2}</div>
							<div class="col-4 text-center">${response.piece_barcode_3}</div>
						<div>
						<br>
						<div class="row">
							<h4>CB Paquete</h4>
							<div class="col-6 text-center">${response.pack_barcode_1}</div>
							<div class="col-6 text-center">${response.pack_barcode_2}</div>
						<div>
						<br>
						<div class="row">
							<h4>CB Caja</h4>
							<div class="col-4 text-center">${response.box_barcode_1}</div>
							<div class="col-4 text-center">${response.box_barcode_2}</div>
							<div class="col-4">
								<h5>Codigos de validacion de caja</h5>
								${response.boxes_validation_barcodes}
							</div>
						<div>
					</div>`;
		show_emergent( resp, true );
	}

	function buildTableHeader(){
		var url = "ajax/db.php?fl_db=getDinamicHeader&warehouses=" + current_warehouses;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}else{
			$( '#products_header' ).html( response[1] );
		}
	}

	function show_product_detail( position ){
		if( $( '#product_row_detail_' + position ).hasClass( 'no_visible' ) ){
			$( '#product_row_detail_' + position ).removeClass( 'no_visible' );
			$( '#icon_row_' + position ).removeClass( 'icon-down-open' );
			$( '#icon_row_' + position ).addClass( 'icon-up-open' );
		}else{
			$( '#product_row_detail_' + position ).addClass( 'no_visible' );
			$( '#icon_row_' + position ).removeClass( 'icon-up-open' );
			$( '#icon_row_' + position ).addClass( 'icon-down-open' );
		}
	}

	function deleteProductRow( product_id ){

	}
	function getProductProviderDetail( product_provider_id ){
		
	}
	function setCurrentTime(){

	}

	function setTestType(){
		var val = $( '#test_type_combo' ).val();
		if( val == 0 ){
			alert( "Es necesario que elijas un tipo de prueba valida para continuar !" );
			return false;
		}
		localStorage.setItem( 'current_date_time', $( '#date_time_input' ).val() );
		$( '#date_time_input' ).val( $( '#date_time_input_tmp' ).val() );
		current_test_type = val;
		localStorage.setItem( 'current_test_type', current_test_type );
		getDateTime();
		close_emergent();
	}
//buscador de transferencias
	function seekTransfer( obj, e ){
		var txt = $( obj ).val().trim();
		var url = "ajax/db.php?fl_db=seekTransfer&key=" + txt;
		var response = ajaxR( url );
		$( '.transfer_seeker_response' ).html( response );
	}
	function setTransfers( type, id, load_complete = true ){
		if( load_complete == true ){
			setTestType();
		}
		/*current_test_type = $( '#test_type_combo' ).val();
		localStorage.setItem( 'current_test_type', current_test_type );*/
		
		var url = 'ajax/db.php?fl_db=getTransfers&type=' + type;
		url += '&value=' + id;

		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}else{
			//alert(  response[5] );
			current_warehouses = response[5].split( ',' );
			localStorage.setItem( 'current_warehouses', response[5] );

			current_transfers_folios = response[4].split(',');
			localStorage.setItem( 'current_transfers_folios', response[4] );

			current_transfers = response[3].split(',');
			localStorage.setItem( 'current_transfers', response[3] );
			
			current_transfers_validation_block = response[2].split(',');
			localStorage.setItem( 'current_transfers_validation_block', response[2] );
			
			current_transfers_reception_block = response[1].split(',');
			localStorage.setItem( 'current_transfers_reception_block', response[1] );
			
			close_emergent();
			buildTransferRows( current_transfers_folios );
			$( '#warehouse_' + current_warehouses[0] ).attr( 'checked', true );
			$( '#warehouse_' + current_warehouses[1] ).attr( 'checked', true );
			$( '#block_warehouse_btn_container' ).addClass( 'no_visible' );//oculta el boton para no poder editarlo
			block_warehouses_checks( true );
			show_and_hidde_warehouse_container();
		}
		if( load_complete == true ){
			//alert();
			getDateTime();
		}
		getTransfersProducts();
		setTimeout( function (){
			$( '#block_warehouse_btn_container' ).addClass( 'no_visible' );
		}, 300 );
	}

	function getTransfersProducts(){
	//	alert( "getTransfersProducts" );
		current_products = new Array();
		localStorage.setItem( 'current_products', '' );
		var url = "ajax/db.php?fl_db=getTransfersProducts&reception_id=" + current_transfers_reception_block;
		url += "&validation_id=" + current_transfers_validation_block;
		url += "&transfer_ids=" + current_transfers;
		//alert( url );
		var response = ajaxR( url );
		var products = JSON.parse( response );
		for (var product in products ){
			//alert();
			insertProductRow( products[product].product_id );
		}
		//console.log( response );
		//alert( response );

	}

	function buildTransferRows(){
		var tmp = current_transfers_folios.join( '\n' );
		$( '#transfers_folios' ).val( tmp );
		tmp = current_transfers_validation_block.join( '\n' );
		$( '#transfers_validations_ids' ).val( tmp );

		tmp = current_transfers_reception_block.join( '\n' );
		$( '#transfers_receptions_ids' ).val( tmp );
	}
//obtener fecha y hora actual
	function getDateTime( just_return_data = false ){
		var date_time = "";
		var today = new Date();
		var date_time = today.toLocaleDateString('en-US');
		var hour = today.toLocaleTimeString('en-US');

		if( hour.includes( 'PM' ) ){
			var hour_tmp = hour.split( ':' );
			hour = parseInt( hour_tmp[0] ) + 12 ;
			hour += ":" + hour_tmp[1] + ":" + hour_tmp[2];
		}

		hour = hour.replace( 'PM', '' );
		hour = hour.replace( 'AM', '' );
		hour= hour.trim();

		date_tmp = date_time.split( '/' );
		date_time =  "";
		date_time += date_tmp[2] + '-';
		date_time += ( date_tmp[0] <= 9 ? "0" + date_tmp[0]  : date_tmp[0] ) + '-';
		date_time += date_tmp[1];
		//date_time = date_time + " " + hour;
		/*var url = "ajax/db.php?fl_db=getCurrentTime";
		var response = ajaxR( url ).split('|');
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}
		var date = JSON.parse( response[1] );
		var date_time = date.current_date;
		var date_time = date.current_time;*/
		if( just_return_data == 'return' ){
			var tmp = new Array();
			tmp.push( date_time );
			tmp.push( hour );
			return tmp;
		}else{
			current_initial_date = date_time;
			localStorage.setItem( 'current_initial_date', current_initial_date );
			current_initial_time = hour;
			localStorage.setItem( 'current_initial_time', current_initial_time );

		}
	}

	function updateProductInformation(){
		//alert( current_test_type );
		var url = "ajax/db.php?fl_db=updateData&test_type=" + current_test_type;
		if( current_test_type == 'transfer' ){
			if( $( '#transfers_receptions_ids' ).val().trim() != '' && $( '#transfers_receptions_ids' ).val().trim() > 0 ){
				url += "&reception_block_id=" + $( '#transfers_receptions_ids' ).val().trim();
			}else if( $( '#transfers_validations_ids' ).val().trim() != '' ){
				url += "&validation_block_id=" + $( '#transfers_validations_ids' ).val().trim().replaceAll( '\n', ',' );
			}else if( $( '#transfers_folios' ).val().trim() != '' ){//
				url += "&current_transfers=" + current_transfers;
			}else{ 
				alert( "Error : \nninguna transferencia esta activa en esta prueba!" );
			}
		}else if( current_test_type == 'datetime' ){
			//alert( 'here');
			var tmp = localStorage.getItem( 'current_products' ).split( ',' );
			console.log( tmp );
			for( var i = 0; i < tmp.length; i ++ ){
				console.log( "Producto : " + tmp[i] );
			}
			return false;
		}
		//if( current_transfers == '' ){
		//	url += "&current_products=";
		//}
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
			return false;
		}
		if( current_test_type == 'transfer' ){
		//actualiza variables de transferencias

		}else if( current_test_type == 'datetime' ){
		//actualiza productos

		}
	}

	function reset_test(){
		if( ! confirm( "Realmente deseas resetear esta prueba?\nLa informacion guardada sera eliminada!" ) ){
			return false;
		}
		localStorage.removeItem( 'current_test_type' );
		localStorage.removeItem( 'current_warehouses' );
		localStorage.removeItem( 'current_products' );
		localStorage.removeItem( 'current_initial_date' );
		localStorage.removeItem( 'current_initial_time' );
	//storage de transferencias
		localStorage.removeItem( 'current_transfers_folios' );
		localStorage.removeItem( 'current_transfers' );
		localStorage.removeItem( 'current_transfers_validation_block' );
		localStorage.removeItem( 'current_transfers_reception_block' );
	//productos eliminados
		localStorage.removeItem( 'deletedProducts' );
	//notas por proveedor producto
		localStorage.removeItem( 'current_product_provider_note' );

	//recarga pagina
		location.reload();
	}

	function show_and_hidde_trash_container( show = false ){
		if( show == true ){
			$( "#trash_container" ).removeClass( 'no_visible' );
			$( "#trash_btn" ).attr( 'onclick', 'show_and_hidde_trash_container( false )' );
		}else{
			$( "#trash_container" ).addClass( 'no_visible' );
			$( "#trash_btn" ).attr( 'onclick', 'show_and_hidde_trash_container( true )' );
		}
	}
//manda a papelera el producto
	function removeProduct( obj, product_id, position ){
		localStorage.setItem( 'deletedProducts', ( localStorage.getItem( 'deletedProducts' ) ? localStorage.getItem( 'deletedProducts' )  + ',' : '' )  + product_id );
		$( obj ).parent( 'td' ).parent( 'tr' ).remove();
		$( '#product_row_detail_' + position ).remove();
		buildTrashProducts();
	}
//saca el producto de la papelera
	function restartProduct( obj, product_id ){
	//elimina el producto de los productos eliminados
		var tmp = localStorage.getItem( 'deletedProducts' ).split( ',' );
		var aux = "";
		for( var i = 0; i < tmp.length; i ++ ){
			if( product_id != tmp[i] ){
				aux += ( aux == ''  ? "" : "," );
				aux += tmp[i];
			}
		} 
		localStorage.setItem( 'deletedProducts', aux );
		$( obj ).parent( 'div' ).parent( 'div' ).remove();
		//buildTrashProducts();
	}
//elimina el producto de la papelera
	function deleteProductTrash( product_id ){

	}

	function buildTrashProducts(){
		var resp = '';
		if( localStorage.getItem( 'deletedProducts' ) == null ){
			return false;
		}
		var products = localStorage.getItem( 'deletedProducts' ).split( ',' );		
		if( products == '' ){
			return false;
		}
		var url = "ajax/db.php?fl_db=getProductsToRemove&products_ids=" + products;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
			return false;
		} 
		products = JSON.parse( response[1] );
		for( var product in products ){
			resp += `<div 
				class="row trash_item">
					<div 
						class="col-8"
						onclick=""
					>${products[product].product_name}</div>
					<div
						class="col-2"
					>
						<button
							class="btn btn-success"
							onclick="restartProduct( this, ${products[product].product_id} )"
						>
							<i class="icon-spin3"></i>
						</button>
					</div>
					<div
						class="col-2"
					>
						<button
							class="btn btn-danger"
							onclick="deleteProductTrash( ${products[product].product_id} )"
						>
							<i class="icon-minus-circled"></i>
						</button>
					</div>
				</div>`;
		}
		$( '#trash_container' ).html( '' );
		$( '#trash_container' ).html( resp );  
	}

//llamadas asincronas
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