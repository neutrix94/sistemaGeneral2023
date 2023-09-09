<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center">Cargando...</h2>
		<img src="../../../../../img/img_casadelasluces/load.gif">
	</div>
</div>	
<script>
	// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->petition_id;?>';
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
        	}, 2000
    		);
    	}
	};
	</script>
