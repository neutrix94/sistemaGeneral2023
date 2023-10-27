	//$('#dispara_modal').click();
	function carga_consulta(flag,extra){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/consultas.php',
			cache:false,
			data:{fl:flag,extra},
			success:function(dat){

			}
		});
	}

	function carga_filtros(id){
	//enviamos datos por ajax para obtener los filtros
		$.ajax({
			type:'post',
			url:'header_consultas.php',
			cache:false,
			data:{id_herramienta:id},
			success:function(dat){
				var aux=dat.split("___");
				$("#txt_consulta").val(aux[0]);
				$("#filtros").html(aux[1]);
				$("#edita_consulta").attr("onclick","carga_form("+id+");");
				$("#resultados").html("");
			}
		});
	}
	function habilitar_txt_consulta(){
		$("#txt_consulta").removeAttr("disabled");
	}

	function carga_form(id_consulta){
		if(id_consulta==-1){
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/formulario.php',
			cache:false,
			data:{id:id_consulta},
			success:function(dat){
				$("#contenido_emergente").html(dat);
				$("#emergente").css("display","block");
			}
		});
	}

	function guarda(flag){
		var id,tit,cons,desc,filt_suc,filt_fcha1,filt_fcha2,filt_fam,filt_tipo,filt_subtipo,filt_color,
		filt_alm,filt_ext, tipo_cons;
		id=$("#id_herramienta").val();
		
		if(flag==0){
			id="(Automático)";
		}

		tit=$("#titulo").val();
		if(tit.length<=0){
			alert("El título no puede ir vacío!!!");
			$("#titulo").focus();
			return false;
		}
		
		cons=$("#consulta").val();
		if(cons.length<=0){
			alert("La consulta no puede ir vacía!!!");
			$("#consulta").focus();
			return false;
		}
		
		desc=$("#descripcion").val();
		if(desc.length<=0){
			alert("La descripción no puede ir vacía!!!");
			$("#descripcion").focus();
			return false;
		}
	/*implementacion Oscar 2021 para separar tipos de Consulta*/
		tipo_cons = $('#query_type').val();
	/*Fin de cambio Oscar 2021*/
		filt_suc=$("#campo_filtro_sucursal").val();
		filt_fcha1=$("#campo_filtro_fecha_1").val();
		filt_fcha2=$("#campo_filtro_fecha_2").val();
		filt_fam=$("#campo_filtro_familia").val();
		filt_tipo=$("#campo_filtro_tipo").val();
		filt_subtipo=$("#campo_filtro_subtipo").val();
		filt_color=$("#campo_filtro_color").val();
		filt_alm=$("#campo_filtro_almacen").val();
		filt_ext=$("#campo_filtro_es_externo").val();
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/herramientasBD.php',
			cache:false,
			data:{
				fl:'guarda_herramienta',
				id_herramienta:id,
				titulo:tit,
				consulta:cons,
				descripcion:desc,
				campo_filtro_sucursal:filt_suc,
				campo_filtro_fecha_1:filt_fcha1,
				campo_filtro_fecha_2:filt_fcha2,
				campo_filtro_familia:filt_fam,
				campo_filtro_tipo:filt_tipo,
				campo_filtro_subtipo:filt_subtipo,
				campo_filtro_color:filt_color,
				campo_filtro_almacen:filt_alm,
				campo_filtro_es_externo:filt_ext,
				query_type : tipo_cons
			},
			success:function(dat){
				$("#contenido_emergente").html('');
				$("#emergente").css('display','none');	
			}
		});

	}

	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}

	function genera_consulta(id_herr){
	//mandamos la emergente
		$("#contenido_emergente").html('<p align="center" class="titulo_emergente">Cargando...<br><img src="../../../img/img_casadelasluces/load.gif"></p>');
		$("#emergente").css("display","block");
	//extraemos los filtros
		var arr_filtros="";
		var filtros_consulta=$("#lista_filtros").val().split("|");
		for(var i=0;i<filtros_consulta.length-1;i++){
		//extraemos los atributos necesarios
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("campo_filtrar")+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("caracter_cambio")+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).value+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("datosDB")+"°";

		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/genera_consulta.php',
			cache:false,
			data:{ fl : 'queryExecution', id:id_herr, arr:arr_filtros },
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert( "Error : \n" + aux );
					$("#resultados").html(dat);
					$("#contenido_emergente").html();
					$("#emergente").css("display","none");
					return false;
				}
				$("#txt_consulta").val(aux[1]);
				$("#resultados").html(aux[2]);
				$("#contenido_emergente").html();
				$("#emergente").css("display","none");
			}
		});
	}

	function exporta_grid( id_herr ){
		var tabla,trs,tds;
		var datos="";
		var current_product = '';
		var separator = "--------------------";

	/*implementacion Oscar 2021 para insertar el nombre de la estacionalidad en la conulta*/
		(id_herr == 36 ? datos += $('#est_destino option:selected').text() + "\n" : null);

		if( id_herr == 43 ){//formato de ubicaciones
			datos += ",,,,,,,,,,,Ubicacion desde,,,,Ubicacion hasta\n";
		}
		if( id_herr == 48 || id_herr == 49 ){
			datos += ",,,SAN MIGUEL,,,,,,,TROJES,,,,,,,CASA,,,,,,,CHECO,,,,,,,PALMA,,,,,,,TROJES";
			datos += ",,,,,,,VIVEROS,,,,,,,LOPEZ,,,,,,,LAGO,,,,,,,CENTRO URBANO,,,,,,,LOMAS VERDER\n";

		}
	/*fin de cambio Oscar 2021*/

	//obtenemos la tabla
		if(!document.getElementById("grid_resultado")){
			alert("Es necesario que escriba una consulta que retorne resultados!!!");
			return false;
		}
		tabla=document.getElementById("grid_resultado");
	
	//obtenemmos las filas
		trs=tabla.getElementsByTagName('tr');
	//obtenemos encabezado
    	tds=trs[0].getElementsByTagName('th');
		for(var i=0;i<tds.length;i++){
			datos+=tds[i].innerHTML.trim();
			if(i<tds.length-1){
				datos+=",";
			}
		}
		datos+="\n";
	//Obtenemos el cuerpo de la tabla
	    for(var j=1;j<trs.length;j++){	
	    //obtenemos celdas
	        tds=trs[j].getElementsByTagName('td');
	        for(var i=0;i<tds.length;i++){
	        	if(  id_herr == 43 && i == 0 ){
			        if( current_product == '' ){
			        	current_product = tds[1].innerHTML.trim();
			        }
					if( tds[1].innerHTML.trim() != current_product ){
						//alert( tds[1] + '!=' + current_product );
						datos += separator + "," + separator + "," + separator + "," + separator + "," + separator + ",";
						datos += separator + "," + separator + "," + separator + "," + separator + "," + separator + ",";
						datos += separator + "," + separator + "," + separator + "," + separator + "," + separator + ",";
						datos += separator + "," + separator + "," + separator + "," + separator + "\n";
					}
					current_product = tds[1].innerHTML.trim();
				}

				datos+=tds[i].innerHTML.trim();
				if(i<tds.length-1){
					datos+=",";//coma delimi  tadora
				}else if( i==tds.length-1 ){
					datos+="\n";//salto de línea
				}
			}    
	    }//termina for i
	//asignamos el valor a la variable del formulario
		$("#datos").val(datos);
	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana, 3000);			
	}

	function cierra_pestana(){
		$("#datos").val("");//resteamos variable de datos
		ventana_abierta.close();//cerramos la ventana
	}

