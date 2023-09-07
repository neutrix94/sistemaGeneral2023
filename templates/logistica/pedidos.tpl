{include file="_header.tpl" pagetitle="$contentheader"}

<div id="titulo">Pedidos</div>
     
  	
    
    
    <div class="redondo" align="center" ><table width="772" border="0" >
  <tr>
    <td width="69" height="42" class="texto_form">No. Pedido</td>
    <td width="175"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
    <td width="158">&nbsp;</td>
    <td width="155" class="texto_form">Cliente</td>
    <td width="193"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input  class="barra" type="text" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="37" class="texto_form">Hecha por</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input  class="barra" type="text" name="text1" id="text1" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">Fecha de alta</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text" class="barra" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="40" class="texto_form">Hora</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra"name="text1" id="text1" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">Monto</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
</table>

    </div>
  
  <div id="bg_seccion">
    <div class="name_module" align="center">Detalle de productos</div>
  <div id="cosa"><table width="801" height="154" border="0">
  <tr>
    <td width="396" height="47" align="center" valign="middle">En el siguiente apartado puedes consultar y agregar productos.</td>
    <td width="94" align="right" valign="middle"><img src="{$rooturl}img/nuevo.png" /></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
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
	</td>
    </tr>
</table>
 </div>
  </div>
    
  <div id="bg_seccion">
    <div class="name_module" align="center">Otros qu&iacute;micos </div>
    <div id="cosa"><table width="801" height="154" border="0">
  <tr>
    <td width="396" height="47" align="center" valign="middle">En el siguiente apartado puedes consultar y agregar pagos.</td>
    <td width="94" align="right" valign="middle"><img src="{$rooturl}img/nuevo.png"/></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
	
	<table id="pedidos1" cellpadding="0" cellspacing="0" border="1" Alto="250" conScroll="S" validaNuevo="false" AltoCelda="25" auxiliar="0"
		 ruta="../../img/grid/" validaElimina="false" Datos="" verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
		 ordenaPHP="S" title="Listado de Registros">
			<tr class="HeaderCell">
				<td width="200">Producto</td>
				<td width="100">Unidad</td>
				<td width="120">Cantidad</td>
			</tr>
		</table>
		
		<script>
			
			
			
			CargaGrid('pedidos1');
			
		</script>
	
	</td>
    </tr>
</table> 
    </div>
</div>

<div id"acciones" align="right"><table width="142" border="0">
  <tr>
    <td width="85" valign="bottom"><img src="img/guardar.png"  /></td>
    <td width="28" valign="bottom"><a href="#" alt="listado"><img src="img/listado.png" alt="listado"  /></a></td>
    <td width="35" valign="bottom"><img src="img/nuevo.png" /></td>
    <td width="61" valign="bottom"><img src="img/editar.png" /></td>
  </tr>
</table>
</div>

{include file="_footer.tpl" pagetitle="$contentheader"}