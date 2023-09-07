<link href="../../css/estilos.css" rel="stylesheet" type="text/css" />
<link href="../../css/gridSW.css" rel="stylesheet" type="text/css" />


{include file="_header.tpl" pagetitle="$contentheader"}

<script language="JavaScript" type="text/javascript" src="{$rooturl}js/jquery/jquery.js"></script>

<div id="bg_seccion">
    <div id="campos">
        <div class="report-bg">
            <div class="titulo-icono" id="titulo-icono">
                <div class="titulo" id="titulo">{$titulo} </div>
            </div>
            <div class="buscador" id="buscador">
                <div class="buscador1">
                    <form action="#" method="post" name="formax">
                        <input type="hidden" name="titulo" value="{$titulo}">
                        <input type="hidden" name="campoTexto" value="">

                        <table align="center"  class="campos">
                            <!--Numeros de reporte que incluiran el filtro -->
                            {if  1}


                                <tr>
                                    <td colspan="4" class="tdContorno">
                                        {if $reporte lt 4 or ($reporte gt 4 and $reporte lt 10)}
                                            &nbsp;&nbsp;
                                            <span class="requerido">Rango de fechas:</span>

                                            &nbsp;&nbsp;del&nbsp;
                                            <input type="text" name="fecha1" id="fecha1" maxlength="10" size="12" class="campos" onFocus="calendario(this);" value="{$fecha1}" />&nbsp;al&nbsp;
                                            <input type="text" name="fecha2" id="fecha2" maxlength="10" size="12" class="campos" onFocus="calendario(this);" value="{$fecha2}"/>	

                                        {/if}
                                        
                                        
                                        

                                        Delegacion :                                       
                                            <select id="delegacion" name="delegacion" class="combos">						
                                                {html_options values=$delegaciones output=$delegaciones}
                                            </select>	
                                            
                                        {if $reporte eq 4}				
                                            Periodo :
                                            <select name="anio" class="combos">						
                                                {html_options values=$anios output=$anios}
                                            </select>				
                                        {/if}
                                        
                                        {if $sucUsuario eq -1}
                                        
	                                        Sucursal:
		                                    <select name="sucursal" id="sucUsuario">
		                                    	<option value="-1">--Cualquiera--</option>
		                                    	<option value="1">Floreria Liliana</option>
		                                    	<option value="2">Floreria Funebre</option>
		                                    	<option value="3">Nicte</option>
		                                    </select>
	                                    {else}
	                                    	<input type="hidden" name="sucursal" id="sucUsuario" value="{$sucUsuario}">
	                                    {/if}

                                    </td>
                                </tr>		

                                <tr>
                                    <td>
                                    &nbsp;
                                    </td>
                                </tr>
                                <tr valign="top" >
                                    <td class="subreporte">TABLA:<br />
                                        <select name="tabla" class="combos" onchange="cambiaCampos(this, 'campo')" id="tabla">
                                            <option value="0">-- Seleccionar --</option>
                                            {html_options options=$arrayTablas}
                                        </select>
                                    </td>
                                    <td class="subreporte">CAMPO:<br />
                                        <select name="campo" id="campo" class="combos" size="14" >
                                            <option value="0">-- Seleccionar --</option>
                                        </select>
                                    </td>
                                    <td class="subreporte">CRITERIO:<br />
                                        <select name="criterio" id="criterio" class="combos" size="14">
                                            <option value="0">Igual a (&#61;)</option>
                                            <option value="1">Diferente a (&ne;)</option>
                                            <option value="2">Mayor que (&gt;)</option>
                                            <option value="3">Menor que (&lt;)</option>
                                            <option value="4">Mayor o igual a (&ge;)</option>
                                            <option value="5">Menor o igual a (&le;)</option>
                                            <option value="6">Empieza con...</option>
                                            <option value="7">No empieza con...</option>
                                            <option value="8">Termina con...</option>
                                            <option value="9">No termina con...</option>
                                            <option value="10">Contiene a...</option>
                                            <option value="11">No contiene a...</option>
                                            <option value="12">Es nulo</option>
                                            <option value="13">No es nulo</option>
                                        </select>
                                    </td>
                                    <td class="subreporte" valign="top" >VALOR:<br />
                                        <div id="contenedor_general">
                                            <input id='valor' size="30" class="campos_req" name="valor" style="width:200px;" onkeyup="actPrefiltro(event, this)"/>
                                            <img id="flecha" src="{$carpetaImagenes}down.gif" style="vertical-align:top; height:19px; width:19px;" onmouseover="this.style.cursor = 'hand'" />
                                            <div id="valores" onmouseover="this.style.cursor = 'arrow'"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="subreporte">INCLUSI&Oacute;N:<br />
                                        <input type="radio" name="limit" value="AND" id="limit" checked="checked"/>Y 
                                        <input type="radio" name="limit" value="OR" id="limit"/>O

                                    </td>
                                    <td colspan="3" align="right" class="tdContorno">
                                        <input type="button" value="Agregar" class="boton" onclick="agregaCriterio(this.form);" id="agregar"/>
                                        &nbsp;
                                        <input type="button" value="Seleccionar todos" class="boton" onclick="SelectAll(document.forms.formax.agregados);"/>
                                        &nbsp;
                                        <input type="button" value="Eliminar seleccionado(s)" class="boton" onclick="trashElements(document.forms.formax.agregados);"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="left" class="tdContorno">
                                        <select name="agregados" id="agregados" class="combos" size="5" style="width:900px" multiple="multiple">
                                        </select>
                                    </td>
                                </tr>

                            {/if}
                        </table>
                        <!--<input type="hidden" id="valor" value="" />-->
                    </form>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <!-- Reportes que contendran el campo de orden -->
                        {if 0}
                            <tr style="height:60px;">
                                <td>
                                    &nbsp;&nbsp;<span class="requerido">Ordenar por:</span>&nbsp;&nbsp;
                                    <select name="orden" class="combos" id="orden">
                                        {html_options values=$arrOrden[0] output=$arrOrden[1] }
					</select>
					</td>
				</tr>
                                        {/if}
				<tr valign="middle">
					<td height="26">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr align="left">
								
<!--Botonera-->
								<td align="left" class="user campos">
										<input type="button" value="Regresar" onClick="history.back(-1);" class="boton"/>
                                            {if (($reporte ge 1 and $reporte le 78) or $reporte eq 104) or ($reporte ge 100 and $reporte le 101) or $reporte eq 103  or $reporte eq 200 or $reporte eq 201 or $reporte eq 202 or $reporte eq 203 or $reporte eq 300}
											<input type="button" value="Generar reporte" class="boton" onclick="generaReporte(1,document.forms.formax.agregados,{$reporte})"/>
                                            <input type="button" value="Exportar a Excel" class="boton" onclick="generaReporte(2,document.forms.formax.agregados,{$reporte})"/>
                                            {else}
											<input type="button" value="Generar reporte" class="boton" onclick="generaReporteConta(1,{$reporte})"/>
                                            <input type="button" value="Exportar a Excel" class="boton" onclick="generaReporteConta(2,{$reporte})"/>
                                            {/if}
								</td>
<!--Fin de botonera-->
								
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<td class="lines" height="1"><img src="{$imgpathmaster}/space.gif" alt="" width="1" height="1"></td>
				</tr>
			</table>

 </div>
</div>
 </div>
 </div>
                                            {literal}
                                                <script type="text/javascript" language="javascript">
                                                				function cambiaCampos(combo, campo){
																	if(combo.name!='ejercicios')
																		if(combo.name=='nivelSuperior'){
																			if(document.getElementById("tipos").value=='1')
																				return true;
																			comboRequest('../ajax/camposTablas.php?tabla='+combo.value+'&opcion=conta', campo);
																		}else
																			comboRequest('../ajax/camposTablas.php?tabla='+combo.value, campo);
																	else
																		comboRequest('../ajax/camposTablas.php?tabla='+combo.name+'&ejercicio='+combo.value+'&opcion=conta', campo);
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
																
																function comboRequest (url, campo) {
																	var ret = ajaxR (url);
																	var valores = "";
																	
																	var opciones=ret.split('|');
																	var combo = document.getElementById(campo); 
																	combo.options.length=0;
																	
																	for(var i=1; i<=opciones.length; i++)
																	{				
																		//opciones de llenado para los generales
																		if(opciones[i])
																		{
																			valores= opciones[i].split('~');
																			combo.options[i-1]=new Option(valores[1],valores[0]);
																		}
																		else
																			combo.options[i-1]=new Option('-- Seleccionar --','');
																	}
																	combo.options.length--;
																}
                                                                

                                                                function makeRequest(url) {
                                                                    http_request = false;
                                                                    if (window.XMLHttpRequest) { // mozilla, netscape, opera...
                                                                        http_request = new XMLHttpRequest();
                                                                        if (http_request.overrideMimeType) {
                                                                            http_request.overrideMimeType('text/xml');
                                                                        }
                                                                    } else if (window.ActiveXObject) { // IE
                                                                        try {
                                                                            http_request = new ActiveXObject("Msxml2.XMLHTTP");
                                                                        } catch (e) {
                                                                            try {
                                                                                http_request = new ActiveXObject("Microsoft.XMLHTTP");
                                                                            } catch (e) {
                                                                            }
                                                                        }
                                                                    }
                                                                    if (!http_request) {
                                                                        alert('Falla :( No es posible crear una instancia XMLHTTP');
                                                                        return false;
                                                                    }
                                                                    http_request.onreadystatechange = cambiaCombo;
                                                                    http_request.open('GET', url, true);
                                                                    http_request.send(null);
                                                                }

                                                                function cambiaCombo() {
                                                                    var valores = '';
                                                                    if (http_request.readyState == 4) {
                                                                        if (http_request.status == 200) {

                                                                            var opciones = http_request.responseText.split('|');
                                                                            document.getElementById('campo').options.length = 0;

                                                                            for (var i = 1; i <= opciones.length; i++)
                                                                            {
                                                                                //opciones de llenado para los generales
                                                                                if (opciones[i])
                                                                                {
                                                                                    valores = opciones[i].split('~');
                                                                                    document.getElementById('campo').options[i - 1] = new Option(valores[1], valores[0]);
                                                                                }
                                                                                else
                                                                                    document.getElementById('campo').options[i - 1] = new Option('-- Seleccionar --', '');
                                                                            }
                                                                            document.getElementById('campo').options.length--;
                                                                        }
                                                                        else
                                                                        {
                                                                            alert('Hubo problemas con la petici�n.');
                                                                        }
                                                                    }
                                                                }

                                                                function actualizaComboEditable() {
                                                                    var tabla = document.forms.formax.tabla.value;
                                                                    var campo = document.forms.formax.campo.value;
                                                                    ajax_request('POST', 'text', '../inc/ajax/opcionesComboEditable.php', 'tabla=' + tabla + '&campo=' + campo, 'actualizaComboEditable2(ans)');
                                                                    return true;
                                                                }

                                                                function actualizaComboEditable2(ans) {
                                                                    var arr = eval(ans);
                                                                    var pos = 2;
                                                                    document.getElementById('e').options.length = 2;
                                                                    for (var i = 0; i < arr.length; i++)
                                                                        document.getElementById('e').options[eval(pos + i)] = new Option(arr[i][1], arr[i][0]);
                                                                    inicializaCombo();
                                                                    return true;
                                                                }

                                                                function agregaCriterio(objform) {
                                                                    var tabla = document.getElementById('tabla').value;

                                                                    var indiceSeleccionadoTab = objform.tabla.selectedIndex;
                                                                    var tablaNombre = objform.tabla.options[indiceSeleccionadoTab].text;


                                                                    if (tabla == 0) {
                                                                        alert("Debe seleccionar una tabla.");
                                                                        return false;
                                                                    }
                                                                    var campo = document.getElementById('campo').value;
                                                                    //document.frm.elemTec.selectedIndex

                                                                    var indiceSeleccionado = objform.campo.selectedIndex;
                                                                    var texto = objform.campo.options[indiceSeleccionado].text;

                                                                    //document.frm.elemTec.options[indice].text

                                                                    //alert(' campo '+ campo);
                                                                    //alert(' texto '+ texto);

                                                                    if (campo == '') {
                                                                        alert("Debe seleccionar un campo a agregar.");
                                                                        return false;
                                                                    }
                                                                    var criterio = document.getElementById('criterio').value;

                                                                    //alert(' criterio '+ criterio);

                                                                    if (criterio == '') {
                                                                        alert("Debe seleccionar un criterio de b�squeda.");
                                                                        return false;
                                                                    }

                                                                    var valor = document.getElementById('valor').value;

                                                                    //alert(' valor '+ valor);

                                                                    if (valor == '' && criterio < 12) {
                                                                        alert("Especif�que un valor.");
                                                                        return false;
                                                                    }
                                                                    switch (criterio) {
                                                                        case '0':
                                                                            criterio = "= '" + valor + "'";
                                                                            break;
                                                                        case '1':
                                                                            criterio = "!= '" + valor + "'";
                                                                            break;
                                                                        case '2':
                                                                            criterio = "> '" + valor + "'";
                                                                            break;
                                                                        case '3':
                                                                            criterio = "< '" + valor + "'";
                                                                            break;
                                                                        case '4':
                                                                            criterio = ">= '" + valor + "'";
                                                                            break;
                                                                        case '5':
                                                                            criterio = "<= '" + valor + "'";
                                                                            break;
                                                                        case '6':
                                                                            criterio = "LIKE '" + valor + "*'";
                                                                            break;
                                                                        case '7':
                                                                            criterio = "NOT LIKE '" + valor + "*'";
                                                                            break;
                                                                        case '8':
                                                                            criterio = "LIKE '*" + valor + "'";
                                                                            break;
                                                                        case '9':
                                                                            criterio = "NOT LIKE '*" + valor + "'";
                                                                            break;
                                                                        case '10':
                                                                            criterio = "LIKE '*" + valor + "*'";
                                                                            break;
                                                                        case '11':
                                                                            criterio = "NOT LIKE '*" + valor + "*'";
                                                                            break;
                                                                        case '12':
                                                                            criterio = "IS NULL";
                                                                            break;
                                                                        case '13':
                                                                            criterio = "IS NOT NULL";
                                                                            break;
                                                                        default:
                                                                            criterio = "= '" + valor + "'";
                                                                    }

                                                                    //alert(' criterio 2 '+ criterio);

                                                                    var limit = document.forms.formax.limit;

                                                                    //alert(' limit '+ limit);

                                                                    var numRegs = document.getElementById('agregados').options.length;

                                                                    if (numRegs > 0) {
                                                                        if (limit[0].checked == true)
                                                                            andor = limit[0].value;
                                                                        else
                                                                            andor = limit[1].value;
                                                                    } else
                                                                        andor = "";

                                                                    var cadena = " " + andor + " " + tabla + "." + campo + " " + criterio;

                                                                    var cadDesp = " " + andor + " " + tablaNombre + "." + texto + " " + criterio;

                                                                    //alert(' cadena '+ cadena);

                                                                    //var cadDesp = cadena;
                                                                    /*	cadDesp = cadDesp.replace("OR","O");
                                                                     cadDesp = cadDesp.replace("AND","Y");
                                                                     cadDesp = cadDesp.replace("LIKE '%","CONTIENE A '");
                                                                     cadDesp = cadDesp.replace("LIKE '","EMPIEZA CON '");
                                                                     cadDesp = cadDesp.replace("NOT LIKE '%","NO CONTIENE A '");
                                                                     cadDesp = cadDesp.replace("NOT LIKE '","NO EMPIEZA CON '");
                                                                     cadDesp = cadDesp.replace("%'","'");*/

                                                                    if (numRegs == 0)
                                                                        cadena = "AND (" + cadena;

                                                                    //document.getElementById('tabla').value = 0;
                                                                    //makeRequest('../ajax/camposTablas.php?tabla='+document.getElementById('tabla').value);

                                                                    document.getElementById('criterio').selectedIndex = 0;
                                                                    document.getElementById('valor').value = '';

                                                                    document.getElementById('agregados').options[numRegs++] = new Option(cadDesp, cadena);

                                                                    //alert(cadDesp);

                                                                    return true;
                                                                }

                                                                function trashElements(FromCombo) {
                                                                    var to_remove_counter = 0;
                                                                    for (var i = 0; i < FromCombo.options.length; i++) {
                                                                        if (FromCombo.options[i].selected == true) {
                                                                            FromCombo.options[i].selected = false;
                                                                            ++to_remove_counter;
                                                                        } else {
                                                                            FromCombo.options[i - to_remove_counter].selected = false;
                                                                            FromCombo.options[i - to_remove_counter].text = FromCombo.options[i].text;
                                                                            FromCombo.options[i - to_remove_counter].value = FromCombo.options[i].value;
                                                                        }
                                                                    }
                                                                    //now cleanup the last remaining options
                                                                    var numToLeave = FromCombo.options.length - to_remove_counter;
                                                                    for (i = FromCombo.options.length - 1; i >= numToLeave; i--) {
                                                                        FromCombo.options[i] = null;
                                                                    }
                                                                }

                                                                function SelectAll(combo) {
                                                                    for (var i = 0; i < combo.options.length; i++) {
                                                                        combo.options[i].selected = true;
                                                                    }
                                                                }

                                                                function colocaValor(valor) {
                                                                    document.getElementById('valor').value = valor;
                                                                    return true;
                                                                }

                                                                function generaReporte(opcion, combo, idRep)
                                                                {
                                                                    var cadena = "";
                                                                    var cadena2 = "";
                                                                    var opPagoRegistro = "";

                                                                    for (var i = 0; i < combo.options.length; i++)
                                                                    {

                                                                        if (idRep != 1 && idRep >= 6 && idRep <= 7)
                                                                        {

                                                                            //var arr=combo.options[i].value.split(".");

                                                                            //alert(arr[0]+" "+arr[1])

                                                                            //var arr2=arr[1].split(" ");

                                                                            //alert(arr2[0]+" -  "+arr2[1]+"  - "+arr2[2])

                                                                            if (combo.options[i].value.indexOf("sias_empresas") != -1 || combo.options[i].value.indexOf("sias_estados") != -1 || combo.options[i].value.indexOf("sias_ciudades") != -1)
                                                                            {
                                                                                //colocamos en una variable la cadena
                                                                                var cad = combo.options[i].value;
                                                                                //encontramos la primera posicion del punto para separar la tabla de la cadena
                                                                                var indice = cad.indexOf('.');
                                                                                //obtenemos la primera cadena antes del punto 
                                                                                var cadA = cad.substring(0, indice);
                                                                                //de la cadena a obtenemos la ultima posicion del punto 
                                                                                var indice2 = cadA.lastIndexOf(' ');
                                                                                //partimos la cadena en A1 y en A2
                                                                                var cadA1 = cadA.substring(0, indice2);
                                                                                //partimos la segunda cadena
                                                                                var cadA2 = cadA.substring(indice2 + 1, cadA.length);
                                                                                //obtenemos la segunda cadena despues del punto
                                                                                var cadB = cad.substring(indice + 1, cad.length);
                                                                                //formamos todas las variables
                                                                                var cadenaoriginal = cadA1 + " (" + cadA2 + "." + cadB + " or " + cadA2 + "_2." + cadB + ")";

                                                                                cadena += cadenaoriginal + "~";
                                                                            }
                                                                            else
                                                                                cadena += combo.options[i].value + "~";

                                                                        }
                                                                        else
                                                                            cadena += combo.options[i].value + "~";
                                                                        //vemos el numero de reporte y enviamos dos o n wheres dependiendo del reporte



                                                                    }
                                                                    //alert(cadena);
                                                                    var opcionales = "";
                                                                    //	Fecha de corte
                                                                    if (document.forms.formax.fechaCorte)
                                                                    {
                                                                        opcionales += (document.forms.formax.fechaCorte.value + "|");
                                                                    }
                                                                    //	Versi�n
                                                                    if (document.forms.formax.version)
                                                                        opcionales += (document.forms.formax.version.value + "|");
                                                                    //	Fecha 1
                                                                    if (document.forms.formax.fecha1)
                                                                    {
                                                                        if (document.forms.formax.fecha1.value == '')
                                                                        {
                                                                            alert("La fecha Inicial es requerida.")
                                                                            document.forms.formax.fecha1.focus();
                                                                            return false;
                                                                        }
                                                                        opcionales += (document.forms.formax.fecha1.value + "|");
                                                                    }
                                                                    //	Fecha 2
                                                                    if (document.forms.formax.fecha2)
                                                                    {

                                                                        if (document.forms.formax.fecha2.value == '')
                                                                        {
                                                                            alert("La fecha Final es requerida.")
                                                                            document.forms.formax.fecha2.focus();
                                                                            return false;
                                                                        }

                                                                        opcionales += (document.forms.formax.fecha2.value + "|");
                                                                    }
                                                                    if (document.forms.formax.fecha3)
                                                                        opcionales += (document.forms.formax.fecha3.value + "|");
                                                                    if (document.forms.formax.fecha4)
                                                                        opcionales += (document.forms.formax.fecha4.value + "|");
                                                                    if (document.forms.formax.maximo)
                                                                        opcionales += (document.forms.formax.maximo.value + "|");
                                                                    if (document.forms.formax.anio)
                                                                        opcionales += (document.forms.formax.anio.value + "|");
                                                                    if (document.forms.formax.razon_social)
                                                                        opcionales += (document.forms.formax.razon_social.value + "|");

                                                                    if (document.forms.formax.opPagoRegistro)
                                                                    {
                                                                        var opcionPR = document.forms.formax.opPagoRegistro;
                                                                        if (opcionPR[0].checked == true)
                                                                            opPagoRegistro = opcionPR[0].value;
                                                                        else if (opcionPR[1].checked == true)
                                                                            opPagoRegistro = opcionPR[1].value;
                                                                        else
                                                                            opPagoRegistro = 0;
                                                                    }

                                                                    if (document.forms.formax.estado)
                                                                    {
                                                                        opcionales += document.forms.formax.estado.value + "|";
                                                                    }

                                                                    if (document.forms.formax.tipo_cliente)
                                                                    {
                                                                        opcionales += document.forms.formax.tipo_cliente.value + "|";
                                                                    }

                                                                    orden = "";
                                                                    var objOrden = document.getElementById("orden");
                                                                    if (objOrden)
                                                                        orden = "&orden=" + objOrden.value;


                                                                    var centroAncho = (screen.width / 2) - 400;
                                                                    var centroAlto = (screen.height / 2) - 300;
                                                                    var especificaciones = "top=" + centroAlto + ",left=" + centroAncho + ",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,width=800,height=600,resizable=yes"
                                                                    var titulo = "ventanaEmergente"
                                                                    //Campo de Delegacion
                                                                    var delegacion = document.getElementById('delegacion').value;


                                                                    window.open("procesaReportes.php?parametros=" + cadena + "&opcion=" + 
                                                                            opcion + "&sucUsuario=" + document.getElementById('sucUsuario').value + 
                                                                            "&delegacion=" + delegacion+
                                                                            "&idRep=" + idRep + "&opcionales=" + opcionales + "&titulo=" + document.forms.formax.titulo.value + "&opPagoRegistro=" + opPagoRegistro + orden, "_blank", especificaciones);

                                                                    //vamos a ver si ya existe la ventana abierta
                                                                    //alert("abriendo ventana");
                                                                    return true;
                                                                }


                                                                function cambiaAtributoMultiple(radio) {
                                                                    var comboMultiple = document.forms.formax.periodos;
                                                                    if (radio.value == 'porperiodo')
                                                                        comboMultiple.multiple = false;
                                                                    else
                                                                        comboMultiple.multiple = true;
                                                                    return true;
                                                                }
                                                </script>
                                            {/literal}

                                            {if (($reporte ge 1 and $reporte le 78) or  $reporte eq 104) or ($reporte ge 100 and $reporte le 101) or $reporte eq 103}
                                                {literal}
                                                    <script type="text/javascript">
                                                        var to = null;
                                                        function actPrefiltro(evento, texto)
                                                        {
                                                            if (evento.keyCode === 27 || texto.value === '')
                                                                return false;
                                                            var objValor = document.getElementById("valor");
                                                            if (!objValor)
                                                                return false;
                                                            var objTabla = document.getElementById("tabla");
                                                            if (!objTabla)
                                                                return false;
                                                            var objCampo = document.getElementById("campo");
                                                            if (!objCampo)
                                                                return false;
                                                            if (objTabla.value == 0 || objCampo.value == 0)
                                                                return false;
                                                            clearTimeout(to);
                                                            to = setTimeout('busqPrefiltro("' + objTabla.value + '","' + objCampo.value + '","' + objValor.value + '")', 300);
                                                        }

                                                        function busqPrefiltro(tabla, campo, valor)
                                                        {
                                                            var resp = ajaxR('../ajax/opcionesComboEditable.php?tabla=' + tabla + '&campo=' + campo + '&valor=' + valor);
                                                            var arrResp = resp.split("|");
                                                            cad = "";
                                                            var objValores = document.getElementById("valores");
                                                            if (!objValores)
                                                                return false;
                                                            for (var i = 0; i < arrResp.length; i++)
                                                            {
                                                                cad += "<div class='resultado' onclick='selecciona(this)'>" + arrResp[i] + "</div>";
                                                            }
                                                            objValores.innerHTML = cad;
                                                        }

                                                        function selecciona(div)
                                                        {
                                                            var objValor = document.getElementById("valor");
                                                            if (!objValor)
                                                                return false;
                                                            objValor.value = div.innerHTML;
                                                            var objValores = document.getElementById("valores");
                                                            if (!objValores)
                                                                return false;
                                                            objValores.innerHTML = "";
                                                        }
                                                    </script>
                                                    <script type="text/javascript">
                                                        /*
                                                         $.ajaxSetup({ type: "POST" });
                                                         var to = null;
     
                                                         $('#agregar').click(function(){
                                                         $('#valores div').remove();
                                                         $('#valores').hide();
                                                         });
     
                                                         $('#valor').keyup(function(e){
                                                         if(e.keyCode===27 || this.value==='') return;
                                                         if($('#tabla').attr('value')==0 && $('#campo').attr('value')==0) return;
                                                         clearTimeout(to);
                                                         to = setTimeout("$.getJSON('../inc/ajax/opcionesComboEditable.php',{tabla:$('#tabla').attr('value'),campo:$('#campo').attr('value'),valor:$('#valor').attr('value')},despliega_valores)",300);
                                                         });
     
                                                         $('#flecha').click(function(){
                                                         if($('#valores div').length<1) return;
                                                         if($('#valores').css('display')!='none'){
                                                         $('#valores').hide();
                                                         }else{
                                                         $('#valores').show();
                                                         $(document).keyup(onESC_hide);
                                                         }
                                                         });
     
                                                         function despliega_valores(json){
                                                         $('#valores').empty().hide();
                                                         if(json.length==1 && json[0]=='') return;
                                                         for(var i=0,r;r=json[i];i++){
                                                         $('#valores').append($('<div class="resultado">'+r+'</div>').click(selecciona).hover(function(){ $(this).addClass('sobre'); },function(){ $(this).removeClass('sobre'); }));
                                                         }
                                                         $('#valores').show();
                                                         $(document).keyup(onESC_hide);
                                                         }
     
                                                         function onESC_hide(e){
                                                         if(e.keyCode==27){
                                                         $('#valores').hide();
                                                         $(document).unbind('keyup',onESC_hide);
                                                         }
                                                         return false;
                                                         }
     
                                                         function selecciona(){
                                                         $('#valor').attr('value',$(this).html());
                                                         $('#valores').hide();
                                                         }*/
                                                    </script>
                                                {/literal}
                                            {else}
                                                {literal}
                                                    <script>
                                                        $.ajaxSetup({type: "POST"});

                                                        $('#searchField').keyup(searchLevel);

                                                        function selectLevel(obj) {
                                                            var nivel_actual = $(obj).parents('div').attr('id').match(/[0-9]+/);
                                                            var cuenta_seleccionada = $(obj).siblings('select').val();
                                                            $.getJSON('../ajax/nivelesCC.php', {cuenta: cuenta_seleccionada, nivel: nivel_actual}, subLevel);
                                                        }

                                                        function subLevel(json) {
                                                            if (parseInt(json.numhijas) > 1 && $('#nivel_' + json.nivel + ' div').length < 1) {
                                                                var nuevoNivelID = 'nivel_' + (json.nivel + 1);
                                                                $('#nivel_' + json.nivel).clone(true).attr('id', nuevoNivelID).appendTo($('#nivel_' + json.nivel));
                                                                var nuevoCombo = $('#' + nuevoNivelID + ' select');
                                                                $(nuevoCombo).empty();
                                                                var nuevoInput = $('#' + nuevoNivelID + ' input[@type="text"]:eq(0)');
                                                                $(nuevoInput).keyup(searchLevel);
                                                                $('#nivel_' + json.nivel + ' input[@type="text"]:eq(0)').val($('#nivel_' + json.nivel + ' input[@type="text"]:eq(0)').siblings('select').val());
                                                                for (var i = 0; i < parseInt(json.numhijas); i++)
                                                                    nuevoCombo[0].options[i] = new Option(json.hijas[i][1], json.hijas[i][0]);
                                                            }
                                                            return;
                                                        }

                                                        function selectOption(obj) {
                                                            if ($(obj).val() == '-- Todas --') {
                                                                $(obj).siblings('input[@type="text"]:eq(0)').val("");
                                                                $(obj).siblings('div').remove();
                                                            } else {
                                                                $(obj).siblings('div').remove();
                                                                $(obj).siblings('input[@type="text"]:eq(0)').val($(obj).val());
                                                                selectLevel(obj);
                                                            }
                                                            return;
                                                        }

                                                        function searchLevel() {
                                                            $(this).siblings('div').remove();
                                                            $(this).siblings('select').val($(this).val());
                                                        }
                                                    </script>
                                                {/literal}
                                            {/if}


                                            {if $reporte eq 82 or $reporte eq 80 or $reporte eq 84 or $reporte eq 85}
<script language="javascript" type="text/javascript">
	var funcion_excepcion="cambiaCampos(document.forms.formax.ejercicios);";
</script>
                                            {elseif $reporte eq 81 or $reporte eq 83}
<script language="javascript" type="text/javascript">
	var funcion_excepcion="cambiaCampos(document.forms.formax.ejercicios);";
</script>
                                            {else}
<script language="javascript" type="text/javascript">
	var funcion_excepcion="";
</script>
                                            {/if}


                                            {include file="_footer.tpl" aktUser=$username}
