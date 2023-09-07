<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Pagos</title>
<link href="casaluces.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="../jquery-mobile/jquery.mobile.structure-1.3.2.min.css"/>
<script src="../jquery-mobile/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="../jquery-mobile/jquery.mobile-1.3.0.min.js" type="text/javascript"></script>
<!-- Librerias para el grid-->
  <script language="javascript" src="RedCatGrid.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/fix.js"></script>
  <script type="text/javascript" src="../js/calendar.js"></script>
  <script type="text/javascript" src="../js/calendar-es.js"></script>
  <script type="text/javascript" src="../js/calendar-setup.js"></script>
  
 <link rel="stylesheet" type="text/css" href="../css/grid_touch_nuevo.css"/>
</head>
<body>
<div data-role="page" id="page" >
  <div data-role="header" data-id="header1">
    <h1>Cambios y devoluciones</h1>
    <a data-role="button" href="#page1" class="ui-btn-right"> Cerrar sesion </a>
    <div data-role="navbar" data-iconpos="top">
      <ul>
        <li> <a href="nueva-venta.php" data-transition="fade" data-theme="b" data-icon="plus"
                    class="ui-btn-active ui-state-persist"> nueva venta </a> </li>
        <li> <a href="#page1" data-transition="fade" data-theme="" data-icon="delete"> Cerrar ventana </a> </li>
        <li> <a href="modificar.php" data-transition="fade" data-theme="" data-icon="edit"> Cambio y devoluciones </a> </li>
        <li> <a href="pagos-v.php" data-transition="fade" data-theme="" data-icon="check"> pagos y apartados </a> </li>
      </ul>
    </div>
  </div>
  <!--Termina el menu y header-->
  
  <!--Contenido-->
  <div data-role="content">
   
      <div data-role="controlgroup" data-type="horizontal" data-mini="true"> 
     <a href="#popupLogin" data-inline="true" id="salirbtn"   data-role="button" data-icon="minus"> Cerrar ventana</a> 
 </div>
<!--Comienza el popup-->
 
  <!--Termina el popup-->
  <!---Comienza ñla sección de pedido-->
   <div class="ui-grid-solo">
   
  <form action="algo.php" method="post">
  <fieldset>
   <ul data-role="listview" data-inset="true">
        <li>
        <input type="text" name="folio" value="Folio">
    </li>
       <li>
       
            <input type="text" name="cliente" value="Nombre del cliente">
            </li>
            <li>
            <button type="button" name="submit" class="buscar"  data-iconpos="notext"  data-icon="search" onclick="busca(this.form)">Buscar</button>
       </li>
       </fieldset>
  </form>
   </div>
 
  <!---Grid-->
  <div class="ui-grid-solo">
    <div class="contenedorGrid">
   <table  id="notasVenta" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="false" AltoCelda="25"
                    auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=1"
                    verFooter="N" guardaEn="false" listado="S" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                        <tr class="cab">
                            <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                            <td  tipo="texto" width="100" offsetWidth="100" modificable="N" align="left" campoBD="p.nombres">Folio</td>
                            <td tipo="texto" width="100" offsetWidth="100" modificable="N" align="left" campoBD="cantidad">Fecha</td>
                            <td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left" campoBD="cantidad">Clientes</td>
                            <td tipo="decimal" width="100" offsetWidth="100" modificable="N" align="left" campoBD="cantidad" mascara="$#,###.##">Monto</td>
                            <td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
                              <img class="vermini" src="../img/vermini2.png" height="22" width="22" border="0"  onclick="verPedido('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Continuar"/>
                            </td>   
                        </tr>
                    </table>
                    <script>        
                        CargaGrid('notasVenta');
                    </script>
          </div>
  </div>
  <!--seccion-->
  <br/>
  <br/>
  <div class="ui-grid-solo">
    <div class="contenedorGrid">
         <div class="contenedorGrid">
         <table id="notasPagos" cellpadding="0" cellspacing="0" Alto="120" conScroll="S" validaNuevo="true" AltoCelda="25"
            auxiliar="0" ruta="../img/" validaElimina="validaEliminaPago('#')" Datos="pedidosBusca.php?tipo=2"
            verFooter="S" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                <tr class="HeaderCell">
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="NO">id_pedido_pago</td>
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="$LLAVE">id_pedido</td>
                    <td tipo="combo" datosdb="tiposPago.php" width="300" offsetWidth="300" modificable="S" align="left" campoBD="p.nombres" on_Click="validaModPago('#')" inicial="1">Tipo de pago</td>
                    <td tipo="decimal" width="150" offsetWidth="150" modificable="S" mascara="$#,###.##" align="right" sumatoria="S" campoBD="cantidad" on_Click="validaModPago('#')">Monto</td>
                       
                </tr>
            </table>
            <script>        
                CargaGrid('notasPagos');
            </script>
          </div>
          <div data-role="controlgroup" data-type="horizontal" data-mini="true"> 
          <button type="button" name="submit"  data-icon="arrow-r" onclick="GuardaPagos()">Finalizar</button>
      </div>
          
  </div>
  <!---Script del grid-->
  <script>
    
     //alert("Hola");
    
    //variables globales
  //  var montoPend=0;
