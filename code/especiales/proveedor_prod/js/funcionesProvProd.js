var prov_desp=0;
/*****
	-Cuando se quitan filas que se pueda recorrer normal con las flechas; lo que se tiene que hacer es reasignar los ids y datos de los productos que 
	 que se empatan automática y manualmente.
*****/
	function enfoca(obj,flag,num){
		if(flag==1){	
			$("#fila_"+num).css("background","rgba(0,225,0,.5)");
		}
	}

	function desenfoca(obj,flag,num){
		if(flag==1){
			var color="";
			if(num%2==0){
				color="#E6E8AB";
			}else{
				color="#BAD8E6";
			}
			$("#fila_"+num).css("background",color);
		}
	}

	
	function quitar_sin_empate(){
		var tam=$("#total_filas").val();
		var validos=0;
		//alert(tam);
	//recorremos tabla
		for(var i=1;i<=tam;i++){
			var id=$("#id_prod_"+i).attr("value");
			if(id==0){
				$("#fila_"+i).remove();
			}else{
				validos++;
			}
		}//fin de for i
		alert("Registros Validos: "+validos);
	}

	
/*Implementación Oscar 15.02.2019 para resear los precios de proveedor*/
	function reseta_precios_prov(){
		var id_prov=$("#id_prov").val();
		if(id_prov==0){
			alert("Primero debe de seleccionar un proveedor!!!");
			return false;
		}
		if(!confirm("Realmente desea resetear a 0 los precios del proveedor "+$("#id_prov option:selected").text()+"?\n Este proceso deberá de hacerse solo una vez"+
			"al inicio de la temporada; Verifique que no se haya realizado")){
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/guardaRegistros.php',
			cache:false,
			data:{flag:'resetear_precios',id_pro:id_prov},
			success:function(dat){
				if(dat!='ok'){
					alert("Error!!!\n"+dat);
				}else{
					alert("Precios de Proveedor reestablecidos exitosamente!!!");
					location.href="proveedor_prod.php?prv="+id_prov;
				}
			}
		});
	}
/*Fin de cambio Oscar 15.02.2019*/
//eliminar registro
function eliminar(num){
	var confirmar=confirm("Realmente desea eliminar este proveedor para este producto???");
	if(confirmar==false){
		return false;
	}
//quitamos fila de la tabla
	$("#fila_"+num).remove();
	return true;
}
//función que recarga la página
function carga_prov_inicial(obj){
	location.href="proveedor_prod.php?prv="+obj.value;
}

function posicion_coordenadas(obj){
	var coordenadas = $(obj).position();
	$("#res_busc_grid").offset({top: parseInt(coordenadas.top+22), left: coordenadas.left});
	//$("#res_busc_grid").css("display","block");
}

//buscador
function buscador(e,num,flag){
	var tca=e.keyCode;
	var txt='';
	if(tca==27){
		$("#res_busc_grid").css("display","none");
		return false;
	}
	if(tca==38){//
		//if($("#opc_gr_1")){$("#opc_gr_1").focus();}
		var num_aux=parseInt(num-1);
		$("#fila_"+num_aux).focus();
		$("#c_1_"+num_aux).select();
		return true;
	}
	if(tca==40){//
		//if($("#opc_gr_1")){$("#opc_gr_1").focus();}
		var num_aux=parseInt(num+1);
		$("#fila_"+num_aux).focus();
		$("#c_1_"+num_aux).select();
		return true;
	}
	if(tca==40){
		$("#contenido_pro_prod").next("input");
		return true;
	}
	
	txt=$("#c_1_"+num).val();
	if(txt.length<=2){	
		$("#res_busc_grid").css("display","none");//ocultamos div
		$("#res_busc_grid").html("");//limpiamos div
		$("#res_busc_grid").offset({top:0,left:0});//reseteamos coordenadas
		return false;
	}

//obtenemos coordenadas del elemento
	//var coordenadas = $("#c_1_"+obj).position();
	var coordenadas = $("#c_1_"+num).position();
	//alert(coordenadas.top+"\n"+coordenadas.left);
//enviamos datos por Ajax
	$.ajax({
		type:'post',
		url:'ajax/buscaProds.php',
		cache:false,
		data:{clave:txt,fl:1,posicion:num},
		success: function(dat){
			//alert(dat);
			var ax=dat.split("|");
			if(ax[0]!='ok'){
				alert("Error!!!\n"+dat);
				return false;
			}
		//asignamos coordenadas, cargamos coincidencias, mostramos div de resultados
			$("#res_busc_grid").html(ax[1]);
			$("#res_busc_grid").offset({top: parseInt(coordenadas.top+22), left: coordenadas.left});
			$("#res_busc_grid").css("display","block");
			return true;
		}
	});
}
//empatar manualmente
function empate_manual(num,posic){
//obtenemos valores de la opción
	var a1=$("#datos_opc_"+num).val();
//separamos valores
	var aux=a1.split("~");
	//document.getElementById("id_prod_"+posic).value=aux[0];//id_del producto
	$("#id_prod_"+posic).attr("value",aux[0]);//id_del producto
	//alert("c_1_"+num);
//	document.getElementById("c_1_"+posic).value=aux[1];
	$("#c_1_"+posic).val(aux[1]);

	$("#nom_prd_sys_"+posic).html(aux[2]);
	//$("#c_1_"+posic).attr("disabled");
//limpiamos búsqueda	
	$("#res_busc_grid").html("");
	$("#res_busc_grid").css("display","none");
}

