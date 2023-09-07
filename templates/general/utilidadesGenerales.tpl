
{literal}
	<script type="text/javascript">
	var global_is_repeat = 0;

	function getRenewProductProviderBarcodePrefix(){
		var url = "../especiales/Etiquetas/barcodes/ajax/db.php?fl=validateBarcodesSeriesUpdate&since_content=1";
		var response = ajaxR( url );//alert( response );
		$( '#contenido_emergente_global' ).html( response );
		$( '#ventana_emergente_global' ).css( 'display', 'block' );
	}
	function update_barcodes_prefix( obj ){
	//valida que coincida el texto
		if( $( '#change_prefix_input_tmp' ).val().trim() == '' || $( '#change_prefix_input_tmp' ).val().trim().toUpperCase() != 'CAMBIAR PREFIJO'  ){
			alert( "Debes escribir \"CAMBIAR PREFIJO\" para continuar!" );
			$( '#change_prefix_input_tmp' ).focus();
			return false;
		}
		var url = "../especiales/Etiquetas/barcodes/ajax/db.php?fl=updateBarcodesPrefixSinceContent";
		var response = ajaxR( url );
	//alert( response );
		$( '#contenido_emergente_global' ).html( response );
		$( '#ventana_emergente_global' ).css( 'display', 'block' );

	}

