/****Variables globales****/
var insumos_de="",insumos_a="";//variables donde se guardan los filrtros de insumos
var ventana_abierta;//variable que guarda ventana abierta
var id_orden=0;//decalaramoa variable de id de orden de compra
var hay_cambios=0;//declaramos variable que da referencia de si hay cambios o no
var resaltado=0;//variable que guarda la fila que esta resaltada del grid
var foc_opc=0;
var fecha_max_del='',fecha_max_al='',factor_ventas_adic=1;//variables declaradas para los filtros de promedio y máximo de fechas//fecha_prom_del='',fecha_prom_al=''
var asignacion_fechas=0;
/****Fin de declaracion de variables globales****/

window.onload=function(){
	id_orden=$("#id_orden_compra").val();//capturamos el valor de la orden de compra
	if(id_orden!=0){
	//cargamos el detalle de la orden de compra si se detecta un id diferente de cero
		carga_pedido('carga');
	}
}

/*implementacion Oscar 23.09.2019 para no dejar guardar piezas en proveedor en cero*/
	function verifica_prov_prod(obj){
		if($(obj).val()<=0){
			alert("Las peresentación mínima es de una pieza por caja!!!");
			$(obj).val('1');
		}
	}
/*fin de cambio Oscar 23.09.2019*/

/**/
	function ocultar_busqueda(){
		$("#res_busc").html('');
		$("#res_busc").css('display','none');
	}
/**/
/*implementación Oscar 02.09.2019 para habilitar/deshabilitar productos*/
	function habilita_deshabilita_prd(id_prd){
		var tipo=0;
		if(document.getElementById('check_multi_deshabilita').checked==true){
			tipo=1;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			data:{fl:'status_prd',valor:tipo,id:id_prd},
			success:function(dat){
				$("#info_cambios").html(dat);
			}
		});
	}
/*Fin de cambio Oscar 02.09.2019*/

/*implementación Oscar 19.02.2019 para poder guardar al generar nuevo cálculo*/
	var pedido_guardado=0;//variable que sirve para ver si ya hay datos de un pedido generado
	function recarga_pantalla(){
	//reseteamos evento a a los botones y los hacemos visibles
		$("#env_correos").attr("onclick","");
		$("#env_correos").css("display","none");
		$("#desc_ped_csv").attr("onclick","");
		$("#desc_ped_csv").css("display","none");
		$("#t2").html('<table border="0" width="99.99%" style="margin:0;padding:0;" id="lista_de_prods">'	
							+'</table>'
							+'<input type="hidden" id="filas_totales" value="0">');//limpiamos la tabla del detalles
	//mostramos botón de guardar pedido
		$("#guardado_pedido").css("display","block");
		pedido_guardado=0;//reseteamos la variable de pedido guardado
	}
/*Fin de cambio 19.02.2019*/

/*Implementación de Oscar 27.08.2018 para asiganr las fechas*/
	function asignar_fechas(id_prod,num,flag){
	//obtenemos los filtros de fechas
	if(document.getElementById('omitir_filtros').checked==false){
		fecha_max_del=$('#fcha_max_del').val();
		if(fecha_max_del==''||fecha_max_del==null){
			alert("Esta fecha no puede ir vacía 1!!!");
			$("#fcha_max_del").focus();
			return false;
		}
		fecha_max_al=$('#fcha_max_al').val();
		if(fecha_max_al==''||fecha_max_al==null){
			alert("Esta fecha no puede ir vacía 2!!!");
			$("#fcha_max_al").focus();
			return false;
		}
		factor_ventas_adic=$("#factor_ventas").val();
		if(factor_ventas_adic=='' || factor_ventas_adic==null){
			factor_ventas_adic=0;
		}
/*		fecha_prom_del=$('#fcha_prom_del').val();
		if(fecha_prom_del==''||fecha_prom_del==null){
			alert("Esta fecha no puede ir vacía 3!!!");
			$("#fcha_prom_del").focus();
			return false;
		}
		fecha_prom_al=$('#fcha_prom_al').val();
		if(fecha_prom_al==''||fecha_prom_al==null){
			alert("Esta fecha no puede ir vacía 4!!!");
			$("#fcha_prom_al").focus();
			return false;
		}
*/
	}
	//mandamos a lamar la función de nuevo
		if(flag=='grafica'){
			graficar_inv_vtas(id_prod);
		}else{
			config_de_prod(id_prod,num);
		}
	}

/*Fin de cambio 27.08.2018*/
/*********Implementación Oscar 29.06.2018 para descargr csv******************/
	function descarga_previo(){
	//sacamos el tamaño de la tabla
		var tam=$("#filas_totales").val();
	//variable que guardará datos
		var datos_csv='Orden de Lista,Id Producto,Producto,Entradas,Ventas,Inv final,Pedido(piezas),Precio|proveedor|presentacion,Total Cajas,Total $\n';
	//extraemos los datos
		for(var i=1;i<=tam;i++){
		//verificamos si existe la fila
			if(document.getElementById('cant_p_'+i)){
				datos_csv+=$("#0_"+i).html()+",";//orden de lista del producto
				datos_csv+=$("#id_p_"+i).val()+",";//id del producto
				datos_csv+=$("#1_"+i).html().trim()+",";//nombre del producto
				datos_csv+=$("#2_"+i).html().trim()+",";//total de entradas
				datos_csv+=$("#3_"+i).html().trim()+",";//ventas del producto
				datos_csv+=$("#4_"+i).html().trim()+",";//inventario actual del producto
				datos_csv+=$("#cant_p_"+i).val()+",";//cantidad de compra del producto
				datos_csv+=$("#c_p_"+i+" option:selected").text()+",";//proveedor,presentacion caja, precio pieza
				datos_csv+=$("#valor_cajas_"+i).html().trim()+",";//total(cajas)
				datos_csv+=$("#valor_monto_"+i).html().trim();//total(monto)
				if(i<tam){
					datos_csv+="\n";//concatenamos el salto de linea
				}
			}
		}//fin de for i
	//asignamos el valor a la variable del formulario
		$("#datos").val(datos_csv);
	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
//alert('descargado!!!');
		setTimeout(cierra_pestana,2000);
		//setTime(,'5000');
/*		
		if(csv_2('ajax/afterSave.php?fl=3&id='+datos_csv)){
		ventana_abierta.close();//cerramos ventana
			
		}
*/	}

	function cierra_pestana(){
		ventana_abierta.close();
	}

	function cierra_ventanaCsv(){
		ventana_abierta.close();//cerramos ventana
	}

//generar y descargar csv
	function descargarCSVpedidos(ids){
		if(ids==""||ids==null){
			alert("No se detectaron pedidos por descargar\nVerifique que los pedidos se hayan guardado en órdenes de compra y descárguelos desde el módulo órdenes de compra!!!");
			return false;
		}
		var arr=ids.split("~");
		var cont=0;
		for(var i=0;i<arr.length-1;i++){
			csv_2('ajax/afterSave.php?fl=1&id='+arr[i]);
		//		cont++
		}
	}
	function csv_2(url){
			ventana_abierta=window.open(url, '_blank');
		//alert("Evia");
	}
