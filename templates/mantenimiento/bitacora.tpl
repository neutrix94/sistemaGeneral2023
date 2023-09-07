{include file="_header.tpl" pagetitle="$contentheader"}


<div style="height:90px;">
    <div id="titulo_dos">Bitácora de Mantenimiento</div>
    <div  class="search"><table width="299" border="0">
  <tr>
    <td height="34" colspan="2" align="center" valign="middle" class="name_module">Búsqueda</td>
    </tr>
  <tr>
    <td width="86" align="right" class="texto_form">Palabra clave</td>
    <td width="203"><input name="textfield" type="text" class="barra" id="textfield" /></td>
    </tr>
  <tr>
    <td height="25" align="right" class="texto_form">Fecha</td>
    <td valign="bottom"><input name="textfield2" type="text" class="barra" id="textfield2" />
      <img src="{$rooturl}img/search.png" width="19" height="19" /></td>
  </tr>
  </table>
</div>
    </div>
    
  <div id="bg_seccion">
    <div class="name_module" align="center">Bitácora</div>
    
  <div id="cosa_dos"> 
  	
    <div id="blue">
    <span class="azul">Camión</span></div>&nbsp;
    
    <div class="formulario" align="center" ><table width="727" border="0" >
  <tr>
    <td width="47" height="42" class="texto_form">No. Camión</td>
    <td width="230" class="detalle">PT-06</td>
    <td width="71" class="texto_form">Modelo</td>
    <td width="135" class="detalle">2006</td>
    <td width="57">&nbsp;</td>
    <td width="161">&nbsp;</td>
  </tr>
  <tr>
    <td height="37" class="texto_form">Placas</td>
    <td class="detalle">76DE1</td>
    <td class="texto_form">No. de serie</td>
    <td class="detalle">3AKJA6CG96DV40520</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="40" class="texto_form">Marca</td>
    <td class="detalle">Freightliner</td>
    <td class="texto_form">Color</td>
    <td class="detalle">Blanco</td>
    <td class="texto_form">Seguro</td>
    <td class="texto_form"><form id="form2" name="form2" method="post" action="">
      <input type="checkbox" name="checkbox" id="checkbox" />
      <label for="checkbox"></label>
    </form></td>
  </tr>
</table>
</div>

<div style="margin-top:20px;"><table width="793" border="0">
  <tr>
    <td width="376" align="center"><a href="img/foto1_gde.jpg" rel="lightbox" title="Camión N0456"><img src="{$rooturl}img/camion_foto1.jpg" width="208" height="138" class="foto" /></a></td>
    <td width="351" align="center" ><a href="img/foto2_gde.jpg" rel="lightbox" title="Camión N0456 perfil"><img src="{$rooturl}img/camion_foto2.jpg" width="208" height="138" class="foto"  /></a></td>
    <td width="52" align="left" >&nbsp;</td>
    </tr>
</table>
</div>


  </div>
  </div>
  
  <div id="bg_seccion">
    <div class="name_module_dos" align="center">Reparaciones y Mantenimiento</div>
  <div id="cosa_dos">
      <div id="blue">
    <span class="azul">Reparación</span></div>
      &nbsp;
    
    <div class="formulario" align="center" ><table width="764" border="0" >
  <tr>
    <td width="76" height="31" valign="top" class="texto_form">Concepto</td>
    <td width="199" valign="top" class="detalle">Reparaci&oacute; de escape de aceite</td>
    <td width="65" valign="top" class="texto_form">Fecha</td>
    <td width="138" valign="top" class="detalle">11/11/2011</td>
    <td width="247" rowspan="3" align="left" valign="top"><span class="texto_form">Observaciones</span> <span class="detalle">Se ha desgastado la tapa de aceite hasta que permitio el escape de acite</span></td>
    </tr>
  <tr valign="top">
    <td height="37" class="texto_form">Tipo</td>
    <td class="detalle">Aver&iacute;a por desgaste</td>
    <td class="texto_form">Solicitante</td>
    <td class="detalle">Erwin</td>
    </tr>
  <tr valign="top">
    <td height="30" class="texto_form">Fecha de Reparación</td>
    <td class="detalle">11/11/2011</td>
    <td class="texto_form">Taller</td>
    <td class="detalle">taller 1</td>
    </tr>
</table>
</div>

<div style="margin-top:20px;"><table width="802" border="0">
  <tr>
    <td height="16" colspan="2" valign="middle" class="texto_sub">Antes</td>
    <td colspan="2" valign="middle" class="texto_sub"> Después</td>
    </tr>
  <tr>
    <td width="201" align="left"><a href="img/foto_3.jpg" rel="lightbox[roadtrip]" title="defecto vista 1"><img src="{$rooturl}img/img_uno.jpg" width="178" height="133" class="foto" /></a></td>
    <td width="210" align="left" ><a href="img/foto_5.jpg" rel="lightbox[roadtrip]" title="defecto vista 2"><img src="{$rooturl}img/img_tres" width="178" height="133" class="foto" /></a></td>
    <td width="195" align="left" ><a href="img/foto_4.jpg" rel="lightbox[roadtrip]" title="Compostura vista 1"><img src="{$rooturl}img/img_cuatro" width="178" height="133" class="foto" /></a></td>
    <td width="178" align="left"><a href="img/fot_6.jpg" rel="lightbox[roadtrip]" title="Compostura vista2"><img src="{$rooturl}img/img_dos" width="178" height="133" class="foto" /></a></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</div>
  </div>
  </div>
  <div id"acciones" align="right"><table width="142" border="0">
  <tr>
    <td width="32" valign="bottom">
      <input name="button" type="submit" class="boton" id="button" value="1" />
    </td>
    <td width="33" valign="bottom"><input name="button2" type="submit" class="boton" id="button2" value="2" /></td>
    <td width="28" valign="bottom"><input name="button3" type="submit" class="boton" id="button3" value="3" /></td>
    <td width="31" valign="bottom">&nbsp;</td>
  </tr>
</table>
</div>

{include file="_footer.tpl" pagetitle="$contentheader"}