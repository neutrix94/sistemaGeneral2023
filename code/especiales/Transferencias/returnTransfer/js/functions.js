var current_product = null;
	function seek_product( e, value = null ){
		var txt;
		if( e != 'intro' && e.keyCode != 13 ){
			return false;
		}
	//obtiene valor del buscador
		txt = $( "#principal_seeker" ).val().trim();
		if( txt.length == 0 && value == null ){
			alert( "El buscador no puede ir vacio!" );
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

		var response = ajaxR( 'ajax/db.php?fl=seekProduct&key=' + txt );
		var aux = response.split( '|' );
		//alert( response );
		$( "#principal_seeker" ).val( '' );
		switch( aux[0] ){
			case 'seeker' :
				$( '#seeker_response' ).html( aux[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
			break;
			case 'ok' :
				current_product = JSON.parse( aux[1] );
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
				alert( "Producto no econtrado!" + aux[1] ); 
				clean_current_product();
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
			alert( "El codigo de barras no puede ir vacio!" );
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
					alert( "Las piezas escaneadas superan la cantidad capturada al inicio, verifica el conteo y vuelve a capturar este producto!" );
					$( "#seeker_barcodes" ).val( '' );
					clean_current_product();
					return false;
				}	
		  	}
			$( '#scans_counter' ).val( aux + 1 );
			calculate_barcodes_quantity();	
			$( "#seeker_barcodes" ).val( '' );
		}else{
			alert( "Error : Este producto no corresponde a los que se estan escaneando, separalo de los demás" );
			$( "#seeker_barcodes" ).focus();
		}
	}

	function setCurrentProduct( current_product ){
		$( '#product_description_header' ).html( current_product.product_name );//product_name
		$( '#principal_seeker' ).attr( 'disabled', true );
		$( '#principal_seeker_search_btn' ).css( 'display', 'none' );

		//alert( current_product.provider_clue );
		$( '#product_model' ).val( current_product.provider_clue );//provider_clue
		$( '#pieces_per_box' ).val( current_product.pieces_per_box );//pieces_per_box

		$( '#pieces_number' ).focus();
		if( current_product.is_maquiled == 1 || current_product.is_maquiled == '1' ){
			getMaquileForm();
		}
		if( current_product.is_without_tag == 1 || current_product.is_without_tag == '1' ){
			getPiecesForm();
		}
		getImages( current_product );
	}

	function getMaquileForm(){
		var response = ajaxR( '../../plugins/maquila.php?fl_maquile=getMaquileForm&product_id=' + current_product.product_id + "&function=setProductPieces();" );	
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).focus();
	}

	function getPiecesForm(){
		var response = ajaxR( '../../plugins/product_has_not_tag.php?fl_special=getMaquileForm&product_id=' + current_product.product_id + "&function=setProductPiecesSpecial();" );	
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).focus();
	}
	function setProductPiecesSpecial(){
		$( '#pieces_number' ).val( $( '#special_tmp_input' ).val() );
		$( '#pieces_number_set_btn' ).click();
		$( '#scans_counter' ).val( $( '#special_tmp_input' ).val() );
		calculate_barcodes_quantity();
		setTimeout( function (){
			close_emergent();
		}, 100);
	}

	function setProductPieces(){
		//$( '#maquila_decimal' ).val();
		$( '#pieces_number' ).val( $( '#maquila_decimal' ).val() );
		$( '#pieces_number_set_btn' ).click();
		$( '#scans_counter' ).val( $( '#maquila_decimal' ).val() );
		calculate_barcodes_quantity();
		setTimeout( function (){
			close_emergent();
		}, 100);
	}

	function getImages( current_product ){
		var response = ajaxR( 'ajax/db.php?fl=getImages&product_provider_id=' + current_product.product_provider_id ).split( '|' );
//alert( response );
		
		$( '#packs_details' ).empty();
		$( '#packs_details' ).append( response[0] );
		$( '#pack_type_description' ).html( response[1] );
		$( '#meassures_container' ).html( response[2] );
	}


	function clean_current_product(){
		current_product = null;
		$( '#principal_seeker' ).val( '' );//product_name
		$( '#principal_seeker' ).removeAttr( 'disabled' );

		$( '#product_model' ).val( '' );
		$( '#pieces_per_box' ).val( '' );
		$( '#pieces_number' ).val( '' );
		$( '#pieces_number' ).removeAttr( 'disabled' );
		$( '#scans_counter' ).val( '' );

		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display', 'none' );

		$( '#boxes_quantity' ).val( '' );
		$( '#packs_quantity' ).val( '' );
		$( '#pieces_quantity' ).val( '' );

		$( '#product_description_header' ).html( '' );
		$( '#principal_seeker_search_btn' ).css( 'display', 'flex' );
		$( '#pieces_number_set_btn' ).css( 'display', 'flex' );

		$( '#packs_details' ).empty();
		$( '#pack_type_description' ).html( '' );
	}

	function setPiecesNumber(){
		$( '#pieces_number' ).attr( 'disabled', true );
		$( '#seeker_barcodes' ).focus();

		$( '#pieces_number_set_btn' ).css( 'display', 'none' );
	}

	function printTags(){
		if( current_product == null ){
			alert( "Primero selecciona un producto - modelo!" ); 
			$( "#seeker_barcodes" ).val().trim();
			return false;
		}
		if( $( '#pieces_number' ).val() == "" || $( '#pieces_number' ).val() <= 0 ){
			alert( "Es necesario capturar la cantidad inicial" );
			$( '#pieces_number' ).focus();
			return false;
		}
		if( $( '#edition_permission' ).val() == 0  ){
			if( parseInt( $( '#pieces_number' ).val() ) != parseInt( $( '#scans_counter' ).val() ) ){
				alert( "Las piezas escaneadas son diferentes de las piezas capturadas inicialmente, escanea las piezas capturadas inicialmente para continuar" );
				//clean_current_product();
				return false;
			}	
		}
		//var scanned_counter = ;//parseInt( $( '#scans_counter' ).val() );
		var boxes_number = $( '#boxes_quantity' ).val();
		var packs_number = $( '#packs_quantity' ).val();//parseInt( scanned_counter / parseInt( current_product.pieces_per_pack ) );
		var pieces_number = $( '#pieces_quantity' ).val();//scanned_counter % parseInt( current_product.pieces_per_pack );
		var url = "ajax/db.php?fl=makeBarcodes&product_provider_id=" + current_product.product_provider_id;
		var is_valid = 0;
	//cajas
		if( boxes_number > 0 ){
			url += "&boxes_number=" + boxes_number; 
			is_valid = 1;
		}
	//paquetes
		if( packs_number > 0 ){
			url += "&packs_number=" + packs_number; 
			is_valid = 1;
		}
	//piezas
		if( pieces_number > 0 ){
			url += "&pieces_number=" + pieces_number;
			is_valid = 1;
		}
		if( is_valid == 0 ){
			alert( "Es necesario escanear los productos para continuar!" );
			return false;
		}
	//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
		//
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			//alert( response );
		}else{
			alert( response[1] );
			clean_current_product();
		}
	}

	function calculate_barcodes_quantity(){
		var scanned_counter,
			boxes_number,
			packs_number,
			pieces_number;
			
		scanned_counter = parseFloat( $( '#scans_counter' ).val() );
		if( $( '#edition_permission' ).val() == 1 ){
			boxes_number =parseInt( current_product.pieces_per_box ) == 0 ? 0 : parseInt( scanned_counter / parseInt( current_product.pieces_per_box ) );
			packs_number = parseInt( current_product.pieces_per_pack ) == 0 ? 0 : parseInt( scanned_counter / parseInt( current_product.pieces_per_pack ) );
			pieces_number = (current_product.pieces_per_pack == 0 ? 
									(current_product.pieces_per_box == 0 ? 
										scanned_counter : 
										parseFloat( scanned_counter % parseFloat( current_product.pieces_per_box ) ).toFixed( 2 )
									) :
									parseFloat( scanned_counter % parseFloat( current_product.pieces_per_pack ) ).toFixed( 2 )
								);
		}else{
			if( current_product.pieces_per_pack > 0 ){
				boxes_number = 0;
				packs_number = parseInt( scanned_counter / parseInt( current_product.pieces_per_pack ) );
				pieces_number = parseFloat( scanned_counter % parseFloat( current_product.pieces_per_pack ) ).toFixed( 2 );
			}else{
				packs_number = 0;
				boxes_number = parseInt( scanned_counter / parseInt( current_product.pieces_per_box ) );
				pieces_number = parseFloat( scanned_counter % parseFloat( current_product.pieces_per_box ) ).toFixed( 2 );
			}
		}
		//var url = "ajax/db.php?fl=makeBarcodes&product_provider_id=" + current_product.product_provider_id;
		//alert( scanned_counter + ' / ' + current_product.pieces_per_box  + ' / ' + current_product.pieces_per_pack );
		$( '#boxes_quantity' ).val( boxes_number );
		$( '#packs_quantity' ).val( packs_number );
		$( '#pieces_quantity' ).val( pieces_number );
		//alert();
	}

	function setProductByName( product_id ){
		//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/db.php?fl=getOptionsByProductId&product_id=" + product_id;
		var response = ajaxR( url );
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

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function redirect( type ){
		switch( type ){
			case 'home' :
				if( confirm( "Regresar al panel del sistema?" ) ){
					location.href= '../../../../index.php?';
				}
			break;
		}
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

