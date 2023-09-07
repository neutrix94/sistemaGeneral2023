<link href="estilo_final.css" rel="stylesheet" type="text/css" />
<link href="css/demos.css" rel="stylesheet" type="text/css" />

{include file="_header.tpl" pagetitle="$contentheader"}
    
<div id="titulo">Inventario</div>
<br><br>

<div id="filtros">
	<form id="form1" name="form1" method="post" action="">
		<table border="0">
        	<tr>
          		<td class="motivo">Nombre</td>
          		<td>
          			<input name="valor" type="text" class="barra2" id="text1"/>
          		</td>
          		<td>&nbsp;</td>
          		<td class="motivo">C. mayor a</td>
          		<td>
          			<input name="mayor" type="text" class="barra2" id="text1" size="10" />
          		</td>
          		<td class="motivo">C. menor a</td>
          		<td>
          			<input name="menor" type="text" class="barra2" id="text1" size="10" value=0/>
          		</td>
          		<td>&nbsp;</td>
          		{if $multi eq 1}
          			<td class="motivo">Sucursal</td>
          			<td>
          				<select name="sucur">
          					{html_options values=$sucval output=$suctxt}
          				</select>
          			</td>
          		{else}
          			<input type="hidden" name="sucur" value="{$sucursal_id}">	
          		{/if}
          		
          		<td>&nbsp;</td>
          		<td>
            		<input name="button" type="button" class="boton" id="button" value="Buscar" onclick="busca(this.form)"/>
          		</td>	
          	</tr>
    	</table>      	
	</form>
</div>

<div id="bg_seccion">
	<div class="name_module" align="center">
		<p>Productos</p>		    
	</div>
	<div id="cosa">
		<br />
		<table align="center">
	    	<tr>
                <td align="center">
					<table id="productos" cellpadding="0" cellspacing="0" Alto="255" conScroll="S" validaNuevo="false" AltoCelda="25"
					auxiliar="0" ruta="../../img/grid/" validaElimina="false" Datos="../ajax/especiales/Inventario.php?tipo=1&sucur={$sucursal_id}"
					verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
					ordenaPHP="S" title="Listado de Registros">
						<tr class="HeaderCell">
							<td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_producto</td>
							<td tipo="texto" width="350" offsetWidth="350" modificable="N" align="left" campoBD="p.nombres">Nombre</td>
							<td tipo="texto" width="120" offsetWidth="120" modificable="N" align="right" campoBD="cantidad">Cantidad</td>
							<td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
								<img class="vermini" src="{$rooturl}img/vermini.png" height="22" width="22" border="0"  onclick="verProd('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Ver Registro"/>
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


<script>
	{literal}
	
	function busca(f)
	{
		RecargaGrid('productos', '../ajax/especiales/Inventario.php?tipo=1'+"&sucur="+f.sucur.value);
	}
	
	function verProd(pos)
	{
		id=celdaValorXY('productos', 0, pos);
		window.open("../general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfcHJvZHVjdG9z&a1de185b82326ad96dec8ced6dad5fbbd=Mg==&a01773a8a11c5f7314901bdae5825a190="+id+"&bnVtZXJvX3RhYmxh=MA==");
	}
	
	{/literal}
	
</script>


{include file="_footer.tpl" pagetitle="$contentheader"} 