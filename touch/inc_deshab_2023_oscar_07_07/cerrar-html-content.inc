<div id="divApartado" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 0.98; filter: alpha(opacity=98);">
    <div class="espera_ctn_form" >
       <h3>Ingresa lo datos del cliente </h3>
       <label>Nombre:</label> 
        <input type="text" name="nombre"  id="cliente_nombre">
         <label>Teléfono:</label> 
        <input type="text" name="telefono"  id="cliente_telefono">
         <label>Celular:</label> 
        <input type="text" name="movil"  id="cliente_movil">
         <label>Mail:</label> 
        <input type="text" name="email"  id="cliente_email">
		<button  name="guardar_apartado" id="guardar_apartado" class="btn1">Guardar</button>
		<button  name="cancelar_apartado" id="cancelar_apartado" class="btn1">Cancelar</button>
  	</div>
</div>

<div class="ctn">
	<div class="base">
        <label class="fieldlabel">Por pagar</label> 
        <input type="text" name="monto" id="monto">
        <label class="fieldlabel">Medio de pago</label>
       <select name="tipo_pago" id="tipo_pago" >
			<?php foreach ($tipos_pago as $tipo_pago) { ?>
		    <option value="<?php echo $tipo_pago[id_tipo]; ?>"><?php echo $tipo_pago["tipo"]; ?></option>
		    <?php } ?>
		</select>
		<label class="fieldlabel">Total de la orden</label>
		<input type="text" name="total_orden" id="total_orden" disabled="disabled" value="$ <?php echo number_format($total, 2); ?>">
	<button  name="agregar" id="agregar" class="btn1">Agregar +</button>
	<div style="clear: both;"></div>
	</div>
    <div class="centro">
           <table id="listaPagos">	
               <tr class="cabecera">
               	<td><p>Tipo de pago</p></td>
               	<td><p>Monto</p></td>
                <td></td>
               </tr>
               <?php if (count ($tipos_pago)) { foreach ($pagos as $pago) { ?>
               <tr class="move">
               	<td class="tabla_tipo_pago"><?php echo $pago["tipo"]; ?></td>
               	<td class="tabla_monto">$ <?php echo number_format($total, 2); ?></td>
                <td><a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a>
                <input type="hidden"  class="id_tipo"  value="<?php echo $pago["id_tipo"]; ?>" /></td>
               </tr>
               <?php }} ?>
               <tr class="total">
               	<td class="foot">Total</td>
               	<td class="tabla_total">$ <?php echo number_format($total, 2); ?></td>
                <td>&nbsp;</td>
               </tr>

         </table>
          
    </div>
    <div class="footer">
    	<form name="fticket" id="fticket" action="index.php?scr=ticket&amp;idp=<?php echo $id_pedido; ?>" method="post" target="_blank">
           <label for="es_apartado">es apartado</label>
           <input type="checkbox" name="es_apartado" id="es_apartado">
  		   <!-- <button type="submit" name="submit" class="cerrar"> <span>Cerrar</span></button> -->
  		   <button  name="cerrar" id="cerrar" class="btn1" style="min-width: 180px; margin-right: 50px;">✔ Cerrar venta</button>
  		   <button  name="cancelar" id="cancelar" class="btn1" style="min-width: 180px; margin-right: 50px;">✗ Cancelar</button>
  		   <!-- <button  name="regresar" id="regresar" class="btn1" style="min-width: 180px; margin-right: 50px;">◀ Regresar</button> -->
  		   <div style="clear: both;"></div>
  		</form>
  	</div>
</div>