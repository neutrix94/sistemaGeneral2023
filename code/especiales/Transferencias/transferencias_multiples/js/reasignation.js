	function detect_assign_change( type, obj, counter ){
		/* Deshabilitado por Oscar 2023
		if( $( '#asigned_number_' + counter ).html().trim() != 0 ){ 
			if( $( obj ).prop( 'checked' ) == false ){
				alert( "No se puede deshabilitar, primero libera las asignaciones del usuario para continuar" );
				$( obj ).prop( 'checked', true );
				return false;
			}
		}*/
		if( type == 0 ){
			assign_has_changed = 1;
			$( obj ).parent('td').parent('tr').css( 'background-color', ( $( obj ).prop( 'checked' ) ?  '' : 'rgba(225, 0, 0, .3)' ) );			
		//IMPLEMENTACION OSCAR 2023 PARA EMERGENTE DE ASIGNACION DE PARTIDAS
			getReasignationDetailView( counter );
		}
	}

	function getReasignationDetailView( counter ){
	//alert( counter );
		var user_name = $( '#asigned_user_name_' + counter ).html().trim();
		var transfer_id = $( '#transfer_folio_input' ).val().trim().split( ' ' );
		transfer_id = transfer_id[1];
		var content = `<div class="row">
			<div class="accordion" id="accordionExample">
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading_1_0">
						<button
							class="accordion-button collapsed" 
							type="button" 
							data-bs-toggle="collapse" 
							data-bs-target="#collapse_1_0" 
							aria-expanded="true" 
							aria-controls="collapse_1_0" 
							onclick="carga_filtros(38,'busc_prod');" 
							id="herramienta_1_0"
						>
							${user_name} ( <span id="to_asign_counter" class="text-success"></span> )
						</button>
					</h2>
					<div id="collapse_1_0" class="accordion-collapse collapse description" aria-labelledby="heading_1_0" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Producto</th>
										<th>Modelo</th>
										<th>Cajas</th>
										<th>Paquetes</th>
										<th>Piezas</th>
										<!--th>Total Surt</th>
										<th>Fecha / hora</th-->
										<th>Ubicación</th>
									</tr>
								</thead>
								<tbody id="reasignation_table_info">`;
							content += getReasignationDetail( counter );		
							content += `</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12">
				Elige una opcion para las partidas del usuario que estas quitando del surtimiento
			</div>`;
				
			content += getCurrentUsers( transfer_id, $( '#asigned_user_id_' + counter ).html().trim(), $( '#asignation_id_' + counter ).html().trim(), counter );
			//content += build_parts_number();
			content += `</div>
			<div class="row">
				<div class="col-4 text-center">
					<button 
						class="btn btn-warning form-control"
						onclick="save_reassignation( ${counter} );"
					>
						<i>Reasignar</i>
					</button>
				</div>
				<div class="col-4 text-center">
					<button 
						class="btn btn-info form-control"
						onclick="reassignTransfer( ${transfer_id}, true )"
					>
						<i>Asignar partidas entre los usuarios actuales</i>
					</button>
				</div>
				<div class="col-4 text-center">
					<button 
						class="btn btn-danger form-control"
						onclick="close_emergent( 1 )"
					>
						<i>Cancelar y salir</i>
					</button>
				</div>
			</div>
		`;
		$( '.subemergent_content' ).html( content );
		$( '.subemergent' ).css( 'display', 'block' );
		$( '#to_asign_counter' ).html( $( '#reasignation_table_info tr' ).length );
		$( '#reassignation_total_parts' ).val( $( '#reasignation_table_info tr' ).length );

	}

	function getReasignationDetail( counter ){
		//alert( counter );
		var transfer_id = $( '#transfer_folio_input' ).val().trim().split( ' ' );
		transfer_id = transfer_id[1];
		//alert( $( '#asignation_id_' + counter ).html() );
		var assignment_id = $( '#asignation_id_' + counter ).html().trim();
		var url = "php/db.php?fl=getReasignationDetail&transfer_id=" + transfer_id;
		url += "&user_assignment_id=" + assignment_id + "&type=reasignation";
		//$( '#reasignation_table_info' ).empty();
		//$( '#reasignation_table_info' ).html();
		//alert( url );
		var resp = ajaxR( url );//.split( "|" );
	//	alert( resp );
		return resp;
	}
