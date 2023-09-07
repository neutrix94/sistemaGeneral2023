<script type="text/javascript">

var det_pqt_edit=0;//variable de referencia para salir de emergente en paquetes
	function recargaListadoPaquetes(obj,flag){
		var txt_bsc=obj.value;
		var id_res="#res_paquete_"+flag;
		var fil_tot=$('#total_resultados').val();
		if(txt_bsc.length<=2){
			txt_bsc='muestra_todos';
		//removemos filas
//alert(fil_tot);
		for(var i=1;i<=fil_tot;i++){
			if($("#fila_"+i)){
				$("#fila_"+i).remove();
			}
		}
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{fl:flag,texto:txt_bsc},
			success:function(dat){
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
		//cargamos resultados en la tabla
			$("#contenido_paquetes").html(aux_res[1]);	
			}
		});

	}

/**/
	function modificar_img(id_paquete){
		$("#archivo").click();
	//cargamos el formulario
	}
	function cambia_img(){
		$("#sube_img_pqt").css("display","block");
	}
	function cambia_imagen_bd(id){
		$("id_paq_img").val(id);
	//enviamos el formulario
		$("#form_img_pqt").submit();
		setTimeout(function(){modificar(1,'',id);},2000);
	}

	function editar_emerge(flag){
		/*if(flag==1){
			$("#sub_emerge_img").css("display","block");
		}*/
	}
/**/

	function agrandar_img(id_paquete){
		//alert(id_paquete);
		$("#img_paquete").attr("class","img_ampliada");
		$("#img_paquete").attr("onclick","restaura_tam_img("+id_paquete+")");
	}

	function restaura_tam_img(id_paquete){
		//alert(id_paquete);
		$("#img_paquete").attr("class","img_normal");
		$("#img_paquete").attr("onclick","agrandar_img("+id_paquete+")");
	}
/**/

//acciones de regstros
	function modificar(flag,num,id_reg){
	var url_arch="ajax/formPaquetes.php";
	//si es eliminar paquete
		if(flag==3){
			var conf=confirm("Realmente desea eliminar esste paquete?");
		//validamos eliminación de paquee con el usuario
			if(conf==false){
				return false;
			}
		//asignamos flag y archivo al que se enviarán datos por Ajax
			flag=10;
			url_arch="ajax/buscadorPaquetes.php";
		}
		//
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:url_arch,
			cache:false,
			data:{fl:flag,id:id_reg},
			success:function(dat){
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
			if(flag<3){
			//cargamos resultados en la tabla
				$("#cont_emergente_pqt").html(aux_res[1]);
				$("#emer_pqt").css("display","block");	
			}else if(flag==10){//si es eliminar paquete
				$("#fila_"+num).remove();
			}
			}
		});		
	}
