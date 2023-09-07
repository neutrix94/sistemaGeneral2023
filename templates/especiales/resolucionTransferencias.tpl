<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />
<!--incluimos la librería de Passterisco--
<script type="text/javascript" src="../../js/passteriscoByNeutrix.js"></script>-->
<!--Modificación Oscar 08.05.2019 para incluir la libreria de Grids modificada para resolver el error de que cambiaba los valores al editar celdas del listado-->
	{include file="_headerResolucion.tpl" pagetitle="$contentheader"}
<!--Fin de cambio Oscar 08.05.2019-->
	<div id="campos">  
		<div id="titulo">Resoluci&oacute;n de transferencias</div>
		<br><br>
<!--Implementación Oscar -->

<!---->
		<div id="filtros">
			<form id="form1" name="form1" method="post" action="">
				<input type="hidden" name="procesa" value="SI">
				
				
				<fieldset>
					<legend style="color:#F81E04;"><b>Datos de la transferencia</b></legend>
					<table>
						<tr>
							<td class="texto_form">Folio</td>
							<td>&nbsp;</td>
							<td ><b>{$folio}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Fecha y hora</td>
							<td>&nbsp;</td>
							<td><b>{$fechahora}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Sucursal de origen</td>
							<td>&nbsp;</td>
							<td><b>{$sucursal_or}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Almac&eacute;n de origen</td>
							<td>&nbsp;</td>
							<td><b>{$alma_or}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Sucursal destino</td>
							<td>&nbsp;</td>
							<td><b>{$sucursal_des}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Almac&eacute;n destino</td>
							<td>&nbsp;</td>
							<td><b>{$alma_des}</b></td>
						</tr>
						<tr>
							<td class="texto_form">Creada por</td>
							<td>&nbsp;</td>
							<td><b>{$creadapor}</b></td>
						</tr>
					</table>
				</fieldset>	
			</form>
		</div>
	
	</div>
			
	<div id="bg_seccion">
    	<div class="name_module" align="center">
    		<table>
				<tr valign="middle">
					<td><p class="margen">Productos</p></td>   
				</tr>
			</table>
    	</div>
<!--Implemetación Oscar 25.02.2019 para buscador de productos en la resolución-->
    <div id="emergente_resolucion" style="position: fixed;width: 100%;height: 100%;left:0;top:0;background: rgba(0,0,0,.7);z-index: 100;display: none;"><br><br><br><br>
    	<div style="color: white;font-size:25px;" align="center">
    		<b>Ingrese su nombre para finalizar con la resolución:</b><br><br>
    		<p style="width: 30%;" align="center">
    			<input type="text" id="passWord_1" placeholder="su nombre..." style="padding: 10px;"><!--onkeyDown="cambiar(this,event,'passWord');"-->
    		</p>
    		<br>
    		<button style="padding: 10px;" onclick="verificar_pss();" id="btn_accept">Aceptar</button>
    		<!--<input type="hidden" id="passWord" value="">-->
    	</div>
    </div>
