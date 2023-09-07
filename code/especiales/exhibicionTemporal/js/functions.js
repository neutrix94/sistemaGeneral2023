var en_edicion=0;//variable que indica si una celda está en edición
	var valor_antes="";//variable que guarda el valor antes de editar una celda
	var fila_resaltada=0;//variable que guarda que fila está resaltada
	var opc_resaltada=0;

	function exit(){
		if( confirm( "Salir al panel principal" ) ){
			location.href = "../../../index.php";
		}
	}
/*función que edita celdas*/
	function editaCelda(flag,num){
	//validamos que la celda no se encuentre en edición
		if(en_edicion==1){
			return false;//si la celda ya está en edición no hacemos nada
		}
	//ponemos la celda en edición
		en_edicion=1;
	//extraemos el valor de la celda y lo guardamos en variable global
		valor_antes=$("#p_p_"+flag+"_"+num).html();
		//alert( "#p_p_"+flag+"_"+num );
		var caja_txt='<input type="text" id="tmp_txt" style="width:99%;height:35px;text-align:right;" value="'+valor_antes+'"" '+
		'onblur="desEditaCelda('+flag+','+num+');" onkeyup="validarTeclaCelda(event,'+flag+','+num+');">';
		$("#p_p_"+flag+"_"+num).html(caja_txt);
		$("#tmp_txt").select();
	}
/*fin de función que edita celdas*/

/*función que desEdita celdas*/
	function desEditaCelda(flag,num){
	//extraemos el valor de la caja de texto
		var nvo_valor=$("#tmp_txt").val();
		if(nvo_valor==''||nvo_valor==null){
			nvo_valor=0;
		}
		$("#p_p_"+flag+"_"+num).html(nvo_valor);
		var pendientes=parseInt($("#7"+"_"+num).html());
		if((parseInt($("#4"+"_"+num).html())+parseInt($("#5"+"_"+num).html()))>pendientes){
			$("#p_p_"+flag+"_"+num).html("0");
//alert("no se puede regresar o exhibir más de lo que se tomó!!!");
			en_edicion=0;
//setTimeout(editaCelda(flag,num),500);
			return false;
		}

	//hacemos las sumas de las columnas
		var sumatoria=pendientes-(parseInt($("#4"+"_"+num).html())+parseInt($("#5"+"_"+num).html()));
		$("#3"+"_"+num).html(sumatoria);
		en_edicion=0;//liberamos celda en edición
	}

/*fin de función que desEdita celdas*/

/*función que valida las teclas en caja de texto temporal*/
	function validarTeclaCelda(e,flag,num){
		var tca=e.keyCode;
		//alert(tca);
	//tecla izquierda
		if(tca==37){
			if(flag==4){
				return false;
			}
			$("#buscador_exh").focus();
			$("#"+parseInt(flag-1)+"_"+num).click();
		}
	//tecla arrriba
		if(tca==38){
			if(num==1){
				return false;
			}
			$("#fila_"+parseInt(num-1)).focus();
			$("#"+flag+"_"+parseInt(num-1)).click();
		}
	//tecla derecha
		if(tca==39){
			if(flag==5){
				return false;
			}
			$("#buscador_exh").focus();
			$("#"+parseInt(flag+1)+"_"+num).click();

		}
	//tecla abajo o intro
		if(tca==40||tca==13){
			if(num>=$("#total_filas").val()){
				return false;
			}
			$("#fila_"+parseInt(num+1)).focus();
			$("#"+flag+"_"+parseInt(num+1)).click();
		}
	}
/*fin de función que valida las teclas en caja de texto temporal*/

/*funcion que resalta/regresa color de filas del grid de datos*/
	function resalta_fila(num){
		var color="#BAD8E6";
		if(num%2==0){
			color="#E6E8AB";
		}
	//verificamos si ya hay una fila resaltada
		if(fila_resaltada!=0){
		//regresamos el color original a la celda resaltada
			$("#fila_"+fila_resaltada).css("background",color);	
		}
	//resaltamos la fila donde se dió click
		fila_resaltada=num;//asignamos la nueva fila resltada
			//$("#fila_"+fila_resaltada).focus();
			$("#fila_"+fila_resaltada).css("background","rgba(0,225,0,0.5)");	
	}
/*fin de funcion que resalta/regresa color de filas del grid de datos*/

