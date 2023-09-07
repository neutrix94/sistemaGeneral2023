	
	function show_emergent_transfers_add( transfer_folio ){
	//	alert( transfer_folio );
		var url = "ajax/db.php?fl=getMessageToAddTransfer&transfers=" + current_transfers;
		url += "&transfer_to_add=" + transfer_folio;
		url += "&validation_block_id=" + localStorage.getItem( 'current_validation_block_id' );//implementacion Oscar 2023 para bloqueo de validacion por edicion
		url += "&validation_token=" + localStorage.getItem( 'validation_token' );
		var response = ajaxR( url );
		//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function validateGroups(){
		var resp = true;
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_8_' + index ).prop( 'checked' ) == true 
				&& $( '#validation_list_9_' + index ).hasClass( 'btn-warning' ) == true ){
				resp = false;
				return false;
			}
		});
		return resp;
	}

/**Funciones de bloque de transferencias**/
	function getAllGroup( counter ){
		var val = 1;
	/*implementacion Oscar 2023 para validar que sea mismo almacen origen y destino entre las transferencias escaneadas*/	
		if( current_origin_warehouse == '' ){
			current_origin_warehouse = $( '#validation_list_10_' + counter ).html().trim();
			current_destinity_warehouse = $( '#validation_list_11_' + counter ).html().trim();
		//alert(current_origin_warehouse + "\n" + current_destinity_warehouse );
		}else{
			if( current_origin_warehouse != $( '#validation_list_10_' + counter ).html().trim() 
				|| current_destinity_warehouse != $( '#validation_list_11_' + counter ).html().trim() 
			){
				alert( "Las transferencias por validar deben de ser del mismo almacen origen y destino " );
				if( $( '#validation_list_8_' + counter ).prop( 'checked' ) ){
					 $( '#validation_list_8_' + counter ).removeAttr( 'checked' );
					 $( '#validation_list_9_' + counter ).removeClass( 'btn-success' );
					 $( '#validation_list_9_' + counter ).addClass( 'btn-warning' );
	/*fin de cambio Oscar 2023*/
				}
			}
		}
		var block = $( '#validation_list_6_' + counter ).html().trim();
		if( ! $( '#validation_list_8_' + counter ).prop( 'checked' ) ){
			val = 0;
		}
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_6_' + index ).html().trim() == block && block != '' ){
				if( val == 1 ){
					$( '#validation_list_8_' + index ).prop( 'checked', true );
				}else{
					$( '#validation_list_8_' + index ).removeAttr( 'checked', true );
				}
			}
		});
	//verifica si ningún check esta checado
		var without_sucursal = 0;
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_8_' + index ).prop( 'checked' ) ){
				without_sucursal ++;
			}
		});
		if( without_sucursal == 0 ){
			global_current_transfer_destinity = '';
		}
	}

	function remove_transfer_group( transfer_id ){
		global_remove_transfer_id = transfer_id;
		var remove_all_validation = 0;
		if( $( '#current_transfers_sets tr' ).length <= 1 ){
			remove_all_validation = 1;	
		}
		var url = "ajax/db.php?fl=getPreviousRemoveTransferToValidation&transfer_id=" + transfer_id + "&reset_unic_transfer";
		if( remove_all_validation == 1 ){
			url += "&reset_unic_transfer=1";
		}
		var response = ajaxR( url );
		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		
		if( remove_all_validation == 1 )
			$( '#manager_password' ).focus();
	}

	function confirm_remove_transfer_block(){
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
			/*$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );*/
		 	$( '#manager_password' ).select();
			return true;
		}
		url = "ajax/db.php?fl=removeTransferBlock&transfer_id=" + global_remove_transfer_id ;
		response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function removeTransferBlockDetail( transfer_product_id ){
		var url = "ajax/db.php?fl=removeTransferBlockDetail&transfer_id=" + global_remove_transfer_id;
			url += "&transfer_product_id=" + transfer_product_id;
		response = ajaxR( url );
		/*if( response!= 'ok' ){
			alert( response );
			return false;
		}else{*/
			$( '#detail_' + transfer_product_id ).remove();
			$( '.emergent_content_2' ).html( response );
			$( '.emergent_2' ).css( 'display', 'block' );
		//}
	}

	function option_add_transfer_validation( transfer_id ){
		var same_group = '', different_group = '';
	//verifica palabra en el primer campo
		same_group = $( '#together_option' ).val().trim();
		if( same_group != '' ){
			if( same_group.toUpperCase() != 'JUNTO' ){
				alert( `${same_group} no es una opcion valida, verifique y vuelva a intentar` );
				$( '#together_option' ).select();
				return false;
			}
			addTransferBlock( transfer_id );
			return true;
		}
	//verifica palabra en el segundo campo
		different_group = $( '#separate_option' ).val().trim();
		if( different_group != '' ){
			if( different_group.toUpperCase() != 'SEPARADO' ){
				alert( `${different_group} no es una opcion valida, verifique y vuelva a intentar` );
				$( '#separate_option' ).select();
				return false;
			}
			createBlock( transfer_id );
			return true;
		}
	}

	function createBlock( transfer_id = null ){
		var url = "ajax/db.php?fl=makeTransfersGroup&";
		if( transfer_id == null ){
			url += "transfers=" + current_transfers;
		}else{
			url += "transfers=" + transfer_id;
		}
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : " + response[0] );
			return false;
		}
