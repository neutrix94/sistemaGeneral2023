<?php

    include("../../../conectMin.php");

    require ("header.php");
    
    
    //Buscamos el Folio de pedido proximo
    $sql="SELECT
          folio_pedido
          FROM ec_pedidos
          WHERE id_sucursal=$user_sucursal
          AND id_pedido > 0
          ORDER BY id_pedido DESC";
          
    $res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
    
    if(mysql_num_rows($res) > 0)
    {
        $row=mysql_fetch_row($res);
        //remplazamos el folio
        $folioNV=str_replace("NV", "", $row[0]);
        $folioNV=$folioNV+1;
        
        
    }   
    else
    {
        $folioNV="NV00001";
    }       


?>
<?php require ("menu.php");?>

<script>
      
      function presionaEnter(eve, obj)
      {
          var key=0;      
          key=(eve.which) ? eve.which : eve.keyCode;
          
          //alert(key);
          
          if(key == 13)
          {
              alert('OK');
          }  
      }
      
      
      function validarNumero(e,punto,id)
      {
        var valor="";
        
        tecla_codigo = (document.all) ? e.keyCode : e.which;
        valor=document.getElementById(id).value;
        
        
        if(tecla_codigo==8 || tecla_codigo==0)return true;
        if (punto==1)
            patron =/[0-9\-.]/; 
        else
            patron =/[0-9\-]/;
        
            
        //validamos que no existan dos puntos o 2 -
        tecla_valor = String.fromCharCode(tecla_codigo);
        //46 es el valor de "."
        if (valor.split('.').length>1 && tecla_codigo==46)      
        {
            return false;
        }
        else if (valor.split('-').length>1 && tecla_codigo==45)     
        {
            //45 es el valor de "-"
            return false;
        }
        
        
        return patron.test(tecla_valor);
    
    }
      
  </script>


<div class="ctn">
    <div class="base">
	   <form action="algo.php" method="post">
            <label>Folio</label> 
            <input type="text" name="folioNV" disabled="true" value="<?php echo $folioNV; ?>">
            <label>Pedido</label>
            <input type="text" name="folioP" disabled="true" value="<?php echo $folioP; ?>" readonly="true">
            <button type="button" name="submit" class="cerrar" onclick="if(confirm('¿Realmente desea cerrar sin guardar?'))location.href='../index.php';">
            <span>Cerrar</span></button>    
        </form>
        
        
  		<form action="algo.php" method="post">
  		 <label> Producto</label>
  		  <input type="text" name="producto" onkeypress="return presionaEnter(event, this)">
  		  <label>Cantidad</label>
  		  <input type="text" name="cantidad" class="cantidad" id="cantidadProd" onkeypress="return validarNumero(event, 1, 'cantidadProd')" maxlength="7">
            <button type="submit" name="submit" class="btn1">Agregar +</button>
            
  </form>
  
  

	</div>
    <div class="centro">
            <table>
             <tr class="cabecera">
                <td><p>Producto</p></td>
                <td><p>Descripción</p></td>
                <td><p>Cantidad</p></td>
                <td><p>Precio</p></td>
                <td><p>Monto</p></td>
                <td> </td>
             </tr>
            </table>
          
    </div>
    <div class="footer">
         
         	<form action="algo.php" method="post">
  	   <a href="#" class="regalo"><span>regalo</span></a>
            <input type="checkbox" name="regalo">
           <label>Generara pedido</label>
          <input type="checkbox" name="pedido">
  		  <label>Paquete</label>
          <input type="checkbox" name="paquete">
  		  <label>Total</label>
          <input type="text" name="folio">
          
          
              
          
          
            
  </form>
    </div>
</div>
<?php require ("footer.php");?>