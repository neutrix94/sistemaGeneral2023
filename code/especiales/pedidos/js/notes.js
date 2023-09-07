var global_show_historic_notes = 0;
	function load_notes_by_type( obj, product_id ){
		//alert( 'uaishdfgkbvwjr' );
		var notes_category;
		if( obj == -1 ){
			notes_category = -1;
		}else{
			notes_category = $( obj ).val();
		}
		var url = "ajax/notas.php?fl=getNotesByProduct&category_id=" + notes_category;
		url += "&product_id=" + product_id;
		
		//alert( 'here ' + url );
		
		var response = ajaxR( url );
		//alert( response );
		$( '#product_notes_lists' ).empty();
		$( '#product_notes_lists' ).html( response );
	}

	function add_row_note( product_id ){
		var url  = "ajax/notas.php?fl=addProductNote&product_id=" + product_id;
	//add_note_category
		url += "&category=" + $( '#add_note_category' ).val();
	//id_type_category

		url += "&category_value=" + $( '#id_type_category' ).val();
	//add_note_txt
		url += "&text=" + $( '#add_note_txt' ).val();
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ) {
			alert( `Error : ${response[0]}` ); 
		}else{
			alert( `Nota agregada existosamente` );
			reloadProductNotes( product_id );
		}
		//alert( response );
	}
	function delete_product_note( obj, note_id , product_id ){
		var url  = "ajax/notas.php?fl=delete_product_note&note_id=" + note_id;
		var response = ajaxR( url );
		if( response.trim() == 'ok' ){
			$( obj ).parent().parent().remove();
		}
		alert( 'Nota eliminada existosamente.' );
		reloadProductNotes( product_id );

	}



