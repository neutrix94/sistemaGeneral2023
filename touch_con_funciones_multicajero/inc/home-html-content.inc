<div data-role="content">
   
   <div  style="width:400px; margin:0 auto;">
    <?php
//
    if($mayoreo!='0'){  
  ?>  
    <div onclick="activaMayoreo();" style="position:absolute;top:10%;left:0%;background:rgba(210,225,0,.8);width:150px;height:50px;text-align:center;color:white;
    border-radius:5px;"><!--position:absolute;top:70%;left:70%;-->
      <p>Venta Especial</p>
    </div>
  <?php
    }
  ?>
  <img src="img/logo-casa.png" width="366" height="594" alt=""/> 
  </div>
  
  </div>
  <!--Implementación de Administración de paquetes Oscar 27.03.2018 -->
    <p align="center" class="pqt" onclick="location.href='index.php?scr=paquetes';">
      <img src="img/paquete.png" height="70%" width="70%" title="ingresa para administrar paquetes">
      <br>Paquetes
    </p>

  <!--Implementación Oscar 03.03.2019 para las devoluciones que quedaron pendientes-->
    <p align="center" class="pqt" style="top:30%;" onclick="location.href='../code/general/listados.php?tabla=ZWNfZGV2b2x1Y2lvbg==&no_tabla=MQ==';">
      <img src="img/devolucion.png" height="50%" width="70%" title="ingresar al listado de devoluciones pendientes">
      <br>Devoluciones<br>Pendientes
    </p>
  <!--Implementación de Cobros de ventas Oscar 27.03.2018 -->
  <!--boton redirecciona catalogo-->

    <p align="center" class="pqt" style="top:50%;" onclick="window.open('../../pagina/productos.php');">
      <img src="../img/icono-catalogo.png" height="70%" width="75%" title="Catálogo">
      <br>Catálogo
    </p>
<?php
/*if($perfil_usuario==7){*/
  $sql="SELECT IF(p.ver=1 OR p.modificar=1,1,0) 
      FROM sys_permisos p
      LEFT JOIN sys_users_perfiles perf ON perf.id_perfil=p.id_perfil
      LEFT JOIN sys_users u ON u.tipo_perfil=perf.id_perfil 
      WHERE p.id_menu=200
      AND u.id_usuario=$user_id";
  //die($sql);
  $eje=mysql_query($sql)or die("Error al consultar el permiso de cajero!!!<br>".mysql_error()."<br>".$sql);
  $es_cajero=mysql_fetch_row($eje);
  if($es_cajero[0]==1){
    $sql="SELECT multicajero FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!<br>".mysql_error());
    $r_c=mysql_fetch_row($eje);
   // die('here');
    if($r_c[0]==1){
?>
    <p align="center" class="pqt" style="top:70%;" onclick="location.href='../code/especiales/tesoreria/cobros/cobros.php?';">
      <img src="img/cobros.png" height="70%" width="70%" title="ingresae para cobrar ventas">
      <br>Cobrar
    </p>
<?php
  }
}
?>
<style type="text/css">
  .pqt{border-radius: 100%;border:2px solid brown;width: 100px;height: 100px;position: absolute;top:10%;padding: 5px;right:5%;z-index: 2;color: brown;}
  .pqt:hover{
    border:4px solid gray;
    background:green;
    color: white;
  }
</style>
<!--Fin de implementación Oscar 27.03.2018-->