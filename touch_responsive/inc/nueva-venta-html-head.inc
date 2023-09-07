<script type="text/javascript">

/* <![CDATA[ */      
    var totalVenta=0;
    var descuentoAplicado=0;
    var aplicaDescuento=0;
    var existeRegalo=0;
    var sa=0;
    var autorizado=0;
    var totalFinal=0;
    var referenciaPaquete=0;
//implementado pos oscar 26-12-2017
    var pqt=0;
//fin de cambio
    var inventario_validado=0;//variable implementada por Oscar 13.08.2018
    var verifica_cambio_desc=0;
/**/
    var paqte=0;
/**/
/*variables implementadas por Oscar para enlistar el paquete*/
    arreglo_paquete= new Array(); 
    var enlistando_paquete=0;
    var lista_precios_actual=0;
/*Fin de cambio Oscar 18.03.2019*/

/*implementación Oscar 09.11.2018*/
    function accion_tecla(e,flag){
        var tca=e.keyCode;//sacamos el valor de la tecla presionada
        if(flag==1){
            if(tca==13){//si es intro
                $("#bot_aut_1").click();
            }
        }
    }
/*fin de cambio Oscar 09.11.2018*/

/*Implementación Oscar 04.03.2019 para llenar grid de ventas con paquetes*/
function  enlista_paquete(arreglo){
    paqte=0;
    enlistando_paquete=1;//marcamos que se esta enlistando paquete
/**
    var url="ajax/valida_precios_paquete.php?&arreglo="+arreglo_paquete;
    if(document.getElementById("id_lista_mayoreo")){
        url+="&id_lista_precio="+$("#id_lista_mayoreo").val();
    }
    /*alert();
    var aux_lista_pqte=ajaxR(url);
    alert("aux: "aux_lista_pqte);return false;
/**/
    if(pqt==0){// si no hay paquete activado
        activa_paq();//activamos el paquete
    }
    /*var aux_dt=arreglo.split("*");//separamos por producto*/
    var aux_arr="";
    for(var i=0;i<=arreglo_paquete.length-1;i++){
        if(i<arreglo_paquete.length-1){
            aux_arr=arreglo_paquete[i].split("~");//separamos los datos del producto
        //mandamos agregar la fila
            //alert(null+'|'+aux_arr[1]+'|'+-1+'|'+aux_arr[0]);
            //arreglo_paquete.shift();//quitamos la posición
            agregaFila(null,aux_arr[1],-1,aux_arr[0]);        
        }
    }//fin de for i
    //alert("tam: "+arreglo_paquete.length);
    if(arreglo_paquete.length<=0){
        enlistando_paquete=0;//marcamos que se termino de enlistar paquete
    }
}
/*Fin de cambio Oscar 04.03.2019*/

/*implementación de Oscar 03.09.2018 para cambios en apartados*/
  function carga_ped_apartado(id_venta,transfer){/*se qui ,flag deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores*/
    //alert('aqui'+id_venta);
    var url_tmp="ajax/buscaProdOrCoId.php?";
    if(id_venta!=0){
        url_tmp+="id_pedido="+id_venta;
    }else if(transfer!=0){
        url_tmp+="id_transferencia="+transfer;
    }
    //alert(url_tmp);
    var dats=ajaxR(url_tmp);
    //alert(dats);return false;
    var aux_dt=dats.split("|");//separamos por producto
    var aux_arr="";
    for(var i=1;i<=aux_dt.length-1;i++){
        if(i<aux_dt.length-1){
            aux_arr=aux_dt[i].split("~");//separamos los datos del producto
        //mandamos agregar la fila
        //    alert(null+'|'+aux_arr[1]+'|'+-1+'|'+aux_arr[0]);
            agregaFila(null,aux_arr[1],-1,aux_arr[0]);
        }else{
            if(parseInt(aux_dt[i])>0){
                document.getElementById("porcGeneral").value=Math.round(aux_dt[i]);//asignamos el porcentaje de descuento
                calculaTotalVenta(1);//recalculamos el total/*,1 deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores*/
            }
        }
    }//fin de for i
  }
/*fin de cambio 03.09.2018*/

/*implementación de Oscar 13.08.2018 para validar inventarios e insertar temporal de productos tomados de exhibición*/
    function eliminarTemporalExhib(id){
        var res=ajaxR("ajax/verificaInventario.php?flag=eliminar_tmp_exhib&id_reg="+id);
        var aux=res.split("|");
        if(aux[0]!='ok'){
            alert("Error al eliminar el temporal de la exhibición!!!\n"+res);
            return false;
        }
    }

    function insertaMovExhibTemporal(id,flag){
    //extraemos los valores de las cajas de texto de la ventana emergente
        var alm1=0,alm2=0,pass_enc,existe=0;
    /*implementación Oscar 25.09.2018*/
        var cant_alm=$("#cantidades_capturadas").val().split("|");
            alm1=$("#cant_alm_1").val();//almacen principal
            if(alm1==''||alm1==null){
                alm1=0;
            }
            alm2=$("#cant_alm_2").val();//almacen de exhibición
            if(alm2==''||alm2==null){
                alm2=0;
            }
    /*fin de cambio*/
    
    //si no se pide nada
        if(alm1==0&&alm2==0){
            $("#emergente_1").css("display","none");//ocultamos emergente
            $("#cantidad2").val("");
            $("#buscadorLabel").select();
            return false;
        }
        pass_enc=$("#pss_encargado").val();
        if(alm1!=0&&alm1!=''||alm2!=0&&alm2!=''){
            if(pass_enc==''){
                alert("El encargado debe autorizar con su contraseña!!!");
                $("#pss_encargado").focus();//enfocamos la caja de contraseña
                return false;
            }
        //verificamos la contraseña del encargado 
            var res=ajaxR("ajax/verificaInventario.php?clave="+pass_enc+"&id_pr="+id+"&cant="+alm2+"&alm_princ="+alm1+"&alm_exh="+alm2);
//alert("ajax/verificaInventario.php?clave="+pass_enc+"&id_pr="+id+"&cant="+alm2+"&alm_princ="+alm1+"&alm_exh="+alm2);
            var aux=res.split("|");            
            if(aux[0]!='ok'){
                alert("Error!!!\n\n"+res);
                return false;
            }
        //
            inventario_validado=1;
            if(aux[1]!='ok'){
                alert(aux[1]);
                $("#pss_encargado").select();
                inventario_validado=0;
                return false;
            }
            if(aux[2]!='ok'){
                if(aux[2]=='sinTemporal'){
                    agregaFila(null,parseInt(alm1));
                    $("#emergente_1").css("display","none");
                    return false;
                }
                alert("Error!!!\n"+aux[1]);
                inventario_validado=0;
                return false;
            }
        /*implementación de Oscar para pquetes*/
            if(flag=='pqt'){
                var ax_tmp=id.split("|");
                for(var i=0;i<ax_tmp;i++){

                }
            }
        /*fin del cambio*/

    /*implementación Oscar 25.09.2018 para almacenar las cantidades pedidas por almacen*/
            existe=0;//esta variable esta declarada al inicio de la función
            var arr_ax="",str_final='';
            if(cant_alm!=''){
                //alert(cant_alm);
                for(var i=1;i<cant_alm.length;i++){
                    arr_ax=cant_alm[i].split('~');
                    //alert(arr_ax[0]+"\n"+id);
                    if(arr_ax[0]==id){
                        //alert("si existe");
                        cant_alm[i]=cant_alm[i].replace(cant_alm[i],id+'~'+alm1+'~'+alm2);
                        //alert(cant_alm[i]);
                        existe=1;
                    }
                    str_final+='|'+cant_alm[i];
                }
            }
            if(existe==0){
                str_final+='|'+id+'~'+alm1+'~'+alm2;
            }
        //reemplazamos los valores en variable oculta
            $("#cantidades_capturadas").val(str_final);

    /*fin de cambio 25.09.2018*/
    //alert('sale:'+parseInt(alm1)+parseInt(alm2));
            agregaFila(null,parseInt(alm1)+parseInt(alm2),aux[3]);//madamos llamar function agregar fila y le enviamos la suma de los almacenes prim y exhib
            $("#emergente_1").css("display","none");
            return false;
        }
    }
/*Fin de cambio 13.08.2018*/
      
    var id_mov_tmp=0;//variable implementada el 03.05.2018 para referencia de cabecera del movimietno temporal (Oscar)