/**************Fin de cambio*************/
	//enviar correos a proveedor
	function enviaCorreoProv(ids){
		if(ids==""||ids==null){
			alert("No se detectaron pedidos por enviar\nVerifique que los pedidos se hayan guardado en órdenes de compra y envíelos desde el módulo órdenes de compra!!!");
			return false;
		}else{
			var arr=ids.split("~");
			var cont=0;
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'ajax/afterSave.php',
				cache:false,
				data:{fl:2,id_ordenes:ids},
				success:function(dat){
					var ax_rs=dat.split("|");
					if(ax_rs[0]!='ok'){
						alert("Error\n"+dat);
						return false;
					}
					alert(dat);
				}
			});
		}
	}

	function cambiar_var_insumos(){
		insumos_de=$("#cmb_var_1").val();
		insumos_a=$("#cmb_var_2").val();
	//verificamos que se hayan seleccionado los nuevos filtros
		if(insumos_de==-1||insumos_a==-1){
			alert("Debe seleccionar una opción válida!!!");
			insumos_de="",insumos_a="";
			return false;
		}
		if(insumos_de>=insumos_a){
			alert("El nivel base no puede ser mayor o igual al nivel nuevo!!!");
			insumos_de="",insumos_a="";
			return false;
		}
		cierra_eme_prod();
	}

	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}
//habilitar/deshabilitar resurtimiento
	function resurtimiento(num,flag){
		//alert(flag);
		var dto=0,acc="deshabilitar",id_prod=0;
		if(flag!=2){
			if(document.getElementById('re_surt_'+num).checked==true){
				dto=1;
				acc="habilitar";
			}
			id_prod=$("#id_p_"+num).val();
		}

		if(flag==2){
			if(document.getElementById("re_surtir").checked==true){
				dto=1;
				acc="habilitar"
			}
			id_prod=num;
		}
		/*
		var conf=confirm("Realmente desea "+acc+" el resurtimiento de este producto?");
		if(conf==false){
			return false;
		}*/
		
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{valor:dto,id_p:id_prod,fl:9},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert(dat);
					return false;
				}
			}
		});
	}

/*********************Funciones de grid**********************/
//resaltar/moverse entre opciones de buscador
	function valida_opc_busc(e,num,id_prod){
		var tec=e.keyCode;
	//tecla abajo
		if(tec==40){
			resalta_opc(num+1);
			return true;
		}
	//tecla arriba
		if(tec==38){
		//enfoca buscador
			if(num==1){
				//resalta_opc(1);
				//$("#busc").select();
				return true;
			}
			resalta_opc(num-1);
			return true;
		}
		if(tec==13){
			$("#tr_1_"+num).click();
			return false;
		}
	}
	function resalta_opc(num){
		if(foc_opc!=0){
			$("#r_"+foc_opc).css("background","white");
			if(num==1){
				foc_opc=0;
				return false;
			}
		}
		$("#r_"+num).css("background","rgba(0,225,0,.5)");
		$("#r_"+num).focus();
		foc_opc=num;
		return false;
	}

//resaltar/devolver color de filas
	function resalta(num,accion){
		var color_1,color_2="rgba(0,225,0,.5)";
	//regreamos color de fila desenfocada
		if(resaltado!=0){
			if(resaltado%2==0){
				color_1="#E6E8AB";
			}else{
				color_1="#BAD8E6";
			}
			$("#f_"+resaltado).css("background",color_1);
		/*mar(camos de amarillo la fila si tiene pendientes de recibir*/
			if(id_orden==0 && ($("#cant_p_"+resaltado).attr("title")!=null && $("#cant_p_"+resaltado).attr("title")!='')){
				$("#f_"+resaltado).css("background","#FFFF00");
			}
		/**/			
		}
	//resaltamos nueva fila de enfoque
		$("#f_"+num).css("background",color_2);
	/*mar(camos de amarillo la fila si tiene pendientes de recibir*/
		if(id_orden==0 && ($("#cant_p_"+num).attr("title")!=null && $("#cant_p_"+num).attr("title")!='')){
			$("#f_"+num).css("background","#FFFF00");
		}
	/**/
	//enfocamos caja de texto
		if(accion!='click'){
			$("#c_p_"+num).focus();
			$("#cant_p_"+num).select();
		//limpiamos buscador
			document.getElementById("busc").value="";
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
		}
		resaltado=num;
	}

/*********************Funciones de proveedor**********************/
//eliminar proveedor
	function elimina_prov(id_registro,num){
		var con_del=confirm("Realmente desa quitar este proveedor para este producto???");
		if(con_del==false){
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:3,id:id_registro},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					elimina_fila(num);
				}
			}
		});
	}
//agregar proveedor en BD
	function guarda_prov(num,id_prod){
		var cant,pco,proved;
	//extraemos datos
		cant=document.getElementById('c_'+num).value;
		if(cant==''||cant==null){
			alert("La cantidad del producto no puede ir vacía");
			document.getElementById('c_'+num).select();
			return false;
		}
		pco=document.getElementById('p_'+num).value;
		if(pco==''||pco==null){
			alert("El precio del producto no puede ir vacío");
			document.getElementById('p_'+num).select();
			return false;
		}
		proved=document.getElementById("nvo_pr_"+num).value;
		if(proved==''||proved==null||proved==-1){
			alert("El proveedor del producto no es valido");
			document.getElementById('p_'+num).select();
			return false;
		}
		//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:1,prod:id_prod,prov:proved,c_pr:cant,p_pr:pco},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				//alert("Proveedor agregado a este producto exitosamente!!!");
			//sustituimos el botón de edición
				$("#bot_"+num).html('<input type="button" value="Editar" onclick="edita_prov('+ax[1]+','+num+')" id="edit_'+num+'" disabled>');
			//sustituimos el botón de borrar
				$("#bot_del_"+num).html('<input type="button" value="x" onclick="elimina_prov('+ax[1]+','+num+')">');
			//asignamos el nombre del proveedor
				var txt_prov=$("#nvo_pr_"+num+" option:selected").text();
				//alert(txt_prov);
				$("#col_temp_"+num).html(txt_prov);
			}
		});
	}
//muestra proveedores por producto
	function adm_prov_prod(id){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/prov.php',
			cache:false,
			data:{prod:id},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				$("#emer_prod").html(ax[1]);
				$("#emer_prod").css("display","block");
			}
		});
	}

//cerrar ventana emergente
	function cierra_eme_prod(flag){
		if(flag==1){
			$('#fam > option[value="-1"]').attr('selected', 'selected');
		}
		if(activa_cambio==1){
			var cnf=confirm("Hay cambios sin guardar!!!\nDesea cerrar esta ventana sin guardar?");
			if(cnf==false){
				return false;
			}
		}
		$('#emer_prod').html('');//limpiamos emergente
		$('#emer_prod').css('display','none');//ocultanos emergente
		activa_cambio=0;//reseteamos variable que detecta cambios
	}

//agregar filas en blanco para grid detalle precios y config de producto
	function agrega_filas_subg(id_p,flg){
		//alert();
		var tabla="";
		if(flg==1){
			tabla="#t_prov_prod";
		}
		if(flg==3){
			tabla="#precios_producto";
		}
		var val_nv=($(tabla+" tr").length);
		//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/add_fila.php',
			cache:false,
			data:{c:val_nv,flag:flg,prod:id_p},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				//alert(ax[1]);
				$(tabla).append(ax[1]);
				$("#fil_tot_provs").val(val_nv);
				activa_cambio=1;
				//$("#res_busc").css("display","block");
			}
		});
	}

