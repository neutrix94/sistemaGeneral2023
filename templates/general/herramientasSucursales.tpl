<table align="center" cellspacing="15px;" style="width:80%;">
	<tr>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(1,{$campos[0][10]});" class="btns_deshabilit"
			title="Habilita los productos que tienen inventario mayor a cero {$sucursal_informacion_tooltip}">
				Habilitar Productos con inventario <br> en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 1 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(3,{$campos[0][10]});" class="btns_deshabilit"
			title="Habilita todos los productos {$sucursal_informacion_tooltip}">
				Habilitar todos los productos <br> en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 2 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(2,{$campos[0][10]});" class="btns_deshabilit"
			title="Deshabilita todos los productos {$sucursal_informacion_tooltip}">
				Deshabilitar todos los productos <br> en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 3 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(0,{$campos[0][10]});" class="btns_deshabilit"
			title="Deshabilita todos los productos que no tienen inventario {$sucursal_informacion_tooltip}">
				Deshabilitar Productos sin inventario <br> en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 4 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(4,{$campos[0][10]});" class="btns_deshabilit"
			title="Habilita/deshabilita los productos maquilados de acuerdo al inventario del producto origen {$sucursal_informacion_tooltip}">
				Actualizar Productos Maquilados <br>con inventario en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 5 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(5,{$campos[0][10]});" class="btns_deshabilit"
			title="Habilita todos los productos de la categoría 'General' y ultimas piezas {$sucursal_informacion_tooltip}">
				Habilitar Categoria General <br>y 18000 en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 6 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
		<!--Botón para resetear contador de folios-->
			<button type="button" onclick="resetea_con_folios_vtas();" class="btns_deshabilit"
			title="Resetear el contador de folio de ventas (Contador Global)">
				Resetear Folios<br>de Ventas
			</button>
			<button type="button" onclick="show_help_button( 7 );" class="btn_help">
				?
			</button>
			<!--<button type="button" onclick="generaDescPrecio({$campos[0][10]});" class="btns_deshabilit"
			title="Genera registros de descarga de lista de Precios configurada en la sucursal {$campos[1][10]}">
				Generar descarga<br>de precios
			</button>-->
			<!--<input type="button" value="Generar descarga de Precios" onclick="generaDescPrecio();" style="padding:10px;border-radius:5px;position:absolute;top:300px;right:30px;">-->
			
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(6,{$campos[0][10]});" class="btns_deshabilit"
			title="Realiza proceso para modificar los valores de niveles maximo,medio,mínimo de la estacionalidad final de los productos de la sucursal {$campos[1][10]} de acuerdo a la estacionalidad alta de {$campos[1][10]} y los factores de estacionalidad configurados en esta sucursal">
				Generar Estacionalidad<br>final en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 8 );" class="btn_help">
				?
			</button>
		</td>
		<td align="center">
			<button type="button" onclick="deshabilitaSinInventario(7,{$campos[0][10]});" class="btns_deshabilit"
			title="">
				Habilitar productos con<br>estacionalidad en {$campos[1][10]}
			</button>
			<button type="button" onclick="show_help_button( 9 );" class="btn_help">
				?
			</button>
		</td>
	</tr>
</table>
<!--div id="">
	<div>
	</div>
</div-->

