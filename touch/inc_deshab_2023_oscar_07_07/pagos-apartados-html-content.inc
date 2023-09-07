<?php
  //sacamos la fecha actual desde mysql
    $sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
    $eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
    $fecha_actual=mysql_fetch_row($eje);
  //comprobamos que haya una sesion abierta en el dia actual
    $sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha='$fecha_actual[0]' AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
    $eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
    $r=mysql_fetch_row($eje);
    if($r[0]<1){
      die("<script>alert('Pida al cajero que inicie sesion de caja para poder acceder a esta pantalla!!!');location.href='index.php?';</script>");
    }
?>
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
<div data-role="content">
   
     

      <div title="ocultar teclado"  style="position:relative; display:block; width:300px; float:right;">
      <img style="padding-left:13px;" src="img/teclado.png" width="70" height="70">
     <p style="display:block; position:relative; top:-3.7em; left:5.8em;"> ocultar  <br>
     teclado</p>
   
 </div>
<!---Comienza la sección de pedido-->
   <div class="ui-grid-solo">
   
  <form action="algo.php" method="post">
  <fieldset>
   <ul data-role="listview" data-inset="true" class="ui-grid-b">
        <li class="ui-block-b">
      <!--  <input type="text" name="folio" placeholder="Folio">-->
    </li>
        <li lass="ui-block-b" style="width:500px;">
       
            <input type="text" name="cliente" placeholder="Búsqueda por Folio, Nombre del cliente,fecha,monto, productos" onkeyup="buscaPedidoPago(this)">
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
 
  <!---Grid-->
  <div>
    <div class="contenedorGrid" style="width:;">
     <table style="display:block; float:left;" width="60%" id="notasVenta" cellpadding="0" cellspacing="0" Alto="240" conScroll="S" validaNuevo="false" AltoCelda="70"
                    auxiliar="0" ruta="" validaElimina="false" Datos="pedidosBusca.php?tipo=1"
                    verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                        <tr class="cab">
                          <td  tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos">id_pedido</td>
                          <td  tipo="texto" width="100" offsetWidth="80" modificable="S" align="center" campoBD="p.nombres" on_Click="verPedido('#')">Apartado</td>
                          <td  tipo="texto" width="200" offsetWidth="80" modificable="S" align="center" campoBD="p.nombres" on_Click="verPedido('#')">Folio</td>
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
  <!--seccion-->
  <br/>
  <br/>
  <div class="ui-grid-solo">
   <!--<a href="#popupLogin" data-inline="true" id="salirbtn"   data-role="button" data-icon="plus" onclick="agregaPago()"> Agregar</a>--> 
     <div class="contenedorGrid">
         <div style="width:750px; display:block; margin:0 auto;">
        <!-- <table width="100%" id="notasPagos" cellpadding="0" cellspacing="0" Alto="120" conScroll="S" validaNuevo="true" AltoCelda="70"
            auxiliar="0" ruta="../img/" validaElimina="validaEliminaPago('#')" Datos="pedidosBusca.php?tipo=2"
            verFooter="N" guardaEn="false" listado="N" class="tabla_Grid_RC" paginador="N" title="Listado de Registros">
                <tr class="HeaderCell">
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="NO">id_pedido_pago</td>
                    <td tipo="oculto" width="0" offsetWidth="0" campoBD="p.id_productos" inicial="$LLAVE">id_pedido</td>
                    <td tipo="combo" datosdb="tiposPago.php" width="250" offsetWidth="250" modificable="S" align="left" campoBD="p.nombres" on_Click="validaModPago('#')" inicial="1">Tipo de pago</td>
                    <td tipo="decimal" width="80" offsetWidth="80" modificable="N" align="center"  campoBD="cantidad" inicial="<?php echo date('Y-m-d'); ?>">Fecha</td>
                    <td tipo="decimal" width="150" offsetWidth="150" modificable="S" mascara="$#,###.##" align="right" sumatoria="S" campoBD="cantidad" on_Click="validaModPago('#')">Monto</td>
                </tr>
            </table>
            <script>        
                CargaGrid('notasPagos');
            </script>-->
			
			 <table cellpadding="0" cellspacing="0">
             <tr class="cabecera">
             <td width="5%"><p>No.</p></td>
             <td width="23%"><p>Tipo de pago</p></td>
			 <td width="23%"><p>Fecha</p></td>
             <td width="23%"><p> Monto</p></td>
         
             </tr>
             <tr class="move">
             <td class="cabecera"><p>1</p></td>
             <td>
                 <input type="text" value="Efectivo" name="efectivo" readonly="true">
                 <input type="hidden" value="1" name="tipoPago1" />
             </td>
			 <td><?php echo date('Y-m-d'); ?></td>
             <td><input type="text" value="0" name="monto1" id="monEfe" onkeypress="return validarNumero(event,1,'monEfe')" onblur="validaMontos(1)"></td>
           
             </tr>
             <tr class="move" style="display:none;">
               <td class="cabecera"><p>2</p></td>
               <td>
                   <input type="text" value="Tarjeta" name="tarjeta" readonly="true">
                   <input type="hidden" value="7" name="tipoPago2" />
               </td>
			    <td><?php echo date('Y-m-d'); ?></td>
               <td><input type="text" value="0" name="monto2" id="monTar" onkeypress="return validarNumero(event,1,'monEfe')" onblur="validaMontos(2)"></td>
           
             </tr>
             </table>
              
			
          </div>
		  </div>
          <div data-role="controlgroup" data-type="horizontal"> 
          <button type="button" name="submit"  data-icon="arrow-r" onclick="GuardaPagos()">Finalizar</button>
      
          </div>
		  <div data-role="controlgroup" data-type="horizontal"> 
     <!--<a href="#popupLogin" data-inline="true" id="salirbtn"  data-theme="a"  data-role="button" data-icon="minus" onclick="if(confirm('¿Realmente desea salir sin guardar los cambios?'))location.href='index.php?src=home'"> Cerrar ventana</a> -->

 </div>
  
  <!---Script del grid-->
  <script>
    
    //alert("Hola");
    
    //variables globales
    var montoPend=0;
    var pedId=0;
    var montoNota=0;
    
    function validaMontos(tip)
       {
           
           
           var total=montoPend;
           if(tip == 1)
           {
               obj1=document.getElementById('monEfe');
               obj2=document.getElementById('monTar');
           }
           else
           {
               obj1=document.getElementById('monTar');
               obj2=document.getElementById('monEfe');
           }
           
           var monto2=isNaN(parseFloat(obj2.value))?0:parseFloat(obj2.value);
           var monto1=isNaN(parseFloat(obj1.value))?0:parseFloat(obj1.value);
           
           if((monto1 + monto2) > total)
           {
               alert("El pago es mayor al total de la nota de venta");
               obj1.value="";
               obj1.focus();
               
               return false;
           }
           
           else
           {
               //obj2.value=total-monto1;
               //alert('?');
               //obj2.value=total-monto1;
               
           }
           //else
           
           
       }
        
        function validarNumero(e,punto,id){
        var valor="";
        
        tecla_codigo = (document.all) ? e.keyCode : e.which;
        valor=document.getElementById(id).value;
        
        
        if(tecla_codigo==8 || tecla_codigo==0)return true;
        if (punto==1)
            patron =/[0-9\-.]/; 
        else
            patron =/[0-9\-]/;
        
            
        //validamos que no existan dos puntos o 2 -
        tecla_valor = String.fromCharCode(tecla_codigo);
        //46 es el valor de "."
        if (valor.split('.').length>1 && tecla_codigo==46)      
        {
            return false;
        }
        else if (valor.split('-').length>1 && tecla_codigo==45)     
        {
            //45 es el valor de "-"
            return false;
        }
        
        
        return patron.test(tecla_valor);
    
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
    
    
    function buscaPedidoPago(obj)
    {
        var url="pedidosBusca.php?tipo=1&clave="+obj.value;
        
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
       // alert(pos);
        var aux=celdaValorXY('notasVenta', 0, pos);
        var url="pedidosBusca.php?tipo=2&id_pedido="+aux;
        montoNota=celdaValorXY('notasVenta', 5, pos);
        montoNota=parseFloat(montoNota+"");
        //RecargaGrid('notasPagos', url);
        
        RecargaGrid('notasVenta', 'pedidosBusca.php?tipo=1&id_pedido='+aux);
        
        //Buscamos datos de pedido
        url="pedidosBusca.php?tipo=3&id_pedido="+aux;
        var res=ajaxR(url);
        aux=res.split('|');
        montoPend=isNaN(parseFloat(aux[0]))?0:parseFloat(aux[0]);
        
        pedId=aux[1];
        
        obj2=document.getElementById('monEfe');
        obj2.value=montoPend;
        
        //setValueHeader('notasPagos', 4, 'inicial', montoPend);
        //var obj=document.getElementById('notasPagos');
        //obj.guardaEn="pedidosBusca.php?tipo=4&id_pedido="+aux[1];
        //obj.setAttribute('guardaEn', "pedidosBusca.php?tipo=4&id_pedido="+aux[1]);
        return false; 
    }
    
    function GuardaPagos(){
        if(pedId == 0)
        {
            alert("Debe elegir un pedido a pagar");
            return false;
        }
        
        //num=NumFilas('notasPagos');
        //var totalPagos=0;
        
        /*for(i=0;i<num;i++)
        {
            totalPagos+=parseFloat(celdaValorXY('notasPagos', 4, i)+"");
        }*/
       
       obj1=document.getElementById('monTar');
       obj2=document.getElementById('monEfe');
           
      var monto2=isNaN(parseFloat(obj2.value))?0:parseFloat(obj2.value);
      var monto1=isNaN(parseFloat(obj1.value))?0:parseFloat(obj1.value);
       
       totalPagos=monto1/*+monto2*/; 
      
        if(totalPagos > montoNota){
            alert('No es posible pagar un monto mayor al restante');
            return false;
        }
/*implementacion Oscar 26.11.2019 para generar el codigo de barras por si no esta en los archivos*/
        $.post( "inc/img_codigo.php", {text:$("#notasVenta_1_0").html()});
/*fin de cambio Oscar 26.11.2019*/
        //var res=GuardaGrid('notasPagos', 5);
        
        var url="pedidosBusca.php?tipo=4&id_pedido="+pedId;
        
        if(monto1 > 0 && monto2 > 0)
            url+="&numdatos=2";
        else
            url+="&numdatos=1";
        
        i=0;
       
        if(monto1 > 0)
        {
           url+="&dato1["+i+"]=NO&dato3["+i+"]=7";
           url+="&dato5["+i+"]="+monto1;
           i++;
        }
        if(monto2 > 0)
        {
           url+="&dato1["+i+"]=NO&dato3["+i+"]=1";
           url+="&dato5["+i+"]="+monto2;
           i++;
        }   
        
        
        
        var res=ajaxR(url);
        var aux=res.split('|');
//alert(res);
        if(aux[0] == 'exito')
        {
            alert('Se han registrado sus pagos con exito!');
            RecargaGrid('notasVenta', '');
            //RecargaGrid('notasPagos', 'pedidosBusca.php?tipo=4&id_pedido=-3');
            obj1.value="0";
            obj2.value="0";
            
            pedId=0;
            var i=0;//2
            var limite_for=1;
            if(aux.length<=2){i=1;}else{i=2;limite_for=2;}
            for(i;i<=limite_for;i++){//aux.length /*modificación Oscar 28.02.2019 para que no salga doble la impresión desde línea*/
                var ejemplo=ajaxR("ajax/imprimeAbono.php?id_pago="+aux[i]+"&noImp=1");
                //alert(ejemplo);
                //ajaxR("ajax/imprimeAbono.php?id_pago="+aux[i]+"&noImp=2");
            }
            
        }    
        else{
            alert(res);
      }
    }
    
</script>   
      
      <!---Termina el script del grid-->
  <!--Termina seccion-->
</div>

</div>