//edición de proveedor
	function edita_prov(id_reg,num){
		var prec,cant,id_reg;
	//obetenemos valores para editar
		cant=document.getElementById("c_"+num).value;
		prec=document.getElementById("p_"+num).value;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{nvo_prec:prec,nva_cant:cant,id:id_reg,fl:2},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				alert("Actualizado correctamente!!!");
  				document.getElementById("edit_"+num).disabled=true;
				//$("#res_busc").css("display","block");
			}
		});

	}
//funcion que valida
	function validar_tc_prov(e,fl,num){
		var tec=e.keyCode;
//	alert(tec);
		if((tec>47&&tec<58)||tec==8){//(tec=8||tec==13)
			if(tec==13){
				return false;
			}
  			document.getElementById("edit_"+num).disabled=false;
   			//alert();
  		}else{
  			//alert("deshabiliar");
  			document.getElementById("edit_"+num).disabled=true;
  		}
	}
/*******************Terminan funciones de proveedor****************/


/*******************Funciones de filtrado y busqueda*******************/
//ver/ocultar filtrado de fechas
	function muestra_fechas(fl){
		//alert();
		if(fl!=0){
			fl=fl.value;
		}
		//fl=fl.value;
		var atrib="";
		if(fl==2){
			atrib="block";
		}else if(fl==0){
			atrib="none";
		}
	//mandamos atributo css
		$("#fechas_filtro").css("display",atrib);
	}

//ver/ocultar lista de proveedores
	function ver_lista_prov(fl){
		var atrib="";
		if(fl==1){
			atrib="block";
		}else if(fl==0){
			atrib="none";
		}
	//mandamos atributo css
		$("#lista_de_proveedores").css("display",atrib);
	}

//buscador
	function busqueda(e,obj){
		var tec=e.keyCode;
		var filtro_deshabil=0;
		if(document.getElementById("st_prd").checked==true){
			filtro_deshabil=1;
		}
		if(tec==40){
			resalta_opc(1);
			return false;
		}
		var val=obj.value;
		if(val.length<=2){
			$("#res_busc").html('');
			$("#res_busc").css("display","none");
			return false;
		}
	//verificamos filtros
		var fa,ti,subt,fto="";
		fa=document.getElementById('fam').value;
		if(fa!=""||fa!=-1){
			fto+=" AND p.id_categoria='"+fa+"'";
		}
		ti=document.getElementById('tpo').value;
		if(ti!=""||ti!=-1){
			fto+=" AND p.id_subcategoria='"+ti+"'";
		}
		subt=document.getElementById('sub_tpo').value;
		if(subt!=""||subt!=-1){
			fto+=" AND p.id_subtipo='"+subt+"'";
		}
	//verificamos proveedores
		var cond_prov="";
/*cambio para buscar de acuerdo al proveedor de una oc que se está modificando*/
	if(id_orden!=0){
		cond_prov=$("#ids_provs").val();//extraemos el id delproveedor de la oc correspondiente
	}
/*fin de cambio*/
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscaProductos.php',
			cache:false,
			data:{txt:val,filtros1:fto,id_proveedor:cond_prov,id_oc:id_orden,filt_deshab:filtro_deshabil},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				$("#res_busc").html(ax[1]);
				$("#res_busc").css("display","block");
			}
		});
	}

//llenado de combos
	function carga_combo(obj,flag){
		var dato=obj.value;
		var id_obj2="";
		if(flag==1){
			id_obj2="#tpo";
		}else{
			id_obj2="#sub_tpo";
		}
	//aqui detectamos si son extras
		if(flag==1&&obj.value==35){
		//cargamos archivo de combos en emergente
			$("#emer_prod").load("ajax/filtrosExtras.php");
			$("#emer_prod").css("display","block");//mostramos emergente
			return true;
		}else{
			insumos_de="",insumos_a="";//reseteamos variables de niveles de insumos
		}
		$.ajax({
			type:'post',
			url:'ajax/getDatosCombo.php',
			cache:false,
			data:{id:dato,campo:flag},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error al cargar cambo!!!\n"+dat);
				}else{
				//limpiamos combo dependiente
					$(id_obj2).empty();
				//llenamos combo con nuevos datos de combo dependiente
					$(id_obj2).append('<option value="-1">------Filtrar------</option>');
					var ax1=ax[1].split("°");
					for(var i=0;i<ax1.length-1;i++){
						var ax2=ax1[i].split("~");
						$(id_obj2).append('<option value="'+ax2[0]+'">'+ax2[1]+'</option>');
					}
					if(flag==1){
					//limpiamos combo de subtipo
						$("#sub_tpo").empty();
						$("#sub_tpo").append('<option value="-1">------Filtrar------</option>');
					}
				}
			}
		});
	}

//remplazar datos de combos
	var desplegado=0;
	function carga_proveedor_prod(num,id_prod){
			if(desplegado==1){
				//desplegado=0;
			//return false;
		}
	//extremos el filtro de proveedor sin precio
		var sin_precio=0;
		if(document.getElementById('incluye_sin_precio_proveedor') && document.getElementById('incluye_sin_precio_proveedor').checked==true){
			sin_precio=1;
			//alert("incluye sin precio");
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/getDatosCombo.php',
			cache:false,
			data:{id:id_prod,flag:2,c:num,precio_ceros:sin_precio},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error al cargar lista de proveedores!!!\n"+dat);
					return false;
				}else{
				//limpiamos combo dependiente
					$("#combo_prov_"+num).html(ax[1]);
					return true;
					/*$("#c_p_"+num).empty();
					
					$("#c_p_"+num).append('<option value="null"></option>');
					for(var i=1;i<ax.length;i++){
						var arr=ax[i].split("~");
						$("#c_p_"+num).append('<option value="'+arr[3]+'">'+arr[1]+':'+arr[2]+'pzas</option>');
					}
					if(ax.length<2){
					//agregamos opción vacía para poder cmabiar a agregar nuevo cuado el prod no tiene proveedores
						$("#c_p_"+num).append('<option value="-1"></option>');
					}
				//agregamos nueva opción
					$("#c_p_"+num).append('<option value="nvo">Administar Proveedores</option>');
				//marcamos como desplegado el combo
					desplegado=1;
				*/
				}
			}
		});
	}

//activa proveedor
	function muestra_prov(obj,num,fl){
	//administrar proveedores
		if(obj.value=='nvo'){
			$("#b1_"+num).click();
			return false;
		}
		if(obj.value==0||obj.value==-1){
			return false;
		}
	//cambiar valor
		recalcular(num,2);
	}

//recalcular producto
	function recalcular(num,flag){
	//detectamos si no es valido el combo para la operación
		if($("#c_p_"+num).val()=='0'||$("#c_p_"+num).val()==-1||$("#c_p_"+num).val()=='nvo'){
			return false;
		}
	//obtenemos valores
		var pedir,precio,pres_caja;
		var txt_prov=$("#c_p_"+num+" option:selected").text();
		var arr1=txt_prov.split(":");//separamos información
		var arr2=arr1[0].split("$");//quitamos signo al precio
		precio=arr2[1];//obtenemos precio por producto
		var arr2=arr1[2].split("pzas");//quitamos letra de presentación
		pres_caja=arr2[0];
		pedir=$("#cant_p_"+num).val();//valor en piezas
		var nvas_cajas=pedir/pres_caja;
	//asignamos nuevo valor en cajas
		$("#valor_cajas_"+num).html(parseFloat(nvas_cajas).toFixed(2));
	//asignamos nuevo valor en monto
		$("#valor_monto_"+num).html(parseFloat(pedir*precio).toFixed(2));
		return true;
		//alert(precio+" "+cajas);

	}

