/*funciones resolucion*/
	function change_missing_resolution( _case, _type, transfers, product_id, product_provider_id ){
		var inventory = 0, pending_to_recive = 0, quantity_to_scan = 0, difference = 0;
		var count = $( '#resolution_field_count' ).val();
		if( count <= 0 ){
			$( '#resolution_receive_complete_button' ).attr( 'disabled', true );
			count = 0;
		}else{
			$( '#resolution_receive_complete_button' ).removeAttr( 'disabled' );
		}
	//calcula diferencia
		inventory = $( '#resolution_field_inventory' ).val();
		pending_to_recive = $( '#resolution_field_missing' ).val();
		difference =  ( parseInt( inventory ) + parseInt( pending_to_recive ) ) - count;
		$( '#resolution_field_difference' ).val( difference );
		quantity_to_scan = pending_to_recive - difference;
		$( '#resolution_field_to_scan' ).val( quantity_to_scan );
	//botones
		$( '#resolution_to_scan_button' ).html( quantity_to_scan );

		$( '#resolution_receive_partial_button' ).attr( 'onclick', `save_missing_resolution( ${_case}, '${_type}', ${pending_to_recive}, '${transfers}', ${product_id}, ${product_provider_id} );` );
		/*if( quantity_to_scan == 0 ){
			$( '#resolution_receive_partial_button' ).css( 'display', 'none' );
			$( '#resolution_receive_partial_button' ).attr( 'onclick', '' );
		}else{*/
			$( '#resolution_receive_partial_button' ).css( 'display', 'block' );
			$( '#resolution_receive_partial_button' ).attr( 'onclick', `save_missing_resolution( ${_case}, '${_type}', ${quantity_to_scan}, '${transfers}', ${product_id}, ${product_provider_id} );` );
		//}
	}

