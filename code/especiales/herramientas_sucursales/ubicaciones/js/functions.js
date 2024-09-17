	var global_location_has_changed = 0;
//buscador de ubicacion de productos
	function seekProductsLocations( obj, e ){
		var txt = $( obj ).val();
		key = e.keyCode;
		var is_scanner = ( key == 13 || e == 'enter' ? 1 : 0 );
		
		if( txt.length <= 2 ){
			$( '.product_location_seeker_response' ).html( '' );
			$( '.product_location_seeker_response' ).css( 'display', 'none' );
			return false;
		}
	//omite código único
		if( is_scanner == 1 ){
		//omite codigo de barras unico si es el caso
			var tmp_txt = txt.split( ' ' );
			if( tmp_txt.length == 4 ){
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
			data : { location_fl : 'seekProductsLocations',
					key :  txt,
					scanner : is_scanner
			},
			success : function ( dat ){
				//alert( dat );
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

	function setProductLocation( location_array ){
		cleanProductLocationForm();
		clean_location_form();
		var location_data = location_array.split( '~' );
		console.log( location_data );
		$( '#product_id' ).val( location_data[0] );
		getProductLocations( location_data[0] );
		$( '#product_name' ).html( location_data[1] );//nombre
		$( '#product_inventory_recived' ).val( location_data[2] );
		$( '#product_inventory_no_ubicated' ).val( location_data[3] );
	//oculta resultados de busqueda
		$( '.product_location_seeker_response' ).html( '' );
		$( '.product_location_seeker_response' ).css( 'display', 'none' );
		$( '#location_status_seeker' ).removeAttr( 'disabled' );
		$( '#seeker_product_location' ).val( '' );
	//$( '#product_location_seeker' ).val( location_data[5] );

		$( '#product_provider_id_location_form_seeker' ).val( location_data[6] );//proveedor producto

//getProductLocationOptions( location_data[6] );
		
		//$( "#location_status_seeker").val( ( location_data[5] != '' && location_data[4] == 3 ? 2 : location_data[4] ) );
		$( '#product_seeker_location_form_btn' ).css( 'display', 'none' );
		$( '#product_reset_location_form_btn' ).css( 'display', 'block' );
		setTimeout( function (){ 
				if( location_data[7] == 1 ){
					if( $( '#locations_list tr' ).length <= 0 ){
						$( '#is_principal' ).prop( 'checked', true );
					}else{
						$( '#is_principal' ).removeAttr( 'checked' );
					}
				}else{
					$( '#is_principal' ).removeAttr( 'checked' );
					$( '#is_principal' ).attr( 'disabled', true );
				}
				if( location_data[8] == 1 ){
					$( '#is_supplied' ).prop( 'checked', true );
				}else{
					$( '#is_supplied' ).removeAttr( 'checked' );
				}
			}, 100);
//setTimeout( function (){ getLocationDetail( $( "#location_status_seeker").val(), '_seeker' ); }, 300 );
	}

	function cleanProductLocationForm( ){
		$( '#product_id' ).val( '' );
		$( '#product_name' ).html( '' );
		$( '#locations_list' ).html('');
		$( '#is_principal' ).removeAttr( 'disabled' );
		/*$( '#product_id_location_form_' + type ).val( '' );
		$( '#product_id_location_form_' + type ).attr( 'disabled', 'true' );
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
		clean_location_form( type );*/
	}

	function clean_location_form(){
		$( '#location_number_from' ).val( '' );
		$( '#aisle_from' ).val( '' );
		$( '#level_from' ).val( '' );

		$( '#location_number_until' ).val( '' );
		$( '#aisle_until' ).val( '' );
		$( '#level_until' ).val( '' );

		$( '#is_enabled').removeAttr( 'checked' );
		$( '#is_principal' ).removeAttr( 'checked' );
		$( '#store_location_id' ).val( '' );
	//solo para buscador

		//$( '#product_location_seeker' ).val( '' );
		//$( '#location_status_source' ).val( 0 );
		//global_location_has_changed = 0;
	}
//validacion de entradas de texto
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
	//para llenar automaticamente los campos hasta
		var tmp_obj_id = $( obj ).attr( 'id' );
		if( tmp_obj_id == 'location_number_from' ){
			$( '#location_number_until' ).val( $(obj).val() );
		}
		if( tmp_obj_id == 'aisle_from' ){
			$( '#aisle_until' ).val( $(obj).val() );
		}
		if( tmp_obj_id == 'level_from' ){
			$( '#level_until' ).val( $(obj).val() );
		}
	}

	function detect_location_change( ){
		//alert();
		global_location_has_changed = 1;
		$( '#save_location_btn' ).removeAttr( 'disabled' );

			//$( '#save_location_btn' ).html( 'disabled' );
	}

	function validate_only_one(){
		var url = "ajax/db.php?location_fl=validate_only_one&product_id=" + $( '#product_id' ).val();
		url += "&store_location_id=" + $( '#store_location_id' ).val().trim();
		url += "&warehouse_id=" + $( '#warehouse_id' ).val().trim();
		var resp = ajaxR( url );
		//alert( resp );
		if( resp =='ok' ){
			return true;
		}else{
			return false;
		}
	/*	$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { location_fl : 'validate_only_one',
				product_id : $( '#product_id' ).val(),
				store_location_id : $( '#store_location_id' ).val().trim(),
				warehouse_id : $( '#warehouse_id' ).val().trim()
			},
			success : function ( dat ){
				if( dat == 'ok' ){
					saveLocation( true );
					//console.log( dat );
					//return true;
				}
			}
		});*/
	}

	function disabled_principal_location_before(){
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { location_fl : 'disabled_principal_location',
				product_id : $( '#product_id' ).val(),
				warehouse_id : $( '#warehouse_id' ).val().trim()
			},
			success : function ( dat ){
				console.log( dat );
				alert( "Ubicacion Principal anterior deshabilitada!" );
				if( dat == 'ok' ){
					saveLocation( true );
				}
			}
		});
	}

	function saveLocation( validated = null ){
		var location_data = getFormData();
		if( ! location_data ){
			return false;
		}else{
			if( $( '#is_principal' ).prop( 'checked' ) == true && validated == null ){
				if( ! validate_only_one() ){
					if( ! confirm( "Este producto ya tiene una ubicacion principal; Realmente deseas habilitar esta ubicacion como principal?" ) ){
						$( '#is_principal' ).removeAttr( 'checked' );
						//saveLocation( );
						//return false;
					}else{
						disabled_principal_location_before();
						return false;
					}
				}
			}
			if( ! location_is_not_repeat( location_data ) ){//verifica que la ubicacion no se repita
				return false;
			}
			//console.log( location_data );
	//envia datos por ajax
			$.ajax({
				type : 'post',
				url : 'ajax/db.php',
				cache : false,
				data : { location_fl : 'saveLocation',
					product_id : location_data['product_id'],
					location_number_from : location_data['location_number_from'],
					aisle_from : location_data['aisle_from'],
					level_from : location_data['level_from'],
					store_location_id : $( "#store_location_id" ).val(),
					is_enabled : ( $( '#is_enabled' ).prop( 'checked' ) ? 1 : 0 ),
					is_principal : ( $( '#is_principal' ).prop( 'checked' ) ? 1 : 0 ),
					warehouse_id : $( '#warehouse_id' ).val().trim()
				},
				success : function ( dat ){
					//console.log( dat );
					alert( "Ubicacion Guardada!" );
					if( dat == 'ok' ){
						//cleanProductLocationForm();
						getProductLocations( location_data['product_id'] );
						clean_location_form();
					}
				}
			});
		}
	}
	function getFormData(){
		var data = new Array();
		//var product_id, location_number_from, aisle_from, level_from;
		data['product_id'] = $( "#product_id" ).val();
		if( data['product_id'].length <= 0 ){
			alert( "Debes elegir un producto para continuar!" );
			return false;
		}
		
		data['location_number_from'] = $( '#location_number_from' ).val();
		if( data['location_number_from'].length <= 0 ){
			alert( "Debes ingresar un numero valido para continuar!" );
			$( '#location_number_from' ).focus();
			return false;
		}
		
		data['aisle_from'] = $( '#aisle_from' ).val();

		data['level_from'] = $( '#level_from' ).val();
		if( data['level_from'].length <= 0 ){
			alert( "Debes ingresar una altura valida para continuar!" );
			$( '#level_from' ).focus();
			return false;
		}
		return data;
	}

	function getStoreProductLocation( store_location_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { location_fl : 'getStoreProductLocation',
					store_location_id : store_location_id
			},
			success : function ( dat ){
				var tmp = dat.split( '|' );
				if( tmp[0] == 'ok' ){
					var location = JSON.parse( tmp[1] );
					setProductLocationForm( location );
				}
			}
		});
	}

	
	function setProductLocationForm( location ){
		$( '#store_location_id' ).val( location.store_location_id );
		$( '#location_number_from' ).val( location.number_from );
		$( '#location_number_until' ).val( location.number_from );
		$( '#aisle_from' ).val( ( location.aisle_from == 0 ? '' : location.aisle_from ) );
		$( '#aisle_until' ).val( ( location.aisle_from == 0 ? '' : location.aisle_from ) );
		$( '#level_from' ).val( location.level_from );
		$( '#level_until' ).val( location.level_from );

		$( '#is_enabled' ).prop( 'checked', ( location.is_enabled == 1 ? true : false ) );
		$( '#is_principal' ).prop( 'checked', ( location.is_principal == 1 ? true : false ) );
	}

	function buildProductLocations( locations ){
		var content = ``;
		for( var pos in locations ){
			content += `<tr>
				<td id="location_info_0_${pos}" class="no_visible">${locations[pos].store_location_id}</td>
				<td id="location_info_1_${pos}" class="text-end">${locations[pos].number_from}</td>
				<td id="location_info_2_${pos}" class="text-end no_visible">${ ( locations[pos].aisle_from == 0 ? '-' : locations[pos].aisle_from )}</td>
				<td id="location_info_3_${pos}" class="text-end">${locations[pos].level_from}</td>
				<td id="location_info_4_${pos}" class="text-center">
					<button
						class="btn"
						onclick="getStoreProductLocation( ${locations[pos].store_location_id} )";
					>
						<i class="icon-edit"></i>
					</button>
					<button
						class="btn"
						onclick="deleteProductLocation( ${locations[pos].store_location_id}, ${locations[pos].product_id} )";
					>
						<i class="icon-trash"></i>
					</button>
				</td>
			</tr>`;
		}
		$( '#locations_list' ).html( content );
	}

	function getProductLocations( product_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { location_fl : 'getProductLocations',
					warehouse_id : $( '#warehouse_id' ).val().trim(),
					product_id : product_id
			},
			success : function ( dat ){
				var tmp = dat.split( '|' );
				if( tmp[0] == 'ok' ){
					var locations = JSON.parse( tmp[1] );
					buildProductLocations( locations );
				}
			}
		});
	}

	function deleteProductLocation( store_location_id, product_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			cache : false,
			data : { location_fl : 'deteleProductLocation',
					store_location_id : store_location_id
			},
			success : function ( dat ){
				alert( "Ubicacion eliminada" );
				var tmp = dat.split( '|' );
				if( tmp[0] == 'ok' ){
					getProductLocations( product_id )
				}
			}
		});
	}
//valida que no se repita la ubicacion
	function location_is_not_repeat( location_data ){
	//busca en su misma tabla
		var exists = false;
		$( '#locations_list tr' ).each(function( index ){
			if( ( $( '#location_info_1_' + index ).html().trim() == location_data['location_number_from'] ) 
			&& ( $( '#location_info_3_' + index ).html().trim() == location_data['level_from'] )
			&& ( $( '#location_info_0_' + index ).html().trim() != $( '#store_location_id' ).val().trim() ) ){
				exists = true;
			}
		});
		if( exists ){
			alert( "Esta ubicacion ya esta registrada en este producto, verifica y vuelve a intentar!" );
			return false;
		}
		return true;
	}
	//lamadas asincronas
	function ajaxR(url){
	    if(window.ActiveXObject){       
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
         