/*************************************************Asignación de datos*****************************************/

//carga de datos
	function carga_pedido(tipo){
		var cuenta_deshabilitados=0;
		if(document.getElementById('st_prd').checked==true){
			cuenta_deshabilitados=1;
		}
	//extremos el filtro de proveedor sin precio
		var sin_precio=0;
		if(document.getElementById('incluye_sin_precio_proveedor') && document.getElementById('incluye_sin_precio_proveedor').checked==true){
			sin_precio=1;
			//alert("incluye sin precio");
		}
/*implementación Oscar 19.02.2019 para poder guardar al generar nuevo cálculo*/
		if(pedido_guardado!=0){
			recarga_pantalla();
		}
/*fin de cambio Oscar 19.02.2018*/
	//obtenemos el id de la orden de compra y acción 
		if(tipo=='carga' && id_orden==0){
			return false;
		}
		var accion=$("#tipo_accion").val();
		/*id_orden=$("#id_orden_compra").val();*/
	//sacamos dato del factor
		if(id_orden==0){
			var f=document.getElementById('factor').value;
			if(f==''||f==null){
				alert("El factor debe de ser un número válido, este campo no puede ir vacío!!!");
				$("#factor").select();
				return false;
			}
		}else{
			var f=0;
		}
	//extraemos filtros
		var fa,ti,subt,p_nvo=0,fto="",pendientes=0;
		if(document.getElementById('prods_nvos').checked==true){//filtro de productos nuevos
			p_nvo=1;
		}
		fa=document.getElementById('fam').value;//filtro de familia
		if(fa!=-1){
			fto+=" AND p.id_categoria='"+fa+"'";
		}
		ti=document.getElementById('tpo').value;//filtro de tipo
		if(ti!=-1){
			fto+=" AND p.id_subcategoria='"+ti+"'";
		}
		subt=document.getElementById('sub_tpo').value;//filtro de subtipo
		if(subt!=-1){
			fto+=" AND p.id_subtipo='"+subt+"'";
		}
		pendientes=document.getElementById('filt_pendientes').value;

		var provs="";
		var filtro2="";
	//extraemos filtro de resutrimiento
		var resurt=$("#resurt_prod").val();
		var filt_resurt="";
		if(resurt==1){
			filt_resurt=" AND p.es_resurtido=1";
		}
		if(resurt==2){
			filt_resurt=" AND p.es_resurtido=0";
		}	
	//extraemos numero de proveedores existentes
		tam=document.getElementById('num_provs').value;
	//validamos que todos los proveedores esten marcados
		if(document.getElementById('tod_prov').checked==false){	
			for(var i=1;i<=tam;i++){
				if(document.getElementById("pr_"+i).checked==true){
					provs+=$("#pr_"+i).val()+"|";
				}
			}
		//validamos que hay por lo menos un proveedor
			if(provs==""&&tipo!='carga'){
				alert("Por lo menos un proveedor debe de ser seleccionado!!!");
				return false;
			}else{
			//formamos filtro
				var ax=provs.split("|");
				for(var i=0;i<ax.length-1;i++){
					if(i==0){
						filtro2+=" AND(";
					}else{
						filtro2+=" OR ";
					}
					filtro2+="prov.id_proveedor="+ax[i];
				}
				filtro2+=")";
			}
		}
	//extraemos tipo de pedido
		var tipo_ped=document.getElementById('tipo_pedido').value;
		var filtro3="",dt=new Date(),year=dt.getFullYear();//declaramos variables; entre ellas la del año
		if(tipo_ped==-1&&tipo!='carga'){
			alert("Debe seleccionar un tipo de pedido antes de Generarlo!!!");
			return false;
		}
		if(tipo_ped==1){//si es pedido inicial
			year=year-1;//le restamos uno al año actual
		}else if(tipo!='carga'){
		//extraemos rangos de fechas
			if(document.getElementById("fta_1").value==""){
				alert("Las fechas no pueden ir vacías si el pedido es resurtimiento; si es libre solo agregue los productos!!!");
				$("#fta_1").select();
				return false;
			}
			filtro3+=document.getElementById("fta_1").value+"|";//año ant
			if(document.getElementById("fta_2").value==""){
				alert("Las fechas no pueden ir vacías si el pedido es resurtimiento; si es libre solo agregue los productos!!!");
				$("#fta_2").select();
				return false;
			}
			filtro3+=document.getElementById("fta_2").value+"|";
			if(document.getElementById("ftc_1").value==""){
				alert("Las fechas no pueden ir vacías si el pedido es resurtimiento; si es libre solo agregue los productos!!!");
				$("#ftc_1").select();
				return false;
			}
			filtro3+=document.getElementById("ftc_1").value+"|";//año act
			if(document.getElementById("ftc_2").value==""){
				alert("Las fechas no pueden ir vacías si el pedido es resurtimiento; si es libre solo agregue los productos!!!");
				$("#ftc_2").select();
				return false;
			}
			filtro3+=document.getElementById("ftc_2").value;
		}
	//extraemos el filtro de incluir sin pedir
		var reg_invalidos=0;
		if(document.getElementById("incluye_invalidos").checked==true){
			reg_invalidos=1;
		}
		
	//mandamos emergente
		$("#emer_prod").css("display","block");
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/calculaPedido.php',
			cache:false,
			data:{filtro1:fto,
				provs:filtro2,
				fechas:filtro3,
				f_resurtimiento:filt_resurt,
				factor:f,
				prod_nuevos:p_nvo,
				acc:accion,
				id_oc:id_orden,
				filt_pend:pendientes,
				filt_invalidos:reg_invalidos,
				pov_sin_precio:sin_precio,
				st_prd:cuenta_deshabilitados
			},
			success:function(dat){
				var ax=dat.split("|");
				/*if(ax[0]!='ok'){
					$("#t2").html(dat);
					alert("Error!!!\n"+dat);
					$("#emer_prod").css("display","none");//ocultamos emergente
					return false;
				}*/
				/*if(ax[3]==''){

				}*/
				var ano="";
			//alert($("#tipo_pedido").val());
				if($("#tipo_pedido").val()==2){//si es resurtimiento
					var ano_aux=($("#ftc_1").val()).split("-");
					ano=ano_aux[0];
				}else{
					ano=dt.getFullYear()-1;
				}
			//asignamos los años en los encabezados
				$("#periodo_entradas").html(ano);
				$("#periodo_ventas").html(ano);
			//caregamos la respuesta
				$("#t2").html(ax[1]);
				$("#emer_prod").css("display","none");//ocultamos emergente
				//document.getElementById("genera").disabled=true;
				//$("#res_busc").css("display","block");
				
			}
		});
	}

//marca/desmarca proveedores
	function marca_desmarca(){
		var act=false;
		if(document.getElementById("tod_prov").checked==true){
			act=true;
		}
		var tam=document.getElementById('num_provs').value;
		for(var i=1;i<=tam;i++){
			document.getElementById("pr_"+i).checked=act;
		}
	}