<!--Fin de cambio Oscar 25.02.2019-->

		<div class="tablas-res"> 	
		<!--Implemetación Oscar 25.02.2019 para buscador de productos en la resolución-->
			<p align="left" style="width:30%;position: absolute;top:0;">
				<input type="text" id="buscador_prods" placeholder="Buscar producto..." onkeyup="buscador_prods(this,event);"> 
				<div id="res_busqueda" style="position: absolute;z-index:50;width: 40%;height:300px;border:1px solid;background: white;top:100px;display: none;overflow-y: auto;"></div>
			</p>
			<p style="width:8%;position: absolute;top:0;left: 35%;">
				<input type="number" id="campo_cantidad" style="padding: 10px;width: 100%;border: 2px solid green;" placeholder="Cantidad" onkeyup="valida_tca_cant(event);">
			</p>
				<button id="btn_agrega" style="border-radius:50%;padding: 10px;font-size: 20px;width: 50px;height: 50px;background: green;color:white;position: absolute;top:0;position: absolute;top:10px; left: 45%;"><b>+</b></button>
		<!--Fin de cambio Oscar 25.02.2019-->
 			<div id="cosa" align="center"> 				
 				<table id="transferenciasProductos" cellpadding="0" cellspacing="0" border="1" Alto="250" conScroll="S" validaNuevo="false" despuesInsertar="" AltoCelda="25" auxiliar="0" ruta="../../img/grid/" validaElimina="false" Datos="../ajax/getDatosDif.php?id={$llave}" verFooter="N" guardaEn="" listado="N" class="tabla_Grid_RC" scrollH="N" despuesEliminar="" >
					<tr class="HeaderCell">                      
						<td tipo="oculto" modificable="N" align="center" width="0" offsetwidth="0">id_transferencia_producto</td>
						<td tipo="texto" modificable="N" align="center" width="100" offsetwidth="100">Orden lista</td>
						<td tipo="texto" modificable="N" align="left" width="200" offsetwidth="200">Descripción</td>
						<td tipo="oculto" modificable="N" align="left" verSumatoria="N" width="0" offsetwidth="0">Presentación</td>
						<td tipo="decimal" modificable="N" align="right" width="100" offsetwidth="100">Pedido</td>
						<td tipo="decimal" modificable="S" align="right" width="100" offsetwidth="100" onchange="cambia_valores_grid('#')">Recibida</td>
					<!--la diferencia va oculta-->
						<td tipo="oculto" modificable="N" align="right" width="0" offsetwidth="0" formula="$Enviada-$Recibida">Diferencia</td>
						<td tipo="decimal" modificable="S" align="right" width="110" offsetwidth="100" onchange="cambiar_valores_regresa('#');">Sobrante/se queda</td>
						<td tipo="decimal" modificable="N" align="right" width="100" offsetwidth="100">Faltante</td>
						<td tipo="decimal" modificable="N" align="right" width="120" offsetwidth="100">Sobrante/se regresa</td>
						<td width="65" offsetWidth="65" tipo="libre" valor="Confirmar" align="center">
							<img src="{$rooturl}img/icono_ok.png" width="22" height="22" onclick="valida_tipo_resolucion('#')">
						</td>
						<td tipo="oculto" modificable="N" align="right" width="0" offsetwidth="100">id_producto</td>
					</tr>       
				</table>
			</div>
	<button onclick="termina_resolucion();">
		<img src="../../img/especiales/save.png" width="40px"><br>Guardar<br>Resolución
	</button>
			<script>	  	
				CargaGrid('transferenciasProductos');								
			</script> 
		</div>  
	</div>	
		
<script>

	{literal}
	var password=0;//variale implementada por Oscar 25.02.2019 para saber cuando ya se tecleó la contraseña del usuario
	var posicion_actual=0;
	var caso;
	
	function termina_resolucion(){
		var tabla=document.getElementById('transferenciasProductos'); 
		trs=tabla.getElementsByTagName('tr');	
       	var tam=trs.length-3;
       	if(tam>0){alert("Para terminar la resolución es necesario que resuelva todos lo productos!!!");return false;}
       	$("#emergente_resolucion").css("display","block");return false;
    //enviamos datos por ajax
    	$.ajax({
    		type:'post',
    		url:'',
    		cache:false,
    		data:{},
    		success:function(dat){
    			alert(dat);
    		}
    	});
	}