var proyection_date = null, global_date_from = null, global_date_to = null;
var global_is_proyection_is_active = 0;
	function get_proyection_by_product( product_id ){
		if( proyection_date == null ){
			alert( "Es necesario establecer la fecha de inventario inicial y fechas para el calculo de recepcion y validacion de pedidos " );
			show_calendar_to_proyection( product_id );
			return false;
		}	
		if( global_is_proyection_is_active == 0 ){
			get_proyection_rows( product_id );
			//$( '#config_adic_container' ).css( 'top', '-5%' );
			$( '#proyection_container' ).css( 'display', 'block' );
			global_is_proyection_is_active = 1;
			$( '#proyection_btn_txt' ).html( 'Ocultar' );

		}else if( global_is_proyection_is_active == 1 ){
			//$( '#config_adic_container' ).css( 'top', '1%' );
			$( '#proyection_container' ).css( 'display', 'none' );
			global_is_proyection_is_active = 0;
			$( '#proyection_btn_txt' ).html( 'Detalle' );
		}
	}
	function reloadProductNotes( product_id ){
		load_notes_by_type( -1, product_id );
	}

	function show_calendar_to_proyection( product_id ){
		var resp = `<h5 class="form-control text-center">Selecciona las fechas de la proyección : </h5>
					<br>
					<div class="row">
						<div class="col-3"></div>
						<div class="col-6 text-center">
							<h5>Selecciona una fecha para el inventario inicial : </h5>
							<input type="date" id="proyection_date" class="form-control">
							<br>
							<h5>Selecciona el rango de fechas para calcular Recepciones y validaciones : </h5>
							Desde : <input type="date" id="proyection_date_from" class="form-control">
							<br>
							Hasta : <input type="date" id="proyection_date_to" class="form-control">
							<br>
							<button type="button" class="btn btn-success" onclick="set_proyection_date( ${product_id} );">Aceptar</button>
							<br>
							<button type="button" class="btn btn-danger" onclick="close_emergent_proyection();">Cancelar</button>
						</div>
					</div><br><br>`;
		$( '.emergent_content_proyection' ).html( resp );
		$( '.emergent_proyection' ).css( 'display', 'block' );
	}

	function set_proyection_date( product_id ){
		proyection_date = $( '#proyection_date' ).val();
		if( proyection_date == '' || proyection_date == null ){
			alert( 'La fecha de inventario inicial no puede ir vacía!' );
			$( '#proyection_date' ).focus();
			return false;
		}
		
		global_date_from = $( '#proyection_date_from' ).val();
		if( global_date_from == '' || global_date_from == null ){
			alert( 'La fecha de inventario inicial no puede ir vacía!' );
			$( '#proyection_date' ).focus();
			return false;
		}
		
		global_date_to = $( '#proyection_date_to' ).val();
		if( global_date_to == '' || global_date_to == null ){
			alert( 'La fecha de inventario inicial no puede ir vacía!' );
			$( '#proyection_date' ).focus();
			return false;
		}
		close_emergent_proyection();
		get_proyection_by_product( product_id );
	}

	function get_proyection_rows( product_id ){
		var url = "ajax/notas.php?fl=getProyectionDetail&product_id=" + product_id;
		url += "&inital_inventory_date=" + proyection_date + "&date_from=" + global_date_from;
		url += "&date_to=" + global_date_to;
		var response = ajaxR( url );
		$( '#proyection_container' ).html( response );
	}

	function close_emergent_proyection(){
		$( '.emergent_content_proyection' ).html( '' );
		$( '.emergent_proyection' ).css( 'display', 'none' );
	}

	function editProductNote( counter, note_id ){
		var tmp_txt = $( '#product_note_2_' + counter ).html().trim(); 
		//obtiene coordenadas
		var coords = $( '#product_note_2_' + counter ).position();
		$( '#productNoteTextareaTmp' ).css( 'display', 'block' );
		$( '#productNoteTextareaTmp' ).css( 'height', '200px' );
		$( '#productNoteTextareaTmp' ).css( 'top', ( coords.top + 35 ) );
		$( '#productNoteTextareaTmp' ).css( 'left', coords.left - 100  );
		$( '#productNoteTextareaTmp' ).val( tmp_txt );
		$( '#productNoteTextareaTmp' ).attr( 'onblur', `updateProductNote( ${counter}, ${note_id}, 1 )` );
		$( '#productNoteTextareaTmp' ).focus();
		//alert();
		//$( '#productNoteTextareaTmp' ).css(  );
	}

	function updateProductNote( counter, note_id, is_text = 0){
		var new_value = $( '#productNoteTextareaTmp' ).val();
		if( is_text == 0 ){
			new_value = $( '#product_note_2_' + counter ).html().trim();
		}
		var is_new = 0;
		if( $( '#product_note_-1_' + counter ).html().trim() == '' ){
			is_new = 1;
		}
	//guarda cambio en la base de datos

		var url = 'ajax/notas.php?fl=updateNote&txt=' + new_value;
		url += '&is_new='+ is_new +'&note_id=' + note_id;
		
		url += "&category_id=" + $( '#product_note_0_' + counter ).val();
		url += "&category_value_id=" + $( '#product_note_1_' + counter ).val();
		url += "&product_id=" + $( '#product_note_-2_' + counter ).html().trim();
	//	alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( `Error : ${response[0]}` );
		}else{
			if( is_text != null ){
				$( '#product_note_2_' + counter ).html( new_value );
				$( '#productNoteTextareaTmp' ).val( '' );
				$( '#productNoteTextareaTmp' ).css( 'display', 'none' );
				$( '#productNoteTextareaTmp' ).attr( 'onblur', '' );
			}
			reloadProductNotes( $( '#product_note_-2_' + counter ).html().trim() );
		}
	}

	function expand( obj, expand ){
		if( expand == 1 ){
			$( obj ).css( 'position' , 'absolute' );
			$( obj ).css( 'width', '90%' ); 
			$( obj ).css( 'left' , '10%' );
			$( obj ).css( 'height' , '200px' );
			$( obj ).attr( 'onblur', `expand( '${obj}', 0 )` ); 
			$( obj ).attr( 'onclick', '' ); 
		}else{
			$( obj ).css( 'position' , 'relative' );
			$( obj ).css( 'width', '100%' ); 
			$( obj ).css( 'left' , '0' );
			$( obj ).css( 'height' , '100%' );
			$( obj ).attr( 'onclick', `expand( '${obj}', 1 )` );
			$( obj ).attr( 'onblur', '' ); 

		}
	}

	function show_historic_notes( product_id ){
		var url = "ajax/notas.php?fl=getProducNotesBefore&product_id=" + product_id;
		global_show_historic_notes = window.open( url, 'Histórico', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left=0,top=0');
		global_show_historic_notes.innerHTML = '<button>here</button>';
		$( '#historic_btn' ).removeClass( 'btn-info' );
		$( '#historic_btn' ).addClass( 'btn-danger' );
		$( '#historic_btn' ).html( 'Cerrar Histórico' );
		$( '#historic_btn' ).attr( 'onclick', `hide_historic_notes( ${product_id} );` );
		//width=300,height=200,left = 390,top = 50
		//global_show_historic_notes;
	}
	function hide_historic_notes( product_id ){
		global_show_historic_notes.close();
		global_show_historic_notes = 0;
		$( '#historic_btn' ).removeClass( 'btn-danger' );
		$( '#historic_btn' ).addClass( 'btn-info' );
		$( '#historic_btn' ).html( 'Histórico' );
		$( '#historic_btn' ).attr( 'onclick', `show_historic_notes( ${product_id} );` );
	}

	function getNexAndBeforeProduct( type ){
		$( '#btn_get_' + type ).click();
	}


	function show_historic_info(){
		var message = `
		<div class="row">
			<div class="col-2"></div>
			<div class="col-8 text-center">
				<h4>Si deseas generar el histórico, ve a la pantalla 
					<b style="color : green;">7.3 Mantenimiento de la BD</b> y da click en el botón : "Pasar notas de productos al histórico"</h4>
				<img src="../../../img/help/pedidos/instrucciones_historico.png" width="80%">
				<center>
					<button
						type="button"
						class="btn btn-success"
						onclick="close_historic_emergent();"
					>

						<i class="icon-ok-circle">Aceptar</i>
					</button>
				</center>
			</div>
		</div>`;
		$( '.emergent_content_3' ).html( message );
		$( '.emergent_3' ).css( 'display', 'block' );
		$( '.emergent_3' ).css( 'z-index', '3000' );
	}

	function close_historic_emergent(){
		$( '.emergent_content_3' ).html( '' );
		$( '.emergent_3' ).css( 'display', 'none' );

	}