/*implementacion Oscar 2023 para hacer arrastable el grid de proveedor producto
	function sort_product_provider( ){
		ordena(25, 'proveedorProducto','48');	
		$( function() {
		$( '#Body_proveedorProducto' ).children( 'tbody' ).each( function ( index ){
			$( this ).addClass( 'sortable' );
		});
	    $( ".sortable" ).sortable({
	      	stop : function (e, ui){
	      		reorder_prduct_provider_priorities();
	      		//setTimeout( 'reorder_prduct_provider_priorities()', 300 );
	    	}
	  	});
	});
	}

	function reorder_prduct_provider_priorities(){
		alert();
		$( '#Body_proveedorProducto tr' ).each( function( index ){
			var tmp = ( index + 1 );
			$( this ).children( 'td' ).each( function( index_2 ){
				if( index_2 == 0 || index_2 == 25 ){
					$( this ).html( tmp );
					valorXY( 'proveedorProducto' , 24, index, tmp );
					$( '#proveedorProducto_24_' + index ).attr( 'valor', tmp );
				}
			});
		});
	}
fin de cambio Oscar 2023*/

		function get_maquile_configuration_info(){
			var message = `<div style="position : relative; width : 90%; left : 5%; padding : 10px; font-size : 150%;" >
								<div class="col-1"></div>
								<div style="text-align : center; background-color : white">
									<br>
									<h3>Se ocupa para rollos de productos maquilados. Ejemplo : </h3>
									<p>Se tienen dos rollos de feston oro 20m, si este check esta activado imprimirá <b>2</b> etiquetas 
									de pieza.</p>
									<br>
								</div>
							</div>`;
			$( '#contenido_emergente_global' ).html( message );
			$( '#ventana_emergente_global' ).css( 'display', 'block' );

		}

		function show_list_order_msg(){
			var message = '<div id="l_o_emergent" style="position:relative; background-color : white;" tabindex="1"><p align="center" style="color:red; font-size:300%;">Importante :</p>';
			message += '<p style="color:black; font-size:200%;">Verifique los códigos de barras.</p>';
			message += '<p align="center"><button onclick="close_emergent();">Aceptar</button></p></div>';
			$( '#contenido_emergente_global' ).html( message );
			$( '#ventana_emergente_global' ).css( 'display', 'block' );
			$( '#l_o_emergent' ).focus();
		}

		function close_emergent(){
			$( '#contenido_emergente_global' ).html( '' );
			$( '#ventana_emergente_global' ).css( 'display', 'none' );
		}
	/*oscar 20202 ( desactivar caja, paquete )*/
		function just_piece( obj, pos ){
			return false;
			//alert(  );
			if( $( '#proveedorProducto_18_' + pos ).attr( 'valor' ) == '1' ){
				if( confirm( "Si el tratamiento es por pieza se desactivará CAJA y PAQUETE\nDesea continuar?" ) == true ){
					$( '#proveedorProducto_10_' + pos ).attr( 'valor', '0' );
					$( '#proveedorProducto_10_' + pos ).html( '0' );
					$( '#proveedorProducto_11_' + pos ).attr( 'valor', '' );
					$( '#proveedorProducto_11_' + pos ).html( '' );
					$( '#proveedorProducto_14_' + pos ).attr( 'valor', '0' );
					$( '#proveedorProducto_14_' + pos ).html( '0' );
					$( '#proveedorProducto_15_' + pos ).attr( 'valor', '' );
					$( '#proveedorProducto_15_' + pos ).html( '' );
				}else{
					$( '#proveedorProducto_18_' + pos ).attr( 'valor', '0' );
					$( '#cproveedorProducto_18_' + pos ).removeAttr( 'checked' );
				}
			}/*else{
				alert( 'no checked' );
			}*/
		}

	/*implementación Oscar 12.02.2019 para calcular precio por caja en grid de proveedor producto*/
		function cambia_precio_caja_proveedor(pos,grid){
			//alert(pos);
			var prc_pza=parseFloat($("#"+grid+"_5_"+pos).html().trim());
			if(isNaN(prc_pza)){
				alert("El precio por pieza no puede ir vacío!!!");
				$("#"+grid+"_5_"+pos).html('0');
				$("#"+grid+"_5_"+pos).attr("valor",'0');
				return false;
			}

			var pza_caja=parseFloat($("#"+grid+"_14_"+pos).html().trim());
			var precio_caja=Math.round(parseFloat(pza_caja*prc_pza),2);
			$("#"+grid+"_17_"+pos).html(precio_caja);
			$("#"+grid+"_17_"+pos).attr("valor",precio_caja);

			//valorXY(grid, 6, pos, 0);

		}
	/*Fin de cambio Oscar 12.02.2019*/

	/*Implementación Oscar 2022 para limpieza de productos*/
		function reset_product(){
			if( !confirm( "¿Realmente desea resetaer el producto?\nEsta acción ELIMINARÁ toda la INFORMACIÓN del producto" ) ){
				return false;
			}
			product_id = $( '#id_productos' ).val();
			$.ajax({
				type : 'post',
				url : '../especiales/reset_product.php',
				data : { id : product_id },
				success : function ( dat ){
					if( dat == 'ok' ){
						alert( 'El producto fue reseteado exitosamente' );
						location.reload();
					}else{
						alert( dat );
						return false;
					}
				}
			});	
		}
	/*implementacion de Oscar 2021 para evitar que se capturen caracteres especiales*/
		function evitar_simbolos_especiales(e, obj){
			$(obj).val($(obj).val().split(',').join(''));
			$(obj).val($(obj).val().split('/').join(''));
			$(obj).val($(obj).val().split('\\').join(''));
			$(obj).val($(obj).val().split('|').join(''));
			$(obj).val($(obj).val().split(' ').join(''));
		}
	/*implementacion de Oscar 2021 para enviar mensaje en cambio de estacionalidades*/
		function lanza_aviso_cambio_estacionalidad(){
			var txt_description = "<h1 style=\"color:white; top :800px; position : relative; margin:30px;\"><b>"
			+ "Antes de generar la estacionalidad inicial de la temporada debe de estar en estacionalidad alta y antes de seleccionar la estacionalidad final debe de generarse la estacionalidad final de cada sucursal"
			+ "</b><br /><br /><br />"
			+ "<center><button type=\"button\" onclick=\"document.getElementById('btn_cerrar_emergente_global').click();\""
			+ " style=\"padding : 20px; font-size : 20px; border-radius : 15px;\">Aceptar</button></center>"
			+ "</h1>";
			$( '#contenido_emergente_global' ).html( txt_description );
			$( '#ventana_emergente_global' ).css( 'display', 'block' );
		}

		/*implementacion de Oscar 2021 para enviar mensaje en cambio de estacionalidades*/
		function lanza_aviso_cambio_estacionalidad(){
			var txt_description = "<h1 style=\"color:white; top :800px; position : relative; margin:30px;\"><b>"
			+ "Antes de generar la estacionalidad inicial de la temporada debe de estar en estacionalidad alta y antes de seleccionar la estacionalidad final debe de generarse la estacionalidad final de cada sucursal"
			+ "</b><br /><br /><br />"
			+ "<center><button type=\"button\" onclick=\"document.getElementById('btn_cerrar_emergente_global').click();\""
			+ " style=\"padding : 20px; font-size : 20px; border-radius : 15px;\">Aceptar</button></center>"
			+ "</h1>";
			$( '#contenido_emergente_global' ).html( txt_description );
			$( '#ventana_emergente_global' ).css( 'display', 'block' );
		}

		var global_change_barcode_validation = 0;
		function valida_codigo_barras ( obj, pos, cell, grid ){
			if( global_change_barcode_validation == 1 ){
				global_change_barcode_validation = 0;
				return false;
			}
			global_change_barcode_validation = 1;
		//valida que el código de barras no esté en el grid
			/*if( !search_in_grid_x( $( '#' + grid + '_' + cell +'_' + pos ).html(), pos, cell ) ){*/
			var check = check_codigo_barras_final( obj, pos, cell, grid );
			if ( ! check ){
				valorXY( grid , cell, pos, '' );
				$( '#' + grid + '_' + cell +'_' + pos ).html('');
				alert( "El código de barras ya existe en otro proveedor para este producto");
				setTimeout( function (){ 
					global_change_barcode_validation = 0;
					$( '#' + grid + '_' + cell +'_' + pos ).click();
				}, 100);
				return false;
			}else if( check == 'is_the_same_barcode_and_provider' ){
				setTimeout( function (){ 
					global_change_barcode_validation = 0;
					//$( '#' + grid + '_' + cell +'_' + pos ).click();
				}, 100);
				return false;
			}
//return false;
			$.ajax({
				type : 'post',
				url : '../especiales/validacion_codigo_barras.php',
				data : { 
					barcode : $( '#' + grid + '_' + cell +'_' + pos ).html(),
					key : $('#' + grid + '_1_' + pos ).html(),
					type : cell 
				},
				success : function ( dat ){
					if ( dat != 'ok' ){
						$( '#' + grid + '_' + cell +'_' + pos ).html('');
						valorXY( grid , cell, pos, '' );
						alert( dat );
						setTimeout( function (){ 
					global_change_barcode_validation = 0;
							$( '#' + grid + '_' + cell +'_' + pos ).click();
						}, 100);						
						return false;
					}
				}
			});
		}
