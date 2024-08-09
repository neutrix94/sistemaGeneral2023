//declaracion de variables
	var provider = 0;
	var invoices_number = 0;
	var invoices = new Array();
	var invoices_detail = new Array();
	var parts = new Array();
	var series = new Array();
	var first_steep_validate = false;
	var global_block_id = '';
	var global_meassures_home_path ='../../../';
	var global_save_meassure_type = 0;

	var global_is_the_same_product_provider = 0;
	
	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}
	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}
	function getGlobalConfig(){
		var url = "ajax/db.php?fl=getSystemConfig";	
		var response = ajaxR( url );
		$( '#dont_request_reception_measures' ).val( response );
	}

	function insertReceptionBlock(){
		var url = 'ajax/db.php?fl=setBlockSession';
		var response = ajaxR( url );
		var aux = response.split( '|' );
		if( aux[0] == 'ok' ){
			localStorage.setItem( 'block_id', aux[1] );
		}else{
			alert( "Error : " + response );
		}
	}
//mostrar / ocultar vistas del menú
	function show_view( obj, view ){
		if( first_steep_validate == false && view == '.invoices_products' ){
			alert( "Primero capture las remisiones!" );
			return false;
		}
		if( series.length == 0 && view == '.invoices_lists' ){
			alert( "Primero especifique las remisiones en la sección de remisiones para continuar!" );
			return false;
		}
	//limpia formulario de producto
		if( view == '.invoices_products' ){
			clean_product_form();
			desactivate_product_form();
			clean_location_form( 'source' );
			clean_messages_descriptions();
		}
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );
	}
//redireccionamientos
	function redirect( type ){
		switch ( type ){
			case 'home' : 
				if( confirm( "Salir sin Guardar?" ) ){
					localStorage.removeItem( 'block_id' );
					location.href="../../../";
				}
			break;

		}
	}

	function seeker_provider( show_all = 0 ){
		if( show_all == 1 ){
			$( '#invoice_provider' ).val( '' );
			$( '#invoice_provider_seeker' ).val( '' );
			$( '#invoice_provider_seeker' ).removeAttr( 'disabled' );
		}
		var url = 'ajax/db.php?fl=seekProvider&txt=' + $( '#invoice_provider_seeker' ).val();
		//alert( url );
		var response = ajaxR( url );
		$( '#provider_seeker_response' ).html( response );
		$( '#provider_seeker_response' ).css( 'display', 'block' );

	}

	function setProvider( provider_id, provider_name ){
		$( '#invoice_provider' ).val( provider_id );
		$( '#invoice_provider_seeker' ).val( provider_name );
		$( '#invoice_provider_seeker' ).attr( 'disabled' , true );
		$( '#provider_seeker_response' ).css( 'display', 'none' );
		$( '#btn_show_all_providers' ).attr( 'disabled', true );
		provider = provider_id;
		$( '#invoices_initial_counter' ).focus();
	}

//establecer proveedor / numero de remisiones por recibir
	function setInitialConfig(){
		provider = $( '#invoice_provider' ).val();
		if( provider == 0 ){
			alert( "Escoja un proveedor para continuar");
			$( '#invoice_provider' ).focus();
			return false;
		}
		invoices_number = $( '#invoices_initial_counter' ).val();
		if( invoices_number <= 0 ){
			alert( "Ingrese el numero de remisiones a recibir para continuar");
			$( '#invoices_initial_counter' ).select();
			return false;
		}
		$( '#invoice_provider' ).attr( 'disabled', 'true' );
		$( '#invoices_initial_counter' ).attr( 'disabled', 'true' );
		$( '#invoices_initial_config_confirm' ).css( 'display', 'none' );
		$( '#invoices_initial_config_edit' ).css( 'display', 'block' );
	//habilita campos de busqueda para agregar pedido
		$( '#invoices_seeker' ).removeAttr( 'disabled' );
		$( '#invoice_folio' ).removeAttr( 'disabled' );
		$( '#invoice_parts' ).removeAttr( 'disabled' );
		$( '#invoice_button_add' ).removeAttr( 'disabled' );

		$( '#btn_show_all_providers' ).attr( 'disabled', true );

		validate_invoices_number();
	}

//editar proveedor / numero de remisiones por recibir
	function editInitialConfig(){
		$( '#invoice_provider' ).removeAttr( 'disabled' );
		$( '#invoices_initial_counter' ).removeAttr( 'disabled' );
		$( '#invoices_initial_config_confirm' ).css( 'display', 'block' );
		$( '#invoices_initial_config_edit' ).css( 'display', 'none' );
	//habilita campos de busqueda para agregar pedido
		$( '#invoices_seeker' ).attr( 'disabled', 'true' );
		$( '#invoice_folio' ).attr( 'disabled', 'true' );
		$( '#invoice_parts' ).attr( 'disabled', 'true' );
		$( '#invoice_button_add' ).attr( 'disabled', 'true' );

		$( '#btn_show_all_providers' ).removeAttr( 'disabled' );

	}
//validar que el folio de la remisión no exista
	function validateInvoiceNoExists( obj ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'validateInvoiceNoExists', to_check : $( obj ).val().trim() },
			success : function ( dat ){
				if ( dat != 'ok' ){
					alert( dat );
					$( '#invoice_button_add' ).attr( 'disabled', 'true' );
					$( obj ).select();
					$( obj ).val( '' );
					return false;
				}
				$( '#invoice_button_add' ).removeAttr( 'disabled' );
			}
		});
		
	}