var global_excedent_difference =  0;
var global_excedent_product__case;
var global_excedent_product__type;
var global_excedent_transfers;
var global_excedent_product_id;
var global_excedent_product_provider_id;

	function change_excedent_resolution( _case, _type, transfers, product_id, product_provider_id ){
		global_excedent_product__case = _case;
		global_excedent_product__type = _type;
		global_excedent_transfers = transfers;
		global_excedent_product_id = product_id;
		global_excedent_product_provider_id = product_provider_id;
		var inventory = 0,
			excedent_count = 0,
			user_count = 0,
			excedent = 0, 
			quantity_to_scan = 0, 
			difference = 0;
		user_count = $( '#resolution_field_count' ).val();
		if( user_count <= 0 ){
			$( '#resolution_receive_complete_button' ).attr( 'disabled', true );
			user_count = 0;
		}else{
			$( '#resolution_receive_complete_button' ).removeAttr( 'disabled' );
		}
	//calcula diferencia 
		inventory =( $( '#resolution_field_inventory_excedent' ).val() == '' ? 0 : $( '#resolution_field_inventory_excedent' ).val() );
		excedent_count =( $( '#resolution_field_excedent_count' ).val() == '' ? 0 : $( '#resolution_field_excedent_count' ).val() );
		global_excedent_difference = ( parseInt(user_count) + parseInt( excedent_count ) ) - parseInt( inventory );
		//alert(global_excedent_difference);
		quantity_to_scan = global_excedent_difference;
		$( '#resolution_field_to_scan' ).val( quantity_to_scan );
	//botones
		$( '#resolution_to_scan_button' ).html( quantity_to_scan );
		$( '#resolution_to_scan_button_return' ).html( quantity_to_scan );

		$( '#resolution_receive_partial_button' ).attr( 'onclick', `save_excedent_resolution( ${_case}, '${_type}', ${global_excedent_difference}, '${transfers}', ${product_id}, ${product_provider_id} );` );
		/*if( quantity_to_scan == 0 ){
			$( '#resolution_receive_partial_button' ).css( 'display', 'none' );
			$( '#resolution_receive_partial_button' ).attr( 'onclick', '' );
			$( '#resolution_receive_partial_button_excedent' ).css( 'display', 'none' );
			$( '#resolution_receive_partial_button_excedent' ).attr( 'onclick', '' );

		}else{*/
			$( '#resolution_receive_partial_button' ).css( 'display', 'block' );
			$( '#resolution_receive_partial_button' ).attr( 'onclick', `save_excedent_resolution( 1, '${_type}', ${quantity_to_scan}, '${transfers}', ${product_id}, ${product_provider_id} );` );
			$( '#resolution_receive_partial_button_excedent' ).css( 'display', 'block' );
			$( '#resolution_receive_partial_button_excedent' ).attr( 'onclick', `save_excedent_resolution( -1, '${_type}', ${quantity_to_scan}, '${transfers}', ${product_id}, ${product_provider_id} );` );
		//}
	}

	function compare_excedent( obj ){
		var id = $( obj ).attr( 'id' );
		if( id == 'resolution_field_count' && $( '#resolution_field_excedent_count' ).val() <= 0  ){
			//alert( "El conteo de excedente no puede ir vacío!" );
			$( '#resolution_field_excedent_count' ).select();
			return false;
			return false;
		}
		if( id == 'resolution_field_excedent_count' && $( '#resolution_field_count' ).val() <= 0  ){
//alert( "El conteo Físico no puede ir vacío!" );
			$( '#resolution_field_count' ).select();
			return false;
		}
		if( $( '#resolution_field_excedent' ).val() != global_excedent_difference ){
			var resp = `<br><br><div class="row">
							<div class="col-1"></div>
							<div class="col-10 text-center">
								<h5>El conteo excedente es diferente del excedente del sistema</h5>
								<p>Cuenta de nuevo el excedente y escribelo en el siguiente campo : </p>
								<div class="row">
									<div class="col-2"></div>
									<div class="col-8 text-center">
										<input type="number" id="excendent_count_tmp">
									</div>
									<div class="col-2"></div>
									<div class="col-6">
										<button 
											class="btn btn-success"
											onclick="assort_excedent();"
										>
											<i>Continuar</i>
										</button>
									</div>
									<div class="col-6">
										<button 
											class="btn btn-danger"
											onclick="close_emergent_2();"
										>
											<i>Cancelar</i>
										</button>
									</div>
								</div>
							</div>
						</div><br><br>`;
			$( '.emergent_content_2' ).html( resp );
			$( '.emergent_2' ).css( 'display', 'block' );
		}
	}

	function assort_excedent(){
		if( $( '#excendent_count_tmp' ).val() <= 0 ){
			alert( "El conteo Excedente no puede ir vacío!" );
			$( '#excendent_count_tmp' ).focus();
			return false;
		}
		if( $( '#excendent_count_tmp' ).val() != $( '#resolution_field_excedent_count' ).val() ){
		//alert( $( '#excendent_count_tmp' ).val() + '!=' + $( '#resolution_field_excedent_count' ).val() );
			var resp = `<div class="row">
							<div class="col-1"></div>
							<div class="col-10 text-center">
								<h5>El dato capturado en el conteo excedente no corresponde al dato de 
								excedente capturado en esta pantalla, verifique su información y vuelva a capturar el excedente </h5>
								<br>

								<button
									class="btn btn-success"
									onclick="close_emergent_2();
									document.getElementById( 'resolution_field_excedent_count' ).select();"
								>
									<i class="icon-ok-circle">Aceptar</i>
								</button>
							</div>
						</div>`;
			$( '.emergent_content_2' ).html( resp );
			$( '.emergent_2' ).css( 'display', 'block' );
		}else{
			var resp = `<div class="row">
							<div class="col-1"></div>
							<div class="col-10 text-center">
								<h5>Se ajustaron los movimientos de acuerdo a los conteos, posibles causas : </h5>
								<p>1- Inventario incorrecto</p>
								<p>2- Se escaneó incorrecto</p>
								<p>3- Conteo incorrecto</p>
								<br>
								<button
									class="btn btn-success"
									onclick="equal_excedent();"
								>
									<i class="icon-ok-circle">Aceptar</i>
								</button>
							</div>
						</div>`;
			$( '#resolution_field_excedent' ).val( global_excedent_difference );
			$( '.emergent_content_2' ).html( resp );
			$( '.emergent_2' ).css( 'display', 'block' );
		}
	}

	function equal_excedent(){
		change_excedent_resolution( global_excedent_product__case,
			global_excedent_product__type,
			global_excedent_transfers,
			global_excedent_product_id,
			global_excedent_product_provider_id );
		close_emergent_2();
		/*global_excedent_product__case = '';
		global_excedent_product__type = '';
		global_excedent_transfers = '';
		global_excedent_product_id = '';
		global_excedent_product_provider_id = '';*/
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}

	function save_excedent_resolution( _case, _type, difference, block_resolution_id, product_id, product_provider_id ){
		var url = 'ajax/Resolution.php?fl_r=saveResolutionExcedentRow';
		url += '&product_id=' + product_id;
		url += '&product_provider_id=' + product_provider_id;
		url += '&quantity=' + difference;
		url += '&type=' + _case;
		url += '&transfers=' + global_current_transfers;
		url += '&reception_block_id=' + global_current_reception_blocks;
		url += '&block_resolution_id=' + block_resolution_id;
		url += '&user_count=' + $( '#resolution_field_count' ).val();
		url += '&pieces_faltant=' + $( '#resolution_field_difference' ).val();
		url += '&difference=' + $( '#resolution_field_missing' ).val();

		var response = ajaxR( url );
//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );

		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
		receptionResumen( 4 );
		loadLastReceptions();
	}

	function save_doesnt_correspond_resolution( _type, id, quantity ){
		var url = 'ajax/Resolution.php?fl_r=saveResolutionDoesntCorrespondRow';
		url += '&quantity=' + quantity;
		url += '&type=' + _type;
		url += '&block_resolution_id=' + id;
		var response = ajaxR( url );
//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );

		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
		receptionResumen( 4 );
		loadLastReceptions();
	}
	
	
//inserta registros de resolución de transferencias
	function save_missing_resolution( _case, _type, difference, transfer_product_id, product_id, product_provider_id ){
		var data = new Array();
		var url = 'ajax/Resolution.php?fl_r=saveResolutionMissingRow';
		url += '&product_id=' + product_id;
		url += '&product_provider_id=' + product_provider_id;
		url += '&quantity=' + difference;
		url += '&type=' + _type;
		url += '&transfers=' + global_current_transfers;
		url += '&reception_block_id=' + global_current_reception_blocks;
		url += '&user_count=' + $( '#resolution_field_count' ).val();
		url += '&pieces_faltant=' + $( '#resolution_field_difference' ).val();
		url += '&difference=' + $( '#resolution_field_missing' ).val();
//alert( url );//return false;
		var response = ajaxR( url );
//alert( response );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );

		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
		receptionResumen( 4 );
		loadLastReceptions();
	//
	}