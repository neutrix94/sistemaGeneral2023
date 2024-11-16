<?php
	
?>

<div class="row group_card">
	<div style="max-height: 300px; overflow : auto;">
		<table class="table table-bordered table-striped" >
			<thead style="position : sticky; top : -10px; background-color : white;">
				<tr>
					<th colspan="4" class="text-center">Conteo para resolución</th>
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
	<!--div style="max-height: 300px; overflow : auto;">
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
	</div-->
</div>
<div class="row">
	<div class="col-1"></div>
	<div class="col-10">
		<button 
			type="button"
			class="btn btn-success form-control"
			onclick="getResolutionsToSave();"
			id="insert_products_resolution_headers"
		>
			<i class="icon-ok-circle">Aceptar y Continuar</i>
		</button>

		<br><br>
		
		<button 
			type="button"
			id="btn_resolve_resolution"
			class="btn btn-info form-control"
			onclick="resolveResolution();"
			disabled
		>
			<i>Continuar con la resolucion</i>
		</button>
	</div>
</div>


<script type="text/javascript">
	function getResolutionsToSave(){
		var url = "", response = "";
		var excedente_and_missing = '';
		var doesnt_correspond = '';
		var stop = false;
	/*implementacion Oscar 2023 para verificar que la transferencia se haya marcado como finalizada
		url = "ajax/db.php?fl=checkTransferStatus&transfer_block_id=" + global_current_reception_blocks;
		//alert( url );
		response = ajaxR( url );
		//alert( response );
		if( response.trim() != 'ok' ){
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
			return false; 	
		}
	fin de cambio Oscar 2023*/
		$( '#missing_and_excedent_counter_capture tr' ).each( function ( index ){
			excedente_and_missing += ( excedente_and_missing == '' ? '' : '|~|' );
			excedente_and_missing += $( '#1_' + index ).html().trim() + '~';//id_producto
			
			if( $( '#4_' + index ).val() == '' || $( '#4_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar; si no hay, escribe 0" );
				$( '#4_' + index ).focus();
				stop = true;
				return false;
			}

			excedente_and_missing += $( '#4_' + index ).val() + '~';//conteo_fisico

			if( $( '#5_' + index ).val() == '' || $( '#5_' + index ).val() < 0 ){
				alert( "Debes de llenar todos los conteos para continuar; si no hay, escribe 0" );
				$( '#5_' + index ).focus();
				stop = true;
				return false;
			}
			excedente_and_missing += $( '#5_' + index ).val() + '~';//conteo_excedente
			excedente_and_missing += $( '#3_' + index ).html().trim() + '~';//inventario
			excedente_and_missing += $( '#6_' + index ).html().trim() + '~';//cantidad faltante
			excedente_and_missing += $( '#7_' + index ).html().trim() + '~';//id_transferencia_productos
			excedente_and_missing += $( '#0_' + index ).html().trim() + '~';//id_bloques_resoluciones
			excedente_and_missing += $( '#8_' + index ).html().trim();//id_registro conteo
			 
		});
		//id_producto//conteo_fisico//conteo_excedente//inventario//cantidad//id_transferencia_productos
		
		if( stop == true ){
			return false;
		}
		url = `ajax/productResolution.php?resolution_fl=saveResolutionPrevious&case_1=${excedente_and_missing}&case_2=${doesnt_correspond}&reception_block_id=${global_current_reception_blocks}`;
//console.log( url );alert( url );return false;
		response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		$( '#insert_products_resolution_headers' ).css( 'display', 'none' );//implementacion Oscar 2023 para ocultar el boton de continuar de la resolucion
	//implementacion Oscar 2023
		$( '#btn_resolve_resolution' ).removeAttr( 'disabled' );
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
		if( response[0] == 'ok' ){
			$( '.emergent_content_2' ).html( response[1] );
			$( '.emergent_2' ).css( 'display', 'block' );
			getResolutionForms();
		}else{
			$( '.emergent_content' ).html( response );
			$( '.emergent' ).css( 'display', 'block' );
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

	
	function start_resolution_transfer_proccess( steep = 0 ){
			if( steep == 0 ){
				if( $( "#alert_log_enabled" ).val() == 1 ){
					$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_resolution_transfer_proccess( 1 );"><i class="icon-ok-circled">Comenzar primer paso</button></div>` );
					$( '.emergent_4' ).css( 'display', 'block' );
				}else{
					setTimeout( function(){
						start_resolution_transfer_proccess(1);
					}, 500 );
				}
			}else if( steep == 1 ){//primer paso
				close_emergent_4();
				setTimeout( function(){
					var url = "ajax/productResolution.php?resolution_fl=updateTransfer&reception_block_id=" + global_current_reception_blocks;
					var resp = ajaxR( url );//alert( "resp" + resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}else{
						var tmp_json = JSON.parse( resp );
						$( '#initial_date_time_steep_one' ).html( tmp_json.tiempo.inicio );
						$( '#final_date_time_steep_one' ).html( tmp_json.tiempo.fin );
					}
					if( $( "#alert_log_enabled" ).val() == 1 ){
						$( '#json_steep_one' ).html( resp );
						$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_resolution_transfer_proccess( 2 );"><i class="icon-ok-circled">Continuar segundo paso</button></div>` );
						$( '.emergent_4' ).css( 'display', 'block' );
					}else{
						setTimeout( function(){
							start_resolution_transfer_proccess(2);
						}, 500 );
					}
				}, 500 );
			}else if( steep == 2 ){//segundo paso
				close_emergent_4();
				setTimeout( function(){
					var url = "ajax/productResolution.php?resolution_fl=updateTransfer&reception_block_id=" + global_current_reception_blocks;
					var resp = ajaxR( url );//alert( resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}else{
						var tmp_json = JSON.parse( resp );
						$( '#initial_date_time_steep_two' ).html( tmp_json.tiempo.inicio );
						$( '#final_date_time_steep_two' ).html( tmp_json.tiempo.fin );
					}
					if( $( "#alert_log_enabled" ).val() == 1 ){
						$( '#json_steep_two' ).html( resp );
						$( '.emergent_content_4' ).html( `<div class="text-center"><button type="button" class="btn btn-success" onclick="start_resolution_transfer_proccess( 3 );"><i class="icon-ok-circled">Continuar tercer paso</button></div>` );
						$( '.emergent_4' ).css( 'display', 'block' );
					}else{
						setTimeout( function(){
							start_resolution_transfer_proccess(3);
						}, 500 );
					}
				}, 500 );
			}else if( steep == 3 ){//tercer paso
				setTimeout( function(){
					var url = "ajax/productResolution.php?resolution_fl=updateTransfer&reception_block_id=" + global_current_reception_blocks;
					var resp = ajaxR( url );//alert( resp );
					resp = resp.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\n\t\t\t\t\t\t`, `\n`);
					resp = resp.replaceAll(`\r\n\t\t\t`, `\n`);
					resp = resp.replaceAll(`\\t`, `    `);
					resp = resp.replaceAll(`\\r\\n`, `\n`);
					resp = resp.replaceAll(`,"`, `,\n"`);
					resp = resp.replaceAll(`,{`, `,\n{`);
					if( resp.trim() == '' ){
						resp = `{"respuesta" : "Este proceso ya se habia realizado."}`;
					}else{
						var tmp_json = JSON.parse( resp );
						$( '#initial_date_time_steep_three' ).html( tmp_json.tiempo.inicio );
						$( '#final_date_time_steep_three' ).html( tmp_json.tiempo.fin );
					}
					$( '#json_steep_three' ).html( resp );
					$( '#btn_close_emergent' ).css( 'display', 'block' );
					close_emergent_4();
					hljs.initHighlighting.called = false;
					hljs.highlightAll();
					$( '#log_close_emergent_btn_container' ).removeClass( 'hidden' );
				}, 500 );
			}
		} 

</script>