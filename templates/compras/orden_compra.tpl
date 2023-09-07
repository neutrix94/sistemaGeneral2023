{include file="_header.tpl" pagetitle="$contentheader"}


 <div id="titulo">Orden de compra</div>
  <div id="bg_seccion">
    <div class="name_module" align="center">
      <p>Orden</p>
      
    </div>
  <div id="cosa" align="center">
  
  <table id="pedidos" cellpadding="0" cellspacing="0" border="1" Alto="250" conScroll="S" validaNuevo="false" AltoCelda="25" auxiliar="0"
		 ruta="../../img/grid/" validaElimina="false" Datos="" verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
		 ordenaPHP="S" title="Listado de Registros">
			<tr class="HeaderCell">
				<td width="200">Producto</td>
				<td width="100">Unidad</td>
				<td width="120">Cantidad</td>
			</tr>
		</table>
		
		<script>
			
			
			
			CargaGrid('pedidos');
			
		</script>
  
  </div>
  </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  

{include file="_footer.tpl" pagetitle="$contentheader"}