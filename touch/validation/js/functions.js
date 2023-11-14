var global_current_ticket;
var global_ticket_has_return = 0;
var global_view = '';

	document.addEventListener('keydown', (event) => {
		var keyValue = event.keyCode;

		if( keyValue == 13 && global_view == '.check_ticket_detail' 
			&& document.activeElement.id != 'return_seeker' 
			&& document.activeElement.id != 'product_barcode_seeker'
			&& document.activeElement.id != 'product_barcode_seeker_pieces'
		){//!= element_focus_locked && element_focus_locked !
			var resp = "<h5 class=\"orange\">No estas posicionado en el campo del código de barras!</h5>";
			//alert( '' ) ;
			alert_scann( 'error' );
			
			resp += '<div class="row"><div class="col-2"></div><div class="col-8">';
			resp += '<button class="btn btn-warning form-control" onclick=\"close_emergent();';
			//if( element_focus_locked == '' && global_view == '.transfers_products' ){
				//$( '#barcode_seeker' ).focus();
				//$( '#barcode_seeker_lock_btn' ).click();
				//resp += "lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );";
			//}else{//$( element_focus_locked ).focus();
			//	var aux = element_focus_locked.replace( '#', '' );
			//	resp += "document.getElementById( '" + aux + "' ).focus();";
			//}

			resp += '\"><i class=\"icon-ok-circle\">Aceptar</i></button>';
			resp += '</div></div>';
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			//return false;
		}
	}, false);

//mostrar / ocultar vistas del menú
	function show_view( obj, view ){
		if( view == '.check_ticket_detail' ){
			//alert( localStorage.getItem( 'current_ticket' ) );
			if( localStorage.getItem( 'current_ticket' ) == 'null'
				|| localStorage.getItem( 'current_ticket' ) == null 
				|| localStorage.getItem( 'current_ticket' ) == 0 ){
				alert( "Primero selecciona un ticket de venta!" );
				return false;
			}else{
				load_ticketDetail();
			}
		}else{
			localStorage.setItem( 'current_ticket', null );
		}
		global_view = view;
		$( '#validation_finish_btn' ).css( 'display', ( view == '.check_ticket_detail' ? 'block' : 'none' ) );
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
					location.href="../../";
				}
			break;
		}
	}

	function close_emergent( obj_clean = null, obj_focus = null){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		if( obj_clean != null ){
			$( obj_clean ).val( '' );
		}
		if( obj_focus != null ){
			$( obj_focus ).focus();
		}
	}

	function seekTicketBarcode( e, obj, type, barcode = null, pieces_quantity = null, 
		sale_detail_id = null, was_found_by_name = null ){
		if( e != -1 ){
			if( e.keyCode != 13 && e != 'enter' ){
				$( '#scanner_response' ).html( '' );
				$( '#scanner_response' ).css( 'display', 'none' );
				return false;
			}
		}
//alert();
		var txt = $( obj ).val();
		if( barcode != null ){
			txt = barcode.trim();
		}
		if( txt.length == 0 || txt == '' ){
			alert( "El código de barras no puede ir vacío!" );
			$( obj ).focus();
			return false;
		}
	//omite codigo de barras si es el caso
		txt = txt.replace( '  ', ' ' );//reemplaza el dobles espacio
		if( ! validateBarcodeStructure( txt ) ){
			alert( "El codigo de barras no tiene la estructura correcta, verifica y vuelve a intentar" );
			return false;
		}
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 && ( ( tmp_txt[1].includes( 'PQ' ) )||( tmp_txt[1] ).includes( 'CJ' ) ) ){
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}
/*implementacion Oscar 2023/10/11 para decodificar el codigo de barras en formato64 
deshabilitado por oscar 2023/10/17 ( habilitar para proceso de pagos/validacion )*/

		if( type == 'seekTicketBarcode' ){
			txt = txt.replaceAll( '?', '=' );
			txt = txt.replaceAll( '¿', '=' );
			txt = atob( txt );
		}
