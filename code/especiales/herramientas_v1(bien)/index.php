<?php
	include('../../../conectMin.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Interface</title>
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<link rel="stylesheet" href="css/estilos.css">
<link rel="stylesheet" type="text/css" href="../../../css/gridSW_l.css"/>
<!-- bootstrap -->
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.min.css"/>
<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript" src="../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../js/calendar-setup.js"></script>
</head>
<body>
	<div id="emergente">
		<div id="contenido_emergente">
		</div>
	</div>
	<div class="global">
<!--		<center><span>Consultas Prediseñadas</span></center>-->
		<div id="izquierda">
			<div align="right">

				<!--button class="btn btn-warning">
					◀
				</button-->
			</div>
			<table width="99%">
				<tr class="encabezado">

					<th class="tools" align="center">
						Herramientas
						<button onclick="carga_form(0);" class="add_herr" title="Click para agregar nueva Herramienta"><b>+</b></button>

						<input type="text" class="form-control" onkeyup="search_menu(this, event);" placeholder="Buscador..."/>
					</th>
				</tr>
			<?php
				$sql="SELECT id_herramienta,titulo,descripcion FROM sys_herramientas ORDER BY titulo ASC";
				$eje=mysql_query($sql)or die("Error al consultar las herramientas!!!<br>".mysql_error()."<br>".$sql);
				$cont = 0;
				while($r=mysql_fetch_row($eje)){
					echo '<tr>';
						echo '<td>';
							echo '<button onclick="carga_filtros('.$r[0].',\'busc_prod\');"';
							echo ' id="herramienta_' . $cont . '" class="opc_btn" id="btn_1" title="';
							echo $r[2].'">';
							echo $r[1];
							echo '</button>';
						echo '</td>';
					echo '</tr>';
					$cont ++;
				}

				echo '<input type="hidden" id="contador_herramientas" value="' . $cont . '">';
			?>

			</table>
		</div>
		<div id="derecha">
		<!--Filtros-->
			<div id="filtros">
				<b class="filter">Filtros</b>
			</div>
		<!--contenido-->
			<div id="resultados">
			</div>
			<div id="info_consulta">
		<!--caja de texto donde se muestran las consultas-->
				<textarea class="consulta" id="txt_consulta" disabled onclick="habilitar_txt_consulta();"></textarea>
		<!--botones para modificar y agregar consultas-->
				<table class="btns">
					<tr>
						<td>
							<button id="edita_consulta" onclick="carga_form(-1);" class="btn_opc btn btn-primary"><!-- btn_opc -->
								Editar
							</button>
						</td>
					</tr>
					<tr>	
						<td>
							<button class="btn_opc btn btn-danger">
								Cancelar
							</button>
						</td>
					</tr>
					<tr>
						<td>
							<button class="btn_opc btn btn-success">
								Guardar como<br>Nuevo
							</button>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<form id="TheForm" method="post" action="ajax/genera_consulta.php" target="TheWindow">
			<input type="hidden" name="fl" value="1" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>

</body>
</html>

<script type="text/javascript">
	function carga_consulta(flag,extra){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/consultas.php',
			cache:false,
			data:{fl:flag,extra},
			success:function(dat){

			}
		});
	}

	function carga_filtros(id){
	//enviamos datos por ajax para obtener los filtros
		$.ajax({
			type:'post',
			url:'header_consultas.php',
			cache:false,
			data:{id_herramienta:id},
			success:function(dat){
				var aux=dat.split("___");
				$("#txt_consulta").val(aux[0]);
				$("#filtros").html(aux[1]);
				$("#edita_consulta").attr("onclick","carga_form("+id+");");
				$("#resultados").html("");
			}
		});
	}
	function habilitar_txt_consulta(){
		$("#txt_consulta").removeAttr("disabled");
		//alert();
	}

	function carga_form(id_consulta){
		if(id_consulta==-1){
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/formulario.php',
			cache:false,
			data:{id:id_consulta},
			success:function(dat){
				$("#contenido_emergente").html(dat);
				$("#emergente").css("display","block");
			}
		});
	}

	function guarda(flag){
		var id,tit,cons,desc,filt_suc,filt_fcha1,filt_fcha2,filt_fam,filt_tipo,filt_subtipo,filt_color,
		filt_alm,filt_ext;
		id=$("#id_herramienta").val();
		
		if(flag==0){
			id="(Automático)";
		}

		tit=$("#titulo").val();
		if(tit.length<=0){
			alert("El título no puede ir vacío!!!");
			$("#titulo").focus();
			return false;
		}
		
		cons=$("#consulta").val();
		if(cons.length<=0){
			alert("La consulta no puede ir vacía!!!");
			$("#consulta").focus();
			return false;
		}
		
		desc=$("#descripcion").val();
		if(desc.length<=0){
			alert("La descripción no puede ir vacía!!!");
			$("#descripcion").focus();
			return false;
		}
		
		filt_suc=$("#campo_filtro_sucursal").val();
		filt_fcha1=$("#campo_filtro_fecha_1").val();
		filt_fcha2=$("#campo_filtro_fecha_2").val();
		filt_fam=$("#campo_filtro_familia").val();
		filt_tipo=$("#campo_filtro_tipo").val();
		filt_subtipo=$("#campo_filtro_subtipo").val();
		filt_color=$("#campo_filtro_color").val();
		filt_alm=$("#campo_filtro_almacen").val();
		filt_ext=$("#campo_filtro_es_externo").val();
		//alert(filt_ext);
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/herramientasBD.php',
			cache:false,
			data:{
				fl:'guarda_herramienta',
				id_herramienta:id,
				titulo:tit,
				consulta:cons,
				descripcion:desc,
				campo_filtro_sucursal:filt_suc,
				campo_filtro_fecha_1:filt_fcha1,
				campo_filtro_fecha_2:filt_fcha2,
				campo_filtro_familia:filt_fam,
				campo_filtro_tipo:filt_tipo,
				campo_filtro_subtipo:filt_subtipo,
				campo_filtro_color:filt_color,
				campo_filtro_almacen:filt_alm,
				campo_filtro_es_externo:filt_ext
			},
			success:function(dat){
				//alert(dat);
				$("#contenido_emergente").html('');
				$("#emergente").css('display','none');	
			}
		});

	}

	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}

	function genera_consulta(id_herr){
	//mandamos la emergente
		$("#contenido_emergente").html('<p align="center" class="titulo_emergente">Cargando...<br><img src="../../../img/img_casadelasluces/load.gif"></p>');
		$("#emergente").css("display","block");
	//extraemos los filtros
		var arr_filtros="";
		var filtros_consulta=$("#lista_filtros").val().split("|");
		for(var i=0;i<filtros_consulta.length-1;i++){
			//alert("$('#'"+filtros_consulta[i]+")");
		//extraemos los atributos necesarios
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("campo_filtrar")+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("caracter_cambio")+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).value+"~";
			arr_filtros+=document.getElementById(filtros_consulta[i]).getAttribute("datosDB")+"°";

		}
