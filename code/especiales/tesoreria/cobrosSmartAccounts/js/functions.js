
var total_cobros=0,monto_real=0;
var respuesta = null;
var debug_json = "";

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
		var txt=$("#buscador").val().trim();
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
						if( aux[2] == 'sin_sesion' ){
							alert( "No hay sesion de cajero activa, inicia sesion para continuar!" );
							location.reload();
							return false;
						}else{
							$( '#session_id' ).val( aux[2] );//actualiza el id de la sesion
						}
						//alert( dat );
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
					respuesta = JSON.parse( aux[1] );
					console.log( respuesta );//return '';
					$("#monto").val( respuesta.total_real );
					$("#buscador").val( respuesta.folio_venta );
					$("#saldo_favor").val( parseFloat( respuesta.pagos_cobrados ) + parseFloat( respuesta.monto_saldo_a_favor ) );
					$( "#id_venta_origen" ).val( respuesta.id_venta_origen );
					//respuesta.por_pagar = respuesta.total_venta - respuesta.pagos_cobrados;
					//$( '#monto_total' ).val( respuesta.por_pagar );
					//return null;
				//	var payment_ammount = ( aux[3]-aux[4] );
				//if( respuesta.por_pagar < 0 ){
					if( respuesta.pagos_pendientes <= 0 ){
						$( '#efectivo' ).val(respuesta.pagos_pendientes);
						$( '#efectivo' ).attr( 'readonly', true );
						if( respuesta.pagos_pendientes == 0 ){
							$( '#payment_description' ).html( 'Sin Dif.' );
							$( '#payment_description' ).css( 'color', 'green' );
							$( '#monto_total' ).css( 'color', 'green' );
							$( '#finalizar_cobro_contenedor' ).css( 'display', 'block' );
							$( '#finalizar_cobro_devolucion_contenedor' ).css( 'display', 'none' );
						}else{
							$( '#payment_description' ).html( 'Devolver' );
							$( '#payment_description' ).css( 'color', 'red' );
							$( '#monto_total' ).css( 'color', 'red' );
							$( '#finalizar_cobro_contenedor' ).css( 'display', 'none' );
							$( '#finalizar_cobro_devolucion_contenedor' ).css( 'display', 'block' );

						}
						$( '#terminal_qr_input' ).attr( 'disabled', true );
						$( '.icon-qrcode' ).parent( 'button' ).css( 'display', 'none' );
						$( '#add_card_btn' ).css( 'display', 'none' );
						$( '#start_payments_btn' ).css( 'display', 'none' );
						$( '#card_qr_container' ).css( 'display', 'none' );
						$( '#cards_container' ).css( 'display', 'none' );
						$( '#transferencias_cheques_contenedor' ).css( 'display', 'none' );
						$( '#id_devolucion' ).val(1);
						$( '#add_form_btn' ).css( 'display', 'none' );
					}else{
						$( '#efectivo' ).val( respuesta.pagos_pendientes );
						$( '#payment_description' ).html( 'Cobrar' );
						$( '#payment_description' ).css( 'color', 'black' );
						$( '#monto_total' ).css( 'color', 'black' );
						$( '#id_devolucion' ).val(0);
						$( '#finalizar_cobro_contenedor' ).css( 'display', 'block' );
						$( '#finalizar_cobro_devolucion_contenedor' ).css( 'display', 'none' );
						$( '#add_form_btn' ).css( 'display', 'flex' );
					}

					$( '#monto_total' ).val( Math.abs( respuesta.pagos_pendientes ) );
					$( '#efectivo' ).attr( 'readonly', true );//solo informativo
					//$("#monto_total").val( payment_ammount );
					//$("#efectivo").val(payment_ammount);//oscar 2023
					
					$("#buscador").attr('disabled','true');
					$("#res_busc").html('');
					$("#res_busc").css("display","none");
					$( '#seeker_btn' ).addClass( 'no_visible' );//oculta boton de buscador
					$( '#seeker_reset_btn' ).removeClass( 'no_visible' );//muestra boton de reseteo
					$("#id_venta").val( respuesta.id_venta );
					
					//if( respuesta.por_pagar > 0 ){//pago
					if( respuesta.pagos_pendientes > 0 ){
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
/*Oscar 2024-02-08*/
aux[1] = aux[1].replaceAll(`\r\n\t\t\t\t\t`, `\n`);
aux[1] = aux[1].replaceAll(`\r\n\t\t\t\t`, `\n`);
aux[1] = aux[1].replaceAll(`\r\n\t\t\t`, `\n`);
aux[1] = aux[1].replaceAll(`\\t`, `    `);
aux[1] = aux[1].replaceAll(`\\r\\n`, `\n`);
aux[1] = aux[1].replaceAll(`,"`, `,\n"`);
aux[1] = aux[1].replaceAll(`,{`, `,\n{`);

debug_json = aux[1];

/*$(".emergent_content").html(`<button onclick="close_emergent();">X</button><br><pre><code class="json">${aux[1]}</code></pre>`);
$(".emergent").css("display","block");
hljs.highlightAll();
//hljs.initHighlightingOnLoad();
/**/
						getHistoricPayment( respuesta.id_venta );
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
		/*if ( value.includes('.') ) {
			alert( "No se admiten numeros decimales!" );
    		value.replace('.', '');
    		$( obj ).val( value );
  		}*/
  		if (!regex.test(value)) {
    		//$( obj ).val( value.replace(/[^\d]/g, '') );
  		}
  		/*var valorFormateado = parseFloat(value).toLocaleString('es', {
		    style: 'currency',
		    currency: 'MXN', // Cambia esto a tu moneda preferida si es diferente
		    minimumFractionDigits: 0,
		});*/
		//formatear numero
		value = value.replace(',','');
		//var valorFormateado = Mascara('###,###,###,###', value);
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

		$( obj ).val( value );
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
//console.log( "Total cobros : " + total_cobros );
	//extraemos e monto a favor
		a_favor=$("#saldo_favor").val();
		if(a_favor==''){a_favor=0;}
		a_favor=parseFloat(a_favor);
//console.log( "a favor :" + a_favor );
	//extraemos el monto total
		monto_total=$("#monto_total").val();
		if(monto_total==''){monto_total=0;}
		monto_total=parseFloat(monto_total);
//console.log( "monto total :" + monto_total );
		//alert(monto_total+'\n'+total_cobros);
		total=monto_total-(total_cobros);//+a_favor

//console.log( " total :" + monto_total );
		//alert('total:'+total);
		$("#efectivo").val(total);
		total_cobros=total_cobros+total;
		calcula_cambio();
	}

	function calcula_cambio(){
		var monto_pago = parseFloat( $( '#monto_cobro_emergente' ).val() );
		if( monto_pago > 0 ){
			var total_tarjetas=0,total_cheques=0,total_cobros=0;
			var recibido=$("#efectivo_recibido").val().replaceAll( ',', '' );
			var devolver=$("#efectivo_devolver").val();
			if(recibido<=0){
				return true;
			}
			//total_cobros += monto_pago;//;parseFloat($("#efectivo").val());
			$("#efectivo_devolver").val(parseFloat(recibido-monto_pago));//total_cobros
			//$( '#monto_cobro_emergente' ).val();
		}else if( monto_pago == 0 || monto_pago == '' || monto_pago < 0 ){
			alert( "El pago debe de ser mayor a cero!" );
			$( '#monto_cobro_emergente' ).focus();
			return false;
		}		
	}
	
	/*Agregar cheque o transferencia*/
	function agrega_cheque_transferencia(){
		var id_caja_cuenta = $( '#caja_o_cuenta' ).val();
		if( id_caja_cuenta <= 0 ){
			alert( "Es necesario que elijas una caja o cuenta correcta para continuar!" );
			$( '#tarjeta_0' ).focus();
			return false;
		}
		var amount = $( '#monto_cheque_transferencia' ).val();
		if( amount <= 0 ){
			alert( "El monto no puede ir vacio!" );
			return false;
		}
		var url = "ajax/db.php?fl=insertCashPayment&ammount=" + amount + "&tipo_pago=8";
		url += "&id_caja_cuenta=" + id_caja_cuenta;
		url += "&session_id=" + $( '#session_id' ).val();
		url += "&sale_id=" + $( '#id_venta' ).val();
		if( respuesta.monto_saldo_a_favor > parseFloat( respuesta.total_real ) ){//tomar saldo a
			url += "&pago_por_saldo_a_favor=" + parseFloat( respuesta.total_real );
		}
		url += "&id_venta_origen=" + $( "#id_venta_origen" ).val();
		if( respuesta.id_devolucion != null && respuesta.id_devolucion != 'null' && respuesta.id_devolucion != 0  ){
			url += "&id_devolucion_relacionada=" + respuesta.id_devolucion;
		}
		url += "&tipo_pago=8";
		//alert( url );return false;
		var resp = ajaxR( url );
		//alert( resp );
		if( resp == 'ok|' ){
			alert( "Pago agregado con exito!" );
			carga_pedido(  $( '#id_venta' ).val()  );
			$( '#caja_o_cuenta' ).val( '' );
		}else{
			alert( "Error : " + resp );
		}
		if( $( "#id_venta_origen" ).val() != '' && $( "#id_venta_origen" ).val() != 0 && $( "#id_venta_origen" ).val() != '0' && $( "#id_venta_origen" ).val() != null ){
			if( imprimir_tickets() == true ){
				location.reload();
			}
			/*url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( "#id_venta_origen" ).val();
			resp = ajaxR( url );
			console.log( resp );
			alert( resp );*/
		}

	}
