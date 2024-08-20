
	function buildPayment(){
		var content = `<tr>
			<td><td>
			<td><td>
			<td><td>
		</tr>`;

	}

	function getCashPaymentForm(){
		var sale_id = $( '#id_venta' ).val();
		if( sale_id == 0 ){
			alert( "Es necesario que selecciones una nota de venta para continuar." );
			$( '#buscador' ).select();
			return false;
		}
		var amount = $( '#efectivo' ).val();
		if( amount <= 0 ){
			alert( "La cantidad del pago debe de ser mayor a cero." );
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
						onkeyup="validateNumberInput( this );calcula_cambio();"
					>
				<p class="text-start text-danger hidden" id="monto_cobro_emergente_alerta">Campo numérico*</p>
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
						onkeyup="validateNumberInput( this );calcula_cambio();"
					>
				<p class="text-start text-danger hidden" id="efectivo_recibido_alerta">Campo numérico*</p>
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

	function get_reverse_form(){
		var content = `<div class="row" style="padding:10px !important;">
		<div class="row">
			<div class="col-6">
				<button
					type="button"
					class="btn btn-warning"
					onclick="rePrintByOrderIdManualHelper();"
					style="border-radius:100% !important;"
				>
					<i class="">?</i>
				</button>
			</div>
			<div class="col-6 text-end">
				<button
					type="button"
					class="btn btn-light"
					onclick="close_emergent();"
				>
					<i class="text-danger">X</i>
				</button>
				<br><br>
			</div>
		</div>
			<input type="text" class="form-control" id="reverse_input" placeholder="RNN-Terminal">
			<p> </p>
			<p> </p>
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

	function rePrintByOrderIdManualHelper(){
		var content = `<div class="row">
				<div class="text-end"><button class="btn btn-light" onclick="close_emergent_2();">X</button></div>
				<h2 class="text-center">La función de reimpresión se genera ingresado el order id, cuya estructura es el valor <b class="text-success">RRN</b>-<b class="text-primary">Terminal</b> del boucher que se imprime al realizar un cobro</h2>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8 text-center">
						<img src="../../../../img/NetPay/boucher_netpay.png" width="40%">
						<h2><b class="text-success">240806114259</b>-<b class="text-primary">1494113054</b></h2>
					</div>
				</div>
			</div>`;
		$( '.emergent_content_2' ).html( content );
		$( '.emergent_2' ).css( 'display', 'block' );
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
		var sale_id = $( '#id_venta' ).val();
		if( sale_id == 0 ){
			alert( "Es necesario que selecciones una nota de venta para continuar." );
			$( '#buscador' ).select();
			return false;
		}
  		if( ! confirm( "Realmente deseas eliminar el pago?\nEsta accion va a recargar la pantalla, vuelve a escanear tu ticket de venta" ) ){//
  			return false;
  		}
  		//$( '#t' + counter ).val(0);
  		//$( '#card_payment_row_' + counter ).css('display', 'none');
		//recalcula();
		location.reload();
  	}
	
	function close_emergent(){
		$( '#stop' ).click();
		setTimeout( function (){
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
		}, 100 );
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}


//obtener lista de terminales sin integracion
	function getAfiliacionesForm(){
		var session_id = $( '#session_id' ).val();
		var url = "ajax/db.php?fl=obtenerListaAfiliaciones&session_id=" + session_id;
		var resp = ajaxR( url );
		var url = "ajax/db.php?fl=obtenerListaAfiliacionesActuales&session_id=" + session_id;
		var afiliaciones = ajaxR( url );
		var content = `<div>
			<div class="row">
				<div class="text-end">
					<button
						type="button"
						class="btn btn-light"
						onclick="close_emergent();"
					>
						<i class="text-danger">X</i>
					</button>
				</div>
				<div class="col-2"></div>
				<div class="col-8">
					<h2 class="text-center">Selecciona una terminal para agregar : </h2>
					<div class="row">
						<div class="col-9">
							<div class="input-group">
								${resp}
								<button
									class="btn btn-light"
								>	
									<i class="icon-ok-circled text-secondary" id="afiliation_validation_btn_icon"></i>
								</button>	
							</div>
							<br>
							<div class="input-group">
								<input type="password" id="afiliation_validation_input" onkeyup="validate_afiliation( event );" placeholder="escanea la terminal" class="form-control">
								<button
									onclick="validate_afiliation( 'intro' );"
									class="btn btn-warning icon-qrcode"
								>	
								</button>	
							</div>
						</div>
						<div class="col-3 text-center">	
							<button type="button" class="btn btn-info" onclick="show_afiliations_info();">?</span>
							<!--Cobro único :
							<p id="afiliacion_por_error" error="0" class="icon-toggle-off text-success fs-3 text-center" onclick="cambiar_check_error(this);"></p-->
						</div>
					</div>
					<br>
					<!--button class="btn btn-success form-control" onclick="agregarAfiliacionSesion();">
						<i class="icon-plus">Agregar</i>
					</button-->
					<br>
					<h1>Afiliaciones activas : </h1>
					${afiliaciones}
					<div class="text-center">
					<br>
					<div id=\"afiliations_changes_container\" class=\"no_visible\">
						<h2>Pide al encargado que ingrese su contraseña para continuar : </h2>
						<input type="password" id="mannager_password" class="form-control">
						<br>
						<button class="btn btn-success form-control" onclick="saveAfiliationsChanges();">
							<i class="icon-plus">Guardar Cambios</i>
						</button>
					</div>
				</div>
			</div>
			
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function validate_afiliation( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var combo_val = $( '#afiliacion_combo_tmp' ).val();
		if( combo_val == 0 ){
			alert( "Debes seleccionar una terminal válida para continuar." );
			$( '#afiliacion_combo_tmp' ).focus();
			return false;
		}else{
			combo_val = $( '#afiliacion_combo_tmp' ).find('option:selected').text();
		}
		var input_val = $( '#afiliation_validation_input' ).val();
		if( input_val == 0 ){
			alert( "El campo de comprobación de terminal no puede ir vacío." );
			$( '#afiliation_validation_input' ).focus();
			return false;
		}
		if( combo_val == input_val){
			$( '#afiliation_validation_btn_icon' ).removeClass( "text-secondary" );
			$( '#afiliation_validation_btn_icon' ).removeClass( "text-danger" );
			$( '#afiliation_validation_btn_icon' ).addClass( "text-success" );
			setTimeout( function(){
				agregarAfiliacionSesion();
			}, 500 );
		}else{
			alert( "El escaneo no coincide con la terminal seleccionada" );
			$( '#afiliation_validation_btn_icon' ).removeClass( "text-secondary" );
			$( '#afiliation_validation_btn_icon' ).addClass( "text-danger" );
			$( '#afiliation_validation_input' ).select();
		}
	}

	function show_afiliations_info(){
		var content = `<div class="row">
			<h3>Instrucciones para agregar terminales : </h3>
			<p>1.- Selecciona la terminal en el combo</p>
			<p>2.- Escanea la terminal en la caja de texto para confirmar</p>
			<p>3.- Pide al cajero que ingrese su contraseña.</p>
			<button
				type="button"
				class="btn btn-success"
				onclick="close_emergent_2();"
			>
				<i class="icon-ok-circled">Aceptar y cerrar</i>
			</button>
		</div>`;
		$( '.emergent_content_2' ).html( content );
		$( '.emergent_2' ).css( "display", "block" );
	}

	function checkTerminalError(obj){
		if( $( obj ).prop( 'checked' ) == true ){
			var tr = $(obj).parent().parent();	
			$( tr ).children('td').each( function(index){
				if( index == 2 ){
					$( this ).children( 'input' ).each( function(index2){
						$( this ).attr( 'disabled', true );
						$( this ).attr( 'checked', true );
					});
				}
			});
		}else if( $( obj ).prop( 'checked' ) == false ){
			var tr = $(obj).parent().parent();	
			$( tr ).children('td').each( function(index){
				if( index == 2 ){
					$( this ).children( 'input' ).each( function(index2){
						$( this ).attr( 'disabled', false );
						$( this ).attr( 'checked', false );
					});
				}
			});
		}
		$( '#afiliations_changes_container' ).removeClass( "no_visible" );
	}

//obtener lista de terminales con integracion
	function getTerminalesForm(){
		var session_id = $( '#session_id' ).val();
		var url = "ajax/db.php?fl=obtenerListaTerminales&session_id=" + session_id;
		var resp = ajaxR( url );
		var url = "ajax/db.php?fl=obtenerListaTerminalesActuales&session_id=" + session_id;
		var terminales = ajaxR( url );
		var content = `<div>
			<div class="row">
				<div class="text-end">
					<button
						type="button"
						class="btn btn-light"
						onclick="close_emergent();"
					>
						<i class="text-danger">X</i>
					</button>
				</div>
				<div class="col-2"></div>
				<div class="col-8">
					<h2 class="text-center">Selecciona una terminal para agregar : </h2>
					<div class="input-group">
						${resp}
					</div>
					<br>
					<h2>Pide al encargado que ingrese su contraseña para continuar : </h2>
					<input type="password" id="mannager_password" class="form-control">
					<br>
					<button class="btn btn-success form-control" onclick="agregarTerminalSesion();">
						<i class="icon-plus">Agregar</i>
					</button>
					<h1>Afiliaciones activas : </h1>
					${terminales}
				</div>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}
//debug de Json Oscar 2024-03-1
	function show_debug_json(){
		$(".emergent_content").html(`<button onclick="close_emergent();">X</button><br><pre><code class="json">${debug_json}</code></pre>`);
		$(".emergent").css("display","block");
		hljs.highlightAll();
	}
//creacion de fila en afiliaciones
	function build_afiliation_row( afiliation_id, afiliation_description ){
		var content = `<tr>
			<td  class="text-center no_visible"></td>
			<td class="text-center" afiliation_id="${afiliation_id}">
				${afiliation_description}
			</td>
			<td class="text-center">
				<input type="checkbox" checked>
			</td>
			<td class="text-center">
				<input type="checkbox">
			</td>
		</tr>`;
		return content;
		//onclick="checkTerminalSesion( this, {$row['afiliation_id']} );
		//onclick=\"checkTerminalError( this, {$row['afiliation_id']} );\"
	}