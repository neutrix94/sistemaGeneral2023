<!-- Librerias para el grid-->
  <script language="javascript" src="RedCatGrid.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/yahoo.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/event.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/dom.js"></script>
  <script language="JavaScript" type="text/javascript" src="../js/grid/fix.js"></script>
  <script type="text/javascript" src="../js/calendar.js"></script>
  <script type="text/javascript" src="../js/calendar-es.js"></script>
  <script type="text/javascript" src="../js/calendar-setup.js"></script>
  
 <link rel="stylesheet" type="text/css" href="../css/grid_touch.css"/>

<div class="ctn">
	<div class="base">
	  <p><a href="#" class="exe" style="background: #f4bc00 url(img/iconos_03.png) no-repeat 2px -184px;" ></a></p>
		<form action="algo.php" method="post">
        <label>Folio</label> 
        <input type="text" name="folio">
        <label>Nombre del cliente</label>
            <input type="text" name="cliente">
            <button type="button" name="submit" class="buscar" onclick="buscaPedidoPago(this.form)">Buscar</button>
  </form>

	</div>
    <div class="centro">
          
            <!--comienza el grid para ser modificable-->
           <div class="contenedorGrid">
                    <table  id="notasVenta" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="false" AltoCelda="25"
                    auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=1"
                    verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                        <tr class="cab">
                            <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                            <td  tipo="texto" width="80" offsetWidth="80" modificable="S" align="center" campoBD="p.nombres" on_Click="verPedido('#')">Folio</td>
                            <td tipo="texto" width="100" offsetWidth="100" modificable="S" align="center" campoBD="cantidad" on_Click="verPedido('#')">Fecha</td>
                            <td tipo="texto" width="200" offsetWidth="200" modificable="S" align="left" campoBD="cantidad" on_Click="verPedido('#')">Clientes</td>
                            <td tipo="decimal" width="90" offsetWidth="90" modificable="S" align="right" campoBD="cantidad" mascara="$#,###.##" on_Click="verPedido('#')">Monto</td>
                            <td tipo="decimal" width="90" offsetWidth="90" modificable="S" align="right" campoBD="cantidad" mascara="$#,###.##" on_Click="verPedido('#')">Restante</td>
                            <td tipo="texto" width="200" offsetWidth="200" modificable="S" align="left" campoBD="cantidad" on_Click="verPedido('#')">Productos</td>
                            <!--<td width="60" offsetWidth="60" tipo="libre" valor="Ver" align="center">
                              <img class="vermini" src="../img/vermini2.png" height="22" width="22" border="0"  onclick="verPedido('#')" onmouseover="this.style.cursor='hand';this.style.cursor='pointer';" alt="Ver" title="Continuar"/>
                            </td>-->   
                        </tr>
                    </table>
                    <script>        
                        CargaGrid('notasVenta');
                    </script>
                
          </div>
    </div>
	   <p><a href="#" onclick="return false;" class="exe_mas"><img src="img/mas2.png" width="35" height="35" alt="" onclick="agregaPago()"/></a></p>
    <div class="footer">
	
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
    </div>
       <button type="button" name="submit" class="finalizar" onclick="GuardaPagos()">Finalizar</button>
       
<script>
    
    //alert("Hola");
    
    //variables globales
    var montoPend=0;
    var pedId=0;
    
    function validaEliminaPago(pos)
    {
        aux=celdaValorXY('notasPagos', 0, pos)
        if(aux == 'NO')
            return true;
        else    
            return false;
        
        return false;
    }
    
    
    function buscaPedidoPago(f)
    {
        var url="pedidosBusca.php?tipo=1&cliente="+f.cliente.value+"&folio="+f.folio.value;
        
        RecargaGrid('notasVenta', url);
        
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
    
    function verPedido(pos)
    {
        //alert(pos);
        var aux=celdaValorXY('notasVenta', 0, pos);
        var url="pedidosBusca.php?tipo=2&id_pedido="+aux;
        
        RecargaGrid('notasPagos', url);
        
        
        //Buscamos datos de pedido
        url="pedidosBusca.php?tipo=3&id_pedido="+aux;
        var res=ajaxR(url);
        aux=res.split('|');
        montoPend=isNaN(parseFloat(aux[0]))?0:parseFloat(aux[0]);
        pedId=aux[1];
        
        setValueHeader('notasPagos', 3, 'inicial', montoPend);
        
        
        var obj=document.getElementById('notasPagos');
        
        obj.guardaEn="pedidosBusca.php?tipo=4&id_pedido="+aux[1];
        obj.setAttribute('guardaEn', "pedidosBusca.php?tipo=4&id_pedido="+aux[1]);
        
        return false; 
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
    
</script>       
       
</div>