//
	function check_individual(num){
		var tam=document.getElementById('num_provs').value;
		if(document.getElementById("pr_"+num).checked==true){
		//validamos que todos los proveedores esten marcados
			for(var i=1;i<=tam;i++){
				if(document.getElementById("pr_"+i).checked==false){
				//deshabilitamos check indicador
					document.getElementById("tod_prov").checked=false;
					reasignar_provs();
					return false;
				}
			//habilitamos check indicador
				document.getElementById("tod_prov").checked=true;
				reasignar_provs();
				return false;
			}
		}else{
		//deshabilitamos check indicador
			document.getElementById("tod_prov").checked=false;
			reasignar_provs();
			return false;
		}
	}
//reasigna array de proveedores
	function reasignar_provs(){
	//extraemos numero de proveedores existentes
		var tam=document.getElementById('num_provs').value;
		var provs="";
		for(var i=1;i<=tam;i++){
			if(document.getElementById("pr_"+i).checked==true){
				provs+=$("#pr_"+i).val()+"|";
			}
		}
		document.getElementById("ids_provs").value=provs;//asignamos nuevo arreglo con ids de proveedores
	}

	function carga_filtros_prom(id_prod,num,flag){
		var contenido_emergente='<div style="position:absolute;width:60%;left:20%;top:25%;background:rgba(225,0,0,.6);border:1px solid;color:white;font-size:20px;border-radius:15px;">'; 
/*Implementación Oscar 13.02.2019 para mostrar el título*/
		contenido_emergente+='<p align="center" style="font-size:25px;position:absolute;top:-15%;width:100%;"><b>Rango de fechas para cálculo de promedio y estacionalidades</b></p>';
/*Fin de cambio Oscar 13.02.2019*/
		contenido_emergente+='<button onclick="document.getElementById(\'emer_prod\').value=\'\';document.getElementById(\'emer_prod\').style.display=\'none\';"';
		contenido_emergente+='style="font-size:20px;padding:10px;color:white;background:red;position:absolute;top:-25px;right:-20px;border-radius:15px;">X</button><br>';
		contenido_emergente+='<p align="center">Seleccione el rango de fechas por filtrar!</p><p>Filtros de máximo de ventas:</p><br>';
		contenido_emergente+='<p align="center">Del: <input type="text" onclick="calendario(this);" id="fcha_max_del" value="'+fecha_max_del+'" style="padding:10px;border-radius:5px;">';
		contenido_emergente+='Al: <input type="text" onclick="calendario(this);" id="fcha_max_al" value="'+fecha_max_al+'" style="padding:10px;border-radius:5px;"></p>';
		contenido_emergente+='<p>Factor de ventas:</p>';
/*implementación de Oscar 13.02.2019 para omitir filtrado de configuración del producto en sucursal*/
		contenido_emergente+='<p align="right" style="position:absolute;top:50%;right:2%;color:#ffff9e;">';
			contenido_emergente+='<input type="checkbox" id="omitir_filtros"> <b> Omitir Filtros</b>';
		contenido_emergente+="</p>";
/*Fin de cambio Oscar 13.02.2019*/
		//contenido_emergente+='<p align="center">Del: <input type="text" onfocus="calendario(this);" id="fcha_prom_del" value="'+fecha_prom_del+'" style="padding:10px;border-radius:5px;">';
		//contenido_emergente+='Al: <input type="text" onclick="calendario(this);" id="fcha_prom_al" value="'+fecha_prom_al+'" style="padding:10px;border-radius:5px;"></p>';
		contenido_emergente+='<p align="center"><input type="number" id="factor_ventas" style="padding:10px;border-radius:5px;" placeholder="Factor" value="'+factor_ventas_adic+'"></p>';
		contenido_emergente+='<br><p align="center"><input type="button" onclick="asignar_fechas('+id_prod+','+num+',\''+flag+'\');" value="Aceptar" style="padding:10px;border-radius:5px;"><br><br></p>';
		contenido_emergente+='</div>';
		/*if(flag=='grafica'){
			$("#simula_tooltip_grafica").html(contenido_emergente);
			$("#simula_tooltip_grafica").css("display","block");	
		}else{*/
			$("#emer_prod").html(contenido_emergente);
			$("#emer_prod").css("display","block");	
		//}
		
		return true;
	}

/**************************************************************************************************************************************/
//configuración del producto
	function config_de_prod(id_prod,num){
	//alert(num);
	if(activa_cambio!=0){
		alert("Aun hay cambios sin guardar\nGuarde y después podra recorrer de producto");
		return false;
	}
/*implementacion Oscar 30.07.2019 para cambiar entre productos*/
	var prd_ant=0,prd_sig=0;
	
	if(document.getElementById('f_'+parseInt(num-1))){
		prd_ant=parseInt(parseInt(num)-1)+'~';
		prd_ant+=$("#id_p_"+parseInt(parseInt(num)-1)).val();

	}

	if(document.getElementById('f_'+parseInt(num+1))){
		prd_sig=parseInt(parseInt(num)+1)+'~';
		prd_sig+=$("#id_p_"+parseInt(parseInt(num)+1)).val();
	}

/*alert(id_prod);
/*implementación de Oscar 27.08.2018 para meter el filtrado inicial en la pantalla emergente*/
	if(fecha_max_del==''||fecha_max_al==''||factor_ventas_adic==''){//fecha_prom_del==''||fecha_prom_al==''
/*implementación de Oscar 13.02.2019 para omitir filtrado de configuración del producto en sucural*/
		if(asignacion_fechas==0){
			carga_filtros_prom(id_prod,num);
			asignacion_fechas=1;
			return false;
/*Fin de cambio Oscar 13.02.2019*/
		}
	}
/*Fin de cambio 27.08.2018*/

	//recolectamos los filtros de fechas
	var tipo_pedido,fpa_del,fpa_al,fpac_del,fpac_al;
	tipo_pedido=$("#tipo_pedido").val();
	if(tipo_pedido==2){
		fpa_del=$("#fta_1").val();
		fpa_al=$("#fta_2").val();
		fpac_del=$("#ftc_1").val();
		fpac_al=$("#ftc_2").val();
	}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/conf_adic.php',
			cache:false,
			data:{id:id_prod,
				tipo_ped:tipo_pedido,
				fmax_del:fecha_max_del,
				fmax_al:fecha_max_al,
				fact_prom:factor_ventas_adic,
				num_ant:prd_ant,
				num_sig:prd_sig,
				num_act:num
			},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				$("#emer_prod").html(ax[1]);
				$("#emer_prod").css("display","block");
			}
		});	
	}
//buscar producto en grid
	function busca_prod_grid(id_prod){
		if($("#tipo_pedido").val()==-1 && $("#id_orden_compra").val()==0){	
			alert("Elija un tipo de pedido antes de agregar productos!!!");
			$("#tipo_pedido").focus();
			return false;
		}
	//calculamos tamaño
		var tam=document.getElementById("filas_totales").value;
		var tmp="";
		for(var i=1;i<=tam;i++){
			if(document.getElementById("id_p_"+i)){//si existe
				tmp=document.getElementById("id_p_"+i).value;
				//alert(tmp);
				if(tmp==id_prod){
					resalta(i);
					return false;
				}
			}
		}
		//alert("el producto no existe en la lista desea agregarlo?");
		agrega_fila(id_prod);
	}

