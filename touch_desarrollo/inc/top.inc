<!--div class=""><data-role="header" data-id="header1"-->

  <!--<img src="img/logo-casa.png" width="5%" style=" display:block; float:left; position:relative; top:-0.5em;"><h1>Punto de Venta</h1>
 
  <a data-role="button"  href="#page1" class="ui-btn-right" onclick="cierraSesion()"> Cerrar Sesion </a>-->
    <div class="row menu_container " id="menus_touch"><!-- data-role="navbar" data-iconpos="top"  -->
        <div class="col-3 text-center menu_item<?php echo $scr == "nueva-venta" ? " active_menu" : ""; ?>"> 
          <a 
            id="menu_nueva" 
            href="index.php?scr=nueva-venta" 
            data-transition="fade" 
            data-ajax="false" 
            data-theme="" 
            data-icon="plus" 
            class="" 
            <?php if($scr != '' && $scr != 'home'){ ?>
            onclick="deleteLocalStorageProducts( true );return confirm('¿Realmente desea salir sin guardar los cambios?');"<?php } ?>
          > 
          <i class="icon-ok-circle"></i><br>
          Nueva Venta
        </a> 
        </div>
        <div class="col-3 text-center menu_item<?php echo $scr == "cerrar-venta" ? " active_menu" : ""; ?>"> 
          <a id="menu_cerrar" href="<?php echo $scr != "nueva-venta" ? "javascript:void(0)" : "index.php?scr=cerrar-venta"; ?>" data-transition="fade" data-ajax="false" data-theme="" data-icon="check" 
            class=""> 

          <i class="icon-basket-1"></i><br>
          Cerrar Venta </a> </div>
        <div class="col-3 text-center menu_item <?php echo $scr == "cambios-devoluciones" ? " active_menu" : ""; ?>"> <a id="menu_modificar" href="index.php?scr=cambios-devoluciones&pag=1" data-transition="fade" data-ajax="false" data-theme="" data-icon="edit" class="" <?php if($scr != '' && $scr != 'home'){ ?>onclick="return confirm('¿Realmente desea salir sin guardar los cambios?')"<?php } ?>> 
          <i class="icon-edit"></i><br>Cambio y Devoluciones </a> </div>
        <div class="col-3 text-center menu_item<?php echo $scr == "pagos-apartados" ? " active_menu" : ""; ?>"> <a id="menu_pagos" href="index.php?scr=pagos-apartados&pag=1" data-transition="fade" data-ajax="false" data-theme="" data-icon="check" class="" <?php if($scr != '' && $scr != 'home'){ ?>onclick="return confirm('¿Realmente desea salir sin guardar los cambios?')"<?php } ?>> 
          
          <i class="icon-dollar"></i><br>
          Pagos y Apartados </a> </div>
    </div>
  <!--/div-->
  
  <!--Oscar 31.05.2018; Se implementa la variable "pag" en las variables enviadas por get en los menús de pagos y apartados, cambios y devoluciones-->
 