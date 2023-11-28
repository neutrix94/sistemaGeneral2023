<?php
	$sql = "SELECT id_tipo_pago AS pyment_type, nombre AS name FROM ec_tipos_pago WHERE id_tipo_pago >= 7";
	$stm = $link->query( $sql ) or die( "Error al consultar los tipos de pagos : {$link->error}" );
	$pymentsTypes = "<option value=\"0\">--Selecciona tipo de pago--</option>";
	while ( $row = $stm->fetch_assoc() ) {
		$pymentsTypes .= "<option value=\"{$row['pyment_type']}\">{$row['name']}</option>";
	}
?>
<div class="row" style="padding : 10px;">
	<div class="col-12">
		<div class="input-group">
			<select class="text-center form-select" style="font-size : 120%;">
				<?php
					echo $pymentsTypes;
				?>
				<!--option value="Tarjeta">Tarjeta</option>
				<option value="Transferencia">Transferencia</option>
				<option value="Cheque">Cheque</option-->
			</select>
		</div>
		<div class="input-group">
			<!--button
				type="button"
				class=""
			>
				<i class="icon-dollar"></i>
			</button-->
			<input type="number" id="dinamic_amount" class="form-control" placeholder="$">
			<button
				type="button"
				class="btn btn-success"
				onclick="getDimamicPaymentForm();"
			>
				<i class="icon-plus"></i>
			</button>
		</div>
		<br>

		<table class="table table-bordered">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th class="text-center">Referencia</th>
					<th class="text-center">Monto</th>
					<th class="text-center">Afiliacion</th>
					<th class="icon-cancel-circled text-center"></th>
				</tr>
			</thead>
			<tbody id="dinamic_payments_list">
				<!--tr><td class="text-start" colspan="3">Example</td></tr>
				<tr><td class="text-start" colspan="3">Example</td></tr>
				<tr><td class="text-start" colspan="3">Example</td></tr-->
			</tbody>
		</table>

	</div>
	<div class="col-12">
		<h2 class="text-center">Efectivo</h2>
		<div class="row text-center">
			<div class="col-6">
				<label for="">Total</label>
				<input type="number" class="form-control text-end text-success" id="cash_total">
			</div>
			<div class="col-6">
				<label for="">Restante</label>
				<input type="number" class="form-control text-end text-danger" id="cash_remaining">
			</div>
		</div>
		<table class="table table-bordered">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th colspan="2">
						<div class="input-group">
							<input type="number" class="form-control" id="add_cash_input">
							<button
								class="btn btn-success"
								onclick="add_cash_payment();"
							>
								<i class="icon-plus-circle"></i>
							</button>
						</div>
					</th>
				</tr>
				<tr>
					<th class="text-center">Monto</th>
					<th class="text-center">Quitar</th>
				</tr>
			</thead>
			<tbody id="cash_payments_list">
				<!--tr>
					<td class="text-start">
						<input type="number" class="form-control">
					</td>
					<td>
						<button class="btn btn-danger">
							<i class="icon-cancel-circled"></i>
						</button>
					</td>
				</tr>
				<tr>
					<td class="text-start">
						<input type="number" class="form-control">
					</td>
					<td>
						<button class="btn btn-danger">
							<i class="icon-cancel-circled"></i>
						</button>
					</td>
				</tr>
				<tr>
					<td class="text-start">
						<input type="number" class="form-control">
					</td>
					<td>
						<button class="btn btn-danger">
							<i class="icon-cancel-circled"></i>
						</button>
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
			onclick="validate_cash_payments_and_references();"
		>
			<i class="icon-right-big">Continuar</i>
		</button>
	</div>
