//declaramos variables globales
	var filActiva=0;
	var saltos=1;
	var topeAbajo,contCambios=0;
//extraemos el total de filas
	$(function() {
		//topeAbajo=document.getElementById('tope').value;
		//alert(topeAbajo);
	});
	
	function cambia_almacen(obj){
		location.href="inventario.php?id_suc_adm="+$("#cambiaSuc").val()+"&alm="+$("#almacenes_sucursal").val()+"&id_tipo_filtro="+$("#tipo_filtrado").val();
	}
//
	function recorrer(contador,flag){
		var elemento,vert;
		//vert=$('#listado').scrollTop();
		//alert(vert);
		if(contador==12){
			$('#listado').scrollTop(50);
		}else{
			vert=$('#listado').scrollTop();
			if(flag==1){
			//	alert('abajo');
				$('#listado').scrollTop(parseFloat(vert)+42);//recorre hacia abajo
			}else if(flag==2){
			//	alert('arriba');
				$('#listado').scrollTop(parseFloat(vert)-42);//recorre hacia arriba
			}
		return false;
		}
	}

//funcion que hace hover sobre fila
	function resalta(contador){
	//si hay una fila en hover la regresamos a estado normal
		if(filActiva!=0){
			document.getElementById('fila'+filActiva).style.background=color(filActiva);//sacamos color de fila
			document.getElementById('3,'+filActiva).style.background='transparent';
			/*document.getElementById('3,'+filActiva).style.textAlign='right';*/
		}
		filActiva=contador;
		document.getElementById('fila'+filActiva).style.background='rgba(0,225,0,.5)';
		document.getElementById('3,'+filActiva).style.background='white';
		/*document.getElementById('3,'+filActiva).style.textAlign='left';*/
		document.getElementById('3,'+filActiva).focus();
		document.getElementById('3,'+filActiva).select();

	}

//funcion que calcula el color correspondiente
	function color(contador){
		var tono='';
		if(contador%2==0){
			tono='#FFFF99';
		}else{
			tono='#CCCCCC';
		}
		return tono;//retorna el color
	}
//funcion que valida teclas
	function validar(e,contador){
		var tecla,sig;
		tecla=(document.all) ? e.keyCode : e.which;//convertimos tecla a valor numerico
	//alert(tecla);
	if(tecla==1){
		return false;
	}
	if(tecla==13){//si tecla es enter
		if(contador==topeAbajo){
			return false;
		}
		resalta(parseInt(contador+1));
		var contenido2 = $("#listado").offset();
		contenido2=contenido2.top;
		//alert(contenido2);
		return false;
	}
	if(tecla==38){//si tecla es arriba
		if(contador==1){
			$("#listado").scrollTop(0);//mandamos hasta arriba el scroll
			return false;
		}
		sig=(parseInt(contador-1));
		resalta(sig);
		if(contador>=12){
			recorrer(sig,2);
		}
		return false;
	}

	if(tecla==40){//si tecla es abajo
		if(contador==topeAbajo){
			return false;
		}
		sig=parseInt(contador+1);
		resalta(sig);
		if(contador>11){
			//alert('desde aqui baja');
			recorrer(sig,1);
		}
		return false;
	}
	if(tecla==37){//si tecla es izquierda
		return false;
	}
	if(tecla==39){//si tecla es derecha
		return false;
	}
	calcula(contador);
	return false;

	}
//funcion que calcula diferencia a insertar
	function calcula(contador){
		contCambios=1;
		var Diferencia=0;
		var idRes='4,'+contador;
	//generamos id de inventario virtual
		var vir='2,'+contador;
	//sacamos el valor de inventario virtual
		var invVirt=document.getElementById(vir).value;
	//generamos id de inventario fisico
		var fis='3,'+contador;
	//obtenemos valor tecleado
		var invFis=document.getElementById(fis).value;
	//
		if(invVirt==invFis){
			//alert('los inventarios son iguales');
			return false;
		}
	//restamos al almacen
		if(invVirt>invFis){
			diferencia=parseFloat(invFis-invVirt);
			document.getElementById(idRes).value=diferencia;
		}
	//sumamos al almacen
		if(invVirt<invFis){
			diferencia=parseFloat(invFis-invVirt);
			document.getElementById(idRes).value=diferencia;
		}
		document.getElementById('cambios').value=1;
		return false;
	}

