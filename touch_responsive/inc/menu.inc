
<nav>
             <ul>
              <li><a id="menu_nueva"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=nueva"; ?>"> <img src="img/mas.png" <?php  if($scr != 'logo'){echo "style='opacity:0.4'";} ?>  alt=""/>Nueva Venta</a></li>
              <li><a id="menu_cerrar"  href="<?php echo $scr != "nueva" ? "javascript:void(0)" : "index.php?scr=cerrar"; ?>"><img src="img/cerrar.png" <?php if($scr != 'nueva') echo "style='opacity:0.4'"; ?>  alt=""/>Cerrar  Venta</a></li>
              <li><a id="menu_modificar"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=modificar"; ?>"><img src="img/devo.png"  alt="" <?php if($scr != 'logo'){echo "style='opacity:0.4'";} ?>/>Cambios, devoluciones y pedidos</a></li>
              <li><a id="menu_pagos"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=pagos"; ?>"><img src="img/pagos.png" alt="" <?php if($scr != 'logo'){echo "style='opacity:0.4'";} ?>/>Pagos y apartados</a></li>
             </ul>  
         </nav>

<!--  <nav>
             <ul>
              <li><a id="menu_nueva"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=nueva"; ?>"> <img src="<?php echo $scr != "logo" ? "disable" : ""; ?>img/mas.png"  alt=""/>Nueva Venta</a></li>
              <li><a id="menu_cerrar"  href="<?php echo $scr != "nueva" ? "javascript:void(0)" : "index.php?scr=cerrar"; ?>"><img src="<?php echo $scr != "nueva" ? "disable" : ""; ?>img/cerrar.png"  alt=""/>Cerrar  Venta</a></li>
              <li><a id="menu_modificar"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=modificar"; ?>"><img src="<?php echo $scr != "logo" ? "disable" : ""; ?>img/devo.png"  alt=""/>Cambios, devoluciones y pedidos</a></li>
              <li><a id="menu_pagos"  href="<?php echo $scr != "logo" ? "javascript:void(0)" : "index.php?scr=pagos"; ?>"><img src="<?php echo $scr != "logo" ? "disable" : ""; ?>img/pagos.png" alt=""/>Pagos y apartados</a></li>
             </ul>  
         </nav>

        </li>


-->