	function seekTransferByBarcode( e ){
		var key = e.keyCode;
		var txt = $( '#transfers_seeker' ).val().trim().toUpperCase();
		var stop = false;

		if( key == 13 || e == 'intro' ){
			if( txt == '' ){
				alert( "Es necesario ingresar un valor para continuar!" );
			}
		//busca si el bloque de recepcion corresponde
			var is_the_same_block = true;
			$( '.transfers_list_content tr' ).each( function ( index ){
				//alert();
				if( $( '#reception_list_3_' + index ).html().trim() == txt ){
					if( $( '#current_store_reception_block' ).val() != '' ){
						if( $( '#current_store_reception_block' ).val() != $( '#reception_block_' + index ).val().trim() && global_current_reception_blocks == '' ){	
							alert( "Hay un bloque de recepcion pendiente de finalizar, Solo se puede recibir un bloque a la vez." 
								+ "\nPara agregar esta transferencia es necesario que primero se escaneen las demas transferencias del bloque o se finalice la recepcion del bloque anterior!" );
							$( '#transfers_seeker' ).val();
							//show_emergent_transfers_add( txt );
							stop = true;	
							return false;	
						}	
					}
				/*implementacion Oscar 2023 para validar que sea mismo almacen origen y destino entre las transferencias escaneadas*/	
					if( current_origin_warehouse == '' ){
						current_origin_warehouse = $( '#reception_list_10_' + index ).html().trim();
						current_destinity_warehouse = $( '#reception_list_11_' + index ).html().trim();
				//alert(current_origin_warehouse + "\n" + current_destinity_warehouse );
					}else{
						if( current_origin_warehouse != $( '#reception_list_10_' + index ).html().trim() 
							|| current_destinity_warehouse != $( '#reception_list_11_' + index ).html().trim() 
						){
							alert( "Las transferencias por validar deben de ser del mismo almacen origen y destino " );
							if( $( '#reception_list_8_' + counter ).prop( 'checked' ) ){
								 $( '#reception_list_8_' + counter ).removeAttr( 'checked' );
								 $( '#reception_list_9_' + counter ).removeClass( 'btn-success' );
								 $( '#reception_list_9_' + counter ).addClass( 'btn-warning' );
				/*fin de cambio Oscar 2023*/
							}
						}
					}
				//	alert( $( '#reception_block_' + index ).val().trim() + ' != ' + global_current_reception_blocks );
					if( $( '#reception_block_' + index ).val().trim() != global_current_reception_blocks
					 && $( '#reception_block_' + index ).val().trim() != '' 
					 && global_current_reception_blocks != '' ){
						is_the_same_block = false;
					}

					if( $( '#reception_block_' + index ).val().trim() == '' && global_current_reception_blocks != '' ){	
//alert( $( '#reception_block_' + index ).val().trim() + " == " + global_current_reception_blocks );
					 //	alert(  $( '#reception_block_' + index ).val().trim() );		
						if( check_user_permission_to_edit_block( txt ) == true ){
							show_emergent_transfers_add( txt );
							stop = true;
							return false;
						}
					}

					if( $( '#reception_block_' + index ).val().trim() != '' ){
						global_current_reception_blocks = $( '#reception_block_' + index ).val().trim();
						global_current_transfers.push( $( '#reception_list_2_' + index ).html().trim() );
					}
				}
			});
			if( stop == true ){
				return false;
			}
			if( is_the_same_block == false ){
				alert( "La transferencia no se puede asignar porque pertenece a un bloque diferente al que se esta recibiendo!" );
				$( '#transfers_seeker' ).val();
				$( '#transfers_seeker' ).focus();
				return false;
			}

			/*if( global_current_transfers.length > 0 ){
				show_emergent_transfers_add( txt );
				return false;
			}*/

			if ( ! setTransferCheck( txt ) ){
				$( '#transfers_seeker' ).select();
			}else{
				$( '#transfers_seeker' ).val( '' );
			}
		}
		return false;
	}

	function setTransferCheck( barcode ){
		//alert( 'setTransferCheck' );
		var was_found = 'false';
		var block_id = '';
		var was_found = '';
		$( '.transfers_list_content tr' ).each( function ( index ){
			if( $( '#reception_list_3_' + index ).html().trim() == barcode ){
				was_found = index;
				block_id = $( '#reception_block_' + index ).attr( 'value' ).trim();
//alert(block_id);
				$( '#receive_' + index ).prop( 'checked', true );
				$( '#validation_list_9_' + index ).removeClass( 'btn-warning' );
				$( '#validation_list_9_' + index ).addClass( 'btn-success' );
			//agrega la transferencia a las transferencias por setear
				if( global_transfers_to_set.indexOf( $( '#reception_list_2_' + index ).html().trim() ) == -1 ){
					global_transfers_to_set.push( $( '#reception_list_2_' + index ).html().trim() );
				}
				if( ! $( '#reception_block_' + index ).prop( 'checked' ) ){
					$( '#reception_block_' + index ).click();
					getAllGroup( index );
				}
			//	alert( 'global_transfers_to_set :' + global_transfers_to_set );
				//setTimeout( function (){ getAllGroup( index ); }, 100 );
				return true;
			}
		});
		if( was_found == 'false' ){
			alert( "La transferencia escaneada no esta en el Listado : " + barcode );
			return false;
		}

		if( block_id != '' ){
			if( ! validateBlock_is_not_editing( block_id ) ){
				$( '#reception_block_' + was_found ).removeAttr( 'checked' );
				$( '#reception_block_' + was_found ).removeAttr( 'disabled' );
				$( '#validation_list_9_' + was_found ).removeClass( 'btn-success' );
				$( '#validation_list_9_' + was_found ).addClass( 'btn-warning' );
				return false;
			}
		}else{//verifica si el usuario tiene el permiso de edición
			if( ! check_user_permission_to_edit_block( barcode ) ){
				$( '#reception_block_' + was_found ).removeAttr( 'checked' );
				$( '#reception_block_' + was_found ).removeAttr( 'disabled' );
				$( '#validation_list_9_' + was_found ).removeClass( 'btn-success' );
				$( '#validation_list_9_' + was_found ).addClass( 'btn-warning' );
				return false;
			}
		}
		return true;
	}

	function  validateBlock_is_not_editing( block_id ) {
		var url = "ajax/db.php?fl=validate_permission_block&reception_block_id=" + block_id;
		if( localStorage.getItem( 'reception_token' ) != null ){
			url += "&reception_token=" + localStorage.getItem( 'reception_token' );
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

	function check_user_permission_to_edit_block( transfer_folio ){
		var url = "ajax/db.php?fl=check_user_permission_to_edit_block";
		url += "&current_transfers=" + global_current_transfers;
		url += "&folio=" + transfer_folio;
//alert( url );
		if( global_current_reception_blocks.length > 0 ){
			url += "&reception_block_id=" + global_current_reception_blocks;	
		}
		if( localStorage.getItem( 'reception_token' ) != null ){
			url += "&reception_token=" + localStorage.getItem( 'reception_token' );
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

	/**Funciones de bloque de transferencias**/
	function getAllGroup( counter, origin = null ){
	//	alert( 'getAllGroup' );
		var val = 1;
		var validation_block = $( '#reception_list_1_' + counter ).html().trim();
		var reception_block = $( '#reception_block_' + counter ).val().trim();
		var action = ( $( '#reception_block_' + counter ).prop( 'checked' ) ? 1 : 0 );
		if( $( '#reception_block_' + counter ).prop( 'checked' ) ){
			val = 1;
		}
		$( '.transfers_list_content tr' ).each( function( index ){
			if( ( $( '#reception_block_' + index ).val().trim() == reception_block 
				&& reception_block != '') || ( $( '#reception_list_1_' + index ).val().trim() == validation_block 
				&& validation_block != '' )  ){
				//alert( 'here_2' );
				if( val == 1 ){
					$( '#reception_block_' + index ).prop( 'checked', true );
					$( '#receive_' + index ).prop( 'checked', true );
				//	$( '#reception_block_' + index ).prop( 'checked', true );
				}else{
					$( '#reception_block_' + index ).removeAttr( 'checked' );
					$( '#receive_' + index ).removeAttr( 'checked' );

				//	$( '#reception_block_' + index ).removeAttr( 'checked' );
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
		/*if( without_sucursal == 0 ){
			global_current_transfer_destinity = '';
		}*/
	}
	/*
		global_current_transfers
		global_current_validation_blocks
		global_current_reception_blocks
	*/

	function setGlobalBlock( counter ){
		//alert('setGlobalBlock');
	//saca el bloque de validacion
		var validation_block = $( '#reception_list_1_' + counter ).html().trim();
		var reception_block = $( '#reception_block_' + counter ).val().trim();
		var action = ( $( '#reception_block_' + counter ).prop( 'checked' ) ? 1 : 0 );
	//recorre tabla
		$( '.transfers_list_content tr' ).each( function ( index ){
			if( ( ( $( '#reception_list_1_' + index ).html().trim() == validation_block ) 
				|| ( $( '#reception_list_1_' + index ).val().trim() == reception_block && reception_block != '' ) )
				|| ( $( '#reception_block_' + index ).val() == reception_block && $( '#reception_block_' + index ).val() != '' ) 
			){
				if( action == 1 ){
					$( '#receive_' + index ).prop( 'checked', true );
					$( '#reception_block_' + index ).prop( 'checked', true );
					$( '#reception_block_' + index ).attr( 'disabled', true );
				}/*else{
					$( '#receive_' + index ).removeAttr( 'checked' );
					$( '#reception_block_' + index ).removeAttr( 'checked' );
				}*/
			}
		});
	}

	function validateGroups(){
		var resp = true;
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#reception_list_5_' + index ).children( 'input' ).prop( 'checked' ) == true 
				&& $( '#validation_list_9_' + index ).hasClass( 'btn-warning' ) == true ){
				resp = false;
				return false;
			}
		});
		return resp;
	}

	function setTransfersAfter(){

	}


//emergente para agregar transferencias al bloque
	function show_emergent_transfers_add( barcode ){
	//	alert( transfer_folio );
		var url = "ajax/db.php?fl=getMessageToAddTransfer&transfers=" + global_current_transfers;
		url += "&transfer_to_add=" + barcode;
		url += "&reception_block_id=" + global_current_reception_blocks;
		url += "&reception_token=" + localStorage.getItem( 'reception_token' );
//alert( url );
		var response = ajaxR( url );
		//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function option_add_transfer_validation( transfer_id, reception_block_id ){
		var same_group = '', different_group = '';
	//verifica palabra en el primer campo
		same_group = $( '#together_option' ).val().trim();
		if( same_group != '' ){
			if( same_group.toUpperCase() != 'JUNTO' ){
				alert( `${same_group} no es una opcion valida, verifique y vuelva a intentar` );
				$( '#together_option' ).select();
				return false;
			}
			addTransferBlock( transfer_id, reception_block_id );
			return true;
		}
	/*verifica palabra en el segundo campo
		different_group = $( '#separate_option' ).val().trim();
		if( different_group != '' ){
			if( different_group.toUpperCase() != 'SEPARADO' ){
				alert( `${different_group} no es una opcion valida, verifique y vuelva a intentar` );
				$( '#separate_option' ).select();
				return false;
			}
			createBlock( transfer_id );
			return true;
		}*/
	}

	function createBlock( transfer_id = null ){//global_current_reception_blocks
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
		//alert( response[0] + ' - ' + response[1]  );
		//alert( current_transfers_blocks.includes( response[1] ) );
		if(  ! current_transfers_blocks.includes( response[1] ) && response[1] != '' ){// && transfer_id != null
			//alert( 'here_2' );
			current_transfers_blocks.push( response[1] );
		}
		if( transfer_id != null	 ){
			close_emergent();
		}
		reload_transfers_list_view();
		
	}


	function create_reception_token( is_principal = false ){
		var url = "ajax/db.php?fl=create_reception_token&reception_block_id=" + global_current_reception_blocks;
		url += "&make_principal=" + is_principal;
		//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
			return false;
		}else{
			localStorage.setItem( 'reception_token', response[1] );
			localStorage.setItem( 'current_reception_block_id', response[2] );
			localStorage.setItem( 'is_principal_reception_session', is_principal );
			return true;
		}
	}


	function addTransferBlock( transfer_id, reception_block_id ){
		/*var url = "ajax/db.php?fl=addTransferBlock&transfer=" + transfer_id;
		url += "&block_id=" + reception_block_id;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
		}else{
			alert( "Transferencia agregada al bloque exitosamente." );
			close_emergent();
			current_transfers.push( transfer_id );
		}*/
		global_transfer_to_add.push( transfer_id );
		$( '.transfers_list_content tr' ).each( function ( index ){
			if( $( '#reception_list_2_' + index ).html().trim() == transfer_id ){
				$( '#reception_block_' + index ).click();
				$( '#validation_list_9_' + index ).removeClass( 'btn-warning' );
				$( '#validation_list_9_' + index ).addClass( 'btn-success' );
				close_emergent();
			}
		});
		//reload_transfers_list_view();
		//alert( url );
	}


	function show_block_emergent(){
		var content = `<div class="row">
			<h5>No se puede recibir este bloque porque este dispositivo esta ligado actualmente a otro bloque de recepcion.</h5>
			<p>En caso de seguir validando el bloque <b>${localStorage.getItem( 'current_reception_block_id' )}</b> 
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
				var url = 'ajax/db.php?fl=removeUnicToken&token=' + localStorage.getItem( 'reception_token' );
				var response = ajaxR( url ).split( '|' );
				if( response[0] != 'ok' ){
					alert( "Error : " + response );
				}else{
					localStorage.removeItem("reception_token");
					localStorage.removeItem("current_reception_block_id");
					location.reload();
				}
			break;
		}
	}


	function validate_option_unique_code(){
		var option_type;
		if( $( '#validate_option_unique_code_add' ).val() != "" 
			|| $( '#validate_option_unique_code_resolution' ).val() != "" ){	
			if( $( '#validate_option_unique_code_add' ).val() != ""  ){
		//verifica si es agregar transferencia
				if( $( '#validate_option_unique_code_add' ).val().trim().toLowerCase() != "" 
					&& $( '#validate_option_unique_code_add' ).val().trim().toLowerCase() != "agregar" ){
					alert( "La palabra escrita es incorrecta, escribe la palabra 'agregar' para continuar!" );
				}else{
					option_type = 'add';
				}

			}else if( $( '#validate_option_unique_code_resolution' ).val() != "" ){
		//verifica si es agregar resolucion
				if( $( '#validate_option_unique_code_resolution' ).val().trim().toLowerCase() != "" 
					&& $( '#validate_option_unique_code_resolution' ).val().trim().toLowerCase() != "resolucion" ){
					alert( "La palabra escrita es incorrecta, escribe la palabra 'resolucion' para continuar!" );
				}else{
					option_type = 'resolution';
				}
			}
		}else{
			alert( "Es necesario escribir la palabra indicada en la opcion deseada!" );
			return false;
		}
	//valida password de usuario
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
	//alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		close_emergent();
		if( option_type == 'add' ){
			$( '.mnu_item.invoices' ).click();
			$( '#transfers_seeker' ).focus();
		}else{
			alert( "El producto fue enviado a resolucion exitosamente." );
		}
	}

	function close_reception_session(){
		var url = "ajax/db.php?fl=close_reception_session&reception_token=" + localStorage.getItem( 'reception_token' );
		url += "&reception_block_id=" + global_current_reception_blocks;
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		}else{
			localStorage.removeItem( 'reception_token' );
			localStorage.removeItem( 'current_reception_block_id' );
			alert( "Sesión de recepción finalizada exitosamente!" );
			location.reload();
		}
	}

	function cancel_reception_block_lock( reception_block_id ){
		var url = "ajax/db.php?fl=cancel_reception_block_lock&reception_block=" + reception_block_id;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( "Error : " + response );
		}else{
			close_emergent();
		}
	}

	function reassign_principal_session_reception( reeception_session_id ){
		var url = "ajax/db.php?fl=reassign_principal_session_reception&reception_session_id=" + reeception_session_id;
		url += "&reception_block_id=" + localStorage.getItem( 'current_reception_block_id' );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : " + response );
			return false;
		}
		$( '.emergent_content' ).html( response[1] );
		$( '.emergent' ).css( 'display', 'block' );
	}