//agregar remisiones
	function addInvoice(){
	//valida que el numero de partidas de la remisión sea valido
		if ( $( '#invoice_parts' ).val() <= 0 ){
			alert( "El nuimero de partidas tiene que ser mayor a 0" );
			$( '#invoice_parts' ).select();
			return false;
		}
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'insertInvoice', 
					invoice_folio : $( '#invoice_folio' ).val(),
					parts_number : $( '#invoice_parts' ).val(),
					provider_id : provider 
			},
			success : function ( dat ){
				console.log( dat );
			//limipa los inputs
				$( '#invoice_folio' ).val( '' );
				$( '#invoice_parts' ).val( '' );
			//agrega el renglon al arreglo
				build_invoice_row( dat.split( '~' ) );
			}
		});
	}

	function seek_invoices( obj ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'seekInvoices', 
					provider_id : provider,
					key : $( obj ).val(),
					current_series : series 
			},
			success : function ( dat ){
				$( '.search_results_invoice' ).html( dat );
				$( '.search_results_invoice' ).css( 'display', 'block');
			}
		});

	}
	function setInvoiceExistent( dat ){
		var row = dat.split( '~' );
		$( '.search_results_invoice' ).html( '' );
		$( '.search_results_invoice' ).css( 'display', 'none');
		$( '#invoices_seeker' ).val( '' );
		build_invoice_row( row );
		build_invoices_lists();
		build_invoices_lists_finish();
	}

	function build_invoice_row ( row ){
		var resp = '';
		resp += '<tr id="row_' + row[0] + '">'
				+ '<td class="no_visible">' + row[0] + '</td>'
				+ '<td>' + row[2] + '</td>'
				+ '<td>' + row[1] + '</td>'
				+ '<td id="' + row[2] + '_parts_number">' + row[3] + '</td>'
				+ '<td><button class="btn padding_0" onclick="remove_invoice( ' + row[0] + ' );"><i class="icon-cancel-alt-filled"></i></button></td>'
			+ '</tr>'; 
		$( '#invoice_to_receive' ).append( resp );
		invoices[ row[2] ] = row ;
		invoices[ row[2] ]['invoice_detail'] = new Array();
		series.push( row[2] );
		validate_invoices_number();
		build_series_combo();
		first_steep_validate = true;
		//console.log( invoices );
	}

	function remove_invoice( invoice_id ){
		var url = 'ajax/db.php?fl=validateRemoveInvoice&pk=' + invoice_id;
		url += '&block_id=' + global_block_id;
		//alert(url); return false;
		var response = ajaxR( url );
		var aux = response.split( '|' );
		$( '.emergent_content' ).html( ( aux[0] == 'ok' ? aux[1] : response ) );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).focus();
		if( aux[0] == 'ok' ){
			$( '#row_' + invoice_id ).remove();
		}
	}

	function build_series_combo(){
		var combo = '<select id="product_serie" onchange="build_series_parts_combo( this )" class="form-control" disabled>';
		combo += '<option value="0">Seleccionar</option>';
		for( var i = 0; i < series.length; i++ ){
			combo += '<option value="' + series[i] + '">' + series[i] + '</option>';
		}
		combo += '</select>';
		$( '.product_serie' ).html( combo );
	}

	function build_series_parts_combo( serie ){
		$( '#product_part_number' ).empty();
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'getInvoiceParts',
					reference : $( serie ).val()
			},
			success : function ( dat ){
				$( '#product_part_number' ).append( dat );
				$( '#product_part_number' ).removeAttr( 'disabled' );
				$( '#btn_parts_edition' ).removeAttr( 'disabled' );

				
			global_is_the_same_product_provider = validate_is_new_row();
				setTimeout( function(){
					clean_location_form( 'source' );
					if( global_is_the_same_product_provider == 1 ){
						//alert( 1 );
						getProductLocationOptions( $( '#product_provider' ).val(), '_source' );
					}else{
						//alert( 2 );
						getProductLocationOptions( null, '_source' );
					}
				}, 100 );
				//carga el formulario de ubicación

			}
		});
	}	

	function validate_first_part(){
		var current_invoices_number = $( '#invoice_to_receive tr').length;
		//alert( current_invoices_number );
		if( current_invoices_number <= 0 ){
			alert( "Primero capture remisiones por recibir!" );
			return false;
		}
		if( current_invoices_number != invoices_number ){
			alert( "Hay diferencias entre el numero de remisiones a recibir y las recepciones capturadas" );
			return false;
		}
		first_steep_validate = true;
		$( '.source' ).click();
	}

	function validate_invoices_number(){
		if( $( '#invoice_to_receive tr' ).length == invoices_number ){
			$( '#invoices_seeker' ).attr( 'disabled', 'true' );
			$( '#invoice_folio' ).attr( 'disabled', 'true' );
			$( '#invoice_parts' ).attr( 'disabled', 'true' );
			$( '#invoice_button_add' ).attr( 'disabled', 'true' );
		}else{
			$( '#invoices_seeker' ).removeAttr( 'disabled' );
			$( '#invoice_folio' ).removeAttr( 'disabled' );
			$( '#invoice_parts' ).removeAttr( 'disabled' );
			$( '#invoice_button_add' ).removeAttr( 'disabled' );
		}
	}

