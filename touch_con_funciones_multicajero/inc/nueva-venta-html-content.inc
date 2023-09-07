<?php
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Content-Type: application/xml; charset=utf-8");
?>
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
<style>
  /*impplementado por Oscar 04-11-2017*/
  .des{
    width:95%;
    height:30px;
    border:1px solid blue;
    padding:2px;
    font-size:15px;
    border-radius: 5px;
  }
  .objetoLista{
  	padding:20px;
  }

  .autDesc{
    font-size: 25px;
    color:white;
  }
	.Pcolores{
		display:none;
		position:fixed;
		z-index:1000;
		top:0px;
		left:0px; 
		bottom:0px;
		right:0px;
		background-color: black;
		filter: alpha(opacity=100);
	}
	
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
  /*Agregado por Oscar(28-10-2017)*/
  .tablaEmergente{
    /*border:1px solid rgba(0,0,225,0.5);*/
    width:95%;
    height:200px;
    overflow:scroll;
  }
  .bot_reg_efec{
    padding: 5px;
    width: 200px;
    background:white;
    text-decoration: none;
    color:black;
    border-radius: 5px;
  }
  .bot_emerge_1{
    position:absolute;top:15px;right:15px;background:rgba(225,0,0,0.6);color:white;padding:20px;
  }
  .btn_emgr{padding: 10px; color:black;font-size:20px;margin:30px;}
  .btn_emgr:hover{background:#36FF33;}
</style>
<?php
 // extract($_GET);
 //die($tv);
/*impleemntacion Oscar 25.06.2019 para guardar los ids de devolucion*/
  if(isset($id_dev)){
    echo '<input type="hidden" id="id_de_devoluciones" value="'.$id_dev.'">';
  }

/*fin de cambio Oscar 25.06.2019*/
  
  if($tv!=""||$tv!=null){

?>
<!---->
    <input type="hidden" id="id_lista_mayoreo" value="<?php echo $aWRfcHJlY2lv;?>">
<!---->
<div>
  <p align="center" style="font-size:25px;font-family:fantasy;color:orange;">Venta Por Mayoreo</p>
</div>

<?php
  }
?>
<div class="eme" id="emergente_1" style="width:100%;height:160%;position:absolute;top:0;background:rgba(0,0,0,.8);display:none;z-index:100;">
  <p id="cont_emerg" style="color:white;font-size:30px;" align="center">
    
  </p>
</div>

<input type="hidden" id="tipo_venta" value="<?php echo $tv;?>">
<div id="paleta_Colores" class="Pcolores"> <!--style="display:none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 1; filter: alpha(opacity=100);"-->
    <div class="ctn_paleta">
	<form name="formaColores">
     <table width="100%" class="tablaFuera"  cellpadding="0" cellspacing="0" align="center" id="tablaPaleta">
     <tr>
     <td id="labelProductoPaleta"></td>
     <td id="cantidadProductoPaleta"></td>
     </tr>
     <tr id="">
     	<input type="hidden" name="cantidadPaleta" id="cantidadPaleta" value=""/>
      
     	<td colspan="2" width="100%">
     		<div style="height: 350px; width:100%; overflow-y: scroll; color: #333 !important;">
     		<table id="contenidoPaleta" height="300px" width="100%">
     			<tr>
       				<td style="color: #333 !important;">Rojo</td>
       				<td><input class="btn_sss" maxlength="4" type="text" name="cantidad"></td>
       			</tr>

            <tr>
        	</table>
      </div>
       </td>  


     </tr>
      
     <tr>
     <td>
    <button class="btn_ss" type="button" name="cancelar" id="cantidadPaleta99" tabindex="99" onclick="cancelaPaleta()">Cancelar</button>
     </td>
     <td>
     <button  class="btn_ss" type="button" name="enviar" tabindex="100" onclick="cierraPaleta()">Enviar</button>
     </td>
     </tr>
     </table>
     </form> 
    
    </div>


</div>

 <div id="divEspera" style="display: none; position:fixed; overflow:hidden; z-index:1000; top:0px; left:0px; bottom:0px; right:0px; background-color: black; opacity: 1; filter: alpha(opacity=100);">
    <!--Comienza la ventana en bloqueo-->
    <div class="espera_ctn">
                  <img src="img/loading.gif"> 
    <p>Esperando autorización...</p>
    <button name="cancelar" id="cancelar" data-role="button">X Cancelar</button>
  </div>
    </div>
<!--Termina la ventana en bloqueo-->
<!--Implementación de oscar 24-12-2017-->
  <?php
    if(isset($s_f_c)){
  ?>
    <div style="width:300px;" style="border:0;position:fixed;right:30px;">
      <table style="border:0;position:absolute;width:100%;background:transparent;margin:0;border-spacing : 0;">
        <tr style="background:rgba(225,0,0,0.5);">
        <?php
            if(isset($abonado)&$abonado!=0){//si se trata de un cambio en apartados
              echo '<td style="color:white;font-size:30px;padding:10px;" align="center"><b>Cambio en Apartado</b><div style="font-size:22px;">Ingrese los productos nuevos</div></td>';
            }else{
        ?>
          <td align="right" width="50%" style="font-size:20px;border:inset 0pt;color:white;">
            <b>Saldo a favor del cliente: $</b></td>
            <td width="25%" style="border: inset 0pt;">
            <input type="text" value="<?php echo $s_f_c;?>" id="saldoAFavor" style="color:black;font-size:20px;background:transparent;font-weight:bold;margin_:0;
            position:relative;left:-8px;color:white;">
          </td>
          <td width="25%" align="center">
            <a href="javascript:regresaEfectivo();" class="bot_reg_efec">Regresar efectivo</a>
          </td>
        <?php
          }//fin de else
        ?>
        </tr>
      </table>
    </div><br>
  <?php
   if(isset($abonado)&&$abonado!=0){
    echo '<br><br>';
   }
  }
  ?>
<!--Fin de cambio-->
<div data-role="content">
	 <p style="position:absolute;left:52%;">
      <button type="button" onclick="document.getElementById('resBus').style.display='none';">ocultar<br>resultados</button>
    </p>
  <div title="ocultar teclad"  style="position:absolute; display:block; width:150px;top:90px;right:14px;">
		<img style="padding-left:10px;" src="img/teclado.png" width="70" height="70">
		<p style="display:block; position:relative; top:-1.7em; left:1.2em;">
			ocultar<br>teclado
		</p>
   </div>
  <ul  data-role="listview" data-inset="true" class="ui-grid-a">
   
        <li class="ui-block-a">
          <label for="nombre">Folio</label>
      <input type="text" value="<?php echo $es_pedido ? "" : (isset($folio) ? $folio : ""); ?>" disabled="disabled" name="folioNV" id="folio_venta">
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
      <div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:1000;width:150%"></div>
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
  <div class="ui-grid-solo" style="width:98%;">
   <div class="contenedorGrid" id="contenedorGrid">
    <table id="listaProductos"   cellpadding="0" cellspacing="0">
      <tr class="cabecera">
        <td width="100"><p>Producto</p></td>
        <td width="350"><p>Descripción</p></td>
        <td width="100"><p>Cantidad</p></td>
        <td width="100"><p>Precio</p></td>
      <!--Cambio de omitir oferta en venta por mayoreo Oscar (31-10.2017)-->
      <?php
        if($tv!=1){
      ?>  
        <td width="100"><p>Oferta</p></td>
      <?php
        }
      ?>
        <td width="100"><p>Monto</p></td>
        <td colspan="10" width="0"><p></p></td>
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
        <!--<td class="tabla_descuento_producto" style="display:block;"><p></p></td> Desactivado por Oscar 31.08.2018-->
        <input type="hidden" class="es_regalo"  value="<?php echo $producto["es_regalo"]; ?>"/></td>
      </tr>
      <?php }} ?>
    </table>
    </div>
  </div>
  <!--seccion-->