/*fin de cambio Oscar 2023/10/11*/

		alert_scann( 'audio' ); 
		$( obj ).val( '' );
		$( obj ).focus();
		var url = 'ajax/db.php?fl=' + type + '&barcode=' + txt;		
		if( type == 'seekProductBarcode' ){
			url += '&ticket_id=' + localStorage.getItem( 'current_ticket' );
			if( pieces_quantity != null ){
				url += "&pieces_quantity=" + pieces_quantity;
			}
		}
		if( $( obj ).attr( 'id' ) == 'product_barcode_seeker_pieces' ){
			url += "&pieces_form=1";
		}
		url += "&sale_detail_id=" + sale_detail_id;
		url += "&found_by_name=" + was_found_by_name;
//alert( url );//return false;
		var response = ajaxR( url );
//alert( response );
		var aux = response.trim().trim().split( '|' );
		if( type == 'seekTicketBarcode' ){
			if( aux[0] == 'ok' ){
				//setTicket( aux[1] );
				getTicketInfo( aux[1] );
				$( '.emergent' ).focus();

			}else{
				$( '.emergent_content' ).html( response );
				$( '.emergent' ).css( 'display', 'block' );
				$( '.emergent' ).focus();
			}
		}else if( type == 'seekProductBarcode'){
			if( aux[0] == 'ok' ){
				load_ticketDetail();//recarga el detalle
				$( '#scanner_response' ).html( aux[1] );
				$( '#scanner_response' ).css( 'display', 'block' );
				
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );
				
				alert_scann( 'ok' );
				setTimeout( function ( ) {
					$( '#scanner_response' ).html( '' );
					$( '#scanner_response' ).css( 'display', 'none' );
				},1000);
			}else if( aux[0] == 'pieces_form' ){
				$( '.emergent_content' ).html( aux[1] );
				$( '.emergent' ).css( 'display', 'block' );
				$( '#pieces_quantity_tmp' ).focus();
			}else if( aux[0] == 'separate_this_product' ){
				$( '.emergent_content' ).html( aux[1] );
				$( '.emergent' ).css( 'display', 'block' );
				$( '.emergent_content' ).focus();
			}else if( aux[0] == 'seeker' ){
				/*var id = $( obj ).attr( 'id' );
				$( `#${id}_response` ).html( aux[1] );
				$( `#${id}_response` ).css( 'display', 'block' );*/
				//alert(aux[1] + '!= ' + aux[2] );
				if( aux[1] != '' && aux[2] != '' ){
					//alert( aux[1] + " , " + aux[2] );
					setProductByName( aux[1], aux[2], 1 );
					$( '.emergent' ).focus();
				}else{
					alert( "el producto no corresponse a esta nota de venta, verifica y vuelve a intentar!" );
				}
			}else{
				$( '.emergent_content' ).html( response );
				$( '.emergent' ).css( 'display', 'block' );
				$( '.emergent' ).focus();
			}
		}

	}

	function setPiecesQuanity( barcode, ticket_id, sale_detail_id, was_found_by_name ){
		var pieces = $( '#pieces_quantity_tmp' ).val();
		if( pieces <= 0 ){
			alert( "El valor del número de piezas no pude ser menor a 1!" );
			return false;
		}else{
			seekTicketBarcode( -1, '#product_barcode_seeker_pieces', 'seekProductBarcode', barcode, pieces, sale_detail_id, was_found_by_name );
//e, obj, type, barcode = null, pieces_quantity = null
		}
	}

	function focus_again( obj ){
		//alert();
		setTimeout( function (){
				document.getElementById( 'barcode_seeker' ).focus();
				document.getElementById( 'barcode_seeker' ).select();
			}, 100
		);
		return false;
	}

	function getTicketInfo( ticket_id ){
		var url = 'ajax/db.php?fl=getTicketInfo&ticket_id=' + ticket_id;
		var response = ajaxR( url );
		//console.log( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setTicket( ticket_id, was_payed ){
		localStorage.setItem( 'current_ticket', ticket_id );
		global_current_ticket = ticket_id;
	//carga detalle del ticket
		$( '#check_ticket_detail' ).click();
		getProductsCatalogue();
		load_ticketDetail();
		if( was_payed == 0 ){//deshabilita la edicion de nota si es apartado
			$( '#sale_edition_btn' ).attr( 'disabled', true );
		}
		$( '#sale_reset_btn' ).attr( 'onclick', 'reset_validation( ' + ticket_id + ' )' );
	}

	function load_ticketDetail(){
	//piezas por validar
		var url = "ajax/db.php?fl=getTicketDetail&p_k=" + localStorage.getItem( 'current_ticket' );
		url += "&type=pending";
//	alert( url );
		var response = ajaxR( url );
		$( '#pending_validation' ).html( response );
	//piezas validadas
		var url = "ajax/db.php?fl=getTicketDetail&p_k=" + localStorage.getItem( 'current_ticket' );
		url += "&type=validated";
//alert( url );
		var response = ajaxR( url );
		$( '#validated' ).html( response );
	}

	function finish_validation(){
		if( $( '#pending_validation tr' ).length > 0 ){
			var resp = `<div class="row">
				<div class="col-1"></div>
				<div class="col-10 text-center">
						<h5>Aún hay productos pendientes de validar!</h5>
						<button
							type="button"
							class="btn btn-info"
							onclick="close_emergent();"
						>
							<i class="icon-cancel-circled">Aceptar</i>
						</button>
				</div>
			</div>`;
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( "display", 'block' );
			return false;
		}else{
			var url = "ajax/db.php?fl=finishValidation&p_k=" + localStorage.getItem( 'current_ticket' );
			url += "&ticket_has_changed=" + global_ticket_has_return;
			
			//alert( url ); return  false;
			var response = ajaxR( url ).trim().split( '|' );
			if( response[0] != 'ok' ){
				alert( "Error : " + response[0] );
			}
			$( '.emergent_content' ).html( response[1] );
			$( '.emergent' ).css( 'display', 'block' );
		
		//redirecciona si es el caso
			if( response[2] != '' ){
				//$( '#btn_reload_final' ).css( 'display', 'none' );
	            url = "../../touch_desarrollo/ajax/ticket-php-head-reimpresion.php?id_ped="+localStorage.getItem( 'current_ticket' )+"&id_dev=0";
	            var reimp = ajaxR(url);
				setTimeout( function (){
				//libera el id de ticket
					location.href = response[2];
					localStorage.setItem( 'current_ticket', null );
				}, 1000 );
			}
		//libera el id de ticket
			localStorage.setItem( 'current_ticket', null );
		}	
	}

	function setProductSeekerType( obj ){
		if( $( obj ).prop( 'checked' ) ){
			$( '#product_barcode_seeker' ).attr( 'disabled', true );
			$( '#product_barcode_seeker_pieces' ).removeAttr( 'disabled' );
			$( '#product_barcode_seeker_pieces' ).focus();
			$( '#product_barcode_seeker_response' ).css('display', 'none');
			//$( '#product_barcode_seeker_response' ).css('display', 'true');
		}else{
			$( '#product_barcode_seeker_pieces' ).attr( 'disabled', true );
			$( '#product_barcode_seeker' ).removeAttr( 'disabled' );
			$( '#product_barcode_seeker' ).focus();
			$( '#product_barcode_seeker_pieces_response' ).css('display', 'none');
			//$( '#product_barcode_seeker_pieces_response' ).css('display', 'none');
		}
	}

	function setProductByName( product_id, sale_detail_id, was_found_by_name = false ){
	//alert( sale_detail_id );
	//	lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		//$( '#product_barcode_seeker_pieces_response' ).html( '' );
		$( '#product_barcode_seeker_pieces_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		//$( '#product_barcode_seeker_response' ).html( '' );
		$( '#product_barcode_seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/db.php?fl=getOptionsByProductId&product_id=" + product_id;
		url += "&sale_detail_id=" + sale_detail_id + "&is_by_name=1";
//alert( url );
		var response = ajaxR( url ).split( "|" );
//alert( response );
		$( '.emergent_content' ).html( response[0] );
		$( '.emergent' ).css( 'display', 'block' );
	//implementacion Oscar 2023 para que no se tenga que seleccionar el pp en maquilados
		if( response[1] == '1' ){
			select_product_provider_automatic();
		}
	}

	function select_product_provider_automatic(){
		setTimeout( function(){}, 300);
		$( '#p_m_5_0' ).prop( 'checked', true );
		setTimeout( function(){}, 300);
		$( '#select_p_p_by_name_btn' ).click();
		
	}
	function setLastPiecesModel( sale_detail_id, is_sale_return = null, product_id = null, ticket_id = null, was_found_by_name = null ){
		var detail_selected = -1;
		var barcode_selected = "";
		$( '#last_pieces_list tr' ).each( function ( index ){
			if( $( '#last_pieces_3_' + index ).prop( 'checked' ) ){
			//	alert( index );
				detail_selected = $( '#last_pieces_3_' + index ).val();
				barcode_selected = $( '#last_pieces_4_' + index ).html().trim();
				if( is_sale_return != null ){
					//detail_selected = $( '#p_m_6_' + index ).html().trim();
				}
			}
		});
		if( detail_selected == -1 ){
			alert( "Debes de seleccionar un producto para continuar!" );
			return false;
		}else{
			seekTicketBarcode( -1, '#product_barcode_seeker_pieces', 'seekProductBarcode', barcode_selected, 
				null, detail_selected, null );

		}
	}

	function setProductModel( sale_detail_id, is_sale_return = null, product_id = null, ticket_id = null, was_found_by_name = null ){
		var model_selected = -1;
		$( '#model_by_name_list tr' ).each( function ( index ){
			if( $( '#p_m_5_' + index ).prop( 'checked' ) ){
			//	alert( index );
				model_selected = $( '#p_m_5_' + index ).val();
				if( is_sale_return != null ){
					model_selected = $( '#p_m_6_' + index ).html().trim();
				}
			}
		});
		if( model_selected == -1 ){
			alert( "Debes de seleccionar un modelo para continuar!" );
			return false;
		}else{
			if( is_sale_return == null ){
//alert( 1 );
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );
				$( '#barcode_seeker' ).val( model_selected.trim() );
				//validateBarcode( '#barcode_seeker', 'enter', null, null, null, null, 1 );
				//seekTicketBarcode( -1, obj, type, barcode = null, pieces_quantity = null );
				seekTicketBarcode( -1, '#product_barcode_seeker_pieces', 'seekProductBarcode', model_selected.trim(), 
					null, sale_detail_id, was_found_by_name );
			}else{
			//alert( 2 );
				var url = "ajax/db.php?fl=saveNewProductProviderValidation&product_provider_id=" + model_selected;
				url += "&product_id=" + product_id + "&ticket_id=" + ticket_id;
				url += "&sale_detail_id=" + sale_detail_id;
//alert( url ); return false;
				var response = ajaxR( url );
				//alert( response );
				if( response == 'ok' ){
					getValidationHistoric( product_id, ticket_id, sale_detail_id );
					close_emergent_2();
				}else{
					alert( "Error : " + response );
				}
			}
			//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
	}

	function add_product_provider_in_validation( product_id, ticket_id, sale_detail_id ){
		var url = "ajax/db.php?fl=getProductProvidersToValidation&product_id=" + product_id;
		url += "&ticket_id=" + ticket_id + "&sale_detail_id=" + sale_detail_id;
//alert( url );
		var response = ajaxR( url ).split( '|' );
		$( '.emergent_content_2' ).html( response[0] );
		$( '.emergent_2' ).css( 'display', 'block' );
	//implementacion Oscar 2023 para que no se tenga que seleccionar el pp en maquilados
		if( response[1] == '1' ){
			select_product_provider_automatic();
		}
	}

	function getReturnPrevious(){
		var url = "ajax/db.php?fl=getReturnPrevious&p_k=" + localStorage.getItem( 'current_ticket' );

		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).css( 'position', 'relative' );
		$( '.emergent_content' ).css( 'top', '-100px !important' );
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}

	function recalculateReturnProduct(){
		var limit = $( '#validation_resumen_list tr' ).length;
		var total = parseFloat( $( '#sale_product_total_quantity' ).html().trim() );
		$( '#validation_resumen_list tr' ).each( function ( index ){
			if( document.getElementById( 'vrs_row_2_' + index ) && index < ( limit -1 ) ){
//alert( $( '#vrs_row_2_' + index ).val() );
				total -= parseFloat( $( '#vrs_row_2_' + index ).val() );
			}
		});
		$( '#row_without_validation' ).empty();
		$( '#row_without_validation' ).html( total );
	}


	function validateReturnProduct(){
		if( parseFloat( $( '#row_without_validation' ).html().trim() ) < 0 ){
			alert( "La cantidad validada no puede ser mayor a la cantidad vendida." );
			return false;
		}
		return true;
		/*var sale_pieces_quantity = parseFloat( $( '#sale_product_total_quantity' ).html().trim() );
		var total = 0;
		var length = $( '#validation_resumen_list tr' ).length - 1;
		$( '#validation_resumen_list tr' ).each( function ( index ){
			if( document.getElementById( 'vrs_row_2_' + index ) ){
				//alert( $( '#vrs_row_2_' + index ).val() );
				total += parseFloat( $( '#vrs_row_2_' + index ).val() );
			}
		});
//alert( total + '>' + sale_pieces_quantity );
		if( total > sale_pieces_quantity){
			alert( "La cantidad validada no puede ser mayor a la cantidad vendida." );
			return false;
		}
		return true;*/
		//$( '#row_without_validation' ).html( total );
	}

//implementacion Oscar 2023 para validar que no se aceptan decimales
	function validate_no_decimals( obj ){
		var tmp_val = $( obj ).val();
		//alert( tmp_val.includes( '.' ) + ' ? ' + tmp_val );
		if( tmp_val.includes( '.' ) ){
			alert( "Los decimales no son permitidos, verifica y vuelve a intentar!" );
		}

		tmp_val = tmp_val.replaceAll( 'e', '' );
		tmp_val = tmp_val.replaceAll( '.', '' );
		$( obj ).focus().val( '' ).val( tmp_val );
	}
//fin de cambio Oscar 2023
/*implementacion Oscar 2023 para resetear la validacion de la nota de venta*/
	function reset_validation( ticket_id, confirmation = false ){
		if( !confirmation ){
			get_reset_view( ticket_id );
			return false;
		}else{
		//valida el password del encargado
			var pass = $( '#mannager_password' ).val();
			if( pass == '' ){
				alert( "La contraseña del encargado no puede ir vacia!" );
				$( '#mannager_password' ).focus();
				return false;
			}
			var url = "ajax/db.php?fl=validateMannagerPassword&pass=" + pass;
			var resp = ajaxR( url );
			if( resp != 'ok' ){
				alert( resp );
				$( '#mannager_password' ).select();
				return false;
			}else{
		//resetea la validacion de nota de venta
				url = "ajax/db.php?fl=reset_validation&sale_id=" + ticket_id;
				resp = ajaxR( url );
				if( resp == 'ok' ){
					alert( "Esta validacion fue reseteada exitosamente!" );
					location.reload();
				}else{
					alert( "Error al resetear validacion : \n" + resp );
				}
			}
		}
	}

	function get_reset_view( ticket_id ){
		var content = `<div class="row text-center">
			<h4>Pide al encargado que ingrese su contraseña para resetear esta validacion : </h4>

			<div class="col-2"></div>
			<div class="col-8 text-center">
				<br><br>
				<input type="password" id="mannager_password" class="form-control">
				<br><br>
				<button
					class="btn btn-success form-control"
					onclick="reset_validation( ${ticket_id}, true )"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>
				<br><br>
				<button
					class="btn btn-danger form-control"
					onclick="close_emergent()"
				>
					<i class="icon-cancel-circled">Cancelar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
		$( '#mannager_password' ).focus();
	}
/**/

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