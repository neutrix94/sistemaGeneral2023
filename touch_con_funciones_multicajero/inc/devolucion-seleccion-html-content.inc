<?php
  //sacamos la fecha actual desde mysql
    $sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
    $eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
    $fecha_actual=mysql_fetch_row($eje);
  //comprobamos que haya una sesion abierta en el dia actual
    $sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha='$fecha_actual[0]' AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
    $eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
    $r=mysql_fetch_row($eje);
    if($r[0]<1){
      die("<script>alert('Pida al cajero que inicie sesion de caja para poder acceder a esta pantalla!!!');location.href='index.php?';</script>");
    }

?>

<style>
	.btn_pal{
		width: 70px;
		height:30px;
	}
	
	.ctn_paleta{
		width:90%;
		height:90%;
		margin:0 auto;
		margin-top:12px;
		background:#fff;
		padding:12px;
		background: #fff;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		margin-top:12px; 
		overflow: hidden;
		-webkit-box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		-moz-box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		box-shadow: -1px 0 0 0 #d2d2d2, -1px 0 0 0 #e6e6e6, 1px 0 0 0 #d2d2d2, 2px 0 0 0 #e6e6e6, 0 -1px 0 0 #e8e8e8, 0 2px 0 0 rgba(241, 241, 241, 0.3), 0 1px 0 0 #b1b1b1;
		-ie7-8-border: 1px solid #dedede;
		border: 1px solid #dedede \9;
		position: relative;
		margin-bottom: 9p
	}
		
	.ctn_paleta > td > button{
		background:#f60;
		border-radius:6px;}	
		
	.ctn_paleta table{
		border: none !important;
		width:80%px !important;
		
	}
	
	.ctn_paleta table td{border: none !important;}
	
	.tablaFuera{
		width:80% !important;
		height:70% !important;
	}	
</style>

<input type="hidden" id="id_ped" value="<?php echo $id_pedido;?>">
<input type="hidden" id="porc_desc" value="0">

<div>
  <p align="center" style="color:white;font-size:25px;background:rgba(225,0,0,.7);padding:10px;top:32px;position:absolute;z-index:3;width:99.5%;left:.1%;">
    <b>Cambio o Devolución de Productos</b> <br>Capture los productos a devolver o cambiar</p>
  </p>
</div>
<div data-role="content">
	<div title="ocultar teclado"  style="position:absolute; display:block; width:150px;top:125px;right:14px;">
		<img style="padding-left:13px;" src="img/teclado.png" width="70" height="70"><br>
      <p style="display:block; position:relative; top:-1.7em; left:1em;">Ocultar<br>
      teclado</p>
   </div>
<!--botón de agregar todos los productos Oscar 08.08.2018-->
   <div onclick="regresaTodo();" style="border:1px solid red;width:120px;border-radius:8px;text-align:center;color:white;background:#83B141;padding:5px;position:absolute;left:60%;top:150px;">
     Cancelar la nota o devolver todo
   </div>
