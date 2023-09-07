<?php require ("header.php");?>
<?php require ("menu.php");?>
<div class="ctn">
	<div class="base">
		<form action="algo.php" method="post">
        <label>Total a pagar</label> 
        <input type="text" name="folio">
       <button type="submit" name="submit" class="btn1">Agregar +</button>
            
  </form>


	</div>
    <div class="centro">
           <table>	
               <tr class="cabecera">
               	<td><p>Tipo de pago</p></td>
               	<td><p>Monto</p></td>
                <td></td>
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
               <tr>
               	<td class="foot">Total</td>
               	<td>$15600</td>
                <td> </td>
               </tr>

         </table>
          
    </div>
    <div class="footer">
         	<form action="algo.php" method="post">
  	
           <label>es apartado</label>
          <input type="checkbox" name="pedido">
  		 <button type="submit" name="submit" class="cerrar"> <span>Cerrar</span></button>
        
          
            
  </form>
    </div>
</div>
<?php require ("footer.php");?>