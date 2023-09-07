	var global_location_has_changed = 0;
//buscador de ubicacion de productos ( solo finalizar )
	function seekProductsLocations( obj, e ){
		var txt = $( obj ).val();
		key = e.keyCode;
		var is_scanner = ( key == 13 || e == 'enter' ? 1 : 0 );
		/*if( is_scanner ) 
			alert( "Es scanner" );*/
		
		if( txt.length <= 2 ){
			$( '.product_location_seeker_response' ).html();
			$( '.product_location_seeker_response' ).css( 'display', 'none' );
			return false;
		}
	//omite código único
		if( is_scanner == 1 ){
		//omite codigo de barras si es el caso
			var tmp_txt = txt.split( ' ' );
			if( tmp_txt.length == 4 ){
				/*if( $( '#skip_unique_barcodes' ).val().trim() == 0 ){
					global_tmp_unique_barcode = txt;
				}*/
				txt = '';
				for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
					txt += ( txt != '' ? ' ' : '' );
					txt += tmp_txt[i];
				}
			}
			//alert('is_scanner');
		}//alert( txt );
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'seekProductsLocations',
					key :  txt,
					scanner : is_scanner
			},
			success : function ( dat ){
				$( '.product_location_seeker_response' ).html( dat );
				$( '.product_location_seeker_response' ).css( 'display', 'block' );
				if( is_scanner == 1 ){
				//	setTimeout( function(){
						$( '#location_response_0' ).click();
				//	}, 300 );
				}
			}
		});
	}