/*Sección de busqueda de productos*/
	function seek_product( e, obj ){
		var key = e.keyCode;
		var txt = $( obj ).val().trim();
		var is_scanner = ( key == 13 || e == 'enter' ? 1 : 0 );
		if ( txt.length <= 2 ){
			$( '.productResBusc' ).html( '' );
			$( '.productResBusc' ).css( 'display', 'none');
			//alert( 'here' );
			return false;
		}
		if( is_scanner == 1 ){
		//omite codigo de barras si es el caso
			var tmp_txt = txt.split( ' ' );
			if( tmp_txt.length == 4 ){
				/*if( $( '#skip_unique_barcodes' ).val().trim() == 0 ){
					global_tmp_unique_barcode = txt;
				}*/
				txt = '';
				for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
					txt += ( txt != '' ? ' ' : '' );
					txt += tmp_txt[i];
				}
			}
			//alert('is_scanner');
		}//alert( txt );
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'seekProduct', 
					provider_id : provider,
					key : txt,
					scanner : is_scanner  
			},
			success : function ( dat ){
				$( '.productResBusc' ).html( dat );
				$( '.productResBusc' ).css( 'display', 'block');
			//verifica si es una sola opcion para seleccionar la primera opción
				setTimeout(function () {
					if( document.getElementById( 'seeker_product_response_0' )
						&& ! document.getElementById( 'seeker_product_response_1' )
						&& is_scanner == 1 ){
						$( '#seeker_product_response_0' ).click();
					}
				}, 100 );
			}
		});
	}

	function setProduct( product_id, product_name, product_model, product_provider_id, product_barcode,
				product_pack_cluces, product_pack_cluces_barcode, product_box, product_box_barcode, 
				product_location, is_new_tmp = 0, validated = false ){
		$( '#is_new_product_row' ).val( is_new_tmp );
	//si solo es cambio de producto
		if( is_row_edition == 1 ){
			$( '#product_id' ).val( product_id );
			$( '.product_name' ).html( product_name );
			$( '#product_provider' ).val( product_provider_id );
			/*$( '.product_model' ).html( product_model );*/
			$( '#product_seeker' ).attr( 'disabled', 'true' );
			is_row_edition = 1;//marca edicion de registro
			
			$( '#product_seeker' ).val( '' );
			$( '.productResBusc' ).html( '' );
			$( '.productResBusc' ).css( 'display', 'none' );
			return true;
		}else{
/*implementacion Oscar 2023*/
			if( ! validated && product_provider_id != '' && is_new_tmp == 0 ){
				validate_if_product_exists( product_id, product_name, product_model, product_provider_id, product_barcode,
				product_pack_cluces, product_pack_cluces_barcode, product_box, product_box_barcode, 
				product_location, is_new_tmp, true );
				return false;
			}
/*fin de cambio Oscar 2023*/				
		}
	//
		$( '#product_reset_btn' ).attr( 'onclick', 'desactivate_product_form();clean_product_form();clean_location_form( \'source\' );clean_messages_descriptions();' );
	//asigna los valores en pantalla / ocultos
		$( '#product_id' ).val( product_id );
		$( '.product_name' ).html( product_name );
		$( '.product_model' ).html( product_model );
		$( '#product_model' ).val( product_model );
		$( '#db_product_model' ).val( product_model );
		$( '#product_provider' ).val( product_provider_id );

		var array_aux = product_barcode.split( '~' );
		$( '#db_piece_barcode' ).val( array_aux[0] );
		$( '#db_piece_barcode_2' ).val( array_aux[1] );
		$( '#db_piece_barcode_3' ).val( array_aux[2] );
		
		var array_aux = product_pack_cluces_barcode.split( '~' );
		$( '#db_pieces_per_pack' ).val( product_pack_cluces );
		$( '#db_pack_barcode' ).val( array_aux[0] );
		$( '#db_pack_barcode_2' ).val( array_aux[1] );

		var array_aux = product_box_barcode.split( '~' );
		$( '#db_pieces_per_box' ).val( product_box );
		$( '#db_box_barcode' ).val( array_aux[0] );
		$( '#db_box_barcode_2' ).val( array_aux[1] );

	//oculta opciones del buscador
		$( '.productResBusc' ).html( '' );
		$( '.productResBusc' ).css( 'display', 'none');
	//habilita el formulario
		activate_product_form();
		$( '#product_seeker' ).val( '' );
		//alert( product_location );
		if( product_location != '' ){
			$( "#location_status_source option[value=2]" ).text( "Ubicación actual : " + product_location );
		}else{
			$( "#location_status_source option[value=2]" ).text( "No tiene ubicación" );
		}
		getPiecesEmergent();
	}

	function validate_if_product_exists( product_id, product_name, product_model, product_provider_id, product_barcode,
				product_pack_cluces, product_pack_cluces_barcode, product_box, product_box_barcode, 
				product_location, is_new_tmp, validated ){
		var url = "ajax/db.php?fl=validate_if_product_exists&product_id=" + product_id;
		url += "&product_name=" + product_name + "&product_model=" + product_model;
		url += "&product_provider_id=" + product_provider_id + "&product_barcode=" + product_barcode;
		url += "&product_pack_cluces=" + product_pack_cluces + "&product_pack_cluces_barcode=" + product_pack_cluces_barcode;
		url += "&product_box=" + product_box + "&product_box_barcode=" + product_box_barcode;
		url += "product_location="+product_location+"&series=" + series;
//alert( url );
		var response = ajaxR( url );
//alert( response );
		if( response != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		}else{
			setProduct( product_id, product_name, product_model, product_provider_id, product_barcode,
				product_pack_cluces, product_pack_cluces_barcode, product_box, product_box_barcode, 
				product_location, is_new_tmp, true );
		}
	}

	function setProductExistentProduct(){
		var historic_id = null;
		$( '#historic_matched_list' ).each( function ( index ){
			if( $( '#historic_radio_' + index ).prop( 'checked' ) == true ){
				historic_id = $( '#historic_radio_' + index ).prop( 'value' );
				return true;
			}
		});
		if( historic_id == null ){
			alert( "Debes seleccionar una partida para editar!" );
			return false;
		}
		close_emergent();
		editDetail( historic_id );
		setTimeout( function(){
			$( '.productResBusc' ).html( '' );
			$( '.productResBusc' ).css( 'display', 'none' );
			$( '#product_seeker' ).val( '' );
		}, 100 );
	}

	function getPiecesEmergent(){
		var content = `<div class="row" style="padding : 20px;"><div class="col-12">
				<label for="">Piezas por Caja</label>
				<input type="number" id="pieces_per_box_tmp" onkeyup="make_initial_calculation();" class="form-control">
			</div>
			<div class="col-12">
				<label for="">Total Cajas</label>
				<input type="number" id="boxes_tmp_total" onkeyup="make_initial_calculation();" class="form-control">
			</div>
			<div class="col-12">
				<label for="">Total Piezas sueltas</label>
				<input type="number" id="pieces_tmp_total" onkeyup="make_initial_calculation();" class="form-control">
			</div>
			<div class="col-12">
				<label for="">Total en piezas</label>
				<input type="number" id="quantity_tmp_total" class="form-control" disabled>
			</div>
		</div>
		<div class="row">
			<div class="col-1"></div>
			<div class="col-5">
				<button
					class="btn btn-danger"
					onclick="desactivate_product_form();clean_product_form();clean_location_form( 'source' );clean_messages_descriptions();close_emergent();"
				>
					<i class="icon-cancel-circled">Cancelar y cerrar</i>
				</button>
			</div>
			<div class="col-5">
				<button
					class="btn btn-success"
					onclick="setTotalQuantitiesPerProduct();"
				>
					<i class="icon-ok-circle">Aceptar y continuar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function make_initial_calculation(){
		var pieces_per_box = ( parseInt( $( '#pieces_per_box_tmp' ).val() ) <= 0 || ( isNaN(parseInt( $( '#pieces_per_box_tmp' ).val()) ) ) ? 0 : parseInt( $( '#pieces_per_box_tmp' ).val() ) );
		var boxes_quantity = ( parseInt( $( '#boxes_tmp_total' ).val() ) <= 0 || ( isNaN(parseInt( $( '#boxes_tmp_total' ).val()) ) ) ? 0 : parseInt( $( '#boxes_tmp_total' ).val() ) );
		var pieces_quantity = ( parseInt( $( '#pieces_tmp_total' ).val() ) <= 0 || ( isNaN(parseInt( $( '#pieces_tmp_total' ).val()) ) ) ? 0 : parseInt( $( '#pieces_tmp_total' ).val() ) );
		var pieces_total = ( pieces_per_box * boxes_quantity ) + pieces_quantity;
		$( '#quantity_tmp_total' ).val( pieces_total );
	}

	function setTotalQuantitiesPerProduct(){
		var pieces_per_box,boxes_tmp_total, pieces_tmp_total, quantity_tmp_total;
		pieces_per_box = $( "#pieces_per_box_tmp" ).val();
		if( pieces_per_box == '' ){
			alert( "Las piezas por caja no pueden ir vacias, si este producto no tiene caja escribe 0 en piezas por caja!" );
			$( "#pieces_per_box_tmp" ).focus();
			return false;
		}
		boxes_tmp_total = $( "#boxes_tmp_total" ).val();
		if( boxes_tmp_total == '' ){
			alert( "Las cajas no pueden ir vacias, si no hay cajas escribe 0!" );
			$( "#boxes_tmp_total" ).focus();
			return false;
		}
		pieces_tmp_total = $( "#pieces_tmp_total" ).val();
		if( pieces_tmp_total == '' ){
			alert( "Las piezas no pueden ir vacias, si no hay piezas sueltas escribe 0!" );
			$( "#pieces_tmp_total" ).focus();
			return false;
		}
		quantity_tmp_total = $( "#quantity_tmp_total" ).val();
		if( quantity_tmp_total == '' || quantity_tmp_total <= 0 ){
			alert( "Las piezas totales no pueden ir vacias y deben de ser mayor a cero!" );
			$( "#quantity_tmp_total" ).focus();
			return false;
		}
		$( "#remission_pieces_per_box" ).val( pieces_per_box );
		$( "#remission_total_boxes" ).val( boxes_tmp_total );
		$( "#remission_total_pieces" ).val( pieces_tmp_total );
		$( "#remission_total_quantity" ).val( quantity_tmp_total );
		close_emergent();
	}

	function validateNoRepeatBarcode( obj ){
		var id = $( obj ).attr( 'id' );
		var value = $( obj ).val().trim();
		var msg = "", ask = "";
		if( value.length <= 0 ){
			return false;
		}
		if( id != 'piece_barcode' && value == $( '#piece_barcode' ).val()  && value != '' ){
			msg = "El código de barras ya existe en el código de pieza";
			ask = " Desea guardar este código?";
			if( confirm( msg + "\n" + ask ) ){
				$( '#' + id + '_notes' ).val( msg );
				$( '#' + id + '_null' ).prop( 'checked', true );
			}else{
				$( obj ).val('');
				$( '#' + id + '_notes' ).val( '' );
				return false;
			}
			
		}

		if( id != 'pack_barcode' && value == $( '#pack_barcode' ).val() && value != '' ){
			msg = "El código de barras ya existe en el código de paquete";
			ask = " Desea guardar este código?";
			if( confirm( msg + "\n" + ask ) ){
				$( '#' + id + '_notes' ).val( msg );
				$( '#' + id + '_null' ).prop( 'checked', true );
			}else{
				$( obj ).val('');
				$( '#' + id + '_notes' ).val( '' );
				return false;
			}
		}

		if( id != 'box_barcode' && value == $( '#box_barcode' ).val() && value != '' ){		
			msg = "El código de barras ya existe en el código de caja";
			ask = " Desea guardar este código?";
			if( confirm( msg + "\n" + ask ) ){
				$( '#' + id + '_notes' ).val( msg );
				$( '#' + id + '_null' ).prop( 'checked', true );
			}else{
				$( obj ).val('');
				$( '#' + id + '_notes' ).val( '' );
				return false;
			}
		}
		if( $( '#' + id ).val() == $( '#db_' + id ).val() ){//si es codigo de barras que ya esta registrado
			return false;
		}
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'seekBarcode',
					code : value,
					p_p : $( '#product_provider' ).val()
			},
			success : function ( dat ){
				if( dat != 'ok' ){
					if( confirm(dat) ){
						$( '#' + id + '_notes' ).val( dat );
						$( '#' + id + '_null' ).prop( 'checked', true );
					}else{
						$( obj ).val('');
						$( '#' + id + '_notes' ).val( '' );
						return false;
					}
					/*alert( dat );
					$( obj ).val('');
					return false;*/	
				}
			}
		});
	}

	function saveInvoiceDetail() {
		if( global_location_has_changed == 1 ){
			$( '#save_location_btn_source' ).click();
			//alert( "Hay cambios pendientes de guardar en la ubicación, guarde los cambios de la ubicación antes de continuar " );
			//return false;
		}
		setTimeout( function (){ console.log( "Gardando ubicación..." ); }, 300 );
		var is_new_row = 0;
		var product_model, piece_barcode, pieces_per_pack, pack_barcode, pieces_per_box, box_barcode;
		var box_recived, pieces_recived, product_part_number, product_serie, product_location_status, 
		product_location, row_detail_id, is_new_product, product_notes = "", measures_id;
		var remission_total_boxes, remission_total_pieces, remission_total_quantity;
  //valida que los campos no esten vacios y que los datos sean los mismos	
	//modelo del producto
		product_model = $( '#product_model' ).val().trim();
		if( product_model == '' && ! $( '#product_model_null' ).prop( 'checked' ) 
			&& $( '#db_product_model' ).val().trim() != '' 
		){
			alert( "El modelo del producto no puede ir vacío, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#product_model' ).focus();
			return false;
		}else if( $( '#db_product_model' ).val().trim() != product_model ){
			is_new_row = 1;
			product_notes += "El producto no tiene MODELO o lo tiene repetido";
		}
	//codigo de barras pieza
		piece_barcode = $( '#piece_barcode' ).val().trim();
		if( piece_barcode == '' && ! $( '#piece_barcode_null' ).prop( 'checked' )
			&& $( '#db_piece_barcode' ).val().trim() != ''  ){
			alert( "El código de barras no puede ir vacío, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#piece_barcode' ).focus();
			return false;
		}else if( $( '#db_piece_barcode' ).val().trim() != piece_barcode 
		&& $( '#db_piece_barcode_2' ).val().trim() != piece_barcode 
		&& $( '#db_piece_barcode_3' ).val().trim() != piece_barcode){
			is_new_row = 1;
		}
		product_notes += ( product_notes != '' ? "\n" : "" );
		product_notes += $( '#piece_barcode_notes' ).val().trim();
	//piezas por paquete
		pieces_per_pack = $( '#pieces_per_pack' ).val().trim();
		if( pieces_per_pack == '' && ! $( '#pieces_per_pack_null' ).prop( 'checked' )
			&& $( '#db_pieces_per_pack' ).val().trim() != '' ){
			alert( "Las piezas por paquete no pueden ir vacías, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#pieces_per_pack' ).focus();
			return false;
		}else if( $( '#db_pieces_per_pack' ).val().trim() != pieces_per_pack ){
			is_new_row = 1;
		}
		product_notes += ( product_notes != '' ? "\n" : "" );
		product_notes += $( '#pack_barcode_notes' ).val().trim();
	//codigo de barras paquete
		pack_barcode = $( '#pack_barcode' ).val().trim();
		if( pack_barcode == '' && ! $( '#pack_barcode_null' ).prop( 'checked' )
			&& $( '#db_pack_barcode' ).val().trim() != '' ){
			alert( "El código de barras del paquete no puede ir vacío, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#pack_barcode' ).focus();
			return false;
		}else if( $( '#db_pack_barcode' ).val().trim() != pack_barcode && $( '#db_pack_barcode_2' ).val().trim() != pack_barcode ){
			is_new_row = true;
		}
	//piezas por caja
		pieces_per_box = $( '#pieces_per_box' ).val().trim();
		if( pieces_per_box == '' && ! $( '#pieces_per_box_null' ).prop( 'checked' )
			&& $( '#db_pieces_per_box' ).val().trim() != '' ){
			alert( "Las piezas por caja no pueden ir vacías, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#pieces_per_box' ).focus();
			return false;
		}else if( $( '#db_pieces_per_box' ).val().trim() != pieces_per_box ){
			is_new_row = true;
		}
	//codigo de barras caja
		box_barcode = $( '#box_barcode' ).val().trim();
		if( box_barcode == '' && ! $( '#box_barcode_null' ).prop( 'checked' )
			&& $( '#db_box_barcode' ).val().trim() != '' ){
			alert( "El código de barras de la caja no puede ir vacío, si no tiene este dato marque la casilla 'No tiene'" );
			$( '#box_barcode' ).focus();
			return false;
		}else if( $( '#db_box_barcode' ).val().trim() != box_barcode && $( '#db_box_barcode_2' ).val().trim() != box_barcode ){
			is_new_row = true;
		}
		product_notes += ( product_notes != '' ? "\n" : "" );
		product_notes += $( '#box_barcode_notes' ).val().trim();
	//cajas recibidas
		box_recived = $( '#received_packs' ).val().trim();
		if( box_recived < 0 || box_recived == '' ){
			alert( "Las cajas recibidas no pueden ser menor a cero" );
			$( '#received_packs' ).val( 0 );
			$( '#received_packs' ).select();
			return false;
		}
	//piezas sueltas recibidas
		pieces_recived = $( '#received_pieces' ).val().trim();
		if( pieces_recived < 0 || pieces_recived == '' ){
			alert( "Las piezas recibidas no pueden ser menor a cero" );
			$( '#received_pieces' ).val( 0 );
			$( '#received_pieces' ).select();
			return false;
		}
		if( box_recived <= 0 && pieces_recived <= 0 ){
			alert( "No se puede recibir el producto en ceros, ponga una cantidad válida de cajas y/o piezas sueltas" );
			if( box_recived <= 0 ){
				$( '#received_packs' ).focus();
				$( '#received_packs' ).select();
			}else if( pieces_recived <= 0 ){
				$( '#received_pieces' ).focus();
				$( '#received_pieces' ).select();
			}
			return false;
		}
	/*Oscar 2023*/
		box_recived = parseInt( box_recived ) + parseInt( $( '#received_boxes_before' ).val() );
		pieces_recived = parseInt( pieces_recived ) + parseInt( $( '#received_pieces_before' ).val() );
	/**/
		//valida que las piezas por caja sean mayor a cero
		if( ( pieces_per_box <= 0 || pieces_per_box == '' ) && box_recived > 0 ){
			alert( "Si va a recibir cajas primero ingrese el número de Piezas por caja!" );
			$( '#pieces_per_box' ).focus();
			return false;
		}

	//partida del producto
		product_part_number = $( '#product_part_number' ).val().trim();
		if( product_part_number <= 0 || product_part_number == '' ){
			alert( "La partida del producto no pueden ser menor a uno" );
			$( '#product_part_number' ).select();
			return false;
		}
	//serie del producto
		product_serie = $( '#product_serie' ).val();
		if( product_serie == 0 ){
			alert( "La serie del producto no puede ir vacía!" );
			$( '#product_serie' ).select();
			return false;
		}
	//estatus de ubicación del producto
		product_location_status = $( '#location_status_source' ).val();
		if( product_location_status == 0 ){
			alert( "El estatus de ubicación del producto no puede ir vacío!" );
			$( '#location_status_source' ).focus();
			return false;
		}
	//ubicacion del producto
		product_location = $( '#product_location_source' ).val();
		/*if( product_location == 0 && product_location_status > 1 ){
			alert( "La ubicación del producto no puede ir vacía!" );
			$( '#product_location_source' ).focus();
			return false;
		}*/
	//id del detalle
		row_detail_id = $( '#reception_detail_id' ).val();

	//producto nuevo
		is_new_product = $( '#is_new_product_row' ).val();
	//id de medidas
		measures_id = $( '#measure_tmp_id' ).val();
		if( ( measures_id == 0 || measures_id == '' ) && $( '#dont_request_reception_measures' ).val().trim() == 1 ){
			alert( "Las medidas de la presentación son obligatorias" );
			show_measures_form();
			return false;
		}
	//implementacioon Oscar 2023 
		var pieces_per_box_inital = parseInt( $( '#remission_pieces_per_box' ).val() );
		if( pieces_per_box_inital != pieces_per_box ){	
			var content = `<div class="row">
				<h2 class="text-danger text-center">Las piezas por caja capturadas al inicio ( ${pieces_per_box_inital} ) son diferentes a las piezas por caja 
				capturadas en el formulario de piezas por caja ( ${pieces_per_box} )</h2>
				<h2 class="text-warning text-center">Escribe la cantidad correcta de piezas por caja : </h2>
				<br>
				<div class="col-2"></div>
				<div class="col-8 text-center">
					<br><br>
					<input type="number" class="form-control text-end" id="pieces_per_box_tmp_global">
					<br><br>
					<button
						type="button"
						class="btn btn-success form-control"
						onclick="setPiecesPerBoxGlobal();"
					>
						<i class="icon-ok-circle">Aceptar y continuar</i>
					</button>
					<br><br>
				</div>
			</div>`;
			$( '.emergent_content' ).html( content );
			$( '.emergent' ).css( 'display', 'block' );
			$( '#pieces_per_box_tmp_global' ).focus();
			return false;
		}
	//fin de cambio Oscar 2023
	//
		remission_total_boxes = $( '#remission_total_boxes' ).val();
		remission_total_pieces = $( '#remission_total_pieces' ).val();
		remission_total_quantity = $( '#remission_total_quantity' ).val();
		//alert( 'is_new_row : ' + is_new_row + ' , pp : ' + $( '#product_provider' ).val() );
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'saveInvoiceDetail',
				pk : $( '#product_id' ).val(),
				pp : (global_is_the_same_product_provider == 0 ? '' : $( '#product_provider' ).val() ),
				model : product_model,
				pz_bc : piece_barcode,
				pzs_x_pack : pieces_per_pack,
				pack_bc : pack_barcode,
				pzs_x_box : pieces_per_box,
				box_bc : box_barcode,
				box_rec : box_recived,
				pieces_rec : pieces_recived,
				product_p_num : product_part_number,
				product_serie : product_serie,
				is_new : is_new_row,
				location_status : product_location_status,
				location : product_location,
				detail_id : row_detail_id,
				block_id : global_block_id,
				is_new : (global_is_the_same_product_provider = 1 ? 0 : 1 ),
				notes : product_notes,
				tmp_measures_id : measures_id,
				location_tmp_id : $( '#location_tmp_id' ).val(),
				is_new_product : is_new_product,
				remission_total_boxes : remission_total_boxes,
				remission_total_pieces : remission_total_pieces,
				remission_total_quantity : remission_total_quantity
			},
			success : function ( dat ){
				var response = dat.split( '|' );
				if( response[0] == 'ok' ){
					alert( 'Producto registrado exitosamente, puede verfificarlo en la Sección "Listado"' );
					clean_product_form();
					desactivate_product_form();
					$( '#product_seeker' ).focus();
					var new_detail = JSON.parse( response[1] );
					//console.log( new_detail[0].id_recepcion_bodega_detalle );
					invoices[ product_serie ]['invoice_detail'].push( new_detail[0] );
					build_invoices_lists();
					build_invoices_lists_finish();
					clean_location_form( 'source' );
				}else{
					alert( "Error : " + dat );
				}
				is_row_edition = 0;//resetea indicador de edición
				clean_messages_descriptions();//limpia los mensajes
			}
		});
	}

	function setPiecesPerBoxGlobal(){
		var pieces_per_box = parseInt( $( '#pieces_per_box_tmp_global' ).val() );
		if( pieces_per_box == '' ){
			alert( "Las piezas por caja no pueden ir vacias!" );
			$( '#pieces_per_box_tmp_global' ).focus();
			return false;
		}else{
			$( '#remission_pieces_per_box' ).val( pieces_per_box );
			$( '#pieces_per_box' ).val( pieces_per_box );
			close_emergent();
		}
	}

//limpia el formulario de recepcion de productos
	function clean_product_form(){
		$( '#product_id' ).val('');
		$( '#product_provider' ).val('');
		$( '#reception_detail_id' ).val( '' );
		$( '#is_new_product_row' ).val( 0 );
		$( '#measure_tmp_id' ).val( 0 );

		$( '#product_model' ).val('');
		$( '#db_product_model' ).val('');
		$( '#product_model_null' ).val('');

		$( '#piece_barcode' ).val('');
		$( '#db_piece_barcode' ).val('');
		$( '#db_piece_barcode_2' ).val('');
		$( '#db_piece_barcode_3' ).val('');
		$( '#piece_barcode_null' ).removeAttr('checked');
		$( '#pieces_per_pack' ).val('');
		$( '#db_pieces_per_pack' ).val('');
		$( '#pieces_per_pack_null' ).removeAttr('checked');
		
		$( '#pack_barcode' ).val('');
		$( '#db_pack_barcode' ).val('');
		$( '#db_pack_barcode_2' ).val('');
		$( '#pack_barcode_null' ).removeAttr('checked');

		$( '#pieces_per_box' ).val('');
		$( '#db_pieces_per_box' ).val('');
		$( '#pieces_per_box_null' ).removeAttr('checked');
		$( '#box_barcode' ).val('');
		$( '#db_box_barcode' ).val('');
		$( '#db_box_barcode_2' ).val('');
		$( '#box_barcode_null' ).removeAttr('checked');
		$( '#received_packs' ).val('');
		$( '#received_pieces' ).val('');
		$( '#product_part_number' ).val('');
		$( '#product_serie' ).val('');
		$( '.product_name' ).html('');
		$( '.product_model' ).html('');
	//limpia formulario de ubicaciones	
		$( "#location_status_source option[value='2']" ).text( "Ubicación actual : " );
		$( "#product_location_source" ).val('');
		$("#location_status_source option[value='0']").attr("selected", 'true');

		$( '#product_part_number' ).empty();
		$( '#btn_parts_edition' ).attr( 'disabled', 'true' );
	//limpia formulario de recibido anteriormente
		$( '#validated_boxes_before' ).val( 0 );
		$( '#validated_pieces_before' ).val( 0 );
		$( '#validated_total_before' ).val( 0 );
		$( '#received_boxes_before' ).val( 0 );
		$( '#received_pieces_before' ).val( 0 );
		$( '#received_total_before' ).val( 0 );
	//limpia variables de cantidades totales
		$( '#remission_pieces_per_box' ).val( '' );
		$( '#remission_total_boxes' ).val( '' );
		$( '#remission_total_pieces' ).val( '' );
		$( '#remission_total_quantity' ).val( '' );
	}

	function activate_product_form(){
		$( '#piece_barcode' ).removeAttr('disabled');
		$( '#product_model' ).removeAttr('disabled');
		$( '#product_model_null' ).removeAttr('disabled');
		$( '#db_piece_barcode' ).removeAttr('disabled');
		$( '#piece_barcode_null' ).removeAttr('disabled');
		$( '#pieces_per_pack' ).removeAttr('disabled');
		$( '#db_pieces_per_pack' ).removeAttr('disabled');
		$( '#pieces_per_pack_null' ).removeAttr('disabled');
		$( '#pack_barcode' ).removeAttr('disabled');
		$( '#db_pack_barcode' ).removeAttr('disabled');
		$( '#pack_barcode_null' ).removeAttr('disabled');
		$( '#pieces_per_box' ).removeAttr('disabled');
		$( '#db_pieces_per_box' ).removeAttr('disabled');
		$( '#pieces_per_box_null' ).removeAttr('disabled');
		$( '#box_barcode' ).removeAttr('disabled');
		$( '#db_box_barcode' ).removeAttr('disabled');
		$( '#box_barcode_null' ).removeAttr('disabled');
		$( '#received_packs' ).removeAttr('disabled');
		$( '#received_pieces' ).removeAttr('disabled');
		$( '#product_part_number' ).removeAttr('disabled');
		$( '#product_serie' ).removeAttr('disabled');

		$( '#product_seeker_btn' ).css( 'display', 'none' );
		$( '#product_reset_btn' ).css( 'display', 'block' );
		$( '#product_seeker' ).attr( 'disabled', 'true' );

		$( "#location_status_source" ).removeAttr( 'disabled' );
		$( "#show_measures_form_btn" ).removeAttr( 'disabled' );
	}

	function desactivate_product_form(){
		$( '#piece_barcode' ).attr('disabled', 'true');
		$( '#product_model' ).attr('disabled', 'true');
		$( '#product_model_null' ).attr('disabled', 'true');
		$( '#product_model_null' ).removeAttr('checked');
		$( '#db_piece_barcode' ).attr('disabled', 'true');
		$( '#piece_barcode_null' ).attr('disabled', 'true');
		$( '#pieces_per_pack' ).attr('disabled', 'true');
		$( '#db_pieces_per_pack' ).attr('disabled', 'true');
		$( '#pieces_per_pack_null' ).attr('disabled', 'true');
		$( '#pack_barcode' ).attr('disabled', 'true');
		$( '#db_pack_barcode' ).attr('disabled', 'true');
		$( '#pack_barcode_null' ).attr('disabled', 'true');
		$( '#pieces_per_box' ).attr('disabled', 'true');
		$( '#db_pieces_per_box' ).attr('disabled', 'true');
		$( '#pieces_per_box_null' ).attr('disabled', 'true');
		$( '#box_barcode' ).attr('disabled', 'true');
		$( '#db_box_barcode' ).attr('disabled', 'true');
		$( '#box_barcode_null' ).attr('disabled', 'true');
		$( '#received_packs' ).attr('disabled', 'true');
		$( '#received_pieces' ).attr('disabled', 'true');
		$( '#product_part_number' ).attr('disabled', 'true');
		$( '#product_serie' ).attr('disabled', 'true');

		$( '#product_seeker_btn' ).css( 'display', 'block' );
		$( '#product_reset_btn' ).css( 'display', 'none' );
		$( '#product_seeker' ).removeAttr( 'disabled' );

		$( "#location_status_source" ).attr( 'disabled', 'true' );
		$( '#reception_detail_id' ).val( '' );
		$( '#btn_parts_edition' ).attr( 'disabled', 'true' );
		$( "#show_measures_form_btn" ).attr( 'disabled', 'true' );
	}

	function build_invoices_lists(){//obj_destinity
		//var dats;
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/builder.php',
			cache : false,
			data : { fl : 'buildInvoiceList',
					series : series
			},
			success : function ( dat ){
				$('#invoices_lists_container').html( dat );
				//console.log(dat);
			}
		});
	}
	function build_invoices_lists_finish(){//obj_destinity
		//var dats;
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/builder.php',
			cache : false,
			data : { fl : 'buildInvoiceListFinish',
					series : series
			},
			success : function ( dat ){
				$('#finish_invoices_container').html( dat );
				//console.log(dat);
			}
		});
	}
	
	function editDetail( detail_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'getRecepcionDetail',
					id : detail_id
			},
			success : function ( dat ){
				console.log( dat );
				var new_detail = JSON.parse(dat)
				//console.log( new_detail, new_detail[0].c_b_pieza);	
				//$( '.source' ).click();	

				$('.mnu_item.active').removeClass('active');
				$( '.source' ).addClass('active');
				$( '.content_item' ).css( 'display', 'none' );
				$( '.invoices_products' ).css( 'display', 'block' );	
			
			//setea los valores de los campos
				$( '#product_id' ).val( new_detail[0].id_producto );
				$( '.product_name' ).html( new_detail[0].nombre );
				$( '.product_model' ).html( new_detail[0].modelo );
				$( '#measure_tmp_id' ).val(new_detail[0].measures_id);
				$( '#is_new_product_row' ).val(new_detail[0].new_product_id);



				$( '#piece_barcode' ).val( new_detail[0].c_b_pieza );
				$( '#db_piece_barcode' ).val( new_detail[0].c_b_pieza );
				if( new_detail[0].c_b_pieza == '' || new_detail[0].c_b_pieza == null ){
					$( '#piece_barcode_null' ).prop( 'checked', 'true' );				
				}
				
				$( '#product_model' ).val( new_detail[0].modelo );
				$( '#db_product_model' ).val( new_detail[0].modelo );
				if( new_detail[0].modelo == '' || new_detail[0].modelo == null ){
					$( '#product_model_null' ).prop( 'checked', 'true' );				
				}

				$( '#pieces_per_pack' ).val( new_detail[0].piezas_por_paquete );
				$( '#db_pieces_per_pack' ).val( new_detail[0].piezas_por_paquete );
				if( new_detail[0].piezas_por_paquete == '' || new_detail[0].piezas_por_paquete == null ){
					$( '#pieces_per_pack_null' ).prop( 'checked', 'true' );				
				}

				$( '#pack_barcode' ).val( new_detail[0].c_b_paquete );
				$( '#db_pack_barcode' ).val( new_detail[0].c_b_paquete );
				if( new_detail[0].c_b_paquete == '' || new_detail[0].c_b_paquete == null ){
					$( '#pack_barcode_null' ).prop( 'checked', 'true' );				
				}

				$( '#pieces_per_box' ).val( new_detail[0].piezas_por_caja );
				$( '#db_pieces_per_box' ).val( new_detail[0].piezas_por_caja );
				if( new_detail[0].piezas_por_caja == '' || new_detail[0].piezas_por_caja == null ){
					$( '#pieces_per_box_null' ).prop( 'checked', 'true' );				
				}

				$( '#box_barcode' ).val( new_detail[0].c_b_caja );
				$( '#db_box_barcode' ).val( new_detail[0].c_b_caja );
				if( new_detail[0].c_b_caja == '' || new_detail[0].c_b_caja == null ){
					$( '#box_barcode_null' ).prop( 'checked', 'true' );				
				}

			//	$( '#received_packs' ).val( new_detail[0].cajas_recibidas );
			//	$( '#received_pieces' ).val( new_detail[0].piezas_sueltas_recibidas );
				$( '#product_part_number' ).empty();
				$( '#product_part_number' ).append( '<option value="' + new_detail[0].numero_partida + '">' + new_detail[0].numero_partida + '</option>' );
				$( '#product_serie' ).val( new_detail[0].serie );
			
			//ubicacion del producto
				if( new_detail[0].ubicacion_almacen != '' ){
					$( "#location_status_source option[value=2]" ).text( "Ubicación actual : " + new_detail[0].ubicacion_almacen );
				}else{
					$( "#location_status_source option[value=2]" ).text( "No tiene ubicación" );
				}
				$("#location_status_source option[value='" + new_detail[0].id_status_ubicacion + "']").attr("selected", 'true');
				$( '#product_location_source' ).val( new_detail[0].ubicacion_almacen );
			//id del detalle
				$( '#reception_detail_id' ).val( new_detail[0].id_recepcion_bodega_detalle );

		/*implementacion Oscar 2023*/
				$( '#validated_boxes_before' ).val( new_detail[0].cajas_validadas );
				$( '#validated_pieces_before' ).val( new_detail[0].piezas_recibidas );
				$( '#validated_total_before' ).val( new_detail[0].total_piezas_validadas );

				$( '#received_boxes_before' ).val( new_detail[0].cajas_recibidas );
				$( '#received_pieces_before' ).val( new_detail[0].piezas_sueltas_recibidas );
				$( '#received_total_before' ).val( new_detail[0].total_piezas_recibidas );

				$( '#remission_pieces_per_box' ).val( new_detail[0].piezas_por_caja );
				$( '#remission_total_boxes' ).val( new_detail[0].total_cajas_remision );
				$( '#remission_total_pieces' ).val( new_detail[0].total_piezas_sueltas_remision );
				$( '#remission_total_quantity' ).val( new_detail[0].total_en_piezas_remision );
		/*fin de cambio Oscar 2023*/
				//alert( 'val : ' + $( '#reception_detail_id' ).val() );
			//activa el formulario
				activate_product_form();
			//cambia funcion del botón de cambiar producto
				$( '#product_reset_btn' ).attr( 'onclick', 'activate_change_product();' );
				/*$( '#product_seeker_btn' ).css( 'display', 'block' );
				$( '#product_reset_btn' ).css( 'display', 'none' );
				$( '#product_seeker' ).removeAttr( 'disabled' );*/

			}
		});
	}
//
	var is_row_edition = 0;
	function activate_change_product(){
		$( '#product_seeker' ).removeAttr( 'disabled' );
		is_row_edition = 1;//marca edicion de registro
	}


	function change_invoices_status(){
		var req = '';
		$( '#tbody_finish tr' ).each( function ( index ){
			if( index > 0 ){
				req += '|~|';
			}
			$(this).children("td").each(function (index2) {
				if( index2 == 0 ){
					req += $( this ).html() + '~';
				}else if( index2 == 5 ){
					req += $( '#status_' + index ).val();
				}
			});
		});
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'changeInvoicesStatus',
					data : req
			},
			success : function ( dat ){
				var aux = dat.split( '|' );
				if( aux[0] != 'ok' ){
					alert( "Error : \n" + dat );
					return false;
				}else{
					alert( aux[1] );
					location.reload();
				}
			}
		});
	}

	function edit_parts_number(){
		$( '.number_part_aux' ).css( 'display', 'flex' );
		$( '#product_part_number_aux' ).focus();

	}

	function save_edition_parts(){
		var new_serie = $( '#product_part_number_aux' ).val().trim();
		if( new_serie.length <= 0 || new_serie <= 0 ){
			alert( "El número de serie no puede ir vacío y debe de ser mayor a cero." );
			$( '#product_part_number_aux' ).focus();
			return false;
		}
		new_serie = parseInt( new_serie );
		var serie = $( '#product_serie' ).val();
		var serie_number = parseInt( $( '#' + serie + '_parts_number' ).html().trim() );
		
		if( new_serie > serie_number ){
			alert( "El número de partida para la serie " + serie + " no puede ser mayor a " + serie_number );//+ ' ( ' + new_serie + ' )'
			$( '#product_part_number_aux' ).select();
			return false;
		}
		$( '#product_part_number_aux' ).val( '' );
		$( '#product_part_number' ).append( '<option value="' + new_serie + '">' + new_serie + '</option>' );
		$( '#product_part_number' ).val( new_serie );
		$( '.number_part_aux' ).css( 'display', 'none' );

		new_serie = '';
	}

	function show_measures_form( flag = null ){
	//no solicitar las medidas por configuracion del sistema
		if( $( '#dont_request_reception_measures' ).val() == 1 && flag != null ){
			return false;
		}
	//no solicitar medidas si ya fueron capturadas al desenfocar codigo de pieza 
		if( $( '#measure_tmp_id' ).val() != '0' && flag != null ){
			return false;
		}
		var url = 'ajax/db.php?fl=measuresForm&product_id=' + $( '#product_id' ).val();
	//manda id para mostrar medidas capturadas
		if( $( '#measure_tmp_id' ).val().trim() != '0' ){
			url += "&tmp_meassure_id=" + $( '#measure_tmp_id' ).val().trim();
		}
		url += "&home_path=" + global_meassures_home_path;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function save_measures( measures_id = null ){
		var box_measure_lenght = 0, box_measure_width = 0, box_measure_height = 0,
			pack_photo_1 = '', pack_photo_2 = '', pack_photo_3 = '', bag_type_id = 0,
			pack_measure_lenght = 0, pack_measure_width = 0, pack_measure_height = 0,
			piece_measure_lenght = 0, piece_measure_width = 0, piece_measure_height = 0, piece_measure_weight = 0;
		var url = 'ajax/db.php?fl=saveMeasures&product_id=' + $( '#product_id' ).val();
			url += '&product_provider_id=' + $( '#product_provider' ).val();
			url += '&is_new_product=' + $( '#is_new_product_row' ).val();
		if( measures_id != null ){
			url += '&measures_id=' + measures_id;
		}

		url += "&reception_detail_id=" + $( '#reception_detail_id' ).val();
	//caja
		if( $( '#no_box_measures' ).prop( 'checked' ) ){
			box_measure_lenght = $( '#box_lenght' ).val();
			if( box_measure_lenght <= 0 ){
				alert( "El largo de la caja no puede ir vacío!" );
				$( '#box_lenght' ).focus();
				return false;
			}else{
				url += '&box_lenght=' + box_measure_lenght;
			}

			box_measure_width = $( '#box_width' ).val();
			if( box_measure_width <= 0 ){
				alert( "El ancho de la caja no puede ir vacío!" );
				$( '#box_width' ).focus();
				return false;
			}else{
				url += '&box_width=' + box_measure_width;
			}

			box_measure_height = $( '#box_height' ).val();
			if( box_measure_height <= 0 ){
				alert( "El alto de la caja no puede ir vacío!" );
				$( '#box_height' ).focus();
				return false;
			}else{
				url += '&box_height=' + box_measure_height;
			}
		}

	//aplica paquete
		if( $( '#no_pack_measures' ).prop( 'checked' ) ){
			pack_measure_lenght = $( '#pack_lenght' ).val();
			if( pack_measure_lenght <= 0 ){
				alert( "El largo del paquete no puede ir vacío!" );
				$( '#pack_lenght' ).focus();
				return false;
			}else{
				url += '&pack_lenght=' + pack_measure_lenght;
			}

			pack_measure_width = $( '#pack_width' ).val();
			if( pack_measure_width <= 0 ){
				alert( "El ancho del paquete no puede ir vacío!" );
				$( '#pack_width' ).focus();
				return false;
			}else{
				url += '&pack_width=' + pack_measure_width;
			}

			pack_measure_height = $( '#pack_height' ).val();
			if( pack_measure_height <= 0 ){
				alert( "El alto del paquete no puede ir vacío!" );
				$( '#pack_height' ).focus();
				return false;
			}else{
				url += '&pack_height=' + pack_measure_height;
			}
			bag_type_id = $( '#pack_bag' ).val();
			if( bag_type_id <= 0 ){
				alert( "El tipo de bolsa no puede ir vacío!" );
				$( '#pack_bag' ).focus();
				return false;
			}else{
				url += '&bag_type=' + bag_type_id;
			}
			
		//imágenes del paquete
			if( $( '#no_pack_measures_photos' ).prop( 'checked' ) ){
				pack_photo_1 = $( '#previous_img_1' ).attr( 'src' );
				if( pack_photo_1 == '' || pack_photo_1 == '../../../img/frames/camera_icon.jpeg' ){
					alert( "La fotografía de caja abierta no puede ir vacía!" );
					$( '#previous_img_1' ).click();
					return false;
				}else{
					url += '&photo_1=' + pack_photo_1.replace( '../../../files/packs_img/', '' );
				}
				
				pack_photo_2 = $( '#previous_img_2' ).attr( 'src' );
				if( pack_photo_2 == '' || pack_photo_2 == '../../../img/frames/camera_icon.jpeg' ){
					alert( "La fotografía frontal no puede ir vacía!" );
					$( '#previous_img_2' ).click();
					return false;
				}else{
					url += '&photo_2=' + pack_photo_2.replace( '../../../files/packs_img/', '' );
				}
				
				pack_photo_3 = $( '#previous_img_3' ).attr( 'src' );
				if( pack_photo_3 == '' || pack_photo_3 == '../../../img/frames/camera_icon.jpeg' ){
					alert( "La fotografía del ancho no puede ir vacía!" );
					$( '#previous_img_3' ).click();
					return false;
				}else{
					url += '&photo_3=' + pack_photo_3.replace( '../../../files/packs_img/', '' );
				}
			}
		}

	//pieza
		if( $( '#no_piece_measures' ).prop( 'checked' ) ){
			piece_measure_lenght = $( '#piece_lenght' ).val();
			if( piece_measure_lenght <= 0 ){
				alert( "El largo de la pieza no puede ir vacío!" );
				$( '#piece_lenght' ).focus();
				return false;
			}else{
				url += '&piece_lenght=' + piece_measure_lenght;
			}

			piece_measure_width = $( '#piece_width' ).val();
			if( piece_measure_width <= 0 ){
				alert( "El ancho de la pieza no puede ir vacío!" );
				$( '#piece_width' ).focus();
				return false;
			}else{
				url += '&piece_width=' + piece_measure_width;
			}

			piece_measure_height = $( '#piece_height' ).val();
			if( piece_measure_height <= 0 ){
				alert( "El alto de la pieza no puede ir vacío!" );
				$( '#piece_height' ).focus();
				return false;
			}else{
				url += '&piece_height=' + piece_measure_height;
			}

			piece_measure_weight = $( '#piece_weight' ).val();
			if( piece_measure_weight <= 0 ){
				url += '&piece_weight=0';
			}else{
				url += '&piece_weight=' + piece_measure_weight;
			}
		}
		if( ! $( '#no_box_measures' ).prop( 'checked' ) 
			&& ! $( '#no_pack_measures' ).prop( 'checked' )
			&& ! $( '#no_piece_measures' ).prop( 'checked' )  ){
			alert( "Debe elegir al menos ua categoaría para guardar medidas( Caja, Paquete o Pieza )" );
			return false;
		}
		//alert( url ); //return false;
		var response = ajaxR( url );
		var aux = response.split( '|' );
		if( aux[0] != 'ok' ){
			alert( response );
		}else{
			$( '#measure_tmp_id' ).val( aux[1] );
			var resp = "<div class=\"row\"><div class=\"col-2\"></div>";
				resp += "<div class=\"col-8\">";
					resp += "<h5 style=\"color : green\">Las medidas fueron guardas exitosamente!</h5>";
					resp += "<button onclick=\"close_emergent();\" class=\"btn btn-success form-control\">";
						resp += "<i class=\"icon-ok-circle\">Aceptar y cerrar</i>";
					resp += "</button>";
				resp += "</div>"
			resp += "</div>";
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			$( '.emergent_content' ).focus();
			//setTimeout( function(){close_emergent(); }, 5000 );
		}
	}

	function sow_new_product_form(){
		var resp = "<div class=\"row\">";
				resp += "<div class=\"col-2\"></div>";
				resp += "<div class=\"col-8\">";
					resp += "<label>Nombre del producto : </label>";
					resp += "<input type=\"text\" id=\"tmp_product_name\" class=\"form-control\">";
					resp += "<br>"; 
					resp += "<label>Modelo del producto : </label>";
					resp += "<input type=\"text\" id=\"tmp_product_model\" class=\"form-control\">";
					resp += "<br>"; 
					resp += "<div class=\"row\">";
						resp += "<div class=\"col-6\">";
							resp += "<button class=\"btn btn-success\" onclick=\"save_new_product();\">";
								resp += "<i class=\"icon-ok-circle\">Guardar</i>";
							resp += "</button>";
						resp += "</div>";
						resp += "<div class=\"col-6\">";
							resp += "<button class=\"btn btn-danger\" onclick=\"close_emergent_2();\">";
								resp += "<i class=\"icon-cancel-circled\">Cancelar</i>";
							resp += "</button>";
						resp += "</div>";
					resp += "<div>";
				resp += "</div>";
				resp += "<div class=\"col-2\"></div>";
			resp += "</div>";
		$( '.emergent_content_2' ).html( resp );
		$( '.emergent_2' ).css( 'display', 'block' );
	}
	function save_new_product(){
		var name = $( '#tmp_product_name' ).val();
		if( name == '' ){
			alert( "El nombre del nuevo producto no puede ir vacío!" );
			return false;
		}
		var model = $( '#tmp_product_model' ).val();
		var url = 'ajax/db.php?fl=saveNewProduct&product_name=' + name + "&model=" + model;
		var response = ajaxR( url );
		var aux = response.split('|');
		if( aux[0] != 'ok' ){
			alert( response );
			return false;
		}else{
			//$( '#product_id' ).val( aux[1] );
			setProduct( aux[1], name + "<br>MODELO : <b>" + model + "</b>", null, '', '', '', '', '', '', '', 1 );
			$( '#product_model' ).val( model );
			close_emergent_2();
		//implementacion Oscar 2023
			getPiecesEmergent();
		//fin de cambio Oscar 2023
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

	function omit_unic_barcode( obj ){
		var txt = "";
		var tmp_txt = $( obj ).val().split( ' ' );
		if( tmp_txt.length == 4 ){
			//txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		//	alert( tmp_txt );
			$( obj ).val( txt );
		}
	}

	function setDefaultValueToInput( obj, obj_to_set_id, value ){
		if( $( obj ).prop( 'checked' ) ){
			$( obj_to_set_id ).val( value );
			$( obj_to_set_id ).attr( 'disabled' , true );
		}else{
			$( obj_to_set_id ).val( '' );
			$( obj_to_set_id ).removeAttr( 'disabled' );
			$( obj_to_set_id ).focus();
		}
	}

	function delete_reception_detail( reception_detail_id, was_validated = 0 ){
		if( was_validated != 0 ){
			show_reception_detail_was_validated_message( reception_detail_id );
			return false;
		}else{				
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			var url = "ajax/db.php?fl=deleteReceptionDetail&reception_detail_id=" + reception_detail_id;
			var response = ajaxR( url );
			alert( response );
			build_invoices_lists();

		}
	}

	function show_reception_detail_was_validated_message( reception_detail_id ){
		var resp = `<h5>Este producto ya fue validado desde la validación de Remisiones y eliminarán los siguientes registros : </h5>
					<ul>
						<li class="icon-right-big">Detalle de remisión
							<ul>
								<li class="icon-right-big">
									Movimiento de almacén
									<ul>
										<li class="icon-right-big">
											Detalle de movimiento de almacén ( nivel producto )
											<ul>
												<li class="icon-right-big">
													Detalle de movimiento de almacén ( nivel proveedor - producto )
												</li>
											</ul>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
					<div class="row">
						<div class="col-2"></div>
						<div class="col-8">
							<p align="center">¿Deseas continuar?</p>
							<div class="row">
								<div class="col-1"></div>
								<div class="col-5">
									<button type="button" class="btn btn-success" onclick="delete_reception_detail( ${reception_detail_id} )">
										<i class="icon-ok-circle">Aceptar</i>
									</button>
								</div>
								<div class="col-5">
									<button type="button" class="btn btn-danger" onclick="close_emergent();">
										<i class="icon-cancel-circled">Cancelar</i>
									</button>
								</div>
							</div>
						</div>
					</div>`;
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function getPermissions(){
		var url = "ajax/db.php?fl=getPermissions";
		var response = ajaxR( url );
		var aux = JSON.parse(response);
		if( aux.view == 1 && aux.edit == 0 && aux.del == 0 && aux.new == 0  ){//&& aux.print == 1 && aux.make == 1
			show_view( this, '.invoices_finish');
			$( ".header" ).css( 'display', 'none' );
			$( "#save_location_btn_seeker" ).css( 'display', 'none' );
			$( "#finish_invoices_container" ).css( 'display', 'none' );
			$( ".group_card.without_location" ).css( 'display', 'none' );

			$( "#aisle_seeker_since" ).attr( 'disabled', 'true' );
			$( "#location_number_seeker_since" ).attr( 'disabled', 'true' );
			$( "#aisle_from_seeker" ).attr( 'disabled', 'true' );
			$( "#level_from_seeker" ).attr( 'disabled', 'true' );
			$( "#aisle_seeker_to" ).attr( 'disabled', 'true' );
			$( "#location_number_seeker_to" ).attr( 'disabled', 'true' );
			$( "#aisle_until_seeker" ).attr( 'disabled', 'true' );
			$( "#level_to_seeker" ).attr( 'disabled', 'true' );
			$( "#enabled_seeker" ).attr( 'disabled', 'true' );
			$( "#is_principal_seeker" ).attr( 'disabled', 'true' );
			


			return false;

		}else{
			return true;
		}
	}
