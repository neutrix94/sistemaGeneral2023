
var total_cobros=0,monto_real=0;
	function link(flag){
		if(flag==1 && confirm("Realmente desea regresar al panel?")==true){
			location.href='../../../../index.php?';
		}
		if(flag==2 && confirm("Realmente desea buscar otra venta para cobrar?")==true){
			location.reload();
		}
	}
	function valida_tca_opc(e,num){
		var tca=e.keyCode;
			//alert(num);
	//enter
		if(tca=='13'){
			$("#opc_"+num).click();return true;
		}
	//tecla arriba
		if(tca=='38'){
			if(num==1){
				$("#buscador").focus();return true;
			}else{
				$("#opc_"+parseInt(parseInt(num)-1)).focus();return true;
			}
		}
	//tecla abajo
		if(tca=='40'){
			$("#opc_"+parseInt(parseInt(num)+1)).focus();return true;	
		}
	}

	function marca(num){
		$("#opc_"+num).css("background","rgba(0,0,225,.5)");
	}

	function desmarca(num){
		$("#opc_"+num).css("background","white");
	}

	function busca(e){
		/*if(e.keyCode==40){
			$("#opc_1").focus();return true;
		}*/
		var txt=$("#buscador").val();
		if(txt.length<=2){
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
			return true;
		}
		//alert(e.keyCode);
		if( e.keyCode==13 || e.keyCode==40 || e == 'intro' ){
		//enviamos detos por ajax
			$.ajax({
				type:'post',
				url:'cobrosBd.php',
				cache:false,
				data:{flag:'buscador',valor:txt},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert(dat);return false;
					}else{
						$("#res_busc").html(aux[1]);
						$("#res_busc").css("display","block");
						if( e.keyCode==13 ){
							$("#opc_1").click();
						}else if(e.keyCode==40 || e == 'intro' ){
							$("#opc_1").focus();
							return true;
						}
					}
				}		
			});
		}
	}

	function carga_pedido(id,pagado){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'cobrosBd.php',
			cache:false,
			data:{flag:'carga_datos',valor:id},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					if( aux[0] == 'was_payed' ){//ya fue pagado totalmente
						alert( aux[1] );
						$( '#res_busc' ).html( '' );
						$( '#res_busc' ).css( 'display', 'none' );
						$( '#buscador' ).select();
						return false;
					}else{
						alert(dat);return false;
					}
				}else{
					var respuesta = JSON.parse( aux[1] );
					console.log( respuesta );//return '';
					$("#monto").val( respuesta.total_venta );
					$("#buscador").val( respuesta.folio_venta );
					$("#saldo_favor").val( respuesta.pagos_cobrados );
					respuesta.por_pagar = respuesta.total_venta - respuesta.pagos_cobrados;
					//$( '#monto_total' ).val( respuesta.por_pagar );
					//return null;
				//	var payment_ammount = ( aux[3]-aux[4] );
					if( respuesta.por_pagar < 0 ){
						$( '#efectivo' ).val(respuesta.por_pagar);
						$( '#efectivo' ).attr( 'readonly', true );
						$( '#payment_description' ).html( 'Devolver' );
						$( '#payment_description' ).css( 'color', 'red' );
						$( '#monto_total' ).css( 'color', 'red' );

						$( '#terminal_qr_input' ).attr( 'disabled', true );
						$( '.icon-qrcode' ).parent( 'button' ).css( 'display', 'none' );
						$( '#add_card_btn' ).css( 'display', 'none' );
						$( '#start_payments_btn' ).css( 'display', 'none' );
						$( '#card_qr_container' ).css( 'display', 'none' );
						$( '#cards_container' ).css( 'display', 'none' );
						$( '#transferencias_cheques_contenedor' ).css( 'display', 'none' );
						$( '#id_devolucion' ).val(1);
						$( '#finalizar_cobro_contenedor' ).css( 'display', 'none' );
						$( '#finalizar_cobro_devolucion_contenedor' ).css( 'display', 'block' );
						$( '#add_form_btn' ).css( 'display', 'none' );
					}else{
						$( '#efectivo' ).val( respuesta.por_pagar );
						$( '#efectivo' ).removeAttr( 'readonly' );
						$( '#payment_description' ).html( 'Cobrar' );
						$( '#payment_description' ).css( 'color', 'black' );
						$( '#monto_total' ).css( 'color', 'black' );
						$( '#id_devolucion' ).val(0);
						$( '#finalizar_cobro_contenedor' ).css( 'display', 'block' );
						$( '#finalizar_cobro_devolucion_contenedor' ).css( 'display', 'none' );
						$( '#add_form_btn' ).css( 'display', 'flex' );
					}

					$( '#monto_total' ).val( Math.abs( respuesta.por_pagar ) );
					//$("#monto_total").val( payment_ammount );
					//$("#efectivo").val(payment_ammount);//oscar 2023
					
					$("#buscador").attr('disabled','true');
					$("#res_busc").html('');
					$("#res_busc").css("display","none");
					$( '#seeker_btn' ).addClass( 'no_visible' );//oculta boton de buscador
					$( '#seeker_reset_btn' ).removeClass( 'no_visible' );//muestra boton de reseteo
					$("#id_venta").val( respuesta.id_venta );
					
					if( respuesta.por_pagar > 0 ){//pago
						//$("#t0").val(aux[3]-aux[4]);//oscar 2023
						$("#venta_pagada").val(pagado);
						total_cobros = respuesta.total_venta - respuesta.pagos_cobrados;
						//alert(aux[3]);
						monto_real = respuesta.pagos_cobrados - respuesta.total_venta;
					
					/*implementacion Oscar 2023/10/10 para recuperar los pagos anteriores de la nota de venta*/
						getHistoricPayment( respuesta.id_venta );
				/*fin de cambio Oscar 2023/10/10*/
					}else{//devolucion
						$( '#cards_container' ).css( 'display', 'none' );
					}
				}
			}		
		});
	}

	function getHistoricPayment( sale_id ){
		var url = "ajax/db.php?fl=getHistoricPayment&sale_id=" + sale_id; 
		var resp = ajaxR( url ).split( "|" );
		if( resp[0] != 'ok' ){
			alert( "Error : \n" + resp );
		}
		$( '#historic_payments' ).html( resp[1] );
	}

	function prevenir(e){
		var tca=e.keyCode;
		if(tca==38||tca==40){
			event.preventDefault();
		}
	}

	function valida_tca(obj,e,flag,num){
		const regex = /^[0-9]+$/; // Expresión regular que permite solo números
		var value = $(obj).val();
		if ( value.includes('.') ) {
			alert( "No se admiten numeros decimales!" );
    		value.replace('.', '');
    		$( obj ).val( value );
  		}
  		if (!regex.test(value)) {
    		$( obj ).val( value.replace(/[^\d]/g, '') );
  		}
  		/*var valorFormateado = parseFloat(value).toLocaleString('es', {
		    style: 'currency',
		    currency: 'MXN', // Cambia esto a tu moneda preferida si es diferente
		    minimumFractionDigits: 0,
		});*/
		//formatear numero
		value = value.replace(',','');
		var valorFormateado = Mascara('###,###,###,###', value);
		/*var aux_counter = 0;
		value = value.replace(',', '');
		for (var i = 0; i <= value.length - 1; i++) {
			valorFormateado += value[i];
			aux_counter ++;
			if( aux_counter == 3 && i < value.length - 2 ){
				valorFormateado += ',';
				aux_counter = 0;
			}
		};*/

		$( obj ).val( valorFormateado );
	//sacamos el id del objeto
	//sacamos el evento
		var tca=e.keyCode;
  		//alert( $(obj).val() );
	//campos de tarjetas (afiliaciones)
		if(flag==1){
			if(tca==40 && document.getElementById("t"+parseInt(num+1))){
				$("#t"+parseInt(num+1)).select();
			}
			
			if(tca==38 && document.getElementById("t"+parseInt(num-1))){
				//if(num==1)//alert();$("#buscador").focus();return true;}
				$("#t"+parseInt(num-1)).select();
			}

			if(num==$("#cantidad_tarjetas").val() && tca==40){//!document.getElementById("t"+parseInt(num-1)
				//alert(tca);
				$("#efectivo").select();
			}
		}
	//campo de efectivo
		if(flag==2){
			if(tca==40){$("#efectivo_recibido").focus();return true;}
			if(tca==38){$("#t"+$("#cantidad_tarjetas").val()).select();}
		}
	//campo de efectivo recibido
		if(flag==3){
			if(tca==38){$("#efectivo").select();}
		}
		recalcula();
		return true;
	}

	function recalcula(){
		total_cobros=0;
		var total_tarjetas=0,total_cheques=0,a_favor=0,total=0,monto_total=0;
		//var recibido=$("#efectivo_recibido").val();
	//sacamos los pagos por tarjeta
		var tope_tarjetas=$("#payments_list tr").length;
		for( var i=0;i <tope_tarjetas; i++ ){
			if($("#t"+i).val()!=''){
				total_tarjetas+=parseFloat($("#t"+i).val().replaceAll( ',', '' ));
			}else{
				$("#t"+i).val(0);
			}
		}
		total_cobros+=parseFloat(total_tarjetas);
	//	alert(total_tarjetas);
	//total de pagos con cheque/transferencias
		var tope_cheques=$("#no_cheque_transferencia").val();
		for(var i=1;i<=tope_cheques;i++){
			if($("#monto_"+i).html()!=''){
				total_cheques+=parseFloat($("#monto_"+i).html());
			}else{
				$("#monto_"+i).html(0);
			}
		}
		
		total_cobros=total_cobros+parseFloat(total_cheques);

	//extraemos e monto a favor
		a_favor=$("#saldo_favor").val();
		if(a_favor==''){a_favor=0;}
		a_favor=parseFloat(a_favor);
	//extraemos el monto total
		monto_total=$("#monto_total").val();
		if(monto_total==''){monto_total=0;}
		monto_total=parseFloat(monto_total);
		//alert(monto_total+'\n'+total_cobros);
		total=monto_total-(total_cobros+a_favor);
		//alert('total:'+total);
		$("#efectivo").val(total);
		total_cobros=total_cobros+total;
		calcula_cambio();
	}

	function calcula_cambio(){
		var total_tarjetas=0,total_cheques=0,total_cobros=0;
		var recibido=$("#efectivo_recibido").val().replaceAll( ',', '' );
		var devolver=$("#efectivo_devolver").val();
		if(recibido<=0){
			return true;
		}
		total_cobros+=parseFloat($("#efectivo").val());
		$("#efectivo_devolver").val(parseFloat(recibido-total_cobros));
	}
	
	/*Agregar cheque o transferencia*/
	function agrega_cheque_transferencia(){
	//obtenemos el valor de la caja
		var id_caja=$("#caja_o_cuenta").val();
		if(id_caja==0){
			alert("Elija una cuenta valida!!!");
			$("#caja_o_cuenta").focus();
			return false;
		}
		var txt_select=$('#caja_o_cuenta option:selected').text();
		//alert(txt_select);
	//obtenemos el monto
		var monto=$("#monto_cheque_transferencia").val();
		if(monto<=0){
			alert("El monto no puede ir vacío!!!");
			$("#monto_cheque_transferencia").focus();
			return false;
		}
	//obtenemos la referencia
		var observacion = `<p class="text-success" align="center">
			Ingresa la referencia del Cheque/Transferencia</p>
			<p align="center">
				<textarea id="referencia_cheque_transferencia"></textarea>
				<br><br>
				<button 
					class="btn btn-success" 
					onclick="agregar_fila( ${id_caja}, ${monto}, '${txt_select}')">
				Aceptar</button>
			</p>`;
		$(".emergent_content").html(observacion);
		$(".emergent").css("display","block");
		return true;
	}
