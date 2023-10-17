	function setTicket( ticket_id, folio, was_payed, amount ){
		var content = `<tr>
			<td style="display:none;">${ticket_id}</td>
			<td>${folio}</td>
			<td style="display:none;">${was_payed}</td>
			<td class="text-end">${amount}</td>
			<td class="text-center">
				<button
					class="btn"
				>
					<i class="icon-cancel-circled text-danger"></i>
				</button>
			</td>
		</tr>`;
		$( '#tickets_list' ).append( content );
		$( '#res_busc' ).html( '' );
		$( '#res_busc' ).css( 'display', 'none' );
		$( '#buscador' ).val( '' );
	}

	function setTickets(){
		$( '#tickets_list' ).html().trim();
		setTimeout( function(){
			close_emergent();
		}, 200 );
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}