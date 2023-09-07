<?php
//1. Abre el archivo /conexion_inicial.txt
	$path = "conexion_inicial.txt";
	if(file_exists($path)){
		$file = fopen($path,"r");
		$line=fgets($file);
		fclose($file);
    	$config=explode("<>",$line);
	}

	$archivo_path = "conexion_inicial.txt";
	if(file_exists($archivo_path)){
		//echo 'si';
		$file = fopen($archivo_path,"r");
		$line=fgets($file);
		fclose($file);	
	    $config=explode("<>",$line);
	    $conf_loc=explode("~",$config[0]);
	    $conf_ext=explode("~",$config[1]);
	    $conf_tk=explode("~",$config[2]);

	    $ruta_jar=$config[3];
	    $ruta_pte_imp = $config[4];
	    $impresora = $config[5];
	    $intervalo_imp = $config[6];
	    $retardo_sync = $config[7];
	    $puerto_sync = $config[8];
	    $puerto_imp = $config[9];
	    //$store_id = base64_decode( $conf_ext[5] );
	    $store_id = $config[10];
	    $system_type = $config[11];
	// die($store_id );
	}
//1.1. Implementacion Oscar 2020 para listar rutas de ticket e impresoras
	$impresoras = '';
	$arr_imp = explode("____", $config[5]); 
	$js_printers = "<script type=\"text/JavaScript\">
		var printers = new Array();";
	for ($i=0; $i < sizeof($arr_imp)-1; $i++) { 
		$js_printers .= "printers[{$i}] = new Array();";
		$sub_array_imp = explode("~~", $arr_imp[$i]);
		$impresoras .= '<tr id="imp_'.$i.'" onclick="form_impr( ' . $i . ' );">';
		$impresoras .= '<td width="30%" id="printer_row_1_' . $i . '">'.$sub_array_imp[0].'</td>';
		$js_printers .= "printers[{$i}].push( '{$sub_array_imp[0]}' );";
		$impresoras .= '<td width="20%" id="printer_row_2_' . $i . '">'.$sub_array_imp[1].'</td>';
		$js_printers .= "printers[{$i}].push( '{$sub_array_imp[1]}' );";
		$impresoras .= '<td width="20%" id="printer_row_3_' . $i . '">'.$sub_array_imp[2].'</td>';
		$js_printers .= "printers[{$i}].push( '{$sub_array_imp[2]}' );";
		$impresoras .= '<td width="20%" id="printer_row_4_' . $i . '">'.$sub_array_imp[3].'</td>';
		$js_printers .= "printers[{$i}].push( '{$sub_array_imp[3]}' );";
		$impresoras .= '<td width="20%" id="printer_row_5_' . $i . '">'.$sub_array_imp[4].'</td>';
		$js_printers .= "printers[{$i}].push( '{$sub_array_imp[4]}');";
		$impresoras .= '<td width="10%" id="printer_row_6_' . $i . '" align="center"><button onclick="quita_impresora('.$i.')">X</button></td>';
		$impresoras .= '</tr>';
	}
	$js_printers .= "</script>";
?>
<!DOCTYPE html>
<!-- 2. Estilos CSS -->
<style type="text/css">
	#global{width:100%;height: 100%;position: absolute; top:0;left:0; /*background-image: url("img/backgrounds/general.jpg");*/}
	.entrada{padding: 12px;border-radius: 15px;width: 105%;}
	.descripcion{color:black;font-size: 120%;}
	#impresoras{background: white;}
	th{background: red;padding: 10px; color: white;}
	#emergente{position: fixed; background: rgba(0,0,0,.5);display: none; width: 100%;height: 100%;top:0; left: 0;}
	*{
		font-size: 95%;
	}
</style>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Configuración Inicial</title>

	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/icons/css/fontello.css">
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php
	
