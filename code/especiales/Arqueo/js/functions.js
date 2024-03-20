
	function salir(){
		location.href="../../../index.php";
	}
	function activaBusqueda(){
	//obtenemos el valor del combo
		if($("#f2").val()!=-1){
			location.href="arqueo.php?aWRfY29ydGU="+btoa($("#f2").val());//btoa("id_corte")+"="+btoa(id)id_corte="+$("#f2").val()
		}else{
			location.href="arqueo.php?";
		}
	}
	
	var cambio_caja = 0;
	function generaTicket(){
		if( $('#cambio_caja').val() <= 0 ){
			alert("El monto de cambio en caja es obligatorio!");
			$('#cambio_caja').select();
			return false;
		}
		cambio_caja = $('#cambio_caja').val();
		llenaReporte();
		setTimeout(function(){generaTicket_1();},'1000');
	}
	function generaTicket_1(){
		var cantidad_tarjetas=$("#no_tarjetas").val();
		var cantidad_cheque=$("#no_cheque_transferencia").val();
		var id_corte=$("#id_sesion").val();
		var tarjetas='',cheques='',password='',fecha_ultimo_corte='';
		var ingreso_efe=0;
		fecha_ultimo_corte=$("#fecha_del_corte").val();
		$('#cambio_caja').val( cambio_caja );
	//obtenemos la contraseña
		password=$("#password1").val();
		if(password==''){
			alert("La contraseña no puede ir vacía");
			$("#password").focus();
			return true;
			
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_tarjetas;i++){
			if($("#tarjeta_"+i).val()!=0){
				tarjetas+=$("#tarjeta_"+i).val()+'~';//id de afiliacion
				
		/*Implementacion Oscar 04.12.2019 para evitar el error cuando las tarjetas estan vacias*/
				if($("#t"+i).val()!=''){
					tarjetas+=$("#t"+i).val()+'°';//monto
				}else{
					tarjetas+='0°';//monto					
				}
		/*Fin de cambio Oscar 04.12.2019*/					
		
			}
		}
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_cheque;i++){
				cheques+=$("#caja_"+i).html()+'~';//id de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'°';//monto
		}
		ingreso_efe=$('#ingreso_final_efectivo').html();
		var ingresos='';

		ingresos+=$("#ing_int").html()+'|';

		if(document.getElementById('efe_ext')){
    		ingresos+=$('#efe_ext').html();//6
    	}else{
    		ingresos+='0';//6
    	}
		var gast="";
	//extraemos los gastos
		var obj1=document.getElementById('gastos');
        var trs=obj1.getElementsByTagName('tr');
        var hora=document.getElementById('horaFinal').value;
        for(i=1;i<trs.length-2;i++){
            var tds=trs[i+1].getElementsByTagName('td');
            if(tds[3]){
            	gast+=tds[0].innerHTML+"|"+tds[2].innerHTML+"|"+tds[3].innerHTML+"~";
        	}
        }

		$.ajax({
			type:'post',
			url:'ajax/imprimeTicket.php',
			cache:false,
			data:{
					tar : tarjetas,
					cheq_trans : cheques,
					corte : id_corte,
					pss : password,
					fcha_corte : fecha_ultimo_corte,
					efectivo : ingreso_efe,
					arr_ing : ingresos,
					gastos : gast,
					subt_in_efe : $("#subtotal_ing_efect").html(),
					cambio : cambio_caja
			},
			success:function(dat){
				if(dat=='ok'){
					alert('Caja cerrada e Impresion generada exitosamente.');
					location.href='arqueo.php?';
				}else{
					alert('Ocurrió un problema al imprimir, actualice la pantalla y vuelva a intentar!!!\n'+dat);
					//location.reload();
				}
			}
		});
	}

	function llenaReporte(flag){
		if(flag==1){
			var msg_info='<p align="center" style="color:white;font-size:50px;" >';
			msg_info+='<br><br>Registre todos sus gastos pendientes antes de generar el corte!';
			msg_info+='<br><br><button type="button" style="padding:20px;font-size:30px;" ';
			msg_info+='onclick="document.getElementById(\'emergente\').style.display=\'none\';"><b>ACEPTAR</b></button>';		
			msg_info+='</p>';
		
			$("#contenido_emergente").html(msg_info);
			$("#emergente").css("display","block");
		}
		//pending_sales_validation();
		
		var cantidad_tarjetas=$("#no_tarjetas").val();
		var cantidad_cheque=$("#no_cheque_transferencia").val();
		var id_corte=$("#id_sesion").val();
		var tarjetas='',cheques='',password='';
	//sacamos la fecha del corte 
		var fecha_ultimo_corte=$("#fecha_del_corte").val();
		var hora_inicio=$("#hora_de_inicio").val();
		var hora_final=$("#hora_de_cierre").val();

		password=$("#password1").val();
		if(password==''){
			alert("La contraseña no puede ir vacía");
			$("#password").focus();
			return true;	
		}
		var cards_counter = 1;
		$( '#tarjetas tr' ).each( function ( index ){
			if( $( this ).hasClass( 'is_card_row' ) ){
				tarjetas += $("#tarjeta_"+ cards_counter ).val()+'~';//id de afiliacion
				if( $("#t" + cards_counter ).val()!='' ){
					tarjetas += $("#t"+ cards_counter ).val()+'~';//monto
				}else{ 
					tarjetas += '0~';//monto					
				}
				tarjetas += $( '#card_description_' + cards_counter ).html() + "°";
				cards_counter ++;
			}
		});
		//alert( tarjetas );
	//extraemos los valores de las tarjetas
		/*deshabilitado por Oscar 2023/10/24
			for(var i=1;i<=8;i++){
			if($("#tarjeta_"+i).val()!=0){
				tarjetas+=$("#tarjeta_"+i).val()+'~';//id de afiliacion
				if($("#t"+i).val()!=''){
					tarjetas+=$("#t"+i).val()+'°';//monto
				}else{
					tarjetas+='0°';//monto					
				}
			}
		}*/
	//extraemos los valores de las tarjetas
		for(var i=1;i<=cantidad_cheque;i++){
			if(document.getElementById('fila_ch_'+i)){
				cheques+=$("#caja_"+i).html()+'~';//id de banco
				cheques+=$("#monto_"+i).html()+'~';//monto
				cheques+=$("#referencia_"+i).html()+'°';//monto
			}
		}
		
		var tmp_txt='<p align="center" style=";font-size:30px;"><b>Cargando...</b><br><img src="../../../img/img_casadelasluces/load.gif"></p>';
		$("#reporte").html(tmp_txt);
	
	//	alert(hora_inicio+"\n"+hora_final);
	//generamos el reporte
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',//'ajax/detalle.php'
			cache:false,
			data:{tar:tarjetas,cheq_trans:cheques,corte:id_corte,pss:password,fcha_corte:fecha_ultimo_corte,inicio:hora_inicio,fin:hora_final},
			success:function(dat){
				$("#reporte").html(dat);
				if(dat=='Hay devoluciones pendientes de terminar<br>Terminelas y vuelva a intentar!!!'){
					window.open("../../general/listados.php?tabla=ZWNfZGV2b2x1Y2lvbg==&no_tabla=MQ==");
				}
		//alert(fF);
			}
		});
	}
	function calendario(objeto){
    	Calendar.setup({
    	    inputField     :    objeto.id,
    	    ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
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
		var observacion='<button class="btn_cerrar" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button>';
		observacion+='<p style="color:white;font-size:30px;">Ingrese la referencia del Cheque/Transferencia</p>';
		observacion+='<textarea id="referencia_cheque_transferencia"></textarea>';
		observacion+='<br><br><button class="boton" onclick="agregar_fila('+id_caja+','+monto+',\''+txt_select+'\')">Aceptar</button>';
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
		var htmlTags='<tr id="fila_ch_'+cont_cheques_transferencia+'">'+
        '<td id="caja_'+cont_cheques_transferencia+'" class="td_oculto">'+caja+'</td>'+
        '<td align="left">'+texto+'</td>'+
        '<td id="monto_'+cont_cheques_transferencia+'" align="center">'+monto+'</td>'+
        '<td id="referencia_'+cont_cheques_transferencia+'" align="left">'+observacion+'</td>'+
      	'<td><button onclick="eliminaFila('+cont_cheques_transferencia+');" class="btn_eliminar">x</button></td>'+
      '</tr>';
     	tabla.append(htmlTags);

    	$("#caja_o_cuenta option[value=0]").attr("selected",true);//reseteamos el combo de banco
    	$("#monto_cheque_transferencia").val(0);//reseteamos el valor del campo monto
     	$("#contenido_emergente").html("");//limpiamos la emergente
     	$("#emergente").css("display","none");//ocultamos la emergente
		var cont=parseInt(parseInt($("#no_cheque_transferencia").val())+1);
		$("#no_cheque_transferencia").val(cont);
	//si ya existe el botón porque el reporte ya fue genrado recargamos informacion
		if (document.getElementById('btn_cierra_caja')) {llenaReporte();}
	}

	function eliminaFila(num){
		if(confirm("Realmente desea eliminar este cheque o transferencias?")==true){
			$("#fila_ch_"+num).remove();
			llenaReporte();
		}
	}

		function valida_tca_opc(e,num){
		var tca=e.keyCode;
	//enter
		if(tca=='13'){
			$("#opc_"+num).click();return true;
		}
	//tecla arriba
		if(tca=='38'){
			if(num==1){
				$("#buscador").select();return true;
			}else{
				$("#opc_"+parseInt(parseInt(num)-1)).focus();return true;
			}
		}
	//tecla abajo
		if(tca=='40'){
			$("#opc_"+parseInt(parseInt(num)+1)).focus();return true;	
		}
	}

/*funciones del buscador*/
	function busca(e){
		if(e.keyCode==40){
			$("#opc_1").focus();return true;
		}
		var txt=$("#buscador").val();
		if(txt.length<=2){
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
			return true;
		}
	//enviamos detos por ajax
		$.ajax({
			type:'post',
			url:'ajax/detalle.php',
			cache:false,
			data:{flag:'buscador',valor:txt},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert(dat);return false;
				}else{
					$("#res_busc").html(aux[1]);
					$("#res_busc").css("display","block");
				}
			}		
		});
	}

	function marca(num){
		$("#opc_"+num).css("background","rgba(0,0,225,.5)");
	}

	function desmarca(num){
		$("#opc_"+num).css("background","white");
	}

	function cambia_valor(obj,id_elemento){
		$("#"+id_elemento).html($(obj).val());
		llenaReporte();
	}

	function pedir_cerrar_corte(){
		var contenido='<p style="font-size:40px;color:white">';
		contenido+='<b>Este corte fue generado porque las ventas se extendieron a otro dia despues de la fecha de inicio de sesion de caja.<br>';
		contenido+='Es necesario finalizar este corte de caja para poder cuadrar los cortes!!!</p><br><br>';
		contenido+='<p align="center"><button style="padding:15px;border-radius:20px;font-size:30px;" onclick="cierra_emergente();">Aceptar</button></p>'
		$("#contenido_emergente").html(contenido);
		$("#emergente").css("display","block");
	}
	
	function cierra_emergente(){
		$("#contenido_emergente").html('');
		$("#emergente").css("display","none");	
	}

	function pending_sales_validation(){
	//envia detos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/pending_validation_tkt.php',
			cache : false,
			data : { flag : 'seek_pending_to_validate' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					alert(dat);return false;
				}else{
					if( aux[1] != '' && aux[1] != null ){
						$( "#contenido_emergente" ).html( aux[1] );
						$( "#emergente" ).css( "display", "block" );
					}	
				}
			}		
		});
	}

	function print_pending_ticket(){
		cierra_emergente();
		$.ajax({
			type : 'post',
			url : 'ajax/pending_validation_tkt.php',
			cache : false,
			
			data : { flag : 'print_pending_to_validate', absolute_path : '../../../' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					$( "#contenido_emergente" ).html( aux );
					$( "#emergente" ).css( "display", "block" );
					//alert(dat);
					return false;
				}else{
					$( "#contenido_emergente" ).html( '' );
					$( "#emergente" ).css( "display", "none" );
					//$( "#contenido_emergente" ).append( `` );
				}
			}		
		});
	}
	
	function close_emergent(){
		$( '#contenido_emergente' ).html( '' );
		$( '#emergente' ).css( 'display', 'none' );
	}