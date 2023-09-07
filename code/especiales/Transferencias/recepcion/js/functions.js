//declaracion de variables
	var global_current_transfers = new Array();
//var global_current_transfers = (7163, 7164, 7165);
	var global_current_validation_blocks = new Array();
//global_current_validation_blocks.push( 129 );
	var global_current_reception_blocks = new Array();
//global_current_reception_blocks.push( 58 );
	var global_new_reception_blocks = '';

	var global_transfer_to_add = new Array();

	var audio_is_playing = false;
	var global_pieces_quantity = 0;
	var element_focus_locked = '';
	var global_was_find_by_name = 0;

	var global_transfers_to_set = new Array(); 
	var global_transfers_block_validation_to_set = new Array(); 
	var global_transfers_block_reception_to_set = new Array(); 
/*implemenatcion Oscar 2023*/
	var current_origin_warehouse = '';
	var current_origin_warehouse = '';
/*fin de cambio Oscar 2023*/

/*implementacion Oscar 2023 para validar que el usuario separa los codigos unico que son de diferente almacen*/
	function mannager_has_separated_unique_code(){
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			alert( "La contraseña es incorrecta!" );
			$( '#manager_password' ).focus();
			return false;
		}else{
			close_emergent();
		}
	}
/*fin de cambio Oscar 2023*/

//mostrar / ocultar vistas del menú
	function show_view( obj, view, is_permission_ok = null ){
	//implementacion Oscar 2023
		if( localStorage.getItem( 'is_principal_reception_session' ) == 0 && is_permission_ok == null ){
			if( view == '.validate_transfers' || view == '.finish_transfers' ){
				alert( "Solo el usuario principal puede accedder a esta pantalla!" );
				return false;
			}
		}
		if( global_current_transfers.length == 0 && view == '.receive_transfers' ){
			alert( "Seleccione la(s) transferencia(s) a Recibir desde el Listado!" );
			return false;
		}
	//
		if( view == '.validate_transfers' && $( '#finish_transfer_permission' ).val() == 0 ){
			alert( "Solo los usuarios autorizados pueden entrar a esta sección de la pantalla!" );
			return false;
		}

		$( '#btn_finish_reception' ).css( 'display', ( view == '.validate_transfers' ? 'block' : 'none' ) );//validate_
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );

		if( view == '.receive_transfers' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
	}
//redireccionamientos
	function redirect( type ){
		switch ( type ){
			case 'home' : 
				if( confirm( "Salir sin Guardar?" ) ){
					location.href="../../../../index.php?";
				}
			break;
		}
	}

	function close_emergent( obj_clean = null, obj_focus = null ){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		if( obj_clean != null ){
			$( obj_clean ).val( '' );
		}
		if( obj_focus != null ){
			$( obj_focus ).focus();
		}

		global_was_find_by_name = 0;
	}
	function close_emergent_2( obj_clean = null, obj_focus = null ){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}
/*lanza emergente para confirmar transferencias por recibir
	function setTransferToReceive( obj_list ){
		transfers_to_receive_info = '<div class="transfer_to_receive_container"><div class="row header_transfer_to_receive">'
			+ '<div class="col-6 text-center">Folio</div>'
			+ '<div class="col-6 text-center">Fecha</div>'
		+ '</div>';

		$( obj_list + " tr" ).each(function ( index ) {
			if( $( '#receive_' + index ).prop( 'checked' ) ){
				transfers_to_receive_info += '<div class="row">';
				$(this).children("td").each(function ( index2 ) {
					if( index2 == 0 ){
						global_current_transfers.push( $( this ).html() );
						transfers_to_receive_info += '<div class="no_visible">' + $( this ).html() + '</div>';
					}else if( index2 <= 2 ){
						transfers_to_receive_info += '<div class="col-6">' + $( this ).html() + '</div>';
					}	
				});
				transfers_to_receive_info += '</div>';
				transfers_to_receive_info += '</div>';
			}
		});
		
		$( '.emergent_content' ).html( 
			'<br /><br />'
			+ '<div style="min-height: 350px;"><p align="center">Las siguentes transferencias serán recibidas :<p>' 
				+ transfers_to_receive_info
				+ '<br />'
				+ '<div class="row">'
					+ '<div class="col-2"></div>'
					+ '<div class="col-8">'
						+ '<button onclick="show_view( \'.mnu_item.source\', \'.receive_transfers\' );close_emergent();" class="btn btn-success form-control">'
							+ 'Confirmar y continuar'
						+ '</button>'
					+'</div>'
					+ '<div class="col-2"></div>'
				+ '</div>'
			+ '</div>' );

		$( '.emergent' ).css( 'display', 'block' );	
		loadLastReceptions();
		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
	}
*/

	function alert_scann( type ){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById(type);
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();

	}


	function setPiecesQuantity( barcode, is_maquiled = null ){
		var global_pieces_quantity = $( '#pieces_quantity_emergent' ).val();
		if( is_maquiled != null ){
			global_pieces_quantity = $( '#maquila_decimal' ).val().trim();
		}
		/*if( barcode == '' || barcode == null ){
			alert( "El código de barras no puede ir vacío" );
		}*/		
		if( global_pieces_quantity <= 0 ){
			alert( "El número de piezas debe ser mayor a Cero!" );
			$( '#pieces_quantity_emergent' ).val( 1 );
			$( '#pieces_quantity_emergent' ).select();
			return false;
		}
		global_tmp_unique_barcode= "";
		global_barcode= "";
		validateBarcode( '#barcode_seeker', 'enter', null, global_pieces_quantity, null, barcode );
	}

