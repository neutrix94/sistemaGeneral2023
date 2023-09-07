<style type="text/css">
  .res_busc{
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

<center>
<div data-role="content">

    <div title="ocultar teclado" style="width:20%;float:right;"  style="position:relative; display:block; width:300px; float:right;">
      <img style="padding-left:13px;" src="img/teclado.png" width="70" height="70"><br>
      <p style="display:block; position:relative; top:-1.7em; left:.5em;">Ocultar<br>
      teclado</p>
    </div>
<!--      <div data-role="controlgroup" data-type="horizontal" data-mini="true"> 
     <a href="#popupLogin" data-inline="true" id="salirbtn"   data-role="button" data-icon="minus" onclick="if(confirm('¿Realmente desea salir sin guardar los cambios?'))location.href='index.php?src=home'"> Cerrar ventana</a> 
 </div>-->
<!--Comienza el popup-->
 
  <!--Termina el popup-->
  <!---Comienza ñla sección de pedido--> 
  <div class="ui-grid-solo" style="width:80%;border:0;float:left;">
   
  <form action="algo.php" method="post">
 <fieldset>
   <ul data-role="listview" data-inset="true" class="ui-grid-b">
        <li class="ui-block-b">
      <!--  <input type="text" name="folio" placeholder="Folio">-->
    </li>
        <li lass="ui-block-b" style="width:500px;">
       
            <input type="text" name="cliente" placeholder="Búsqueda por Folio, Nombre del cliente,fecha,monto" onkeyup="busca(this)">
            </li>
             <li class="ui-block-b" style="background: transparent; zoom:1; -webkit-transform:scale(1.5,1.5);transform:scale(1.5,1.5); width:40px !important ;  display:block; position:relative;">
    
           <!-- <button  type="button" name="submit"  data-iconpos="notext"  data-iconpos="bottom" class="buscar" data-icon="search" onclick="buscaPedidoPago(this.form)">Buscar</button>-->
       </li>
       </fieldset>
  </form>
   </div>

      <!--Rsultaodos de búsqueda-->
      <div id="res_busqueda" class="res_busc">
        
      </div>
 </center>
  <!---Grid-->
  <div class="ui-grid-solo">
    <div class="contenedorGrid">
        <table style="display:block; float:left;"  id="notasDev" cellpadding="0" cellspacing="0" Alto="250" conScroll="S" validaNuevo="false" AltoCelda="40"
            auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=5"
            verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" datosxPag="30" title="Listado de Registros" numPag="1"><!--Se agrega atributo datosxPag="30" Oscar 31-05-2018-->
              <!--cambiado por Oscar 28-10-2017-->  
                   <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="center" campoBD="p.nombres" on_Click="verDetalleP('#')">Folio</td>
                    <td tipo="texto" width="250" offsetWidth="250" modificable="S" align="left" campoBD="cantidad" on_Click="verDetalleP('#')">Clientes</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="center" campoBD="cantidad" on_Click="verDetalleP('#')">Fecha</td>
                    <td tipo="texto" width="120" offsetWidth="120" modificable="S" align="right" campoBD="cantidad" on_Click="verDetalleP('#')" mascara="$#,###">Monto</td>
                    <!--<td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
                        <img class="vermini" src="../img/vermini2.png" height="22" width="22" border="0"  onclick="verDetalleP('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Continuar"/>
                    </td>-->   
                </tr>
            </table>

            <script>        
                CargaGrid('notasDev');
            </script> 
		  
          </div>
  </div>
  <!--seccion-->
  <br/>
  <br/>
  <!--
  <div class="ui-grid-solo">
  <label style=" position:relative; padding-bottom:12px; font-size:20px;color:#6C9831;  margin-left:23px; display:block;">Selecciona el producto a devolver en la tabla de abajo</label>
    <div class="contenedorGrid">
      <table  width="100%"  id="pagosDev" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="false" AltoCelda="70"
        auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=6"
        verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
        <tr>
          <td  tipo="oculto" width="" offsetWidth="" campoBD="p.id_productos">id_pedido_detalle</td>
          <td  tipo="oculto" width="" offsetWidth="" campoBD="p.id_productos">id_pedido</td>
          <td  tipo="oculto" width="" offsetWidth="" campoBD="p.id_productos">id_producto</td>
          <td tipo="binario" width="" offsetWidth="" modificable="S">-</td>
          <td  tipo="texto" width="" offsetWidth="" modificable="N" align="left" campoBD="p.nombres">Producto</td>
          <td tipo="texto" width="" offsetWidth="" modificable="N" align="left" campoBD="cantidad">Descripcion</td>
          <td tipo="texto" width="" offsetWidth="" modificable="S" align="center" campoBD="cantidad" mascara="#,###" on_Click="validaCamCan('#')" valida="validaCan('#', '$DATO')">Cantidad</td>
          <td tipo="texto" width="" offsetWidth="" modificable="N" align="right" campoBD="cantidad" mascara="$#,###">Precio</td>
          <td tipo="texto" width="" offsetWidth="" modificable="N" align="right" campoBD="cantidad" mascara="$#,###">Monto</td>               
        </tr>
      </table>
    </div>

      <script>        
        CargaGrid('pagosDev');
      </script>-->
 <div data-role="controlgroup" data-type="horizontal" > 
    
      <button   type="button" name="submit"  data-inline="true" data-icon="arrow-r"  onclick="modifica()">Siguiente</button>
	  </div>
	   <div data-role="controlgroup" data-type="horizontal"> 
     <!--<a href="#popupLogin" data-inline="true" id="salirbtn"  data-theme="a"  data-role="button" data-icon="minus" onclick="if(confirm('¿Realmente desea salir sin guardar los cambios?'))location.href='index.php?src=home'"> Cerrar ventana</a> -->

 </div>
    </div>
  <!---Script del grid-->
  <script>
    
        var id_pedido='NO';

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
            
            RecargaGrid('notasDev', 'pedidosBusca.php?tipo=5&id_pedido='+aux);
            id_pedido=aux;
            
            return false;
        }
        
        function busca(obj){
            var url="pedidosBusca.php?tipo=5&folio="+obj.value;
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
            location.href="index.php?scr=devolucion-seleccion&id_pedido="+id_pedido+"&dets="+dets+"&cans="+ncan;
           //location.href="index.php?scr=modificar2&id_pedido="+id_pedido+"&dets="+dets+"&cans="+ncan;
        }
        
    </script> 
      
      <!---Termina el script del grid-->
  <!--Termina seccion-->
</div>