//función que recarga oantalla y le quita el saldo a favor
    function regresaEfectivo(flag){
    //si se llama la función desde el botón Regresar Efectivo
        if(flag==null){
            var bot='<input type="button" value="Regresar efectivo" onclick="regresaEfectivo(1)" style="padding:15px;width:150px;left:15%;;position:absolute;">';
            bot+='<input type="button" value="Cancelar" onclick="regresaEfectivo(2);" style="padding:15px;width:150px;right:15%;;position:absolute;">';
            $("#emergente_1").html('<br><br><br><br><center><p style="color:white;font-size:40px;">¿Realmente desea regresar el dinero en efectivo al cliente?</p>'+bot+'</center>');
            $('#emergente_1').css('display','block');
        }
    //si se llama a la función desde el botón de regresar efectivo en la emergente
        if(flag==1){
        //enviamos la impresión del ticket
/*Implementación Oscar 02.03.2019 para el status de la devolucion cuando se devuelve efectivo*/
            var imp_dev=ajaxR("ajax/imprimeDev.php?monto_devolucion="+$("#saldoAFavor").val()+"&id_pedido_original="+$("#id_de_pedido_original").val()+"&flag_tkt=devuelve_efectivo"+
                "&ids_devoluciones="+$("#id_de_devoluciones").val());
           //alert(imp_dev);return true;
            location.href="index.php?";//scr=nueva-venta
/*Fin de Cambio Oscar 02.03.2019*/
        }
    //si se llama a la función desde el botón de cancelar
        if(flag==2){
            $("#emergente_1").css("display","none");
        }
        return false;
    }

/*función encargada de quitar los descuentos por producto *************Deshabilitado por Oscar 09.11.2018
    function resetearMontos(){
        var tabla=document.getElementById('listaProductos');
        trs=tabla.getElementsByTagName('tr');
        for(i=0;i<trs.length-1;i++){
            tds=trs[i+1].getElementsByTagName('td');
        //regresamos monto original
            var objIn=tds[13].getElementsByTagName('input');
            tds[5].innerHTML=objIn[0].value;
        //ponemos en cero el descuento
            objIn1=tds[12].getElementsByTagName('input');
            objIn1[0].value='';
        }
    }
*/
//implementado por Oscar 26-12-2017
    function activa_paq(){
        if(pqt==1){
            $("#paq1").css("background","transparent");
            $("#paq1").css("color","black");
            pqt=0;
            document.getElementById("porcGeneral").value='';
            document.getElementById("porcGeneral").disabled=false;
            document.getElementById("descGeneral").disabled=false;
            calculaTotalVenta();
            return false;
        }
        var ur='ajax/consultaDescuentoSucursal.php';
        var resp=ajaxR(ur);
        var axP=resp.split("|");
        if(axP[0]!='ok'){
            alert('Ocurrió un error al activar el paquete\nActualice la pantalla y vuela a intentar!!!');
            return false
        }
        $("#paq1").css("background","#91D617");
        $("#paq1").css("color","white");
        document.getElementById("porcGeneral").value=axP[1]*100;
        document.getElementById("porcGeneral").disabled=true;
        document.getElementById("descGeneral").disabled=true;
        //resetearMontos();
        calculaTotalVenta(1);
        pqt=1;
    }
//fin de cambio

/* deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores*
//implementado por Oscar(28-10-2017)
    function cambiaMonto(pos){
    //si es paquete no hacemos acción
        if(pqt==1){
            alert("El pedido tiene activo el paquete y no se pueden agregar descuentos extras...!!!");
            var tds=$(pos).parents("tr").children("td");
            var montOrig=tds[12].getElementsByTagName('input');
            montOrig[0].value="";
            return false;
        }
    //quitamos descuentos directos
        document.getElementById("porcGeneral").value='';
        document.getElementById("descGeneral").value='';
        
        var tablaT=$('#listaProductos').length;
        var tds=$(pos).parents("tr").children("td");
        if(!tds[13]){
        }else{
            var montOrig=tds[13].getElementsByTagName('input');
            var montoAct=montOrig[0].value;
            var aux=montoAct.split('$');
            var aux2=aux[1].split(",");
            montoAct="";
            for(var i=0;i<aux2.length;i++){         
                montoAct+=""+aux2[i];   
            }
            var montOrig=tds[13].getElementsByTagName('input');
            var montoAct=montOrig[0].value;
            var aux=montoAct.split('$');
            var aux2=aux[1].split(",");
            montoAct="";
            for(var i=0;i<aux2.length;i++){         
                montoAct+=""+aux2[i];   
            }
        }
    //alert(montoAct);
        if(tds[12]){
            var objIn=tds[12].getElementsByTagName('input');   
            var descuentoProducto=isNaN(parseFloat(objIn[0].value))?0:parseFloat(objIn[0].value); 
            //alert('monto: '+montoAct+"|descuento: "+descuentoProducto);
            var id_prod=tds[5].innerHTML="$"+parseFloat(montoAct-descuentoProducto);
            calculaTotalVenta();
     //alert('first');
        }
        return false;
    }*/


    function habilitaDesc(flag,permiso){
        if(flag==1||flag==2){
            //resetearMontos();
            if((permiso==0||permiso==null) && verifica_cambio_desc==0 && /*$("#id_de_apartado").val()!='' 
                && $("#id_de_apartado").val()!=null && */ $("#porcGeneral").val()!=''){
                if(!confirm("realmente desea cambiar el descuento original del pedido????")){
                    return false;
                }else{
                    verifica_cambio_desc=1;
                }
            }
        }
        var objValido;
        var objNulo;
            if(flag==2){
                //alert('here');
                objValido=document.getElementById('descGeneral');
                objNulo=document.getElementById('porcGeneral');
            }else if(flag==1){
                objValido=document.getElementById('porcGeneral');
                objNulo=document.getElementById('descGeneral');
            }
            //objValido.disabled=false;
            //objNulo.disabled=true;
            if(flag==1||flag==2 && pqt==0){
                objNulo.value="";   
            }
            calculaTotalVenta();
            verifica_cambio_desc=0;//reseteamos la variable para que salga el mensaje
    }
//implementado por Oscar(29-10-2017)
        var verifica_cambio_desc=0;
        function calculaTotalVenta(flag){
        //alert("calcula Venta:"+flag);
            if(flag==1||flag==2){
                //resetearMontos();
            }

        // Sumar el total 
            var objNulo;
            if(flag==1){
                //alert ('vta');
                objNulo=document.getElementById('descGeneral');
                objNulo.value="";
            }else if(flag==2 && pqt==0){
                //alert('vta2');
                objNulo=document.getElementById('porcGeneral');
                objNulo.value="";
            }
        //objNulo.disabled=true;
            var tot = 0.0;
            var tabla1=document.getElementById('listaProductos');
            var filas=tabla1.getElementsByTagName('tr');
            for(i=0;i<filas.length-1;i++){
                var monto="";
                var celdas=filas[i+1].getElementsByTagName('td');
                var aux=celdas[5].innerHTML.split('$');
                var aux2=aux[1].split(",");            
                
                //alert("valor de celda: "+celdas[5].innerHTML+"celda_oculta_monto"+celdas[8].valor);
                for(var j=0;j<aux2.length;j++){
                    monto+=""+aux2[j];   
                    //alert("valor de celda: "+aux2[j]);
                    //monto=parseInt(celdas[8].valor);
                }
            //sacamos los descuentos
                /*var desc=celdas[12].getElementsByTagName('input');
                var desc_prod=0;
                if(desc[0].value!=""){
                    desc_prod=parseFloat(desc[0].value);
                }*/
                tot+=parseFloat(monto); 
        //asignamos el total real (sumamos el monto de descuento)
            totalVenta=tot;/*+desc_prod*/
                //alert(tot);
            }//fin de for i
//           
        //asignamos el total menos descuentos
            var auxPorc=(document.getElementById('porcGeneral').value)/100;
            var descPorc=tot*auxPorc;
            if(descPorc!=0){
                var comprobar_no_nums=isNaN(descPorc);//comprobamos que no se trate de letras
                if(comprobar_no_nums==false){
                    tot-=descPorc; 
                }
            }

            var descDir=document.getElementById('descGeneral').value;
            if(descDir!=0){
                var comprobar_no_nums=isNaN(descDir);//comprobamos que no se trate de letras
                if(comprobar_no_nums==false){
                    tot-=descDir;
                }
            }
            totalFinal=tot;

            var verificar_montos=verificaTotales();
//   
            $("#total").val("$"+Math.ceil(tot));//moneyFormat
   
            if(Math.ceil(verificar_montos)==Math.ceil(tot)){
 //               $("#total").val("$"+Math.ceil(tot));//moneyFormat
            }else{
                /*alert("Hay una diferencia en los montos!!! \nProceso_1:"+tot+"\nValidacion:"+verificar_montos);
                var msg='<br><br><p align="center" align="center" style="font-size:30px;color:white;">Ah ocurrido un error en el monto de esta venta!!!</p>';
                msg+='<p align="center" style="font-size:25px;color:white;">Monto correcto:  $ '+verificar_montos+'</p>';
                msg+='<p align="center" style="font-size:25px;color:white;">Monto incorrecto:  $ '+Math.ceil(tot)+'</p>';
                msg+='<p align="center" style="font-size:25px;color:white;">Tome una Fotografía y mandela al encargado de sistemas<br>Pida al encargado que ingrese su contraseña</p>';
                msg+='<p align="center"><input type="text" id="passWord_1" onkeyDown="cambiar(this,event,\'passWord\');" style="padding:10px;" name="passWord"></p>';
                msg+='<input type="hidden" id="passWord" value="">';
                msg+='<p align="center"><button onclick="verificaAutorizacion(\'mantener\');" style="margin:20px;">Continuar<br>El monto corrrecto aparecerá <br>al imprimir el ticket</button>';
                msg+='<button style="margin:20px;" onclick="verificaAutorizacion(\'recargar\');">Continuar<br>Volver a cargar <br></button></p>';
                document.getElementById('passWord').setAttribute('autocomplete','off');
                $("#cont_emerg").html(msg);//contEmerge
                $("#emergente_1").css("display","block");//hacemos visible la ventana emergente
                $("#total").val("$"+Math.ceil(tot));//moneyFormat*/
               /*var err_log="Monto correcto: $ "+verificar_montos+"\n"+"Monto incorrecto:  $ "+Math.ceil(tot);
                var insertaError=ajaxR('ajax/guardarErrores.php?msg='+err_log);
                if(insertaError!='ok'){
                    alert("No se pudo guardar el error!!!\n"+insertaError);
                }*/
            }
      	return false;
      }