var url_impresion_venta_origen = '';
var venta_origen_impresa = false;
var url_impresion_venta_actual = '';
var venta_actual_impresa = false;
	function imprimir_tickets(){
//alert( "imprimir_tickets" );
		//setTimeout( function(){
			if( parseInt( $( '#monto_total' ).val().trim() ) == 0 ){
				//alert( "condicion 1" );
//alert("tkt 1");
				url_impresion_venta_actual = "../../../../touch_desarrollo/ajax/impresionTicket.php?idp=" + $( '#id_venta' ).val();//index.php?scr=ticket&
				url_impresion_venta_actual += "&id_venta_origen=" + $( "#id_venta_origen" ).val();
				url_impresion_venta_actual += "&id_sesion_caja=" + $( '#session_id' ).val();
				url_impresion_venta_actual += "&path=../../../../";
//alert("origen : " + $( "#id_venta_origen" ).val());
				if( $( "#id_venta_origen" ).val() != '' && $( "#id_venta_origen" ).val() != 0 && $( "#id_venta_origen" ).val() != '0' && $( "#id_venta_origen" ).val() != null
					){//&& parseInt( $( '#monto_total' ).val().trim() ) == 0 
					//url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( "#id_venta_origen" ).val();
					url_impresion_venta_origen = "../../../../touch_desarrollo/ajax/impresionTicket.php?idp=" + $( "#id_venta_origen" ).val();
					url_impresion_venta_origen += "&reload_page=true";
					url_impresion_venta_origen += "&path=../../../../";//agregamos indicador para imprimir segundo ticket por si falla api				
					url_impresion_venta_origen += "&aditional_object_text=Ticket Origen ( Devolución )";

					//alert( url_impresion_venta_origen);
					url_impresion_venta_actual += "&reprint_initial_sale=true";
				}else{
					url_impresion_venta_actual += "&reload_page=true";
				}
				url_impresion_venta_actual += "&aditional_object_text=Ticket Actual";
//alert( url );
				var resp = ajaxR( url_impresion_venta_actual );
//alert(resp);
				if( resp.trim() != 'ok' ){
					$( '.emergent_content' ).html( resp );
					$( '.emergent' ).css( 'display', 'block' );
					return false;
				}
			}
			if( $( "#id_venta_origen" ).val() != '' && $( "#id_venta_origen" ).val() != 0 && $( "#id_venta_origen" ).val() != '0' && $( "#id_venta_origen" ).val() != null
			&& parseInt( $( '#monto_total' ).val().trim() ) == 0 ){//alert('here');
				
				//alert("tkt 2");
				
				//url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( "#id_venta_origen" ).val();
				//resp = ajaxR( url );
				//console.log( resp );
				//alert( resp );
				return imprimir_ticket_dependiente( true );
			}
			return true;
		//}, 500 );
	}
	function imprimir_ticket_dependiente( reload = false ){

		resp = ajaxR( url_impresion_venta_origen );
		if( resp != 'ok' ){
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		if(reload){
			location.reload();
		}else{
			return true;
		}
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
     	$(".emergent_content").html("");//limpiamos la emergente
     	$(".emergent").css("display","none");//ocultamos la emergente
		var cont=parseInt(parseInt($("#no_cheque_transferencia").val())+1);
		$("#no_cheque_transferencia").val(cont);
		$("#listado_cheque_transferencia").css('display','block');
		recalcula();
	}

		function cobrar( amount_type, permission = false ){
		//alert(`here : cobrar , ${amount_type}, ${permission}`);
			var sale_id = $( '#id_venta' ).val();
			var pago_efectivo =  parseFloat( $( '#efectivo' ).val() );
			if( pago_efectivo == '' || pago_efectivo == null || pago_efectivo == 'undefined' ||  pago_efectivo == undefined ){
				pago_efectivo = 0;
			}
			if( permission == false && pago_efectivo > 0 ){
				buildPaymentJustCash();
				return false;
			}
		//verifica que no haya cobros pendientes en tarjetas netPay
			var montos_smart_accounts = 0;
			$( '#payments_list tr' ).each( function( index ){
				if( ! isNaN( parseFloat( $( '#t' + index ).val() ) ) ){
					montos_smart_accounts += parseFloat( $( '#t' + index ).val() );
				}
			});

			if( montos_smart_accounts != 0 && montos_smart_accounts != 0.00 ){
				$( '.emergent_content' ).html( `<h2 class="text-center">No se puede finalizar el cobro porque hay pagos de netPay pendientes!</h2>
				<div class="text-center"><br>
					<button
						type="button"
						class="btn btn-warning"
						onclick="close_emergent();"
					>Cancelar y volver a intentar
					</button><br>
				</div>` );
				$( '.emergent' ).css( 'display', 'block' );
				return false;
			}
		//verifica si hay cobro en efectivo a favor
			//if( parseInt( $( '#efectivo' ).val() ) != 0 && $( '#efectivo' ).val().trim() != '' 
			//	&& parseInt( $( '#efectivo' ).val() ) < 0 ){
			//inserta pago en efectivo
				var url = "ajax/db.php?fl=insertCashPayment&ammount=" + pago_efectivo;
				url += "&session_id=" + $( '#session_id' ).val();
				url += "&sale_id=" + $( '#id_venta' ).val();
				url += "&amount_type=" + amount_type;
				if( amount_type == -1 ){//parseInt( $( '#saldo_favor' ).val() ) < 0
					url += "&ammount_permission=1";
				}
				if( respuesta.monto_saldo_a_favor > parseFloat( respuesta.total_real ) ){
					url += "&pago_por_saldo_a_favor=" + parseFloat( respuesta.total_real );
				}
				url += "&id_venta_origen=" + $( "#id_venta_origen" ).val();
				if( respuesta.id_devolucion != null && respuesta.id_devolucion != 'null' && respuesta.id_devolucion != 0  ){
					url += "&id_devolucion_relacionada=" + respuesta.id_devolucion;
				}
//alert( url ); return false;
				var resp = ajaxR( url ).split( '|' );
//console.log( resp );return false;
//alert( resp );
				if( resp[0] != 'ok' ){
//alert("entra 1");
					$( '.emergent_content' ).html( resp );
					$( '.emergent' ).css( 'display', 'block' );
					carga_pedido( $( '#id_venta' ).val() );
					if( imprimir_tickets() == true ){
						location.reload();
					}
					return false;
				}
			//}
		//verifica que el total de pagos sea igual al total de venta
			var url = "ajax/db.php?fl=validatePayments&sale_id=" + sale_id;
			//alert( url );
			var resp = ajaxR( url ).split( '|' );
			if( resp[0] != 'ok' ){
//alert("entra 2");
				$( '.emergent_content' ).html( resp );
				$( '.emergent' ).css( 'display', 'block' );
				carga_pedido( $( '#id_venta' ).val() );
				if( imprimir_tickets() == true ){
					location.reload();
				}
				return false;
			}
			//alert( resp );

		//manda impresion del ticket
			//var url = "ticket_pagos.php?id_pedido=" + $( '#id_venta' ).val();
			//var resp = ajaxR( url );

			//getHistoricPayment( $( '#id_venta' ).val() );
			/*carga_pedido( $( '#id_venta' ).val() );
			imprimir_tickets();//impresion de tickets*/
			
//alert( resp );
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
//alert("entra 3");
					var aux=dat.split("|");
					//alert(dat);return false;
					carga_pedido( $( '#id_venta' ).val() );
					setTimeout( function(){
						if( imprimir_tickets() == true ){//impresion de tickets
							location.reload();
						}
					}, 100);
				}
			});
			//location.reload();
			return false;
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
		url += '&session_id=' + $( '#session_id' ).val(); 
		//alert( url );
		if( $( '#payments_list' ).length == 1 ){
			$( '#t0' ).val( '' );
		}
		var resp = ajaxR( url );
		$( '#payments_list' ).append( resp );
	}
