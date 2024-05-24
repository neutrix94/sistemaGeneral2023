var current_product = null;
var current_warehouse = 0;
var scanned_historic = new Array();
	function seek_product( e, value = null ){
		var txt;
		if( e != 'intro' && e.keyCode != 13 ){
			return false;
		}
	//obtiene valor del buscador
		txt = $( "#principal_seeker" ).val().trim();
		if( txt.length == 0 && value == null ){
			show_emergent( `<h5 class="text-center">El buscador no puede ir vacio!</h5>`, true );
			$( "#principal_seeker" ).focus();
			return false;
		}
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 ){
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}

		if( value != null ){
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			txt = value;
		}
		//alert( boxes_ceils );
		//alert( boxes_ceils.includes( txt ) );
		if( boxes_ceils.includes( txt ) != false ){
			var box_tmp  = boxForm.replace( '$_type_id', 1 );
			show_emergent( box_tmp, false, false );
			return false;
		}

		var response = ajaxR( 'ajax/inventory.php?inventory_fl=seekProduct&key=' + txt + "&warehouse_id=" + current_warehouse );
		var aux = response.split( '|' );
		if( response[0].trim() == 'invalid_store' ){
			exit_by_session_error();
			return false;
		}
		//alert( response );
		$( "#principal_seeker" ).val( '' );
		switch( aux[0] ){
			case 'seeker' :
				$( '#seeker_response' ).html( aux[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
			break;
			case 'ok' :
				current_product = JSON.parse( aux[1] );
				if( current_product.print_box_tag == 0 && current_product.print_pack_tag == 0 && current_product.print_loose_parts_tag == 0 && current_product.print_piece_tag == 0   ){
					show_emergent( `<h4 class="text-center">Este producto no requiere que se generen etiquetas</h4>`, true );
					return false;
				}
//alert( current_product.is_maquiled );
//alert( current_product.special_product );
				setCurrentProduct( current_product );
			break;

			case 'multiProductProvider' :
			//alert('here' );
				$( '.emergent_content' ).html( aux[1] );
				$( '.emergent' ).css( 'display', 'block' );
			break;
			default :
				show_emergent( `<h5 class="text-center">Producto no econtrado!</h5>`, true ); 
				console.log( response );
				//clean_current_product();
			break;
		}
	}

	function scann_barcode( e ){
		var barcode;
		if( e != 'intro' && e.keyCode != 13 ){
			return false;
		}
		barcode = $( "#seeker_barcodes" ).val().trim();
		//var response = ajaxR( 'ajax/db.php?fl=seekProduct&key=' + txt + ""  );
		//var aux = response.split( '|' );
		if( barcode == '' ){
			show_emergent( `<h5 class="text-center">El código de barras no puede ir vacío!</h5>`, true ); 
			$( "#seeker_barcodes" ).focus();
			return false;
		}
		if( current_product.codigo_barras_pieza_1 == barcode
		  || current_product.codigo_barras_pieza_2 == barcode
		  || current_product.codigo_barras_pieza_3 == barcode ){
		/*
			
		  || current_product.codigo_barras_presentacion_cluces_1 == barcode
		  || current_product.codigo_barras_presentacion_cluces_2 == barcode
		  || current_product.codigo_barras_caja_1 == barcode
		  || current_product.codigo_barras_caja_2 == barcode
		*/
		//alert( 'ok' );
			var aux = parseInt( $( '#scans_counter' ).val() == '' ? 0 : $( '#scans_counter' ).val() );
			if( $( '#edition_permission' ).val() == 0  ){
			//alert( parseInt( aux + 1 ) + '>'+ parseInt( $( '#pieces_number' ).val()) );
				if( parseInt( aux + 1 ) > parseInt( $( '#pieces_number' ).val() ) ){
					show_emergent( `<h5 class="text-center">Las piezas escaneadas superan la cantidad capturada al inicio, 
						verifica el conteo y vuelve a capturar este producto!</h5>`, true );
					$( "#seeker_barcodes" ).val( '' );
					clean_current_product();
					return false;
				}	
		  	}
			$( '#scans_counter' ).val( aux + 1 );
			calculate_barcodes_quantity();	
			$( "#seeker_barcodes" ).val( '' );
		}else{
			show_emergent( `<h5 class="text-center">Error : Este producto no corresponde a los que se estan escaneando, separalo de los demás</h5>`, true );
			$( "#seeker_barcodes" ).focus();
		}
	}

	function setProductByName( product_id ){
		//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/inventory.php?inventory_fl=getOptionsByProductId&product_id=" + product_id;
		var response = ajaxR( url );
		if( response.trim() == 'invalid_store' ){
			exit_by_session_error();
			return false;
		}
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setProductModel(){
		var model_selected = -1;
		$( '#model_by_name_list tr' ).each( function ( index ){
			if( $( '#p_m_5_' + index ).prop( 'checked' ) ){
			//	alert( index );
				model_selected = $( '#p_m_5_' + index ).val();
			}
		});
		if( model_selected == -1 ){
			alert( "Debe de seleccionar un modelo para continuar!" );
			return false;
		}else{
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			$( '#principal_seeker' ).val( model_selected.trim() );
			seek_product( 'intro' );
			//validateBarcode( '#barcode_seeker', 'enter', null, null, null, null, 1 );
			//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
	}

	function setCurrentProduct( current_product ){
		$( '#product_description_header' ).html( current_product.product_name );//product_name
		$( '#principal_seeker' ).attr( 'disabled', true );
		$( '#principal_seeker_search_btn' ).css( 'display', 'none' );

		//alert( current_product.provider_clue );
		$( '#product_model' ).html( current_product.provider_clue );//provider_clue
		$( '#product_location' ).html( current_product.location );//provider_clue
		//$( '#pieces_per_box' ).val( current_product.pieces_per_box );//pieces_per_box
		$( '#product_provider_inventory' ).val( current_product.inventory );

		$( '#principal_seeker_search_btn' ).css( 'display', 'none' );
		$( '#principal_seeker_reset_btn' ).css( 'display', 'flex' );


		$( '#product_seeker' ).removeAttr( 'disabled' );

		if( current_product.is_maquiled == 1 || current_product.is_maquiled == '1' ){
			getMaquileForm();
		}else if( current_product.is_without_tag == 1 || current_product.is_without_tag == '1' ){
			//alert();
			getPiecesForm();
		}else{
			$( '#product_seeker' ).focus();
		}
		getProductCounterHistoric( current_product );
	}

	function getProductCounterHistoric( product ){
		scanned_historic = new Array();
		var url = 'ajax/inventory.php?inventory_fl=getProductCounterHistoric&product_id=' + current_product.product_id;
		url += '&product_provider_id=' + current_product.product_provider_id;
		url += '&warehouse_id=' + current_warehouse;
		//alert( url );
		var response = ajaxR( url ).split( '|~~|' );
		//alert( response );
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error : " + response[0] );
		}else{
			var tmp1 = response[1].split( '|~|' );
			for( var i = 0; i < tmp1.length; i++ ){
				var tmp2 = tmp1[i].split( '|' );
				var tmp = new Array();
				if( tmp2[0] != null && tmp2[0] != '' ){
					//alert( type );
					tmp['type'] = tmp2[0];
					tmp['product_id'] = tmp2[1];
					tmp['product_provider_id'] = tmp2[2];
					tmp['boxes'] = tmp2[3];
					tmp['packs'] = tmp2[4];
					tmp['pieces'] = tmp2[5];
					tmp['total_pieces'] = tmp2[6];
					tmp['user_id'] = tmp2[7];
					tmp['date'] = tmp2[8];
					tmp['barcode'] = tmp2[9];
					tmp['row_id'] = tmp2[10];
					/*if( !insert_scanned_counter( tmp ) ){
						return false;
					}*/
					scanned_historic.push( tmp );
				}
			}

		}
	}

	function setPiecesNumber(){
		$( '#pieces_number' ).attr( 'disabled', true );
		$( '#seeker_barcodes' ).focus();

		$( '#pieces_number_set_btn' ).css( 'display', 'none' );
	}
		function getMaquileForm(){
		var response = ajaxR( '../../plugins/maquila.php?fl_maquile=getMaquileForm&product_id=' + current_product.product_id + "&function=setProductPieces();" );	
		show_emergent( response, false, true );
		//$( '.emergent_content' ).html( response );
		//$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).focus();
	}

	function getPiecesForm(){
		var response = ajaxR( '../../plugins/product_has_not_tag.php?fl_special=getMaquileForm&product_id=' + current_product.product_id + "&function=setProductPiecesSpecial();" );	
		/*$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );*/
		show_emergent( response, false, true );
		$( '.emergent_content' ).focus();
	}
	
	function setProductPiecesSpecial(){
		var pieces_quantity = $( '#special_tmp_input' ).val();
		if( pieces_quantity == '' || pieces_quantity < 0 ){
			alert( "El numero de piezas no puede ir vacio!" );
			$( '#special_tmp_input' ).focus();
			return false;
		}
		/*$( '#pieces_number' ).val( $( '#special_tmp_input' ).val() );
		$( '#pieces_number_set_btn' ).click();
		$( '#scans_counter' ).val( $( '#special_tmp_input' ).val() );*/
		calculate_scanned_pieces( 'Sin etiqueta', '',0, 0, pieces_quantity );
			//alert( content );
		//alert();
//calculate_barcodes_quantity();
		setTimeout( function (){
			close_emergent();
		}, 100);
	}

	function calculate_scanned_pieces( type, barcode, boxes = 0, packs = 0, pieces = 0 ){
	//guarda el escaneo
		if( barcode != null ){
			//alert( '' );
			saveScannerTmp( type, barcode, boxes, packs, pieces );
		}
		var content = "";
		var boxes_tmp = 0, packs_tmp = 0, pieces_tmp = 0, total_pieces_tmp = 0; 
		/*boxes_tmp = parseInt( $( '#boxes_scanned_quantity' ).html().trim() ) + boxes; 
		packs_tmp = parseInt( $( '#packs_scanned_quantity' ).html().trim() ) + packs; 
		pieces_tmp = parseFloat($( '#pieces_scanned_quantity' ).html().trim() ) + parseFloat( pieces );
		total_pieces_tmp += parseInt( boxes_tmp * current_product.pieces_per_box );
		total_pieces_tmp += parseInt( packs_tmp * current_product.pieces_per_pack );
		total_pieces_tmp += parseFloat( pieces_tmp );*/

		for( var i = 0; i < scanned_historic.length; i ++ ){
			boxes_tmp += ( parseInt( scanned_historic[i]['boxes'] ) <= 0 ? 0 : parseInt( scanned_historic[i]['boxes'] ) );
			packs_tmp += ( parseInt( scanned_historic[i]['packs'] ) <= 0 ? 0 : parseInt( scanned_historic[i]['packs'] ) );
			pieces_tmp += ( parseFloat( scanned_historic[i]['pieces'] ) <= 0 ? 0 : parseFloat( scanned_historic[i]['pieces'] ) );
		}
		total_pieces_tmp += parseInt( boxes_tmp * current_product.pieces_per_box );
		total_pieces_tmp += parseInt( packs_tmp * current_product.pieces_per_pack );
		total_pieces_tmp += parseFloat( pieces_tmp );
//alert( total_pieces_tmp );
		setTimeout( function (){
			var content = `<tr>
					<td id="boxes_scanned_quantity" class="text-end">${boxes_tmp}</td>
					<td id="packs_scanned_quantity" class="text-end">${packs_tmp}</td>
					<td id="pieces_scanned_quantity" class="text-end">${pieces_tmp}</td>
					<td id="total_scanned_quantity" class="text-end">${total_pieces_tmp}</td>
				<tr>`;

			$( '#scanner_resume' ).empty();
			$( '#scanner_resume' ).html( content );	
		}, 100 );
	}

	function saveScannerTmp( type, barcode, boxes = 0, packs = 0, pieces = 0 ){
		var tmp = new Array();
		//alert( type );
		tmp['type'] = type;
		tmp['product_id'] = current_product.product_id;
		tmp['product_provider_id'] = current_product.product_provider_id;
		tmp['boxes'] = boxes;
		tmp['packs'] = packs;
		tmp['pieces'] = pieces;
		tmp['total_pieces'] = ( parseInt( boxes *  current_product.pieces_per_box ) + parseInt( packs * current_product.pieces_per_pack ) + parseFloat( pieces ) );
		tmp['user_id'] = $( '#user_id' ).val().trim();
		tmp['date'] = getDateTime();
		tmp['barcode'] = barcode;

		var id  = insert_scanned_counter( tmp ).split( '|' );
		if( id[0] != 'ok' ){
			return false;
		}
		tmp['row_id'] = id[1];
		scanned_historic.push( tmp );
	}

	//inserta los registros
	function insert_scanned_counter( data ){
		if( current_product == null ){
			alert( "Es necesario que primero selecciones un producto!" );
			return false;
		}
		//var detail = data;//JSON.stringify(  );
		//alert(  detail );
		var url = "ajax/inventory.php?inventory_fl=insertScannAndDetail";
		url += "&type=" + data['type'];
		url += "&product_id=" + data['product_id'];
		url += "&product_provider_id=" + data['product_provider_id'];
		url += "&boxes=" + data['boxes'];
		url += "&packs=" + data['packs'];
		url += "&pieces=" + data['pieces'];
		url += "&total_pieces=" + data['total_pieces'];
		url += "&user_id=" + data['user_id'];
		url += "&date=" + data['date'];
		url += "&barcode=" + data['barcode'];
		url += "&warehouse_id=" + current_warehouse;
		//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error: " + response[0] );
		}else{
			return 'ok|' + response[1];
		}
		//alert( url );
		//console.log( response );
	}

	function save_product_count(){
		if( current_product == null ){
			alert( "Primero debes de seleccionar un producto!" );
			return false;
		}
		var url = "ajax/inventory.php?inventory_fl=saveProductCount&product_id=" + current_product.product_id;
		url += "&product_provider_id=" + current_product.product_provider_id + "&warehouse_id=" + current_warehouse;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error : " + response[0] );
		}else{
			show_emergent( response[1], true, false );
			clean_current_product();
			close_emergent();
			getOmitedProducts();

			if( count_type == 'specific' ){
				getNextProduct();
			}
		}
	}

	function showScannedHistoric(){
		var content = `<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th class="text-center">Codigo</th>
								<th class="text-center">Tipo</th>
								<th class="no_visible">product_id</th>
								<th class="no_visible">product_provider_id</th>
								<th class="text-center">Cajas</th>
								<th class="text-center">Paquetes</th>
								<th class="text-center">Piezas</th>
								<th class="no_visible">user_id</th>
								<th class="text-center">Fecha / Hora</th>
								<th>X</th>
							</tr>
						</thead>
						<tbody id="scanned_historic_list">`;
		for( var i = 0; i < scanned_historic.length; i ++ ){
			if(  scanned_historic[i]['type'] != null ){
				content += `<tr>
						<td class="text-end">${scanned_historic[i]['barcode']}</td>
						<td class="text-center">${scanned_historic[i]['type']}</td>
						<td class="no_visible">${scanned_historic[i]['product_id']}</td>
						<td class="no_visible">${scanned_historic[i]['product_provider_id']}</td>
						<td class="text-end">${scanned_historic[i]['boxes']}</td>
						<td class="text-end">${scanned_historic[i]['packs']}</td>
						<td class="text-end">${scanned_historic[i]['pieces']}</td>
						<td class="no_visible">${scanned_historic[i]['user_id']}</td>
						<td class="text-center">${scanned_historic[i]['date']}</td>
						<td class="text-center">
							<button
								type="button"
								class="btn btn-danger"
								onclick="remove_scann( ${i}, ${scanned_historic[i]['row_id']} );"
							>
								<i>X</i>
							</button>
						</td>
					</tr>`;
			}
		}
		content += `</tbody></table>`;
		show_emergent( content, true, false );
	}
	function remove_scann( position, id ){
		if( !confirm( "Eliminar este escaneo? " ) ){
			return false;
		}
		//delete( scanned_historic[ position ] );
		var url = "ajax/inventory.php?inventory_fl=remove_scann&row_id=" + id;
		url += "&warehouse_id=" + current_warehouse;
		response = ajaxR( url );
		if( response.trim() != 'ok' ){
			if( response.trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error : " + response );
			return false;
		}
		scanned_historic.splice( position, 1 );
		calculate_scanned_pieces( null, null, 0, 0, 0 );
		showScannedHistoric();
	}

	function setProductPieces(){
		//$( '#maquila_decimal' ).val();
		var quantity = $( '#maquila_decimal' ).val();
		if( quantity == '' || quantity < 0 ){
			alert( "Debes ingresar un valor válido para continuar!" );
			$( '#maquila_complete' ).focus();
			return false;
		}
		//$( '#pieces_number_set_btn' ).click();
		//alert(  current_product.codigo_barras_pieza_1 );
	//	alert(  quantity );
		calculate_scanned_pieces( 'Maquilado', current_product.codigo_barras_pieza_1, 0, 0, quantity );
		$( '#scans_counter' ).val( $( '#maquila_decimal' ).val() );
		//calculate_barcodes_quantity();
		setTimeout( function (){
			close_emergent();
		}, 100);
	}

	function clean_current_product(){
		current_product = null;
		$( '#principal_seeker' ).val( '' );//product_name
		$( '#principal_seeker' ).removeAttr( 'disabled' );

		$( '#product_model' ).html( '' );
		$( '#product_location' ).html( '' );
		$( '#product_provider_inventory' ).val( '' );

		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display', 'none' );


		$( '#product_description_header' ).html( '' );
		$( '#principal_seeker_search_btn' ).css( 'display', 'flex' );
		$( '#principal_seeker_reset_btn' ).css( 'display', 'none' );

		$( '#scanner_resume' ).empty();
		$( '#product_seeker' ).attr( 'disabled', 'true' );

		$( '#boxes_scanned_quantity' ).html( '0' );
		$( '#packs_scanned_quantity' ).html( '0' );
		$( '#pieces_scanned_quantity' ).html( '0' );
		$( '#total_scanned_quantity' ).html( '0' );

		scanned_historic = new Array();
	}

	function validate_barcode( e, barcode = null, permission_box = null, pieces_number = null ){
		var unique_code = "";
		if( e.keyCode != 13 && e != 'intro' ){
			//alert(  );
			return false;
		}
		barcode = ( barcode != null ? barcode : $( '#product_seeker' ).val().trim() );
		if( barcode.length <= 0 ){
			show_emergent( '<p>El buscador no puede ir vacio!</p>', true, false );
			return false;
		}
		var tmp_txt = barcode.split( ' ' );
		
		if( ! validateBarcodeStructure( barcode ) ){
			alert( "El codigo de barras no tiene la estructura correcta, verifica y vuelve a intentar" );
			return false;
		}

		if( boxes_ceils.includes( barcode.toUpperCase() ) ){
			var box_tmp  = boxForm.replace( '$_type_id', 2 );
			show_emergent( box_tmp, false, false );
			return false;
		}

		if( tmp_txt.length == 4 ){
			unique_code = barcode;
		//valida que no sehaya escaneado el codigo unico anteriormente
			if( !validateNoRepeatBarcode( unique_code ) ){
				return false;
			}
			barcode = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				barcode += ( barcode != '' ? ' ' : '' );
				barcode += tmp_txt[i];
			}
		}
//alert( 'is_here' + barcode );
		/*if( value != null ){
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			txt = value;
		}*/
//alert( 'here 1' );

		unique_code = ( unique_code == '' ? barcode : unique_code );
//alert( 'is_here' + unique_code );
	//compara los codigos de barras
		if( ( current_product.codigo_barras_pieza_1 == barcode && current_product.codigo_barras_pieza_1 != '' ) 
			||	( current_product.codigo_barras_pieza_2 == barcode && current_product.codigo_barras_pieza_2 != '' ) 	
			||	( current_product.codigo_barras_pieza_3 == barcode && current_product.codigo_barras_pieza_3 != '' ) ){
			calculate_scanned_pieces( 'Pieza', barcode, 0, 0, 1 );
		}else if( ( current_product.codigo_barras_presentacion_cluces_1 == barcode && current_product.codigo_barras_presentacion_cluces_1 != '' )
			|| ( current_product.codigo_barras_presentacion_cluces_2 == barcode && current_product.codigo_barras_presentacion_cluces_2 != '' ) ){
			if( ( current_product.codigo_barras_presentacion_cluces_1 == unique_code && current_product.codigo_barras_presentacion_cluces_1 != '' )
				|| ( current_product.codigo_barras_presentacion_cluces_2 == unique_code && current_product.codigo_barras_presentacion_cluces_2 ) ){
				show_emergent( `<h4>Este codigo de barras no es unico, verifica y vuelve a intentar</h4>`, true, true );
				return false;
			}
		//registra paquete
			calculate_scanned_pieces( 'Paquete', unique_code, 0, 1, 0 );
		}else if( ( current_product.codigo_barras_caja_1 == barcode && current_product.codigo_barras_caja_1 != '' )
			|| ( current_product.codigo_barras_caja_2 == barcode && current_product.codigo_barras_caja_2 != '' ) ){
//alert( 'here 2' );
			if( permission_box == null && current_product.pieces_per_box > 1 ){
				show_emergent( `<h4>Primero debes de escanear el sello de caja para continuar!</h4>`, true, false );
				return false;
			}
			if( ( current_product.codigo_barras_caja_1 == unique_code && current_product.codigo_barras_caja_1 != '' )
				|| ( current_product.codigo_barras_caja_2 == unique_code && current_product.codigo_barras_caja_2 ) ){
				show_emergent( `<h4>Este codigo de barras no es unico, verifica y vuelve a intentar</h4>`, true, true );
				return false;
			}
//alert( 'here 3' );
		//registra caja
		//alert( unique_code );
			calculate_scanned_pieces( 'Caja', unique_code, 1, 0, 0 );
		}else{
			show_emergent( '<p>El codigo de barras no es valido para este producto!</p>', true, false );
		}
		$( '#product_seeker' ).val( '' );
	}

	function validateNoRepeatBarcode( unique_code ){
	//busca en la pantalla local
		for( var i = 0; i < scanned_historic.length; i ++ ){
			if(  scanned_historic[i]['type'] != null ){
				if( unique_code == scanned_historic[i]['barcode'] ){
					show_emergent( "<p>Este codigo unico ya fue escaneado anteriormente!</p>", true, false );
					$( '#product_seeker' ).val('');
					return false;
				}
			}
		}
	//busca en la base de datos
		var url  = "ajax/inventory.php?inventory_fl=unic_barcode_no_repeat_check&barcode=" + unique_code;
			url += "&warehouse_id=" + current_warehouse;
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			if( response.trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			show_emergent( response, true, true );
			return false;
		}
		return true;
	}

	function getDateTime(){
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
		//}
		date_time = date_time + " " + hour;
		return date_time;
	}

	function getNextProduct(){
		var letter_since = "", number_since = "", letter_to = "", number_to = "";
		if( location_range_since == null ){
			alert( "Es necesario seleccionar el rango de ubicacion DESDE!" );
			location.reload();
			return false;
		}

		if( location_range_to == null ){
			alert( "Es necesario seleccionar el rango de ubicacion HASTA!" );
			location.reload();
			return false;
		}
		for ( i = 0; i < location_range_since.length; i++ ) {
			//alert(location_range_since[i]);
			if( isNaN(location_range_since[i] ) ){
				letter_since += location_range_since[i];	
			}else if ( !isNaN(location_range_since[i] ) ){
				number_since +=  location_range_since[i];
			}
		}	
		letter_since = letter_since.toUpperCase();
		number_since = parseInt( number_since ); 

		for ( i = 0; i < location_range_to.length; i++ ) {
			//alert(location_range_to[i]);
			if( isNaN(location_range_to[i] ) ){
				letter_to += location_range_to[i];	
			}else if ( !isNaN(location_range_to[i] ) ){
				number_to +=  location_range_to[i];
			}
		}	
		letter_to = letter_to.toUpperCase();
		number_to = parseInt( number_to ); 

		var url = "ajax/inventory.php?inventory_fl=getNextProductByRange";
		url += "&range_letter_since=" + letter_since;
		url += "&range_number_since=" + number_since;
		url += "&range_letter_to=" + letter_to;
		url += "&range_number_to=" + number_to;
		url += "&warehouse_id=" + current_warehouse;
	//filtro de familia
		url += "&category=" + $( '#category_combo' ).val();
	//filtro de tipo
		url += "&subcategory=" + $( '#subcategory_combo' ).val();
	//filtro de subtipo
		url += "&subtype=" + $( '#subtype_combo' ).val();
//alert(url); return false;
		var response  = ajaxR( url ).split( '|' );
		//alert( response );
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error" + response );
		}else{
			if( response[1] == 'withouth_rows' ){
				var content = `<div class="row">
					<div class="col-12">Ya no hay regitros en el rango de ubicaciones ${location_range_since} - ${location_range_to}</div>
					<div class="col-3"></div>
					<div class="col-6">
						<button
							class="btn btn-success form-control"
							onclick="location.reload()"
						>
							<i>Aceptar y recargar</i>
						</button>
					</div>
				<div>`;
				show_emergent( content, false, false );
			}else{
				//alert();
				seek_product( 'intro', response[1] );
			}
		}
	}


