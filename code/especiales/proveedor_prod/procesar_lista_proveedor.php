<style type="text/css">
#emergente{position: absolute;top:0;left:0;width: 100%;height: 100%;background: rgba(0,0,0,.5);z-index: 2000;}
#cont_emergente{position: absolute;border: 1px solid white;height: 50%;width: 60%;left:20%;top:20%;background: rgba(0,0,0,.4);border-radius: 20px;align-items: center;color:white;}
</style>
<?php
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Preparar Lista de Proveedor</title>
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../../js/papaparse.min.js"></script>
</head>
<body>

<!--emergente-->
	<div id="emergente">
		<div id="cont_emergente">
			<center>
                <br><br><b>DIGITE EL PREFIJO DEL PROVEEDOR</b><br><br>
                    <input type="text" id="prefijo_prov" placeholder="PREFIJO..." style="padding:10px;">
                <br><br><b>SELECCIONAR UN ARCHIVO CSV</b><br><br>
				<button onclick="importarCSV(2)" id="importa_csv_icon">SELECCIONAR<br>CSV</button>
	<form class="form-inline">
    <input type="file" id="files" style="display:none;" accept=".csv" required />
    <span>
			<input type="text" id="txt_info_csv" style="display:none;position:relative;padding: 8px;top:-40;" disabled>
			</span>
    		<button type="submit" id="submit-file" style="display:none;position:relative;top:-35;" class="opc_menu">
				<img src="../../../img/especiales/procesar.png" height="40px;">
				<br>Procesar CSV
    		</button>
	</form>
<!--
	<button onclick="manda_proceso_csv();">Procesar y Descargar</button>
-->
	</center>

		</div>
	</div>
<!--emergente-->

    <form id="TheFormTmp" method="post" action="ajax/prepararCSV.php" target="TheWindow">
            <input type="hidden" id="pref" name="pref" value=""/>
            <input type="hidden" id="datos_tmp" name="datos" value=""/>
    </form>
</body>
</html>
<!--Script´s JavaScript para almacenar e interpretar CSV temporalmente-->
<script type="text/javascript">

//detectamos archivo cargado
$("#files").change(function(){
        var fichero_seleccionado = $(this).val();
        var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
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

function importarCSV(flag){
	if(flag==2){
		$("#files").click();//abrimos explorador de archivos
	}
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

$('#submit-file').on("click",function(e){
    e.preventDefault();
    if($("#prefijo_prov").val()==""){
        alert("El prefijo del proveedor no puede ir vacio!!!");
        $("#prefijo_prov").focus();
        return false;
    }
    $('#files').parse({
        config: {
            delimiter: "auto",
            complete: procesarDatos,
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

var ventana_abierta;
function procesarDatos(results){
    //alert(results);
    var data = results.data;//guardamos en data los valores delarchivo CSV
    var arr="";
    var id_p=$("#id_prov").val();
    for(var i=1;i<data.length;i++){
        //arr+=data[i];
        var row=data[i];
        var cells = row.join(",").split(",");
        for(j=0;j<cells.length;j++){
            arr+=cells[j];
            if(j<cells.length-1){
                arr+=",";
            }
        }
        if(i<data.length-2){
            arr+="|";
        }
    }
//asignamos el prefijo
    $("#pref").val($("#prefijo_prov").val());
//asignamos los datos
    $("#datos_tmp").val(arr);
//enviamos datos al archivo que genera el archivo en Excel
    ventana_abierta=window.open('', 'TheWindow');   
    document.getElementById('TheFormTmp').submit();
    setTimeout(cierra_pestana,1000);    
}

function cierra_pestana(){
    ventana_abierta.close();//cerramos la ventana
    alert("Archivo procesado exitosamente");
    this.close();
}

</script>