var global_permission_box = 0;
var global_tmp_barcode = '';
var global_tmp_unique_barcode = '';
//validación de códigos de barras
	function validateBarcode( obj, e, permission = null, pieces = null, permission_box = null, barcode = null, is_by_name = 0 ){
		
		if( is_by_name == 1 ){
			global_was_find_by_name = 1;
		}

		var key = e.keyCode;
		var txt = '', unique_code = '';
		if( key != 13 && e != 'enter' ){
			$( '#scanner_products_response' ).css( 'display', 'none' );
			return false;
			
		}
		alert_scann( 'audio' );

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
		txt = txt.replace( '  ', ' ' );//reemplaza el dobles espacio
		if( ! validateBarcodeStructure( txt ) ){
			alert( "El codigo de barras no tiene la estructura correcta, verifica y vuelve a intentar" );
			return false;
		}
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 ){
			if( $( '#skip_unique_barcodes' ).val().trim() == 0 ){
				global_tmp_unique_barcode = txt;
			}
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}
//alert( txt ); return false;
		global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
		var url = "ajax/db.php?fl=validateBarcode";
		url += "&transfers=" + global_current_transfers;
		url += "&reception_token=" + localStorage.getItem( 'reception_token' );//implementacion Oscar 2023 para enviar el token de recpcion
	//	
		if( barcode != null ){
	//alert( 'here' );
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
		if( permission != null ){
			url += "&manager_permission=1";
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
		url += "&validations_blocks=" + global_current_validation_blocks;
		url += "&reception_block=" + global_current_reception_blocks;
//alert( url ); //return false;
		var response =  ajaxR( url );
//alert( response );
		var ax = response.split( '|' );
		if( ax[0] != 'seeker' ){
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		//recarga los listados
			receptionResumen( 1 );
			receptionResumen( 2 );
			receptionResumen( 3 );
			receptionResumen( 4 );
			loadLastReceptions();
		}
		switch( ax[0] ){
	/*implementacion Oscar 2023 para recibir respuesta de token invalido*/
			case 'invalid_token' :
				localStorage.removeItem("reception_token");
				localStorage.removeItem("current_reception_block_id");
				localStorage.removeItem( 'is_principal_reception_session' );
				$( '.emergent_content' ).html( ax[1] );
				$( '.emergent' ).css( 'display', 'block' );
	/**/
			case 'exception_repeat_unic':
				alert_scann( 'unic_code_is_repeat' );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				$( '.barcode_is_repeat_btn' ).focus();
			break; 

			case 'exception':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				alert_scann( 'error' );
			break;

			case 'scan_seil_barcode':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				alert_scann( 'scan_seil_barcode' );
			break;
			case 'is_box_code':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				setTimeout( function(){ $( '#tmp_sell_barcode' ).focus(); }, 300 );
				//alert_scann( 'scan_box_barcode' );
			break; 
			case 'message_info':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				global_permission_box = '';
				global_pieces_quantity = 0;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '.emergent_content' ).focus();
				alert_scann( 'error' );

				global_was_find_by_name = 0;
			
			break; 
			case 'manager_password':
				global_tmp_barcode = txt;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
				alert_scann( 'error' );
			break; 
			case 'pieces_form':
				alert_scann( 'pieces_number_audio' );
				global_tmp_barcode = txt;
	//alert( 'global_barcode :' + global_tmp_barcode );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				setTimeout( function(){ $( '#pieces_quantity_emergent' ).focus(); }, 300 );
			break; 
			case 'is_not_a_box_code':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#tmp_sell_barcode' ).focus();
				alert_scann( 'error' );
			break; 
			case 'amount_exceeded':
				global_tmp_barcode = txt;
				//alert( txt + ' - '  + global_tmp_barcode );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
				alert_scann( 'error' );
			break; 

			case 'seeker':
				//was_find_by_name = 1;
				$( '#seeker_response' ).html( ax[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
				return false;
			break;

			case 'ok':
		//	alert();
				receptionResumen( 1 );
				receptionResumen( 2 );
				receptionResumen( 3 );
				receptionResumen( 4 );
				loadLastReceptions();
				getResolutionForms();//implementacion Oscar 2023 para recargar los datos de las resoluciones

				global_was_find_by_name = 0;

				/*$( obj ).val( '' );
				global_pieces_quantity = 0;
				global_tmp_unique_barcode = '';
				global_tmp_barcode = '';
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );

				$( '#scanner_products_response' ).html( ax[1] );
				$( '#scanner_products_response' ).css( 'display', 'block' );

				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );

				$( '#barcode_seeker' ).val( '' );
				$( '#barcode_seeker' ).focus();*/

				$( obj ).val( '' );
				global_pieces_quantity = 0;
				global_tmp_unique_barcode = '';
				global_tmp_barcode = '';
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );

				$( '#scanner_products_response' ).html( ax[1] );
				$( '#scanner_products_response' ).css( 'display', 'block' );
				alert_scann( 'ok' );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );

				$( '#barcode_seeker' ).val( '' );
				$( '#barcode_seeker' ).focus();
				
			break; 
		}
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda

	}

	function lock_and_unlock_focus( obj_btn, obj_field, block = false ){
		if ( ( $( obj_btn ).attr( 'class' ) == 'btn btn-success' && block == false ) || block == 'lock'){
			/*$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			element_focus_locked = '';*/
			$( obj_btn ).attr( 'class' , 'btn btn-danger' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			$( '#barcode_seeker' ).attr( 'disabled', true );
			//$( '#barcode_seeker' ).css( 'background-color', 'red' );
			$( '#barcode_seeker' ).addClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Presionar botón de candado para habilitar' );
			$( '#barcode_seeker' ).val( '' );
			element_focus_locked = obj_field;
			element_focus_locked = '';
		}else{
			//alert();
			$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock"></i>' );
			$( obj_field ).attr( 'onblur', "this.focus();return false;" );
			$( '#barcode_seeker' ).removeAttr( 'disabled' );
			$( '#barcode_seeker' ).removeClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Escanear / Buscar productos' );
			setTimeout( function(){ $( obj_field ).click();$( obj_field ).focus();}, 300);
		}
	}
/*
	function alert_scann(){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById("audio");
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();
	}*/


	function setProductByName( product_id ){
		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
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
			$( '#barcode_seeker' ).val( model_selected.trim() );
			validateBarcode( '#barcode_seeker', 'enter', null, null, null, null, 1 );
			//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
	}

	function save_new_reception_detail( product_id, product_provider_id, box, pack, piece ){
		var url = "ajax/db.php?fl=insertNewProductReception";
		url += "&transfers=" + global_current_transfers;
		url += "&p_id=" + product_id + "&p_p_id=" + product_provider_id;
		url += "&box=" + box + "&pack=" + pack + "&piece=" + piece;
		var response = ajaxR( url );
//alert( url );
	}
//
	function loadLastReceptions(){
		var url = "ajax/db.php?fl=loadLastReceptions&transfers=" + global_current_transfers;
		var response = ajaxR( url );
		$( '#last_received_products' ).html( response );
	}

	function getReceptionProductDetail( product_id, product_provider_id ){
		var url = 'ajax/db.php?fl=getReceptionProductDetail';
		url += '&p_id=' + product_id + "&p_p_id=" + product_provider_id;
		url += '&transfers=' + global_current_transfers;
		var response = ajaxR( url );

		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function receptionResumen( type ){		
		var response_obj = "", counter_obj = "";
		var url = "ajax/db.php?fl=getReceptionResumen&transfers=" + global_current_transfers;
		url += "&type=" + type + "&reception_block_id=" + global_current_reception_blocks;
		var response = ajaxR( url );
		switch ( type ){
			case 1 : 
				response_obj = '#transfer_difference';
				counter_obj = '#transfer_difference_counter';
			break;
			case 2 :
				response_obj = '#transfer_excedent';
				counter_obj = '#transfer_excedent_counter';
			break;
			case 3 : 
				response_obj = '#transfer_dont_correspond';
				counter_obj = '#transfer_dont_correspond_counter';
			break;
			case 4 : 
				response_obj = '#transfer_return';
				counter_obj = '#transfer_return_counter';
			break;
		}
		var aux = response.split( '|' );
		$( counter_obj ).html( aux[0] );
		$( response_obj ).html( aux[1] );
		//$( '#last_received_products' ).html( response );
	}
//resumen del detalle ( resolucion )
	function show_resumen_detail( counter, transfer_product_id, product_id, type, difference ){
		if( $( `#${type}_row_5_${counter}` ).html().trim() != '' ){
			var resp = `<div class="text-center">
							<h5>Este detalle de transferencia ya fue resuelto</h5>
							<br>
							<button 
								type="button"
								class="btn btn-success"
								onclick="close_emergent();"
							>
								<i class="icon-ok-circle">Aceptar</i>
							</button>
							<br>
						</div>`
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		var url = 'ajax/db.php?fl=getProductResolution';
		url += '&t_p=' + transfer_product_id + '&p_id=' + product_id + '&type=' + type;
		url += "&difference=" + difference + "&transfers=" + global_current_transfers;
		url += '&reception_block_id=' + global_current_reception_blocks;
//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	//	alert( response );
	}

	/*function show_excedent_resumen_detail( counter, block_resolution_id, product_id, type, difference ){

	}*/
//show_dont_correspond_resumen_detail

	function change_filter_type( obj ){
		var option_selected = $( obj ).val();
		
		/*switch( type ){
			case '1' ://no resueltos*/
			show_and_hidde_transfer_check( option_selected, 'transfer_difference', 'missing' );	
		//	show_and_hidde_transfer_check( option_selected, 'transfer_excedent',  );	
		//	show_and_hidde_transfer_check( option_selected, 'transfer_return',  );	
	}

	function show_and_hidde_transfer_check( show_type, type, prefix ){
		$( `#${type} tr` ).each( function( index ){
			if( show_type == 0 ){
				$( `#${prefix}_row_${index}` ).css( 'display' , '' );
//	alert( `0, 3 : #${prefix}_row_${index}` );
			}else if( show_type == 1 ){
//alert( `1: #${prefix}_row_${index}` );
				$( `#${prefix}_row_${index}` ).css( 'display' , ( $( `#${prefix}_row_5_${index}` ).html().trim() == '' ? '' : 'none' ) );
			}else if( show_type == 2 ){
//alert( `2: #${prefix}_row_${index}` );
				$( `#${prefix}_row_${index}` ).css( 'display' , ( $( `#${prefix}_row_5_${index}` ).html().trim() != '' ? '' : 'none' ) );
				//$( `#${prefix}_row_${index}` ).css( 'display' , 'block' );				
			}
		});
	}

	function show_unic_codes_pending_to_recive(){
		if( global_current_validation_blocks.length <= 0 ){
			alert( "Es necesario seleccionar que transferencias se van a recibir" );
			show_view( this, '.transfers_list' );
			return false;
		}

		var url = "ajax/db.php?fl=showUnicCodesPendingToRecive";
		url += "&validations_blocks=" + global_current_validation_blocks;
		var response = ajaxR( url );
		//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function receive_unique_code( obj, id ){
		var url = "ajax/db.php?fl=receiveUniqueCode&p_k=" + id;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( "Error : " + response );
		}else{
			alert( "Recibido exitosamente." );
			$( obj ).parent().parent().remove();
		}
	}

//transferencias por corregir
	function getTransfersToCorrection( sucursal_id ){
		var url = "ajax/db.php?fl=getTransfersToCorrection&sucursal_id=" + sucursal_id;
		var response = ajaxR( url );
		//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}


//confirma envio de excedente
	function confirm_exceeds( barcode, quantity, permission_box = null ){
	//valida el password del encargado
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		//obj, e, permission = null, pieces = null, permission_box = null, barcode = null, is_by_name = 0
		//obj, e, permission = null, pieces = null, permission_box = null
//alert( global_tmp_barcode );//( global_permission_box != null ? 1 : null )
		validateBarcode( 'tmp', 'enter', 1, quantity, permission_box, barcode );
	}//( permission_box != null ? 'tmp' : '#barcode_seeker' )

	/*Deshanilitado por Oscar 2023
	function confirm_product_was_separated(){
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		close_emergent();
		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
	}*/
/*Implementacion Oscar 2023 para tratamiento de codigos unicos que no corresponden*/
	function confirm_product_was_separated( type, barcode, unique_code, is_a_box, flag, resolution_row_id = null ){/* pieces_quantity = 0,*/
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );

		if( response != 'ok' ){
			alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		if( type == 1 ){
			if( $( '#unique_code_resolution_field' ).val().toLowerCase() != 'resolucion' ){
				alert( "Escribe la palabra 'resolucion' para continuar." );
				$( '#unique_code_resolution_field' ).select();
				return false;
			}else{
				close_emergent();
			}
		}else if( type == 2 ){
			if( resolution_row_id == null ){	
				alert( "El codigo unico que se pretende eliminar no existe!" );
				return false;
			}
			if( $( '#unique_code_return_field' ).val().toLowerCase() != 'cancelar' ){
				alert( "Escribe la palabra 'cancelar' para continuar." );
				$( '#unique_code_return_field' ).select();
				return false;
			}
			cancel_unique_code_resolution( resolution_row_id, unique_code );
			return false;
		}/*else{
			alert( "Opcion incorrecta!" );
			return false;
		}*/
		close_emergent();
		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
	}
//implementacion Oscar 2023 para poder regresar un codigo unico
	function cancel_unique_code_resolution( resolution_row_id, unique_code ){
		var url = "ajax/db.php?fl=remove_resolution_unique_code&resolution_row_id=" + resolution_row_id;
		url += "&unique_code=" + unique_code;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}else{
			$( '.emergent_content' ).html( `<h4>${response[1]}</h4>
				<br>
				<button
					class="btn btn-success form-control"
					onclick="close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>` );
			$( '.emergent' ).css( 'display', 'block' );
			setTimeout( function(){
			/*implementacion Oscar 2023 para recargar la vista de listados de productos seccion verificar*/
				receptionResumen( 1 );
				receptionResumen( 2 );
				receptionResumen( 3 );
				receptionResumen( 4 );
				getResolutionForms();
			/*fin de cambio Oscar 2023*/
			}, 100);
		}
	}
/*fin de cambio Oscar 2023*/
	
	function getFinishPermission(){
		var url = "ajax/db.php?fl=getFinishPermission&reception_token=" + localStorage.getItem( 'reception_token' );
		url += "&reception_block_id=" + global_current_reception_blocks;
//alert( url );
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			alert_scann( 'error' );
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		return true;
	}

	function validateReceptionIsComplete( permission = null ){
	//implementacion Oscar 2023 para recargar listado antes de verificar transferencia en transito
		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
		receptionResumen( 4 );
	//fin de cambio Oscar 2023	
		if( getFinishPermission() == false ){
			return false;
		}
		if( permission != null ){
			var pss = $( '#manager_password' ).val();
			if( pss.length <= 0 ){
				alert( "La contraseña del encargado no puede ir vacía!" );
				$( '#manager_password' ).focus();
				return false;
			}
			var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
			var response = ajaxR( url );
			if( response != 'ok' ){
				alert( "La contraseña del encargado es incorrecta, verifica y vuelve a intentar!" );
				return false;
			}
			return true;
		}
		
		var resp = true;
	//verifica en listado de faltante
		$( '#transfer_difference' ).each( function( index ){
			if( document.getElementById( 'missing_row_5_' + index ) ){
				if( $( '#missing_row_5_' + index ).html().trim() == '' ) {
					if( permission == null ){
						resp = false;
						alert_scann( 'error' );
		//alert( "Hay registros sin recibir, verifica y vuelve a intentar" );
						var aux = `<div class="row">
									<div class="col-2"></div>
									<div class="col-8 text-center">
										<h5>Aun hay productos pendientes de recibir, si vas a poner EN TRÁNSITO la transferencia 
										pide al encargado que ingrese su contraseña para continuar : </h5>
										<br>
										<input type="password" id="manager_password" class="form-control">
										<br>
										<button
											type="button"
											class="btn btn-success"
											onclick="finish_transfers_reception( 1 );"
										>
											<i class="icon-ok-circle">Aceptar y continuar</i>
										</button>
										<br>
										<br>
										<button
											type="button"
											class="btn btn-danger"
											onclick="close_emergent();"
										>
											<i class="icon-cancel-circled">Cancelar y cerrar</i>
										</button>
										<br>
										<br>
									</div>
								</div>`;
						$( '.emergent_content' ).html( aux );
						$( '.emergent' ).css( 'display', 'block' );
						$( '#manager_password' ).focus();
						return false;
					}
				}
			}
		});
		if( resp != true ){
			return resp;
		}
		return true;

	/*verifica en listado de excedente
		$( '#transfer_excedent' ).each( function( index ){
			if( document.getElementById( 'excedent_row_5_' + index ) ){
				if( $( '#excedent_row_5_' + index ).html().trim() == '' ) {
					resp = false;
					alert( "Hay registros por resolver, verifica y vuelve a intentar" );
					return false;
				}
			}
		});
		if( resp != true ){
			return resp;
		}*/

	/*verifica en listado de no corresponden
		$( '#transfer_dont_correspond' ).each( function( index ){
			if( document.getElementById( 'does_not_correspond_row_5_' + index ) ){
				if( $( '#does_not_correspond_row_5_' + index ).html().trim() == '' ) {
					resp = false;
					alert( "Hay registros por resolver, verifica y vuelve a intentar" );
					return false;
				}
			}
		});
		return resp;*/
	}

	function validate_transfers_are_completed(){
		var url = 'ajax/db.php?fl=validate_transfers_are_completed&transfers=' + global_current_transfers;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( response );
			return false;
		}else{
			if( response[1] != 'ok' ){
				$( '.emergent_content' ).html( response[1] );
				$( '.emergent' ).css( 'display', 'block' );
				return false;
			}
		}
		return true;
	}

/**/
	function validate_devices_sessions(){
		var url = "ajax/db.php?fl=validate_devices_sessions&current_block=" + localStorage.getItem( 'current_reception_block_id' );
		url += "&reception_token=" + localStorage.getItem( 'reception_token' );
		var response = ajaxR( url );
		if( response != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}else{
			return true;
		}
		
	}
/**/

	function finish_transfers_reception( permission = null ){
//alert( 'here' );
		if( global_current_transfers.length == 0 ){
			alert( "Para finalizar la recepción es necesario seleccionar la(s) Transferencia(s)" );
			show_view( this, '.transfers_list');
			return false;
		}
		if( ! validate_devices_sessions() ){
			return false;
		}
/*implementacion Oscar 2023 para validar que las transferencias este surtidas y validadas*/
		if( ! validate_transfers_are_completed() ){
			return false
		}
/*fin de cambio Osscar 2023*/

		if( ! validateReceptionIsComplete( permission ) ){
			return false;
		}
		/* deshabilitado por Oscar 2013 var url = "ajax/db.php?fl=finishTransfersReception&transfers=" + global_current_transfers;
		url += "&reception_block_id=" + global_current_reception_blocks;*/
		var url = "ajax/db.php?fl=transfers_in_transit&transfers_ids=" + global_current_transfers;
		url += "&reception_token=" + localStorage.getItem( 'reception_token' );
		url += "&reception_block_id=" + global_current_reception_blocks;
//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			//alert( "Error : " + response );
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'none' );
			return false;
		}
		localStorage.removeItem( 'reception_token' );
		localStorage.removeItem( 'current_reception_block_id' );
		localStorage.removeItem( 'is_principal_reception_session' );
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function getResolutionForms(){
		var url = "ajax/db.php?fl=getResumeCounterForms&reception_block_id=" + global_current_reception_blocks + "&type=1";
		var response = ajaxR( url ).split( '|' );
//console.log( response );
		if( response[0] == 'ok' ){
			$( '#missing_and_excedent_counter_capture' ).empty();
			$( '#missing_and_excedent_counter_capture' ).append( response[1] );
		}else{
			alert( "Error al recargar vista de pendientes de recibir : " + response );
		}

	}


