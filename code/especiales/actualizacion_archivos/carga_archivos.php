<?php

?>
<!DOCTYPE html>
<html>
<head>
	<title>Actualización de Archivos</title>
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<style type="text/css">
	#global{position:absolute;margin: 0;top:0;left:0;width: 100%;height:100%;background-image: url("../../../img/img_casadelasluces/bg8.jpg"); }
	#contenido{width: 72.5%;height: 420px;border:1px solid;overflow: scroll;}
	#enc{position: absolute;top:0;background: rgba(0,0,0,.5);width: 100%;height: 55px;left:0; color: white;}
	#footer{position: absolute;bottom:0;background: rgba(0,0,0,.5);width: 100%;height: 55px;left:0;}
	th{position:fixed;padding:15px;background: rgba(0,0,0,.8);color: white;top:70px;}
	td{padding: 8px;}
	#datos{width:99.9%;height:100px;overflow: scroll;}
	.color_1{background: #708090;}
	.color_2{background: #808080;}
	.entrada_txt{padding: 8px;width: 90%;font-family:monospace;}
	.archivo{display: none;}
	.bot_add{position: fixed;top:30%;left:6%;}
</style>
</head>
<body><br>
	<div id="global">
	<div id="enc">
		<p style="font-size:25px;"> 6.20 Sincronización de Archivos</p>
	</div>
	<center><br><br><br><br>
	<div id="contenido"><!--Div contenido-->
	<form action="ajax/sube_ficheros.php" method="POST" name="f1" enctype="multipart/form-data"><!--Inicio de formulario-->
		<br><table id="datos">
			<tr>
				<th width="31.3%" style="left: 14%;">Ruta</th>
				<th width="32.2%" style="left: 47.3%;" colspan="2">Archivo</th>
				<th width="1.2%" style="left: 81.6%;">X</th>
			</tr>
			<tr id="fila_1" class="color_1">
				<td width="50%" align="center">
					<input type="text" id="ruta_1" placeholder="Ruta destino" name="ruta_destino[]" class="entrada_txt">
				</td>
				<td width="20%" align="center">
					<input type="button" id="abre_1" value="Seleccionar archivo" onclick="selecciona_archivo(1);">
					<input type="file" id="archivo_1" name="archivo[]" placeholder="" class="archivo" onchange="muestra_nombre(1);">					
				</td>
				<td width="30%" align="center">
					<input type="text" id="info_1" placeholder="" class="entrada_txt">					
				</td>
				<td width="10%" align="center">
					<img src="../../../img/especiales/menos.png" width="30px" title="Quitar">	
				</td>
			</tr>	
		</table>
	</form><!--Fin de formulario-->
	</div><!--Fin de Div contenido-->
<!--Contador de formulario-->
	<input type="hidden" id="filas_cont" value="1">
	</center>
	<button type="button" onclick="nuevaFila();" class="bot_add">
			<img src="../../../img/especiales/add_fila.png" width="50px"><br>Agregar<br>Fila
	</button>
	<br>
	<button type="button" onclick="valida();" style="position: absolute;right:10%;">
			<img src="../../../img/especiales/subir_archivo.jpg" width="50px"><br>Subir<br>Archivos
	</button>	
	</div>
	<div id="footer">
		<p align="center">
			<button type="button" style="padding: 8px;" onclick="link(1);">Regresar al Panel</button>
		</p>
	</div>
</body>
</html>
<script type="text/javascript">

	var no_filas=1;
	
	function selecciona_archivo(num){
		$("#archivo_"+num).click();
	}
	
	function nuevaFila(){
	//sacamos el valor de contador
		no_filas=parseInt($("#filas_cont").val());
		no_filas+=1;
	//ebviamos datos por ajax
		$.ajax({
			type:'POST',
			url:'ajax/fila.php',
			cache:false,
			data:{contador:no_filas},
			success:function(dat){
				$("#datos").append(dat);
				$("#filas_cont").val(no_filas);//incrementamos el contador de filas
			}
		});
	}
	
	function valida(){
		for(var i=1;i<=no_filas;i++){
			if(document.getElementById('fila_'+i)){
			//detectamos si la fila no se uso
				if($("#archivo_"+i).val()=="" && $("#ruta_"+i).val()==""){
					$("#fila_"+i).remove();
				}
			//detectamos si falta el nombre 
				if($("#archivo_"+i).val()!="" && $("#ruta_"+i).val()==""){
					alert("La ruta no puede ir vacía!!!");
					$("#ruta_"+i).focus();
					return false;
				}
			//detectamos si falta el archivo 
				if($("#archivo_"+i).val()=="" && $("#ruta_"+i).val()!=""){
					alert("Debe seleccionar un archivo!!!");
					$("#ruta_"+i).select();
					return false;
				}
			}
		}//fin de for i
		document.f1.submit();	
	}

	function muestra_nombre(num){
		$("#info_"+num).val(document.getElementById("archivo_"+num).files[0].name);
		//alert(this.files[0].mozFullPath);
	}
	function link(flag){
		if(flag==1){
			if(!confirm("Realmente desea Salir sin guardar???")){
				return false;
			}else{
				location.href="../../../index.php?";
				return false;
			}
		}
	}
</script>