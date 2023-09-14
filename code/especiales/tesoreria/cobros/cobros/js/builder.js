
	function buildPayment(){
		var content = `<tr>
			<td><td>
			<td><td>
			<td><td>
		</tr>`;

	}

	function getCashPaymentForm(){
		var amount = $( '#efectivo' ).val();
		if( amount <= 0 ){
			alert( "La cantidad del pago debe de ser mayor a cero!" );
			$( '#efectivo' ).select();
			return false;
		}
		var content = `<div>
			<input 
					type="number" 
					id="efectivo_recibido" 
					class="form-control" 
					onkeydown="prevenir(event);" 
					onkeyup="valida_tca(this,event,3);calcula_cambio();"
				>
			<br>
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="addCashPayment( ${amount} );"
			>
				<i class="icon-plus">Agregar cobro</i>
			</button>
			<br>
			<br>
			<button
				type="button"
				class="btn btn-danger form-control"
				onclick="close_emergent();"
			>
				<i class="icon-cancel-circled">Cancelar</i>
			</button>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function addCashPayment( amount ){
		var content = `<tr>
			<td>Efectivo</td>
			<td>Efectivo</td>
			<td>${amount}</td>
		</tr>`;
		$( '#payments_list' ).append( content );
		close_emergent();
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}