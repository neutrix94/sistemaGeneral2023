	var current_transfers = new Array();
	var current_transfers_blocks = new Array();
	var global_pieces_quantity = 0;
	var audio_is_playing = false;
	var global_is_box_barcode = 0;
	var global_view = '';
	var global_remove_transfer_id = 0;

	var current_origin_warehouse = '';//implementacion Oscar 2023
	var current_destinity_warehouse = '';//implementacion Oscar 2023

	var global_current_transfer_destinity = '';
//var soporteVibracion= "vibrate" in navigator;
//alert( soporteVibracion );
//window.navigator.vibrate([20, 10, 20]);
//window.navigator.vibrate() ;//&& 
//window.navigator.vibrate(100);

/*implementacion Oscar 2023/09/26 para evitar numeros decimales en emergente de piezas*/
	function validate_is_not_decimal( obj ){
		var value = $( obj ).val();
		if( value.includes( '.' ) ){//value % 1 != 0
			alert_scann( 'audio' );
			alert_scann( 'error' );
			alert( "No se permiten decimales en esta ventana!" );
			var val = parseInt( $( obj ).val() );
			$( obj ).focus().val('').val(val);
			return false;
		}
	}
/*fin de cambio Oscar 2023/09/26*/

	//var global_focus_locked = 0;
	var element_focus_locked = '';
	//alert( element_focus_locked );

	document.addEventListener('keydown', (event) => {
		var keyValue = event.keyCode;

		if( keyValue == 13 && document.activeElement.id == '' && global_view == '.transfers_products' ){//!= element_focus_locked && element_focus_locked !
			var resp = "<h5 class=\"orange\">No estas posicionado en el campo del código de barras!</h5>";
			//alert( '' ) ;
			alert_scann( 'error' );
			
			resp += '<div class="row"><div class="col-2"></div><div class="col-8">';
			resp += '<button class="btn btn-warning form-control" onclick=\"close_emergent();';
			if( element_focus_locked == '' && global_view == '.transfers_products' ){
				//$( '#barcode_seeker' ).focus();
				//$( '#barcode_seeker_lock_btn' ).click();
				resp += "lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );";
			}else{//$( element_focus_locked ).focus();
				var aux = element_focus_locked.replace( '#', '' );
				resp += "document.getElementById( '" + aux + "' ).focus();";
			}

			resp += '\"><i class=\"icon-ok-circle\">Aceptar</i></button>';
			resp += '</div></div>';
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			//return false;
		}
	}, false);