//alert( response  );
		//alert( current_transfers_blocks.includes( response[1] ) );
		if(  ! current_transfers_blocks.includes( response[1] ) && response[1] != '' ){// && transfer_id != null
			//alert( 'here_2' );
			current_transfers_blocks.push( response[1] );
		}
	/*implementacion Oscar 2023 para la sesion de validacion*/
		if( localStorage.getItem( 'validation_token' ) == null ){
//alert( response );
			if( ! create_validation_token( ( response[2] == 0 ? 0 : 1 ) ) ){
				return false;
			}
		}
	/*fin de cambio Oacar 2023*/
		if( transfer_id != null	 ){
			close_emergent();
		}
		reload_transfers_list_view(  );		
	}

	function create_validation_token( is_principal = false ){
		var url = "ajax/db.php?fl=create_validation_token&validation_block_id=" + current_transfers_blocks;
		url += "&make_principal=" + is_principal;
//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
			return false;
		}else{
			localStorage.setItem( 'validation_token', response[1] );
			localStorage.setItem( 'current_validation_block_id', response[2] );
			localStorage.setItem( 'is_principal_validation_session', is_principal );
			return true;
		}
	}

	function addTransferBlock( transfer_id ){
		var url = "ajax/db.php?fl=addTransferBlock&transfer=" + transfer_id;
		url += "&block_id=" + current_transfers_blocks[0];
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
		}else{
			alert( "Transferencia agregada al bloque exitosamente." );
			close_emergent();
			current_transfers.push( transfer_id );
		}
	//	location.reload();
		$( '#transfers_list_content tr' ).each( function ( index ){
			var aux = $( '#validation_list_1_' + index ).html().trim();
			for( var i = 0; i < current_transfers.length; i++ ){
				if( aux == transfer_id ){//current_transfers.includes( aux )
					$( '#validation_list_8_' + index ).prop( 'checked', true );
					$( '#validation_list_9_' + index ).removeClass( 'btn-warning' );
					$( '#validation_list_9_' + index ).addClass( 'btn-success' );
				}
			}
		});
		//reload_transfers_list_view();
		//alert( url );
	}

	function show_block_emergent(){
		var content = `<div class="row">
			<h5>No se puede validar este bloque porque este dispositivo esta ligado actualmente a otro bloque de validacion.</h5>
			<p>En caso de seguir validando el bloque <b>${localStorage.getItem( 'current_validation_block_id' )}</b> 
			escribe "CONTINUAR", de lo contrario escribe "LIBERAR"</p>
			<div class="col-6 text-center">
				<input type="text" id="continue_with_the_same_block_input" class="form-control" placeholder="ESCRIBE CONTINUAR...">
				<br>
				<button class="btn btn-success"
					onclick="current_block_action( 'continuar' )"
				>
					<i class="icon-ok-circle">CONTINUAR</i>
				</button>
			</div>

			<div class="col-6 text-center">
				<input type="text" id="release_block_lock" class="form-control" placeholder="ESCRIBE LIBERAR...">
				<br>
				<button class="btn btn-warning"
					onclick="current_block_action( 'liberar' )"
				>
					<i class="icon-warning">LIBERAR</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

//
	function current_block_action( action ){
		switch( action ){
			case 'continuar' :
				if( $( '#continue_with_the_same_block_input' ).val().trim().toUpperCase() != 'CONTINUAR' ){
					alert( "Debes de escribir la palabra CONTINUAR para esta opcion!" );
					$( '#continue_with_the_same_block_input' ).select();
					return false;
				}
				close_emergent();
				$( '#transfers_seeker' ).val('');
				$( '#transfers_seeker' ).focus();
			break;
			case 'liberar' :
				if( $( '#release_block_lock' ).val().trim().toUpperCase() != 'LIBERAR' ){
					alert( "Debes de escribir la palabra LIBERAR para esta opcion!" );
					$( '#release_block_lock' ).select();
					return false;
				}
				var url = 'ajax/db.php?fl=removeUnicToken&token=' + localStorage.getItem( 'validation_token' );
				var response = ajaxR( url ).split( '|' );
				if( response[0] != 'ok' ){
					alert( "Error : " + response );
				}else{
					localStorage.removeItem("validation_token");
					localStorage.removeItem("current_validation_block_id");
					location.reload();
				}
			break;
		}
	}

	function check_user_permission_to_edit_block( transfer_folio ){
		var url = "ajax/db.php?fl=check_user_permission_to_edit_block";
		url += "&current_transfers=" + current_transfers;
		url += "&folio=" + transfer_folio;
		if( current_transfers_blocks.length > 0 ){
			url += "&validation_block_id=" + current_transfers_blocks;	
		}
		if( localStorage.getItem( 'validation_token' ) != null ){
			url += "&validation_token=" + localStorage.getItem( 'validation_token' );
		}
//alert( url );
		var response = ajaxR( url ).split( '|' );
//alert( response );
		if( response[0] != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		return true;
	}

	function reassign_principal_session_validation( validation_session_id ){
		var url = "ajax/db.php?fl=reassign_principal_session_validation&validation_session_id=" + validation_session_id;
		url += "&validation_block_id=" + current_transfers_blocks;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : " + response );
			return false;
		}
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function cancel_validation_block_lock( validation_block_id ){
		var url = "ajax/db.php?fl=cancel_validation_block_lock&validation_block=" + validation_block_id;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( "Error : " + response );
		}else{
			close_emergent();
		}
	}

	function close_validation_session(){
		var url = "ajax/db.php?fl=close_validation_session&validation_token=" + localStorage.getItem( 'validation_token' );
		url += "&validation_block_id=" + current_transfers_blocks;
//alert( url );
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		}else{
			localStorage.removeItem( 'validation_token' );
			localStorage.removeItem( 'current_validation_block_id' );
			alert( "Sesión de validación finalizada exitosamente!" );
			location.reload();
		}
	}
