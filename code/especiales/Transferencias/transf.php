<?php
//1. Incluye archivo %/conectMin.php%%
	require('../../../conectMin.php');
	$muestraStatus=0;
?>
<title>Transferencia</title>
<!--2. Incluye hojas de estilos CSS-->
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css2/fontello.css" rel="stylesheet" type="text/css"  media="all" />
<link href="css2/estilo.css" rel="stylesheet" type="text/css"  media="all" />
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
<!-- 3. Incluye archivo %jquery-1.10.2.min.js%% -->
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<!-- 4. Funciones JavaScript-->
<script type="text/javascript">

//4.1. Funcion para generar el calculo de Transferencia por medio del archivo %nuevaTransferencia.php%%
function ejecutar(flag){
//obtenemos valores para enviarlos por ajax
	var sucOrigen=document.getElementById('origen').value;
	var sucDestino=document.getElementById('destino').value;
	var tipo=document.getElementById('id_tipo').value;//obtenemos valor de tipo de transferencia
	var alOrigen="";
	var alDestino=document.getElementById('id_almacen_destino').value;
	var filtrarPor=document.getElementById('filtroPor').value;//obtenemos valor del filtro(mostrar todo o por stock bajo)
//verificamos si es vaciar almacen
	if(tipo==1 || tipo==3){
		//alert('urgente y medio');
		/*if(filtrarPor=='' || filtrarPor=='full'){
			//alert('En este tipo solo se puede seleccionar filtrado por stock bajo!!!');
					$('#filtroPor > option[value="s_b"]').attr('selected',true);
				filtrarPor='s_b';	
		}*/

	}	
	if(tipo==5 || tipo==2){
		/*if(filtrarPor=='s_b' || filtrarPor=='full'){
			//alert('En este tipo solo se puede seleccionar no aplica!!!');
			$('#filtroPor > option[value=""]').attr('selected',true);
			filtrarPor=='';
		}*/
	}
	if(tipo==6){
				alOrigen=document.getElementById('id_almacen_origen').value;
				if(filtrarPor=='s_b' || filtrarPor=='full'){
				//	alert('En este tipo solo se puede seleccionar no aplica!!!');
					$('#filtroPor > option[value=""]').attr('selected',true);
					filtrarPor=='';
				}
			}else{
				alOrigen=document.getElementById('id_almacen_origen').value;
			}
	//alert(tipo);
//validamos datos de sucursales y almacenes	
	if(sucOrigen=="0"){
		alert("Elija sucursal de origen");
		return null;
	}
	if(tipo==4 && filtrarPor=="" || tipo==3 && filtrarPor=="" || tipo==1 && filtrarPor==""){//verificamos que el friltrado no este vacio en los tipos correspondientes
		//alert(tipo);
		alert('selecciona el tipo de filtrado');
		//document.getElementById('id_tipo').value="";
		$('#id_tipo').get(0).selectedIndex = 0;
		document.getElementById('filtroPor').focus();
		return false;
	}
	if(sucDestino=="0"){
		alert("Elija sucursal de Destino");
		document.getElementById('id_tipo').value="";
		return null;
	}
	if(alOrigen==alDestino){//si los almacenes son iguales;
		alert("El almacen de origen y destino deben ser diferentes");//mandamos error
		document.getElementById('id_tipo').value="0";//reseteamos el select tipo
		return null;//retornamos false
		}else{//caso contrario;
//alert(tipo);
			document.getElementById('cargando').style.display='block';//mandamos ventana de carga
			var contCategoria=document.getElementById('cont_fam').value;//obtenemos contador de categorias
			var filtro1;//declaramos filtrado
			if(contCategoria==0){//si no se hicieron filtrados por categoria;
				filtro1='';//dejamos variable de filtrado vacia
			}else{//casocontrario;
				filtro1=document.getElementById('filtroFam').value;//obtenemos condición para filtrar consultapor categoria
				if(filtro1==' AND ()'){
					alert("Debe elegir por lo menos una categoría!!!");
					$('#cargando').css('display','none');
					return false;
				}
			}

		//alert('almacen origen antes de ajax:'+alOrigen);
		//alert(filtro1);return false;
			$.ajax({
				type:'post',
				url:"nuevaTransferencia.php",
				data:{origen:sucOrigen,destino:sucDestino,al_origen:alOrigen,al_destino:alDestino,id_tipo:tipo,filtroFam:filtro1,filtrarPor:filtrarPor},
				success:function(datos){
					$('#general').html(datos);
					document.getElementById("btn_guardar").style.display="block";
					document.getElementById('cargando').style.display='none';//ocultamos ventana de carga
					}
				});
		}
}
//funcion que muesttra todos lo producos o filtra por stock bajo
/*
function mostrarPor(){
//obtenemos valores para generar consulta
	var tipo=document.getElementById('filtroPor').value;//tipo de filtro 
	var sucOrigen=document.getElementById('origen').value;
	var sucDestino=document.getElementById('destino').value;
	var alOrigen=document.getElementById('id_almacen_origen').value;
	var alDestino=document.getElementById('id_almacen_destino').value;
//mandamos datos por ajax
	$.ajax({
		type:"POST",
		url:"nuevaTransferencia.php",
		data:{origen:sucOrigen,destino:sucDestino,},
		succes: function(datos){

		}
	});

}
*/
//4.2. Funcion para obtener almacenes de sucursal por medio del archivo %obtenerAlmacen.php%%
function prueba(flag){					
	var accion=flag;
	if(accion==1){
	var id=document.getElementById('origen').value;
	$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{id_sucursal:id,a:accion},
		success: function(datos){
				$('#almOrigen').html(datos);
			}	
		});
	}else if(accion==2){
	var id=document.getElementById('destino').value;
		//alert(id);
		$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{id_sucursal:id,a:accion},
		success: function(datos){
				$('#almDestino').html(datos);
			}	
		});
		}
	}

