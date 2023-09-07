
//declaracion de variables globales
	var tipo_database,tipo_sistema,id_sucursal,agrupar_movimientos=0,eliminar_movimientos=0,agrupar_ventas=0,eliminar_ventas=0,fec_rsp,user,pass;

//
	function prepara_acciones(obj){
		var t_db=$("#tipo_bd").val(),t_s=$("#tipo_sys").val();
		if(t_db==0){
			alert("Elija el tipo de Base de Datos!!!");
			$('#id_suc option[value="0"]').attr("selected", true);
			$("#tipo_bd").focus();
			return false;
		}

		if(t_s==0){
			alert("Elija el tipo de sistema!!!");
			$('#id_suc option[value="0"]').attr("selected", true);
			$("#tipo_sys").focus();
			return false;
		}
	//
		if($(obj).val()==1){//si es nueva BD

		}
		if($(obj).val()==1){//si es respaldo de BD

		}
	}

	function set_apis_paths(){
		var api_url = "", versioner_url = "";
		api_url = $( '#api_path_input' ).val();
		if( api_url == '' ){
			alert( "La url del API no puede ir vacia!" );
			$( '#api_path_input' ).focus();
			return false;
		}
		versioner_url = $( '#versioner_path_input' ).val();
		if( versioner_url == '' ){
			alert( "La url del versionador no puede ir vacia!" );
			$( '#versioner_path_input' ).focus();
			return false;
		}
		var url = "ajax/Restoration.php?restoration_fl=set_apis_paths&api_path=" + api_url;
		url += "&versioner_path=" + versioner_url;
		var resp = ajaxR( url );
		if( resp == 'ok' ){
			verificar( true );
		}else{
			alert( "Error en set_apis_paths : " + resp );
		}
	}

	function build_paths_configuration_emergent(){
		var content = `<div class="row">
			<div class="col-1"></div>
			<div class="col-10 text-center">
				<h5>Ingresa la url del api ( Servidor destino ) : </h5>
				<input type="text" class="form-control" id="api_path_input">
				<hr>
				<hr>
				<h5>Ingresa la url del versionador : </h5>
				<input type="text" class="form-control" id="versioner_path_input">
				<br><br>
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="set_apis_paths();"
				>
					<i class="icon-ok-circle">Aceptar y continuar</i>
				</button>
				<button
					type="button"
					class="btn btn-success form-control"
					onclick="close_emergent();"
				>
					<i class="icon-ok-circle">Cancelar y cerrar</i>
				</button>
			</div>
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
	}

//función que verifica los datos
	function verificar( paths = false ){
		var content = `<div class="text-center"><h2>Generando registros...</h2>
		<img src="../../../img/img_casadelasluces/load.gif" height="10%"></div>`;
		$(".emergent_content").html( content );
		$(".emergent").css("display","block");
	//tipo de base de datos
		tipo_database=$("#tipo_bd").val();
		if(tipo_database==0){
			$("#tipo_bd").focus();
			alert("es necesario elegir el tipo de base de  datos!!!");
			return false;
		}
	//tipo de sistema
		tipo_sistema=$("#tipo_sys").val();
		if(tipo_sistema==0){
			$("#tipo_sys").focus();
			alert("es necesario elegir el tipo de sistema!!!");
			return false;
		}
	//id de nueva sucursal
		id_sucursal=$("#id_suc").val();
		if(id_sucursal==0){
			$("#id_suc").focus();
			alert("es necesario elegir la sucursal!!!");
			return false;
		}
	//agrupar movimientos
		if($("#agrupa_mov").checked==true){
			agrupar_movimientos=1;
		}
	//eliminar movimientos
		if($("#elimina_mov").checked==true){
			eliminar_movimientos=1;
		}
	//agrupar ventas
		if($("#agrupa_vtas").checked==true){
			agrupar_ventas=1;
		}
	//eliminar ventas
		if($("#elimina_vtas").checked==true){
			eliminar_ventas=1;
		}
	//fecha de respaldo
		fec_rsp=$("#fecha_respaldo").val();

	//verificamos la contraseña
		user=$("#usuario").val();
		if(user.length==0){
			alert("Debes ingresar un usuario!!!");
			$("#usuario").focus();
			return false;
		}
		pass=$("#contrasena").val();
		if(pass.length==0){
			alert("Debes ingresar la contraseña!!!");
			$("#contrasena").focus();
			return false;
		}
/*oscar 2023*/
		if( $( "#datetime" ).val() != $( '#fecha_respaldo' ).val() ){
			alert( "La fecha y hora no corresponden a la fecha y hora de respaldo\nVerifica y vuelve a intentar." );
			close_emergent();
			$( '#fecha_respaldo' ).select();
			return false;
		}
		if( $( "#unique_folio" ).val() != $( '#user_unique_folio' ).val() ){
			alert( "El folio unico de respaldo no corresponde!\nVerifica y vuelve a intentar." );
			close_emergent();
			$( '#user_unique_folio' ).select();
			return false;
		}
	/*implementacion Oscar 2023 para validar si ya se capturaron los paths*/
		if( ! paths ){
			build_paths_configuration_emergent();
			return false;
		}
	/**/
		//alert(id_sucursal);
	//enviamos validación de contraseña por ajax
		$.ajax({
			type:'POST',
			//url:'restauraBase.php',
			url:'ajax/Restoration.php',
			cache:false,
			data:{restoration_fl:'validateUserPassword',usuario:user,clave:pass,suc:id_sucursal},
			success: function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					$("#usuario").val('');
					$("#contrasena").val('');
					if( aux[0] == 'warning' ){
						var content = `<div class="row text-center">
							<br><br>
							<h2 class="text-center">${aux[1]}</h2>
							<button
								type="button"
								class="btn btn-danger"
								onclick="close_emergent();"
							>
								<i class="icon-cancel-circled">Aceptar</i>
							</button>
						</div>`;
						$( ".emergent_content" ).html( content );
						return false;
					}
					alert(dat);
					close_emergent();
					return false;
				}else{
					var actions = JSON.parse( aux[1] );
					build_restoration_actions( actions );
					//console.log( actions );
					//$("#emergente").css("display","block");
					//genera_restauracion();
					return true;
				}
			}
		});
	}

	function close_emergent(){
		$(".emergent_content").html( '' );
		$(".emergent").css("display","none");
	}

	function build_restoration_actions( actions ){
		var local_icon = "";
		var line_icon = "";
		var process_completed = "";
		var content = `<div class="text-center" style="position : relative; max-height : 450px !important; overflow : auto;">
		<table class="table table-bordered table-striped">
			<thead class="bg-warning" style="position : sticky; top : 0;">
				<tr>
					<th>Descripcion</th>
					<th>Inicio</th>
					<th>Final</th>
					<th>Local</th>
					<th>Linea</th>
					<th>Realizado</th>
				</tr>
			</thead>
			<tbody id="restoration_actions_list">`;
		for (var key in actions){
			local_icon = ( actions[key].is_in_local == 1 ? "icon-toggle-on text-success" : "icon-toggle-off text-danger" );
			line_icon = ( actions[key].is_in_line == 1 ? "icon-toggle-on text-success" : "icon-toggle-off text-danger" );
			process_completed = ( actions[key].was_finished == 1 ? "icon-ok-circle text-success" : "icon-cancel-circled" );
			content += `<tr id="actions_row_${key}">
					<td id="0_${key}">${actions[key].description}</td>
					<td id="1_${key}">${actions[key].initial_date}</td>
					<td id="2_${key}" class="text-center">${actions[key].final_date}</td>
					<td id="3_${key}" value="${actions[key].is_in_local}" class="text-center ${local_icon}"></td>
					<td id="4_${key}" value="${actions[key].is_in_line}" class="text-center ${line_icon}"></td>
					<td id="5_${key}" value="${actions[key].was_finished}" class="text-center ${process_completed}"></td>
					<td id="6_${key}" style="display : none;" value="${actions[key].current_restoration_id}"></td>
					<td id="7_${key}" style="display : none;" value="${actions[key].sql_code}"></td>
				</tr>`;
		}
		content += `</tbody>
			</table>
		</div>
		<div class="text-center">
			<button
				type="button"
				class="btn btn-success"
				id="start_restoration_btn"
				onclick="start_resoration();"
			>
				<i class="icon-ok-circle">Restaurar</i>
			</button>
		</div>`;
		$( '.emergent_content' ).html(content );
	}
var procesing_restoration = 0;
	function start_resoration(){
		var temporal_description = "";
		if( procesing_restoration == 1 ){
			alert( "Restauracion en proceso!" );
			return false;
		}
	//manda eliminar los triggers
		var delete_triggers = delete_bd_triggers();
		if( ! delete_triggers ){
			return false;
		}
		procesing_restoration = 1;
		$( '#start_restoration_btn' ).css( 'display', 'none' );
		setTimeout( function(){}, 2000 );
		var store_id, restoration_id;
		store_id = $( '#id_suc' ).val();
	//recorre la tabla
		$( '#restoration_actions_list tr' ).each( function( index ){
			if( document.getElementById( 'actions_row_' + index ) ){
				if( $( '#5_' + index ).attr( 'value' ) == '0' ){
					restoration_id = $( '#6_' + index ).attr( 'value' );
					temporal_description  = restoration_id + " - " + $( "#0_" + index ).html();
					var excecution = send_sql_instruction( store_id, restoration_id, index, temporal_description );
					if( ! excecution ){
						return false;
					}
					
				}
			}
		});
	//manda insertar triggers
		var insert_triggers = insert_bd_triggers();
		if( ! insert_triggers ){
			return false;
		}
		alert( "Base restaurada exitosamente!" );
		procesing_restoration = 0;
	}

	function delete_bd_triggers(){
		var url = "ajax/Restoration.php?restoration_fl=delete_triggers";
		var resp = ajaxR( url ).split( '|' );
		if( resp[0] == 'ok' ){
			alert( resp[1] );
			return true;
		}else{
			alert( resp );
			return false;
		}
	}	
	function insert_bd_triggers(){
		var url = "ajax/Restoration.php?restoration_fl=insert_triggers";
		var resp = ajaxR( url ).split( '|' );
		if( resp[0] == 'ok' ){
			alert( resp[1] );
			return true;
		}else{
			alert( resp );
			return false;
		}
	}	

	function send_sql_instruction( store_id, restoration_id, counter, description ){
		/*if( system_type == 'local' ){*/
		$( '#5_' + counter ).removeClass( 'icon-ok-circle' );
		$( '#5_' + counter ).addClass( 'icon-spin6' );
		/*}else if( system_type == 'line' ){
			$( '#4_' + counter ).removeClass( 'icon-toggle-on' );
			$( '#4_' + counter ).addClass( 'icon-spin6' );
		}*/
	//envia peticion por ajax

		var url = "ajax/Restoration.php?restoration_fl=excecute_script&store_id="+ store_id;
		url += "&restoration_id=" + restoration_id;
		alert( "Da click para : " + description );
		//alert( url ); 
		//return false;
		var resp = ajaxR( url );
		if( resp == 'ok' ){
			//alert( restoration_id );
			$( '#5_' + counter ).removeClass( 'icon-spin6' );
			$( '#5_' + counter ).addClass( 'icon-ok-circle text-success' );
			return true;
		}else{
			alert( "Error :\n" + resp );
			return false;
		}
	}

//función que respalda la BD
	function genera_restauracion(){
//		alert('restauración: '+id_sucursal+"|"+tipo_sistema);return false;
	//enviamos datos por ajax
		$.ajax({
			type:'POST',
			url:'restauraBase.php',
			cache:false,
			data:{t_bd:tipo_database,
				t_sys:tipo_sistema,
				id_suc:id_sucursal,
				gpo_mov:agrupar_movimientos,
				el_mov:eliminar_movimientos,
				gpo_vta:agrupar_ventas,
				el_vta:eliminar_ventas,
				fecha:fec_rsp},
			success: function(dat){
				console.log( dat );
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error al procesar la petición, recargue la pantalla y vuelva a intentar!!!\n"+dat);
					
					$("#emergente").css("display","none");
				}else{
					alert("La base de datos fue procesada correctamente!!!");
					location.href="../../../index.php";
				}
			}
		});
	}

//funcion que carga archivo	
	function carga_archivo(){
		
	}
//función que carga combo
	function cambia_combo(obj){
		var tipo=$(obj).val();
		if(tipo==0){
			return true;
		}else{
		//enviamos dato por ajax
			$.ajax({
				type:'post',
				url:'getDatosCombo.php',
				cache:false,
				data:{fl:tipo},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!\n"+dat);
					}else{
						$("#combo_sucs").html(aux[1]);
					}
				}
			});
		}

	}

//lamadas asincronas
	function ajaxR(url){
	    if(window.ActiveXObject){       
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

//función del calendario
	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}