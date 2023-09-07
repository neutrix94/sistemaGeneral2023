
	var global_permission_box = 0;
	var global_tmp_barcode = '';
	var global_tmp_unique_barcode = '';
	var global_pieces_quantity = 0;
	var global_was_find_by_name = 0;
//validación de códigos de barras
	function validateBarcode( transfer_product_id, obj, e, permission = null, pieces = null, permission_box = null, barcode = null, is_by_name = 0 ){
		
		if( is_by_name == 1 ){
			global_was_find_by_name = 1;
		}

		var key = e.keyCode;
		var txt = '', unique_code = '';
		if( key != 13 && e != 'enter' ){
			$( '#scanner_products_response' ).css( 'display', 'none' );
			return false;
			
		}

		if( obj == 'tmp' ){
			txt = global_tmp_barcode;
		}else{
			if( $( obj ).val().length <= 0 && global_tmp_barcode == '' ){
				alert( "El código de barras no puede ir vacío!" );
				$( obj ).focus();
				return false;
			}
			txt = $( obj ).val().trim();
		}
		
	//omite codigo de barras si es el caso
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 ){
			global_tmp_unique_barcode = txt;
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}
//alert( txt ); return false;
		global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
		var url = "ajax/db.php?fl=validateBarcode";
		url += "&transfer_detail_id=" + transfer_product_id ;//global_current_transfer
	//	
		if( barcode != null ){
			url += "&barcode=" + barcode;
			global_tmp_barcode = '';
		}else{
			url += "&barcode=" + txt/*( global_permission_box != 0 ? global_tmp_barcode : txt )*/;

		}

		
		if( global_pieces_quantity != 0){
			url += "&pieces_quantity=" + global_pieces_quantity;
		}else if( pieces != null ){
			url += "&pieces_quantity=" + pieces;
		}
		if( permission_box != null || global_permission_box == 1 ){
			//global_permission_box = 1;
			url += "&permission_box=" + permission_box;
			//global_permission_box = 0;//oscar
		}else{
			//global_permission_box = 0;
		}

		if( global_tmp_unique_barcode != '' ){
			url += "&unique_code=" + global_tmp_unique_barcode;
		}
		url += "&was_find_by_name=" + global_was_find_by_name;
//alert( url ); //return false;
		var response =  ajaxR( url );
