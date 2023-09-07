	<script type="text/javascript">
	/* <![CDATA[ */
	var pedidor='';
	var id_aprt='';//extraemos el id del apartado
//implementacion de Oscar 29-12-2017
	
	var s_f='no',s_f_o=0;
<?php
	if(isset($sald_fav)){
?>
		s_f=<?php echo $sald_fav;?>;
		s_f_o=<?php echo $sald_fav;?>;
<?php
	}
?>
//fin de cambio

/*Implementación Oscar 12.11.2018 para redireccionar a index de ventas cuando sale el error de apartado pagado*/
	function recargar_por_error(){
		var pss_enc=$("#pass_enc").val();
		if(pss_enc.length<=0){//validamos que el campo de passsword no este vacío
			alert("El campo de contraseña no puede ir vacío!!!");
			$("#pass_enc_1").focus();
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/verificaPassEncargado.php',
			cache:false,
			data:{clave:pss_enc},
			success:function(dat){
				if(dat!='ok'){//si la contraseña es incorrecta
					alert("La contraseña del encargado es incorrecta!!!");
					$("#pass_enc").val('');
					$("#pass_enc_1").val('');
					$("#pass_enc_1").focus();
				}else{//si la contraseña es correcta
					location.href='index.php?';
				}
			}
		});
	}
/*Fin de cambio Oscar 12.11.2018*/

/*implementación Oscar 05.09.2018 para finalizar el proceso de cambios y devoluciones de apartado*/
		function imprime_cambio(){
			var abono=$("#monEfe").val();
		//checamos si tiene algun pago
			if(abono!=''&&abono!=0&&abono!=null){
				//alert();
			//enviamos el registro del pago
				$.ajax({
					async: false,
					type: 'GET',
					url: 'pedidosBusca.php?tipo=4&numdatos=1&id_pedido='+id_aprt+"&dato1[0]=NO&dato3[0]=1&dato5[0]="+abono
				}).done(function (data) {
					//alert(data);return false;
					var aux = data.split('|');
					if(aux[0]!='exito'){
						alert("Error!!!\n"+data);
						return false;
					}
			}).fail (function () {
				alert("Error!!\n"+data);
				return false;
			});
			}
		//reimprimimos el ticket
			setTimeout('reimprimeTicket('+id_aprt+')',1000);
		}

		function reimprimeTicket(id){
			//alert(id);
			var u1='';
			/*if($("#total_fc").val()==0){
			//reimpresión de ticket por devolución
				u1="ajax/ticket-php-head-reimpresion.php?id_ped="+id+"&reimpresion=0";
			}*/
			//if($("#total_fc").val()!=0){
			//si hay saldo a favor
			u1="index.php?scr=ticket&idp="+$("#id_de_apartado").val()+"&noImp=1&tv="+"&sald_fav_cl="+$("#total_fc").val()+"&favOrig=0&es_apartado=1";
			/*Implementación Oscar 07.03.2019 para actualizar status de devoluciones*/
			if(document.getElementById('id_pedido_original')){
				u1+="&id_pedido_original="+$("#id_pedido_original").val();
			}
			/*Fin de cambio Oscar 07.03.2019*/
			//}
	//fin de cambio
				$.ajax({
					async: false,
					type: 'GET',
					url: u1
				}).done (function (data){
					location.href="index.php?";
					return true;
				});
		}
		function cambiar_por_pagar(){
			id_aprt=$("#id_de_apartado").val();
        	if(id_aprt!=''&&id_aprt!=null){
	   		var subt=$("#total_orden").val().split("$");
	   		var abonado=$("#total_pagos").val().split("$");
	   		var pago=0;
	   		if($("#monEfe").val()!=''){
	   			pago=$("#monEfe").val();
	   		}
/*	   		alert(parseFloat(subt[1])+"\n\n"+parseFloat(abonado[1]));*/
	   		$("#monto").val(parseFloat(subt[1].replace(',',''))-parseFloat(abonado[1].replace(',',''))-parseFloat(pago));
	   	}
		}