/*implementación de Oscar para el buscador*/
	function valida_tca_cant(e){
		if(e.keyCode==13){
			$("#btn_agrega").click();
		}
	}

	function buscador_prods(obj,e){
		if(e.keyCode==40){
			$("#resultado_1").focus();
			return false;
		}
	//tomamos el valor del buscador
		var clave=$(obj).val().trim();
		if(clave.length<=2){
			$("#res_busqueda").val("");
			$("#res_busqueda").css("display","none");
			return false;
		}else{
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'../ajax/guardaResolucion.php',
				cache:false,
				data:{es_buscador:'1',txt:clave},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!\n"+dat);
					}else{
						$("#res_busqueda").html(aux[1]);
						$("#res_busqueda").css("display","block");
					}
				}
			});
		}

		}
	function valida_tca_opc(num,e){
		var tca=e.keyCode;
		//alert(num+"|"+tca);
		if(tca==38){//arriba
			if(num==1){
				$("#buscador_prods").select();return false;
			}else{
				$("#resultado_"+parseInt(num-1)).focus();return false;
			}
		}
		if(tca==40){//abajo
			$("#resultado_"+parseInt(num+1)).focus();return false;
		}
		if(tca==13){//enter
			$("#resultado_"+num).click();return false;
		}

	}
	function buscar_prod_grid(id_prod){
	//recorremos el grid en busca del producto
		var tabla=document.getElementById('transferenciasProductos'); 
		trs=tabla.getElementsByTagName('tr');	
       	var can2=0;
       	var tam=trs.length-3;
      	//alert(tam);return false;
//       	transferenciasProductos_1_0
		for(i=0;i<tam;i++){
			if($("#transferenciasProductos_11_"+i).attr('valor')==id_prod){//si xiste en la tabla
				$("#transferenciasProductos_5_"+i).click();//enfocamos el producto
				$("#res_busqueda").css("display","none");//escndemos la emergente
				return true;
			}
		}//fin de for i
	//enfocamos el campo de cantidad
		$("#campo_cantidad").focus();
		$("#btn_agrega").attr("onclick","segunda_busqueda("+id_prod+");");
				$("#transferenciasProductos_5_"+i).click();//enfocamos el producto
				$("#res_busqueda").css("display","none");//escndemos la emergente
	}

	function segunda_busqueda(id_prod){
		{/literal}var id_trasnf_orig={$llave};{literal}
	//si no se encontró en el grid buscamos datos del producto
	//obtenemos la cantidad
		var cant=$("#campo_cantidad").val();
	//validamos que haya una cantidad mayor a cero
		if(cant.length<=0 || cant<=0){
			alert("La cantidad no puede ir vacía ni puede ser negativa!!!");
			$("#campo_cantidad").select();return false;
		}
		//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'../ajax/guardaResolucion.php',
			cache:false,
			data:{es_buscador:'2',id:id_prod,id_trans:id_trasnf_orig,nva_cant:cant},
			success:function(dat){
				if(dat=='ok'){
					location.reload();//recargamos la página
				}else{
					alert("Error\n"+dat);
				}
			}
		});
	}

	function resalta_opc(num){
		$("#resultado_"+num).css("background","rgba(0,0,225,.5)");
	}

	function regresa_color_opc(num){
		$("#resultado_"+num).css("background","white");
	}

	/*Implementación Oscar 09.04.2019 para cambiar cambiar los valores del grid*/
		function valida_tipo_resolucion(pos){			
		/*Implementación Oscar 07.05.2019 para el cambio de validación antes de guardar la resolución*/
			if( parseInt($("#transferenciasProductos_5_"+pos).html())!=( parseInt($("#transferenciasProductos_4_"+pos).html())+parseInt($("#transferenciasProductos_7_"+pos).html())-parseInt($("#transferenciasProductos_8_"+pos).html())+parseInt($("#transferenciasProductos_9_"+pos).html()) ) ){
				alert("No puede quedarse un valor mayor a la cantidad recibida");			
				setTimeout(function(){$("#transferenciasProductos_7_"+pos).click();},200);	
				setTimeout(function(){$("#ctransferenciasProductos_7_"+pos).val('0');$("#ctransferenciasProductos_7_"+pos).select();},500);
				return false;
			}
		/*Fin de cambio Oscar 07.05.2019*/
			var id=celdaValorXY('transferenciasProductos', 0, pos);
			var sobra=$("#transferenciasProductos_7_"+pos).html();
			var falta=$("#transferenciasProductos_8_"+pos).html();
			var se_regresa=$("#transferenciasProductos_9_"+pos).html();
			var dif=celdaValorXY('transferenciasProductos', 6, pos);
		//condicionamos de acuerdo al caso
			if(dif<0 && se_regresa>0){//regresar
				devolver(pos);return true;
			}else{
				mantiene(pos);return true;
			}
		}

		function cambia_valores_grid(pos){
		//sacamos el valor del campo 'Pedido'
			var ped=parseInt($("#transferenciasProductos_4_"+pos).html());
		//sacamos el valor del campo 'Recibida'
			var recib=parseInt($("#transferenciasProductos_5_"+pos).html());
			var operacion=recib-ped;
			/*$("#transferenciasProductos_5_"+pos).attr('valor',operacion);*/
			
//			$("#transferenciasProductos_6_"+pos).html(ped-recib);
			$("#transferenciasProductos_6_"+pos).attr('valor',(ped-recib));

			if(operacion>0){
				$("#transferenciasProductos_7_"+pos).html(operacion);
				$("#transferenciasProductos_7_"+pos).attr('valor',operacion);
				$("#transferenciasProductos_8_"+pos).html(0);
				$("#transferenciasProductos_8_"+pos).attr('valor',0);
				$("#transferenciasProductos_9_"+pos).html(0);
				$("#transferenciasProductos_9_"+pos).attr('valor',0);
			}else if(operacion<0){
				$("#transferenciasProductos_8_"+pos).html((operacion*-1));
				$("#transferenciasProductos_8_"+pos).attr('valor',(operacion*-1));
				$("#transferenciasProductos_7_"+pos).html(0);
				$("#transferenciasProductos_7_"+pos).attr('valor',0);
				$("#transferenciasProductos_9_"+pos).html(0);
				$("#transferenciasProductos_9_"+pos).attr('valor',0);
			}else if(operacion==0){
				$("#transferenciasProductos_8_"+pos).html(0);
				$("#transferenciasProductos_8_"+pos).attr('valor',0);
				$("#transferenciasProductos_7_"+pos).html(0);
				$("#transferenciasProductos_7_"+pos).attr('valor',0);
				$("#transferenciasProductos_9_"+pos).html(0);
				$("#transferenciasProductos_9_"+pos).attr('valor',0);

			}
/*
			if(aux<0){
				$("#transferenciasProductos_7_"+pos).html('0');
				$("#transferenciasProductos_8_"+pos).html(aux);
				$("#transferenciasProductos_9_"+pos).html('0');
			}else if(aux>0 && recib<ped){
				$("#transferenciasProductos_7_"+pos).html(aux);
				$("#transferenciasProductos_8_"+pos).html('0');
				$("#transferenciasProductos_9_"+pos).html('0');
			}else if(aux>0 && recib>ped){
				$("#transferenciasProductos_7_"+pos).html('0');
				$("#transferenciasProductos_8_"+pos).html('0');
				$("#transferenciasProductos_9_"+pos).html(aux);
			}*/
			//alert(aux);
		}
	/*Fin de cambio Oscar 09.04.2019*/

	function cambiar_valores_regresa(pos){
		var operacion=$("#transferenciasProductos_5_"+pos).html()-(parseInt($("#transferenciasProductos_4_"+pos).html())+parseInt($("#transferenciasProductos_7_"+pos).html())); 
		
		if(operacion<0){
			alert("No puede quedarse un valor mayor a la cantidad recibida");			
			setTimeout(function(){$("#transferenciasProductos_7_"+pos).click();},200);	
			setTimeout(function(){$("#ctransferenciasProductos_7_"+pos).val('0');$("#ctransferenciasProductos_7_"+pos).select();},500);		
			return false;
		}

		document.getElementById("transferenciasProductos_7_"+pos).setAttribute("valor",operacion);	
		$("#transferenciasProductos_9_"+pos).html(operacion);
	}

		function devolver(pos){
			var id=celdaValorXY('transferenciasProductos', 0, pos);
			var diferencia=celdaValorXY('transferenciasProductos', 6, pos);
			
			diferencia=parseInt(diferencia);

			if(diferencia >0){
				alert("Error, la diferencia es mayor a cero");return false;
			}
			
				var url="../ajax/guardaResolucion.php?id_transferencia="+id+"&tipo=1&cant_recibida="+celdaValorXY('transferenciasProductos', 5, pos);
				url+="&se_queda="+celdaValorXY('transferenciasProductos', 7, pos)+"&faltante="+celdaValorXY('transferenciasProductos', 8, pos);
				url+="&se_regresa="+$('#transferenciasProductos_9_'+pos).html();
				//alert(url);return false;
				var res =ajaxR(url);
				if(res=='SI'){
					location.reload();
				}else{
					alert(res);	
				}
				
				
				if(res == "Se ha actualizado el inventario conforme a las resoluciones elegidas"){
					location.href="../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
				}else{
		/*Implementación de Oscar 25.02.2019 para impresión del ticket de resoluciones*/
					var ax=res.split("|");
					if(ax[0]=="ok"){
						var imp_tkt=ajaxR("../especiales/Transferencias/ticket_transferencia/ticket_transf.php?id_transf="+ax[1]+"&num_tickets="+ax[2]);
						location.href="../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
						//alert(imp_tkt);
					}else{
						location.reload();
					}
				}	
		}
		
		function mantiene(pos)
		{

			var id=celdaValorXY('transferenciasProductos', 0, pos);
	
				var url="../ajax/guardaResolucion.php?id_transferencia="+id+"&tipo=2&cant_recibida="+celdaValorXY('transferenciasProductos', 5, pos);
				url+="&se_queda="+celdaValorXY('transferenciasProductos', 7, pos)+"&faltante="+celdaValorXY('transferenciasProductos', 8, pos);
				url+="&se_regresa="+$('#transferenciasProductos_9_'+pos).html();
				//alert(url);return false;
				var res =ajaxR(url);
				//alert(res);return false;
				if(res=='SI'){
					location.reload();
				}else{
					//alert(res);	
				}
				//RecargaGrid('transferenciasProductos', '');
				
				if(res == "Se ha actualizado el inventario conforme a las resoluciones elegidas"){
					location.href="../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
				}else{
		/*Implementación de Oscar 25.02.2019 para impresión del ticket de resoluciones*/
					var ax=res.split("|");
					if(ax[0]=="ok"){
						var imp_tkt=ajaxR("../especiales/Transferencias/ticket_transferencia/ticket_transf.php?id_transf="+ax[1]+"&num_tickets="+ax[2]);
						location.href="../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
//						alert(imp_tkt);
					}else{
						location.reload();
					}
		/*Fin de cambio Oscar 25.02.2019*/
				
				}
		}
