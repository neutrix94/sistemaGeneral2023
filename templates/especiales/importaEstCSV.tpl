<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
  <div id="campos">  
<div id="titulo">Importa CSV - {$nombreLista}</div>
<br><br>

<div id="filtros">
	<form id="form1" name="form1" method="post" action="importaEstCSV.php" enctype="multipart/form-data">
	<input type="hidden" name="procesa" value="SI">
	<input type="hidden" name="id_estacionalidad" value="{$id_estacionalidad}">
		<span class="mensaje">{$mensaje}<span>
		<table border="0">
        	<tr>
          		<td class="motivo">Archivo</td>
          		<td>
          			<input name="archivo" type="file" class="barra2" id="text1"/>
          		</td>
          		
          		
          		<td>&nbsp;</td>
          		<td>
            		<input name="button" type="button" class="boton" id="button" value="Importar" onclick="sube(this.form)"/>
          		</td>	
          	</tr>
    	</table>      	
	</form>
</div>

	
</div>

<script>
	{literal}
	
	
	function sube(f)
	{
		if(f.archivo.value == '')
		{
			alert("Es necesario que seleccione un archivo");
			return false;
		}
		
		f.submit();
		
	}
	
	
	{/literal}
	
</script>


{include file="_footer.tpl" pagetitle="$contentheader"} 