//creacion de variables js
		echo "<script type=\"text/JavaScript\">
			var initial_local_host = '" . base64_decode( $conf_loc[0] ) . "';
			var initial_local_route = '" . base64_decode( $conf_loc[1] ) . "';
			var initial_local_db = '" . base64_decode( $conf_loc[2] ) . "';
			var initial_local_user = '" . base64_decode( $conf_loc[3] ) . "';
			var initial_local_pass = '" . base64_decode( $conf_loc[4] ) . "';

			var initial_web_host = '" . base64_decode( $conf_ext[0] ) . "';
			var initial_web_route = '" . base64_decode( $conf_ext[1] ) . "';
			var initial_web_db = '" . base64_decode( $conf_ext[2] ) . "';
			var initial_web_user = '" . base64_decode( $conf_ext[3] ) . "';
			var initial_web_pass = '" . base64_decode( $conf_ext[4] ) . "';

			var initial_system_path = '{$conf_tk[0]}';
			var initial_jar_initial_route = '{$config[3]}';

			var initial_printer_interval = '{$intervalo_imp}';
			var initial_delay_interval = '{$retardo_sync}';
			var initial_sinc_port = '{$puerto_sync}';
			var initial_print_port = '{$puerto_imp}';
			var initial_store_id = '{$store_id}';
			var initial_system_type = '{$system_type}';
		</script>
		{$js_printers}";
?>
	<script>
		$( function() {
			$( "#draggable" ).draggable();
		} );

		/*$( function() {
    		$( "#draggable" ).resizable();
		} );*/
	</script>
