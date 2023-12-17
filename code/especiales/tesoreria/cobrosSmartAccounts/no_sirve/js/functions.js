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
	if(e.keyCode==13 || e.keyCode==40 || e == 'intro' ){
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
					if( e.keyCode==13 || e == 'intro' ){
						$("#opc_1").click();
					}else if(e.keyCode==40){
						$("#opc_1").focus();return true;
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
					alert(dat);return false;
				}else{
					$("#efectivo").val(aux[3]-aux[4]);
					$("#monto_total").val(aux[3]);
					$("#buscador").val(aux[2]);
					$("#id_venta").val(aux[1]);
					$("#saldo_favor").val(aux[4]);
					$("#venta_pagada").val(pagado);
					$("#buscador").attr('disabled','true');
					$("#res_busc").html('');
					$("#res_busc").css("display","none");
					total_cobros=aux[3]-aux[4];
					//alert(aux[3]);
					monto_real=aux[3]-aux[4];
				}
			}		
		});
	}

	function prevenir(e){
		var tca=e.keyCode;
		if(tca==38||tca==40){
			event.preventDefault();
		}
	}

	function valida_tca(obj,e,flag,num){
	//sacamos el id del objeto
	//sacamos el evento
		var tca=e.keyCode;
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
		var tope_tarjetas=$("#cantidad_tarjetas").val();
		for(var i=1;i<=tope_tarjetas;i++){
			if($("#t"+i).val()!=''){
				total_tarjetas+=parseFloat($("#t"+i).val());
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
		var recibido=$("#efectivo_recibido").val();
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
		var observacion='<p style="color:white;font-size:30px;" align="center">Ingrese la referencia del Cheque/Transferencia</p>';
		observacion+='<p align="center"><textarea id="referencia_cheque_transferencia"></textarea>';
		observacion+='<br><br><button class="boton" onclick="agregar_fila('+id_caja+','+monto+',\''+txt_select+'\')">Aceptar</button></p>';
		$("#contenido_emergente").html(observacion);
		$("#emergente").css("display","block");
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

		function cobrar(){
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
				data:{flag:'cobrar',efe:efectivo,camb:cambio,recib:recibido,tar:tarjetas,chq:cheques,id_venta:id_corte},
				success:function(dat){
					var aux=dat.split("|");
				//	alert(dat);
					location.reload();
				}
			});
		}

		function api_petition( counter ){
			var amount = $( '#t' + counter ).val();
			if( amount <= 0 || amount == '' ){
				alert( "El monto debe de ser mayor a cero, verfica y vuelve a intentar!" ); 
				$( '#t' + counter ).focus();
				$( '#t' + counter ).select();
				return false;
			}
			var content = `<div class="row">
				<div class="col-2"></div>
				<div class="col-8">
					<h2 class="text-center">Enviar peticion a Terminal</h2>
					<button
						class="btn btn-success form-control"
						onclick="send_api_petition( ${counter}, ${amount} );"
					>
						<i class="icon-ok-circle">Enviar</i>
					</button>
				</div>
			</div>`;
			$( '.emergent_content' ).html( content );
			$( '.emergent' ).css( 'display', 'block' );
		}

		function send_api_petition( counter, amount ){
			var payment = new Object();
			payment.terminal_id = $( '#tarjeta_' + counter ).val();
			payment.amount = amount;

		}