/*función que guarda los datos en la BD*
	function guardar(){
	//sacamos el tamaño de la tabla
		var tam=$("#total_filas").val();
		var datos="";//declaramos la variable que guardará los datos
		var agotados=0;//declaramos contador de productos agotados
	//recorremos la tabla en busca de valores
		for(var i=1;i<=tam;i++){
		//comprobamos si existe la fila
			if(document.getElementById("fila_"+i)){

			//verificamos si hay datos válidos
				if(($("#4_"+i).html()!=0 && $("#4_"+i).html()!='')||($("#5_"+i).html()!=0 && $("#5_"+i).html!='')){
				//guardamos los datos en la variable datos
					datos+=$("#1_"+i).html()+"~";//id del registro
					datos+=$("#4_"+i).html()+"~";//piezas exhibidas
					datos+=$("#5_"+i).html()+"~";//piezas agotadas
					datos+=$("#6_"+i).html()+"|";//id del producto
				//checamos si se trata de un producto agotado
					if($("#5_"+i).html()!=0 && $("#5_"+i).html!=''){
						agotados++;
					}
				}//fin de si es registro válido
			}//fin de si existe la fila
		}//fin de for i	
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'procesosBD.php',
			cache:false,
			data:{fl:'guarda',arr:datos,mov_alm:agotados},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n"+dat);
				}else{
				//recargamos la página
					location.reload();
				}
			}

		});
	}
/*fin de función que guarda los datos en la BD*/
	function save_row( exhibition_id ){
		var array = "";
		$( '#contenidoTabla tr' ).each( function( index ){
			//alert(  $( this ).attr( 'exhibition_id' ) + ' == ' + exhibition_id );
			if( document.getElementById( 'p_fila_' + index ) ){
				if( $( '#p_fila_' + index ).attr( 'exhibition_id' ) == exhibition_id && $( '#p_5_' + index ).html().trim() > 0 ){
					if( $( '#p_fila_' + index ).attr( 'is_principal' ) == 1 ){
						array += exhibition_id + '|' + $( '#p_5_' + index ).html().trim() + '|' + $( '#p_8_' + index ).html().trim();
					}
				}
			}
		});
		$( '#contenidoTabla tr' ).each( function( index ){
			//alert(  $( this ).attr( 'exhibition_id' ) + ' == ' + exhibition_id );
			if( document.getElementById( 'p_p_fila_' + index ) ){
				if( $( '#p_p_fila_' + index ).attr( 'exhibition_id' ) == exhibition_id ){
					if ( parseInt( $( '#p_p_5_' + index ).html().trim() ) > 0 || $( '#p_p_8_' + index ).html().trim() > 0 ){
						array += "|~|";
	//alert( $( this ).attr( 'detail_id' ) );
						array += exhibition_id;
						array += '|' + $( '#p_p_fila_' + index ).attr( 'detail_id' );
						array += '|' + $( '#p_p_5_' + index ).html().trim();
						array += '|' + $( '#p_p_8_' + index ).html().trim();
						array += '|' + $( '#p_p_2_' + index ).html().trim();
						array += '|' + $( '#p_p_1_' + index ).attr( 'value' ).trim();
					}
				}
			}
		});
		if( array == "" ){
			alert( "No hay acciones por guardar, escanea / busca y vuelve a intentar" );
			return false;
		}
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exhibitionProducts.php',
			cache:false,
			data:{ exhibition_flag : 'saveRow', arr : array },
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n"+dat);
				}else{
					alert( aux[1] );
					getExhibitionPending();
				}
			}
		});

	}

	function cancel_row( exhibition_id ){
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exhibitionProducts.php',
			cache:false,
			data:{ exhibition_flag : 'cancelRow', exhibition_header_id : exhibition_id },
			success:function(dat){
				alert( dat );
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n"+dat);
				}else{
					alert( aux[1] );
					getExhibitionPending();
				}
			}
		});
	}

	function getExhibitionPending(  ){
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exhibitionProducts.php',
			cache:false,
			data:{ exhibition_flag : 'getPendingList' },
			success:function(dat){
				$( '#contenidoTabla' ).empty();
				$( '#contenidoTabla' ).html( dat );
			}
		});
	}


/*función que realiza busqueda*/
	function validaBusc(e){
		var texto=$("#buscador_exh").val();
		var tca=e.keyCode;
		if(tca==40){
		//enfocamos la primera opción del buscador
			resalta_opc_busc(1);
			return true;
		}
		if(texto.length<=2){
			$("#res_busc").html("");
			$("#res_busc").css("display","none");
			return true;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'procesosBD.php',
			cache:false,
			data:{fl:'busqueda',txt:texto},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n"+dat);
					return false;
				}
			//cargamos los datos en el resultado de la búsqueda
				$("#res_busc").html(aux[1]);
				$("#res_busc").css("display","block");
			}
		});
	}