</head>
<body>
<!-- 3. Formulario de configuracion -->
	<div id="global">
		<div class="row" style="background-color : white;">
			<div class="col-2"></div>
			<div class="col-8  text-center">
				<h4>Tipo de Sistema</h4>
				<select class="form-control" id="system_type" onchange="change_form_type();">
					<option value="0">-- Seleccionar --</option>
					<option value="general" <?php echo $system_type == 'general' ? 'selected' : ''; ?>>Sistema General Local</option>
					<option value="impresion" <?php echo $system_type == 'impresion' ? 'selected' : ''; ?>>Sistema solo de impresión</option>
				</select>
			</div>
		</div>
		<div class="accordion" id="accordionExample" style="background-color : transparent !important ;">
			<div class="accordion-item"> <!--  style="background-color : transparent !important ;" -->
				<h2 class="accordion-header" id="heading_1_0">
					<button 
						class="accordion-button collapsed" 
						type="button" data-bs-toggle="collapse" 
						data-bs-target="#collapse_1_0" 
						aria-expanded="true" 
						aria-controls="collapse_1_0" 
						onclick=""
						id="herramienta_1_0">
						<i class="icon-database" style="font-size : 120%;">Configuracion de conexiones</i>
					</button>
				</h2>
				<div 
					id="collapse_1_0" 
					class="accordion-collapse collapse description" 
					aria-labelledby="heading_1_0" 
					data-bs-parent="#accordionExample">
					<div class="accordion-body">
						<div class="row">
							<div class="col-6" id="local_host_container">
								<table class="table"><!--  style="position:absolute;top:10%;left:1%;" -->
									<tr>
										<td align="left"><b class="descripcion">Host Local:</b>
											<input type="text" id="host_loc" class="form-control" value="<?php echo base64_decode($conf_loc[0]);?>" placeholder="localhost/ www.dominio...">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Ruta Local:</b>
											<input type="text" id="ruta_loc" class="form-control" value="<?php echo base64_decode($conf_loc[1]);?>" placeholder="carpeta(s) del sistema">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Nombre BD Local:</b>
											<input type="text" id="nombre_bd_loc" value="<?php echo base64_decode($conf_loc[2]);?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Usuario BD Local:</b>
											<input type="text" id="usuario_bd_loc" value="<?php echo base64_decode($conf_loc[3]);?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Password BD Local:</b>
											<input type="text" id="pass_bd_loc" value="<?php echo base64_decode($conf_loc[4]);?>" class="form-control">
										</td>
									</tr>
								</table>
							</div>
							<div class="col-6" id="web_host_container">
								<table class="table" ><!--style="position:absolute;top:10%;left:30%;" -->
									<tr>
										<td align="left">
											<b class="descripcion">Host Linea:
											<input type="text" id="host_lin" class="form-control" value="<?php echo base64_decode($conf_ext[0]);?>" placeholder="localhost/ www.dominio...">
										</td>
									</tr>
									<tr>
										<td align="left">
											<b class="descripcion">Ruta Linea:</b>
											<input type="text" id="ruta_lin" class="form-control" value="<?php echo base64_decode($conf_ext[1]);?>" placeholder="carpeta(s) del sistema">
										</td>
									</tr>
									<tr>
										<td>
											<b class="descripcion">Nombre de la BD Linea: </b>
											<input type="text" id="nombre_bd_lin" value="<?php echo base64_decode($conf_ext[2]);?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td>
											<b class="descripcion">Usuario BD Linea: </b>
											<input type="text" id="usuario_bd_lin" value="<?php echo base64_decode($conf_ext[3]);?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td>
											<b class="descripcion">Password BD Linea: </b>
											<input type="password" id="pass_bd_lin" value="<?php echo base64_decode($conf_ext[4]);?>" class="form-control">
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="accordion-item">
				<h2 class="accordion-header" id="heading_2_0">
					<button 
						class="accordion-button collapsed" 
						type="button" data-bs-toggle="collapse" 
						data-bs-target="#collapse_2_0" 
						aria-expanded="true" 
						aria-controls="collapse_2_0" 
						onclick=""
						id="herramienta_2_0">
							<i class="icon-wrench-outline" style="font-size : 120%;">
								Configuraciones Generales
							</i>
					</button>
				</h2>
				<div 
					id="collapse_2_0" 
					class="accordion-collapse collapse description" 
					aria-labelledby="heading_2_0" 
					data-bs-parent="#accordionExample">
					<div class="accordion-body">
						<div class="row">
							<div class="col-6">
								<table class="table"><!-- style="position:absolute;top:10%;left:65%;" -->
									<tr>
										<td>
											<b class="descripcion">Path Sistema:</b>
											<input type="text" id="ruta_ticket_origen" value="<?php echo $conf_tk[0];?>" class="form-control">
										</td>
									</tr>
									<tr>
								<!-- deshabilitado por  Oscar 2023 -->
										<td style="display: none;">
											<b class="descripcion">Ruta destino ticket: </b>
											<input type="text" id="ruta_ticket_destino" value="<?php echo $conf_tk[1];?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td>
											<b class="descripcion">Ruta de archivo jar: </b>
											<input type="text" id="ruta_archivo_jar" value="<?php echo $ruta_jar;?>" class="form-control">
										</td>
									</tr>
									<!--tr>
										<td>
											<b class="descripcion">Comando impresion: </b>
											<textarea id="ruta_puente_impresion" class="entrada"><?php //echo $ruta_pte_imp;?></textarea>
										</td>
									</tr>	
									<tr>
										<td colspan="2" align="center"><br><br>
											<button onclick="genera_config();">Crear Configuracion</button>
										</td>
									</tr>-->
								</table>
							</div>
							<div class="col-6">

							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="accordion-item">
				<h2 class="accordion-header" id="heading_3_0">
					<button 
						class="accordion-button collapsed" 
						type="button" data-bs-toggle="collapse" 
						data-bs-target="#collapse_3_0" 
						aria-expanded="true" 
						aria-controls="collapse_3_0" 
						onclick=""
						id="herramienta_3_0">
							<i class="icon-spin3" style="font-size : 120%;">
								Configuracion de impresiones y Sincronización
							</i>
					</button>
				</h2>
				<div 
					id="collapse_3_0" 
					class="accordion-collapse collapse description" 
					aria-labelledby="heading_3_0" 
					data-bs-parent="#accordionExample">
					<div class="accordion-body">
						<div class="row">
							<div class="col-6">
								<table class="table">
									<tr>
										<td>
											<b class="descripcion">Intervalo Impresion <br>(milesimas seg): </b>
											<input type="number" id="intervalo_imp" value="<?php echo $intervalo_imp;?>" class="form-control">
										</td>
									</tr>	
									<tr>
										<td>
											<b class="descripcion">Retraso sincronizacion <br>(milesimas seg): </b>
											<input type="number" id="retraso_sinc" value="<?php echo $retardo_sync;?>" class="form-control">
										</td>
									</tr>	
								</table>
							</div>
							<div class="col-6">
								<table class="table">
									<tr>
										<td><b class="descripcion">Puerto Sincronizacion: </b>
											<input type="number" id="puerto_sinc" value="<?php echo $puerto_sync;?>" class="form-control" placeholder="recomendado => 1335">
										</td>
									</tr>	

									<tr>
										<td>
											<b class="descripcion">Puerto Impresion: </b>
											<input type="number" id="puerto_imp" value="<?php echo $puerto_imp;?>" class="form-control" placeholder="recomendado => 1336">
										</td>
									</tr>	


									<tr>
										<td>
											<b class="descripcion">Id Sucursal: </b>
											<input type="number" id="store_id" value="<?php echo $store_id;?>" class="form-control" placeholder="">
										</td>
									</tr>
								</table>
							</div>
							<div class="col-12">
								<table class="table table-bordered" id="impresoras"><!-- style="position:absolute;top:60%; width: 500px; left:5%;" -->
									<tr>
										<th style="background : red;">Ruta de Archivos</th>
										<th style="background : red;">Nombre Impresora</th>
										<th style="background : red;">Comando de impresión</th>
										<th style="background : red;">Extension Archivos</th>
										<th style="background : red;">Lenguaje</th>
										<th style="background : red;">X</th>
									</tr>
									<?php echo $impresoras;?>

									<tr>
										<td colspan="6" align="center">
											<button onclick="form_impr();" class="btn btn-warning">Agregar Impresora</button>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>

	<button 
		class="form-control btn btn-success"
		onclick="genera_config();"
		style="position:absolute;top:90%;right:5%; width : 90%;padding:10px;"
	><!--  -->
		<b>
			Crear Configuracion
		</b>
	</button>
	
	<div id="emergente"></div>
