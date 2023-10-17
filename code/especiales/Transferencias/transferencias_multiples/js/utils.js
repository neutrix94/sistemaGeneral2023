var global_current_transfer_id = '';
var global_tmp_assigned_user_name = '', global_tmp_assigned_user_id = '';
var global_tmp_assigned_user_counter = '';
var user_parts = new Array();
var current_users;
var assign_has_changed = 0;

	function reload_transfers(){
		aux=ajaxR( './php/builder.php?status_id=6&days=' + $( '#days_filter' ).val() );
		$( '#finished_transfers' ).html( aux );
	}
	
	function clean_filters(){
		$('#seeker').val('');
		$('#date_in').val('')
		$('#date_out').val('');
		$('#warehouses').val('0');
		show_all( 'dates' );
		show_all( 'warehouse' );
		show_all( 'busc' );
	}
	
	function dates_filter(  ){
		var ref = parseInt($( '#total_list' ).val().trim());
		if( $( '#date_in' ).val() != '' && $( '#date_out' ).val() != ''){
			for ( var i = 0; i <= ref; i++ ) {//itera tablas
				var count = 0;
				$('#list_' + i + ' tr').each(function() {
					var mostrar = 0;
					if( this.cells[3] ){
							var aux_in = this.cells[5].innerHTML.trim().toLowerCase().split(' ');
							if( aux_in[0] >= $( '#date_in' ).val() && aux_in[0] <= $( '#date_out' ).val() ){
								mostrar = 1;
							}
						if( mostrar == 1){	
					    	$('#' + i + '_' + count ).removeClass( 'no-visible-dates' );
					    	$('#' + i + '_' + count ).addClass( 'visible-dates' );
					    }else{
					    	$('#' + i + '_' + count ).removeClass( 'visible-dates' );
					    	$('#' + i + '_' + count ).addClass( 'no-visible-dates' );
					    }		
					}   	
			    	count ++;
				});
	   		}
	   	}else{
			alert( "Es necesario poner las dos fechas para filtra por fecha");
			show_all( 'dates' );
			return false;
		}
	}
	function warehouse_filter(){
		var val = $( '#warehouses option:selected' ).text().trim().toLowerCase();
		if( $( '#warehouses option:selected' ).val() == 0 ){
			show_all( 'warehouse' );
			return true;
		}
		var ref = parseInt($( '#total_list' ).val().trim());
		for ( var i = 0; i <= ref; i++ ) {//itera tablas
			var count = 0;
			$('#list_' + i + ' tr').each(function() {
				var mostrar = 0;
				if( this.cells[3] ){
						//var aux_in = this.cells[4].innerHTML.trim().toLowerCase().split(' ');
					if( this.cells[4].innerHTML.trim().toLowerCase() == val ){
						mostrar = 1;
					}
					if( mostrar == 1){	
				    	$('#' + i + '_' + count ).removeClass( 'no-visible-warehouse' );
				    	$('#' + i + '_' + count ).addClass( 'visible-warehouse' );
				    }else{
				    	$('#' + i + '_' + count ).removeClass( 'visible-warehouse' );
				    	$('#' + i + '_' + count ).addClass( 'no-visible-warehouse' );
				    }		
				}   	
		    	count ++;
			});
   		}
	}

	function active_seeker ( obj, comparison = null ){
		var val = $( obj ).val().trim().toLowerCase().split(' ');
		if ( val[0].length <= 2 ) {
			show_all( 'busc' );
			return false;
		}
	//verifica si hay que validar contra almacen y fechas
		var validate_warehouse = ( $( '#warehouses' ).val() != 0 ? 1 : 0 );
		var validate_date = ( $( '#date_in' ).val() != '' || $( '#date_out' ).val() != '' ? 1 : 0 );

		var ref = parseInt($( '#total_list' ).val().trim());

		for ( var i = 0; i <= ref; i++ ) {//itera tabla
			var count = 0;
			$('#list_' + i + ' tr').each(function() {//itera filas
			//comparacion de celdas
				var match_id = 0,match_folio = 0, match_a_orig = 0, match_a_dest = 0, match_date = 0, match_titulo = 0;
				var mostrar = 0; 
				if( count > 0 ){
					for( var j = 0; j < val.length; j++ ){
					    if( this.cells[0].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
					    	match_id = 1;
					    }
						
					    if( this.cells[1] ){
						    if( this.cells[1].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
						    	match_folio = 1;
						    }
						    if( this.cells[2].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
						    	match_a_orig = 1;
						    }
						    if( this.cells[3].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
						    	match_a_orig += 1;
						    }
						    if( this.cells[7].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
						    	match_titulo += 1;
						    }
						    if( this.cells[10].innerHTML.trim().toLowerCase().indexOf( val[j] ) != -1 ){
						    	match_folio += 1;
						    }
						}
					}
				    if( match_id > 0 || match_a_orig > 0 || match_titulo > 0 || match_folio > 0 ){
				    	$('#' + i + '_' + count ).removeClass( 'no-visible-busc' );
				    	$('#' + i + '_' + count ).addClass( 'visible-busc' );
				    }else{
				    	$('#' + i + '_' + count ).removeClass( 'visible-busc' );
				    	$('#' + i + '_' + count ).addClass( 'no-visible-busc' );
				    }
				}
			    count ++;
			});
		}
	}

	function show_all ( type ){
		$( 'tr' ).removeClass( 'no-visible-' + type );
		$( 'tr' ).addClass( 'visible-' + type );
	}
	var posTransList,posTransAut;
	function autorizaTrans( num_list, num_cell, flag = null ){
	//obtenemos el id de la transferencia
		var texto_info='';
		var id = $( '#' + num_list + '_0_' + num_cell ).html().trim();
		if(flag=='password'){
			texto_info=$("#texto_trns").val();
			if(texto_info.length<=0){
				alert("El nombre no puede ir vacío!!!");
				$("#texto_trns").focus();
			}
		}
	//enviamos datos por ajax
		$.ajax({
			url:'../../../ajax/autorizaTrans.php',
			type:'post',
			cache:false,
			data:{id_transferencia:id,autorizacion:texto_info},
			success:function(dat){
				//alert( dat );
				var aux=dat.split("|");
//					alert(dat);return false;
				if(aux[0]!='ok'){
					alert(dat);
					return false;
				}
				if(aux[1]==0){//si es autorizar					
					document.getElementById('mensajesPop').style.display='block';
					posTransList = num_list;
					posTransAut = num_cell;
				}
				if(aux[1]==1){
					location.reload();
				}
				if(aux[1]==2){
					location.href="../../../../" + aux[2] + '&is_list_transfer=1';
				}

				if(aux[1]==7){
					alert( aux[2] );
					location.reload();
					return false;
				}

				if(aux[1]=='pedir_pass'){
					var pide_autorizacion='<p style="color:white;font-size:25px;" id="msg_trans"><b>'+aux[2]+'</b></p>';
					//pide_autorizacion+='<p style="width:50%;"><input type="text" id="passWord_1" placeholder="Contraseña..." onkeyDown="cambiar(this,event,\'passWord\');"></p>';
					pide_autorizacion+='<p style="width:50%;"><input type="text" class="form-control" id="texto_trns"></p>';
					//pide_autorizacion+='<input type="hidden" id="passWord" value="">   ';
					pide_autorizacion+='<button onclick="autorizaTrans('+ num_list + ',' + num_cell +',\'password\')" class="btn btn-success">Confirmar</button>   ';
					pide_autorizacion+='<button onclick="cancela_autorizacion_trnsf();" class="btn btn-danger">Cancelar</button><br><br>';
					$("#contenidoInfo").html(pide_autorizacion);
					$("#emergenteAutorizaTransfer").css('display','block');
					$("#texto_trns").focus();
					if(aux[3]=='yellow'){
						$("#contenidoInfo").css("background",aux[3]);
						$("#msg_trans").css("color","black");
					}
				}					
			}
		});
	}
	

	function autorizaTrans2(nval){
		//var conf;
		var id = $( '#' + posTransList + '_0_' + posTransAut ).html().trim();//celdaValorXY('listado', 0, posTransAut);
		var temp='';	
	//si el flag es 5 ceramos ventana
		if(nval==5){
			document.getElementById('mensajesPop').style.display='none';
			return false;
		}
		$( '#mensajesPop' ).css( 'display', 'none');
		$( '#filtros' ).css( 'display', 'none');
		$( '#emergenteAutorizaTransfer' ).css( 'display', 'block');
		
			$.ajax({
				type:'post',
				url:'../../../ajax/autorizaTrans.php',
				cache:false,
				data:{id_transferencia:id,nval:nval,autoriza_transferencia:1},
				success:function(datos){
					alert( datos );location.reload();return false;
					var aux=datos.split("|");
					temp=datos;
					if(temp=="No cuenta con los permisos para realizar esta acción"){
					document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
					document.getElementById('filtros').style.display='block';//desocultamos los filtros
					alert(temp);
					return false;
				}
				if(temp=="La transferencia ya ha sido autorizada"){
					document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
					document.getElementById('filtros').style.display='block';//desocultamos los filtros
					alert(temp);
					return false;
				}
				if(temp=="No es posible continuar con el proceso de transferencia localmente.\nContacte al administrador para continuar!!!"){
					document.getElementById('emergenteAutorizaTransfer').style.display='none';//ocultamos mensaje de estado de transferencia
					document.getElementById('filtros').style.display='block';//desocultamos los filtros
					alert(temp);
					return false;
				}				
				posTransAut=-1;
				window.location.reload();//recargamos pagina
				return false;
				}
			});
	}		
