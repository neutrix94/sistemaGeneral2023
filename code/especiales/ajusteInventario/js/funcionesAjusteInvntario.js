//declaramos variables globales
	var filActiva=0;
	var saltos=1;
	var topeAbajo,contCambios=0;
	var id_sucursal_en_edicion=0;
	var tabla_ubicacion='';


	function showEmergent( counter, product_id, block_counter ){
		$.ajax({
			type : 'post',
			url : 'ajax/functions.php',
			cache : false,
			data : { action : 'maquila', 
				product : product_id, 
				count : counter,
				quantity : document.getElementById( '3,' + counter ).value,
				block_count : block_counter
			},
			success : function( resp ){
				//alert( resp );
				$( '.emergent_content' ).html( resp );
				$( '.emergent' ).css( 'display', 'block' );
				$( '.emergent_content' ).focus();
			}
		});
	}
	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	function putDecimalValue( counter, product_id, block_counter ){
		//alert( '#3,' + counter );
		document.getElementById( '3,' + counter ).value = document.getElementById( 'maquila_decimal' ).value;
		//$( '#3,' + counter ).val( '100' );
		setTimeout( function (){ close_emergent(); 
		calcula(counter, product_id, block_counter); }, 100 ); 
	}

	function oculta_res_busc(){
		$('#resBus').css("display","none");
	}

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
	function resalta(contador,fl){
	//si hay una fila en hover la regresamos a estado normal
		if(filActiva!=0){
			document.getElementById('fila'+filActiva).style.background=color( parseInt($('#fila' + filActiva).attr('group_counter'))) ;//sacamos color de fila
			//document.getElementById('3,'+filActiva).style.background='transparent';
			/*document.getElementById('3,'+filActiva).style.textAlign='right';*/
		}
		//alert(contador);
		filActiva=contador;
		if( document.getElementById('fila'+filActiva) ){
			document.getElementById('fila'+filActiva).style.background='rgba(0,225,0,.5)';
		}else{
			document.getElementById('fila'+$('#'+filActiva+''));
		}
		//document.getElementById('3,'+filActiva).style.background='white';
		/*document.getElementById('3,'+filActiva).style.textAlign='left';*/
		if(fl!=0){
			document.getElementById('fila'+filActiva).focus();
		}
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
	function validar( e, contador, product_id, block_counter ){
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
			e.preventDefault();
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
			e.preventDefault();
			return false;
		}
		sig=parseInt(contador+1);
		resalta(sig);
		if(contador>11){
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
//alert();
	calcula(contador, product_id, block_counter);
	return false;

	}
//funcion que calcula diferencia a insertar
	function calcula( contador, product_id, block_counter ){
		contCambios=1;
		var diferencia=0;
		var idRes='4,'+contador;
	//generamos id de inventario virtual
		var vir='2,'+contador;
	//sacamos el valor de inventario virtual
		var invVirt=parseFloat(document.getElementById(vir).value);
	//generamos id de inventario fisico
		var fis='3,'+contador;
	//obtenemos valor tecleado
		var invFis=parseFloat(document.getElementById(fis).value);
	//
		if(invVirt==invFis){
			document.getElementById(idRes).value=0;
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
	//valor del inventario físico actual

	//cambia el inventario general del producto
		//document.getElementById( '3,' + product_id ).value = parseInt( document.getElementById( '3,' + product_id ).value )  + parseInt( diferencia );
		//$( '#3,' + product_id ).val( parseInt( $( '#3,' + product_id ).val() + Diferencia ) );
		//alert( $( '#3,' + product_id ).val() + '-' + product_id );
	//marca que hubo un cambio
		document.getElementById('cambios').value=1;
		search_block_items( block_counter, product_id );
		return false;
	}

	function search_block_items( block_counter, product_id ) {
		var tope = parseInt( $('#formInv tr').length );
		//alert( tope );
		var id = '';
	//itera todos los elementos de la tabla
		var resp = 0;
		for(var i=1;i<tope;i++){
			if( $( '#fila' + i ).attr('group_counter') == block_counter && 
				$( '#fila' + i ).attr('is_master') != 1 ){
				//alert( 'yes' );
				resp += ( isNaN( parseFloat( document.getElementById( '3,' + i ).value ) ) ? 0 : parseFloat( document.getElementById( '3,' + i ).value ));
			}
		}
		resp = parseFloat( resp ).toFixed( 2 );
		//alert( resp + ' : ' + id );
		document.getElementById( "3" + '_' + product_id ).value = resp ;
		document.getElementById( "4" + '_' + product_id ).value = ( resp - document.getElementById( "2" + '_' + product_id ).value );
		return resp;
	}
	function prevent_event( e ){
		var evento = e.keycode;
		/*evento == 38 || evento == 40 ? e.preventDefault() : null; */
	}

//funcion que guarda ajuste de Inventario
	function guarda(sucursal){
	//validamos si se realizaron cambios
		if(contCambios==0){
			alert('No hay cambios por guardar');
			return false;
		}
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
	//limpiamos el contenido de la pantalla emergente
		$("#info_emerge").html('');
	//recorremos la tabla
		for(var i=1;i<=tope;i++){
			aux='4,'+i;
			if( document.getElementById(aux) ){
				ax=document.getElementById(aux).value;
				if(ax=='0'){
					//alert('sin accion');
				}else{
				//sacamos el id del producto
					aux_id='0,'+i;
					idProd=document.getElementById(aux_id).getAttribute('value');
				//checamos si la diferencia es positiva o negativa para hacer el movimiento de almacen
					if(ax<0){
						cR++;//aumenntamos contador de resta
						resta+='|'+parseFloat(ax*-1)+','+idProd+','+$( '#fila' + i ).attr('product_provider');//multiplicamos ax por -1 para volverlo positivo
					}
					if(ax>0){
						cS++;//aumenta contador de suma
						suma+='|'+ax+','+idProd+','+$( '#fila' + i ).attr('product_provider');
					}
				}//cierra else
			}
		}
		//alert( suma +' === '+ resta); return false;
	//mandamos valores por ajax
		$.ajax({
			type:'POST',
			url:'ajax/guardaAjuste.php',
			cache:'false',
			data:{quita:cR+resta,agrega:cS+suma,suc:sucursal},
			success: function(datos){
				var aux=datos.split("|");
				if(aux[0]=='ok' && aux[1]=='ok'){
					if($("#info_emerge").html('<br><br><br><br><br><p>Folio generado: '+aux[2]+'</p><input type="button" value="Aceptar" onclick="link(2);">')){
						alert('Cambios guardados exitosamente!!!');
					}
					
					return true;
				}else{
					alert('ERROR!!!\n'+datos);
					$("#emergente").css("display","none");
				}
			}//fin de function(datos)
		});//fin de ajax
		return false;
	}//fin de funcion guardar

//funcion que eabre formulario de otra sucursal
	function cargaSucursal(nuevaSucursal){
	//alert
		window.location="inventario.php?id_suc_adm="+btoa($('#cambiaSuc').val());
		
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
//
	function buscaMovimientos(hora){
		alert("hora de inicio:\n"+hora);
	if(buscando==1){
		return true;
	}
//limpiamos temporal
	var ids="";
	for(var i=1;i<$("#tope").val();i++){
		if($("#temporal_"+i).html()!=0){
			//alert("si entra en condicion"+i+"\n\n"+$("#temporal_"+i).html());
		//regresamos variables
			var inVirtua=parseInt( $("#2,"+i).val() );
			var inFisic=parseInt( $("#3,"+i).val() );
			//document.getElementById("2,"+i).value=parseInt(inVirtua-parseInt($("#temporal_"+i).html()));
			//document.getElementById("3,"+i).value=parseInt(inFisic-parseInt($("#temporal_"+i).html()));
			ids += $("#0,"+i).attr( 'value' ) + "~";			
		}
		//$("#temporal_"+i).html(0);//limpiamos valor temporal
	}
	//alert("ids:\n"+ids);
	buscando=1;
	//buscamos en ajax
		$.ajax({
			type:'post',
			url:'ajax/buscaNuevosMovimientos.php',
			cache:false,
			data:{productosEnTemporal:ids,horaDeInicio:hora,suc:id_sucursal_en_edicion},
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
						//si hay cambio de temporales
							if($("#temporal_"+cont).html()!=ax_info[1]){

								$("#temporal_"+cont).html(ax_info[1]);//asignamos valor a temporal		
							
							//cambiamos los valores de inventario virual y físico DESHABILITADO POR oSCAR 14.09.2018
								//var inVirtual=parseInt(document.getElementById("2,"+cont).value);
								//var inFisico=parseInt(document.getElementById("3,"+cont).value);
								//document.getElementById("2,"+cont).value=parseInt(inVirtual+parseInt(ax_info[1]));
								//document.getElementById("3,"+cont).value=parseInt(inFisico+parseInt(ax_info[1]));
							//asignamos color rojo a la fila
								document.getElementById("fila"+cont).style.color="red";
								document.getElementById("1,"+cont).style.color="red";
								document.getElementById("2,"+cont).style.color="red";
								document.getElementById("3,"+cont).style.color="red";
								document.getElementById("4,"+cont).style.color="red";
							}
							if(ax_info[1]==0){
							//regresamos color negro a la fila
								document.getElementById("fila"+cont).style.color="black";
								document.getElementById("1,"+cont).style.color="black";
								document.getElementById("2,"+cont).style.color="black";
								document.getElementById("3,"+cont).style.color="black";
								document.getElementById("4,"+cont).style.color="black";
							}
						}
					}//termina for i
				}
				buscando=0;//liberamos varible que bloque busquedas repetidas
			}
		});
		return true;
	}

