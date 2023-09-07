{include file="_header.tpl" pagetitle="$contentheader"}
 <div id="cosa">
<div id="titulo">Usuarios</div>
    
  <div id="bg_seccion">
    <div class="name_module" align="center">Usuario</div>
  <div id="cosa"> 
  	
    <div id="blue">
    <span class="azul">Información</span></div>&nbsp;
    
    <div class="formulario" align="center" ><table width="772" border="0" >
  <tr>
    <td width="69" height="42" class="texto_form">ID</td>
    <td width="175"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
    <td width="158">&nbsp;</td>
    <td width="155" class="texto_form">Login</td>
    <td width="193"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input  class="barra" type="text" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td width="69" height="42" class="texto_form">Nombre</td>
    <td width="175"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
    <td width="158">&nbsp;</td>
    <td width="155" class="texto_form">Teléfono</td>
    <td width="193"><form id="form1" name="form1"  method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input  class="barra" type="text" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="37" class="texto_form">Apellido Paterno</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input  class="barra" type="text" name="text1" id="text1" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">Correo</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text" class="barra" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="40" class="texto_form">Apellido Materno</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra"name="text1" id="text1" />
       </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">Contraseña</td>
    <td><form id="form1" name="form1" method="post" action="">
      <span id="sprytextfield1">
        <label for="text1"></label>
        <input type="text"  class="barra" name="text1" id="text1" />
       </span>
    </form></td>
  </tr>
  <tr>
    <td height="40" class="texto_form">Sucursal</td>
    <td><form id="form2" name="form2" method="post" action="">
      <span id="spryselect1">
        <label for="select1"></label>
        <select name="select1" size="3" multiple="multiple" id="select1">
          <option>sucursal1</option>
          <option>sucursal2</option>
          <option>sucursal3</option>
        </select>
      </span>
    </form></td>
    <td>&nbsp;</td>
    <td class="texto_form">Grupo</td>
    <td><form id="form3" name="form2" method="post" action="">
      <span id="spryselect2">
        <label for="select1"></label>
        <select name="select2" size="3" multiple="multiple" id="select1">
          <option selected="selected">Grupo1</option>
          <option>Grupo2</option>
          <option>Grupo3</option>
        </select>
      </span>
    </form></td>
  </tr>
  
</table>
</div>

  </div>
    </div>
  
  <div id="bg_seccion">
    <div class="name_module" align="center">Permisos</div>
  <div id="cosa"><span class="azul">General</span>&nbsp;
    <div class="formulario">
  <table width="520" border="0" align="center" cellpadding="3px" cellspacing="3px">
  <tr>
    <td width="183" class="texto_form"><a href="#" onClick="MM_openBrWindow('opcion_usuarios.html','Opciones','width=320,height=250')">Bancos</a></td>
    <td width="190" class="texto_form">Productos</td>
    <td width="133" class="texto_form">Clientes</td>
    </tr>
  <tr>
    <td class="texto_form"><a href="#" onClick="MM_openBrWindow('opcion_usuarios2.html','','width=420,height=250')">Refacciones</a></td>
    <td class="texto_form">Tranportes</td>
    <td class="texto_form">Unidades</td>
    </tr>
  <tr>
    <td class="texto_form">Auxiliares</td>
    <td class="texto_form">Conductores</td>
    <td class="texto_form">Clasificaci&oacute;n de pedidos</td>
    </tr>
  <tr>
    <td class="texto_form">Talleres mec&aacute;nicos</td>
    <td class="texto_form">Cuentas bancarias</td>
    <td class="texto_form">Unidades de medida</td>
    </tr>
  <tr>
    <td class="texto_form">Paises</td>
    <td class="texto_form">Estados</td>
    <td class="texto_form">Tipos de productos</td>
    </tr>
  
</table>
  </div>
  
  
  
  </div>
  </div>
  <div id"acciones" align="right"><table width="142" border="0">
  <tr>
    <td width="85" valign="bottom"><img src="{$rooturl}img/guardar.png"  /></td>
    <td width="28" valign="bottom"><a href="#" alt="listado"><img src="{$rooturl}img/listado.png" alt="listado"  /></a></td>
    <td width="35" valign="bottom"><img src="{$rooturl}img/nuevo.png" /></td>
    <td width="61" valign="bottom"><img src="{$rooturl}img/editar.png" /></td>
  </tr>
</table>
</div>

{include file="_footer.tpl" pagetitle="$contentheader"}