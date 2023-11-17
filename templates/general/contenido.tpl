<!--version 30.10.2019-->
<!-- 1. Incluye archivo /templates/_header.tpl -->
{include file="_header.tpl" pagetitle="$contentheader"}

<!-- implementacion Oscar 2021 para agregar JS adicional -->
	{include file="general/utilidadesGenerales.tpl"}
<!-- Fin de cambio Oscar 2021 -->

<!-- implementacion Oscar 2023/11/15 para agregar JS adicional y ok en escaneos de la plantilla-->
	{include file="general/responsive_by_js.tpl"}

	<audio id="ok" controls style="display : none;">
		<source type="audio/wav" src="../../files/sounds/ok.mp3">
	</audio>

	<audio id="error" controls style="display : none;">
		<source type="audio/wav" src="../../files/sounds/error.mp3">
	</audio>
	<div class="emergent" style="position : fixed; top : 0; left : 0; width : 100%; height : 100%; background-color : rgba( 0,0,0,.5 ); z-index : 10000; display : none;">
	
		<div class="emergent_content" style="position : relative; background-color : white; width : 90%; left : 5%; height : 50%; top : 10%;"></div>
	</div>
<!-- Fin de cambio Oscar 2023/11/15 -->

<!-- Excepcion 1: Implementacion para botones de exportacion de ubicaciones desde la configuracion de la sucursal -->
{if $tabla eq 'ec_configuracion_sucursal' && $no_tabla eq '0'}
	<table style="position:absolute;bottom:30%;">
		<tr>
			<td>
				<button onclick="exporta_ubics_sucs();">
				Exportar<br>Ubicaciones
				</button>
			</td>
		</tr>
		<tr>
			<td>
				<button id="acciona_imp_ubic_sucs" onclick="msg_importa_ubics();">
					Importar<br>Ubicaciones
				</button>
				<button id="acciona_importacion_ubic_sucs" style="display:none;">
					Actualizar<br>Ubicaciones
				</button>
			</td>
	</table>
	<form id="formularioUbicaciones" method="post" action="../ajax/importaExportaUbicacionesSucursales.php" target="TheWindow">
			<input type="hidden" id="fl_ubic" name="fl_ubic" value="1" />
			<input type="hidden" id="sucursal_ubic" name="sucursal_ubic" value="1" />
			<inupt type="hidden" id="datos_ubic_sucs" name="datos_ubic_sucs" value="1">
			<input type="file" id="archivo_ubic" name="archivo_ubic" value="" style="display:none;" onchange="preparar_importacion_ubic();"/>
	</form>
	{literal}
		<script type="text/javascript">

			var ventana_abierta_ubics;
			function msg_importa_ubics(){
				if(!confirm("Recuerde que no pueden ir comas en las ubicaciones y las ubicaciones deberán de ser menores a 10 caracteres!!!")){
					return false;
				}
				$('#archivo_ubic').click();
			}

			function exporta_ubics_sucs(){
			//obtenemos el id de la sucursal
				var id_suc_ubic=$("#id_sucursal").val();
				if(id_suc_ubic<1){
					alert("Esta sucursal no puede tener ubicaciones!!!");
					return false;
				}
				$("#fl_ubic").val('exporta_ubicaciones');
				$("#sucursal_ubic").val(id_suc_ubic);
			//enviamos la descarga
				ventana_abierta_ubics=window.open('', 'TheWindow');
				document.getElementById('formularioUbicaciones').submit();
				setTimeout(cierra_pestana_ubic,10000);
			}

			function cierra_pestana_ubic(){
				ventana_abierta_ubics.close();//cerramos la ventana
			}

			function preparar_importacion_ubic(){
				$("#acciona_imp_ubic_sucs").css("display","none");
				$("#acciona_importacion_ubic_sucs").css("display","block");

			}

			/*function importar_ubic_sucs(){

			}*/
				$('#acciona_importacion_ubic_sucs').on("click",function(e){
  					e.preventDefault();
  					$('#archivo_ubic').parse({
        				config: {
            				delimiter:"auto",
            				complete: importaUbicacionesSucursales,
        				},
       			 		before: function(file, inputElem){
       			 			//$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
            			//console.log("Parsing file...", file);
        				},
       					error: function(err, file){
         		   			console.log("ERROR:", err, file);
        					alert("Error!!!:\n"+err+"\n"+file);
        				},
       			 		complete: function(){
            				//console.log("Done with all files");
        				}
    				});
				});

			function importaUbicacionesSucursales(results){
				$("#contenido_emergente_global").html('<p align="center" style="color:white;font-size:30px;">Cargando datos<br><img src="../../img/img_casadelasluces/load.gif"></p>');
					$("#ventana_emergente_global").css("display","block");

				var data = results.data;//guardamos en data los valores delarchivo CSV
				var arr="";
				var id_suc_ubic=$("#id_sucursal").val();
				var msg_alrta="El formato del archivo no es valido!!!\nVerifique que no haya comas en los campos de ubicación y vuelva a intentar!!!\n";
				msg_alrta+="El encabezado debe de llevar los campos: ID PRODUCTO|ORDEN DE LISTA|ALFANUMERICO|NOMBRE|INVENTARIO EN SUCURSAL(ALMACEN PRIMARIO)";
				msg_alrta+="|UBICACION";
				for(var i=1;i<data.length;i++){
					var row=data[i];
					var cells = row.join(",").split(",");
					if(cells.length>1){
						if(cells.length!=6){
							alert(msg_alrta+"\n"+cells.length);
							location.reload();
							return false;
						}
						arr+=cells[0]+",";
    					arr+=cells[5];//se cambia la posición  de 6 a 7 por la implementación de la clave de proveedor Oscar 26.02.2019
						if(i<data.length-1){
					 		arr+="|";
						}
					}
				}//fin de for i
				//alert(arr);
			//enviamos la descarga
				$.ajax({
					type:'post',
					url:'../ajax/importaExportaUbicacionesSucursales.php',
					cache:false,
					data:{fl_ubic:'importa_ubicaciones',datos_ubic_sucs:arr,sucursal_ubic:id_suc_ubic},
					success:function(dat){
						alert(dat);
						$("#ventana_emergente_global").css("display","none");
						location.reload();
					}
				});
			}

		</script>
	{/literal}
{/if}


<!-- Excecpcion 2: Implementacion para exportar/ importar estacionalidades -->
{literal}

