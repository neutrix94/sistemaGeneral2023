<?php
	include("../../../conectMin.php");//incluimos archivo de conexion	
?>
<!DOCTYPE html>
<html>
<head>
	<title>consolaAdmin</title>
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
</head>
<body onload="carga();">
	<div id="emergente">
		<div id="contenido_emergente">
			<p align="center"><b style="font-size:40px;color:white;">Cargando...</b><br>
			<img src="../../../img/img_casadelasluces/load.gif"></p>
		</div>
	</div>
	<div id="global">
		<div class="enc">
			<p align="center" style="font-size:20px;">Interface Gráfica de BD</p>
		</div><br><br><br>
		<div>
		<center>
			<br>
			<textarea id="script_consulta" style="background:black;width:90%;height:250px;color:white;padding:10px;font-family: monospace;border:15px solid gray;">
				
			</textarea><br>
			<button onclick="envia_query();" style="padding:10px;">
				Ejecutar
			</button>
			<input type="checkbox" id="enviar_reg_sinc" style="padding:10px;"> Generar registros de Sincronizacion

			<p id="resultado">
				
			</p>
			<button onclick="exporta_grid();">Exportar CSV</button>
		</center>
		<br>
		</div>
	</div>
	<form id="TheForm" method="post" action="ejecuta_consulta.php" target="TheWindow">
			<input type="hidden" name="fl" value="1" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>
</body>
</html>
<style type="text/css">
	#global{position: absolute;width: 100%;height: 100%;top:0;left:0;background-image: url("../../../img/bg8.png");}
	.enc{position:absolute;top:0;width: 100%;left:0;background: rgba(0,0,0,.6);color: white;}
	#resultado{background:rgba(0,0,0,0.3);width:90%;height:300px;color:white;padding:10px;font-family: monospace;border:15px solid white;overflow:scroll;}
	input[type=checkbox]{
		/* Doble-tamaño Checkboxes */
 	 	-ms-transform: scale(2); /* IE */
  		-moz-transform: scale(2); /* FF */
  		-webkit-transform: scale(2); /* Safari y Chrome */
  		-o-transform: scale(2); /* Opera */
  		padding: 10px;
	}
	#emergente{position: fixed;z-index: 10000;background: rgba(0,0,0,.6);width: 100%;height:100%;top:0;left:0;display: none;}
	#contenido_emergente{position: absolute;width: 60%;height: 60%;top:20%;left:20%;background: rgba(0,0,0,.5);border: 1px solid white;
		border-radius:40px;}

</style>

<script type="text/javascript">
	function carga(){
		document.getElementById("script_consulta").focus();
	}

	function envia_query(){
		var gen_reg=0;
		var query=$("#script_consulta").val();
	//obtenemos texto
		if(query.length<=0){
			alert("La consulta no puede ir vacía!!!");
			$("#script_consulta").focus();
			return false;
		}
		$("#emergente").css("display","block");
	//obtenemos el check para generar registros
		if(document.getElementById('enviar_reg_sinc').checked==true){
			gen_reg=1;
		}
	//enviamos datos por ajax
	$.ajax({
		type:'post',
		url:'ejecuta_consulta.php',
		cache:false,
		data:{cadena_original:query,generar_reg:gen_reg},
		success:function(dat){
			var aux=dat.split("|");
			if(aux[0]!='ok'){
				alert("Error\n"+dat);
				$("#resultado").html(dat);
			}else{
				//alert(aux[2]);
//				if(aux[1]=='select'){
					$("#resultado").html(aux[2]);
//				}
			}
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
		
		$("#emergente").css("display","block");

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
		$("#emergente").css("display","none");
	}
</script>