//alert( response );
		var ax = response.split( '|' );
		if( ax[0] != 'seeker' ){
			$( '.emergent_content_3' ).html( ax[1] );
			$( '.emergent_3' ).css( 'display', 'block' );
		}
		switch( ax[0] ){
			case 'exception_repeat_unic':
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				$( '.barcode_is_repeat_btn' ).focus();
			break; 
			case 'is_box_code':
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				setTimeout( function(){ $( '#tmp_sell_barcode' ).focus(); }, 300 );
			break; 
			case 'message_info':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				global_permission_box = '';
				global_pieces_quantity = 0;
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '.emergent_content' ).focus();

				global_was_find_by_name = 0;
			
			break; 
			case 'manager_password':
				global_tmp_barcode = txt;
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
			break; 
			case 'pieces_form':
		//alert_scann( 'pieces_number_audio' );
				global_tmp_barcode = txt;
				//alert( 'global_barcode :' + global_tmp_barcode );
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				setTimeout( function(){ $( '#pieces_quantity_emergent' ).focus(); }, 300 );
			break; 
			case 'is_not_a_box_code':
				//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#tmp_sell_barcode' ).focus();
			break; 
			case 'amount_exceeded':
				global_tmp_barcode = txt;
				//alert( txt + ' - '  + global_tmp_barcode );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
			break; 

			case 'seeker':
				//was_find_by_name = 1;
				$( '#seeker_response' ).html( ax[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
				return false;
			break;
			case 'box' :
				setBoxesQuantity();
			break;
			case 'ok':
				setPacksQuantity();
				
			break; 
		}
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
	}

	function setPiecesQuantity(){
		var pieces = parseInt( $( '#pieces_quantity_emergent' ).val() );
		var new_pieces = parseInt( $( '#pieces_returned_quantity' ).html().trim() ) + pieces; 
		$( '#pieces_returned_quantity' ).html( new_pieces );
		recalculatePiecesTotal();
		close_emergent_3();
	}

	function setPacksQuantity(){
		if( global_tmp_unique_barcode != '' ){
			$( '#packs_unique_codes' ).append( global_tmp_unique_barcode + "\n" );
			global_tmp_unique_barcode = '';
		}
		var new_packs = parseInt( $( '#packs_returned_quantity' ).html().trim() ) + 1; 
		$( '#packs_returned_quantity' ).html( new_packs );
		recalculatePiecesTotal();
		close_emergent_3();
	}

	function setBoxesQuantity(){
		if( global_tmp_unique_barcode != '' ){
			$( '#boxes_unique_codes' ).append( global_tmp_unique_barcode + "\n" );
			global_tmp_unique_barcode = '';
		}
		var new_boxes = parseInt( $( '#boxes_returned_quantity' ).html().trim() ) + 1; 
		$( '#boxes_returned_quantity' ).html( new_boxes );
		recalculatePiecesTotal();
		close_emergent_3();
	}

	function recalculatePiecesTotal(){
		var pieces_boxes = parseInt( $( '#boxes_returned_quantity' ).html().trim() ) * parseInt( $( '#return_pieces_per_box' ).html().trim() );
		var pieces_packs = parseInt( $( '#packs_returned_quantity' ).html().trim() ) * parseInt( $( '#return_pieces_per_pack' ).html().trim() );
		var pieces = parseInt( $( '#pieces_returned_quantity' ).html().trim() );
		var total = pieces_boxes + pieces_packs + pieces;
		$( '#total_pieces_returned' ).html( total );
		var original_pieces = parseInt( $( '#total_pieces_to_return_origin' ).html().trim() );
		var pending_to_return = original_pieces - total;
		$( '#total_pieces_to_return' ).html( pending_to_return );
		$( '#product_seeker' ).val( '' );
		$( '#product_seeker' ).focus();
	}

	function close_emergent_3(){
		$( '.emergent_content_3' ).html( '' );
		$( '.emergent_3' ).css( 'display', 'none' );
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}

	function saveProductTransferReturn( transfer_product_id, type ){
	//recoleccion de los datos
		var boxes = 0, packs = 0, pieces = 0;
		var ids_to_delete = '';
		var residue = 0;
		var unique_codes = '';
		if( $( '#total_pieces_to_return' ).html().trim() != 0 ){
			if( !confirm( "La cantidad que se se escaneó es diferente de la cantidad que se tenia que regresar, desea continuar ? " ) ){
				return false;
			}
			residue = parseInt( $( '#total_pieces_to_return' ).html().trim() - $( '#total_pieces_returned' ).html().trim() );
		}
	//recolecta los ids
		ids_to_delete += $( '#boxes_ids' ).html().trim();
		ids_to_delete += ( ids_to_delete != '' && $( '#packs_ids' ).html().trim() != '' ? ',' : '' ) + $( '#packs_ids' ).html().trim();
		ids_to_delete += ( ids_to_delete != '' && $( '#pieces_ids' ).html().trim() ? ',' : '' ) + $( '#pieces_ids' ).html().trim();
	//recolecta códigos únicos
		unique_codes += $( '#boxes_unique_codes' ).val().trim().split( "\n" ).join(',');
		unique_codes += ( unique_codes != '' &&   $( '#packs_unique_codes' ).val().trim() != '' ? ',' : '' ) +  $( '#packs_unique_codes' ).val().trim().split( "\n" ).join(',');
		//alert( unique_codes );
		var url = "ajax/db.php?fl=returnTransferProduct&transfer_product_id=" + transfer_product_id;
		url += "&ids_to_delete=" + ids_to_delete + "&unique_codes=" + unique_codes + "&residue=" + residue;
		var response = ajaxR( url );
		if( response.trim() == 'ok' ){
			close_emergent_3();
			before_remove_transfer( global_current_transfer, global_current_validation_block, global_current_reception_block );
		}else{
			alert( response );
		}
		
	}	