/*Implementación Oscar 07.11.2018*/
    function verificaTotales(){
        var tot = 0.0;
            var tabla1=document.getElementById('listaProductos');
            var filas=tabla1.getElementsByTagName('tr');
            for(i=0;i<filas.length-1;i++){
                var monto=0;
                var celdas=filas[i+1].getElementsByTagName('td');
                var aux=celdas[5].innerHTML.split('$');
                    monto=monto+parseInt(Math.round((celdas[7].valor)*celdas[2].innerHTML));
            //sacamos los descuentos
                var desc=celdas[12].getElementsByTagName('input');
                var desc_prod=0;
                tot+=parseFloat(monto); 
            }//fin de for i
        //asignamos el total menos descuentos
            var auxPorc=(document.getElementById('porcGeneral').value)/100;
            var descPorc=tot*auxPorc;
            if(descPorc!=0){
                var comprobar_no_nums=isNaN(descPorc);//comprobamos que no se trate de letras
                if(comprobar_no_nums==false){
                    tot-=descPorc; 
                }
            }

            var descDir=document.getElementById('descGeneral').value;
            if(descDir!=0){
                var comprobar_no_nums=isNaN(descDir);//comprobamos que no se trate de letras
                if(comprobar_no_nums==false){
                    tot-=descDir;
                }
            }
        return tot;//regresamos como respuesta el total
    }
/*fin de cambio Oscar 07.11.2018*/