/*	function getResolutionForms(){
		var url = "ajax/db.php?fl=getResumeCounterForms&reception_block_id=" + global_current_reception_blocks + "&type=1";
		var response = ajaxR( url ).split( '|' );
//console.log( response );
		if( response[0] == 'ok' ){
			$( '#missing_and_excedent_counter_capture' ).empty();
			$( '#missing_and_excedent_counter_capture' ).append( response[1] );
		}else{
			alert( "Error al recargar vista de pendientes de recibir : " + response );
		}

*		url = "ajax/db.php?fl=getResumeCounterForms&reception_block_id=" + global_current_reception_blocks + "&type=2";
		response = ajaxR( url ).split( '|' );
//alert(response);
		if( response[0] == 'ok' ){
			$( '#does_not_correspond_counter_capture' ).empty();
			$( '#does_not_correspond_counter_capture' ).append( response[1] );
		}else{
			alert( "Error al recargar vista de excedentes : " + response );
		}
*
		/*url = "ajax/db.php?fl=getResumeCounterForms&reception_block_id=" + global_current_reception_blocks + "&type=2";
		response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			$( '#does_not_correspond_counter_capture' ).empty();
			$( '#does_not_correspond_counter_capture' ).append( response[1] );
		}else{
			alert( "Error al recargar vista de productos que no corresponden : " + response );
		}*

	}*/


	function setResolutionBlock( resolution_block_id ){
		global_current_transfers = new Array();
		global_current_validation_blocks = new Array();
		global_current_reception_blocks = new Array();
		global_transfer_to_add = new Array();
		global_current_reception_blocks = resolution_block_id;
		getResolutionForms();
		show_view( '', '.finish_transfers', 1 );
		//$( '.mnu_item.validate' ).click();
	}