//carga las opciones
	function getProductLocationOptions( product_provider_id = null, type = null, tmp_location_id ){
		if( type == null ){
			type = '_seeker';
		}
		var url = "ajax/db.php?fl=getProductLocationOptions";
		if( product_provider_id != null ){
			url += "&product_provider_id=" + product_provider_id;
		}
		if( tmp_location_id != null ){
			url += "&tmp_location_id=" + tmp_location_id;
		}
//alert( url );
		var response = ajaxR( url );
		//alert( 'is_the_same : ' + global_is_the_same_product_provider + "\nresp : " + response );
		$( '#location_status' + type ).empty();
		$( '#location_status' + type ).append( response );
		getLocationDetail( $( '#location_status' + type ).val(), '_source' );
	}

	function change_location( type = null ){
		if( type == null ){
			type = 'seeker';
		}
		var location = $( '#location_status_' + type + ' option:selected' ).text().split(':');
		location = location[1];
		if( $( '#location_status_' + type ).val() != 2 ){
			location = '';
		}
		if ( type == 'source' ) {
			$( '#product_location_' + type ).val( location );
		}else{
			$( '#product_location_' + type ).val( location )
		}
		if( $( '#location_status_' + type ).val() != 0 && $( '#location_status_' + type ).val() != 'no_location'  ){
			$( '#new_location_form_' + type ).css( 'height', 'auto' );
			if( $( '#location_status_' + type ).val() != 'new_location' ){
				getLocationDetail( $( '#location_status_' + type ).val() , '_' + type );
			}else{
				if( $( '#location_status_' + type ).val() == 'new_location' ){
					$( '#aisle_' + type + '_since' ).val( '' );
					$( '#location_number_' + type + '_since' ).val( '' );
					$( '#aisle_from_' + type ).val( '' );
					$( '#level_from_' + type ).val( '' );

					$( '#aisle_' + type + '_to' ).val( '' );
					$( '#location_number_' + type + '_to' ).val( '' );
					$( '#aisle_until_' + type ).val( '' );
					$( '#level_to_' + type ).val( '' );
					$( '#enabled' + type ).prop( 'checked', true );

					$( '#enabled_' + type ).removeAttr( 'checked' );
					$( '#is_principal_' + type ).removeAttr( 'checked' );
				}
			}
		}else{
			$( '#new_location_form_' + type ).css( 'height', '0' );
		}
	}
	function make_new_location( type ){
		var letter, location_number, row_from, row_until, final_location='', level_from = '', level_to = '';

	/*letra pasillo de 
		letter = $( '#aisle_' + type + '_since' ).val();
		if( letter == '' ){
			alert( "El pasillo \"de\" no puede ir vacío!" );
			$( '#aisle_' + type + '_since' ).focus();
			return false;
		}
		final_location += letter;
	//numero pasillo de 
		location_number = $( '#location_number_' + type + '_since' ).val();
		if( location_number == '' ){
			alert( "La ubicacion \"de\" no puede ir vacía!" );
			$( '#location_number_' + type + '_since' ).focus();
			return false;
		}
		final_location += location_number;
	//letra pasillo a 
		letter = '-' + $( '#aisle_' + type + '_to' ).val();
		if( letter == '' ){
			alert( "El pasillo \"a\" no puede ir vacío!" );
			$( '#aisle_' + type + '_to' ).focus();
			return false;
		}
		final_location += letter;
	//numero pasillo a 
		location_number = $( '#location_number_' + type + '_to' ).val();
		if( location_number == '' ){
			alert( "La ubicacion \"a\" no puede ir vacía!" );
			$( '#location_number_' + type + '_to' ).focus();
			return false;
		}
		final_location += location_number;
	//fila - pasillo
		row_from = $( '#aisle_from_' + type ).val();
		if( row_from != '' ){
			final_location += ' f/p' + row_from;
			row_until = $( '#aisle_until_' + type ).val();	
			if( row_until != '' ){
				final_location += '-' + row_until;
			}
		}
	//nivel
		level_from = $( '#level_from_' + type ).val();
		if( level_from != '' ){
			final_location += ' nv' + level_from;
			level_to = $( '#level_to_' + type ).val();
			if( level_to != '' ){
				final_location += '-' + level_to;
			}
		}*/
			

		$( '#product_location_' + type ).val( final_location );
		$( "#location_status_" + type + " option[value=2]" ).text( "Ubicación actual : " + final_location );
		$( '#new_location_form_' + type ).css( 'height', '0' );
	//limpia los campos
		$( '#aisle_' + type + '_since' ).val( '' );
		$( '#location_number_' + type + '_since' ).val( '' );

		$( '#aisle_' + type + '_to' ).val( '' );
		$( '#location_number_' + type + '_to' ).val( '' );

		$( '#aisle_from_' + type ).val( '' );
		$( '#aisle_until_' + type ).val( '' );
		
		$( '#level_from_' + type ).val( '' );
		$( '#level_to_' + type ).val( '' );
	}

	function saveNewLocation( type ){
			
		if( ! validate_location_fields( type ) ){
			return false;
		}

		var new_status = 1;
		if( $( "#location_status" + type ).val() == 'new_location' ){
			new_status = 3;
		}else if( $( "#location_status" + type ).val() != 'no_location' ){
			new_status = 2;
		}
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { fl : 'changeProductLocation',
					p_k : $( '#product_id_location_form' + type ).val(),
					/*new_location : $( '#product_location_seeker' ).val(),*/
					location_id : $( "#location_status" + type ).val(),
					new_status : $( "#location_status" + type ).val(),
					
					location_letter_from  : $( '#aisle' + type + '_since' ).val(),
					location_number_from  : $( '#location_number' + type + '_since' ).val(),
					aisle_from : $( '#aisle_from' + type + '' ).val(),
					level_from  : $( '#level_from' + type + '' ).val(),

					location_letter_to  : $( '#aisle' + type + '_to' ).val(),
					location_number_to  : $( '#location_number' + type + '_to' ).val(),
					aisle_to : $( '#aisle_until' + type ).val(),
					level_to : $( '#level_to' + type ).val(),
					
					product_id : ( type == '_seeker' ? $( '#product_id_location_form' + type ).val() : $( '#product_id' ).val() ),
					product_provider_id : ( type == '_seeker' ? $( '#product_provider_id_location_form' + type ).val() : $( '#product_provider' ).val() ),
					
					is_enabled : ( $( '#enabled' + type ).prop( 'checked' ) ? '1' : '0' ),
					is_principal : ( $( '#is_principal' + type ).prop( 'checked' ) ? '1' : '0' ),

					is_temporal_location : ( global_is_the_same_product_provider == 1 && type == '_source' || type == '_seeker' ? 0 : 1 ),
					reception_detail_id : $( '#reception_detail_id' ).val(),
					type : type

			},
			success : function ( dat ){
				var response = dat.split( '|' );
				if( response[0] != 'ok' ){
					alert( "Error : \n" + dat );
					return false;
				}else{
					var selected_value = $( '#location_status' + type ).val();
					//cleanProductLocationForm();//limpia formulario de ubicacion de productos
					if( type != '_source' ){
						alert( "Los cambios fueron guardados exitosamente!" );
					}
					if( type == '_source' && $( '#reception_detail_id' ).val() == '' ){
						$( '#location_tmp_id' ).val( response[1] );
					}
				//recarga opciones
					if( type == '_source' && global_is_the_same_product_provider == 0 ){//si es registro temporal
						getProductLocationOptions( null, type, response[1] );
					}else{
						getProductLocationOptions( ( type == '_seeker' ?  $( '#product_provider_id_location_form' + type ).val() : $( '#product_provider' ).val() ), type );
					}
					setTimeout( function (){
						$( '#location_status' + type ).val( selected_value );
					//obtiene detalle
						getLocationDetail( selected_value, type );
						global_location_has_changed = 0;//resetea variable de detección de cambio
						$( '#save_location_btn' ).prop( 'disabled', true );
					}, 200 );
				}
			}
		});
	}

	function  validate_location_fields( type ){//_source, _seeker
	//ubicacion desde
		if( $( '#aisle' + type + '_since' ).val() == '' ){
			alert( 'La letra desde no puede ir vacía' );
			$( '#aisle' + type + '_since' ).focus();
			return false;
		}
		if( $( '#location_number' + type + '_since' ).val() == '' ){
			alert( 'El nímero desde no puede ir vacío' );
			$( '#location_number' + type + '_since' ).focus();
			return false;
		}	
	//ubicacion hasta
		if( $( '#aisle' + type + '_to' ).val() == '' ){
			alert( 'La letra desde no puede ir vacía' );
			$( '#aisle' + type + '_to' ).focus();
			return false;
		}
		if( $( '#location_number' + type + '_to' ).val() == '' ){
			alert( 'El número desde no puede ir vacío' );
			$( '#location_number' + type + '_to' ).focus();
			return false;
		}
	//pasillo
		if( $( '#aisle_from' + type ).val() != ''  ){
			if( $( '#aisle_until' + type ).val() == '' ){
				alert( 'El número de pasillo hasta no puede ir vacío si existe un pasillo desde, si solo ocupa un pasillo escribe el mismo en ambos campos' );
				$( '#aisle_until' + type ).focus();
				return false;
			}
		}
	//altura
		if( $( '#level_from' + type ).val() != ''  ){
			if( $( '#level_to' + type ).val() == '' ){
				alert( 'La altura hasta no puede ir vacía si existe una altura desde, si solo ocupa una altura escribela la misma en ambos campos' );
				$( '#level_to' + type ).focus();
				return false;
			}
		}

		return true;
	}
	function getLocationDetail( location_id, type = null ){
		if( location_id == 0 || location_id == 'no_location' || location_id == 'new_location' ){
			//alert( 'none' );
			return false;
		}
		if( type == 'null' ){
			type = '_seeker';
		}
		var url = 'ajax/db.php?fl=getLocationDetail&location_id=' + location_id;
		var response = ajaxR( url ).split( '|' );
		console.log( response );
		//alert( response[1] +  response[2] );
		if( response[0] != 'ok' ){
			alert( "Error  : " + response[0] );
			return false;
		}else{
			if( response[1] != 'null' ){
				var resp = JSON.parse( response[1] );
			//piso
				$( '#floor_from' + type ).html( resp.floor_from );
				$( '#aisle' + type + '_since' ).val( resp.location_letter_from );
				$( '#location_number' + type + '_since' ).val( resp.location_number_from );
				$( '#aisle_from' + type ).val( resp.aisle_from );
				$( '#level_from' + type ).val( resp.level_from  );

				$( '#aisle' + type + '_to' ).val( resp.location_letter_to );
				$( '#location_number' + type + '_to' ).val( resp.location_number_to );
				$( '#aisle_until' + type + '' ).val( resp.aisle_to );
				$( '#level_to' + type + '' ).val( resp.level_to );
			//habilitado
				if( resp.enabled == 1 ){
					 $( '#enabled' + type ).prop( 'checked', true );
				}else{
					 $( '#enabled' + type ).removeAttr( 'checked' );
				}
			//es unbicación principal
				if( resp.is_principal == 1 ){
					 $( '#is_principal' + type ).prop( 'checked', true );
				}else{
					//alert( 'here' );
					 $( '#is_principal' + type ).removeAttr( 'checked' );
					 //if( type == 'product_provider' ){
					 	$( '#is_principal' + type ).attr( 'onchange', 'disabled_principal_location( ' + ( type == '_source' ? $('#product_provider' ).val().trim() : $( '#product_provider_id_location_form' + type ).val().trim() ) + ', false, \'' + type + '\' );' );
					 //}
				}
			}else{
				clean_location_form( type.split('_').join('') );
			}
		}

	}

	function disabled_principal_location( product_provider_id, confirm = false, type = null ){
	//obtiene ubicación principal
		if( confirm == false ){//muestra formulario
			var url = 'ajax/db.php?fl=getPrincipalLocation&product_provider_id=' + product_provider_id;
			url += "&type=" + type;
			var response = ajaxR( url );
			//alert( response );
			if( response == 'no_exists_principal' ){
				return false;
			}
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			$( '.emergent' ).css( 'position', 'fixed' );
			return false;
		}else if( confirm == 'just_change' || confirm == 'disabled' ){//deshabilita la emergente
			var url = 'ajax/db.php?fl=disabledPrincipalLocation&location_id=' + $( '#product_provider_location_id_aux' ).val();
			url += "&action=" + confirm;
			var response = ajaxR( url );
			//alert( response );
		}else if( confirm == 'cancel' ){
			$( '#is_principal' + type ).removeAttr( 'checked' );
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			$( '.emergent' ).css( 'position', 'absolute' );
			return false;
		}
		setTimeout( function (){
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			$( '.emergent' ).css( 'position', 'absolute' );
			saveNewLocation( type );
			//$( '#save_location_btn' ).click();
		}, 100 );
	}

