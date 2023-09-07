//extraemos el total de filas
	$(function() {
		topeAbajo=document.getElementById('tope').value;
		//alert(topeAbajo);
	});
	$( '#emergente' ).css( 'display', 'none' );


//funcion que guarda ajuste de Inventario
	function guarda(sucursal){
		var store_id = $( '#store_id' ).val();
		var warehouse_id = $( '#warehouse_id' ).val();
	//validamos si se realizaron cambios
		/*if(contCambios==0){
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
		var tope=parseInt($('#adjustment_content tr').length);
	//declaramos variables
		var suma='',resta='',aux='',ax='',aux_id='',idProd='',tipo='',cS=0,cR=0, equals = '';
	//limpiamos el contenido de la pantalla emergente
		$("#info_emerge").html('');
	//recorremos la tabla
	//alert( tope );
		for(var i=1;i<=tope;i++){
			aux='4,'+i;
			if( document.getElementById(aux) ){
				ax=document.getElementById(aux).getAttribute('value');
				if( ax == '0' ){
					equals += ( equals == "" ? "" : "|" );
					equals += $( '#fila' + i ).attr('product_provider');
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
			data:{
				quita : cR + resta,
				agrega : cS + suma,
				suc : store_id, 
				alm : warehouse_id,
				sin_movimientos : equals
			},
			success: function(datos){
				var aux=datos.split("|");
				if(aux[0]=='ok' && aux[1]=='ok'){
					if($("#info_emerge").html('<br><br><br><br><br><p>Folio generado: '+aux[2]+'</p><input type="button" value="Aceptar" onclick="location.href=\'configuration.php\';">')){
						alert('Cambios guardados exitosamente!!!');
					}
					return true;
				}else{
					alert('ERROR :\n'+datos);
					$("#emergente").css("display","none");
				}
			}//fin de function(datos)
		});//fin de ajax
		return false;
	}//fin de funcion guardar

	function redirect( type ){
		switch( type ){
			case 'home' : 
				if( confirm( "Salir de esta pantalla?" ) )
					location.href="../../../../index.php?";
			break;

			case 'configuration' : 
				if( confirm( "Salir de esta pantalla?" ) )
					location.href="./configuration.php?";
			break;
		}
	}