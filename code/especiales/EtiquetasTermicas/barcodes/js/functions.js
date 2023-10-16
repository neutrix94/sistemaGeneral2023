	
	var global_pieces_per_box = 0, global_pieces_per_pack = 0;
	var ventana_abierta = null;
	var global_counter = 0;

	function redirect( type, is_direct = null ){
		if( is_direct == null ){
			if( !confirm( "Salir sin guardar?" ) ){
				return false;
			}
		}
		switch ( type ){
			case 'home' :
				location.href = '../../../../index.php?';
			break;
		}
	} 

	function seek_product( obj, e, no_length = 0 ){
		var txt = $( '#products_seeker' ).val();
		if( txt.length <= 2 && no_length == 1 ){
			$( '#seeker_response' ).html( '' );
			$( '#seeker_response' ).css( 'display' , 'none' );
			return false;
		}
		var url = "ajax/db.php?fl=seekProduct&key=" + txt;
		var response = ajaxR( url );
//alert( response );
		$( '#seeker_response' ).html( response );
		$( '#seeker_response' ).css( 'display' , 'block' );
	}

	function setProduct( obj, product_id, product_provider_array ){

		$( '#products_seeker' ).val( $( obj ).html().trim() );
		$( '#products_seeker' ).attr( 'disabled', true );
		$( '#reset_seeker_btn' ).css( 'display', 'block' );

		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display' , 'none' );

		$( '#product_provider_model' ).empty();
		$( '#product_provider_model' ).append( '<option value="0">--Seleccionar--</option>' );
		var options = "";
		var array_options = product_provider_array.split( '|' );
		for ( var i = 0; i < array_options.length; i++ ){
			var ax = array_options[i].split( '~' );
			options += '<option value="' + ax[0] + '">' + ax[1] + '</option>'; 
		}
		$( '#product_provider_model' ).append( options );
		$( '#product_provider_model' ).focus();
	}

	function setProductProvider(){
		var value = $( '#product_provider_model' ).val();
		if( value == 0 ){
			alert( "Elije un modelo válido!" );
			$( '#product_provider_model' ).focus();
			return false;
		}
		$( '#type_container' ).css( 'display', 'block' );
	//	$( '#product_provider_model' ).focus();
		/*if( global_since_get == null ){*/
		//	alert( '0' );
			get_presentation_quantities();
		//}
	}

	function get_presentation_quantities(){
		
		if( $( '#automatic_calculate' ).prop( 'checked') ){// && global_since_get == null
			reset_quantities_form();
		//	alert(1);
		}else{// if() global_since_get == null

		//	alert(2);
			$( '#boxes_quantity' ).val( 0 );
			if( $( '#boxes_check' ).prop( 'checked' ) )
				$( '#boxes_quantity' ).val( 0 );
			
			$( '#packs_quantity' ).val( 0 );
			if( $( '#packs_check' ).prop( 'checked' ) )
				$( '#packs_quantity' ).val( 0 );

			$( '#pieces_quantity' ).val( 0 );
			if( $( '#pieces_check' ).prop( 'checked' ) )
				$( '#pieces_quantity' ).val( 0 );
		}

		var base = $( '#product_provider_model option:selected' ).text().trim();
		var aux = base.split( ':' );
		global_pieces_per_box = parseInt( aux[1].replace( ' pzas, paquete ', '' ) );
		global_pieces_per_pack = parseInt( aux[2].replace( '  pzas)', '' ) );
		
		if( $( '#automatic_calculate' ).prop( 'checked') ){//&& global_since_get == null
			calculate_quantities_barcodes();
		}
		//alert( global_pieces_per_pack + ' - ' + global_pieces_per_box );
	}

	function resetSeekerButton(){
		$( '#product_provider_model' ).empty();
		$( '#product_provider_model' ).append( '<option value="0">--Seleccionar--</option>' );

		$( '#products_seeker' ).val( '' );
		$( '#products_seeker' ).removeAttr( 'disabled' );
		$( '#reset_seeker_btn' ).css( 'display', 'none' );
		$( '#products_seeker' ).focus();

		/*$( '#boxes_quantity' ).val( 0 );
		$( '#boxes_check' ).attr( 'checked', true );
		
		$( '#packs_quantity' ).val( 0 );
		$( '#packs_check' ).attr( 'checked', true );
		
		$( '#pieces_quantity' ).val( 0 );
		$( '#pieces_check' ).attr( 'checked', true );*/
		if( $( '#automatic_calculate' ).prop( 'checked' ) ){
			reset_quantities_form();
		}

	}

	function reset_quantities_form(){
		//$( '#automatic_calculate' ).attr( 'checked', true );
			$( '#boxes_quantity' ).val( 0 );
			/*$( '#boxes_quantity' ).removeAttr( 'disabled' );
			if( $( '#automatic_calculate' ).prop( 'checked' ) )
				$( '#boxes_check' ).prop( 'checked', true );*/

			$( '#packs_quantity' ).val( 0 );
			/*$( '#packs_quantity' ).removeAttr( 'disabled' );
			if( $( '#automatic_calculate' ).prop( 'checked' ) )
				$( '#packs_check' ).prop( 'checked', true );*/

			$( '#pieces_quantity' ).val( 0 );
			/*$( '#pieces_quantity' ).removeAttr( 'disabled' );
			if( $( '#automatic_calculate' ).prop( 'checked' ) )
				$( '#pieces_check' ).prop( 'checked', true );*/

	}

	function calculate_quantities_barcodes( obj = null ){
		if( obj != null ){
			var id = '#' + $( obj ).attr( 'id' );
			if( $( '#automatic_calculate' ).prop( 'checked' ) ){
				switch( id ){
					case '#boxes_quantity':
						//alert( 'here' );
						if( $( '#packs_check' ).prop( 'checked' ) ){
							$( '#packs_quantity' ).val( ($( obj ).val() * global_pieces_per_box ) / global_pieces_per_pack );
						}
						if( $( '#pieces_check' ).prop( 'checked' ) ){
							$( '#pieces_quantity' ).val( $( obj ).val() * global_pieces_per_box );
						}
					break;
					case '#packs_quantity':
						if( $( '#boxes_check' ).prop( 'checked' ) ){
							$( '#boxes_quantity' ).val( Math.floor( ( $( obj ).val() * global_pieces_per_pack ) / global_pieces_per_box ) );
						}
						if( $( '#pieces_check' ).prop( 'checked' ) ){
							$( '#pieces_quantity' ).val( $( obj ).val() * global_pieces_per_pack );
						}
					break;
					case '#pieces_quantity':
						if( $( '#boxes_check' ).prop( 'checked' ) ){
							$( '#boxes_quantity' ).val( Math.floor( $( obj ).val() / global_pieces_per_box ) );
						}
						if( $( '#packs_check' ).prop( 'checked' ) ){
							$( '#packs_quantity' ).val( Math.floor( $( obj ).val() / global_pieces_per_pack ) );
						}
					break;
				}
			}
			return false;
		}
		//if( $( '#automatic_calculate' ).prop( 'checked' ) ){

			if( global_pieces_per_box == 0 ){
				$( '#boxes_quantity' ).val( 0 );
				$( '#boxes_quantity' ).attr( 'disabled', true );
				$( '#boxes_check' ).removeAttr( 'checked' );
				/*if( $( '#boxes_check' ).prop( 'checked' ) )
					$( '#boxes_quantity' ).val( 0 );*/

			}else{
				if( $( '#boxes_check' ).prop( 'checked' ) )
					$( '#boxes_quantity' ).val( 1 );
				
				if( $( '#pieces_check' ).prop( 'checked' ) )
					$( '#pieces_quantity' ).val( global_pieces_per_box );

			}

			if( global_pieces_per_pack == 0 ){
				$( '#packs_quantity' ).val( 0 );
				$( '#packs_quantity' ).attr( 'disabled', true );
				$( '#packs_check' ).removeAttr( 'checked' );

			}else{
				if( $( '#packs_check' ).prop( 'checked' ) )
					$( '#packs_quantity' ).val( Math.floor( global_pieces_per_box / global_pieces_per_pack ) );
					//$( '#packs_quantity' ).removeAttr( 'disabled' );
			}

			
		//}
	}

	function change_field_status( obj ){
		var id = $( obj ).attr( 'id' );
		switch( id ){
			case 'boxes_check' : 
				if( $( obj ).prop( 'checked' ) == false ){
					$( '#boxes_quantity' ).val( 0 );
					$( '#boxes_quantity' ).attr( 'disabled', true );
				}else{
					$( '#boxes_quantity' ).removeAttr( 'disabled' );
				}
			break;
			case 'packs_check' : 
				if( $( obj ).prop( 'checked' ) == false ){
					$( '#packs_quantity' ).val( 0 );
					$( '#packs_quantity' ).attr( 'disabled', true );
				}else{
					$( '#packs_quantity' ).removeAttr( 'disabled' );
				}
			break;
			case 'pieces_check' : 
				if( $( obj ).prop( 'checked' ) == false ){
					$( '#pieces_quantity' ).val( 0 );
					$( '#pieces_quantity' ).attr( 'disabled', true );
				}else{
					$( '#pieces_quantity' ).removeAttr( 'disabled' );
				}
			break;
		} 
	}

	function build_ceil(){
		if( $( '#product_provider_model' ).val() == 0 ){
			alert( "Primero seleccione un proveedor producto válido" );
			$( '#product_provider_model' ).focus();
			return false;
		}
		if( $( '#boxes_quantity' ).val() == 0 && $( '#packs_quantity' ).val() == 0 
			&& $( '#pieces_quantity' ).val() == 0 ){
			alert( "Debe registrar al menos una etiqueta para continuar!" );
			$( '#pieces_quantity' ).focus();
			return false;
		}
		var resp = "<tr id=\"row_" + global_counter + "\" tabindex=\"" + global_counter + "\">";
			resp += "<td>" +  ( global_counter + 1 ) + "</td>";//id proveedor producto
			resp += "<td id=\"barcode_1_" + global_counter + "\" class=\"no_visible\">" + $( '#product_provider_model' ).val().trim() + "</td>";//id proveedor producto
			resp += "<td id=\"barcode_2_" + global_counter + "\">" + $( '#products_seeker' ).val().trim() + "</td>";//nombre
			resp += "<td id=\"barcode_3_" + global_counter + "\">" + $( '#product_provider_model option:selected' ).text().trim() + "</td>";//modelo
			resp += "<td id=\"barcode_4_" + global_counter + "\">" + $( '#boxes_quantity' ).val().trim() + "</td>";//etiquetas caja
			resp += "<td id=\"barcode_5_" + global_counter + "\">" + $( '#packs_quantity' ).val().trim() + "</td>";//etiquetas paquete
			resp += "<td id=\"barcode_6_" + global_counter + "\">" + ( $( '#pieces_check' ).prop( 'checked' ) ? $( '#pieces_quantity' ).val().trim() : 0 ) + "</td>";//etiquetas pieza
			resp += "<td id=\"barcode_7_" + global_counter + "\">";
				resp += "<button class=\"btn btn-danger\" onclick=\"remove_ceil( " + global_counter + " );\">";
					resp += "<i class=\"icon-cancel-alt-filled\"></i>";
					resp += "</button></td>";
		resp += "</tr>";
		$( '#barcodes_list' ).append( resp );
		$( "#row_" + global_counter ).focus();
		resetSeekerButton();
		global_counter ++;
	}

	function remove_ceil( counter ){
		if( !confirm( "¿Eliminar este registro? " ) ){
			return false;
		}
		$( '#row_' + counter ).remove();
	}

	function setTargetType(){
		var type = $( '#product_provider_model' ).val();
		if( type == 0 ){
			alert( "Elije un tipo de etiquetas válido!" );
			$( '#product_provider_model' ).focus();
			return false;
		}
		$( '#quantity_container' ).css( 'display', 'block' );
		$( '#boxes_quantity' ).focus();
		$( '#confirm_button_container' ).css( 'display', 'block' );
	}

	function generate_barcodes(){
		var barcodes_packs = '', barcodes_boxes = '', barcodes_pieces = '';
	//recorre la tabla
		$( '#barcodes_list tr' ).each( function ( index ){
			//$( this ).children( 'td' ).each( function (index2){
				if( document.getElementById( 'row_' + index ) ){//si existe la fila
					if( $( '#barcode_4_' + index).html().trim() > 0 ){
						barcodes_boxes += ( barcodes_boxes != '' ? '|' : '' );
						barcodes_boxes += $( '#barcode_1_' + index ).html().trim();
						barcodes_boxes += '~' + $( '#barcode_4_' + index ).html().trim();
					}
					if( $( '#barcode_5_' + index).html().trim() > 0 ){
						barcodes_packs += ( barcodes_packs != '' ? '|' : '' );
						barcodes_packs += $( '#barcode_1_' + index ).html().trim();
						barcodes_packs += '~' + $( '#barcode_5_' + index ).html().trim();
					}
					if( $( '#barcode_6_' + index).html().trim() > 0 ){
						barcodes_pieces += ( barcodes_pieces != '' ? '|' : '' );
						barcodes_pieces += $( '#barcode_1_' + index ).html().trim();
						barcodes_pieces += '~' + $( '#barcode_6_' + index ).html().trim();
					}
				}
			//});
		});
		//alert( barcodes_boxes + ' - ' + barcodes_packs + ' - ' + barcodes_pieces );
		if( barcodes_boxes == '' && barcodes_packs == '' && barcodes_pieces == '' ){
			alert( "Debes de seleccionar al menos un producto!" );
			return false;
		}
		var url = "ajax/db.php?fl=generateBarcodes";
		if( barcodes_boxes != '' ){
			url += "&boxes=" + barcodes_boxes;
		}
		if( barcodes_packs != '' ){
			url += "&packs=" + barcodes_packs;
		}
		if( barcodes_pieces != '' ){
			url += "&pieces=" + barcodes_pieces;
		}
		url += "&type=" + ($( '#barcode_type' ).val() == 1 ? 'normal' : 'unique' );
		var response = ajaxR( url );
		var aux = response.split( '|' );

	//descarga de archivos
		if( aux[0] != '' ){
			download_csv( 'ajax/db.php?fl=download_csv&name=' + aux[0] + '&output_name=codigos_caja.csv' );
		}
		if( aux[1] != '' ){
			download_csv( 'ajax/db.php?fl=download_csv&name=' + aux[1] + '&output_name=codigos_paquetes.csv' );
		}
		if( aux[2] != '' ){
			download_csv( 'ajax/db.php?fl=download_csv&name=' + aux[2] + '&output_name=codigos_piezas.csv' );
		}
		if( aux[3] != '' ){
			download_csv( 'ajax/db.php?fl=download_csv&name=' + aux[3] + '&output_name=codigos_piezas_2.csv' );
		}
		alert( 'Archivos descargador exitosamente!' );
		location.reload();
	}
	function download_csv(url){
		ventana_abierta=window.open(url, '_blank');
	}
