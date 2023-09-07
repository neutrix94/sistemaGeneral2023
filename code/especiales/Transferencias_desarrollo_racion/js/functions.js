
	var current_product_to_detail = null;
	var current_counter = null;
	var require_option_round = 0;

	function show_transfer_product_detail( product_id, counter, is_automatic = 0 ){
		current_product_to_detail = product_id;
		current_counter = counter;
	//carga el detalle del producto
		$( '.emergent_content' ).html( buildProductProvider() );
//$( '.emergent_content' ).append( '¿' + parseInt( $( '#product_provider_total_pieces' ).html().trim() ) + ' != ' + parseInt( $( '#6_' + counter ).val() ) + '?' );
			
		if( parseInt( $( '#product_provider_total_pieces' ).html().trim() ) != parseInt( $( '#6_' + counter ).val() ) 
			&& is_automatic == 0 ){
			$( '.emergent_content' ).append( '<br/><br/>' );
			$( '.emergent_content' ).append( buildProductProviderRounder( counter ) );
			require_option_round = 1;
		}
		$( '.emergent' ).css( 'display', 'block' );
	}

	function buildProductProvider(){
		var resp = '<table class="table table-bordered table-striped">';
		resp += '<thead><tr>';
			resp += '<th class="no_visible">id proveedor</th>';
			resp += '<th class="">PROVEEDOR</th>';
			resp += '<th class="no_visible">id proveedor producto</th>';
			resp += '<th class="">INVENTARIO ORIGEN</th>';
			resp += '<th class="">MODELO</th>';
			resp += '<th class="">PIEZAS POR CAJA</th>';
			resp += '<th class="">PIEZAS POR PAQUETE</th>';
			resp += '<th class="">CAJAS</th>';
			resp += '<th class="">PAQUETES</th>';
			resp += '<th class="">PIEZAS</th>';
			resp += '<th class="">TOTAL</th>';
		resp += '</tr></thead>';
		resp += '<tbody id="product_provider_detail">';
		var productsProviders = $( '#13_' + current_counter ).html().trim().split( '||' );
		var total_row = 0;
		for ( var i = 0; i < productsProviders.length; i++ ) {
			var row_sum = 0;
			var productProvider = productsProviders[i].split( '' );
			resp += '<tr>';
				resp += '<td class="no_visible">' + productProvider[0] + '</td>';
				resp += '<td class="">' + productProvider[1] + '</td>';
				resp += '<td class="no_visible" id="pp_9_' + i + '">' + productProvider[2] + '</td>';
				resp += '<td class="text-right" id="pp_8_' + i + '">' + productProvider[3] + '</td>';
				resp += '<td class="text-right" id="pp_7_' + i + '">' + productProvider[4] + '</td>';
				resp += '<td class="text-right" id="pp_6_' + i + '">' + productProvider[5] + '</td>';
				resp += '<td class="text-right" id="pp_5_' + i + '">' + productProvider[6] + '</td>';
				resp += '<td class="text-right" id="pp_1_' + i + '" onclick="edit_ceil( 1, ' + i + ' )">' + productProvider[7] + '</td>';
				resp += '<td class="text-right" id="pp_2_' + i + '" onclick="edit_ceil( 2, ' + i + ' )">' + productProvider[8] + '</td>';
				resp += '<td class="text-right" id="pp_3_' + i + '" onclick="edit_ceil( 3, ' + i + ' )">' + productProvider[9] + '</td>';
				row_sum += ( parseInt( productProvider[5] ) * parseInt( productProvider[7] ) ) + ( parseInt( productProvider[6] ) * parseInt( productProvider[8] ) ) + parseInt( productProvider[9] );
				total_row += row_sum;
				resp += '<td class="text-right" id="pp_4_' + i + '">' + row_sum + '</td>';
			resp += '</tr>';
		}
		resp += '</tbody>';
		resp += '<tfoot>';
			resp += '<tr>';
				resp += '<td colspan="10" id="product_provider_total_pieces" align="right">' + total_row + '</td>';
			resp += '</tr>';
		resp += '</tfoot>';
		resp += '</table>';

		resp += '<br/><br/>';
		resp += '<div class="row">';
			resp += '<div class="col-2"></div>';
			resp += '<div class="col-8">';
				resp += '<button type="button" class="btn btn-success form-control" onclick="changeTransferDetail( ' + current_counter + ' );">';
					resp += '<i class="icon-floppy-1">Guardar';
				resp += '</button>';
			resp += '</div>';
		resp += '</div>';
		return resp;
	}

	function buildProductProviderRounder( counter ){
		var increment = 0, decrement = 0;
		var pack_to_decrement = -1, pack_to_increment = -1;
		var piece_mant = '';
		var type = 'pack';
		var product_id = $( '#1_' + counter ).html().trim(); 

//decremento
	//verifica paquetes ( decremento )
		$( '#product_provider_detail tr' ).each( function ( index ){
			if( parseInt( $( '#pp_2_'+ index ).html().trim() ) > 0 ){/*paquetes*/
				decrement = parseInt( $( '#product_provider_total_pieces' ).html().trim() ) - parseInt( $( '#pp_5_'+ index ).html().trim() );
				pack_to_decrement = index;
			}
		});
	//verifica cajas ( decremento )
		if( pack_to_decrement == -1 ){
			$( '#product_provider_detail tr' ).each( function ( index ){
				if( parseInt( $( '#pp_1_'+ index ).html().trim() ) > 0 ){
					decrement = parseInt( $( '#product_provider_total_pieces' ).html().trim() ) - parseInt( $( '#pp_6_'+ index ).html().trim() );
					pack_to_decrement = index;
				}
			});
		}

//incremento
		//verifica paquetes ( incremento )
		$( '#product_provider_detail tr' ).each( function ( index ){
			if( ( parseInt( $( '#pp_8_'+ index ).html().trim() ) - parseInt( $( '#pp_4_'+ index ).html().trim() ) ) 
				>= parseInt( $( '#pp_5_'+ index ).html().trim() ) && pack_to_increment == -1 && parseInt( $( '#pp_5_'+ index ).html().trim() ) > 0
			){
				increment = parseInt( $( '#product_provider_total_pieces' ).html().trim() ) + parseInt( $( '#pp_5_'+ index ).html().trim() );
				pack_to_increment = index;
			}
		});
	//verifica cajas ( incremento )
		if( pack_to_increment == -1 ){
			$( '#product_provider_detail tr' ).each( function ( index ){
				if( ( parseInt( $( '#pp_8_'+ index ).html().trim() ) - parseInt( $( '#pp_4_'+ index ).html().trim() ) ) 
					>=
					parseInt( $( '#pp_6_'+ index ).html().trim() ) && pack_to_increment == -1 && parseInt( $( '#pp_6_'+ index ).html().trim() ) > 0
				){
					increment = parseInt( $( '#product_provider_total_pieces' ).html().trim() ) + parseInt( $( '#pp_6_'+ index ).html().trim() );
					pack_to_increment = index;
				}
			});
		}

	//genera la vista
		var resp = '<div class="row rounder_options">';
			resp += '<h4 class="warning_alert">La cantidad pedida requiere paquetes incompletos; seleccione una opción :</h4>';
			if( pack_to_increment >= 0 ){
				resp += '<div class="col-4 text-center">';
					resp += '<input type="radio" name="product_provider_round" id="product_provider_round_1" onclick="rebuildProductProvider(' + increment + ', ' + counter + ', ' + product_id + ');">';//set_rounder( 1, ' + pack_to_increment + ',' + counter + ', ' + increment + '  )
					resp += '<label for="product_provider_round_1">Paquete más (<b>' + increment + '</b>)';
				resp += '</div>';
			}
			if( pack_to_decrement >= 0 ){
				decrement = ( decrement < parseInt( $( '#product_provider_total_pieces' ).html().trim() ) ? parseInt( $( '#product_provider_total_pieces' ).html().trim() ) : decrement );
				resp += '<div class="col-4 text-center">';
					resp += '<input type="radio" name="product_provider_round" id="product_provider_round_2" onclick="rebuildProductProvider(' + decrement + ', ' + counter + ', ' + product_id + ');">';//set_rounder( -1, ' + pack_to_increment + ',' + counter + ', ' + decrement + ' )
					resp += '<label for="product_provider_round_2">Paquete menos(<b>' + decrement + '</b>)';
				resp += '</div>';
				//pack_to_increment = $( '#6_'+ counter ).val();
			}/*else{
				alert( 'decremetn : ' + pack_to_decrement );
			}*/

			resp += '<div class="col-4 text-center">';
				resp += '<input type="radio" name="product_provider_round" id="product_provider_round_3" onclick="rebuildProductProvider(' + $( '#6_'+ counter ).val() + ', ' + counter + ', ' + product_id + ', 1);">';//set_rounder( 0, ' + pack_to_increment + ',' + counter + ', ' + $( '#6_'+ counter ).val() + ' )
				resp += '<label for="product_provider_round_3">Mantener(<b>' + $( '#6_'+ counter ).val() + '</b>)</label>';
			resp += '</div>';
		resp += '</div>';
		return resp;
	}

	function rebuildProductProvider( quantity, counter, product_id, permission = 0 ){
		var url = 'ajax/productProvider.php?fl=calculateProductProvider';
		url += '&product_id=' + product_id;
		url += '&quantity=' + quantity;
		url += '&counter=' + counter;
		url += ( permission == 1 ? '&permission=1' : '' );
	//alert( url );
		var response = ajaxR( url );
		var aux_split = response.split( '||' );
	
		$( '#13_' + counter ).html( aux_split[1] );
		buildProductProvider();
		show_transfer_product_detail( product_id, counter, 1 );
		//$( '#show_transfer_detail_' + counter ).click();
		//alert( response );
	}

	function set_rounder( type, counter_p_p = null, counter, total ){
		if( type == 0 ){
			//alert( counter_p_p );

			if( parseInt( $( '#product_provider_total_pieces' ).html().trim() ) > total ){
				$( '#pp_3_' + counter_p_p ).html( 0 );

				setTimeout( function(){},1000 );

				set_rounder( -1, counter_p_p, counter, total );
			}
			var diff = parseInt( $( '#6_' + counter ).val() ) - parseInt( $( '#product_provider_total_pieces' ).html() );
			$( '#pp_3_' + counter_p_p ).click();
			$( '#p_p_tmp' ).val( ( parseInt(  $( '#p_p_tmp' ).val() ) + diff ) );
			$( '.emergent_content' ).focus();
			require_option_round = 0;
		}else if( type == 1 ){
			if( $( '#pp_3_' + counter_p_p ).html().trim() > 0 ){
				$( '#pp_3_' + counter_p_p ).html( 0 );
			}
			setTimeout( function(){},1000 );
			if( parseInt( $( '#product_provider_total_pieces' ).html().trim() ) >= total ){
				return false;
			}
			var aux = Math.ceil( total / parseInt( $( '#pp_5_' + counter_p_p ).html() ) );
			
			$( '#pp_2_' + counter_p_p ).click();
			$( '#p_p_tmp' ).val( ( parseInt( ( $( '#p_p_tmp' ).val() ) ) + 1 ) );
			$( '.emergent_content' ).focus();
		}else if( type == -1 ){
			if( $( '#pp_3_' + counter_p_p ).html().trim() > 0 ){
				$( '#pp_3_' + counter_p_p ).html( 0 );
			}
			setTimeout( function(){},1000 );
			if( parseInt( $( '#product_provider_total_pieces' ).html().trim() ) <= total ){
				return false;
			}
			var aux = Math.floor( total / parseInt( $( '#pp_5_' + counter_p_p ).html() ) );
			$( '#pp_2_' + counter_p_p ).click();
			$( '#p_p_tmp' ).val( aux );
			$( '.emergent_content' ).focus();
		}

	}

	function close_emergent( obj_to_clean = null, obj_to_focus = null ){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		if( obj_to_clean != null ){
			$( obj_to_clean ).val( '' );
		}
		if( obj_to_focus != null ){
			$( obj_to_focus ).focus();
		}
	}
	
	var tmpCeil = '<input type="number" id="p_p_tmp" class="form-control text-right" onblur="desedit_ceil();">';
	var tmpVal, current_counter1 = null, current_counter2 = null;
	function edit_ceil( counter1, counter2 ){
		if( current_counter1 != null ){
			return false;	
		}
		current_counter1 = counter1;
		current_counter2 = counter2;
		tmpVal = $( '#pp_' + counter1 + '_' + counter2 ).html().trim();
		$( '#pp_' + counter1 + '_' + counter2 ).html( tmpCeil );
		$( '#p_p_tmp' ).val( tmpVal );
		$( '#p_p_tmp' ).focus();
		$( '#p_p_tmp' ).select();
	}

	function desedit_ceil(){
		$( '#pp_' + current_counter1 + '_' + current_counter2 ).html( ( $( '#p_p_tmp' ).val() == '' || $( '#p_p_tmp' ).val() < 0 ? 0 : $( '#p_p_tmp' ).val() ) );
		recalculateDetailTotal( current_counter1 );
		current_counter1 = null;
		current_counter2 = null;	
	}

	function changeTransferDetail( counter = null ){
		if( require_option_round == 1 && $( '#product_provider_round_1' ).prop( 'checked' ) == false
		&& $( '#product_provider_round_2' ).prop( 'checked' ) == false && $( '#product_provider_round_3' ).prop( 'checked' ) == false ){
			alert( "Primero seleccione una opcion de la cantidad pedida!" );
			$( '.rounder_options' ).css( 'background-color', 'silver' );
			return false;
		}
	//cambia la cantidad global del producto
		$( '#6_' + current_counter ).val( $( '#product_provider_total_pieces' ).html().trim() );
		$( '#13_' + current_counter ).html( getTransferProductProvider() );
		close_emergent();
		require_option_round = 0;
	}

	function getTransferProductProvider(){
		var resp = '';
		$( '#product_provider_detail tr' ).each( function ( index ){
			if( index > 0 ){
				resp += '||';
			}
			$(this).children("td").each(function (index2) {
				if( index2 > 0 ){/*&& index2 != 7*/
					resp += '';
				}/*else if( index2 == 7 ){
					resp += '';
				}*/
				resp += $( this ).html().trim();
			});
		});
		//alert( resp );
		return resp;
	}

	function recalculateDetailTotal( counter ){
		var resp = 0;
		var total_per_row = 0;
		var pieces_per_box = 0;
		var pieces_per_pack = 0;
		$( '#product_provider_detail tr' ).each( function ( index ){
			//pzas x caja [5]
			$( this ).children( 'td' ).each( function( index2 ){
				if( index2 == 5 ){
					pieces_per_box = parseInt( $( this ).html().trim() );
				}else if( index2 == 6 ){
					pieces_per_pack = parseInt( $( this ).html().trim() );
				}else if( index2 == 7 ){
					total_per_row += parseInt( $( this ).html().trim() ) * pieces_per_box;
				}else if( index2 == 8 ){
					total_per_row += parseInt( $( this ).html().trim() ) * pieces_per_pack;
				}else if( index2 == 9 ){
					total_per_row += parseInt( $( this ).html().trim() );
				}else if( index2 == 10 ){
					$( this ).html( total_per_row )	
				}
			});
			resp += total_per_row;
			total_per_row = 0;//resetea el total por renglon
		});
		$( '#product_provider_total_pieces' ).html( resp );
		$( '#6_' + counter ).val( resp );
	}

//obtener detalle de la transferencia
	function getTransferRowDetail( counter ){
		if( $( '#6_' + counter ).val().trim() <= 0 ){
			alert( "La cantidad de piezas debe ser mayor a cero!" );
			$( '#6_' + counter ).val( '' );
			$( '#6_' + counter ).select();
			return false;
		}
		//getTransferRowDetail( 2 );
		var url = "ajax/productProvider.php?fl=calculateProductProvider";
		url += "&product_id=" + $( '#1_' + counter ).html().trim();
		url += "&quantity=" + $( '#6_' + counter ).val().trim();
		url += "&counter=" + counter ;
	
		var response = ajaxR( url );
		var aux_split = response.split( '||' );
	
		$( '#13_' + counter ).html( aux_split[1] );
		$( '#show_transfer_detail_' + counter ).click();
	}

	function refresh_request(){
		$('.emergent_content').html( '<h3>Cargando Datos ...</h3>' );
		accionar( null );
	}

	function clean_seeker(){
		$( '#buscador' ).val( '' );
		$( '#agrega' ).val( '' );
	}

	//llamadas asincronas
	function ajaxR(url){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}