//ocultar mensaje de autorización
	function cancela_autorizacion_trnsf(){
			$("#contenidoInfo").html('');//limpiamos el contenido
			$("#emergenteAutorizaTransfer").css('display','none');//ocultamos la emergente
	}
	function resolverTrans(pos){
		var f=document.form1;
		var id=celdaValorXY('listado', 0, pos);
		var tabla=f.tabla.value;
		var no_tabla=f.no_tabla.value;
		
		aux=ajaxR('../ajax/validaAccion.php?tipo='+1+'&id_valor='+id+'&tabla='+tabla);
		ax=aux.split('|');
		
		if(ax[0] == 'SI'){
			url='../especiales/resolucionTransferencias.php?a1de185b82326ad96dec8ced6dad5fbbd='+ax[2]+'&a01773a8a11c5f7314901bdae5825a190='+ax[1];
			location.href=url;
		}	
	}

//ver transferencias
	function viewTrans( list, pos ){
		window.open('../../../pdf/imprimeDoc.php?tdoc=transferencia&id=' + $( '#' + list + '_0_' + pos ).html().trim() + '&view=1');
		setTimeout(function () {location.href = "index.php";}, 500);
	}
	function view_transfer( list, pos ){
		location.href="../../Transferencias_desarrollo_racion/nuevaTransferencia.php?idTransfer=" 
		+ $( '#' + list + '_0_' + pos ).html().trim() + '&is_list_transfer=1';
	}