var opc_resaltada=0;
function resalta_opc(e,num){
	var tca=e.keyCode;
	var temp=0;
	if(tca==40){//abajo
		temp=parseInt(num+1);
	}
	if(tca==38){//arriba
		temp=parseInt(num-1);
	}
	var tca=e.keyCode;
	if(opc_resaltada!=0){
		$("#opc_gr_"+opc_resaltada).css("background","white");
	}
	$("#opc_gr_"+temp).css("background","gray");
	$("#opc_gr_"+temp).focus();
	opc_resaltada=temp;//asignamos nuevo valor a fila resaltada
	return true;
}
/***********************************************************************************************************************************/

//desplegar lista de proveedores
function ver_lista_prov(){
	var acc="block";
	if(prov_desp==1){
		acc="none";
	}
	$("#lista_de_proveedores").css("display",acc);
	if(prov_desp==1){
		prov_desp=0;
	}else{
		prov_desp=1;
	}
	return true;
}

//importación de CSV
function importarCSV(flag){
	if(flag==1){
		$("#files").click();//abrimos explorador de archivos
	}
}	

//guardar grid
function guarda_grid(){
	var datos="";
//sacamos tamaño de la tabla
	if(!$("#total_filas")||$("#total_filas").val()<1){
		alert("No hay datos para guardar!!!"+$("#total_filas").val());
		return false;
	}
	var tam=$("#total_filas").val();
//recorremos tabla
	for(var i=0;i<tam;i++){		
		var id=$("#id_prod_"+i).attr("value");
		if(id==0){
			alert("Hay registros inválidos, busque manualmente el producto!!!");
			$("#c_1_"+i).focus();
			return false;
		}
		if(document.getElementById("fila_"+i)){//si la fila existe
			datos+=id+"~"+$("#c_2_"+i).html()+"~"+$("#c_3_"+i).html()+"~"+$("#c_4_"+i).html();		
		//asignamos separador
			if(i<tam){
				datos+="|";
			}
		}//fin de si la fila existe
	}//fin de for i alert(datos);
//id proveedor
	var prov=$("#id_prov").val();
//enviamos datos por ajax
	$.ajax({
	type:'post',
	url:'ajax/guardaRegistros.php',
	cache:false,
	data:{id_prov:prov,info:datos},
	success: function(dat){
		//var ax=dat.split("|");
		if(dat!='ok'){
			alert("Error!!!\n"+dat);
			return false;
		}
		alert("registros insertados correctamente!!!");
		location.reload();
		return true;
	}
	});	
}

//funcion que exporta el CSV
function exportarCSV(){
	//obtenemos id_del proveedor
	var id_pr=$("#id_prov").val();
	location.href="ajax/getDatos.php?prov="+id_pr+"&flag=1";	
}