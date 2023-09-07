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
						if( $( '#current_store_reception_block' ).val() != $( '#reception_block_' + index ).val().trim() 
							&& global_current_reception_blocks == '' ){	
							alert( "Hay un bloque de recepcion pendiente de finalizar, Solo se puede recibir un bloque a la vez." 
								+ "\nPara agregar esta transferencia es necesario que primero se escaneen las demas transferencias del bloque o se finalice la recepcion del bloque anterior!" );
							$( '#transfers_seeker' ).val();
							//show_emergent_transfers_add( txt );
							stop = true;	
							return false;	
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
						show_emergent_transfers_add( txt );
						stop = true;
						return false;
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
		var was_found = false;
		$( '.transfers_list_content tr' ).each( function ( index ){
			if( $( '#reception_list_3_' + index ).html().trim() == barcode ){
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
				was_found = true;
				//setTimeout( function (){ getAllGroup( index ); }, 100 );
				return true;
			}
		});
		if( ! was_found ){
			alert( "La transferencia escaneada no esta en el Listado" + barcode );
			return false;
		}
		return true;
	}
	/**Funciones de bloque de transferencias**/
	function getAllGroup( counter, origin = null ){
		//alert( 'getAllGroup' );
		var val = 1;
		/*if( global_current_transfer_destinity == '' ){
			global_current_transfer_destinity = $( '#validation_list_4_' + counter ).html().trim();
		}else{*/
			//if( global_current_transfer_destinity != $( '#validation_list_4_' + counter ).html().trim() ){
				/*alert( "Las transferencias por validar deben de ser de la misma sucursal " );
				if( $( '#validation_list_8_' + counter ).prop( 'checked' ) ){
					 $( '#validation_list_8_' + counter ).removeAttr( 'checked' );
				}*/
			//}
		//}
		//alert( 'here' );
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
