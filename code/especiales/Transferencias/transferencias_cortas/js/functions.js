var global_transfer_type = "";
var global_current_store = "";
var global_current_transfer = 0;
	function setTransferType(){
		global_current_store = $( '#current_store' ).val();
		global_transfer_type = $( '#transfer_type' ).val();
		//alert( global_transfer_type );
		if( global_transfer_type  ==  "" ){
			alert( "El tipo de transferencia no puede ir vacio" );
			$( '#transfer_type' ).focus();
			return false;
		}
		if( global_transfer_type == 10 ){
			$( '#transfer_store_origin' ).val( global_current_store );
			change_warehouses_by_store( 'origin' );
			$( '#transfer_store_origin' ).attr( "disabled", true );
			$( '#transfer_store_destinity' ).val( global_current_store );
			change_warehouses_by_store( 'destinity' );
			$( '#transfer_store_destinity' ).attr( "disabled", true );
		}else{
			$( '#transfer_store_destinity' ).removeAttr( "disabled" );

			//$( '#transfer_store_destinity' ).removeAttr( "disabled" );

		}
	}

	function change_warehouses_by_store( type ){
		var response = ajaxR( "ajax/Transfer.php?fl_transfer=changeComboContent&type=" + type + "&store_id=" + $( '#transfer_store_' + type ).val() );
		//alert( response );
		$( '#transfer_warehouse_' + type ).empty();
		$( '#transfer_warehouse_' + type ).html( response );
	}

	function insertTransferHeader(){
	//valida las susucursales
		if( global_transfer_type == 10 ){
			if( $( '#transfer_store_origin' ).val() != $( '#transfer_store_destinity' ).val() ){
				alert( "Las transferencias entre la mismas sucursales requieren la misma sucursal en origen y destino" );
				return false;
			}
			if( $( '#transfer_warehouse_origin' ).val() == $( '#transfer_warehouse_destinity' ).val() ){
				alert( "Las transferencias entre la mismas sucursales requieren diferente almacen en origen y destino" );
				return false;
			}
		}else{
			if( $( '#transfer_store_origin' ).val() == $( '#transfer_store_destinity' ).val() && global_transfer_type != 6 ){
				alert( "Las transferencias entre diferentes sucursales requieren diferente sucursal en origen y destino" );
				return false;
			}
			if( $( '#transfer_warehouse_origin' ).val() == $( '#transfer_warehouse_destinity' ).val() ){
				alert( "Las transferencias entre diferentes sucursales requieren diferente almacen en origen y destino" );
				$( '#transfer_store_destinity' ).focus();
				return false;
			}
		}
		if( $( '#transfer_type' ).val() == '' ){
			alert( "Es necesario que se elijas el tipo de Transferencia." );
			$( '#transfer_type' ).focus();
			return false;
		}
	//valida los almacenes
		var url = "ajax/Transfer.php?fl_transfer=insertTransfer";
		url += "&origin_store=" + $( '#transfer_store_origin' ).val();
		url += "&origin_warehouse=" + $( '#transfer_warehouse_origin' ).val();
		url += "&destinity_store=" + $( '#transfer_store_destinity' ).val();
		url += "&destinity_warehouse=" + $( '#transfer_warehouse_destinity' ).val();
		url += "&type_id=" + global_transfer_type;
		url += "&transfer_title=" + $( '#transfer_title' ).val();
//alert( url);
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			console.log( response[0] );
			alert( "Error : " + response[0] );

		}else{
			location.href = "index.php?pk=" + response[1];
			/*global_current_transfer = response[1];
			$( '#transfer_store_origin' ).attr( 'disabled', true );
			$( '#transfer_warehouse_origin' ).attr( 'disabled', true );
			$( '#transfer_store_destinity' ).attr( 'disabled', true );
			$( '#transfer_warehouse_destinity' ).attr( 'disabled', true );
			$( '#global_transfer_type' ).attr( 'disabled', true );
			$( '#create_transfer_btn' ).attr( 'disabled', true );
			$( '#barcode_seeker' ).removeAttr( 'disabled' );
			$( '#barcode_seeker' ).focus();*/
		}
	}

	function close_emergent(){
		$( '.emergent_content' ).html( "" );
		$( '.emergent' ).css( "display", "none" );
	}

	function setStore( type ){
	}
	
