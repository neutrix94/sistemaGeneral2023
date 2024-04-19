<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center"><?php echo $resp->message;?></h2>
		<img src="../../../../img/img_casadelasluces/load.gif">
	</div>
	<button
		class="btn btn-danger"
		onclick="stop_server_events(<?php echo $resp->folio_unico_peticion;?>);"
	>
		<i class="icon-cancel-circled">Cancelar y cerrar</i>
	</button>
</div>	

	<!--script>
        const serverUrl = 'ajax/server_events.php?transaction_id=<?php echo $resp->folio_unico_peticion;?>';
        const emergentCountTmp = <?php echo $counter;?>;

        // Crear un nuevo Web Worker
        const myWorker = new Worker('js/webWorker.js');

        // Manejar mensajes del Web Worker
        myWorker.addEventListener('message', function (e) {
            // Este código se ejecutará cuando el Web Worker envíe un mensaje de vuelta
            const result = e.data;
            console.log('Resultado del Web Worker:', result);

            // Puedes realizar acciones adicionales con el resultado aquí
        });

        // Crea una nueva conexión SSE
        var eventSource = new EventSource(serverUrl);

        // Define una función para manejar los mensajes entrantes del SSE
        eventSource.onmessage = function(event) {
            const messageContainer = document.getElementById('message-container');
            if (event.data !== '' && event.data !== null) {
                $( '.emergent_content' ).html(`<h2 class="text-success text-center">${event.data}</h2>`);
                eventSource.close(); // Cierra la conexión SSE

                // Envía un mensaje al Web Worker con la información relevante
                myWorker.postMessage({
                    eventData: event.data,
                    emergentCountTmp: emergentCountTmp
                });

                // Continúa con el resto del código aquí si es necesario
            }
        };

		function stop_server_events( petition_id ){
            $( '.emergent_content' ).html(`<h2 class="text-success text-center">Cancelado desde 'stop_server_events'</h2>`);
			eventSource.close();
			close_emergent();
			/*if( ! confirm( "Realmente deseas cancelar el cobro?" ) ){
				return false;
			}
			/*var url = "ajax/db.php?fl=cancelEvents&transaction_id=" + petition_id;
			alert( url );
			var resp = ajaxR( url ).trim();
			alert( resp );
			/*if( resp != 'ok' ){
				alert( "Error : \n" + resp );
			}*/
			/*myWorker.postMessage({
						eventData: event.data,
						emergentCountTmp: emergentCountTmp
					}); // Cierra la conexión SSE*/
		}
    </script-->
<script>
	// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->folio_unico_transaccion;?>';
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
	}
	</script>