//habilitar pagos
	function enable_payments(){
		var amount_total = parseFloat( $( '#monto_total' ).val() );
		var amount_sum = 0;
		var stop = false;
	//verifica que todos lo pagos esten lleno y sumen la cantidad del monto total
		$( '#payments_list tr' ).each( function( index ){
			//$( '#payment_btn_' + index ).removeClass( 'no_visible' );
			if( parseFloat( $( '#t' + index ).val() ) <= 0 ){
				stop = index;
				return false;
			}
			amount_sum += parseFloat( $( '#t' + index ).val().replaceAll( ',', '' ) );
		});
		if( stop != false ){
			alert( "Hay cobros con tarjeta sin monto, verfica y vuelve a intentar!" );
			$( '#t' + stop ).select();
			return false;
		}
		if( $( '#efectivo' ).val() != '' ){
			amount_sum += parseFloat( $( '#efectivo' ).val() );
		}
		if( amount_sum != amount_total ){
			alert( "La suma de los montos es diferente del total!" );
			return false;
		}
	//muestra los botones para enviar la peticion
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
		url += "&session_id=" + $( '#session_id' ).val();
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
			<select class="form-select" id="afiliation_select_tmp" is_error="${terminal.is_per_error}">
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
	
	function addCashPayment( amount ){	
		//inserta pago en efectivo
			amount = $( '#monto_cobro_emergente' ).val();
			if( amount <= 0 ){
				alert( "El monto del pago debe de ser mayor a cero!" );
				$( '#monto_cobro_emergente' ).focus();
				return false;
			}
			if( amount > parseFloat( $( '#monto_pendiente_emergente' ).val() ) ){
				alert( "El monto del pago no puede ser mayor al monto pendiente de pagar" );
				$( '#monto_cobro_emergente' ).select();
				return false;
			}
			var url = "ajax/db.php?fl=insertCashPayment&ammount=" + amount;
			url += "&session_id=" + $( '#session_id' ).val();
			url += "&sale_id=" + $( '#id_venta' ).val();
			if( respuesta.monto_saldo_a_favor > parseFloat( respuesta.total_real ) ){//tomar saldo a
				url += "&pago_por_saldo_a_favor=" + parseFloat( respuesta.total_real );
			}
			url += "&id_venta_origen=" + $( "#id_venta_origen" ).val();
			if( respuesta.id_devolucion != null && respuesta.id_devolucion != 'null' && respuesta.id_devolucion != 0  ){
				url += "&id_devolucion_relacionada=" + respuesta.id_devolucion;
			}

//alert( url );
			var resp = ajaxR( url ).split( '|' );
//alert( "Respuesta : " + resp );
			if( resp[0] != 'ok' ){
				$( '.emergent_content' ).html( resp[1] );
				$( '.emergent' ).css( 'display', 'block' );
				return false;
			}else{
				carga_pedido( $( '#id_venta' ).val() );
				//getHistoricPayment( $( '#id_venta' ).val() );
				if( $( "#id_venta_origen" ).val() != '' && $( "#id_venta_origen" ).val() != 0 && $( "#id_venta_origen" ).val() != '0' && $( "#id_venta_origen" ).val() != null 
				&& parseInt( $( '#monto_total' ).val().trim() ) == 0 ){
					setTimeout( function(){
						if( imprimir_tickets() == true ){
							location.reload();
						}
						/*url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( "#id_venta_origen" ).val();
						resp = ajaxR( url );
						console.log( resp );*/
						alert( resp );
					}, 1000);
				}

			//recarga vista de cobros
				$( '#efectivo' ).val( '' );
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
		url += "&is_per_error=" + $( '#afiliation_select_tmp' ).attr( "is_error" );
		if( respuesta.monto_saldo_a_favor > parseFloat( respuesta.total_real ) ){//tomar saldo a
			url += "&pago_por_saldo_a_favor=" + parseFloat( respuesta.total_real );
		}
		url += "&id_venta_origen=" + $( "#id_venta_origen" ).val();

		if( respuesta.id_devolucion != null && respuesta.id_devolucion != 'null' && respuesta.id_devolucion != 0  ){
			url += "&id_devolucion_relacionada=" + respuesta.id_devolucion;
		}

//alert( url );
		var resp = ajaxR( url ).split( '|' );
		if( resp[0] != 'ok' ){
			alert( "Error : \n" + resp );
		}else{
			
			alert( resp[1] );
			carga_pedido( $( '#id_venta' ).val() );
			if( $( "#id_venta_origen" ).val() != '' && $( "#id_venta_origen" ).val() != 0 && $( "#id_venta_origen" ).val() != '0' && $( "#id_venta_origen" ).val() != null ){
				/*url = "../../../../touch_desarrollo/index.php?scr=ticket&idp=" + $( "#id_venta_origen" ).val();
				resp = ajaxR( url );
				console.log( resp );
				alert( resp );*/
				if( imprimir_tickets() == true ){
					location.reload();
				}
			}
			close_emergent();
		}
	}

	function delete_payment_saved( payment_id, sale_id ){
		if( !confirm( "Realmente deseas eliminar el pago?" ) ){
			return false;
		}
		var url = "ajax/db.php?fl=delete_payment_saved&payment_id=" + payment_id;
		var resp = ajaxR( url );
		if( resp != 'ok' ){
			alert( "Error al eliminar el pago : " + resp );
		}else{
			alert( "El pago fue eliminado exitosamente!" );
			carga_pedido( sale_id );
		}
	}

//agregar afiliacion 
	function agregarAfiliacionSesion(){
	//saca lista de afiliaciones actuales
		var curent_afiliations_tmp = new Array();
		$( '#afiliations_table_body tr' ).each( function ( index ) {
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 == 1 ){
					curent_afiliations_tmp.push( $( this ).attr( 'afiliation_id' ) );
				}
			});
		});
		//console.log( curent_afiliations_tmp );
		var afiliation_id = $( '#afiliacion_combo_tmp' ).val();
		if( afiliation_id == '' || afiliation_id == null || afiliation_id == 0 || afiliation_id == '0' ){
			alert( "Pimero elije una afiliación válida!" );
			return false;
		}
		if( curent_afiliations_tmp.indexOf( afiliation_id ) != -1 ){
			alert( "Esta terminal ya existe y no puede ser agregada nuevamente!" );
			return false;
		}
		var afiliation_description = $( '#afiliacion_combo_tmp option:selected' ).text();
		var new_row = build_afiliation_row( afiliation_id, afiliation_description );

		$( '#afiliations_table_body' ).append( new_row );
		$( '#afiliations_changes_container' ).removeClass( 'no_visible' );
	}

	function saveAfiliationsChanges(){
	//recolecta los valores
		var afiliations = '';
		$( '#afiliations_table_body tr' ).each( function ( index ) {
			if( index > 0 ){
				afiliations += '|~|';
			}
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 == 0 ){
					afiliations += $( this ).html().trim() + '|';//id_afiliacion
				}else if( index2 == 1 ){
					afiliations += $( this ).attr( 'afiliation_id' ) + '|';//id 
				}else if ( index2 == 2 || index2 == 3 ){
					var tmp ='0';
					$( this ).children( 'input' ).each( function( index3 ){
						if( index3 == 0 ){
							if( $( this ).prop( "checked" ) == true ){
								tmp = '1';
							}
							afiliations += tmp + '|';
						}
					});
				}
			});
		});
		//alert( afiliations );return false;
		var password = $( '#mannager_password' ).val();
		var afiliation_id = $( '#afiliacion_combo_tmp' ).val();
		if( password.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			return false;
		}
		var session_id = $( '#session_id' ).val();
		var url = "ajax/db.php?fl=guardaAfiliacionSesion&session_id=" + session_id;
		url += "&mannager_password=" + password;
		url += "&afiliations=" + afiliations;
		//alert( url );return false;
		var resp = ajaxR( url );
		if( resp != 'ok' ){
			alert( resp );
		}else{
			alert( "Cambios guardados exitosamente." );
			getAfiliacionesForm();//recarga emergente de afiliaciones
		}
	}

	function checkAfiliationSesion( obj, session_terminal_id ){
		var enabled = 1;
		//var session_id = $( '#session_id' ).val();
		if( ! $(obj).prop('checked') ){
			enabled = 0;
		}	
		var url = 'ajax/db.php?fl=checkAfiliationSesion&enabled=' + enabled + '&session_terminal_id=' + session_terminal_id;
		alert( url );
		alert( ajaxR( url ) );
		return false;
	}
