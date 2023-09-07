<!--Contenido-->
<div data-role="content">
	
	
	<div class="ui-grid-solo">
		<div class="contenedorGrid">
			<table id="listaProductos"  cellpadding="0" cellspacing="0">
  			    <tr class="cabecera">
			        <td width="100"><p>Producto</p></td>
			        <td width="350"><p>Descripción</p></td>
			        <td width="100"><p>Cantidad</p></td>
			        <td width="100"><p>Precio</p></td>
			        <td width="100"><p>Oferta</p></td>
			        <td width="100"><p>Monto</p></td>
			        <td width="50"></td>
			        <td width="0"></td>
			        <td width="0"></td>
			        <td width="0"></td>
			        <td width="0"></td>
			        <td width="0"></td>
				</tr>
				<?php
					for($i=0;$i<sizeof($prods);$i++)
			        {
			            echo "<tr class='move'>";
			            for($j=0;$j<sizeof($prods[$i]);$j++)
			            {
                
            			    if($j == 0)
			                    $alin="center";
			                if($j == 1)
            			        $alin="left";
			                if($j == 2)
			                    $alin="right";
			                if($j == 4)
			                    $alin="center";
			                if($j == 5)
            			        $alin="right";
			                if($j == 6)
            			        $alin="center";        
							if($j > 6)
			                {
            			        echo "<td valor='".$prods[$i][$j]."' id='celda_".$i."_".$j."'><script>setTimeout('cambia(\"celda_".$i."_".$j."\", ".$prods[$i][$j].")', 1000)</script></td>";
			                }    
			                else
			                    echo "<td align='$alin'>".$prods[$i][$j]."</td>";
			            }
			            echo "</tr>";
			        }
				?>
			</table>
		</div>
	</div>
 
	<ul data-role="listview" data-inset="true" class="ui-grid-c" >
		<li class="ui-block-a" style="width:65% !important">
        	<label for="producto"> Producto</label>
			<input type="text" name="buscadorLabel"  id="buscadorLabel" style="background: transparent;" size="30" onkeyup="activaBuscador(this, event)" onfocus="this.setSelectionRange(0, this.value.length);"/>
			<div id="resBus" class="lista_producto" style="display:none; position:relative; z-index:1000;"></div>
			<input type="hidden" name="id_productoN" value="" />
			&nbsp;<br><br>
		</li>
		
	</ul>	
	<ul data-role="listview" data-inset="true" class="ui-grid-c" >
		<li class="ui-block-b" style="width:65% !important">
			<label>Cantidad</label>
			<input type="text" maxlength="7"  id="cantidad2" class="cantidad" name="cantidad" onfocus="muestraDesc(0)" onkeyup="validaKey(event, this.form)" onkeypress="return validarNumero(event,0,'cantidad2')">
			<br><br>
		</li>  
		<li class="ui-block-c" style="background: transparent; zoom:1.5; -webkit-transform:scale(1.5,1.5);transform:scale(1.5,1.5); width:75px !important ; top:23px;  display:block; position:relative;">   
			<button name="AgregaFila" data-ajax="false"   data-iconpos="notext" data-icon="plus" data-iconpos="bottom"  id="AgregaFila" onclick="agregaFila(this.form)">Agregar producto </button>   
			<br><br><br>
		</li>

	</ul> 
	
	
	<div class="ui-grid-solo">
	</div>

	<form action="algo.php" method="post">
		<fieldset>
			<ul data-role="listview" data-inset="true" class="ui-grid-a">
				<li class="ui-block-a">
					<label>Pago a favor de </label><input type="text" name="pago" id="favorPago" value="<?php
						if($pagado > $totres)
							echo "CLIENTE";
						else
							echo "CASA DE LAS LUCES";	
							
					 ?>">
				</li>
				<li class="ui-block-b">
					<label>de </label>
					<input type="text" name="de" id="montoPago" value="<?php
						if($pagado > $totres)
							echo $pagado-$totres;
						else
						{
							echo "0";
						}	
							
					 ?>">
				</li>
			</ul>
       </fieldset>
	</form>
   
   <div class="ui-grid-a">
		<!--Comienza el nuevo bloque que debe estar en lugar del grid-->     
		<table cellpadding="0" cellspacing="0">
        	<tr class="cabecera">
				<td width="5%"><p>No.</p></td>
				<td width="23%"><p>Tipo de pago</p></td>
				<td width="23%"><p> Monto</p></td>
			</tr>
             <tr class="move">
             <td class="cabecera"><p>1</p></td>
             <td>
                 <input type="text" value="Efectivo" name="efectivo" readonly="true">
                 <input type="hidden" value="1" name="tipoPago1" />
             </td>
             <td><input type="text" value="<?php
						if($pagado > $totres)
							echo $pagado-$totres;
						else
						{
							echo "0";
						}	
							
					 ?>" name="monto1" id="monEfe" onkeypress="return validarNumero(event,1,'monEfe')" onblur="validaMontos(1)"></td>
           
             </tr>
             <tr class="move">
               <td class="cabecera"><p>2</p></td>
               <td>
                   <input type="text" value="Tarjeta" name="tarjeta" readonly="true">
                   <input type="hidden" value="7" name="tipoPago2" />
               </td>
               <td><input type="text" value="0" name="monto2" id="monTar" onkeypress="return validarNumero(event,1,'monEfe')" onblur="validaMontos(2)"></td>
            
             </tr>
             </table>
              
            
         
        </div>
        <div  style="float:right; left:-23em; display:block;" data-type="horizontal"> 
      <button  type="button" name="submit"  data-inline="true" data-icon="arrow-r"  onclick="modifica()">Finalizar</button>
 </div>
        </div>
         
           
      

  </div>
  <!---Script del grid-->
   <script>
    
        var restante=<?php
            if($pagado > $totres)
              echo $pagado-$totres;
            else
            {
              echo "0";
            } 
              
           ?>;
        
        
        function validaMontos(tip)
       {
           
           
           var total=restante;
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
               obj2.value=total-monto1;
               
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
    
        function cambia(id, val)
        {
            //alert(id+' '+val);
            document.getElementById(id).valor=val;
        }
    
    
        function cambiaTotal(id)
        {
            
            
           
            var tabla=document.getElementById('listaProductos');
           
            trs=tabla.getElementsByTagName('tr');
            var total=0;
            for(i=0;i<trs.length;i++)
            {
                tds=trs[i].getElementsByTagName('td');
                
                //alert(tds[8].valor);
                
                if(tds[9].valor != 'NO')
                {
                    total-=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);
                }
                else
                    total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);
            }    
            
            //alert(total);
            if(total > 0)
                document.getElementById('favorPago').value="CASA DE LAS LUCES";
            else
                document.getElementById('favorPago').value="CLIENTE";

            restante=Math.abs(total);


			if(restante > <?php echo $pagado; ?> && total <= 0)
			{
				document.getElementById('montoPago').value="$"+Math.abs(<?php echo $pagado; ?>);
            	document.getElementById('monEfe').value=<?php echo $pagado; ?>;
				restante=<?php echo $pagado; ?>
            }
			else
			{
            	document.getElementById('montoPago').value="$"+Math.abs(total);
            	document.getElementById('monEfe').value=restante;
			}
			
            document.getElementById('monTar').value=0;  
        }
        
        function eliminarItem (item) {
          
          //alert(item);
          $(item).parents ("tr.move").first ().remove ();
          //calculaTotal ();
          $("#producto, #cantidad").val ("");
          
          calculaTotalProd();
      }
    
        function agregaProducto()
        {
            InsertaFila('pagosDev');
        }
        
        function revisaProd(pos, val)
        {
            
            //alert(pos);
            //val=celdaValorXY('pagosDev', 2, pos);
            
            //aux=val.split('->');
            //alert(val);
            var res=ajaxR('ajax/valProd.php?val='+val);
            //alert(res);
            var aux=res.split('|');
            
            if(aux[0] != 'exito')
            {
                alert('Valor no valido');
                valorXY('pagosDev', 2, pos, '');
                hmtlXY('pagosDev', 4, pos, '');
                return false;
            }
            
            else
            {
                //alert(res);
                
                valorXYNoOnChange('pagosDev', 2, pos, aux[1]);
                valorXYNoOnChange('pagosDev', 4, pos, aux[2]);
                
                var res=ajaxR('ajax/precioProducto.php?idp='+aux[1]);
                //alert(res);
                aux=res.split('|');
                aux=aux[1].split(':');
                valorXYNoOnChange('pagosDev', 6, pos, aux[1]);
                //htmlXY('pagosDev', 5, pos, aux[1]);
                return true;
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
        
        var tabla=document.getElementById('listaProductos');
        obj1=document.getElementById('monTar');
        obj2=document.getElementById('monEfe');
           
        var monto2=isNaN(parseFloat(obj2.value))?0:parseFloat(obj2.value);
        var monto1=isNaN(parseFloat(obj1.value))?0:parseFloat(obj1.value);
        
        var total=monto1+monto2;

        //alert(total+' '+restante);
        
        
        if(total != restante)
        {
            alert('Es necesario que se salde el pago para realizar la transacción');
            return false;
        }
        
           
        trs=tabla.getElementsByTagName('tr');
        
        var url="ajax/modificaPedido.php?id_pedido=<?php echo $id_pedido; ?>&restante="+restante+"&beneficiario="+document.getElementById('favorPago').value;
        url+="&numDet="+(trs.length-1);
        
        
        
        
        for(i=1;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            
            
            
            url+="&idProducto["+(i-1)+"]="+tds[11].valor;
            url+="&cantidad["+(i-1)+"]="+tds[2].innerHTML;
            url+="&precio["+(i-1)+"]="+tds[7].valor;
            url+="&monto["+(i-1)+"]="+tds[8].valor;
            url+="&idDetalle["+(i-1)+"]="+tds[9].valor;
        }
        
        /*url+="&numPag="+NumFilas('notasPagos');
        for(i=0;i<NumFilas('notasPagos');i++)
        {
            url+="&tipoPago["+i+"]="+celdaValorXY('notasPagos', 2, i);
            url+="&montoPago["+i+"]="+celdaValorXY('notasPagos', 3, i);
            
        }*/
       
       
       
       
       if(monto1 > 0 && monto2 > 0)
        url+="&numPag=2";
       else
        url+="&numPag=1";
        
       i=0;
       
       if(monto1 > 0)
       {
           url+="&tipoPago["+i+"]=7";
           url+="&montoPago["+i+"]="+monto1;
           i++;
       }
       if(monto2 > 0)
       {
           url+="&tipoPago["+i+"]=1";
           url+="&montoPago["+i+"]="+monto2;
           i++;
       }   

       //alert (url);
        
        var res=ajaxR(url);
        var aux=res.split('|');
        if(aux[0] == 'exito')
        {
            alert('Las modificaciones se han realizado con exito');
            
            if(aux[1] != '')
            {
                ajaxR("index.php?scr=ticket&idp="+aux[1]+"&noImp=1");
                ajaxR("index.php?scr=ticket&idp="+aux[1]+"&noImp=2");
            }    
            
            if(aux[2] != '')
            {
                ajaxR("ajax/imprimeDev.php?id_dev="+aux[2]+"&noImp=1");
                ajaxR("ajax/imprimeDev.php?id_dev="+aux[2]+"&noImp=2");
            }    
            
            location.href="index.php?scr=home";
        }
        else
            alert(res);
    }
    
    
    var totalVenta=0;
      var descuentoAplicado=0;
      var aplicaDescuento=0;
      var sig=0;
      
      
     function Mascara(mascara, valor)
{   
    //validamos que realmente haya una mascara a evaluar
    if(mascara == 'null' || mascara == null || mascara.length <= 0)
        return valor;   
        
        
    //Obtenemos los datos relevantes de la mascara
    var aux=mascara.split(",");
    var coma=aux.length;
    var aux=mascara.replace(',','');
    aux=aux.replace('.','');
    aux=aux.split('#');
    var prefijo=aux[0];
    var posfijo=aux[aux.length-1];
    var aux=mascara.split(".");
    if(aux.length > 1)
    {
        var ndec=aux[1].replace(posfijo,'').length;
    }
    else
        var ndec=0;
        
        
            
    //Empezamor a evaluar   
    var cad=valor;
    
    //Proceso para numero de posiciones decimales
    if(ndec > 0)
    {
        cad=parseFloat(cad);
        cad=Math.round(cad*Math.pow(10,ndec))/Math.pow(10,ndec);
        if(cad<0)
        {
            cad=Math.abs(cad);
            prefijo='-'+prefijo;
        }
        cad=cad.toString();
    }
    
    
    //Comas en numeros
    if(coma > 1)
    {           
        //alert(cad);
        var aux=cad.split('.');
        cM=0;
        var ax="";
        for(m=aux[0].length-1;m >= 0;m--)
        {
            cM++;
            ax=aux[0].charAt(m)+ax;
            if(cM == 3 && m != 0)
            {

                ax=','+ax;
                cM=0;
            }
        }
        cad=ax;
        if(aux.length > 1 && ndec > 0)
        {
            dec=aux[1];
            for(var i=dec.length;i<ndec;i++)
                dec+="0";
            cad=cad+"."+dec;
        }
        else if(ndec > 0)
        {
            dec=aux[0];
            nu='';
            for(var i=nu.length;i<ndec;i++)
                nu+="0";
            cad=cad+"."+nu;
        }
    }
    
    //Prefijos y posfijos
    if(prefijo.length > 0)
        cad=prefijo+cad;
    if(posfijo.length > 0)
        cad=cad+posfijo;9
        
    return cad;
} 
      
      
     function muestraDesc(val)
     {
         
         var obj=document.getElementById('buscadorLabel');
         
         //Buscamos si tiene descripcion
        var aux=obj.value;
        
        if(aux == '')
            return false;
        
        aux=aux.split(' | ');
        if(aux.length == 1)
        {
            var res=ajaxR('ajax/buscaProdOrCoId.php?val='+aux[0]+"&can=1&can2=1");
            var ax=res.split('|');
            
            if(ax[0] != 'exito')
            {
                alert('Producto incorrecto, pruebe con uno diferente');
                obj.focus();
                obj.setSelectionRange(0, obj.value.length);
                return false;
            }
            
            obj.value=ax[2]+" | "+ax[3];
            
            if(ax[10] == 'SI' && val == 1)
            {
                 can=document.getElementById('cantidad2');
                 can.value="1";
                 agregaFila(null);
            }
            
            //alert(res);
        }
     } 
      
    var filant=-1;    
      
    //Funcionalidad del buscador
    function activaBuscador(obj, eve)
    {
        
        key=(eve.which) ? eve.which : eve.keyCode;
        
        //alert(key);
        
        if(key == 40 && obj.value.length >= 3)
        {
            var cuadro=document.getElementById("resBus");
            els=cuadro.getElementsByTagName("DIV");
            
            
            if(els[filant])
            {
                els[filant].className="objetoLista";
                els[filant].focus();
            }    
            
            
            
            filant++;
            
            if(els[filant])
            {
                els[filant].className="lista_productos2";
                els[filant].focus();
            }    
            
            //els[0].innerHTML="l";
            
            return false;
            
        }
        
        if(key == 38 && obj.value.length >= 3)
        {
            var cuadro=document.getElementById("resBus");
            els=cuadro.getElementsByTagName("DIV");
            
            
            if(els[filant])
            {
                els[filant].className="objetoLista";
                els[filant].focus();
            }    
            
            
            if(filant > 0)
                filant--;
            
            if(els[filant])
            {
                els[filant].className="lista_productos2";
                els[filant].focus();
            }    
            
            //els[0].innerHTML="l";
            
            return false;
            
        }
        
        if(key == 13 && filant != -1)
        {
            //alert(filant);
            ocultaBuscador(document.getElementById('lista_'+filant));   
        } 
        
        
        if(key == 13)
        {
            
            
            
            
             muestraDesc(1);
            
            can=document.getElementById('cantidad2');
        
            can.value="1";
            can.focus();
            can.setSelectionRange(0, 1);
            
            
            
            return false;
        } 
        
        if(!isNaN(obj.value))
            return false;   
        
        
        if(obj.value.length >= 3)
        {
            var url="ajax/buscaProductos.php?clave="+obj.value;
            
            var res=ajaxR(url);
            
            var aux=res.split('←');
            
            if(aux[0] != 'exito')
            {
                alert(res);
                return false;
            }
            
            if(aux.length <= 1)
            {
                document.getElementById('resBus').style.display="none";
                return false;   
            }
            
            var cuadro=document.getElementById('resBus');
            
            cuadro.innerHTML="";    
            
            //var sel=document.getElementById('respuestasBusc');
            //sel.options.length=0;
            
            var op=new Array();
            
            for(var i=1;i<aux.length;i++)
            {
                var ax=aux[i].split('~');
                
                op[i]=document.createElement("DIV");
                op[i].id="lista_"+(i-1);
                op[i].innerHTML=ax[1]/*+"<span style='display:none'>"+ax[0]+"</span>"*/;
                op[i].className="objetoLista";
                op[i].onclick=function(){ocultaBuscador(this);}
                
                
                
                cuadro.appendChild(op[i]);
                
                //option=null;   
            }
            
            document.getElementById('resBus').style.display="block";
        }
        else
        {
            document.getElementById('resBus').style.display="none";
        }
    }
    
    function ocultaBuscador(obj)
    {
       
        /*var aux=obj.getElementsByTagName('span');
        
        var aux=aux[0].innerHTML;*/
        
        var aux=obj.innerHTML;
        
        aux=aux.replace("</span>", "");
        aux=aux.replace("</span>", "");
        aux=aux.replace('<span class="txtNegrita">', "");
        aux=aux.replace('<span class="txtVerde">', "");
        
        /*var aux=aux.split(' - ');
        aux=aux[0]+":"+aux[1];*/
        
        
        document.getElementById('buscadorLabel').value=aux;
        
        document.getElementById('resBus').style.display="none";
        
        can=document.getElementById('cantidad2');
        
        can.value="1";
        can.focus();
        can.setSelectionRange(0, 1);
        filant=-1;
        
    }  
    
    function validaKey(eve, f)
    {
        key=(eve.which) ? eve.which : eve.keyCode;
        
        if(key == 13)
            agregaFila(f);            
    }
    
    function agregaFila(f)
    {
        filant=-1;
        var busc=document.getElementById('buscadorLabel');
        
        var aux=busc.value.split(' | ');
        var busqueda=aux[0];
        
        var can=document.getElementById('cantidad2');
        var tabla=document.getElementById('listaProductos');
        
        //alert(tabla);
        
        var url="ajax/buscaProdOrCoId.php?val="+busqueda+"&can="+can.value+"&can2="+can.value;
        
        if(busc.value.length == 0)
        {
            alert('Debe introducir un valor a agregar');
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
        
        if(can.value.length == 0)
        {
            alert('Debe introducir una cantidad a agregar');
            can.focus();
            can.setSelectionRange(0, can.value.length);
            return false;
        }
        
        if(parseInt(can.value) > 1 && sig == 1)
        {
            alert('Sólo es permitido regalar un producto');
            can.value="1";
            can.focus();
            can.setSelectionRange(0, can.value.length);
            return false;
        }
        
        
        var res=ajaxR(url);
        
        //alert(res);
        
        var aux=res.split('|');
        
        if(aux[0] != 'exito')
        {
            alert(res);
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
        
        //Buscamos si existe uno igual
        trs=tabla.getElementsByTagName('tr');
        var can2=-1;
        for(i=1;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            //alert(tds[8].valor+" - "+sig);
            if(tds[0].innerHTML == aux[2] && sig == tds[9].valor && aux[2] != '18000')
            {
                
                //alert('Repetido');
                can2=parseInt(tds[2].innerHTML)+parseInt(can.value);
                //alert(can2);
                eliminarItem(tds[0]);
                break;
            }    
        }
        if(can2 != -1)
        {
            var url="ajax/buscaProdOrCoId.php?val="+busqueda+"&can="+can2+"&can2="+can2;
            
            var res=ajaxR(url);
            var aux=res.split('|');
            
            if(aux[0] != 'exito')
            {
                alert(res);
                busc.focus();
                busc.setSelectionRange(0, busc.value.length);
                return false;
            }
        }
        else
            can2=can.value;
        
        
        if(aux[1] == '1808')
        {
            var precio=null;
            
            while(precio == null)
            {
                var monax=prompt("Inserte el precio del producto");
                precio=parseFloat(monax);
                if(isNaN(precio))
                {
                    alert("Valor no valido, intentalo nuevamente");
                    precio=null;
                }
                
                if(precio < 0)
                    precio=null;    
            }
            
            
            
            
            aux[5]=precio;
            aux[7]=precio*can2;
            aux[4]=Mascara('$#,###.##', precio)
            aux[6]=Mascara('$#,###.##', precio*can2)
            
        }
        
        
        
        var newRow=tabla.insertRow(1);
        newRow.className="move";
        
        if(sig == 1)
        {
            aux[4]="$0";
            aux[6]="$0";
            aux[5]=0;
            aux[7]=0;
        }
        //Orden de lista
        var newCell=newRow.insertCell(0);
        newCell.innerHTML=aux[2];
        newCell.align="center";
        
        //Descripcion
        var newCell=newRow.insertCell(1);
        newCell.innerHTML=aux[3];
        newCell.align="left";
        
        //Cantidad
        var newCell=newRow.insertCell(2);
        newCell.innerHTML=can2;
        newCell.align="right";
        
        //Precio formato
        var newCell=newRow.insertCell(3);
        newCell.innerHTML=aux[4];
        newCell.align="right";
        
        //Oferta
        var newCell=newRow.insertCell(4);
        newCell.innerHTML=aux[9];
        newCell.align="right";
        
        //monta
        var newCell=newRow.insertCell(5);
        newCell.innerHTML=aux[6];
        newCell.align="right";
        
        //Eliminar        
        var newCell=newRow.insertCell(6);
        newCell.innerHTML='<a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a>';
        newCell.align="center";
        
        //Fila precio oculta
        var newCell=newRow.insertCell(7);
        newCell.valor=aux[5];
        newCell.width="0";
        newCell.style="0";
        
        //Fila monto oculta
        var newCell=newRow.insertCell(8);
        newCell.valor=aux[7];
        newCell.width="0";
        newCell.border="0";
        
        //Fila regalo oculta
        var newCell=newRow.insertCell(9);
        newCell.valor="NO";
        newCell.width="0";
        newCell.border="0";
        
        //Fila familia oculta
        var newCell=newRow.insertCell(10);
        newCell.valor=aux[8];
        newCell.width="0";
        newCell.border="0";
        
        //id familia oculta
        var newCell=newRow.insertCell(11);
        newCell.valor=aux[1];
        newCell.width="0";
        newCell.border="0";
        
        sig=0;



       //Buscamos familias 21126
       familiaAct=aux[8];
       familias="";
       cans="";
       posiciones=Array();
       nfams=0;
       
       trs=tabla.getElementsByTagName('tr');
       var can2=0;
       for(i=1;i<trs.length;i++)
       {
           tds=trs[i].getElementsByTagName('td');
           //alert(tds[8].valor+" - "+sig);
           if(tds[10].valor == familiaAct)
           {
               //Validamos si tiene la misma promocion
               //var url="ajax/validaPromo.php?id_prod1="+aux[1]+"&id_prod2="+aux[8]+"&can="+can2;
               can2+=parseInt(tds[2].innerHTML);
               
               if(familias != '')
                    familias+=",";
                    
               if(cans != '')
                    cans+=",";     
               
               familias+=tds[11].valor;
               cans+=tds[2].innerHTML;
               posiciones[nfams]=i;     
                
               nfams++;
           }     
       }
       
       if(nfams > 1 && familias != '')
       {
           var url="ajax/validaPromo.php?prods="+familias+"&can="+can2+"&cans="+cans+"&prodAct="+aux[1];
           
           var res=ajaxR(url);
           
           var aux=res.split('|');
           
           if(aux[0] == 'exito')
           {
           
                for(i=0;i<parseInt(aux[1]);i++)
                {
                    var ax=aux[i+2].split('~');
                    tds=trs[posiciones[i]].getElementsByTagName('td');
                    
                    if(ax[0] != 'NO')
                    {
                    
                        tds[3].innerHTML=ax[0];
                        tds[5].innerHTML=ax[1];
                        tds[7].valor=ax[2];
                        tds[8].valor=ax[3];
                    }    
                }
                
                
           }
           else
                alert(res);     
           
       }
       
        
        //alert('OK');

        calculaTotalProd();
        
        can.value="";
        busc.value="";
        //busc.focus();
        
        setTimeout("document.getElementById('buscadorLabel').focus();", 100);
        
    }
    
    
    function calculaTotalProd()
    {
       cambiaTotal(<?php echo $id_pedido; ?>);
    }
    
    function redond(val, ndec)
    {
        return(Math.round(eval(val)*Math.pow(10,ndec))/Math.pow(10,ndec));  
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
    
    //setTimeout("cambiaTotal(<?php echo $id_pedido; ?>)", 1000)
    
        
    </script>  
      
      
      <!---Termina el script del grid-->