var global_was_find_by_name;
//validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 )
	function validateBarcode ( obj = null, e, is_by_name = 0, barcode = null, pieces = null, permission_box = null  ){
		var unique_code = '';
		if( is_by_name == 1 ){
			global_was_find_by_name = 1;
		}
		var key = e.keyCode;
		var txt = '', unique_code = '';
		if( key != 13 && e != 'enter' ){
			$( '#scanner_products_response' ).css( 'display', 'none' );
			return false;
		}
		txt = ( obj != null ? $( obj ).val().trim() : barcode );
		if( txt.trim().length <= 0 ){
			alert( "El código de barras no puede ir vacío." );
			$( obj ).focus();
			return false;
		}
		txt = txt.replace( '  ', ' ' );//reemplaza el dobles espacio
		if( ! validateBarcodeStructure( txt ) ){
			$( ".emergent_content" ).html( `El codigo de barras no tiene la estructura correcta, verifica y vuelve a escanear
					<br>
					<button class="btn btn-success" type="button"
						onclick="close_emergent();"
					>Aceptar</button>` );
			alert_scann( 'scan_again' );
			$( ".emergent" ).css( 'display', 'block' );
			return false;
		}

	//omite codigo de barras si es el caso
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 ){
			unique_code = txt;
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}
		
//alert( txt ); return false;
	//	global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
		var url = "ajax/Transfer.php?fl_transfer=validateBarcode";
		url += "&barcode=" + txt;
		url += "&transfer_id=" + global_current_transfer;
		url += "&pieces_quantity=" + ( pieces != null ? pieces : 1 );
		url += "&was_find_by_name=" + 0;
/*Oscar 2023/09/25 TRANSFERENCIAS RAPIDAS, QUE SOLO BUSQUE COINCIDENCIAS EN LOS PRODUCTOS HABILITADOS POR SUCURSAL*/
		url += "&destinity_store=" + $( '#transfer_store_destinity' ).val();
/*Fin de cambio Oscar 2023/09/25*/
		if( $( '#multiple_pieces' ).prop( 'checked' ) && pieces == null ){
			url += "&pieces_form=1";
		}
		if( unique_code != '' ){
			url += "&unique_code=" + unique_code;
		}
		if( permission_box != null ){
			url += "&permission_box=" + permission_box;
		}
//alert( url );

		var response =  ajaxR( url );
