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


  $sql = "SELECT 
            id_productos,
            orden_lista 
          FROM ec_productos
          WHERE es_ultimas_piezas = '1'";
  $stm = mysql_query( $sql ) or die( "Error al consultar los productos  que son ultimas  piezas : " . mysql_error() );
  $last_pieces = "";
  $last_orders = "";
  while( $row = mysql_fetch_row( $stm ) ){
    $last_pieces .= ( $last_pieces != "" ? "," : "" );
    $last_pieces .= $row[0];

    $last_orders .= ( $last_orders != "" ? "," : "" );
    $last_orders .= $row[1];
  //echo "here : {$row[0]}";
  }
  echo "<script> 
          var global_last_pieces_array = '{$last_pieces}'.split( ',' );
          var global_last_orders_array = '{$last_orders}'.split( ',' );
          //alert( global_last_pieces_array );
        </script>";
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
  <p align="center" style="color:white;font-size:100%;background:rgba(225,0,0,.7);padding:10px;top:0;position:absolute;z-index:3;width:99.5%;left:.1%;">
    <b>Cambio o Devolución de Productos</b> <br>Capture los productos a devolver o cambiar</p>
  </p>
</div>
<div class="container" style="position : relative; max-width : 99% !important;">
   <br><br>
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
    <div class="row">
      <div class="col-6">
        <label for="nombre">Folio</label>
        <input 
          type="text" 
          value="<?php echo $folioVenta; ?>" 
          disabled="disabled" 
          name="folioNV" 
          id="f"
          class="form-control"><!--id="folio_venta"-->
      </div>
      <div class="col-6" style="display:none">
        <label for="pedido">Pedido</label>
        <input type="text" readonly value="<?php echo $es_pedido ? (isset($folio) ? $folio : "") : ""; ?>" disabled="disabled" name="folioP"  id="folio_pedido">
 
    </div>
    <div 
        title="ocultar teclado"  
        class="col-6 text-center">
      <img style="padding-left:13px;" src="img/teclado.png" width="70" height="70"><br>
        <p style="display:block; position:relative; top:-3.4em; left:1em; left : 70px;">Ocultar<br>
        teclado</p>
      </div>  
    </div>
    <div class="row">
      
      <!--botón de agregar todos los productos Oscar 08.08.2018
        style="border:1px solid red;width:120px;border-radius:8px;text-align:center;color:white;background:#83B141;padding:5px;position:absolute;left:60%;top:150px;"
    -->
      <div class="col-12 text-center">
        <button
          class="btn btn-warning"
          onclick="regresaTodo();"
        >
          <i class="">Cancelar la nota o devolver todo</i>
        </button>
      </div>
      <!---->
    </div>
    
      <!--ul data-role="listview" data-inset="true" class="ui-grid-b" -->
   
    <div class="row">
      <div class="col-8">
        <label for="producto"> Producto</label>
        <input 
          type="text"
          name="buscadorLabel"  
          id="buscadorLabel" 
          onkeyup="activaBuscador(this, event)" 
          onfocus="this.setSelectionRange(0, this.value.length);"
          class="form-control"
        />
        <div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:1000;"></div>
        <input type="hidden" name="id_productoN" value="" />
      </div>  
      <div class="col-3">
         <label>Cantidad</label>
        <input 
          type="text" 
          maxlength="7"  
          id="cantidad2" 
          class="cantidad form-control" 
          name="cantidad" 
          onfocus="muestraDesc(0)" 
          onkeyup="validaKey(event, this.form)" 
          onkeypress="return validarNumero(event,0,'cantidad2')">
      </div>
      <div class="col-1" 
        style=""><br>
        <button 
          name="AgregaFila"  
          data-iconpos="bottom"   
          id="AgregaFila" 
          class="btn btn-success"
          onclick="agregaFila(this.form)"
        >
          <i class="icon-ok-circle"></i>
        </button>   
      </div>
    </div>  
    
      
    <input type="hidden" name="id_producto"  id="id_producto" />
    <input type="hidden" name="id_pedido"  id="id_pedido" value="<?php echo $id_pedido; ?>" />
    <!-- </form> -->
   
 
  <!---Grid-->
  <br>
  <center>
  <div class="row">
   <div class="contenedorGrid">
    <table id="listaProductos" class="table" cellpadding="0" cellspacing="0">
      <tr class="cabecera">
        <td width="0"></td>
        <td width="100"><p>Producto</p></td>
        <td width="350"><p>Descripción</p></td>
        <td width="100"><p>Cantidad</p></td>
        <td width="100"><p>Precio</p></td>
        <td width="100"><p>Descuento</p></td>
        <td width="100"><p>Total</p></td>
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
  <div class="row">
    <div class="col-1"></div>
    <div class="col-10">
      <button data-theme="a" name="cerrar" id="cerrar" class="btn1 btn btn-success form-control">
        ✔ Continuar
      </button>
    </div>
    <div class="col-1"></div>
    <div class="col-1"></div>
    <div class="col-10">
      <!--div data-role="fieldcontain"-->
        <label>
          <strong style="font-weight:bold; font-size:20px; padding-right: 2px;">
            Devolver:  
          </strong>
        </label>
        <input 
          type="text"  
          style="font-size:200%;" 
          readonly 
          value="$ <?php echo number_format($subtotal, 2); ?>"  
          name="total" 
          id="total"
          class="form-control"> 
        
      <!-- /div-->

    </div>
        
  <!--Termina seccion-->
</div>

<style type="text/css">
  .contenedorGrid{
    position: relative;
    max-width: 100%;
    width: 100%;
    overflow: auto;
    height: 200px !important;
    max-height: 200px !important;
  }
  .row{

    max-width: 100% !important;
  }

  .cabecera>td{
    padding: 10px !important;

  }
</style>