var cont_cheques_transferencia=0;
	function agregar_fila(caja,monto,texto){
		var observacion=$("#referencia_cheque_transferencia").val();
		if(observacion.length<=0){
			alert("La referencia no puede ir vacía!!!");
			return false;
		}
		cont_cheques_transferencia+=1;
		var tabla=$("#listado_cheque_transferencia");
		var htmlTags='<tr>'+
        '<td id="caja_'+cont_cheques_transferencia+'" class="td_oculto">'+caja+'</td>'+
        '<td align="left">'+texto+'</td>'+
        '<td id="monto_'+cont_cheques_transferencia+'" align="center">'+monto+'</td>'+
        '<td id="referencia_'+cont_cheques_transferencia+'" align="left">'+observacion+'</td>'+
      '</tr>';
     	tabla.append(htmlTags);

    	$("#caja_o_cuenta option[value=0]").attr("selected",true);//reseteamos el combo de banco
    	$("#monto_cheque_transferencia").val(0);//reseteamos el valor del campo monto
     	$("#contenido_emergente").html("");//limpiamos la emergente
     	$("#emergente").css("display","none");//ocultamos la emergente
		var cont=parseInt(parseInt($("#no_cheque_transferencia").val())+1);
		$("#no_cheque_transferencia").val(cont);
		$("#listado_cheque_transferencia").css('display','block');
		recalcula();
	}

		function cobrar( amount_type ){
			var sale_id = $( '#id_venta' ).val();
		//verifica si hay cobro en efectivo a favor
			if( parseInt( $( '#efectivo' ).val() ) != 0 && $( '#efectivo' ).val().trim() != '' 
				&& parseInt( $( '#efectivo' ).val() ) < 0 ){
			//inserta pago en efectivo
				var url = "ajax/db.php?fl=insertCashPayment&ammount=" + parseInt( $( '#efectivo' ).val() );
				url += "&session_id=" + $( '#session_id' ).val();
				url += "&sale_id=" + $( '#id_venta' ).val();
				url += "&amount_type=" + amount_type;
				if( amount_type == -1 ){//parseInt( $( '#saldo_favor' ).val() ) < 0
					url += "&ammount_permission=1";
				}
				//alert( url ); return false;
				var resp = ajaxR( url ).split( '|' );
				if( resp[0] != 'ok' ){
					$( '.emergent_content' ).html( resp );
					$( '.emergent' ).css( 'display', 'block' );
					return false;
				}
			}
		//verifica que el total de pagos sea igual al total de venta
			var url = "ajax/db.php?fl=validatePayments&sale_id=" + sale_id;
			//alert( url );
			var resp = ajaxR( url ).split( '|' );
			if( resp[0] != 'ok' ){
				$( '.emergent_content' ).html( resp );
				$( '.emergent' ).css( 'display', 'block' );
				return false;
			}
			//alert( resp );

		//manda impresion del ticket
			//var url = "ticket_pagos.php?id_pedido=" + $( '#id_venta' ).val();
			//var resp = ajaxR( url );
			url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( '#id_venta' ).val();
			//alert( url );
			var resp = ajaxR( url );
			//alert( url );
			var id_corte = $( "#id_venta" ).val();
			$.ajax({
				type:'post',
				url:'cobrosBd.php',
				cache:false,
				data:{
					flag:'cobrar',
					id_venta:id_corte, 
					session_id : $( '#session_id' ).val() },
				success:function(dat){
					var aux=dat.split("|");
					//alert(dat);return false;
					location.reload();
				}
			});
			//location.reload();
			return false;
			var id=$("#id_venta").val();
			//alert(id);
			if(id==0){
				alert("Es necesario que seleccione un folio de pedido antes de continuar!!!");
				$("#buscador").focus();
			}
			if(total_cobros!=monto_real){
				alert("La suma de los pagos es diferente al monto total;\nverifique sus atos y vuelva a intentar!!!"+monto_real+"\n"+total_cobros);return false;
			}
			var cantidad_tarjetas=$("#cantidad_tarjetas").val();
			var cantidad_cheque=$("#no_cheque_transferencia").val();
			var id_corte=$("#id_venta").val();
			var tarjetas='',cheques='',efectivo=0,cambio=0,recibido=0;
		//no dejamos crrrar venta si el pago es menor a el monto total y la venta no es apartado
			/*if(total_cobros<$("#monto_total").val() && $("#venta_pagada").val()==1 ){
				alert("Esta nota debe ser saldada!!!");
				$("#efectivo").focus();
				return false;
			}*/
		//extraemos los valores de las tarjetas
			for(var i=1;i<=cantidad_tarjetas;i++){
				if($("#t"+i).val()!=0){
					tarjetas+=$("#tarjeta_"+i).val()+'~';//id de afiliacion
					tarjetas+=$("#t"+i).val()+'°';//monto
				}
			}
		//extraemos los valores de las tarjetas
			for(var i=1;i<=cantidad_cheque;i++){
				cheques+=$("#caja_"+i).html()+'~';//id de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'°';//monto
			}
//			alert(tarjetas+"\n"+cheques);
		//efectivo
			efectivo=$("#efectivo").val();

			cambio=$("#efectivo_devolver").val();
			recibido=$("#efectivo_recibido").val();
		//	alert(cambio);
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'cobrosBd.php',
				cache:false,
				data:{flag:'cobrar',efe:efectivo,camb:cambio,recib:recibido,tar:tarjetas,chq:cheques,
				id_venta:id_corte, session_id : $( '#session_id' ).val() },
				success:function(dat){
					var aux=dat.split("|");
					alert(dat);return false;
					location.reload();
				}
			});
		}