//buscador detalle de paquetes
	function buscador_detalle(e,obj){
		var tca=e.keyCode;
	//enfocamos la primera celda de opciones
		if(tca==40||tca==13){
			resaltar_opc(0,1);	
			return false;
		}
		var txt_busc=obj.value;
		if(txt_busc.length<=2){
			if(txt_busc.length<2){
				return false;
			}
			$("#resp_busc").html("");
			$("#resp_busc").css("display","none");
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{fl:2,texto:txt_busc},
			success: function(dat){
				var ax_r=dat.split("|");
				if(ax_r[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				$("#resp_busc").html(ax_r[1]);
				$("#resp_busc").css("display","block");
			}
		});
	}

var opc_resaltada=0;
//resaltar opciones con flechas del teclado
	function resaltar_opc(num,flag){
		if(flag!=1){
			var tca=flag.keyCode;
			if(tca==40){//abajo
				flag=1;
			}
			if(tca==38){//arriba
				if(opc_resaltada==1){
				//regresamos atrbutos por defecto a opción, enfocamos buscador
					$("#resp_busc_prod_"+opc_resaltada).css("background","white");				
					$("#busc_det_paq").select();
					return false;
				}else{
					flag=-1;
				}
			}
			if(tca==13){//intro
				asignaValorBuscador(num);
				return false;
			}
		}
	//regresamos atributos por default de celda resaltada
		if(opc_resaltada!=0){
			$("#resp_busc_prod_"+opc_resaltada).css("background","white");
		}
		var nva_resaltada=parseInt(parseInt(num)+parseInt(flag));
//alert("here: "+nva_resaltada);
		$("#resp_busc_prod_"+nva_resaltada).css("background","rgba(225,0,0,.5)");
		$("#resp_busc_prod_"+nva_resaltada).focus();	
		opc_resaltada=nva_resaltada;//asignamos nuevo valor de celda resaltada
	}

//asignar valor del buscador
	function asignaValorBuscador(num){
	//validamos si el producto se encuentra en la tabla del detalle
		var tam=$("#total_detalles_paquete").val();//numero de registros en la tabla
		var tmp="";//temporal
		var  id_prod=$("#r_b_"+num).html();//id del producto seleccionado
		for(var i=1;i<=tam;i++){
		if($("#detalle_"+i)){//verificamos que la fila exista
				tmp=$("#c_2_"+i).html();
				if(tmp==id_prod){
				//ocultamos resultados de búsqueda
					$("#resp_busc").css("display","none");
				//enfocamos fila
					$("#c_4_"+i).select();
					return false;
				}
			}
		}
	//agregammos datos en buscador
		var dsply=$("#r_b_"+num).html();
		dsply+="|"+$("#n_b_"+num).html();
		$("#busc_det_paq").val(dsply);//asignamos nombre del producto en barra de buscador
		$("#cant_add").val(1);//asignammos 1 por default a la cantidad a ingresar
		$("#cant_add").select();//seleccionamos campo de cantidad
		$("#resp_busc").css("display","none");//ocultamos resultados de búsqueda
	//asignamos valores a bevento de botón
		$("#add_bscdor").attr("onclick","agregaFila("+tam+")");
		$("#cant_add").attr("onkeyup","valida_key_cant("+tam+",event)");	
	}
//agregar con intro
	function valida_key_cant(t,e){
		var tca=e.keyCode;
		if(tca==13){//si es intro
			agregaFila(t);
		}else{
			return false;
		}
	}
//agregar fila en detalle de paquetes
	function agregaFila(tam){//recibimos tamaño de tabla
	//obtenemos valores de la opción
		var cadena=$("#busc_det_paq").val();
		var tam=$("#total_detalles_paquete").val();
		if(cadena==""||cadena==null){
			alert("Debe seleccionar un producto antes de agregar!!!");
			$("#busc_det_paq").select();
			return false;
		}
		var cantd=$("#cant_add").val();
		if(cantd<=0||cantd==""){
			alert("Debe ingresar una cantidad numérica mayor a cero!!!");
			$("#cant_add").select();
			return false;
		}
		var ax=cadena.split("|");
		var id_prod=ax[0];//guardamos id del producto seleccionado
	//ocultamos resultados de búsqueda
		//$("#resp_busc").css("display","none");
		tam=parseInt(parseInt(tam)+1);

	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{fl:3,id:id_prod,cont:tam,cantidad:cantd},
			success:function(dat){
				var ax_r=dat.split("|");
				if(ax_r[0]!='ok'){
					alert("Error\n"+dat);
					return false;
				}
			//activamos botón de edición
				det_pqt_edit=1;
				document.getElementById("gda_nvo_pqt").disabled=false;
			//agregamos la nueva fila en la tabla
				$("#detalle_paquete").append(ax_r[1]);
			//actualizamos numero de filas de esta tabla
				$("#total_detalles_paquete").val(tam);
			//limpiamos cajas de texto
				$("#busc_det_paq").val("");
				$("#cant_add").val("");
			//enfocamos de nuevo al buscador
				$("#busc_det_paq").focus();
				return true;
			}
		});
	}

//quitar fila
	function eliminaDetalle(flag,num){
		$("#detalle_"+num).remove();
	//activamos botón de edición
		det_pqt_edit=1;
		document.getElementById("gda_nvo_pqt").disabled=false;
		return false;
	}

