 <div id="divEspera" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 0.70; filter: alpha(opacity=70);">
    <!--Comienza la ventana en bloqueo-->
    <div class="espera_ctn">
                  <img src="img/loading.gif"> 
    <p>Esperando autorización...</p>
    <button name="cancelar" id="cancelar" data-role="button">X Cancelar</button>
  </div>
  <!--Termina la ventana en bloqueo-->
  </div>

  <div data-role="content">
 
  <!---Comienza ñla sección de pedido-->
   
  <!-- <form> -->

    <ul data-role="listview" data-inset="true" class="ui-grid-b">
   
        <li class="ui-block-a">
          <label for="nombre">Folio</label>
      <input type="text" value="<?php echo $es_pedido ? "" : (isset($folio) ? $folio : ""); ?>" disabled="disabled" name="folioNV" id="folio_venta">
        
        </li>
    <li class="ui-block-b">
      <label for="pedido">Pedido</label>
      <input type="text" readonly value="<?php echo $es_pedido ? (isset($folio) ? $folio : "") : ""; ?>" disabled="disabled" name="folioP"  id="folio_pedido">
 
    </li>
 
 
  
 </ul>
      <ul data-role="listview" data-inset="true" >
    
  <li>
        <label for="producto"> Producto</label>
      <input type="text" name="buscadorLabel"  id="buscadorLabel" style="background: transparent;" size="30" onkeyup="activaBuscador(this, event)" onfocus="this.setSelectionRange(0, this.value.length);"/>
      <div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:1000;"></div>
      <input type="hidden" name="id_productoN" value="" />
         
     &nbsp; 
        <br>
      </li>
    
      <li>
 
       <label>Cantidad</label>
    
      <input type="text" maxlength="7"  id="cantidad2" class="cantidad" name="cantidad" onfocus="muestraDesc()" onkeyup="validaKey(event, this.form)" onkeypress="return validarNumero(event,0,'cantidad2')">
    </li>  
    <li >
    <div title="ocultar teclado">
      <img style="padding-left:13px;" src="img/teclado.png" width="70" height="70">
     <p style="display:block; position:relative; top:-3.7em; left:8em;"> ocultar  <br>
     teclado</p>
      

 </div>
 
</li>    
 <li>   <button name="AgregaFila" data-ajax="false" style="width:50%;"   id="AgregaFila" onclick="agregaFila(this.form)">Agregar producto </button>   
        
   </li>
  
   </ul> 
      
 
   

   
    <input type="hidden" name="id_producto"  id="id_producto" />
    <input type="hidden" name="id_pedido"  id="id_pedido" value="<?php echo $id_pedido; ?>" />
    <!-- </form> -->
   
 
  <!---Grid-->
  <div class="ui-grid-solo">
   <div class="contenedorGrid">
    <table id="listaProductos"  cellpadding="0" cellspacing="0">
      <tr class="cabecera">
        <td width="100"><p>Producto</p></td>
        <td width="350"><p>Descripción</p></td>
        <td width="100"><p>Cantidad</p></td>
        <td width="100"><p>Precio</p></td>
        <td width="100"><p>Oferta</p></td>
        <td width="100"><p>Monto</p></td>
        <td width="50"></td>
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
        <td class="tabla_detalles"><a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a>
          <input type="hidden" class="es_regalo"  value="<?php echo $producto["es_regalo"]; ?>" /></td>
      </tr>
      <?php }} ?>
    </table>
    </div>
  </div>
  <!--seccion-->
 
    
<!--  <a class="regalo" id="img_regalo"  href="javascript:void(0)"><span>regalo</span></a>-->
  <div class="ui-grid-b">
            <div class="ui-block-a">
                <div data-role="fieldcontain">
                <fieldset  data-role="controlgroup" data-type="horizontal">
                 <label>
       <input   class="rojo"  type="checkbox" name="regalo" id="es_regalo" checked>
          Regalo	   </label>
                  </fieldset><br>
                    <fieldset data-role="controlgroup" >
       
        <label>
          <input type="checkbox" name="pedido" id="es_pedido">
          Generar pedido</label>
        <label>
          <input type="checkbox" name="paquete" id="es_paquete">
          Paquete</label>
          </fieldset>
          </div>
            </div>
         
         <div class="ui-block-c">
        <br>
        <br>
        <br>
            
            </div>
      <div class="ui-block-c">
               
                 <br>
         <br>
           <br>
         <div data-role="fieldcontain">
      <label><strong style="font-weight:bold; font-size:20px; padding-right: 2px;">Total</strong></label>
      <input type="text"  style="width:800px !important;" readonly value="$ <?php echo number_format($subtotal, 2); ?>" disabled="disabled" name="total" id="total"> 
      
 </div>

            </div>
             
  <!--Termina seccion-->
</div>
<div  class="ui-grid-b" data-type="horizontal">
 <div class="ui-block-a">
  <a data-icon="delete" data-role="button" href="javascript:void(0)"  onclick="if(confirm('¿Realmente desea salir sin guardar los cambios?'))location.href='index.php?src=home'">
               Cancelar
                </a>
                </div>
                <div class="ui-block-b" style="display:block;position:relative; left:11em;">
 				<!-- <a data-role="button" data-theme="a"  data-icon="check" data-ajax="false" href="javascript:void(0)" id="boton_cerrar">
                Cerrar venta
                </a> -->
                </div>
                </div>
</div>