//verifica registro en la misma fila
		/*function search_in_grid_x( new_barcode_value, pos, cell ){
			var num=NumFilas('proveedorProducto');//numero de filas en el grid
			for ( var i = 0; i < num; i++ ){
				if( ($( '#proveedorProducto_6_' + i ).html().trim() == new_barcode_value.trim() && pos != i) ){
					return false;
				}
				if( ($( '#proveedorProducto_7_' + i ).html().trim() == new_barcode_value.trim() && pos != i) ){
					return false;
				}
				if( ($( '#proveedorProducto_8_' + i ).html().trim() == new_barcode_value.trim() && pos != i) ){
					return false;
				}
				if( ($( '#proveedorProducto_11_' + i ).html().trim() == new_barcode_value.trim() && pos != i) ){
					return false;
				}
				if( ($( '#proveedorProducto_12_' + i ).html().trim() == new_barcode_value.trim() && pos != i) ){
					return false;
				}
			}
			return true;
		}*/

//verificacion final del código de barras
	function check_codigo_barras_final( obj, pos, cell, grid ){//1,6
		//alert( pos + ',' + cell );
		var num=NumFilas('proveedorProducto');//numero de filas en el grid
		var existentes = new Array();
		var provider_to_check = new Array();
		var response = true;
		var is_the_same_product_provider = 0;
		//alert( pos );

	if( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() != '' 
		&& $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() != '&nbsp;' ){
		for ( var i = 0; i < num; i++ ){
			//alert( 'i :' + i );
			//if( i != pos ){
				//console.log(pos);
				if(	( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_6_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_7_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_8_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_11_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_12_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_15_' + i ).html().trim() 
					|| $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_16_' + i ).html().trim() )
					//&& $( '' )
				){
					if( ( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_6_' + i ).html().trim()
						|| $( '#proveedorProducto_' + cell + '_' + pos).html().trim() == $( '#proveedorProducto_7_' + i ).html().trim()
						|| $( '#proveedorProducto_' + cell + '_' + pos).html().trim() == $( '#proveedorProducto_8_' + i ).html().trim() )
						&& $( '#proveedorProducto_2_' + pos ).attr( 'valor' ) == $( '#proveedorProducto_2_' + i ).attr( 'valor' )
						&& i != pos
					){
						/*alert( 'here_1 : ' + $( '#proveedorProducto_2_' + pos ).attr( 'valor' ) + ' == ' + $( '#proveedorProducto_2_' + i ).attr( 'valor' ) + "\n" + '#proveedorProducto_2_' + pos  + ' == ' +  '#proveedorProducto_2_' + i  );*/
    					show_the_same_barcoder_and_provider_emergent( obj, pos, cell, grid, i );//, i, k
    					return 'is_the_same_barcode_and_provider';
					}else if( i != pos ){
					//	alert( 'here_2' );
             		   response = false;
					//	return false;
					}
				} 
			//}
		}
	}
		return response;
	//recolecta todos los datos
		for ( var i = 0; i < num; i++ ){
			existentes[i] = new Array();
			existentes[i].push( $( '#proveedorProducto_2_' + i ).attr( 'valor' ) );
			existentes[i].push( $( '#proveedorProducto_6_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_7_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_8_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_11_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_12_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_15_' + i ).html().trim() );
			existentes[i].push( $( '#proveedorProducto_16_' + i ).html().trim() );

			existentes[i].push( [ 6, i ] );
			existentes[i].push( [ 7, i ] );
			existentes[i].push( [ 8, i ] );
			existentes[i].push( [ 11, i ] );
			existentes[i].push( [ 12, i ] );
			existentes[i].push( [ 15, i ] );
			existentes[i].push( [ 16, i ] );

		}
		console.log( existentes );
		for (var i = 0; i < existentes.length; i++) {
			//3
		    for( var k = 1; k <= 7; k ++ ){//existentes[i].length
	       	//7	
	       		for (var j = 0; j < existentes.length; j++) {
		    //3

		        	/*alert( "linea " + ( i + 1 ) + " : " + i +'_'+k + "    linea " + ( j + 1 ) + " : " + j +'_'+k + "\n"
		        		 + "     i = " + i + " K : " + k + "      j = " + j + " k : " + k +"\n"
		        		+ 'proveedor 1: ' + existentes[i][0] + '  proveedor 2 : ' + existentes[i][0] + "\n"
		        		+ existentes[i][k] + '==' +  existentes[j][k] + "?\n"
		        		 );//+ "linea 1 : " + i +'_'+k + "  linea 2 : " + j +'_'+k*/
			        if( i != j ){
			            if ( existentes[i][k] == existentes[j][k] 
			            	/*&& i != j*/ 
			            	&& existentes[i][k].trim() != ''
			            	&& existentes[j][k].trim() != ''
			            	&& existentes[i][k].trim() != '&nbsp;'
			            	&& existentes[j][k].trim() != '&nbsp;'
		            		&& ( existentes[j][8][0] == cell || existentes[j][9][0] == cell ||  existentes[j][10][0] == cell
	        					/*|| existentes[j][11][0] == cell || existentes[j][12][0] == cell
	        					|| existentes[j][13][0] == cell || existentes[j][14][0] == cell*/
	        					)
		            		&& (  existentes[j][8][1] == pos || existentes[j][9][1] == pos ||  existentes[j][10][1] == pos
	        					/*|| existentes[j][11][1] == pos || existentes[j][12][1] == pos
	        					|| existentes[j][13][1] == pos || existentes[j][14][1] == pos*/
	        					)

			            ) {
	        				//if( existentes[i][k] == existentes[j][k] ){
	        				//	alert( "ES EL MISMO EN DIFERENTE RENGLON" );
	            				if( ( k == 1 || k == 2 || k == 3 ) && existentes[i][0] == existentes[j][0] ){// && i != j
	            				//	alert( "Es el mismo proveedor : p1 - " + existentes[i][0] + " p2 - " + existentes[j][0] );
	            					show_the_same_barcoder_and_provider_emergent( obj, pos, cell, grid, j );
	            					return 'is_the_same_barcode_and_provider';
	        					}else{
	        					//	alert( `es el mismo valor en diferente proveedor` );
			             		   	response = false;
	        						return false;
	        					}
	        				//}
			            }/*else if( i == j && existentes[i][k] == existentes[j][k] ){
			            	//alert( `Es el mismo valor en el mismo renglon : ${i} ${k} == ${j} ${k} / ${existentes[i][k]} == ${existentes[j][k]}` );
			            }else if( existentes[i][k] != existentes[j][k] ){
		            		//alert( "-- NO SE REPITE --" );
			            }*/
		        	}
		        }
         	}
	   	}
	   	//alert( 'ok ' + response );
	   	return response;
	}

		function show_the_same_barcoder_and_provider_emergent( obj, pos, cell, grid, position ){

			var num=NumFilas('proveedorProducto');//numero de filas en el grid
			var barcode = '';
			var information_table = "<table class=\"table table-bordered table-striped\" style=\"width : 100%;\">";
				information_table += "<thead>";
					information_table += "<tr>";
						information_table += "<th>Proveedor</th>";
						information_table += "<th>Código</th>";
					information_table += "</tr>";
				information_table += "</thead>";

			/*for ( var i = 0; i < num; i++ ){
				if( position != i ){
					information_table += '<td>' + $( '#proveedorProducto_2_' + i ).html() + '</td>';
					information_table += '<td>' + $( '#proveedorProducto_6_' + i ).html() + '</td>';
					barcode = $( '#proveedorProducto_6_' + i ).html().trim();
				}
			}*/

		for ( var i = 0; i < num; i++ ){
			if( i != pos ){
				if( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_6_' + i ).html().trim()
					&& $( '#proveedorProducto_2_' + pos ).attr( 'valor' ) == $( '#proveedorProducto_2_' + i ).attr( 'valor' )
				){
					information_table += '<tr><td>' + $( '#proveedorProducto_2_' + i ).html() + ' (' + $( '#proveedorProducto_3_' + i ).html() + ' ) </td>';
					information_table += '<td>' + $( '#proveedorProducto_6_' + i ).html() + '</td></tr>';
					barcode = $( '#proveedorProducto_6_' + i ).html().trim();
				}
				if( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_7_' + i ).html().trim()
					&& $( '#proveedorProducto_2_' + pos ).attr( 'valor' ) == $( '#proveedorProducto_2_' + i ).attr( 'valor' )
				){

					information_table += '<tr><td>' + $( '#proveedorProducto_2_' + i ).html() + ' (' + $( '#proveedorProducto_3_' + i ).html() + ' ) </td>';
					information_table += '<td>' + $( '#proveedorProducto_7_' + i ).html() + '</td></tr>';
					barcode = $( '#proveedorProducto_7_' + i ).html().trim();
				}
				if( $( '#proveedorProducto_' + cell + '_' + pos ).html().trim() == $( '#proveedorProducto_8_' + i ).html().trim()
					&& $( '#proveedorProducto_2_' + pos ).attr( 'valor' ) == $( '#proveedorProducto_2_' + i ).attr( 'valor' )
				){

					information_table += '<tr><td>' + $( '#proveedorProducto_2_' + i ).html() + ' (' + $( '#proveedorProducto_3_' + i ).html() + ' ) </td>';
					information_table += '<td>' + $( '#proveedorProducto_8_' + i ).html() + '</td></tr>';
					barcode = $( '#proveedorProducto_8_' + i ).html().trim();
				}
    		}
    	}
			information_table += "</table>";

			var resp = "<h3>El código de barras que escribió ya existe para el mismo proveedor, ¿Desea asignarle el mismo a este nuevo proveedor producto?</h3>";

			resp += '<br>' + information_table + '<br>';

			resp += "<div class=\"row\">";
				resp += "<div class=\"col-2\"></div>";
				resp += "<div class=\"col-3\">";
					resp += "<button type=\"button\" onclick=\"setProductProviderPieceBarcode( 1,'" + obj + "'," + pos + "," + cell + ", '" + grid + "', '" + barcode + "' )\" class=\"btn btn-success form-control\">";
						resp += "<i class=\"\">Aceptar</i>";
					resp += "</button>";
				resp += "</div>";
				resp += "<div class=\"col-1\"></div>";
				resp += "<div class=\"col-3\">";
					resp += "<button type=\"button\" onclick=\"setProductProviderPieceBarcode( 0,'" + obj + "'," + pos + "," + cell + ", '" + grid + "' )\" class=\"btn btn-danger form-control\">";
						resp += "<i class=\"\">Cancelar</i>";
					resp += "</button>";
				resp += "</div>";
			resp += "<div>";
			$( '.emergent_content' ).html( resp );
			$( '.emergente' ).css( 'display', 'block' );
			$( '.emergent_content' ).focus();
		}

		function setProductProviderPieceBarcode( action, obj, pos, cell, grid, barcode = '' ){
			if( action == 1 ){//
				valorXY( grid , cell, pos, barcode );
				close_emergent();
				valorXY( grid , 22, pos, 1 );
				$( '#cproveedorProducto_22_' + pos ).prop( 'checked', true );
			}else{
				valorXY( grid , cell, pos, '' );
				$( '#' + grid + '_' + cell +'_' + pos ).html('');
				valorXY( grid , 22, pos, 0 );
				$( '#cproveedorProducto_22_' + pos ).removeAttr( 'checked', true );
				close_emergent();
			}
		}

/*funciones para ocultar / mostrar columnas del grid*/
		function hide_grid_accordion( column_number, grid_name ){
			return false;
			var num=NumFilas('proveedorProducto');//numero de filas en el grid

			$( '#H' + grid_name + column_number ).css('width', '35px');
			/*$( '#HproveedorProducto' + column_number ).val('CB1>');*/
			$( '#H' + grid_name + column_number ).attr('onclick', 'show_grid_accordion(' + column_number + ', \'' + grid_name + '\')');
			$( '#H' + grid_name + column_number ).attr('title', 'Código de Barras');
			for ( var i = 0; i <= num; i++ ){
				$( '#' + grid_name + '_' + column_number + '_' + i ).css('width', '35px');
				$( '#' + grid_name + '_' +  column_number + '_' + i ).css('color', '#f1f1f1');
			}
		} 
		function show_grid_accordion( column_number, grid_name ){
			var num=NumFilas('proveedorProducto');//numero de filas en el grid
			$( '#H' + grid_name + column_number ).css('width', '120px');
			$( '#H' + grid_name + column_number ).attr('onclick', 'hide_grid_accordion(' + column_number + ', \'' + grid_name + '\')');
			for ( var i = 0; i <= num; i++ ){
				$( '#' + grid_name + '_' + column_number + '_' + i ).css('width', '120px');
				$( '#' + grid_name + '_'  + column_number + '_' + i ).css('color', '#333');
			}
		} 

	//validación de proveedor-producto
		function modelsDepuration( obj, counter ){
			obj = $( '#proveedorProducto_3_' + counter );
			var models_array = $(obj).html().split( '*' );
			if( models_array.length <= 1 ){
				return false;
			}
			
			//alert( 'here : ' + $(obj).html() ); return false;
			var resp = "<div class=\"row\"><div class=\"col-2\"></div><div class=\"col-8\">";  
			resp += "<h5>Seleccione los modelos que se quedarán : <h5><br><br><div id=\"product_provider_models_container\">";
			for ( var i = 0; i < models_array.length; i++ ) {
				resp += "<div class=\"porc_10_inline\"><input type=\"checkbox\" id=\"model_tmp_" + i + "\" value=\"" + models_array[i] + "\" checked></div><div class=\"porc_80_inline\"><input type=\"text\"" + "value=\"" + models_array[i] +"\" id=\"model_value_" + i + "\"></div>" + "<br><br>";
			}
			resp += "<br><button type=\"button\" class=\"btn btn-success\" onclick=\"setCurrentModels( " + counter + " );\"><i class=\"icon-ok-circle\">Aceptar</i></button>";
			resp += "<button type=\"button\" class=\"btn btn-danger\" onclick=\"close_emergent();\"><i class=\"icon-ok-circle\">Cancelar</i></button>";
			resp += "</div></div>";

			$( '.emergent_content' ).html( '<div style="background-color:white;">' + resp + '</div>' );
			$( '.emergente' ).css( 'display', 'block' );
			$( '.emergent_content' ).focus();
		}	

		function setCurrentModels( counter ){
			var final_string = "";
			$( '#product_provider_models_container input' ).each( function ( index ){
				if( $( '#model_tmp_' + index ).prop( 'checked' ) ){
					final_string += ( final_string == '' ? '' : '*' );
					final_string += $( '#model_value_' + index ).val();
				}
			});
			//$( '#proveedorProducto_3_' + counter ).html( final_string );
			valorXY( 'proveedorProducto', 3, counter, final_string );
			$( '#proveedorProducto_3_' + counter ).html( final_string );
			$( '.emergent_content' ).html( '' );
			$( '.emergente' ).css( 'display', 'none' );
		}

		function close_emergent(){
			$( '.emergent_content' ).html( '' );
			$( '.emergente' ).css( 'display', 'none' );

		}

	</script>

	<style type="text/css">
		#bg_seccion{
			/*border : 1px solid red;*/
			/*/*max-height: 500px !important;*/
			max-height: 650px !important;
			overflow-y: auto; 
			padding-bottom: 20px;
			top: 0;
			box-shadow: 1px 1px 15px rgba( 0,0,0,.5 );
			margin: 10px;
			margin-left: 2%;
			padding-top: 10px;
			width: 94%;
		}
		#cosa{
			width: 95%;
			background-color: transparent;
			margin-left: 1%;
		}
		.margen{
			margin: 0;
			padding: 0;
			margin-top: -10px;
		}
		.name_module{
			margin:0;
		}
		.redondo{
			width: 92% !important;
			left: -1% !important;
			margin-bottom: 100px;
			position: relative; !important;
			box-shadow: 1px 1px 15px rgba( 0,0,0,.5 );
			background-color: rgba( 0, 0, 0, .1 ) !important;
		}
	</style>	
{/literal}