//seleccionar un producto del buscador ( solo aplica en finalizar )
	function setProductLocation( location_array ){
	//	alert(location_array);
		cleanProductLocationForm( 'seeker' );
		clean_location_form( 'seeker' );
		var location_data = location_array.split( '~' );
		$( '#product_id_location_form_seeker' ).val( location_data[0] );
		$( '#product_name_location_form_seeker' ).html( location_data[1] );//nombre
		$( '#product_inventory_recived' ).val( location_data[2] );
		$( '#product_inventory_no_ubicated' ).val( location_data[3] );
	//oculta resultados de busqueda
		$( '.product_location_seeker_response' ).html( '' );
		$( '.product_location_seeker_response' ).css( 'display', 'none' );
		$( '#location_status_seeker' ).removeAttr( 'disabled' );
		$( '#seeker_product_location' ).val( '' );
	//$( '#product_location_seeker' ).val( location_data[5] );

		$( '#product_provider_id_location_form_seeker' ).val( location_data[6] );//proveedor producto

		getProductLocationOptions( location_data[6] );
		
		//$( "#location_status_seeker").val( ( location_data[5] != '' && location_data[4] == 3 ? 2 : location_data[4] ) );
		$( '#product_seeker_location_form_btn' ).css( 'display', 'none' );
		$( '#product_reset_location_form_btn' ).css( 'display', 'block' );

		setTimeout( function (){ getLocationDetail( $( "#location_status_seeker").val(), '_seeker' ); }, 300 );
	}

	function cleanProductLocationForm( type = 'seeker' ){
		$( '#product_id_location_form_' + type ).val( '' );
		$( '#product_id_location_form_' + type ).attr( 'disabled', 'true' );
		$( '#product_name_location_form_' + type ).html( '' );
		$( '#product_name_location_form_' + type ).attr( 'disabled', 'true' );
		$( '#product_inventory_recived' ).val( '' );
		$( '#product_inventory_recived' ).attr( 'disabled', 'true' );
		$( '#product_inventory_no_ubicated' ).val( '' );
		$( '#product_inventory_no_ubicated' ).attr( 'disabled', 'true' );
		$( '#location_status_' + type ).val( 0 );
		$( '#location_status_' + type ).attr( 'disabled', 'true' );
		$( '#product_location_' + type ).val( '' );
		$( '#product_location_' + type ).attr( 'disabled', 'true' );

		$( '#product_provider_id_location_form_seeker' ).val( '' );
		$( '#product_seeker_location_form_btn' ).css( 'display', 'block' );
		$( '#product_reset_location_form_btn' ).css( 'display', 'none' );
		clean_location_form( type );
	}

	function validate_is_new_row(){
		var is_equal = 1;
	//validacion de modelo
		if( $( '#product_model' ).val() != $( '#db_product_model' ).val() && $( '#db_product_model' ).val() != '' ){
			var message = 'El modelo es diferente : ';
			message += $( '#product_model' ).val() + "/" + $( '#db_product_model' ).val();
			$( '#product_model_message' ).html( message );
			$( '#product_model_message' ).css( 'display', 'block' );
			is_equal = 0;
		}else{
			$( '#product_model_message' ).html( '' );
			$( '#product_model_message' ).css( 'display', 'none' );
		}
	//validacion de codigo pieza
		if ( $('#piece_barcode' ).val() != $( '#db_piece_barcode' ).val()  
				|| ( $('#piece_barcode' ).val() != $( '#db_piece_barcode_2' ).val() && $('#db_piece_barcode_2' ).val() != '' )
				|| ( $('#piece_barcode' ).val() != $( '#db_piece_barcode_3' ).val() && $('#db_piece_barcode_3' ).val() != '' )
		){
			var message = 'El codigo de pieza es diferente : '; 
				message += $( '#piece_barcode' ).val() + "/" + $( '#db_piece_barcode' ).val() + ', '; 
				message += $( '#db_piece_barcode_2' ).val() + ', ' + $( '#db_piece_barcode_3' ).val();
			$( '#piece_barcode_message' ).html( message );
			$( '#piece_barcode_message' ).css( 'display', 'block' );
			is_equal = 0;
		}else{
			$( '#piece_barcode_message' ).html( '' );
			$( '#piece_barcode_message' ).css( 'display', 'none' );
		}
	//validacion de piezas por paquete
		if( $( '#pieces_per_pack' ).val() != $( '#db_pieces_per_pack' ).val() ){
			var message = 'Las piezas por paquete son diferentes : '; 
			message += $( '#pieces_per_pack' ).val() + "/" + $( '#db_pieces_per_pack' ).val();
			$( '#pieces_per_pack_message' ).html( message );
			$( '#pieces_per_pack_message' ).css( 'display', 'block' );
			is_equal = 0;
		}else{
			$( '#pieces_per_pack_message' ).html( '' );
			$( '#pieces_per_pack_message' ).css( 'display', 'none' );
		}
	//validacion de codigos de paquetes
		if( $( '#pack_barcode' ).val() != $( '#db_pack_barcode' ).val() 
				|| ( $('#pack_barcode' ).val() != $( '#db_pack_barcode_2' ).val() && $( '#db_pack_barcode_2' ).val() != '' ) ){
			var message = 'El codigo de paquete es diferente : '; 
			message += $( '#pack_barcode' ).val() + "/" + $( '#db_pack_barcode' ).val() + ', '; 
			message += $( '#db_pack_barcode_2' ).val();
			$( '#pack_barcode_message' ).html( message );
			$( '#pack_barcode_message' ).css( 'display', 'block' );
			is_equal = 0;
		}else{
			$( '#pack_barcode_message' ).html( '' );
			$( '#pack_barcode_message' ).css( 'display', 'none' );
		}
	//validacion de piezas por caja
		if( $( '#pieces_per_box' ).val() != $( '#db_pieces_per_box' ).val() ){
			var message = 'Las piezas por caja son diferentes : '; 
			message += $( '#pieces_per_box' ).val() + "/" + $( '#db_pieces_per_box' ).val();
			$( '#pieces_per_box_message' ).html( message );
			$( '#pieces_per_box_message' ).css( 'display', 'block' );
			is_equal = 0;	
		}else{
			$( '#pieces_per_box_message' ).html( '' );
			$( '#pieces_per_box_message' ).css( 'display', 'none' );
		}
	//validacion de codigos de caja
		if( $( '#box_barcode' ).val() != $( '#db_box_barcode' ).val() 
				|| ( $('#box_barcode' ).val() != $( '#db_box_barcode_2' ).val() && $( '#db_box_barcode_2' ).val() != '' ) ){
			var message = 'El codigo de paquete es diferente : '; 
				message += $( '#box_barcode' ).val() + "/" + $( '#db_box_barcode' ).val() + ', '; 
				message += $( '#db_box_barcode_2' ).val();
			$( '#box_barcode_message' ).html( message );
			$( '#box_barcode_message' ).css( 'display', 'block' );
			is_equal = 0;
		}else{
			$( '#box_barcode_message' ).html( '' );
			$( '#box_barcode_message' ).css( 'display', 'none' );
		}
		//global_is_the_same_product_provider = is_equal;
		//alert( is_equal );
		return is_equal;
	}

	function detect_location_change( type = null ){
		//alert();
		global_location_has_changed = 1;
		$( '#save_location_btn' + type ).removeAttr( 'disabled' );

			//$( '#save_location_btn' ).html( 'disabled' );
	}

	function clean_location_form( type ){
		$( '#aisle_' + type + '_since' ).val( '' );
		$( '#location_number_' + type + '_since' ).val( '' );
		$( '#aisle_from_' + type ).val( '' );
		$( '#level_from_' + type ).val( '' );

		$( '#aisle_' + type + '_to' ).val( '' );
		$( '#location_number_' + type + '_to' ).val( '' );
		$( '#aisle_until_' + type ).val( '' );
		$( '#level_to_' + type ).val( '' );
		$( '#enabled_' + type ).removeAttr( 'checked' );
		$( '#is_principal_' + type ).removeAttr( 'checked' );
	//solo para buscador
		$( '#product_inventory_recived' ).val( '' );
		$( '#product_inventory_no_ubicated' ).val( '' );

		$( '#product_location_seeker' ).val( '' );
		$( '#location_status_source' ).val( 0 );
		global_location_has_changed = 0;
	}

	function clean_messages_descriptions(){
		$( '#product_model_message' ).html( '' );
		$( '#product_model_message' ).css( 'display', 'none' );

		$( '#piece_barcode_message' ).html( '' );
		$( '#piece_barcode_message' ).css( 'display', 'none' );

		$( '#pieces_per_pack_message' ).html( '' );
		$( '#pieces_per_pack_message' ).css( 'display', 'none' );

		$( '#piece_barcode_message' ).html( '' );
		$( '#piece_barcode_message' ).css( 'display', 'none' );

		$( '#pieces_per_box_message' ).html( '' );
		$( '#pieces_per_box_message' ).css( 'display', 'none' );

		$( '#box_barcode_message' ).html( '' );
		$( '#box_barcode_message' ).css( 'display', 'none' );

	}

