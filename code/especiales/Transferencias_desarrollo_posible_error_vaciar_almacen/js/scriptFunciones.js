//1. Declaracion de variables globales
var filaActiva=0;//declaramos como global esta variable para saber que fila esta en hover
//onload.cuentaFilas();
var validaPres=0;
var nFilasIniciales;
var temporal="";//aqui guardamos el valor total por producto antes de la modificacion
var eventoTemporal="";//en esta variable global se guarda el inventario
var autorizaCambiosInventario=0;//se implementa esta variable el 14.06.2018 para indicar que los cambios en el inventario se pueden realizar
var cTemp=0;
var edicionTemporal="";
window.onload=function(){
	nFilasIniciales=$('#cont').val();//calculamos numero de productos(filas en tabla)
}
/*2. Funcion activa_productos(); Implementacion de Oscar 23.10.2018 para habilitar productos en transferencia por medio del archivo %ajax/modificaTransferencia.php%%*/
	function activa_productos(){
		var tam=$("#cont").val();//sacamos el valor del contador
	//extraemos la sucursal destino
		var s_dest=$("#dest").val();
		var ids_prods="";
		for(var i=0;i<=tam;i++){
			if( document.getElementById('fila'+i) ){
				ids_prods+=($("#1_"+i).html()).trim();//concatenamos el id del producto
				if(i<tam){
					ids_prods+='|';
				}
			}
		}//fin de for i
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/modificaTransferencia.php',
			data:{fl:'activa',id:ids_prods,sucursal:s_dest},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n\n"+dat);
				}else{
					alert("Productos activados correctamente!!!");
				}
			}
		});
	}
/*fin de cambio 23.10.2018*/

/*3. Funcion validaPaquete(); implementacion Oscar 17.08.2018 para validar paquetes y agregar sus productos por medio del archivo %ajax/agregarColumna.php%%*/
	function validaPaquete(id_pqt){
		if(id_pqt==null||id_pqt==''){alert("No se detectó ningún id de paquete!!!");return false;}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/agregarColumna.php',
			cache:false,
			data:{id_paq:id_pqt},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error al agregar paquete!!!\n"+dat+"\n"+aux[0]);
					return false;
				}else{
					for(var i=1;i<aux.length-1;i++){
						var aux_1=aux[i].split("~");
					//mandamos agregar fila
					//alert(aux_1[0]+"|"+aux_1[1]);
						window.setTimeout(agregarFila('libre',aux_1[0],aux_1[1]),5000);
					}//fin de for i
				}
			}
		});	
	}

//4. Funcion para cargar datos para cambio de inventario, presentacion en ventana emergente
	function carga_datos_emergente(tipo_acc,flag,num){
		var onclick="",val_txt="",titulo="";//declaramos variables
		if(tipo_acc=='inventario'){//si es por inventario
		//asignamos el evento de verificar pass por inventario
			val_txt='value="Solo cambiar este Inventario"';
		}else if(tipo_acc=='mantener_cantidad'){//si es por mantener una presentación incompleta
		//asignamos el evento para verificar pass y mantener presentacion incompleta
			val_txt='value="Mantener presentación incompleta"';
			edicionTemporal=0;
			flag="1";
		}
	//formamos datos a cargar en pantalla emeregente
		var info_pantalla='<p align="right" style="position:relative;top:-20px;"><input type="button" value="X" onclick="verifica_pass_inventario('+num+','+edicionTemporal+','+($("#"+flag+"_"+num).html()).trim()+',-1);" style="padding:5px;border:1px black;background:red;color:white;border-radius:5px;"></p>';
		info_pantalla+='Pida al encargado de la Sucural que introduzca la contraseña:<br>';
/*modificación Oscar 12.11.2018 para hacer tipo password un tipo text*/
		info_pantalla+='<input type="text" style="padding:10px;font-size:25px;" id="pass_inventario_1" onkeyDown="cambiar(this,event,\'pass_inventario\')"><br>';
		info_pantalla+='<input type="hidden" id="pass_inventario" value="">';
		info_pantalla+='<input type="button" '+val_txt+' onclick=verifica_pass_inventario('+num+','+edicionTemporal+','+($("#"+flag+"_"+num).html()).trim()+',0,\''+tipo_acc+'\'); style="padding:10px;border-radius:6px;">     ';
/*fin de cambio Oscar 12.11.2018*/
		if(tipo_acc=='inventario'){
			info_pantalla+='<input type="button" value="Cambiar varios Inventarios" onclick=verifica_pass_inventario('+num+','+edicionTemporal+','+($("#"+flag+"_"+num).html()).trim()+',1,\''+tipo_acc+'\'); style="padding:10px;border-radius:6px;">';
		}
	//mostramos emergente
		$("#mensaje_pres").html(info_pantalla);
		$("#cargandoPres").css("display","block");
	}

/*5. Implementacion de Oscar 14.06.2018 para editar celdas de estacionalidad maxima e inventario*/
	function editaCelda(flag,num){
	//removemos el atributo de edición de celda
		$("#"+flag+"_"+num).attr("onclick","");
	//sacamos el valor de la celda
		edicionTemporal=$("#"+flag+"_"+num).html().trim();
	//cargamos la celda temporal en 
		$("#"+flag+"_"+num).html('<input type="number" id="celda_temporal" onkeyDown="detiene(event);" value="'+edicionTemporal+'" style="width:100%;text-align:right;font-size:30px;" onblur="desEditaCelda('+flag+','+num+');" onkeyup="valida_celda_tmp(event,'+num+','+flag+');">');
		$("#celda_temporal").select();
		
	}
/*6. Implemntacion de Oscar 01.08.2018 para desplazarse entre celdas editables*/
	function valida_celda_tmp(e,c,flag){
		var tca=e.keyCode;
		if(tca==38){//si es tecla arriba
			if(c==1){
				return true;
			}
			$("#buscador").focus();
			$("#"+flag+"_"+parseInt(c-1)).click();
			return false;
		}
		if(tca==40||tca==13){//si es tecla abajo o tecla intro
			if(c==nFilasIniciales){
				return true;
			}
			$("#buscador").focus();
			$("#"+flag+"_"+parseInt(c+1)).click();
			return false;
		}
	}
/*Fin de cambio*/
//7. Funcion para guardar los datos editados en la celda por medio del archivo %ajax/modificaTransferencia.php%%
	function desEditaCelda(flag,num){
	//asignamos el nuevo valor a la celda
		/*if(){}*/
		$("#"+flag+"_"+num).html($("#celda_temporal").val().trim());
	//agregamos el atributo de edición de celda
		$("#"+flag+"_"+num).attr("onclick","editaCelda("+flag+","+num+");");
	//si es modificación de inventario 
		if(flag==4 && edicionTemporal!=($("#"+flag+"_"+num).html()).trim()){
			if(autorizaCambiosInventario==1){
				modifica_inventario(num,edicionTemporal,($("#"+flag+"_"+num).html()).trim(),1);
			}else{
			//arrojamos emergente para introducir contraseña
				carga_datos_emergente('inventario',flag,num);
				return true;
			}		
		}//fin de if es inventario
	//si es modificación de la estacionalidad
		if(flag==5){
		//sacamos el id del registro de estacionalidad
			var reg=($("#7_"+num).html()).trim();
			var val=($("#5_"+num).html()).trim();//sacamos el valor actual
			var id_producto=($("#1_"+num).html()).trim();
		//enviamos datos por ajax
			if(edicionTemporal!=val){
				$.ajax({
					type:'post',
					url:'ajax/modificaTransferencia.php',
					cache:false,
					data:{fl:4,valor:val,id:reg,prod:id_producto},
					success:function(dat){
						var arr_tmp=dat.split("|");
						if(arr_tmp[0]!='ok'){
							alert("Error estacionaldiad!!!\n"+dat);
							$("#5_"+num).html(edicionTemporal);//regresamos el valor anterior
							return false;
						}else{
							alert("Estacionalidad cambiada exitosamente!!!");
						//cambiamos el numero de piezas por pedir
							var invD=parseInt($("#4_"+num).html());
							var est=parseInt($("#5_"+num).html());
						//validamos si aún entra en la transferencia
							if(invD>=est){//si no necesita más stock
								$("#6_"+num).val("0");//0
							}else{//si necesita stock
								$("#6_"+num).val(est-invD);//estacionalidad-inventario destino
							}
						}//fin de else
					}
				});
			}	
		}
	}

