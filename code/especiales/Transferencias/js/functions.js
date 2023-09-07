
	var current_product_to_detail = null;
	var current_counter = null;

	function show_transfer_product_detail( product_id, counter ){
		current_product_to_detail = product_id;
		current_counter = counter;
	//carga el detalle del producto
		$( '.emergent_content' ).html( buildProductProvider() );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function buildProductProvider(){
		var resp = '<table class="table table-striped table-bordered">';
		resp += '<thead><tr>';
			resp += '<th class="no_visible">id proveedor</th>';
			resp += '<th class="">PROVEEDOR</th>';
			resp += '<th class="no_visible">id proveedor producto</th>';
			resp += '<th class="">INVENTARIO ORIGEN</th>';
			resp += '<th class="">MODELO</th>';
			resp += '<th class="">PIEZAS POR CAJA</th>';
			resp += '<th class="">PIEZAS POR PAQUETE</th>';
			resp += '<th class="">CAJAS</th>';
			resp += '<th class="">PAQUETES</th>';
			resp += '<th class="">PIEZAS</th>';
		resp += '</tr></thead>';
		resp += '<tbody id="product_provider_detail">';
		var productsProviders = $( '#13_' + current_counter ).html().trim().split( '|~|' );
		for ( var i = 0; i < productsProviders.length; i++ ) {
			var productProvider = productsProviders[i].split( '~' );
			resp += '<tr>';
				resp += '<td class="no_visible">' + productProvider[0] + '</td>';
				resp += '<td class="">' + productProvider[1] + '</td>';
				resp += '<td class="no_visible">' + productProvider[2] + '</td>';
				resp += '<td class="text-right">' + productProvider[3] + '</td>';
				resp += '<td class="text-right">' + productProvider[4] + '</td>';
				resp += '<td class="text-right">' + productProvider[5] + '</td>';
				resp += '<td class="text-right">' + productProvider[6] + '</td>';
				resp += '<td class="text-right" id="pp_1_' + i + '" onclick="edit_ceil( 1, ' + i + ' )">' + productProvider[8] + '</td>';
				resp += '<td class="text-right" id="pp_2_' + i + '" onclick="edit_ceil( 2, ' + i + ' )">' + productProvider[9] + '</td>';
				resp += '<td class="text-right" id="pp_3_' + i + '" onclick="edit_ceil( 3, ' + i + ' )">' + productProvider[10] + '</td>';
			resp += '</tr>';
		}
		resp += '</tbody>';
		resp += '</table>';
		resp += '<br/><br/>';
		resp += '<div class="row">';
			resp += '<div class="col-2"></div>';
			resp += '<div class="col-4">';
				resp += '<button type="button" class="btn btn-info form-control" onclick="close_emergent();">';
					resp += 'Aceptar';
				resp += '</button>';
			resp += '</div>';
			resp += '<div class="col-4">';
				resp += '<button type="button" class="btn btn-success form-control" onclick="changeTransferDetail( ' + current_counter + ' );">';
					resp += 'Guardar';
				resp += '</button>';
			resp += '</div>';
		resp += '</div>';
		return resp;
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}
	
	var tmpCeil = '<input type="number" id="p_p_tmp" class="form-control" onblur="desedit_ceil();">';
	var tmpVal, current_counter1 = null, current_counter2 = null;
	function edit_ceil( counter1, counter2 ){
		if( current_counter1 != null ){
			return false;	
		}
		current_counter1 = counter1;
		current_counter2 = counter2;
		tmpVal = $( '#pp_' + counter1 + '_' + counter2 ).html().trim();
		$( '#pp_' + counter1 + '_' + counter2 ).html( tmpCeil );
		$( '#p_p_tmp' ).val( tmpVal );
		$( '#p_p_tmp' ).focus();
		$( '#p_p_tmp' ).select();
	}

	function desedit_ceil(){
		$( '#pp_' + current_counter1 + '_' + current_counter2 ).html( $( '#p_p_tmp' ).val() );
		current_counter1 = null;
		current_counter2 = null;	
	}

	function changeTransferDetail( counter = null ){
		$( '#13_' + current_counter ).html( getTransferProductProvider() );
		close_emergent();

	}
	function getTransferProductProvider(){
		var resp = '';
		$( '#product_provider_detail tr' ).each( function ( index ){
			if( index > 0 ){
				resp += '|~|';
			}
			$(this).children("td").each(function (index2) {
				if( index2 > 0 && index2 != 7 ){
					resp += '~';
				}else if( index2 == 7 ){

					resp += '~~';
				}
				resp += $( this ).html().trim();
			});
		});
		//alert( resp );
		return resp;
	}