/*
//guarda edición de detalle de paquete
	function gEdicion(flag,num){
	//obtenemos valores necesarios para la modififcación
		var id_reg,cantidad_p;
		id_reg=$("#c_1_"+num).html();
		//alert();
		cantidad_p=$("#c_4_"+num).val();
		if(flag==1){//si es update
			flag=4;
		}if(flag==2){//si es delete
			var confirma=confirm("Realmente desea eliminar este producto del paquete?");
			if(confirma==false){
				return false;
			}
			flag=5;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{fl:flag,id:id_reg,nva_cant:cantidad_p},
			success:function(dat){
				var arr_ax=dat.split("|");
				if(arr_ax[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
				if(flag==4){
				//deshabilitamos el botón de edición
					document.getElementById('b_d_1_'+num).disabled=true;
					$("#b_d_1_"+num).css("border-radius","0");
					$("#b_d_1_"+num).css("background","gray");
					return true;
				}else if(flag==5){
				//removemos fila
					$("#detalle_"+num).remove();	
				}
			}
		});
	}
*/
//guardar nuevo registro
	function guardaNuevoRegistro(flag,num){
		if(flag=="insertar_nuevo"){
			if(!confirm("Este paquete se guardará como un paquete nuevo, Desea continuar?")){
				return false;
			}
			flag='insertar';
		}
	//sacamos datos de cabecera de paquete
		var id_prod="",cantidad="";
		var stat=1;
		var nombre="";
		var id_pqt=$("#id_encab_pqt").val();//id del paquete
		if(flag=="insertar"||flag=="actualizar"){
		//obtenemos valores
			if(document.getElementById("stat_paq").checked==false){//valor del checkbox
				stat=0;
			}
			nombre=$("#nom_paq").val();
			if(nombre==''){
				alert("El paquete debe tener un nombre!!!");
				$("#nom_paq").select();
				return false;
			}
		//asignamos flag para el archivo que realiza las consultas
			if(flag=="insertar"||flag=="insertar_nuevo"){
				flag=8;	
			}
			if(flag=="actualizar"){
				flag=9;
			}
		}
		if(flag==3){//detalle de paquete
			id_prod=$("#c_2_"+num).html();//id del producto
			cantidad=$("#c_4_"+num).val();//cantidad del producto
			flag=7;
		}
	//sacamos detalle del paquete
		var arr_dtlle="";
		var tam=$("#total_detalles_paquete").val();//total de filas
	//alert(tam);
	//recolectamos valores de filas
		for(var i=1;i<=tam;i++){
			if(document.getElementById("detalle_"+i)){
				arr_dtlle+=$("#c_2_"+i).html();//id del producto
				arr_dtlle+="~"+$("#c_4_"+i).val();//cantidad del producto
				if(i<tam){
					arr_dtlle+="|";//concatenamos separador
				}
			}
		}
		//alert(arr_dtlle);
		//return false;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{fl:flag,id:id_pqt,cant:cantidad,id_producto:id_prod,status:stat,nom:nombre,arr:arr_dtlle},
			success:function(dat){
				var ax_res=dat.split("|");
				if(ax_res[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
			//si es nueva incerción
				if(flag==8){
					alert("Paquete registrado existosamente!!!");
					$("#busc_det_paq").css("display","block");//mostramos buscador
					$("#id_encab_pqt").val(ax_res[1]);//asignamos a variable de referencia el nuevo id del paquete para poder insertar detalles
				}
			//si es actualización
				if(flag==9){
					alert("Paquete modificado exitosamenente!!!");
				}	
			//asignamos nuevo moto del paquete
				$("#cost_paq").val(ax_res[2]);
			//desactivamos botón de edición
			det_pqt_edit=0;
			document.getElementById("gda_nvo_pqt").disabled=true;
			location.reload();
			}
		});
	}