</body>
</html>
<!-- 4. Funciones JavaScript -->
<script type="text/javascript">
 
 //4.1. Funcion para generar los archivos de configuracion por medio del archivo code/ajax/conf_inicial.php
	function genera_config(){
		var system_type = $( '#system_type' ).val();
		//alert( system_type );
		if( system_type == 0 ){
			alert( "Debes de Seleccionar un tipo de sistema valido para continuar!" );
			$( '#system_type' ).focus();
			return false;
		}
		if( ! validate_fields() ){
			return false;
		}
//recolectamos los datos de la configuración local
		var h_l=$("#host_loc").val();
		if( h_l.length <=0 && system_type == 'general' ){
			alert("El campo de Host local no puede ir vacío!!!");
			$("#host_loc").focus();
			return false;
		}
		var r_l=$("#ruta_loc").val();
		if( r_l.length<=0  && system_type == 'general' ){
			alert("El campo de Ruta Local no puede ir vacío!!!");
			$("#ruta_loc").focus();
			return false;
		}
		var n_bd_l=$("#nombre_bd_loc").val();
		if( n_bd_l.length<=0  && system_type == 'general' ){
			alert("El campo de Nombre de Base de datos no puede ir vacío!!!");
			$("#nombre_bd_loc").focus();
			return false;
		}
		var u_l=$("#usuario_bd_loc").val();
		if( u_l.length<=0  && system_type == 'general' ){
			alert("El usuario de Base de Datos no puede ir vacío!!!");
			$("#usuario_bd_loc").focus();
			return false;
		}
		var p_l=$("#pass_bd_loc").val();

//recolectamos los datos de la configuración de bd linea
		var h_lin=$("#host_lin").val();
		if(h_lin.length<=0){
			alert("El campo de Host linea no puede ir vacío!!!");
			$("#host_lin").focus();
			return false;
		}
		var r_lin=$("#ruta_lin").val();
		if(r_lin.length<=0){
			alert("El campo de Ruta Local no puede ir vacío!!!");
			$("#ruta_lin").focus();
			return false;
		}
		var n_bd_lin=$("#nombre_bd_lin").val();
		if(n_bd_lin.length<=0){
			alert("El campo de Nombre de Base de datos no puede ir vacío!!!");
			$("#nombre_bd_lin").focus();
			return false;
		}
		var u_lin=$("#usuario_bd_lin").val();
		if(u_lin.length<=0){
			alert("El usuario de Base de Datos no puede ir vacío!!!");
			$("#usuario_bd_lin").focus();
			return false;
		}
		var p_lin=$("#pass_bd_lin").val();

		var ru_t_or=$("#ruta_ticket_origen").val();
		if(ru_t_or.length<=0){
			alert("La ruta de origen del ticket no puede ir vacía!!!");
			$("#ruta_ticket_origen").focus();
			return false;
		} 
		var ru_t_des=$("#ruta_ticket_destino").val();
		/*if(ru_t_des.length<=0){
			alert("La ruta de destino del ticket no puede ir vacía!!!");
			$("#ruta_ticket_destino").focus();
			return false;
		}*/ 
		var ru_jar=$("#ruta_archivo_jar").val();
		if(ru_jar.length<=0 && system_type == 'general' ){
			alert("La ruta del archivo jar no puede ir vacía!!!");
			$("#ruta_archivo_jar").focus();
			return false;
		} 

		var pte_imp = $("#ruta_puente_impresion").val();
		
		var int_imp = $("#intervalo_imp").val();


		var imp = cadena_impresoras();

		var retraso_sincronizacion = $("#retraso_sinc").val(); 
		if(retraso_sincronizacion.length<=0 && system_type == 'general'){
			alert("El retraso de ejecucion del sistema de sincronización no puede ir vacio!!!");
			$("#retraso_sinc").focus();
			return false;
		} 

		var puerto_sincronizacion = $("#puerto_sinc").val(); 
		if(puerto_sincronizacion.length<=0 && system_type == 'general' ){
			alert("El puerto del sistema de sincronizacion no puede ir vacio!!!");
			$("#puerto_sinc").focus();
			return false;
		} 

		var puerto_impresion = $("#puerto_imp").val(); 
		if(puerto_impresion.length<=0){
			alert("El puerto del sistema de impresion no puede ir vacio!!!");
			$("#puerto_imp").focus();
			return false;
		} 

		var store_id =$( '#store_id' ).val();
		if( store_id == '' || isNaN(store_id)  ){
			alert("El id de la sucursal no puede ir vacio y debe de ser un valor numérico!!!");
			$("#store_id").focus();
			return false;
		}
	//	alert( store_id );
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'code/ajax/conf_inicial.php',
			cache:false,
			data:{
				host_local:h_l,
				ruta_local:r_l,
				nombre_local:n_bd_l,
				usuario_local:u_l,
				pass_local:p_l,
				host_linea:h_lin,
				ruta_linea:r_lin,
				nombre_linea:n_bd_lin,
				usuario_linea:u_lin,
				pass_linea:p_lin,
				ru_or : ru_t_or,
				ru_des : ru_t_des,
				archivo_jar:ru_jar,
				impresion : pte_imp,
				impresora : imp,
				intervalo_impresion : int_imp,
				retraso_sis_sinc : retraso_sincronizacion,
				puerto_sis_sinc : puerto_sincronizacion,
				puerto_sis_imp : puerto_impresion,
				store_id : store_id,
				system_type : $( '#system_type' ).val()

			},
			success:function(dat){
				if(dat!='ok'){
					alert("Error, actualice la pantalla y vuelva a intentar!!!"+dat);
					return false;
				}else{
					alert("La configuración fue guardada exitosamente!!!");
					location.href='index.php?';
				}
			}
		});
	}

