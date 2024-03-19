audio_is_playing = null;
	
	function validate_is_just_text( event ){
        var inputValue = event.target.value;
        var sanitizedValue = inputValue.replace(/[^a-zA-Z0-9]/g, '');
        event.target.value = sanitizedValue;
	}
	//document.getElementById('inputSinEspeciales').addEventListener('input', function(event) {
      //  var inputValue = event.target.value;
       // var sanitizedValue = inputValue.replace(/[^a-zA-Z0-9]/g, '');
        //event.target.value = sanitizedValue;
    //});

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

	function changeToUpperCase( obj ){
		$( obj ).val( $( obj ).val().toUpperCase() );
		if( $( obj ).attr( 'id' ) == 'name_input' && $( obj ).val() != '' ){
			var tmp = $( obj ).val().trim();
			tmp = tmp.replaceAll( 'Á', 'A' );
			tmp = tmp.replaceAll( 'É', 'E' );
			tmp = tmp.replaceAll( 'Í', 'I' );
			tmp = tmp.replaceAll( 'Ó', 'O' );
			tmp = tmp.replaceAll( 'Ú', 'U' );
			tmp = tmp.replaceAll( '"', '\"' );
			//tmp = tmp.replaceAll( '&AMP;', '&amp;' );
			tmp = tmp.replaceAll( '&AMP;', '&' );
			$( obj ).val( tmp );
		}
	}

	function save_costumer(){
		/*$( '.emergent_content' ).html( `<div class="text-center"><br><br><br><h2 class="text-center">Guardando...</h2>
				<img src="../../../../img/img_casadelasluces/load.gif" widt="50%"></div>` );
		$( '.emergent' ).css( 'display', 'block' );*/
		setTimeout( function(){
			$( '.emergent_content' ).html( `<div class="text-center"><br><br><br><h2 class="text-center">Guardando...</h2>
				<img src="../../../../img/img_casadelasluces/load.gif" widt="50%"></div>` );
		$( '.emergent' ).css( 'display', 'block' );

		}, 100 );
		var stop = false;
		var costumer_contacts = "";
		var costumer_name, rfc, name, cellphone, telephone, email, person_type, street_name,
			internal_number, external_number, cologne, municipality, 
			postal_code, location, reference, country, state, fiscal_cedule, fiscal_regime, costumer_unique_folio, costumer_id;
	//elimina los contactos vacios
		$( '.card' ).each( function( index ){
			var data_counter = 0;
			costumer_contacts += ( costumer_contacts == "" ? "" : "|~|" );
			if( $( '#costumer_name_input_' + index ).val() != "" ){
				data_counter ++;
			}
			if( $( '#cellphone_input_' + index ).val() != "" ){
				data_counter ++;
			}
			if( $( '#email_input_' + index ).val() != "" ){
				data_counter ++;
			}
			if( $( '#cfdi_input_' + index ).val() != 0 ){
				data_counter ++;
			}
			if( $( '#contact_unique_folio_' + index ).val() != "" ){
				data_counter ++;
			}
			if( $( '#contact_unique_folio_' + index ).val() != "" ){
				data_counter ++;
			}
			//alert( data_counter );
			if( data_counter == 0 ){
				$( this ).remove();
			}
		});
	//obtener datos de contacto
		$( '.card' ).each( function( index ){
			costumer_contacts += ( costumer_contacts == "" ? "" : "|~|" );
			if( $( '#costumer_name_input_' + index ).val() == "" ){
				alert( "El nombre de contacto es obligatorio!" );
				alert_scann( "error" );
				setTimeout( function(){ close_emergent(); }, 100 );
				$( '#costumer_name_input_' + index ).focus();
				stop = true;
				return false;
			}
			costumer_contacts += $( '#costumer_name_input_' + index ).val() + "~";//nombre

			costumer_contacts += "~";//telefono
			
			if( $( '#cellphone_input_' + index ).val() == "" ){
				alert( "El numero telefónico de contacto es obligatorio!" );
				alert_scann( "error" );
					setTimeout( function(){
						close_emergent();
					}, 100 );
				stop = true;
				$( '#cellphone_input_' + index ).focus();
				return false;
			}
			costumer_contacts += $( '#cellphone_input_' + index ).val() + "~";//celular
			
			if( $( '#email_input_' + index ).val() == "" ){
				alert( "El correo de contacto es obligatorio!" );
				alert_scann( "error" );
				setTimeout( function(){
					close_emergent();
				}, 100 );
				stop = true;
				$( '#email_input_' + index ).focus();
				return false;
			}
			costumer_contacts += $( '#email_input_' + index ).val() + "~";//correo

			if( $( '#cfdi_input_' + index ).val() == 0 ){
				alert( "Elige un uso de CFDI válido!" );
				alert_scann( "error" );
				setTimeout( function(){
					close_emergent();
				}, 100 );
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
			alert_scann( "error" );
			alert( "El campo RFC no puede ir vacío!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
			$( '#rfc_input' ).focus();
			return false;
		}
//validacion de tipo de Persona
		if( rfc.length == 12 ){
			if( $( '#person_type_combo' ).val() != 3 ){
				alert_scann( "error" );
				alert( "El tipo de persona es incorrecto, verifica y vuleve a intentar!" );
				setTimeout( function(){
					close_emergent();
				}, 100 );
				$( '#person_type_combo' ).focus();
				return false;
			}
		}else if( rfc.length == 13 ){
			if( $( '#person_type_combo' ).val() != 2 ){
				alert_scann( "error" );
				alert( "El tipo de persona es incorrecto, verifica y vuleve a intentar!" );
				setTimeout( function(){
					close_emergent();
				}, 100 );
				$( '#person_type_combo' ).focus();
				return false;
			}
		}
		name = $( '#name_input' ).val();
		if( name == '' ){
			alert_scann( "error" );
			alert( "El campo NOMBRE/RAZON SOCIAL no puede ir vacío!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
			$( '#name_input' ).focus();
			return false;
		}
		person_type = $( '#person_type_combo' ).val();
		if( person_type == '' ){
			alert_scann( "error" );
			alert( "El campo TIPO DE PERSONA no puede ir vacío!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
			$( '#person_type_combo' ).focus();
			return false;
		}

		street_name = $( '#street_name_input' ).val();	
		if( street_name == '' ){
			street_name = "";
		}

		internal_number = $( '#internal_number_input' ).val();	

		external_number = $( '#external_number_input' ).val();		

		cologne = $( '#cologne_input' ).val();	
		if( cologne == '' ){
			cologne = "";
		}

		municipality = $( '#municipality_input' ).val();	
		if( municipality == '' ){
			municipality = "";
		}

		postal_code = $( '#postal_code_input' ).val();	
		if( postal_code == '' ){
			alert_scann( "error" );
			alert( "El campo CODIGO POSTAL no puede ir vacío!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
			$( '#postal_code_input' ).focus();
			return false;
		}
		
		location = $( '#location_input' ).val();	
		if( location == '' ){
			location = "";
		}
		
		reference = ( $( '#reference_input' ).val() == '' ? '' : $( '#reference_input' ).val() );
		
		country = $( '#country_combo' ).val();
		if( country == '' ){
			country = "";
		}
		
		state = $( '#state_input' ).val();
		if( state.trim() == '' || state.trim == '-- Seleccionar --' ){
			state = "";
		}

		fiscal_regime = $( '#regime_input' ).val();
		if( fiscal_regime == '' ){
			alert_scann( "error" );
			alert( "El campo Regimen Fiscal no puede ir vacío!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
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
			alert_scann( "error" );
			alert( "Debes de capturar almenos un contacto para continuar!" );
			setTimeout( function(){
				close_emergent();
			}, 100 );
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
				dat = dat.trim().split( '|' );
				if( dat[0]== 'ok' ){
					var content = `<div class="row text-center" style="padding : 10px;">
						<h2 class="text-success text-center">El cliente fue registrado exitosamente!</h2>
						<br><br>
						<h2 class="text-primary text-center">Folio del cliente : <b>${dat[1]}</b></h2>
						<br><br>
						<button
							class="btn btn-success form-control"
							onclick="location.href='index.php';"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>`;
					alert_scann( "costumer_saved" );
					setTimeout( function(){
							$( '.emergent_content' ).html( content );
							$( '.emergent' ).css( 'display', 'block' );
					}, 100);
				}else{
					alert_scann( "error" );
					setTimeout( function(){
						$( '.emergent_content' ).html( dat );
						$( '.emergent' ).css( 'display', 'block' );
					}, 100);
				}
			}
		});
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function check_if_exists_costumer( e ){
		var rfc_url = false;
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var rfc = $( '#rfc_seeker' ).val().trim();
	//condicion si es url

	//alert( rfc.includes( 'https' ) );
		if( rfc.includes( 'https' ) && rfc.includes( '_' )  ){
			rfc_url = rfc;
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

		$( '#accordion' ).html( '' );

		if( resp[0] != 'ok' ){
			//console.log( resp[0] );
			if( rfc_url != false ){
				getDataSat( rfc_url );
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
				alert_scann( 'new_costumer_with_constance' );
			}else{
				var content = `<div class="row">
					<h2 class="text-center text-warning">El rfc ${rfc} no esta registrado, captura los datos del cliente!</h2>
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
				//alert( "El rfc " + rfc + " no esta registrado, captura los datos del cliente!" );
				$( '#rfc_input' ).val( rfc.toUpperCase() );

				$( "#regime_input" ).children( 'option' ).each( function( index ){
					$( this ).css( 'display', 'block' );
				});
				alert_scann( 'new_costumer_without_constance' );
			}
		}else{
			var costumer = JSON.parse( resp[1].trim() );
		//muestra emergente
			$( '.emergent_content' ).html( "Cargando datos..." );
			$( '.emergent' ).css( "display", "block" );
		//obtiene los datos de contacto
			getCostumerDB( costumer );
			if( rfc_url ){
				getDataSat( rfc_url );
			}
		}
	}

	function getCostumerDB( costumer ){
		$( '#street_name_input' ).val( "" );
		$( '#street_name_input' ).removeAttr( "disabled" );
		$( '#internal_number_input' ).val( "" );
		$( '#internal_number_input' ).removeAttr( "disabled" );
		$( '#external_number_input' ).val( "" );
		$( '#external_number_input' ).removeAttr( "disabled" );
		$( '#cologne_input' ).val( "" );
		$( '#cologne_input' ).removeAttr( "disabled" );
		$( '#municipality_input' ).val( "" );
		$( '#municipality_input' ).removeAttr( "disabled" );
		$( '#postal_code_input' ).val( "" );
		$( '#postal_code_input' ).removeAttr( "disabled" );
		$( '#state_input' ).val( "" );
		$( '#state_input' ).removeAttr( "disabled" );
		$( '#regime_input' ).val( "" );
		$( '#regime_input' ).removeAttr( "disabled" );
	//muestra todas las opciones de los regimenes
		$( "#regime_input" ).children( 'option' ).each( function( index ){
			$( this ).css( 'display', 'block' );
		});


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
		//if( costumer.fiscal_certificate_url != '' ){
			//getDataSat( costumer.fiscal_certificate_url );
		//	$( '#rfc_seeker' ).val( costumer.fiscal_certificate_url );
		//	getDataSat( 'intro' );
		//}
		//alert( costumer.fiscal_certificate_url );
		if( costumer.fiscal_certificate_url != '' && costumer.fiscal_certificate_url != null	 ){
			setTimeout( function(){
				getDataSat( costumer.fiscal_certificate_url );
			}, 300 );
		}else{

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

		alert_scann( 'costumer_exists' );

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