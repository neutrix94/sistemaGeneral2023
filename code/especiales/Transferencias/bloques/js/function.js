var global_current_transfer = null;
var global_current_validation_block = null;
var global_current_reception_block = null;

	function seek_transfer( event, obj ){
		var txt = $( '#seeker' ).val().trim();
		var resp = '';
		if( txt.length <= 0 ){
			//alert( "El buscador debde de contener un valor, verifica y vuelve a intentar" );
			$( '.seeker_response' ).html( '' );
			$( '.seeker_response' ).css( 'display', 'none' );
			return false;
		}else{
			txt = txt.toUpperCase();
	//busca en las transferencias actuales 
			$( '#blocks_list tr' ).each( function( index ) {
				if( $( '#transfer_row_1_' + index ).html().includes( txt.trim() ) 
					|| $( '#transfer_row_3_' + index ).html().includes( txt.trim() ) 
				){
					var tmp =  $( '#transfer_row_3_' + index ).html().trim();
					resp += `<div class="" onclick="focus_response( ${index} );" style="width : 100%; box-shadow : 1px 1px 10px rgba( 0, 0, 0, .5); margin : 10px; padding:10px; color : black; )">
								${tmp}
							</div>`;
				}
			});
			//alert( resp );
			$( '.seeker_response' ).html( resp );
			$( '.seeker_response' ).css( 'display', 'block' );
		}
	}

	function focus_response( counter ){
		$( '.seeker_response' ).html( '' );
		$( '.seeker_response' ).css( 'display', 'none' );
		$( '#seeker' ).val( '' );
		$( '#btn_del_' + counter ).focus();
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function unlock_block( validation_block_id, reception_block_id = null ){
		var url = "ajax/db.php?fl=unlock_block&validation_block_id=" + validation_block_id;
		if( reception_block_id != null ){
			url += "&reception_block_id=" + reception_block_id;
		}
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( "Error : " + response );
			return false;
		}
		close_emergent();
	}

	function before_remove_transfer( transfer_id, validation_block_id, reception_block_id = null ){
		var url = "ajax/db.php?fl=infoBeforeRemove&transfer_id=" + transfer_id;
		url += "&validation_block_id=" +validation_block_id;
		if( reception_block_id != null ){
			url += "&reception_block_id=" + reception_block_id;
		}
		var response = ajaxR( url );

		//alert( transfer_id );
		var resp = `<div class="row">
						${response}
						<div>
							<h5>Los escaneos de validación de esta transferencia serán asignados automáticamente a los 
							registros faltantes de validación de Transferencias que pertenezcan al mismo bloque</h5>
						</div>
						<div class="col-6 text-center">
							<button class="btn btn-success"
			
								onclick="before_remove_transfer_2( ${transfer_id}, ${validation_block_id}, ${reception_block_id} );">
								<i class="icon-ok-circle">Aceptar</i>
							</button>
						</div>
						<div class="col-6 text-center">
							<button class="btn btn-danger"
									
								onclick="unlock_block( ${validation_block_id}, ${reception_block_id} );">
								<i class="icon-cancel-circled">Cancelar</i>
							</button>
						</div>
					</div>`;
		
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function before_remove_transfer_2( transfer_id, validation_block_id, reception_block_id = null ){
		global_current_transfer = transfer_id;
		global_current_validation_block = validation_block_id;
		global_current_reception_block = reception_block_id;
		var url = 'ajax/db.php?fl=beforeRemoveTransfer';
		url += '&transfer_id=' + transfer_id;
		url += '&validation_block_id=' + validation_block_id;
		if( reception_block_id != null ){
			url += '&reception_block_id=' + reception_block_id;
		}
//alert( url );
		var response = ajaxR( url );
		//alert(response);
		if( response.trim() == 'ok' ){
			var type_message = ( reception_block_id == null ? 'La transferencia fue removida del bloque' : 'El bloque de transferencias fue removido ' )
			//var type = (  )
			alert( `${type_message} exitosamente.` );
			reload_transfer_list( reception_block_id == null ? 'validation' : 'reception' );
			close_emergent();
			close_emergent_2();
			close_emergent_3();
			return false;
		}
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function reload_transfer_list( type ){
		var url = 'ajax/db.php?fl=getTransfers';
		url += ( $( "#sucursal_id" ).val() > 0 ? "&sucursal_id=" + $( "#sucursal_id" ).val() : '' );
		url += "&type_block_id=" + type;
//alert( url );
		var response = ajaxR( url );
		$( '#blocks_list' ).empty();
		$( '#blocks_list' ).append( response );
		/*if( $( "#type_block_id" ).val() == 'reception' ){
			$( '#reception_block_header' ).removeClass( 'no_visible' );	
		}else{
			$( '#reception_block_header' ).addClass( 'no_visible' );	
		}*/
	}

	

	/*function resolve_detail( transfer_id, action, pieces_quantity, detail_id, product_provider_id, validation_block_id = null, reception_block_id = null ){
		var url = 'ajax/db.php?fl=resolve&action=' + action;
		url += '&quanity=' + pieces_quantity + '&detail_id=' + detail_id;
		url += "&product_provider_id=" + product_provider_id;
		url += "&transfer_id=" + transfer_id;
		var response = ajaxR( url );
		alert( response );
	}*/

	function resolve_detail( action, transfer_product_id, type ){
		switch( action ){
			case -1:/*regresar*/
				getValidationDetail( transfer_product_id, type );
			break; 
			case 1:/*asignar*/
				reasignTransferDetail( transfer_product_id, type );
			break; 
		}
	}
//funcion para reasignar el detalle
	function reasignTransferDetail( transfer_product_id, type ){
		var url = "ajax/db.php?fl=reasignTransferDetailExcedent&transfer_product_id=" + transfer_product_id;
		url += "&current_transfer_block=" + global_current_validation_block + "&type=" + type + "&excedent_permission=1";
		var response = ajaxR( url ).split( '|' );
	//alert( url );
		if ( response[0] != 'ok' ) {
			alert( response[0] );
			$( '.emergent_content' ).append( response[0] );
		}else{
			alert( "Producto ajustado para enviar exitosamente." );
			reload_transfer_list( type );
			before_remove_transfer( global_current_transfer, global_current_validation_block, global_current_reception_block );
		}
	}


	function getValidationDetail( transfer_product_id, type ){
		var url = 'ajax/db.php?fl=getValidationDetail&transfer_product_id=' + transfer_product_id;
		url += "&type=" + type;
		var response = ajaxR( url );
		$( '.emergent_content_2' ).html( response );
		$( '.emergent_2' ).css( 'display', 'block' );
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