<!-- 2. Incluye archivo /js/papaparse.min.js -->
<script language="JavaScript" type="text/javascript" src="../../js/papaparse.min.js"></script>
				<script type="text/JavaScript">
				$('#submit-file').on("click",function(e){
  					e.preventDefault();
  					$('#imp_csv_prd').parse({
        				config: {
            				delimiter:"auto",
            				complete: importaEstac,
        				},
       			 		before: function(file, inputElem){
            			//console.log("Parsing file...", file);
        				},
       					error: function(err, file){
         		   			console.log("ERROR:", err, file);
        					alert("Error!!!:\n"+err+"\n"+file);
        				},
       			 		complete: function(){
            				//console.log("Done with all files");
        				}
    				});
				});

		//detectamos archivo cargado
				$("#imp_csv_prd").change(function(){
        			var fichero_seleccionado = $(this).val();
      				var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
       				/* if(nombre_fichero_seleccionado==='') {
      			    $('#delCarta').addClass('invisible');
        			} else {
        			   $('#delCarta').removeClass('invisible');
       				 }*/
       				if(nombre_fichero_seleccionado!=""){
        				$("#bot_imp_estac").css("display","none");//ocultamos botón de importación
        				$("#submit-file").css("display","block");//mostramos botón de inserción
        				$("#txt_info_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
        				$("#txt_info_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
        				//$("#importa_csv_icon").css("display","none");
        			}else{
        				alert("No se seleccionó ningun Archivo CSV!!!");
        				return false;
        			}
    			});
/*
				function cambiaEstacDependiente(){
					alert("est");
				}
*/
				function importaEstac(results){
					$("#contenido_emergente_global").html('<p align="center" style="color:white;font-size:30px;">Cargando datos<br><img src="../../img/img_casadelasluces/load.gif"></p>');
					$("#ventana_emergente_global").css("display","block");

					var id_estac=$("#id_estacionalidad").val();
	  				var data = results.data;//guardamos en data los valores delarchivo CSV
	    			var tam_grid=$("#estacionalidadProducto tr").length-3;
	    			//alert(data);
	    			//return true;
	    			var arr="";
	   				for(var i=1;i<data.length-1;i++){
	    				//arr+=data[i];
	    				var row=data[i];
	    				var cells = row.join(",").split(",");
	    				/*for(j=0;j<cells.length;j++){*/
	            			arr+=cells[0]+",";
	            			arr+=cells[6];
	        			/*}*/
	        			if(i<data.length-2){
	        			arr+="|";
	        			}
	    			}
	    		//enviamos datos por ajax
	    			$.ajax({
	    				type:'post',
	    				url:'../ajax/importaExportaEstacionalidades.php',
	    				cache:false,
	    				data:{fl:2,id_estac:id_estac,arreglo:arr},
	    				success:function(dat){
	    					var arr_resp=dat.split("|");
	    					if(arr_resp[0]!='ok'){
	    						alert("Error al recargar el grid de estacionalidad-producto!!!\n"+dat);
	    					}else{
	    						location.reload();
	    					}
	    					$("#ventana_emergente_global").css("display","none")//ocultamos la emergente;
	    				}
	    			});
	    		}

				function importa_exporta_estacionalidades(flag){
					var est_id=$("#id_estacionalidad").val();//extraemos el id de la estacionalidad
					if(flag==1){
						document.location.href='../ajax/importaExportaEstacionalidades.php?fl='+flag+"&estacionalidad_id="+est_id;
					}else if(flag==2){
						$("#imp_csv_prd").click();
					}
				}

		/*************************Fin de implementacion Oscar 16.05.2018*******************************/
</script>
{/literal}

    <div id="campos">
<!-- 3. Div de ventana emergente -->
	<div id="emerge" style="position:fixed;top:0;height:100%;width:100%;background:rgba(0,0,0,.6);display:none;z-index:100;"><!--rgba(103, 161,13,.8);-->
		<center>
			<div id="mensajEmerge" style="width:50%;position:absolute;top:200px;left:25%;background:rgba(225,0,0,.5);border-radius:10px;">

			</div>
		</center>
	</div>
<!--Excepcion 3: Implementacion Oscar 19.08.2019 para impresion de credencial de usuario-->
		{if $tabla eq 'sys_users' && $no_tabla eq '0' && $tipo neq '0'}
			<button id="impresion_cred_usuario" onclick="imprimeCredencial();" style="position:absolute;top:300px;left:42%;">
				Imprimir credencial<img src="../../img/especiales/credencial_usuario.png"  width="50px">
			</button>
		{/if}
<!--Fin de cambio Oscar 19.08.2019-->

<!--Excepcion 4: Implementacion Oscar 02.08.2019 para importacion del detalle de ordenes de compra-->
		{if $tabla eq 'ec_ordenes_compra' && $no_tabla eq '1' && $tipo neq '0'}
			<button style="position:fixed;right:1.6%;top:72%;border-radius:10px;font-size:10px;" onclick="descarga_formato_detalle_oc();">
				<img src="../../img/especiales/fotmato_en_blanco.png" width="30px"><br>
				Descarga<br>
				Formato
			</button>
		<!--formulario para la exportación del formato en CSV-->
			<form id="detalle_oc" method="post" action="../ajax/importarDetalleOrdenCompra.php?" target="TheWindow">
				<input type="hidden" name="fl" value="formato" />
			</form>
		<!--formulario para a importacion del csv de detalle de orden de cmompra-->
			<form class="">
				<input type="file" id="imp_detalle_oc" style="display:none;">
				<p class="nom_csv" style="position:fixed;right:7.5%;top:84.3%;border-radius:10px;font-size:10px;display:none;width:10%;">
					<input type="text" id="txt_info_detalle_oc_csv" disabled>
				</p>
				<button type="submit" id="submit_file_detalle_oc" style="position:fixed;right:1.6%;top:84%;border-radius:10px;font-size:10px;display:none;" class="bot_imp">
					<img src="../../img/especiales/sube.png" height="30px;">
					<br><b>Cargar<br>Archivo</b>
				</button>
			</form>
			<!--style="position:absolute;top:107%;right:15%;padding:5px;border-radius:10px;"-->
			<button style="position:fixed;right:1.6%;top:84%;border-radius:10px;font-size:11px;"
			onclick="carga_datos_detalle_oc();" id="btn_imp_detalle_oc">
				<img src="../../img/especiales/importaCSV.png" width="30px"><br>
				importar<br>Detalle
			</button>
<!--					<td>
					</form>-->
		{/if}
<!--Fin de cambio Oscar 02.08.2019-->

	<!-- Excepcion 5: Implementacion de Oscar 25.07.2018 para exportar/importar lista de estacionalidades-->
		{if $tabla eq 'ec_oc_recepcion'}
		<div style="position:fixed;z-index:40;top:15px;right:15px;">
			<button onclick="emerge_pagos();" id="pags_prv" style="background:white;border-radius:15px;">
				<img src="../../img/especiales/pagar.png" width="50px;">
				<br>Registrar Pago
				</button>
		</div>
			{literal}
			<script>
			function carga_cajas_sobrante(){
				$("#id_caja_o_cuenta").val();//extraemos el valor de la caja de donde se toma el pago
				var envia='../ajax/cargaPagosProveedor.php?caja_pago='+$("#id_caja_o_cuenta").val()+'&fl=carga_caja_sobrante';
				var env=ajaxR(envia);
               	var tmp=env.split("|");
               	if(tmp[0]!='ok'){
               		alert("Error!!!\n"+env);
               	}
               	$("#caja_de_sobrante").html(tmp[1]);//cargamos los resultados en el combo de caja sobrante
			}

			function emerge_pagos(filtro){
			//obtenemos el id de la orden de compra
			var id_ord_comp=$("#id_oc_recepcion").val();
				//alert(id_ord_comp);
				var envia='../ajax/cargaPagosProveedor.php?oc='+id_ord_comp;
               //metemos los filtros
               	if(filtro==1){
               		envia+="&status="+$("#filtro_tipo_rec").val();
               	//extraemos los filtros de fecha
               		if($("#rango_del").val()!="" || $("#rango_al").val()!=""){
               		//validamos que las 2 fechas esten capturadas
               			if($("#rango_del").val()!="" && $("#rango_al").val()==""){
               				alert("El campo de fecha final no puede ir vacío!!!");
               				$("#rango_al").focus();
               				return false;
               			}
               			if($("#rango_al").val()!="" && $("#rango_del").val()==""){
               				alert("El campo de fecha inicial no puede ir vacío!!!");
               				$("#rango_del").focus();
               				return false;
               			}
               		//mandamos la condición de rango de fechas
               			envia+="&periodo=AND ocr.fecha_recepcion BETWEEN '"+$("#rango_del").val()+" 00:00:00' AND '"+$("#rango_al").val()+" 23:59:59'";
               		}
               		//alert(envia);
               	}
               	var env=ajaxR(envia);
                var auxi=env.split('|');
                if(auxi[0]!='ok'){
                //mostramos el error
                	alert("Error!!!\n"+env);
                }else{
                //cargamos los datos en la emergente
                	$("#mensajEmerge").html(auxi[1]);
                	$("#emerge").css("display","block");
                }
			}
			</script>
			{/literal}

		{/if}
	<!--Fin de cambio-->

<!-- 4. Div de titulo de la pantalla -->
	<div id="titulo">{$titulo}</div>
<!-- 5. Declaracion del formulario -->
		<form action="" method="post" name="formaGral" enctype="multipart/form-data">
			<script>
				var ejecutar="";
			</script>
<!-- 6. Variables ocultas %tipo, accion, tabla, no_tabla, llave%% -->
				<input type="hidden" name="tipo" value="{$tipo}" />
				<input type="hidden" name="accion" value="" />
				<input type="hidden" name="tabla" value="{$tabla}" />
				<input type="hidden" name="no_tabla" value="{$no_tabla}" />
				<input type="hidden" name="llave" value="{$llave}" />
		<!--Excepcion : Implementacion de botones especiales de la pantalla de sucursal-->
				{if $tabla eq 'sys_sucursales' && $no_tabla eq 0 && ($tipo_perfil eq '1' or $tipo_perfil eq '5')}
				{literal}
					<style type="text/css">
						.btns_deshabilit{padding:10px;border-radius:5px;}
					.btns_deshabilit:hover{background: rgba(225,0,0,.5);color: white;}
					</style>
				{/literal}
				{assign var="sucursal_informacion_tooltip" value="en la sucursal"}
				{if $campos[0][10] eq '-1'}
					{assign var="sucursal_informacion_tooltip" value="en todas las sucursales"}
				{/if}
		<!-- Sección de herramientas por sucursal -->	
				{include file="general/herramientasSucursales.tpl"}

				{/if}
				{if $no_tabs eq 1}
					<div class="redondo" align="center" >
						<table width="87%" border="0" class="tabla-inputs" >
	<!-- 7. Iteracion del arreglo %$campos%% para formar campos (catalogos) de la pantalla-->
							{section loop=$campos name=indice max=$no_filas}
		<!-- 7.1. Condicion si son tres o menos campos-->
									{if $no_campos <= 3}
										<td width="69" class="texto_form">{$campos[indice][3]}</td>
										<td width="175">
			<!-- 7.1.1. Condicion si el tipo de campo es CHAR -->
										{if $campos[indice][5] eq 'CHAR'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if $campos[indice][12] > 60}
												<textarea name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if}>{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}</textarea>
											{else}
												<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if}/>
											{/if}
			<!-- 7.1.2. Condicion si el tipo de campo es DATE -->
										{elseif $campos[indice][5] eq 'DATE'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="10" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else}onfocus="calendario(this)"{/if} />
											<span class="text_legend">yy-mm-dd</span>
			<!-- 7.1.3. Condicion si el tipo de campo es TIME -->
										{elseif $campos[indice][5] eq 'TIME'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="8" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else} onkeypress="return validaTime(event, this.id)"{/if} />
											<span class="text_legend">hh:mm:ss</span>
			<!-- 7.1.4. Condicion si el tipo de campo es INT o FLOAT -->
										{elseif $campos[indice][5] eq 'INT' or $campos[indice][5] eq 'FLOAT'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} onkeypress="return validarNumero(event,{if $campos[indice][5] eq 'FLOAT'}1{else}0{/if},id);" onblur="{$campos[indice][15]}"/>
			<!-- 7.1.5. Condicion si el tipo de campo es PASSWORD -->
										{elseif $campos[indice][5] eq 'PASSWORD'}
                                            {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
                                            <input type="password" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if}/>
			<!-- 7.1.6. Condicion si el tipo de campo es BINARY -->
										{elseif $campos[indice][5] eq 'BINARY'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} value="1"{else} value="0"{/if} {else} {if $campos[indice][10] neq 0} value="1" {else} value="0" {/if} {/if}/>
												<input type="checkbox" name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} disabled="disabled"/>
											{else}
												<input type="checkbox" value="1" name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} onclick="{$campos[indice][16]}"/>
											{/if}
			<!-- 7.1.7. Condicion si el tipo de campo es COMBO-->
										{elseif $campos[indice][5] eq 'COMBO'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<select  name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" disabled="disabled">
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}"/>
											{else}
												<select  name="{$campos[indice][2]}" id="{$campos[indice][2]}" onclick="{$campos[indice][16]}" class="{$campos[indice][11]}" {if isset($campos[indice][29]) or isset($campos[indice][17])}onchange="{if isset($campos[indice][29])}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}{if isset($campos[indice][17])}{$campos[indice][17]}{/if};"{/if}>
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>

											{/if}
			<!-- 7.1.8. Condicion si el tipo de campo es BUSCADOR-->
										{elseif $campos[indice][5] eq 'BUSCADOR'}
											{if $campos[indice][8] eq 1 && $tipo neq 2 && $tipo neq 3}
												<table>
													<tr>
														<td>
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" onkeyup="activaBuscador('{$campos[indice][2]}',event);" onclick="ocultaCombobusc('{$campos[indice][2]}')" autocomplete="off"  on_change="{$campos[indice][17]}"/>
														</td>
														<td>
												<!--<img onclick="botonBuscador('{$campos[indice][2]}')" src="{$rooturl}img/flecha_abajo.gif" style="height:12px;" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" />-->
														</td>
													</tr>
												</table>
												<div id="{$campos[indice][2]}_div" style="visibility:hidden; display:none; position:absolute; z-index:3;">
													<select id="{$campos[indice][2]}_sel" size="4" onclick="asignavalorbusc('{$campos[indice][2]}');{if $campos[indice][29] neq ''}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}" onkeydown="teclaCombo('{$campos[indice][2]}',event)" datosDB="getBuscador.php?id={$campos[indice][0]}">
														<option>						</option>
													</select>
												</div>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}">
											{else}
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" />
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}">
											{/if}
			<!-- 7.1.9. Condicion si el tipo de campo es FILE-->
										{elseif $campos[indice][5] eq 'FILE'}
											{if $campos[indice][10] neq ''}
												<a href="{$campos[indice][10]}" target="_blank" class="texto_form">Ver documento</a>
											{/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												&nbsp;
											{else}
												<input type="file" id="{$campos[indice][2]}" class="{$campos[indice][11]}" name="{$campos[indice][2]}" size="{$campos[indice][12]}"/>
											{/if}
											{if $campos[indice][27] neq ''}
											    <br>
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                            {/if}
										{/if}
										</td>
		<!-- 7.2. Condicion si son 4 campos-->
									{elseif $no_campos eq 4}
										<td width="69"  class="texto_form">{$campos[indice][3]}</td>
										<td width="175">
			<!-- 7.2.1. Condicion si el tipo de campo es CHAR-->
										{if $campos[indice][5] eq 'CHAR'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if $campos[indice][12] > 60}
												<textarea name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if}>{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}</textarea>
											{else}
												<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if}/>
											{/if}
										{elseif $campos[indice][5] eq 'DATE'}
			<!-- 7.2.2. Condicion si el tipo de campo es DATE-->
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="10" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else}onfocus="calendario(this)"{/if} />
											<span class="text_legend">yy-mm-dd</span>
			<!-- 7.2.3. Condicion si el tipo de campo es TIME-->
										{elseif $campos[indice][5] eq 'TIME'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="8" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else} onkeypress="return validaTime(event, this.id)"{/if} />
											<span class="text_legend">hh:mm:ss</span>
			<!-- 7.2.4. Condicion si el tipo de campo es INT o FLOAT-->
										{elseif $campos[indice][5] eq 'INT' or $campos[indice][5] eq 'FLOAT'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} onkeypress="return validarNumero(event,{if $campos[indice][5] eq 'FLOAT'}1{else}0{/if},id);" onblur="{$campos[indice][15]}"/>
			<!-- 7.2.5. Condicion si el tipo de campo es PASSWORD-->
										{elseif $campos[indice][5] eq 'PASSWORD'}
                                            {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
                                            <input type="password" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} />
			<!-- 7.2.6. Condicion si el tipo de campo es BINARY-->
										{elseif $campos[indice][5] eq 'BINARY'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} value="1"{else} value="0"{/if} {else} {if $campos[indice][10] neq 0} value="1" {else} value="0" {/if} {/if}/>
												<input type="checkbox" name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} disabled="disabled"/>
											{else}
												<input type="checkbox" value="1" name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} onclick="{$campos[indice][16]}"/>
											{/if}
			<!-- 7.2.7. Condicion si el tipo de campo es COMBO-->
										{elseif $campos[indice][5] eq 'COMBO'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<select  name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" disabled="disabled">
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}"/>
											{else}
												<select  name="{$campos[indice][2]}" id="{$campos[indice][2]}" onclick="{$campos[indice][16]}" class="{$campos[indice][11]}" {if isset($campos[indice][29]) or isset($campos[indice][17])}onchange="{if isset($campos[indice][29])}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}{if isset($campos[indice][17])}{$campos[indice][17]}{/if};"{/if}>
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>
											{/if}
			<!-- 7.2.8. Condicion si el tipo de campo es BUSCADOR-->
										{elseif $campos[indice][5] eq 'BUSCADOR'}
											{if $campos[indice][8] eq 1 && $tipo neq 2 && $tipo neq 3}
												<table>
													<tr>
														<td>
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" onkeyup="activaBuscador('{$campos[indice][2]}',event);" onclick="ocultaCombobusc('{$campos[indice][2]}')" autocomplete="off"  on_change="{$campos[indice][17]}"/>
														</td>
														<td>
												<!--<img onclick="botonBuscador('{$campos[indice][2]}')" src="{$rooturl}img/flecha_abajo.gif" style="height:12px;" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" />-->
														</td>
													</tr>
												</table>
												<div id="{$campos[indice][2]}_div" style="visibility:hidden; display:none; position:absolute; z-index:3;">
													<select id="{$campos[indice][2]}_sel" size="4" onclick="asignavalorbusc('{$campos[indice][2]}');{if $campos[indice][29] neq ''}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}" onkeydown="teclaCombo('{$campos[indice][2]}',event)" datosDB="getBuscador.php?id={$campos[indice][0]}">
														<option>						</option>
													</select>
												</div>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}">
											{else}
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" />
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}">
											{/if}
			<!-- 7.2.9. Condicion si el tipo de campo es FILE-->
										{elseif $campos[indice][5] eq 'FILE'}
											{if $campos[indice][10] neq ''}
												<a href="{$campos[indice][10]}" target="_blank" class="texto_form">Ver documento</a>
											{/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												&nbsp;
											{else}
												<input type="file" id="{$campos[indice][2]}" class="{$campos[indice][11]}" name="{$campos[indice][2]}" size="{$campos[indice][12]}"/>
											{/if}
											{if $campos[indice][27] neq ''}
											    <br>
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                            {/if}
										{/if}
										</td>
		<!-- 7.3. Condicion si existe la variable %$smarty.section.indice.first%%-->
										{if $smarty.section.indice.first}
											 <td width="158">&nbsp;</td>
										     <td width="155" class="texto_form">{$campos2[indice][3]}</td>
											 <td width="193">
			<!-- 7.3.1. Condicion si el tipo de campo es CHAR-->
											 {if $campos2[indice][5] eq 'CHAR'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
											 	{if $campos2[indice][12] > 60}
													<textarea name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if}>{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}</textarea>
												{else}
													<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if}/>
												{/if}
			<!-- 7.3.2. Condicion si el tipo de campo es DATE-->
											{elseif $campos2[indice][5] eq 'DATE'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="10" maxlength="10" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}readonly=""{else}onfocus="calendario(this)"{/if} />
												<span class="text_legend">yy-mm-dd</span>
			<!-- 7.3.3. Condicion si el tipo de campo es TIME-->
											{elseif $campos2[indice][5] eq 'TIME'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="10" maxlength="8" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}readonly=""{else} onkeypress="return validaTime(event, this.id)"{/if} />
												<span class="text_legend">hh:mm:ss</span>
			<!-- 7.3.4. Condicion si el tipo de campo es INT O FLOAT-->
											{elseif $campos2[indice][5] eq 'INT' or $campos2[indice][5] eq 'FLOAT'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} onkeypress="return validarNumero(event,{if $campos2[indice][5] eq 'FLOAT'}1{else}0{/if},id);" onblur="{$campos2[indice][15]}"/>
			<!-- 7.3.5. Condicion si el tipo de campo es PASSWORD-->
											{elseif $campos2[indice][5] eq 'PASSWORD'}
                                                {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
                                                <input type="password" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} />
			<!-- 7.3.6. Condicion si el tipo de campo es BINARY-->
											{elseif $campos2[indice][5] eq 'BINARY'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} value="1"{else} value="0"{/if} {else} {if $campos2[indice][10] neq 0} value="1" {else} value="0" {/if} {/if}/>
													<input type="checkbox" name="{$campos2[indice][2]}_1" id="{$campos2[indice][2]}_1" class="{$campos2[indice][11]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} checked="checked" {/if} {else} {if $campos2[indice][10] neq 0} checked="checked" {/if} {/if} disabled="disabled"/>
												{else}
													<input type="checkbox" value="1" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} checked="checked" {/if} {else} {if $campos2[indice][10] neq 0} checked="checked" {/if} {/if} onclick="{$campos2[indice][16]}"/>
												{/if}
			<!-- 7.3.7. Condicion si el tipo de campo es COMBO-->
											{elseif $campos2[indice][5] eq 'COMBO'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}

													<select  name="{$campos2[indice][2]}_1" id="{$campos2[indice][2]}_1" class="{$campos2[indice][11]}" disabled="disabled">
														{if $tipo eq 0}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][9]}
														{else}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][10]}
														{/if}
													</select>
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}"/>
												{else}

													<select  name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" onclick="{$campos2[indice][16]}" class="{$campos2[indice][11]}" {if isset($campos[indice][29]) or isset($campos[indice][17])}onchange="{if isset($campos[indice][29])}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}{if isset($campos[indice][17])}{$campos[indice][17]}{/if};"{/if}>
														{if $tipo eq 0}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][9]}
														{else}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][10]}
														{/if}
													</select>
												{/if}
			<!-- 7.3.8. Condicion si el tipo de campo es BUSCADOR-->
											{elseif $campos2[indice][5] eq 'BUSCADOR'}
												{if $campos2[indice][8] eq 1 && $tipo neq 2 && $tipo neq 3}
													<table>
														<tr>
															<td>
													<input type="text" name="{$campos2[indice][2]}_txt" id="{$campos2[indice][2]}_txt" size="{$campos2[indice][12]}" class="{$campos2[indice][11]}" value="{$campos2[indice][25]}" onkeyup="activaBuscador('{$campos2[indice][2]}',event);" onclick="ocultaCombobusc('{$campos2[indice][2]}')" autocomplete="off"  on_change="{$campos2[indice][17]}"/>
															</td>
															<td>
													<!--<img onclick="botonBuscador('{$campos2[indice][2]}')" src="{$rooturl}img/flecha_abajo.gif" style="height:12px;" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" />-->
															</td>
														</tr>
													</table>
													<div id="{$campos2[indice][2]}_div" style="visibility:hidden; display:none; position:absolute; z-index:3;">
														<select id="{$campos2[indice][2]}_sel" size="4" onclick="asignavalorbusc('{$campos2[indice][2]}');{if $campos2[indice][29] neq ''}actualizaDependiente('{$campos2[indice][29]}', '{$campos2[indice][30]}', this.value, 'NO');{/if}" onkeydown="teclaCombo('{$campos2[indice][2]}',event)" datosDB="getBuscador.php?id={$campos2[indice][0]}">
															<option>						</option>
														</select>
													</div>
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}">
												{else}
													<input type="text" name="{$campos2[indice][2]}_txt" id="{$campos2[indice][2]}_txt" size="{$campos2[indice][12]}" class="{$campos2[indice][11]}" value="{$campos2[indice][25]}" />
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}">
												{/if}
			<!-- 7.3.9. Condicion si el tipo de campo es FILE-->
											{elseif $campos2[indice][5] eq 'FILE'}
												{if $campos2[indice][10] neq ''}
													<a href="{$campos2[indice][10]}" target="_blank" class="texto_form">Ver documento</a>
												{/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}
													&nbsp;
												{else}
													<input type="file" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" name="{$campos2[indice][2]}" size="{$campos2[indice][12]}"/>
												{/if}
												{if $campos2[indice][27] neq ''}
												    <br>
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                {/if}
											{/if}
											 </td>
										{/if}
		<!-- 7.4. Condicion si son mas de 4 campos-->
									{else}
										<td width="69" class="texto_form">{$campos[indice][3]}</td>
										<td width="175">
			<!-- 7.4.1. Condicion si el tipo de campo es CHAR-->
										{if $campos[indice][5] eq 'CHAR'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if $campos[indice][12] > 60}
												<textarea name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} tabindex="{$campos[indice][4]}">{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}</textarea>
											{else}
										<!--implementacion de {if $campos[indice][0] eq '467'}onkeyup="validaNoLista('this');"{/if} Oscar 21.02.2018 para agregar buscador en campos de tipo char-->
												<input type="text" {if $campos[indice][0] eq '467' && $tipo neq '2'}onkeyup="validaNoLista(this,event);" onchange="show_list_order_msg();"{/if}
												{if $campos[indice][0] eq '5' && $tipo neq '2'}onkeyup="validaLogin(this,event);"{/if} name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} tabindex="{$campos[indice][4]}"
											/*Modificacion Oscar 2021 para evitar comas en ubicacion matriz y proveedor*/
												{if $campos[indice][20] neq '' && $tipo neq '2'}
													onkeyup="{$campos[indice][20]}"
												{/if}
											/*Fin de cambio Oscar 2021*/

												/>
												{if $campos[indice][0] eq '467' || $campos[indice][0] eq '5'}
													<div style="position:relative;top:0px;border:1px solid;width:110%;background:white;
													height:100px;overflow:auto;display:none;" id="res_ord_lis"></div>
												{/if}
										<!--Fin de cambio-->
											{/if}
			<!-- 7.4.2. Condicion si el tipo de campo es DATE-->
										{elseif $campos[indice][5] eq 'DATE'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="10" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else}onfocus="calendario(this)"{/if} tabindex="{$campos[indice][4]}"/>
											<span class="text_legend">yy-mm-dd</span>
			<!-- 7.4.3. Condicion si el tipo de campo es TIME-->
										{elseif $campos[indice][5] eq 'TIME'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="10" maxlength="8" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}readonly=""{else} onkeypress="return validaTime(event, this.id)"{/if} tabindex="{$campos[indice][4]}"/>
											<span class="text_legend">hh:mm:ss</span>
			<!-- 7.4.4. Condicion si el tipo de campo es INT o FLOAT-->
										{elseif $campos[indice][5] eq 'INT' or $campos[indice][5] eq 'FLOAT'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											<input type="text" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} onkeypress="return validarNumero(event,{if $campos[indice][5] eq 'FLOAT'}1{else}0{/if},id);" tabindex="{$campos[indice][4]}" onblur="{$campos[indice][15]}"/>
			<!-- 7.4.5. Condicion si el tipo de campo es PASSWORD-->
										{elseif $campos[indice][5] eq 'PASSWORD'}
                                            {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
                                            <input type="password" name="{$campos[indice][2]}" id="{$campos[indice][2]}" size="{$campos[indice][12]}" maxlength="{$campos[indice][21]}" class="{$campos[indice][11]}" {if $tipo eq 0} value="{$campos[indice][9]}" {else} value="{$campos[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0} readonly="" {/if} />
			<!-- 7.4.6. Condicion si el tipo de campo es BINARY-->
										{elseif $campos[indice][5] eq 'BINARY'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} value="1"{else} value="0"{/if} {else} {if $campos[indice][10] neq 0} value="1" {else} value="0" {/if} {/if}/>
												<input type="checkbox" name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} disabled="disabled" />
											{else}
												<input type="checkbox" value="1" name="{$campos[indice][2]}" id="{$campos[indice][2]}" class="{$campos[indice][11]}" {if $tipo eq 0} {if $campos[indice][9] neq 0} checked="checked" {/if} {else} {if $campos[indice][10] neq 0} checked="checked" {/if} {/if} tabindex="{$campos[indice][4]}" onclick="{$campos[indice][16]}"/>
											{/if}
			<!-- 7.4.7. Condicion si el tipo de campo es COMBO-->
										{elseif $campos[indice][5] eq 'COMBO'}
										    {if $campos[indice][27] neq ''}
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                                <br />
                                            {/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												<select  name="{$campos[indice][2]}_1" id="{$campos[indice][2]}_1" class="{$campos[indice][11]}" disabled="disabled">
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}"/>
											{else}
										<!--EXCEPCION : Implementacion Oscar 17.09.2019 para no poder editar el campo de proveedor en OC al ser edicion-->
												<select  name="{$campos[indice][2]}" id="{$campos[indice][2]}" onclick="{$campos[indice][16]}" class="{$campos[indice][11]}" tabindex="{$campos[indice][4]}" {if isset($campos[indice][29]) or isset($campos[indice][17])}onchange="{if isset($campos[indice][29])}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}{if isset($campos[indice][17])}{$campos[indice][17]}{/if};"{/if}>
													<!--
												{if $campos[indice][0] eq '95' && ($tipo eq '1' or $tipo eq '2')} disabled="disabled"{/if}-->
													{if $tipo eq 0}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][9]}
													{else}
														{html_options values=$campos[indice][25][0] output=$campos[indice][25][1] selected=$campos[indice][10]}
													{/if}
												</select>
										<!--Fin de cambio Oscar 17.09.2019-->

											{/if}
			<!-- 7.4.8. Condicion si el tipo de campo es BUSCADOR-->
										{elseif $campos[indice][5] eq 'BUSCADOR'}
											{if $campos[indice][8] eq 1 && $tipo neq 2 && $tipo neq 3}
												<table>
													<tr>
														<td>
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" onkeyup="activaBuscador('{$campos[indice][2]}',event);" onclick="ocultaCombobusc('{$campos[indice][2]}')" autocomplete="off"  on_change="{$campos[indice][17]}"/>
														</td>
														<td>
												<!--<img onclick="botonBuscador('{$campos[indice][2]}')" src="{$rooturl}img/flecha_abajo.gif" style="height:12px;" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" />-->
														</td>
													</tr>
												</table>
												<div id="{$campos[indice][2]}_div" style="visibility:hidden; display:none; position:absolute; z-index:3;">
													<select id="{$campos[indice][2]}_sel" size="4" onclick="asignavalorbusc('{$campos[indice][2]}');{if $campos[indice][29] neq ''}actualizaDependiente('{$campos[indice][29]}', '{$campos[indice][30]}', this.value, 'NO');{/if}" onkeydown="teclaCombo('{$campos[indice][2]}',event)" datosDB="getBuscador.php?id={$campos[indice][0]}">
														<option>						</option>
													</select>
												</div>
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}" >
											{else}
												<input type="text" name="{$campos[indice][2]}_txt" id="{$campos[indice][2]}_txt" size="{$campos[indice][12]}" class="{$campos[indice][11]}" value="{$campos[indice][25]}" readonly />
												<input type="hidden" name="{$campos[indice][2]}" id="{$campos[indice][2]}" value="{if $tipo eq 0}{$campos[indice][9]}{else}{$campos[indice][10]}{/if}">
											{/if}
			<!-- 7.4.9. Condicion si el tipo de campo es FILE-->
										{elseif $campos[indice][5] eq 'FILE'}

											{if $campos[indice][10] neq ''}
												<a href="{$campos[indice][10]}" target="_blank" class="texto_form">Ver documento</a>
											{/if}
											{if ($tipo eq 2 or $tipo eq 3) or $campos[indice][8] eq 0}
												&nbsp;
											{else}
												<input type="file" id="{$campos[indice][2]}" class="{$campos[indice][11]}" name="{$campos[indice][2]}" size="{$campos[indice][12]}"/>
											{/if}
											{if $campos[indice][27] neq ''}
											     <br>
                                                <span class="{$campos[indice][28]}">({$campos[indice][27]})</span>
                                            {/if}
										{/if}
										</td>
		<!-- 7.5 Condiciones si el campo display es !='' -->
										{if $campos2[indice][3] neq ''}
											<td width="158">&nbsp;</td>
										    <td width="155" class="texto_form">{$campos2[indice][3]}</td>
											<td width="193" {if $campos2[indice][0] eq '466'}align="center"{/if}>
											 {if $campos2[indice][5] eq 'CHAR'}
			<!-- 7.5.1. Condicion si el tipo de campo es CHAR-->
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
											 	{if $campos2[indice][12] > 60}
													<textarea name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} tabindex="{$campos2[indice][4]}">{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}</textarea>
												{else}
													<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} tabindex="{$campos2[indice][4]}" {if $campos2[indice][0] eq '466'}onkeyup="crea_previo_etiqueta();"{/if}/>
					<!-- Implementacion Oscar 2023 para agregar botón de cambio de prefijo-->
													{if $campos2[indice][0] eq '945' }
														</td>
														<td>
															<button
																type="button"
																class="btn btn-success"
																onclick="getRenewProductProviderBarcodePrefix();"
															>
																<i class="icon-ok-circle">Cambiar código Único </i>
															</button>
													{/if}
					<!-- Fin de cambio Oscar 2023 -->
												{/if}
											<!--Excepcion : Implementacion Oscar 19.02.2019 para previo de etiqueta de productos-->
												{if $campos2[indice][0] eq '466'}
													<div id="previo_etiqueta" style="position:relative;top: 0;border:4px solid blue;width:400px;background:white;
													height:190px;overflow:none;margin:5px;display: none;">
													</div>
													{literal}
													<script type="text/javascript">
														function crea_previo_etiqueta(){
															if($("#nombre_etiqueta").val().length<=5){
																$("#previo_etiqueta").css("display","none");
																return false;
															}
															var ajax_previo_etq=ajaxR("../ajax/previoEtiquetaProducto.php?datos_etiqueta="+$("#nombre_etiqueta").val()+
																"&ord_lsta="+$("#orden_lista").val()+"&id_prod="+$("#id_productos").val());
															//var arr_mq=es_pd_mq.split("|");
															$("#previo_etiqueta").html(ajax_previo_etq);
															$("#previo_etiqueta").css("display","block");
														}
													</script>
													{/literal}
												{/if}
											<!--Fin de Cambio Oscar 19.02.2018-->
			<!-- 7.5.2. Condicion si el tipo de campo es DATE-->
											{elseif $campos2[indice][5] eq 'DATE'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="10" maxlength="10" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}readonly=""{else}onfocus="calendario(this)"{/if} tabindex="{$campos2[indice][4]}"/>
												<span class="text_legend">yy-mm-dd</span>
			<!-- 7.5.3. Condicion si el tipo de campo es TIME-->
											{elseif $campos2[indice][5] eq 'TIME'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="10" maxlength="8" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}readonly=""{else} onkeypress="return validaTime(event, this.id)"{/if} tabindex="{$campos2[indice][4]}"/>
												<span class="text_legend">hh:mm:ss</span>
			<!-- 7.5.4. Condicion si el tipo de campo es INT o FLOAT-->
											{elseif $campos2[indice][5] eq 'INT' or $campos2[indice][5] eq 'FLOAT'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
                                        <!-- Excepcion : Implementacion Oscar 27.02.2018 se agrega if id eq 615 or 617 para hacer cambio de tipo de pago de usuario-->
												<input type="text" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} onkeypress="return validarNumero(event,{if $campos2[indice][5] eq 'FLOAT'}1{else}0{/if},id);" tabindex="{$campos2[indice][4]}" onblur="{$campos2[indice][15]}"
												{if ($campos2[indice][0] eq '615' OR $campos2[indice][0] eq '617')} onclick="{$campos2[indice][16]}" {/if}/>
										<!--fin de cambio 27.02.2018-->
			<!-- 7.5.5. Condicion si el tipo de campo es PASSWORD-->
											{elseif $campos2[indice][5] eq 'PASSWORD'}
                                                {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
                                                <input type="password" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" size="{$campos2[indice][12]}" maxlength="{$campos2[indice][21]}" class="{$campos2[indice][11]}" {if $tipo eq 0} value="{$campos2[indice][9]}" {else} value="{$campos2[indice][10]}" {/if} {if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0} readonly="" {/if} />
			<!-- 7.5.6. Condicion si el tipo de campo es BINARY-->
											{elseif $campos2[indice][5] eq 'BINARY'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})
                                                    	{$campos2[indice][0]}
											   
                                                    </span>
                                                    <br />
                                                {/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} value="1"{else} value="0"{/if} {else} {if $campos2[indice][10] neq 0} value="1" {else} value="0" {/if} {/if}/>
													<input type="checkbox" name="{$campos2[indice][2]}_1" id="{$campos2[indice][2]}_1" class="{$campos2[indice][11]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} checked="checked" {/if} {else} {if $campos2[indice][10] neq 0} checked="checked" {/if} {/if} disabled="disabled" />
												{else}
													<input type="checkbox" value="1" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" {if $tipo eq 0} {if $campos2[indice][9] neq 0} checked="checked" {/if} {else} {if $campos2[indice][10] neq 0} checked="checked" {/if} {/if} tabindex="{$campos2[indice][4]}" onclick="{$campos2[indice][16]}"/>
												{/if} 
										<!-- Implemntacion Oscar 2022 para boton de ayuda de impresion etiquetas de piezas maquiladas -->
												{if $campos2[indice][0] eq '975'}
											    	<button 
											    		type="button" 
											    		class="btn btn-success"
											    		onclick="get_maquile_configuration_info();"
											    		style="border-radius : 50%;"
											    	>
											    		?
											    	</button>
											    {/if}
										<!-- fin de cambio -->
			<!-- 7.5.7. Condicion si el tipo de campo es COMBO-->
											{elseif $campos2[indice][5] eq 'COMBO'}
											    {if $campos2[indice][27] neq ''}
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                    <br />
                                                {/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}
													<select  name="{$campos2[indice][2]}_1" id="{$campos2[indice][2]}_1" class="{$campos2[indice][11]}" disabled="disabled">
														{if $tipo eq 0}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][9]}
														{else}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][10]}
														{/if}
													</select>
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}"/>
												{else}
													<!--valor 29:{$campos2[indice][29]}-->
													<select  name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" onclick="{$campos2[indice][16]}" class="{$campos2[indice][11]}" tabindex="{$campos2[indice][4]}" {if isset($campos2[indice][29]) or isset($campos2[indice][17])}onchange="{if isset($campos2[indice][29])}actualizaDependiente('{$campos2[indice][29]}', '{$campos2[indice][30]}', this.value, 'NO');{/if}{if isset($campos2[indice][17])}{$campos2[indice][17]}{/if};"{/if}>
														{if $tipo eq 0}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][9]}
														{else}
															{html_options values=$campos2[indice][25][0] output=$campos2[indice][25][1] selected=$campos2[indice][10]}
														{/if}
													</select>
												{/if}
			<!-- 7.5.8. Condicion si el tipo de campo es BUSCADOR-->
											{elseif $campos2[indice][5] eq 'BUSCADOR'}
												{if $campos2[indice][8] eq 1 && $tipo neq 2 && $tipo neq 3}
													<table>
														<tr>
															<td>
													<input type="text" name="{$campos2[indice][2]}_txt" id="{$campos2[indice][2]}_txt" size="{$campos2[indice][12]}" class="{$campos2[indice][11]}" value="{$campos2[indice][25]}" onkeyup="activaBuscador('{$campos2[indice][2]}',event);" onclick="ocultaCombobusc('{$campos2[indice][2]}')" autocomplete="off" on_change="{$campos2[indice][17]}"/>
															</td>
															<td>
													<!--<img onclick="botonBuscador('{$campos2[indice][2]}')" src="{$rooturl}img/flecha_abajo.gif" style="height:12px;" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" />-->
															</td>
														</tr>
													</table>
													<div id="{$campos2[indice][2]}_div" style="visibility:hidden; display:none; position:absolute; z-index:3;">
														<select id="{$campos2[indice][2]}_sel" size="4" onclick="asignavalorbusc('{$campos2[indice][2]}');{if $campos2[indice][29] neq ''}actualizaDependiente('{$campos2[indice][29]}', '{$campos2[indice][30]}', this.value, 'NO');{/if}" onkeydown="teclaCombo('{$campos2[indice][2]}',event)" datosDB="getBuscador.php?id={$campos2[indice][0]}">
															<option>						</option>
														</select>
													</div>
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}">
												{else}
													<input type="text" name="{$campos2[indice][2]}_txt" id="{$campos2[indice][2]}_txt" size="{$campos2[indice][12]}" class="{$campos2[indice][11]}" value="{$campos2[indice][25]}" />
													<input type="hidden" name="{$campos2[indice][2]}" id="{$campos2[indice][2]}" value="{if $tipo eq 0}{$campos2[indice][9]}{else}{$campos2[indice][10]}{/if}">
												{/if}
			<!-- 7.5.9. Condicion si el tipo de campo es FILE-->
											{elseif $campos2[indice][5] eq 'FILE'}
												{if $campos2[indice][10] neq ''}
													<a href="{$campos2[indice][10]}" target="_blank" class="texto_form">Ver documento</a>
												{/if}
												{if ($tipo eq 2 or $tipo eq 3) or $campos2[indice][8] eq 0}
													&nbsp;
												{else}
													<input type="file" id="{$campos2[indice][2]}" class="{$campos2[indice][11]}" name="{$campos2[indice][2]}" size="{$campos2[indice][12]}"/>
												{/if}
												{if $campos2[indice][27] neq ''}
												    <br>
                                                    <span class="{$campos2[indice][28]}">({$campos2[indice][27]})</span>
                                                {/if}
											{/if}
											 </td>
										{/if}
									{/if}
								</tr>
	<!-- 8. Fin de iteracion de campos visibles -->
							{/section}
						</table>
	<!-- 9. Iteracion de campos no visibles -->
						{section loop=$datosInvisibles name=indice}
							<input type="hidden" name="{$datosInvisibles[indice][1]}" id="{$datosInvisibles[indice][1]}" value="{if $tipo eq 0}{$datosInvisibles[indice][3]}{else}{$datosInvisibles[indice][4]}{/if}"/>
						{/section}
                 	</div>
				{/if}
	<!-- 10. Seccion de botones de opciones-->
               <div class="Botones">
               	<!-- Excepcion : Implementacion para exportar/importar lista de precios -->
               		{if $tabla eq 'ec_precios'}
               			<a href="#" class="fl" title="Exportar" onclick="window.open('../especiales/listaCSV.php?id_precio={$llave}')">Exportar </a>
               			<a href="#" class="fr" title="Importar" onclick="location.href='../especiales/importaCSV.php?id_precio={$llave}'">Importar </a>
               			<a href="#" class="fl" title="Exportar para mayoreo"
               			onclick='window.open("../especiales/listaCSV.php?id_precio={$llave}&#x26para_mayoreo=1")'>Exportar<br>Mayoreo</a>
               		{/if}

               	<!-- Excepcion : Implementacion para exportar/importar estacionalidades -->
               		{if $tabla eq 'ec_estacionalidad'}
               			<a href="#" class="fl" title="Exportar" onclick="window.open('../especiales/listaEstCSV.php?id_estacionalidad={$llave}')">Exportar </a>
               			<a href="#" class="fr" title="Importar" onclick="location.href='../especiales/importaEstCSV.php?id_estacionalidad={$llave}'">Importar </a>
               		{/if}

               	<!-- No se usa -->
                    {if $tabla eq 'ec_autorizacion' && $tipo == 1}

						<a href="#"  class="fr b" title="Rechazar"  onclick="document.getElementById('autorizado').checked=false;valida()">Rechazar</a>
                    	<a href="#"  class="fr b" title="Autorizar"  onclick="document.getElementById('autorizado').checked=true;valida()">Autorizar</a>

                    {/if}

               </div>