//alert( response );
		var ax = response.split( '|' );
		if( ax[0] != 'seeker' ){
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
			$( '#seeker_response' ).css( 'display' , 'none' );
		}else{
			$( '#seeker_response' ).html( ax[1] );
			$( '#seeker_response' ).css( 'display' , 'block' );//oculta resultado de búsqueda

		}
		switch( ax[0] ){
			case 'pieces_form':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#pieces_quantity_emergent' ).focus();
				alert_scann( "pieces_number_audio" );
				return false;
			break;

			case 'message_error' : 
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				alert_scann( 'error' );
			break;

			case 'is_box_code' : 
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#tmp_sell_barcode' ).focus();
			break;

			case 'ok':
				$( '#scanner_products_response' ).html( ax[1] );
				$( '#scanner_products_response' ).css( 'display', 'block' );
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display' , 'none' );//oculta resultado de búsqueda
				
				$( '#barcode_seeker' ).val( '' );
				$( '#barcode_seeker' ).focus();
				alert_scann( 'ok' );
				getTransferDetail( global_current_transfer );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker',  'unlock' );
				
			break; 

			default :
			break;
		}
	}

	function getTransferDetail( transfer_id ){
		var response = ajaxR( "ajax/Transfer.php?fl_transfer=getTransferDetail&transfer_id=" + transfer_id ).split( '|' );
		//alert( `${response[0]} \ ${response[1]} \ ${response[2]} \ ${response[3]} \ ${response[4]} \ ${response[5]}`   );
		$( '#transfer_products' ).empty();
		$( '#transfer_products' ).html( response[0] );

		$( '#transfer_store_origin' ).val( response[1].trim() );
		$( '#transfer_warehouse_origin' ).val( response[2].trim() );
		$( '#transfer_store_destinity' ).val( response[3].trim() );
		$( '#transfer_warehouse_destinity' ).val( response[4].trim() );
		$( '#transfer_type' ).val( response[5].trim() );
		global_current_transfer = response[6].trim();

		$( '#transfer_title' ).val( response[7].trim() );
		$( '#transfer_title' ).attr( 'disabled', true );

		$( '#transfer_status' ).val( response[8].trim() );
		
		$( '#create_transfer_btn' ).attr( 'disabled', true );

		$( '#barcode_seeker' ).removeAttr( 'disabled' );
		$( '#barcode_seeker' ).focus();

	//deshabilita campos
		$( '#transfer_type' ).attr( 'disabled', true );
		$( '#transfer_store_origin' ).attr( 'disabled', true );
		$( '#transfer_store_destinity' ).attr( 'disabled', true );
		$( '#transfer_warehouse_origin' ).attr( 'disabled', true );
		$( '#transfer_warehouse_destinity' ).attr( 'disabled', true );
		if( response[8].trim() != 1 ){
			$( '#barcode_seeker' ).attr( 'disabled', true );
			$( '#barcode_seeker_lock_btn' ).attr( 'disabled', true );
			$( '#multiple_pieces' ).attr( 'disabled', true );
		}
		//$( '#' ).attr( 'disabled', true );
	}

	function setProductByName( product_id ){
		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/Transfer.php?fl_transfer=getOptionsByProductId&product_id=" + product_id;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );

		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
	}
