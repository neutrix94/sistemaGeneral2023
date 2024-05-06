<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center"><?php echo $resp->message;?></h2>
		<img src="../../../../img/img_casadelasluces/load.gif">
	</div>
<?php
	if( isset( $is_payment_petition ) && $is_payment_petition == true ){
?>
	<div class="row text-center">
		<div class="col-6 text-center">
			<button
				class="btn btn-info"
				onclick="buscar_repuesta_peticion_por_folio( '<?php echo $resp->folio_unico_transaccion;?>' );"
			>
				<i class="icon-arrows-cw">Recargar respuesta</i>
			</button>
		</div>
		<div class="col-6 text-center">
			<button
				class="btn btn-danger"
				onclick="stop_server_events( '<?php echo $resp->folio_unico_transaccion;?>' );"
			>
				<i class="icon-cancel-circled">Cancelar y cerrar</i>
			</button>
		</div>
	</div>
<?php
	}else{
?>
	<button
		class="btn btn-danger"
		onclick="stop_server_events( '<?php echo $resp->folio_unico_transaccion;?>' );"
	>
		<i class="icon-cancel-circled">Cancelar y cerrar</i>
	</button>
<?php
	}
?>
</div>	

<script>


// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->folio_unico_transaccion;?>';
	var emergent_count_tmp = <?php echo $counter;?>;
	//alert( server_url );
	/*const eventSource = new EventSource( server_url );
	// Define una función para manejar los mensajes entrantes
	eventSource.onmessage = function(event) {
	    const messageContainer = document.getElementById('message-container');
	    if ( event.data != '' && event.data != null ) {
	    	//messageContainer.innerHTML += '<p>' + event.data + '</p>';
        	$( '.emergent_content' ).html( `<h2 class="text-success text-center">${event.data}</h2>
				<div class="text-center">
					<button
						type="button"
						class="btn btn-success"
						onclick="marcar_notificacion_vista( '<?php echo $resp->folio_unico_transaccion;?>' );"
					><i class="icon-ok=circle">Aceptar y marcar notificacion como vista</i>
					</button>
				</div>` );
        	eventSource.close(); // Cierra la conexión SSE
        	//console.log('Conexión SSE detenida.');
        	//alert( "Cobro exitoso!" );
        	setTimeout( function(){
        		//$( '.emergent_content' ).html( '' );
        		//$( '.emergent' ).css( 'display', 'none' );
        		//obtiene el ordenId para los botones
        		//var url = "ajax/dp.php?fl=getOrderId&transaction_id=<?php echo $resp->folio_unico_transaccion;?>";
        		//var resp = ajaxR( url );

        		if( event.data.trim() == 'Transacción exitosa' || event.data.trim() == 'Transaccion exitosa' ){
        			$( '#reprint_btn_' + emergent_count_tmp ).removeClass( 'no_visible' );
        			$( '#reprint_btn_' + emergent_count_tmp ).attr( 'onclick', 'rePrintByOrderId( \'<?php echo $resp->folio_unico_transaccion;?>\' )' );
        			$( '#cancel_btn_' + emergent_count_tmp ).removeClass( 'no_visible' );
        			$( '#cancel_btn_' + emergent_count_tmp ).attr( 'onclick', 'cancelByOrderId( \'<?php echo $resp->folio_unico_transaccion;?>\' )' );
        			
        			$( '#payment_btn_' + emergent_count_tmp ).addClass( 'no_visible' );
					
					$( '#cancel_btn_' + emergent_count_tmp ).remove();	
					carga_pedido( $( '#id_venta' ).val() );
						getHistoricPayment( respuesta.id_venta );
        		}
        	}, 2000
    		);
    	}
	};

	function stop_server_events( petition_id ){
		if( ! confirm( "Realmente deseas cancelar el cobro?" ) ){
			return false;
		}
		var url = "ajax/db.php?fl=cancelEvents&transaction_id=" + petition_id;
		alert( url );
		var resp = ajaxR( url ).trim();
		alert( resp );
		if( resp != 'ok' ){
			alert( "Error : \n" + resp );
		}
        eventSource.close(); // Cierra la conexión SSE
	}*/
	</script>