<!-- 11. Implementacion de ventana emergente para avisos Oscar 11.04.2018-->
	<div id="ventana_emergente_global" style="position:absolute;z-index:1000;width:100%;height:250%;background:rgba(0,0,0,.8);top:0;left:0;display:none;">
		<p align="right"><img src="../../img/especiales/cierra.png" height="50px" onclick="document.getElementById('ventana_emergente_global').style.display='none';" id="btn_cerrar_emergente_global"></p>
		<p id="contenido_emergente_global"></p><!--En este div se cargan los datos o avisos que se quieren mostrar en pantalla-->
	</div>

<!-- Implementación Oscar 2022 -->
	<div class="emergente">
		<div class="row">
			<!--div class="col-1"></div-->
			<div class="col-12 emergent_content"></div>
			<!--div class="col-1"-->
				<button 
					type="button" 
					class="emrgent_btn_close"
					onclick="close_emergent();"
				>
					X
				</button>
			<!--/div-->
		</div>
	</div>
{literal}
	<style type="text/css">
		.emergente{
			position: fixed;
			top : 0;
			left: 0;
			width: 100% !important;
			height: 100%;
			background: rgba( 0,0,0,.7);
			z-index: 3000;
			vertical-align: middle !important;
			display: none;
		}
		.emergente>.row{
			position: relative;
			top: 10%;
			height: 80%;
		}

		.emergente>.row>.col-12{
			text-align: center;
			background-color: white;
			overflow: scroll;
		}
		.emrgent_btn_close{
			background-color: rgba( 225,0,0,.9);
			color: white;
			font-size: 20px;
			position: absolute;
			right: 7px;
			top : -36px;
			width: 5%;
		}
		.emergent_content{
			font-size: 120%;	
		}
		.label_for_models{
			margin-left: 20px;
			font-size: 120%;
		}
		.porc_10_inline{
			position: relative;
			display: inline-block !important;
			width: 9% !important;
		}
		.porc_80_inline{
			position: relative;
			display: inline-block !important;
			width: 79% !important;
		}
		.btn{
			padding: 10px;
			font-size: 110%;
			margin-left : 10%;
		}
		.btn-success{
			background-color: green;
			color: white;
		}
		.btn-danger{
			background-color: red;
			color: white;
		}
	</style>