/*fin de cambio 05.09.2018*/

	   function validaMontos(tip){
	       /*se deshabilitan los monto2 y obj2 porque ya no se usa tarjeta de crédito*/
	       var total=parseFloat(document.getElementById('totalOrd').value);
	       if(tip == 1)
	       {
	           obj1=document.getElementById('monEfe');
	           //obj2=document.getElementById('monTar');
	       }
	       else
	       {
	           obj1=document.getElementById('monTar');
              // obj2=document.getElementById('monEfe');
	       }
	       
	      // var monto2=isNaN(parseFloat(obj2.value))?0:parseFloat(obj2.value);// + monto2
	       var monto1=isNaN(parseFloat(obj1.value))?0:parseFloat(obj1.value);
	      //
	       if(monto1>total){
	           alert("El pago es mayor al total de la nota de venta");
	           obj1.value="0";
	           obj1.focus();
	           $('#es_apartado').prop("checked",false);
			/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
	           	$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
	        /*Fin de cambio Oscar 28.02.2019*/
	           return false;
	       }else if(monto1==total){
	           //obj2.value=total-monto1;
	           document.getElementById('monto').value=total-monto1;
	          // document.getElementById('es_apartado').checked=false;//este causaba el error
	       }
	    //
	       if(document.getElementById('es_apartado').checked==false){
	       		//alert('no es apartado');
	       		var monto_total=($("#total_orden").val()).replace("$","");
	       		monto_total=monto_total.replace(",","");
	       		$("#monto").val(parseFloat(monto_total)-parseFloat($("#monEfe").val()));
	       }
	       //else
	   }
	
	   function validarNumero(e,punto,id){
        var valor="";
        tecla_codigo = (document.all) ? e.keyCode : e.which;
        valor=document.getElementById(id).value;
        
        
        if(tecla_codigo==8 || tecla_codigo==0)return true;
        if (punto==1)
            patron =/[0-9\-.]/; 
        else
            patron =/[0-9\-]/;
          
        //validamos que no existan dos puntos o 2 -
        tecla_valor = String.fromCharCode(tecla_codigo);
        //46 es el valor de "."
        if (valor.split('.').length>1 && tecla_codigo==46)      
        {
            return false;
        }
        else if (valor.split('-').length>1 && tecla_codigo==45)     
        {
            //45 es el valor de "-"
            return false;
        }

        return patron.test(tecla_valor);
    
    }
	
	
	   function enterNext(obj, eve)
       {
           key=(eve.which) ? eve.which : eve.keyCode;
        
            //alert(key);
        
            if(key == 13)
            {
                if(obj.id == 'cliente_nombre')
                    document.getElementById('cliente_telefono').focus();
                
                if(obj.id == 'cliente_telefono')
                    document.getElementById('cliente_movil').focus();
                
                if(obj.id == 'cliente_movil')
                    document.getElementById('cliente_email').focus();
                    
                if(obj.id == 'cliente_email')
                    document.getElementById('guardar_apartado').focus();
                    
                if(obj.id == 'guardar_apartado')
                    document.getElementById('cancelar_apartado').focus();
                    
                if(obj.id == 'cancelar_apartado')
                    document.getElementById('cliente_nombre').focus();                    
            }
       }
	
		function eliminarItem (item) {
	    	  $(item).parents ("tr.move").first ().remove ();
	    	  calculaTotal ();
	    	  $("#monto").focus ();
	      }

		function calculaTotal () {
	    	// Sumar el total 
		    total = 0.0;
		    $("#listaPagos tr.move td.tabla_monto").each (function(index, value) {
			    total += parseFloat($(this).find ("input.montoVal").val ());
			});
		    $("#listaPagos tr.total td.tabla_total").html ("$ " + moneyFormat(total));
		    
		    var totPedido=parseFloat(document.getElementById('totPedido').value);
		    if(isNaN(totPedido))
		      totPedido=0;
		    
		    document.getElementById('monto').value=totPedido-total;
		}

		function agregarMonto ()
		{
			if ($("#monto").val ().match (/^-?\d+(?:\.\d*)?$/i))
			{
			    
			    /*alert('Test');
			    calculaTotal ();*/
				var existe = false;
				$("#listaPagos tr.move input.id_tipo").each (function(index, value)
				{
					if ($(this).val () == $("#tipo_pago").val ()) {
						var monto = parseFloat($(this).parents("tr.move").first ().find ("td.tabla_monto").html ().replace (/[\$\s,]/g, ""));
						monto = monto + parseFloat ($("#monto").val ());
						$(this).parents("tr.move").first ().find ("td.tabla_monto").html ("$ " + moneyFormat(monto));
						calculaTotal ();
						existe = true;
					}
				});

				if (!existe)
				{
					if (parseFloat($("#monto").val ()) > 0) $("<tr class=\"move\"><td class=\"tabla_tipo_pago\">" + $("#tipo_pago option:selected").text() + "</td><td class=\"tabla_monto\">$ " + moneyFormat($("#monto").val ()) + "</td><td><a href=\"javascript:void(0)\" onclick=\"eliminarItem(this)\" class=\"eliminar\"> <span>eliminar</span></a><input type=\"hidden\"  class=\"id_tipo\"  value=\""+ $("#tipo_pago").val() + "\" /></td></tr>").insertBefore ($("#listaPagos tr.total"));
					else alert ("Introduzca una cantidad positiva mayor a cero.");
					calculaTotal ();
				} 
				
				
			}
			else
			{
				alert ("El monto capturado es inválido");
			}

			//$("#monto").val ("");
			$("#monto").focus ();
		}
