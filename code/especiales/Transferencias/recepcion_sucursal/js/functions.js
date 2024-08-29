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

//mostrar / ocultar vistas del menú
	function show_view( obj, view ){
		if( global_current_transfers.length == 0 
			&& ( view == '.validate_transfers' || view == '.finish_transfers' )  ){
			alert( "Seleccione la(s) transferencia(s) a Recibir desde el Listado!" );
			return false;
		}
	//oscar 2023
		if( view == '.finish_transfers_since_button' ){
			if( !validate_transfers_were_finished() ){
				return false;
			}else{
				view = '.finish_reception';
			}
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
		}else if ( view == '.finish_reception' ){
			setResolutionBlock( global_current_reception_blocks, false, false );
			getResolutionForms();
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

	function validate_transfers_were_finished(){
		var url = "ajax/db.php?fl=validate_transfers_were_finished&transfers=" + global_current_transfers;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			return false;
		}
		return true;
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



	function loadLastReceptions(){
		var url = "ajax/db.php?fl=loadLastReceptions&transfers=" + global_current_transfers;
		var response = ajaxR( url );
		$( '#last_received_products' ).html( response );
	}


	function receptionResumen( type ){		
		var response_obj = "", counter_obj = "";
		var url = "ajax/db.php?fl=getReceptionResumen&transfers=" + global_current_transfers;
		url += "&type=" + type + "&reception_block_id=" + global_current_reception_blocks;
		var response = ajaxR( url ).split( '|' );
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
				response_obj = '#products_ok_list';
				counter_obj = '#products_ok_list_counter';
			break;
		}

		$( counter_obj ).empty();
		$( response_obj ).empty();
//		var aux = response;
		$( counter_obj ).html( response[0] );
		$( response_obj ).html( response[1] );
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

	function validateReceptionIsComplete( permission = null ){
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
		//alert( "Hay registros sin recibir, verifica y vuelve a intentar" );
						var aux = `<div class="row">
									<div class="col-2"></div>
									<div class="col-8">
										<h5>Aun hay productos pendientes de recibir, si vas a finalizar la transferncia 
										pide al encargado su contraseña para continuar : </h5>
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
									</div>
								</div>`;
						$( '.emergent_content' ).html( aux );
						$( '.emergent' ).css( 'display', 'block' );
						return false;
					}
				}
			}
		});
		if( resp != true ){
			return resp;
		}
		return true;
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
		var url = "ajax/db.php?fl=finishTransfersReception&transfers=" + global_current_transfers;
		url += "&reception_block_id=" + global_current_reception_blocks;
//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
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
/*
		url = "ajax/db.php?fl=getResumeCounterForms&reception_block_id=" + global_current_reception_blocks + "&type=2";
		response = ajaxR( url ).split( '|' );
console.log( response );
		if( response[0] == 'ok' ){
			$( '#does_not_correspond_counter_capture' ).empty();
			$( '#does_not_correspond_counter_capture' ).append( response[1] );
		}else{
			alert( "Error al recargar vista de excedentes : " + response );
		}*/
	}


	function getResolutionBlocks(){
		var url = "ajax/db.php?fl=getBlocksInResolution";
		var response = ajaxR( url ).split( '|' );
		if( response[0] == 'ok' ){
			$( '#blocks_resolution_list' ).empty(  );
			$( '#blocks_resolution_list' ).append( response[1] );
		}else{
			alert( "Error : " + response );
		}
	}

	function setResolutionBlock( resolution_block_id, finish_transfers = false, show_transfer = false ){
		var url = "ajax/db.php?fl=getDataByBlock&reception_block_id=" + resolution_block_id;
		var response = ajaxR( url ).split( '|' );
		alert( "1" + response );
		if( response[0] != 'ok' ){
			alert( "Error : " + response );
			return false;
		}
		global_current_reception_blocks = resolution_block_id;
		global_current_validation_blocks = response[1].split( ',' );
		global_current_transfers = response[2].split( ',' );
		getResolutionForms();

	//finalizar la transferencia
		if( finish_transfers == true && show_transfer == false ){
			alert( "finish_transfers" );return false;
			finish_transfers_();
			getResolutionBlocks();
		}
	//visualizar detalle de la transferencia
		if( show_transfer == true || finish_transfers == true ){
			show_view( this, '.validate_transfers');
		}else{
			show_view( '', '.finish_transfers');
		}
		alert('pasa');
		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
		getResolutionForms();
	}

	function finish_transfers_(){
		var url = "ajax/db.php?fl=finishTransfersReception&transfers=" + global_current_transfers;
		url += "&reception_block_id=" + global_current_reception_blocks;
		
		//alert(url);return false;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : " + response );
			return false;
		}
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
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