{literal}
<script type="text/javascript">
//implementacion Oscar 2017
	function deshabilitaSinInventario(flag,sucursal_seleccion){
		var texto,animacion;
		texto='<p align="center"><font color="white" size="20px">Generando....</font></p>';
		animacion='<img src="../../img/img_casadelasluces/load.gif" height="120px" "width="120px">';
		$('#mensajEmerge').html(texto+'\n'+animacion);
		if(document.getElementById('emerge').style.display="block"){
			document.getElementById('multifacturacion').style.display="none";
			document.getElementById('alertas_resurtimiento').style.display="none";
			$.ajax({
				type:'post',
				url:('../ajax/deshabilitaSinInventario.php'),
				cache:false,
				data:{fl:flag,suc_selecc:sucursal_seleccion},
				success:function(datos){
					if(datos=='ok'){
						alert('Proceso realizado exitosamente!!!');
						location.reload();
					}else{
						alert("Error!!!\n"+datos);
						$("#emerge").css("display","none");//ocultamos emergente
					}
				}
			});
		}
	}

	function resetea_con_folios_vtas(){
				if(!confirm("Si realiza esta accion de manera inadecuada se pueden repetir los folios.\nRealmente desea resetear el contador de folios de Venta?")){
					return false;
				}
				var envia=ajaxR("../ajax/deshabilitaSinInventario.php?&fl=resetear_cont_fol");
				alert(envia);
	}

	function generaDescPrecio(id_sucu){
		//var datos=<?php echo $user_sucursal?>;
	{/literal}
		var sucursal='{$llave}';
		//alert('{$tipo_sistema}');
	{literal}
	//alert(sucursal);
		var envia=ajaxR("../ajax/generaDescargaPrecios.php?&id_suc="+id_sucu);
		var aux=envia.split();
		if(aux[0]!='ok'){
			alert("Error al crear registros de precios!!!\n"+envia);
		}else{
			alert("Registros de precios creados!!!");
			location.reload();
		}
		//alert(envia);
	}
//
	var tools_helps = new Array(
		'Habilita los productos que tengan inventario en la Sucursal que corresponde a la pantalla en la tabla de sucursal - producto ( sys_sucursales_producto ) ; si se esta en la pantalla de sucursal línea se habilitarán los productos con inventario en la tabla de productos (ec_productos)',
		'Habilita todos los productos en la Sucursal que corresponde a la pantalla en la tabla de sucursal - producto ( sys_sucursales_producto ) ;  si se esta en la pantalla de sucursal línea se habilitan los productos en la tabla de productos (ec_productos)',
		'Deshabilita todos los productos en la Sucursal que corresponde a la pantalla en la tabla de sucursal - producto ( sys_sucursales_producto ) ;  si se esta en la pantalla de sucursal línea se deshabilita los productos en la tabla de productos (ec_productos)',
		'Deshabilita los productos que no tengan inventario en la Sucursal que corresponde a la pantalla en la tabla de sucursal - producto ( sys_sucursales_producto ) ; si se esta en la pantalla de sucursal línea se deshabilitarán los productos sin inventario en la tabla de productos (ec_productos)',
		'Habilita / Deshabilita productos maquilados de acuerdo a la configuración que tiene el origen de la maquila',
		'Habilita los productos que pretenezcan a la categoría de General y ID 1808 ( últimas piezas ) en la Sucursal que corresponde a la pantalla en la tabla de sucursal - producto ( sys_sucursales_producto ) ; si se esta en la pantalla de sucursal línea se habilitarán los productos en la tabla de productos (ec_productos)',
		'Elimina los registros de la tabla que contiene los contadores de Folios (cont_folios_vta), regresa el valor del autoincrementable a 1 y por último elimina los codigos de barras generados en ventas anteriores',
		'Realiza proceso para modificar los valores de niveles maximo,medio,mínimo de la estacionalidad final de los productos de la sucursal de acuerdo a la estacionalidad alta y los factores de estacionalidad configurados en la sucursal de la pantalla',
		''
	);
	var btn_accept_help = '<button type="button" class="btn_accept_help" onclick="document.getElementById(\'ventana_emergente_global\').style.display=\'none\';">'
	+ 'Aceptar</button>';
	function show_help_button( number_tool ){
		$( '#contenido_emergente_global' ).html( '<p class="helper_tools_info" >'
			+ tools_helps[ parseInt( number_tool - 1 ) ]
			+ '</p>'
			+ '<center>' + btn_accept_help + '</center>'
		);
		$( '#ventana_emergente_global' ).css( 'display', 'block' );
	}

</script>

<style type="text/css">
	.btn_help{
		border-radius: 50%;
		background-color: white;
		color: blue;
	}
	.helper_tools_info{
		color : blue;
		background-color: white; 
		font-size : 25px; 
		margin : 10%;
		padding: 5%;
		width: 70%;
		text-align: justify;
		border-radius: 15px;
	}
	.btn_accept_help{
		font-size: 20px;
		padding: 10px;
		border-radius: 10px;
	}
</style>
{/literal}