var accionCheck=0;
//4.3. Funcion que filtra genera filtro de categorias
function check(cont){//recibimos cont
	if(cont==0){//en caso de ser el checkbox de habilitar/deshabilitar
		//alert('habilitar/deshabilitar');
		var nCat=document.getElementById('numCat').value;//obtenemos numero de sucursales
		var aux="";//declaramos variable auxiliar
		for(var i=0;i<nCat;i++){//creamos for para deshabilitar/habilitar checkbox
			aux='ch_'+parseInt(i+1);//generamos ids
			if(accionCheck==1){
			document.getElementById(aux).checked=true;//habilitamos checkbox
			}else{
			document.getElementById(aux).checked=false;//deshabilitamos checkbox
			}
		}
		if(accionCheck==1){
				accionCheck=0;
		}else{
			accionCheck=1;
		}
		//return false;
	}
	var elem='ch_';//creamos prefijo para id de cada checkbox
	var tamaño=document.getElementById('numCat').value;//obtenemos numero de categorias existentes
	var contAccion=document.getElementById('cont_fam').value;//valuamos si hay acciones
	var valores=new Array();//declaramos arreglo que contendra id de cada categoria
	var sql="";//declaramos variable de consulta
	var consultas=0;//declaramos contador de consultas
	for(var i=0;i<tamaño;i++){//condicionamos for e acuerdo a numero de categorias
		var ch='ch_'+parseInt(i+1);//creamos variable temporal de id
		var clave=document.getElementById(ch).value;//extraemos el id de la categoria
	//generamos condiciones
		if($("#"+ch).is(':checked')){//si el checkbox esta marcado
			if(consultas==0){//si contador de consultas esta en ceros
				valores[i]=" p.id_categoria="+clave+"";//guardamos primera condición
				consultas+=1;//incrementamos contador de consultas
			}else{//de lo contrario
				valores[i]=" or p.id_categoria="+clave+"";//guardamos siguientes condiciones
			}
		}else{//de lo contrario
			valores[i]="";//no afectamos a consulta
		}
	}
	//armamos consulta
	sql=" AND (";
		for(var i=0;i<tamaño;i++){
			sql=sql+valores[i];//asignamos condiciones a variable de consulta
		}
	sql+=")";
		//alert(sql);
		document.getElementById('filtroFam').value=sql;//asignamos nuevo valor a variable de condicion
		document.getElementById('cont_fam').value=1;//marcamos que se ha generado condicion
		//var actual=document.getElementById('filtroFam').value;
		//alert(actual);
}
//4.4. Funcion para obtener combo de sucursales destino
function obtenerCombo(){
	$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{a:3},
		success:function(datos){
			$('#dest').html(datos);
		}
	});

}
</script>

