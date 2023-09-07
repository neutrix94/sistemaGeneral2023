		{literal}
    		<style type="text/css">
    			input[type=date]{padding:10px; border-radius: 10px;}
    			.date_label{color: white;}
    			.montos_linea{padding:10px; border-radius: 10px;}
    			.textos_linea_area{ background: white; width: 300px; height: 80px; margin: 10px 10px 0 10px;}
    		</style>
    	{/literal}
	<!--    			style="position : relative; top:120%;width: 100%; height:60px; font-size:30px; padding: 5px; text-align: center; vertical-align: center; border:0;"-->
				<table style="position : relative; top:120%;width: 100%; height:60px; font-size:30px; padding: 5px; text-align: center; vertical-align: center; border:0;">
				<tr><td align="left">
					<b style="background: yellow;">Configuración del producto Tienda en Línea</b>
				</div>
<!-- Implementacion Oscar 23-09-2020 para agregar seccion habilitado, minimo stock -->
				<div style=" width : 98%; height:80px; background : gray; padding : 10px;">
					<label class="date_label"> Habilitado : </label>
					<input type="checkbox" id="producto_tienda_web_habilitado" name="producto_tienda_web_habilitado"
					{if $precios_especiales[5] eq '1'} checked {/if} >

					<label class="date_label"> Stock minimo : </label>
					<input type="number" name ="producto_tienda_web_stock_minimo" value="{$precios_especiales[6]}"
					class="montos_linea" onchange="valida_fecha(3, this);">

					<label class="date_label"> Producto solo facturacion : </label>
					<input type="checkbox" id="producto_solo_facturacion" name="producto_solo_facturacion"
					{if $precios_especiales[13] eq '1'} checked {/if} >

					<!-- AF- Cambio I 2020-09-19-->
					<label class="date_label"> % Descuento : </label>
					<input type="number" name ="porcentaje_descuento_agrupado" value="{$precios_especiales[14]}"
					class="montos_linea" onchange="valida_fecha(3, this);">
					<!-- AF- Cambio F-->

					<!-- Implementación Oscar 2021-->
					<label class="date_label"> Habilitado (Magento) : </label>
					<input type="checkbox" id="habilitado_en_magento" name="habilitado_en_magento"
					{if $precios_especiales[15] eq '1'} checked {/if} >
					<!-- Fin de cambio Oscar 2021 -->

				</div>
<!---->

				<div style="width : 98%; height:80px; background : gray; padding : 10px;">
					<b>Precio Especial</b><br>
					<label class="date_label">Fecha desde : </label>
					<input type="date" id="pe_del" name="pe_del" value="{$precios_especiales[0]}"
					onchange="valida_fecha(1, this);">

					<label class="date_label"> hasta : </label>
					<input type="date" id="pe_al" name="pe_al" value="{$precios_especiales[1]}"
					onchange="valida_fecha(2, this, 'pe_del');">

					<label class="date_label"> Monto : </label>
					<input type="number" class="montos_linea" id="montos_linea" name="monto_esp_product" value="{$precios_especiales[2]}"
					onchange="valida_fecha(3, this);">
				</div>
				<div style="width : 98%; height:160px; background : gray; padding : 10px;">
					<b>Producto nuevo</b><br>
					<label class="date_label">Fecha desde : </label>
					<input type="date" id="pn_del" name="pn_del" value="{$precios_especiales[3]}"
					onchange="valida_fecha(1, this);">

					<label class="date_label"> hasta : </label>
					<input type="date" id="pn_al" name="pn_al" value="{$precios_especiales[4]}"
					onchange="valida_fecha(2, this, 'pn_del');">

					<label class="date_label" id="elimina_especial_venta_linea">
						<button type="button" onclick="resetea_prod_linea();">X</button>
					</label>
				</div>

<!-- Implementacion Oscar 23-09-2020 para agregar seccion campos de posicionamiento SEO y busqueda -->
				<div style="width : 98%; height:360px; background : gray; padding : 10px; overflow : auto;">
					<b>Campos de posicionamiento SEO y búsqueda</b><br>
				<table>
					<tr>
						<td> <label class="date_label"> Descripcion : </label> </td>
						<td> <textarea id="producto_tienda_web_descripcion"
							name="producto_tienda_web_descripcion"
							class="textos_linea_area">{$precios_especiales[8]}</textarea></td>

						<td> <label class="date_label"> Breve Descripcion : </label> </td>
						<td> <textarea id="producto_tienda_web_descripcion_breve"
							name="producto_tienda_web_descripcion_breve"
							class="textos_linea_area">{$precios_especiales[9]}</textarea></td>

					</tr>
						<td> <label class="date_label"> Palabras clave : </label> </td>
						<td> <textarea id="producto_tienda_web_palabras_clave"
							name="producto_tienda_web_palabras_clave"
							class="textos_linea_area">{$precios_especiales[10]}</textarea> </td>

						<td> <label class="date_label"> Metatitulo : </label> </td>
						<td> <textarea id="producto_tienda_web_metatitulo"
							name="producto_tienda_web_metatitulo"
							class="textos_linea_area">{$precios_especiales[11]}</textarea> </td>

					</tr>
						<td> <label class="date_label"> Metadescripcion : </label> </td>
						<td> <textarea id="producto_tienda_web_metadescripcion"
							name="producto_tienda_web_metadescripcion"
							class="textos_linea_area">{$precios_especiales[12]}</textarea> </td>
					</tr>
				</table>
			</td></tr></table>
				</div>
<!---->
		{literal}
    		<script type="text/javascript">
    			function valida_fecha(flag, obj, depende){
    				{/literal}
						var llave='{$llave}';
					{literal}
    				if( flag == 3 && $(obj).val() < .1 && $(obj).val() != '' ){
    					alert("Este valor no puede ser negativo ni menor a cero");
    					$(obj).select();
    					return false;
    				}
    				var f = new Date();
					var fecha_actual = f.getFullYear() + "-" +
					( ( (f.getMonth() + 1 ) < 10) ? ("0" + (f.getMonth() + 1) ) : (f.getMonth() + 1) )
					+ "-" + f.getDate() ;
    				if( flag == 1 && $(obj).val() < fecha_actual ){
    					alert("La fecha no puede ser menor a la fecha actual");

    					$(obj).val("");
    					$(obj).select();
    				}

    				//alert($(obj).val() + " º " + fecha_actual + " ___ " + $("#" + depende).val());

    				if( flag == 2 && ( $(obj).val() < fecha_actual || $(obj).val() < $("#" + depende).val() ) ) {
    					alert("La fecha hasta no puede ser menor a la fecha actual ni menor a la fecha 'desde' ");

    					$(obj).val("");
    					$(obj).select();
    				}
    				$("#elimina_especial_venta_linea").css("display" , "inline-block");
    			}

    			function resetea_prod_linea(){
    				/*if(!confirm("Eliminar la configuracion del producto en venta linea implica eliminar la imagen principal\n" +
    					"Realmente desea eliminarla?")){
    					return false;
    				}*/
    				$("#pe_del").val("");
    				$("#pe_al").val("");
    				$("#pn_del").val("");
    				$("#pn_al").val("");
    				$("#montos_linea").val("");
    				$("#elimina_especial_venta_linea").css("display" , "none");
    				$("#producto_tienda_web_habilitado").removeAttr("checked");
    				$("#producto_tienda_web_stock_minimo").val("");
    				//$("#nombre_img_principal").val("");
    				$("#producto_tienda_web_descripcion").html("");
    				$("#producto_tienda_web_descripcion_breve").html("");
    				$("#producto_tienda_web_").html("");
    			}
    		</script>
    	{/literal}
