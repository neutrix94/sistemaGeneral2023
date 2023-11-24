
	function changeToUpperCase( obj ){
		$( obj ).val( $( obj ).val().toUpperCase() );
	}

	function save_costumer(){
		$( '.emergent_content' ).html( `<br><br><br><h2 class="text-center">Guardando...</h2>` );
		$( '.emergent' ).css( 'display', 'block' );
		var stop = false;
		var costumer_contacts = "";
		var costumer_name, rfc, name, cellphone, telephone, email, person_type, street_name,
			internal_number, external_number, cologne, municipality, 
			postal_code, location, reference, country, state, fiscal_cedule, fiscal_regime, costumer_unique_folio, costumer_id;

	//obtener datos de contacto
		$( '.card' ).each( function( index ){
			costumer_contacts += ( costumer_contacts == "" ? "" : "|~|" );
			if( $( '#costumer_name_input_' + index ).val() == "" ){
				alert( "El nombre de contacto es obligatorio!" );
				close_emergent();
				$( '#costumer_name_input_' + index ).focus();
				stop = true;
				return false;
			}
			costumer_contacts += $( '#costumer_name_input_' + index ).val() + "~";//nombre

			costumer_contacts += "~";//telefono
			
			if( $( '#cellphone_input_' + index ).val() == "" ){
				alert( "El numero telefónico de contacto es obligatorio!" );
				close_emergent();
				stop = true;
				$( '#cellphone_input_' + index ).focus();
				return false;
			}
			costumer_contacts += $( '#cellphone_input_' + index ).val() + "~";//celular
			
			if( $( '#email_input_' + index ).val() == "" ){
				alert( "El correo de contacto es obligatorio!" );
				close_emergent();
				stop = true;
				$( '#email_input_' + index ).focus();
				return false;
			}
			costumer_contacts += $( '#email_input_' + index ).val() + "~";//correo

			if( $( '#cfdi_input_' + index ).val() == 0 ){
				alert( "Elige un uso de CFDI válido!" );
				close_emergent();
				stop = true;
				$( '#cfdi_input_' + index ).focus();
				return false;
			}
			costumer_contacts += $( '#cfdi_input_' + index ).val() + "~";//uso cfdi

			if( $( '#contact_unique_folio_' + index ).val() == "" ){
				costumer_contacts += "~";
			}else{
				costumer_contacts += $( '#contact_unique_folio_' + index ).val() + "~";//folio_unico
			}

			if( $( '#contact_unique_folio_' + index ).val() == "" ){
				costumer_contacts += "";
			}else{
				costumer_contacts += $( '#costumer_contact_id_' + index ).val() + "";//id contacto
			}
		});
		//alert( costumer_contacts ); return false;

		if( stop ){
			return false;
		}
		/*costumer_name = $( '#costumer_name_input' ).val();
		if( telephone == '' ){
			alert( "El campo NOMBRE DEL CLIENTE / EMPRESA no puede ir vacío!" );
			$( '#costumer_name_input' ).focus();
			return false;
		}
		cellphone = $( '#cellphone_input' ).val();
		/*if( cellphone == '' ){
			alert( "El campo CELULAR no puede ir vacío!" );
			$( '#cellphone_input' ).focus();
			return false;
		}*/
		/*telephone = $( '#telephone_input' ).val();
		if( telephone == '' ){
			alert( "El campo TELEFONO no puede ir vacío!" );
			$( '#telephone_input' ).focus();
			return false;
		}

		email = $( '#email_input' ).val();
		if( email == '' ){
			alert( "El campo EMAIL no puede ir vacío!" );
			$( '#email_input' ).focus();
			return false;
		}*/

		rfc = $( '#rfc_input' ).val();
		if( rfc == '' ){
			alert( "El campo RFC no puede ir vacío!" );
			close_emergent();
			$( '#rfc_input' ).focus();
			return false;
		}
//validacion de tipo de Persona
		if( rfc.length == 12 ){
			if( $( '#person_type_combo' ).val() != 3 ){
				alert( "El tipo de persona es incorrecto, verifica y vuleve a intentar!" );
				$( '#person_type_combo' ).focus();
				close_emergent();
				return false;
			}
		}else if( rfc.length == 13 ){
			if( $( '#person_type_combo' ).val() != 2 ){
				alert( "El tipo de persona es incorrecto, verifica y vuleve a intentar!" );
				$( '#person_type_combo' ).focus();
				close_emergent();
				return false;
			}
		}
		name = $( '#name_input' ).val();
		if( name == '' ){
			alert( "El campo NOMBRE/RAZON SOCIAL no puede ir vacío!" );
			$( '#name_input' ).focus();
			return false;
		}
		person_type = $( '#person_type_combo' ).val();
		if( person_type == '' ){
			alert( "El campo TIPO DE PERSONA no puede ir vacío!" );
			$( '#person_type_combo' ).focus();
			return false;
		}

		street_name = $( '#street_name_input' ).val();	
		if( street_name == '' ){
			street_name = "";
		//	alert( "El campo CALLE no puede ir vacío!" );
		//	$( '#street_name_input' ).focus();
		//	return false;
		}

		internal_number = $( '#internal_number_input' ).val();	

		external_number = $( '#external_number_input' ).val();		

		cologne = $( '#cologne_input' ).val();	
		if( cologne == '' ){
			cologne = "";
		//	alert( "El campo no puede ir vacío!" );
		//	$( '#cologne_input' ).focus();
		//	return false;
		}

		municipality = $( '#municipality_input' ).val();	
		if( municipality == '' ){
			municipality = "";
		//	alert( "El campo MUNICIPIO/DELEGACIÓN no puede ir vacío!" );
		//	$( '#municipality_input' ).focus();
		//	return false;
		}

		postal_code = $( '#postal_code_input' ).val();	
		if( postal_code == '' ){
			alert( "El campo CODIGO POSTAL no puede ir vacío!" );
			close_emergent();
			$( '#postal_code_input' ).focus();
			return false;
		}
		
		location = $( '#location_input' ).val();	
		if( location == '' ){
			location = "";
			//alert( "El campo LOCACIÓN no puede ir vacío!" );
			//$( '#location_input' ).focus();
			//return false;
		}
		
		reference = ( $( '#reference_input' ).val() == '' ? '' : $( '#reference_input' ).val() );
		
		country = $( '#country_combo' ).val();
		if( country == '' ){
			country = "";
			//alert( "El campo PAIS no puede ir vacío!" );
			//$( '#country_combo' ).focus();
			//return false;
		}
		
		state = $( '#state_input' ).val();
		if( state.trim() == '' || state.trim == '-- Seleccionar --' ){
			state = "";
			//alert( "El campo ESTADO no puede ir vacío!" );
			//$( '#state_input' ).focus();
			//return false;
		}

		fiscal_regime = $( '#regime_input' ).val();
		if( fiscal_regime == '' ){
			alert( "El campo Regimen Fiscal no puede ir vacío!" );
			close_emergent();
			$( '#regime_input' ).focus();
			return false;
		}

		fiscal_cedule = $( '#fiscal_cedule' ).val();

		costumer_unique_folio = ( $("#costumer_unique_folio").val() == "" ? "" : $("#costumer_unique_folio").val() );
		costumer_id = ( $("#costumer_id").val() == "" ? "" : $("#costumer_id").val() );
/*cellphone : cellphone
costumer_name : costumer_name,
telephone :telephone,
email : email,*/
		//alert( costumer_contacts );
		if( costumer_contacts == '' ){
			alert( "Debes de capturar almenos un contacto para continuar!" );
			close_emergent();
			return false;
		}

		$( '.emergent_content' ).html( "<br><br><br><br><h2 class=\"text-center fs-1\">Guardando...</h2>" );
		$( '.emergent' ).css( "display", "none" );
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			data : { rfc : rfc,
					name : name,
					person_type : person_type,
					street_name : street_name,
					internal_number : internal_number,
					external_number : external_number,
					cologne : cologne,
					municipality : municipality,
					postal_code : postal_code,
					location : location,
					reference : reference,
					country : country,
					state : state,
					token : $( '#current_token' ).val(),
					costumer_contacts : costumer_contacts,
					costumer_fl : 'saveCostumer',
					fiscal_regime : fiscal_regime,
					fiscal_cedule : fiscal_cedule,
					costumer_id : costumer_id
			},
			success : function( dat ){
				if( dat.trim() == 'ok' ){
					var content = `<div class="row" style="padding : 10px;">
						<h2 class="">El usuario fue registrado exitosamente!</h2>
						<br><br>
						<button
							class="btn btn-success form-control"
							onclick="location.href='index.php';"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>`;
					$( '.emergent_content' ).html( content );
					$( '.emergent' ).css( 'display', 'block' );
				}else{
					$( '.emergent_content' ).html( dat );
					$( '.emergent' ).css( 'display', 'block' );
				}
			}
		});
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