//implementado por Oscar 30-10-2017
    function verificaAutorizacion(flag){//implementación de flag Oscar 08.11.2018
    //sacamos texto tecleado
    //alert(flag);
        var pass=document.getElementById('passWord').value; 
        $.ajax({
        	type:'post',
        	url:'ajax/verificaPassEncargado.php',
        	cache:false,
        	data:{clave:pass},
        	success:function (datos){
        		//alert(datos);
        		if(datos=='ok'){
        /*Cambio Oscar 08.11.2018*/
        			if(flag=='recargar'){   
                        location.reload();//recargamos la página
                        return false;
                    }
                    if(flag=='mantener'){
                        $("#emergente_1").css("display","none");//ocultamos la emergente                             
                        return false;
                    }
        /*Fin de cambuio Oscar 08.11.2018*/

                    var objC=document.getElementById("cerrar");
					if(objC){
						totalVenta=totalFinal;
						autorizado=1;
  				 		objC.click('1');
					}
        		}else{
        			alert('La contraseña es incorrecta!!!');
        			document.getElementById('passWord').select();
        			
        			return false;
        		}
        	}
        });
    }

    function Mascara(mascara, valor){   
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
      
//inserta información de productos en buscador 
    function muestraDesc(val){
        var obj=document.getElementById('buscadorLabel');
         
    //Buscamos si tiene descripcion
        var aux=obj.value;   
        if(aux == ''){
        //alert("es_ aqui");
            return false;
        }
        
        aux=aux.split(' | ');
    //alert(aux);
        if(aux.length == 1){
    /*implementación de paquetes prediseñados Oscar 04.03.2019*/
            var aux1=aux[0].split("paquete");
            if(aux1.length>1){
                var aux2=aux1[1].split("|");
                if(aux2[0]!="" && aux2[0]!=null){
                    paqte=aux2[0];
                //alert(paqte);
                }else{
                    paqte=0;
                }
            }
            setTimeout(muestraDesc_2(aux[0]),300);
    /*Fin de Cambio Oscar 04.03.2019*/
        } 

/*implementación de paquetes prediseñados Oscar 04.03.2019*/
    function muestraDesc_2(valor_et){
            var complemento="&es_paquete="+paqte+"&flag=info";
            //alert("id del paquete: "+complemento);
        
            var res=ajaxR('ajax/buscaProdOrCoId.php?val='+valor_et+"&can=1&can2=1"+complemento);
            //alert(res);
            if(res=='no se encontró el paquete!!!'){
                $("#cantidad2").val('');
                $("#buscadorLabel").select();
                return false;
            }
            //alert(res);   
    /*Fin de cambio Oscar 04.03.2019*/
            var ax=res.split('|');
           //alert(ax);
            $("#resBus").css("display","none");

            if(ax[0] != 'exito'){
                alert('Producto incorrecto, pruebe con uno diferente');
                obj.focus();
                obj.setSelectionRange(0, obj.value.length);
                return false;
            }

            var complemento="";
        /*implementacion OScar 10.10.2019*/
            if(paqte==0){
                complemento=ax[3].split("<br>");
                ax[3]=complemento[0];
            }
        /*fin de CAmbio Oscar 10.10.2019*/

            obj.value=ax[2]+" | "+ax[3];
            //alert(ax[2]+" | "+ax[3]);
            if(ax[10] == 'SI' && val == 1)
            {
                 can=document.getElementById('cantidad2');
                 can.value="1";
                 agregaFila(null);
            }    
        }
    }
/*Fin de Cambio Oscar 04.03.2019*/
  
    var filant=-1;  
      
//Funcionalidad del buscador (por cada teclaque se oprime se manda la búsqueda)
    function activaBuscador(obj, eve){
//implementado por oscar...
    var ventaTipo=document.getElementById('tipo_venta').value;//sacamos el tipo de venta
        
        key=(eve.which) ? eve.which : eve.keyCode;//sacamos el valor de la tecla
        
        //alert(obj.value.indexOf(' '));
        
        if(key == 40 && obj.value.length >= 3){
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
        
        if(key == 38 && obj.value.length >= 3){
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
            
            //alert("intro");
            muestraDesc(1);
            can=document.getElementById('cantidad2');
            
            can.value="1";
            can.focus();
            can.setSelectionRange(0, 1);            
            return false;
        } 
        
        /*Deshabilitado por Oscar 12.11.2018 parq eu también busque numeros
        	if(!isNaN(obj.value))
            	return false;   
        */
        
        
        
        if(obj.value.length >= 3)
        {
            var url="ajax/buscaProductos.php?clave="+obj.value+"&t="+ventaTipo;
        /*Implementación Oscar 03.03.2019 para tomar precio de lista de mayoreo*/
            url+="&id_lista_precio="+$("#id_lista_mayoreo").val();
        /*Fin de Cambio Oscar 03.03.2019*/
            //alert(url);
            
            var res=ajaxR(url);
            
            var aux=res.split('←');
            
            if(aux[0] != 'exito')
            {
                alert("Error: "+res);
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
        //aqui se forman las opciones
            for(var i=1;i<aux.length;i++)
            {
                var ax=aux[i].split('~');
                
                op[i]=document.createElement("DIV");
                op[i].id="lista_"+(i-1);
                op[i].innerHTML=ax[1]/*+"<span style='display:none'>"+ax[0]+"</span>"*/;
                op[i].className="objetoLista";
                op[i].onclick=function(){
                    ocultaBuscador(this);
                }
                
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
        var ventaTipo=document.getElementById('tipo_venta').value;
        filant=-1;
        /*var aux=obj.getElementsByTagName('span');
        
        var aux=aux[0].innerHTML;*/
        
        var aux=obj.innerHTML;
        
        aux=aux.replace("</span>", "");
        aux=aux.replace("</span>", "");
        aux=aux.replace('<span class="txtNegrita">', "");
        aux=aux.replace('<span class="txtVerde">', "");
        aux_1=aux.split("<br>");
        aux=aux_1[0];
        if(ventaTipo==1){
            aux_1=aux.split("<p style");
            aux=aux_1[0]
        }
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
        
        if(key == 13){
            agregaFila(f);            
        }
    }
    
    function cancelaPaleta()
    {
    	var obj=document.getElementById('paleta_Colores');
        	
       	obj.style.display="none";
       	
       	var busc=document.getElementById('buscadorLabel');
        var can=document.getElementById('cantidad2');	
        
        busc.value="";
        can.value="";
        
        busc.focus();
    }
var es_paleta_tmp=0;
    function cierraPaleta()
    {
    	
    	var can=document.getElementById('cantidad2');
    	canFin=isNaN(parseInt(can.value))?0:parseInt(can.value);
    	
    	var can=document.getElementById('cantidadPaleta');
    	
    	can=isNaN(parseInt(can.value))?0:parseInt(can.value);
    	
    	var f=null;
    	canCol=0;
    es_paleta_tmp=1;
    	for(i=1;i<=can;i++)
    	{
    		//document.getElementById('buscadorLabel').value=document.getElementById('productoPaleta'+i).value;
    		var cant=document.getElementById('cantidadPaleta'+i);
    		cant=isNaN(parseInt(cant.value))?0:parseInt(cant.value);
    		canCol+=cant;
    	}
    	
    	
    	if(canCol > canFin)
    	{
    		alert("La cantidad a vender es mayor a la pedida");
    es_paleta_tmp=0;
    		return false;
    	}

		if(canCol<canFin){
    		alert("La cantidad a vender es menor a la pedida");
    es_paleta_tmp=0;
    		return false;
    	}
    	//alert(can);
    	for(cpi=1;cpi<=can;cpi++)
    	{    	

			//alert('Iteracion '+cpi+' de '+can);
	
    		var cant=document.getElementById('cantidadPaleta'+cpi);
    		cant=isNaN(parseInt(cant.value))?0:parseInt(cant.value);
    		
    		if(cant > 0)
    		{
    			document.getElementById('cantidad2').value=cant;
    			document.getElementById('buscadorLabel').value=document.getElementById('productoPaleta'+cpi).value;
    			//alert("valor: "+document.getElementById('productoPaleta'+cpi).value);
                agregaFila(f);
    		}	
    	}
    	
    	var obj=document.getElementById('paleta_Colores');
        obj.style.display="none";
    	
        es_paleta_tmp=0;
    }
var hay_paquete=0;
//agregar fila a tabla del pedido
    function agregaFila(f,cant,temp,id_producto_pedido,lista_normal){//se implementan las variables cant,temp por Oscar 13.08.2018
       // alert('add_fila');
        //alert('FILA: '+f+"CANTIDAD:"+cant+" ID_REG_TMP:"+temp+" id_producto:"+id_producto_pedido);
        var ventaTipo=document.getElementById('tipo_venta').value;//sacamos el tipo de venta
        filant=-1;
        var busc=document.getElementById('buscadorLabel');
        var aux=busc.value.split(' | ');
        var busqueda=aux[0];
    //return false;
        var can=document.getElementById('cantidad2');
        var tabla=document.getElementById('listaProductos');
    
    //cambio para cargar paquetes oscar 03.09.2018
        if(id_producto_pedido!='' && id_producto_pedido!=null){
            busqueda=id_producto_pedido;
        }
    //fin de cambio 03.09.2018

    //cambio para cargar paquetes oscar 03.09.2018
        var url="ajax/buscaProdOrCoId.php?val="+busqueda;
        if(cant!='' && cant!=null){
         //   alert('aqui_1');
            url+="&can="+cant+"&can2="+cant+"&t="+ventaTipo+"&es_paquete="+paqte+"&l_normal="+lista_normal;//
        }else{
           // alert('aqui_2');
            url+="&can="+can.value+"&can2="+can.value+"&t="+ventaTipo+"&l_normal="+lista_normal+"&es_paquete="+paqte;//+"&paquete="+paqte
        }
    /*Implementación Oscar 03.03.2019 para tomar el precio de la lista de precios de mayoreo*/
        if(document.getElementById("id_lista_mayoreo")){
            url+="&id_lista_precio="+$("#id_lista_mayoreo").val();
        }
        //alert(url);
    /*Fin de Cambio oscar 03.03.2019*/

    //fin de cambio 03.09.2018

        if(busc.value.length == 0 && (cant==null || cant=='')){
            alert('Debe introducir un valor a agregar en el buscador');
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
            return false;
        }
        
        if(can.value.length == 0 && (cant==null || cant=='')){
            alert('Debe introducir una cantidad a agregar en la cantidad');
            can.focus();
            can.setSelectionRange(0, can.value.length);
            return false;
        }
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
       //if(parseInt(can.value) > 1 && sig == 1){
            alert('Sólo es permitido regalar un producto');
            can.value="1";
            can.focus();
            can.setSelectionRange(0, can.value.length);
            return false;
        }
    */
    //enviamos datos por ajax
        var res=ajaxR(url);
        var aux=res.split('|');
        //alert(res);
        if(aux[0] != 'exito'){
            alert("Error!!!\n"+res);
            busc.focus();
            busc.setSelectionRange(0, busc.value.length);
        //$("#contenedorGrid").html(res);
            return false;
        }

    /*Implementación Oscar 04.03.2019 para paquetes prediseñados*/
        if(paqte!=0){
            arreglo_paquete=aux[1].split("*");//asignamos valores al arreglo global de productos de paquete
            enlista_paquete();return false;
        }
    /*Fin de Cambio Oscar 04.03.2019*/

    /*Implementación Oscar 30.03.2019 para  no dejar vender productos externos en venta de mayoreo*/
        if(ventaTipo==1 && aux[16]==1){
            alert("Este producto no puede ser vendido en un ticket de mayoreo\nCapturelo en un ticket de venta normal!!!");
            $("#cantidad2").val("");
            $("#buscadorLabel").select();
            return false;
        }
    /*Fin de cambio Oscar 30.03.2019*/

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
                //alert(res);
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
        }//fin de paleta de colores

    //OBTENEMOS LA TABLA
        trs=tabla.getElementsByTagName('tr');
    //verificamos existencias
        var cantidadTotal=parseInt($("#cantidad2").val());
        if(inventario_validado==0){
            for(var j=1;j<trs.length;j++){
                tds=trs[j].getElementsByTagName('td');
                if(tds[0].innerHTML == aux[2] && sig == tds[9].valor && aux[2] != '18000'){
                    cantidadTotal=parseInt(tds[2].innerHTML)+parseInt($("#cantidad2").val());
                }    
            }//termina for
    /*implementación Oscar 25.09.2018*/
            var alm1=0,alm2=0,pass_enc,existente=0,arr_ax;
        //alert($("#cantidades_capturadas").val());
            var cant_alm=$("#cantidades_capturadas").val().split("|");
        //alert(cant_alm.length);
            if(cant_alm!=''){
                for(var i=1;i<cant_alm.length;i++){
                    arr_ax=cant_alm[i].split('~');
                    if(arr_ax[0]==aux[1]){
                        alm1=arr_ax[1];
                        alm2=arr_ax[2];
                        existente=1;
                    }
                }//fin de for i
            }
            if(existente==0){
                alm1=0;
                alm2=0;
            }
    /*fin de cambio*/
        if(cant!='' && cant!=null){
            cantidadTotal=cant;
        }
        var url2='ajax/verificaInventario.php?id_pr='+aux[1]+"&cant="+cantidadTotal+"&principal="+alm1+"&exhibicion="+alm2;
        //alert(url2);
        verif=ajaxR(url2);
//alert('cantidad que llega a la función: '+cant);
        if(verif!='ok' && aux[1]!='1808' && (cant=='' || cant==null)  && aux[13]==0 && es_paleta_tmp==0){
            /*se implementa la condición de si la respuesta en la 13 es igual a cero (quiere decir que no es maquilado ni muestra paleta Oscar 10.11.2018)*/ 
        /*implementacion de Oscar 02.11.2018 para ocultar los resultados de busqueda*/
            $("#resBus").css("display","none");//ocultamos resultados de busqueda
        /*fin de cambio*/
            $("#cont_emerg").html(verif);//cargamos respuesta
            $("#emergente_1").css("display","block");//hacemos visible la ventana emergente
            return false;
        }
    }
    //alert('hasta validar');

    //Buscamos si existe un producto igual
        var can2=-1;
        for(i=1;i<trs.length;i++){
            tds=trs[i].getElementsByTagName('td');
           // alert('|'+aux[2]+'|\n|'+tds[0].innerHTML+'|');
            //alert('|'+sig+'|'+tds[9]+'|');
            if(tds[0].innerHTML == aux[2]&&aux[2] != '18000' /*&& (cant==null||cant=='')*/){ /*&& sig == tds[9].valor se quita de la condición Oscar 03.09.2018*/
                //alert('Repetido');
                can2=parseInt(tds[2].innerHTML)+parseInt(can.value);
                //alert('hasta ');
                eliminarItem(tds[0]);
                //break;
                //alert('pasa');
            }    
        }//termina for
//alert('cantidad_anterior mas cantidad nueva'+can2);
        if(can2 != -1 && (cant==null||cant=='')){/*se agrega condición de que cant qe llega a la función sea nula o vacía*/
            var url="ajax/buscaProdOrCoId.php?val="+busqueda+"&can="+can2+"&can2="+can2+"&t="+ventaTipo+"&l_normal="+lista_normal;

        /*Implementación Oscar 03.03.2019 para tomar el precio de la lista de precios de mayoreo*/
            if(document.getElementById("id_lista_mayoreo")){
                url+="&id_lista_precio="+$("#id_lista_mayoreo").val();
            }
       // alert(url);
    /*Fin de Cambio oscar 03.03.2019*/

            var res=ajaxR(url);
//alert('res:'+res);
            var aux=res.split('|');
            
            if(aux[0] != 'exito'){
                alert(res);
                busc.focus();
                busc.setSelectionRange(0, busc.value.length);
                return false;
            }
        }
        else{
            can2=can.value;
        }
        
        
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
        var verif;
        
    //verificamos minimo de venta    
        url2='ajax/verificaMinimo.php?id_pr='+id_prod_selec+"&t="+ventaTipo;
        verif=ajaxR(url2);
//alert('ventaTipo: '+ventaTipo);
        if(can.value<parseInt(verif) && ventaTipo==""){
            //alert('catidad 2:'+can2);
            if(can2>=parseInt(verif)){//

            }else if(cant=='' || cant==null){//ese if agregado por Oscar 03.09.2018 para que no pida cantidad en cambios de apartados
                alert('Este productos no puede ser vendido en una cantidad menor a '+verif);
                can.value='0';
                can.focus();
                can.select();
                return false;
            }
        }
    //aqui detenemos venta si no existe el precio
   /* alert("aux[12]: "+aux[12]+" ventaTipo: "+ventaTipo+" cant: "+cant);
        if(aux[5]==0){//&&
            var mensaje="Este producto no se puede vender porque no tiene precio!!!!";
            if(ventaTipo==1){
                mensaje="Este producto no aplica en precio de mayoreo!!!";
            }
            alert(mensaje);
            can.value="";
            document.getElementById('buscadorLabel').select();
            return false;
        }*/
        if(aux[5]==0 && ventaTipo!=1){//&&
            var mensaje="Este producto no se puede vender porque no tiene precio!!!!";
            alert(mensaje);
            can.value="";
            document.getElementById('buscadorLabel').select();
            return false;
        }

//alert("aux[12]: "+aux[12]+" ventaTipo: "+ventaTipo+" cant: "+cant);
    /*implementación de emergente para productos que no entran en precio de mayoreo Oscar 09.05.2018*/
        if((aux[12]=='muestra_emergente'||aux[12]=='es_externo')&&ventaTipo==1&& ((cant==null||cant==''||aux[5]==0)/*||paqte==1*/) ){
            var btn_1='<button class="btn_emgr" onclick="agregaFila(null,';
            if(can.value==''){btn_1+=cant;}else{btn_1+=can.value;}
            btn_1+=',-1,'+aux[2]+',1);document.getElementById(\'emergePermisos\').style.display=\'none\';';
            if(enlistando_paquete!=0){
                    btn_1+='enlista_paquete();';
            }
            btn_1+='">Aceptar</button>';/*agregaFila(null,'+can.value+',0,'+id_prod_selec+');*/
            var btn_2='<button class="btn_emgr" onclick="document.getElementById(\'cantidad2\').value=\'\';document.getElementById(\'buscadorLabel\').value=\'\';';
            btn_2+='document.getElementById(\'emergePermisos\').style.display=\'none\';document.getElementById(\'buscadorLabel\').focus();';
            if(enlistando_paquete!=0){
                    btn_2+='enlista_paquete();';
            }
            btn_2+='">Cancelar</button>';
            $("#contEmerge").html("<br><br><br><p align=\"center\" style=\"color:white;font-size:30px;\">El producto "+aux[3]+" no cuenta con precio de mayoreo,<br>El precio por pieza es de $"+aux[14]+"<b></p>"+btn_1+"    "+btn_2);
            $('#emergePermisos').css('display','block');
            $('#emergePermisos').css('z-index','100');
            return false;
        }
    /*Fin de cambio 09.05.2018*/

    /*implementación de emergente para productos que no entran en precio de mayoreo Oscar 09.05.2018*
        if(aux[12]=='es_externo'&&ventaTipo==1){
            $("#contEmerge").html("<br><br><p align=\"center\" style=\"color:white;font-size:30px;\">El producto "+aux[3]+" no se puede vender como mayoreo <br>Si desea venderlo, captúrelo en un ticket que no sea de mayoreo!!!<b></p>");
            $('#emergePermisos').css('display','block');
            $('#emergePermisos').css('z-index','100');
            return false;
        }
//alert('llega_2');
    /*Fin de cambio 09.05.2018*/
        var newRow=tabla.insertRow(1);
        newRow.className="move";
        
        if(sig == 1){
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
    /*implementación de Oscar 13.08.2018*/
        if(cant!=null&&cant!=0&&cant!=''){
            can2=cant;
        }
    /*fin de cambio*/
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
        if(ventaTipo==1){
        	newCell.style.display="none";
        }
        newCell.innerHTML=aux[9];
        newCell.align="right";
        
        //monto
        var newCell=newRow.insertCell(5);
        newCell.innerHTML=aux[6];
        newCell.align="right";

    /*implementación Oscar 13.08.2018*/
        var segunda_funcion='';
        if(temp!=null&&temp!=''){
            segunda_funcion='eliminarTemporalExhib('+temp+');';
        }

    //Eliminar        
        var newCell=newRow.insertCell(6);
        newCell.innerHTML='<a href="javascript:void(0)" onclick="eliminarItem(this);'+segunda_funcion+'" class="eliminar"> <span>eliminar</span></a>';/*fin de cambio 13.08.2018*/
        newCell.align="center";
        
        //Fila precio oculta
        var newCell=newRow.insertCell(7);
        newCell.valor=aux[5];
        newCell.width="0";
        newCell.style="0";
        newCell.display="none";
        
        //Fila monto oculta
        var newCell=newRow.insertCell(8);
        newCell.valor=aux[7];
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
        
        //Fila regalo oculta
        var newCell=newRow.insertCell(9);
        newCell.valor=sig;
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
        
        //Fila familia oculta
        var newCell=newRow.insertCell(10);
        newCell.valor=aux[8];
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
        
        //id  oculta
        var newCell=newRow.insertCell(11);
        newCell.valor=aux[1];
        newCell.width="0";
        newCell.border="0";
        newCell.display="none";
        
        //descuento
        var axDisable='';
        if(referenciaPaquete==1){
            axDisable='disabled';
        }
        var newCell=newRow.insertCell(12);
        newCell.width="0";//120px
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
        newCell.innerHTML='<input type="text" class="des" style="display:none;" onkeyup="cambiaMonto(this);" '+axDisable+'">';*/
        newCell.border="0";
        newCell.display="none";

        //respaldo de monto original
        var newCell=newRow.insertCell(13);
        newCell.width="0";
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
        newCell.innerHTML='<input type="hidden" value="'+aux[6]+'">';*/
        newCell.border="0";
        newCell.display="none";

/*implementación de Oscar 18.03.2019 para saber de que lista fue tomado el precio y validar promoción*/
        var newCell=newRow.insertCell(14);
        newCell.valor=aux[15];
        newCell.display="none";
/*Fin de cambio Oscar 18.03.2019*/
        sig=0;
        lista_precios_actual=aux[15];

    /*implementacion de Oscar 03.05.2018 para insertar en tabla temporal de movimientos*/      
        var urlTmp="ajax/insertaTemporal.php?id_mov_temp="+id_mov_tmp+"&id_prod="+aux[1]+"&cant="+can2;
        //alert(aux[0]+"\n"+aux[1]+"\n"+aux[2]);
        var registraTemporal=ajaxR(urlTmp);
        var respTmp=registraTemporal.split("|");
        if(respTmp[0]!='ok'){
            alert("Error al enviar registro temporal:\n"+registraTemporal);
            return false;
        }else{
        //asignamos valor al movimiento temporal que corresponde al pedido
            id_mov_tmp=respTmp[1];
        }
    /*Fin de cambio 03.05.2018 NOTA: Este pedaso de código estaba abajo de  */




    //Buscamos familias 21126
       familiaAct=aux[8];//declaramos la familia contra la que vamos a comparar los productos previamente capturados
       familias="";//declaramos como vacía la variable de familias
       cans="";//capturamos las cantidades
       posiciones=Array();//declaramos arreglo donde serán guardadas las posiciones de coincidencia de familias
       nfams=0;//declaramoscontador de numero de familias
       
       trs=tabla.getElementsByTagName('tr');//obtenemos filas
       var can2=0;//declaramos cantidad auxiliar en ceros
       for(i=1;i<trs.length;i++)//recorremos tabla de productos agregados
       {
           tds=trs[i].getElementsByTagName('td');//obtenemos celdas
         //  alert(tds[1].innerHTML/*+"  "+sig*/);
        /*cehcamos coincidencia de familia, lista de precios*/
           if(tds[10].valor == familiaAct && /*ventaTipo!=1*/tds[14].valor==lista_precios_actual )/*****Se agrega condición de tipo de venta de mayoreo Oscar 22.10.2018*****/
           {
//alert(tds[1].innerHTML+":\n"+tds[14].valor+'='+lista_precios_actual+"\n"+tds[10].valor+' == '+familiaAct);
            //Validamos si tiene la misma promocion
               //var url="ajax/validaPromo.php?id_prod1="+aux[1]+"&id_prod2="+aux[8]+"&can="+can2+"&id_lista_prc="+lista_precios_actual;
               //alert(url);
               can2+=parseInt(tds[2].innerHTML);
               if(familias != ''){
                    familias+=",";//concatenamos coma
               }
                    
               if(cans != '')
                    cans+=",";     
               
               familias+=tds[11].valor;
               cans+=tds[2].innerHTML;
               posiciones[nfams]=i;
               nfams++;
           }//fin de coincidencia de familia y lista de precio

       }//fin de for i

//alert("numero de Familias: "+nfams);
       if(nfams > 1 && familias != '' && aux[1]!=1808){/*&&ventaTipo!=1*///tds[14].valor==lista_precios_actual 
           //alert(ventaTipo);
           var url="ajax/validaPromo.php?prods="+familias+"&can="+can2+"&cans="+cans+"&prodAct="+aux[1]+"&id_lista_prc="+lista_precios_actual;
           //alert("entra en 2: "+url);
        //alert('aqui entra_2');
           var res=ajaxR(url);
           
           var aux=res.split('|');
//alert('respuestaaa: '+res);
           var pTemp=res.split('~');
           if(aux[0] == 'exito')
           {
           
                for(i=0;i<parseInt(aux[1]);i++){
                    var ax=aux[i+2].split('~');
                    tds=trs[posiciones[i]].getElementsByTagName('td');
                    
                    if(ax[0] != 'NO'){
                        tds[3].innerHTML=ax[0];
                        tds[5].innerHTML=ax[1];
                        tds[7].valor=ax[2];
                        tds[8].valor=ax[3];
                    /*
						-Cambio implementado por Oscar(02-11-2017)
							(cambie el monto temporal cuando cambia la oferta)
                    */
                    	//alert('ax en la 0: '+ax[0]);
                        //var conv=ax[0].split("$");
                        ax[0]=pTemp[2]*tds[2].innerHTML;
                        //return false;
                        tds[13].innerHTML='<input type="hidden" value="$'+Math.round(ax[0])+'">';
                        //alert(tds[13].innerHTML);
                    }    
                }  
                
           }
           else{
                alert("Error_valida_promo_1:\n"+res); 
           	}	
           
       }

        //calculaTotalProd();
        calculaTotalVenta();//se cambia el 0.04.2018 a esta funcion ya que la función calculaTotalProd(); generaba conflictos con paquetes prediseñados
        can.value="";
        busc.value="";
    
    //reseteamos la variable de inventario validado
        inventario_validado=0;

        //busc.focus();

        setTimeout("document.getElementById('buscadorLabel').focus();lista_precios_actual=0;", 100);
        //alert('fin');
    }



	function enterNext(obj, eve)
    {
		key=(eve.which) ? eve.which : eve.keyCode;
        
		if(key == 13)
		{
		
			var aux=obj.id.replace("cantidadPaleta","");
			aux=parseInt(aux);
			
			if(document.getElementById("cantidadPaleta"+(aux+1))){
				document.getElementById("cantidadPaleta"+(aux+1)).focus();
            }
			else{
				document.getElementById("cantidadPaleta99").focus();
            }
		}
	}
    

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores    
    function calculaTotalProd()
    {
        alert('entra en proceso anterior');
        var total=0;
        var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
        
        var tabla=document.getElementById('listaProductos');
        trs=tabla.getElementsByTagName('tr');
        for(i=1;i<trs.length;i++)
        {
            tds=trs[i].getElementsByTagName('td');
            
            total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);
            
        }
        
//(alert(total);
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
        
        //alert(totalVenta);
        totalVenta=Math.floor(total);    
        
        document.getElementById('total').value=Mascara("$#,##.##", total);
    }*/
    
    function redond(val, ndec){
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
             
             
    var id_ver=0;
    var sig=0; 

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
    var id_autorizacion=0; 
    
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
*/
     function ajaxR(url)
        {
            if(window.ActiveXObject){       
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
//alert(descuento);
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

/*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
      function calculaTotal () {
        // Sumar el total 
            total = 0.0;
            $("#listaProductos tr.move td.tabla_total p").each (function(index, value) {
                total += parseFloat($(this).html ().replace (/[\$\s,]/g, ""));
            });
            $("#total").val ("$ " + moneyFormat(total));
      }*/


    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
      function calculaPrecios () {
          $("#listaProductos tr.move td.tabla_id_producto").each (function(index, value) {
                var id_producto = $(this).find("p").html();
                var tr = $(this).parent ();

                $.ajax({
                     async: false,
                     type: 'GET',
                     url: "ajax/precioProducto.php?idp=" + id_producto
                }).done (function (data){
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
      }*/

	function eliminarItem (item){
		
		//tds=$(item).getElementsByTagName('td');
		
		var tds=$(item).parents("tr").children("td");
		familiaAct=tds[10].valor;
		lista_precios_actual=tds[14].valor;
		var id_prod=tds[11].valor;
		//alert(id_prod);
		//return false;
		$(item).parents ("tr.move").first ().remove ();
		//calculaTotal ();
		$("#producto, #cantidad").val ("");
		
		familias="";
		cans="";
		posiciones=Array();
		nfams=0;
		

		var tabla=document.getElementById('listaProductos'); 
		trs=tabla.getElementsByTagName('tr');
		
       	var can2=0;
		for(i=1;i<trs.length;i++)
		{
			tds=trs[i].getElementsByTagName('td');
			//alert(tds[8].valor+" - "+sig);
			
			//alert(tds[10].valor);
			
			if(tds[10].valor == familiaAct && tds[14].valor==lista_precios_actual)/*Oscar 19.03.2019*/
			{
				//Validamos si tiene la misma promocion
				can2+=parseInt(tds[2].innerHTML);
               
				if(familias != '')
					familias+=",";
                    
				if(cans != '')
					cans+=",";     
               
				familias+=tds[11].valor;
				id_prod=tds[11].valor
				cans+=tds[2].innerHTML;
				posiciones[nfams]=i;     
                
				nfams++;
			}     
		}
				
		var ventaTipo=$("#tipo_venta").val();//esta variable se declara para saber si es venta por mayoreo
       
		if(nfams > 0 && familias != '' && id_prod!=1808 /*&& ventaTipo!=1*/)/*se agregó condición para que no se aplique promo cuando la venta es por mayoreo*/
		{
            //alert("Entra en validacion \nnfams: "+nfams+"\nfamilias: "+familias);
			var url="ajax/validaPromo.php?prods="+familias+"&can="+can2+"&cans="+cans+"&prodAct="+id_prod+"&tipo=eliminar"+"&id_lista_prc="+lista_precios_actual;
			//alert(url);           
			var res=ajaxR(url);			
			//alert(res);           
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
                        tds[13].innerHTML='<input type="hidden" value="'+ax[0]+'">';
                    }    
                }                
           }else{
                alert("Error_valida_promo_3:\n"+res);     
           }//fin de else
           
       }
        calculaTotalVenta();
      }

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
      function bloqueaPantalla () {
          if ($("#es_regalo").prop('checked'))
                $("#divEspera").css ("display", "block");
      }*/

	function guardarVenta (){
//alert('entra enn venta');
		var retval = false; 
        //alert('vta');             
		$("#menu_cerrar").prop ("disabled", true);		
		var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
		var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
		var id_pedido = $("#id_pedido").val ();
		var datos = "nitems=" + $("#listaProductos tr.move").length;
//implementacion de correo y facebook Oscar(29-10-2017)
    /*recolectamos información del cliente 
        var corr=$("#co").val();
        var fac=$("#fa").val();
        if(corr==""){
            corr='-';
        }
        if(fac==""){
            corr='-';
        }
     */   
	//Detalles
		var tabla=document.getElementById('listaProductos');
		trs=tabla.getElementsByTagName('tr');
		for(i=0;i<trs.length-1;i++)
		{
			tds=trs[i+1].getElementsByTagName('td');
            
            //implementado por Oscar(28-10-2017)
            var objIn=tds[12].getElementsByTagName('input');
        
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            if(objIn.length > 0){
                descProd=isNaN(parseFloat(objIn[0].value))?0:parseFloat(objIn[0].value);
            }
            //termina cambio*/
            
			//total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);tds[11].innerHTML
            datos += "&idp" + i + "=" + tds[11].valor + "&can" + i + "=" + tds[2].innerHTML + "&pre" + i +"=" + tds[7].valor
            + "&mon" + i +"=" + tds[8].valor+"&id_precio" + i +"=" + tds[14].valor;//se agrega variable desc para guardar

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            +"&desc"+i+"="+descProd*/
		}
         
		//var url= "ajax/guardaNuevaVenta.php?idp=" + id_pedido  + "&pe=" + es_pedido + "&pa=" + es_paquete + "&" + datos + "&totalPed="+totalVenta;
    //validamos password  
        totalVentAux=($("#total").val()).split("$");
		//alert(totalVentAux[1]);
        var url="ajax/guardaVentaTemp.php?idp="+id_pedido +"&pe="+es_pedido;

/*Implementación Oscacr 01.03.2019 para mandar el tipo de venta a la tabla de venta tmp*/
        url+="&tv="+$("#tipo_venta").val();
/*Fin de cambio Oscar 01.03.2019*/

        url+="&pa="+es_paquete+"&"+datos+"&totalPed="+parseFloat(totalVentAux[1])+"&abonado="+$("#monto_abonado").val();//modif 11.04.2018 por paq prediseñados totalVenta
        
    /*implementación Oscar 03.09.2018 para cambios en apartado*/
        var id_apartado=$("#id_de_apartado").val();
        if(id_apartado!=null&&id_apartado!=''){
            url+="&id_ped_apart="+id_apartado;
        }
    /*Implementación Oscar 06.11.2018 para mandar autorización de descuento*/
        url+="&descuento_autorizado="+autorizado;
    /*fin de cambio Oscar 06.11.2018*/
       //alert(url);
    /*fin de cambio 03.09.2018*/
    
    /*implementación Oscar 12.03.2019 para guardar la lista de precios usada durante la venta de mayoreo*/
        if(document.getElementById('id_lista_mayoreo')){
            url+="&lista_precios_mayoreo="+$("#id_lista_mayoreo").val();
        }
    /*Fin de cambio Oscar 12.03.2019*/

        var res=ajaxR(url);
/*alert(res);
return false;*/
		aux=res.split('|');
		/**/
        if(aux[0]=='sin_sesion_caja'){
            alert("No se pueden hacer ventas porque el cajero no ha iniciado sesión de caja\nPida al cajero que inicie su sesión de caja!!!");
            return false;
        }
        //alert(res);
        if(aux[0] == 'OK')
		{
			var ax=aux[1].split(':');
			$("#id_pedido").val (ax[1]);
		/*modificación Oscar 13.11.2018*
            $("#detalle_nota_tmp").val(aux[3]);
        /*fin de cambio Oscar 13.11.2018*/
			retval = true;
		}
		else{
			retval = false;
        }
/*Implementación para borrado de tablas en temporal Oscar (03.05.2018)*/
    //borramos el temporal
        var urlTmp="ajax/insertaTemporal.php?id_mov_temp="+id_mov_tmp+"&fl=1";
        var registraTemporal=ajaxR(urlTmp);
        var respTmp=registraTemporal.split("|");
        if(respTmp[0]!='ok'){
            alert("Error al eliminar registro temporal:\n"+registraTemporal);
            return false;
        }
/*fin de cambio 03.05.2018*/

//alert('fin de generaVe');	
		return retval;
	}
/*implementación Oscar 2017*/
    function verificaPass(){
        activaMayoreo();
    }


    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
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
            alert("Error al buscar las ofertas.");
            retval = false;
       });

     return retval;
    }*/

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
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
            
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
                actualizaOfertas ();
                *
            });
        }else{
            alert("El producto seleccionado no existe o la cantidad solicitada es incorrecta.");
        }

        $("#producto, #cantidad").val ("");
        $("#producto").focus ();
    }*/