//8. Funcion para verificar el password de cambio de inventario por medio del archivo %ajax/modificaTransferencia.php%%
	function verifica_pass_inventario(num,anterior,nuevo,tipo,acc){
		if(tipo==-1){
			$("#4_"+num).html(anterior);
			$("#cargandoPres").css("display","none");
			return true;
		}
	//sacamos el valor ingresado en contraseña
		var pass=$("#pass_inventario").val();
		if(pass==''||pass==null){
			alert("La contraseña no puede ir vacía");
			$("#pass_inventario").select();
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/modificaTransferencia.php',
			cache:false,
			data:{password:pass,fl:2},
			success:function(dat){
				var arr_tmp=dat.split("|");
			//si la consulta esta mal
				if(arr_tmp[0]!="ok"&&arr_tmp[0]!="no"){
					alert("Error pass\n"+dat);
				}
			//si el password es incorrecto
				if(arr_tmp[0]=="no"){
					alert("La contraseña es incorrecta, verfifique y vuelva a intentar!!!");
					$("#pass_inventario").select();
					return false;
				}
			//si el password es correcto
				if(arr_tmp[0]=="ok"){
					$("#cargandoPres").css("display","none");//ocultamos la emergente
					if(acc=='inventario'){//si e inventario
					//mandamos modificar el inventario
						modifica_inventario(num,anterior,nuevo,tipo);
					}else if(acc=='mantener_cantidad'){
					//mantenemos la cantidad
						seleccion(1,num,1);						
					//reseteamos el oermiso de mantener cantidad
					}
				}
			}
		});
	}

//8. Funcion para de cambio de inventario por medio del archivo %ajax/modificaTransferencia.php%%
	function modifica_inventario(num,valAnt,valNvo,multi_inv){
		//alert(multi_inv);
	//extraemos el id del producto
		var id_prod=$("#1_"+num).html().trim();
	//extraemos el almacén
		var id_alm=$("#almDestino").val();
		var tipo_mov=8;//resta por ajuste de inventario
	//sacamos la diferencia
		var dif=valNvo-valAnt;
		if(dif>0){
			tipo_mov=9;//suma por ajuste de inventario
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/modificaTransferencia.php',
			cache:false,
			data:{id_producto:id_prod,id_tipo:tipo_mov,id_almacen:id_alm,diferencia:dif,fl:3},
			success: function(dat){
				var arr_tmp=dat.split("|");
				if(arr_tmp[0]!='ok'){
					alert("Error al modificar el inventario!!!\n"+dat);
				//regresamos el valor de la celda al inventario anterior
					$("#4_"+num).html(valAnt);	
					//return false;
				}else{
					var invD=parseInt($("#4_"+num).html());
					var est=parseInt($("#5_"+num).html());
				//validamos si aún entra en la transferencia
					if(invD>=est){//si no necesita más stock
						$("#6_"+num).val("0");//0
					}else{//si necesita stock
						$("#6_"+num).val(est-invD);//estacionalidad-inventario destino
					}
				}
			}	
		});
		autorizaCambiosInventario=multi_inv;//aqui le indicamos si se renovamos/cerramos el permiso para seguir modificando los inventarios
	}

