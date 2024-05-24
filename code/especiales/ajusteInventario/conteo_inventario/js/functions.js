var location_range_since, location_range_to, count_type;

	function show_initial_configuration(){
		show_emergent( initial_configuration, false, false );		
		$( '#warehouse_id' ).focus();
	}

	function setLocationRange(){
		current_warehouse  = $( '#warehouse_id' ).val();
		if( current_warehouse == 0 ){
			alert( `Debes de seleccionar un almacén válido para continuar!` );
			$( '#warehouse_id' ).focus();
			return false;
		}

		var url = 'ajax/inventory.php?inventory_fl=insertProductProvidersInTemporalCount';
		/*url += '&range_since=' + location_range_since; 
		url += '&range_to=' + location_range_to;*/
		url += '&warehouse_id=' + current_warehouse;
		var response = ajaxR( url );//
		if( response.trim() == 'invalid_store' ){
			exit_by_session_error();
			return false;
		}
		//alert( response );

		if( ! $( '#is_per_product' ).prop( 'checked' ) ){
			location_range_since = $( '#range_since' ).val();
			
			if( location_range_since == "" ){
				alert( "Ingresa una ubicacion valida" );
				$( '#range_since' ).focus();
				return false;
			}
			location_range_to = $( '#range_to' ).val();
			if( location_range_to == "" ){
				alert( "Ingresa una ubicacion valida" );
				$( '#range_to' ).focus();
				return false;
			}
			count_type = 'specific';
			getNextProduct();
		}else{
			var pass = $( "#mannager_password" ).val();
			if( pass == "" ){
				alert( "La contraseña no puede ir vacia!" );
				$( "#mannager_password" ).focus();
				return false;
			}else{
				if( ! validate_mannager_password( pass ) ){
					$( "#mannager_password" ).focus();
					return false;
				}else{
					count_type = 'per_product';
				}
			}
			getOmitedProducts();
		}
	//inserta los registros que faltan de insertar
			setTimeout( function (){
				if( current_product == null 
					|| ( current_product.is_maquiled == 0
					|| current_product.is_without_tag == 0 ) ){
					close_emergent();
				}
				$( '#principal_seeker' ).focus();
			}, 100);
	}

	function validate_mannager_password( password ){
		var url = "ajax/inventory.php?inventory_fl=check_mannager_password&password=" + password;
		var response = ajaxR( url );
		if( response == 'ok' ){
			return true;
		}else{
			if( response.trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( response );//"La contraseña es incorrecta, verifica y vuelve a intentar : " + 
			return false;
		}
	}

	function change_function_type(){
		if( $( '#is_per_product' ).prop( 'checked' ) ){
			$( "#range_since" ).val( '' );
			$( "#range_since" ).attr( 'disabled', 'true' );

			$( "#range_to" ).val( '' );
			$( "#range_to" ).attr( 'disabled', 'true' );
			$( ".range_input_container" ).css( 'display', 'none' );
			$( "#is_per_product_password_container" ).css( 'display', 'flex' );

			$( "#order_by" ).attr( 'disabled', 'true' );
			$( "#category_combo" ).attr( 'disabled', 'true' );
			$( "#subcategory_combo" ).attr( 'disabled', 'true' );
			$( "#subtype_combo" ).attr( 'disabled', 'true' );
			$( ".is_per_location_range_container" ).css( 'display', 'none' );
			$( ".is_per_product_container" ).css( 'display', 'flex' );

			$( '#mannager_password' ).focus();

		}else{
			$( "#range_since" ).removeAttr( 'disabled' );
			//$( "#range_since" ).css( 'display', 'block' );
			$( "#range_to" ).removeAttr( 'disabled' );
			$( ".range_input_container" ).css( 'display', 'flex' );
			$( "#is_per_product_password_container" ).css( 'display', 'none' );

			$( "#order_by" ).removeAttr( 'disabled');
			$( "#category_combo" ).removeAttr( 'disabled' );
			$( "#subcategory_combo" ).removeAttr( 'disabled' );
			$( "#subtype_combo" ).removeAttr( 'disabled' );
			$( ".is_per_location_range_container" ).css( 'display', 'flex' );
			$( ".is_per_product_container" ).css( 'display', 'none' );

			$( '#range_since' ).focus();
		}
	}


	function show_emergent( content, btn_acept = false, show_close_btn = true ){
		if( btn_acept != false ){
			content += `<div class="row">
							<div class="col-2"></div>
							<div class="col-8">
								<button
									class="btn btn-success form-control"
									onclick="close_emergent();"
								>
									<i class="icon-ok-circled">Acceptar</i>
								</button>
							</div>
						</div>`;
		}
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.close_emergent_bnt' ).css( 'display', ( show_close_btn ? "block" : "none" ) );
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}
	function getStoreWharehouses(){
		var url = "ajax/inventory.php?inventory_fl=getStoreWhareouses";
		var response =  ajaxR( url );
		if( response.trim() == 'invalid_store' ){
			exit_by_session_error();
			return false;
		}
		return response;
	}

	function redirect( type ){
		switch( type ){
			case 'home' : 
				if( confirm( "Salir de esta pantalla?" ) )
					location.href="../../../../index.php?";
			break;
		}
	}

	function ommit_product_provider(){
		var url = "ajax/inventory.php?inventory_fl=ommit_product_provider&product_provider=" + current_product.product_provider_id;
		url += "&warehouse_id=" + current_warehouse;
		var response = ajaxR( url ).split('|');
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error : " + response );
		}else{
			clean_current_product();
			getOmitedProducts();
			if( count_type == 'specific' ){
				getNextProduct();
			}
		}		
	}

	function getOmitedProducts(){
		var url = "ajax/inventory.php?inventory_fl=getOmitedProducts&warehouse_id=" + current_warehouse;
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			if( response[0].trim() == 'invalid_store' ){
				exit_by_session_error();
				return false;
			}
			alert( "Error : " + response );
		}else{
			$( '#omited_products_list' ).empty();
			$( '#omited_products_list' ).append( response[1] );
		}

	}

	function seek_product_by_box_form( type ){
		var barcode = $( '#box_barcode' ).val();
		//var 
		close_emergent();
		if( type == 1 ){
			seek_product( 'intro', barcode );
		}else{
			//alert(  'here' );
			validate_barcode( 'intro', barcode, 1 );	
			if( ( current_product.codigo_barras_caja_1 == barcode && current_product.codigo_barras_caja_1 != '' )
			|| ( current_product.codigo_barras_caja_2 == barcode && current_product.codigo_barras_caja_2 != '' ) ){
			//	close_emergent();
			}
		}
	}

	function change_combo( type ){
		var url = "";
		switch( type ){
			case 1:
				url = "ajax/inventory.php?inventory_fl=getSubcategories&category_id=" + $( '#category_combo' ).val();
				var response = ajaxR( url );
				if( response.trim() == 'invalid_store' ){
					exit_by_session_error();
					return false;
				}
				//alert( response );
				$( '#subcategory_combo' ).empty();
				$( '#subcategory_combo' ).append( response );
			break;

			case 2 :
				url = "ajax/inventory.php?inventory_fl=getSubtypes&subcategory_id=" + $( '#subcategory_combo' ).val();
				var response = ajaxR( url );
				if( response.trim() == 'invalid_store' ){
					exit_by_session_error();
					return false;
				}
				//alert( response );
				$( '#subtype_combo' ).empty();
				$( '#subtype_combo' ).append( response );
			break;

			case 3:

			break; 
		}
		setTimeout( function(){
			getNextProduct();
		}, 100);
	}

	var initial_configuration = `
		<div class="row">
			<h3>Selecciona el almacén en el que se va a ajustar el inventario : </h3>
			<div class="col-3"></div>
			<div class="col-6">
		` + 
			getStoreWharehouses()
			+ `<br>
			</div>
			<h3>Ingresa el rango de Ubicaciones que vas a contar</h3>

			<div class="col-12 text-center">
				<br>
				<input type="checkbox" id="is_per_product" onclick="change_function_type();">
				<label class="">Por producto:</label>				
			</div>
			<div class="col-1"></div>
			<div class="col-5 text-center range_input_container">
				Desde : <input type="text" class="form-control range_input" id="range_since">
			</div>
			<div class="col-5 text-center range_input_container">
				Hasta : <input type="text" class="form-control range_input" id="range_to">
			</div>
			<div class="col-1"></div>
			<div class="col-12 text-center">
				<br>
				<div class="row" id="is_per_product_password_container">
					<div class="col-2"></div>
					<div class="col-8">
						<p>Ingresa  contraseña de encargado : </p>
						<input type="password" class="form-control" id="mannager_password">
					</div>	
				</div>
				<br>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8 text-center">
						<button
							class="btn btn-success form-control"
							onclick="setLocationRange();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
						<br><br>
						<button
							class="btn btn-danger form-control"
							onclick="redirect( 'home' );"
						>
							<i class="icon-cancel-circled">Cancelar y Salir</i>
						</button>
					</div>
			</div>
		</div>`;

		var boxForm =  `
			<div class="row">
				<h5>Escanea el código de barras de la caja : </h5>
				<div class="col-3"></div>
				<div class="col-6 text-center">
					<br>
					<input type="text" id="box_barcode" class="form-control">
					<br>
					<button
						type="button"
						class="btn btn-success"
						onclick="seek_product_by_box_form( $_type_id );"
					>
						<i  class="icon-ok-circle">Aceptar</i>
					</button>
				</div>
			</div>`;

	function exit_by_session_error(){
		alert( "Esta pantalla solo se puede abrir logueado en sucursal matriz, logueate en Matriz para continuar!" );
		location.href = "../../../../index.php";
		return false;
	}

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
