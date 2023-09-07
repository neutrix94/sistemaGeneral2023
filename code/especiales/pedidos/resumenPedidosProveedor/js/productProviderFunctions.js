var global_product_provider_to_meassures = null;
var global_counter_row_product_provider = null;

/*var global_meassures_home_path = '';
var global_meassures_include_jquery = '';
//var global_meassures_frames_path = '';
var global_meassures_path_camera_plugin = '';*/
//var global_meassures_path_files = '';
//var global_meassures_product_provider_id = ''
	function show_product_providers( product_id, count ){
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/getProductProvider.php',
			cache : false,
			data : { p_k : product_id , 
				fl : 'getProviders',
				c : count,
				reception_detail_id : ( global_product_provider_path == '' ? $( '#0_' + count ).html().trim() : -1 )
			},
			success : function ( dat ){
				$( '.emergent_content' ).html( dat );
				$( '.emergente' ).css( 'display', 'block' );
			}
		});
	}
	global_product_provider_meassures_path = '../';

/**/
	function add_row( type, table, product_id ){
		counter = $( table + ' tr' ).length;
	//envio de datos por ajax
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/getProductProvider.php',
			cache : false,
			data : { current_counter : (counter - 1), fl : 'getRow', p_k : product_id },
			success : function ( dat ){
				$( table + ' tbody' ).append( dat );
			}
		});
	}

	function reassign_meassure( id ){
		var product_provider_id = $( '#temporal_meassures_reassign_select' ).val();
		if( product_provider_id == 0 ){
			alert( 'Es necesario elegir un proveedor producto!' );
			$( '#temporal_meassures_reassign_select' ).focus();
			return false;
		}
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/getProductProvider.php',
			cache : false,
			data : { fl : 'reassignMeassure', p_k : id, product_provider : product_provider_id  },
			success : function ( dat ){
				alert( dat );
				close_emergent_3();
				$( '#pp_18_' + global_counter_row_product_provider ).html('');
				$( '#pp_19_' + global_counter_row_product_provider ).children( 'button' ).each( function( index ){
					$( this ).click();
				});
			}
		});
	}

	function show_measures_reassign_form( counter, id ){
		global_counter_row_product_provider = counter;
		var resp = '<div class="row">';
			resp += '<div class="col-2"></div>';
			resp += '<div class="col-8">';
	//medidas
		if( $( '#pp_18_' + global_counter_row_product_provider ).html().trim() != '' ){
				resp += '<p align="center">Para poder eliminar este registro es necesario asignar las medidas a un proveedor producto : </p>';
				resp += '<select id="temporal_meassures_reassign_select" class="form-control">';
					resp += '<option value="0">-- seleccionar --</option>';
			$( '#product_provider_list tr' ).each( function( index ){
				if( ( index + 1 ) != counter && $( '#pp_0_' + ( index + 1 ) ).html().trim() != '' ){
					resp += '<option value="' + $( '#pp_0_' + ( index + 1 ) ).html().trim() + '">' + $( '#pp_3_' + ( index + 1 ) ).html().trim() + '</option>';
				}
			});
				resp += '</select><br>';
		}
	//ubicación
		if( $( '#pp_19_' + global_counter_row_product_provider ).html().trim() != '' ){
				resp += '<p align="center">Para poder eliminar este registro es necesario asignar la ubicación a un proveedor producto : </p>';
				resp += '<select id="temporal_meassures_reassign_select" class="form-control">';
					resp += '<option value="0">-- seleccionar --</option>';
			$( '#product_provider_list tr' ).each( function( index ){
				if( ( index + 1 ) != counter && $( '#pp_0_' + ( index + 1 ) ).html().trim() != '' ){
					resp += '<option value="' + $( '#pp_0_' + ( index + 1 ) ).html().trim() + '">' + $( '#pp_3_' + ( index + 1 ) ).html().trim() + '</option>';
				}
			});
				resp += '</select><br>';
		}
				resp += '<button type="button" class="btn btn-success form-control" onclick="reassign_meassure(' + id + ')">Cambiar</button><br>';
				resp += '<button type="button" class="btn btn-danger form-control" onclick="close_emergent_3();">Cancelar</button>';
			resp += '</div>';
		resp += '</div>';
		//alert( resp );
		$( '.emergent_content_3' ).html( resp );
		$( '.emergent_3' ).css( 'display', 'block' );	
	}
	function remove_product_provider( counter ){
		//alert( 'here1' );
		var reassign = false;
		var id = 0;
	//verifica que no haya medidas pendientes de asignar
	//$( '#pp_15_' + counter ).children( 'button' ).each( function( index ){
		if( $( '#pp_18_' + counter ).html().trim() != '' ||  $( '#pp_20_' + counter ).html().trim() != '' ){
			id = $( '#pp_18_' + counter ).html().trim();
			//alert( '18 :' + $( '#pp_18_' + counter ).html().trim() + '-20:' +  $( '#pp_20_' + counter ).html().trim() );
			//alert( "El registro tiene medidas / ubicaciones que dependen de el, para eliminar es necesario asignarlo a un proveedor - producto!" );
			reassign = true;
			//return false;
		}
		//alert( 'here2' );
	//});
	if( reassign ){

	//	alert( 'here2' );
		show_measures_reassign_form( counter, id );
		return false;
	}
	//omite registro
		if( $( '#pp_13_' + counter ).html().trim() != '' ){
			$.ajax({
				type : 'post',
				url : global_product_provider_path + 'ajax/getProductProvider.php',
					cache : false,
					data : { fl : 'omitProductProvider', 
					p_k : $( '#pp_13_' + counter ).html().trim()
				},
				success : function ( dat ) {
					$( '#product_provider_' + counter ).remove();
					alert( dat );
				}

			});
		}else{
			$( '#product_provider_' + counter ).remove();
		}

	}