//mostrar / ocultar vistas del menú
	function show_view( obj, view, make_group = null ){//alert( element_focus_locked );
	/*fin de acambio Oscar 2023*/
	//implementacion Oscar 2023
		if( localStorage.getItem( 'is_principal_validation_session' ) == 0 ){
			if( view == '.resume' ){
				alert( "Solo el usuario principal puede accedder a esta pantalla!" );
				return false;
			}
		}

		if( make_group != null ){
			createBlock();
		}

		/*if( element_focus_locked != '' && global_view == '.transfers_products'){
			//alert( element_focus_locked );
			return false;
		}*/
		global_view = view;
		if( current_transfers.length <= 0 && ( view == '.transfers_products' || view == '.resume' ) ){
			alert( "Seleccione las transferencia a Validar desde el Listado!" );
			close_emergent();
			return false;
		}
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );
		close_emergent();
		$( '#btn_finish_validation' ).css( 'display', ( view == '.resume' ? 'inline-block' : 'none' ) );

		if( view == '.transfers_products' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
			//setTimeout( function (){$( '#barcode_seeker' ).focus(); //alert();}, 300);
		}

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
		/*if( $(obj).attr( 'class' ) == 'btn' ){

		}
		if( )*/
	}
	//redireccionamientos
	function redirect( type ){
		if( global_view == '.transfers_products' && element_focus_locked != '' ){
			return false;
		}
		switch ( type ){
			case 'home' : 
				if( confirm( "Salir sin Guardar?" ) ){
					location.href="../../../../index.php";
				}
			break;
		}
	}
	function close_emergent( obj_to_clean = null, obj_to_focus = null ){
	//cierra emergente
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		
		global_pieces_quantity = 0;

		if( global_view == '.transfers_products' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
		
		if( obj_to_clean != null ){
			$( obj_to_clean ).val('');
		}
		if( obj_to_focus != null ){
			$( obj_to_focus ).focus();
		}
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );

	}

	

	function set_transfers(){
		//var X = validateGroups();
		if( validateGroups() == false ){
			alert( "Primero escanea los folios que falta para continuar!" );
			return false;
		}
		//alert();
		/*if( current_transfers.length > 0 ){
			show_emergent_transfers_add();
			return false;
			/*if( !confirm( "Ya hay transferencias validandose, Realmente desea validar nuevas transferencias?" ) ){
				return false;
			}
			current_transfers = new Array();
			current_transfers_blocks = new Array();*
		}*/
		var transfer_to_show = '<h5>Las siguientes transferencias serán verificadas :</h5>';
			var transfer_to_set = '<table class="table table-striped table-bordered">';
		transfer_to_show += '<table class="table table-striped table-bordered">';
			transfer_to_set += '<thead><tr><th>Folio</th><th>Destino</th></tr></thead><tbody id="current_transfers_sets">';
		transfer_to_show += '<thead><tr><th>Folio</th><th>Destino</th><th>Status</th><th class="text-center">X</th></tr></thead><tbody id="current_transfers_list">';
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_8_' + index ).prop( 'checked' ) == true ){
				$( this ).children( 'td' ).each( function (index2){
					if( index2 == 0 ){
						current_transfers.push( parseInt( $( this ).html().trim() ) );
						//alert( current_transfers );
					}else if( index2 == 2 ){
						transfer_to_show += '<tr><td>' + $( this ).html() + '</td>' ;
							transfer_to_set += '<tr><td>' + $( this ).html() + '</td>' ;
					}else if( index2 == 3 ){
						transfer_to_show += '<td>' + $( this ).html() + '</td>';
							transfer_to_set += '<td>' + $( this ).html() + '</td>';
					}else if( index2 == 4 ){
						transfer_to_show += '<td>' + $( this ).html() + '</td>';
						transfer_to_show += '<td class=\"text-center\"><button onclick=\"delete_tranfer_to_block( ' + ( current_transfers[current_transfers.length - 1] ) +  ', this );\" class=\"btn btn-danger\">X</button></td>';
						transfer_to_show +='</tr>';
							/*transfer_to_set += '<td><button type="button" class="btn btn-danger btn-trans-del"';
							transfer_to_set += ' onclick="remove_transfer_group( ' + $( '#validation_list_1_' + index ).html().trim() + ' );">';
							transfer_to_set += '<i class="icon-cancel-alt-filled"></i></button></td>';*/
							transfer_to_set += '</tr>';
					}else if( index2 == 5 ){
					//alert( 'here' );
						var aux = $( '#validation_list_6_' + index ).html().trim();
						if( ! current_transfers_blocks.includes( aux ) && aux.trim() != '' ){
							//alert( 'no existe' + aux );
							current_transfers_blocks.push( aux );
							//alert( current_transfers_blocks );
						}else{
						}
					}
				});
			}
		});
		if( current_transfers.length <= 0 ){
			alert( "Elije al menos una transferencia para continuar!" );
			current_transfers = new Array();
			current_transfers_blocks = new Array();
			return false;
		}
