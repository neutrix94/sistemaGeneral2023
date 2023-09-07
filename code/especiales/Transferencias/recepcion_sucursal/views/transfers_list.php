<?php

?>

	<div class="">

		<br>
			<h5 class="title_sticky" style="position : sticky; top : 28px;">Transferencias</h5>
		<div class="group_card transfer_container">
		<div class="input-group" style="position : sticky; top : -10px; z-index : 3;">
			<input 
				type="text" 
				id="transfers_seeker"
				class="form-control" 
				onkeyup="seekTransferByBarcode( event );"
				placeholder="Escanear Transferencias por recibir">
			<button class="btn btn-warning"
				onclick="seekTransferByBarcode( 'intro' );"
			>
				<i class="icon-barcode"></i>
			</button>
		</div>
			<table class="table table-striped table-bordered">
				<thead class="header_sticky" style="position : sticky; top : 25px;"><!-- style="position : sticky; top : -15px; background-color : white; " -->
					<tr>
						<th>Bloque</th>
						<th>Transferencias</th>
						<th>Recibir</th>
						<th>Contar</th>
					</tr>
				</thead>
				<tbody id="blocks_resolution_list">
				</tbody>
			</table>
			<!--table class="table table-bordered table_80">
				<thead class="header_sticky" style="position : sticky; top : 50px;">
					<tr>
						<th>Bloque<br>Rec</th>
						<th class="icon-barcode text-center"></th>
						<th>Bloque<br>Valid</th>
						<th>Transf</th>
						<th class="no_visible">Fecha</th>
						<th class="no_visible">Recibir</th>
						<th>Imprimir</th><!-- implementado por Oscar 2023 
					</tr>
				</thead>
				<tbody class="transfers_list_content" id="transfers_list_content">
					<?php
					//	echo getTransfersToReceive( $sucursal_id, $perfil_usuario, $link );
					?>
				</tbody>
				<tfoot></tfoot>
			</table!-->
		</div>
		
		<br>
		
		<!--div class="row">
			<div class="col-2"></div>
			<div class="col-8">
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="makeTransferToReceive( '.transfers_list_content' );"
				>
					Recibir<i class="icon-truck"></i>
				</button>
			</div>
			<div class="col-2"></div>
		</div-->
		<br>
		<div class="row group_card" id="permission_container">
		</div>


	</div>

