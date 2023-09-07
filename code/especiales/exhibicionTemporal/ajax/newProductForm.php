<?php
?>
<div class="row">
	<div class="col-3"></div>
	<div class="col-6">
		<div class="input-group">
			<input type="text" 
				onkeyup="seek_new_product( event );"
				id="new_product_seeker" 
				class="form-control" 
				placeholder="Escanear / Buscar producto">
			<button
				class="btn btn-warning"
				onclick="seek_new_product( 'intro' );"
			>
				<i class="icon-barcode"></i>
			</button>
		</div>
		<div id="new_product_seeker_response"></div>
	</div>
	<div class="col-4"></div>
	<div class="new_product_container">
		<br>
		<table class="table table-striped table-bordered">
			<thead class="new_product_header_sticky">
				<tr>
					<th class="col-2 text-center">Sucursal</th>
					<th class="col-1 text-center">Muro</th>
					<th class="col-2 text-center">Nota</th>
					<th class="col-1 text-center">Colgar</th>
					<th class="col-2 text-center">Nota</th>
					<th class="col-1 text-center">Adicional</th>
					<th class="col-2 text-center">Nota</th>
					<th class="col-1 text-center">
						<input type="checkbox" id="new_product_check_all" onclick="select_all();">
					</th>
				</tr>
				<tr>
					<th>Generales</th>
					<th><input type="number" id="n_p_general_1" class="form-control"></th>
					<th><textarea id="n_p_general_2" class="form-control"></textarea></th>
					<th><input id="n_p_general_3" type="number" class="form-control"></th>
					<th><textarea id="n_p_general_4" class="form-control"></textarea></th>
					<th><input id="n_p_general_5" type="number" class="form-control"></th>
					<th><textarea id="n_p_general_6" class="form-control"></textarea></th>
					<th></th>
				</tr>
			</thead>
			<tbody id="new_product_content"></tbody>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-3 text-center"></div>
	<div class="col-3 text-center">
		<button
			class="btn btn-success"
			onclick="save_new_product();"
		>
			<i class="icon-ok-circle">Guardar</i>
		</button>
	</div>
	<div class="col-3 text-center">
		<button
			class="btn btn-danger"
			onclick="close_emergent();"
		>
			<i class="icon-cancel-circled">Cancelar</i>
		</button>
	</div>
