<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />
<!--Modificacaión Oscar 11.11.2018 para implementar librería para convertir entrada de texto en password-->
	<script type="text/javascript" src="../../js/passteriscoByNeutrix.js"></script>
	<link rel="stylesheet" type="text/css" href="../../css/bootstrap/css/bootstrap.css">
<!--Fin de cambio Oscar 11.11.2018-->
{include file="_header.tpl" pagetitle="$contentheader"}

<div id="campos">  
<div id="titulo">4.1 Control de asistencia</div>
<br><br>
<div class="row">
		<table border="0" class="table" style="position:absolute;right:38%;top:400px;width:40%;left:30%;"><!-- style="position:absolute;right:38%;top:400px;width:40%;left:30%;" -->
         	<tr style="background:green;">
	        	<td style="padding:20px;"><b style="font-size:40px;color:white;">Entrada:</b></td>
          		<td>
          			<input type="text" name="sucur" id="clave_as" placeholder="Usuario" onkeyup="buscaEmpleado(this.form,1,event,1);">	
	          	</td>				
	          	<td align="center">
					<input name="button" type="button" id="button" style="padding:10px;border-radius:10px;" value="Aceptar" onclick="buscaEmpleado(this.form,2)"/>
				</td>				
          	</tr>
          	<tr style="background:red;">
	        	<td style="padding:20px;"><b style="font-size:40px;color:white;">Salida:</b></td>
          		<td "padding:20px;">
          			<input type="text" name="sucur" id="clave_as_1" placeholder="Usuario" onkeyup="buscaEmpleado(this.form,1,event,3);">	
	          	</td>				
	          	<td "padding:20px;">
					<input name="button" type="button" style="padding:10px;border-radius:10px;" id="button" value="Aceptar" onclick="buscaEmpleado(this.form,4)"/>
				</td>				
          	</tr>
          	</tr>
        {if $ver_log_login eq '1'}  	
          	<tr>
          		<td colspan="4" align="center"><br><input type="checkbox" id="incluye_login" style="transform : scale( 1.3 );" checked>  Incluir busqueda por Login</td>
          	</tr>
        {/if}
    	</table> 	
</div>

                	<p style="color:#c1272d;font-weight:bold;top:75%;position:absolute;left:40%;width:20%" align="center" id="textoErr"></p>
<div id="bg_seccion">
	<!--<div class="name_module" align="center">
		<p>Control de asistencia</p>		    
	</div>-->
		
</div>
<!--implementación de Oscar 27.02.2018-->
	<div style="width:100%;height:130%;position:absolute;background:rgba(0,0,0,.8);top:0;display:none;z-index:1500;" id="emer_asist">
	<center>
		<p style="top:150px;position:relative;font-size:25px;color:white;" id="instr_asist"></p>
		<table width="40%" border="1" style="position:relative;top:170px;background:white;" id="contenido_horas">
			<tr>
				<th colspan="3"><p id="nom_em">erlge</p></th>
			</tr>
			<tr>
				<td align="center">
					Fecha 
				</td>
				<td>
					Hora Entrada
				</td>
				<td>
					Hora Salida
				</td>
			</tr>
			<tr>
				<td align="center">
					<input type="text" id="fec_as" disabled>
					<input type="hidden" id="id_reg" value="0">
				</td>
				<td>
					<input type="text" id="hor_ent_as" disabled>
				</td>
				<td>
					<input type="text" id="hor_sal_as">
				</td>
			</tr>
			<tr>
				<td colspan="3" width="100%">
					<p align="center">
					<br><br>
						<input type="text" id="pss_encar_suc1" onkeydown="cambiar(this,event,'pss_encar_suc');" style="width:50%;">
						<input type="hidden" id="pss_encar_suc" value="">
						<br><br>
						<input type="button" value="Cambiar Salida" onclick="cambia_salida();" style="padding:10px;"><br>
					</p>
				</td>
			</tr>
		</table>
	</center>
	</div>