//		alert(arr_filtros);
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/genera_consulta.php',
			cache:false,
			data:{id:id_herr,arr:arr_filtros},
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!");
					$("#resultados").html(dat);
					$("#contenido_emergente").html();
					$("#emergente").css("display","none");
					return false;
				}
				$("#txt_consulta").val(aux[1]);
				$("#resultados").html(aux[2]);
				$("#contenido_emergente").html();
				$("#emergente").css("display","none");
			}
		});
	}

	function exporta_grid(){
		var tabla,trs,tds;
		var datos="";
	//obtenemos la tabla
		if(!document.getElementById("grid_resultado")){
			alert("Es necesario que escriba una consulta que retorne resutados!!!");
		}
		tabla=document.getElementById("grid_resultado");
	
	//obtenemmos las filas
		trs=tabla.getElementsByTagName('tr');
	//obtenemos encabezado
    	tds=trs[0].getElementsByTagName('th');
		for(var i=0;i<tds.length;i++){
			datos+=tds[i].innerHTML.trim();
			if(i<tds.length-1){
				datos+=",";
			}
		}
		datos+="\n";
	//Obtenemos el cuerpo de la tabla
	    for(var j=1;j<trs.length;j++){	
	    //obtenemos celdas
	        tds=trs[j].getElementsByTagName('td');
	        for(var i=0;i<tds.length;i++){
				datos+=tds[i].innerHTML.trim();
				if(i<tds.length-1){
					datos+=",";//coma delimitadora
				}else if(i==tds.length-1){
					datos+="\n";//salto de línea
				}
			}    
	    }//termina for i
	    //alert(datos);
	//asignamos el valor a la variable del formulario
		$("#datos").val(datos);
	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana,1500);			
	}

	function cierra_pestana(){
		$("#datos").val("");//resteamos variable de datos
		ventana_abierta.close();//cerramos la ventana
	}

/*implementacion Oscar 2021 para menu de herramientas*/
	function search_menu(obj, e){
		var txt = $( obj ).val().toUpperCase().split(' ');
		var size = $('#contador_herramientas').val();
		var ref_comp = txt.length;
		for (var i = 0; i < size; i++) {
			var txt_comp = $('#herramienta_' + i).html().toUpperCase().trim();
			var matches  = 0;
			for (var j = 0;j < ref_comp; j++) {//comparacion de cadena de texto
				txt_comp.includes(txt[j]) ? matches ++ : null;
			}
			$('#herramienta_' + i)
			.css('display', matches ==  ref_comp ? 'block' : 'none');
		}	
	}
	function hide_and_show_section( obj, porc ){

	}
/*fin de cambio Oscar 2021*/

</script>