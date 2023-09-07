{include file="_header.tpl" pagetitle="$contentheader"}

<div id="campos">
<div id="titulo"><!--{$titulo}--> Pedidos</div>

         
    <form action="pedidos-w.php" method="post"  name="formGral">
    
    <input type="hidden" name="procesa" value="{$procesa}">
    
    <input type="hidden" name="fileProductos" id="fileProductos" value="">
    <input type="hidden" name="aplicaCupon" id="aplicaCupon" value="0">   
   
    <div id="bg_seccion">
    <!--Comienza los pedidos básicos-->
       <div id="pedido">
          <div id="Pedido_cabecera">
             <div class="H_pedidos">
               <h2>Pedidos</h2>
               </div>
               <div id="datos_pedido">
                      <div class="bas">
                      <label> n&uacute;mero de pedido:</label> <input type="text" readonly name="no_pedido" value="{$no_pedido}">
                      <label> Cliente: </label>
                      {if $read1 neq 1}                       
	                      <div class="n_cliente " style="cursor:pointer; position:relative;top:23px; " onclick="window.open('../ajax/nuevoc.php', '', 'width=500,height=350')">
	                      	<img src="{$rooturl}img/img_floreria/nuevo_cliente.png" alt="Crear Nuevo" border="0" />
	                      	<p>Nuevo</p>
	                      </div>
	                      <input class="b_uscador" type="text" name="txt_busc" onkeyup="buscaCliente(this.value)"><br>
					  {/if}	                      
                      
                      <select style="display:block; position:relative; top:-2.5em;" name="id_cliente{if $read1 eq 1}_no{/if}" id="id_cliente{if $read1 eq 1}_no{/if}" size="4" {if $read1 eq 1}disabled="true"{/if} onclick="this.form.cupon_codigo.value=''">
                      	{html_options values=$clival output=$clitxt selected=$id_cliente}
                      </select>
                      {if $read1 eq 1}
                      	<input type="hidden" name="id_cliente" value="{$id_cliente}">
                      {/if}
                      <label> Hora: </label> <input type="text" readonly name="hora" value="{$hora}">
                      <label> Vendedor: </label><select name="id_vendedor_m" disabled>{html_options values=$venval output=$ventxt selected=$id_vendedor}</select>
                      <input type="hidden" name="id_vendedor" value="{$id_vendedor}">
                      <label> Sucursal: </label>
                      <select id="id_sucursal{if $read1 eq 1 or $multsuc neq '-1'}_no{/if}" name="id_sucursal{if $read1 eq 1 or $multsuc neq '-1'}_no{/if}" {if $read1 eq 1 or $multsuc neq '-1'}disabled{/if}>
                      	{html_options values=$sucval output=$suctxt selected=$id_sucursal}
                      </select>
                      
                      {if $read1 eq 1 or $multsuc neq '-1'}
                      
                      	<input type="hidden" readonly name="id_sucursal" id="id_sucursal" value="{$id_sucursal}">
                      {/if}	
                     
                      </div>
                     <div class="bas"> 
                     <label> Fecha: </label> <input type="text" readonly name="fecha" value="{$fecha}">
                     <label> Subtotal:  </label>  <input type="text" id="subtotal" readonly name="subtotal" value="{$subtotal}">
                     <label> IVA: </label> <input type="text" readonly id="iva" name="iva" value="{$iva}">
                     <label> Total:</label>   <input type="text" readonly id="total" name="total" value="{$total}">
                     </div>
               </div>
          </div>
          <!--Productos adqiridos-->
          <div id="productos_ad">
                
                <!---Aqui va el producto-->
                <div class="H_pedidos">
               <h2>Productos</h2>
              <div class="Sin_fondo">
              	{if $read1 neq 1} 
                	<div style="margin-right:8px; display:block; position:relative;" class="insertar" title="clic para agregar un nuevo registro" onclick="InsertaFila('pagosDev')">
          				<p>Nueva Fila</p>
            		</div>
            	{/if}
                  </div>     
               </div>
             <div class="grid_sos">
           <div class="grid-d" >
           
            <div class="tablas-res1">
           
           
           	<table  id="pagosDev" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="validaNuevoFil()" AltoCelda="70"
            auxiliar="0" ruta="../../img/" validaElimina="{if $read1 eq 1}false{else}true{/if}" Datos="../ajax/productosPedido.php?tipo=1&id_pedido={$id_pedido}"
            verFooter="N" guardaEn="../ajax/productosPedido.php?tipo=2" listado="{if $read1 eq 1}S{else}N{/if}" class="tabla_Grid_RC" paginador="N" title="Listado de Registros" despuesEliminar="cambiaTotal(0)">
                <tr>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="NO">id_pedido_entrega</td>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="$LLAVE">id_pedido</td>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" >id_producto</td>
                    <td  tipo="texto" width="80" offsetWidth="80" modificable="S" align="left" campoBD="p.nombres" onChange="validaProd('#')">Producto</td>
                    <td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left" campoBD="cantidad">Descripcion</td>
                    <td tipo="texto" width="80" offsetWidth="80" modificable="S" align="left" campoBD="cantidad"  onChange="cambiaTotal(0)">Cantidad</td>
                    <td tipo="decimal" width="80" offsetWidth="80" modificable="N" align="left" campoBD="cantidad" mascara="$#,###.##">Precio</td>
                    <td tipo="formula" width="80" offsetWidth="80" modificable="N" align="left" campoBD="cantidad" formula="$Cantidad*$Precio" mascara="$#,###.##">Monto</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="N" align="left" campoBD="cantidad">Imagen</td>
                    <td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left" campoBD="cantidad">Adicionales</td>   
                </tr>
            </table>
            <script>        
                CargaGrid('pagosDev');
                
                {literal}
                
                function validaNuevoFil()
                {
                	num=NumFilas('pagosDev');
                
                	if(num <= 0)
                		return true;
               
               		var pos=num-1;
               		
               		if(celdaValorXY('pagosDev', 3, pos) == '' || celdaValorXY('pagosDev', 5, pos) == '')
               		{
               			alert("Antes debe llenar los datos de la ultima fila");
               			return false;
               		}
               		
               		return true; 			
                
                }
                
                
                function cambiaTotal(id)
                {
                	var fac=document.getElementById('facturado');
                	var desc=parseFloat(document.getElementById('aplicaCupon').value)/100;
                	
                	var num=NumFilas('pagosDev');
                	var sub=0;
                	
                	for(i=0;i<num;i++)
                	{
                		sub+=parseFloat(celdaValorXY('pagosDev', 7, i)+"");
                	}
                	
                	if(desc > 0)
                		sub=sub*(1-desc);
                	
                	document.getElementById('subtotal').value=sub;
                	
                	if(fac.checked == true)
                	{
                		document.getElementById('iva').value=sub*.16;
                		document.getElementById('total').value=sub*1.16;
                	}
                	else
                	{
                		document.getElementById('iva').value=0;
                		document.getElementById('total').value=sub;
                	}	
                
                }
                
                
                function validaProd(pos)
                {
                	var val=celdaValorXY('pagosDev', 3, pos);
                	
                	var res=ajaxR("../ajax/validaProd.php?val="+val+"&id_sucursal="+document.getElementById('id_sucursal').value);
                	
                	var aux=res.split('|');
                	
                	if(aux[0] == 'exito')
                	{
                	
                		valorXYNoOnChange('pagosDev', 2, pos, aux[1]);
                		valorXYNoOnChange('pagosDev', 4, pos, aux[2]);
                		htmlXY('pagosDev', 4, pos, aux[2]);
                		valorXYNoOnChange('pagosDev', 6, pos, aux[3]);
                		//htmlXY('pagosDev', 6, pos, Mascara('$#,###.##', aux[2]));
                	
                	}
                	else
                	{
                		alert('Codigo de producto no valido');
                		valorXYNoOnChange('pagosDev', 3, pos, '');
                	}
                	
                	
                }
                
                
                function valida()
                {
                
                	var f=document.formGral;
                
                	if(f.id_cliente.value == '')
                	{
                		alert("Es necesario que seleccione un cliente");
                		f.id_cliente.focus();
                		return false;
                	}
                	if(f.fecha_entrega.value == '')
                	{
                		alert("Es necesario que seleccione una fecha de entrega");
                		f.fecha_entrega.focus();
                		return false;
                	}
                	if(f.destinatario.value == '')
                	{
                		alert("Es necesario que inserte un destinatario");
                		f.destinatario.focus();
                		return false;
                	}
                	if(f.calle.value == '')
                	{
                		alert("Es necesario que inserte una calle");
                		f.calle.focus();
                		return false;
                	}
                	if(f.no_exterior.value == '')
                	{
                		alert("Es necesario que inserte un numero exterior");
                		f.no_exterior.focus();
                		return false;
                	}
                	if(f.colonia.value == '')
                	{
                		alert("Es necesario que inserte una colonia");
                		f.colonia.focus();
                		return false;
                	}
                	if(f.telefono.value == '')
                	{
                		alert("Es necesario que inserte una telefono");
                		f.telefono.focus();
                		return false;
                	}
                
                	if(validaNuevoFil() == false)
                		return false;
 
 
 					               
                
                	var aux=GuardaGrid('pagosDev', 5);
                	var ax=aux.split('|');
                	
                	if(ax[0] != 'exito')
                	{
                		alert(aux);
                		return false;
                	}
                	
                	document.getElementById('fileProductos').value=ax[1];
                	
                	f.submit();
                
                }
                
                function buscaCliente(val)
                {
                	if(val.length > 3)
                	{
                	
                		var res=ajaxR("../ajax/getClientes.php?valor="+val);
                		var aux=res.split('|');
                		if(aux[0] == 'exito')
                		{
                			obj=document.getElementById('id_cliente');
                			obj.options.length=0;
                			
                			for(i=1;i<aux.length;i++)
                			{
                				var ax=aux[i].split('~');
                				obj.options[i-1]=new Option(ax[1], ax[0]);
                			}
                		}
                	
                	}
                }
                
                
                
                function validaCupon(obj)
                {
                	//alert(obj.value);
                	
                	if(obj.value == '')
                		return false;
                	
                	var id_cliente=document.getElementById('id_cliente').value;
                	
                	var url="../ajax/validaCupon.php?codigo="+obj.value+"&id_cliente="+id_cliente;
                	
                	var res=ajaxR(url);
                	
                	var aux=res.split('|');                	
                	if(aux[0] != 'exito')
                	{
                		alert(res);
                		document.getElementById('aplicaCupon').value="0";
                		obj.value="";
                	}
                	else
                	{
                		document.getElementById('aplicaCupon').value=aux[1];
                		cambiaTotal();	
                	}
                	
                	
                }
                
                {/literal}
                
            </script> 
           
           
            </div>
           
           </div>
       
            </div>
           
         </div>
         
          
          <!--Datos de envío-->
         <div id="Datos_envio">
        
         <div class="H_pedidos">
           <h2>Datos de Env&iacute;o</h2>
           </div>
           <div class="bas">
                      <label> Fecha de entrega :</label>
                      <input type="text" name="fecha_entrega" value="{$fecha_entrega}" readonly id="fecha_entrega" {if $read2 neq 1}onfocus="calendario(this)"{/if}>
                      <label> Hora de entrega:</label>
                      <select name="hora_entrega{if $read2 eq 1}_no{/if}" {if $read2 eq 1}disabled="true"{/if}>
                      	{html_options values=$horval output=$hortxt selected=$hora_entrega}
                      </select>
                      {if $read2 eq 1}
                      	<input type="hidden" name="hora_entrega" value="{$hora_entrega}">	
                      {/if}
                      <label> Nota: </label> <input type="text" name="nota" value="{$nota}" {if $read2 eq 1}readonly="true"{/if}>
                      <label> Destinatario:</label>  <input type="text" name="destinatario" value="{$destinatario}" {if $read2 eq 1}readonly="true"{/if}>
                      <label> Calle: </label> <input type="text" name="calle" value="{$calle}" {if $read2 eq 1}readonly="true"{/if}>
                      <label> No. exterior: </label> <input type="text" name="no_exterior" value="{$no_exterior}" {if $read2 eq 1}readonly="true"{/if}>
                      <label> No. interior:</label>  <input type="text" name="no_interior" value="{$no_interior}" {if $read2 eq 1}readonly="true"{/if}>
                      <label> Colonia:</label>  <input type="text" name="colonia" value="{$colonia}" {if $read2 eq 1}readonly="true"{/if}>

                      </div>
                     <div class="bas"> 
                       <label>Delegaci&oacute;n: </label>
                     <select name="id_delegacion{if $read2 eq 1}_no{/if}" {if $read2 eq 1}disabled="true"{/if}>
                     	{html_options values=$delval output=$deltxt selected=$id_delegacion}
                     </select>
                     {if $read2 eq 1}
                     	<input type="hidden" name="id_delegacion" value="{$id_delegacion}">
                     {/if}
                     <label> C&oacute;digo postal:</label> <input type="text" name="cp" value="{$cp}" {if $read2 eq 1}readonly="true"{/if}>
                        <label>Estado: </label>
                     <select name="id_estado{if $read2 eq 1}_no{/if}" {if $read2 eq 1}disabled="true"{/if}>
                    	{html_options values=$estval output=$esttxt selected=$id_estado}
                      </select>
                      {if $read2 eq 1}
                      	<input type="hidden" name="id_estado" value="{$id_estado}">
                      {/if}
                     <label> T&eacute;lefono:</label><input type="text" name="telefono" value="{$telefono}" {if $read2 eq 1}readonly="true"{/if}>
                     <label> T&eacute;lefono2:</label><input type="text" name="telefono_2" value="{$telefono_2}" {if $read2 eq 1}readonly="true"{/if}>
                     <label> Referencias:</label><input type="text" name="referencia" value="{$referencia}" {if $read2 eq 1}readonly="true"{/if}>
                     <label> Tipo de domicilio:</label>
                     <select name="id_tipo_domicilio{if $read2 eq 1}_no{/if}" {if $read2 eq 1}disabled="true"{/if}>
                    	{html_options values=$tdoval output=$tdotxt selected=$id_tipo_domicilio}
                      </select>
                      {if $read2 eq 1}
                      	<input type="hidden" name="id_tipo_domicilio" value="{$id_tipo_domicilio}">
                      {/if}
                     <label> Otro:</label><input type="text" name="otro" value="{$otro}" {if $read2 eq 1}readonly="true"{/if}>
                     </div>
         </div>
         <!---Termian datos de envío--> 
           <!--Inicia información de pago-->
         <div id="info_pago">
         <div class="H_pedidos">
         <h2>Informaci&oacute;n de pago</h2>
         </div>
         
         <div class="grid_sos">
          	<div class="bas">
          		<label>Forma de pago: </label>
          			
                     <select name="id_forma_pago{if $read1 eq 1}_no{/if}" {if $read1 eq 1}disabled="true"{/if}>
                    	{html_options values=$fpaval output=$fpatxt selected=$id_forma_pago}
                      </select>
                      {if $read1 eq 1}
                      	<input type="hidden" name="id_forma_pago" value="{$id_forma_pago}">
                      {/if}
                      
                      
                      
				<label>Requiere factura: </label>
				<input type="checkbox" id="facturado" onclick="cambiaTotal(0)" name="facturado" value="1" {if $facturado eq '1'}checked="true"{/if} {if $read1 eq 1}disabled="true"{/if}>
				
				
				<label>Cupon: </label>
				<input type="text" name="cupon_codigo" value="{$cupon_codigo}" {if $read1 eq 1}disabled="true"{/if} onblur="validaCupon(this)">                      
          	</div>
         </div>
         
           
         
         </div>
         <!--Termina el pago--> 
         <!--Comienza el footer-->
         <div id="Pago_footer"  class="btn-inferior"align="right">
          
            
                <table  border="0">
       	{if $tipo == 0 or $tipo == 1}
				<td id="guardarlistado" valign="bottom" title="Guardar listado"><table width="60"><tr><td ><img class="botonesacciones guardarbtn" src="{$rooturl}img/guardar.png" alt="guardar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para guardar los cambios" onclick="valida()"/> </td></tr><tr><td valign="top"><p>Guardar</p></td></tr></table></td>
			{/if}	
            
			<td id="botonlistado" valign="bottom" title="Botón listado">
              <table>  <tr><td valign="top"><img class="botonesacciones listadobtn" src="{$rooturl}img/listado.png" alt="listado"  onMouseOver="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para ir al listado" onClick="{if $tipo eq 0 or $tipo eq 1}if(confirm('¿Desea salir de este formulario sin guardar?'))location.href='{$rooturl}code/general/listados.php?tabla=ZmxfcGVkaWRvcw==&no_tabla=MA=={else}location.href='{$rooturl}code/general/listados.php?tabla=ZmxfcGVkaWRvcw==&no_tabla=MA=='{/if}'"/></td></tr><tr><td><p>Listado</p></td></tr></table>
            
            </td>
			{if ($tipo == 2 or $tipo == 3) and $mostrar_nuevo eq '1'}
				<td id="botonnuevo" valign="bottom"><table width="60"><tr><td><img class="botonesacciones nuevobtn" src="{$rooturl}img/nuevo.png" alt="nuevo" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para agregar un nuevo registro" onclick="location.href='contenido.php?aab9e1de16f38176f86d7a92ba337a8d={$tabla64}&a1de185b82326ad96dec8ced6dad5fbbd=MA==&bnVtZXJvX3RhYmxh={$no_tabla64}'"/></td></tr><tr><td><p>Nuevo</p></td></tr></table></td>
			{/if}	
			{if ($tipo == 2 or $tipo == 3) && $mostrar_mod eq '1'}	
				<td valign="bottom" title="Editar"><table width="60"><tr><td><img class="botonesacciones editarbtn" src="{$rooturl}img/editar.png" alt="editar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para editar este registro" onclick="location.href='contenido.php?aab9e1de16f38176f86d7a92ba337a8d={$tabla64}&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190={$llave64}&bnVtZXJvX3RhYmxh={$no_tabla64}'"/></td></tr><tr><td><p>Editar</p></td></tr></table></td>
			{/if}
			{if $tipo == 3 && $mostrar_eli eq '1'}
				<td valign="bottom" title="Eliminar"><table width="60"><tr><td><img class="botonesacciones eliminarbtn" src="{$rooturl}img/eliminar.png" alt="eliminar" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" title="clic para eliminar el registro" onclick="valida()"/></td></tr><tr><td><p>Eliminar</p></td></tr></table></td>
			{/if}
			
			{if ($tipo eq 1 or $tipo eq 2) && $mostrar_imp eq '1' && ($tabla eq 'ec_ordenes_compra' || $tabla eq 'ec_pedidos')}
				<td valign="bottom" title="Imprimir"><table width="60"><tr><td><img src="{$rooturl}img/imprimir.png" alt="imprimir" width="31" class="botonesacciones imprimirbtn" title="clic para imprimir el registro" onclick="imprime()" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';"/> </td></tr><tr><td><p>Imprimir</p></td></tr></table></td>
                
			{/if}
            
        
        <!--<td width="16">&nbsp;
				{$tabla} - {$mostrar_imp} - {$tipo}
			</td>--> 
      </tr>
    </table>
                
             
       
         </div>   
       </div>
       
       </form>
       
    <!--Termino los pedidos-->
    
     
{include file="_footer.tpl" pagetitle="$contentheader"}