//habilita botón de guardado
	function habilitaEdicionPaquete(flag,num){
		if(flag==1){
		//habilitamos boton de guardado de cabecera de paquete
			document.getElementById("gda_nvo_pqt").disabled=false;
			document.getElementById("gda_nvo_pqt_1").disabled=false;
		}
		if(flag==2){
		//habilitamos boton de cambios del detalle del paquete 
			document.getElementById("b_d_1_"+num).disabled=false;
		}
		det_pqt_edit=1;//marcamos que hay un cambio...
		return true;
	}
//cerrar emergente de paquete
	function cierra_detalle(){
		if(det_pqt_edit==1){
			var conf=confirm("Hay cambios sin guardar\n¿Realmente desea salir sin Guardar?");
			if(conf==false){
				return false;
			}
		}
		location.reload();
	}


	function abre_config_paqt(id_paq){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/sucursalPaquetes.php',
			cache:false,
			data:{id_pqt:id_paq},
			success:function(dat){
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
			//cargamos resultados en la tabla
				$("#cont_emergente_pqt").html(aux_res[1]);
				$("#emer_pqt").css("display","block");	
			}
		});
	}	

	function guarda_config_pqt(tope){
	//recolectamos los datos de la tala
		var datos='';
		for(var i=1;i<=tope;i++){
			datos+=$("#sp_0_"+i).html()+"~";//registro de sucursal paquete

			if($("#sp_1_"+i).prop("checked")){//estado de sucursal paquete
				datos+="1";
			}else{
				datos+="0";
			}
			datos+="|";
		}//fin de for i
		//alert(datos);//return false;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{dats:datos,fl:'sucursal_paqt'},
			success:function(dat){
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					alert("Configuración del paquete en Sucursal guardada correctamente!!!");
					location.reload();
				}
			}
		});
	}

	function crea_transfer(id_pqt,tr_gen,perm){
		var observacion="",msg='',pss='';
		pss=$("#pss_enc").val();
		if(perm==1 && pss.length<=0 ){
			alert("La contraseña de encargado no puede ir vacia!!!");
			$("#pss_enc").focus();
			return false;
		}else{
		//capturamos la observacion
			observacion=$("#obs_tr").val();
		}
		if(tr_gen==1 && perm!=1){
			msg+='<br><br><br><br><p align="center" style="color:white;font-size:25px;">Este paquete ya habia generado una Transferencia, '
			msg+='recuerde que solo se puede hacer una transferencia para cada paquete,<br>Contacte al administrador para eliminar la transferencia anterior<br><br>';
			msg+='Indique la razon por la que vuelve a hacer la transferencia!!!<br>';
			msg+='<textarea id="obs_tr" style="position:absolute;width:50%;left:25%;height:15%;" placeholder="Observaciones..."></textarea>';
			msg+='<br><br><br><br><br>Ingrese la contraseña de encargado:<br>';
			msg+='<input type="password" id="pss_enc" style="padding:10px;width:250px;"><br><br>';
			msg+='<button style="position:absolute;left:40%;padding:10px;" onclick="crea_transfer('+id_pqt+','+tr_gen+',1);">Continuar</button>';//<table 
			msg+='<button style="position:absolute;right:40%;padding:10px;" onclick="cierra_detalle();">Cancelar</button>';//<td align="center"></td></tr></table> 
			msg+="</p>";
		
			$("#cont_emergente_pqt").html(msg);
			$("#emer_pqt").css("display","block");
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/buscadorPaquetes.php',
			cache:false,
			data:{id:id_pqt,fl:'crea_transfer',pass:pss,ya_tr:tr_gen,obs:observacion},
			success:function(dat){
				var aux_res=dat.split("|");
				if(aux_res[0]!='ok'){
					if(aux_res[0]=='pss_no'){
						alert(aux_res[1]);
						$("#pss_enc").select();
						return false;
					}
					alert("Error!!!\nRecargue la pantalla y vuela a intentar\n"+dat);
					return false;
				}else{
					alert("Transferencia creada exitosamente!!!");
					window.open("../code/general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==");
					location.reload();
				}
			}
		});		
	}

</script>