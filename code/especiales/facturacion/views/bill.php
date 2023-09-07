<div class="row text-center">
	<div class="col-1"></div>
	<div class="col-10">
	<label for="">Clientes(s)</label>
	<div class="input-group">
		<input type="text" id="costumer_seeker" class="form-control" placeholder="Escribe RFC de cliente"
		onkeyup="seek_costumer( event );">
		<button
			class="btn btn-success"
			onclick="seek_costumer( 'intro' );"
		>
			<i class="icon-search"></i>
		</button>
		<button
			class="btn btn-warning"
		>
			<i class="icon-plus"></i>
		</button>
	</div>
		<table class="table table-bordered">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th>RFC</th>
					<th>
						<i class="icon-cancel-circled"></i>
					</th>
				</tr>
			</thead>
			<tbody id="costumers_list">
				<!--tr><td class="text-start">Example</td></tr>
				<tr><td class="text-start">Example</td></tr>
				<tr><td class="text-start">Example</td></tr-->
			</tbody>
		</table>
	</div>
</div>
<hr>
<div class="row text-center">
	<div class="col-1"></div>
	<div class="col-10">
	<label for="">Ticket(s)</label>
	<div class="input-group">
		<input type="text" id="ticket_seeker" class="form-control" placeholder="Escanea / Captura folio de ticket"
			onkeyup="seek_ticket( event );"
		>
		<button
			class="btn btn-success"
			onclick="seek_ticket( 'intro' );"
		>
			<i class="icon-search"></i>
		</button>
	</div>
		<table class="table table-bordered table-striped">
			<thead><!--style="position : sticky; top :-5%;" class="btn-danger"-->
				<tr>
					<th class="text-center">FOLIO</th>
					<th class="text-center">MONTO</th>
					<th class="text-center">
						<i class="icon-cancel-circled"></i>
					</th>
				</tr>
			</thead>
			<tbody id="ticket_list">
				<!--tr><td class="text-start">Example</td></tr>
				<tr><td class="text-start">Example</td></tr>
				<tr><td class="text-start">Example</td></tr-->
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
			onclick="validate_payments_and_costumers();"
		>
			<i class="icon-right-big">Continuar</i>
		</button>
	</div>
</div>

<script type="text/javascript">
//buscador clientes
	function seek_costumer( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var rfc = $( '#costumer_seeker' ).val().trim();
		if( rfc == '' ){
			alert( "El buscador no puede ir vacío!" );
			$( '#costumer_seeker' ).focus();
			return false;
		}
	//verifica si el cliente existe en el grid
		var constumer_exists = 'no';
		$( '#costumers_list tr' ).each( function( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				//alert( $( this ).html().trim() );
				if( index2 == 0 && $( this ).html().trim() == rfc ){
					constumer_exists = 'exists';
				}
			});
		});
		if( constumer_exists == 'exists' ){
			alert( "El cliente '" + rfc + "' ya fue agregado anteriormente"  );
			$( '#costumer_seeker' ).select();
			return false;
		}
	//envia peticion por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			data : { bill_fl : 'seek_costumer', rfc : rfc },
			success : function( dat ){
				//alert(dat);
				var aux = dat.split( '|' );
				if( aux[0] == 'ok' ){
					addCostumer( aux[1] );
					$( '#costumer_seeker' ).val( '' );
				}else{
					var content = `<div class="row">
						<h3 class="text-danger">El RFC '${rfc}' no fue encontrado, Verifica y vuelve a intentar!</h3>
						<button
							class="btn btn-danger"
							onclick="close_emergent();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>`;
					$( '#costumer_seeker' ).val( '' );
					$( '.emergent_content' ).html( content );
					$( '.emergent' ).css( 'display', 'block' );
				}
			}
		});
	}

	function addCostumer( rfc ){
		var counter = $( '#costumers_list tr' ).length;
		var content = `<tr id="costumer_${counter}">
			<td>${rfc}</td>
			<td>
				<button
					type="button"
					class="btn btn-danger"
					onclick="deleteCostumer( '${counter}' );"
				>
					<i class="icon-cancel-circled"></i>
				</button>
			</td>
		</tr>`;
		$( '#costumers_list' ).append( content );
	}
	function deleteCostumer( counter ){
		if( confirm( "Quitar este cliente?" ) ){
			$( '#costumer_' + counter ).remove();
		}
	}