var rfc_url = false;
	function check_if_exists_costumer( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var rfc = $( '#rfc_seeker' ).val().trim();
	//condicion si es url

	//alert( rfc.includes( 'https' ) );
		if( rfc.includes( 'https' ) && rfc.includes( '_' )  ){
			rfc_url = true;
			var tmp = rfc.split( '_' );
			rfc = tmp[1];
		}
		if( rfc == "" ){
			alert( "El RFC no puede ir vacio!" );
			$( '#rfc_seeker' ).focus();
			return false;
		}
		var url = "ajax/db.php?costumer_fl=seek_by_rfc&rfc=" + rfc;
		var resp = ajaxR( url ).split( "|" );
		if( resp[0] != 'ok' ){
			//console.log( resp[0] );
			if( rfc_url != false ){
				getDataSat( url );
				var content = `<div class="row">
					<h2 class="text-center text-warning">El cliente no existe, verifica los datos del cliente y captura datos de contacto</h2>
					<div class="col-3"></div>
					<div class="col-6 text-center">
						<button
							type="button"
							class="btn btn-success"
							onclick="close_emergent();"

						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>
				</div>`;
				$( '.emergent_content' ).html( content );
				$( '.emergent' ).css( "display", "block" );
			}else{
				alert( "El rfc " + rfc + " no esta registrado, captura los datos del cliente!" );
			}
		}else{
			var costumer = JSON.parse( resp[1].trim() );
		//muestra emergente
			$( '.emergent_content' ).html( "Cargando datos..." );
			$( '.emergent' ).css( "display", "block" );
		//obtiene los datos de contacto
			getCostumerDB( costumer );
		}
	}

	function getCostumerDB( costumer ){
		$( '.emergent_content' ).html( "<br><br><br><h3 class=\"text-center text-primary\">Obteniendo informacion...</h3>" );
		$( '.emergent' ).css( "display", "block" );
		//$( '#costumer_name_input' ).val( costumer.costumer_id );
		$( '#rfc_input' ).val( costumer.rfc );
		$( '#name_input' ).val( costumer.bussines_name );
		$( '#person_type_combo' ).val( costumer.person_type );
		//$( '#' ).val( costumer.delivery_fiscal_certificate );
		//$( '#' ).val( costumer.fiscal_certificate_url );
		$( '#street_name_input' ).val( costumer.street_name );
		$( '#internal_number_input' ).val( costumer.internal_number );
		$( '#external_number_input' ).val( costumer.external_number );
		$( '#cologne_input' ).val( costumer.cologne );
		$( '#municipality_input' ).val( costumer.municipality );
		$( '#postal_code_input' ).val( costumer.postal_code );
		$( '#state_input' ).val( costumer.state );
		$( '#regime_input' ).val( costumer.tax_regime );
		$( '#fiscal_cedule' ).val( costumer.fiscal_certificate_url );
		$( '#costumer_unique_folio' ).val( costumer.unique_folio );
		$( '#costumer_id' ).val( costumer.costumer_id );
		//$( '#costumer_unique_folio' ).attr( 'disabled', true );
	//carga los datos de contacto
		var contacts = getCostumerContacts( costumer.costumer_id );
		var contacts_view = ``;
		for( var position in contacts ){
			contacts_view += buildCostumerContacts( contacts[position], position );
		}
		$( '#accordion' ).empty();
		$( '#accordion' ).html( contacts_view );

	//	$( '#social_reason_container' ).html( contacts_view );
		if( costumer.fiscal_certificate_url != '' ){
			//getDataSat( costumer.fiscal_certificate_url );
			$( '#rfc_seeker' ).val( costumer.fiscal_certificate_url );
			getDataSat( 'intro' );
		}
	//emergente
		var content = `<div class="row">
			<h2 class="text-center text-primary">El cliente ya existe, verifica los contactos del cliente</h2>
			<h2 class="text-success text-center">Folio de cliente : ${costumer.unique_folio}</h2>
			<div class="col-3"></div>
			<div class="col-6 text-center">
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="close_emergent();"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( "display", "block" );

	}

	function enable_scann_camera(){
		//$.ajax({
		//	type : "post",
		//	url : "reader.php",
		//	cache : false,
		//	success : function( dat ){
				$( '.emergent_content' ).load( "reader.php" );
				$( '.emergent' ).css( 'display', '' );
		//	}
		//});
	}