//filtrado de caracteres
	function character_filter( obj, rule, depend_obj = null ){
		//alert();
		var value = $( obj ).val();
		switch( rule ){
			case 'cappital_letter' :
				if( depend_obj != null && $( depend_obj ).val() == ''  ){
					alert( "Es necesario que primero se escriba el valor desde " );
					$( obj ).val( '' );
					$( depend_obj ).focus();
					return false; 
				}
				var tmp = '';
				for( var i = 0; i < value.length; i++ ){
					if( isNaN( value[i] ) ){
						tmp += value[i];
					}else{
						alert( 'El campo solo puede contener letras' );
					}
				}
				$(obj).val( tmp.toUpperCase() );
			break;
			case 'numeric' : 
				if( depend_obj != null && $( depend_obj ).val() == ''  ){
					alert( "Es necesario que primero se escriba el valor desde " );
					$( obj ).val( '' );
					$( depend_obj ).focus();
					return false; 
				}
				var tmp = '';
				for( var i = 0; i < value.length; i++ ){
					if( ! isNaN( value[i] ) ){
						tmp += value[i];
					}else{
						alert( 'El campo solo puede contener números' );
					}
				}
				$(obj).val( tmp );
			break;
			case 'lower_case' : 
				if( depend_obj != null && $( depend_obj ).val() == ''  ){
					alert( "Es necesario que primero se escriba el valor desde " );
					$( obj ).val( '' );
					$( depend_obj ).focus();
					return false; 
				}
				var tmp = '';
				for( var i = 0; i < value.length; i++ ){
					if( isNaN( value[i] ) ){
						tmp += value[i];
					}else{
						alert( 'El campo solo puede contener letras' );
					}
				}
				$(obj).val( tmp.toLowerCase() );
			break;	
		}
	}

	function show_product_provider_barcodes(){
		if( $( '#product_provider_id_location_form_seeker' ).val().trim() == '' ){
			alert( "Es necesario buscar y seleccionar un producto para continuar." );
			$( '#seeker_product_location' ).focus();
			return false;
		}

		var url = "ajax/db.php?fl=getProductProviderBarcodes&product_provider_id=" + $( '#product_provider_id_location_form_seeker' ).val().trim();
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}