//console.log( 'Bloques : ',  current_transfers_blocks );
		transfer_to_show += '</tbody></table><br />';
			transfer_to_set += '</tbody></table><br />';
		build_transfers_to_validate( transfer_to_set );
		transfer_to_show += '<div class="row">';
		transfer_to_show += '<div class="col-2"></div>';
		transfer_to_show += '<div class="col-8">';
			transfer_to_show += '<button onclick="show_view( \'.mnu_item.source\', \'.transfers_products\', 1 );" class="btn btn-success form-control">';
				transfer_to_show += 'Aceptar';
			transfer_to_show += '</button>';
			transfer_to_show += '</div>'; 
		transfer_to_show += '</div>'; 

		$( '.emergent_content' ).html( transfer_to_show );
		$( '.emergent' ).css( 'display', 'block' );
		loadLastValidations();
		load_resumen();
	}

	function delete_tranfer_to_block( value, obj ){
		if( !confirm( `¿Quitar la transferencia del bloque?` ) ){
		return false;
	}
		//current_transfers.splice( position, position );
		var aux = new Array();
		for( var i = 0; i < current_transfers.length; i++  ){
			if( current_transfers[i] != value  ){
				aux.push( current_transfers[i] );
			}
		}
		setTimeout( function(){}, 100 );
		$( obj ).parent().parent().remove();
		current_transfers = aux;
		if( current_transfers.length == 0 ){
			alert( "No hay Transferencias Seleccionada!\nVuelve a escanear las transferenias que deseas validar." );
			location.reload();
		}/*else{
			setTimeout( function(){set_transfers();}, 100 );
		}*/
		//alert( current_transfers );
	}

	function seekTransferByBarcode( e ){
		var key = e.keyCode;
		var txt = $( '#transfers_seeker' ).val().trim();
		
		if( key == 13 || e == 'intro' ){
			if( txt == '' ){
				alert( "Es necesario ingresar un valor para continuar!" );
			}
			if ( ! setTransferCheck( txt ) ){
				$( '#transfers_seeker' ).select();
			}else{
				alert_scann( 'audio' );
				$( '#transfers_seeker' ).val( '' );
			}
		}
		return false;
	}

	function  validateBlock_is_not_editing( block_id ) {
		var url = "ajax/db.php?fl=validate_permission_block&validation_block_id=" + block_id;
		if( localStorage.getItem( 'validation_token' ) != null ){
			url += "&validation_token=" + localStorage.getItem( 'validation_token' );
		}
		var response = ajaxR( url ).split( '|' );
		if( response[0] != '' ){
			$( '.emergent_content' ).html( response[1] );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}else{
			return true;
		}
	}

	function setTransferCheck( barcode ){
//alert(0);
		barcode = barcode.toUpperCase();
		var invalid = false;
		var aux = 0, aux_ = 0;
		var is_the_same_block = true;
		var block_id = '';
		var was_found = false;
		if( current_transfers.length > 0 && $( '#blocks_permission' ).val().trim() == 1 ){
//alert(-1);
		//obtiene el id de la transferencia
			$( '#transfers_list_content tr' ).each( function ( index ){
				aux = $( '#validation_list_2_' + index ).html().trim();
			//	alert( index );
				if( $( '#validation_list_2_' + index ).html().trim() == barcode ){
					was_found = index;
					aux_ = $( '#validation_list_1_' + index ).html().trim();
					block_id = $( '#validation_list_6_' + index ).html().trim();
				
//alert(1);
				if( localStorage.getItem( 'validation_token' ) != null && block_id == '' ){
//alert( 'here_1' );
					return false;
				}
					if( $( "#validation_list_6_" + index ).html().trim() != "" 
						&& localStorage.getItem( 'current_validation_block_id' ) != null
						&& localStorage.getItem( 'current_validation_block_id' ) != $( "#validation_list_6_" + index ).html().trim() ){
						invalid = true;
					}
				/*implementacion Oscar 2023
					if(  ){

					}
					//return true;*/
				}
			});
			if( block_id != '' ){
				if( ! validateBlock_is_not_editing( block_id ) ){
					return false;
				}
			}else{//verifica si el usuario tiene el permiso de edición
				if( ! check_user_permission_to_edit_block( $( '#validation_list_2_' + was_found ).html().trim() ) ){
					$( '#validation_list_8_' + was_found ).removeAttr( 'checked' );
					$( '#validation_list_9_' + was_found ).removeClass( 'btn-success' );
					$( '#validation_list_9_' + was_found ).addClass( 'btn-warning' );
					return false;
				}
			}
			/*if( block_id != '' ){
				if( ! validateBlock_is_not_editing( block_id ) ){
					return false;
				}
			}else{//verifica si el usuario tiene el permiso de edición
			}*/
/*implementacion Oscar 2023 para validar que el bloque de transferencia es el mismo de la sesion del local storage*/
			if( invalid ){
				show_block_emergent();
				return false;
			}
/*fin de cambio Oscar 2023*/
		//verifica que la transferencia no haya sido escaneada
			var transfer_exists = 0;

			for (var i = current_transfers.length - 1; i >= 0; i--) {
				//alert( current_transfers[i] + "==" + aux_  );
				if( current_transfers[i] == aux_ ){
					transfer_exists = 1;
				}
			}
			if( transfer_exists == 1 ){
				alert( "Esta transferencia ya fue escaneada!" );
				$( '#transfers_seeker' ).val( '' );
				$( '#transfers_seeker' ).select();
				return false;
			}
			show_emergent_transfers_add( barcode );
			return false;
		}else if( current_transfers.length > 0 && $( '#blocks_permission' ).val().trim() == 0 ){
//alert(-2);
			var aux = `<div class="row">
						<h3>Solo se puede validar un bloque a la vez, si necesita validar otra transferencia pida al encargado que modifique el bloque</h3>
						<div class="col-1"></div>
						<div class="col-10">
							<button
								class="btn btn-success"
								onclick="close_emergent();"
							><i class="icon-ok-circle">Aceptar</i>
							</button>
						</div>
					</div>`;
			$( '.emergent_content' ).html( aux );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		//alert( 2 );
		
			$( '#transfers_list_content tr' ).each( function ( index ){
				if( document.getElementById( 'validation_list_2_' + index ) ){
					if( $( '#validation_list_2_' + index ).html().trim() == barcode ){
						/*if( current_transfers_blocks.length > 0 && current_transfers_blocks[0] != $( '#validation_list_6_' + index ).html().trim()  ){
							alert( "No se puede recibir más de un bloque a la vez." );
							return null;
						}*/
						//alert( localStorage.getItem( 'current_reception_block_id' ) + "!=" + $( "#validation_list_6_" + index ).html().trim() );
						
					if( current_transfers_blocks.length > 0 ){
						//alert( 'here 1 : ' + current_transfers_blocks );
						if( current_transfers_blocks[0] != $( "#validation_list_6_" + index ).html().trim()
							&& $( "#validation_list_6_" + index ).html().trim() != ''
						 ){
							alert( "La transferencia es de un bloque de validacion diferente y no se puede recibir!" );
							is_the_same_block = false;
							return false;
						}
						block_id = $( "#validation_list_6_" + index ).html().trim();
//alert(2);
					/*	if( localStorage.getItem( 'validation_token' ) != null && block_id == '' ){
							alert( 'here_2' );
							return false;
						}*/
					}else if( $( "#validation_list_6_" + index ).html().trim() != '' ){
						block_id = $( "#validation_list_6_" + index ).html().trim();
//alert(3);
						if( localStorage.getItem( 'validation_token' ) != null && block_id == '' ){
//alert( 'here_3' );
							return false;
						}
						current_transfers_blocks.push( $( "#validation_list_6_" + index ).html().trim() );
						block_id = current_transfers_blocks;
					}
/*implementacion Oscar 2023 para validar que el bloque de transferencia es el mismo de la sesion del local storage*/
						if( $( "#validation_list_6_" + index ).html().trim() != "" 
						&& localStorage.getItem( 'current_validation_block_id' ) != null
						&& localStorage.getItem( 'current_validation_block_id' ) != $( "#validation_list_6_" + index ).html().trim() ){
							invalid = true;
							return true;
						}
/*fin de cambio Oscar 2023*/
						$( '#validation_list_8_' + index ).prop( 'checked', true );
						$( '#validation_list_9_' + index ).removeClass( 'btn-warning' );
						$( '#validation_list_9_' + index ).addClass( 'btn-success' );
						getAllGroup( index );
						was_found = index;
						return true;
					}
				}
			});
//alert( 'pasa' );
			if( block_id != '' ){
				if( ! validateBlock_is_not_editing( block_id ) ){
					return false;
				}
			}else{//verifica si el usuario tiene el permiso de edición
				if( ! check_user_permission_to_edit_block( $( '#validation_list_2_' + was_found ).html().trim() ) ){
					$( '#validation_list_8_' + was_found ).removeAttr( 'checked' );
					$( '#validation_list_9_' + was_found ).removeClass( 'btn-success' );
					$( '#validation_list_9_' + was_found ).addClass( 'btn-warning' );
					return false;
				}
			//implementacion Oscar 2023
				if( localStorage.getItem( 'validation_token' ) != null ){
					alert( "Hay una sesion que no pertenece al bloque, esta transferencia no se puede escanear sin escanear otra transferencia del bloque!" );
					show_block_emergent();
					$( '#validation_list_8_' + was_found ).removeAttr( 'checked' );
					$( '#validation_list_9_' + was_found ).removeClass( 'btn-success' );
					$( '#validation_list_9_' + was_found ).addClass( 'btn-warning' );
					return false;
				}
			}

			if( ! is_the_same_block ){
				return false;
			}
/*implementacion Oscar 2023 para validar que el bloque de transferencia es el mismo de la sesion del local storage*/
			if( invalid ){
				show_block_emergent();
				//alert( "No se puede recibir este bloque porque este dispositivo esta ligado actualmente a otro bloque de validacion.\n Pide ayuda al encargado de la bodega para continuar!" );
				return false;
			}
/*fin de cambio Oscar 2023*/
		
		if( was_found == false ){
			alert( "La transferencia escaneada no esta en el Listado" );
			return false;
		}
		return true;
	}

	function load_resumen(){
		var response = ajaxR( 'ajax/db.php?fl=getResumeHeader&transfers=' + current_transfers + '&type=1'  );
		$( '.group_card.adjustments.differences' ).html( response );

		response = ajaxR( 'ajax/db.php?fl=getResumeHeader&transfers=' + current_transfers + '&type=2'  );
		$( '.group_card.adjustments.aggregates' ).html( response );
		return true;
	}
	function build_transfers_to_validate( content ){
		$( '.accordion-body.transfers' ).html( content );
	}
var global_permission_box = 0;
var global_tmp_barcode = '';
var global_tmp_unique_barcode = '';
/*validacion de códigos de barras*/

	function validateBarcode( obj, e, permission = null, pieces = null, permission_box = null ){
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
			alert_scann( 'error' );
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
		//global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
		var url = "ajax/db.php?fl=validateBarcode";
		url += "&transfers=" + current_transfers;

		url += "&barcode=" + txt;/*( global_permission_box != 0 ? global_tmp_barcode : txt )*/
		
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
		url += "&block_id=" + current_transfers_blocks[0];
		url += "&validation_token=" + localStorage.getItem( 'validation_token' );
//alert( url ); //return false;
		var response =  ajaxR( url );
		//alert( response );
		var ax = response.split( '|' );
		//alert( ax[0] );
		if( ax[0] != 'seeker' ){
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
		}
		switch( ax[0] ){
	/*implementacion Oscar 2023 para recibir respuesta de token invalido*/
			case 'invalid_token' :
				localStorage.removeItem("validation_token");
				localStorage.removeItem("current_validation_block_id");
				localStorage.removeItem("is_principal_validation_session");
				$( '.emergent_content' ).html( ax[1] );
				$( '.emergent' ).css( 'display', 'block' );
	/**/
			break;
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
				$( '#tmp_sell_barcode' ).focus();
			break; 
			case 'message_info':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				global_permission_box = '';
				global_pieces_quantity = 0;
				alert_scann( 'error' );

			break; 
			case 'manager_password':
				global_tmp_barcode = txt;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
				alert_scann( 'error' );
				if( document.getElementById('new_supply_pieces_quantity') ){
					document.getElementById('new_supply_pieces_quantity').focus();
				}
			break; 
			case 'pieces_form':
			alert_scann( 'pieces_number_audio' );
				global_tmp_barcode = txt;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#pieces_quantity_emergent' ).focus();
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
				$( '#seeker_response' ).html( ax[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
				return false;
			break;

			case 'ok':
				loadLastValidations();
				load_resumen();
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

	function setPiecesQuantity( is_maquiled = null ){
		global_pieces_quantity = $( '#pieces_quantity_emergent' ).val();
		
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
		validateBarcode( 'tmp', 'enter', null, global_pieces_quantity );
	}

	function setProductByName( product_id ){
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
			validateBarcode( '#barcode_seeker', 'enter' );
		}
	}

	function loadLastValidations(){
		var url = "ajax/db.php?fl=loadLastValidations&transfers=" + current_transfers;
		var response =  ajaxR( url );
	//alert( response );
		$( '#last_validations' ).html( response );
	}
//confirma envio de excedente
	function confirm_exceeds( permission_box = null ){
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
		//obj, e, permission = null, pieces = null, permission_box = null
		validateBarcode( 'tmp', 'enter', 1, null, ( permission_box != null ? 1 : null ) );
	}//( permission_box != null ? 'tmp' : '#barcode_seeker' )
//regresar el excedente
	function return_exceeds(){
		var return_instructions = '<h5>Aparte este producto de la transferencia para que no sea enviado a la Sucursal!</h5>';
		return_instructions += '<div class="row">';
			return_instructions += '<div class="col-2"></div>';
			return_instructions += '<div class="col-8">';
				return_instructions += '<button class="btn btn-warning form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
					return_instructions += 'Aceptar';
				return_instructions += '</button>';
			'</div>';
		return_instructions += '<div>';
		$( '.emergent_content' ).html( return_instructions );
	}
//agregar proveedor-producto en transferencias
	function save_new_supply( validation_block_id, product_id, product_provider, 
		box, pack, piece, barcode, unique_code ){
	//obtiene el valor de la contraseña
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado es obligatoria!" );
			$( '#manager_password' ).focus();
			return false;
		}
		if( document.getElementById('new_supply_pieces_quantity') && document.getElementById('new_supply_pieces_quantity').value <= 0 ){
			alert( "El número de piezas no puede ir vacío." );
			document.getElementById('new_supply_pieces_quantity').focus();
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

		if( document.getElementById('new_supply_pieces_quantity') ){
			piece = document.getElementById('new_supply_pieces_quantity').value;
		}
	//agrega el registro en la base de datos
		url = "ajax/db.php?fl=insertNewProductValidation&p_id=" + product_id;
		url += "&p_p_id=" + product_provider + "&box=" + box;
		url += "&pack=" + pack + "&piece=" + piece + "&transfers=" + current_transfers;
		url += "&barcode=" + barcode;
		url += "&unique_code=" + unique_code;
		url += "&block_id=" + validation_block_id;		
//alert( url );
		response = ajaxR( url );
//alert( response );
		$( '.emergent_content' ).html( response );
		//$( '.emergent' ).css( 'display', none );
	}
/*implementacion Oscar 2023 para validar que este completamente surtida la transferencia*/
	function validate_all_was_surted(){
		var url = "ajax/db.php?fl=validateTransferStatus&transfers=" + current_transfers;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}
		if( response[1] != 'ok' ){
			$( '.emergent_content' ).html( response[1] );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
			//show_emergent();
		}
		return true;
	}
/*fin de cambio Oscar 2023*/

/**/
	function validate_devices_sessions(){
		var url = "ajax/db.php?fl=validate_devices_sessions&current_block=" + localStorage.getItem( 'current_validation_block_id' );
		url += "&validation_token=" + localStorage.getItem( 'validation_token' );
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

//finaliza la validacion de la transferencia
	function finish_validation(){
	/*implementacion Oscar 2023 para recargar la vista */
		if( !load_resumen() ){
			alert( "Error al recargar la vista del resumen de validacion!" );
			return false;
		}
	/*fin de cambio Oscar 2023*/
/*implementacion Oscar 2023 para validar que ester completamente surtida la transferencia / que no haya sessiones de validacion pendientes*/
		if( !validate_all_was_surted() ){
			return false;
		}
		if( ! validate_devices_sessions() ){
			return false;
		}
/*fin de cambio Oscar 2023*/

		if( $( '#validation_resume_1 tr' ).length > 0 ){
			alert( "No se puede terminar la validación de las Transferencias.\nAún hay registros pendientes de validar! " );
			$( '.group_card.adjustments.differences' ).css( 'border', '1px solid red' );
			$( '.group_card.adjustments.differences' ).css( 'background-color', 'orange' );
			setTimeout( function(){
				$( '.group_card.adjustments.differences' ).css( 'border', 'none' );	
				$( '.group_card.adjustments.differences' ).css( 'background-color', 'white' );
			}, 3000 );
			return false;
		}

		var url = 'ajax/db.php?fl=saveValidation&transfers=' + current_transfers;
		url += '&validation_token=' + localStorage.getItem( 'validation_token' );//implementacion Oscar 2023 para enviar el token al finalizar la sesion
		var response = ajaxR( url );

		var ax = response.split( '|' );
		if( ax[0] == 'ok' ){
			localStorage.removeItem("validation_token");
			localStorage.removeItem("current_validation_block_id");
			localStorage.removeItem("is_principal_validation_session");
			alert( ax[1] );
			location.reload();
		}else{
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		}
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
//guardar ajuste de inventario
	function save_adjustment(){
		var data_request_substraction = '', data_request_addition = '', 
			data_request_ok = '';
		var validation_failed = false;
		$( '#inventoryAdjudments tr' ).each( function( index ){
			if( $( '#adjustment_7_' + index ).val().trim() < 0
			|| $( '#adjustment_7_' + index ).val().trim() == '' ){
				validation_failed = '#adjustment_7_' + index;
				return false;
			}
			if( parseInt( $( '#adjustment_8_' + index ).html().trim() ) < 0 ){
				data_request_substraction += ( data_request_substraction != '' ? '|' : '' );
				data_request_substraction += $( '#adjustment_1_' + index ).html().trim();//id de registro
				data_request_substraction += '~' + $( '#adjustment_2_' + index ).html().trim();//id de producto
				data_request_substraction += '~' + $( '#adjustment_3_' + index ).html().trim();//id de proveedor producto
				data_request_substraction += '~' + ( parseInt( $( '#adjustment_8_' + index ).html().trim() ) * -1 );//cantidad para ajustar
			}else if( parseInt( $( '#adjustment_8_' + index ).html().trim() ) > 0 ){
				data_request_addition += ( data_request_addition != '' ? '|' : '' );
				data_request_addition += $( '#adjustment_1_' + index ).html().trim();//id de registro
				data_request_addition += '~' + $( '#adjustment_2_' + index ).html().trim();//id de producto
				data_request_addition += '~' + $( '#adjustment_3_' + index ).html().trim();//id de proveedor producto
				data_request_addition += '~' + parseInt( $( '#adjustment_8_' + index ).html().trim() );//cantidad para ajustar
			}else{
				data_request_ok += ( data_request_ok != '' ? '|' : '' );
				data_request_ok += $( '#adjustment_1_' + index ).html().trim();//id de registro
			}
		});
		if( validation_failed != false ){
			alert( "Aún hay inventarios sin ajustar\nVerifique y vuelva a intentar!" );
			$( validation_failed ).focus();
			return false;
		}
		/*alert( data_request_rest );
		alert( data_request_sum );
		alert( data_request_ok );*/
		var url = 'ajax/db.php?fl=inventoryAdjustment';
		url += '&addition=' + data_request_addition;
		url += '&substraction=' + data_request_substraction;
		url += '&data_ok=' + data_request_ok;
		//alert( url );
		var response = ajaxR( url );
		//alert( response );
		$( '.emergent_content' ).html( response ); 
		$( '.emergent' ).css( 'display', 'block' ); 
	}

	function sow_adjustemt_locations( counter ){
		var resp = '<table class="table table-striped table-bordered">';
			resp += '<thead>';
				resp += '<tr>';
					resp += '<th width="20%">#</th>';
					resp += '<th width="80%">Ubicación</th>';
				resp += '</tr>';
			resp += '</thead>';
			resp += '<tbody>';
		var array = $( '#adjustment_9_' + counter ).html().trim().split('~');
			for (var i = 0; i < array.length; i++) {
				resp += '<tr>';
					resp += '<td>' + ( array[i] != 'No hay ubicaciones registradas' ? ( i + 1 ) : '' ) + '</td>';
					resp += '<td>' + array[i] + '</td>';
				resp += '</tr>';
			}
			resp += '</tbody>';
		resp += '</table>';

		resp += '<p align="center">';
			resp += '<button class="btn btn-success" onclick="close_emergent();">';
				resp += '<i class="icon-ok-cirlce">Aceptar</i>';
			resp += '</button>';
		resp += '</p>';

		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
		
	}

	function omit_inventory_adjustment( ){
		var pss = $( '#manager_password' ).val();
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		location.href= "./index.php?ommitInvAdj=1";
	}
	
	function build_adjustemnts_locations(){

	}

	function calculate_adjustment_differece( counter ){
		var virtual_inventory = parseInt( $( '#adjustment_6_' + counter ).html().trim() );
		var physical_inventory = parseInt( $( '#adjustment_7_' + counter ).val().trim() );
		if ( physical_inventory < 0 ){
			alert( "El inventario físico no puede ser menor a cero!" );
			$( '#adjustment_7_' + counter ).val( 0 );
			$( '#adjustment_7_' + counter ).select();
			return false;
		}
		var differece = parseInt( physical_inventory - virtual_inventory );

		$( '#adjustment_8_' + counter ).html( differece );
	}	

//búsqueda de productos recibidos
	function seek_recived_products(){
		var txt = $( '#recived_products_seeker' ).val();
		if( txt.length <= 2 ){
			$( '#recived_products_seeker_response' ).html( '' );
			$( '#recived_products_seeker_response' ).css( 'display', 'none' );
		}
		//omite codigo de barras si es el caso
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

		var url = 'ajax/db.php?fl=seekRecivedProducts&txt=' + txt + '&transfers=' + current_transfers;
		var response = ajaxR( url );
		$( '#recived_products_seeker_response' ).html( response );
		$( '#recived_products_seeker_response' ).css( 'display', 'block' );
	}

	function load_product_validation_detail( obj, product_id ){
		var url = "ajax/db.php?fl=loadProductValidationDetail&product_id=" + product_id + "&transfers=" + current_transfers;
		response = ajaxR( url );
		$( '#last_validations' ).html( response );
		var aux = $( obj ).html().trim().replace( '<b>', '' );
		aux = aux.replace( '</b>', '' );
		$( '#recived_products_seeker' ).val( aux );
		$( '#recived_products_seeker' ).attr( 'disabled', true );
		$( '#recived_products_seeker_response' ).html( '' );
		$( '#recived_products_seeker_response' ).css( 'display', 'none' );
	}

	function clean_recived_form(){
		$( '#recived_products_seeker' ).removeAttr( 'disabled' );
		$( '#recived_products_seeker' ).val( '' );
		$( '#recived_products_seeker_response' ).html( '' );
		$( '#recived_products_seeker_response' ).css( 'display', 'none' );
		loadLastValidations();
	}

	function reload_transfers_list_view( obj = null ){
		var url = 'ajax/db.php?fl=getTransfersListValidation';
	//obtiene los filtros
		url += "&store_orig=" + $('#store_filter_Orig').val();
		url += "&store_dest=" + $('#store_filter_Dest').val();

	//obtiene ordenamiento
		if( obj != null ){
			var tmp = $( obj ).attr( 'order' );
			if( tmp == "ASC" ){
				$( obj ).attr( 'order', "DESC" );
				$( obj ).children('i').removeClass( "icon-up-big" );
				$( obj ).children('i').addClass( "icon-down-big" );
			}else{
				$( obj ).attr( 'order', "ASC" );
				$( obj ).children('i').addClass( "icon-up-big" );
				$( obj ).children('i').removeClass( "icon-down-big" );
			}		
		}
		var folio_order = $( '#folio_order' ).attr( 'order' );
		url += "&folio=" + ( folio_order != "" ? `t.id_transferencia-${folio_order}` : "" );
		var status_id_order = $( '#status_id_order' ).attr( 'order' );
		url += "&status=" + ( status_id_order != "" ? `t.id_estado-${status_id_order}` : "" );
		var order_block_id_order = $( '#order_block_id_order' ).attr( 'order' );
		url += "&block_id=" + ( order_block_id_order != "" ? `tvd.id_bloque_transferencia_validacion-${order_block_id_order}` : "" );


		var response = ajaxR( url );
		$( '#transfers_list_content' ).empty();
		$( '#transfers_list_content' ).append( response );	
	//marca los checks y los filios escaneados
		setTimeout( function(){
			$( '#transfers_list_content tr' ).each( function ( index ){
				var aux = $( '#validation_list_1_' + index ).html().trim();
				for( var i = 0; i < current_transfers.length; i++ ){
					if( current_transfers[i] == aux ){//current_transfers.includes( aux )
						$( '#validation_list_8_' + index ).prop( 'checked', true );
						$( '#validation_list_9_' + index ).removeClass( 'btn-warning' );
						$( '#validation_list_9_' + index ).addClass( 'btn-success' );
					}
				}
			});
		}, 300
		);
	}

	function show_hidde_validate_pending_form( transfer_product_id ){
		var url = "ajax/db.php?fl=showHiddeValidatePendingForm";
		url += "&transfer_product_id=" + transfer_product_id;
		var response = ajaxR( url );

		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		
	}

	function skip_pending_validation( transfer_product_id ){
		var supply_case = $( '#supply_case' ).val();
		if( supply_case == '' ){
			alert( "Debes de elegir una aopción valida para omitir la validación de este producto!" );
			$( '#supply_case' ).focus();
			return false;
		}
		var url = "ajax/db.php?fl=skipPendingValidation";
		url += "&transfer_product_id=" + transfer_product_id;
		url += "&selected_case=" + supply_case;
		var response = ajaxR( url );
		var ax = response.split( '|' );
		if( ax[0] == 'ok' ){
			loadLastValidations();
			load_resumen();
		}else{
			alert( "Ocurrió un error : " + response );
			return false;
		}
		$( '.emergent_content' ).html( ax[1] );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function close_device_validation_session( validation_token, permission = false ){
		var content = '';
		if( permission == false ){
			content = `<div class="row">
				<h5>Escribe la palabra "FINALIZAR" para finalizar la sesion ${validation_token}</h5>
				<div class="col-4"></div>
				<div class="col-4">
					<input type="text" id="close_session_input_tmp" class="form-control">
					<br>
					<button 
						type="button"
						class="btn btn-success form-control"
						onclick="close_device_validation_session( '${validation_token}', true );"
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
			var url = "ajax/db.php?fl=close_device_validation_session&validation_token=" + validation_token;
			url += "&validation_block_id=" + localStorage.getItem( 'current_validation_block_id' );
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

//var getFormAssignTransfer = ajaxR( "php/formAssignTransfer.php?p_k=" + transfer_id );
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

