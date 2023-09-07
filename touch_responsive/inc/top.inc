<!--div data-role="header" data-id="header1" class="row" style="width:100%;"-->

  <!--<img src="img/logo-casa.png" width="5%" style=" display:block; float:left; position:relative; top:-0.5em;"><h1>Punto de Venta</h1>
 
  <a data-role="button"  href="#page1" class="ui-btn-right" onclick="cierraSesion()"> Cerrar Sesion </a>-->
    <div data-role="navbar" data-iconpos="top" id="menus_touch" class="row no_padding" style="width:100%;">
      <!--ul-->
        <div class="col-3 row_mnu"> 
          <i class="icon-plus-circle"></i><br>
          <a id="menu_nueva" href="index.php?scr=nueva-venta" data-transition="fade" data-ajax="false" data-theme="" data-icon="plus" class="<?php echo $scr == "nueva-venta" ? "ui-btn-active ui-state-persist" : ""; ?>" <?php if($scr != '' && $scr != 'home'){ ?>onclick="return confirm('¿Realmente desea salir sin guardar los cambios?')"<?php } ?>> Nueva Venta </a> 
        </div>

        <div class="col-3 row_mnu"> 
          <i class="icon-share"></i><br>
          <a id="menu_cerrar" href="<?php echo $scr != "nueva-venta" ? "javascript:void(0)" : "index.php?scr=cerrar-venta"; ?>" data-transition="fade" data-ajax="false" data-theme="" data-icon="check" class="<?php echo $scr == "cerrar-venta" ? "ui-btn-active ui-state-persist" : ""; ?>"> Cerrar Venta </a> 
        </div>

        <div class="col-3 row_mnu"> 
          <i class="icon-pencil-neg"></i><br>
          <a id="menu_modificar" data-transition="fade" data-ajax="false" data-theme="" data-icon="edit" class="<?php echo $scr == "cambios-devoluciones" ? "ui-btn-active ui-state-persist" : ""; ?>" <?php if($scr != '' && $scr != 'home'){ ?><?php } ?>> Cambios y Dev </a> 
        </div><!-- onclick="return confirm('¿Realmente desea salir sin guardar los cambios?')" href="index.php?scr=cambios-devoluciones&pag=1"-->

        <div class="col-3 row_mnu"> 
          <i class="icon-bag"></i><br>
          <a id="menu_pagos" data-transition="fade" data-ajax="false" data-theme="" data-icon="check" class="<?php echo $scr == "pagos-apartados" ? "ui-btn-active ui-state-persist" : ""; ?>" <?php if($scr != '' && $scr != 'home'){ ?><?php } ?>> Pagos y Apart </a> 
        </div><!-- onclick="return confirm('¿Realmente desea salir sin guardar los cambios?')" href="index.php?scr=pagos-apartados&pag=1"-->
      <!--/ul-->
    </div>
  <!--/div-->
  
  <!--Oscar 31.05.2018; Se implementa la variable "pag" en las variables enviadas por get en los menús de pagos y apartados, cambios y devoluciones-->
 