{/literal}

<!-- Fin de implememtacion Oscar 2022 -->
<!--Fin de implementacion de ventana emergenete-->


<!-- 12. Seccion de grids-->
	{section loop=$gridArray name=x}
		<input type="hidden" name="file{$gridArray[x][1]}" value="">
	<!-- Impementacion Oscar 17-09-2020 para insertar separarador de venta en linea productos-->
    		{if $gridArray[x][0] eq '60'}
    			{include file="general/seccionProductosLinea.tpl"}
			{/if}
    <!-- Fin de cambio Oscar 17-09-2020 -->

		<div id="bg_seccion">		
    		<div class="name_module" align="center">
    

    			<table>
					<tr valign="middle">
						<td>
							<p class="margen" id="desp_{$smarty.section.x.index}" onClick="despliega(1,{$smarty.section.x.index});">{$gridArray[x][2]}
							<i id="desp_{$smarty.section.x.index}" class="icon-down-open" 
								style="border-radius : 50%; padding : 5px; position:absolute; right:10%;"></i><p>
							<!--img src="../../img/especiales/add.png" id="desp_{$smarty.section.x.index}" width="35px" style="top:20px;position:relative;padding:10px;"></p--></td>

					</tr>
				</table>
    		</div>
    <!-- 12.1. Implementacion de Oscar 12/02/2018 Para buscador de grids-->
    	{if $gridArray[x][21] eq '1'}<!--condicionamos que solo muestre buscador si el grid asi lo marca en la BD  && ($tipo eq '0' || $tipo eq '1')-->
    		<br><br>
			<div style="border:0;display:none;" id="div_busc_grid_{$smarty.section.x.index}">
				<span align="left" style="position:absolute;padding:0; font-size:15px; width : 300px;"><b>Buscador:</b></span>
				<input type="text" id="b_g_{$smarty.section.x.index}" style="width:50%;" onkeyup="activa_buscador_general(this,'{$smarty.section.x.index}','{$tabla}','{$no_tabla}',event ,'{$gridArray[x][0]}', '{$gridArray[x][1]}');">
				<input type="text" id="cantidad_{$smarty.section.x.index}" style="width:3%;{if $gridArray[x][0] eq '43' || $gridArray[x][0] eq '24'}display:none;{/if}" onkeyup="validarEv(event,{$smarty.section.x.index});">
				{if $gridArray[x][0] eq '43' || $gridArray[x][0] eq '24'}
					<img src="../../img/busca_gral.png" height="50px;" style="top:17px;position:relative;" id="img_add_{$smarty.section.x.index}">
				{else}
					<img src="../../img/icono-agregar.png" height="50px;" style="top:17px;position:relative;" id="img_add_{$smarty.section.x.index}">
				{/if}
				<!--<input type="button" value="agregar" id="img_add_{$smarty.section.x.index}">-->
				<div style="width:51.5%;height:200px;background:white;left:0px;position:relative;border:1px solid;display:none;overflow:auto;" id="res_bus_glob_{$smarty.section.x.index}"></div>
				<input type="hidden" value="" id="aux_1_{$smarty.section.x.index}"><!--id-->
				<input type="hidden" value="" id="aux_2_{$smarty.section.x.index}"><!--descripcion-->
			{if $gridArray[x][0] eq '9'}
				<div class="row">
					<div class="col-6">
						<div class="input-group">
							<input type="text" class="form-control" placeholder="Buscador por codigo de barras"
								onkeyup="seekProductProviderByBarcode( event )" id="barcode_seeker"
							>
							<button
								type="button"
								class="btn btn-warning"
							>
								<i class="icon-barcode"></i>
							</button>
						</div>
					</div>
				</div>
			{/if}
		</div>

	<!--Implementación de Oscar 16.05.2018 para exportar/importar lista de estacionalidades
	{if $tabla eq 'ec_estacionalidad' && $no_tabla eq '0'}
		<div style="position:absolute;bottom:-40%;z-index:3;">
			<input type="button" id="bot_imp_estac" onclick="importa_exporta_estacionalidades(2);" value="Importar estacionalidad" style="padding:5px;border-radius:5px;">
			<input type="button"  onclick="importa_exporta_estacionalidades(1);" value="Exportar estacionalidad"style="padding:5px;border-radius:5px;">

			<form class="form-inline">
				<input type="file" id="imp_csv_prd" style="display:none;">
				<p class="nom_csv">
    				<input type="text" id="txt_info_csv" style="display:none;" disabled>
    			</p>
    			<input type="button" id="submit-file" style="display:none;" class="bot_imp" value="Enviar">
			</form>
		</div>
	{/if}
	fin de implementación OScar 16.05.2018-->


			{literal}
		<!--/*********************Implementación de importar/exportar estacionalidades con excel Oscar 16.05.2018******************************************************/-->
			<!--incluimos libreria para poner csv en temporal-->
				<script type="text/JavaScript">
    // 12.2. Funcion que realiza la busqueda de registros por medio de archivo /code/ajax/buscadorGlobal.php
					var tmp_busc="";
					function activa_buscador_general(t,nu,ta,nt,e, grid_id, grid_nombre){//Se agrega grid_id, grid_nombre Oscar 17-09-2020
						if(e.keyCode==40){
							if($('#r_1')){
								resalta_busc(0,1);
							}
							return false;
						}
					{/literal}
						/*var id_gr='{$gridArray[x][0]}';//capturamos el id del grid
						var posic='{$gridArray[x][1]}';//capturamos el nombre del grid*/
			/*Implementacion Oscar 2020 para obtener valor de llave primaria de la pantalla*/
						var llave_primaria = '{$llave}';
						//alert('{$tipo}');
					{literal}
				//Excepcion : Implementacion Oscar 09.09.2019 para mandar el id de proveedor en el buscador de detalle de ordenes de compra
						var id_condicion="";
						if(grid_id == 5){
							id_condicion=$("#id_proveedor").val();
						}
				/**/
					//sacamos las filas existentes en el grid
						var fil_ex=($('#'+ grid_nombre +' tr').length-5);
						//alert(t.value+', '+ta+', '+nt+', '+id_gr+posic);
					//sacamos valor del buscador
						var txt_busc=t.value;
						if(txt_busc.length<3){
							$("#res_bus_glob_"+nu).css("display","none");
							return false;
						}
						$.ajax({
							type:'post',
							url:'../ajax/buscadorGlobal.php',
							cache:false,
							data:{clave:txt_busc,tabla:ta,no_t:nt, id : grid_id, fil_exist:fil_ex,n_d:nu,
								grid_nom : grid_nombre, id_cond:id_condicion,
								llave_principal : llave_primaria/*Implementacion Oscar 2020 para enviar valor de llave primaria de la pantalla*/
							},
							success:function(datos){
								var respuesta=datos.split('|');
								if(respuesta[0]!='ok'){
									alert('Error!!!....\n'+datos+'   '+txt_busc);
									return false;
								}else{
									//alert('ok');
									$("#res_bus_glob_"+nu).html(respuesta[1]);
									$("#res_bus_glob_"+nu).css("display","block");
								}
							}
						});
					}
    // 12.3. Funcion que valida accion de teclas sobre opciones
					function eje(e,num_res,id_opc){
						//alert(e.keyCode+num_res+id_opc);
							//alert(e.keyCode);
							if(e.keyCode==40){
								if($("#r_"+num_res)){
									resalta_busc(num_res,1);
								}
							}
							if(e.keyCode==38){
								if($("#r_"+num_res)){
									resalta_busc(num_res,-1);
								}
							}
							if(e.keyCode==13){
								document.getElementById("r_"+num_res).click();
							}
							return true;
					}

	//12.4 Funcion que resalta hover
					function resalta_busc(actual,flag){
						var nvo=actual+(flag);
					//alert(nvo);
					//
						if(actual>0){
							$("#r_"+actual).css("background","white");
						}
						if(actual==1&&flag==-1){
							$("#b_g_0").select();
						}
						$("#r_"+nvo).css("background","#6BFF33");
						$("#r_"+nvo).focus();
						return false;
					}

	//12.5. Funcion que pone producto en buscador y enfoca a cantidad
					function insertaBuscador(n_b, valores, grid_id, grid_nombre){
						//alert(n_b+"\n"+valores);
						if(valores=="" || valores==null){
							alert('No hay valores válidos');
							return false;
						}
						$("#res_bus_glob_"+n_b).html('');//limpiamos resultados
						$("#res_bus_glob_"+n_b).css("display","none");//ocultamos div de resultados
						$("#cantidad_"+n_b).val("1");//asignamos uno por default a cantidad
						$("#cantidad_"+n_b).select();
					//preparamos el boton para agregar producto seleccionado
						{/literal}
						/*'{assign var="count_tmp" value="'+n_b+'"}';
						alert('{$count_tmp}');
						var id_gr='{$gridArray[count_tmp][0]}';//capturamos el id del grid
						var posic='{$gridArray[count_tmp][1]}';//capturamos el nombre del grid
						alert(posic);*/
					{literal}
				//validamos que el registro no este en el grid
						var valida_gr=validaRegGrid(grid_id,n_b,grid_nombre);
						if(valida_gr=='1'){
//							alert('El registro ya se encuentra en el grid...');
							return false;
						}else{
							//alert('no esta');
						//asignamos valores al buscador
							document.getElementById("b_g_"+n_b).value=valores;
						//descomponemos descripcion de buscador
							var arr=document.getElementById('b_g_'+n_b).value.split("°");
						/***********************************implementación de confirmación de movimiento de almacen prod c/maquila Oscar 11.04.2018*/
						//alert( 'here' );
							if(grid_id == 9){//si es el grid de movimientos de almacén entra al proceso de validación...
								var es_pd_mq=ajaxR("../ajax/validaMovProdMaq.php?id_pr="+arr[0]);
								var arr_mq=es_pd_mq.split("|");
								if(arr_mq[0]=='ok'){
									if(arr_mq[1]=='maquilado'){
									//se informa al usuario que el productos es maquilado, y se pregunta si desea agregarlo
										var cf_mq=confirm(arr_mq[2]);
										if(cf_mq==false){
											$("#b_g_0").val("");
											$("#b_g_0").focus();
											return false;
										}
									}
								}else{
									alert("Error:\n"+es_pd_mq);
									return false;
								}
							}
						//fin de implementación 11.04.2018
					//sacamos las filas existentes en el grid
						var fil_ex=($('#'+grid_nombre+' tr').length-5);
						$.ajax({
							type:'post',
							url:'../ajax/buscadorGlobal.php',
							cache:false,
							data:{flag:1,id : grid_id, fil_exist:fil_ex,clave:arr[1],n_d:n_b},
							success:function(dat){
								var resul=dat.split("|");
								if(resul[0]!='ok'){
									alert("Error!!!\n\n"+dat);
								}else{
									$("#img_add_"+n_b).attr("onclick",resul[2]);
									return 'ok';
								}
							}
							});
						}
						//alert("ya cambió evento");
						//document.getElementById('img_add_'+n_b).style.display="none";
					}

	//12.6. Funcion que valida evento click o intro
					function validarEv(e,nu_bus){
						if(e.keyCode==13||e=='click'){
							$("#img_add_"+nu_bus).click();
						}

					}
	//12.7. Funcion que valida si el registro ya existe en en grid
					function validaRegGrid(id_g,n_b,posic){
						//alert(id_g+":"+n_b+":"+posic);
						$.ajax({
							type:'post',
							url:'../ajax/buscadorGlobal.php',
							cache:false,
							data:{id:id_g,flag:-2},
							success:function(dat){
								//alert('dat: '+dat);
								var arr_re=dat.split("|");
								if(arr_re[0]!='ok'){
									alert("Error al mandar validación de registro");
								}
							//sacamos el numero de registros
								var fil_ex=($('#'+posic+' tr').length-4);
								var arr=document.getElementById('b_g_'+n_b).value.split("°");
								for(var i=0;i<=fil_ex;i++){//se cambia menor o igual condicion del ciclo for Oscar 26.03.2018
									//alert(posic+'_'+arr_re[1]+"_"+i);
									if( id_g == 9 ){
										arr_re[1] = 8;
										arr[0] = arr[2];
									}

									if(document.getElementById(posic+"_"+arr_re[1]+"_"+i)){//campo a comparar
									//tmp=document.getElementById(posic+"_"+arr_re[1]+"_"+i).innerHTML;
									//tmp = $( posic+"_"+arr_re[1]+"_"+i ).prop( 'valor' );
										var tmp = celdaValorXY(posic, arr_re[1], i);//modificacion Oscar 2023 para obtener valor de celda a comparar
										if(tmp==arr[0]||tmp==arr[1]){
											$("#"+posic+"_"+arr_re[2]+"_"+i).click();
											//alert("resalta:"+"#"+posic+"_"+arr_re[2]+"_"+i);
											$("#"+posic+"_"+arr_re[2]+"_"+i).select();//#c
											$("#"+posic+"_"+arr_re[2]+"_"+i).focus();//#c
											var arr = $('#b_g_'+n_b).val( '' );//modificacion Oscar 2023 para limpiar buscador
											return '1';
										}
									}
								}//fin de for i
							//Excepcion 
								if(id_g==43 || id_g==24){
									alert("El producto no fue encontrado!!!");
									$("#cantidad_0").val('');
									$("#b_g_0").select();
								}
							}
						});
					}
				</script>
	<!-- 12.8. Estilos CSS del buscador de grids -->
				<style type="text/css">
					.opcion{
						height: 30px;
					}
					.opcion:hover{
						background:#6BFF33;
					}
				</style>
			{/literal}
		{/if}
	<!--Fin de Cambio 12-02-2017-->

	<!-- 12.9. Div que contiene a cada Grid -->
			<div style="border:0px solid;display:none;height:100px;z-index:100;" id="div_grid_{$smarty.section.x.index}" ><!--12.9.1. Implementacion de Oscar 31.07.2018 para no mostrar la informacion de todos los grids   (se quita la clase class="tablas-res_") position:relative;-->
		<!--Excepcion : para mostrar el buscador por codigo de barras en transferencias -->
				{if $tabla eq 'ec_transferencias' && $no_tabla eq '3'}

					{literal}
					<style>

						.buscaProductoBar{
							position:relative;
							top:-20px;
						}

						.inProductoBar{
							width:200px !important;
						}

					</style>

					<script>


						function validaBar(obj)
						{
							{/literal}
								var llave='{$llave}';
							{literal}


							var url="../ajax/validaProductoVer.php?id_transferencia="+llave+"&code="+obj.value;

							var res=ajaxR(url);

							var aux=res.split('|');

							if(aux[0] != 'exito')
							{
								alert(res);
								return false;
							}


							var id_prod=aux[1];


							var num=NumFilas('transferenciasProductos');

							for(var i=0;i<num;i++)
							{
								if(celdaValorXY('transferenciasProductos', 2, i) == id_prod)
								{
									aux=celdaValorXY('transferenciasProductos', 7, i);
									aux=parseInt(aux);
									aux++;
									valorXY('transferenciasProductos', 7, i, aux);
									htmlXY('transferenciasProductos', 7, i, aux);
								}
							}


							obj.value="";
							obj.focus();


						}

						function validaEntBar(eve, obj)
						{
							var key=0;
							key=(eve.which) ? eve.which : eve.keyCode;


							if(key == 13)
							{
								validaBar(obj);
							}

						}



					</script>



					{/literal}

					<div class="buscaProductoBar">
						Código de barras:
						<input type="text" name="codigo" class="inProductoBar" onkeyup="validaEntBar(event, this)">
						<input type="button" class="boton" onclick="validaBar(codigo)" value="Validar">
					</div>

				{/if}

		<!--Excepcion : para mostrar el buscador por codigo de barras en transferencias -->
				{if $tabla eq 'ec_transferencias' && $no_tabla eq '0'}


					{literal}
					<style>

						.buscaProductoBar{
							position:relative;
							top:-20px;
						}

						.inProductoBar{
							width:200px !important;
						}

					</style>

					<script>


						function validaBar(obj)
						{
							{/literal}
								var llave='{$llave}';
							{literal}


							var url="../ajax/validaProductoTrans.php?code="+obj.value;

							var res=ajaxR(url);

							var aux=res.split('|');

							if(aux[0] != 'exito')
							{
								alert(res);
								return false;
							}


							//alert(aux[2]);

							var id_prod=aux[1];
							var nver=0;

							var num=NumFilas('transferenciasProductos');

							for(var i=0;i<num;i++)
							{
								if(celdaValorXY('transferenciasProductos', 2, i) == id_prod)
								{
									aux=celdaValorXY('transferenciasProductos', 7, i);
									aux=parseInt(aux);
									aux++;
									valorXY('transferenciasProductos', 7, i, aux);
									htmlXY('transferenciasProductos', 7, i, aux);
									nver++;
								}
							}


							if(nver == 0)
							{
								InsertaFilaNoVal('transferenciasProductos');


								valorXYNoOnChange('transferenciasProductos', 2, num, aux[1]);
								valorXYNoOnChange('transferenciasProductos', 3, num, aux[2]);
								valorXY('transferenciasProductos', 6, num, -1);
								valorXY('transferenciasProductos', 10, num, 1);
								valorXY('transferenciasProductos', 7, num, 1);
								valorXY('transferenciasProductos', 8, num, 1);

								//htmlXY('transferenciasProductos', 3, num, aux[2]);

							}

							obj.value="";
							obj.focus();


						}

						function validaEntBar(eve, obj)
						{
							var key=0;
							key=(eve.which) ? eve.which : eve.keyCode;


							if(key == 13)
							{
								validaBar(obj);
							}

						}



					</script>



					{/literal}


					<div class="buscaProductoBar">
						C&oacute;digo de barras:
						<input type="text" name="codigo" class="inProductoBar" onkeyup="validaEntBar(event, this)">
						<input type="button" class="boton" onclick="validaBar(codigo)" value="Validar">
					</div>

				{/if}

				{if ($tabla64 eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $tipo eq '0') or (($tipo == 0 or $tipo == 1) and $gridArray[x][6] neq 'false')}

            	<div class="submenu">

					{if $tabla64 eq 'ZWNfdHJhbnNmZXJlbmNpYXM=NO' && $tipo eq '0'}
						<div class="productos-ojo" title="Mostrar producto">
							<img src="{$rooturl}/img/mproducto.png" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" onclick="creaListado()">
            				<p>Productos</p>
            			</div>
            		{/if}
	<!-- 12.10. Menu lateral boton para agregar fila en el grid-->
					{if ($tipo == 0 or $tipo == 1) and $gridArray[x][6] neq 'false'}
						<div class="Fila" title="clic para agregar un nuevo registro" onclick="InsertaFila('{$gridArray[x][1]}')">
							<p>Nueva Fila</p>
						</div>
					{/if}
				</div>

				{/if}

 				<!--Termina el menu lateral de los conetenidos-->

 			<div id="cosa" align="center"><!--style="display:none;"-->

	<!--Excepcion : Implementacion Oscar 22.11.2019 para mostrar el mensaje en el grid de pagos-->
				{if $gridArray[x][0] eq '7'}
					<p align="center" style="color:red;font-size:25px;">Si modifica uno de estos pagos no se verá reflejado; hay que modificarlo manualmente en los pagos a proveedor por partida</p>
				{/if}
	<!--Excepcion : Implementacion Oscar 22.11.2019 para mostrar el mensaje en el grid de pagos-->
	
				{if $gridArray[x][0] eq '65'}
					<p align="left" style="font-size:25px;">Imagen Principal</p>
					<div align="center" style="font-size:20px; position: relative; left:0; width:80%; border: 1px solid; padding: 10px;
					background:gray; color: white;">
						
						<label>Nombre : </label>
						<input type="text" id="nombre_img_principal_editable" style="width : 250px;"
						onchange="cambia_valor_nombre_img_principal();">
						
						<label>Extension : </label>
						<select id="formato_imagen_principal" style="padding:10px;"
						onchange="cambia_valor_nombre_img_principal();">
							{section loop=$formatos_imagenes name=x_formatos}
								{html_options values=$formatos_imagenes[x_formatos][0] output=$formatos_imagenes[x_formatos][1] }
							{/section}
						</select>
						<label>  </label>
						<input type="text" name="nombre_img_principal" id="nombre_img_principal" value="{$precios_especiales[7]}"
						style="width : 250px;" readonly placeholder="Nombre Completo">
						<label> <button type="button" onclick="limpia_valor_img_principal();">X</button> </label><br>
					</div>
					<p align="left" style="font-size:25px;">Imagenes Adicionales</p>
					{literal}
						<script type="text/javascript">
							function cambia_valor_nombre_img_principal(){
								var nom_img_princ = $("#nombre_img_principal_editable").val().trim();
								var nom_form_img_princ =  $("#formato_imagen_principal option:selected").text().trim();
								$("#nombre_img_principal").val( nom_img_princ + '.' + nom_form_img_princ);
							}
							function limpia_valor_img_principal(){
								if(!confirm("Realmente desea eliminar el valor de la imagen principal") ){
									return false;
								}
								$("#nombre_img_principal_editable").val('');
								$("#nombre_img_principal").val('');
							}
						</script>
					{/literal}

				{/if}

	<!--Fin de cambio Oscar 22.11.2019-->
 		<!-- 12.11. Tabla del grid (contiene datos de cabecera y configuracion del grid)-->
 			<!--12.11.1. Se implementa {$filtro_fechas_1} en el atributo datos para filtrar grid por rango Oscar 14.08.2018-->
 				<table id="{$gridArray[x][1]}" cellpadding="0" cellspacing="0" border="1" Alto="{$gridArray[x][9]}"
                   conScroll="{$gridArray[x][8]}" validaNuevo="{$gridArray[x][6]}" despuesInsertar="" AltoCelda="25" auxiliar="0" ruta="../../img/grid/"
                   validaElimina="{$gridArray[x][7]}" Datos="{$gridArray[x][10]}{$llave}&campoid={$gridArray[x][16]}&id_grid={$gridArray[x][0]}&id_PF={$id_PF}&rango_fechas={$filtro_fechas_1}"
                   verFooter="{$gridArray[x][12]}" guardaEn="{$gridArray[x][11]}{$llave}&campoid={$gridArray[x][16]}&id_grid={$gridArray[x][0]}&make={$tipo}"
                   listado="{$gridArray[x][13]}" class="tabla_Grid_RC" scrollH="N" despuesEliminar="" >
                	<tr class="HeaderCell">
                <!-- 12.11.2. Modificacion de Oscar 07.06.2019 para mandar la llave al archivo que carga los combos del grid en atributo datosDB-->
                    	{section loop=$gridArray[x][20] name=y}
                    		{if $gridArray[x][20][y][3] neq 'libre'}
	                        	<td tipo="{$gridArray[x][20][y][3]}" modificable="{$gridArray[x][20][y][4]}" mascara="{$gridArray[x][20][y][5]}" align="{$gridArray[x][20][y][6]}" formula="{$gridArray[x][20][y][7]}" datosdb="../grid/getCombo.php?id={$gridArray[x][20][y][0]}&llave={$llave}" depende="{$gridArray[x][20][y][9]}" onChange="{$gridArray[x][20][y][10]}" largo_combo="{$gridArray[x][20][y][11]}" verSumatoria="{$gridArray[x][20][y][12]}" valida="{$gridArray[x][20][y][13]}" onkey="{$gridArray[x][20][y][14]}" inicial="{$gridArray[x][20][y][15]}" width="{$gridArray[x][20][y][17]}" offsetwidth="{$gridArray[x][20][y][17]}" on_Click="{$gridArray[x][20][y][19]}" multiseleccion="{$gridArray[x][20][y][20]}" requerido="{$gridArray[x][20][y][16]}">{$gridArray[x][20][y][1]}</td>
	                        {else}
	                        	<td tipo="{$gridArray[x][20][y][3]}" modificable="{$gridArray[x][20][y][4]}" mascara="{$gridArray[x][20][y][5]}" align="{$gridArray[x][20][y][6]}" formula="{$gridArray[x][20][y][7]}" datosdb="../grid/getCombo.php?id={$gridArray[x][20][y][0]}&llave={$llave}" depende="{$gridArray[x][20][y][9]}" onChange="{$gridArray[x][20][y][10]}" largo_combo="{$gridArray[x][20][y][11]}" verSumatoria="{$gridArray[x][20][y][12]}" valida="{$gridArray[x][20][y][13]}" onkey="{$gridArray[x][20][y][14]}" inicial="{$gridArray[x][20][y][15]}" width="{$gridArray[x][20][y][17]}" offsetwidth="{$gridArray[x][20][y][17]}" on_Click="{$gridArray[x][20][y][19]}" valor="{$gridArray[x][20][y][18]}">{$gridArray[x][20][y][1]}</td>
	                        {/if}
                    	{/section}
                   <!--Fin de cambio Oscar 07.06.2019-->

                   <!--Excepcion : Implementacion Oscar 10.06.2019 para agregar el boton de direccion al listado de detalle de ediciones de movimientos caja-->
                 	{if $gridArray[x][0] eq '54'}
                   		<td width="56" offsetWidth="56" tipo="libre" valor="Ver detalle" align="center" campoBD='{$valuesEncGrid[x]}'>
							<img class="autorizarmini" src="{$rooturl}img/autorizarmini.png" width="22" height="22" border="0" onclick="ver_detalle_mov_caja('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Autorizar" title="De clic para ver los cambios en el movimiento"/>
						</td>
                   	{/if}
                   <!--Fin de cambio Oscar Oscar 10.06.2019-->
    	            </tr>
        	 	</table>
             </div>
             <script>
             // 12.12. Se mandan cargar los datos mediante la funcion CargaGrid(%$nombre,$id_grid%%);
                CargaGrid('{$gridArray[x][1]}','{$gridArray[x][0]}');