//descarga del csv
	function cierra_pestana(){
		ventana_abierta.close();
	}

	var global_helpers = new Array();
	global_helpers['type_codes'] = "<div class=\"row\">";
		global_helpers['type_codes'] +=	"<div class=\"col-2\"></div>";
		global_helpers['type_codes'] +=	"<div class=\"col-8\">";
			global_helpers['type_codes'] +=	"<p>Este combo sirve para generar códigos de barras únicos o estandar</p>";
			global_helpers['type_codes'] +=	"<p>Para que aparezca por defecto alguna de las opciones se puede configurar desde <b>6.5 Configuración del Sistema ";
			global_helpers['type_codes'] +=	"-> Tipos de códigos de barras por default</b>";
			global_helpers['type_codes'] +=	"</p>";
			global_helpers['type_codes'] +=	"<button onclick=\"close_emergent();\" class=\"btn btn-success form-control\">";
				global_helpers['type_codes'] +=	"<i class=\"icon-ok-circle\">Aceptar</i>";
			global_helpers['type_codes'] +=	"</button>";
		global_helpers['type_codes'] +=	"</div>";
	global_helpers['type_codes'] +=	"</div>";

	function show_helper ( helper ){ 
		$( '.emergent_content' ).html( global_helpers[helper] );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.emergent_content' ).focus();
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}


	function show_config( field ){
		var url = "ajax/db.php?fl=getConfigForm&field=" + field;//calulation_field
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function save_system_conf( field ){
		var url = "ajax/db.php?fl=saveSystemConf&value=" + ( $( '#' + field + '_field' ).prop( 'checked' ) ? '1' : '0' );
		url += "&field=" + field;
		var response = ajaxR( url );
		var aux = response.split( '|' );
		if( field == 'default_calcular_etiquetas_cb' ){
			if( aux[1] == 1 ){
				$( '#automatic_calculate' ).prop( 'checked', true );
			}else{
				$( '#automatic_calculate' ).removeAttr( 'checked' );
			}
		}
		alert( aux[0] );
	}

	function validate_barcodes_series_update(){
		var url = "ajax/db.php?fl=validateBarcodesSeriesUpdate";
		var response = ajaxR( url );
	//alert( response );
		if( response != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
		}
	}

	function update_barcodes_prefix( obj ){
		$( obj ).attr( 'disabled', true );
		$( obj ).css( 'display', 'none' );
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		var url = "ajax/db.php?fl=updateBarcodesPrefix";
		//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setProductProviderSinceGet( product_provider, boxes_quantity, pieces_quantity ){
		$( '#products_seeker' ).val( product_provider );
		seek_product( product_provider, '', 1 );
		setTimeout(function (){
			$( '#seeker_response_0' ).click();
			$( '#automatic_calculate' ).prop( 'checked', true );
			$( '#pieces_check' ).prop( 'checked', true );
		}, 100);
		setTimeout(function (){
			get_presentation_quantities();
		}, 300);
		setTimeout(function (){
			$( '#product_provider_model' ).val( product_provider );
			$( '#boxes_quantity' ).val( boxes_quantity );
		}, 100);
		setTimeout(function (){
			$( '#boxes_quantity' ).val( boxes_quantity );
			calculate_quantities_barcodes( '#boxes_quantity' );
			$( '#pieces_check' ).removeAttr( 'checked' );
			$( '#pieces_quantity' ).val( parseInt( $( '#pieces_quantity' ).val() ) + parseInt( pieces_quantity ) );
		}, 300);
		/*setTimeout(function (){
			build_ceil();
		}, 100);*/
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