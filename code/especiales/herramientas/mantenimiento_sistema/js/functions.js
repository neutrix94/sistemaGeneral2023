/*7.1.1 implementación Oscar 20201 ara lanzar el modal en automático*/
	$(document).ready(function(){
		$('#lanza_modal').click();
		validateBarcodesSeriesUpdate();
	});
/*fin de cambio*/
//7.1. Funcion para cambiar el numero de dias por medio del archivo bd_sql.php
	function cambiar_numero_dias(obj,flag){
	//obtenemos el valor del objeto
		var fecha_tmp=$(obj).val();
		if(fecha_tmp.length<10){
			alert("Ingrese una fecha correcta");
			$(obj).select();
			return false;
		}
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:'obtener_dias',fecha:fecha_tmp},
			success:function(dat){
				$("#"+flag).val(dat);
			}
		});
	}

//7.2. Funcion para habilitar / deshabilitar edicion de dias
	function habilitar_campo(id_campo,flag,id_boton,btn_guarda,campo_cambia){
		//alert(campo_cambia);
		if(flag==1){
			$("#"+id_campo).removeAttr('disabled');
			$("#"+id_campo).select();
		//cambiamos el botón
			$("#"+id_boton).attr('onclick','cambiar_parametro(\''+campo_cambia+'\',0,\''+id_boton+'\','+btn_guarda+');');
			$("#"+id_boton).html('Aceptar');
			$("#btn_"+btn_guarda).prop('disabled',true);
		}else if(flag==0){ 
			//document.getElementById(id_campo).disabled=true;
			$("#"+id_campo).prop('disabled',true);
			$("#"+id_boton).attr('onclick','habilitar_campo(\''+id_campo+'\',1,\''+id_boton+'\','+btn_guarda+');');
			$("#"+id_boton).html('Editar');
			$("#btn_"+btn_guarda).removeAttr('disabled');
		}
	}

//7.3. Funcion para cambiar parametro por medio del archivo bd_sql.php
	function cambiar_parametro(id_campo,flag,id_boton,btn_guarda){
	//extraemos el nuevo dato
		var dato_nvo=$("#"+id_campo).val();
		if(dato_nvo<=0){
			alert("Este campo tiene que ser positivo!!!");
			$("#"+id_campo).select();
			return false;
		}

	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:id_campo,valor:dato_nvo},
			success:function(dat){
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Error!!!\n'+dat);return false;
				}
				habilitar_campo(id_campo,0,id_boton,btn_guarda);				
			}
		});
	}

//7.4. Funcion para mandar llamar procedure por medio del archivo bd_sql.php
	function llamar_procedure(id_campo,flag,subtipo){
		var cont_emerge='<b style="font-size:50px;">Procesando...</b>';
		cont_emerge+='<br><br><img src="../../../../img/img_casadelasluces/load.gif">';
		var dato_nvo=$("#"+id_campo).val();
		$("#contenido_emergente").html(cont_emerge);
		$("#emergente").css("display","block");
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag,tipo:subtipo},
			success:function(dat){
			//lanzamos emergente
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Error!!!\n'+dat);return false;
				}
				alert("Proceso realizado exitosamente");
				location.reload();			
			}
		});

	}

//7.5. Funcion de Calendario
	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}

//7.6. Funcion para instalar procedures
	function insertaProcedures(fl){
		var cont_emerge='<b style="font-size:50px;">Procesando...</b>';
		cont_emerge+='<br><br><img src="../../../../img/img_casadelasluces/load.gif">';
		$("#contenido_emergente").html(cont_emerge);
		$("#emergente").css("display","block");

		var confirmacion = "";
		if( fl == 'procedures_inserta'){ 
			confirmacion = "Procedures insertados/actualizados exitosamente"; 
		}
		if( fl == 'recalcula_inventario_almacen' ){ 
			confirmacion = "Inventarios de almacenes por productos recalculados exitosamente"; 
		}
		if( fl == 'recorre_productos_por_liberar' ){ 
			confirmacion = "Los productos con orden de lista cero ( 0 ) fueron reseteados existosamente!";
		}
		if( fl == 'prefijo_codigos_unicos' ){
			confirmacion = "El prefijo de los códigos de barras únicos fue actualizado exitosamente";
		}
		if( fl == 'historico_productos' ){
			confirmacion = "El historico de notas de productos fue generado exitosamente exitosamente";
		}
	//implementacion Oscar 2023
		if( fl == 'reinsertar_almacen_producto' ){
			confirmacion = "Los productos que faltaban en la tabla de almacen producto fueron insertados exitosamente.";
		}
	//fin de cambio Oscar 2023
	//implementacion Oscar 2023
		if( fl == 'triggers_movimientos' ){
			confirmacion = "Los triggers de inventario fueron insertados exitosamente.";
		}
	//fin de cambio Oscar 2023
	//implementacion Oscar 2023
		if( fl == 'triggers_sistema' ){
			confirmacion = "Los triggers del sistema fueron insertados exitosamente.";
		}
	//fin de cambio Oscar 2023
	//implementacion Oscar 2023
		if( fl == 'triggers_transferencias' ){
			confirmacion = "Los triggers de transferencias fueron insertados exitosamente.";
		}
	//fin de cambio Oscar 2023

	//implementacion Oscar 2023
		if( fl == 'update_scripts' ){
			confirmacion = "Los SCRIPTS del sistema fueron insertados exitosamente desde el VERSIONADOR.";
		}
	//fin de cambio Oscar 2023
	//implementacion Oscar 2023
		if( fl == 'pause_sinchronization_apis' ){
			confirmacion = "Las APIS fueron pausadas exitosamente.";
		}
		if( fl == 'renew_sinchronization_apis' ){
			confirmacion = "Las APIS fueron reanudadas exitosamente.";
		}
		if( fl == 'pause_sinchronization_apis_store' ){
			confirmacion = "Las sincronizacion de la sucursal fue pausada exitosamente.";

		}
		if( fl == 'renew_sinchronization_apis_store' ){
			confirmacion = "Las sincronizacion de la sucursal fue reanudada exitosamente.";
		}
		if( fl == 'barrido_general_productos' ){
			confirmacion = "Productos barrido en Facturación exitosamente.";
		}
	
		if(! confirm( "Desea continuar con esta operación?" )){
			$("#contenido_emergente").html('');
			$("#emergente").css("display","none");
			return false;
		}
	//

	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag : fl},
			success:function(dat){
			//lanza emergente
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Mensaje : \n'+dat);//return false;
					close_emergent();
					//location.reload();
					return false;		
				}
				if( fl != 'restoration_mode' ){
					alert(confirmacion);
					close_emergent();
					//location.reload();			
				}else{
					alert( aux[1] );
					close_emergent();
					//location.reload();	
				}
			}
		});
	}

	function close_emergent(){
		$("#emergente").css("display","none");
	}