/*funcion para agregar pagos con tarjeta*/
	function addPaymetCard( user_id ){
		if( $( '#id_venta' ).val() == 0 ){
			alert( "Es necesario que selecciones una nota de venta para continuar" );
			$( '#buscador' ).select();
			return false;
		}
		var url = 'ajax/db.php?fl=getTerminals&user_id=' + user_id;
		url += '&counter=' + $( '#payments_list tr' ).length;
		//alert( url );
		if( $( '#payments_list' ).length == 1 ){
			$( '#t0' ).val( '' );
		}
		var resp = ajaxR( url );
		$( '#payments_list' ).append( resp );
	}
//habilitar pagos
	function enable_payments(){
		var amount_total = parseInt( $( '#monto_total' ).val() );
		var amount_sum = 0;
		var stop = false;
		//var stop = false;
	//verifica que todos lo pagos esten lleno y sumen la cantidad del monto total
		$( '#payments_list tr' ).each( function( index ){
			//$( '#payment_btn_' + index ).removeClass( 'no_visible' );
			if( parseInt( $( '#t' + index ).val() ) <= 0 ){
				stop = index;
				return false;
			}
			amount_sum += parseInt( $( '#t' + index ).val().replaceAll( ',', '' ) );
		});
		if( stop != false ){
			alert( "Hay cobros con tarjeta sin monto, verfica y vuelve a intentar!" );
			$( '#t' + stop ).select();
			return false;
		}
		if( $( '#efectivo' ).val() != '' ){
			amount_sum += parseInt( $( '#efectivo' ).val() );
		}
		if( amount_sum != amount_total ){
			alert( "La suma de los montos es diferente del total!" );
			return false;
		}
	//mustra los botones para enviar la peticion
		$( '#payments_list tr' ).each( function( index ){
			$( '#payment_btn_' + index ).removeClass( 'no_visible' );//muestra boton para cobrar
			$( '#t' + index ).attr( 'readonly', true );
			$( '#efectivo' ).attr( 'readonly', true );
		});
		$( '#start_payments_btn' ).addClass( 'no_visible' );
		$( '#add_card_btn' ).addClass( 'no_visible' );

	}
