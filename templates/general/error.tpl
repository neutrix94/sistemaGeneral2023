{include file="_header.tpl" pagetitle="$contentheader"}

	<div id="titulo">Error {$no}_{$nombre}</div>
	
	
    <div id="texto">
		<table width="865" border="0">
			<tr>
    			<td width="200" height="54" valign="top" class="motivo">Descripción:</td>
    			<td width="65" valign="top" class="detalle">{$descripcion}</td>
  			</tr>
  			<tr>
				<td height="67" valign="top" class="motivo">Consulta:</td>
				<td valign="top"><span class="detalle">{$consulta}</span></td>
			</tr>
  			<tr>
    			<td height="61" valign="top" class="motivo"> Archivo que generó el error:</td>
    			<td valign="top"><span class="detalle">{$archivo}</span></td>
  			</tr>
		</table>   
  </div>

{include file="_footer.tpl" pagetitle="$contentheader"}