var audio_is_playing;
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

	function lock_and_unlock_focus( obj_btn, obj_field, block = false ){
		if ( ( $( obj_btn ).attr( 'class' ) == 'btn btn-success' && block == false ) || block == 'lock'){
			/*$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			element_focus_locked = '';*/
			$( obj_btn ).attr( 'class' , 'btn btn-danger' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			$( '#barcode_seeker' ).attr( 'disabled', true );
			//$( '#barcode_seeker' ).css( 'background-color', 'red' );
			$( '#barcode_seeker' ).addClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Presionar botón de candado para habilitar' );
			$( '#barcode_seeker' ).val( '' );
			element_focus_locked = obj_field;
			element_focus_locked = '';
		}else{
			//alert();
			$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock"></i>' );
			$( obj_field ).attr( 'onblur', "this.focus();return false;" );
			$( '#barcode_seeker' ).removeAttr( 'disabled' );
			$( '#barcode_seeker' ).removeClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Escanear / Buscar productos' );
			setTimeout( function(){ $( obj_field ).click();$( obj_field ).focus();}, 300);
		}
	}

	function setProductModel(){
		var model_selected = -1;
		$( '#model_by_name_list tr' ).each( function ( index ){
			if( $( '#p_m_5_' + index ).prop( 'checked' ) ){
				model_selected = $( '#p_m_5_' + index ).val();
			}
		});
		if( model_selected == -1 ){
			alert( "Debe de seleccionar un modelo para continuar!" );
			return false;
		}else{
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			$( '#barcode_seeker' ).val( model_selected.trim() );
			validateBarcode( '#barcode_seeker', 'enter', null, null, null, null, 1 );
		}
	}

	function save_transfer( type ){
		if( type == 0 ){
			if( confirm( "Guardar y salir?" ) ){
				location.href = "../../../../code/general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=Ng==";
			}
		}
	}

	function setPiecesQuantity( barcode, is_maquiled = null ){
		var global_pieces_quantity = $( '#pieces_quantity_emergent' ).val();

		if( is_maquiled != null ){
			global_pieces_quantity = $( '#maquila_decimal' ).val().trim();
		}
		/*if( barcode == '' || barcode == null ){
			alert( "El código de barras no puede ir vacío" );
		}*/		
		if( global_pieces_quantity <= 0 ){
			alert( "El número de piezas debe ser mayor a Cero!" );
			$( '#pieces_quantity_emergent' ).val( 1 );
			$( '#pieces_quantity_emergent' ).select();
			return false;
		}
		validateBarcode( null, 'enter', 0, barcode, global_pieces_quantity );
		//obj, e, is_by_name = 0, barcode = null, pieces = null
	}

	function delete_scanns( counter, transfer_product_id, is_maquiled, permission_delete = null, product_id = null ){
		if( $( '#transfer_status' ).val() != 1 ){
			alert( "Esta transferencia ya no se puede modificar porque esta en proceso de recepcion o ya fue terminada!" );
			return false;
		}
		var resp = '';
		if( permission_delete != null ){
			var quantity = $( '#pieces_quantity_emergent' ).val();
			if( is_maquiled == 1 ){
				quantity = $( '#maquila_decimal' ).val();
			}
			if( quantity > parseFloat( $( '#transfer_3_' + counter ).html().trim() ) ){
				var aux = $( '#transfer_3_' + counter ).html().trim();
				alert( "El numero de piezas a quitar no puede ser mayor al numero de piezas escaneadas." );
				$( '#pieces_quantity_emergent' ).select();
				return false;
			}
			if( quantity <= 0 ){
				alert( "El numero de piezas a quitar debe de ser mayor a cero." );
				$( '#pieces_quantity_emergent' ).select();
				return false;
			}
			var url = "ajax/Transfer.php?fl_transfer=removePiecesToDetail";
			url += "&transfer_product_id=" + transfer_product_id;
			url += "&quantity=" + quantity;
			var response = ajaxR( url );
			if( response.trim() == 'ok' ){
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );
				close_emergent();
				getTransferDetail( global_current_transfer );
			}else{
				alert( `Error : ${response}` );
			}
			return false;
		}
		if( is_maquiled == 0 ){	
			var product_name = $( '#transfer_5_' + counter ).html().trim();
			resp = `<div class="row">
						<div><h5>Ingresa el número de Piezas "${product_name}" a quitar de la Transferencia : </h5></div>
					<div class="col-2"></div>
						<div class="col-8">
							<input type="number" class="form-control" id="pieces_quantity_emergent">
							<button type="button" class="btn btn-success form-control"
							 onclick="delete_scanns( ${counter}, ${transfer_product_id}, ${is_maquiled}, 1 );">
								Aceptar
							</button>
							<button class="btn btn-danger form-control" onclick="close_emergent();lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );">
								<i class="icon-ok-circle">Cancelar</i>
							</button>
						</div>
					</div>
				</div>`;

			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		}else{
			var url = `../../plugins/maquile.php?fl_maquile=getMaquileForm&product_id=${product_id}`;
			url += `&quantity=0&function=delete_scanns( ${counter}, ${transfer_product_id}, ${is_maquiled}, 1, ${product_id} );`;
			url += `&subtitle=Ingresa el número de piezas a quitar de la Transferencia`;
			url += `&transfer_product_id=${transfer_product_id}&type=fast_transfer`;
			resp = ajaxR( url );
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		}
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
		$( '#pieces_quantity_emergent' ).focus();
	}

	function getPendingTransfers( store_id ){
		var url = "ajax/Transfer.php?fl_transfer=getPendingTransfers";
		var response = ajaxR( url ).split( '|' );
		if( response[1] != 'ok' ){
			$( '.emergent_content' ).html( response[1] );
			$( '.emergent' ).css( 'display', 'block' );
		}
		//alert( response );
	}

	function release_unique_code( unique_code ){
		if( $( '#tmp_validation_word' ).val().toLowerCase() != 'liberar' ){
			alert( "Es necesario escribir la palabra liberar para poder liberar el código único!" );
			$( '#tmp_validation_word' ).focus();
			return false;
		}
		var url = "ajax/Transfer.php?fl_transfer=release_unique_code";
		url += "&unique_code=" + unique_code;
		var response = ajaxR( url );
	//	alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		//lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker' );
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