//4.2. Funcion para eliminar impresora
	function quita_impresora(id){
		if(!confirm("Realmente desea eliminar la impresora?")){
			return false;
		}
		$("#imp_" + id).remove();
	}

//4.3. Funcion para formulario de impresora
	function form_impr( counter = null ){

		var cont_emerg = `<table class="table" style="position:absolute;width:50%;left:25%;top:25%; background:white;">
							<thead>
								<tr>
									<th class="text-center" colspan="2" style="background : red;">Agregar / Modificar impresora</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-end" width="25%"> Ruta de ticket : </td>
									<td>
										<textarea id="valor_ruta" style="width:100%;"></textarea>
									</td>
								</tr>
								<tr>
									<td class="text-end" width="25%"> Nombre de Impresora : </td>
									<td><textarea id="valor_impresora" style="width:100%;"></textarea></td>
								</tr>
								<tr>
									<td class="text-end" width="25%"> Comando de Impresion : </td>
									<td>
										<textarea id="comando_impresora" style="width:100%;"></textarea>
									</td>
								</tr>
								<tr>
									<td class="text-end" width="25%"> Extension de Archivos </td>
									<td>
										<select id="extension_archivos_impresora" style="width:100%;" class="form-control">
											<option value=".pdf">PDF</option>
											<option value=".txt">TXT</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="text-end" width="25%"> Lenguaje Adicional </td>
									<td>
										<select id="lenguaje_adicional_impresora" style="width:100%;" class="form-control">
											<option value="No Aplica">No Aplica</option>
											<option value="ZPL">ZPL</option>
											<option value="EPL">EPL</option>
										</select>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2" class="text-center">
										<div class="row">
											<div class="col-6 text-center">
												<button 
													class="btn btn-success"
													onclick="agrega_impresora();">
													<i class="icon-ok-circle">Guardar</i>
												</button>
											</div>
											<div class="col-6 text-center">
												<button 
													class="btn btn-danger"
													onclick="close_emergent();">
													<i class="icon-cancel-circled">Cancelar</i>
												</button>
											</div>
										</div>
									</td>
								</tr>
							</tfoot>
						</table>`;
		$("#emergente").html(cont_emerg);
		$("#emergente").css("display", "block");

		setTimeout( function (){
			if( counter != null ){
				var route, printer_name, command, file_type, printer_language;
				route = $( '#printer_row_1_' + counter ).html().trim();
				printer_name = $( '#printer_row_2_' + counter ).html().trim();
				command = $( '#printer_row_3_' + counter ).html().trim();
				file_type = $( '#printer_row_4_' + counter ).html().trim();
				printer_language = $( '#printer_row_5_' + counter ).html().trim();
				$( '#valor_ruta' ).val( route );
				$( '#valor_impresora' ).val( printer_name );
				$( '#comando_impresora' ).val( command );
				$( '#extension_archivos_impresora' ).val( file_type );
				$( '#lenguaje_adicional_impresora' ).val( printer_language );
			}
		}, 300 );
	}

	function close_emergent(){
		$("#emergente").html( '' );
		$("#emergente").css("display", "none");
	}