//buscador de la terminal por QR
	function seekTerminalByQr( e ){
		if( e.keyCode != 13 && e != 'intro' ){
			return false;
		}
	//obtiene el valor del qr de la terminal
		var qr_txt = $( '#terminal_qr_input' ).val().trim();
		if( qr_txt == '' ){
			alert( "El codigo qr no puede ir vacio!" );
			$( '#terminal_qr_input' ).focus();
			return false;
		}	
		var url = "ajax/db.php?fl=seekTerminalByQr&qr_txt=" + qr_txt;
		var resp = ajaxR( url ).split( '|' );
		if( resp[0] != 'ok' ){
			alert( "Error : \n" + resp );
		}else{
			$( '#terminal_qr_input' ).val( '' );
			var terminal = JSON.parse( resp[1] );
			buildEmergentAfiliationPayment( terminal );
		}
	}

	function buildEmergentAfiliationPayment(terminal){
		var content = `<div class="row" style="padding : 15px !important;">
			<div>
			<br>
			Tarjeta : 
			<select class="form-select" id="afiliation_select_tmp">
				<option value="${terminal.afiliation_id}">${terminal.afiliation_number}</option>
			</select>
			<div>
			<br>
				Monto :
				<input type="text" class="form-control" id="ammount_input_tmp">
			</div>
			<div>
			<br>
				Numero de autorizacion :
				<input type="text" class="form-control" id="authorization_input_tmp">
			</div>
			<div>
			<br>
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="setPaymentWhithouthIntegration();"
				>
					<i class="icon-floppy">Registrar cobro</i>
				</button>
			</div>
			<div>
			<br><br>
				<button
					type="button"
					class="btn btn-danger form-control"
					onclick="close_emergent();"
				>
					<i class="icon-cancel-circled">Cancelar y Salir</i>
				</button>
			</div>
		</div>`;
		//alert( content );
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setPaymentWhithouthIntegration(){
		var afiliation_id = $( '#afiliation_select_tmp' ).val();
		if( afiliation_id == '' || afiliation_id == 0 ){
			alert( "La afiliacion es invalida!" );
			$( '#afiliation_select_tmp' ).focus();
			return false;
		}
		var ammount = $( '#ammount_input_tmp' ).val();
		if( ammount <= 0 ){
			alert( "El monto debe de ser mayor a cero!" );
			$( '#ammount_input_tmp' ).focus();
			return false;
		}
		var authorization_number = $( '#authorization_input_tmp' ).val();
		if( authorization_number <= 0 ){
			alert( "El número de autorizacion no puede ir vacío!" );
			$( '#authorization_input_tmp' ).focus();
			return false;
		}
		var url = "ajax/db.php?fl=setPaymentWhithouthIntegration&afiliation_id=" + afiliation_id;
		url += "&ammount=" + ammount + "&authorization_number=" + authorization_number;
		url += "&sale_id=" + $( '#id_venta' ).val() + "&session_id=" + $( '#session_id' ).val();

		//alert( url ); return false;
		var resp = ajaxR( url ).split( '|' );
		if( resp[0] != 'ok' ){
			alert( "Error : \n" + resp );
		}else{
			//getHistoricPayment( $( '#id_venta' ).val() );
			carga_pedido( $( '#id_venta' ).val() );
			alert( resp[1] );
			close_emergent();
		}
	}


//lamadas asincronas
	function ajaxR( url ){
		if(window.ActiveXObject)
		{		
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