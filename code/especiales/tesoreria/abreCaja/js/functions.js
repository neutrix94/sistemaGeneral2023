	
	function focus_password(){
		$( '#password' ).focus();
	}

	function link(flag){
		if(flag==1){
			if(confirm("Realmente desea regresar al panel?")==true){
				location.href=("../../../../index.php");
			}
		}
	}
//funcion que valida login
	function abrir_caja(){
		//validamos datos
		var log, contra, cambio, afiliaciones = "", terminales = "";
		log=$("#user").val();
		if(log.length<=0){
			alert("El campo de cajero no puede ir vacío!!!");
			$("#user").focus();
			return false;
		}
		contra=$("#password").val();
		if(contra.length<=0){
			alert("La contraseña de cajero no puede ir vacía!!!");
			$("#password").focus();
			return false;
		}
		cambio=$("#cambio_caja").val();
		if(cambio.length<=0){
			alert("El cambio inicial en caja es obligatorio*");
			$("#cambio_caja").focus();
			return false;
		}
//obtiene arreglo de afiliaciones
		$( '#afiliations_list tr' ).each( function(index){
			$( this ).children( 'td' ).each( function ( index2 ){
				if( index2 == 0 ){
					afiliaciones += ( afiliaciones == "" ? "" : "," );
					afiliaciones += $(this).html().trim();//id de afiliacion

				}
			});
		});
//obtiene arreglo de terminales
		$( '#terminals_list tr' ).each( function(index){
			$( this ).children( 'td' ).each( function ( index2 ){
				if( index2 == 0 ){
					terminales += ( terminales == "" ? "" : "," );
					terminales += $(this).html().trim();//id de terminal
				}
			});
		});

//alert( `Afiliaciones : ${afiliaciones} \n Terminales : ${terminales}` );return false;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'abreSesionCaja.php',
			cache:false,
			data:{
				login : log,
				contrasena : contra,
				cambio_caja : cambio,
				terminales : terminales,
				afiliaciones : afiliaciones
			},
			success:function(dat){
				if(dat!='ok'){
					alert(dat);
					//location.reload();
				}else{
					alert("Sesión de caja iniciada exitosamente!!!");
					location.href='../../../../index.php?';
				}
			}
			});
	}

	function pending_sales_validation(){
	//envia detos por ajax
		$.ajax({
			type : 'post',
			url : '../../Reportes/ajax/pending_validation_tkt.php',
			cache : false,
			data : { flag : 'seek_pending_to_validate' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					alert(dat);return false;
				}else{
					if( aux[1] != '' && aux[1] != null ){
						$( "#contenido_emergente" ).html( aux[1] );
						$( "#emergente" ).css( "display", "block" );
					}	
				}
			}		
		});
	}

	function print_pending_ticket(){
		cierra_emergente();
		$.ajax({
			type : 'post',
			url : '../../Reportes/ajax/pending_validation_tkt.php',
			cache : false,
			data : { flag : 'print_pending_to_validate' },
			success : function( dat ){
				var aux = dat.split("|");
				if( aux[0] != 'ok' ){
					alert(dat);return false;
				}else{
					$( "#contenido_emergente" ).html( '' );
					$( "#emergente" ).css( "display", "none" );
					//$( "#contenido_emergente" ).append( `` );
				}
			}		
		});
	}

	function cierra_emergente(){
		$("#contenido_emergente").html('');
		$("#emergente").css("display","none");	
	}
/*Implementaciones Oscar 2023 para terminales sin smart Accounts*/
	function setAfiliation(){
		var terminal_id = $( '#principal_terminal' ).val();
		if( terminal_id == 0 ){
			alert( "Selecciona una terminal valida para continuar!" );
			return false;
		}else{
			var terminal_txt = $( '#principal_terminal option:selected' ).text().trim();
			var exists = false;
			$( '#afiliations_list tr' ).each( function( index ){
				$( this ).children( 'td' ).each( function ( index2 ){
					if( index2 == 1 && $( this ).html().trim() == terminal_txt ){
						exists = true;
						return false;
					}

				});
			});
			if( exists ){
				alert( "Esta terminal ya fue agregada para este usuario!" );
			}else{
				$( '#afiliations_list' ).append( build_afiliation_row( terminal_id, terminal_txt ) );
			}
			$( '#principal_terminal' ).val( '' );
		}
	}

	function build_afiliation_row( terminal_id, terminal ){
		var content = `<tr>
			<td style="display:none;">${terminal_id}</td>
			<td>${terminal}</td>
			<td>
				<button 
					type="button"
					class="btn btn-danger"
					onclick="remove_afiliation( this );"
				>
					<i class="icon-cancel-circled"></i>
				</button>
			</td>
		</tr>`;
		return content;
	}

	function remove_afiliation( obj ){
		var element = $( obj ).parent( 'td' ).parent( 'tr' );
		element.remove();
	}

/*Implementaciones Oscar 2023 para terminales con smart Accounts*/
	function setTerminal(){
		var terminal_id = $( '#principal_terminal_smartAccounts' ).val();
		if( terminal_id == 0 ){
			alert( "Selecciona una terminal valida para continuar!" );
			return false;
		}else{
			var terminal_txt = $( '#principal_terminal_smartAccounts option:selected' ).text().trim();
			var exists = false;
			$( '#terminals_list tr' ).each( function( index ){
				$( this ).children( 'td' ).each( function ( index2 ){
					if( index2 == 1 && $( this ).html().trim() == terminal_txt ){
						exists = true;
						return false;
					}

				});
			});
			if( exists ){
				alert( "Esta terminal ya fue agregada para este usuario!" );
			}else{
				$( '#terminals_list' ).append( build_terminal_row( terminal_id, terminal_txt ) );
			}
			$( '#principal_terminal_smartAccounts' ).val( '' );
		}
	}

	function build_terminal_row( terminal_id, terminal ){
		var content = `<tr>
			<td style="display:none;">${terminal_id}</td>
			<td>${terminal}</td>
			<td>
				<button 
					type="button"
					class="btn btn-danger"
					onclick="remove_afiliation( this );"
				>
					<i class="icon-cancel-circled"></i>
				</button>
			</td>
		</tr>`;
		return content;
	}

	function remove_afiliation( obj ){
		var element = $( obj ).parent( 'td' ).parent( 'tr' );
		element.remove();
	}