/*fin de cambio Oscar 20.12.2019*/
/*implementacion Oscar 2023 para el;iminar alertas erroneas*/
	function delete_alerts(){
		var min_number = $( '#alerts_min_number' ).val();
		if( min_number < 1 ){
			alert( "La cantidad minima para eliminar alertas debe de ser uno ( 1 )" );
			$( '#alerts_min_number' ).focus();
			return false;
		}
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{ flag : 'eliminar_alertas_inventarios_erroneas', min_number : min_number },
			success:function(dat){
			//lanza emergente
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Mensaje : \n'+dat);//return false;
					location.reload();
					return false;		
				}
				alert("Alertas eliminadas exitosamente!");
				location.reload();			
			}
		});
	}
/*fin de cambio Oscar 2023*/
/*implementacion Oscar 2021 para instrucciones de uso y regreso al index*/
	function back_index(){
		if( !confirm('Realmente desea salir?')){
			return false;
		}else{
			location.href="../../../../index.php?";
		}
	}

	function activar_instrucciones( num, num2 = null ){
	/*oculta todas las intrucciones*/
		$('#instrucciones_agrupacion').css('display', 'none');
		$('#instrucciones_eliminar_sin_uso').css('display', 'none');
		$('#instrucciones_eliminar_sin_inventario').css('display', 'none');
		
		$('#1_1').css('display', 'none');
		$('#1_2').css('display', 'none');
		$('#1_3').css('display', 'none');
		
		num == 1 ?$('#instrucciones_agrupacion').css('display', 'block') : null;
		num == 2 ?$('#instrucciones_eliminar_sin_uso').css('display', 'block') : null;
		num == 3 ?$('#instrucciones_eliminar_sin_inventario').css('display', 'block') : null;
		
		num2 != null ? $( '#1_' + num2 ).css('display', 'block') : null;

		$('#lanza_modal').click();
	}


	function validateBarcodesSeriesUpdate(){
		$.ajax({
			type : 'POST',
			url : 'bd_sql.php',
			data:{flag : 'validateBarcodesSeriesUpdate'},
			success : function ( dat ){
				if( dat.trim() != 'ok'){
					$( '.modal-title' ).css( 'display', 'none' );
					$( '.modal_btn' ).css( 'display', 'none' );
					$( '.modal-body' ).html( dat );
				}
			}

		});
	}
	function update_barcodes_prefix( obj ){
		$( obj ).attr( 'disabled', true );
		$( obj ).css( 'display', 'none' );

		$.ajax({
			type : 'POST',
			url : 'bd_sql.php',
			data:{flag : 'updateBarcodesPrefix'},
			success : function ( dat ){
				if( dat.trim() != 'ok'){
					$( '.modal_btn' ).css( 'display', 'none' );
					$( '.modal-body' ).html( dat );
					$( '.prefix_has_changed' ).attr( 'onclick', 'location.reload();');
				}
			}

		});
	}
			
	function getStoresLocations(){
		var url = "ajax/ubicacionesSucursales.php?storeLocationsFlag=getStoresForm";
		var resp = ajaxR( url );
		$( '.modal_btn' ).css( 'display', 'none' );
		$( '.modal-title' ).html( 'Resetear Ubicaciones de Sucursales' );
		$( '.modal-body' ).html( resp );
		$('#lanza_modal').click();
	}

	function reset_store_locations(){
		var stores = '';
		$( '#store_locations_list tr' ).each( function( index ) {
			$( this ).each( function( index2 ){
				$( this ).children( 'td' ).each( function( index3 ){
					if( index3 == 1 ){
						$( this ).children( 'input' ).each( function( index3 ){
							if( $( this ).prop( 'checked' ) ){
								stores += ( stores == '' ? '' : ',' );
								stores += $( this ).attr( 'value' );
							}
						})
					}
				})
			})
		})
		var url = 'ajax/ubicacionesSucursales.php?storeLocationsFlag=resetStoresLocations&stores=' + stores;
		var resp = ajaxR( url );
		$( '.modal_btn' ).css( 'display', 'none' );
		$( '.modal-body' ).html( resp );
	}
	
	//lamadas asincronas
	function ajaxR( url ){
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

		/*var url = "ajax/db.php?fl=updateBarcodesPrefix";
		//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );*/
	
/*fin de cambio Oscar 2021*/