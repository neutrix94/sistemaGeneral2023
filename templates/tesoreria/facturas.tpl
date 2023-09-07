{include file="_header.tpl" pagetitle="$contentheader"}
<div id="titulo">Generar Factura</div>
    
   
  	
   
    
  <div class="redondo" align="center"><table width="827" border="0" >
  
  <tr>
    <td height="44" colspan="2" class="name_module">Datos</td>
    <td width="164">&nbsp;</td>
    <td width="149" class="texto_form">&nbsp;</td>
    <td width="248">&nbsp;</td>
  </tr><tr>
    <td width="69" height="44" class="texto_form">ID</td>
    <td width="175" height="44"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
    <td width="164">&nbsp;</td>
    <td width="149" class="texto_form">Cliente</td>
    <td width="248"><form id="form2" name="form2" method="post" action="">
      <label for="select"></label>
      <select name="select" class="barra_tres" id="select">
	  <option>Halliburton</option>
      </select>
    </form></td>
  </tr>
  <tr>
    <td height="44" class="texto_form">Razón Social</td>
    <td><select name="select2" class="barra_tres" id="select2">
	<option>HALLIBURTON DE MEXICO, S.A. DE C.V.</option>
    </select></td>
    <td>&nbsp;</td>
    <td class="texto_form">Folio</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input name="text1" type="text" class="barra" id="text1" readonly="readonly" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="44" class="texto_form">Subtotal</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input name="text1" type="text"  class="barra_dos" id="text1" readonly="readonly" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">I.V.A.</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input name="text1" type="text" class="barra" id="text1" readonly="readonly" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="44" class="texto_form">Total</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <inputname="text1" type="text"  class="barra" id="text1" readonly="readonly" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  
  
</table>
</div>
  
  
  <div id="bg_seccion">
    <div class="name_module" align="center">Agregando Factura</div>
  <div id="cosa" align="center">
  
  <table id="pedidos" cellpadding="0" cellspacing="0" border="1" Alto="250" conScroll="S" validaNuevo="false" AltoCelda="25" auxiliar="0"
		 ruta="../../img/grid/" validaElimina="false" Datos="" verFooter="N" guardaEn="False" listado="S" class="tabla_Grid_RC" paginador="S" datosxPag="30" pagMetodo='php'
		 ordenaPHP="S" title="Listado de Registros">
			<tr class="HeaderCell">
				<td width="200">Pedido</td>
				<td width="100">fecha</td>
				<td width="120">producto</td>
				<td width="120">cantidad</td>
				<td width="120">monto</td>
			</tr>
		</table>
		
		<script>
			
			
			
			CargaGrid('pedidos');
			
		</script>
  
  </div>
  </div>
  <div id"acciones" align="right"><table width="172" border="0">
  <tr>
    <td width="24" valign="bottom">&nbsp;</td>
    <td width="21" valign="bottom">&nbsp;</td>
    <td width="28" valign="bottom">&nbsp;</td>
    <td width="38" valign="bottom">&nbsp;</td>
    <td width="49" valign="bottom">&nbsp;</td>
  </tr>
</table>
</div>

{include file="_footer.tpl" pagetitle="$contentheader"}