<?php require ("header.php");?>
<?php require ("menu.php");?>
<div class="ctn">
	
    <div class="centro">
                      <table>
             <tr class="cabecera">
                <td><p>Folio</p></td>
                <td><p>Cliente</p></td>
                <td><p>Fecha</p></td>
                <td><p>Monto</p></td>
                <td><p>continuar</p></td>
               <td> </td>
             </tr>
             <!--Termina la cabecera-->
              <tr class="move">
                <td><p>Folio</p></td>
                <td><p>Cliente</p></td>
                <td><p>Fecha</p></td>
                <td><p>Monto</p></td>
                <td><p>-</p></td>
                <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
             </tr>
                
            </table>

          
    </div>
    <div class="base">
		<form action="algo.php" method="post">
        <label>Pagos a favor</label> 
        <input type="text" name="folio">
        <label>Por</label>
            <input type="text" name="folio">
           
  </form>

	</div>
    <div class="footer">
         <table>	
               <tr class="cabecera">
               	<td><p>Tipo de pago</p></td>
               	<td><p>Monto</p></td>
                <td> </td>
                
               </tr>
               <tr class="move">
               	<td>Efectivo</td>
               	<td>$1052</td>
               <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
               <tr class="move">
               	<td>Tarjeta de credito</td>
               	<td>$15600</td>
               <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
               <tr  class="foot">
               	<td>Total</td>
               	<td colspan="2">$15600 </td>
               </tr>

         </table>
                 </div>
                 <button type="submit" name="submit" class="finalizar">Finalizar</button>

</div>
<?php require ("footer.php");?>