/*fin de función que realiza busqueda*/

/*función que valida tecla oprimida en opciones de bucasdor*/
	function valida_tca_busc(e,id,num){
		var tca=e.keyCode;
	//tecla intro
		if(tca==13){
			$("#fila_opc_"+num).click();
		}
	//tecla arriba
		if(tca==38){
			resalta_opc_busc(parseInt(num-1));
			//$("#fila_opc_"+parseInt(num-1)).focus();
		}
	//tecla abajo
		if(tca==40){
			resalta_opc_busc(parseInt(num+1));
			//$("#fila_opc_"+parseInt(num+1)).focus();
		}
		return true;
	}
/*fin de función que valida tecla oprimida en opciones de bucasdor*/
	
/*función que enfoca la fila correspodiente al resultado del buscador*/
	function enfoca(id){
	//sacamos el valor de la tabla
		var tam=$("#total_filas").val();
	//recorremos la tabla en búsqueda del id seleccionado
		for(var i=0;i<=tam;i++){
		//comprobamos si existe la fila
			if(document.getElementById('fila_'+i)){
				if($("#1_"+i).html()==id){
					$("#buscador_exh").val("");//limpiamos el buscador
					opc_resaltada=0;//resetamos variable de opción resaltada
					$("#res_busc").html();//limpiamos los resultados de busqueda
					$("#res_busc").css("display","none");//oculatamos resultados de busqueda
					$("#fila_"+i).focus();//enfocamos fila
					$("#4_"+i).click();//activamos edición de piezas exhibidas
					return true;
				}
			}
		}//fin de for i
		alert("El registro no se encuentra en la tabla!!!");
		$("#buscador_exh").select();
		return true;
	}
/*fin de función que enfoca la fila correspodiente al resultado del buscador*/

/*función que enfoca opciones de búsqueda*/
	function resalta_opc_busc(num){
	//comprobamos si ya hay una celda resaltada
		if(opc_resaltada!=0){
			//regresamos el color blanco
			$("#fila_opc_"+opc_resaltada).css("background","white");
		}
	//asignamos la nueva opcion resaltada
		opc_resaltada=num;
		$("#fila_opc_"+opc_resaltada).css("background","rgba(0,225,0,.6)");
		$("#fila_opc_"+opc_resaltada).focus();
		return true;
	}