<div class="emergent">
	<button onclick="close_emergent();" class="emergent_btn_close">X</button>
	<div class="emergent_content">
		jkrngfr
	</div>
</div>
										<!--Comienza cuerpo de pantalla-->
<!-- 5. Ventana que muestra mensaje cargando-->
<input type="hidden" value="<?php echo $sucursal_id;?>" id="suc_actual">
<div id='cargando' style="width:100%;height:100%;position:absolute; background:rgba(0,0,0,.5);display:none;">
	<center><br><br><br><br><br><br>
		<p style="border: 0px solid;width:60%;padding:30px;background:rgba(0,255,0,.3);"><font color="white" size="5px"><span id="proceso">Generando Transferencia</font></span><br>
		<img src="img/load.gif" witdh="20%" height="20%"><!--montamos imagen-->
		</p>
	</center>
</div>

<!-- 6. Filtros para generar la transferencia-->
<div id="general" style="text-align:center; color:#CCC; background-image:url(img/bg8.jpg); overflow:scroll; height:90%;border:2px solid;"><!--DIV GENERAL-->
	<div class="encabezado" style="text-align:left;"><!--DIV DE ENCABEZADO-->
		<p style="padding:15px;background:#83B141;">
			<font size="6px"><i class="icon-doc"></i></font>
				<font color="#00000" size="6px">1.15 |	Nueva Transferencia</font></h2>
	</div><!--SE CIERRA DIV DE ENCABEZADO-->
	<center>
	<div class="combos" style="text-align:center; width:95%;border: 2px solid;;border-radius:15px;">
		<form id="transferencias">
			<table width="100%" border="0" cellspacing="10px" style="background:rgba(0,0,0,.3); color:#000000;border-radius:10px;">
				<tr>
	<!-- 6.1. Filtro de Familias (checkbox) -->
					<td colspan="4" width="30%" style="border:1px solid green;background:rgba(0,225,0,.2);border-radius:15px;top:0px;position:relative;">
					<div style="text-align:center;"><font size="5px" color="white">Categorias:</font></div>
						<?php 
							$sql="SELECT id_categoria,nombre FROM ec_categoria where nombre!='' and id_categoria>0";
							$ejecuta=mysql_query($sql);//ejecutamos consulta
							$numCateg=mysql_num_rows($ejecuta);//contamos numero de resultados;
							$cont=0;//inicializamos contador en cero
							while($categ=mysql_fetch_row($ejecuta)){//mientras encuentre resultados
								$cont++;//incrementamos contador
							?>	
							<!--Creamos checkbox-->
								<div style="padding:5px; width:25%;float:left;">
									<input type="checkbox" id="<?php echo 'ch_'.$cont;?>" value="<?php echo $categ[0];?>" 
									onclick="check(<?php echo $cont;?>);" checked style="padding:10px;"><?php echo $categ[1];?>
								</div>
							<?php 
							}//cerramos while
							?>
								<p><input type="checkbox" id='0' value="0" onclick="check(0);" checked>Marcar/Desmarcar</p>
						<!--Asignamos numero de categorias existentes-->
							<input type="hidden" id="numCat" value="<?php echo $numCateg;?>">
					</td>
				</tr>
				<tr>
	<!-- 6.2. Id de transferencia -->
					<td width="10%" align="right" style="padding:10px;">ID</td>
					<td width="20%" align="left">
						<input type="text" class="info" disabled value="(Automatico)">
					</td>
	<!-- 6.3. Sucursal Origen -->
					<td width="15%" align="right" style="padding:10px;">Sucursal De Origen</td>
					<td width="30%" align="left">
						<select id="origen" style="padding:10px; border-radius:10px;" onchange="prueba(1);">
							<?php
							$query="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal>0";
							$extraer=mysql_query($query);
							if($extraer){
							while($row=mysql_fetch_assoc($extraer)){
								//echo 'resultado:<br>';
						?>
								<option value="<?php echo $row['id_sucursal'];?>"><?php echo'<font color="#000099">'.$row['nombre'];?></option>
						<?php
							}//fin de while $extraer
						}else{
							echo 'algo anda mal en la consulta';
						}
						?>
                        <input type="hidden" value="<?php echo $row['id_sucursal'];?>" id="<?php echo $row['id_sucursal'];?>"/>
                        
					</select></td>
				</tr>
				<tr>
	<!-- 6.4. Creada por -->
					<td width="10%" align="right" style="padding:5px;">Creada por</td>
					<td width="20%" align="left">
						<input type="text" class="info" value="<?php echo $user_fullname;?>" disabled>
					</td>
	<!-- 6.5. Almacen Origen -->
					<td width="20%" align="right" style="padding:5px;">Almacen De Origen</td>
                    <td width="30%" align="left">
                <div id="almOrigen"><!--DIV ALMORIGEN-->
                    <select style="padding:10px; border-radius:10px;" id="id_almacen_origen">
						<?php 
							$sql="SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal ='1' order by prioridad";
							$consulta=mysql_query($sql);
							while($almacen=mysql_fetch_row($consulta)){
						?>
								<option value="<?php echo $almacen[0];?>"><?php echo $almacen[1];?></option>
						<?php 
							}//fin de while $consulta
						?>
					</select></td>
				</div><!--FINALIZA DIV ALORIGEN-->
                </tr>
				<tr>
	<!-- 6.6. Fecha -->
					<td width="10%" align="right" style="padding:10px;">Fecha</td>
					<td width="20%" align="left">
						<input type="text" class="info" disabled value="<?php echo date("Y")."-".date("m")."-".date("d");?>"><br />
						<span class="text_legend">yy-mm-dd</span></td>
	<!-- 6.7. Sucursal Destino -->
                    <td width="20%" align="right" style="padding:10px;">Sucursal de Destino</td>
					<td width="30%" align="left">
					<div id="dest">    
                        <select style="padding:10px; border-radius:10px;" id="destino" onclick="obtenerCombo();">
                    	<?php
						$query="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal='".$sucursal_id."'";
						$extraer=mysql_query($query);
						if(!$extraer){
							die("Error!!!\n".mysql_error()."\n".$query);
						}
							while($row=mysql_fetch_assoc($extraer)){
							//echo 'resultado:<br>';
						?>
								<option value="<?php echo $row['id_sucursal'];?>"><?php echo'<font color="#000099">'.$row['nombre'];?></option>
						<?php
							}
						?>          
						</select>
					</div>
					</td>
				</tr>
				<tr>
	<!-- 6.8. Hora -->
					<td width="10%" align="right" style="padding:10px;">Hora</td>
					<td width="20%" align="left">
						<input type="text" class="info" disabled value="<?php echo date("H").":".date("i").":".date("s");?>">
					</td>
	<!-- 6.9. Almacen Destino -->
					<td width="20%" align="right" style="padding:10px;">Almacen De Destino</td>
					<td width="30%" align="left">
                    <div id="almDestino"><!--DIV alacenDestino-->	
                    	<select style="padding:10px; border-radius:10px;" id="id_almacen_destino">
							<?php 
								$sql="SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal ='$sucursal_id' order by prioridad";
								$consulta=mysql_query($sql);
								while($almacen=mysql_fetch_row($consulta)){
							?>
									<option value="<?php echo $almacen[0];?>"><?php echo $almacen[1];?></option>
							<?php 
								}
						?>
					</select></td>
                    </div>
				</tr>
				<tr>
	<!-- 6.10. Folio -->
					<td width="10%" align="right" style="padding:5px;">Folio:</td>
					<td width="20%" align="left">
						<input type="text" class="info" disabled value="(Automático)">
					</td>
	<!-- 6.11. Combo para mostrar todos los productos o por stock-->
					<td align="right">
	<!-- 6.12. Filtro por complemento / normal -->
						<p>Filtrar por: 
					</td>
					<td>
						<SELECT id="filtroPor" style="padding:10px; border-radius:10px;"><!---onchange="ejecutar(1);"-->
							<option value="complemento">Transferencia Diaria</option>
							<option value="s_b">Normal</option>
							<!--<OPTION value="s_b">Stock bajo</OPTION>
							<OPTION value="">-No Aplica-</OPTION>
							<OPTION value="full">Mostrar todos</OPTION>-->
						</SELECT></p>
					</td>
				</tr>
				<tr>
	<!-- 6.13. Status -->
					<td width="10%" align="right" style="padding:10px;">Status</td>
					<td width="20%" align="left">
						<input type="text" class="info" disabled value="No Autorizado">
					</td>
	<!-- 6.14. Tipo de Calculo -->
					<td width="20%" align="right" style="padding:5px;">Tipo</td>
					<td width="30%" align="left">
						<select style="padding:10px; border-radius:10px;" id="id_tipo" onchange="ejecutar(2);">
							<option value="0">-------------</option>
                    		<?php $tipo="SELECT * FROM(
								SELECT 1 AS id_tipo, 'Urgente' AS nombre
								UNION
								SELECT 3 AS id_tipo, 'Medio' AS nombre
								UNION
								SELECT 4 AS id_tipo, 'Completo' AS nombre
								UNION
								SELECT 2 AS id_tipo, 'Manual' AS nombre
								UNION
								SELECT 5 AS id_tipo, 'Libre' AS nombre
								UNION
								SELECT 6 AS id_tipo, 'Vaciar Almacen' AS nombre) AS tipos
								WHERE 1";			
							$obtener=mysql_query($tipo);
							while($tipos=mysql_fetch_assoc($obtener)){
							?>
								<option value="<?php echo $tipos['id_tipo'];?>"><?php echo $tipos['nombre'];?></option>
                    		<?php
							}//cerramos while
							?>
						</select>
					</td>
				</tr>
			</table>
				<input type="hidden" id="cont_fam" value='0'>
				<input type="hidden" id="filtroFam" value=''>
		</form>
	</div><!--SE CIERRA DIV DE FORMULARIO-->
    	</center><br/><center>
    	<div id="listado" style="width:100%; border:0px solid;">
    	</div>
    </center>
<!--7. Busca almacen principal de sucursal de logueo -->
<?php
	$sql="SELECT id_almacen FROM ec_almacen WHERE id_sucursal ='$sucursal_id' order by prioridad";
	$ejecuta=mysql_query($sql);
	if($ejecuta){
		$almacen=mysql_fetch_row($ejecuta);
		//echo 'almacen local: '.$almacen[0];
	}
?>
<!--Declaramos variables ocultas-->
<input type="hidden" value="<?php echo $almacen[0];?>" id="almacen_local">
</div><!--SE CIERRA DIV GENERAL-->
<?php 
//8. Incluye archivo %pieDePagina.php%%
	include('pieDePagina.php');
?>