//cancelar transferencia
	function cancelarTransfer( list, pos ){//recibimos flag y posición
		if( ! confirm( "Realmente desea eliminar la transferencia " + $('#' + list + '_1_' + pos ).html().trim() + '?' ) ){
			return false;
		}
		var aux_ajax=ajaxR( '../../../ajax/validaAccion.php?flag=1&id=' + $( '#' + list + '_0_' + pos ).html().trim() );
		var ax=aux_ajax.split("|");
		if(ax[0]!='ok'){
			alert( "Error!!!\n" + aux_ajax );
		}else{
			alert(ax[1]);
			if( ax[1] == 'Transferencia cancelada exitosamente!!!' ){//si la transferencia se eliminó correctamente
				$( '#' + list + '_' + pos ).remove();
			}
		}
	}
//imprimir transferencia (carta)
	function imprimeTrans( list, pos ){
		if( $( '#' + list + '_5_' + pos ).html().trim() == 'No autorizado'){
			alert("La transferencia debe estar autorizada para poder imprimirla");
			return false;
		}
		window.open( '../../../pdf/imprimeDoc.php?tdoc=transferencia&id=' + $( '#' + list + '_0_' + pos ).html().trim() );
		if( $( '#' + list + '_8_' + pos ).html().trim() == 1 ){
			$( '#' + list + '_' + pos ).addClass('red');
		}else{
			$( '#' + list + '_' + pos ).addClass('green');
		}
	}