<script type="text/javascript">
	
	/*function getAllGroup( counter ){
		var val = 1;
		var reception_block = $( '#reception_list_6_' + counter ).html().trim();
		global_current_validation_blocks;
		global_current_reception_blocks;
		if( ! $( '#reception_list_8_' + counter ).prop( 'checked' ) ){
			val = 0;
		}
		if( reception_block == 'null' ){//si proviene de un bloque sin asignar

		}
		//marca los bloques de validacion que corresponden a los bloques de recepcion
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#reception_list_6_' + index ).html().trim() == block && block != 'null' ){
				if( val == 1 ){
					$( '#reception_list_8_' + index ).prop( 'checked', true );
				}else{
					$( '#reception_list_8_' + index ).removeAttr( 'checked', true );
				}
			}

		});*/
	/*verifica si ningún check esta checado
		var without_sucursal = 0;
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#reception_list_8_' + index ).prop( 'checked' ) ){
				without_sucursal ++;
			}
		});
		if( without_sucursal == 0 ){
			global_current_transfer_destinity = '';
		}
	}
*/
	//var global_transfers_to_set = new Array(); 
	function makeTransferToReceive( obj_list ){
		/*global_transfers_to_set = new Array();
		global_transfers_block_validation_to_set = new Array();
		global_transfers_block_reception_to_set = new Array();*/
		var transfers_to_receive_info = '<div class="transfer_to_receive_container"><div class="row header_transfer_to_receive">'
			+ '<div class="col-6 text-center">Folio</div>'
			+ '<div class="col-6 text-center">Fecha</div>'
		+ '</div>';

		$( obj_list + " tr" ).each(function ( index ) {
			if( $( '#receive_' + index ).prop( 'checked' ) ){
				transfers_to_receive_info += '<div class="row">';

				/*$(this).children("td").each(function ( index2 ) {
					if( index2 == 0 ){
						global_current_transfers.push( $( this ).html() );
						transfers_to_receive_info += '<div class="no_visible">' + $( this ).html() + '</div>';
					}else if( index2 <= 2 ){
						transfers_to_receive_info += '<div class="col-6">' + $( this ).html() + '</div>';
					}	
				});*/
				$( '#reception_list_3_' + index ).children( 'div' ).each( function( index2 ){					
						global_transfers_to_set.push( $( this ).html().trim() );
				});

				if( $( '#receive_' + index ).prop( 'checked' ) 
					&& global_transfers_block_validation_to_set.indexOf( $( '#receive_' + index ).val() ) == -1 
					&& $( '#receive_' + index ).val() != ''
				){	
					global_transfers_block_validation_to_set.push( $( '#receive_' + index ).val() );
				}
				if( document.getElementById( 'reception_block_' + index ) 
					&& $( '#reception_block_' + index ).prop( 'checked' )
					&& global_transfers_block_reception_to_set.indexOf( $( '#reception_block_' + index ).val() ) == -1
					&& $( '#reception_block_' + index ).val() != ''
				){
					global_transfers_block_reception_to_set.push( $( '#reception_block_' + index ).val() );
				}

				transfers_to_receive_info += '<div class="no_visible">' + $( this ).html() + '</div>';
				transfers_to_receive_info += '<div class="col-6">' + $( '#reception_list_3_' + index ).html();
				transfers_to_receive_info += '</div>';
				transfers_to_receive_info += '<div class="col-6" style="vertical-align : middle;">' + $( '#reception_list_4_' + index ).html();
				transfers_to_receive_info += '</div>';
				transfers_to_receive_info += '</div>';
			}
		});
//alert( `here : ${global_transfers_block_validation_to_set}`);
		/*alert( global_transfers_to_set );
		alert( global_transfers_block_validation_to_set );
		alert( global_transfers_block_reception_to_set );*/
		
		$( '.emergent_content' ).html( 
			'<br /><br />'
			+ '<div style="min-height: 350px;"><p align="center">Las siguentes transferencias serán recibidas :<p>' 
				+ transfers_to_receive_info
				+ '<br />'
				+ '<div class="row">'
					+ '<div class="col-2"></div>'
					+ '<div class="col-8">'
						+ '<button onclick="setTransferToReceive();close_emergent();" class="btn btn-success form-control">'
							+ 'Confirmar y continuar'
						+ '</button>'
					+'</div>'
					+ '<div class="col-2"></div>'
				+ '</div>'
			+ '</div>' );//show_view( \'.mnu_item.source\', \'.receive_transfers\' );

		$( '.emergent' ).css( 'display', 'block' );	
		loadLastReceptions();
		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
	}

	function setTransferToReceive(){
		var reload = false;
		if( validateGroups() == false ){
			alert( "Primero escanea los folios que falta para continuar!" );
			return false;
		}
		var url = 'ajax/db.php?fl=setTransferToReceive&transfers_ids=' + global_transfers_to_set;
		url += "&validation_blocks=" + global_transfers_block_validation_to_set;
		url += "&reception_blocks=" + global_transfers_block_reception_to_set;
		if( global_new_reception_blocks != '' ){
			url += "&new_block=" + global_new_reception_blocks;
		}
		url += "&new_transfers=" + global_transfer_to_add;
		if( global_transfer_to_add.length > 0 ){
			reload = true;
		}
//alert( url );
		var response = ajaxR( url ).split( '|' );
//alert( response );
		if( response[0] == 'ok' ){
			if( reload == true ){
				location.reload();
				return false;
			}
//alert( global_transfers_to_set );
			global_current_transfers = global_transfers_to_set;
			global_current_validation_blocks = response[1];
			global_current_reception_blocks = response[2];
			
			//console.log( '1:',global_current_transfers, global_current_reception_blocks );
			loadLastReceptions();
			receptionResumen( 1 );
			receptionResumen( 2 );
			receptionResumen( 3 );
		//carga bloques de resolucion
			getResolutionForms();

			//getTransfersToReceive();
			
			//global_transfers_to_set = new Array();
			global_transfers_block_validation_to_set = new Array();
			global_transfers_block_reception_to_set = new Array();
			global_transfer_to_add = new Array();

//console.log( '1:', global_current_transfers, global_current_reception_blocks, global_current_validation_blocks );

			show_view( '.mnu_item.source', '.receive_transfers' );

/*implementacion Oscar 2023 para la sesion de recepcion*/
			if( localStorage.getItem( 'reception_token' ) == null ){
				if( ! create_reception_token() ){
					return false;
				}
			}
/*fin de cambio Oscar 2023*/
		}else if( response[0] == 'exception' ){
//alert( 'here' );
			setTimeout( function( ){
				$( '.emergent_content' ).html( response[1] );
				$( '.emergent' ).css( 'display', 'block' );
				$( '.emergent_content' ).focus();

			}, 300 );
		}else{
			alert( "Error : " + response );
		}
	}

	/*function getTransfersToReceive(){
		var url = "ajax/db.php?fl=getTransfersToReceive";
		var response = ajaxR( url );
		$( '#transfers_list_content' ).empty();
		$( '#transfers_list_content' ).append( response );
	}*/

</script>

<script>
	getResolutionBlocks();
</script>
