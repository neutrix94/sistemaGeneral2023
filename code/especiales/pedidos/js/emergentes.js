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
		if(activa_cambio==1 && !confirm("Hay cambios sin guardar, Desea salir de esta pantalla?")){
			return false;
		}
		activa_cambio=0;
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

/*implementación Oscar 02.09.2019 para habilitar/deshabilitar en Pagina Web*/
	function habilita_deshabilita_web(id_prd){
		var tipo=0;
		if(document.getElementById('check_omit_web').checked==true){
			tipo=1;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/proBD.php',
			data:{fl:'omitir_web',valor:tipo,id:id_prd},
			success:function(dat){
				$("#info_cambios").html(dat);
			}
		});
	}
/*Fin de cambio Oscar 02.09.2019*/