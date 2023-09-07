<style>
	*{margin:0;}
	.principal{background-image: url("../../../img/img_casadelasluces/bg8.jpg");width: 100%;height:100%;padding: 0;border:0;/*1px solid red*/position: absolute;}
	th{color:white;background: rgba(225,0,0,.5);height:40px;}
	#lista_prods{width: 79.5%;border: 1px solid;height: 350px;overflow: scroll;}
	.enc{width: 98%;top:0;position:relative;height: 50px;background: #83B141;padding: 10px;}
	.busqueda{padding:10px;width:30%;border-radius: 5px;}
	#res_busqueda{position:relative;width:35%;border:1px solid;height: 250px;overflow: auto;z-index: 100;background: white;display:none;}
	#lista_de_proveedores{width: 14.5%;
		background: white;color: black;
		top:10px;position:absolute;
		z-index: 1;display: none;
		text-align: left;
		font-size: 15px;
		left:20.8%;
	}
#filtro_proveedor{
	position: absolute;
	left:10%;
	top:15px;
	color: white;
	font-size: 20px;
	z-index: 1001;
}
#res_busc_grid{
	width:20%;
	height:200px;
	position:fixed;
	overflow: auto;
	background: white;
	display:none;
	color: black;
	z-index: 100;
}
#vizualiza_csv{
	display: none;
}
#txt_info_csv{
	padding: 8px;
	border-radius:6px;
	position:relative;
	top:10;
	right: 105px;
	z-index: 2;
}
.opc_menu{
	border-radius:10px;
	color: white;
	background: transparent;
}
.opc_menu:hover{
	background: rgba(0,0,225,.5);
	color: white;
}
.footer{position:absolute;bottom: 0;height: 50px;width: 100%;background:  #83B141;}
#bot_gda{position:relative;right:10%;top:10px;}
#bot_prepara{position:absolute;left:1%;bottom:50%;background: gray;border-radius: 10px;}
.btn_footer{padding:8px;border-radius:10px;text-decoration:none;color:black;border:1px solid gray;background:silver;}
.btn_footer:hover{background:rgba(0,0,0,.5);color:white;}
#emergente{position: absolute;top:0;left:0;width: 100%;height: 100%;background: rgba(0,0,0,.5);z-index: 2000;display: none;}
#cont_emergente{position: absolute;border: 1px solid white;height: 50%;width: 60%;left:20%;top:20%;background: rgba(0,0,0,.4);border-radius: 20px;align-items: center;color:white;
font-size:20px;}
</style>
<?php
	if(!include("../../../conectMin.php")){
		die("Sin archivo de conexión!!!");
	}