<!---->
   <br><br>
  <ul  data-role="listview" data-inset="true" class="ui-grid-a">
    <?php
      $folioVenta="";
      $sq="SELECT 
          folio_nv,
          IF(pagado=1,0,1),
          (descuento/subtotal) as descuento,
          tipo_pedido 
          FROM ec_pedidos WHERE id_pedido=$id_pedido";
      
      //  echo $id_pedido;
      $eje=mysql_query($sq);
      if(!$eje){
        die("Error al consultar el folio!!!\n".mysql_error()."\n".$sq);
      }
      $rw=mysql_fetch_row($eje);
      $folioVenta=$rw[0];
      echo '<input type="hidden" id="es_apartado" value="'.$rw[1].'">';//variable de referencia de apartado
      echo '<input type="hidden" id="id_pedido_apartado" value="'.$id_pedido.'">';
    //si se trata de un pedido pagado 
      if($rw[1]==0){
        echo '<input type="hidden" id="descuento_en_nota" value="'.$rw[2].'">';
      }
  /*Implementación Oscar 01.03.2019 para guardar variable oculta de tipo de pedido*/
      if($rw[3]!=0){
        echo '<input type="hidden" id="tipo_venta_original" value="'.$rw[3].'">';
      }
  /*Fin de cambio Oscar 01.03.2019*/
    ?>
    <li class="ui-block-a">
      <label for="nombre">Folio</label>
      <input type="text" value="<?php echo $folioVenta; ?>" disabled="disabled" name="folioNV" id="f"><!--id="folio_venta"-->
    </li>
    <li class="ui-block-b" style="display:none">
      <label for="pedido">Pedido</label>
      <input type="text" readonly value="<?php echo $es_pedido ? (isset($folio) ? $folio : "") : ""; ?>" disabled="disabled" name="folioP"  id="folio_pedido">
 
    </li>
  </ul>
      <ul data-role="listview" data-inset="true" class="ui-grid-b" >
   
  <li class="ui-block-a" style="background: transparent; width:65% !important ; display:block; position:relative;">
        <label for="producto"> Producto</label>
      <input type="text" name="buscadorLabel"  id="buscadorLabel" style="background: transparent; display:block;"  onkeyup="activaBuscador(this, event)" onfocus="this.setSelectionRange(0, this.value.length);"/>
      <div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:1000;"></div>
      <input type="hidden" name="id_productoN" value="" />
       
      </li>
   
<li class="ui-block-b" style="background: transparent; width:15% !important ; display:block; position:relative;">
 
       <label>Cantidad</label>
    
      <input type="text" maxlength="7"  id="cantidad2" class="cantidad" name="cantidad" onfocus="muestraDesc(0)" onkeyup="validaKey(event, this.form)" onkeypress="return validarNumero(event,0,'cantidad2')">
    </li>  
    
 <li class="ui-block-c" style="background: transparent; zoom:1.5; -webkit-transform:scale(1.5,1.5);transform:scale(1.5,1.5); width:40px !important ;  display:block; position:relative;">
<button name="AgregaFila" data-ajax="false" data-iconpos="notext" data-icon="plus" data-iconpos="bottom"   id="AgregaFila" onclick="agregaFila(this.form)"></button>   
        
   </li>
  
   </ul> 
      
 
   

   
    <input type="hidden" name="id_producto"  id="id_producto" />
    <input type="hidden" name="id_pedido"  id="id_pedido" value="<?php echo $id_pedido; ?>" />
    <!-- </form> -->
   
 
  <!---Grid-->
  <center>
  <div class="ui-grid-solo">
   <div class="contenedorGrid">
    <table id="listaProductos"  cellpadding="0" cellspacing="0">
      <tr class="cabecera">
        <td width="0"></td>
        <td width="100"><p>Producto</p></td>
        <td width="350"><p>Descripción</p></td>
        <td width="100"><p>Cantidad</p></td>
        <td width="100"><p>Precio</p></td>
        <td width="100"><p>Descuento</p></td>
        <td width="100"><p>Monto Total</p></td>
        <td width="100"><p>Quitar</p> </td>
         <!--  <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>
        <td width="0"></td>-->
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
 </center>
<!--  <a class="regalo" id="img_regalo"  href="javascript:void(0)"><span>regalo</span></a>-->
  <div class="ui-grid-b">
    <div class="ui-block-b">
      <button data-theme="a" name="cerrar" id="cerrar" class="btn1" style="min-width: 180px; margin-right: 50px;">✔ Continuar</button>
    </div>
  <div class="ui-block-c">
               
       
         <div data-role="fieldcontain">
      <label><strong style="font-weight:bold; font-size:20px; padding-right: 2px;">Devolver:  </strong></label>
      <input type="text"  style="width:800px !important; font-size:30px;" readonly value="$ <?php echo number_format($subtotal, 2); ?>"  name="total" id="total"> 
      
 </div>

            </div>
        
  <!--Termina seccion-->
</div>