//funcion que guarda ajuste de Inventario
	function guarda(sucursal){
	//validamos si se realizaron cambios
		/*if(contCambios==0){
			alert('No hay cambios por guardar');
			return false;
		} deshabilitado por oscar para hacer ajuste de inventario en linea*/
	//mostramos mensaje
		document.getElementById('emergente').style.display='block';
	//ocultamos boton de panel
		document.getElementById('footer').style.display='none';
	//ocultamos encabezado
		document.getElementById('enc').style.display='none';
	//calculamos tamaño de la tabla
		var tope=parseInt($('#formInv tr').length);
	//declaramos variables
		var suma='',resta='',aux='',ax='',aux_id='',idProd='',tipo='',cS=0,cR=0;
	//recorremos la tabla
		for(var i=1;i<=tope;i++){
			aux='4,'+i;
			if(document.getElementById(aux).value){
				ax=document.getElementById(aux).value;
				if(ax=='0'){
					//alert('sin accion');
				}else{
				//sacamos el id del producto
					aux_id='0,'+i;
					idProd=document.getElementById(aux_id).value;
				//checamos si la diferencia es positiva o negativa para hacer el movimiento de almacen
					if(ax<0){
						cR++;//aumenntamos contador de resta
							resta+='|'+parseFloat(ax*-1)+','+idProd;//multiplicamos ax por -1 para volverlo positivo
					}
					if(ax>0){
						cS++;//aumenntamos contador de suma
						suma+='|'+ax+','+idProd;
					}
				}//cierra else
			}//cierra if
		}//cierra for
	//capturamos el id del almacen
		var id_almacen=$("#id_alm").val();
		/*alert(id_almacen);
		return false;*/
	//mandamos valores por ajax
		$.ajax({
			type:'POST',
			url:'ajax/guardaAjuste.php',
			cache:'false',
			data:{quita:cR+resta,agrega:cS+suma,suc:sucursal,almacen:id_almacen},
			success: function(datos){
				if(datos=='okok'){
					alert('Cambios guardados exitosamente!!!');
					link(2);
				}else{
					alert('ERROR!!!\n'+datos);
				}
			}//fin de function(datos)
		});//fin de ajax
		return false;
	}//fin de funcion guardar

//funcion que eabre formulario de otra sucursal
	function cargaSucursal(nuevaSucursal){
	//alert
		var s=document.getElementById('cambiaSuc').value;
	//alert('cambiar a sucursal:'+nuevaSucursal);
		window.location="inventario.php?id_suc_adm="+s;
		
	}

//funcion que redirecciona
	function link(flag){
		if(flag==1){
			//verificamos si hubo movimientos
			var seguridad;
			var mov=document.getElementById('cambios').value;
			if(mov>0){
				seguridad=confirm('Hay cambios que no ha guardado, esta seguro de salir sin guardar?');
			}
			if(seguridad==false){
				return false;
			}
			window.location="../../../";
			return false;
		}
		if(flag==2){
			location.reload(true);
			return false;
		}
	}
/*Implementación para acomodar a los productos que tienen movimientos de almacen durante el ajuste de inventario (Oscar 03.05.2018)*/

	var buscando=0;//variable que evita que se mande petición cuando la petición anterior está en proceso
