<div class="row" style="padding : 10px;">
	<div class="col-12">
		<!--select class="text-center form-select" style="font-size : 120%;">
			<option value="Tarjeta">Tarjeta</option>
			<option value="Transferencia">Transferencia</option>
			<option value="Cheque">Cheque</option>
		</select>
		<div class="input-group">
			<input type="number" class="form-control" placeholder="$">
			<button
				type="button"
				class="btn btn-success"
			>
				<i class="icon-plus"></i>
			</button>
		</div-->
		<h2 class="text-center">Tarjeta</h2>
		<table class="table table-bordered">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th>Monto</th>
					<th>Cliente</th>
				</tr>
			</thead>
			<tbody id="dinamic_payments_list_final">
				<!--tr>
					<td class="text-start">$ 1,000.00</td>
					<td>
						
					</td>
				</tr>
				<tr>
					<td class="text-start">$ 2,000.00</td>
					<td>
						<select class="form-select">
							<option>--Seleccionar--</option>
							<option>Cliente 1</option>
							<option>Cliente 2</option>
							<option>Cliente 3</option>
						</select>	
					</td>
				</tr-->
			</tbody>
		</table>

	</div>
	<div class="col-12">
		<hr>
		<h2 class="text-center">Efectivo</h2>
		<!--div class="row text-center">
			<div class="col-6">
				<label for="">Total</label>
				<input type="number" class="form-control">
			</div>
			<div class="col-6">
				<label for="">Restante</label>
				<input type="number" class="form-control">
			</div>
		</div-->
		
		<table class="table table-bordered">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th>Monto</th>
					<th>Cliente</th>
				</tr>
			</thead>
			<tbody id="cash_payments_list_final">
				<!--tr>
					<td class="text-start">$ 2,000.00</td>
					<td>
						<select class="form-select">
							<option>--Seleccionar--</option>
							<option>Cliente 1</option>
							<option>Cliente 2</option>
							<option>Cliente 3</option>
						</select>	
					</td>
				</tr-->
			</tbody>
		</table>
	</div>
</div>
<br>
<div class="row">
	<div class="col-2"></div>
	<div class="col-8">
		<button 
			class="btn btn-success form-control"
			onclick="saveBills();"
		>
			<i class="icon-floppy-1">Guardar e imprimir</i>
		</button>
	</div>
</div>
<br><br>

<script type="text/javascript">
	
	function build_cash_payment_client(){
		var content = ``;
		var sum = 0;
		var tmp = sessionStorage.getItem( 'cash_amount' ).split( ',' );
		for( var i = 0; i < tmp.length; i++ ){
			content += build_cash_payment_final( tmp[i] );
			sum += parseInt( tmp[i] );
		}
		var remaining = parseInt( $( '#cash_remaining' ).val() ) - sum;
		$( "#cash_payments_list_final" ).html( content );
		//$( '#cash_remaining' ).val( remaining );
	}

	function build_card_payment_client(){
		var content = ``;
		var sum = 0;
		var tmp = sessionStorage.getItem( 'card_payments' ).split( '|' );
		for( var i = 0; i < tmp.length; i++ ){
			content += build_card_payment_final( tmp[i] );
			sum += parseInt( tmp[i] );
		}
		//alert( content );
		//var remaining = parseInt( $( '#cash_remaining' ).val() ) - sum;
		$( "#dinamic_payments_list_final" ).html( content );
		//$( '#cash_remaining' ).val( remaining );
	}

	function build_cash_payment_final( amount ){
		var content = `<tr>
					<td class="text-end">${amount}</td>
					<td>`;
		content += build_paymet_clients_combo();
		content += `</td>
				</tr>`;
		return content;
	}

	function build_card_payment_final( tmp ){
		//alert();
		var tmp_1 = tmp.split( '~' );
		var content = `<tr>
					<td class="text-end" style="display : none;">${tmp_1[0]}</td>
					<td class="text-end" style="display : none;">${tmp_1[1]}</td>
					<td class="text-end" style="display : none;">${tmp_1[2]}</td>
					<td class="text-end" style="display : none;">${tmp_1[3]}</td>
					<td class="text-end">${tmp_1[4]}</td>
					<td>`;
		content += build_paymet_clients_combo();
		content += `</td>
				</tr>`;
		return content;
	}


	function build_paymet_clients_combo( option_selected ){
		var options = `<select class="form-select">
							<option>--Seleccionar--</option>`;
		var tmp = sessionStorage.getItem( 'costumers' ).split( ',' );
		for( var i = 0; i < tmp.length; i++ ){
			options += `<option value="${tmp[i]}">${tmp[i]}</option>`;
		}
		options += `</select>`;
		return options;
	}

	function saveBills(){
		var card_payments = "", cash_payments = "", tickets = "";
	//verifica pagos con tarjeta
		card_payments_validation = 'ok';
		$( '#dinamic_payments_list_final tr' ).each( function( index ){
			card_payments += ( card_payments == "" ? "" : "|" );
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 <= 4 ){
					card_payments += $( this ).html() + "~";
				}else if( index2 == 5 ){
					$( this ).children( 'select' ).each( function( index3 ){
						if( $( this ).val() == 0 || $( this ).val() == '--Seleccionar--' ){
							card_payments_validation = index;
							$( this ).css( 'border', '1px solid red' );
							return false;
						}else{
							$( this ).css( 'border', 'none' );
							card_payments += $( this ).val();
						}
					});
				}
			});
			if( card_payments_validation != 'ok' ){
				return false;
			}
		});
		if( card_payments_validation != 'ok' ){
			alert( "Aun hay pagos con tarjeta sin Cliente asignado!\n Verifica y vuelve a intentar!" );
			return false;
		}
		//alert( card_payments );
	//verifica pagos en efectivo
		cash_payments_validation = 'ok';
		$( '#cash_payments_list_final tr' ).each( function( index ){
			cash_payments += ( cash_payments == "" ? "" : "|" );
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 == 0 ){
					cash_payments += $( this ).html() + "~";
				}else if( index2 == 1 ){
					$( this ).children( 'select' ).each( function( index3 ){
						if( $( this ).val() == 0 || $( this ).val() == '--Seleccionar--' ){
							cash_payments_validation = index;
							$( this ).css( 'border', '1px solid red' );
							return false;
						}else{
							$( this ).css( 'border', 'none' );
							cash_payments += $( this ).val();
						}
					});
				}
			});
			if( cash_payments_validation != 'ok' ){
				return false;
			}
		});
		if( cash_payments_validation != 'ok' ){
			alert( "Aun hay pagos en efectivo sin Cliente asignado!\n Verifica y vuelve a intentar!" );
			return false;
		}
	//folios de notas
		var tmp = sessionStorage.getItem( 'tickets' ).split( ',' );
		for( var i = 0; i < tmp.length; i++ ){
			var aux = tmp[i].split( '~' );
			tickets += ( tickets == "" ? "" : "," );
			tickets += "'" + aux[0] + "'";
			//addTicket( aux[0], aux[1] );
		}

		var url = "ajax/db.php?bill_fl=saveBills&cash=" + cash_payments + "&card=" + card_payments;
		url += "&tickets=" + tickets;
		//alert( url );//return false;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );return false;
		}else{
			sessionStorage.clear();
			location.reload();
		}
	}

</script>