</div>
<br><br>
<script type="text/javascript">
	function load_payments_before(){
		var tickets = "";
		if( sessionStorage.getItem( 'tickets' ) != null ){
			var tmp = sessionStorage.getItem( 'tickets' ).split( ',' );
			for( var i = 0; i < tmp.length; i++ ){
				var aux = tmp[i].split( '~' );
				tickets += ( tickets == "" ? "" : "," );
				tickets += "'" + aux[0] + "'";
			}
		}
		var url = "ajax/db.php?bill_fl=get_payments&tickets=" + tickets;
		//alert( url );
		var response = ajaxR( url ).split( '|' );
		if( response[0] != 'ok' ){
			alert( "Error : \n" + response );
		}else{
			var data = JSON.parse( response[1] );
			build_payments( data );
			//console.log( data );
		}
	}

	function build_payments( data ){
	//tajetas
		var cash_amount = 0;
		var cards_payments = "";
		var counter = 0;	
		for ( var key in data ){
			if( data[key].afiliation_number != "Efectivo" ){
				cards_payments += `<tr id="card_payment_col_${counter}" value="${data[key].paymet_detail_id}">
					<td style="display:none;">${data[key].sale_folio}</td>
					<td style="display:none;">${data[key].sale_id}</td>
					<td id="payment_reference_${counter}" onclick="editarCelda( 'payment_reference_', ${counter} );">${data[key].reference}</td>
					<td style="display:none;">${data[key].sale_folio}</td>
					<td>${data[key].amount}</td>
					<td>
						<select id="card_payment_${counter}" class="form-select">
							<option value="${data[key].afiliation_id}">${data[key].afiliation_number}</option>
						<select>
					</td>
					<td>
						<button
							type="button"
							class="btn btn-danger"
						>
							<i class="icon-cancel-circled"></i>
						</button>
					</td>
				</tr>`;
				counter ++;
			}
		}
		$( "#dinamic_payments_list" ).html( cards_payments );
	//efectivo
		for ( var key in data ){
			if( data[key].afiliation_number == "Efectivo" ){
				cash_amount += parseInt( data[key].amount );
			}
		}
		$( '#cash_total' ).val( cash_amount );
		$( '#cash_remaining' ).val( cash_amount );
	}

	function add_cash_payment(){
		var amount  = parseInt( $( '#add_cash_input' ).val() );
		var content = ``;
		if( amount <= 0 ){
			alert( "El monto no puede ser menor a 1!\nVerifica y vuelve a intentar!" );
			$( '#add_cash_input' ).select();
			return false;
		}
		if( amount > parseInt( $( '#cash_remaining' ).val() ) ){
			alert( "El monto no puede ser mayor al restante por dividir!\nVerifica y vuelve a intentar!" );
			$( '#add_cash_input' ).select();
			return false;
		}

		content = build_cash_payment( amount );
		$( '#cash_payments_list' ).append( content );
		$( '#add_cash_input' ).val( '' );
		$( '#cash_remaining' ).val( parseInt( $( '#cash_remaining' ).val() ) - amount );
		if( sessionStorage.getItem( 'cash_amount' ) != null ){
			var tmp = sessionStorage.getItem( 'cash_amount' );
			tmp += ( tmp == "" ? "" : "," );
			tmp += "" + amount;
			sessionStorage.setItem( 'cash_amount', tmp );
		}else{
			sessionStorage.setItem( 'cash_amount', amount );
		}
	}

	function build_cash_payment( amount ){
		var content = `<tr>
						<td class="text-end">${amount}</td>
						<td class="text-center">
							<button class="btn btn-danger">
								<i class="icon-cancel-circled"></i>
							</button>
						</td>
					</tr>`;
		return content;
	}

	function validate_cash_payments_and_references(){
	//verifica que todos los pagos tengan referencia
		var card_payments_verification = 'ok';
		var card_payments = "";
		$( '#dinamic_payments_list tr' ).each( function ( index ){
			card_payments += ( card_payments == "" ? "" : "|" );
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 <= 4 ){
					card_payments += $( this ).html().trim() + "~";
				}else if( index2 == 5 ){
					//alert( $( '#card_payment_' + index ).val() );
					card_payments += $( '#card_payment_' + index ).val() + "~";
					card_payments += $( '#card_payment_' + index + " option:selected").text();
				}
				if( index2 == 2 ){
					if( $( this ).html().trim() == '' ){
						card_payments_verification = index;
						return false;
					}
				}
			});
			if( card_payments_verification != 'ok' ){
				return false;
			}
		});
		if( card_payments_verification != 'ok' ){
			alert( "Aún hay pagos con tarjeta sin Referencia\n Verifica y vuelve a intentar!" );
			$( '#payment_reference_' + card_payments_verification ).css( 'border', '1px solid red' );
			return false;
		}
		sessionStorage.setItem( 'card_payments', card_payments );
		if( parseInt( $( '#cash_remaining' ).val() ) > 0 ){
			alert( "Aun hay pagos en efectivo sin asignar\n Verifica y vuelve a intentar!" );
			return false;
		}
		//alert( 'ok' );
		show_view( this, '.save_bill');
	}


</script>