//    var pedId=0;
//    
//    function validaEliminaPago(pos)
//    {
//        aux=celdaValorXY('notasPagos', 0, pos)
//        if(aux == 'NO')
//            return true;
//        else    
//            return false;
//        
//        return false;
//    }
//    
//    
//    function buscaPedidoPago(f)
//    {
//        var url="pedidosBusca.php?tipo=1&cliente="+f.cliente.value+"&folio="+f.folio.value;
//        
//        RecargaGrid('notasVenta', url);
//        
//    }
//    
//    function validaModPago(pos)
//    {
//      //  alert(pos)
//      
//        aux=celdaValorXY('notasPagos', 0, pos)
//        if(aux == 'NO')
//            return true;
//        else    
//            return false;
//    }
//    
//    function agregaPago()
//    {
//        InsertaFila('notasPagos');
//    }
//    
//    function verPedido(pos)
//    {
//        var aux=celdaValorXY('notasVenta', 0, pos);
//        var url="pedidosBusca.php?tipo=2&id_pedido="+aux;
//        
//        RecargaGrid('notasPagos', url);
//        
//        
//        //Buscamos datos de pedido
//        url="pedidosBusca.php?tipo=3&id_pedido="+aux;
//        var res=ajaxR(url);
//        aux=res.split('|');
//        montoPend=isNaN(parseFloat(aux[0]))?0:parseFloat(aux[0]);
//        pedId=aux[1];
//        
//        setValueHeader('notasPagos', 3, 'inicial', montoPend);
//        
//        
//        var obj=document.getElementById('notasPagos');
//        
//        obj.guardaEn="pedidosBusca.php?tipo=4&id_pedido="+aux[1];
//        obj.setAttribute('guardaEn', "pedidosBusca.php?tipo=4&id_pedido="+aux[1]);
//        
//        
//    }
//    
//    function GuardaPagos()
//    {
//        if(pedId == 0)
//        {
//            alert("Debe elegir un pedido a pagar");
//            return false;
//        }
//        
//        var res=GuardaGrid('notasPagos', 5);
//        
//        if(res == 'exito')
//        {
//            alert('Se han registrado sus pagos con exito');
//            RecargaGrid(notasVenta, '');
//            LimpiaTabla('notasPagos');
//            
//        }    
//        else
//            alert(res);
//    }
//    
     
    </script> 
      
      <!---Termina el script del grid-->
  <!--Termina seccion-->
</div>
<!---Footer-->
<div data-role="footer">
  <h4>Todos los derechos reservados 2013</h4>
</div>

</div>
</body>
</html>