</div>
<script type="text/javascript">
	function seek_new_product( e ){
		var keyCode = e.keyCode;
		if( keyCode != 13 && e != 'intro' ){
			return false;
		}
		var txt = $( '#new_product_seeker' ).val().trim();
		$.ajax({
			type : 'post',
			url : 'ajax/exhibitionProducts.php',
			cache : false,
			data : { exhibition_flag : 'new_products_seeker', key : txt },
			success : function ( dat ){
				//alert( dat );
				var aux  = dat.split( '|' );
				switch ( aux[0] ) {
					case 'error' :
						alert( "Error : \n" + aux );
						return false;
					break;
					case 'seeker' :
						$( '#new_product_seeker_response' ).html( aux[1] );
						$( '#new_product_seeker_response' ).css( 'display', 'block' );
					break;
					case 'was_found' :
						var product_provider = JSON.parse( aux[1] );
						get_stores_products( product_provider.product_provider_id );
					break;
					case 'message_info' :
						$( '.emergent_content' ).html( aux[1] );
						$( '.emergent' ).css( 'display', 'block' );
					break;
					default : 
						alert( dat );
						return false;
					break;
				}
			}
		});
	}

	function get_stores_products( product_provider_id ){
		$.ajax({
			type : 'post',
			url : 'ajax/exhibitionProducts.php',
			cache : false,
			data : { exhibition_flag : 'getStoresProducts', 
			product_provider_id : product_provider_id },
			success : function ( dat ){
				var aux = dat.split( '|' );
				if( aux[0] != 'ok' ){
					alert( "Error : \n" + dat );
				}else{
					var store_product_json = JSON.parse( aux[1] );
					build_store_product( store_product_json );
				}
			}
		});
	}

	function build_store_product( store_product_json ){
		var resp = ``;
		var count = 0;
		for ( var pos in store_product_json ){
			var disabled = ( store_product_json[pos].store_product_status == 0 ? 'disabled' : '' );
			var temporal_exhibition_id = '', product_provider_exhibition_id = '',
				wall_pieces = '', wall_notes = '', hang_pieces = '', 
				hang_notes = '', aditional_pieces = '', aditional_notes = '';
			
			if( store_product_json[pos].detail != null ){
				console.log( store_product_json[pos].detail );
				temporal_exhibition_id = `temporal_exhibition_id="${store_product_json[pos].detail.temporal_exhibition_id}"`;
				product_provider_exhibition_id = `product_provider_temporal_exhibition_id="${store_product_json[pos].detail.product_provider_exhibition_id}"`;
				wall_pieces = store_product_json[pos].detail.wall_pieces;
				wall_notes = store_product_json[pos].detail.wall_notes;
				hang_pieces = store_product_json[pos].detail.hang_pieces;
				hang_notes = store_product_json[pos].detail.hang_notes;
				aditional_pieces = store_product_json[pos].detail.aditional_pieces;
				aditional_notes = store_product_json[pos].detail.aditional_notes;
			}
			resp += `<tr>
				<td class="col-2" id="n_p_0_${pos}" 
					store_id="${store_product_json[pos].store_id}"
					product_id="${store_product_json[pos].product_id}"
					product_provider_id="${store_product_json[pos].product_provider_id}"
					${temporal_exhibition_id}
					${product_provider_exhibition_id}
				>
					${store_product_json[pos].store_name}
				</td>
				<td class="col-1">
					<input type="number" class="form-control" id="n_p_1_${pos}" value="${wall_pieces}" ${disabled}>
				</td>
				<td class="col-2">
					<textarea class="form-control" id="n_p_2_${pos}" ${disabled}>${wall_notes}</textarea>
				</td>
				<td class="col-1">
					<input type="number" class="form-control" id="n_p_3_${pos}" value="${hang_pieces}" ${disabled}>
				</td>
				<td class="col-2">
					<textarea class="form-control" id="n_p_4_${pos}" ${disabled}>${hang_notes}</textarea>
				</td>
				<td class="col-1">
					<input type="number" class="form-control" id="n_p_5_${pos}" value="${aditional_pieces}" ${disabled}>
				</td>
				<td class="col-2">
					<textarea class="form-control" id="n_p_6_${pos}" ${disabled}>${aditional_notes}</textarea>
				</td>
				<td class="col-1 text-center">
					<input type="checkbox" id="n_p_7_${pos}" onchange="change_by_check( ${pos} );">
				</td>
			</tr>`; 
		}
		$( '#new_product_content' ).empty();
		$( '#new_product_content' ).html( resp );
	}

	function select_all(){
		var checked = false;
		if( $( '#new_product_check_all' ).prop( 'checked' ) == true ){
			checked = true;
		}
		$( '#new_product_content tr' ).each( function( index ){
			( checked ? $( '#n_p_7_' + index ).prop( 'checked', 'true' ) : $( '#n_p_7_' + index ).removeAttr( 'checked' ) );			
			( checked ? $( '#n_p_1_' + index ).val( $( '#n_p_general_1' ).val() ) : $( '#n_p_1_' + index ).val( '' ) );			
			( checked ? $( '#n_p_2_' + index ).val( $( '#n_p_general_2' ).val() ) : $( '#n_p_2_' + index ).val( '' ) );			
			( checked ? $( '#n_p_3_' + index ).val( $( '#n_p_general_3' ).val() ) : $( '#n_p_3_' + index ).val( '' ) );			
			( checked ? $( '#n_p_4_' + index ).val( $( '#n_p_general_4' ).val() ) : $( '#n_p_4_' + index ).val( '' ) );			
			( checked ? $( '#n_p_5_' + index ).val( $( '#n_p_general_5' ).val() ) : $( '#n_p_5_' + index ).val( '' ) );			
			( checked ? $( '#n_p_6_' + index ).val( $( '#n_p_general_6' ).val() ) : $( '#n_p_6_' + index ).val( '' ) );			
		});
	}
	function change_by_check( pos ){
		var checked = false;
		if( $( '#n_p_7_' + pos ).prop( 'checked' ) == true ){
			checked = true;
		}
		( checked ? $( '#n_p_1_' + pos ).val( $( '#n_p_general_1' ).val() ) : $( '#n_p_1_' + pos ).val( '' ) );			
		( checked ? $( '#n_p_2_' + pos ).val( $( '#n_p_general_2' ).val() ) : $( '#n_p_2_' + pos ).val( '' ) );			
		( checked ? $( '#n_p_3_' + pos ).val( $( '#n_p_general_3' ).val() ) : $( '#n_p_3_' + pos ).val( '' ) );			
		( checked ? $( '#n_p_4_' + pos ).val( $( '#n_p_general_4' ).val() ) : $( '#n_p_4_' + pos ).val( '' ) );			
		( checked ? $( '#n_p_5_' + pos ).val( $( '#n_p_general_5' ).val() ) : $( '#n_p_5_' + pos ).val( '' ) );			
		( checked ? $( '#n_p_6_' + pos ).val( $( '#n_p_general_6' ).val() ) : $( '#n_p_6_' + pos ).val( '' ) );			
		
	}
	function save_new_product(){
		var data = '';
		var product_provider_id;
		$( '#new_product_content tr' ).each( function( index ){
			if( $( '#n_p_1_' + index ).val() > 0 || $( '#n_p_3_' + index ).val() > 0 
				|| $( '#n_p_5_' + index ).val() > 0 ){
				data += ( data == '' ? '' : '|~|' );
				product_provider_id = $( '#n_p_0_' + index ).attr( 'product_provider_id' ).trim();
				data += $( '#n_p_0_' + index ).attr( 'store_id' ).trim() + '|';
				data += $( '#n_p_0_' + index ).attr( 'product_id' ).trim() + '|';
				data += $( '#n_p_0_' + index ).attr( 'product_provider_id' ).trim() + '|';

				data += $( '#n_p_1_' + index ).val().trim() + '|';
				data += $( '#n_p_2_' + index ).val().trim() + '|';
				data += $( '#n_p_3_' + index ).val().trim() + '|';
				data += $( '#n_p_4_' + index ).val().trim() + '|';
				data += $( '#n_p_5_' + index ).val().trim() + '|';
				data += $( '#n_p_6_' + index ).val().trim();
				if( $( '#n_p_0_' + index ).attr( 'temporal_exhibition_id' ) != null ){
					data += '|' + $( '#n_p_0_' + index ).attr( 'temporal_exhibition_id' ); 
					data += '|' + $( '#n_p_0_' + index ).attr( 'product_provider_temporal_exhibition_id' ); 
				}
			}
		});
//alert( data );return false;
		if( data == '' ){
			alert( "No hay valores por guardar, captura valores y vueleve a intentar!" );
			return false;
		}
		//alert( data );
		$.ajax({
			type : 'post',
			url : 'ajax/exhibitionProducts.php',
			cache : false,
			data : { exhibition_flag : 'saveNewProduct', 
				values : data },
			success : function ( dat ){
				var aux = dat.split( '|' );
				if( aux[0] != 'ok' ){
					alert( "Error : " + dat );
				}else{
					alert( aux[1] );
					get_stores_products( product_provider_id );
				}
			}
		});
	}
</script>

<style type="text/css">
	#new_product_seeker_response{
		position: relative;
		width : 100%;
		max-height : 300px;
		overflow : auto;
		box-shadow : 1px 1px 10px rgba( 0,0,0,.5 );
	}
	.new_product_container{
		position : relative;
	}
	.new_product_header_sticky{
		position : sticky;
		top : -20px;
		background-color: silver;
	}
</style>