  
  <div id="divEspera" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 0.70; filter: alpha(opacity=70);">
    <!--Comienza la ventana en bloqueo-->
    <div class="espera_ctn">
                  <img src="img/loading.gif"> 
    <p>Esperando autorización...</p>
    <button name="cancelar" id="cancelar">X Cancelar</button>
  </div>
  <!--Termina la ventana en bloqueo-->
  </div>

<div class="ctn">
    <div class="base">
     <button  class="cerrar" name="submit" type="button" id="salirbtn">
            <span>Cerrar</span></button>
    		<div style="display: block;">
            <label>Folio</label> 
            <input type="text"  value="<?php echo $es_pedido ? "" : (isset($folio) ? $folio : ""); ?>" disabled="disabled" name="folioNV" id="folio_venta">
            <label>Pedido</label>
            <input type="text" readonly="readonly" value="<?php echo $es_pedido ? (isset($folio) ? $folio : "") : ""; ?>" disabled="disabled" name="folioP"  id="folio_pedido">
           
            </div>    
        
        <div style="display: block;">
           
           
            
            <table class="bas" border="0" background="none">
                <tr valign="middle">
                    <td border="0" background="none"><label for="producto"> Producto</label></td>
  		            <td align="left" border="0" background="none">
  		                <!--<input type="text" name="producto"  id="producto" style="background: transparent;" size="30"/>-->
  		                <input type="text" name="buscadorLabel"  id="buscadorLabel" style="background: transparent;" size="30" onkeyup="activaBuscador(this, event)"/>
  		                <div id="resBus" class="lista_producto" style="display:none; position:absolute; z-index:3;"></div>  
  		                <input type="hidden" name="id_productoN" value="" />
  		            </td>  		            
  		            <td>
  		                <label>Cantidad</label>
  		            </td>
  		            <td>
  		                <!--<input type="text" maxlength="7"  id="cantidad" class="cantidad" name="cantidad">-->
  		                <input type="text" maxlength="7"  id="cantidad2" class="cantidad" name="cantidad" onfocus="muestraDesc()" onkeyup="validaKey(event, this.form)" onkeypress="return validarNumero(event,0,'cantidad2')">
  		            </td>
  		            <td>
  		                <!--<button class="btn1" name="agregar"  id="agregar">Agregar +</button>-->
  		                
  		                <button class="btn1" name="AgregaFila"  id="AgregaFila" onclick="agregaFila(this.form)">Agregar +</button>
  		            </td>
  		        </tr>
  		  </table>
  		  
  		  
  		  <input type="hidden" name="id_producto"  id="id_producto" />
  		  <input type="hidden" name="id_pedido"  id="id_pedido" value="<?php echo $id_pedido; ?>" />
  		  
  		  
  		  
            
           </div>
  
 

	</div>
    <div class="centro">
            <table id="listaProductos">
             <tr class="cabecera">
                <td width="100"><p>Producto</p></td>
                <td width="350"><p>Descripción</p></td>
                <td width="100"><p>Cantidad</p></td>
                <td width="100"><p>Precio</p></td>
                <td width="100"><p>Oferta</p></td>
                <td width="100"><p>Monto</p></td>
                <td width="50"> </td>
                <td width="0"></td>
                <td width="0"></td>
                <td width="0"></td>
                <td width="0"></td>
                <td width="0"></td>
             </tr>
             <!--Termina la cabecera-->
             <?php if (count($productos)) { foreach ($productos as $producto) { ?>
             <tr class="move">
             	<td class="tabla_id_producto"><p><?php echo $producto["id_producto"]; ?></p></td>
             	<td><p><?php echo $producto["producto"]; ?></p></td>
             	<td class="tabla_cantidad"><p><?php echo $producto["cantidad"]; ?></p></td>
             	<td class="tabla_precio"><p>$ <?php echo number_format($producto["precio"], 2); ?></p></td>
				<td class="tabla_total"><p>$ <?php echo number_format($producto["monto"], 2); ?></p></td>
				<td class="tabla_detalles"><a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a> <input type="hidden" class="es_regalo"  value="<?php echo $producto["es_regalo"]; ?>" /> </td>
			</tr>
             <?php }} ?>
            </table>
          
    </div>
    <div class="footer">
         
    	   <a class="regalo" id="img_regalo"  href="javascript:void(0)"><span>regalo</span></a>
            <input type="checkbox" name="regalo" id="es_regalo" <?php echo $es_pedido ? "disabled=\"disabled\"" : ""; ?>>
           <label for="es_pedido">Generara pedido</label>
          <input type="checkbox" name="pedido" id="es_pedido" <?php echo $es_pedido ? "checked=\"checked\"" : ""; ?>>
  		  <label for="es_paquete">Paquete</label>
          <input type="checkbox" name="paquete" id="es_paquete"  <?php echo $es_paquete ? "checked=\"checked\"" : ""; ?> <?php echo $es_pedido ? "disabled=\"disabled\"" : ""; ?>>
  		  <label>Total</label>
          <input style="font-size:30px;" type="text" readonly="readonly" value="$ <?php echo number_format($subtotal, 2); ?>" disabled="disabled" name="total" id="total">
              
          
    </div>
</div>