<!--  <a class="regalo" id="img_regalo"  href="javascript:void(0)"><span>regalo</span></a>-->
  <div class="ui-grid-b">
    <!--<div data-role="fieldcontain">-->

      <!--Aqui se deshabilito regalo Oscar 26-10-2017
      <div style="display:block; position:relative; margin-top:0px;">
        <div style=" display:block; float:left; width:120px">
          <fieldset  data-role="controlgroup"  data-type="horizontal">
            <label>
      <input   class="rojo"  type="checkbox" name="regalo" id="es_regalo"  data-theme="a">
          Regalo	   </label>
          
            </fieldset>
            </div>
            
           </div>-->

          
                   <!-- <fieldset data-role="controlgroup" >-->
      <!--       <div style="position:relative; margin-top:43px; display:block;">
                     <a data-inline="true"  id="es_regalo" data-icon="star" data-role="button" href="javascript:void(0)" data-theme="a">
        Regalo
                </a>
                  <a  data-inline="true" data-icon="check" data-role="button" href="javascript:void(0)" id="es_pedido">
          Generar Pedido
                </a>
            
                  <a  data-inline="true" data-icon="info" data-role="button"   id="es_paquete" href="javascript:void(0)" id="es_paquete">
        Paquete
                </a>
           </div>  
            -->
<!--       
  <label>
          <input  type="checkbox" name="pedido" id="es_pedido">
          Generar pedido</label>
        <label>
          <input type="checkbox" name="paquete" id="es_paquete">
          Paquete</label>-->
     <!--     </fieldset>-->
  <!--            <div id="cuadros">
 <div class="check_boxs">
<a href="#" class="cuadro" >
</a>
<span>pedido</span>
</div>-->
<!--<div class="check_boxs">
 <a href="javascript:void(0)" class="cuadro" name="pedido" id="es_pedido"></a>
