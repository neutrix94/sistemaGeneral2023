<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Modificar</title>
<!--<link href="../jquery-mobile/jquery.mobile-1.3.0.min.css" rel="stylesheet" type="text/css"/>
-->
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
      <a href="#" data-inline="true" id="salirbtn"   data-role="button" data-icon="minus"> Cerrar ventana</a> 
 </div>
<!--Comienza el popup-->
 
  <!--Termina el popup-->
  <!---Comienza ñla sección de pedido-->
 <div class="ui-grid-solo">
   
  <form action="algo.php" method="post">
  <fieldset>
   <ul data-role="listview" data-inset="true" class="ui-grid-b">
        <li class="ui-block-b">
        <input type="text" name="folio" value="Folio">
    </li>
       <li class="ui-block-b">
       
            <input type="text" name="cliente" value="Nombre del cliente">
            </li>
            <li class="ui-block-b">
            <button type="button" name="submit"  class="buscar" data-icon="search" onclick="busca(this.form)">Buscar</button>
       </li>
       </fieldset>
  </form>
   </div>
 
  <!---Grid-->
  <div class="ui-grid-solo">
    <div class="contenedorGrid">
        <table  id="pagosDev" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="true" AltoCelda="25"
            auxiliar="0" ruta="../img/" validaElimina="true" Datos="pedidosBusca.php?tipo=6&id_pedido=<?php echo $id_pedido; ?>"
            verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros" despuesEliminar="cambiaTotal(<?php echo $id_pedido; ?>)">
                <tr>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="NO">id_pedido_detalle</td>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="<?php echo $id_pedido; ?>">id_pedido</td>
                    <td  tipo="buscador" width="150" datosdb="ajax/buscaProds.php?dato=" valida="revisaProd('#')" onChange="revisaProd('#')" offsetWidth="150" modificable="S" align="left" campoBD="p.nombres">Producto</td>
                    <td tipo="texto" width="350" offsetWidth="350" modificable="N" align="left" campoBD="cantidad">Descripcion</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="left" campoBD="cantidad"  onChange="cambiaTotal(<?php echo $id_pedido; ?>)">Cantidad</td>
                    <td tipo="decimal" width="100" offsetWidth="100" modificable="N" align="left" campoBD="cantidad" mascara="$#,###.##">Precio</td>
                    <td tipo="formula" width="100" offsetWidth="100" modificable="N" align="left" campoBD="cantidad" formula="$Cantidad*$Precio" mascara="$#,###.##">Monto</td>   
                </tr>
            </table>
            <script>        
                CargaGrid('pagosDev');
            </script>  
          </div>
  </div>
  <!--seccion-->
  <br/>
  <br/>
  <div class="ui-grid-solo">
    <div class="contenedorGrid">
     <table id="notasPagos" cellpadding="0" cellspacing="0" Alto="80" conScroll="S" validaNuevo="true" AltoCelda="25"
            auxiliar="0" ruta="../img/" validaElimina="validaEliminaPago('#')" Datos="pedidosBusca.php?tipo=2&id_pedido=-3"
            verFooter="S" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros" >
                <tr class="HeaderCell">
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="NO">id_pedido_pago</td>
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="<?php echo $id_pedido; ?>">id_pedido</td>
                    <td tipo="combo" datosdb="tiposPago.php" width="300" offsetWidth="300" modificable="S" align="left" campoBD="p.nombres" on_Click="validaModPago('#')" inicial="1">Tipo de pago</td>
                    <td tipo="decimal" width="150" offsetWidth="150" modificable="S" mascara="$#,###.##" align="right" sumatoria="S" campoBD="cantidad" on_Click="validaModPago('#')">Monto</td>
                       
                </tr>
            </table>
            <script>        
                CargaGrid('notasPagos');
            </script>
         
          </div>
          <div class="ui-grid-solo">
           <div class="ui-block-a"> 
        
              </div>
      <div class="ui-block-a" data-role="controlgroup" data-type="horizontal" data-mini="true"> 
      <button type="button" name="submit"  data-inline="true" data-icon="arrow-r"  onclick="modifica()">Finalizar</button>
 </div>
 </div>
  </div>
  <!---Script del grid-->
   <script>
    
        var restante=0;
    
        function cambiaTotal(id)
        {
            
            var num=NumFilas('pagosDev');
            var sum=0;
            for(i=0;i<num;i++)
            {
                sum+=parseFloat(celdaValorXY('pagosDev', 6, i));
            }
            
            //alert(sum);
            
            var url="ajax/getResultado.php?id_pedido="+id;
            
            var res=ajaxR("ajax/getResultado.php?id_pedido="+id+"&totAct="+sum);
            var aux=res.split('|');
            if(aux[0] != 'exito')
            {
                alert(res);
                return false;
            }
            else
            {
                document.getElementById('favorPago').value=aux[1];
                restante=parseFloat(aux[2]);
                document.getElementById('montoPago').value=aux[3];
                
                setValueHeader('notasPagos', 3, 'inicial', aux[2]);
                
            }    
            
        }
    
        function agregaProducto()
        {
            InsertaFila('pagosDev');
        }
        
        function revisaProd(pos)
        {
            
            //alert(pos);
            val=celdaValorXY('pagosDev', 2, pos);
            
            aux=val.split('->');
            
            var res=ajaxR('ajax/valProd.php?val='+aux[0]);
            
            var aux=res.split('|');
            
            if(aux[0] != 'exito')
            {
                alert('Valor no valido');
                valorXY('pagosDev', 2, pos, '');
                hmtlXY('pagosDev', 2, pos, '');
                return false;
            }
            
            else
            {
                valorXYNoOnChange('pagosDev', 2, pos, aux[1]);
                valorXYNoOnChange('pagosDev', 3, pos, aux[2]);
                
                var res=ajaxR('ajax/precioProducto.php?idp='+aux[1]);
                //alert(res);
                aux=res.split('|');
                aux=aux[1].split(':');
                valorXY('pagosDev', 5, pos, aux[1]);
                //htmlXY('pagosDev', 5, pos, aux[1]);
            }
        }
        
        function calculaPrecios () {
          /*$("#listaProductos tr.move td.tabla_id_producto").each (function(index, value) {
                var id_producto = $(this).find("p").html();
                var tr = $(this).parent ();

                $.ajax({
                     async: false,
                     type: 'GET',
                     url: "ajax/precioProducto.php?idp=" + id_producto
                }).done (function (data) {
                    if (coincidencias = data.match (/^ok\|precio\:(\d+(?:.\d+)?)\|nombre\:(.*)$/i)) {
                        precio = coincidencias[1];
                        if ($("#es_paquete").prop ("checked")) precio = parseFloat(precio) - parseFloat(precio) * descuento;
                        $(tr).find("td.tabla_precio p").html ("$ " + moneyFormat(precio));
                        $(tr).find("td.tabla_total p").html ("$ " + moneyFormat(precio * parseFloat($(tr).find("td.tabla_cantidad p").html())));
                        // Calcular total cada iteración 
                        calculaTotal();
                    }
                }).fail (function () {
                    alert ("Error al intentar obtener el precio del producto seleccionado.");
                });
            });*/
           
           
      }
    
        function validaEdicion(pos)
        {
            if(celdaValorXY('pagosDev', 0, pos) != 'NO')
                return false;
            else
                return true;    
        }
    
        function validaEliminaPago(pos)
        {
            aux=celdaValorXY('notasPagos', 0, pos)
            if(aux == 'NO')
                return true;
            else    
                return false;
            
            return false;
        }
        
        function validaModPago(pos)
        {
          //  alert(pos)
          
            aux=celdaValorXY('notasPagos', 0, pos)
            if(aux == 'NO')
                return true;
            else    
                return false;
        }
        
        function agregaPago()
        {
            InsertaFila('notasPagos');
        }
        
        function GuardaPagos()
        {
            if(pedId == 0)
            {
                alert("Debe elegir un pedido a pagar");
                return false;
            }
            
            var res=GuardaGrid('notasPagos', 5);
            
            if(res == 'exito')
            {
                alert('Se han registrado sus pagos con exito');
                RecargaGrid(notasVenta, '');
                LimpiaTabla('notasPagos');
                
            }    
            else
                alert(res);
        }
    
    function modifica()
    {
        var url="ajax/modificaPedido.php?id_pedido=<?php echo $id_pedido; ?>&restante="+restante+"&beneficiario="+document.getElementById('favorPago').value;
        url+="&numDet="+NumFilas('pagosDev');
        
        for(i=0;i<NumFilas('pagosDev');i++)
        {
            url+="&idProducto["+i+"]="+celdaValorXY('pagosDev', 2, i);
            url+="&cantidad["+i+"]="+celdaValorXY('pagosDev', 4, i);
            url+="&precio["+i+"]="+celdaValorXY('pagosDev', 5, i);
            url+="&monto["+i+"]="+celdaValorXY('pagosDev', 6, i);
            url+="&idDetalle["+i+"]="+celdaValorXY('pagosDev', 0, i);
        }
        
        url+="&numPag="+NumFilas('notasPagos');
        for(i=0;i<NumFilas('notasPagos');i++)
        {
            url+="&tipoPago["+i+"]="+celdaValorXY('notasPagos', 2, i);
            url+="&montoPago["+i+"]="+celdaValorXY('notasPagos', 3, i);
            
        }
        
        var res=ajaxR(url);
        if(res == 'exito')
        {
            alert('Las modificaciones se han realizado con exito');
            location.href="?scr=logo";
        }
        else
            alert(res);
    }
        
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