/*guardar proveedores - producto */
	function save_product_providers( type, table, product_id, count ){
		var product_providers = '';
		var selected_option = '';
	//recorre el grid de datos
		counter = $( '#product_provider_list tr' ).length;
		var count_tmp = 1;
		//alert( 'length : ' + $( '#product_provider_list tr' ).length );
	//registros 
		$( '#product_provider_list tr' ).each(function (index) {
		//verifica el id
			count_tmp = parseInt( $( this ).attr( 'id' ).split( 'product_provider_' ).join( '' ) );//implementacion Oscar para que deje guardar cuando se elimina la primera fila
			//alert( count_tmp );
			if( $( '#pp_-1_' + ( count_tmp ) ).prop( 'checked' ) ){
				selected_option = $( '#pp_3_' + ( count_tmp ) ).html().trim(); //$( '#pp_0_' + ( count_tmp ) ).html().trim();
			}
			if( index > 0 ){
				product_providers += '|';
			}
			$(this).children("td").each(function (index2) {
				if( index2 > 0 ){
					if( index2 <= 17 && ( index2 != 3 && index2 != 15 ) ){
						product_providers += ( index2 > 1 ? '~' : '' ) + $(this).html().trim();
						
					}else if( index2 == 3 || index2 == 15 ){
						product_providers += '~' + $( this ).attr( 'value' );
					}else if( index2 == 18 ){//implementacion Oscar 2023
						//alert( 'here : ' + $(this).html().trim() );
						product_providers += '~' + $(this).html().trim();
					}//fin de cambio Oscar 2023
				}
			});
			count_tmp ++;
		});
		if( selected_option == '' ){
			alert( "Debes seleccionar un proveedor - producto para el combo...!" );
			return false;
		}
	//	alert( selected_option );
		//alert( product_providers ); //return false;
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/getProductProvider.php',
			cache : false,
			data : { fl : 'saveProductProviders', 
					pp : product_providers, 
					p_k : product_id,
					option : selected_option
			},
			success : function ( dat ){
				alert( dat );
				close_emergent();
				if( global_product_provider_path == 'recepcionPedidos/' ){
					carga_proveedor_prod(count, product_id, selected_option );
					setTimeout(function (){ recalcular( count, 2 )} , '500');
				}else if ( global_product_provider_path == '' ) {
					reload_product_provider_combo( count, selected_option );
				}	
			}
		});
	}

	function reload_product_provider_combo( counter, selected_option ){
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/getProductProvider.php',
			cache : false,
			data : { fl : 'comboProductProvider', 
					product_id : $( '#1_' + counter ).html().trim(),
					provider_id : $( '#id_prov' ).val(), 
					option : selected_option 
			},
			success : function ( dat ){
				$( '#13_' + counter ).empty();
				$( '#13_' + counter ).append( dat );
			//recalcula precio x piezas
				setTimeout( function (){ changeProductProvider( '#13_' + counter, '', counter ); }, 100);
			}
		});
	}

	function reload_product_providers( product_id, counter ){
		$.ajax({
			type : 'post',
			url : global_product_provider_path + 'ajax/db.php',
			cache : false,
			data : { fl : 'getProductProviders', 
					pp : $( '#id_prov' ).val(), 
					p_k : product_id,
					c : counter
			},
			success : function ( dat ){
				$( '#12_' + counter ).html( dat );
			}
		});
	}

	function changeProvider( obj, counter ){
		$( "#p_p_2_1_" + counter ).attr( "value", $( obj ).val() );
	}

	function change_product_provider_price( counter, type ){
		if( type == 2 ){
//			parseFloat(Math.round(278.6 * 100) / 100).toFixed(2)
			var total_price = parseFloat( $( '#pp_4_' + counter ).html().trim() * $( "#pp_16_" + counter ).html().trim() ).toFixed( 2 );//.toFixed( 2 );
		//	alert( total_price );
			$( '#pp_17_' + counter ).html( total_price );
		}else if( type == 3 ){
			var total_price = parseFloat( $( "#pp_17_" + counter ).html().trim() / $( '#pp_4_' + counter ).html().trim() ).toFixed( 2 );//.toFixed( 2 );
		//	alert( total_price );
			$( '#pp_16_' + counter ).html( total_price );
		}
	}

	function validateNoRepeatBarcode( obj ){
		var value = $( obj ).val();
		var id_array =  $( obj ).parent().attr( 'id' ).split( '_' );
		var flag = id_array[1];
		var product_id = $( '#pp_1_' + id_array[2] ).html().trim();
		var is_the_same_provider = 0;
		var array_id = $( obj ).parent().attr( 'id' ).split( '_' );
		
		//alert( array_id );
		if( value != '' ){
			var exists = false;
			$( '#product_provider_list tr' ).each(function( index ){
				$( this ).children( 'td' ).each( function( index2 ){
					if( $( this ).html().trim() == value && $( this ).html().trim() != '' && ( index + 1 ) != array_id[2] ){
						//alert( $( '#pp_2_' + index ).val() + '== ' + $( '#pp_2_' + array_id[2] ).val() );
						if( $( '#pp_2_' + ( index + 1 ) ).val() == $( '#pp_2_' + array_id[2] ).val() 
							&& ( index2 == 7 || index2 == 8 ||index2 == 9 ) && ( index + 1 ) != array_id[2] ){
							//alert( 'here1' );
							exists = true;
							is_the_same_provider = 1;
							return false;
						}else{
							//alert( $( '#pp_2_' + ( index + 1 ) ).val() +'=='+ $( '#pp_2_' + array_id[2] ).val() + ' . ' + index2 );
							$( obj ).val( '' );
							exists = true;
							alert( "El código de barras ya existe para este producto!" );
							setTimeout( function(){
								$( '#' + id_array[0] + '_' + id_array[1] + '_' + id_array[2] ).click();
							}, 300 );
							return false;
						}
					}
				});
			});
			if( exists == true && is_the_same_provider == 0 ){
				return false;
			}else if( is_the_same_provider == 1 ){
			//	alert('is_the_same_provider');
				show_the_same_barcoder_and_provider_emergent( value, $( '#pp_2_' + array_id[2] ).val(), $( obj ).parent().attr( 'id' ) );
				return false;
			}else{
				$.ajax({
					type : 'post',
					url : global_product_provider_path + 'ajax/getProductProvider.php',
					cache : false,
					data : { fl : 'validateNoRepeatBarcode', 
							p_k : product_id,
							c : id_array[2],
							barcode : value,
							key : product_id,
							type : flag
					},
					success : function ( dat ){
						if( dat != 'ok'){
							$( '#' + id_array[0] + '_' + id_array[1] + '_' + id_array[2] ).html( '' );
							alert( dat );
							setTimeout( function(){
								$( '#' + id_array[0] + '_' + id_array[1] + '_' + id_array[2] ).click();
							}, 300 );
						}
						//$( '#12_' + counter ).html( dat );
					}
				});
			}
		}
		//alert();
	}

	function show_the_same_barcoder_and_provider_emergent( barcode, provider_id, obj_id ){

			var num= $( '#product_provider_list tr' ).length;//numero de filas en el grid
			//var barcode = '';
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

		for ( var i = 1; i <= num; i++ ){
			//if( i != pos ){
				if( barcode == $( '#pp_6_' + i ).html().trim()
					&& provider_id == $( '#pp_2_' + i ).val()
				){
					information_table += '<tr><td>' + $( '#pp_2_' + i + ' option:selected' ).text() + '</td>';
					information_table += '<td>' + $( '#pp_6_' + i ).html() + '</td></tr>';	
				}
				if( barcode == $( '#pp_7_' + i ).html().trim()
					&& provider_id == $( '#pp_2_' + i ).val()
				){
					information_table += '<tr><td>' + $( '#pp_2_' + i + ' option:selected' ).text() + '</td>';
					information_table += '<td>' + $( '#pp_7_' + i ).html() + '</td></tr>';
				}
				if( barcode == $( '#pp_8_' + i ).html().trim()
					&& provider_id == $( '#pp_2_' + i ).val()
				){
					information_table += '<tr><td>' + $( '#pp_2_' + i + ' option:selected' ).text() + '</td>';
					information_table += '<td>' + $( '#pp_8_' + i ).html() + '</td></tr>';
				}
    		//}
    	}
			information_table += "</table>";

			var resp = "<h3>El código de barras que escribió ya existe para el mismo proveedor, ¿Desea asignarle el mismo a este nuevo proveedor producto?</h3>";

			resp += '<br>' + information_table + '<br>';

			resp += "<div class=\"row\">";
				resp += "<div class=\"col-2\"></div>";
				resp += "<div class=\"col-3\">";
					resp += "<button type=\"button\" onclick=\"setProductProviderPieceBarcode( 1, '" + obj_id + "', '" + barcode + "')\" class=\"btn btn-success form-control\">";
						resp += "<i class=\"\">Aceptar</i>";
					resp += "</button>";
				resp += "</div>";
				resp += "<div class=\"col-1\"></div>";
				resp += "<div class=\"col-3\">";
					resp += "<button type=\"button\" onclick=\"setProductProviderPieceBarcode( 0, '" + obj_id + "')\" class=\"btn btn-danger form-control\">";
						resp += "<i class=\"\">Cancelar</i>";
					resp += "</button>";
				resp += "</div>";
			resp += "<div>";
			$( '.emergent_content_2' ).html( resp );
			$( '.emergent_2' ).css( 'display', 'block' );
			$( '.emergent_content_2' ).focus();
		}
	function setProductProviderPieceBarcode( action, obj_id, barcode = '' ){
		if( action == 1 ){//
			//valorXY( grid , cell, pos, barcode );
			$( '#' + obj_id ).html( barcode );
			close_emergent_2();
		}else{
			$( '#' + obj_id ).html( '' );
			close_emergent_2();
		}
	}

	function modelsDepuration( obj, counter ){
		var models_array = $(obj).val().split( '*' );
		if( models_array.length <= 1 ){
			return false;
		}
		var resp = "<div class=\"row\"><div class=\"col-2\"></div><div class=\"col-8\">";  
		resp += "<h5>Seleccione los modelos que se quedarán : <h5><br><div id=\"product_provider_models_container\" class=\"row\">";
		for ( var i = 0; i < models_array.length; i++ ) {
				resp += "<div class=\"col-2\"><input type=\"checkbox\" id=\"model_tmp_" + i + "\" value=\"" + 
				models_array[i] + "\" checked></div><div class=\"col-8\"><input type=\"text\"" + "value=\"" + 
				models_array[i] +"\" id=\"model_value_" + i + "\"></div>" +
				"<div class=\"col-2\"></div>";//+ "<br><br>"
		}
		resp += "<br><button type=\"button\" class=\"btn btn-success\" onclick=\"setCurrentModels( " + counter + " );\"><i class=\"icon-ok-circle\">Aceptar</i></button>";
		resp += "</div></div>";
		$( '.emergent_content_2' ).html( resp );
		$( '.emergent_2' ).css( 'display', 'block' );
		$( '.emergent_content_2' ).focus();
	}	

	function setCurrentModels( counter ){
		var final_string = "";
		$( '#product_provider_models_container input' ).each( function ( index ){
			if( $( '#model_tmp_' + index ).prop( 'checked' ) ){
				final_string += ( final_string == '' ? '' : '*' );
				final_string += $( '#model_value_' + index ).val();
			}
		});
		$( '#pp_3_' + counter ).html( final_string );
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );
	}

	function just_piece( obj, counter ){
		if( $( obj ).prop( 'checked' ) ){
			/*if( confirm( "El tratamiento por pieza desactivará el código por paquete, caja\nDesea continuar?" ) ){
				$( '#pp_4_' + counter ).html( '0' );
				$( '#pp_5_' + counter ).html( '0' );
				$( '#pp_9_' + counter ).html( '' );
				$( '#pp_11_' + counter ).html( '' );
				$( '#pp_14_' + counter ).attr( 'value', '1' );*/
			//}else{
				$( '#pp_14_' + counter ).attr( 'value', '1' );
			//}
		}else{
			$( '#pp_14_' + counter ).attr( 'value', '0' );
		}
	}

	function show_measures( product_provider_id = null, reception_detail_id = null ){
		global_product_provider_to_meassures = product_provider_id;
		var url = global_product_provider_path + "ajax/getProductProvider.php?fl=getProductProviderMeasures";
		url += "&product_provider_id=" + product_provider_id;
		url += "&reception_detail_id=" + reception_detail_id;
		url += '&home_path=' + global_meassures_home_path;
		/*url += '&include_jquery=' + global_meassures_include_jquery;
		url += '&path_camera_plugin=' + global_meassures_path_camera_plugin;
		/*url += "&imgs_path=" + global_meassures_img_path;*/
		var response = ajaxR( url );
		$( '.emergent_content_2' ).html( response );
		$( '.emergent_content_2' ).css( 'top', '20%' );
		$( '.emergent_2' ).css( 'display', 'block' );
	}

