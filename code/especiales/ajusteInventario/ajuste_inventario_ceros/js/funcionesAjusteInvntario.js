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
	/*	if(contCambios==0){
			alert('No hay cambios por guardar');
			return false;
		}*/
	//mostramos mensaje
		document.getElementById('emergente').style.display='block';
	//ocultamos boton de panel
		document.getElementById('footer').style.display='none';
	//ocultamos encabezado
		document.getElementById('enc').style.display='none';
	//calculamos tamaño de la tabla
		var tope=parseInt($('#products_and_product_providers_list tr').length);
	//declaramos variables
		var product_sum = '',
			product_substract = '',
			pp_sum = '', 
			pp_substract = '', 
			aux='',
			ax='',
			aux_id='',
			idProd='',
			tipo='',
			cS=0,
			cR=0;
	//limpiamos el contenido de la pantalla emergente
		$("#info_emerge").html('');
		var products_array = "";
		var products_providers_array = "";
		var temporal_product = null;
		var adjusts = new Array();
		var tmp_array = new Array();
		var tmp_id = null;
	//recorremos la tabla
		$( '#products_and_product_providers_list tr' ).each( function( index ){
		//busca el valor a nivel producto	
			if( $( this ).attr( 'is_master' ) == 1 ){
				var tmp = $( this ).attr( 'id' ).split( '_' );
				if( $( '#4_' + tmp[1] ).attr( 'value' ) > 0 ){
					product_sum += ( product_sum == "" ? "" : "|" );
					product_sum += tmp[1];
					product_sum += "," + $( '#4_' + tmp[1] ).attr( 'value' );
				}else if( $( '#4_' + tmp[1] ).attr( 'value' ) < 0 ){
					product_substract += ( product_substract == "" ? "" : "|" );
					product_substract += tmp[1];
					product_substract += "," + ( $( '#4_' + tmp[1] ).attr( 'value' ) * - 1 );
				}
				//}
			}
		});
//alert( tope );
	//adjusts = JSON.stringify( adjusts );
		//adjusts = JSON.parse(JSON.stringify(adjusts));
		for(var i=1;i<=tope;i++){
			aux = '4,' + i;
			if( document.getElementById(aux) ){
				ax = parseFloat( document.getElementById(aux).getAttribute('value') );
				if(ax != '0' ){
				// id del producto
					aux_id='0,'+i;
					idProd=document.getElementById(aux_id).getAttribute('value');
				//verifica si la diferencia es positiva o negativa para hacer el movimiento de almacen
					if(ax<0){
						pp_substract += ( pp_substract == '' ? '' : '|' );
						pp_substract += parseFloat( ax*-1 ) + ',' + $( '#fila' + i ).attr('product_provider');//multiplicamos ax por -1 para volverlo positivo
					}
					if(ax>0){
						pp_sum += ( pp_sum == '' ? '' : '|' );
						pp_sum += ax + ',' + $( '#fila' + i ).attr('product_provider');
					}
				}//cierra else
			}
		}
		console.log( product_sum );
		console.log( product_substract );
		console.log( pp_sum );
		console.log( pp_substract );
		/*return false;*/
	//mandamos valores por ajax
		$.ajax({
			type:'POST',
			url:'ajax/guardaAjuste.php',
			cache:'false',
			//data : { prtoducts : products_array, products_providers : products_providers_array },
			data:{sums : product_sum,
				substracts : product_substract, 
				sum_details : pp_sum, 
				subtract_details : pp_substract, 
				store_id : $( '#cambiaSuc' ).val(), 
				warehouse_id : $( '#warehouse' ).val()
			},
			success: function(datos){
				console.log( datos );
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
		window.location="index.php?store_id="+btoa($('#cambiaSuc').val()) + "&warehouse_id="+btoa($('#warehouse').val());	
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
			window.location="../../../../";
			return false;
		}
		if(flag==2){
			location.reload(true);
			return false;
		}
	}

	function getInventory( store_id, warehouse_id ){
	//
		$( '.emergent_content' ).html( '<div class="text-center"><h2>Cargando inventario...</h2><br><img src="../../../../img/img_casadelasluces/load.gif" with="50%"></div>' );
		$( '.emergent' ).css( 'display', 'block' );
		$.ajax({
			type:'POST',
			url:'ajax/getInventory.php',
			cache:'false',
			data:{ store_id : store_id, warehouse_id : warehouse_id },
			success: function(datos){
				//console.log( datos );
				$( '#products_and_product_providers_list' ).html( datos );
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );
				$( '#btn_get_inventory_container' ).addClass( 'no_visible' );
				$( '#btn_save_container' ).removeClass( 'no_visible' );
			}//fin de function(datos)
		});
	}

	function change_warehouse(){
		if( $( '#warehouse' ).val() == 0 ){
			alert( "Es necesario que selecciones un almacen para continuar!" );
		}
		location.href = "./index.php?store_id=" + $("#id_de_sucursal").val() + "&warehouse_id=" + $( '#warehouse' ).val();
	}

	function change_btn_type(){
		$( '#btn_get_inventory_container' ).removeClass( 'no_visible' );
		$( '#btn_save_container' ).addClass( 'no_visible' );
	}
ventana_abierta = null;
	function export_to_csv(){
		if( $( '#products_and_product_providers_list tr' ).length <= 0 ){
			alert( "No hay datos por descargar!" ); return false;
		}
		var data = "Id producto,Orden Lista,Producto,Inventario,Diferencia";
		//recorremos la tabla
		var is_master = 0;
		$( '#products_and_product_providers_list tr' ).each( function( index ){
			is_master = ( $( this ).attr( 'is_master' ) == 1 ? 1 : 0 );
			data += ( data == "" ? "" : "\n" );

			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 == 1 ){//id_producto
					data += $( this ).attr( 'value' ).trim() + ',';
				}
				if( index2 == 2 ){//orden_lista
					data += $( this ).html().trim() + ',';
				}
				if( index2 == 4 ){//Producto
					if( is_master == 1 ){
						$( this ).children( 'div' ).each( function( index3 ){
							data += $( this ).html().trim() + ',';
						});
					}else{
						data += $( this ).html().trim() + ',';
					}
				}
				if( index2 == 7 ){//Inventario
					data += $( this ).html().trim() + ',';
				}
				if( index2 == 8 ){//Diferencia
					data += $( this ).html().trim();
				}
			});
		});
		console.log( data );
		$("#datos").val(data);
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana, 3000);
	}

	function cierra_pestana(){
		$("#datos").val("");//resteamos variable de datos
		ventana_abierta.close();//cerramos la ventana
	}

/*Fin de cambio 07.05.2018*/