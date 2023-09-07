<?php
	
?>

<div class="row group_card">
	<div style="max-height: 300px; overflow : auto;">
		<table class="table table-bordered table-striped" >
			<thead style="position : sticky; top : -10px; background-color : white;">
				<tr>
					<th colspan="4" class="text-center">Productos pendientes / excedente</th>
				</tr>
				<tr>
					<th>Producto</th>
					<th>Inventario</th>
					<th>Conteo Físico</th>
					<th>Sobrante Separado</th>
				</tr>
			</thead>
			<tbody id="missing_and_excedent_counter_capture"></tbody>
		</table>

	</div>
	<div style="max-height: 300px; overflow : auto;">
		<table class="table table-bordered table-striped" >
			<thead style="position : sticky; top : -10px; background-color : white;">
				<tr>
					<th colspan="4" class="text-center">Productos no corresponden</th>
				</tr>
				<tr>
					<th>Producto</th>
					<th>Inventario</th>
					<th>Conteo Físico</th>
					<th>Sobrante Separado</th>
				</tr>
			</thead>
			<tbody id="does_not_correspond_counter_capture"></tbody>
		</table>
	</div>
</div>

	<button 
		type="button"
		class="btn btn-success form-control"
		onclick="getResolutionsToSave();"
	>
		<i class="icon-ok-circle">Aceptar y Continuar</i>
	</button>

	<br><br>


		<button 
			type="button"
			class="btn btn-info form-control"
			onclick="resolveResolution();"
		>
			<i>Continuar con la resolucion</i>
		</button>



<script type="text/javascript">
	function getResolutionsToSave(){
		var excedente_and_missing = '';
		var doesnt_correspond = '';
		var stop = false;
		$( '#missing_and_excedent_counter_capture tr' ).each( function ( index ){
			excedente_and_missing += ( excedente_and_missing == '' ? '' : '|~|' );
			excedente_and_missing += $( '#1_1_' + index ).html().trim() + '~';
			
			if( $( '#1_4_' + index ).val() == '' || $( '#1_4_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar, si no hay escribe 0" );
				$( '#1_4_' + index ).focus();
				stop = true;
				return false;
			}

			excedente_and_missing += $( '#1_4_' + index ).val() + '~';

			if( $( '#1_5_' + index ).val() == '' || $( '#1_5_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar, si no hay escribe 0" );
				$( '#1_5_' + index ).focus();
				stop = true;
				return false;
			}
			excedente_and_missing += $( '#1_5_' + index ).val() + '~';
			excedente_and_missing += $( '#1_3_' + index ).html().trim() + '~';
			excedente_and_missing += $( '#1_6_' + index ).html();
		});
		
		if( stop == true ){
			return false;
		}

		$( '#does_not_correspond_counter_capture tr' ).each( function ( index ){
//alert( index );
			doesnt_correspond += ( doesnt_correspond == '' ? '' : '|~|' );
			doesnt_correspond += $( '#2_1_' + index ).html().trim() + '~';
			
			if( $( '#2_4_' + index ).val() == '' || $( '#2_4_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar, si no hay escribe 0" );
				$( '#2_4_' + index ).focus();
				stop = true;
				return false;
			}

			doesnt_correspond += $( '#2_4_' + index ).val() + '~';
			
			if( $( '#2_4_' + index ).val() == '' || $( '#2_4_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar, si no hay escribe 0" );
				$( '#2_4_' + index ).focus();
				stop = true;
				return false;
			}
			doesnt_correspond += $( '#2_5_' + index ).val() + '~';
			doesnt_correspond += $( '#2_3_' + index ).html().trim() + '~';
			doesnt_correspond += $( '#2_6_' + index ).html();
		});
	
		if( stop == true ){
			return false;
		}
		var url = `ajax/productResolution.php?resolution_fl=saveResolutionPrevious&case_1=${excedente_and_missing}&case_2=${doesnt_correspond}&reception_block_id=${global_current_reception_blocks}`;
//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function resolveResolution(  ){
		var url = "ajax/productResolution.php?resolution_fl=getResolutionForm&reception_block_id=" + global_current_reception_blocks;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function saveResolutionPerProductProvider( product_id, product_provider_id, quantity, type, movement_type, transfer_block_resolution_id = '', product_resolution_id = '' ){
		var url = "ajax/productResolution.php?resolution_fl=save_resolution_row&reception_block_id=" + global_current_reception_blocks;
		url += "&quantity=" + quantity + "&type=" + type;
		url += "&product_id=" + product_id + "&product_provider_id=" + product_provider_id;
		url += "&movement_type=" + movement_type;
		url += "&transfer_block_resolution_id=" + transfer_block_resolution_id;
		url += "&product_resolution_id=" + product_resolution_id;
//alert( url );
		var  response = ajaxR( url ).split( '|' );
		console.log( response );
		if( response[0] == 'ok' ){
			$( '.emergent_content_2' ).html( response[1] );
			$( '.emergent_2' ).css( 'display', 'block' );
			getResolutionForms();
		}else{
			alert( response );
		}
	}

	function getResolutionMaquileForm( obj, product_id ){
		var id = $( obj ).attr( 'id' );
		var url = '../../plugins/maquile.php?fl_maquile=getMaquileForm&product_id=' + product_id;
		if( $( obj ).val() > 0 ){
			url += "&quantity=" + $( obj ).val();
		}
		url += "&function=setResolutionProductMaquile( '" + id + "' );";
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setResolutionProductMaquile( obj ){
		$( '#' + obj ).val( $( '#maquila_decimal' ).val() );
		close_emergent();
	}

</script>