//agregar afiliacion 
	function agregarTerminalSesion(){
		var session_id = $( '#session_id' ).val();
		var password = $( '#mannager_password' ).val();
		var terminal_id = $( '#terminal_combo_tmp' ).val();
		if( terminal_id == '' || terminal_id == null || terminal_id == 0 || terminal_id == '0' ){
			alert( "Pimero elije una afiliación válida!" );
			return false;
		}	
		if( password.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			return false;
		}
		var url = "ajax/db.php?fl=agregarTerminalSesion&session_id=" + session_id;
		url += "&mannager_password=" + password;
		url += "&id_terminal=" + terminal_id;
		var resp = ajaxR( url );
		if( resp != 'ok' ){
			alert( resp );
		}else{
			alert( "Terminal agregada exitosamente." );
			getTerminalesForm();//recarga emergente de afiliaciones
		}
	}

	function checkTerminalSesion( obj, session_terminal_id ){
		/*var enabled = 1;
		//var session_id = $( '#session_id' ).val();
		if( ! $(obj).prop('checked') ){
			enabled = 0;
		}	
		var url = 'ajax/db.php?fl=checkTerminalSesion&enabled=' + enabled + '&session_terminal_id=' + session_terminal_id;
		//alert( url );
		alert( ajaxR( url ) );*/
		$( '#afiliations_changes_container' ).removeClass( "no_visible" );
		return false;
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