/*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores    
    function buscarProducto (){
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
*/

/*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
    function marcaRegalo () {
         var es_regalo = $("#es_regalo").prop ("checked") ? "1" : "0";
         var es_pedido = $("#es_pedido").prop ("checked") ? "1" : "0";
         var es_paquete = $("#es_paquete").prop ("checked") ? "1" : "0";
         var id_pedido = $("#id_pedido").val ();
         
         //$("#es_regalo").prop ("disabled", true);
         //$("#img_regalo").prop ("disabled", true);
         

		if(existeRegalo > 0){
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
          });//////////////*\
         
         //Detalles
         
         var tabla=document.getElementById('listaProductos');
         trs=tabla.getElementsByTagName('tr');
         for(i=0;i<trs.length-1;i++)
         {
            tds=trs[i+1].getElementsByTagName('td');
            
            //total+=isNaN(parseFloat(tds[8].valor))?0:parseFloat(tds[8].valor);tds[11].innerHTML
            
            datos += "&idp" + i + "=" + tds[11].valor + "&can" + i + "=" + tds[2].innerHTML + "&pre" + i +"=" + tds[7].valor+ "&mon" + i +"=" + tds[8].valor;
            
         }
        
    
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
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
        }*******\/
    }*/

    function changeHashOnLoad() {
        //alert('se ocupa');
        var base_href = location.href.match (/^([^\#]*)(?:\#.*)?$/i)[0];
        location.href = base_href + "#";
    /*implementación Oscar 30.05.2018 para limpiar las cajas de texto* DEshabilitado por Oscar 09.11.2018 porque ya no se usa para limpiar cajas de texto
        if($("#id_de_apartado").val()=='' || $("#id_de_apartado").val()==null){
            //alert('limpiar');
            $("#porcGeneral").val("");//limpiamos entrada de texto de descuento por porcentaje
        }
        if(!document.getElementById('desc_anterior')){
            $("#descGeneral").val("");//limpiamos entrada de texto de descuento por monto
        }else if(document.getElementById('desc_anterior').value>0){
            $("#porcGeneral").val($("#desc_anterior").val());
        }
    /*fin de cambio 30.05.2018*/
        setTimeout("changeHashAgain()", "50");
    }

    function changeHashAgain() {          
        location.href += "1";
        //alert("again");
    }

    var storedHash = window.location.hash;
    setInterval(function () {
        if (location.hash != storedHash) {
            location.hash = storedHash;
        }
    }, 50);


    $(document).ready(function(){
        // Bloquear evento goBack () 
        changeHashOnLoad();
        
        <?php if (!isset($folio)) { ?>
            cargaNuevoFolio ();
        <?php } ?>

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#es_pedido").on ("click", function () {
                if ($("#es_pedido").prop('checked')) {
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
                    $("#menu_cerrar").removeClass ("pedido").addClass("nota");
                }
            
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
                calculaPrecios ();*\/
                cargaNuevoFolio ();
                $("#producto").focus ();
            });*/

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#es_regalo").on ("click", function () {
                return marcaRegalo ();
            });*/
            
            /*$("#img_regalo").on ("click", function () {
                
                return marcaRegalo ();
                
            });*/

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
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
            });*/
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#img_regalo").on ("click", function () {
                if (!$("#es_regalo").prop ("disabled")) {
                    $("#es_regalo").prop('checked', true);
                    $("#es_regalo").prop('checked', marcaRegalo ());
                }
                $("#producto").focus ();
            });*/            

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#cantidad").on ("keydown", function (e) {
                alert();
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    agregarProducto();
                    return false;
                }
            });*/
           
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores $("#cantidad").ForceNumericOnly();*/

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#producto").on ("keydown", function (e){
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    buscarProducto();
                    return false;
                }
            });*/

            
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            // Botón agregar... 
            $("#agregar").on ("click", function () {
                agregarProducto ();
             });*/

    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores
            $("#es_paquete").on ("click", function () {
                alert('aqui');
                //alert(res);
            //implementado por oscar 4-11-2017
                if(document.getElementById("es_paquete").checked==true){
                //checamos el descuento
                    var opc=confirm("El paquete no permitirá ningún tipo de descuento adicional!!!\nDesea continuar?");
                    if(opc==false){
                        document.getElementById("es_paquete").checked=false;
                        return false;
                    }
                    var link="ajax/getDescuentoPaquete.php";
                    var res=ajaxR(link);
                    if(res=='no'||res==''||res==0){
                        alert('No se encontró descuento para paquete!!!\nConsulte al encargado...');
                    }
                    document.getElementById("porcGeneral").value=res;
                //deshabilitamos descuentos en grid
                    //resetearMontos(); 
                    referenciaPaquete=1;      
                    $('#listaProductos').find('input').prop('disabled',true);
                //deshabilitamos deescuentos directos
                    $('#descGeneral').prop('disabled',true);
                    $('#porcGeneral').prop('disabled',true);            
                }else{
                    alert('Paquete desactivado!!!');
                    referenciaPaquete=0;  
                    if(opc==false){
                        return false;
                    }
                    document.getElementById("porcGeneral").value="";
                //habilitamos descuentos en grid
                    $('#listaProductos').find('input').prop('disabled',false);
                //habilitamos deescuentos directos
                    $('#descGeneral').prop('disabled',false);
                    $('#porcGeneral').prop('disabled',false);
                }
                calculaTotalVenta();

                //calculaPrecios ();
            //calculaTotalProd();
            });*/

            $("#menu_cerrar, #cerrar").on ("click", function (){
//alert('cierra, AUTORIZADO= '+autorizado);
                if(autorizado==0){
                    if(!confirm("Esta operación cerrará la venta, ¿desea continuar?")) {
                    //regresamos referencia a un valor vacío
                        sa=0;
                        autorizado=0;
                        return false;
                    }
                }
    /*
     CAMBIOS DE OSCAR
    */
            //verificamos que tipo de venta es
                var ventaTipo=document.getElementById('tipo_venta').value;
            //si venta es valor 1 ntra en proceso de mayoreo
                var ventaPrev=document.getElementById('total').value;
                var tem=ventaPrev.split('$');
                ventaPrev="";
                var temp2=tem[1].split(',');
                for(var i=0;i<temp2.length;i++){
                    ventaPrev+=temp2[i];
                }
//alert('total: '+totalVenta+' prev: '+ventaPrev);
            
                if(ventaPrev<totalVenta && autorizado==0){//||referenciaPaquete==1 && autorizado==0
                //sacamos los descuentos por producto
                    var txt1="Porcentaje de descuento aplicado sobre el ticket: ";
                    var txt2="Descuento directo aplicado sobre el ticket: ";
                    var detalleDescProd='<p class="autDesc">Descuentos por Producto:</p>'+
                    '<div class="tablaEmergente"><table><tr><td width="50%">Producto</td><td>Cant</td><td>Monto</td><td>Desc</td><td>Total</td></tr>';
                    
                    /*var tabla=document.getElementById('listaProductos');
                    var trs=tabla.getElementsByTagName('tr');
                    if(referenciaPaquete==1){
                         for(i=0;i<trs.length-1;i++){
                        tds=trs[i+1].getElementsByTagName('td');
                        var objIn=tds[12].getElementsByTagName('input');
                        var descProd=0;
                            //1,13,2
                            var descripcionPr=tds[1].innerHTML;
                            var cantidadProductos=tds[2].innerHTML;
                            var objAux=tds[13].getElementsByTagName('input');
                            var subtotalReal=objAux[0].value;
                            var montTemp=tds[5].innerHTML;
                            detalleDescProd+='<tr><td>'+descripcionPr+'</td><td>'+cantidadProductos+'</td><td>'
                            +subtotalReal+'</td><td>$'+'</td><td>'+montTemp+'</td></tr>';
                        }
                        var txt1="Este descuento es por paquete\n";
                        var txt2="";
                    }else{
                    for(i=0;i<trs.length-1;i++){
                        tds=trs[i+1].getElementsByTagName('td');
                        var objIn=tds[12].getElementsByTagName('input');
                        var descProd=0;
                        if(objIn.length > 0){
                            descProd=isNaN(parseFloat(objIn[0].value))?0:parseFloat(objIn[0].value);
                        }
                        if(descProd>0){
                            //1,13,2
                            var descripcionPr=tds[1].innerHTML;
                            var cantidadProductos=tds[2].innerHTML;
                            var objAux=tds[13].getElementsByTagName('input');
                            var subtotalReal=objAux[0].value;
                            var montTemp=tds[5].innerHTML;
                            detalleDescProd+='<tr><td>'+descripcionPr+'</td><td>'+cantidadProductos+'</td><td>'
                            +subtotalReal+'</td><td>$'+descProd+'</td><td>'+montTemp+'</td></tr>';
                        }
                    }//fin de for
                }//fin de else
                    detalleDescProd+="</table></div>";*/
                    
                    var descDirectos,porcDir,descDir;
                    porcDir=document.getElementById('porcGeneral').value;
                    descDir=document.getElementById('descGeneral').value;
                    if ( $( '#discount_without_password' ).val() == 0 ) {
                        descDirectos='<p class="autDesc">'+txt1+porcDir+'%</p>'+
                        '<p class="autDesc">'+txt2+descDir+'</p>';
                        $("#contEmerge").html('<p class="autDesc">DETALLE DE DESCUENTOS:</p>\n'+/*detalleDescProd+*/'\n'+descDirectos+
                            '<p ><p class="autDesc">Pida al encargado que Ingrese su password</p>'+
                            '<input class="pa" onkeyDown="cambiar(this,event,\'passWord\');" onkeyup="accion_tecla(event,1);" id="passWord_1" type="text" style="width:25%;padding:15px;"></p>'+
                        /*modififcación oscar 10.11.2018 para volver pass un campo de texto*/
                            '<p><input type="button" value="Autorizar" onclick="verificaAutorizacion(1);" id="bot_aut_1" style="padding:15px;border-radius:8px;"></p>'
                            +'<input type="hidden" id="passWord" value="">'
                            );
                        $("#resBus").css("display","none");//implementado por Oscar 12.11.2018 para ocultar resultados de busqueda 
                        /*fin de cambio*/
                        verificaPass();
                        $( '#passWord_1' ).focus();
                        $( '#passWord_1' ).select();
                        window.scrollTo(0, 0);
                        return false;
                   }else{
                        autorizado=1;
                        totalVenta=totalFinal;
                   }
                }
                if(ventaTipo=='1' && sa==0 && autorizado==0){
                      //sa=1;//igualamos variable de referencia a 1 para que la proxima vez no entre a proceso de mayoreo
                //mmostramos emergente de password
                    $("#contEmerge").html('<div id="contEmerge">'+
                        '<p style="font-size:20px;color:white;"><br><br><br><br>Ingrese su contraseña para venta de mayoreo</p>'+
                    /*modificación de Oscar 10.11.2018*/
                        '<input class="pa" id="passWord_1" onkeyDown="cambiar(this,event,\'passWord\');" type="text" style="width:25%;padding:15px;border-radius:10px;height:27px;font-size:28px;">'+
                        '<input type="hidden" id="passWord" value="">'+
                    /*Fin de cambio Oscar 10.11.2018*/
                        '<p id="botEmergente"><br><input type="button" value="Acceder" onclick="verificaMayoreo();"'+
                        'style="padding:15px;border-radius:5px;font-size:20px;width:27%;"></p>'+
                        '<input type="hidden" value="" id="cambiaFunc"></center></div>');

                    $("#resBus").css("display","none");//implementado por Oscar 12.11.2018 para ocultar resultados de busqueda
                    
                    var re=verificaPass();
                    $( '#passWord_1' ).focus();
                    $( '#passWord_1' ).select();
                    window.scrollTo(0, 0);
                    return false;
                }else{
                    document.getElementById('emergePermisos').style.display='none';
                //devolvemos pantalla a estado normal
                    cierraEmer();
            //si no es mayoreo el proceso sigue norma
                }
    /*
        FINALIZAN CAMBIOS
    */
            //valida que haya productos capturados
                 if (!$("#listaProductos tr.move").length)
                 {
                     // Verificar la selección de productos en lista 
                     alert("Imposible continuar.\nNo ha seleccionado productos para la venta.");
                     $("#buscadorLabel").focus ();
                     return false; 
                 }
                 else{
                     if (!guardarVenta ()){
                         // No se pudo guardar la venta 
                         alert("Error mientras se procesaba la venta.\nActualice la pantalla e intente nuevamente.");
                         return false;
                     }
                     else
                     {
                         /*if ($("#menu_cerrar").hasClass ("pedido"))
                         {
                            // Si se trata de un pedido, imprimir ticket y bloquear la opción pedido 
                            // Tambien habilita el botón para cerrar la venta 
                             
                             agregarProducto = function () { alert ("Función deshabilitada."); return false; };
                             //Cambiar por ajax  
                             //window.open ("index.php?scr=ticket&idp=" + $("#id_pedido").val (), "_blank"); 
                             //alert('ok');
                             $.ajax({
				                    async: false,
				                    type: 'GET',
				                    url: "../index.php?scr=ticket&idp=" + $("#id_pedido").val () + "&noImp=1"
				               }).done (function (data) {
                                    //alert(data);
				                   return true;
				               });/*
				             $.ajax({
                                    async: false,
                                    type: 'GET',
                                    url: "../index.php?scr=ticket&idp=" + $("#id_pedido").val () + "&noImp=2"
                               }).done (function (data) {
                                    //alert(data);
                                   return true;
                               });  
                             *\/
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
                         }*\/
                         else{*/
                             if ($("#menu_cerrar").attr ("href").match (/&idp=\d+/i)) {
                                 $("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href").replace (/&idp=\d+/i, "&idp=" + $("#id_pedido").val ()));
                             }
                             else {
                            // modificacion de Oscar(05-11-2017)
                                var ventaTipo=document.getElementById('tipo_venta').value;//sacamos el tipo de venta
                            //implementacion de Oscar 24-12-2017
                                var sFa='';
                                if(document.getElementById('saldoAFavor')){
                                    sFa="&sald_fav="+document.getElementById('saldoAFavor').value;
                                }
                            
                            /*implementación de Oscar 04.09.2018 para cambio de apartados*/
                                var es_apartado=$("#id_de_apartado").val();
                                var aprt='';
                                if(es_apartado!=''&& es_apartado!=null){
                                    aprt="&id_aprt="+es_apartado;
                                }
                                var abono='';
                                if($("#monto_abonado").val()){
                                    abono="&abonado="+$("#monto_abonado").val();
                                }
                            /*fin de cambio 04.09.2018*/

                        /*Cambio Oscar 13.11.2018 para enviar el temporal de la venta antes de que fuera modificada*
                            	var prods_antes="";
                                if($("#detalle_nota_tmp").val()){
                                    prods_antes="&prods_ant="+$("#detalle_nota_tmp").val();
                                }
                        /*Fin de cambio Oscar 13.11.2018*/

                        /*implementacion Oscar 25.06.2019 para arrastrar los ids de devolucion a la pantalla de cerrar venta*/
                            var ids_dvs="";
                            if(document.getElementById('id_de_devoluciones')){
                                ids_dvs+="&ids_devoluciones="+$("#id_de_devoluciones").val();
                            } 
                        /*Fin de cambio Oscar 25.06.2019*/

                        /**/
							$("#menu_cerrar").attr ("href", $("#menu_cerrar").attr ("href") + "&idp=" + $("#id_pedido").val ()+'&tv='+ventaTipo+sFa+aprt+abono+"&id_pedido_original="+$("#id_de_pedido_original").val()+ids_dvs);//
                             
    /*deshabilitado por Oscar 09.11.2018 Revisado con Pedro para corregir errores}*/

                             if ($(this).attr ("id").match (/^cerrar$/i))
                                 location.href = $("#menu_cerrar").attr ("href");
                             
                             return true;
                            
                         }
                     }
                 }
             });

            /*$("#es_regalo").prop ("checked", false);
            $("#producto").focus ();*/
                
        });

      /* ]]> */
  </script>