var global_image_is_expanded = 0;
	function expand_img( obj ){
		var resp = "<div id=\"measures_ids\" style=\"display : inline; width : 10%;\">";
		var resp1  = "<div id=\"measures_images_1\" class=\"sortable\" style=\"display : inline; width : 30%;\">";//"<ul id=\"sortable\">";
		var resp2 = "<div id=\"measures_images_2\" class=\"sortable\" style=\"display : inline; width : 30%;\">";
		var resp3 = "<div id=\"measures_images_3\" class=\"sortable\" style=\"display : inline; width : 30%;\">";
		var path_aux = global_meassures_img_path.replace( 'files', 'img/frames' );
		var verification_path = global_meassures_img_path.replace( 'files/', '' );
	//recorre las imágenes
		$( '#meassures_tbody tr' ).each( function ( index ){
			resp += "<input type=\"hidden\" value=\"" + $('#measures_1_' + index).html().trim() + "\" >";
			$( '#measures_11_' + index ).children( 'img' ).each( function(){
				if( $( this ).attr( 'src' ) != (verification_path + 'img/frames/no_image.png') ){
				//resp1 += "<div class=\"col-1\"></div>";
					resp1 += "<div class=\"group_card\"><img src=\"" + $( this ).attr( 'src' ) + "\" width=\"100%\">";
						resp1 += "<button class=\"btn btn-danger form-control\" onclick=\"delete_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-trash\">Eliminar imágen</i></button>";
					resp1 += "</div>";
				}else{
					resp1 += "<div class=\"group_card text-center\"><img src=\"" + path_aux + "no_image.png\" width=\"65%\">";
						resp1 += "<button class=\"btn btn-info form-control\" onclick=\"change_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-up-big\">Subir Imágen</i></button>";
						resp1 += "<input type=\"file\" style=\"display:none;\">";
					resp1 += "</div>";
				}
			}); 
			$( '#measures_12_' + index ).children( 'img' ).each( function(){
				if( $( this ).attr( 'src' ) != (verification_path + 'img/frames/no_image.png') ){
					//resp2 += "<div class=\"col-1\"></div>";
					resp2 += "<div class=\"group_card\"><img src=\"" + $( this ).attr( 'src' ) + "\" width=\"100%\">";
						resp2 += "<button class=\"btn btn-danger form-control\" onclick=\"delete_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-trash\">Eliminar imágen</i></button>";
					resp2 += "</div>";
				}else{
					resp2 += "<div class=\"group_card text-center\"><img src=\"" + path_aux + "no_image.png\" width=\"65%\">";
						resp2 += "<button class=\"btn btn-info form-control\" onclick=\"change_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-up-big\">Subir Imágen</i></button>";
						resp2 += "<input type=\"file\" style=\"display:none;\">";
					resp2 += "</div>";
				}
			}); 
			$( '#measures_13_' + index ).children( 'img' ).each( function(){
				if( $( this ).attr( 'src' ) != (verification_path + 'img/frames/no_image.png') ){
					//resp3 += "<div class=\"col-1\"></div>";
					resp3 += "<div class=\"group_card\"><img src=\"" + $( this ).attr( 'src' ) + "\" width=\"100%\">";
						resp3 += "<button class=\"btn btn-danger form-control\" onclick=\"delete_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-trash\">Eliminar imágen</i></button>";
					resp3 += "</div>";
				}else{
					resp3 += "<div class=\"group_card text-center\"><img src=\"" + path_aux + "no_image.png\" width=\"65%\">";
						resp3 += "<button class=\"btn btn-info form-control\" onclick=\"change_meassure_img( this, '" + $( this ).attr( 'src' ) + "' );\"><i class=\"icon-up-big\">Subir Imágen</i></button>";
						resp3 += "<input type=\"file\" style=\"display:none;\">";
					resp3 += "</div>";
				}
			}); 
			//resp += "<div class=\"col-12\"><hr></div>";
		});
		resp += "</div>";
		resp1 += "</div>";
		resp2 += "</div>";
		resp3 += "</div>";

		var buttons = "<div class=\"row\">";
		buttons += "<div class=\"col-2\"></div>";
		buttons += "<div class=\"col-4\"><button type=\"button\" class=\"btn btn-success\" onclick=\"saveImagesChanges();\">";
			buttons += "<i class=\"icon-ok-circle\">Guardar</i>";
		buttons += "</button></div>";
		buttons += "<div class=\"col-4\"><button type=\"button\" class=\"btn btn-danger\" onclick=\"close_emergent_3();\">";
			buttons += "<i class=\"icon-ok-circle\">Cancelar</i>";
		buttons += "</button></div>";
		buttons += "</div>";
		//alert( resp );
		$( '.emergent_content_3' ).html( '<div class="row">' + resp + resp1 + resp2 + resp3 + '</div>' + buttons );
		$( '.emergent_3' ).css( 'display', 'block' );
	//para poder mover las imágenes
	    setTimeout( function(){
		    $( ".sortable" ).sortable();
		    $( ".sortable" ).disableSelection();
		}, 300 );
	}
	function change_meassure_img( obj ){
		$( obj ).next().click();
		//alert();
	}
	function delete_meassure_img( obj, name ){
		var url = global_product_provider_path + "ajax/getProductProvider.php?fl=deleteImg";
		name = name.replace( global_meassures_img_path, '' );
		url += "&img_name=" + name;
		alert( url );// return false;
		var response = ajaxR( url ).split( '|' );
		if( response[0] == 'ok' ){
			alert( response[1] );
			$( obj ).parent().remove();
		}else{
			alert( response[0] );
		}
	}

	function saveImagesChanges(){
		var is_tmp = new Array(), 
			meassures_ids = new Array(), 
			measures_images_1 = new Array(),
			measures_images_2 = new Array(),
			measures_images_3 = new Array();
		$( '#measures_ids' ).children( 'input' ).each( function( index ){
			measures_ids[measures_ids.length] = ( $( this ).val() );
		});
		$( '#measures_images_1' ).children( 'div' ).each( function( index ){
			$( this ).children( 'img' ).each( function( index2) {
				measures_images_1[measures_images_1.length] = ( $( this ).attr( 'src' ) ); 
			});
		});
		$( '#measures_images_2' ).children( 'div' ).each( function( index ){
			$( this ).children( 'img' ).each( function( index2) {
				measures_images_2[measures_images_2.length] = ( $( this ).attr( 'src' ) ); 
			});
		});
		$( '#measures_images_3' ).children( 'div' ).each( function( index ){
			$( this ).children( 'img' ).each( function( index2) {
				measures_images_3[measures_images_3.length] = ( $( this ).attr( 'src' ) );
			});
		});
/*		console.log( is_tmp,
meassures_ids,
measures_images_1,
measures_images_2,
measures_images_3 );*/
	}

	function show_product_provider_meassures_form( product_provider_id ){
		var url = global_product_provider_path + 'ajax/getProductProvider.php?fl=showProductProviderMeassuresForm';
		url += '&product_provider_id=' + product_provider_id;
		url += '&home_path=' + global_meassures_home_path;
		url += '&include_jquery=' + global_meassures_include_jquery;
		url += '&path_camera_plugin=' + global_meassures_path_camera_plugin;
		url += '&save_img_path=' + global_save_meassure_img_path;
		url += '&type=1';
		//alert( url );return false;
		var response = ajaxR( url );
		$( '.emergent_content_3' ).html( response );
		$( '.emergent_3' ).css( 'display', 'block' );
		//setTimeout( function(){ $( '#btn_close_meassures_form' ).attr( 'onclick', 'close_emergent_3();' ); }, 300 );
		//$( '#btn_close_meassures_form' ).attr( 'onclick', 'close_emergent_3()' );
	}

	function saveProductProviderMeassures(){
		var data = "";
		$( '#meassures_tbody tr' ).each( function ( index ){
				data += ( data == "" ? "" : "|~|");
			$( this ).children( 'td' ).each( function( index2 ) {
				if( (index2 != 8 && index2 != 9 && index2 != 10 && index2 != 11 ) && index2 <= 16 ){
					data += ( index2 == 0 ? "" : "~");
					data += $( this ).html().trim();
				}
				if( index2 == 8 ){
					$( this ).children( 'select' ).each( function( index3 ){
						data += "~" + $( this ).val();
					});
				}
				if( index2 == 9 || index2 == 10 || index2 == 11 ){
				//	alert( 'here' );
					$( this ).children( 'img' ).each( function( index3 ){
						var aux = $( this ).attr( 'src' ).replace( global_meassures_img_path + 'packs_img_tmp/' , '' );
						aux = aux.replace( global_meassures_img_path + 'packs_img/' , '' );
						data += "~" + aux;
					});
				}	
			});
		});
		if( global_product_provider_to_meassures == null ){
			alert( "Es necesario validar el registro de proveedor producto antes de guardar las medidas!" );
			return false;
		}
	//	alert( global_product_provider_to_meassures );
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : global_product_provider_path + "ajax/getProductProvider.php",
			cache : false,
			data : { fl : 'saveProductProviderMeassures' , 
					meassures : data, 
					img_path : global_meassures_img_path, 
					product_provider_id : global_product_provider_to_meassures },
			success : function( dat ) {
				alert( dat );
			}
		});
	}

	function redirect_to_barcode_printer( counter ){
		if( $( '#13_' + counter ).val() == 0 ){
			alert( "Es necesario poner primero el proveedor producto " );
			$( '#13_' + counter ).focus();
			return false;
		}
		var url = "../../Etiquetas/barcodes/index.php?";
		url += "product_provider=" + $( '#13_' + counter ).val();
		url += "&boxes_quantity=" + $( '#5_' + counter ).html().trim();
		url += "&pieces_quantity=" + $( '#6_' + counter ).html().trim();
		var win = window.open( url, '_blank');
	}
