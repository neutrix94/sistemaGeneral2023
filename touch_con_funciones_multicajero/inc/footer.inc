<!---Footer-->

<div data-role="footer" id="pie">
<div style="width:210px; display:block; margin:0 auto;">
  <a href="javascript:regresa(1);" style="color:white;">
    <img src="img/logo-casa.png" width="8%" style="display:inline-block; width:10%; vertical-align:middle"><h5 style=" display:inline-block; vertical-align:middle"> Casa de las Luces 2017</h5>
  </a>
 <a data-role="button"  href="#page1" class="ui-btn-left" onclick="regresa()"> Ir a panel  </a>
 <a data-role="button"  href="#page1" class="ui-btn-right" onclick="cierraSesion()"> Cerrar Sesion </a>
  </div>
</div>
<!--Este cierre de div lo meti para no afectar la ventana emergente-->
</div>
<?php
/**/
    if(isset($id_ped)){
      echo '<script>$("#menus_touch").css("display","none");$("#pie").css("display","none");</script>';
    }
/**/
?>
<!---->
  <script>
       
function activaMayoreo(){
  var em=document.getElementById('emergePermisos');
  em.style.display='block';
//ocultamos menu y botones
   $('.ui-btn-inner').css('display','none');
   $('#pie').css('display','none');
   if($('#es_regalo')){$('#es_regalo').css('display','none');}
   if($('#es_paquete')){$('#es_paquete').css('display','none');}
   if($('#cerrar')){$('#cerrar').css('display','none');}
   if($(".ui-controlgroup-controls")){$(".ui-controlgroup-controls").css("display","none")}
   document.getElementById('passWord').focus();
//enfocamos en la clave de mayoreo
  $("#clave_lista_precio").focus();
  //$('.cabecera').css('background','red');
  //$('.cabecera').css( 'border-color','white');
}
       function regresa(f){
           if(confirm("¿Realmente desea salir de la aplicacion de ventas?"))
                
            if(f==1){
              location.href='index.php?';
            }else{
                location.href="../index.php";
            }
        }
              
      function cierraSesion(){
        if(confirm("¿Realmente desea salir de la sesion actual?"))
           location.href="../index.php?cierraSesion=YES";
                
        }
       
   </script>