//buscador notas de venta
	function seek_ticket( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
		var ticket = $( '#ticket_seeker' ).val().trim();
		if( ticket == '' ){
			alert( "El buscador no puede ir vacío!" );
			$( '#ticket_seeker' ).focus();
			return false;
		}
	//verifica si el cliente existe en el grid
		var ticket_exists = 'no';
		$( '#ticket_list tr' ).each( function( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				//alert( $( this ).html().trim() );
				if( index2 == 0 && $( this ).html().trim() == ticket ){
					ticket_exists = 'exists';
				}
			});
		});
		if( ticket_exists == 'exists' ){
			alert( "El ticket '" + ticket + "' ya fue agregado anteriormente"  );
			$( '#ticket_seeker' ).select();
			return false;
		}
	//envia peticion por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/db.php',
			data : { bill_fl : 'seek_ticket', ticket : ticket },
			success : function( dat ){
				//alert(dat);
				var aux = dat.split( '|' );
				if( aux[0] == 'ok' ){
					addTicket( aux[1], aux[2] );
					$( '#ticket_seeker' ).val( '' );
				}else{//El ticket '${ticket}' no fue encontrado
					var content = `<div class="row">
						<h3 class="text-danger">${aux[1]}, <br> Verifica y vuelve a intentar!</h3>
						<button
							class="btn btn-danger"
							onclick="close_emergent();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
					</div>`;
					$( '#ticket_seeker' ).val( '' );
					$( '.emergent_content' ).html( content );
					$( '.emergent' ).css( 'display', 'block' );
				}
			}
		});
	}
	function addTicket( ticket, amount ){
		var counter = $( '#ticket_list tr' ).length;
		var content = `<tr id="ticket_${counter}">
			<td>${ticket}</td>
			<td>${amount}</td>
			<td>
				<button
					type="button"
					class="btn btn-danger"
					onclick="deleteTicket( '${counter}' );"
				>
					<i class="icon-cancel-circled"></i>
				</button>
			</td>
		</tr>`;
		$( '#ticket_list' ).append( content );
	}
	function deleteCostumer( counter ){
		if( confirm( "Quitar este cliente?" ) ){
			$( '#costumer_' + counter ).remove();
		}
	//elimina del session Storage
		
	}

	function deleteTicket( counter ){
		if( confirm( "Quitar este ticket?" ) ){
			$( '#ticket_' + counter ).remove();
		}
	//elimina del session Storage
		
	}
	function validate_payments_and_costumers(){
		var costumers_count, tickets_count, tickets = "", costumers = "";
		costumers_count = $( '#ticket_list tr' ).length;
		if( costumers_count <= 0 ){
			alert( "Debes seleccionar almenos un cliente para continuar!" );
			return false;
		}
		tickets_count = $( '#costumers_list tr' ).length;
		if( tickets_count <= 0 ){
			alert( "Debes seleccionar almenos un ticket para continuar!" );
			return false;
		}
		$( '#costumers_list tr' ).each( function( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				//alert( $( this ).html().trim() );
				if( index2 == 0 ){
					costumers += ( costumers == "" ? "" : "," );
					costumers += $( this ).html().trim();
				}
			});
		});
		$( '#ticket_list tr' ).each( function( index ){
			$( this ).children( 'td' ).each( function( index2 ){
				//alert( $( this ).html().trim() );
				if( index2 == 0 ){
					tickets += ( tickets == "" ? "" : "," );
					tickets += $( this ).html().trim();
				}
				if( index2 == 1 ){
					tickets += "~" + $( this ).html().trim();
				}
			});
		});
		//alert( tickets );
	//almacena los datos en sesion storage
		sessionStorage.setItem( 'tickets', tickets );
		sessionStorage.setItem( 'costumers', costumers );
		show_view( this, '.payments');
	}
//carga el sessionStorage
	if( sessionStorage.getItem( 'costumers' ) != null ){
		var tmp = sessionStorage.getItem( 'costumers' ).split( ',' );
		for( var i = 0; i < tmp.length; i++ ){
			addCostumer( tmp[i] );
		}
	}
	if( sessionStorage.getItem( 'tickets' ) != null ){
		var tmp = sessionStorage.getItem( 'tickets' ).split( ',' );
		for( var i = 0; i < tmp.length; i++ ){
			var aux = tmp[i].split( '~' );
			addTicket( aux[0], aux[1] );
		}
	}

</script>