<!--Fin de implementacion 27.02.2018-->
<script>
	{literal}
	function guarda_cambios_salidas(){
		var password=0;
		var pass_enc="";
		var tope=$("#total_salidas_pendientes").val();
		var por_guardar="";
	//recorremos las salidas
		if($("#col_sal_pend_4_1").html()=='00:00:00'){
			alert("No puede registrar una entrada si tiene salidas pendientes!!!");
			setTimeout(function(){$("#col_sal_pend_4_1").click();},'300');
			//$("#input_temporal").select();
			return false;
		}
		for(var i=1;i<=tope;i++){
		//comprobamos que existe la fila
			if($("#col_sal_pend_4_"+i).html()!='' && $("#col_sal_pend_4_"+i).html()!='00:00:00'){
				por_guardar+=$("#col_sal_pend_1_"+i).html()+"~";//id de registro de nomina
				por_guardar+=$("#col_sal_pend_4_"+i).html()+'~';//hora de salida
				por_guardar+=$("#col_sal_pend_2_"+i).html()+"|";//fecha
			}
		}
		//alert(por_guardar);
	//enviamos datos por ajax
		if(por_guardar!=""){//si hay registros		
	//vemos si es necesario el password
			if(document.getElementById('password_assistencia_1')){
				password=1;
				pass_enc=$("#password_assistencia_1").val();//obtenemos el valor del password
				if(pass_enc.length<=0){
					alert("La contraseña de encargado no puede ir vacía!");
					$("#password_assistencia_1").focus();
					return false;
				}
			}
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'../ajax/especiales/asistenciass.php',
				cache:false,
				data:{fl:'actualiza_salidas',verifica_pass:password,password_encargado:pass_enc,salidas:por_guardar},
				success:function(dat){
					if(dat!='ok'){
						alert("Error:\n"+dat);
						return false;
					}else{
						var login=0;
						if(document.getElementById('incluye_login')){
							if(document.getElementById('incluye_login').checked==true){
								login=1;
							}
						}
						var aux=ajaxR("../ajax/especiales/asistenciass.php?tipo=3&clave="+$("#clave_as").val()+
							"&tipo_asistencia=1&incluir_login="+login);//modificación de Oscar 24.04.2018
						var ax=aux.split('|');
						$("#emer_asist").html(ax);
						$("#emer_asist").css("display","block");
						$("#btn_ok").focus();					
					}
				}
			});
		}else{
			location.reload();//recargamos la página
		}
	}

var valor_antes="",editando_celda=0;
	function edita_celda(num){
		if(editando_celda!=0){return false;}else{editando_celda=1;}
		valor_antes=$("#col_sal_pend_4_"+num).html();
		var input_tmp='<p style="width:90%;"><input type="time" min="07:00" max="23:59" id="input_temporal" style="padding:10px;width:90%;" value="'+valor_antes+'"'+
		' onblur="desedita_celda('+num+');"></p>';
		$("#col_sal_pend_4_"+num).html(input_tmp);
		$("#input_temporal").select();	
		$("#input_tmp").select();	
	}

	function desedita_celda(num){
		$("#col_sal_pend_4_"+num).html($("#input_temporal").val());
		editando_celda=0;
	}

var tipo_registro=0;
	function marca_tipo(flag){
		if(flag==1){
			$("#entrada").css("background","rgba(0,225,0,0.6)");
			$("#entrada").css("border","3px solid gray");
			$("#salida").css("background","gray");
			$("#salida").css("border","0");
		}
		if(flag==2){
			$("#salida").css("background","rgba(225,0,0,0.6)");
			$("#salida").css("border","3px solid gray");
			$("#entrada").css("background","gray");
			$("#entrada").css("border","0");
		}
		tipo_registro=flag;
		$("#clave_as").focus();
	}
/*Implementación de Oscar 27.02.2018*/
		function cambia_salida(){
			var id,h_s,h_e,p_e;
		//obtenemos valores
			id=document.getElementById('id_reg').value;
			h_e=document.getElementById('hor_ent_as').value;
			h_s=document.getElementById('hor_sal_as').value;
			p_e=document.getElementById('pss_encar_suc').value;
		//validamos antes de enviar datos
			if(h_s==''||h_s<=h_e){
				alert("La hora de salida debe ser mayor a la hora de entrada y este valor no puede ir vacío");
				document.getElementById('hor_sal_as').value="00:00:00";
				$("#hor_sal_as").select();
				return false;
			}
			var env=ajaxR("../ajax/especiales/modificaSalida.php?id_registro="+id+"&hora_s="+h_s+"&pss="+p_e);
		//trabajamos respuesta
			res=env.split("|");
			//detectamos error
			if(res[0]!='ok'){
				alert("Error\n"+env);
				return false;
			}
			//respuesta de password
			if(res[1]!='ok'){
				alert(res[1]);
				$("#pss_encar_suc").select();
				return false;
			}else{
				if(!$("#button").click()){
					alert("Error al registrar la nueva entrada");
					return false;
				}else{
					alert("La salida del día anterior, así como la entrada de este día fueron registradas exitosamente!!!");
					$("#emer_asist").css("display","none");
				}
			}
		}
