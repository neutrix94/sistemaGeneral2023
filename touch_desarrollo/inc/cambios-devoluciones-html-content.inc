<style type="text/css">
  /*.res_busc{
    width:500px;
    height:300px;
    overflow: auto;
    background: white;
    position: fixed;
    z-index: 100;
    border:1px solid green;
    top:150px;
    left:30px;
    display: none;
  }
  .emergent{
    position: fixed;
    width: 100%;
    height: 100%;
    left : 0;
    top : 0;
    background-color: rgba( 0, 0, 0, .3);
    z-index: 3;
    display: none;
  }
  .emergent_content{
    position: relative;
    top : 100px;
    width: 95%;
    left: 0.5%;
    background-color: white;
    box-shadow: 1px 1px 15px rgba( 0, 0, 0, .5 );
    padding: 10px;
    max-height: 80%;
    overflow-y : auto;
  }*/
</style>
<?php
  //include("../conectMin.php");//incluimos la librería de conexión
  $pagina=$_GET['pag'];//recibimos # de página
  $a_fech=date("Y");//sacamos el año en curso
//consultamos número de registros
  $sql="SELECT count(*) FROM ec_pedidos WHERE fecha_alta LIKE '%$a_fech%' AND id_sucursal=$sucursal_id";
  //die($sql);
  $eje=mysql_query($sql)or die("Error al consultar el número de notas de venta!!!\n\n");
  $r=mysql_fetch_row($eje);
  //die('totales: '.$r[0]);
//calculamos el número de páginas
  $num_pags=ceil($r[0]/30);
//creamos variable que guarda total de registros
  echo '<input type="hidden" id="total_registros_notasDev" value="'.$r[0].'">';
//creamos variable que guarda total de páginas
  echo '<input type="hidden" id="total_paginas_notasDev" value="'.$num_pags.'">';
//sacamos dato del limit
  if($pagina==1){
    $inicio=0;
    $fin=29;
  }else{
    $inicio=($pagina*30);
    $fin=(($pagina+1)*30-1);
  }
?>

  <div class="emergent">
    <div tabindex="1" style="position: relative; top : 0 !important; left: 90%; z-index:1; display:none;">
      <button 
        class="btn btn-danger"
        onclick="close_emergent();"
      >X</button>
    </div>
    <div class="emergent_content" tabindex="1"></div>
  </div>

<div class="row">

    <div title="ocultar teclado" class="col-12 text-center">
      <img src="img/teclado.png" width="70" height="70"><br><!--  style="padding-left:13px;"-->
      <p style="">Ocultar<br><!-- display:block; position:relative; top:-1.7em; left:.5em;-->
      teclado</p>
    </div>
<!--      <div data-role="controlgroup" data-type="horizontal" data-mini="true"> 
     <a href="#popupLogin" data-inline="true" id="salirbtn"   data-role="button" data-icon="minus" onclick="if(confirm('¿Realmente desea salir sin guardar los cambios?'))location.href='index.php?src=home'"> Cerrar ventana</a> 
 </div>-->
<!--Comienza el popup-->
 
  <!--Termina el popup-->
  <!---Comienza ñla sección de pedido--> 
  <div class="row">
   
  <!--form action="" method="post">algo.php -->
        <div class="col-12">
          <div class="input-group">
            <input 
              type="text" 
              name="cliente" id="seeker" 
              onkeyup="seek( event );"
              placeholder="Búsqueda por Folio, Nombre del cliente,fecha,monto"
              class="form-control"
            >
            <button 
              type="button" 
              class="btn btn-primary"
              onclick="busca();"
            >
              <i class="icon-search"></i>
            </button>
          </div>
        </div>
  <!--/form-->
   <br><br>
   </div>

      <!--Resultados de búsqueda-->
      <div id="res_busqueda" class="res_busc">
        
      </div>

  <!---Grid-->
    <div class="row list_container">
        <table style="display:block; float:left;"  id="notasDev" cellpadding="0" cellspacing="0" Alto="250" conScroll="S" validaNuevo="false" AltoCelda="40"
            auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=5"
            verFooter="N" guardaEn="false" listado="N" class="" paginador="N" datosxPag="30" title="Listado de Registros" numPag="1"><!--Se agrega atributo datosxPag="30" Oscar 31-05-2018-->
              <!--cambiado por Oscar 28-10-2017-->  
                <tr class="cabecera" >
                   <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="center" campoBD="p.nombres" on_Click="verDetalleP('#')"  style="padding : 20px !important;">Folio</td>
                    <td tipo="texto" width="250" offsetWidth="250" modificable="S" align="center" campoBD="cantidad" on_Click="verDetalleP('#')">Cliente</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="center" campoBD="cantidad" on_Click="verDetalleP('#')">Fecha</td>
                    <td tipo="texto" width="120" offsetWidth="120" modificable="S" align="right" campoBD="cantidad" on_Click="verDetalleP('#')" mascara="$#,###">Monto</td>
                    <td tipo="texto" width="120" offsetWidth="120" modificable="S" align="center" campoBD="cantidad" on_Click="verDetalleP('#')"  style="padding : 20px !;">Validada</td>
                </tr>
            </table>

      <script>        
          CargaGrid('notasDev');
      </script> 
    </div>
  
  <br/>

    <div class="row text-center">
      <div class="col-2"></div> 
      <div class="col-8">
        <button 
          type="button" 
          class="btn btn-success form-control"
          name="submit"   
          onclick="modifica()">
          <i class="icon-right-big">Siguiente</i>
        </button>
      </div>
	  </div>