//agregar fila 
	function agrega_fila(id_prod){
		//alert("entra..");
	//calculamos tamaño
		var tam=0,tipo_filtro=-1,filt_ant_del=0,filt_ant_al=0,filt_act_del=0,filt_act_al=0;
//extraemos el tamaño de la tabla
		tam=parseInt(parseInt(document.getElementById("filas_totales").value)+1);
	//estraemos los filtros necesarios
		tipo_filtro=$("#tipo_pedido").val();//filtro de tipo de pedido
		if(tipo_filtro==2){
		//extraemos los rangos de fechas
			filt_ant_del=$("#fta_1").val();//filtro de periodo anterior "del"
			if(filt_ant_del==''||filt_ant_del==null){
				alert("Esta fecha no puede ir vacía");
				$("#fta_1").focus();
				return false;
			}
			filt_ant_al=$("#fta_2").val();//filtro de periodo anterior "al"
			if(filt_ant_al==''||filt_ant_al==null){
				alert("Esta fecha no puede ir vacía");
				$("#fta_2").focus();
				return false;
			}
			filt_act_del=$("#ftc_1").val();//filro de periodo actual "del"
			if(filt_act_del==''||filt_act_del==null){
				alert("Esta fecha no puede ir vacía");
				$("#ftc_1").focus();
				return false;
			}
			filt_act_al=$("#ftc_2").val();//filtro del periodo actual "al" 
			if(filt_act_al==''||filt_act_al==null){
				alert("Esta fecha no puede ir vacía");
				$("#ftc_2").focus();
				return false;
			}
		}//fin de if filtro==2
	//extraemos los filtros
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/add_fila.php',
			cache:false,
			data:{c:tam,flag:2,
				id:id_prod,
				id_oc:id_orden,
				tipo_ped:tipo_filtro,
				fpa_del:filt_ant_del,
				fpa_al:filt_ant_al,
				fpac_del:filt_act_del,
				fpac_al:filt_act_al},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					$("#t2").html(dat);
					alert("Error!!!\n"+dat);
					return false;
				}
				//alert(ax[1]);
				$("#lista_de_prods").append(ax[1]);
				//$("#res_busc").css("display","block");
				document.getElementById("filas_totales").value=tam;
				resalta(tam);
			}
		});
	}	
//
	function valida_camp_txt(e,num,id_oc){
		var tec=e.keyCode;
	//tecla abajo
		if(tec==40){
			resalta(num+1);
			return true;
		}
	//tecla arriba
		if(tec==38){
			if(num==1){
				//$("#busc").select();
				return true;
			}
			resalta(num-1);
			return true;
		}
	//tecla derecha
		if(tec==39){
			$("#c_p_"+num).focus();
			//alert();
			return true;
		}
		if(id_oc==0){
			recalcular(num);//recalculamos
		}else{
			recalcula_actualizar(num);
			hay_cambios=1;//marcamos que se realizó un cambio
		}
		return true;
	}

//guardar pedido
	function guarda_pedido(id_oc){
		$("#emer_prod").css("display","block");
		var tam=$("#filas_totales").val();//numero de regitros
		if(tam<1||tam==null){
			alert("El pedido está vacío, Ingrese productos para continuar!!!");
			$("#emer_prod").css("display","none");
			return false;
		}
		var provs=$("#ids_provs").val();
		var ax=provs.split("|");//separamos ids de proveedores
		var datos="";
		var cont_dats=0;
		for(var i=0;i<ax.length-1;i++){
			datos+=ax[i]+"°";
			for(var j=1;j<=tam;j++){
				if($("#fila_"+j)){
			/*implementación Oscar 13.09.2018 para no dejar guardar si se tienen productos sin proveedor*/
					if($("#c_p_"+i).val()==0||$("#c_p_"+i).val()==-1||$("#c_p_"+i).val()=='nvo'){//si no tiene proveedor
						alert("¡Aún hay productos sin proveedor, asigne un proveedor o eliminelos de la lista!");
						$("#emer_prod").css("display","none");
						resalta(i);
						return true;
					}
			/*fin de cambio 13.09.2018*/
					//alert($("#c_p_"+j).val());
					if($("#c_p_"+j).val()==ax[i]&&$("#cant_p_"+j).val()!=0&&$("#cant_p_"+j).val()!=''){

					//id del producto
						datos+=$("#id_p_"+j).val()+"~";

						var tmp=$("#combo_prov_"+j+" option:selected").text();
						var tmp_1=tmp.split(":");
						var pres=tmp_1[2].split('pzas');
						datos+=Math.round(parseFloat($("#valor_cajas_"+j).html().trim()))*pres[0]+"~";
					//	datos+=$("#cant_p_"+j).val()+"~";
					
					//aqui sacamos el valor del precio por pieza
						var tmp_2=tmp_1[0].split("$");
						datos+=tmp_2[1]+"~";/*$("#valor_monto_"+j).html();*/
						//if(j<tam){
						//if($("#nota_"+j).val()!=''){
							datos+=$("#nota_"+j).val()+"~";
						//}else{
						//	datos+='~';
						//}
					//guardamos el id de proveedor producto (implementado por Oscar 16.08.2019)
						tmp_1=tmp.split("//");
						//datos+=window.atob(tmp_1[1]);
						tmp_1[0]=window.atob(tmp_1[1]);
						//alert("tmp:\n"+tmp);
						datos+=window.atob(tmp_1[1]);
					
					//concatenamos el separador de filas
						datos+="|";
						//}
						cont_dats++;//incrementamos el contador
					}//fin de if
				}
			}
			if(i<ax.length-2){
				datos+="#";
			}
		}//fin de for i
		if(cont_dats<=0){
			alert("No se encontraron datos para guardar\nVerifique que todos los productos tengan proveedor seleccionado y una cantidad mayor a cero para continuar");
			$("#emer_prod").css("display","none");
			return true;
		}
		/*alert(datos);
		return false;*/
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:10,arr:datos},
			success:function(dat){
				//alert(dat);
				var ax_rs=dat.split("|");
				if(ax_rs[0]!='ok'){
					alert("Error:\n"+dat);
					$("#emer_prod").css("display","none");
				}else{//asignamos evento a a los botones y los hacemos visibles
					$("#env_correos").attr("onclick","enviaCorreoProv('"+ax_rs[1]+"');");
					$("#env_correos").css("display","block");
					$("#desc_ped_csv").attr("onclick","descargarCSVpedidos('"+ax_rs[1]+"');");
					$("#desc_ped_csv").css("display","block");
				//ocultamos botón de guardar pedido
					$("#guardado_pedido").css("display","none");
				//ocultamos emergente
					$("#emer_prod").css("display","none");
	/*implementación Oscar 19.02.2019 para poder guardar al generar nuevo cálculo*/
				//marcamos que ya hay un pedido guardado
					pedido_guardado=1;
	/*Fin de cambio Oscar 19.02.2019*/
				}
			}
		});
	}

//eliminar fila
	function elimina_fila(num,flag){
		if(flag==2){//si es eliminar precio de grid
			$("#fila_prec_"+num).remove();
			activa_cambio=1;//marcamos que hubo un cambio
			return true;
		}
		if(flag==3){//si es eliminar precio de grid
			$("#fila_prov_"+num).remove();
			activa_cambio=1;//marcamos que hubo un cambio
			return true;
		}
		$("#f_"+num).remove();	
	}