//implementacion Oscar 2020 acordión horizontal en grid proveedor-producto
				{if $gridArray[x][0] eq '48'}
	                hide_grid_accordion( 6, 'proveedorProducto' );
	                hide_grid_accordion( 7, 'proveedorProducto' );
	                hide_grid_accordion( 8, 'proveedorProducto' );
	                hide_grid_accordion( 11, 'proveedorProducto' );
	                hide_grid_accordion( 12, 'proveedorProducto' );
	                hide_grid_accordion( 15, 'proveedorProducto' );
	                hide_grid_accordion( 16, 'proveedorProducto' );
	            {/if}
//fin de cambio Oscar2022
			//Excepcion para insertar fila
				{if $grids[ng][27] neq '0'}
					for(ci=NumFilas('{$gridArray[x][1]}');ci<{$gridArray[x][18]};ci++)
						InsertaFila('{$gridArray[x][1]}');
				{/if}
              </script>
		</div>
        </div>
	{/section}
<!-- 12.13. Fin de iteracion de grids-->







	<br />
<!-- 13. Seccion de botones laterales de la pantalla -->
	<div id="accione"s  class="btn-inferio"r align="right">

		<table  border="0" style="position:fixed;z-index:100;top:35%;right:8px;">
	<!--Ecepcion : implementacion de Oscar 21.08.2018 boton para avanzar al producto siguiente-->
		{if $tabla eq 'ec_productos'}
		  <tr><td align="center"><a href="#" class="fr" title="siguiente" onclick="getSig()" style="background:green;padding:5px;border-radius:5px;color:white;">Siguiente </a><br><br><br></td></tr>
		{/if}
	<!---->
	<!-- 13.1. Boton de guardar-->
       	{if $tipo == 0 or $tipo == 1}
				<tr><td id="guardarlistado" valign="bottom" title="Guardar listado"><table width="60"><tr><td ><img class="botonesacciones guardarbtn" src="{$rooturl}img/guardar.png" alt="guardar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para guardar los cambios" onclick="lanza_mensaje();"/><br>
                    <span style="border:0px solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Guardar</b></span></td></tr></table></td><!--valida() deshabilitado por Oscar 08.06.2018 para lanzar emergente--></tr>
			{/if}

	<!-- 13.2. Boton de listado-->
			<tr>
		<!--Excepcion : implementacion Oscar 08.05.2019 para redireccionar a Listado de Transferencias desde la Recepcion-->
		{if $tabla64 eq 'ZWNfdHJhbnNmZXJlbmNpYXM=' && $no_tabla64 eq 'Mg=='}
			<td id="botonlistado" valign="bottom" title="Botón listado">
              <table>
              	<tr style="">
              	<td valign="top" align="center" style="border:0px solid;height:20px;padding:0px;" height="20px;">
              		<img class="botonesacciones listadobtn" src="{$rooturl}img/listado.png" alt="listado"  onMouseOver="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para ir al listado" 
              		onClick="
              		{if $special_transfers eq 1}
              			if(confirm('{$letSalir}'))
              				location.href='../especiales/Transferencias/transferencias_multiples/index.php?';
              		{else}
              			{if $tipo eq 0 or $tipo eq 1}
              				if(confirm('{$letSalir}'))
              					location.href='{$rooturl}code/general/listados.php?tabla={$tabla64}&no_tabla=MA==';
              			{else}
              				location.href='{$rooturl}code/general/listados.php?tabla={$tabla64}&no_tabla=MA==';
              			{/if}
              		{/if}"
              		/><br>
                    <span style="border:0px solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Listado</b></span>
                </td></tr>
              </table>
            </td>
		<!--Fin de cambio Oscar 08.05.2019-->
		{else}
			<td id="botonlistado" valign="bottom" title="Botón listado">
              <table>
              	<tr style="">
              	<td valign="top" align="center" style="border:0px solid;height:20px;padding:0px;" height="20px;">
              		<img class="botonesacciones listadobtn" src="{$rooturl}img/listado.png" alt="listado"  onMouseOver="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para ir al listado" onClick="{if $tipo eq 0 or $tipo eq 1}if(confirm('{$letSalir}'))location.href='{$rooturl}code/general/listados.php?tabla={$tabla64}&no_tabla={$no_tabla64}'{else}location.href='{$rooturl}code/general/listados.php?tabla={$tabla64}&no_tabla={$no_tabla64}'{/if}"/><br>
                    <span style="border:0px solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Listado</b></span>
                </td></tr>
              </table>
            </td>
        {/if}
        </tr>
	<!-- 13.3. Boton de agregar registro-->
			{if ($tipo == 2 or $tipo == 3) and $mostrar_nuevo eq '1'}
				<tr {if $tipo_sistema neq 'linea' && ($tabla eq 'ec_productos' || $tabla eq 'sys_users' || tabla eq 'ec_traspasos_bancos'
				|| tabla eq 'ec_afiliaciones_cajero') || tabla eq 'ec_caja_o_cuenta'}style="display:none;"{/if}>
				<td id="botonnuevo" valign="top">
				<table width="60"><tr><td><img class="botonesacciones nuevobtn" src="{$rooturl}img/nuevo.png" alt="nuevo" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para agregar un nuevo registro" onclick="location.href='contenido.php?aab9e1de16f38176f86d7a92ba337a8d={$tabla64}&a1de185b82326ad96dec8ced6dad5fbbd=MA==&bnVtZXJvX3RhYmxh={$no_tabla64}'"/><br>
				  <span style="border:opx solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Nuevo</b></span>
				  </td></tr>
				</table>
				</td></tr>
			{/if}
	<!--13.4. Boton de editar-->
			{if ($tipo == 2 or $tipo == 3) && $mostrar_mod eq '1'}
				<tr {if $tipo_sistema neq 'linea' && ($tabla eq 'ec_productos' || $tabla eq 'sys_users' || tabla eq 'ec_traspasos_bancos'
				|| tabla eq 'ec_afiliaciones_cajero') || tabla eq 'ec_caja_o_cuenta'}style="display:none;"{/if}>
				<td valign="top" title="Editar">
				<table width="60"><tr><td><img class="botonesacciones editarbtn" src="{$rooturl}img/editar.png" alt="editar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para editar este registro" onclick="location.href='contenido.php?aab9e1de16f38176f86d7a92ba337a8d={$tabla64}&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190={$llave64}&bnVtZXJvX3RhYmxh={$no_tabla64}'"/><br>
                    <span style="border:opx solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Editar</b></span>
                </td></tr></table></td></tr>
			{/if}
	<!--13.5. Boton de eliminar-->
			{if $tipo == 3 && $mostrar_eli eq '1'}
				<tr {if $tipo_sistema neq 'linea' && ($tabla eq 'ec_productos' || $tabla eq 'sys_users' || tabla eq 'ec_traspasos_bancos'
				|| tabla eq 'ec_afiliaciones_cajero') || tabla eq 'ec_caja_o_cuenta'}style="display:none;"{/if}>
				<td valign="bottom" title="Eliminar">
				<table width="60"><tr><td><img class="botonesacciones eliminarbtn" src="{$rooturl}img/eliminar.png" alt="eliminar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para eliminar el registro" onclick="lanza_mensaje();"/><br>
                    <span style="border:opx solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Eliminar</b></span>
                </td></tr></table></td></tr>
			{/if}<!--valida() deshabilitado por Oscar 08.06.2018 para lanzar emergente-->
	<!--13.6. Boton de imprimir-->
			{if ($tipo eq 1 or $tipo eq 2) && $mostrar_imp eq '1' && ($tabla eq 'ec_ordenes_compraNO' || $tabla eq 'ec_pedidos')}
				<tr><td valign="bottom" title="Imprimir"><table width="60"><tr><td><img src="{$rooturl}img/imprimir.png" alt="imprimir" width="31" class="botonesacciones imprimirbtn" title="clic para imprimir el registro" onclick="imprime()" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';"/><br>
                    <span style="border:opx solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Imprimir</b></span>
                </td></tr></table></td></tr>

			{/if}
	<!--13.7. Boton de reseteo de producto Implentación Oscar 2021-->
			{if $tipo eq 1 && $tabla eq 'ec_productos'}
				<tr><td valign="bottom" title="Resetear Producto"><table width="60"><tr><td><img src="{$rooturl}img/Warning.gif" alt="imprimir" width="31" class="botonesacciones " title="clic para resetear el Producto" onclick="reset_product()" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';"/><br>
                    <span style="border:opx solid;position:relative;bottom:16px;left:0px;width:100%;"><b>Resetear Producto</b></span>
                </td></tr></table></td></tr>

			{/if}
	<!--Excepcion : Implementacion Oscar 21.08.2018 boton para retroceder al producto anterior-->
		  		{if $tabla eq 'ec_productos'}
		  		<tr><td><a href="#" class="fl" style="background:green;padding:5px;border-radius:5px;color:white;" title="anterior" onclick="getAnt()">Anterior</a></td></tr>
                     	<script>
                     		{literal}
                     	//Funcion para retroceder producto a travez del archivo code/ajax/prodAnt.php
                     		function getAnt()
                     		{

                     			id_producto=document.getElementById('id_productos').value;
                     			//alert(id_producto);

                     			res=ajaxR('../ajax/prodAnt.php?tipo=1&id_producto='+id_producto);

                     			aux=res.split('|');

                     			if(aux[0] == 'exito')
                     			{

                     				//&a01773a8a11c5f7314901bdae5825a190=NTE2MA==&bnVtZXJvX3RhYmxh=MA==


	                     			var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfcHJvZHVjdG9z&a1de185b82326ad96dec8ced6dad5fbbd=";
	                     			{/literal}
	                     			url+="{$tipo64}&a01773a8a11c5f7314901bdae5825a190="+aux[1]+"&bnVtZXJvX3RhYmxh=MA==";
	                     			{literal}

	                     			//alert(url);
	                     			location.href=url;
	                     		}
	                     		else if(aux[0] == 'NO')
	                     		{
	                     			alert('No hay un producto anterior');
	                     			return false;
	                     		}
	                     		else
	                     			alert(res);

                     		}

                     	//Funcion para avanzar producto a travez del archivo code/ajax/prodAnt.php
                     		function getSig()
                     		{

                     			id_producto=document.getElementById('id_productos').value;
                     			//alert(id_producto);

                     			res=ajaxR('../ajax/prodAnt.php?tipo=2&id_producto='+id_producto);

                     			aux=res.split('|')

                     			if(aux[0] == 'exito')
                     			{

                     				//&a01773a8a11c5f7314901bdae5825a190=NTE2MA==&bnVtZXJvX3RhYmxh=MA==


	                     			var url="contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfcHJvZHVjdG9z&a1de185b82326ad96dec8ced6dad5fbbd=";
	                     			{/literal}
	                     			url+="{$tipo64}&a01773a8a11c5f7314901bdae5825a190="+aux[1]+"&bnVtZXJvX3RhYmxh=MA==";
	                     			{literal}

	                     			//alert(url);
	                     			location.href=url;
	                     		}
	                     		else if(aux[0] == 'NO')
	                     		{
	                     			alert('No hay un producto siguiente');
	                     			return false;
	                     		}
	                     		else
	                     			alert(res);

                     		}

                     		{/literal}
                     	</script>

                    {/if}
		  	</td>
		  </tr>
		  <!--fin de cambio-->


		</table>
	</div>
	</form>

	<script>

		{literal}
		// 14. Funcion para enviar email atravez del archivo /code/pdf/enviaMail.php 
		    function enviarMail()
		    {
		        {/literal}
                var res=ajaxR('../pdf/enviaMail.php?id={$llave}');
                {literal}

                if(res == 'exito')
                    alert('Se ha enviado el correo con exito');
                else
                {
                    //alert('No fue posible enviar el correo, verifique su configuracion');
                    alert(res);
                }
		    }

		// 15. Funcion para imprimir cotizacion atravez del archivo /code/pdf/imprimeDoc.php
		    function imprimirCot()
		    {
		        {/literal}
		        window.open('../pdf/imprimeDoc.php?tdoc=COT&id={$llave}');
		        {literal}
		    }


			function calculaTotales()
			{
				var num=NumFilas('productos');
				var tot=0;

				for(var i=0;i<num;i++)
				{
					var can=celdaValorXY('productos', 4, i);
					var pre=celdaValorXY('productos', 5, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					pre=isNaN(parseFloat(pre))?0:parseFloat(pre);

					tot+=can*pre;
				}

				var num=NumFilas('otros');
				for(var i=0;i<num;i++)
				{
					var can=celdaValorXY('otros', 6, i);
					var pre=celdaValorXY('otros', 7, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					pre=isNaN(parseFloat(pre))?0:parseFloat(pre);

					tot+=can*pre;
				}

				obj=document.getElementById('subtotal');
				obj.value=tot;

				obj=document.getElementById('iva');
				obj.value=tot*0.16;

				obj=document.getElementById('total');
				obj.value=tot*1.16;

			}


			function test()
			{
				return true;
			}

			function cambiaProds(val)
			{
				id=celdaValorXY('productos', 2, val);
				res=ajaxR('../ajax/valProds.php?id='+id);

				var aux=res.split('|');
				if(aux[0] == 'exito')
				{
					valorXY('productos', 3, val, aux[1]);
					valorXY('productos', 5, val, aux[2]);
				}
				else
				{
					valorXY('productos', 2, val, '');
				}

			}

	/*implementación de Oscar 08.06.2018 para lanzar emergente al editar*/
		function lanza_mensaje(){
			var cargando='<p align="center" style="color:white;font-size:35px;">Guardando</p>';
			cargando+='<br><img src="../../img/img_casadelasluces/load.gif" width="100px;"><br>';
			$("#mensajEmerge").html(cargando);//cargamos el contenido al div
				$("#emerge").css("display","block");
				setTimeout(valida,100);//retrasamos la entrada de la validación
			//alert(2);
			return true;
		}
	/*fin de cambio 08.06.2018*/

			function valida(){
				var f=document.formaGral;
				//alert(f.tabla.value);
				if(f.tabla.value=='ec_productos' 
					&& existe_o_l!=0
					&& $( '#clave' ).val() != -1/*oscar 2022*/
					){
					alert("El Orden De Lista que insertó para este producto ya existe!!!\n\n"+"Pruebe con uno nuevo e intente nuevamente");
					$("#emerge").css("display","none");
					return false;
				}
			/*implementacion Oscar 24.10.2019 para validacion de login unico*/
				if(f.tabla.value=='sys_users' && existe_login!=0){
					alert("El login que tecleo para el usuario ya existe o es invalido!!!\n\n"+"Pruebe con uno nuevo e intente nuevamente");
					$("#emerge").css("display","none");
					$("#login").select();
					return false;
				}

			/* implementacion Oscar 23-09-2020 para validar tipo de producto y fechas de seccion venta en linea*/
				if(f.tabla.value=='ec_productos'){
					if( !validacion_tipo_producto() ){
						$("#emerge").css("display","none");
						return false;
					}
					if( !valida_fecha_tienda_linea() ){
						$("#emerge").css("display","none");
						return false;
					}
				}

				{/literal}
					//alert({$tipo}); edición es tipo=1
					//return true;
					{if $tipo neq 3}
						{$validacion_form}
					{/if}

				{literal}

				if(f.tipo.value == '0')
				{
					f.accion.value="insertar";
				}
				if(f.tipo.value == '1')
				{
					f.accion.value="actualizar";

				}
				if(f.tipo.value == '3')
				{
					f.accion.value="eliminar";
				}

				/*if(f.tabla.value == 'sys_users')
				{
					var aux=GuardaGrid('permisos', 5);

					var ax=aux.split('|');
					if(ax[0] == 'exito')
					{
						f.filePermisos.value=ax[1];
					}
					else
					{
						alert(aux);
						return false;
					}


				}*/


				//Guardamos los grids
				{/literal}

					{section loop=$gridArray name=x}
						{if $gridArray[x][11] neq 'false' && $gridArray[x][0] neq '84' && $gridArray[x][0] neq '54' }// && $gridArray[x][0] neq '84' Se agrega por Oscar 2023 para hacer solo informativo el grid de detalle proveedor producto
						//alert( '{$gridArray[x][2]}');
							{if $gridArray[x][4] neq ''}
								var aux={$gridArray[x][4]};
								if(!aux)
								return false;
							{/if}




							var num=NumFilas('{$gridArray[x][1]}');
							var nomGrid='{$gridArray[x][1]}';
							var disGrid='{$gridArray[x][2]}';

							{literal}



							if (nomGrid == 'sucursalProducto')
							{
						 		if(validaSucursalProducto('sucursalProducto') == false)
						 		{
						 			alert("Todas las presentacines deben ser mayor a 0");
						 			$("#emerge").css("display","none");
						 			return false;
						 		}
						 		//if(validaSucrsalStock('sucursalProducto') == false)
						 		//{
						 		//	alert("El stock debe ser mayor a 0");
						 	//		return false;
						 	//	}

						 		//getJsonSucPro();
						 	}

				/*Implementacion Oscar 2020 para validar que no se repitan atributos por producto
	DESHABILITADO POR OSCAR MARZO 29 2020 
							if (nomGrid == 'atributosProducto'){
								var atributos_existentes = new Array();
								var tmp, tmp_cat;
								var categoria_producto = $("#id_categoria").val();
								var atributos_incorrectos = "";
							//recorre grid
							//alert(NumFilas(nomGrid));
								var tabla=document.getElementById('Body_' + nomGrid);
								trs=tabla.getElementsByTagName('tr');
								for(i_a=0 ; i_a < trs.length; i_a++){
								//
									i_a_a = ($(trs[i_a]).attr("id")).replace('atributosProducto_Fila' , '');
									tds=trs[i_a].getElementsByTagName('td');
           							tmp_cat = tds[5].getAttribute('valor');
           							
           							tmp = tds[3].getAttribute('valor');
											
           							if(tmp_cat != categoria_producto && (tmp_cat != null && tmp_cat != '') ){
										 atributos_incorrectos += $("#" + nomGrid + "_2_" + i_a_a ).html() + "\n";
										 //alert('$("#"'+ nomGrid + "_2_" + i_a_a + ')' );
									}
									for(var a_e = 0; a_e < atributos_existentes.length; a_e++){
										if(tmp == atributos_existentes[a_e] && atributos_existentes[a_e] != null){
											$("#emerge").css("display","none");
											alert("Hay un atributo repetido para el producto y no es posible guardar" +
												"\nVerifique y vuelva a intentar");
											setTimeout(function(){$("#" + nomGrid + "_2_" + (i_a_a) ).click();} , '300'); 
											return false;
										}
									}
									//alert("tmp : " + tmp);
									atributos_existentes.push(tmp);
           						}

								if(atributos_incorrectos != ""){
									$("#emerge").css("display","none");
									alert("Los siguientes atributos no corresponde a la categoria del" +
										" producto, verifique sus datos y vuelva a intentar: \n" 
										+ atributos_incorrectos);
									return false;
						 		}
						 	}*/
				/**/
							for(ig=0;ig<num;ig++)
							{
								var nc=NumColumnas(nomGrid);

								for(jg=0;jg<nc;jg++)
								{
									req=getValueHeader(nomGrid, jg, 'requerido');

							//alert(jg+" req:"+req);

									if(req == 'S' || req == 's')
									{
										if(celdaValorXY(nomGrid, jg, ig) == '')
										{
											//alert(nomGrid+"_"+jg+"_"+ig);
											alert("Debe llenar los datos del grid "+disGrid);
											$("#emerge").css("display","none");
											return false;
										}
									}
								}
							}
				/*implementacion Oscar 2023 para validar que haya almenos un proveedor producto*/
							if ( nomGrid == 'proveedorProducto' && NumFilas(nomGrid) == 0 ){//
								alert( "Es necesario insertar al menos un proveedor producto para continuar!" );
								despliega(1,2);
								$("#emerge").css("display","none");
								$( '#div_grid_2' ).focus();
								return false;
								/*if( ! check_codigo_barras_final() ){
									alert("Hay códigos de barras repetidos en el grid de Proveedor - Producto");
									$("#emerge").css("display","none");
									return false;
								}*/
							}	
				/*fin de cambio Oscar 2023*/

						/*Cambio para no permitir recibir transferencia si no hay internet Oscar(09-11-2017)*/

					/*si el grid es de recepcion de transferencia verificamos conexion con el servidor
						{/literal}
						var xD='{$gridArray[x][0]}';
						{literal}
						if(xD==43){
							var confirmacionServ=ajaxR('../especiales/sincronizacion/conexionSincronizar.php?verifServ=1');
							if(confirmacionServ=='no'){
								alert("No se pueden dar Recepción a las transferencias debido a que no se tiene conexión con el servidor\n"+
								"Verifique su conexion a internet y vuelva a intentar!!!");
								return false;
							}
						}
					//finaliza cambio                   deshabilitado por Oscar 08.06.2018*/

						{/literal}
						var aux=GuardaGrid('{$gridArray[x][1]}', 5);
						var ax=aux.split('|');
						{literal}

						//alert(aux);

						if(ax[0] == 'exito')
						{
							{/literal}
							f.file{$gridArray[x][1]}.value=ax[1];
							{literal}
						}
						else
						{
							alert("Error al guardar grid: "+aux);
							$("#emerge").css("display","none");

							return false;
						}

						{/literal}

						{/if}

					{/section}

				{literal}

				/*alert('Suspendido por pruebas...Atte Equipo de desarrollo');
				return false;*/

				f.submit();

			}//fin de función valida


			function actualizaDependiente(id_catalogos, id_objetos, valor, val_pre)
			{

				var ids=id_catalogos.split(',');
				var obs=id_objetos.split(',');
				var vpres=val_pre.split(',');


				//alert(ids.length);

				for(var j=0;j<ids.length;j++)
				{
					//alert(i)
					var res=ajaxR('comboDependiente.php?id_catalogo='+ids[j]+'&valor='+valor);
					var aux=res.split('|');
					if(aux[0] == 'exito')
					{
						if(document.getElementById(vpres[j]))
							var vpred=document.getElementById(vpres[j]).value;
						else
							var vpred=vpres[j];

						//alert(vpred);

						var obj=document.getElementById(obs[j]);
						obj.options.length=0;
						for(i=1;i<aux.length;i++)
						{
							var ax=aux[i].split('~');
							obj.options[i-1]=new Option(ax[1], ax[0]);
						}
						if(vpred != 'NO')
						{
							obj.value=vpred;
						}

						var och=obj.getAttribute("onchange");
						//var och=och.replace("\n", '');
						//alert(och);
						eval(och);
					}
					else
						alert(res);
				}
			}

			function botonBuscador(nomcampo)
			{
				var obj=document.getElementById(nomcampo+"_txt");
				if(obj)
				{
					var evento=new Object();
					evento.keyCode="40";
					activaBuscador(nomcampo, evento)
				}
				else
					alert("Error, objeto no encontrado.\n\n"+nomcampo+"_txt");
			}

			function activaBuscador(nomcampo, evento)
			{

				objInput=document.getElementById(nomcampo+"_txt");

				var objdiv=document.getElementById(nomcampo+"_div");
				if(!objdiv)
				{
					alert("Error, objeto no encontrado.\n\n"+nomcampo+"_div");
					return false;
				}

				//alert(evento.keyCode);

				if(evento.keyCode==9)
				{
					ocultaCombobusc(nomcampo);
					return false;
				}

				var objh=document.getElementById(nomcampo);
				if(!objh)
				{
					alert("Error, objeto no encontrado.\n\n"+nomcampo);
					return false;
				}

				if(objh&&evento.keyCode!=40)
				{
					objh.value="";
					objh.value=objInput.value;
				}

				if(evento.keyCode==40 && objdiv.style.display=="block")
				{
					//alert("??");
					FocoComboBuscador(nomcampo);
					return false;
				}
				if(evento.keyCode==40)
				{
					//alert('?');

					var depende=(objInput.getAttribute("depende"))?objInput.getAttribute("depende"):"";
					var cadbusq="";
					if(depende!=""&&depende!=0)
					{
						var arrdepen=depende.split("|");
						for(var i=0; i<arrdepen.length;i++)
						{
							if(arrdepen[i].indexOf("~")!=-1)
							{
								var arr=arrdepen[i].split("~");
								var dependencia=arr[0];
								var campodepen=arr[1];
							}
							else
							{
								var dependencia=arrdepen[i];
								var campodepen="";
							}
							var arrnomde=objInput.name.split("_");
							nomde=arrnomde[1]+"_"+dependencia;
							var objvaldep=document.getElementsByName(nomde)[0];
							if(objvaldep)
							{
								if(objvaldep.value!="")
								{
									cadbusq=objvaldep.value;
									var numdep=dependencia;
								}
							}
							if(objvaldep.value!="")
								break;
						}
					}
					if(cadbusq!="")
					{
						muestraBuscador(nomcampo);
						ComboBuscador(nomcampo);
					}

					//alert("Fin ?");
				}

				var numAct=0;


				if((evento.keyCode==40& objInput.value.length>=numAct)||objInput.value.length>=numAct)
				{
					//alert("Y");
					muestraBuscador(nomcampo);
					ComboBuscador(nomcampo);
				}
				else if(objdiv.style.display=="block"&&objInput.value.length>=numAct)
				{
					ComboBuscador(nomcampo);
				}
				return true;
			}

			function muestraBuscador(nomcampo)
			{

				var objdiv=document.getElementById(nomcampo+"_div");
				objInput=document.getElementById(nomcampo+"_txt");
				if(objdiv)
				{
					if(objdiv.style.display=="none")
					{
						objdiv.style.display="block";
						objdiv.style.visibility="visible";
						var top=objdiv.offsetTop;
						var altura=objInput.offsetHeight;
						var y=posicionObjeto(objInput)[1];
						//if(navigator.appName=="Microsoft Internet Explorer")
						top+=2;
						//if(top<(y+altura))
						//{
							top+=altura;
							top+="px";
							//objdiv.style.top=top;
						//}
					}
				}
				return true;
			}


			function ComboBuscador(nomcampo)
			{
				/*var nomcampo=objInput.name;
				var arr=nomcampo.split("_");
				nomcampo=arr[1]+"_"+arr[2];*/

				objInput=document.getElementById(nomcampo+"_txt");


				//alert(objInput);

				var objselec=document.getElementById(nomcampo+"_sel");
				if(objselec)
				{
					if(!objselec)
							return false;
					var lon=objselec.length;
					for(var i=0;i<lon;i++)
						objselec.options[0]=null;


					//alert(objselec)

					var url=objselec.getAttribute("datosdb");


					//alert(url);

					if(url.length>0)
					{
						url+="&val="+objInput.value;
						var depende=(objInput.getAttribute("depende"))?objInput.getAttribute("depende"):"";
						if(depende!=""&&depende!=0)
						{
							var arrdepen=depende.split("|");
							var cadbusq="";
							for(var i=0; i<arrdepen.length;i++)
							{
								if(arrdepen[i].indexOf("~")!=-1)
								{
									var arr=arrdepen[i].split("~");
									var dependencia=arr[0];
									var campodepen=arr[1];
								}
								else
								{
									var dependencia=arrdepen[i];
									var campodepen="";
								}
								var arrnomde=objInput.name.split("_");
								nomde=arrnomde[1]+"_"+dependencia;
								var objvaldep=document.getElementsByName(nomde)[0];
								if(objvaldep)
								{
									if(objvaldep.value!="")
									{
										cadbusq=objvaldep.value;
										var numdep=dependencia;
									}
								}
								if(objvaldep.value!="")
									break;
							}
							if(objvaldep)
							{
								url+="&depende="+(parseInt(numdep)+1)+"&valordep="+cadbusq;
								if(campodepen!="")
									url+="&nom_dependencia="+campodepen;
							}
						}

						//alert(url);

						var resp=ajaxR(url);

						//alert(resp);
						var arr=resp.split("|");


						if(arr[0]!="exito")
						{
							alert(resp);
							return false;
						}
						var num=parseInt(arr[1]);
						//alert(num);

						if(num<=0)
						{
							/*var nombre=objInput.name.split("_");
							nombre=nombre[1]+"_"+nombre[2];*/

							//alert('malo');

							ocultaCombobusc(nomcampo);
							return false;
						}
						var objselec=document.getElementById(nomcampo+"_sel");
						objselec.options.length=0;
						//alert(objselec);
						for(var i=2;i<(num+2);i++)
						{
							var arrOpciones=arr[i].split("~");
							objselec.options[i-2]=new Option(arrOpciones[1],arrOpciones[0]);
						}
						if(objselec.options.length == 1 && !isNaN(objInput.value) && 0)
						{

							//alert('?');
							objselec.options[0].selected=true;
							asignavalorbusc(nomcampo);
						}
					}
				}
				return true;
			}

			function asignavalorbusc(nomcampo)
			{
				//alert('1');
				var objsel=document.getElementById(nomcampo+"_sel");
				if(objsel)
				{
					//alert('2');
					var valor=objsel.value;
					var objcampo=document.getElementById(nomcampo+"_txt");
					if(objcampo)
					{
						//alert('3');
						var seleccionado=objsel.selectedIndex;
						var objseleccionado=objsel.options[seleccionado];
						var visible=objseleccionado.text;
						var oculto=objseleccionado.value;
						var objh=document.getElementById(nomcampo);
						if(objh)
						{
							//alert('4');
							objh.value=oculto;
							objcampo.value=visible;
							var funciononchange=(objcampo.getAttribute("on_change"))?objcampo.getAttribute("on_change"):"";
							//alert(funciononchange);
							if(funciononchange.indexOf("#")!=-1)
								funciononchange=funciononchange.replace('#',objh.id);
							//alert('p1');
							ocultaCombobusc(nomcampo);
							//alert('p2');
							eval(funciononchange);
						}
					}
				}
				return true;
			}


			function ocultaCombobusc(campo)
			{
				var objdiv=document.getElementById(campo+"_div");
				if(!objdiv)
				{
					/*var campo=campo.split("_");
					campo=campo[1]+"_"+campo[2];
					var objdiv=document.getElementById("div"+campo);*/
					return false;
				}
				if(objdiv)
				{
					if(objdiv.style.display=="block")
					{
						var objInput=document.getElementById(campo+"_txt");
						if(objInput)
						{
							var top=objdiv.offsetTop;
							top-=2;
							var altura=objInput.offsetHeight;
							top-=altura;
							top+="px";
							//objdiv.style.top=top;
						}
						objdiv.style.display="none";
						objdiv.style.visibility="hidden";
					}
				}
				return true;
			}

			function posicionObjeto(obj)
			{
			    var left = 0;
			      var top = 0;
			      if (obj.offsetParent) {
			            do {
			                  left += obj.offsetLeft;
			                  top += obj.offsetTop;
			            } while (obj = obj.offsetParent);
			      }
			      return [left,top];
			}
		/*implementación de deshabilitar/habilitar sucursales_producto Oscar 08.05.2018*/
			function afectaSucProd(objeto){
				var tope_gr=$("#sucursalProd tr").length-4;
				var check_val,valor;
				if(objeto.checked==true){
					check_val=true;
					valor=1;
				}else{
					check_val=false;
					valor=0;
				}
			//asignamos valores habilitado/deshabilitado a grid a productos por sucursal
				for(var i=0;i<=tope_gr;i++){
					document.getElementById("csucursalProd_4_"+i).checked=check_val;//habilitamos/deshabilitamos checkbox
					$("#sucursalProd_4_"+i).attr("valor",valor);//cambiamos valor del checkbox
					$("#sucursalProd_4_"+i).attr("valor",valor);//cambiamos valor de la celda (este es el que modifica el valor en la BD)

					if(valor==1  && i==0){
						return true;
					}

					//if(valor==0){//quitamos el atributo cheked en grid de sucursal por producto en el check de habilitado/deshabilitado
					//	$("#csucursalProd_4_"+i+" check").removeAttr('checked');
					//}
				}
			}
		/*Fin de cambio*/
		
var existe_o_l=0;
		//implementación de Oscar 21-02-2018
			function validaNoLista(obj,e){
				var tca=e.keyCode;
				var valor=obj.value;
				{/literal}
				var tpo={$tipo};//capturamos tipo de accion
				{literal}
			//si es edicion capturamos el id del producto
				var idp=0;
				if(tpo==1){
					idp=document.getElementById('id_productos').value;
				}
				if(tca==27){
					$("#res_ord_lis").css("display","none");
					return false;
				}
				if(valor.length<=2){
					$("#res_ord_lis").css("display","none");
				}else{
					//alert(idp);
					$.ajax({
						type:'post',
						url:'../ajax/validaCodLista.php',
						cache:false,
						data:{datos:valor,acc:tpo,id:idp},
						success:function(dat){
							var arr_re=dat.split("|");
							if(arr_re[0]!='ok'){
								alert("Error!!!\n"+dat);
							}
							$("#res_ord_lis").html(arr_re[1]);
							$("#res_ord_lis").css("display","block");
							existe_o_l=arr_re[3];
							if(arr_re[2]<=0){
								$("#res_ord_lis").html('');
								$("#res_ord_lis").css("display","none");
							}

							//alert(existe_o_l);
						}
					});
				}
			}

	/*implementacion Oscar 24.10.2019 para validacion de login unico*/
		var existe_login=0;
			//implementación de Oscar 21-02-2018
			function validaLogin(obj,e){
				var tca=e.keyCode;
				var valor=obj.value;
				{/literal}
				var tpo={$tipo};//capturamos tipo de accion
				{literal}
			//si es edicion capturamos el id del producto
				var idp=0;
				if(tpo==1){
					idp=document.getElementById('id_usuario').value;
				}
/*				if(tca==27){
					$("#res_ord_lis").css("display","none");
					return false;
				}*/
				if(valor.length<=2){
					$("#login").css("color","red");
					existe_login=1;
				}else{
					$.ajax({
						type:'post',
						url:'../ajax/validaCodLista.php',
						cache:false,
						data:{fl:'login',datos:valor,acc:tpo,id:idp},
						success:function(dat){
							var arr_re=dat.split("|");
							if(arr_re[0]!='ok'){
								alert("Error!!!\n"+dat);
							}
							//$("#res_ord_lis").html(arr_re[1]);
							//$("#res_ord_lis").css("display","block");
							existe_login=arr_re[2];
							if(existe_login==0){
							//	$("#res_ord_lis").html('');
								$("#login").css("color","green");
							}else{
								$("#login").css("color","red");
							}
						}
					});
				}
			}
	/*Fin de cambio Oscar 24.10.2019*/

//alert(ejecutar);

/*Deshabilitado por Oscar 08.11.2018 porque ya no se usará
	//implementación Oscar 27.02.2018
		function ch_tip_pgo(flag){
			var campo1,campo2;
		//asignamos campos
			if(flag==1){
				campo1="pago por dia";
				campo2="pago por hora";
			}
			if(flag==2){
				campo1="pago por hora";
				campo2="pago por dia y mínimo de horas";
			}
		//enviamosmensaje
			var conf_c_p=confirm("Al cambiar el "+campo1+" se deshabilitará el "+campo2+"\nDesea continuar?");
		//si no se decide seguir se enfoca el campo de origen
			if(conf_c_p==false){
				if(flag==1){
					$("#pago_hora").focus();
					return false;
				}
				if(flag==2){
					$("#pago_dia").focus();
					return false;
				}

			}else{
				if(flag==1){
					document.getElementById("pago_hora").value="0.00";
					$("#pago_dia").select();
				}
				if(flag==2){
					document.getElementById("pago_dia").value="0.00";
					document.getElementById("minimo_horas").value="0";
					$("#pago_hora").select();
				}
			}
		}
	//fin de implementación 27.02.2018
Fin de deshabilitar Oscar 08.11.2018*/

	//implementación de Oscar 31.07.2018 para desplegar/ocultar los divs de los grids
		function despliega(acc_gr,num_gr){
			var acc_1,acc_2,icono,sig_acc,index;
			if(acc_gr==1){
				acc_1="400px";
				acc_2="block";
				icono="../../img/especiales/menos.png";
				sig_acc="despliega(2,"+num_gr+");";
				$("#desp_"+num_gr).children( 'i' ).removeClass("icon-down-open");
				$("#desp_"+num_gr).children( 'i' ).addClass("icon-up-open");
				//index="2000";
			}
			if(acc_gr==2){
				acc_1="0px";
				acc_2="none";
				icono="../../img/especiales/add.png";
				sig_acc="despliega(1,"+num_gr+");";
				index="5";
				$("#desp_"+num_gr).children( 'i' ).removeClass("icon-up-open");
				$("#desp_"+num_gr).children( 'i' ).addClass(" icon-down-open");
			}
			$("#div_grid_"+num_gr).css("height",acc_1);
			$("#div_grid_"+num_gr).css("display",acc_2);
			$("#div_busc_grid_"+num_gr).css("display",acc_2);
			$("#desp_"+num_gr).attr("src",icono);
			$("#desp_"+num_gr).attr("onClick",sig_acc);
			$("#desp_"+num_gr).css("z-index",index);
			//$(obj_1)
		}
			eval(ejecutar);

		{/literal}

	</script>

<!-- implementacion Oscar 2023 para poder arrastrar los proveedores producto deshabilitado por Oscar 2023-->
	{literal}
	<!--script>
		sort_product_provider();
	</script-->
	{/literal}
<!-- fin de cambio Oscar 2023-->
{include file="general/funciones.tpl" tabla=$tabla}

{include file="_footer.tpl" pagetitle="$contentheader"}


{if $tabla eq 'ec_movimiento_almacen'}
	{literal}
		<script>
			make_responsive_grids();
		</script>
	{/literal}
{/if}
