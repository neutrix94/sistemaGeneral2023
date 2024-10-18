<!---Footer-->

<div class="row footer" id="pie">
  <div class="col-4 text-center footer_item">
    <button 
      class="btn btn-light" 
      onclick="regresa(1);">
      <!--img src="img/logo-casa.png" width="8%" style="display:inline-block; width:10%; vertical-align:middle"> style=" display:inline-block; vertical-align:middle"--><h5 class="text-center icon-hand-pointer-o">Punto Venta</h5>
    </button>
  </div>
  <div class="col-4 text-center footer_item">
    <button
      class="btn btn-light icon-cart"
      style="display:none;"
      id="current_sale_btn"
      onclick="location.href='index.php?scr=nueva-venta#';"
    >Venta Actual</button>
  </div>
  <div class="col-4 text-center footer_item">  
    <a class="btn btn-light icon-home-1"  onclick="back_to_panel()">Regresar al Panel</a><!--href="#page1"-->
    <!--a href="#page1" class="btn btn-light icon-off" onclick="cierraSesion()"></a-->
  </div>
</div>

<!--Este cierre de div lo meti para no afectar la ventana emergente-->
</div>


<!-- -->
  <audio id="enabled_exhibition" controls style="display : none;">
    <source type="audio/wav" src="../files/sales/enabled_exhibition.mp3">
  </audio>
  <audio id="error" controls style="display : none;">
    <source type="audio/wav" src="../files/sounds/error.mp3">
  </audio>
<!-- -->
<?php
/**/
    if(isset($id_ped)){
      echo '<script>$("#menus_touch").css("display","none");$("#pie").css("display","none");</script>';
    }
/**/
?>
<!---->
  <script>
/**/
  function activaCotizacion(){
    location.href = "index.php?scr=nueva-venta&is_quotation=1";
  }
/*fin de cambio Oscar 2023*/

function activaMayoreo( is_quotation = false ){
  //elimina el session Storage Oscar 2023
  //sessionStorage.clear();
  //Antes de eliminar todo el sessionStorage, guardamos temporalmente las claves que se utilizan para el surtimiento
  const idPedidoTemp = sessionStorage.getItem('ID_PEDIDO_TEMP');
  const saleProductsList = sessionStorage.getItem('SALE_PRODUCTS_LIST');

  // Limpiar sessionStorage
  sessionStorage.clear();

  // Restaurar los valores de los elementos que no quieres eliminar
  if (idPedidoTemp !== null) {
      sessionStorage.setItem('ID_PEDIDO_TEMP', idPedidoTemp);
  }

  if (saleProductsList !== null) {
      sessionStorage.setItem('SALE_PRODUCTS_LIST', saleProductsList);
  }

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
  $( '#wholesale' ).attr( 'onclick', "verificaMayoreo(" + ( is_quotation == 1 ? "1" : "" ) + ")" );
//enfocamos en la clave de mayoreo
  $("#clave_lista_precio").focus();
  //$('.cabecera').css('background','red');
  //$('.cabecera').css( 'border-color','white');
}
       function regresa(f){
           if(confirm("¿Realmente deseas salir al inicio del Punto de Venta?")){
          //elimina el session Storage Oscar 2023
            sessionStorage.clear();
            if(f==1){
              location.href='index.php?';
            }/*else{
              location.href="../index.php";
            }*/
          }
        }

        function back_to_panel(){
           if(confirm("¿Realmente deseas salir al Panel Principal?")){
            location.href="../index.php";
          }
        }
              
      function cierraSesion(){
        if(confirm("¿Realmente desea salir de la sesion actual?")){
        //implementacion Oscar 2023 para eliminar session Storage
          sessionStorage.clear();
          location.href="../index.php?cierraSesion=YES";

        }
                
        }

        var audio_is_playing = false;
        function alert_scann( type ){
          if( audio_is_playing ){
            audio = null;
          }
          var audio = document.getElementById( type );
          
          audio_is_playing = true;
          audio.currentTime = 0;
          audio.playbackRate = 1;
          audio.play();
        }
       
   </script>
   <script type="text/javascript">
/*Implementacion Oscar 2023 para evitar el boton de venta actual ( session Storage )*/
    if( sessionStorage.getItem('SALE_PRODUCTS_LIST') != null && sessionStorage.getItem('SALE_PRODUCTS_LIST') != '' ){
    //  getLocalStorageProducts();
      document.getElementById( 'current_sale_btn' ).style.display='block';
    }
/*fin de cambio oscar 2023*/
   </script>