/***********************************FUNCIONES DE CONFIGURACIÓN ADICIONAL*************************************/
	function cambi_est_suc(obj){
		var dto=1;
		var reg=obj.value;
		if(obj.checked==false){
			dto=0;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:4,nvo_dto:dto,id_reg:reg},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					alert("ok");
				}
			}
		});
	}

//cambiar estacionalidad
	function cambia_estacionalidad(id_prod,id_suc,num){
		var dto_nvo=document.getElementById('nva_estac_'+num).value;
		if(dto_nvo==""){
			alert("La nueva estacionalidad no puede ir vacía!!!");
			document.getElementById('nva_estac_'+num).select();
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:5,id_pr:id_prod,id_sucur:id_suc,dato:dto_nvo},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
				//cargamos nueva estacionalidad en tabla
					document.getElementById("estacionalidad_"+num).innerHTML=dto_nvo;
				//deshabilitamos boton de edición
					document.getElementById("bot_conf_"+num).disabled=true;
				//limpiamos caja de texto
					document.getElementById('nva_estac_'+num).value="";
				}
			}
		});
	}

/******************************************FUNCIONES DE LISTAS DE PRECIOS*************************************/
//cargarprecios en emergente
	function carga_precios(id_prod,num){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/getPrecios.php',
			cache:false,
			data:{id:id_prod,contador:num},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					$("#emer_prod").html(ax[1]);
				}
			}
		});

	}	

var activa_cambio=0;//variable que detecta cambio
//activar botón de guardar cambios en precios
	function activa_edic_prec(e,num,flag){
		var tc=e.keyCode;
		//alert();
		if(tc==38 && flag!='nota'){//tecla arriba
			resalta_grid(flag,parseInt(num-1));
			return true;
		}
		if(tc==40 && flag!='nota'){//tecla abajo
			resalta_grid(flag,parseInt(num+1));
			return true;
		}
		var prefijo="";
		if(flag==1){
			prefijo="edita_precios_prod";
		}
		if(flag==2){
			prefijo="bot_guarda_conf";
		}
		if(flag==3){
			prefijo="edita_precios_prod";
		}
		if(flag=='nota'){
			prefijo="bot_guarda_conf";
		}
		$("#info_cambios").html("");
		activa_cambio=1;
		document.getElementById(prefijo).style.background="rgba(0,225,0,.5)";
	}

//inserta lista de precios en celda
	function cambia_list_prec(obj,num,flag){
		if(flag==1){
			var id_lista_pco=obj.value;
			var txt=$("#nvo_prec_"+num+" option:selected").text();
			if(obj.value==-1){
				alert("Debe seleccionar una lista de precios válida!!!");
				return false;
			}
		//asignamos a la variable el id de la lista de precios que ocupa el registro
			$("#nom_lista_prec_"+num).html('<input type="hidden" value="0" id="precio_'+num+'"><input type="hidden" value="'+id_lista_pco+'" id="id_lista_precio_'+num+'">'+txt);
			$("#fil_tot_precios").val(num);//asignamos nuevo tamaño
		}
		if(flag==2){
			var id_prov=obj.value;
			var txt=$("#nvo_pr_"+num+" option:selected").text();
			//alert(id_prov);
			$("#id_prov_"+num).html(id_prov);
			$("#nom_prov_"+num).html(txt);
		}
	}

//resaltado de grids pantalla emergente
	var reg_gr_res=0;
	function resalta_grid(flag,num){
		var prefijo_1="",prefijo_2="";
		if(num<=0){//detenemos acción
			return false;
		}
		if(flag==2){
			prefijo_1="#fil_gr_";
			prefijo_2="#nva_estac_";
		}
		if(flag==1){
			prefijo_1="#fila_prec_";
			prefijo_2="#de_";
		}
		if(flag==3){
			prefijo_1="#fila_prov_";
			prefijo_2="#p_";
		}
		if(reg_gr_res!=0){//verificamos si hay una fila resaltada
			$(prefijo_1+reg_gr_res).css('background','#FFF8BB');
		}
	//hacemos efecto hover en nueva fila
		$(prefijo_1+num).css('background','rgba(0,225,0,.6)');
		if(flag!=3){
			//$(prefijo_2+num).select();//enfocamos caja de texo
		}
		reg_gr_res=num;//asignamos nueva fila resaltada
		return true;
	}

//guardar cambios de configuración adicional
	function guarda_cambios_config(num){
		if(activa_cambio==0){
			return false;
		}
		var datos="";
	//obtenemos valores
		for(var i=1;i<=parseInt($("#fil_tot_conf_adic").val());i++){
		//habilitado/deshabilitado en sucursal
			if(document.getElementById("reg_gr_2_"+i).checked==true){
				datos+='1';
			}else{
				datos+='0';
			}
		//id del registro sys_suc_prod
			datos+='-'+$("#reg_gr_2_"+i).val()+"~";
		//sacamos nueva estacionalidad
			var tmp_est=$("#nva_estac_"+i).val();
			if(tmp_est!=0||tmp_est!=''){//capturamos nueva estacionalidad
				datos+=tmp_est;
			}else{//capturamos estacionalidad actual
				datos+=$("#estacionalidad_"+i).html();
			}
		//id de registro de estacionalidad
			datos+="-"+$("#id_estac_"+i).html();
		//separamos registros
			if(i<parseInt($("#fil_tot_conf_adic").val())){
				datos+='°';
			}
			//alert(datos);
		}//fin de for i
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:4,info:datos},
			success:function(dat){
				var ax_dt=dat.split("|");
				if(ax_dt[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					$("#info_cambios").html("Cambios guardados exitosamente!!!");
				//asignamos la nueva estacionalidad y limpiamos cajas de texto
					for(var i=1;i<=parseInt($("#fil_tot_conf_adic").val());i++){
						var tmp_caja=$("#nva_estac_"+i).val();
						if(tmp_caja!=''){
							$("#estacionalidad_"+i).html(tmp_caja);//asignamos valor de estaconalidad a  la celda de la estacionalidad
							$("#nva_estac_"+i).val("");//limpiamos entrada de texto
						}
					//guardamos la nota
						$("#guardar_nota_prods").click();
					//regresamos el color al boton de guardar
						$("#bot_guarda_conf").css("background","white");
					}
					activa_cambio=0;//resteamos variable de cambios
				}
			}
		});
	}

//modificar precios de producto
	function modifica_precios(id_prod,num){
	//alert(num);
		if(activa_cambio==0){
			//alert("No se han realizado modificaciones para guardar!!!");
			return false;
		}
		var datos="";
		var tam=$("#fil_tot_precios").val();
	//obtenemos datos del precio
		for(var i=1;i<=tam;i++){
			if(document.getElementById("fila_prec_"+i)){//si existe la fila
			//extraemos id del registro
				//datos+=$("#precio_"+i).val()+"~";
			//extraemos el id de la lista de precio
				datos+=$("#id_lista_precio_"+i).val()+"~";
			//extraemos minimo cant
				datos+=$("#de_"+i).val()+"~";
			//extraemos maximo cant
				datos+=$("#a_"+i).val()+"~";
			//extraemos el monto
				datos+=$("#mont_"+i).val()+"~";
			//extraemos oferta
				if(document.getElementById('ofer_'+i).checked==true){
					datos+="1";
				}else{
					datos+="0";
				}
				if(i<tam){
				//concatenamos separador de registros
					datos+="°";
				}
			}//fin de if existe fila 
		}//fin de for i
		/*alert(datos);*/
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:6,id:id_prod,info:datos,contador:num},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					$("#info_cambios").html("Cambios guardados exitosamente!!!");
				//	alert("Cambios guardados exitosamenete!!!");
					activa_cambio=0;//reseteamos variable de cambios
					carga_precios(id_prod,num);
				}
			}
		});
	}