var regresar_efectivo_devolucion = 0;
	function confirmaDevolucion(){
		var pass = $( '#password_encargado' ).val();
		if ( pass.length <= 0 ){
			alert( "La contraseña de encargado es obligatoria!" );
			$( '#password_encargado' ).focus();
			return false;
		}
		$.ajax({
				async: false,
				type: 'POST',
				url: "ajax/verificaPassEncargado.php",
				data : { clave : pass },
				success : function ( dat ){
					if( dat == 'no' ){
						alert( "La contraseña del encargado es incorrecta!");
						$( '#password_encargado' ).select();
					}else{
						regresar_efectivo_devolucion = 1;
						$( '#cerrar' ).click();
						$("#emergente_cerrar").css("display","none");//hacemos visible la emergente
					}
				}
		});
	}

var emrge_aviso='';
		function cerrarVenta() {
		//implementacion de Oscar 29-12-2017
			//obtenemos saldo a favor del cliente
			if(document.getElementById("saldoAFavor")){
				s_f=document.getElementById("saldoAFavor").value;
				//alert(s_f);
			}

		//fin de cambio
			var retval = false; 
			var es_apartado = $("#es_apartado").prop ("checked") ? "1" : "0";
			var id_pedido = "<?php echo $id_pedido; ?>";

			var datos = "nitems=" + $("#listaPagos tr.move").length;
			$("#listaPagos tr.move").each (function(index, value) {
				datos += "&idt" + index + "=" + $(this).find ("input.id_tipo").val () + "&mon" + index + "=" + $(this).find ("input.montoVal").val ();
			});
			//alert(datos);
		//cambio de correo y facebook Oscar(01/11/2017)
			var correoC=document.getElementById('co').value;
			if(correoC==""){
				correoC="-";
			}
			var face=document.getElementById('fa').value;
			if(face==""){
				face="-";
			}
		//return false;
			var saldo_favor='';
			if($("#saldoFavorOriginal").val()>0){
				saldo_favor="&a_favor="+$("#saldoFavorOriginal").val();
			}
		/*implementacion Oscar 25.06.2019 para mandar los ids de las devoluciones*/
			var ids_dvs="";
			if(document.getElementById('ids_de_devoluciones')){
				ids_dvs+="&id_devoluciones="+$('#ids_de_devoluciones').val();
			}
		/*Fin de cambio Oscar 25.06.2019*/
			$.ajax({
				async: false,
				type: 'GET',
				url: "ajax/cerrarVenta.php?idp="+id_pedido+"&ap="+es_apartado+"&faceb="+face+"&cor="+correoC+"&"+datos+""+saldo_favor+ids_dvs
			}).done (function (data) {

	//alert(data);//con este alert mostramos error
				ax = data.split('|');
		/*implementación Oscar 2019 para código de barras*/
			$.post( "inc/img_codigo.php", {text:ax[3]});
		/*fin de cambio Oscar 2019*/
//alert('para');

		/**/
		/*Implementación Oscar 30.11.2018 para no dejar hacer más de una venta por dar click doble*/
				hay_proceso=1;//ocupamos el proceso
		/*Fin de cambio Oscar 30.11.2018*/
	//alert(data);
				retval = ax[0].match (/^ok$/i);
				pedidor=ax[1];
		/**/
				emrge_aviso=ax[2];
		/**/
			}).fail (function () {
				alert(data);
				retval = false;
			});

			return retval;
		}

		function changeHashOnLoad() {
			var base_href = location.href.match (/^([^\#]*)(?:\#.*)?$/i)[0];
	        location.href = base_href + "#";
	    /*Implementación de Oscar 30.05.2018 para limpiar las entradas de texto de facebook y correo*/
	    	$("#co").val("");
	    	$("#fa").val("");
	    /*Fin de cambio 30.05.2018*/
	        setTimeout("changeHashAgain()", "50");
	    }

	    function changeHashAgain() {          
	        location.href += "1";
	    }

	    var storedHash = window.location.hash;
	    setInterval(function () {
	        if (location.hash != storedHash) {
	            location.hash = storedHash;
	        }
	    }, 50);
	      
		$(document).ready(function() {

			// Bloquear evento goBack () 
			changeHashOnLoad();

			$("#monto").on ("keydown", function (e) {
		    	var key = e.charCode || e.keyCode || 0;
		    	if (key == 13) {
			    	agregarMonto();
			    	return false;
		    	}
		    });
			$("#monto").ForceNumericOnly();

			/*$("#agregar").on ("click", function () {
				agregarMonto();
			});*/

			/*$("#regresar").on ("click", function () {
				document.location.href = "index.php?scr=nueva&idp=<?php echo $id_pedido; ?>";
				return false;
			});*/

/*
function bloquea_pantalla(){
}*/

/*implementación Oscar 30.11.2018*/
	var hay_proceso=0;
/*Fin de cambio Oscar 30.11.2018*/
	$("#cerrar").on ("click", function () {
/*implementacion Oscar 04.12.2019 para verificar que haya sesion de caja antes de cerrar la venta*/
		$.ajax({
			async: false,
			type: 'GET',
			url: 'ajax/verificaSesionCajero.php'
			}).done (function (data){
				if(data!='ok'){
					alert('no hay sesion de caja; pida al cajero que inicie sesion de Caja para poder continuar con la venta!!!');
					return true;
				}
			});
/*Fin de cambio Oscar 04.12.2019*/
		if(hay_proceso==1){
			alert('Ya había presionado el botón de "Cerrar Venta"\nVerifique con el encargado si la venta ya fue generada!!!');
		/*Implementación Oscar 28.02.2019 para recargar ´página cuando sale el mensaje de que ya se había dado click*/	
			location.reload();
		/*Fin de cambio Oscar 28.02.2019*/
			return false;
		}

	/*Implementación de Oscar 03.03.2019 para evitar que se de doble click en guardar venta*/
		//ocultamos el botón de cerrar venta
			$("#fticket").css("display","none");
	/*Fin de cambio Oscar 03.03.2019*/
/*Cambio de Oscar 26.11.2018 para lanzar emergente y evitar que se den varios clicks*
		$("#emergente_bloqueo").css("display","block");*/
		hay_proceso=1;
/*fin de cambio Oscar 26.11.2018*/
/***pruebas***
	var es_aprt=0;
	if(document.getElementById("es_apartado").checked==true){
		es_aprt=1;
	}
	alert("Es apartado= "+es_aprt);
	
	/*implementación de Oscar 14.08.2018 para validar correo y facebook*/
		//extraemos los campos obligatorios
			var obl_corr=$("#obligatorio_pedir_correo").val();
			var obl_face=$("#obligatorio_pedir_facebook").val();
			//alert(obl_corr+"\n"+obl_face);
			//return false;

		/*implementación Oscar 2021 para solicitar contraseña cuando el cliente tiene saldo a favor por devolver */
		
			if( $( '#saldoFavorFinal' ).val() > 0 && regresar_efectivo_devolucion == 0 
				&& $( '#accion_devolucion' ).html().trim() != 'Cobrar   al cliente: $' 
				&& $( '#pedir_password_devolucion' ).val() == 1 )
			{
				var emergente_tmp = '<div style="text-align:center;">'
				'<p>Esta nota tiene diferencia a favor del cliente, para devolver el'
				+ ' efectivo pida al encargado su contraseña</p><input type="password" id="password_encargado" style="padding : 10px;"/> <br />'
				+ '<button type="button" onclick="confirmaDevolucion();" style="padding : 10px;">Continuar</button>'
				+ '</div>';
				$("#cont_emergente").html( emergente_tmp );//cargamos respuesta 
				$("#emergente_cerrar").css("display","block");//hacemos visible la emergente
				$("#fticket").css("display","block");
				hay_proceso=0;
				return false;
			}
		/*fin de cambio Oscar 2021*/
			if(obl_corr==1 && obl_face==1){//si pide alguno de los 2
				if($("#co").val().length<6 && $("#fa").val().length<5){
					alert("Debe de llenar el Correo/Instagram (mínimo 6 caracteres) o Whatsapp, de lo contrario escriba\"datos no proporcionados\"");
					$("#co").focus();
					$("#co").select();
				/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
					$("#emergente_bloqueo").css("display","none");
				/*fin de cambio Oscar 26.11.2018*/
					$("#fticket").css("display","block");
					hay_proceso=0;
					return false;
				}
			}else{//si solo pide uno
			//validamos correo
				if(obl_corr==1 && $("#co").val().length<10){
					alert("El correo/instagram debe contener almenos 10 caracteres o la frase \"datos no proporcionados\"");
					$("#co").focus();
					$("#co").select();
				/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
					$("#emergente_bloqueo").css("display","none");
				/*fin de cambio Oscar 26.11.2018*/
					$("#fticket").css("display","block");
					hay_proceso=0;
					return false
				}
			//validamos ek facebook
				if(obl_face==1 &&($("#fa").val().length<8)){
					alert("El Whatsapp debe contener almenos 8 caracteres o la frase \"datos no proporcionados\"");
					$("#fa").focus();
					$("#fa").select();
				/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
					$("#emergente_bloqueo").css("display","none");
				/*fin de cambio Oscar 26.11.2018*/
					$("#fticket").css("display","block");
					hay_proceso=0;
					return false
				}
			}//fin de else
	/*fin de cambio 14.08.2018*/
/*********************/
		$("#cerrar").prop ("disabled", true);
		if($("#monEfe").val()<monto_minimo){
			$("#cerrar").prop ("disabled", false);//volvemos a habiliatar el botón de cerrar venta
			alert("El monto mínimo para este aparado es de $"+monto_minimo);	
	
	/*Implementación de Oscar 03.03.2019 para evitar que se de doble click en guardar venta*/
		//ocultamos el botón de cerrar venta
			$("#fticket").css("display","block");
	/*Fin de cambio Oscar 03.03.2019*/
	
			$("#es_apartado").prop ("checked",false);
		/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
	       	$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
	    /*Fin de cambio Oscar 28.02.2019*/
			$("#monEfe").select();
		/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
			$("#emergente_bloqueo").css("display","none");
		/*fin de cambio Oscar 26.11.2018*/
			return false;
		}
		/*var verificarPago=document.getElementById('monTar').value;
		if(verificarPago=='0'){
			var continuar=confirm("¿Todo el pago es en efectivo???");
			if(continuar==false){
				document.getElementById('monEfe').select();
				$("#cerrar").prop ("disabled", false);
				return false;
			}
		}*/
	
		//alert(document.getElementById('es_apartado').checked);
	
	
		if (parseFloat(document.getElementById('monto').value) == 0) {
			// Venta 
	//alert('aqui es venta');
	//return false;
		//verificamos que tipo de venta es (normal o mayoreo)
			var tipoVenta=document.getElementById('tipo_venta').value;
	//implementacion de Oscar 29-12-2017
			if (cerrarVenta ()){
				//alert('pausa antes del ticket');
				var u1="index.php?scr=ticket&idp="+pedidor+"&noImp=1&tv="+tipoVenta;
				//var u2="index.php?scr=ticket&idp="+pedidor+"&noImp=2&tv="+tipoVenta;
			//agregamos saldos a favor
				if(s_f!='no'){
					//alert('El cliente tiene saldo a favor de $'+s_f+'!!!');
					u1+="&sald_fav_cl="+s_f+"&favOrig="+s_f_o;
					//u2+="&sald_fav_cl="+s_f+"&favOrig="+s_f_o;
				}
			/*Implementación Oscar 07.03.2019 para actualizar status de devoluciones*/
				if(document.getElementById('id_pedido_original')){
					u1+="&id_pedido_original="+$("#id_pedido_original").val();
				}
			/*Fin de cambio Oscar 07.03.2019*/
				//alert(u1);
	//fin de cambio
				$.ajax({
					async: false,
					type: 'GET',
					url: u1
				}).done (function (data){
//alert('here:'+data);
					return true;
				});

            /*                   
				$.ajax({
					async: false,
					type: 'GET',
					url: u2
				}).done (function (data) {
					return true;
				});
			*/		
		/*cambio Oscar 03.11.2018*/    	 
				if(emrge_aviso!=''){
					$("#cont_emergente").html(emrge_aviso);//cargamos respuesta 
					$("#emergente_cerrar").css("display","block");//hacemos visible la emergente
				}else{
					location.href = "index.php?scr=evaluation";  
				}      
		/*03.11.2018*/		
			}else{
				alert("Error al intentar el almacenado de los datos.\nVerifique conectividad e intente nuevamente.");
				$("#cerrar").prop ("disabled", false);
			}
		}//fin de if monto==0

		else if($("#es_apartado").prop("checked")){
		/*Implementación Oscar 07.03.2019 para actualizar status de devoluciones*/
			var url_1="";

			if(document.getElementById('id_pedido_original')){
				url_1="&id_pedido_original="+$("#id_pedido_original").val();
			}
		/*Fin de cambio Oscar 07.03.2019*/

			// Apartado 
			if (cerrarVenta ()){
				$.ajax({
					async: false,
					type: 'GET',
/*Implementación de Oscar 28.02.2019 para que aparezca la leyenda de venta de Mayoreo cuando se trata de un apartado sobre venta de tipo mayoreo
url: "index.php?scr=ticket&idp="+pedidor+"&noImp=1"*/
					url: "index.php?scr=ticket&idp="+pedidor+"&tv="+$("#tipo_venta").val()+url_1,
		/*Fin de cambio Oscar 28.02.2019*/
				}).done (function (data) {
					//alert(data);
					return true;
				});
            /*                   
				$.ajax({
					async: false,
					type: 'GET',
					url: "index.php?scr=ticket&idp="+pedidor+"&noImp=2"
				}).done (function (data) {
					//alert(data);
					return true;
				});
			*/		/*cambio Oscar 03.11.2018*/    	 
				if(emrge_aviso!=''){
					$("#cont_emergente").html(emrge_aviso);//cargamos respuesta 
					$("#emergente_cerrar").css("display","block");//hacemos visible la emergente
				}else{
					location.href = "index.php?scr=evaluation";  
				}      
		/*03.11.2018*/	    
					    
				//location.href = "index.php";
			}//termina proceso de cerra venta
			else{
			//si no se realiza el proceso de venta
				alert("Error al intentar el almacenado de los datos.\nVerifique conectividad e intente nuevamente.");
				$("#cerrar").prop ("disabled", false);
			}
		}//fin de else if($("#es_apartado").prop("checked")
		else{
			// Error 
			alert ("Imposible continuar.\n La venta no se puede cerrar ya que no es un apartado ni esta completamente saldada.");
		/*Implementación de Oscar 03.03.2019 para evitar que se de doble click en guardar venta*/
			//ocultamos el botón de cerrar venta
				$("#fticket").css("display","block");
		/*Fin de cambio Oscar 03.03.2019*/
			/*if($("#es_apartado").prop("checked")){var apart=1;}else{var apart=0;}
			alert("apartado="+apart);
			*/

			$("#cerrar").prop("disabled", false);
			return false;
		}
	});


			$("#cancelar").on ("click", function () {
				if (confirm ("¿Cancelar la venta?")) {
				    $.ajax({
				    	async: false,
		        	     type: 'GET',
				        url: 'ajax/cancelarVenta.php?idp=<?php echo $id_pedido; ?>'
				    }).done(function (source) {
					    document.location.href = "index.php";
					}).fail (function () {
						// No se pudo cancelar la venta 
						// alert ("Error al cancelar la venta."); 
					});
					return false;
				} else {
					return false;
				}
			});
			
			
			
var monto_tot=0;
var monto_minimo=0;

/*En esta función de activa el apartado*/
	$("#es_apartado").on ("click", function () {
		if ($("#es_apartado").prop("checked")){
			hay_proceso=0;//resetamos la variable que no deja cerrar venta dos veces
	/*implementación de Oscar 10.05.2018 para calcular mínimo dl pago para apartado*/
		//enviamos datos por ajax
			var aux=($("#total_orden").val()).split("$");//quitamos signo
			var aux1=aux[1].split(",");//quitamos comas
			aux="";
			for(var i=0;i<aux1.length;i++){
				aux+=aux1[i];
			}
			monto_tot=parseFloat(aux);
			$.ajax({
				type: 'post',
				url: 'ajax/verificaMontoMinimo.php',
				cache:false,
				data:{monto:monto_tot},
				success:function(dat){
					var arr_ax=dat.split("|");
					if(arr_ax[0]!='ok'){
						alert("Error:\n\n"+dat);
						return false;
					}else{
						monto_minimo=parseInt(arr_ax[1]);
						if($("#monEfe").val()<monto_minimo){
							alert("El monto mínimo para este apartado es de $"+monto_minimo);
							$("#es_apartado").prop("checked",false);
							$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");	
							$("#monEfe").select();	
					/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
						$("#emergente_bloqueo").css("display","none");
					/*fin de cambio Oscar 26.11.2018*/					
							return false;
						}
						//alert(monto_tot+"\nmínimo: "+monto_minimo);
						/*if(parseInt($("#monEfe").val())<monto_minimo){
							alert($("#monEfe").val()+"\n"+monto_minimo);
							alert("El monto mínimo para el apartado es de: $"+monto_minimo);
							$("#es_apartado").removeAttr("checked");
							$("#monEfe").select();
							return false;
						}*/
						//$(cant_minima_pago).html("El pago mínimo para generar este apartado es de: $"+monto_minimo);//cargamos mensaje de monto mínimo para el apartado
						var total=parseFloat(document.getElementById('totalOrd').value);
     					obj1=document.getElementById('monEfe');
						//obj2=document.getElementById('monTar'); deshabilitado (ya no se usa tarjeta)
           
						//var monto2=isNaN(parseFloat(obj2.value))?0:parseFloat(obj2.value);
						var monto1=isNaN(parseFloat(obj1.value))?0:parseFloat(obj1.value);
                              
						if((monto1) >= total){
							alert("Capturar el pago inical del cliente\nNo es posible realizar un apartado cuando la nota esta saldada");
							$("#es_apartado").prop("checked",false);
						/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
	        			   	$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
	        			/*Fin de cambio Oscar 28.02.2019*/
					/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
						$("#emergente_bloqueo").css("display","none");
					/*fin de cambio Oscar 26.11.2018*/
							return false;
						}
            			if((monto1) == 0){
							alert("No es posible realizar un apartado sin un pago previo");
							$("#es_apartado").prop ("checked", false);
						/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
	           				$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
	        			/*Fin de cambio Oscar 28.02.2019*/
					/*Cambio de Oscar 26.11.2018 para para lanzar emergente y evitar que se den varios clicks*/
						$("#emergente_bloqueo").css("display","none");
					/*fin de cambio Oscar 26.11.2018*/
							return false;
						}
						$("#divApartado").css ("display", "block");
						$("monEfe").val(0);
					}//fin de else
					}

			});
		}
	/*fin de cambio 10.05.2018*/
	});
	
/*Aqui guardamos datos del cliente*/
	$("#guardar_apartado").on ("click", function () {
		var es_ok = true;
		var falta_nom=0;
		var falta_tel=0;
		// Validar que el usuario haya capturado datos reales 
		if (!$("#cliente_nombre").val ().replace (/[\s\-\(\)]/g, "").length)  { falta_nom=1;}
		if (!$("#cliente_telefono").val ().replace (/[\s\-\(\)]/g, "").match (/\d{8,}/i))  { falta_tel++;}
		if (!$("#cliente_movil").val ().replace (/[\s\-\(\)]/g, "").match (/\d{8,}/i))  { falta_tel++; }
		if (!$("#cliente_email").val ().match (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/))  {  }
				
		if(falta_nom == 1 || falta_tel == 2)
			es_ok=false;
				
		if(!es_ok)
		{
			// Alguna validación detectó una falla con la captura 
			alert ("Introduzca adecuadamente los datos del cliente.\n Es requerido el nombre, y al menos un telefono");
			return false;
		}
		else
		{
			// Se permite el almacenado  					
			$.ajax({
            	async: false,
				type: 'GET',
				url: 'ajax/guardaCliente.php?idp=<?php echo $id_pedido; ?>&nombre='+$("#cliente_nombre").val ()+'&telefono='+$("#cliente_telefono").val ()+"&celular="+$("#cliente_movil").val ()+"&correo="+$("#cliente_email").val ()
			}).done(function (source){
				$("#divApartado").css ("display", "none");
				$("#es_apartado").prop ("checked", true);
		/*Implementación de Oscar para mínimo de apartados 10.05.2018*/
			if($("#monEfe").val()<monto_minimo){//asignamos pago mínimo al pago
				alert("El monto mínimo para este apartado es de $"+monto_minimo);
				$("#monEfe").val(monto_minimo);
				$("#es_apartado").prop ("checked", false);
			/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
	           	$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
	        /*Fin de cambio Oscar 28.02.2019*/
				$("#monEfe").select();//subrayamos el valor del pago mínimo
			}
		/*Fin de cambio*/
//alert(source);
			}).fail (function () {
				// No se pudo cancelar la venta 
				alert ("Error al guardar al cliente."); 
			});
			return false;	
		}
	});


	$("#cancelar_apartado").on ("click", function () {
		alert("Esta desactivando apartado");
		$("#divApartado").css ("display", "none");
		$("#es_apartado").prop("checked", false);
	/*Implementación Oscar 28.02.2019 para quitar color verde cuando se deshabilita el apartado*/
       	$('#label_apartado').prop("class","ui-checkbox-off ui-btn ui-btn-corner-all ui-fullsize ui-btn-icon-left ui-first-child ui-btn-up-c");
    /*Fin de cambio Oscar 28.02.2019*/
	});

	$("#monto").focus ();
});  
/* ]]> */
</script>