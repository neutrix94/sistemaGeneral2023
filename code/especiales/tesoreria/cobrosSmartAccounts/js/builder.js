
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
		var pendiente = $( '#efectivo' ).val();
//onkeyup="valida_tca(this,event,3);calcula_cambio();"
		var content = `<div class="row" style="padding : 15px;">
			<div class="col-6">
				<label class="text-primary">Monto de pago: </label>
				<input 
						type="text" 
						id="monto_cobro_emergente" 
						class="form-control" 
						onkeydown="prevenir(event);" 
						onkeyup="calcula_cambio();"
					>
			</div>
			<div class="col-6">
				<label class="text-primary">Pendiente: </label>
				<input 
						type="text" 
						id="monto_pendiente_emergente" 
						class="form-control" 
						value="${pendiente}"
						readonly
					>
				<br>
			</div>
			<div class="col-6">
				<label class="text-primary">Monto que entrega cliente : </label>
				<input 
						type="text" 
						id="efectivo_recibido" 
						class="form-control" 
						onkeydown="prevenir(event);" 
						onkeyup="calcula_cambio();"
					>
			</div>
			<div class="col-6">
				<label class="text-primary">Monto de cambio : </label>
				<input type="text" id="efectivo_devolver" class="form-control" readonly>
			</div>

			<div class="col-3"></div>
			<div class="col-6">
				<br>
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
				<br>
				<br>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function addCashPayment( amount ){	
	//inserta pago en efectivo
		amount = $( '#monto_cobro_emergente' ).val();
		if( amount <= 0 ){
			alert( "El monto del pago debe de ser mayor a cero!" );
			$( '#monto_cobro_emergente' ).focus();
			return false;
		}
		if( amount > parseInt( $( '#monto_pendiente_emergente' ).val() ) ){
			alert( "El monto del pago no puede ser mayor al monto pendiente de pagar" );
			$( '#monto_cobro_emergente' ).select();
			return false;
		}
		/*if( $( '#efectivo_recibido' ).val() == '' || $( '#efectivo_recibido' ).val() <= 0 ){
			alert( "El monto entregado por el cliente no puede ser menor o igual a cero!" );
			$( '#efectivo_recibido' ).focus();
			return false;
		}*/

		var url = "ajax/db.php?fl=insertCashPayment&ammount=" + amount;
		url += "&session_id=" + $( '#session_id' ).val();
		url += "&sale_id=" + $( '#id_venta' ).val();
		//alert( url ); return false;
		var resp = ajaxR( url ).split( '|' );
		alert( "Respuesta : " + resp );
		if( resp[0] != 'ok' ){
			//alert( "Error al insertar el pago en Efectivo: " + resp );
			$( '.emergent_content' ).html( resp[1] );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}else{
			$( '#efectivo' ).val( '' );
			
			carga_pedido( $( '#id_venta' ).val() );
			getHistoricPayment( $( '#id_venta' ).val() );//recarga vista de cobros
			var content = `<div class="text-center">
				<h2 class="text-success">Pago registrado exitosamente</h2>
				<button
					type="button"
					class="btn btn-success"
					onclick="close_emergent();"
				>
					<i class="icon-ok-circle">Aceptar</i>
				</button>
			</div>`;
			$( '.emergent_content' ).html( content );
			$( '.emergent' ).css( 'display', 'block' );
		}
		/*var content = `<tr>
			<td>Efectivo</td>
			<td>Efectivo</td>
			<td>${amount}</td>
		</tr>`;
		$( '#payments_list' ).append( content );
		close_emergent();*/
	}

	function get_reverse_form(){
		var content = `<div class="row">
			<input type="text" class="form-control" id="reverse_input">
			<!--button
				type="button"
				class="btn btn-danger"
			>
				<i class="icon-warning">Cancelar</i>
			</button-->
			<button
				type="button"
				class="btn btn-info"
				onclick="rePrintByOrderIdManual();"
			>
				<i class="icon-print-6">Reimprimir</i>
			</button>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function show_reprint_view(){
		var content = `<div class="row" style="padding : 20px;">
					      <div class="col-1 text-end"></div>
					      <div class="col-10 text-end">
					        <h2 class="text-center fs-3">Reimpresion</h2>
					      </div>
					      <div class="col-1 text-end">
					        <button
					          type="button"
					          class="btn btn-light"
					          style="background-color : transparent; border : none;"
					          onclick="close_emergent();"
					        >
					          <i class="text-danger fs-7">X</i>
					        </button>
					      </div>
					      </div>
					      <div class="input-group" style="padding : 5px;">
					        <input type="text" id="tickets_seeker" class="form-control" onkeyup="seek_ticket( event );"  style="box-shadow : 1px 1px 1px rgba( 0,20,0,.5 );" placeholder="Buscar...">
					        <button class="icon-search btn btn-primary" onclick="seek_ticket( 'intro' );"></button>
					      </div>
					      <div style="height : 400px;max-height : 400px; overflow : scroll; position : sticky;" class="list_container">
					        <table class="table table-bordered table-striped tickets_table">
					          <thead style="position : sticky; top : 0; background-color : white;">
					            <tr>
					              <th class="text-center">Vendedor</th>
					              <th class="text-center">Folio</th>
					              <th class="text-center text-primary">Monto</th>
					              <th class="text-center text-success">Pagado</th>
					              <th class="text-center">Cliente</th>
					              <th class="text-center">Fecha</th>
					              <th class="text-center">Imprimir</th>
					            </tr>
					          </thead>
					          <tbody id="tickets_results">
					        	${getLastTickets()}
					          </tbody>
					        </table>
					    </div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function getLastTickets( txt = null ){
    //$(  )
	    var url = "ajax/db.php?fl=getTicketsToReprint";
	    if( txt != null ){
	      url += "&key=" + txt;
	    }

	    var resp = ajaxR( url );
	    //alert( resp );
	    return resp;
	}
	
	function seek_ticket( e ){
	    if( e.keyCode != 13 && e != 'intro' ){
	      return false;
	    }
	  //limpia 
	    $( '#tickets_results' ).empty();
	  //mustra resultados
	    var results = getLastTickets( $( '#tickets_seeker' ).val().trim() );
	    $( '#tickets_results' ).append( results );
	}

	function print_ticket( sale_id ){
		var url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + sale_id;
	    //var url = "index.php?scr=talon_pago&idp=" + sale_id + "&noImp=1";
	    var resp = ajaxR( url );
	    $( '.emergent_content' ).html( `<div class="row text-center">
	        <div class="col-1"></div>
	        <div class="col-10 text-center">
	          <br><br><br>
	          <br><br><br>
	          <h3 class="fs-1 text-success text-center">Impresion Generada exitosamente</h3>
	          <br><br>
	          <button
	            type="button"
	            class="btn btn-success"
	            onclick="close_emergent();"
	          >
	            <i class="icon-ok-circled fs-2">Aceptar</i>
	          </button>
	        </div>
	      </div>` );
    //alert( "Impresion Generada" );
  	}

  	function buildPaymentJustCash(){
  		var content = `<div class="row" style="padding : 20px;">
  			<h2 class="text-center text-primary">Estas seguro de cobrar todo el pago en efectivo?</h2>
  			<div class="col-6 text-center">
  				<button 
  					type="button"
  					class="btn btn-success"
  					onclick="cobrar(1, true);"
  				>
  					<i class="icon-ok-circled">Confirmar</i>
  				</button>
  			</div>
  			<div class="col-6 text-center">
  				<button 
  					type="button"
  					class="btn btn-danger"
  					onclick="close_emergent();"
  				>
  					<i class="icon-ok-cancel">Cancelar</i>
  				</button>
  			</div>
  		</div>`;
  		$( '.emergent_content' ).html( content );
  		$( '.emergent' ).css( 'display', 'block' );
  	}

  	function removePaymentTmp( counter ){
  		if( ! confirm( "Realmente deseas eliminar el pago?" ) ){
  			return false;
  		}
  		$( '#card_payment_row_' + counter ).remove();
  	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}