<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
  <div id="campos">  
<div id="titulo">Control de asistencias</div>
<br><br>
{if $tabla eq 'ec_productos'}
	
	{literal}
	
		function cambiaGI(obj)
		{
				
			var of=document.getElementById("porc_iva");
				
			if(!of)
			{
				alert("Error objeto no encontrado");
				return false;
			}
		}	
	{literal}
{/if}
{include file="_footer.tpl" pagetitle="$contentheader"} 