/*
	function buscaMovimientos(hora){
		//alert("hora de inicio:\n"+hora);
	if(buscando==1){
		return true;
	}
//limpiamos temporal
	var ids="";
	for(var i=1;i<=$("#tope").val();i++){
		if($("#temporal_"+i).html()!=0){
			//alert("si entra en condicion"+i+"\n\n"+$("#temporal_"+i).html());
		//regresamos variables
			var inVirtua=parseInt(document.getElementById("2,"+i).value);
			var inFisic=parseInt(document.getElementById("3,"+i).value);
			document.getElementById("2,"+i).value=parseInt(inVirtua-parseInt($("#temporal_"+i).html()));
			document.getElementById("3,"+i).value=parseInt(inFisic-parseInt($("#temporal_"+i).html()));
			ids+=document.getElementById("0,"+i).value+"~";
		//regresamos color negro a la fila
			document.getElementById("fila"+i).style.color="black";
			document.getElementById("1,"+i).style.color="black";
			document.getElementById("2,"+i).style.color="black";
			document.getElementById("3,"+i).style.color="black";
			document.getElementById("4,"+i).style.color="black";			
		}
		$("#temporal_"+i).html(0);//limpiamos valor temporal
	}
	//alert("ids:\n"+ids);
	buscando=1;
	//buscamos en ajax
		$.ajax({
			type:'post',
			url:'ajax/buscaNuevosMovimientos.php',
			cache:false,
			data:{productosEnTemporal:ids,horaDeInicio:hora},
			success:function(dat){
				//alert(dat);
				var ax_t=dat.split("|");
				if(ax_t[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}else{
					//actualizamos productos con movimientos
					var ax_cambios=ax_t[2].split("°");
					for(var j=0;j<ax_cambios.length-1;j++){
						var ax_info=ax_cambios[j].split("~");
						var cont=$("#"+ax_info[0]).val();//sacamos posición de fila

						document.getElementById("2,"+cont).value=ax_info[1];
						document.getElementById("3,"+cont).value=parseInt(parseInt(ax_info[1])+parseInt(document.getElementById("4,"+cont).value));						
					}//fin de for j

				//ponemos temporales
					var ax_dto=ax_t[1].split("°");//separamos productos
					for(var i=0;i<ax_dto.length-1;i++){
						var ax_info=ax_dto[i].split("~");
						if(document.getElementById(ax_info[0])){
							var cont=$("#"+ax_info[0]).val();//sacamos posición de fila
							//alert("Cont: "+cont+"\nValor:"+ax_info[1]);
							$("#temporal_"+cont).html(ax_info[1]);//asignamos valor a temporal		
						//cambiamos los valores de inventario virual y físico
							var inVirtual=parseInt(document.getElementById("2,"+cont).value);
							var inFisico=parseInt(document.getElementById("3,"+cont).value);
							document.getElementById("2,"+cont).value=parseInt(inVirtual+parseInt(ax_info[1]));
							document.getElementById("3,"+cont).value=parseInt(inFisico+parseInt(ax_info[1]));
						//asignamos color rojo a la fila
							document.getElementById("fila"+cont).style.color="red";
							document.getElementById("1,"+cont).style.color="red";
							document.getElementById("2,"+cont).style.color="red";
							document.getElementById("3,"+cont).style.color="red";
							document.getElementById("4,"+cont).style.color="red";
						}
					}//termina for i
				}
				buscando=0;//liberamos varible que bloque busquedas repetidas
			}
		});
		return true;
	}*/

//aqui definimos intervalo de tiempo para buscar ventas en temporal
	//window.onload=
	window.onload=function cargaTiempo(){
	//definimos hora de inicio de inventario
		var fecha = new Date();
		var hora_inicio=fecha.getHours()+":"+fecha.getMinutes()+":"+fecha.getSeconds();
		//setInterval('buscaMovimientos(\''+hora_inicio+'\')',30000);//30 segundos
	}
/*Fin de cambio Oscar(03.05.2018)*/


/*implementación para mostrar emergente con nombre de vendedores Oscar 07.05.2018*/
	var aviso=0;
	function verificaTemporal(num){
		if(aviso==1){
			aviso=0;
			document.getElementById("3,"+num).select();
			return true;
		}
		if($("#temporal_"+num).html()!=0){
	//extraemos id del producto en temporal
		var id_prod=document.getElementById("0,"+num).value;
	//mandamos valores por ajax
		$.ajax({
			type:'POST',
			url:'ajax/buscaNuevosMovimientos.php',
			cache:'false',
			data:{fl:2,id:id_prod},
			success: function(datos){
				var arreglo=datos.split("|");
				if(arreglo[0]!='ok'){
					alert('Error!!!\n'+datos);
					return false;
				}else{
					aviso=1;
					$("#cont_vta_emerge").html(arreglo[1]);
					$("#emergente").css("display","block");
				}
			}//fin de function(datos)
		});//fin de ajax
		return false;
		}
	}
/*Fin de cambio 07.05.2018*/