/*10. Funcion para exportacion de Transferencias Oscar 28.05.2018*/
	var ventana_abierta;
	function exportaTransferencia(flag){
		if(flag==1){
			if(!confirm("Verifique que no vayan comas adicionales en los codigos de proveedores de su archivo CSV!!!")){
				return false;
			}
			$("#imp_csv_prd").click();
			return true;
		}
	//var filas=$("#transferencias tr").length;
	var dats='Id producto,Orden de lista,Clave Proveedor,Nombre,Inv Origen,Inv Destino,Estacionalidad,Pedido,Ubicacion Matriz,Ubicación Destino, Observaciones del producto\n';
		nFilasIniciales=$("#cont").val();
		for(var i=1;i<=nFilasIniciales;i++){
			if(document.getElementById('fila'+i)){
				dats+=$("#1_"+i).html()+",";//id de producto
				dats+=$("#0_"+i).html()+",";//orden de lista
		/*Implementación Oscar 26.02.2019 para incluir el alfanumérico*/
				dats+=($("#clave_"+i).html()).replace(",","*")+",";//alfanumérico
		/*Fin de cambio Oscar 26.02.2019*/
				dats+=$("#2_"+i).html()+",";//nombre del producto
				dats+=$("#3_"+i).html()+",";//inventario Origen
				dats+=$("#4_"+i).html()+",";//inventaerio Destino
				dats+=$("#5_"+i).html()+",";//estacionalidad máxima 
				dats+=$("#6_"+i).val()+",";//cantidad pedida
/**/
				dats+=$("#10_"+i).html()+",";//ubicación de Matriz
				dats+=$("#11_"+i).html()+",";//ubicación de la sucursal Matriz
				dats+=$("#12_"+i).html().trim();//observaciones del producto (impementado por Oscar 22.09.2020)
/**/
//			dats+=$("#7_"+i).val();//total de piezas
				if(i<nFilasIniciales){
					dats+="\n";//añadimos el separados
				//alert(dats[i]);
				}
			}//fin de si existe la fila
		}//fin de for i
	//asignamos el valor a la variable del formulario
		$("#datos").val(dats);
	/**/
		if(flag=='orden_almacen' || flag=='formato_limpio'){
			$("#fl").val(flag);
			$("#datos").val($('#id_trans').val());
		}else{
			$("#fl").val(1);
		}
	/**/

	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana,15000);			
	}

	function cierra_pestana(){
		ventana_abierta.close();//cerramos la ventana
	}
	
	$('#submit-file').on("click",function(e){
  					e.preventDefault();
  					$('#imp_csv_prd').parse({
        				config: {
            				delimiter:"auto",
            				complete: importaTransferencia,
        				},
       			 		before: function(file, inputElem){
       			 			$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
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
       				if(nombre_fichero_seleccionado!=""){
        				$("#bot_imp_estac").css("display","none");//ocultamos botón de importación
        				$("#submit-file").css("display","block");//mostramos botón de inserción
        				$("#txt_info_detalle_oc_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
        				$("#txt_info_detalle_oc_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
        				//$("#importa_csv_icon").css("display","none");
        			}else{
        				alert("No se seleccionó ningun Archivo CSV!!!");
        				return false;
        			}
    			});

//11. Funcion para importacion de Transferencias Oscar 28.05.2018
	function importaTransferencia(results){
	//lanzamos la emergente
		$("#mensaje_pres").html('<p align="center" style="color:white;font-size:30px;">Cargando datos<br><img src="../../../img/img_casadelasluces/load.gif" width="120px"></p>');
		$("#cargandoPres").css("display","block");
		
		var id_estac=$("#id_estacionalidad").val();
		var data = results.data;//guardamos en data los valores delarchivo CSV
		var tam_grid=$("#estacionalidadProducto tr").length-3;
		//alert(data);
		//return true;
		var arr="";
		var orden_lista_tmp="";
		for(var i=1;i<data.length;i++){
			//arr+=data[i];
			var row=data[i];
			var cells = row.join(",").split(",");
			/*for(j=0;j<cells.length;j++){*/
    			arr+=cells[0]+",";
    			arr+=cells[7];//se cambia la posición  de 6 a 7 por la implementación de la clave de proveedor Oscar 26.02.2019 
			/*}*/
			if(i<data.length-1){
				 arr+="|";
			}
/*implementación Oscar 30.09.2019 para validar que el archivo CSV este ordenado por orden de lista correctamente*/
			if(parseInt(orden_lista_tmp)>parseInt(cells[1]) && i>1){
				alert("Los productos no están ordenados por orden de lista; verifique su archivo y vuelva a intentar!!!");//+orden_lista_tmp+"|"+cells[1]
				location.reload();
				i=data.length;
				return false;
			}
			orden_lista_tmp=cells[1];
/*Fin de cambio Oscar 30.09.2019*/

		}//fin de for i
		agregarFila('import_csv',arr,0);
	}
/*Fin de cambio 28.09.2018*/
	
//12. Funcion para agregar fila	por medio del archivo %ajax/obtenerDatosProducto.php%%
function agregarFila(flag,id_prod,cant_por_pqte){/*se agrega la variables id_prod, cant_por_pqt Oscar 17.08.2018*/
	//alert(flag+"\n"+id_prod+"\n"+cant_por_pqte);
	/*if(flag==null){
		alert('si es nulo');
	}*/
		nFilasIniciales=$("#cont").val();
		$('#modificaciones').val(1);
		$("#espacio_importa").css("display","none");
	
		var id=document.getElementById('auxBusqueda').value;
	//obtenemos datos de sucursales y almacenes involucrados en transferencia	
  		var sucOrigen=document.getElementById('orig').value;
		var sucDestino=document.getElementById('dest').value;
		var cantidad=document.getElementById('agrega').value;
		var busc=document.getElementById('buscador').value;
		
		if(busc=="" && flag!=-1 && flag!='import_csv'){
			alert('primero busque un producto!!!');
			document.getElementById('buscador').focus();
			return false;
		}
		if(flag=='libre' && cant_por_pqte==null){/*condición agregada por Oscar 17.08.2018*/
			//no es necesario ingresar cantidad
			cantidad=1;
		}else if(cantidad=="" && cant_por_pqte==null  && flag!='import_csv'){
			if(flag!=null){
				alert('ingrese una cantidad para continuar!!!');
			}
			document.getElementById('agrega').focus();
			return false;
		}
	/*implementación de Oscar 18.08.2018*/
		if(id_prod!=null && cant_por_pqte!=null){
			id=id_prod;
			cantidad=cant_por_pqte;
		}
	//valida que la cantidad por agregar sea mayor a cero
		if( cantidad <= 0 ){
			alert( "La cantidad de piezas debe ser mayor a cero!" );
			$( '#agrega' ).val( '' );
			$( '#agrega' ).select();
			return false;
		}
	/*fin del cambio*/
  	var almOrigen=document.getElementById('almOrigen').value;
		//if(almOrigen)
		var almDestino=document.getElementById('almDestino').value;
  	//alert('almacenes:'+	almOrigen+almDestino);
  		var c_antes=parseInt(document.getElementById('cont').value);//obtenemos valor actual del contador
		var c=c_antes+1;//incrementamos uno al nuevo contador
		var dest_suc=$("#").val();
  	//enviamos datos por ajax
  		$.ajax({
  			type:"POST",
  			url:"ajax/obtenerDatosProducto.php",
  			data:{id:id,
  				sOr:sucOrigen,
  				sDes:sucDestino,
  				aOrigen:almOrigen,
  				aDestino:almDestino,
  				c:parseInt(nFilasIniciales),
  				cant:cantidad,
  				action: ( flag == null ? 'libre' : flag )
  			},
  			success: function(datos){
  				//alert(datos);
				var resultados=datos.trim().split("|~|");//SEPARAMOS RESPUESTA DE AJAX
				if(resultados[0]!='ok'){
					alert("Error!!!\n"+datos);
					return false;
				}
				if( resultados[1] == 'exception' ){
					$( '.emergent_content' ).html( resultados[2] );
					$( '.emergent' ).css( 'display', 'block' );
					return false;
				}
				close_emergent();
//termina implementación y se agrega la variable col_condic a los textos
	var fila=resultados[1];
	$('#transferencias').append(fila);//agregamos fila

	if(filaActiva!="" && document.getElementById("fila"+filaActiva) &&flag!='import_csv'){
		var desenfoca="fila"+filaActiva;
		var campoAnt='6_'+filaActiva;
		document.getElementById(campoAnt).style.fontSize='15px';//cambiamos la fuente del input
		document.getElementById(campoAnt).style.textAlign='right';
		document.getElementById(campoAnt).style.background='transparent';//cambiamos a transparente el color del input
		
	}

	if(flag!='import_csv'){
		var enfoque='6_'+c;//creamos variable id de enfoque
		document.getElementById(enfoque).focus();//enfocamos en la nueva fila
		document.getElementById(enfoque).select();//enfocamos en la nueva fila
	
	//document.getElementById(campoAnt).style.background='rgba();';//cambiamos a transparente el color del input
		document.getElementById('cont').value=c;//actualizamos contador
  		document.getElementById('resBus').style.display='none';
  		document.getElementById('buscador').value="";
  		document.getElementById('agrega').value="";

	//subimos contador de iniciales
  		nFilasIniciales+=1;

		//filaActiva=c;
		resaltar(c);

		document.getElementById('b1').style.display='none';
		document.getElementById('b2').style.display='none';
		document.getElementById('b4').style.display='none';
  		document.getElementById('buscador').focus();
	
		if(resultados[2]>1){
			var d1=$('#6_'+c).val();//obtenemos el valor de la cantidad
			var res=d1/resultados[2];
		//formamos la ventana emergente
			var botOk="",botCanc="",botRedAb="",botRedArr="",botCantIns=""; 
			if(res%1!=0){
				botRedAb='<p align="center">Se recomienda pedir presentaciones completas, seleccione una opción:</p>';
				botRedAb+='<input type="button" class="botEme" value="'+Math.floor(res)+' '+resultados[3]+'(S)='+parseInt($("#8_"+c).html())*Math.floor(res)+'" onclick="redondea_pres(\'1\','+c+','+Math.floor(res)+',\'nvo\')">';
				botRedArr='<input type="button" class="botEme" value="'+Math.ceil(res)+' '+resultados[3]+'(S)='+parseInt($("#8_"+c).html())*Math.ceil(res)+'" onclick="redondea_pres(\'1\','+c+','+Math.ceil(res)+',\'nvo\')">';
				botCantIns='<input type="button" class="botEme" value="'+d1+' piezas" onclick="carga_datos_emergente(\'mantener_cantidad\',4,'+c+')" style="position:absolute;right:20%;top:45%;">';
			}else{
				botOk='<p><input type="button" class="botEme" value="Aceptar" onclick="seleccion(1,'+c+',\'nvo\');" id="botOkEm"></p>';
				if(flag==1){
					botOk='<p><input type="button" class="botEme" value="Aceptar" onclick="confirmar();" id="botOkEm">';
				}
				if(flag==5){
					botOk='<p><input type="button" class="botEme" value="Aceptar" onclick="actualizar();" id="botOkEm">';
				}
				botOk+="</p>";
			}//fin de else
			botCanc='<input type="button" class="botEme" value="Cancelar" onclick="seleccion(0,'+c+',\'nvo\');"></p>';
			$("#mensaje_pres").html("Usted pidió: "+d1+" de "+$("#2_"+c).html()+" que equivalen a "+res+" "+resultados[3]+"S \n"+botRedAb+"     "+botRedArr+"     "+botOk+"     "+botCanc+" "+botCantIns);
			$("#cargandoPres").css("display","block");
		}
	}else{
		nFilasIniciales=parseInt(resultados[2]);
		$("#cont").val(nFilasIniciales);//asignammos el nuevo contador
		$("#cargandoPres").css("display","none");//ocultamos la emergente
		$("#submit-file").css("display","none");//ocultamos el botón de cargar archivo
		$("#txt_info_csv").css("display","none");//ocultamos el mensaje de nombre de archivo
		$("#cont_visual_1").html(resultados[2]);
		//$("#cont_visual_2").html(resultados[2]);
	}
	
  			}
  		});
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

/*
	modificacion 30-10-2017
*/
//13. Funcion para desenfocar
function desenfocar(tipo){
	//alert('desenfoca:'+tipo);
	if(filaActiva!=0 && validaPres!=0){
		if(tipo==1){
			validaPresCorrecta(1,cTemp,1);
		}else if(tipo==2){
			validaPresCorrecta(1,cTemp,5);
		}
		return false;
	}else{
		if(tipo==1){
			confirmar();
		}else if(tipo==2){
			actualizar();
		}else{
			alert("Hay un error!!!...");
		}
	}	
}

//14. Funcion para validar presentacion correcta por medio del archivo %ajax/getPresentacionProducto.php%%
function validaPresCorrecta(e,c,flag){
	if(cTemp==0){//si no hay fila en temporal
		validaPres=0;//regresamos la validación de la presentación a cero
		validar(eventoTemporal,c);//validamos el evento temporal
		return false;//terminamos la función
	}
	var id_pr,aux,d,res;//declaramos las variables a usar en el proceso
//extraemos el id del producto
	id_pr=$('#1_'+cTemp).html();
//enviamos datos por ajax
	$.ajax({
		type:'post',
		url:'ajax/getPresentacionProducto.php',
		cache:false,
		data:{id:id_pr},
		success:function(datos){
			aux=datos.split('|');//fragmentamos respuesta
			if(aux[0]!='ok'){//si no encontramos el ok
				alert("Error!!!\n"+datos);//enviamos error
				return false;//paramos la función
			}
		//si hay una fila temporal
			if(cTemp!=0){
			//obtenemos valores
				d=$('#8_'+cTemp).html();//obtenemos el valor de la presentación
				d1=$('#6_'+cTemp).val();//obtenemos el valor de la cantidad
			//calculamos numero de presentaciones
				res=d1/aux[1];
			//formamos la ventana emergente
				var botOk="",botCanc="",botRedAb="",botRedArr="",botCantIns=""; 
				if(res%1!=0){
					botRedAb='<p align="center">Se recomienda pedir presentaciones completas, seleccione una opción:</p>';
					botRedAb+='<input type="button" class="botEme" value="'+Math.floor(res)+' '+aux[2]+'(S)='+parseInt($("#8_"+cTemp).html())*Math.floor(res)+'" onclick="redondea_pres(\''+e+'\','+cTemp+','+Math.floor(res)+','+flag+')">';
					botRedArr='<input type="button" class="botEme" value="'+Math.ceil(res)+' '+aux[2]+'(S)='+parseInt($("#8_"+cTemp).html())*Math.ceil(res)+'" onclick="redondea_pres(\''+e+'\','+cTemp+','+Math.ceil(res)+','+flag+')">';
					botCantIns='<input type="button" class="botEme" value="'+d1+' piezas" onclick="carga_datos_emergente(\'mantener_cantidad\','+flag+','+cTemp+')" style="position:absolute;right:20%;top:45%;">';
				}else{
					botOk='<br><input type="button" class="botEme" value="Aceptar" onclick="seleccion(\''+e+'\','+c+','+flag+');" id="botOkEm">';
					if(flag==1){
						botOk='<br><input type="button" class="botEme" value="Aceptar" onclick="confirmar();" id="botOkEm">';
					}
					if(flag==5){
						botOk='<br><input type="button" class="botEme" value="Aceptar" onclick="actualizar();" id="botOkEm">';
					}
				//	botOk+="</p>";
				}//fin de else
				botCanc='<input type="button" class="botEme" value="Cancelar" onclick="seleccion(0,'+c+');"></p>';
				$("#mensaje_pres").html(aux[3]+"<br>Usted pidió "+d1+" "+aux[4]+"(S) que equivalen a "+res+" "+aux[2]+"S \n"+botRedAb+"     "+botRedArr+"     "+botOk+"     "+botCanc+" "+botCantIns);
				$("#cargandoPres").css("display","block");
			//$("#botOkEm").focus();$("#botOkEm").select();
			}

		}
	});
}

//15. Funcion para redondeo de presentaciones
	function redondea_pres(e,num,valor,flag){
	//hacemos operación
		$("#6_"+num).val(parseInt($("#8_"+num).html())*valor);
		$("#cargandoPres").css("display","none");
		if(e==0){
			e=null;
		}
		seleccion(e,num,flag);
		return false;
	}
//16. Funcion para sellecionar presentacion
function seleccion(e,c,flag){
//decalaramos elementos que se manipularán
	var obj1=document.getElementById('cargandoPres');
	var objAux="";
	validaPres=0;//reseteamos variable de validación de presentación
//si el evento tiene valor de cero
	if(e==0){
		if(flag=='nvo'){
			resaltar(c);//resaltamos la fila en temporal
			objAux=document.getElementById('6_'+parseInt(c));
		}else{
			resaltar(cTemp);
			objAux=document.getElementById('6_'+cTemp);
		}
		obj1.style.display='none';//cerramos emergente
		objAux.value="0";//dejamos en ceros las cantidades del producto
		objAux.select();//enfocamos y subrayamos el contanido del input de cantidad de la fila en temporal
	}else{
//de lo contrario; si es un evento de teclado
		//validar(eventoTemporal,cTemp,flag);//mandamos validar la acción en temporal
		if(e==1){
			resaltar(c);
		}else{
			resaltar(cTemp);//resaltamos la fila marcada como activa
		}
		obj1.style.display='none';//ocultamos la ventana emergente
	}
//RESTEAMOS VARIABLES 
	cTemp=0;//reseteamos fila en temporal
	validaPres=0;//reseteamos la validación de cantidad presentación
	eventoTemporal="";//reseteamos el evento en temporal
	return false;//terminamos la función
}
/*Termina cambio 30-10-2017*/

//17. Funcion para mover Scroll
function posic(cont,flag){
	var elemento,poscion,altura,lista;
	validaPres=0;
//recorremos
if(flag==1){
	if(cont>=10){
		elemento = $("#contenidoTabla");
		altura=elemento.scrollTop();
		elemento.scrollTop(parseFloat(altura+37));
	}else{
		return false;
	}
}else if(flag==2){	
	if(cont>=10){
		elemento = $("#contenidoTabla");
		altura=elemento.scrollTop();
		elemento.scrollTop(parseFloat(altura-37));
	}else{
		return false;
	}
}
}
//18. Funcion para ocultar resultados de busqueda
function ocultaResultados(){
	var buscador,resultados;
	resultados=document.getElementById('resBus');
	buscador=document.getElementById('buscador');
	$('#resBus').html('');
	buscador.value='';
  	resultados.display='none';//ocultamos buscador
	//alert('all is ok');
  	return false;
}
//19. Funcion para prevenir evento por defecto al presionar teclas
function detiene(e){
	if(e.keyCode==38||e.keyCode==40||e.keyCode==69){
		e.preventDefault();
	}
}
//20. Funcion para valida teclas de desplazamiento
function validar(e,c,flag){
	var tecla=(document.all) ? e.keyCode : e.which;//convertimos tecla a valor numerico
	//alert(tecla+"|"+flag);
	var fila="fila",sig,temp;//declaramos variables
//obtenemos el valor del input que activó la función y lo guardamos en el temporal
	temporal=$('#6_'+c).val();
//si es tecla abajo
	if(tecla==40 && flag==2||tecla==13 && flag==2){
		if(validaPres==1){//si validar presentación es 1
			eventoTemporal=e;//guardamos el evento temporal (el que activó la función)
			var ax=validaPresCorrecta(e,c,flag);//mandamos a validar la presentación
				return false;//terminamos función
		}
		var tope=$('#cont').val();//sacamos el valor máximo de 
		//alert(TOPE);
		if(c==parseInt(tope)){
			return false;//terminamos función
		}else{
			sig=parseInt(c+1);
			posic(sig,1);
			resaltar(sig);
			validaPres=0;
			return false;
		}
	}			
	
	if(tecla==38 & flag==2){//si es tecla arriba
		//alert();
		if(validaPres==1){
			eventoTemporal=e;
			var ax=validaPresCorrecta(e,c,flag);
				return false;
		}
		if(c==1){
			//enfocamos buscador
			$('#buscador').focus();
			$('#buscador').select();			
			return false;
		}else{
			sig=parseInt(c-1);
			posic(sig,2);
			resaltar(sig);
			validaPres=0;
			return false;
		}			
	}
	/*prevenimos que se inserte algo deferente a numeros
		if (tecla< 48 || tecla> 57) {

			if(tecla==8 || tecla==46){//si es borrar o suprimir
				//alert('tecla borrar');
				return false;
			}
			//alert(tecla);
			if(tecla>=96 && tecla<=105){
				return false;
			}
			e.preventDefault();//prevenimos error 
    		alert('solo son admitidos números en este campo!!!');
    		//alert(tecla);
    		temp=document.getElementById('6_'+c);
    		temp.value='0';
    		temp.select();
			return false;//terminamos la función
  		}*/

 }
/*21. Funcion que realiza las operaciones de cantida y presentacion*/
function operacion(e,c,flag){
	var tecla=(document.all) ? e.keyCode : e.which;
	//alert(tecla);
	if(tecla==38||tecla==40){
		return false;
	}
	document.getElementById('modificaciones').value=1;
//cambio Oscar (10-11-2017)
	document.getElementById('b1').style.display='none';
	document.getElementById('b2').style.display='none';
	document.getElementById('b4').style.display='none';
//obtenemos cantidad de presentación y cantidad pedida
	var presentacion=$("#8_"+c).html();
	var cantidad=parseInt($("#6_"+c).val());//,resultado="7_"+c
//obtenemos el total
	var valResultado=cantidad*presentacion;
/*cambio del 30-10-2017*/
	cTemp=0;
	validaPres=0;
	if(presentacion>1 && cantidad>0){//||flag==1
		validaPres=1;
		cTemp=c;
	}
/*implementación Oscar 21.02.2018 color*/
	var inv_or=parseInt($('#3_'+c).html());//sacamos el valor del inventario origen
//validamos que la cantidad pedida sea igual o mayor al inventario de origen
//alert(inv_or+"\n"+cantidad);
	if( inv_or<cantidad){
	//notificamos que en el origen hay menos cantidad de la pedida
		//alert("La cantidad pedida es mayor al inventario de la Sucursal de Origen");
	//coloreamos la fila de rojo
		for(var i=0;i<8;i++){
			document.getElementById(i+"_"+c).style.color='red';
		}
	}else{//de lo contrario; si hay stock
	//coloreamos la fila de negro
		for(var i=0;i<8;i++){
			document.getElementById(i+"_"+c).style.color='black';
		}
	}
/*fin de implementación color*/
}//finaliza la función operación

//22. Funcion para buscar coincidencias por medio del archivo %ajax/buscarProductoTiempoReal.php%%
function buscar(e){
	var tecla=(document.all) ? e.keyCode : e.which;
		//alert(tecla);
	var busqueda=document.getElementById('buscador').value;	
	if(tecla==8 && document.getElementById('buscador').value.length<3){//si la tecla es borrar
		//alert();
		document.getElementById('resBus').style.display="none";
		return false;
	}
	if(tecla==13 ){
		//alert(busqueda);
		if(document.getElementById('id_1')){
			var id=document.getElementById('id_1').value;
			//alert(id);
			validaProducto(id);			
		}else{
			//alert('Es scanner');
			tecla='scanner';
		}
	}
	if(tecla==27 || tecla==38){
		document.getElementById('resBus').style.display='none';
		document.getElementById('buscador').select();
		return false;
	}

	if(tecla==40){//si tecla es abajo
		if(busqueda.length<=2||busqueda==""){
			//alert();
			var cont=0;
			while(cont<=1000){
				cont++;
				if(document.getElementById('6_'+cont)){
					resaltar(1);//enfocamos la primera fila
					return false;
				}else{

				}
			}	
		}else{
			document.getElementById('resBus').style.display='block';
			document.getElementById('r_1').focus();
			document.getElementById('r_1').style.background='rgba(0,225,0,.5)';
			return false;//terminamos accion
		}
	}
	if(document.getElementById('buscador').value.length<3){
		document.getElementById('resBus').style.display='none';
		return false;
	}
	//alert(tecla);
//declaramos variables para asignar datos
	var c=document.getElementById('contador');
	var sucOrigen=document.getElementById('orig').value;
	var sucDestino=document.getElementById('dest').value;
	var almOrigen=document.getElementById('almOrigen').value;
	var almDestino=document.getElementById('almDestino').value;
	//alert('almacen origen: '+almOrigen+', almacen destino: '+almDestino+', sucOrg: '+sucOrigen+', sucDest:'+sucDestino);
//mandamos datos por ajax
	$.ajax({
		type:"post",
		url:"ajax/buscarProductoTiempoReal.php",
		//data:{producto:busqueda,id_sucursal_origen:sucOrigen,id_sucursal_destino:sucDestino,id_almacen_origen:almOrigen,
		//	id_almacen_destino:almDestino},
		data:{producto:busqueda,sucDestino:sucDestino,aD:almDestino},
		success: function(datos){
			if(datos=='sin resultados'){//SI DATOS RETORNA 0;
					//alert('sin resultados');
					//$("#"+desc).html('');//LIMPIAMOS CAMPO DESCRIPCION
				}else{//DE LO CONTRARIO;
					//alert(datos);
					$('#resBus').html(datos);
						if(tecla!='scanner'){					
							document.getElementById('resBus').style.display='block';
						}else{
							//alert();
							scanner();
							$('#resBus').html('');
							document.getElementById('resBus').style.display='none';
							return false;
						}
					}
					//alert('finaliza ajax');
				return false;
			}
		});
}
//23. Funcion para busqueda por Scanner (no se usa)
function scanner(){
	var id=document.getElementById('id_1').value;//obtenemos id de opcion
	//validaProducto(id);//mandamos a metodo que valida producto
	validaProducto(id);//mandamos a metodo que valida producto
	$('#resBus').html('');
	document.getElementById('resBus').style.display='none';
	return false;
}

//24. Funcion para eliminar fila	
	function eliminarFila(fila,flag){
		document.getElementById('modificaciones').value=1;
	//alert(fila+"   "+flag);
		if(flag=='0'){//SI PROVIENE DE UN FLAG VACIO;
			$("#fila" +fila).remove();//ELIMINA AUTOMATICAMENTE LA FILA
		}else{//DE LO CONTRARIO;
			var validar=confirm("Desea quitar este producto de la transferencia?");//LANZAMOS PREGUNTA DE CONFIRMACIÓN.
			if(validar==true){//SI ES VERDADERO;
				$("#fila" +fila).remove();//ELIMINAMOS FILA

				document.getElementById('b1').style.display='none';
				document.getElementById('b2').style.display='none';
				document.getElementById('b4').style.display='none';
			    return false;
			}else{//DE LO CONTRARIO
				return false;//RETORNA FALSE.
			}
		}
	}

//25. Funcion para validar producto por medio del archivo %ajax/getNombre.php%%
function validaProducto(id,excl){/*se agrega la variable excl Oscar 12.09.2018*/ 
/*implementación Oscar 12.09.2018 para mensaje de productos excluidos*/
  	if(excl==1){
  		var msg='<p align="center"><b style="color:yellow;font-size:45px;">EN EXCLUCION de Transferencias</b></p>';
  		msg+='¡Por el momento este producto no tiene inventario en el almacén Matriz,';
  		msg+='\nVerifique su existencia con el encargado del almacén Matriz o en otras sucursales!';
  		msg+='<br><br><table width="60%"><tr>';
		msg+='<td align="left"><input type="button" onclick="validaProducto('+id+',0);document.getElementById(\'cargandoPres\').style.display=\'none\';" value="Continuar" style="padding:10px;border-radius:8px;"></td>';
		msg+='<td align="right"><input type="button" onclick="document.getElementById(\'cargandoPres\').style.display=\'none\';" value="Cancelar"  ';
		msg+='style="padding:10px;border-radius:8px;"></td>';
		msg+='</tr></table>';
		$("#mensaje_pres").html(msg);
		$("#cargandoPres").css("display","block");
  	}
/*fin de cambio 12.09.2018*/

  	var cuenta=0;
  	var tipo=document.getElementById('tipo').value;
 	nFilasIniciales=$("#cont").val(); 	
  	for(var i=1;i<=nFilasIniciales;i++){
  		if(!document.getElementById('1_'+i)){
  			//alert('no existe');
  		}else{
  			var aux=parseInt($('#1_'+i).html());
  			if(aux==id){
  				var enfoca="6_"+i;//guardamos el elemento a enfocar
  				if(tipo!=2){
					//alert('el producto ya existe en la transferencia!!');
					resaltar(i);
  					ocultaResultados();
 					return false;
  				}else{
  						resaltar(i);
  						ocultaResultados();
  						return false;	
  					}
  			}//fin de else
  		}
  	}//fin de for i
  	$.ajax({
  			type:"post",
  			url:"ajax/getNombre.php",
  			data:{id:id},
  			success: function(datos){
  				if(datos!='error'){
  					document.getElementById('buscador').value=datos;
  					document.getElementById('resBus').style.display='none';
  					$('#resBus').html("");//reseteamos resultados
  					document.getElementById('auxBusqueda').value=id;
  					/*if(tipo==5){
  						accionar('libre');
  						return false;
  					}*/
  					document.getElementById('agrega').focus();
				}else{
					alert('error');
					return false;
				}
			}
  		});
  				return false;  	
}
//26. Funcion para validar eventos de teclas en resultados de busqueda			
function eje(e,c,id,excl){
	//alert('here');
	var tecla= (document.all) ? e.keyCode : e.which;
	//alert(tecla);
	if(tecla==27){
		document.getElementById('resBus').style.display='none';
		document.getElementById('buscador').select();
		return false;
	}
	if(tecla==40){
		var n=c+1;
		var enfoca="r_"+n;
		if(document.getElementById(enfoca)){
			$('#'+enfoca).focus();
			var desenfoca="r_"+parseInt(n-1);
			document.getElementById(desenfoca).style.background='white';
			document.getElementById(desenfoca).style.color='black';
			document.getElementById(enfoca).style.color='white';
			document.getElementById(enfoca).style.background='rgba(0,225,0,.5)';
		}else{
			//alert('fin');
			return false;
		}
	}

	if(tecla==38){
		if(c==1){
				document.getElementById('buscador').select();
				return false;
			}
			var n=c-1;
			var enfoca="r_"+n;
			$('#'+enfoca).focus();
				document.getElementById(enfoca).style.background='rgba(0,225,0,.5)';
				var desenfoca="r_"+parseInt(n+1);
				document.getElementById(desenfoca).style.background='white';
			document.getElementById(desenfoca).style.color='black';
			document.getElementById(enfoca).style.color='white';
	}
	if(tecla==13){
		validaProducto(id,excl);
	}else{
		return true;
	}
}

//27. Funcion para agregar fila con enter
function accionar( e = null ){
	var tecla = null;
	if( e != null ){
		tecla = (document.all) ? e.keyCode : e.which;
	}
//	alert(tecla);
	if(tecla==13 || tecla==1 || e == null){
		if(document.getElementById('id_trans')){
			var idTransferencia=document.getElementById('id_trans').value;
			if(idTransferencia!=0 && (tecla==13||tecla==1)||idTransferencia!='' && (tecla==13||tecla==1)){
				agregarFila("modificacion");
				return false;
			}
		}
		agregarFila();
		return false;
	}
	if(e=='libre'){
		agregarFila('libre');
	}

	if(tecla==37){
		document.getElementById('buscador').select();
		return false;
	}
	else{
		return false;
	}
}

//28. Funcion para actualizar transferencia por medio del archivo %ajax/remplazarDetalleTransfer.php%%
function actualizar(){
	//obtenemos id de Transferencia
	var idTr=document.getElementById('id_trans').value;
	//var consultas=document.getElementById('consultasActualizacion').value;
//	alert(consultas);
	//var id=document.getElementById('1_'+i).value;
		if(document.getElementById("proceso")){
			$("#proceso").html('Guardando transferencia');
			document.getElementById('cargando').style.display='block';//mendamosventana de informe
		}
	//declaramos arreglos donde guardamos datos de la transferencia
		//return false;
		var idPro=new Array();
		var ped="",tot="",pres="";
		var info=new Array();
		var cuenta=0,noCuenta=0;
		var tipoTransferencia=parseInt(document.getElementById('tipo').value);
	//si a transferencia es libre
		if(tipoTransferencia==5){
			nFilasIniciales=$("#cont").val();
			//alert(nFilasIniciales);
		}
		var nFilasFinales=$("#cont").val();

	//alert(nFilasFinales);
		var final_request_data = '';
	//recorremos la tabla
		for( var i = 1; i <= nFilasFinales; i++ ){
			if(!document.getElementById('fila'+i) ){//COMPROBAMOS SI LA FILA EXISTE
				noCuenta++;//alert('no cuenta');
			}else{//DE LO CONTRARIO;
				var comprueba=document.getElementById('7_'+i).value;
				if(comprueba==0 || comprueba==""){
					//alert('no cuenta');
				}else if($("#6_"+i).val()!='' && $("#6_"+i).val()!='0'){
					cuenta++;//incrementamos contador
					final_request_data += (final_request_data != '' && i > 0 ?  '|~|' : '' );
					final_request_data += $('#1_'+i).html()+'~';
					final_request_data += $( '#13_' + i ).html().trim();
				}
			}
		}//fin de for i
		/*nFilasFinales=$("#cont").val();
//alert(nFilasFinales);
		for(var i=1;i<=$("#cont").val();i++){
			if(!document.getElementById('fila'+i)){//COMPROBAMOS SI LA FILA EXISTE
				//alert("no existe fila: "+i);
					noCuenta++;
				}else{//DE LO CONTRARIO;
					var comprueba=document.getElementById('7_'+i).value;
					if(comprueba==0 || comprueba==""){
						//alert('no cuenta');
					}else{//alert('cuenta: '+i);
						cuenta++;//incrementamos contador
					//OBTENEMOS VALORES Y LOS GUARDAMOS
						idPro+=$('#1_'+i).html()+'~';//id del producto
						ped+=$('#6_'+i).val()+'~';//cantidad pedida
						pres+=$('#8_'+i).html()+'~';//catidad de presentación
						tot+=$('#6_'+i).val()+'~';
					}
			}
		}*/
		//alert(idPro+'\n'+ped+'\n'+pres+'\n'+tot);
		//return false;
//alert('validos: '+cuenta+"\nNo validos"+noCuenta);
		//guardamos información adicional
			info[0]=parseInt(cuenta);
			info[1]=parseInt(document.getElementById('orig').value);
			info[2]=parseInt(document.getElementById('dest').value);
			info[3]=parseInt(document.getElementById('almOrigen').value);
			info[4]=parseInt(document.getElementById('almDestino').value);
		//si es vaciar almacen convertimos la transferencia a tipo manual
			if(tipoTransferencia==6){
				info[5]=2;
			}else{
		//de lo contrario asignamos valor de tipo de transferencia elegida
				info[5]=parseInt(document.getElementById('tipo').value);
			}
			info[6]=parseInt(1);
			/*alert( final_request_data );
			return false;*/
/**/
	$.ajax({
		type:"post",
		url:'ajax/remplazarDetalleTransfer.php',//"ajax/actualizaProductosTransferencia.php",
		cache:false,
		data:{
				transfer_id:idTr,
				adic:info, 
				detail : final_request_data 
		},
		success: function(datos){
			if(datos=='ok'){
				window.location.reload();
			}else{
				alert('Error!!!\ndatos no guardados....\n'+datos);
			}
		}
	});
}
//29. Funcion que resalta fila y enfoca cantidad
function resaltar(fila,flag){//e,c,flag
	if(validaPres==1){
		validaPresCorrecta();
	}
	var desenfoca,campoAnt,resalta,campo;
	if(filaActiva!=''){
//alert('validacion Presentac.'+validaPres);
		desenfoca=document.getElementById("fila"+filaActiva);
		campoAnt=document.getElementById('6_'+filaActiva);
		if(campoAnt){
			campoAnt.style.fontSize='15px';//cambiamos la fuente del input
			//campoAnt.style.textAlign='right';
			campoAnt.style.border='0 solid transparent';
			campoAnt.style.background='transparent';//cambiamos a transparente el color del input
			desenfoca.style.background=color(filaActiva);//desenfocamos la fila
		}/*
		if(fila==filaActiva){
			//alert('reinicia fila');
			filaActiva="";
			return false;
		}*/
	}//termina if filaActiva>0

	filaActiva=fila;//asignamos valor de nueva fila activa
	//alert('fila nueva: '+filaActiva);
	resalta=document.getElementById("fila"+filaActiva);//creamos id de fila a resaltar
	campo=document.getElementById('6_'+fila);//creamos id de campo a resaltar
	if(flag!=1){
		campo.focus();
		campo.select();//enfocamos y subrayamos
	}
	campo.style.background='white';//coloreamos de blanco el fondo
	campo.style.fontSize='20px';//aumentamos fuente
	campo.style.border='2px solid';
	//campo.style.textAlign='left';//alineamos a la izquierda
	resalta.style.background='rgba(0,225,0,.5)';
	//conteo=1;
}

//30. Funcion que calcula el color
function color(fila){
	var tono;
	if(fila%2==0){
		tono="#FFFF99";
	}else{
		tono="#CCCCCC";	
	}
	return tono;
}
//31. Funcion que restringe modificacion de transferencia
function restringe(){
	alert('no se puede modificar la transferencia porque ya ha sido aprobada');
	return false;
}
//32. Funcion para enfocar Buscador
function enfocar(){
	if(document.getElementById("buscador")){
		document.getElementById("buscador").focus();//enfocamos al buscador
	}else{
		//alert('no esta');
	}
}
/*implementacion Oscar 2021 para cambiar transferencia a urgente*/
	function transfer_type_change( obj ){
		$( '#tipo' ).val( $( obj ).val() );
		if ( $( obj ).val() == 1 ){
			$('#titulo_transfer').val($('#titulo_transfer').val() + '(URGENTE)');
		}else{
			$('#titulo_transfer').val($('#titulo_transfer').val().split('(URGENTE)').join(''));
		}
	}
/*fin de cambio Oscar 2021*/

/*Implementación Oscar 27.02.2019*/
	var nota_confirmada=0;
//33. Funcion que guarda a la transferencia por medio del archivo %insertaTransferencia.php%%
	function confirmar(){
		var nota="", titulo_trans = "";
		var revisa_datos=0;
		if(nota_confirmada==0){
			var msg = '<p align="right"><button style="padding:10px;background:red;font-size:20px;color:white;" ';
				msg += 'onclick="document.getElementById(\'cargando\').style.display=\'none\';">X</button></p>';
			/*implementación Oscar 2021 para título de transferencia*/
				msg += '<h2>Título</h2>';
				msg += '<textarea id="titulo_transfer" style="width:80%;height:60px;">';
				if( $('#tipo').val() == 1 ){
					msg += '(URGENTE)';
				}
				msg += '</textarea><br>';
		/*implementacion Oscar 2021 para cambiar transferencia a urgente*/
				//if( $('#tipo').val() == 5 ){
				msg += '<h2>Prioridad</h2>'
					+ '<select onchange="transfer_type_change(this);" style="padding : 8px; width:80%;">'
						+ '<option value="' + $('#tipo').val() + '">Normal</option>'
						+ '<option value="1">Urgente</option>'
						+ '</select><br />';
				//}
			/*fin de cambio Oscar 2021*/
				msg+='<h2>Observacion/notas</h2>';
				msg+='<br><textarea id="obs_transfer" style="width:80%;height:200px;"></textarea><br>';
				msg+='<p align="center"><button onclick="nota_confirmada=1;confirmar();" style="padding:15px;border-radius:15px;font-Size:30px;">Guardar</button></p>';
			$("#proceso").html(msg);
			document.getElementById('cargando').style.display='block';//mendamosventana de informe
			$('#cargando').css('z-index','10000');//mendamosventana de informe
			$("#cargando img").css("display","none");
			return false;
		}else{
			nota=$("#obs_transfer").val();
			titulo_trans = $("#titulo_transfer").val();		
		}	
/*Fin de Cambio Oscar 27.02.2019*/
	if($("#transferencias tr").length<=0){
			alert("No se puede guardar una transferencia vacía!!!");
			document.getElementById('cargando').style.display='none';//escondemos emergente
			return false;
	}
	//lanzamos emergente
	if(document.getElementById("proceso")){
		$("#proceso").html('Guardando transferencia');
		$("#cargando img").css("display","block");
			//document.getElementById('cargando').style.display='block';//mendamosventana de informe
	}
/*	$("#transferencias tr").each( function(index){

	});*/
	//declaramos variables donde guardamos datos de la transferencia
		var idPro=new Array();
		var ped="";
		var tot="";
		var pres="";
		var raciona="";
		var info=new Array();
		var cuenta=0,noCuenta=0;
		var tipoTransferencia=parseInt(document.getElementById('tipo').value);
	/*si transferencia es libre
		if(tipoTransferencia==5){
			var nFilasIniciales=$("#transferencias tr").length;//sacamos el valor de las filas
			//alert(nFilasIniciales);
		}*/
		
		
	var nFilasFinales=$("#cont").val();

//alert(nFilasFinales);
	var final_request_data = '';
	//recorremos la tabla
		for(var i=1;i<=nFilasFinales;i++){
			if(!document.getElementById('fila'+i) ){//COMPROBAMOS SI LA FILA EXISTE
					noCuenta++;//alert('no cuenta');
				}else{//DE LO CONTRARIO;
					var comprueba=document.getElementById('7_'+i).value;
					if(comprueba==0 || comprueba==""){
						//alert('no cuenta');
					}else if($("#6_"+i).val()!='' && $("#6_"+i).val()!='0'){
						cuenta++;//incrementamos contador
						final_request_data += (final_request_data != '' && i > 0 ?  '|~|' : '' );
						final_request_data += $('#1_'+i).html()+'~';
						final_request_data += $( '#13_' + i ).html().trim();
						//id de producto
						/*final_request_data += parseInt($('#6_'+i).val()/$('#8_'+i).html())+'~';//cantidad presentación
						final_request_data += $('#8_'+i).html()+'~';//presentación*
						final_request_data += $('#6_'+i).val()+'~';//total_piezas
					/*implementación Oscar 24.03.2019 para racionar productos*
						raciona+=$('#9_'+i).html()+'~';//racionar
					/*Fin de cambnio Oscar 26.03.2019*/
						//alert(idPro);
						/*revisa_datos+=$('#6_'+i).val();

					//OBTENEMOS VALORES
						idPro+=$('#1_'+i).html()+'~';//id de producto
						ped+=parseInt($('#6_'+i).val()/$('#8_'+i).html())+'~';//cantidad presentación
						pres+=$('#8_'+i).html()+'~';//presentación
						tot+=$('#6_'+i).val()+'~';//total_piezas
					/*implementación Oscar 24.03.2019 para racionar productos*
						raciona+=$('#9_'+i).html()+'~';//racionar
					/*Fin de cambio Oscar 26.03.2019*
						//alert(idPro);
						revisa_datos+=$('#6_'+i).val();*/
					}
			}
		}//fin de for i
		if( cuenta == 0 ){
			alert("La transferencia no puede ir vacía\nPida cantidades mayores a Cero para poder continuar!!!");
			$("#emergente").css("display","none");
			$("#cargando").css("display","none");
			return false;
		}
//alert('validos: '+cuenta+"\nNo validos"+noCuenta);
		//guardamos información adicional
			info[0]=parseInt(cuenta);
			info[1]=parseInt($('#orig').val());
			info[2]=parseInt($('#dest').val());
			info[3]=parseInt($('#almOrigen').val());
			info[4]=parseInt($('#almDestino').val());
		//si es vaciar almacen convertimos la transferencia a tipo manual
			if(tipoTransferencia==6){
				info[5]=2;
			}else{
		//de lo contrario asignamos valor de tipo de transferencia elegida
				info[5]=parseInt($('#tipo').val());
			}
			info[6]=parseInt(1);
		//enviamos datos por ajax
			$.ajax({
				type : "POST",
				url : "insertaTransferencia.php",
				data : { adic : info, 
						detail : final_request_data, 
						nota_transfer : nota, 
						titulo : titulo_trans 
				},
				cache :'false',
				dataType :'html',
				success : function( datos ){
					if( datos == 'ok' ){
						if( datos == 'SI' ){
							alert('La transferencia fue subida en linea correctamente!!!');
						}
						resultado = "ok";
						window.location = "../../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
					}else{
						var respuesta=datos.split("|");
						if(respuesta[0]=='sincroniza'){
							if(respuesta[1]=='SI'){
								alert('La transferencia fue subida en linea');
							}else{
								alert('Error al subir transferencia en linea!!!\nVerifique su conexion y pruebe manualmente');
							}
							window.location="../../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";
						}else{
							//alert('Error al subir Transferencia en linea verifique su conexion y vuelva a intentar manualmente');
							//$('#resultado').html(datos);
							window.location="../../general/listados.php?tabla=ZWNfdHJhbnNmZXJlbmNpYXM=&no_tabla=MA==";	
						}
					}
					document.getElementById('cargando').style.display='none';//ocultamos la pantalla emergente
				}//fin de funcion datos
			});//fin de ajax
}

function parar(){
	//alert();
	return false;
}
window.onload=enfocar();




/*
function modificar(e,flag,c){
	//alert('e: '+e+' flag: '+flag+' c: '+c);
	var tecla;
	if(c==0){
		nFilasIniciales+=1;
		c=nFilasIniciales;
	} 
	if(e==0){

	}else{
		tecla= (document.all) ? e.keyCode : e.which;
	}

	if(flag>0){
		if(document.getElementById('id_trans')){
			var trans=document.getElementById('id_trans').value;
		}
//alert('c en modificar: '+c);
		var t=document.getElementById('7_'+c).value;//guardamos valor despues de hacer la opercion
		if(temporal==t){
			//alert('no hace modificacion');
			return false;
		}
	if(flag==1){//guardamos datos de actualización.
		operacion(c,flag);
		var id=document.getElementById('1_'+c).value;
		var presentacion=document.getElementById('5_'+c).value;
		var cantidad=document.getElementById('6_'+c).value;
		var total=document.getElementById('7_'+c).value;
		document.getElementById('consultasActualizacion').value+="%UPDATE ec_transferencia_productos SET cantidad='"+total+"', cantidad_presentacion='"+cantidad+"' "	 
																+"WHERE id_transferencia='"+trans+"' AND id_producto_or='"+id+"' ";
		var antes=parseInt(document.getElementById('modificaciones').value);
		var nuevo=parseInt(antes+1);
		document.getElementById('modificaciones').value=nuevo;
		//var cons=document.getElementById('consultasActualizacion').value;
		//alert(cons);
		validar(e,c,2);
	}
	if(flag==2){//guardamos datos a borrar
//alert('aqui entra a borrar fila');
		var id=document.getElementById('1_'+c).value;
		document.getElementById('consultasActualizacion').value+="%DELETE FROM ec_transferencia_productos WHERE id_transferencia="+trans+" AND id_producto_or="+id;
		eliminarFila(c,0);

		var antes=parseInt(document.getElementById('modificaciones').value);
		var nuevo=parseInt(antes+1);
		document.getElementById('modificaciones').value=nuevo;
	}
	if(flag==3){//guardamos datos a insertar
		//alert('here');
		//agregarFila(-1);
		var cont=document.getElementById('cont').value;
		var id=document.getElementById('1_'+cont).value;
		var cantidad=document.getElementById('6_'+cont).value;
		var total=document.getElementById('7_'+cont).value;
		if(document.getElementById('consultasActualizacion')){
			document.getElementById('consultasActualizacion').value+="%INSERT INTO ec_transferencia_productos(id_transferencia, id_producto_or,id_presentacion,"+
			"cantidad_presentacion,cantidad,id_producto_de,referencia_resolucion) VALUES('"+trans+"','"+id+"','-1','"+cantidad+"','"+total+"','"+id+"','"+total+"')";
			//alert(document.getElementById('consultasActualizacion').value);
		}
		var antes=parseInt(document.getElementById('modificaciones').value);
		var nuevo=parseInt(antes+1);
		document.getElementById('modificaciones').value=nuevo;
	}
	//alert(document.getElementById('modificaciones').value);
}else{
	//alert('aqihoiñh');
	return true;
}
}
*/

/*


/*	$("#general").html(fila);
	return false;
	'<tr bgcolor="'+color+'" id="fila'+c+'" border="2">'+
	//td1
		'<td align="center" width="10%" onclick="resaltar('+c+');">'+
		'<input type="text" id="0_'+c+'" value="'+resultados[0]+'" class="lectura" disabled '+col_indic+'>'+
		'<input type="hidden" id="1_'+c+'" value="'+id+'" '+col_indic+'>'+
		'</td>'+
	//td2
		'<td width="36%" onclick="resaltar('+c+');">'+
		'<input type="text" class="lectura" id="2_'+c+'" class="lectura" value="'+resultados[1]+'" disabled onclick="resaltar('+c+');" '+col_indic+'>'+
		'</td>'+
	//td3	
		'<td align="center" width="9%" onclick="resaltar('+c+');">'+
		'<input type="text" id="3_'+c+'" class="inventarios" value="'+resultados[2]+'" disabled '+col_indic+'>'+
		'</td>'+
	//td4
		'<td align="center" width="9%">'+
		'<input type="text" id="4_'+c+'" class="inventarios" value="'+resultados[3]+'" disabled '+col_indic+'>'+
		'</td>'+
	//td5
		'<td align="center" width="10%">'+
		'<select id="5_'+c+'" class="pedir" onchange="operacion('+c+');" '+col_indic+'>'+
			'<option value="'+resultados[4]+'">'+resultados[4]+'x</option><option value="1">1 x</option></select>'+
			'<input type="hidden" id="8_'+c+'" value="-1">'+
		'</td>'+
	//td6
		'<td width="6%">'+
		'<input input type="text" class="pedir" id="6_'+c+'" value="'+cantidad+funcCant+'onclick="resaltar('+c+');operacion'+c+');" '+col_indic+' style="border-color:black;">'+
		'</td>'+
	//td7
		'<td align="center" width="10%">'+
		'<input type="text" id="7_'+c+'" value="'+(cantidad*resultados[4])+'" class="lectura" disabled '+col_indic+'>'+
		'<input type="hidden" value="<?php if($id_tipo==6){echo -1;}else{echo -1;} ?>" id="8'+c+'">'+
		'</td>'+
	//td8
		'<td align="center">'+
		'<a href="'+funcBor+'" style="text-decoration:none;">'+
		'<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font>'+
		'<font size="-1">&nbsp;</font><font size="-1">.</font>'+
		'</a>'+
		'</td>'+
//finaliza tr
	'</tr>';*/

				//alert(c);
				//alert('presentacion: '+datos[4]);
			/*	if(resultados[4]>1){
					validaPres=1;
					cTemp=c;
				}
				if(c%2==0){
					var color="#FFFF99";
				}else{
					var color="#CCCCCC";	
				}
				//alert("res:"+resultados[1]);
		var funcCant='" onkeyup="validar(event,'+c+',2);operacion(event,'+c+');"';
		var funcBor='javascript:eliminarFila('+c+',1);';
		/*if(flag=="modificacion"){
			funcCant='" onkeyup="validar(event,'+c+',2);operacion('+c+');modificar(event,1,'+c+');"';
			funcBor='javascript:modificar(1,2,'+c+');';
		}*
//implementacion Oscar 21.02.2018
	var col_indic='style="color:black;"';
	if(resultados[2]<cantidad){
		alert("La cantidad pedida en este producto es mayor a la existente en la sucursal de origen!!!");
		col_indic='style="color:red;"';//fuente roja
	}*/