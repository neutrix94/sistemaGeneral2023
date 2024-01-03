
	function getCostumerContacts( costumer_id ){
		var url = "ajax/db.php?costumer_fl=getCostumerContacts&costumer_id=" + costumer_id;
		var resp = ajaxR( url ).split( "|" );
		if( resp[0] != 'ok' ){
			alert( "Error al consultar los datos de contacto del cliente : " + resp );
		}else{
			var contacts = JSON.parse( resp[1] );
			return contacts;
		}
	}
	function add_contact_form(  ){
		var content = buildCostumerContacts( null, $( '.card' ).length );
		$( '#accordion' ).append( content );
		var tmp =  $( '.card' ).length - 1;
		$( `#header_btn_${tmp}` ).click();
	}
	function buildCostumerContacts( contact = null, position ){
		var content = ``;
		var color = "rgba( 0,0,0,.1 )";
		if( position % 2 != 0 ){
			color = "white";
		}
		//var position = ;
		var cfdis = '';
		if( contact != null ){
			cfdis = getCfdis( ( contact.cdfi_use != '' && contact.cdfi_use != null ? contact.cdfi_use : null ) );
		}else{
			cfdis = getCfdis( null );
		}
		content += `<div class="accordion-item card" id="contact_container_${position}" style="background-color : ${color};">
			<h2 class="accordion-header" id="heading${position}">
				<button 
					type="button" 
					class="accordion-button collapsed" 
					data-bs-toggle="collapse" 
					data-bs-target="#collapse${position}"
			    	aria-expanded="true" 
			    	aria-controls="collapse${position}"
			    	id="header_btn_${position}" 
			    	class=""
			    >
		        	<p>Nombre :<span id="span_name_${position}" style="margin-right: 20px !important;">${contact == null ? '' : contact.name}</span></p>
		        	<br>
		        	<p>Correo :<span id="span_email_${position}">${contact == null ? '' : contact.email}</span></p>
		        </button>
		    </h2>

		    <div id="collapse${position}" class="collapse" aria-labelledby="heading${position}" data-parent="#accordion">
		      	<div class="card-body row">
		      		<div class="col-sm-6">
						Nombre de Contacto<span class="text-danger">*</span>
					<input type="text" id="costumer_name_input_${position}" 
						value="${contact == null ? '' : contact.name}" class="form-control" 
						onblur="changeToUpperCase( this );"
						onkeyup="validate_is_just_text( event, this );change_accordion_header( 'name', ${position}, this );"
					>
					</div>
					<!--div class="col-sm-6">
						Telefono <span class="text-danger">*</span>
						<input type="number" id="telephone_input_${position}" value="${contact == null ? '' : contact.telephone}" class="form-control">
					</div-->
					<div class="col-sm-6">
						Celular <span class="text-danger">*</span>
						<input type="number" id="cellphone_input_${position}" 
						value="${contact == null ? '' : contact.cellphone}" 
						class="form-control">
					</div>
					<div class="col-sm-6">
						Correo <span class="text-danger">*</span>
						<input type="email" id="email_input_${position}" 
							value="${contact == null ? '' : contact.email}" 
							class="form-control"
							onkeyup="change_accordion_header( 'email', ${position}, this );"
						>
					</div>
					<div class="col-sm-6">
						Uso CFDI <span class="text-danger">*</span>
						<select class="form-select" id="cfdi_input_${position}">
						${cfdis}
						</select>
					<br>
					</div>
					<div class="col-sm-6">
						Folio Único
						<input type="text" id="contact_unique_folio_${position}" 
							value="${contact == null ? '' : contact.unique_folio}" 
							class="form-control"
							disabled
						>
					</div>
					<div class="col-sm-6">
						Id Cliente
						<input type="text" id="costumer_contact_id_${position}" 
							value="${contact == null ? '' : contact.contact_id}" 
							class="form-control"
							disabled
						>
					<br>
					</div>
					<div>
						<button 
							type="button"
							class="btn btn-danger"
							onclick="delete_contact( ${position} )"
							${contact != null ? 'disabled' : ''} 
						>
							<i class="icon-canceled-circled">Eliminar</i>
						</button>
					</div>
		        </div>
		    </div>`;
		return content;
	}
	
	function delete_contact( pos ){
		if( !confirm( "Eliminar contacto?" ) ){
			return false;
		}
		$( '#contact_container_' + pos ).remove();
	}

	function change_accordion_header( type, position, obj ){
		var value = $( obj ).val();
		$( `#span_${type}_${position}` ).html( value );
	}

	function getCfdis( cfdi = null ){
		var url = "ajax/db.php?costumer_fl=getCfdis";
		if( cfdi != null ){
			url += "&cfdi=" + cfdi;
		}
		var resp = ajaxR( url );
	//alert(resp);
		return resp;	
	}
//obtener todos los datos de contacto
	function getContactsData(){
		var contacts = "";
		var contact_name = '', contact_telephone = '', contact_cellphone = '', 
		contact_email = '', contact_cfdi = 0;
		for( var i = 0; i < $( '.card' ).length; i++ ){
			contacts += ( contacts == '' ? '' : '|~|' );
		//nombre
			contact_name = $( `#costumer_name_input_${i}` ).val();
			if( contact_name.length <= 0 ){
				alert( "El nombre del contacto no puede ir vacio!" );
				( $( `#header_btn_${i}` ).hasClass( 'collapsed' ) ? $( `#header_btn_${i}` ).click() : null );
				$( `#costumer_name_input_${i}` ).focus();
				return false;
			}
			contacts += contact_name + '~';
		//telefono
			contact_telephone = $( `#telephone_input_${i}` ).val();
			/*if( contact_telephone.length <= 0 ){
				alert( "El telefono del contacto no puede ir vacio!" );
				( $( `#header_btn_${i}` ).hasClass( 'collapsed' ) ? $( `#header_btn_${i}` ).click() : null );
				$( `#telephone_input_${i}` ).focus();
				return false;
			}*/
			contacts += contact_telephone += '~';
		//celular
			contact_cellphone = $( `#cellphone_input_${i}` ).val();
			if( contact_cellphone.length <= 0 ){
				alert( "El celular del contacto no puede ir vacio!" );
				$( `#header_btn_${i}` ).click();
				$( `#cellphone_input_${i}` ).focus();
				return false;
			}
			contacts += contact_cellphone += '~';
		//email
			contact_email = $( `#email_input_${i}` ).val();
			if( contact_email.length <= 0 ){
				alert( "El correo del contacto no puede ir vacio!" );
				( $( `#header_btn_${i}` ).hasClass( 'collapsed' ) ? $( `#header_btn_${i}` ).click() : null );
				$( `#email_input_${i}` ).focus();
				return false;
			}
			contacts += contact_email += '~';
		//email
			contact_cfdi = $( `#cfdi_input_${i}` ).val();
			if( contact_cfdi.length <= 0 ){
				alert( "El uso de cfdi del contacto no puede ir vacio!" );
				( $( `#header_btn_${i}` ).hasClass( 'collapsed' ) ? $( `#header_btn_${i}` ).click() : null );
				$( `#cfdi_input_${i}` ).focus();
				return false;
			}
			contacts += contact_cfdi;
		}
		//alert( contacts );
		return contacts;
	}