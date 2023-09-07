<!---Footer-->

<div id="pie">
  <div class="row" style="padding-top: 15px !important;"><!-- style="width:210px; display:block; margin:0 auto;" -->
    <div class="col-3">
      <a data-role="button" href="#page1" class="btn form-control btn-light btn_footer" onclick="regresa()"> 
        <i class="icon-home"></i><br />Ir a panel  </a>
    </div>
    <div class="col-6">
      <a href="javascript:regresa(1);" style="color:white; text-align : center;">
        <center>
        <img src="../img/img_casadelasluces/Logo.png" width="3%" class="footer_img" style="vertical-align:middle; display :block;">
        <h6 style=" display:inline-block; vertical-align:middle;font-size : 70%;" class="footer_txt"> Casa de las Luces 2021</h6>
        </center>
      </a>
    </div>
    <div class="col-3">
      <a data-role="button" href="#page1" class="btn btn-danger form-control btn_footer" onclick="cierraSesion()">
      <i class="icon-off"></i> Cerrar Sesion </a>
    </div>
  </div>
</div>
<!--Este cierre de div lo meti para no afectar la ventana emergente-->
<!--/div-->
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