/*implementacion Oscar 2021 para menu de herramientas*/
	function search_menu(obj, e){
		var txt = $( obj ).val().toUpperCase().split(' ');
		var size = $('#contador_herramientas_1').val();
		var ref_comp = txt.length;
		for (var i = 0; i < size; i++) {
			var txt_comp = $('#herramienta_1_' + i).html().toUpperCase().trim();
			var matches  = 0;
			for (var j = 0;j < ref_comp; j++) {//comparacion de cadena de texto
				txt_comp.includes(txt[j]) ? matches ++ : null;
			}
			$('#herramienta_1_' + i)
			.css('display', matches ==  ref_comp ? '' : 'none');
		}
		size = $('#contador_herramientas_2').val();

		for (var i = 0; i < size; i++) {
			var txt_comp = $('#herramienta_2_' + i).html().toUpperCase().trim();
			var matches  = 0;
			for (var j = 0;j < ref_comp; j++) {//comparacion de cadena de texto
				txt_comp.includes(txt[j]) ? matches ++ : null;
			}
			$('#herramienta_2_' + i)
			.css('display', matches ==  ref_comp ? '' : 'none');
		}	
	}

	function home_redirect(){
		if( !confirm("Realmente desea Regresar al Panel?") ){
			return false;
		}else{
			location.href="../../../index.php?";
		}
	}
//combos dependientes
	function dependent_combo( source_object, target_object, dependence){
		var query, query_dependence, value;
		query = $( '#' + target_object ).attr('datosDB');
		query_dependence = dependence;
		value = $( source_object ).val(); 
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/dependent_combo.php',
			cache:false,
			data:{ 
					consulta : query,
					condicion : query_dependence,
					valor : value
				},
			success:function( dat ){
				var tmp = dat.split('|');
				if( tmp[0] != 'ok' ){
					alert("Error al consultar opciones del combo : " + dat);
					return false;
				}else{
			//asigna los nuevos valores
					$( '#' + target_object ).empty().append('<option class="opciones1" value="0">--Ver Todo--</option>');;
					for ( var i = 1; i < tmp.length; i ++ ) {
						var tmp_1 = tmp[i].split('~');
						$( '#' + target_object )
						.append('<option value="' + tmp_1[0] + '">' + tmp_1[1] + '</option>');;
					};
				}
			}
		});
	}


/*fin de cambio Oscar 2021*/