//4.4. Funcion para agregar impresora
	function agrega_impresora(ruta, impresion){
		var cont = $("#impresoras tr").length - 1;
		var ruta = $("#valor_ruta").val();
		var impresora = $("#valor_impresora").val();
		var command_printer = $( '#comando_impresora' ).val();
		var file_type = $( '#extension_archivos_impresora' ).val();
		var printer_language = $( '#lenguaje_adicional_impresora' ).val();
		$('#impresoras tr:last').before(`<tr id="imp_${cont}">
											<td>${ruta}</td>
											<td>${impresora}</td>
											<td>${command_printer}</td>
											<td>${file_type}</td>
											<td>${printer_language}</td>
											<td width="10%" align="center">
												<button onclick="quita_impresora( ${cont} )">X</button>
											</td>
										</tr>`);
		$("#emergente").html("");
		$("#emergente").css("display", "none");
	}

//4.5. Funcion para recopilar info impresiones
	function cadena_impresoras(){
		var cadena = '';
		var cont =0;
		var tabla=document.getElementById('impresoras');
		trs=tabla.getElementsByTagName('tr');
		for(i=0;i<trs.length-2;i++)
		{
			//if(){
				tds=trs[i+1].getElementsByTagName('td');
            	//var objIn=tds[12].getElementsByTagName('input');
				cadena += $(tds[0]).html()+ "~~" + $(tds[1]).html()+ "~~" + $(tds[2]).html()+ "~~" + $(tds[3]).html()+ "~~" + $(tds[4]).html() + "____";
				//cadena += $(tds[1]).html()+ "||";
			//}
		}
		return cadena;
	}

	function change_form_type(){
		var system_type = $( '#system_type' ).val();
		if( system_type == 'general' ){
		//	$( '#local_host_container' ).css( 'display', 'flex' );
		//	$( '#web_host_container' ).css( 'display', 'flex' );

			$( '#host_loc' ).val( initial_local_host );
			$( '#host_loc' ).removeAttr( 'disabled' );
			$( '#ruta_loc' ).val( initial_local_route );
			$( '#ruta_loc' ).removeAttr( 'disabled' );
			$( '#nombre_bd_loc' ).val( initial_local_db );
			$( '#nombre_bd_loc' ).removeAttr( 'disabled' );
			$( '#usuario_bd_loc' ).val( initial_local_user );
			$( '#usuario_bd_loc' ).removeAttr( 'disabled' );
			$( '#pass_bd_loc' ).val( initial_local_pass );
			$( '#pass_bd_loc' ).removeAttr( 'disabled' );

			$( '#ruta_archivo_jar' ).removeAttr( 'disabled' );
			$( '#ruta_archivo_jar' ).val( initial_jar_initial_route );
			$( '#retraso_sinc' ).removeAttr( 'disabled' );
			$( '#retraso_sinc' ).val( initial_delay_interval );
			$( '#puerto_sinc' ).removeAttr( 'disabled' );
			$( '#puerto_sinc' ).val( initial_sinc_port );
			

			$( '#global' ).css( 'background-image', `url( 'img/backgrounds/general.jpg' )` );
		}else if( system_type == 'impresion' ){
		//	$( '#local_host_container' ).css( 'display', 'none' );
			$( '#host_loc' ).val( '' );
			$( '#host_loc' ).attr( 'disabled', true );
			$( '#ruta_loc' ).val( '' );
			$( '#ruta_loc' ).attr( 'disabled', true );
			$( '#nombre_bd_loc' ).val( '' );
			$( '#nombre_bd_loc' ).attr( 'disabled', true );
			$( '#usuario_bd_loc' ).val( '' );
			$( '#usuario_bd_loc' ).attr( 'disabled', true );
			$( '#pass_bd_loc' ).val( '' );
			$( '#pass_bd_loc' ).attr( 'disabled', true );

			$( '#pass_bd_loc' ).val( '' );
			$( '#pass_bd_loc' ).attr( 'disabled', true );

			$( '#ruta_archivo_jar' ).val( '' );
			$( '#ruta_archivo_jar' ).attr( 'disabled', true );
			$( '#retraso_sinc' ).val( '' );
			$( '#retraso_sinc' ).attr( 'disabled', true );
			$( '#puerto_sinc' ).val( '' );
			$( '#puerto_sinc' ).attr( 'disabled', true );

			$( '#global' ).css( 'background-image', 'url( \'img/backgrounds/just_print.jpg\' )' );
		}else{
		}
	} 


	function validate_fields(){
		var system_type = $( '#system_type' ).val();
		if( system_type == 'general' ){
			var local_host = $( '#host_loc' ).val();
			var web_host = $( '#host_lin' ).val(); 
			if( local_host == web_host && local_host != "" ){
				alert( "El host local y el host en linea no pueden ser iguales!" );
				$( '#host_lin' ).select();
				//return false;
			}

			var local_db_name = $( '#nombre_bd_loc' ).val();
			var web_db_name = $( '#nombre_bd_lin' ).val(); 
			if( local_db_name == web_db_name && local_db_name != "" ){
				alert( "La base de datos local y la base de datos en linea no pueden ser iguales!" );
				$( '#host_lin' ).select();
				//return false;
			}

			var local_user = $( '#usuario_bd_loc' ).val();
			var web_user = $( '#usuario_bd_lin' ).val(); 
			if( local_user == web_user && local_user != "" ){
				alert( "El host local y el host en linea no pueden ser iguales!" );
				$( '#usuario_bd_loc' ).select();
				//return false;
			}

			var local_password = $( '#usuario_bd_loc' ).val();
			var web_password = $( '#usuario_bd_lin' ).val(); 
			if( local_password == web_password && web_password != "" ){
				alert( "El host local y el host en linea no pueden ser iguales!" );
				$( '#usuario_bd_loc' ).select();
				//return false;
			}
		}else if( system_type == 'impresion' ){
		}
		return true;
	}

</script>

<script type="text/javascript">
	change_form_type();
</script>
<style type="text/css">
	@media only screen and (max-width: 600px) {
		* {
			font-size : 50% !important;
		}
	}
</style>