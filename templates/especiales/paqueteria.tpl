<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
    <div id="campos">
<div id="titulo">Paqueteria</div>
<br><br>

<div id="filtros">
	<form id="form1" name="form1" method="post" action="">
		<table border="0">
        	<tr>
          		<td class="motivo">Cliente</td>
          		<td>
          			<input name="valor" type="text" class="barra2" id="text1"/>
          		</td>
          		<td>&nbsp;</td>
          		<td class="motivo">Folio</td>
          		<td>
          			<input name="mayor" type="text" class="barra2" id="text1" size="10"/>
          		</td>
          		<td class="motivo">Gu&iacute;a</td>
          		<td>
          			<input name="menor" type="text" class="barra2" id="text1" size="10"/>
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
		<p>Productos</p>		    
	</div>
	<div id="cosa1">
		<br />
		<table align="center">
	    	<tr>
                <td align="center">
					<table id="pedidos" cellpadding="0" cellspacing="0" Alto="255" conScroll="S" validaNuevo="false" AltoCelda="25"
					auxiliar="0" ruta="../../img/grid/" validaElimina="false" Datos="../ajax/especiales/Paqueteria.php?tipo=1"
					verFooter="N" guardaEn="False" listado="N" class="tabla_Grid_RC" paginador="N" datosxPag="30" pagMetodo='php'
					ordenaPHP="S" title="Listado de Registros">
						<tr class="HeaderCell">
							<td tipo="oculto" width="0" offsetWidth="0">id_pedido</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">Folio</td>
							<td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left">Cliente</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">Monto</td>
							<td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right">F. pago</td>
							<td tipo="combo" width="120" offsetWidth="120" modificable="S" align="right" datosdb="../ajax/especiales/Paqueteria.php?tipo=2">Gu&iacute;a</td>
							<td tipo="binario" width="80" offsetWidth="80" modificable="S" align="right">Entregado</td>
							<td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
								<img src="{$rooturl}img/vermini.png" height="22" width="22" border="0"  onclick="verPedido('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Ver Registro"/>
							</td>	
						</tr>
					</table>
					<script>	  	
						CargaGrid('pedidos');
					</script>
				</td>	
			</tr>
			<tr>
				<td align="right">
					<input type="button" class="boton" value=" Guardar " onclick="guardaGuias()"/>
				</td>
			</tr>
		</table>
	</div>	
</div>	
</div>

<script>
	{literal}	
	function guardaGuias()
	{
		var num=NumFilas("pedidos");
		for(var i=0;i<num;i++)
		{
			for(var j=(i+1);j<num;j++)
			{
				if(celdaValorXY('pedidos', 5, i) == celdaValorXY('pedidos', 5, j) && celdaValorXY('pedidos', 5, j) != 0)
				{
					alert("Las guías no pueden asignarse a dos pedidos.");
					return false;
				}
			}
		}
		var pedidos="";
		for(var i=0;i<num;i++)
		{
			/*if(celdaValorXY('pedidos', 5, i) != 0 || celdaValorXY('pedidos', 6, i) != 0)
			{*/
				if(pedidos != '')
					pedidos+='|';
				pedidos+=celdaValorXY('pedidos', 0, i)+'~'+celdaValorXY('pedidos', 5, i)+'~'+celdaValorXY('pedidos', 6, i);
			/*}*/
		}		
	

		var aux=ajaxR("../ajax/especiales/Paqueteria.php?tipo=3&pedidos="+pedidos);
		if(aux != 'exito')
		{
			alert(aux);
			return false;
		}

		alert("Se han realizado los cambios exitosamente.");		
		RecargaGrid("pedidos", '');
	}
	{/literal}
</script>



{include file="_footer.tpl" pagetitle="$contentheader"} 