//imprimir ticket de transferencia
	function imprimeTicketTrans( list, pos ){
		var impr_tkt=ajaxR("../../Transferencias/ticket_transferencia/ticket_transf.php?flag=reimpresion&id_transf="
			+ $( '#' + list + '_0_' + pos ).html().trim());
		var split_resp=impr_tkt.split("|");
		if(split_resp[0]!="ok"){
			alert(impr_tkt);
			return false;
		}
		window.open( "../../../../cache/ticket/" + split_resp[1] );
		if( $( '#' + list + '_8_' + pos ).html().trim() == 1 ){
			$( '#' + list + '_' + pos ).addClass('red');
		}else{
			$( '#' + list + '_' + pos ).addClass('green');
		}
	}
//regresar al menu
	function menu(){
		if ( !confirm("Salir de esta pantalla?") ){
			return false;
		}
		location.href = '../../../../index.php?';
	}

	function close_emergent( type = null ){
		if( type != null ){
			$( '.subemergent_content' ).html( '' );
			$( '.subemergent' ).css( 'display', 'none' );			
			return false;
		}
		$( '#contenidoInfo' ).html( '' );
		$( '#emergenteAutorizaTransfer' ).css( 'display', 'none' );
	}

/*Funciones de asignación de partidas de transferencias a empleados*/
	function assignTransfer( transfer_id ){
		global_current_transfer_id = transfer_id;
		var getFormAssignTransfer = ajaxR( "php/formAssignTransfer.php?p_k=" + global_current_transfer_id );
		$( '#contenidoInfo' ).html( getFormAssignTransfer );
		$( '#emergenteAutorizaTransfer' ).css( 'display', 'block' );
	//estiliza
		$( '#contenidoInfo' ).css( 'width', '90%' );
		$( '#contenidoInfo' ).css( 'top', '0px !important' );
		$( '#contenidoInfo' ).css( 'background-color', 'white' );
	//enfoca numero de personas
		$( '#peopleNumber' ).focus();
	//crea botón para cerrar pantalla
		var btn_tmp = '<button class="btn btn-success" onclick="saveAssignment();">';
			btn_tmp += '<i class="icon-ok-circle">Guardar</i>';
		btn_tmp += '</button>'; 
		$( '#contenidoInfo' ).append( btn_tmp );
	}

	function assignTransferAgain( transfer_id ){
		global_current_transfer_id = transfer_id;
		var getFormAssignTransfer = ajaxR( "php/formAssignTransfer.php?p_k=" + global_current_transfer_id + '&fl=reassign' );
		$( '#contenidoInfo' ).html( getFormAssignTransfer );
		$( '#emergenteAutorizaTransfer' ).css( 'display', 'block' );
	//estiliza
		$( '#contenidoInfo' ).css( 'width', '90%' );
		$( '#contenidoInfo' ).css( 'top', '0px !important' );
		$( '#contenidoInfo' ).css( 'background-color', 'white' );
	//habilita campos
		/*$( '.btn.btn-warning.btn_number_assign_edit' ).click();
		$( '#peopleNumber' ).focus(); */
	}

	function setPeopleNumber( edit = null ){
		if( edit == null ){
			if( $( "#peopleNumber" ).val() <= 0 ){
				alert( "El número de personas que va a surtir la transferencia debe ser mayor a Cero!" );
				$( "#peopleNumber" ).select();
				return false;
			}
			if( parseInt( $( "#peopleNumber" ).val().trim() ) > parseInt( $( '#transfer_parts' ).val().trim() ) ){
				alert( "El número de personas que va a surtir la transferencia ( " + $( "#peopleNumber" ).val() + " ) no puede ser mayor a las " + $( '#transfer_parts' ).val() + " partidas!" );
				$( "#peopleNumber" ).select();
				return false;
			}
			$( "#people_seeker" ).removeAttr( 'disabled' );
			$( "#peopleNumber" ).attr( 'disabled', 'true' );
			$( ".btn_number_assign" ).css( 'display', 'none' );
			$( ".btn_number_assign_edit" ).css( 'display', 'block' );
			var transfer_parts = parseInt( $( '#transfer_parts' ).val() );
			var number_people = parseInt( $( '#peopleNumber' ).val() );
			$( '#partsNumber' ).val( Math.round( transfer_parts / number_people ) );
		}else{
			assign_has_changed = 1;
			$( "#peopleNumber" ).removeAttr( 'disabled' );
			$( "#people_seeker" ).attr( 'disabled', 'true' );
			$( ".btn_number_assign_edit" ).css( 'display', 'none' );
			$( ".btn_number_assign" ).css( 'display', 'block' );

		}
	}

	function seek_people_loged( type, show_all = false, excluyed_user = '', counter ){
		var txt = ( type == 1 ? $( '#people_seeker' ).val().trim() : $( '#reasign_people_seeker' ).val().trim() );
		if( txt.length <= 2 && show_all == false ){
			$( '.people_seeker_response' ).css( 'display', 'none' );
			return false;
		}
		var url = "php/db.php?fl=seekPeopleLoged&key=" + txt + '&users=' + current_users;
		url += "&type=" + type;
		if( type == 2 ){
			url += "&excluyed_user=" + excluyed_user;
			url += "&counter=" + counter;
		}
		var response = ajaxR( url );
		if( type == 1 ){
			$( '.people_seeker_response' ).html( response );
			$( '.people_seeker_response' ).css( 'display', 'block' );	
		}else if( type == 2 ){
			$( '#reassignation_seeker_response' ).html( response );
			$( '#reassignation_seeker_response' ).css( 'display', 'block' );
		}
	}

	function addPeopleTransfer( user_id, type, counter ){
		//verifica que no sobrepase los usuario establecidos
		var valids = 0;
		$( '.assigned_users' + " tr").each(function ( index ) {
			valids += ( $( '#user_valid_' + index ).prop( 'checked' ) ? 1 : 0 );
		});
	//valida que no se pase de usuarios
		if( parseInt( $( '#peopleNumber' ).val()) <= parseInt( valids ) ){
			alert( 'No se puede agregar el usuario porque el número de usuarios ya esta lleno!' );
			return false;	
		} 
		var url = 'php/db.php?fl=insertUserTransfer&id=' + user_id + '&transfer=' + $( '#transfer_id' ).val(); 
		url += '&parts_total=' + $( '#transfer_parts' ).val() + '&slope_parts=' + $( '#transfer_parts' ).val();
		url += '&parts=' + $( '#partsNumber' ).val();
		var insertUser = ajaxR( url );

		user_parts.push( JSON.parse( insertUser ) );

		build_parts_number();
		$( '#people_seeker' ).val('');
		$( '.people_seeker_response' ).html('');
		$( '.people_seeker_response' ).css( 'display', 'none' );
	//recarga datos de transferencia
		url = 'php/db.php?fl=getTransferHeader&transfer_id=' + global_current_transfer_id;
		response = ajaxR( url );
		var aux = response.split( '|' );
		if( aux[0] != 'ok' ){
			alert( "Erorr : \n" + response );
			return false;
		}
		$( '#transfer_parts_assigned' ).val( aux[1] );
		$( '#slope_transfer_parts' ).val( aux[2] );
		if( type == 2 ){
			//var transfer_id = $( '#transfer_folio_input' ).val().trim().split( " " );
			//transfer_id = transfer_id[1];
			getReasignationDetailView( counter );
			//getCurrentUsers( transfer_id );
		}
		
	}

	function getAssignedUsers( transfer_id ){
		var getAssignedUsers = ajaxR( "php/db.php?p_k=" + transfer_id + '&fl=getAssignedUsers' );
		//alert( getAssignedUsers );
		user_parts = JSON.parse( getAssignedUsers );
		build_parts_number( 1 );
	}

	function build_parts_number( is_edition = null ){
		var resp = '';
		$( '.assigned_users' ).empty();//implementacion Oscar 2023 para vaciar la vista de usuarios y volver a llenar
		current_users = new Array();

		//console.log( user_parts );
		for (var i = 0; i < user_parts.length ; i++) {
			resp += '<tr';
			if( user_parts[i]['assignment_status'] == 'canceled' ){
				resp += ' style="background-color:orange;"';
			}
			resp += '>';//no_visible
				resp += '<td align="center" class="' + ( is_edition != null ? '' : '' ) 
				+ '"><input type="checkbox" id="user_valid_' + i + '" ' 
				+ ( user_parts[i]['assignment_status'] == 'canceled' ? '' : 'checked' ) + ' onchange="detect_assign_change( 0, this, ' + i + ' );"></td>'
				resp += '<td class="no_visible" id="asignation_id_' + i + '">' + user_parts[i]['id'] + '</td>';
				resp += '<td class="no_visible" id="asigned_user_id_' + i + '">' + user_parts[i]['user_id'] + '</td>';
				resp += '<td align="left" id="asigned_user_name_' + i + '" onclick="getUserToChange(' + i + ');">' + user_parts[i]['name'] + '</td>';
				resp += '<td align="right" id="asigned_number_' + i + '">' + user_parts[i]['assigned_transfer'] + '</td>';
				resp += '<td align="right">' 
				+ (user_parts[i]['supplied_assigned_transfer'] != undefined ? user_parts[i]['supplied_assigned_transfer'] : 0 ) 
				+ '</td>';
				resp += '<td align="center"><button class="btn btn-warning" onclick="show_assignment_detail( ' 
					+ global_current_transfer_id + ', ' + user_parts[i]['id'] + ' )"><i class="icon-eye"></i></button></td>';

				resp += '<td align="center"><button class="btn btn-success" onclick="delete_assignment_detail( ' 
					+ global_current_transfer_id + ', ' + user_parts[i]['id'] + ', ' + i + ' )"><i class="icon-loop-alt"></i></button></td>';
			resp += '</tr>';
			current_users.push( user_parts[i]['user_id'] );
		}
		$( '.assigned_users' ).html( resp );
		if( $( '.assigned_users tr' ).length >= $( '#peopleNumber' ).val() || is_edition != null  ){
			disabledAssignation();
			if( is_edition != null ){
				$( '#peopleNumber' ).val( $( '.assigned_users tr' ).length );
				$( '#partsNumber' ).val( Math.round( $( '#peopleNumber' ).val( ) / $( '#transfer_parts' ).val( ) ) );
				var transfer_id = $( '#transfer_id' ).val();
				var content = `<div class="row">
								<div class="col-2"></div>
								<div class="col-3 text-center">
									<button class="btn btn-success" 
										onclick="reassignTransfer( ${transfer_id} )">
										Guardar Reasignación
									</button>
								</div>
								<div class="col-2"></div>
								<div class="col-3 text-center">
									<button class="btn btn-danger" 
										onclick="closeReassignTransfer( ${transfer_id} );">
										Cerrar
									</button>
								</div>
								</div>`;
				$( '.assignations' ).append( content );
			}
		}
	}

	function delete_assignment_detail( global_current_transfer_id, user_part_id, counter ){
		var url = "php/db.php?fl=deleteAssignmentDetail&user_part_id=" + user_part_id;
		var response = ajaxR( url );
		if ( response.trim() == 'ok' ){
			alert( 'Las partidas del usuario kfueron liberadas exitosamente.' );
			$( '#asigned_number_' + counter ).html( 0 );
		}else{
			alert( "Error : " + response );
		}
	}

	function closeReassignTransfer( transfer_id ){
		if( assign_has_changed != 0 ){
			alert( "Hay cambio por guardar, para cerrar primero da click en guardar reasignación : " + assign_has_changed );
			return false;
		}
		var response = ajaxR( 'php/db.php?fl=closeReassignTransfer&transfer_id=' + transfer_id );
		if( response.trim() == 'ok' ){
			$( '#contenidoInfo' ).html( '' );
			$( '#emergenteAutorizaTransfer' ).css( 'display', 'none' );
		}
	}
	function show_assignment_detail( transfer_id, user_assignment_id = null ){
		//alert( transfer_id + ',' + user_assignment_id );
		var url = 'php/db.php?fl=getDetail&transfer_id=' + transfer_id;
		if( user_assignment_id != null ){
			url += '&user_assignment_id=' + user_assignment_id;
		}
		
		var response = ajaxR( url );
		//alert( response );
		$( '.subemergent_content' ).html( response );
		$( '.subemergent' ).css( 'display', 'block' );
		//$( '#emergenteAutorizaTransfer' ).css( 'display', 'none' );		
	}
	function close_subemergent(){
		$( '.subemergent_content' ).html( '' );
		$( '.subemergent' ).css( 'display', 'none' );
	}

	function getUserToChange( counter ){
		/*deshabilitado por oscar 2023
		if( global_tmp_assigned_user_counter != ''  ){
			desedit_tmp_usr( global_tmp_assigned_user_counter );
			return false;
		}

		global_tmp_assigned_user_counter = counter;
		global_tmp_assigned_user_id = $( '#asigned_user_id_' + counter ).html().trim();
		global_tmp_assigned_user_name = $( '#asigned_user_name_' + counter ).html().trim();

		var url = 'php/db.php?fl=getUsersCombo&current_users=' + current_users;
		url += '&current_user=' + $( '#asigned_user_id_' + counter ).html().trim();
		url += '&count=' + counter;
		url += '&val=' + global_tmp_assigned_user_id;
		url += '&name=' + global_tmp_assigned_user_name;
		var response = ajaxR( url );
		//alert( response );
		$( '#asigned_user_name_' + counter ).attr( 'onclick', '' );
		$( '#asigned_user_name_' + counter ).html( response );
		$( '#user_tmp' ).focus();*/
	}
	function desedit_tmp_usr( counter ){
		$( '#asigned_user_id_' + counter ).html( global_tmp_assigned_user_id );
		$( '#asigned_user_name_' + counter ).html( global_tmp_assigned_user_name );
		$( '#asigned_user_name_' + counter ).attr( 'onclick', 'getUserToChange(' + counter + ')' );
		global_tmp_assigned_user_id = '';
		global_tmp_assigned_user_name = '';
		global_tmp_assigned_user_counter = '';
	}

