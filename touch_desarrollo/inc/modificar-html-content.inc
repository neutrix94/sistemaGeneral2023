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
            <button type="button" name="submit" class="buscar" onclick="busca(this.form)">Buscar</button>
  </form>

	</div>
    <div class="centro">
	   <div class="contenedorGrid">
        <table  id="notasDev" cellpadding="0" cellspacing="0" Alto="190" conScroll="S" validaNuevo="false" AltoCelda="25"
            auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=5"
            verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                <tr > 
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
    <div class="footer">
	
       <table  id="pagosDev" cellpadding="0" cellspacing="0" Alto="150" conScroll="S" validaNuevo="false" AltoCelda="25"
            auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=6"
            verFooter="N" guardaEn="false" listado="S" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                <tr>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido_detalle</td>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                    <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_producto</td>
                    <td  tipo="texto" width="80" offsetWidth="80" modificable="N" align="left" campoBD="p.nombres">Producto</td>
                    <td tipo="texto" width="250" offsetWidth="250" modificable="N" align="left" campoBD="cantidad">Descripcion</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="N" align="center" campoBD="cantidad" mascara="#,###">Cantidad</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right" campoBD="cantidad" mascara="$#,###">Precio</td>
                    <td tipo="texto" width="100" offsetWidth="100" modificable="N" align="right" campoBD="cantidad" mascara="$#,###">Monto</td>
                       
                </tr>
            </table>
            <script>        
                CargaGrid('pagosDev');
            </script>
           
    </div>
      <button type="button" name="submit" class="btn" onclick="modifica()">Siguiente</button>
      
    <script>
    
        var id_pedido='NO';
        
        function verDetalleP(pos)
        {
            //alert(pos);
            
            var aux=celdaValorXY('notasDev', 0, pos);
            var url="pedidosBusca.php?tipo=6&id_pedido="+aux;
            RecargaGrid('pagosDev', url);
            id_pedido=aux;
            
            return false;
        }
        
        function busca(f)
        {
            var url="pedidosBusca?tipo=5&folio="+f.folio.value+"&cliente="+f.cliente.value;
            
            RecargaGrid('notasDev', url);
            //alert(url);
        }
        
        function modifica()
        {
            if(id_pedido == 'NO')
            {
                alert('Debe elegir un pedido primero');
                return false;
            }
            
            location.href="index.php?scr=modificar2&id_pedido="+id_pedido;
        }
        
    </script> 
      
      
      
</div>