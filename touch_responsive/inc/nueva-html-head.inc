
<script type="text/javascript">
/* <![CDATA[ */
      
      
      var totalVenta=0;
      var descuentoAplicado=0;
      var aplicaDescuento=0;
      
      
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
    var posfijo=aux[aux.length-1]   
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
      
      
     function muestraDesc()
     {
         
         var obj=document.getElementById('buscadorLabel');
         
         //Buscamos si tiene descripcion
        var aux=obj.value;
        aux=aux.split(':');
        if(aux.length == 1)
        {
            var res=ajaxR('ajax/buscaProdOrCoId.php?val='+aux[0]+"&can=1");
            var ax=res.split('|');
            
            if(ax[0] != 'exito')
            {
                alert('Producto incorrecto, pruebe con uno diferente');
                obj.focus();
                obj.setSelectionRange(0, obj.value.length);
                return false;
            }
            
            obj.value=aux[0]+":"+ax[3];
            //alert(res);
        }
     } 
      
    //Funcionalidad del buscador
    function activaBuscador(obj, eve)
    {
        
        key=(eve.which) ? eve.which : eve.keyCode;
        
        //alert(key);
        
        if(key == 13)
        {
            
            
            
            
            can=document.getElementById('cantidad2');
        
            can.value="1";
            can.focus();
            can.setSelectionRange(0, 1);
            
            return false;
        }    
        
        
        if(obj.value.length >= 3)
        {
            var url="ajax/buscaProductos.php?clave="+obj.value;
            
            var res=ajaxR(url);
            
            var aux=res.split('|');
            
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
        /*var aux=aux.split(' - ');
        aux=aux[0]+":"+aux[1];*/
        
        
        document.getElementById('buscadorLabel').value=aux;
        
        document.getElementById('resBus').style.display="none";
        
        can=document.getElementById('cantidad2');
        
        can.value="1";
        can.focus();
        can.setSelectionRange(0, 1);
        
        
    }  
    
    function validaKey(eve, f)
    {
        key=(eve.which) ? eve.which : eve.keyCode;
        
        if(key == 13)
            agregaFila(f);            
    }
    
    function agregaFila(f)
    {
        
        alert('?');
        var busc=document.getElementById('buscadorLabel');
        var can=document.getElementById('cantidad2');
        var tabla=document.getElementById('listaProductos');
        
        var url="ajax/buscaProdOrCoId.php?val="+busc.value+"&can="+can.value;
        
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
        for(i=0;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            //alert(tds[8].valor+" - "+sig);
            if(tds[0].innerHTML == aux[2] && sig == tds[9].valor)
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
            var url="ajax/buscaProdOrCoId.php?val="+busc.value+"&can="+can2;
            
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
        
        
        
        var newRow=tabla.insertRow(-1);
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
        newCell.valor=sig;
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


        calculaTotalProd();
        
        
        alert(can);
        can.value="";
        
        
        busc.value="";
        busc.focus();
        
    }
    
    
    function calculaTotalProd()
    {
        var total=0;
        var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
        
        var tabla=document.getElementById('listaProductos');
        trs=tabla.getElementsByTagName('tr');
        for(i=0;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            
            total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);
            
        }
        
        
        document.getElementById('total').value=Mascara("$#,##.##", total);
        if(es_paquete == 1)
        {
            total=total*(1-descuento);
        }
        
        //Descuento porcentaje
        if(aplicaDescuento == 1)
        {
            total=total*(1-descuentoAplicado);
        }
        
        //Descuento monto
        if(aplicaDescuento == 2)
        {
            total=total-descuentoAplicado;
        }
        
        totalVenta=redond(total,0);    
        
        document.getElementById('total').value=Mascara("$#,##.##", total);
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
             
             
    var id_autorizacion=0; 
    var id_ver=0;
    var sig=0; 
    
    function validaAut()
    {
        res=ajaxR('ajax/getValidacion.php?id='+id_autorizacion);

        // Fines de prueba, autorizar aleatoriamente 
        // aux = Math.random() * 100 > 60 ? "SI" : "NO";
        
        var aux=res.split('|'); 
        
        if(aux[0] == 'SI')
        {
            

            $("#divEspera").css ("display", "none");
            $("#es_regalo").prop ("checked", true);
            
            alert(aux[1]);
            
            if(aux[2] == 1)
            {
                sig=1;
            }
            if(aux[2] == 2)
            {
                //alert(aux[3]);
                //alert(descuento);
                aplicaDescuento=1;
                descuentoAplicado=parseFloat(aux[3])/100;
                calculaTotalProd();
            }
            if(aux[2] == 3)
            {
                aplicaDescuento=2;
                descuentoAplicado=parseFloat(aux[3]);
                calculaTotalProd();
            }
            
            $("#producto").focus ();
                
            clearInterval(id_ver);
        }
        if(aux[0] == 'NO')
        {
            alert("La peticion no ha sido autorizado");
            $("#divEspera").css ("display", "none");
            $("#es_regalo").prop ("checked", false);
            $("#es_regalo").prop ("disabled", false);
            $("#img_regalo").prop ("disabled", false);
            clearInterval(id_ver);
            
            $("#producto").focus ();
        }
    }           
             
     function ajaxR(url)
        {
            if(window.ActiveXObject)
            {       
                var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            else if (window.XMLHttpRequest)
            {       
                var httpObj = new XMLHttpRequest(); 
            }
            httpObj.open("POST", url , false, "", "");
            httpObj.send(null);
            return httpObj.responseText;
        }        
             
      var descuento = parseFloat ("<?php printf ("%.3f", $descuento); ?>");

      function cargaNuevoFolio () {
    	  $.get( "ajax/nuevoFolio.php?tipo=" + ($("#es_pedido").prop('checked') ? "P" : "V") + "&idp=" + $("#id_pedido").val (), function( data ) {
        	  if (coincidencias = data.match (/^ok\|folio\:(\w+)$/i)) {
            	  if ($("#es_pedido").prop('checked')) {
                	  $("#folio_pedido").val (coincidencias[1]);
                	  $("#folio_venta").val ("");
            	  } else {
            		  $("#folio_venta").val (coincidencias[1]);
                	  $("#folio_pedido").val ("");
            	  }
        	  }
    	  });
      }

      function calculaTotal () {
    	// Sumar el total 
		    total = 0.0;
		    $("#listaProductos tr.move td.tabla_total p").each (function(index, value) {
			    total += parseFloat($(this).html ().replace (/[\$\s,]/g, ""));
			});
			$("#total").val ("$ " + moneyFormat(total));
      }

      function calculaPrecios () {
    	  $("#listaProductos tr.move td.tabla_id_producto").each (function(index, value) {
		    	var id_producto = $(this).find("p").html();
		    	var tr = $(this).parent ();

		    	$.ajax({
	        	     async: false,
	        	     type: 'GET',
	        	     url: "ajax/precioProducto.php?idp=" + id_producto
	        	}).done (function (data) {
	        		if (coincidencias = data.match (/^ok\|precio\:(\d+(?:.\d+)?)\|nombre\:(.*)$/i)) {
		    			precio = parseInt($(tr).find("td.tabla_detalles input.es_regalo").val ()) > 0 ? 0 : coincidencias[1];
		    			if ($("#es_paquete").prop ("checked")) precio = parseFloat(precio) - parseFloat(precio) * descuento;
		    			$(tr).find("td.tabla_precio p").html ("$ " + moneyFormat(precio));
		    			$(tr).find("td.tabla_total p").html ("$ " + moneyFormat(precio * parseFloat($(tr).find("td.tabla_cantidad p").html())));
		    			// Calcular total cada iteración 
		    			calculaTotal();
					}
	            }).fail (function () {
		            alert ("Error al intentar obtener el precio del producto seleccionado.");
	            });
		    });
      }

      function eliminarItem (item) {
          
          //alert(item);
    	  $(item).parents ("tr.move").first ().remove ();
    	  //calculaTotal ();
		  $("#producto, #cantidad").val ("");
		  
		  calculaTotalProd();
      }

      function bloqueaPantalla () {
    	  if ($("#es_regalo").prop('checked'))
				$("#divEspera").css ("display", "block");
      }

      function guardarVenta () {
    	  var retval = false; 
    	  
    	  $("#menu_cerrar").prop ("disabled", true);
    	  
          //var es_regalo = $("#es_regalo").prop ("checked") ? "1" : "0"; 
          var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
          var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
          var id_pedido = $("#id_pedido").val ();

          var datos = "nitems=" + $("#listaProductos tr.move").length;
          /*$("#listaProductos tr.move").each (function(index, value) {
              datos += "&idp" + index + "=" + $(this).find ("td.tabla_id_producto p").html () + "&can" + index + "=" + $(this).find ("td.tabla_cantidad p").html () + (parseInt($(this).find("td.tabla_detalles input.es_regalo").val ()) > 0 ? "&reg=" + index : "");
          });*/
         
         //Detalles
         
         var tabla=document.getElementById('listaProductos');
         trs=tabla.getElementsByTagName('tr');
         for(i=0;i<trs.length-1;i++)
         {
            tds=trs[i+1].getElementsByTagName('td');
            
            //total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);tds[11].innerHTML
            
            datos += "&idp" + i + "=" + tds[11].valor + "&can" + i + "=" + tds[2].innerHTML + "&pre" + i +"=" + tds[7].valor+ "&mon" + i +"=" + tds[8].valor;
            
         }
         
         //alert(datos);
         
         
         var url= "ajax/guardaNuevaVenta.php?idp=" + id_pedido  + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos + "&totalPed="+totalVenta;

         /* $.ajax({
        	     async: false,
        	     type: 'GET',
        	     url: "ajax/guardaNuevaVenta.php?idp=" + id_pedido  + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos
        	}).done (function (data) {
            	if (coincidencias = data.match (/^ok\|idp:(\d+)\|folio:(\w+)$/i)) {
                	$("#id_pedido").val (coincidencias[1]);
            		retval = true;
            	}
            }).fail (function () {
            	alert ("Error al registrar la sesión.");
            	retval = false;
            });

          $("#menu_cerrar").prop ("disabled", false);*/
         
         //alert(url);
         
         var res=ajaxR(url);
         
         aux=res.split('|');
         if(aux[0] == 'OK')
         {
             var ax=aux[1].split(':');
             $("#id_pedido").val (ax[1]);
             
             retval = true;
         }
         else
            retval = false;
         

          return retval;
      }

	function actualizaOfertas () {
		var retval = false; 
		var es_regalo = $("#es_regalo").prop ("checked") ? "1" : "0";
		var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
		var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
		var id_pedido = $("#id_pedido").val ();

  		var datos = "nitems=" + $("#listaProductos tr.move").length;
        $("#listaProductos tr.move").each (function(index, value) {
            datos += "&idp" + index + "=" + $(this).find ("td.tabla_id_producto p").html () + "&can" + index + "=" + $(this).find ("td.tabla_cantidad p").html ();
        });

        $.ajax({
	   	     async: false,
	   	     type: 'GET',
	   	     url: "ajax/calculaOfertas.php?idp=" + id_pedido + "&re=" + es_regalo + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos
  	 	}).done (function (data) {
	       	//alert (data);
       }).fail (function () {
	       	alert ("Error al buscar las ofertas.");
	       	retval = false;
       });

     return retval;
	}

	function agregarProducto () {
		if ($("#id_producto").val ().match (/^\d+$/) && $("#cantidad").val ().match (/^-?\d+$/)) {
			$.ajax({
	       	     async: false,
	       	     type: 'GET',
	       	     url: "ajax/precioProducto.php?idp=" + $("#id_producto").val ()
	       	}).done (function (data) {
	       		if (coincidencias = data.match (/^ok\|precio\:(\d+(?:.\d+)?)\|nombre\:(.*)$/i)) {
					var precio = coincidencias[1];
					var nombre = coincidencias[2];
					var existe = false;
					
					if ($("#es_regalo").prop ("checked")) {
						precio = 0;
					} else if ($("#es_paquete").prop ("checked")) {
						precio = parseFloat(precio) - parseFloat(precio) * descuento;
					}
					
					// Si el producto ya existe, se actualiza la cantidad y el total 
					// Se desconoce el comportamiento si se trata de un regalo   
								    		
					$("#listaProductos tr.move td.tabla_id_producto").each (function(index, value) {
						if ($(this).find("p").html() == $("#id_producto").val ()) {
							existe = true;
							if ($("#es_regalo").prop ("checked")) {
								alert ("Imposible registrar este regalo.\nEl producto marcado se encuentra previamente registrado.");
							} else {
								cantidad = parseInt($("#cantidad").val())+parseInt($(this).parent().find("td.tabla_cantidad p").html());
								$(this).parent().html ("<td class=\"tabla_id_producto\"><p>" + $("#id_producto").val() + "</p></td><td><p>" + $("#producto").val() + "</p></td><td class=\"tabla_cantidad\"><p>" + cantidad + "</p></td><td class=\"tabla_precio\"><p>$ " + moneyFormat(precio) + "</p></td><td class=\"tabla_total\"><p>$ " + moneyFormat(precio * cantidad) + "</p></td><td class=\"tabla_detalles\"><a href=\"javascript:void(0)\" onclick=\"eliminarItem(this)\" class=\"eliminar\"> <span>eliminar</span></a> <input type=\"hidden\" class=\"es_regalo\" value=\"0\" /> </td>");
							}
						}
					});
					
					if (!existe) {
						$("#listaProductos").append ("<tr class=\"move\"><td class=\"tabla_id_producto\"><p>" + $("#id_producto").val() + "</p></td><td><p>" + nombre + "</p></td><td class=\"tabla_cantidad\"><p>" + $("#cantidad").val() + "</p></td><td class=\"tabla_precio\"><p>$ " + moneyFormat(precio) + "</p></td><td class=\"tabla_total\"><p>$ " + moneyFormat(precio * parseFloat($("#cantidad").val())) + "</p></td><td class=\"tabla_detalles\"><a href=\"javascript:void(0)\" onclick=\"eliminarItem(this)\" class=\"eliminar\"> <span>eliminar</span></a> <input type=\"hidden\" class=\"es_regalo\" value=\"" + ($("#es_regalo").prop ("checked") ? "1" : "0") + "\" /> </td></tr>");
					}

					if ($("#es_regalo").prop ("checked")) {
						$("#es_regalo").prop ("checked", false);
					}

					calculaTotal();
				}

				// Buscar y recalcular precios por la cuestión de las ofertas 
				actualizaOfertas ();
	        });
		} else {
			alert ("El producto seleccionado no existe o la cantidad solicitada es incorrecta.");
		}

		$("#producto, #cantidad").val ("");
		$("#producto").focus ();
	}

	function buscarProducto () {
		if (coincidencias = $("#producto").val ().match (/^(\d+)~?/i)) {
			es_ok = false;
			$.ajax({
		   	     async: false,
		   	     type: 'GET',
		   	     url: "ajax/buscarProductoCB.php?cb=" + coincidencias[1]
		   	}).done (function (data) {
		       	if (coincidencias = data.match (/^ok\|idp:(\d+)$/i)) {
		           	$("#id_producto").val (coincidencias[1]);
		           	$("#cantidad").val ("1");
		           	$("#cantidad").focus ().select();
		           	es_ok = true;
		       	}
	       });

		   if (!es_ok) {
			   // Blanqueamos el id del producto 
			   $("#id_producto, #producto").val ("");
			   alert ("¡Alerta!\nNo existe el producto solicitado.");
		   }
		}
	}

	function marcaRegalo () {
		 var es_regalo = $("#es_regalo").prop ("checked") ? "1" : "0";
         var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
         var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
         var id_pedido = $("#id_pedido").val ();
         
         $("#es_regalo").prop ("disabled", true);
         $("#img_regalo").prop ("disabled", true);
         

         var hay_regalo = false;

         // Verificar que previamente no haya regalo seleccionado 
         
         $("#listaProductos tr.move td.tabla_detalles input.es_regalo").each (function(index, value) {
             if (parseInt($(this).val ()) > 0)
            	 hay_regalo = true;
         });

         if (hay_regalo) {
        	 alert ("Imposible continuar.\nYa existe un regalo previamente seleccionado.");
 			return false;
         }

         var datos = "nitems=" + $("#listaProductos tr.move").length;
          /*$("#listaProductos tr.move").each (function(index, value) {
              datos += "&idp" + index + "=" + $(this).find ("td.tabla_id_producto p").html () + "&can" + index + "=" + $(this).find ("td.tabla_cantidad p").html () + (parseInt($(this).find("td.tabla_detalles input.es_regalo").val ()) > 0 ? "&reg=" + index : "");
          });*/
         
         //Detalles
         
         var tabla=document.getElementById('listaProductos');
         trs=tabla.getElementsByTagName('tr');
         for(i=0;i<trs.length-1;i++)
         {
            tds=trs[i+1].getElementsByTagName('td');
            
            //total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);tds[11].innerHTML
            
            datos += "&idp" + i + "=" + tds[11].valor + "&can" + i + "=" + tds[2].innerHTML + "&pre" + i +"=" + tds[7].valor+ "&mon" + i +"=" + tds[8].valor;
            
         }
	    
	    
		if ($("#listaProductos tr.move").length > 0) {
		    
		    $.ajax({
                    async: false,
                    type: 'GET',
                    url: "ajax/guardaAutorizacion.php?idp=" + id_pedido + "&re=" + es_regalo + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos
               }).done (function (data) {
                   
                   //alert(data);
                   
                   id_autorizacion=data;
                   
                   bloqueaPantalla (); 
                   
                   id_ver=setInterval('validaAut()', 5000);
                   return true;
               }).fail (function () {
                   alert ("Error al registrar la autorización.");
                   return false;
               });
		} else {
			alert ("Imposible continuar.\nSeleccione cuando menos un producto.");
			return false;
		}
	}

	function changeHashOnLoad() {
		var base_href = location.href.match (/^([^\#]*)(?:\#.*)?$/i)[0];
        location.href = base_href + "#";
        setTimeout("changeHashAgain()", "50");
    }

    function changeHashAgain() {          
        location.href += "1";
    }

    var storedHash = window.location.hash;
    setInterval(function () {
        if (location.hash != storedHash) {
            location.hash = storedHash;
        }
    }, 250);


	$(document).ready(function() {

		// Bloquear evento goBack () 
		changeHashOnLoad();
		
		<?php if (!isset($folio)) { ?>
			cargaNuevoFolio ();
		<?php } ?>

			$("#es_pedido").on ("change", function () {
				alert ("Cambio pedido");
				if ($("#es_pedido").prop('checked')) {
					// Desactivar el regalo 
					// cambiaRegalo ();  
					$("#es_regalo").prop ("checked", false);
					$("#es_paquete").prop ("checked", false);
					$("#es_paquete").prop ("disabled", true);
					$("#es_regalo").prop ("disabled", true);
					$("#menu_cerrar span").html ("Generar Nota");
					$("#menu_cerrar").removeClass ("nota").addClass ("pedido");
				} else {
					$("#es_paquete").prop ("disabled", false);
					$("#es_regalo").prop ("disabled", false);
					$("#menu_cerrar span").html ("Cerrar Venta");
					$("#menu_cerrar").removeClass ("pedido").addClass ("nota");
				}
				calculaPrecios ();
				cargaNuevoFolio ();
				$("#producto").focus ();
			});

			$("#es_regalo").on ("click", function () {

				return marcaRegalo ();
			    
			});
			
			/*$("#img_regalo").on ("click", function () {
                
				return marcaRegalo ();
				
            });*/

			$("#cancelar").on ("click", function () {
			    
			    $("#es_regalo").prop ("disabled", false);
                $("#img_regalo").prop ("disabled", false);
			    
				$("#divEspera").css ("display", "none");
				$("#es_regalo").prop ("checked", false);
				$("#producto").focus ();
				
				clearInterval(id_ver);
			    ajaxR('ajax/cancelaAuto.php?id='+id_autorizacion);	
				
			});

			$("#es_paquete, #es_regalo").on ("change", function () {
				alert ("ERR");
				$("#buscadorLabel").focus ();
			});

			$("#salirbtn").on ("click", function () {
				if (confirm ("¿Realmente desea cerrar sin guardar?")) {
					top.location.href = "index.php";
				}
			});

			$("#img_regalo").on ("click", function () {
				if (!$("#es_regalo").prop ("disabled")) {
					$("#es_regalo").prop('checked', true);
					$("#es_regalo").prop('checked', marcaRegalo ());
					
				}
				$("#producto").focus ();
			});

		    $("#cantidad").on ("keydown", function (e) {
		    	var key = e.charCode || e.keyCode || 0;
		    	if (key == 13) {
			    	agregarProducto();
			    	return false;
		    	}
		    });
		    $("#cantidad").ForceNumericOnly();

		    $("#producto").on ("keydown", function (e) {
		    	var key = e.charCode || e.keyCode || 0;
		    	if (key == 13) {
			    	buscarProducto();
			    	return false;
		    	}
		    });

		    // Botón agregar... 
		    $("#agregar").on ("click", function () {
				agregarProducto ();
		     });

		    $("#es_paquete").on ("click", function () {
		    	//calculaPrecios ();
		    	calculaTotalProd();
			});

		     $("#menu_cerrar").on ("click", function ()
		     {
		    	 if (!$("#listaProductos tr.move").length)
		    	 {
			    	 // Verificar la selección de productos en lista
		    		 alert ("Imposible continuar.\nNo ha seleccionado productos para la venta.");
		    		 $("#producto").focus ();
				     return false; 
		    	 }
		    	 else
		    	 {
		    		 if (!guardarVenta ())
		    		 {
			    		 // No se pudo guardar la venta 
		    			 alert ("Error mientras se procesaba la venta.\nActualice la pantalla e intente nuevamente.");
				    	 return false;
		    		 }
		    		 else
		    		 {
			    		 if ($(this).hasClass ("pedido"))
			    		 {
			    			// Si se trata de un pedido, imprimir ticket y bloquear la opción pedido 
			    			// Tambien habilita el botón para cerrar la venta 
			    			 $("#es_pedido").prop ("disabled", true);
			    			 agregarProducto = function () { alert ("Función deshabilitada."); return false; };
			    			 window.open ("index.php?scr=ticket&idp=" + $("#id_pedido").val (), "_blank");
			    			 $("#menu_cerrar span").html ("Cerrar Venta");
			    			 $("#menu_cerrar").removeClass ("pedido").addClass ("nota");
			    			 return false;
			    		 }
			    		 else
			    		 {
			    			 if ($("#menu_cerrar").attr ("href").match (/&idp=\d+/i))
			    			 {
						    	 $("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href").replace (/&idp=\d+/i, "&idp=" + $("#id_pedido").val () + "#tmp"));
						     }
						     else
						     {
						    	 $("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href") + "&idp=" + $("#id_pedido").val () + "#tmp");
						     }
						     return true;
			    		 }
		    		 }
		    	 }
		     });

		    $("#es_regalo").prop ("checked", false);
		    $("#producto").focus ();
				
		});

      /* ]]> */
  </script>
  