<span>pedido</span>
</div>
<div class="check_boxs">
<a href="javascript:void(0)" class="cuadro" id="es_paquete" name="paquete" ></a>
<span>paquete</span>
</div
</div>>-->
        <!--  </div>-->
      </div>
      <center>
      <div style="width:48%;float:left;"><!--class="ui-block-b"-->
        <button data-theme="a" name="cerrar" id="cerrar" class="btn1" style="min-width:180px; margin-right:50px;">✔ Cerrar venta</button>
      </div>
      </center>

      <div class="ui-block-c" style="float:right;width:48%;">    
    <!--Implementado por Oscar (29-10-2017)-->
      <div style="float:right;" data-role="fieldcontain"><!--data-role="fieldcontain"-->
        <label>
                <strong style="font-weight:bold; font-size:20px; padding-right: 2px;">
                  Total
                </strong>
              </label>
              <input type="text" style="font-size:30px;width:100%;" readonly value="$ <?php echo number_format($subtotal, 2); ?>"  name="total" id="total">
      </div>
    </div>
  <!--Termina seccion-->
</div>
      <br>
      <div class="ui-grid-a" style="border:1px solid;">
      <div class="ui-block-b">
        <div style=" display:none;float:left; width:175px">
         <fieldset  data-role="controlgroup"  data-type="horizontal">
           <label data-mini="true">
			  	    <input  type="checkbox" name="pedido" id="es_pedido" disabled="disabled" >Generar pedido
           </label>
         </fieldset>
        </div>
    <!--Descuentos-->
    
        <div style="display:block;float:left;width:100%;border:0;">
        <table border="0" style="width:200%;background:transparent;border:0px;">
          <tr>
            <td width="30%;" align="center" style="display:none;">
             <fieldset  data-role="controlgroup" data-type="horizontal">
               <label data-mini="true" style="width:100px;padding:10px;text-align:center;">
                  <input type="checkbox" name="paquete" id="es_paquete"/> <?php echo 'Paquete';?>
                </label>
              </fieldset>
            </td>
            <td width="20%" align="center" style="border: inset 0pt;">
              <a href="javascript:activa_paq();" style="text-decoration:none;">
                <p id="paq1" style="color:black;border:0;width:80px;height:40px;background:transparent;color:black;border:1px solid black;border-radius:5px;vertical-align:middle;">
                  Paquete<img src="../img/paquete.png" width="40px" height="40px">
                </p>
              </a>
            </td>
            <td width="20%" align="center" onclick="habilitaDesc(1);" style="border: inset 0pt;">
               <span style="fontsize=20px;font-weight:bold;" >-%</span>
                <input type="number" id="porcGeneral" style="padding:10px;width:80%;border:2px solid;fontsize=20px;font-weight:bold;border-radius:5px;background:white;"
                onkeyup="calculaTotalVenta(1);" value="" ><!--autocomplete="ÑÖcompletes"-->
            </td>
            <td width="20%" align="center" onclick="habilitaDesc(2);" style="border: inset 0pt;">
               <span style="fontsize=20px;font-weight:bold;" >-$</span>
                <input type="number" id="descGeneral" style="padding:10px;width:80%;border:2px solid;fontsize=20px;font-weight:bold;border-radius:5px;background:white;"
                onkeyup="calculaTotalVenta(2);" value="" name="descGeneral" ><!--x-autocompletetype="" autocomplete="off"-->
            </td>
          </tr>
        </table>
        </div>
    
    </div>
           </div>
</div>

<!--Implementación Oscar 13.11.2018 para guardar el temporal en los cambios sobre devolución
  <input type="hidden" id="detalle_nota_tmp" value="">
Fin de cambio Oscar 13.11.2018-->

<input type="hidden" id="cantidades_capturadas" value="">
<!--implementación Oscar 03.09.2018 para cargar apartados-->
  <?php
/*Implementación Oscar 02.03.2019 para el status de la devolucion*/
      echo '<input type="hidden" value="'.$id_ped.'" id="id_de_pedido_original">';
/*Fin de cambio Oscar 02.03.2019*/
      echo '<input type="hidden" value="'.$abonado.'" id="monto_abonado">';
    if($es_apart==1){
      echo '<input type="hidden" value="'.$id_ped.'" id="id_de_apartado">';
      echo '<script type="text/JavaScript">carga_ped_apartado('.$id_ped.',0);</script>';//mandamos llamar la función que carga el apartado en la tabla
      //window.onload= function carga(){ setTimeout("carga_ped_apartado('.$id_ped.')",3000);
    }
    /*implementación para cargar descuento de una nota anterior*/
      if(isset($dsc)&&$dsc>0){
        echo '<input type="hidden" id="desc_anterior" value="'.round($dsc*100).'">';
        //echo '<script type="text/JavaScript">carga_ped_apartado(0,1);</script>';
        //echo '<script>window.onload=document.getElementById(\'porcGeneral\').value=\''.round($dsc*100).'\';</script>';
      }
    /**/

/***/
  if(isset($dHJhbnNmZXJlbmNpYQ) && $dHJhbnNmZXJlbmNpYQ!=''){
    echo '<script type="text/JavaScript">carga_ped_apartado(0,'.$dHJhbnNmZXJlbmNpYQ.');</script>';
  }
/***/
  ?>
<!--window.onload = function() {
  init();
  doSomethingElse();
};-->