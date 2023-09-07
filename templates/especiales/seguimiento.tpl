<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
    <div id="campos">
<div id="titulo">Seguimiento</div>
<br><br>

<div id="filtros">
	<form id="form1" name="form1" method="post" action="">
		<table border="0">
        	<tr>
          		<td class="motivo">Llamadas programadas para el d&iacute;a</td>
          		<td>
          			<input name="fecha" type="text" class="barra2" id="fecha1" onfocus="calendario(this)" size="9"/>
          		</td>
          		<td>
            		<input name="button" type="button" class="boton" id="button" value="Buscar"/>
          		</td>	
          	</tr>
    	</table>      	
	</form>
</div>

<div id="bg_seccion">
	<div class="name_module" align="center">
		<p>Clientes/Prospectos</p>		    
	</div>
	<div id="cosa1">
		<br />
		<table align="center">
	    	<tr>
                <td align="center">
					<table id="productos" cellpadding="0" cellspacing="0" Alto="255" conScroll="S" validaNuevo="false" AltoCelda="25"
					auxiliar="0" ruta="../../img/grid/" validaElimina="false" Datos="../ajax/especiales/Seguimiento.php?tipo=1"
					verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
					ordenaPHP="S" title="Listado de Registros">
						<tr class="HeaderCell">
							<td tipo="oculto" width="0" offsetWidth="0">id_cliente</td>
							<td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left">Nombre</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">Tel&eacute;fono</td>
							<td tipo="texto" width="200" offsetWidth="200" modificable="N" align="right">Correo</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">Tel&eacute;fono 2</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">Tel&eacute;fono 3</td>
							<td width="60" offsetWidth="60" tipo="libre" valor="Historial" align="center">
								<img src="{$rooturl}img/vermini.png" height="22" width="22" border="0"  onclick="verPedido('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Ver Registro"/>
							</td>	
						</tr>
					</table>
					<script>	  	
						CargaGrid('productos');
					</script>
				</td>	
			</tr>
		</table>
	</div>	
</div>	
</div>

{include file="_footer.tpl" pagetitle="$contentheader"} 