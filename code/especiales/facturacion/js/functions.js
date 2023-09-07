
//mostrar / ocultar vistas del menú
	function show_view( obj, view ){
		if( view == '.payments' ){
			if( sessionStorage.getItem( 'costumers' ) == null ){
				alert( "Debes capturar almenos un cliente en el apartado de 'Factura' para continuar!" );
				return false;
			}

			if( sessionStorage.getItem( 'tickets' ) == null ){
				alert( "Debes capturar almenos un ticket en el apartado de 'Factura' para continuar!" );
				return false;
			}
			load_payments_before();
			if( sessionStorage.getItem( 'cash_amount' ) != null ){
				var content = "";
				var sum = 0;
				var tmp = sessionStorage.getItem( 'cash_amount' ).split( ',' );
				for( var i = 0; i < tmp.length; i++ ){
					content += build_cash_payment( tmp[i] );
					sum += parseInt( tmp[i] );
				}
				var remaining = parseInt( $( '#cash_remaining' ).val() ) - sum;
				$( "#cash_payments_list" ).html( content );
				$( '#cash_remaining' ).val( remaining );
			}
		}
		if( view == '.save_bill' ){
			if( sessionStorage.getItem( 'cash_amount' ) == null ){
				alert( "Debes seleccionar almenos un pago en el apartado de 'Pagos' para continuar!" );
				return false;
			}
			build_cash_payment_client();
			build_card_payment_client();
		}
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );
		if( view == '.bill' ){
			$( '#costumer_seeker' ).focus();
		}
	}

	function getDimamicPaymentForm(){
		var amount = $( '#dinamic_amount' ).val();
		if( amount <= 0 ){
			alert( "El monto debe ser mayor a cero!" );
			$( '#dinamic_amount' ).focus();
			return false;
		}
		var content = `<div class="row" style="padding : 10px;">
			<div class="text-center">
				Ingresa referencia : 
				<input type="text" class="form-control" id="reference_number_tmp">
			</div>
			<div class="text-center">
				Seleccionar afiliacion : 
				<select class="form-select" id="afiliation_number_tmp">
					<option value="0">-- Seleccionar --</option>
					<option value="1">NETPAY</option>
					<option value="2">INBURSA</option>
				</select>
			</div>
			<div class="row text-center">
				<div class="col-2"></div>
				<div class="col-8">
					<button 
						class="btn btn-success form-control"
						onclick="addDinamicPaymet( ${amount} );"
					>
						<i class="icon-plus">Agregar</i>
					</button>
				</div>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function addDinamicPaymet( amount ){
		var reference = $( '#reference_number_tmp' ).val();
		if( reference == '' || reference.length <= 5 ){
			alert( "Ingresa una referencia válida para continuar!" );
			$( '#reference_number_tmp' ).focus();
			return false;
		}
		var afiliation = $( '#afiliation_number_tmp' ).val();
		if( afiliation == '0' ){
			alert( "Selecciona una afiliacion valida para continuar!" );
			$( '#afiliation_number_tmp' ).focus();
			return false;
		}
		var content = buildDinamicPayment( amount, reference, afiliation );
		$( '#dinamic_payments_list' ).append( content );
		close_emergent();
	}

	function buildDinamicPayment( amount, reference, afiliation ){
		var content = `<tr>
			<td>${reference}</td>
			<td>${amount}</td>
			<td>${afiliation}</td>
			<td>
				<button
					type="button"
					class="btn btn-danger"
				>
					<i class="icon-cancel-circled"></i>
				</button>
			</td>
		</tr>`;
		return content;
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}
var cell_is_editing = 0;
	function editarCelda( prefix, counter ){
		if( cell_is_editing != 0 ){
			return false;
		}
		cell_is_editing = 1;
		var tmp = $( `#${prefix}${counter}` ).html();
		var content = `<input type="text" value="${tmp}" id="editable_cell" class="form-control" onblur="deseditarCelda( '${prefix}', ${counter} );">`;
		$( `#${prefix}${counter}` ).html( content );
		$( '#editable_cell' ).focus();
	}

	function deseditarCelda( prefix, counter ){
		var tmp = $( '#editable_cell' ).val();
		$( `#${prefix}${counter}` ).html( tmp );
		if( prefix == 'payment_reference_' ){
			if( tmp == '' ){
				$( `#${prefix}${counter}` ).css( 'border', '1px solid red' );
			}else{
				$( `#${prefix}${counter}` ).css( 'border', 'none' );
			}
			var row_id = $( '#card_payment_col_' + counter ).attr( 'value' );
			update_payment_reference( row_id, tmp );
			//alert( row_id );
		}
		cell_is_editing = 0;
	}

	function update_payment_reference( row_id, val ){
		var url = "ajax/db.php?bill_fl=update_payment_reference&row_id=" + row_id + "&value=" + val;
		var response = ajaxR( url );
		console.log( response );
	}

//lamadas asincronas
	function ajaxR(url){
	    if(window.ActiveXObject){       
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
         