//obtiene usuario actuales para reasignacion
	function getCurrentUsers( transfer_id, excluyed_user_id, excluyed_assignation_id, counter ){
		var url = `php/db.php?p_k=${transfer_id}&fl=getAssignedUsers`;
		url += `&excluyed_user=${excluyed_user_id}&excluyed_assignation=${excluyed_assignation_id}`;
		var getAssignedUsers = ajaxR( url );
		var resp = `<div class="row">
				<div class="col-6 text-center">
					<label class="text-primary"># Partidas</label>
					<input type="number" id="reassignation_total_parts" 
					class="form-control text-end text-success" readonly>
				</div>
				<div class="col-6 text-center">
					<label class="text-primary"># Usuarios</label>
					<input type="number" id="reassignation_parts_users" class="form-control text-end" readonly>
				</div>
			</div>
			<div class="input-group">
				<br>
					<input 
						type="text" 
						id="reasign_people_seeker"
						style="margin : 10px !important; box-shadow : 1px 1px 5px rgba( 0,0,0,0.3 );"
						onkeyup="seek_people_loged( 2, false, ${excluyed_user_id}, ${counter} )"
						class="form-control"
						placeholder="Buscar personas por asignar"
					>
					<button
						type="button"
						class="btn btn-light"
						onclick="seek_people_loged( 2, true, ${excluyed_user_id}, ${counter} );"
						title="Guardar"
					>
						<i class="icon-down-open"></i>
					</button>
				</div>
				<br>
				<div class="people_seeker_response" id="reassignation_seeker_response"></div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Usar</th>
					<th>Usuario</th>
					<th>Partidas Asignadas</th>
					<th>Partidas Surtidas</th>
					<th>Agregar</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody id="reassignation_users">`;
		var user_parts = JSON.parse( getAssignedUsers );
		for (var i = 0; i < user_parts.length ; i++) {
			resp += '<tr';
			if( user_parts[i]['assignment_status'] == 'canceled' ){
				resp += ' style="background-color:orange;"';
			}
			resp += '>';//no_visible
				resp += '<td align="center" class="' 
				+ '"><input type="checkbox" id="reasignation_user_valid_' + i + '" value="' + user_parts[i]['id'] + '" onclick=\"reasign_parts_recalculation();\"></td>';
				resp += '<td class="no_visible" id="reasignation_id_' + i + '">' + user_parts[i]['id'] + '</td>';
				resp += '<td class="no_visible" id="reasigned_user_id_' + i + '">' + user_parts[i]['user_id'] + '</td>';
				resp += '<td align="left" id="reasigned_user_name_' + i + '">' + user_parts[i]['name'] + '</td>';
				resp += '<td align="right" id="reasigned_number_' + i + '">' + user_parts[i]['assigned_transfer'] + '</td>';
				resp += '<td align="right">' 
				+ (user_parts[i]['supplied_assigned_transfer'] != undefined ? user_parts[i]['supplied_assigned_transfer'] : 0 ) 
				+ '</td>';
				resp += `<td id="reasigned_quantity_${i}"></td>`;
				resp += `<td id="total_reasigned_quantity_${i}"></td>`;
				resp += '</tr>';
		}
		resp += `</tbody>
		</table>`;
		return resp;
	}

	function reasign_parts_recalculation(){
	//recorre la tabla para ver el numero de usuarios y ver el numero de partidas
		var users = 0;
		var parts_per_user = 0;
		var parts = $( '#reassignation_total_parts' ).val();
		$( '#reassignation_users tr' ).each( function( index ){
			if( $( '#reasignation_user_valid_' + index ).prop( 'checked' ) == true ) {
				users ++;
			}else{
				$( '#reasigned_quantity_' + index ).html( '' );
				$( '#total_reasigned_quantity_' + index ).html( '' );
			}
		});
		parts_per_user = Math.ceil( parts/users );
		$( '#reassignation_parts_users' ).val( users );
		
		$( '#reassignation_users tr' ).each( function( index ){
			if( $( '#reasignation_user_valid_' + index ).prop( 'checked' ) == true ) {
				$( '#reasigned_quantity_' + index ).html( parts_per_user );
				var before = parseInt( $( '#reasigned_number_' + index ).html().trim() );
				//console.log( `#asigned_number_${index} ( before ) = ${before}` );
				//console.log( `#reasigned_quantity_${index} ( after ) = ${after}` );
				var after = parseInt( $( '#reasigned_quantity_' + index ).html().trim() );
				$( '#total_reasigned_quantity_' + index ).html( parseInt( after + before ) );
			}
		});
	}

	function reassignTransfer( transfer_id ){
		var new_users = '';
		if( assign_has_changed == 0 ){
			alert( "No hay cambios por guardar!" );
			var resp = playSupply( transfer_id );
			if( resp != true ){
				alert( resp );
				return false;
			}
			close_emergent();
			return false;
		}
	//recolecta las nuevas personas que surtirán la transferencia
		$( '.assigned_users' + " tr").each(function ( index ) {
			if( index > 0 ){
				new_users += '|';
			}
			$(this).children("td").each(function ( index2 ) {
				if( index2 == 0 ){
					new_users += ( $( '#user_valid_' + index ).prop( 'checked' ) ? 'is_valid' : 'is_invalid' );
				}else if( index2 <= 2 ){			
					new_users += ( index2 > 0 ? '~' : '' ) + $(this).html().trim();
				}
			});
		});
		var url = "php/db.php?fl=reassignTransfer&p_k=" + transfer_id + "&users_array=" + new_users;
		//alert( url );
		var reassignTransfer = ajaxR( url);
		alert( reassignTransfer.trim() );
		var resp = playSupply( transfer_id );
		if( resp != true ){
			alert( resp );
			return false;
		}
		close_emergent();
		//if( close_emergent == true ){
		close_emergent( 1 );
		//}
		assign_has_changed = 0;//oscar 2023 para corregir que no deja cerrar si no hay cambios


//		JSON.stringify(array);
	}

	function save_reassignation( counter ){
		var users = "";
		var transfer_id = $( '#transfer_folio_input' ).val().trim().split( ' ' );
		transfer_id = transfer_id[1];
		$( '#reassignation_users tr' ).each( function( index ){
			//alert();
			if( parseInt( $( '#total_reasigned_quantity_' + index ).html().trim() ) > 0 ){
				users += ( users == "" ? "" : "|" );
				users += $( "#reasigned_user_id_" + index ).html().trim();
				users += "~" + $( '#total_reasigned_quantity_' + index ).html().trim();
			}
		});
		var url = "php/db.php?fl=save_reassignation&data=" + users + "&transfer_id=" + transfer_id;
		url += "&disabled_assignation_id=" + $( '#asigned_user_id_' + counter ).html().trim();
		var response = ajaxR( url );
		if( response.trim() == 'ok' ){
			alert( "Reasignación satisfactoria!" );
			playSupply( transfer_id );
			assign_has_changed = 0;
			close_emergent();
			close_emergent( 1 );
		}else{
			alert( "Error : " + response );
		}
	}

	function playSupply( transfer_id ){
		var url = 'php/db.php?fl=playSupply&transfer=' + transfer_id;
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			alert( response );
			return response;
		}

		global_current_transfer_id = '';
		current_users = new Array();
		return true;
	}