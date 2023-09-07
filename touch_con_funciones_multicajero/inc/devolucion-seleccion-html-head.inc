<script type="text/javascript">

    var totalVenta=0;
    var descuentoAplicado=0;
    var aplicaDescuento=0;
    var existeRegalo=0;
    var pedido="";
    var idPedido="";
    var es_dev_completa=0;
//al cargar la pagina
    $(document).ready(function(){
    //capturamos el id del pedido
        idPedido=document.getElementById('id_ped').value;
        //alert(pedido);
    });

    function regresaTodo(){
        if(es_dev_completa==1){
            alert("La nota ya fue agregada completamente!!!");
            return false;
        }
        var tabla=document.getElementById("listaProductos");
        var tam_tabla=$("#listaProductos tr").length;
    //eliminamos los registros que esten en la tabla
       /* for(var i=1;i==tam_tabla;i++){
        }
        return false;*/
        var cargaTodo=ajaxR("ajax/buscaProductosParaDevolver.php?tipo=todos&id_pedido="+idPedido);
       // alert("Respuesta:"+cargaTodo);
        var auxiliar=cargaTodo.split('~');
        
        if(auxiliar[0] != 'exito'){
            alert(res);
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
    //llenamos la tabla del detalle de devolución
        for(var i=1;i<auxiliar.length-1;i++){
            var aux=auxiliar[i].split("|");
            //capturamos id de producto
            var id_prod_selec=aux[1];
            var newRow=tabla.insertRow(1);
            newRow.className="move";
            if(sig==1){
                aux[4]="$0";
                aux[6]="$0";
                aux[5]=0;
                aux[7]=0;
            }
    //id del producto
        var newCell=newRow.insertCell(0);
        
        newCell.innerHTML=aux[0];
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
    //Orden de lista
        var newCell=newRow.insertCell(1);
        newCell.innerHTML=aux[1];
        newCell.align="center";
        
    //Descripcion
        var newCell=newRow.insertCell(2);
        newCell.innerHTML=aux[2];
        newCell.align="left";
        
    //Cantidad
        var newCell=newRow.insertCell(3);
        newCell.innerHTML=aux[8];
        newCell.align="right";
        
    //Precio formato
        var newCell=newRow.insertCell(4);
        newCell.innerHTML=aux[3];
        newCell.align="right";
        
     //Descuento porcentual
        var newCell=newRow.insertCell(5);
        newCell.innerHTML=aux[4];
        newCell.align="right";
        
    //monto total
        var newCell=newRow.insertCell(6);
        newCell.innerHTML=aux[5];
        newCell.align="right";
        
        //Eliminar        
            var newCell=newRow.insertCell(7);
            newCell.innerHTML='<a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a>';
            newCell.align="center";
            
            sig=0;calculaTotalProd(); 
        }//fin de for i
    //marcamos que es una devolución completa
        es_dev_completa=1;
    //recalculamos monto y reseteamos valores de buscador y cantidad
               
       // can.value="";
     //   busc.value="";
    }

    function Mascara(mascara, valor){   
    //validamos que realmente haya una mascara a evaluar
    if(mascara == 'null' || mascara == null || mascara.length <= 0){
        return valor;           
    }
    //Obtenemos los datos relevantes de la mascara
    var aux=mascara.split(",");
    var coma=aux.length;
    var aux=mascara.replace(',','');
    aux=aux.replace('.','');
    aux=aux.split('#');
    var prefijo=aux[0];
    var posfijo=aux[aux.length-1];
    var aux=mascara.split(".");
    if(aux.length > 1){
        var ndec=aux[1].replace(posfijo,'').length;
    }
    else{
        var ndec=0;
    }            
//Empezamor a evaluar   
    var cad=valor;
    //alert(valor);
//Proceso para numero de posiciones decimales
    if(ndec > 0){
        cad=parseFloat(cad);
        cad=Math.round(cad*Math.pow(10,ndec))/Math.pow(10,ndec);
        //alert(cad);
        cad=cad;
        if(cad<0)
        {
            cad=Math.abs(cad);
            prefijo='-'+prefijo;
        }
        cad=cad.toString();
    }
//Comas en numeros
    if(coma > 1){           
        //alert(cad);
        var aux=cad.split('.');
        cM=0;
        var ax="";
        for(m=aux[0].length-1;m >= 0;m--){
            cM++;
            ax=aux[0].charAt(m)+ax;
            if(cM == 3 && m != 0)
            {

                ax=','+ax;
                cM=0;
            }
        }
        cad=ax;
        if(aux.length > 1 && ndec > 0){
            dec=aux[1];
            for(var i=dec.length;i<ndec;i++)
                dec+="0";
            cad=cad+"."+dec;
        }
        else if(ndec > 0){
            dec=aux[0];
            nu='';
            for(var i=nu.length;i<ndec;i++)
                nu+="0";
            cad=cad+"."+nu;
        }
    }
    
//Prefijos y posfijos
    if(prefijo.length > 0){
        cad=prefijo+cad;
    }
    if(posfijo.length > 0){
        cad=cad+posfijo;/*9*/
    }
        
    return cad;
} 
      
      
    function muestraDesc(val){
         var obj=document.getElementById('buscadorLabel');
         
         //Buscamos si tiene descripcion
        var aux=obj.value;
        
        if(aux == '')
            return false;
        
        aux=aux.split(' | ');
        if(aux.length == 1)
        {
            var res=ajaxR('ajax/buscaProdOrCoId.php?val='+aux[0]+"&can=1&can2=1");
            //alert(res);
            
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
    function activaBuscador(obj, eve){
        key=(eve.which) ? eve.which : eve.keyCode;
    //alert(key);
    //alert(obj.value.indexOf(' '));
        
        if(key == 40 && obj.value.length >= 3){
            var cuadro=document.getElementById("resBus");
            els=cuadro.getElementsByTagName("DIV");
            
            
            if(els[filant]){
                els[filant].className="objetoLista";
                els[filant].focus();
            }    
            filant++;
            if(els[filant]){
                els[filant].className="lista_productos2";
                els[filant].focus();
            }    
            return false;
            
        }
        
        if(key == 38 && obj.value.length >= 3){
            var cuadro=document.getElementById("resBus");
            els=cuadro.getElementsByTagName("DIV");
            
            
            if(els[filant]){
                els[filant].className="objetoLista";
                els[filant].focus();
            }    
            
            if(filant > 0)
                filant--;
            
            if(els[filant]){
                els[filant].className="lista_productos2";
                els[filant].focus();
            }  
            //els[0].innerHTML="l";
            return false;
        }
        
        
        if(key == 13 && filant != -1){
            //alert(filant);
            ocultaBuscador(document.getElementById('lista_'+filant));   
        } 
        
        
        if(key == 13)
        {
            
            
            muestraDesc(1);
            
            can=document.getElementById('cantidad2');
        
            can.value="1";
            can.focus();
            can.setSelectionRange(0,1);
            return false;
        } 
        
        if(!isNaN(obj.value))
            return false;   

        if(obj.value.length >= 3){
            var url="ajax/buscaProductos.php?clave="+obj.value+"&p="+idPedido;
            //alert(url);
            
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
            filant=-1;
            
            for(var i=1;i<aux.length;i++)
            {
                var ax=aux[i].split('~');
                
                op[i]=document.createElement("DIV");
                op[i].id="lista_"+(i-1);
                op[i].innerHTML=ax[1]/*+"<span style='display:none'>"+ax[0]+"</span>"*/;
                op[i].className="objetoLista";
                op[i].onclick=function(){ocultaBuscador(this);}
                
                //op[i].onkeypress=function(){alert('hola');}
                
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
        filant=-1;
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
        
        
    }  
    
    function validaKey(eve, f)
    {
        key=(eve.which) ? eve.which : eve.keyCode;
        
        if(key == 13)
            agregaFila(f);            
    }
    
    function cancelaPaleta(){
    	var obj=document.getElementById('paleta_Colores');
        	
       	obj.style.display="none";
       	
       	var busc=document.getElementById('buscadorLabel');
        var can=document.getElementById('cantidad2');	
        
        busc.value="";
        can.value="";
        
        busc.focus();
    }

    function agregaFila(f){
    //validamos que no sea devolución completa
        if(es_dev_completa==1){
            alert("No se pueden agregar más productos porque se ha seleccionado una devolución completa!!!");
            $("#cantidad2").val("");
            $("#buscadorLabel").val("");
            return false;
        }
        filant=-1;
        var busc=document.getElementById('buscadorLabel');
        var aux=busc.value.split(' | ');
        var busqueda=aux[0];
        var id_p=document.getElementById('id_ped').value;
        var can=document.getElementById('cantidad2');
        var tabla=document.getElementById('listaProductos');
    //
         //Buscamos si existe uno igual
        trs=tabla.getElementsByTagName('tr');
        var cant_anterior=0;
        var eliminar_fila='';
        for(i=1;i<trs.length;i++){
            tds=trs[i].getElementsByTagName('td');
            //alert(aux[2]);
            if(tds[1].innerHTML == busqueda /*&& sig == tds[9].valor */&& aux[2] != '18000'){
               //alert('Repetido');
                cant_anterior=parseInt(tds[3].innerHTML);
                //alert(cant_anterior);
               //$(trs[i]).remove();
               eliminar_fila=trs[i];
                break;
            }    
        }//fin de for i
        var url=ajaxR("ajax/verificaDevolucionProducto.php?cant="+(parseInt(can.value)+cant_anterior)+"&clave="+busqueda+"&id_pedido="+id_p);
        //alert("Respuesta: "+url);
        if(url=='no'){
            alert('la cantidad de devolución de este producto es mayor a la cantidad vendida!!!');
            $("#cantidad2").select();
            return false;
        }
    //asignamos la nueva cantidad
        can.value=(parseInt(can.value)+cant_anterior);
    //eliminamos la fila si se cumple las condiciones
        if(eliminar_fila!=''){
            $(eliminar_fila).remove();
        }
        
        if(busc.value.length == 0){
            alert('Debe introducir un valor a agregar');
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
        
        if(can.value.length == 0){
            alert('Debe introducir una cantidad a agregar');
            can.focus();
            can.setSelectionRange(0, can.value.length);
            return false;
        }
       
        //alert("cant_anterior:"+(parseInt(can.value)+cant_anterior));
        //url="ajax/buscaProdOrCoId.php?val="+busqueda+"&can="+can.value+"&can2="+can.value;
        url="ajax/buscaProductosParaDevolver.php?id_pedido="+id_p+"&cantidad="+can.value+"&orden_lista="+busqueda;
        
       //alert(url);//return false;
       // url="ajax/extraerDatosProducto.php?"
        var res=ajaxR(url);
//alert(res);
        var aux=res.split('|');
        if(aux[0] != 'exito'){
    //alert(res);
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
    //asignamos porcentaje de descuento
        document.getElementById("porc_desc").value=aux[8];
        //alert(document.getElementById("porc_desc").value);
//alert(aux[8]);
        //alert(res);
//alert('aux[5]: '+aux[5]);
		//Validamos si es regalo y no supera el 5%
		/*if(sig == 1){
			var precio=parseFloat(aux[5]);
			
			//alert(precio+' - '+totalVenta);
			if(precio > totalVenta*0.05)
			{
				alert("El precio del producto no debe ser mayor al 5% de la venta.");
				return false;
			}
		}*/

        
        //Vemos si es paleta de colores
        if(aux[11] == '1'){
        	var obj=document.getElementById('paleta_Colores');
        	obj.style.display="block";
        	document.getElementById('labelProductoPaleta').innerHTML=aux[3];
        	document.getElementById('cantidadProductoPaleta').innerHTML=can.value;
        	
        	var tabla=document.getElementById('contenidoPaleta');
        	var htm="";
        	
        	var url="ajax/getColores.php?id_producto="+aux[1];
        	var res=ajaxR(url);
        	var cad=res.split('|');
        	if(cad[0] != 'exito'){
        		alert("Erro\n"+res);
        		return false;
        	}
        	
        	document.getElementById('cantidadPaleta').value=cad.length-1;
        	
        	for(i=1;i<cad.length;i+=2){
        		var ca=cad[i].split('~');
        		htm+="<tr>";
        		
        		htm+='<td>'+ca[0]+'<input type="hidden" id="productoPaleta'+i+'" value="'+ca[1]+'"></td>';
        		
        		htm+='<td><input class="btn_pal" maxlength="4" type="text" id="cantidadPaleta'+i+'" value="" name="cantidadPaleta'+i+'" onkeypress="return validarNumero(event, 0, \'cantidadPaleta'+i+'\')" tabindex="'+i+'" onkeyup="enterNext(this, event)"></td>';
        		

				if((i+1) < cad.length)
				{
					var ca=cad[i+1].split('~');
				
					htm+='<td>'+ca[0]+'<input type="hidden" id="productoPaleta'+(i+1)+'" value="'+ca[1]+'"></td>';
        		
	        		htm+='<td><input class="btn_pal" maxlength="4" type="text" id="cantidadPaleta'+(i+1)+'" value="" name="cantidadPaleta'+(i+1)+'" onkeypress="return validarNumero(event, 0, \'cantidadPaleta'+i+'\')" tabindex="'+(i+1)+'" onkeyup="enterNext(this, event)"></td>';
					
				}

        		htm+="</tr>";
        	}
        	
        	tabla.innerHTML=htm;

			document.getElementById("cantidadPaleta1").focus();
        	
        	return false;
        }//fin de paleta
    //Buscamos si existe uno igual
        trs=tabla.getElementsByTagName('tr');
        var can2=-1;
        for(i=1;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            //alert(aux[2]);
            if(tds[0].innerHTML == aux[2] && sig == tds[9].valor && aux[2] != '18000'){
               //alert('Repetido');
                can2=parseInt(tds[2].innerHTML)+parseInt(can.value);
                //alert(can2);
                eliminarItem(tds[0]);
                break;
            }    
        }
        if(can2 != -1){
//alert('aqui llega');
            var url="ajax/buscaProdOrCoId.php?val="+busqueda+"&can="+can2+"&can2="+can2;
            
            var res=ajaxR(url);
//alert(res);
            var aux=res.split('|');
            
            if(aux[0] != 'exito'){
                alert(res);
                busc.focus();
                busc.setSelectionRange(0, busc.value.length);
                return false;
            }
        }
        else
            can2=can.value;
        
        
        if(aux[1] == '1808'){
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
//cambbios oscar
    //capturamos id de producto
        var id_prod_selec=aux[1];
        /*var verif;
    //verificamos minimo de venta    
        url2='ajax/verificaMinimo.php?id_pr='+id_prod_selec+"&id_ped="+idPedido+"&t_v=2";
        verif=ajaxR(url2);
        if(can2>verif){
            alert('No puede regresar mas productos de los comprados...\nVerifique sus cantidades!!!');
            document.getElementById('cantidad2').select();
            return false;
        }*/
    
        var newRow=tabla.insertRow(1);
        newRow.className="move";
        
        if(sig==1){
            aux[4]="$0";
            aux[6]="$0";
            aux[5]=0;
            aux[7]=0;
        }
    //id del producto
        var newCell=newRow.insertCell(0);
        
        newCell.innerHTML=aux[1];
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
    //Orden de lista
        var newCell=newRow.insertCell(1);
        newCell.innerHTML=aux[2];
        newCell.align="center";
        
    //Descripcion
        var newCell=newRow.insertCell(2);
        newCell.innerHTML=aux[3];
        newCell.align="left";
        
    //Cantidad
        var newCell=newRow.insertCell(3);
        newCell.innerHTML=can2;
        newCell.align="right";
        
    //Precio formato
        var newCell=newRow.insertCell(4);
        newCell.innerHTML=aux[4];
        newCell.align="right";
        
     //Descuento porcentual
        var newCell=newRow.insertCell(5);
        newCell.innerHTML=aux[5];
        newCell.align="right";
        
    //monto total
        var newCell=newRow.insertCell(6);
        newCell.innerHTML=aux[6];
        newCell.align="right";
        
        //Eliminar        
        var newCell=newRow.insertCell(7);
        newCell.innerHTML='<a href="javascript:void(0)" onclick="eliminarItem(this)" class="eliminar"> <span>eliminar</span></a>';
        newCell.align="center";
        
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
         /*  if(tds[10].valor == familiaAct)
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
           }*/       
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
        
        //alert('fin');
        
    }



	function enterNext(obj, eve)
    {
		key=(eve.which) ? eve.which : eve.keyCode;
        
		if(key == 13)
		{
		
			var aux=obj.id.replace("cantidadPaleta","");
			aux=parseInt(aux);
			
			if(document.getElementById("cantidadPaleta"+(aux+1)))
				document.getElementById("cantidadPaleta"+(aux+1)).focus();
			else	
				document.getElementById("cantidadPaleta99").focus();
		}
	}
    
    
    function calculaTotalProd()
    {
        var total=0;
        var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
        
        var tabla=document.getElementById('listaProductos');
        trs=tabla.getElementsByTagName('tr');
        for(i=1;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
//alert(tds[6].innerHTML);
            total+=isNaN(parseFloat(tds[6].innerHTML))?0:parseFloat(tds[6].innerHTML);
            
        }
        
        //alert(total);
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
        
        totalVenta=(total);
        document.getElementById('total').value=Mascara("$#,##.##", totalVenta);
        //alert($("#total").val());
    }
    
    function redond(val, ndec){
        return(Math.round(eval(val)*Math.pow(10,ndec))/Math.pow(10,ndec),2);  
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

			existeRegalo++;
                
            clearInterval(id_ver);
        }
        if(aux[0] == 'NO')
        {
            alert("La peticion no ha sido autorizada");
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

	function eliminarItem (item){
		var tds=$(item).parents("tr").children("td");
		$(item).parents ("tr.move").first ().remove ();
        calculaTotalProd();
    }//fin de function eliminaItem

      function bloqueaPantalla () {
          if ($("#es_regalo").prop('checked'))
                $("#divEspera").css ("display", "block");
      }

	function guardarVenta (){
//alert('entra enn venta');
        var es_apartado=$("#es_apartado").val();
		var retval = false; 
        //alert('vta');             
		$("#menu_cerrar").prop ("disabled", true);
          
		
		var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
		var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
		var id_pedido = $("#id_pedido").val ();
		var datos = "nitems=" + $("#listaProductos tr.move").length;
//implementacion de correo y facebook Oscar(29-10-2017)
    //recolectamos información del cliente 
        var corr=$("#co").val();
        var fac=$("#fa").val();
        if(corr==""){
            corr='-';
        }
        if(fac==""){
            corr='-';
        }
        
	//extrameos detalle que nos interesan para la devolución
		var tabla=document.getElementById('listaProductos');
		trs=tabla.getElementsByTagName('tr');
		for(i=0;i<trs.length-1;i++){
			tds=trs[i+1].getElementsByTagName('td');
            
			//total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);tds[11].innerHTML
            datos +="&idp"+i+"="+tds[0].innerHTML+"&can"+i+"="+tds[3].innerHTML+"&prec"+i+"="+tds[4].innerHTML
            +"&desc"+i+"="+tds[5].innerHTML+"$montoDev"+i+"="+tds[6].innerHTML;
		}
         
		//var url= "ajax/guardaNuevaVenta.php?idp=" + id_pedido  + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos + "&totalPed="+totalVenta;
    //validamos password  

		var url= "ajax/registraDevolucion.php?idp="+id_pedido+"&pe="+es_pedido+"&pa="+es_paquete+"&"+datos+"&totalDev="+totalVenta+
        "&porcDesc="+document.getElementById("porc_desc").value;

    /*Implemetación Oscar 06.03.2019 para que las devoluciones completas si se impriman*/
         url+="&es_completa="+es_dev_completa;
    /*Fin de cambio Oscar 06.03.2019*/
/**/    
        url+='&extra=*es_apart='+es_apartado+'*id_ped='+$("#id_pedido_apartado").val();
        /*concatenamos el % de descuento de la nota original si esta esta pagada

        /*concatenamos el % de descuento de la nota original si esta esta pagada*/
        if(document.getElementById('descuento_en_nota')){
            url+="*dsc="+document.getElementById('descuento_en_nota').value;
        }
    /*Impementación Oscar 01.03.2019 para que si es cambio/devolución de venta por mayoreo redireccione a la pantalla de ventas por mayoreo*/
        if($("#tipo_venta_original").val()!=0 && $("#tipo_venta_original").val()!=null){
            url+="*tv=1"+"*aWRfcHJlY2lv="+$("#tipo_venta_original").val();
        }
    /*Fin de cambio Oscar 01.03.0219*/

/*alert(url);return false;*/
		
        var res=ajaxR(url);
/*alert(res); 
return false;*/
		aux=res.split('|');
		if(aux[0] == 'ok'){
			var ax=aux[1].split(':');
			$("#id_pedido").val(ax[1]);
			retval = true;
/**/
        
/**/
    //validamos que no sea devolución completa, si lo es ya no se reimprime el ticket
            if(es_dev_completa==0 && es_apartado==0){
                url="ajax/ticket-php-head-reimpresion.php?id_ped="+id_pedido+"&id_dev="+aux[2];
                var reimp=ajaxR(url);
            }  

            location.href=aux[3];            
            return false;
		}
		else{
			alert(aux);
			retval = false;
		}
			
//alert('fin de generaVe');	
		return retval;
	}

    function verificaPass(){
        activaMayoreo();
    }

    function actualizaOfertas (){
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
        }else{
            alert("El producto seleccionado no existe o la cantidad solicitada es incorrecta.");
        }

        $("#producto, #cantidad").val ("");
        $("#producto").focus ();
    }

    function buscarProducto () {
        //alert('here');
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
         
         //$("#es_regalo").prop ("disabled", true);
         //$("#img_regalo").prop ("disabled", true);
         

		if(existeRegalo > 0)
		{
			alert ("Imposible continuar.\nYa existe un regalo previamente seleccionado.");
            return false;
		}


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
    }, 50);


    $(document).ready(function() {

        // Bloquear evento goBack () 
        changeHashOnLoad();
        
        <?php if (!isset($folio)) { ?>
            cargaNuevoFolio ();
        <?php } ?>

            $("#es_pedido").on ("click", function () {
                if ($("#es_pedido").prop('checked')) {
                    //alert('OK2');
                    // Desactivar el regalo 
                    // cambiaRegalo ();  
                    $("#es_regalo").prop ("checked", false);
                    $("#es_paquete").prop ("checked", false);
                    $("#es_paquete").prop ("disabled", true);
                    $("#es_regalo").prop ("disabled", true);
                    $("#menu_cerrar span").html ("Generar Nota");
                    $("#menu_cerrar").removeClass ("nota").addClass ("pedido");
                } else {
                    
                    //alert('OK1');
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

            $("#es_paquete, #es_regalo").on ("click", function () {
                $("#producto").focus ();
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

           
            $("#menu_cerrar, #cerrar").on ("click", function (){
                    if(!confirm("Esta operación regresará los productos al inventario, ¿Desea continuar?")) {
                //regresamos referencia a un valor vacío
                    sa=0;
                    return false;
                    }
                


                 if (1!=1)//!$("#listaProductos tr.move").length
                 {
                     // Verificar la selección de productos en lista 
                     alert("Imposible continuar.\nNo ha seleccionado productos para la venta.");
                     $("#buscadorLabel").focus();
                     return false; 
                 }
                 else
                 {
                     if (!guardarVenta ())
                     {
                         // No se pudo guardar la venta 
                         //alert ("Error mientras se procesaba la venta.\nActualice la pantalla e intente nuevamente.");
                         //return false;
                     }
                     else
                     {
                         if ($("#menu_cerrar").hasClass ("pedido"))
                         {
                            // Si se trata de un pedido, imprimir ticket y bloquear la opción pedido 
                            // Tambien habilita el botón para cerrar la venta 
                             
                             agregarProducto = function () { alert ("Función deshabilitada."); return false; };
                             //Cambiar por ajax  
                             //window.open ("index.php?scr=ticket&idp=" + $("#id_pedido").val (), "_blank"); 
                             alert('ok');
                             $.ajax({
				                    async: false,
				                    type: 'GET',
				                    url: "../index.php?scr=ticket&idp=" + $("#id_pedido").val () + "&noImp=1"
				               }).done (function (data) {
                                    alert(data);
				                   return true;
				               });
				               
				             $.ajax({
                                    async: false,
                                    type: 'GET',
                                    url: "../index.php?scr=ticket&idp=" + $("#id_pedido").val () + "&noImp=2"
                               }).done (function (data) {
                                    alert(data);
                                   return true;
                               });  
                             
                             $("#menu_cerrar span").html ("Cerrar Venta");
                             $("#menu_cerrar").removeClass ("pedido").addClass ("nota");
                             
                             //alert('OK');
                             $("#es_pedido").prop ("checked", false);
                             $("#es_pedido").prop ("disabled", true);
                             //cargaNuevoFolio();
                             //$("#es_pedido").prop ("checked", true);
                             
                             var url="ajax/nuevoFolio.php?tipo=V"+ "&idp=" + $("#id_pedido").val ();
                             
                             var res=ajaxR(url);
                             
                             if (coincidencias = res.match (/^ok\|folio\:(\w+)$/i)) {
                              
                                  
                                  $("#folio_venta").val (coincidencias[1]);
                                  $("#folio_pedido").val ("");
                            
                          }
                             
                             
                             return false;
                         }
                         else
                         {
                             if ($("#menu_cerrar").attr ("href").match (/&idp=\d+/i)) {
                                 $("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href").replace (/&idp=\d+/i, "&idp=" + $("#id_pedido").val ()));
                             }
                             else {
                            //  
                                var ventaTipo=document.getElementById('tipo_venta_original').value;//sacamos el tipo de venta
                                $("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href") + "&idp=" + $("#id_pedido").val ()+'&tv='+ventaTipo);
                             }

                             if ($(this).attr ("id").match (/^cerrar$/i))
                                 location.href = $("#menu_cerrar").attr ("href");
                             
                             return true;
                         }
                     }
                 }
             });

            $("#es_regalo").prop ("checked", false);
            $("#producto").focus ();
                
        });

  </script>