/*fin de función que enfoca opciones de búsqueda*/
	function seekProductProvider( e ){
		var keyCode = e.keyCode;
		if( keyCode != 13 && e != 'intro' ){
			return false;
		}
		var txt = $( '#seeker_input' ).val().trim();
		$.ajax({
			type : 'post',
			url : 'ajax/exhibitionProducts.php',
			cache : false,
			data : { exhibition_flag : 'seeker', key : txt },
			success : function ( dat ){
				var aux  = dat.split( '|' );
				switch ( aux[0] ) {
					case 'error' :
						alert( "Error : \n" + aux );
						return false;
					break;
					case 'seeker' :
						$( '#seeker_response' ).html( aux[1] );
						$( '#seeker_response' ).css( 'display', 'block' );
					break;
					case 'was_found' :
						var product_provider = JSON.parse( aux[1] );
						put_product_provider( product_provider );
					break;
					case 'message_info' :
						$( '.emergent_content' ).html( aux[1] );
						$( '.emergent' ).css( 'display', 'block' );
					break;
					default : 
						alert( dat );
						return false;
					break;
				}
			}
		});
	}

	function close_emergent(  ){
		$( '.emergent_content' ).html( '' );		
		$( '.emergent' ).css( 'display', 'none' );		
	}

	function close_emergent_2(  ){
		$( '.emergent_content_2' ).html( '' );		
		$( '.emergent_2' ).css( 'display', 'none' );		
	}

	function put_product_provider( product_provider ){
		alert_scann( 'audio' );
		var exists = false;
	//busca el proveedor producto en la tabla
		$( '#contenidoTabla tr' ).each( function( index ){
			//alert( $( '#p_p_1_' + index ).attr( 'value' ) + '==' + product_provider.product_provider_id );
			if( document.getElementById( 'p_p_1_' + index ) 
				&& $( '#p_p_1_' + index ).attr( 'value' ) == product_provider.product_provider_id 
				&& parseInt( $( '#p_4_' + index ).html().trim() ) > parseInt( $( '#p_5_' + index ).html().trim() ) ){
				exists = true;
				$( '#p_p_5_' + index ).click();
				$( '#tmp_txt' ).val( parseInt( $( '#tmp_txt' ).val() ) + parseInt( 1 ) );
				$( '#seeker_input' ).val( '' );
				$( '#seeker_input' ).focus();
				var exhibition_id = $( '#p_p_fila_' + index ).attr( 'exhibition_id' );
				setTimeout( function(){
					sum_product_level( product_provider.product_id, exhibition_id );
					return false;
				}, 100);
				return false;
			}
		});
		if( ! exists ){
			alert( "El producto no fue encontrado o las exhibiciones ya fueron llenadas!\nVerifica y vuelve a intentar!" );
			$( '#seeker_input' ).select();
		}
	}

	function sum_product_level( product_provider, exhibition_id ){
		$( '#contenidoTabla tr' ).each( function( index ){
			//alert( $( '#p_p_1_' + index ).attr( 'value' ) + '==' + product_provider.product_provider_id );
			if( document.getElementById( 'p_fila_' + index ) 
			&& parseInt($( '#p_fila_' + index ).attr( 'exhibition_id' )) == parseInt( exhibition_id ) ){
				var aux = parseInt( $( '#p_5_' + index ).html().trim() ) + parseInt( 1 );
				$( '#p_5_' + index ).html( aux );
				return true;
			}
		}); 
	}

	var audio_is_playing = false;
	function alert_scann( type ){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById( type );
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();
	}
	
	function setProductByName( product_id, type ){
		$( '#product_barcode_seeker_pieces_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		//$( '#product_barcode_seeker_response' ).html( '' );
		$( '#product_barcode_seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/exhibitionProducts.php?fl=getOptionsByProductId&product_id=" + product_id + "&is_by_name=1";
		url += "&type=" + type;
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : url,
			cache : false,
			data : { exhibition_flag : 'getOptionsByProductId' },
			success : function( dat ){
				dat = dat.split( '|' );
				if( type == 'principal' ){
					$(".emergent_content").html(dat[0]);
					$(".emergent").css("display","block");
					$( "#seeker_response" ).html();
					$( "#seeker_response" ).css( 'display', 'none' );
				}else if( type == 'emergent' ){
					$( '.emergent_content_2' ).html( dat[0] );
					$( '.emergent_2' ).css( "display", "block" );
				}
			//implementacion Oscar 2023 para que no se tenga que seleccionar el pp en maquilados
				/*if( response[1] == '1' ){
					select_product_provider_automatic();
				}*/
			}
		});
//alert( response );
	}


	function setProductModel( sale_detail_id, is_sale_return = null, product_id = null, 
		ticket_id = null, was_found_by_name = null, type ){
		var model_selected = -1;
		$( '#model_by_name_list tr' ).each( function ( index ){
			if( $( '#p_m_5_' + index ).prop( 'checked' ) ){
			//	alert( index );
				model_selected = $( '#p_m_5_' + index ).val();
				if( is_sale_return != null ){
					model_selected = $( '#p_m_6_' + index ).html().trim();
				}
			}
		});
		if( model_selected == -1 ){
			alert( "Debes de seleccionar un modelo para continuar!" );
			return false;
		}
		if( type == 'principal' ){
			$( '#seeker_input' ).val( model_selected );
			seekProductProvider( 'intro' );
			close_emergent();
			$( '#seeker_input' ).val( '' );
		}else if( type == 'emergent' ){
			$( '#new_product_seeker' ).val( model_selected );
			seek_new_product( 'intro' );
			$( '#new_product_seeker_response' ).html( '' );
			$( '#new_product_seeker_response' ).css( 'display', 'none' );
			close_emergent_2();
			$( '#new_product_seeker' ).val( '' );
		}
	}

	function select_product_provider_automatic(){
		setTimeout( function(){}, 300);
		$( '#p_m_5_0' ).prop( 'checked', true );
		setTimeout( function(){}, 300);
		$( '#select_p_p_by_name_btn' ).click();
		
	}

/**/
	function showNewProductForm(){
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/newProductForm.php',
			cache:false,
			data:{fl:'new'},
			success:function(dat){
				$(".emergent_content").html(dat);
				$(".emergent").css("display","block");
			}
		});
	}

	function getProductProviderNotes( product_provider_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/productProviderNotes.php',
			cache : false,
			data : { product_provider_id : product_provider_id },
			success : function( dat ){
				$(".emergent_content").html(dat);
				$(".emergent").css( "display","block" );
			}
		});
	}

	function editExhibitionRow( exhibition_id ){
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/updateExhibitionForm.php',
			cache : false,
			data : { exhibition_id : exhibition_id },
			success : function( dat ){
				$(".emergent_content").html(dat);
				$(".emergent").css( "display","block" );
			}
		});
	}
