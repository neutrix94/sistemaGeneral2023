<link href="estilo_final1.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
    <div id="campos">
<div id="titulo">Comisiones</div>
<br><br>

<div id="filtros">
	<form id="form1" name="form1" method="post" action="">
		<table border="0">
        	<tr>
        		<td class="motivo">Estatus</td>
        		<td>
        			<select class="barra2" name="estatus">
        				
        			</select>
        		</td>
        		<td>&nbsp;</td>
          		<td class="motivo">Del</td>
          		<td>
          			<input name="fecha1" type="text" class="barra2" id="fecha1" onfocus="calendario(this)" size="9"/>
          		</td>
          		<td>&nbsp;</td>
          		<td class="motivo">Al</td>
          		<td>
          			<input name="fecha2" type="text" class="barra2" id="fecha2" onfocus="calendario(this)" size="9"/>
          		</td>
          		<td>&nbsp;</td>
          		<td>
            		<input name="button" type="button" class="boton" id="button" value="Buscar"/>
          		</td>	
          	</tr>
    	</table>      	
	</form>
</div>

<div id="bg_seccion">
	<div class="name_module" align="center">
		<p>Notas de venta</p>		    
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
							<td tipo="oculto" width="0" offsetWidth="0">id_pedido</td>
							<td tipo="oculto" width="0" offsetWidth="0">id_vendedor</td>
							<td tipo="texto" width="80" offsetWidth="80" modificable="N" align="left">Folio</td>
							<td tipo="texto" width="200" offsetWidth="200" modificable="N" align="left">Cliente</td>
							<td tipo="texto" width="90" offsetWidth="90" modificable="N" align="left">Monto</td>
							<td tipo="texto" width="200" offsetWidth="200" modificable="N" align="left">Vendedor</td>
							<td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
								<img src="{$rooturl}img/vermini.png" height="22" width="22" border="0"  onclick="verPedido('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Ver Registro"/>
							</td>	
							<td tipo="combo" width="120" offsetWidth="120" modificable="N" align="left">Estatus</td>
							<td tipo="texto" width="90" offsetWidth="90" modificable="N" align="left">Comision</td>
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