//modificación de proveedores
	function modifica_proveedores(id_prod){
		if(activa_cambio==0){//detecamos si hubo cambios
			alert("No hay modificaciones por guardar!!!");
			return false;
		}
		var datos="";
		var tam=$("#fil_tot_provs").val();//alert(tam);
	//sacamos valores de los proveedores
		for(var i=1;i<=tam;i++){
			if(document.getElementById("fila_prov_"+i)){//si existe la fila
			//capturamos id del proveedor
				if($("#id_prov_"+i).val()!=''&&$("#id_prov_"+i).val()!=null){
					datos+=$("#id_prov_"+i).val()+"~";
				}else{
					datos+=$("#id_prov_"+i).html()+"~";
				}
			//capturamos precio
				datos+=$("#p_"+i).val()+"~";
			//capturamos cantidad
				datos+=$("#c_"+i).val()+"~";
			//capturamos clave de proveedor
				datos+=$("#clave_"+i).val()+"~";//implementado el 17.07.2018
			//capturamos el id de proveedor_producto
				datos+=$("#id_prov_prod_"+i).html();
			//concatenamos separador
				if(i<tam){
					datos+="°";
				}
			}
		}//fin de for i
/*Implementación Oscar 13.02.2019 para guardar la nota en la tabla de productos*/
	//enviamos datos por ajax
		$.ajax({	
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:1,info:datos,id:id_prod},
			success:function(dat){
				var ax_rs=dat.split("|");
				if(ax_rs[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				$("#info_cambios").html("Cambios guardados exitosamente!!!");
				activa_cambio=0;//reseteamos variable de cambio
				//adm_prov_prod(id_prod);
				carga_proveedor_prod(resaltado,id_prod);
//				alert(resaltado);
				return true;
			}
		});
/*Fin de cambio Oscar 13.02.2019*/
	}
//funcion que guarda la nota
	function guarda_nota(id_prod){
		var nota=$("#campo_nota").val();//extraemnos el valor de la nota temporal
	//si la nota esta vacía 
		if(nota==''||nota==null){	
			var icono='<img src="../../../img/especiales/config.png" height="20px"';
			icono+=' onclick="config_de_prod('+$("#id_p_"+resaltado).val()+');" id="b1_'+resaltado+'" class="bot" title="Configuración del producto">';
			icono+=' <input type="hidden" id="nota_'+resaltado+'" value="">';
		}else{
			var icono='<img src="../../../img/especiales/config_2.png" height="20px"';
			icono+=' onclick="config_de_prod('+$("#id_p_"+resaltado).val()+');" id="b1_'+resaltado+'" class="bot" title="'+nota+'">';
			icono+=' <input type="hidden" id="nota_'+resaltado+'" value="'+nota+'">';
		}	
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{fl:'nota_producto',txt:nota,id:id_prod},
			success:function(dat){
				if(dat!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
			}
		});	
	//reinsertamos el icono
		$("#config_"+resaltado).html(icono);
	}
//funcion que recalcula cuando es modificación
	function recalcula_actualizar(num){
		$("#valor_cajas_"+num).html($("#cant_p_"+num).val()/$("#pres_"+num).val());
	}
//función que modifica el pedido a proveedor
	function guarda_cambios_oc(){
	//validamos que no haya cambios
		if(hay_cambios==0){
			alert("No hay cambios por guardar");
			return false;
		}
	//extraemos los nuevos datos 
		var tam=$("#filas_totales").val();
		var datos="";
		for(var j=1;j<=tam;j++){
			if(document.getElementById("f_"+j)){
				if($("#cant_p_"+j).val()!=0 && $("#cant_p_"+j).val()!=''){
					datos+=$("#id_p_"+j).val()+"~";//id del producto
					datos+=$("#cant_p_"+j).val()+"~";//cantidad del producto
					
					var tmp=$("#combo_prov_"+j).html().trim();
					var tmp_1=tmp.split("$");
					datos+=tmp_1[1]+"~";/*$("#valor_monto_"+j).html();*/
					datos+=$("#nota_"+j).val();//nota del producto en el pedido
					datos+="|";//concatenamos el separador
				}//fin de if
			}
		}//fin de for i
		
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			cache:false,
			data:{id_compra:id_orden,fl:'actualizar',arr:datos},
			success:function(dat){
				var ax_rs=dat.split("|");
				if(ax_rs[0]!='ok'){
					alert("Error:\n"+dat);
				}else{//asignamos evento a a los botones y los hacemos visibles
					alert("Orden de compra modificada con éxito");
					hay_cambios=0;//resetesamos variable indicadora de cambios
				}
			}
		});
	}
var simulador_en_uso=0;
	function simulador_tooltip(obj,id_sucursal){
		var coordenadas = $(obj).position();//obtenemos coordenadas
		if(simulador_en_uso==1){
				esconde_tooltip();
				return true;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/tooltipEstacionalidades.php',
			cache:false,
			data:{id_suc_:id_sucursal},
			success:function(dat){
				$("#simula_tooltip").html(dat);//cargamos los datos
				$("#simula_tooltip").offset({top: parseInt(coordenadas.top+70), left:parseInt(coordenadas.left)+275});//asignamos coordenadas
				$("#simula_tooltip").css("display","block");//hacemos visible la simulacion del tooltip
				simulador_en_uso=1;
			}
		});
	}
	function esconde_tooltip(obj){
		$("#simula_tooltip").offset({top:0,left:0});//reseteamos coordenadas
		$("#simula_tooltip").css("display","none");
		simulador_en_uso=0;
	}

	function graficar_inv_vtas(id_prod){
		//fecha_max_del=$('#fcha_max_del').val();
		if(fecha_max_del==''||fecha_max_del==null){/*
			alert("La fecha inicial no puede ir vacía!!!");
			$("#fcha_max_del").focus();*/
			carga_filtros_prom(id_prod,0);//,'grafica'
			return false;
		}
		//fecha_max_al=$('#fcha_max_al').val();
		if(fecha_max_al==''||fecha_max_al==null){/*
			alert("La fecha final no puede ir vacía!!!");
			$("#fcha_max_al").focus();*/
			carga_filtros_prom(id_prod,0);//,'grafica'
			return false;
		}
	//enviamos datos por ajax
		
		$.ajax({
			type:'post',
			url:'ajax/grafica_2.php',
			cache:false,
			data:{id_prodcto:id_prod,fcha_del:fecha_max_del,fcha_al:fecha_max_al},
			success:function(dat){
//alert(dat);
				$("#simula_tooltip_grafica").html("");
				$("#simula_tooltip_grafica").html(dat);
				$("#simula_tooltip_grafica").css('display','block');
			}
		});
	}
/*
	function bloquear_simulacion(flag){
		tool_bloqueo=0;
	}*/