//aqui definimos intervalo de tiempo para buscar ventas en temporal
	//window.onload=
	var hora_inici='';
	window.onload=function cargaTiempo(){
	//extraemos el id de la sucursal en edición
		id_sucursal_en_edicion=$("#id_de_sucursal").val();
	//ocultamos la emergente de carga
		$("#emergente").css("display","none");
	//definimos hora de inicio de inventario
		var fecha = new Date();
		hora_inicio=fecha.getHours()+":"+fecha.getMinutes()+":"+fecha.getSeconds();
		//setInterval('buscaMovimientos(\''+hora_inicio+'\')',3000);//30 segundos
	}
/*Fin de cambio Oscar(03.05.2018)*/

/*implementación para mostrar emergente con nombre de vendedores Oscar 07.05.2018*/
	var aviso=0;
	function verificaTemporal(num, product_id){
		if(aviso==1){
			aviso=0;
			document.getElementById("3,"+num).select();
			return true;
		}
		
		if($("#temporal_"+product_id).html()!=0){
//alert( $("#temporal_"+product_id).html() + " id : " + '$("#temporal_"'+num+').html()' ); aviso=0; return false;
	//extraemos id del producto en temporal
		var id_prod=0;
		if( document.getElementById("0,"+num )){
			id_prod =  document.getElementById("0,"+num).getAttribute( 'value' );
		}else{
			id_prod =  document.getElementById("0,"+product_id).getAttribute( 'value' );
		}
	//mandamos valores por ajax
		$.ajax({
			type:'POST',
			url:'ajax/buscaNuevosMovimientos.php',
			cache:'false',
			data:{fl:2,id:id_prod,suc:id_sucursal_en_edicion,horaDeInicio:hora_inicio},
			success: function(datos){
//alert( datos );
				var arreglo=datos.split("|");
				if(arreglo[0]!='ok'){
					alert('Error!!!\n'+datos);
					return false;
				}else{
					aviso=1;
					$("#info_emerge").html(arreglo[1]);
					$("#emergente").css("display","block");
				//alert();
				}
			}//fin de function(datos)
		});//fin de ajax
		return false;
		}
	}
/*Fin de cambio 07.05.2018*/