//recibimos id_de proveedor
	if(isset($_GET['prv'])){
		$id_proveedor=$_GET['prv'];
	}else{
		$id_proveedor=-1;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Administración Proveedor-Producto</title>
	<script type="text/javascript" src="js/funcionesProvProd.js"></script>
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../../js/papaparse.min.js"></script>
</head>
<body>
<div id="res_busc_grid">
	
</div>
<!--emergente-->
	<div id="emergente">
		<div id="cont_emergente"></div>
	</div>
<!--emergente-->
<div class="principal">
<div class="enc">
<!--	<input type="text" onkeyup="buscador(event,this);" class="busqueda">-->
	<div id="res_busqueda"></div>

		<div id="filtro_proveedor">
		<?php
		//sacamos info del proveedor que esta como activo en el combo
			$sql="SELECT id_proveedor,nombre_comercial FROM ec_proveedor WHERE id_proveedor=$id_proveedor AND id_proveedor>1";
			$eje1=mysql_query($sql)or die("Error al consultar proveedor!!!\n\n".$sql."\n\n".mysql_error());
			$sql="SELECT id_proveedor,nombre_comercial FROM ec_proveedor WHERE id_proveedor!=$id_proveedor  AND id_proveedor>1";
			$eje=mysql_query($sql)or die("Error al consultar proveedores!!!\n\n".$sql."\n\n".mysql_error());
			$c=0;
			echo 'Proveedor: <select style="padding:10px;" id="id_prov" onchange="carga_prov_inicial(this);">';
			if(mysql_num_rows($eje1)==1){
				$prov=mysql_fetch_row($eje1);
				echo '<option value="'.$prov[0].'">'.$prov[1].'</option>';
			}
			echo '<option value="0">--Elija un proveedor--</option>';
			while($r=mysql_fetch_row($eje)){
				$c++;
				//	echo '<input type="checkbox" class="ch" id="pr_'.$c.'" value="'.$r[0].'" checked onclick="check_individual('.$c.');">'.$r[1]."<br>";
				echo '<option value='.$r[0].'>'.$r[1].'</option>';
			}
			echo '</select>';//guardamos número de proveedores existentes
		?>
		<!--Implementación Oscar 15.02.2019 para resetear precios de proveedores-->
			<button style="padding: 10px;" onclick="reseta_precios_prov();">
				Resetear Precios de proveedor
			</button>
		<!--Fin de cambio Oscar 15.02.2019-->
		</div>

<!--Botónes de carga y vizualización-->
	<table width="40%" style="position:fixed;top:0;right: 0;">
<!--formulario de archivo-->
		<tr>
			<td align="right">
				<span align="right" id="importa_csv_icon">

					<button onclick="exportarCSV();" title="Click para seleccionar archivo CSV" class="opc_menu">
					<!--<a href="javascript:importarCSV();" title="Click para seleccionar archivo CSV" style="text-decoration:none;color:black;">-->
						<img src="../../../img/especiales/exportaCSV1.png" height="40px;">
						<br>Exporar
					</button>

					<button onclick="importarCSV(1);" title="Click para seleccionar archivo" class="opc_menu">
					<!--<a href="javascript:importarCSV();" title="Click para seleccionar archivo CSV" style="text-decoration:none;color:black;">-->
						<img src="../../../img/especiales/importaCSV1.png" height="40px;">
						<br>Importar
					</button>
					<!--</a>-->
				</span>
				<form class="form-inline">
    	  			<input type="file" id="files" style="display:none;" accept=".csv" required />
    				<span>
    					<input type="text" id="txt_info_csv" style="display:none;" disabled>
    				</span>
    				<button type="submit" id="submit-file" style="display:none;position:relative;top:-35;" class="opc_menu">
    					<img src="../../../img/especiales/ver.png" height="40px;">
    					<br>Vizualizar CSV
    				</button>
			
				</form>
			</td>
		</tr>
	</table>
					<!--</td>-->
</div>

<!--Encabezado de tabla-->
	<center><br>
		<table style="width:80%;">
			<tr>
				<th width="10%">Orden Lista</th>
				<th width="10%">Código</th>
				<th width="10%">Proveedor</th>
				<th width="20%">Producto</th>
				<th width="20%">Producto Proveedor</th>
				<th width="10%">Prec x pieza</th>
				<th width="10%">Pzas x caja</th>
				<th width="7%">Quitar</th>
				<th width="2%"></th>
			</tr>
		</table>
	<!--aqui recargamos lista-->
		<div id="lista_prods">
			<?php if($id_proveedor!=''||$id_proveedor!=null){include("ajax/getDatos.php");} ?>
		</div>
	</center>

	<p align="right" id="bot_quita_varios" style="right:20%;">
	<button onclick="quitar_sin_empate();" class="opc_menu" style="color:black;position:absolute;right:30%;display:none;">
			<img src="../../../img/especiales/delete.png" height="40px;">
			<br>Quitar sin<br>Empatar
	</button>
	</p>
<!--Botón de guardado-->
	<p align="right" id="bot_gda">
	<button onclick="guarda_grid();" class="opc_menu" style="color:black;position:absolute;right:10%;display:none;">
			<img src="../../../img/especiales/save.png" height="50px;">
			<br>Guardar
	</button>
	</p>
<!--fin de botón de guardado-->	

<!--implementación OScar 05.07.2019 para prepara el archivo CSV-->
	<p align="right" id="bot_prepara">
	<button onclick="link(1);" class="opc_menu">
			<img src="../../../img/especiales/procesar.png" height="60px;">
			<br>Procesar<br>archivo CSV
	</button>
	</p>	
<!--importacion de proveedor-producto-->
	<form>
		<div class="row">

		</div>
		<div class="row">
			<input type="file">
		</div>
		<div class="row">
			<button type="button">Importar</button>
		</div>
		<div class="row">

		</div>
	</form>

<!--pie de página-->	
	<div class="footer">
	<center><br>
		<table width="60%">
			<tr>
				<td width="50%" align="center">
					<a href="../../../index.php" class="btn_footer">
						Regresar al Panel
					</a>
				</td>
				<td width="50%" align="center">
				<a href="proveedor_prod.php?prv=-1" class="btn_footer">
					Reiniciar
				</a>
				</td>
			</tr>
		</table>
	</center>
	</div><!--fin de pie de página-->
</div>
</body>

<!--Script´s JavaScript para almacenar e interpretar CSV temporalmente-->
<script type="text/javascript">

function link(flag){
	if(flag==1){
		window.open('procesar_lista_proveedor.php?');
	}
}

$('#submit-file').on("click",function(e){
    e.preventDefault();
    $('#files').parse({
        config: {
            delimiter: "auto",
            complete: empatarDatos,
        },
        before: function(file, inputElem)
        {
            //console.log("Parsing file...", file);
        },
        error: function(err, file)
        {
            console.log("ERROR:", err, file);
        },
        complete: function()
        {
            //console.log("Done with all files");
        }
    });
});


function empatarDatos(results){
    var data = results.data;//guardamos en data los valores delarchivo CSV
    var arr="";
    var id_p=$("#id_prov").val();
    $("#cont_emergente").html('<p style="font-size:30px;" align="center"><br><b>Cargando...</b><br><img src="../../../img/img_casadelasluces/load.gif" height="60%"></p>');
    $("#emergente").css("display","block");
    for(var i=1;i<data.length;i++){
    	//arr+=data[i];
    	var row=data[i];
    	var cells = row.join(",").split(",");
    	for(j=0;j<cells.length;j++){
            arr+=cells[j];
            if(j<cells.length-1){
            	arr+=",";
            }
        //aqui entra la validacion de que la presentacion por caja no sea igual a 0 
        	if(cells[2]<=0 || cells[2]==''){
        		alert("La presentación por caja debe de ser de mínimo una pieza!!!\nVerifique la línea "+i+" y vuelva a intentar!!!");
        		location.reload();
        	}
        }
        if(i<data.length-2){
        	arr+="|";
        }
    }
//enviamos datos por Ajax
//alert(arr);
    $.ajax({
    	type:'post',
    	url:'ajax/procesaCSV.php',
    	cache:false,
    	data:{datos:arr,fl:2,id_proveedor:id_p},
    	success:function(dat){
    		var ax=dat.split("|");
    		if(ax[0]!="ok"){
    			alert(dat);
    			$("#emergente").css("display","none");
    			return false;
    		}
    		$("#lista_prods").html(ax[1]);
    		$(".opc_menu").css("display","block");
    		$("#bot_gda").css("display","block");
    		$("#bot_quita_varios").css("display","block");
    		$("#emergente").css("display","none");
    	}
    });
}

//detectamos archivo cargado
$("#files").change(function(){
        var fichero_seleccionado = $(this).val();
        var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
       /* if(nombre_fichero_seleccionado==='') {
           $('#delCarta').addClass('invisible');
        } else {
           $('#delCarta').removeClass('invisible'); 
        }*/
        if(nombre_fichero_seleccionado!=""){
        	$("#submit-file").css("display","block");
        	$("#txt_info_csv").val(nombre_fichero_seleccionado);
        	$("#txt_info_csv").css("display","block");
        	$("#importa_csv_icon").css("display","none");
        }else{
        	alert("No se seleccionó ningun Archivo CSV!!!");
        	return false;
        }
    });
</script>
</html>