</div>
  <!---Script del grid-->
  <script>
    
        var id_pedido='NO';
        var was_validated = '';

        function validaCan(pos, val){
            valAnt = celdaValorXY('pagosDev', 6, pos);
            
            valAnt = parseInt(valAnt+"");
            valNue = parseInt(val+"");
            
            if(valNue > valAnt)
            {
                alert('No es posible devolver una cantidad mayor a los articulos adquiridos');
                return false;
            }
            
            return true;
        }
      
    //mofificaciones Oscar(28-10-2017)
      //funcion al hacer click direcciona a seleccion de productos
        function verDetalleP(pos){
            var aux=celdaValorXY('notasDev', 0, pos);
            //alert(aux);
            //return false;
            //var url="pedidosBusca.php?tipo=6&id_pedido="+aux;
            //RecargaGrid('pagosDev', url);
          //verifica que no esta validada parcialmente
            var url = 'pedidosBusca.php?tipo=7&id_pedido='+aux;
            var response = ajaxR( url ).trim();
            if( response != 'ok' ){
              //alert( response );
              $( '.emergent_content' ).html( response );
              $( '.emergent' ).css( 'display', 'block' );
              return false;
            }
            
            RecargaGrid('notasDev', 'pedidosBusca.php?tipo=5&id_pedido='+aux);
            id_pedido=aux;
            was_validated = ( $( '#notasDev_5_' + pos ).attr( 'valor' ) == "<i class='icon-cancel-circled' style='color : red;'></i>" ? 0 : 1 );
           // alert( was_validated );
            return false;
        }
        
        function seek( e ){
          if( e.keyCode == 13 ){
            busca();
          }
        }

        function busca(obj){
            var url="pedidosBusca.php?tipo=5&folio=" + $( '#seeker' ).val();//+obj.value;
            RecargaGrid('notasDev', url);
            //alert(url);
        }
        
        
        function validaCamCan(pos){
            
            //alert(pos);
            
            //alert(celdaValorXY('pagosDev', 3, pos));
            
            if(celdaValorXY('pagosDev', 3, pos) == 1){
                return true;
            }
            
            return false;
            
        }
       
        
        function modifica(){
            if(id_pedido == 'NO'){
                alert('Debe elegir un pedido primero');
                return false;
            }
          /*implementacion Oscar 2024-03-07 para validar que no se haga devolucion sobre una misma nota cuya devolucion no ah pasado por pantalla de cobros*/
            var url = "pedidosBusca.php?tipo=8&id_pedido=" + id_pedido;
            var resp = ajaxR( url );
            if( resp.trim() != 'ok' ){
              alert( resp );
              return false;
            }
            was_validated = ( $( '#notasDev_5_0'  ).attr( 'valor' ) == "<i class='icon-cancel-circled' style='color : red;'></i>" ? 0 : 1 );
            var num=NumFilas('pagosDev');
            var dets="";
            var ncan="";
            
            
            for(i=0;i<num;i++)
            {
                if(celdaValorXY('pagosDev', 3, i) == 1)
                {
                    if(dets != '')
                    {
                        dets+=",";
                        ncan+=",";
                    }
                    
                    dets+=celdaValorXY('pagosDev', 0, i);
                    ncan+=celdaValorXY('pagosDev', 6, i);
                    
                    var url="ajax/validaProdDev.php?id_detalle="+celdaValorXY('pagosDev', 0, i)+"&cantidad="+celdaValorXY('pagosDev', 6, i);
                    var res=ajaxR(url);
                    
                    //var aux=res.split('|');
                    
                    if(res != 'exito'){
                        alert(res);
                        return false;
                    }
                    
                }
            }
            if( was_validated == 0 ){
              location.href="index.php?scr=devolucion-seleccion&id_pedido="+id_pedido+"&dets="+dets+"&cans="+ncan;
            }else if( was_validated == 1 ){
              location.href="../sale_return/index.php?scr=devolucion-seleccion&id_pedido="+id_pedido+"&dets="+dets+"&cans="+ncan;
            }
           //location.href="index.php?scr=modificar2&id_pedido="+id_pedido+"&dets="+dets+"&cans="+ncan;
        }

        function close_emergent(){
          $( '.emergent_content' ).html( '' );
          $( '.emergent' ).css( 'display', 'none' );
        }
        
    </script> 
      
      <!---Termina el script del grid-->
  <!--Termina seccion-->
</div>
<style type="text/css">
  .celda_notasDev_Cabecera{
    padding: 20px !important;
  }
</style>