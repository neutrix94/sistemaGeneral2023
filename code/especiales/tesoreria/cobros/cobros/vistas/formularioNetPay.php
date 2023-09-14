<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center">Esperando autorizacion...</h2>
		<img src="../../../../../img/img_casadelasluces/load.gif">
	</div>
	<button
		class="btn btn-danger"
		onclick="stop_server_events(<?php echo $resp->petition_id;?>);"
	>
		<i class="icon-cancel-circled">Cancelar y cerrar</i>
	</button>
</div>	
<script>
	// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->petition_id;?>';
	var emergent_count_tmp = <?php echo $counter;?>;
	//alert( server_url );
	const eventSource = new EventSource( server_url );
	// Define una función para manejar los mensajes entrantes
	eventSource.onmessage = function(event) {
	    const messageContainer = document.getElementById('message-container');
	    if ( event.data != '' && event.data != null ) {
	    	//messageContainer.innerHTML += '<p>' + event.data + '</p>';
        	$( '.emergent_content' ).html( `<h2 class="text-success text-center">${event.data}</h2>` );
        	eventSource.close(); // Cierra la conexión SSE
        	//console.log('Conexión SSE detenida.');
        	//alert( "Cobro exitoso!" );
        	setTimeout( function(){
        		$( '.emergent_content' ).html( '' );
        		$( '.emergent' ).css( 'display', 'none' );
        		//obtiene el ordenId para los botones
        		//var url = "ajax/dp.php?fl=getOrderId&transaction_id=<?php echo $resp->petition_id;?>";
        		//var resp = ajaxR( url );

        		if( event.data.trim() == 'Transacción exitosa' ){
        			$( '#reprint_btn_' + emergent_count_tmp ).removeClass( 'no_visible' );
        			$( '#reprint_btn_' + emergent_count_tmp ).attr( 'onclick', 'rePrintByOrderId( <?php echo $resp->petition_id;?> )' );
        			$( '#cancel_btn_' + emergent_count_tmp ).removeClass( 'no_visible' );
        			$( '#cancel_btn_' + emergent_count_tmp ).attr( 'onclick', 'cancelByOrderId( <?php echo $resp->petition_id;?> )' );
        			
        			$( '#payment_btn_' + emergent_count_tmp ).addClass( 'no_visible' );
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
		var resp = ajaxR( url ).trim();
		alert( resp );
		if( resp != 'ok' ){
			alert( "Error : \n" + resp );
		}
        eventSource.close(); // Cierra la conexión SSE
	}
	</script>