/*Implementacion Oscar 2023 para impresion de ticket de recepcion de Transferncia*/
	function print_block_ticket( block_id ){
		var url = "../ticket_transferencia/reception_barcode_ticket.php?block_id=" + block_id;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response[0] );
		}else{
			alert( "El ticket fue generado exitosamente." );
		}
	}
/*fin de cambio Oscar 2023*/
	
	function close_device_reception_session( reception_token, permission = false ){
		var content = '';
		if( permission == false ){
			content = `<div class="row">
				<h5>Escribe la palabra "FINALIZAR" para finalizar la sesion ${reception_token}</h5>
				<div class="col-4"></div>
				<div class="col-4">
					<input type="text" id="close_session_input_tmp" class="form-control">
					<br>
					<button 
						type="button"
						class="btn btn-success form-control"
						onclick="close_device_reception_session( '${reception_token}', true );"
					>
						<i class="icon-ok-circle">Finalizar</i>
					</button>
					<br>
					<button 
						type="button"
						class="btn btn-danger form-control"
						onclick="close_emergent_2();"
					>
						<i class="icon-cancel-circled">Cancelar</i>
					</button>
				</div>
			</div>`;
		}else{
			if( $('#close_session_input_tmp' ).val().toUpperCase() != "FINALIZAR" ){
				alert( "Escribe la palabra FINALIZAR para continuar!" );
				return false;
			}
			var url = "ajax/db.php?fl=close_device_reception_session&reception_token=" + reception_token;
			url += "&reception_block_id=" + localStorage.getItem( 'current_reception_block_id' );

			var response = ajaxR( url ).split( '|' );
			if( response[0] != 'ok' ){
				alert( "Error : \n" + response );
				return false;
			}
			content = response[1];
		}
		validate_devices_sessions();
		$( '.emergent_content_2' ).html( content );
		$( '.emergent_2' ).css( 'display', 'block' );
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