//reasignar usuario - usuario
	function reassignUserToUser( obj, counter ){
		var selected_option = $( obj ).val();
		if( selected_option != global_tmp_assigned_user_id ){
		//cambia usuario
			var url = 'php/db.php?fl=changeUserToUser&old_user=' + global_tmp_assigned_user_id;
			url += '&new_user=' + selected_option + '&transfer_id=' + global_current_transfer_id;
			
			var response = ajaxR( url );
			var aux_resp = response.split( '|' );
			if( aux_resp[0] != 'ok' ){
				alert( "Error al reasignar el usuario : " + response );
				return false;
			}else{
				alert( aux_resp[1] );
			}
		}
		
		var val = $( '#user_tmp' ).val();
		var txt =  $( '#user_tmp option:selected' ).text();

		$( '#asigned_user_id_' + counter ).html( val );
		$( '#asigned_user_name_' + counter ).html( txt );
		$( '#asigned_user_name_' + counter ).attr( 'onclick', 'getUserToChange( ' + counter + ' )' );
		
		global_tmp_assigned_user_id = '';
		global_tmp_assigned_user_name = '';
	}



	function saveAssignment(){
		close_emergent();
		global_current_transfer_id = '';
		current_users = new Array();
		location.reload();
	}

//deshabilita la función
	function disabledAssignation(){
		$( '#peopleNumber' ).attr( 'disabled', 'true' );
		$( '.btn_number_assign' ).css( 'display', 'none' );
		$( '.btn_number_assign_edit' ).css( 'display', 'block' );
	}

//poner la transdferencia en salida
	function transfer_output( transfer_id ){
		var url = 'php/db.php?fl=transferOutput&id=' + transfer_id;
		var response = ajaxR( url );
		var aux = response.split( '|' );
		if( aux[0] == 'ok' ){
			$( '.emergent_content' ).html( aux[1].trim() );
			$( '.emergent' ).css( 'display', 'block' );
			//location.reload();
		}else{
			alert( response.trim() );
		}
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