/*fin de implementacion 27.02.2018*/
		
		function buscaEmpleado(f,flag,e,flag2)//implementación Oscar 24.05.2018 para registrar nómina con intro
		{	//flag2=1;entrada,=1;salida
			if( flag == 2 && $( '#clave_as' ).val() == '' ){
				alert( "Es necesario ingresar el login para registrar la entrada" );
				$( '#clave_as' ).focus();
				return false;
			}
			if( flag == 4 && $( '#clave_as_1' ).val() == '' ){
				alert( "Es necesario ingresar el login para registrar la salida" );
				$( '#clave_as_1' ).focus();
				return false;
			}
			var tipo_de_reg=1,login=0;//entrada por default
			var clave=$("#clave_as").val();
			if(flag==1||flag==3){
				var tca=e.keyCode;
				if(tca!=13){				
					return true;
				}
			}
			//fin de cambio 24.05.2018

			if(flag2==3||flag==4){
				tipo_de_reg=2;//salida
				clave=$("#clave_as_1").val();
			}
		//vemos si existe y esta habilitado el checkbox de incluir login en la busqueda
			if(document.getElementById('incluye_login')){
				if(document.getElementById('incluye_login').checked==true){
					login=1;
				}
			}
			document.getElementById('textoErr').innerHTML="";
			var aux=ajaxR("../ajax/especiales/asistenciass.php?tipo=3&clave="+clave+"&tipo_asistencia="+tipo_de_reg+"&incluir_login="+login);
			var ax=aux.split('|');
			$("#emer_asist").html(ax);
			$("#emer_asist").css("display","block");
			if(flag==2){
				$("#emer_asist").css("background","rgba(0,225,0,.8)");
			}
			if(flag==4){
				$("#emer_asist").css("background","rgba(225,0,0,.8)");
			}
			if( ax[1] == 'error' ){
				
				$("#emer_asist").html(ax[0]);
				$("#emer_asist").css("background","rgba(0,0,0,1)");
				$("#emer_asist").css("color","white");
			}
				$("#btn_ok").focus();
			
		}
//implementacion Oscar 2023 
	function token_evaluation( message = "No hay token de Asistencia!\nSolicita un token e ingresalo : " ){
		var ok = false;
		//while( ! ok ){	
			if( localStorage.getItem( 'assistance_token' ) == null || ! localStorage.getItem( 'assistance_token' ) ){
				/*if( ! confirm( "\nDa click en aceptar para salir o click en Cancelar para escibir un token : " ) ){
					//ok = true;
					location.href = "../../index.php";
				}else{*/
					var token = prompt( message );
					if( token == null ){
						location.href = "../../index.php";
						return false;
					}
					var check = seek_token( token );
					if( check == true ){
						ok = true;
					}
				//}
			}else{
				seek_token( localStorage.getItem( 'assistance_token' ) );
			}
			//location.reload();
		//}
	}

	function seek_token( token ){
		$.ajax({ type : 'post', url : '../../code/especiales/development/generador_tokens/ajax/tokensGenerator.php',
			data : { tokenFl : 'seekToken', token : token, device_token : localStorage.getItem( 'device_session_token' ) }, 
			success : function( dat ){ 
				dat = dat.split( '|' );
				//alert( dat[1] );
				if( dat[0] == 'ok' ){
					setToken( token );
					return true;
				}else{
					//var token = prompt( dat[1] );
					//if( token != null ){	
					//	var check = seek_token( token );
					//}else{
						localStorage.removeItem( 'assistance_token' );
						token_evaluation( dat[1] );
					//}
					/*if( ! confirm( dat[1] ) ){
						location.href = "../../index.php";
					}else{
					//	location.reload();
					}*/
				}
				return false;
			} 
		});
	}

	function setToken( token ){
		localStorage.setItem( 'assistance_token', token );
	}

	token_evaluation();
//fin de cambio Oscar 2023
	{/literal}
</script>
</div>
{literal}
<style type="text/css">
	#botones{
		padding-bottom: 25px !important;
		color : red !important;
		height : 30px;
	}
</style>
{/literal}


{include file="_footer.tpl" pagetitle="$contentheader"} 