var se_dio_click=0;//implementado por Oscar el 26.11.2019 para no permitir que se gurden varias veces las resoluciones
/*Implementación Oscar 25.02.2019 para confirmación de la resolución*/
		function verificar_pss(){
			if(se_dio_click==1){//implementado por Oscar el 26.11.2019 para no permitir que se gurden varias veces las resoluciones
				return false;
			}
			se_dio_click=1;
			$( '#btn_accept' ).css( 'display', 'none');
		//obtenemos el valor de la variable oculta
		{/literal}var id_trasnf_orig={$llave};{literal}
			var pss=$("#passWord_1").val();
			if(pss.length<=0){
				alert("El nombre no puede ir vacío!!!\n\n");
				return false;
			}
			var url=ajaxR("../ajax/guardaResolucion.php?flag=verificar_password&clave="+pss+"&finalizar_resolucion=1&id="+id_trasnf_orig);
			//alert(url);return false;
			var ax=url.split('|');
			if(ax[0]!='ok'){
				alert(url);
				$( '#btn_accept' ).css( 'display', 'block');
				se_dio_click = 0;
				return false;
			}else{
		/*impresion del ticket*/
			//var ax=res.split("|");
			var imp_tkt=ajaxR("../especiales/Transferencias/ticket_transferencia/ticket_transf.php?id_transf="+ax[1]+"&num_tickets="+ax[2]);
	/*implementacion Oscar 2021 para redireccionar a los diferentes listados*/
		//redireccionamos al listado
		{/literal}
			{if $is_list_transfer eq 1}
				location.href="../especiales/Transferencias/transferencias_multiples/index.php?";
			{else}
				location.href="../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
			{/if}
		{literal}
	/*fin de cambio Oscar 2021*/
			}
		}
/*Fin de cambio Oscar 25.02.2019*/
	{/literal}	

</script>

{include file="_footer.tpl" pagetitle="$contentheader"} 