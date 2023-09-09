
	function sendTerminalPetition( counter, terminal_id ){		
		var amount = $( `#t${counter}` ).val();
		if( amount <= 0 ){
			alert( "El monto debe de ser mayor a cero!" );
			$( `#t${counter}` ).focus();
			return false;
		}
		var url = "ajax/db.php?fl=sendPaymentPetition&amount=" + amount;
		url += "&terminal_id=" + terminal_id;
		//alert( url );
		var resp = ajaxR( url );
		console.log( resp );
		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
	} 

