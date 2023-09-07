
<?php require ("header.php");?>
<?php require ("menu.php");?>
<div class="ctn">
	<div class="base">
		<form action="algo.php" method="post">
        <label>Folio</label> 
        <input type="text" name="folio">
        <label>Nombre del cliente</label>
            <input type="text" name="folio">
            <button type="submit" name="submit" class="buscar">Buscar</button>
  </form>

	</div>
    <div class="centro">
            <table>
             <tr class="cabecera">
                <td><p>Folio</p></td>
                <td><p>Cliente</p></td>
                <td><p>Fecha</p></td>
                <td><p>Monto</p></td>
                <td><p>continuar</p></td>
                <td></td>
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
    <div class="footer">
         <table>	
               <tr class="cabecera">
               	<td><p>Folio</p></td>
               	<td><p>Descripción</p></td>
                <td><p>Cantidad</p></td>
                <td> </td>

               </tr>
               <tr class="move">
               	<td>102</td>
               	<td>Producto1</td>
                <td>4</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
               <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
                <tr class="move">
               	<td>123</td>
               	<td>Producto 123</td>
                <td>7</td>
                  <td><a href="#" class="eliminar"> <span>eliminar</span></a> </td>
               </tr>
             
         </table>
           
    </div>
      <button type="submit" name="submit" class="btn">Siguiente</button>
</div>
<?php require ("footer.php");?>