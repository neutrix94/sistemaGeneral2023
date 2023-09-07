// JavaScript Document


var posgen;
var ultfilaed;
function CargaGrid(NomId)
{
//alert(NomId);
	var tipos = new Array();
	var mods = new Array();
	var lons = new Array();
	var mask = new Array();
	var formulas = new Array();
	var opc="";
	var alins = new Array();
	var valH=new Array();
	var cab= new Array();
	var noms = new Array();
	var getCom= new Array();
	var depen = new Array();
	var htmls= new Array();
	var funcam= new Array();
	var combolargo= new Array();
	var SumGral= new Array();
	var verSum = new Array();
	var valida = new Array();
	var onkey = new Array();
	var vini = new Array();
	var oclicks = new Array();
	var dblclick = new Array();
	var camposbd = new Array();
	var valLib = new Array();
	var depenMulti = new Array();
	var datos="";
	var htmldb =new Array();
	var multiseleccion =new Array();
	var numMos=0;
	var numDatos=0;
	var numPag=0;
	
	
	
	//Obtenemos la tabla
	var Tabla=document.getElementById(NomId);
	if(!Tabla)
	{
		alert('No se encontro la tabla: '+NomId);
		return false;
	}
	
	var cellP=Tabla.cellpadding?Tabla.cellpadding:Tabla.getAttribute("cellpadding");
	var cellS=Tabla.cellspacing?Tabla.cellspacing:Tabla.getAttribute("cellspacing");
	var borde=Tabla.border?Tabla.border:Tabla.getAttribute("border");
	var brcolor=Tabla.bordercolor?Tabla.bordercolor:Tabla.getAttribute("bordercolor");
	var AltoGral=Tabla.Alto?Tabla.Alto:Tabla.getAttribute("Alto");
	var alto=Tabla.AltoCelda?Tabla.AltoCelda:Tabla.getAttribute("AltoCelda");
	var conScroll=Tabla.conScroll?Tabla.conScroll:Tabla.getAttribute("conScroll");
	var ruta=Tabla.ruta?Tabla.ruta:Tabla.getAttribute("ruta");
	var validaElimina=Tabla.validaElimina?Tabla.validaElimina:Tabla.getAttribute("validaElimina");
	var posElimina=Tabla.posElimina?Tabla.posElimina:Tabla.getAttribute("posElimina");
	var validaNuevo=Tabla.validaNuevo?Tabla.validaNuevo:Tabla.getAttribute("validaNuevo");	
	var DatosFile=Tabla.Datos?Tabla.Datos:Tabla.getAttribute("Datos");
	var verFooter=Tabla.verFooter?Tabla.verFooter:Tabla.getAttribute("verFooter");
	var scrollH=Tabla.scrollH?Tabla.scrollH:Tabla.getAttribute("scrollH");
	var bloqueoDatos=Tabla.bloqueoDatos?Tabla.bloqueoDatos:Tabla.getAttribute("bloqueoDatos");
	var paginador=Tabla.paginador?Tabla.paginador:Tabla.getAttribute("paginador");
	var datosxPag=Tabla.datosxPag?Tabla.datosxPag:Tabla.getAttribute("datosxPag");
	datosxPag=isNaN(parseFloat(datosxPag))?10:parseFloat(datosxPag);
	var pagMetodo=Tabla.pagMetodo?Tabla.pagMetodo:Tabla.getAttribute("pagMetodo");
	pagMetodo=(pagMetodo == '')?'javascript':pagMetodo;	
	var ordenaPHP=Tabla.ordenaPHP?Tabla.ordenaPHP:Tabla.getAttribute("ordenaPHP");
	
	

	//Modificamos el largo de la tabla cuando hay scroll Horizontal
	if(scrollH == 'S')
	{
		if(/*navigator.appName == 'Microsoft Internet Explorer'*/1)
		{
			var widthAnt=Tabla.width?Tabla.width:Tabla.getAttribute("width");			
			var widthNew=parseInt(widthAnt)+63;			
			Tabla.width=widthNew;			
			Tabla.setAttribute('width', widthNew);
		}		
	}
		
	//Obtenemos las filas
	var Trs=Tabla.getElementsByTagName('tr');
	Trs[0].height=17;
	Trs[0].setAttribute("height",17);			
	var Tds=Trs[0].getElementsByTagName('td');
	for(var j=0;j<Tds.length;j++)
	{
		var texto=Tds[j].innerHTML;
		valH[j]=texto;
		if(navigator.appName == 'Microsoft Internet Explorer')
			var largo=Tds[j].offsetWidth?Tds[j].offsetWidth:Tds[j].getAttribute('offsetWidth');
		else	
			var largo=Tds[j].width?Tds[j].width:Tds[j].getAttribute('width');
		if(j == 0)
			opc=texto;
		lons[j]=largo-2;
		noms[j]=texto;		
		tipos[j]=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");		
		mods[j]=Tds[j].modificable?Tds[j].modificable:Tds[j].getAttribute("modificable");
		mask[j]=Tds[j].mascara?Tds[j].mascara:Tds[j].getAttribute("mascara");
		alins[j]=Tds[j].align?Tds[j].align:Tds[j].getAttribute("align");
		formulas[j]=Tds[j].formula?Tds[j].formula:Tds[j].getAttribute("formula");
		getCom[j]=Tds[j].datosdb?Tds[j].datosdb:Tds[j].getAttribute("datosdb");
		depen[j]=Tds[j].depende?Tds[j].depende:Tds[j].getAttribute("depende");
		funcam[j]=Tds[j].onChange?Tds[j].onChange:Tds[j].getAttribute("onChange");
		combolargo[j]=Tds[j].combolargo?Tds[j].combolargo:Tds[j].getAttribute("combolargo");
		verSum[j]=Tds[j].verSumatoria?Tds[j].verSumatoria:Tds[j].getAttribute("verSumatoria");
		valida[j]=Tds[j].valida?Tds[j].valida:Tds[j].getAttribute("valida");
		onkey[j]=Tds[j].onKey?Tds[j].onKey:Tds[j].getAttribute("onKey");
		vini[j]=Tds[j].inicial?Tds[j].inicial:Tds[j].getAttribute("inicial");
		oclicks[j]=Tds[j].inicial?Tds[j].inicial:Tds[j].getAttribute("on_Click");				
		dblclick[j]=Tds[j].dobleClick?Tds[j].dobleClick:Tds[j].getAttribute("dobleClick"); 
		htmldb[j] =Tds[j].htmldebase?Tds[j].htmldebase:Tds[j].getAttribute("htmldebase");//MS
		multiseleccion[j] =Tds[j].multiseleccion?Tds[j].htmldebase:Tds[j].getAttribute("multiseleccion");//MS
		camposbd[j]=Tds[j].campoBD?Tds[j].campoBD:Tds[j].getAttribute("campoBD");
		valLib[j]=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute("valor");
		depenMulti[j]=Tds[j].multidependencia?Tds[j].multidependencia:Tds[j].getAttribute("multidependencia");
		
		SumGral[j]=0;
		if(tipos[j] == 'libre')		
		{			
			cab[j]='<input type="button" value="'+valLib[j]+'" style="width:'+(largo)+'px; height:20px;" class="buttonHeader" id="H'+NomId+j+'" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';			
			valH[j]=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute("valor");						
			var pi=Tds[j].innerHTML.toLowerCase().indexOf('onclick=');			
			var axc=Tds[j].innerHTML.substring(pi+8, Tds[j].innerHTML.length);						
			pi=axc.indexOf(' ')!=-1?pi=axc.indexOf(' '):pi=axc.indexOf('>');
			axc=axc.substring(0,pi);			
			if(axc.substring(0,1) == "'" || axc.substring(0,1) == '"')
				axc=axc.substring(1,axc.length);
			if(axc.substring(axc.length-1,axc.length) == "'" || axc.substring(axc.length-1,axc.length) == '"')
				axc=axc.substring(0,axc.length-1);			
			//alert("["+axc+"] en:\n"+Tds[j].innerHTML+"  =\n"+Tds[j].innerHTML.indexOf(axc));
			axcaux=axc;
			while(axcaux.indexOf("'") != -1)
				axcaux=axcaux.replace("'", "_CS_");
			while(axcaux.indexOf('"') != -1)	
				axcaux=axcaux.replace('"', '_CD_');
			//alert(axc);
			htmls[j]=Tds[j].innerHTML.replace(axc, "clickLibre('"+NomId+"', '"+axcaux+"')");
			/*alert(htmls[j]);	
			return false;*/

			while(htmls[j].indexOf('""') != -1)
				htmls[j]=htmls[j].replace('""','"');						
			//alert("["+htmls[j]+"]");	
		}
		else if(tipos[j] == 'oculto')
		{
			cab[j]="";			
			lons[j]=0;
		}
		else if(tipos[j] == 'binario'&&multiseleccion[j]=="S")//MS
		{
			cab[j]='<input type="button" value="'+texto+'" style="width:'+(largo)+'px; height:20px;" class="buttoncheckfalse" id="H'+NomId+j+'" onclick="multiselecciona('+(j+1)+', \''+NomId+'\');" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';
		}
		else
			cab[j]='<input type="button" value="'+texto+'" style="width:'+(largo)+'px; height:20px;" class="buttonHeader" id="H'+NomId+j+'" onclick="ordena('+(j+1)+', \''+NomId+'\');" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';
					
	}	
	/*Insertamos la nueva Fila que contendra la cabecera*/	
	var caux="";
	caux+='<table cellpadding="'+cellP+'" cellspacing="'+cellS+'" id="Head_'+NomId+'" style="border:none">'+"\n\t"+'<tr height="20" style="border:none">'+"\n";	
	caux+="\t\t"+'<td align="center">'+"\n\t\t\t"+'<input type="button" value="No" style="width:25px; height:20px;" class="buttonHeader">'+"\n";
	caux+="\t\t\t"+'<input type="hidden" name="fc" id="'+NomId+'_aux_fun" value="0">'+"\n\t\t"+'</td>';
	for(j=0;j<Tds.length;j++)
	{	
		
		caux+="\n\t\t"+'<td datosdb="'+getCom[j]+'" depende="'+depen[j]+'" tipo="'+tipos[j]+'" modificable="'+mods[j]+'" mascara="'+mask[j]+'" on_click="'+oclicks[j]+'"';
		if(tipos[j] == 'libre')
		{
			var htaux=htmls[j];
			while(htaux.indexOf('"') != -1)
				htaux=htaux.replace('"', '_COMA_');
			caux+='vht="'+htaux+'"';
		}
		caux+='onchange="'+funcam[j]+'" verSumatoria="'+verSum[j]+'" combolargo="'+combolargo[j]+'" width="'+lons[j]+'" valida="'+valida[j]+'" onKey="'+onkey[j]+'" campoBD="'+camposbd[j]+'"';
		caux+=' inicial="'+vini[j]+'" formula="'+formulas[j]+'" align="'+alins[j]+'" id="H_'+NomId+j+'" style="border:none" height="20" valor="'+valH[j]+'" dobleClick="'+dblclick[j]+'" htmldebase="'+htmldb[j]+'" multidependencia="'+depenMulti[j]+'">';
		caux+="\n\t\t\t"+cab[j];		
		caux+='\n\t\t</td>';
	}
	
	/*Agregamos la columan para eliminar en caso de ser necesario*/
	if(validaElimina != 'false')
		caux+='<td align="center" width="15px" tipo="eliminador" modificable="S" id="H_'+NomId+j+'" style="border:none" height="20" valor="deleteRow"><input type="button" value="" style="width:16px; height:20px;" class="buttonHeader"></td>';
	
	caux+='</tr></table>';
	//Obtenemos los datos
	
	var altomenos=20;
	if(verFooter == "S")
		altomenos=40;
	if(paginador == 'S' || paginador == 's')	
		altomenos+=25;
	if(conScroll == 'S')
	{
		datos='<div id="div_'+NomId+'_overflow_Y" style="overflow:auto; height:'+(AltoGral-altomenos)+'px; padding:0;width:100%;" >';
		var onlyDiv='<div id="div_'+NomId+'_overflow_Y" style="overflow:auto; height:'+(AltoGral-altomenos)+'px; padding:0"></div>';
	}
	datos+='<table cellpadding="'+cellP+'" border="'+borde+'" bordercolor="'+brcolor+'" cellspacing="'+cellS+'" id="Body_'+NomId+'" style="width:100%;">';
	
	//Agregamos desde tabla
	if(DatosFile == null || DatosFile == 'null' || DatosFile.length == 0)
	{	
		for(k=1;k<Trs.length;k++)
		{
			datos+='<tr class="NormalCell" height="'+alto+'" width="100%" onmouseover="selfil(this);" onmouseout="dselfil(this);" class="NormalCell" id="'+NomId+'_Fila'+(k-1)+'">';
			var Tds=Trs[k].getElementsByTagName('td');
			datos+='<td width="21px" class="Contador" align="center">'+k+'</td>';
			for(l=0;l<Tds.length;l++)
			{
				var idcelda=NomId+'_'+l+'_'+(k-1);
				if(verSum[l] == 'S')
				{
					SumGral[l]+=parseFloat(Tds[l].innerHTML);
				}
				datos+='<td width="'+lons[l]+'" height="'+alto+'" ondblclick="analizaDBLCLICK(\''+dblclick[l]+'\', this)"';
				if(bloqueoDatos!="S")
					datos+=' onclick="EditarValor(this);"';



				if(tipos[l] == 'libre')
					datos+=" onmouseover='asignaActivo(this);'";
				else if(tipos[l] == 'oculto')
					datos+=' style="border-left:none; border-left-style:none; border-left-width:0px; border-right:none; border-right-style:none; border-right-width:0px;"';	
				datos+='id="'+idcelda+'" align="'+alins[l]+'" valor="'+Tds[l].innerHTML+'">';
				if(tipos[l] == 'binario')///////////////*******************************************************************
				{
					if(Tds[l].innerHTML == "0")
						var sel="";
					else
						var sel="checked";
					var extra="";
					if(mods[l] != 'S'&&bloqueoDatos!="S")
						extra='disabled';
					datos+='<input type="checkbox" value="SI" '+sel+' onBlur="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');"  class="check" '+extra+'>';
				}				
				else if(tipos[l] == 'libre')
				{					
					if(htmldb[l]!="S")//MS
						datos+=htmls[l];
					else
					{
						datos+=axF[l];
					}
				}///////////////*******************************************************************
				else if(tipos[l] == 'oculto')
						datos+="";
				else
				{
					if(mask[l] != null)
						datos+=Mascara(mask[l], Tds[l].innerHTML);			
					else
						datos+=Tds[l].innerHTML;
				}				
				datos+="</td>";
			}
			if(validaElimina != 'false')
				datos+='<td id="'+NomId+'_'+l+'_'+(k-1)+'" width="15px" align="center"><img id="c'+NomId+'_'+l+'_'+(k-1)+'" src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+NomId+'_'+l+'_'+(k-1)+'\');" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+l+'_'+(k-1)+'\');" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';"></td>';
			datos+="</tr>";
		}		
	}
	//Desde archivo php
	else
	{
		if((paginador == 'S' || paginador == 's') && pagMetodo == 'php')
		{
			if(ordenaPHP == 'S' || ordenaPHP == 's'){
				var datosF=ajaxR(DatosFile+'&ini=0&fin='+datosxPag+'&dxp='+datosxPag+'&orderGRC='+camposbd[0]+'&sentidoOr=Asc&prefiltro=1');
				Tabla.ordenCampo=camposbd[0];
				Tabla.setAttribute('ordenCampo', camposbd[0]);		
				Tabla.sentidoOr='Asc';
				Tabla.setAttribute('sentidoOr', 'Asc');
			}else{
				var datosF=ajaxR(DatosFile+"");//'&ini=0&fin='+datosxPag+'&dxp='+datosxPag
			}
			//var datosF=ajaxR(DatosFile+'&ini=0&fin='+datosxPag+'&dxp='+datosxPag);
			var arrF=datosF.split('|');
			numPag=1;			
			var auxGl=arrF[arrF.length-1];
			auxGl=auxGl.split('~');
			numDatos=parseInt(auxGl[0]);
			numMos=parseInt(auxGl[1]);
			numPag=numDatos/datosxPag;
			arrF.pop();
		}else{
			//alert(DatosFile);//aqui se muestra la url de pantalla de ventas
			var datosF=ajaxR(DatosFile);
			var arrF=datosF.split('|');
		}
		
		
		if(arrF[0] == 'exito')
		{
			var limMos=arrF.length;
			if((paginador == 'S' || paginador == 's') && pagMetodo == 'javascript')
			{
				limMos=datosxPag+1;
				numPag=(arrF.length-1)/datosxPag;				
				numDatos=arrF.length-1;
				if(numDatos < (limMos-1))
				{
					numMos=numDatos;
					limMos=numDatos+1;
				}
				else
					numMos=limMos-1;					
				//alert(limMos+' <> '+numDatos);	
			}
			for(k=1;k<limMos;k++)
			{
				var axF=arrF[k].split('~');
				datos+='<tr class="NormalCell" height="'+alto+'" onmouseover="selfil(this);" onmouseout="dselfil(this);" class="NormalCell" id="'+NomId+'_Fila'+(k-1)+'">';				
				datos+='<td width="21px" class="Contador" align="center">'+k+'</td>';
				for(l=0;l<axF.length;l++)
				{
					var idcelda=NomId+'_'+l+'_'+(k-1);
					if(verSum[l] == 'S')
					{						
						SumGral[l]+=parseFloat(axF[l]);
					}
					datos+='<td width="'+lons[l]+'" height="'+alto+'" ondblclick="analizaDBLCLICK(\''+dblclick[l]+'\', this)"';
					if(bloqueoDatos!="S")
						datos+=' onclick="EditarValor(this);"';
					if(tipos[l] == 'libre')
						datos+=" onmouseover='asignaActivo(this);'";
					else if(tipos[l] == 'oculto')
						datos+='style="border-left:none;border-right:none;"';
					datos+='id="'+idcelda+'" align="'+alins[l];//MS
					if(tipos[l]!='libre'&&htmldb[l]!="S")
						datos+='" valor="'+axF[l];
					datos+='">';	
					if(tipos[l] == 'binario')//MS
					{
						if(axF[l] == "0")
						{
							var valor=0;
							var sel="";
						}
						else
						{
							var sel="checked";
							var valor=1;
						}
						var extra="";
						if(mods[l] != 'S')
							extra='disabled';
						if(htmldb[l]=="S"&&axF[l]==-1)
							datos+='&nbsp;';
						else		
							datos+='<input type="checkbox" value="SI" '+sel+' onBlur="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" valor='+valor+'  class="check" '+extra+'>';
					}
					else if(tipos[l] == 'libre')//MS
					{							
						if(htmldb[l]!="S")
							datos+=htmls[l];
						else
						{
							datos+=axF[l];
						}
					}
					else if(tipos[l] == 'oculto')
						datos+="";
					else
					{
						if(axF[l].length == 0 || axF[l] == " ")
							datos+='&nbsp;';
						else if(mask[l] != null)
							datos+=Mascara(mask[l], axF[l]);			
						else
							datos+=axF[l];
					}
					datos+="</td>";
				}
				if(validaElimina != 'false')
					datos+='<td id="'+NomId+'_'+l+'_'+(k-1)+'" width="15px" align="center"><img id="c'+NomId+'_'+l+'_'+(k-1)+'" src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+NomId+'_'+l+'_'+(k-1)+'\');" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+l+'_'+(k-1)+'\');" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';"></td>';
				
				datos+="</tr>";
			}
		}
		else
			alert("No se pudo cargar datos desde "+DatosFile+"\nDescripcion del Error:\n\n"+datosF);
	}
	datos+="</table>";	
	if(conScroll == 'S')
		datos+="</div>";
	for(i=(Trs.length-1);i>=0;i--)
		Tabla.deleteRow(i);	
	var newRow = Tabla.insertRow(0);
	var newCell= newRow.insertCell(0);
	newCell.innerHTML=caux;
	newCell.id="celda_"+NomId+"_Cabecera";	
	var newRow = Tabla.insertRow(1);
	var newCell= newRow.insertCell(0);	
	//Metemos solo el div, para sacar el alto del scroll original
	newCell.innerHTML=onlyDiv;
	/*Asignamos el tama?o del overflow original*/	
	
	if(/*navigator.appName == 'Netscape'*/1)
	{		
		var obj=document.getElementById('div_'+NomId+'_overflow_Y');
		var shtmp=obj.scrollHeight?obj.scrollHeight:obj.getAttribute('scrollHeight');
		if(shtmp == 0)
			shtmp = obj.style.height.replace('px', '');
		Tabla.oFO=shtmp;
		Tabla.setAttribute('oFO',shtmp);
		widthAnt=Tabla.offsetWidth?Tabla.offsetWidth:Tabla.getAttribute("offsetWidth");
		if(widthAnt == 0)
			widthAnt=Tabla.width;
		Tabla.scroll_activo="0";
		Tabla.setAttribute('scroll_activo',0);	
		//alert(widthAnt);
	}

	newCell.innerHTML=datos;
	newCell.id="celda_"+NomId+"_Datos";		
	if(verFooter == "S")
	{
		var caux="";
		caux+='<table cellpadding="'+cellP+'" border="'+0+'" bordercolor="'+brcolor+'" cellspacing="'+cellS+'" id="Footer_'+NomId+'" >';
		caux+='<tr height="20" style="border:none"><td>';
		var sbo=2;
		caux+='<input type="button" value=" " style="width:'+(21+sbo)+'px; height:20px;" class="buttonFooter" id="F'+NomId+'NaN" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';
		for(j=0;j<lons.length;j++)
		{
			var valauxbot=" ";
			if(verSum[j] == 'S')
			{
				valauxbot=SumGral[j];
				if(mask[j] != null)
					valauxbot=Mascara(mask[j], ""+SumGral[j]+"");	
			}
			if(tipos[j] != 'oculto')
			caux+='<input type="button" align="left" value="'+valauxbot+'" style="width:'+(lons[j]+sbo)+'px; height:20px;" class="buttonFooter" id="F'+NomId+j+'" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';
		}
		if(validaElimina != 'false')
			caux+='<input type="button" value=" " style="width:'+(15+sbo)+'px; height:20px;" class="buttonFooter" id="F'+NomId+j+'" onkeydown="TeclasEspeciales(event, this, \''+NomId+'_'+(j+1)+'_NaN\');"/>';
		caux+='</td></tr></table>';
		var newRow = Tabla.insertRow(2);
		var newCell= newRow.insertCell(0);	
		newCell.innerHTML=caux;
		newCell.id="celda_"+NomId+"_Footer";
	}	
	
	/*if(paginador == 'S' || paginador == 's')//||NomId=='notasVenta'
	{
		var caux="";		
		var numdatini=(numMos==0)?0:1;
		//var pagini=()
		caux+='<table cellpadding="'+cellP+'" border="'+0+'" bordercolor="'+brcolor+'" style="background:#6c9831;" cellspacing="'+cellS+'" id="Paginador_'+NomId+'" class="paginador_GridRC">';
		caux+='<tr height="25" style="border:none" id="fila_paginador"><td class="mostrar_paginador_GridRC">';		
		caux+='&nbsp;Mostrar:<select class="combo_mostrar_paginador_GridRC" id="combo_paginador_'+NomId+'" onChange="actPaginador(\''+NomId+'\', this.value, 1);">';
		caux+='<option value="10">10</option>';
		caux+='<option value="20">20</option>';
		caux+='<option value="30">30</option>';
		
		var ruta_img="../img/grid/";//cambio de Oscar 31.05.2018 para mostrar imágenes
		caux+='</select>&nbsp;&nbsp;';
		caux+='<td class="prev_paginador_GridRC">';
		caux+='&nbsp;&nbsp;<img class="first_grid" src="'+ruta_img+'first.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';" onclick="firstPaginador(\''+NomId+'\')">&nbsp;';
		caux+='<img class="prev_grid" src="'+ruta_img+'prev.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';" onclick="antPaginador(\''+NomId+'\')">&nbsp;&nbsp;';
		caux+='<td class="paginas_paginador_GridRC" align="center">';
		caux+='&nbsp;&nbsp;P&aacute;gina <span id="pagact_paginador_'+NomId+'">1</span> de ';
		caux+='<span id="numpages_paginador_'+NomId+'">1</span>&nbsp;&nbsp;';
		caux+='<td class="next_paginador_GridRC">';
		caux+='&nbsp;&nbsp;<img class="next_grid" src="'+ruta_img+'next.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';" onclick="sigPaginador(\''+NomId+'\')">&nbsp;';
		caux+='<img class="last_grid" src="'+ruta_img+'last.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';" onclick="lastPaginador(\''+NomId+'\')">&nbsp;&nbsp;';
		caux+='<td class="texo_paginador_'+NomId+'" id="texto_paginador_'+NomId+'" >';
		caux+='&nbsp;&nbsp;Mostrando del '+numdatini+' al '+numMos+' de '+numDatos+' dato(s)';
		caux+='</td></tr></table>';
		if(verFooter == "S")
			var newRow = Tabla.insertRow(3);			
		else		
			var newRow = Tabla.insertRow(2);
			
		newRow.className="paula_23";	
		var newCell= newRow.insertCell(0);	
		newCell.innerHTML=caux;
		newCell.id="celda_"+NomId+"_paginador";
		
		obj=document.getElementById('combo_paginador_'+NomId);
		obj.value=datosxPag;


		obj=document.getElementById('numpages_paginador_'+NomId);
		//alert(numPag+' => '+Math.ceil(numPag));
		obj.innerHTML=Math.ceil(numPag);
		obj=document.getElementById(NomId);
		obj.setAttribute("pagAct", "1");
		obj.setAttribute("datosxPag", datosxPag);		
	}*/
	
	if(ordenaPHP != 'S' && ordenaPHP != 's')
		ordena(1, NomId);	
	
	Tabla=document.getElementById('Body_'+NomId);
	Trs=Tabla.getElementsByTagName('tr');
	for(i=0;i<Trs.length;i++)
	{
		Tds=Trs[i].getElementsByTagName('td');		
		for(j=1;j<Tds.length;j++)
		{			
			if(tipos[j-1] == "formula" || (tipos[j-1] == "oculto" && formulas[j-1] != null && formulas[j-1] != "null" && formulas[j-1].length > 0))
				AplicaFormula(Tds[j]);
			else if(tipos[j-1] == 'combo')
			{				
				if((depen[j-1] == null || depen[j-1] == "null") && i == 0)
				{
					//alert('OK');	
					ObtenDatosCombo('POST', getCom[j-1], '', NomId, j, null);
				}
				else if(depen[j-1] != null && depen[j-1] != "null")
				{
					//alert(noms[j-1]+' => '+depen[j-1]);
					caux=ajaxR(getCom[j-1]+'?iniDB=0&finDB=10000&llave='+(Tds[parseInt(depen[j-1])+1].valor?Tds[parseInt(depen[j-1])+1].valor:Tds[parseInt(depen[j-1])+1].getAttribute('valor')));
					var datos=caux.split('|');
					if(datos[0] == 'exito')
					{
						for(k=2;k<datos.length;k++)
						{
							caux=datos[k].split('~');							
							if(parseInt(caux[0]) == parseInt(Tds[j].valor?Tds[j].valor:Tds[j].getAttribute('valor')))
								Tds[j].innerHTML=caux[1];
						}
					}
				}
			}	
		}
	}
	/*Validamos que con los datos no haya aparecido el scroll*/	
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Tabla=document.getElementById(NomId);
		widthNew=Tabla.offsetWidth?Tabla.offsetWidth:Tabla.getAttribute("offsetWidth");
		if(widthNew == 0)
			widthNew=Tabla.width;
		var obj=document.getElementById('div_'+NomId+'_overflow_Y');
		var shtmp=obj.scrollHeight?obj.scrollHeight:obj.getAttribute('scrollHeight');
		if(shtmp == 0)
			shtmp = obj.style.height.replace('px', '');		
		if((widthNew-widthAnt) < 17 && shtmp > Tabla.oFO)
		{			
			var widthNew=parseInt(widthAnt)+17;			
			Tabla.width=widthNew;
			Tabla.setAttribute('width', widthNew);
			Tabla.scroll_activo='1';
			Tabla.setAttribute('scroll_activo', '1');
			
		}
	}
}


function EditarValor(celda)
{	
	
	var idcelda=celda.id;	
	//alert(idcelda);
	var aux=celda.id.split("_");
	var tabla=aux[0];
	var fila=parseInt(aux[1]);	
	var columna=aux[2];
	ultfilaed=columna;
	var extra="readonly";	
	var cab=document.getElementById(tabla);
	//alert(cab);
	if(!cab)
		return false;
	var tipo=cab.listado?cab.listado:cab.getAttribute('listado');
	
	//alert(tipo);
	if(tipo == 'S' || tipo == 's')
		return false;	
	
	var cab=document.getElementById("H_"+tabla+fila);
	var tipo=cab.tipo?cab.tipo:cab.getAttribute('tipo');
	var modificable=cab.modificable?cab.modificable:cab.getAttribute('modificable');

	//problema con ie no funciona la siguiente declaracion
	var onClick=cab.on_click?cab.on_click:cab.getAttribute('on_Click');
	var onClick=cab.on_Click?cab.on_Click:cab.getAttribute('on_Click');	
	var yreal=yReal(idcelda);
	
	//alert(modificable);
	if(modificable == 'S')
		extra="";
	else
		return false;
		
	
	//alert(onClick);	
	if(onClick != '' && onClick != "null" && onClick != null)
	{
		var clickAux=onClick.replace('#', yreal);	
		//agregado try, catch por error en iexplorer (para variar)
		try
		{
			if(eval(clickAux) == false)
				return false;
		}
		catch(e)
		{}
	}
	
	
	//alert(tipo);
	switch(tipo)
	{
		case 'texto':
		case 'formula':
		case 'entero':
		case 'decimal':
		case 'fecha':
			var valor=celda.innerHTML;		
			if((valor.substring(0,6).toUpperCase()) == '<INPUT'.toUpperCase())
				return false;
			
				valor=celda.valor?celda.valor:celda.getAttribute('valor');
			var largo=celda.offsetWidth-6;
			var alto=celda.offsetHeight-4;
			if(tipo == 'entero')
				extra=extra+' onkeyup="enteros(this);"';
			else if(tipo == 'decimal')
				extra=extra+' onkeyup="decimales(this);"';
			else if(tipo == 'fecha' && modificable == 'S')
				extra=extra+' onfocus="calendario(this);"';	
			if(modificable == 'S')
				celda.innerHTML='<input id="c'+idcelda+'" type="text" style="width:'+(largo-1)+'px; height:'+(alto-1)+'px;" class="celdaTexto" value="'+valor+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" '+extra+'/>';
			else	
				celda.innerHTML='<input id="c'+idcelda+'" type="text" style="width:'+largo+'px; height:'+alto+'px;" class="celdaTextoD" value="'+valor+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" '+extra+'/>';
			obj=document.getElementById('c'+idcelda);						
			obj.focus();			
			break;
		case 'binario':
			//<input name="as" type="checkbox" value="raton" checked>
			obj=document.getElementById('c'+idcelda);
			obj.focus();
			break;
		case 'eliminador':			
			/*if(celda.innerHTML.indexOf('id="NULL"') != -1)
				celda.innerHTML=celda.innerHTML.replace('id="NULL"','id="c'+idcelda+'"');
			else
				celda.innerHTML=celda.innerHTML.replace('id=NULL','id="c'+idcelda+'"');			
			obj=document.getElementById('c'+idcelda);*/
			//obj.focus();
			break;
		case 'combo':			
			if(celda.innerHTML.toLowerCase().indexOf('<select') != -1 || celda.innerHTML.toLowerCase().indexOf('<input') != -1)
				return false;	
			var Headers=document.getElementById('Head_'+tabla);	
			Trs=Headers.getElementsByTagName('tr');
			Tds=Trs[0].getElementsByTagName('td');
			var depende=Tds[fila+1].depende?Tds[fila+1].depende:Tds[fila+1].getAttribute('depende');
			if(depende == null || depende.length == 0 || depende == "null")
			{			
				var Tabla=document.getElementById(tabla);			
				var aux=eval('document.getElementById("'+tabla+'").auxiliar_'+(fila+1)+'?document.getElementById("'+tabla+'").auxiliar_'+(fila+1)+':document.getElementById("'+tabla+'").getAttribute("auxiliar_'+(fila+1)+'")');
				//alert('document.getElementById("'+tabla+'").auxiliar_'+(fila+1)+'?document.getElementById("'+tabla+'").auxiliar_'+(fila+1)+':document.getElementById("'+tabla+'").getAttribute("auxiliar_'+(fila+1)+'")');
				if(aux == null)
				{					
					var file=Tds[fila+1].datosdb?Tds[fila+1].datosdb:Tds[fila+1].getAttribute('datosdb');
										
					var aux=ajaxR(file);
					if(aux.split('|')[0] != 'exito')
						alert(aux);
					eval('document.getElementById("'+tabla+'").auxiliar_'+(fila+1)+'="'+aux+'"');
				}
			}
			else
			{				
				var file=Tds[fila+1].datosdb?Tds[fila+1].datosdb:Tds[fila+1].getAttribute('datosdb');				
				depende=parseInt(depende);
				var fff=document.getElementById(tabla+'_Fila'+columna);
				var filav=fff.rowIndex?fff.rowIndex:fff.getAttribute('rowIndex');
				if(filav == null)
					filav=0;				
				var dats=document.getElementById('Body_'+tabla);	
				Trs=dats.getElementsByTagName('tr');				
				Tds=Trs[filav].getElementsByTagName('td');				
				var val=Tds[depende+1].valor?Tds[depende+1].valor:Tds[depende+1].getAttribute('valor');								
				//alert(file+'&'+'iniDB=0&finDB=10000&llave='+val);
				var aux=ajaxR(file+'&'+'iniDB=0&finDB=10000&llave='+val);
				//alert(aux);
				if(aux.split('|')[0] != 'exito')
					alert(aux);
			}
			valor=celda.valor?celda.valor:celda.getAttribute('valor');
			aux=aux.split('|');
			if(aux[0] != 'exito')
				return false;
			var largo=celda.offsetWidth-2;
			var alto=celda.offsetHeight-8;
			var x='<select class="combos" id="c'+idcelda+'" style="width:'+largo+'px" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" onblur="DesEditaCelda(\''+idcelda+'\', this);">';
			for(var f=2;f<aux.length;f++)	
			{
				var ax=aux[f].split('~');
				x+='<option value="'+ax[0]+'"';
				if(ax[0] == valor)
					x+=' selected';
				x+='>'+ax[1]+'</option>';
			}
			x+='</select>';			
			if(navigator.appName == 'Microsoft Internet Explorer')
			{
				if(modificable == 'S')
				{
					celda.innerHTML='<span class="select-box">'+x+'</span>';
					new YAHOO.Hack.FixIESelectWidth("c"+idcelda);


				}
				else
					celda.innerHTML='<input id="c'+idcelda+'" type="text" style="width:'+(largo-4)+'px; height:'+alto+'px;" class="celdaTextoD" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" value="'+celda.innerHTML+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" readonly/>';				
			}
			else
			{
				if(modificable == 'S')
					celda.innerHTML=x;
				else
					celda.innerHTML='<input id="c'+idcelda+'" type="text" style="width:'+(largo-4)+'px; height:'+alto+'px;" class="celdaTextoD" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" value="'+celda.innerHTML+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" readonly/>';				
			}
			obj=document.getElementById('c'+idcelda);
			obj.focus();
			break;
		case 'buscador':						
			var valor=celda.innerHTML;
			var aux="";
			var largo=celda.offsetWidth;
			var alto=celda.offsetHeight;
			var obj=document.getElementById(tabla);
			var ax=obj.elefoc?obj.elefoc:obj.getAttribute('elefoc');			
			if(ax == 'com'+idcelda)
				return false;			
			obj.elefoc='c'+idcelda;
			obj.setAttribute('elefoc', 'c'+idcelda);
			if((valor.substring(0,6).toUpperCase()) == '<INPUT'.toUpperCase())
				return false;			
			valor=celda.valor?celda.valor:celda.getAttribute('valor');				
			var largo=celda.offsetWidth-6;
			var alto=celda.offsetHeight-2;
			if(modificable == 'S')
				aux='<input extra="0"; id="c'+idcelda+'" type="text" style="width:'+(largo-1)+'px; height:'+(alto-1)+'px;" class="celdaTexto" value="'+valor+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" onkeyup="Rebusca(event, \''+idcelda+'\', this)" onmouseover="this.extra=1;" onmouseout="this.extra=0;"/>';
			else	
				aux='<input id="c'+idcelda+'" type="text" style="width:'+largo+'px; height:'+alto+'px;" class="celdaTextoD" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" value="'+valor+'" onblur="DesEditaCelda(\''+idcelda+'\', this);" readonly/>';				
			
			celda.innerHTML=aux;
			//alert(aux);
			obj=document.getElementById('c'+idcelda);
			obj.focus();
			break;
	}
	return true;
}

function DesEditaCelda(cad, obj)
{	
	//alert('INIDES');
	var celda=document.getElementById(cad);	
	//alert(celda);
	var aux=celda.id.split("_");

	var tabla=aux[0];
	var fila=aux[1];
	var columna=aux[2];
	var cab=document.getElementById("H_"+tabla+fila);
	var tipo=cab.tipo?cab.tipo:cab.getAttribute('tipo');
	var funcam=cab.onChange?cab.onChange:cab.getAttribute('onChange');	
	var modificable=cab.modificable?cab.modificable:cab.getAttribute('modificable');
	var maskara=cab.mascara?cab.mascara:cab.getAttribute('mascara');
	var verSum=cab.verSumatoria?cab.verSumatoria:cab.getAttribute('verSumatoria');
	var valida=cab.valida?cab.valida:cab.getAttribute('valida');
	
	//var onChange=cab.onChange?cab.onChange:cab.getAttribute('onChange');
	var nver=false;	
	var yreal=yReal(cad);
	
	if(!obj)
		return false;
		
		
	
	switch(tipo)
	{
		case 'texto':
		case 'formula':
		case 'entero':
		case 'decimal':
		case 'fecha':			
			var val=celda.valor?celda.valor:celda.getAttribute('valor');	
			celda.innerHTML="&nbsp;";			
			if(val == obj.value)
			{					
				if(obj.value.length > 0 && obj.value != " ")
					celda.innerHTML=Mascara(maskara,val);
				return true;
			}			
		    if(val != obj.value)
				nver=true;				
			
			
			if(valida != null && valida != "null" && valida.length > 0)
			{
				//alert(valida+' => '+eval(valida.replace('#',columna)));
				var valTemp=valida.replace('#',yreal);
				valTemp=valTemp.replace('$DATO', obj.value);
				//alert(valTemp);
				if(eval(valTemp) != true)
				{
					if(val.length > 0)
						celda.innerHTML=Mascara(maskara,val);						
					return false;
				}
			}
			
			if(verSum == "S")
			{
				hobj=document.getElementById('F'+tabla+fila);
				var val=hobj.value;
				if(maskara != "null" && maskara != null && maskara.length > 0)
				{
					for(j=0;j<maskara.length;j++)
					{
						if(maskara.charAt(j) != '.')
						{
							while(val.indexOf(maskara.charAt(j)) != -1)	
								val=val.replace(maskara.charAt(j), '');
						}
					}
				}
				val=isNaN(parseFloat(val))?0:parseFloat(val);	
				if(!isNaN(parseFloat(celda.valor?celda.valor:celda.getAttribute('valor'))))
					val-=parseFloat(celda.valor?celda.valor:celda.getAttribute('valor'));
				if(!isNaN(parseFloat(obj.value)))	
					val+=parseFloat(obj.value);
				if(maskara != "null" && maskara != null && maskara.length > 0)
					hobj.value=Mascara(maskara, ""+val+"");
				else
					hobj.value=val;
			}			
				
			celda.valor=obj.value;			
			if(obj.value.length > 0)			
				celda.innerHTML=Mascara(maskara,obj.value);	
			//alert(celda.innerHTML);	
			celda.setAttribute("valor",obj.value);			
			/*-----Buscamos los datos con formulas-----*/
			
			var Tabla=document.getElementById('Head_'+tabla);
			var Trs=Tabla.getElementsByTagName('tr');
			
			//Buscamos las cabecera			
			var Tds=Trs[0].getElementsByTagName('td');
			
			//Buscamos los tipo formula
			for(j=1;j<Tds.length;j++)
			{
				var tipo=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");
				var formula=Tds[j].formula?Tds[j].formula:Tds[j].getAttribute("formula");					
				if(tipo == "formula"|| (tipo == "oculto" && formula != null && formula != "null" && formula.length > 0))
				{						
					var cH=document.getElementById("H_"+tabla+fila);
					var nombre=cH.valor?cH.valor:cH.getAttribute("valor");
					//alert(formula);
					if(formula.indexOf('$'+nombre) != -1)						
						AplicaFormula(document.getElementById(tabla+'_'+(j-1)+'_'+columna));
				}
			}			
			break;
		case 'binario':	//////////////////******************************************+		
			valorB=(celda.valor?celda.valor:celda.getAttribute("valor"));
			if((obj.checked == true && valorB == 0) || (obj.checked == false && valorB == 1))
			{
				nver=true;
				
			}
			if(obj.checked == true)
			{
				celda.valor=1;
				celda.setAttribute('valor',1);
			}
			else
			{
				celda.valor=0;
				celda.setAttribute('valor',0);
			}			
			break;//////////////////******************************************+	
		case 'eliminador':			
			//celda.innerHTML=celda.innerHTML.replace('id="c'+cad+'"', 'id="NULL"');			
			break;
		case 'combo':			
			var val=celda.valor?celda.valor:celda.getAttribute('valor');			
			if(obj.value != val)
				nver=true;
			if((celda.innerHTML.substring(0,6).toUpperCase()) == '<INPUT'.toUpperCase())
				celda.innerHTML=obj.value;
			else
			{				
				var nvalc=obj.value;
				if(valida != null && valida != "null" && valida.length > 0)
				{
					var valTemp=valida.replace('#',yreal);
					valTemp=valTemp.replace('$DATO', obj.value);
					if(eval(valTemp) != true)					
						nvalc=val;
				}	
				//alert(nvalc);				
				for(var i=0;i<obj.options.length;i++)
				{
					if(obj.options[i].value == nvalc)
						var texto=obj.options[i].text;
				}
				if(nvalc == '')
					texto='&nbsp;';
				celda.valor=obj.value;
				celda.setAttribute("valor",obj.value);
				celda.innerHTML=texto;
			}			
			break;
		case 'buscador':
						
			var aux=document.getElementById(tabla);
			var aux=aux.elefoc?aux.elefoc:aux.getAttribute('elefoc');
			
			//alert(aux+" != com"+cad);
			
									
			if(aux == 'com'+cad)
				return false;			
			if(celda.valor != obj.value)
				nver=true;
								
			celda.valor=obj.value;
			if(obj.value.length == 0)
				celda.innerHTML='&nbsp;';	
			else
				celda.innerHTML=obj.value;
				
				
			break;
	}
	if(funcam != 'null' && funcam != null && funcam.length > 0 && nver == true)
	{
		var funaux=funcam;
		//alert(funaux);
		while(funaux.indexOf('#') != -1)
			funaux=funaux.replace('#',yreal)
		//alert(funaux);	
		eval(funaux);
	}
	
	//alert('FINDES');
}

function TeclasEspeciales(eve, obj, cad)
{
	//Obtenemos el codigo de la tecla presionada	
	var key=0;	
	key=(eve.which) ? eve.which : eve.keyCode;	
	//alert(key);	
	
	
	
		
	//Separamos los datos del id
	var aux=cad.split('_');
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	var yreal=yReal(cad);
	
	
	//En caso de ser cabecera hacemos insert
	if(isNaN(fila) && key == 13)
	{
		//alert('???');
		InsertaFila(tabla);
		return true;
	}
	
	
	//Elementos para obtener la fila anterior y siguiente
	if(key != 37 && key != 39)
	{		
		//Obtenemos la fila actual y conseguimos la siguiente y la anterior
		var fil=document.getElementById(tabla+"_Fila"+fila);		
		if(fil)
		{
			var pos=fil.rowIndex?fil.rowIndex:fil.getAttribute('rowIndex');
			if(pos == null)
				pos=0;
			posgen=pos;//utilizada para observacionesinc.tpl 	
			//Obtenemos las filas de datos	
			var Tabla=document.getElementById("Body_"+tabla);
			Trs=Tabla.getElementsByTagName('tr');
			if(Trs.length > 0)
			{
				Tds=Trs[0].getElementsByTagName('td');
				var nfilas=Tds.length;
			}
			else
				var nfilas=0;				
			
			
			//conseguimos el numero siguiente y anterior
			if(pos == 0)
				var ant=null;
			else
			{
				var ant=Trs[pos-1].id?Trs[pos-1].id:Trs[pos-1].getAttribute('id');
				ant=ant.split('_');
				ant=parseInt(ant[1].replace('Fila',''));			
			}
			if(pos == (Trs.length-1))
				var sig=null;
			else
			{
				var sig=Trs[pos+1].id?Trs[pos+1].id:Trs[pos+1].getAttribute('id');
				sig=sig.split('_');
				sig=parseInt(sig[1].replace('Fila',''));			
			}
		}		
	}
	
	var cabecera=document.getElementById("H_"+tabla+(columna+1));//MS
	if(!cabecera)
	{
		if(key == 40)
		{
			InsertaFila(tabla);
			return true;
		}
		return false;
	}
	var tipoSig=cabecera.tipo?cabecera.tipo:cabecera.getAttribute('tipo');
	var modsig=cabecera.modificable?cabecera.modificable:cabecera.getAttribute('modificable');
	var cabecera=document.getElementById("H_"+tabla+columna);
	var tipo=cabecera.tipo?cabecera.tipo:cabecera.getAttribute('tipo');
	var onKey=cabecera.onkey?cabecera.onkey:cabecera.getAttribute('onkey');	
		
	var ax=onKey.split('|');	
	for(var i=0;i<ax.length;i++)
	{
		axF=ax[i].split('->');		
		if(parseInt(axF[0]) == key)
		{
			eval(axF[1].replace('#',yreal));
			return true;
		}
	}
	//Enter
	if(key == 13)
	{			
		// if(tipo == 'buscador')				
		//alert('1?');
		
		if(tipoSig == 'libre' || tipoSig == 'eliminador' || tipoSig == 'oculto' || modsig == 'N' || modsig == 'n')
		{	
			//alert('OK ?');
				
			var ncol=NumColumnas(tabla);
			//alert(columna+' -> '+ncol);
			for(lk=(columna+1);lk<ncol;lk++)
			{
				var cabeaux=document.getElementById("H_"+tabla+lk);
				var tipoaux=cabeaux.tipo?cabeaux.tipo:cabeaux.getAttribute('tipo');
				var modifi=cabeaux.modificable?cabeaux.modificable:cabeaux.getAttribute('modificable');				
				if(tipoaux != 'eliminador' && tipoaux != 'oculto' && tipoaux != 'libre' && (modifi == 'S' || modifi == 's'))
				{
					//alert(modifi+' - '+tipoaux);	
					break;
				}
			}	
			//alert(lk);
			if(lk == ncol)
			{
				for(lk=0;lk<ncol;lk++)
				{
					var cabeaux=document.getElementById("H_"+tabla+lk);
					var tipoaux=cabeaux.tipo?cabeaux.tipo:cabeaux.getAttribute('tipo');
					var modifi=cabeaux.modificable?cabeaux.modificable:cabeaux.getAttribute('modificable');				
					if(tipoaux != 'eliminador' && tipoaux != 'oculto' && tipoaux != 'libre' && (modifi == 'S' || modifi == 's'))
					{
						//alert(modifi+' - '+tipoaux);	
						break;
					}
				}
				var real=aux[0]+'_'+lk+'_'+sig;
				//alert(real);
			}
			else
				var real=aux[0]+'_'+lk+'_'+fila;			
			var ncelda=document.getElementById(real);
			if(ncelda)
			{
				//alert('r 1');
				
				var dispositivo = navigator.userAgent.toLowerCase();
	      		if( dispositivo.search(/iphone|ipod|ipad|android/) > -1 )
	      		{
	      			//document.location = ‘http://www.pepfarinweb.com/mobile’; 
	      		}
	      		else
					DesEditaCelda(cad, obj);
				//alert('r 2');				
				EditarValor(ncelda);
				//alert('r 3');
				ax=document.getElementById('c'+real);
				if(ax)
					ax.focus();				
			}
			else
			{
				
				//alert('r 2');
				if(NumFilas(tabla) > 0)	
					DesEditaCelda(cad, obj);		
				InsertaFila(tabla);
			}
			return false;	
		}
		//alert(tipo+' -> '+tipoSig);
		var real=tabla+'_'+(columna+1)+'_'+fila;
		
		var ncelda=document.getElementById(real);
		if(ncelda)
		{			
			//alert('ok1');
			DesEditaCelda(cad, obj);
			EditarValor(ncelda);			
			ax=document.getElementById('c'+real);
			if(ax)
				ax.focus();
			/*else
				alert('NO c'+real);*/
		}
		else
		{			
			//alert('ok2');
			
			if(NumFilas(tabla) > 0)	
				DesEditaCelda(cad, obj);
			//return false;
			InsertaFila(tabla);
		}
		
	}
	//Tecla de cursor derecha
	else if(key == 39)
	{		
		var real=tabla+'_'+(columna+1)+'_'+fila;		
		var mod=cabecera.modificable?cabecera.modificable:cabecera.getAttribute('modificable');
		if((tipo == 'texto' || tipo == 'decimal' || tipo == 'entero' || tipo == 'buscador' || tipo == 'fecha') && mod == 'S')
			return true;
		var ncelda=document.getElementById(real);
		if(ncelda)
		{			
			DesEditaCelda(cad, obj);
			EditarValor(ncelda);			
			ax=document.getElementById('c'+real);
			if(ax)
				ax.focus();			
		}
	}
	//Tecla de cursor Izquierda
	else if(key == 37)
	{			
		var real=tabla+'_'+(columna-1)+'_'+fila;		
		var mod=cabecera.modificable?cabecera.modificable:cabecera.getAttribute('modificable');
		if((tipo == 'texto' || tipo == 'decimal' || tipo == 'entero' || tipo == 'buscador' || tipo == 'fecha') && mod == 'S')
			return true;		
		var ncelda=document.getElementById(real);
		if(ncelda)
		{			
			DesEditaCelda(cad, obj);
			EditarValor(ncelda);			
			ax=document.getElementById('c'+real);
			if(ax)
				ax.focus();			
		}
	}
	//Tecla de cursor Arriba
	else if(key == 38 && ant != null)
	{			
		if(tipo == 'combo')
			return false;
		var real=tabla+'_'+columna+'_'+ant;
		var ncelda=document.getElementById(real);
		if(ncelda)
		{			
			DesEditaCelda(cad, obj);
			EditarValor(ncelda);			
			ax=document.getElementById('c'+real);
			if(ax)
				ax.focus();			
		}
	}
	
	//Tecla de cursor Abajo
	else if(key == 40 || (tipo == 'buscador' && obj.value.length > 2))
	{			
		//return false;
		if(tipo == 'combo' && !isNaN(fila))
			return false;
		if(tipo == 'buscador')	
		{			
			var datosdb=cabecera.datosdb?cabecera.datosdb:cabecera.getAttribute('datosdb');
			var ax=datosdb.split(">>");
			if(ax[0])
				datosdb=ax[0];
			for(var i1=1;i1<ax.length;i1++)
			{
				var instruccion=ax[i1];				
				if(instruccion.indexOf('#')!=-1)
					instruccion=instruccion.replace('#',yreal);
				//alert(instruccion);	
				datosdb+=eval(instruccion);				
			}
			var combolargo=cabecera.combolargo?cabecera.combolargo:cabecera.getAttribute('combolargo');
			//alert(cad);
			var celda=document.getElementById(cad);			
			var aux= celda.innerHTML;
			//alert(aux);
			if(aux.toUpperCase().indexOf('<SELECT'.toUpperCase()) != -1)
				return true;			
			var largo=celda.offsetWidth-6;
			var alto=celda.offsetHeight-2;			
			if(navigator.appName=="Microsoft Internet Explorer")
				objx=celda;
			else
				var objx=document.getElementById('c'+cad);
			var curleft = curtop = 0;
			if (objx.offsetParent)
			{
				//alert(curleft+" - "+curtop);
				
				if(navigator.appName=="Microsoft Internet Explorer")
				{
					curleft += objx.offsetLeft;
					curtop += objx.offsetTop;
				}
				else
				{
					
					
					curleft += objx.style.position.left?objx.style.position.left:objx.style.position.offsetLeft;
					curtop += objx.style.position.top?objx.style.position.offseTop:objx.style.position.top;
				}
				/*while (objx = objx.offsetParent)
				{
					curleft += objx.style.left;
					curtop += objx.style.top;
				}*/
			}			
				
			if(navigator.appName=="Microsoft Internet Explorer")
			{				
				/*curtop-=39;
				curleft-=11;				*/
			}			
			curtop+=alto;
			var divcom=document.createElement("DIV");
			//divcom.style='position:absolute; top:'+curtop+'; left:'+curleft+';';
			divcom.style.position="absolute";
			divcom.style.top=curtop;
			divcom.style.offseTop=curtop;
			divcom.style.left=curleft;
			divcom.style.offsetLeft=curleft;
			
			if(navigator.appName=="Microsoft Internet Explorer" || (navigator.appName=="Netscape" && navigator.userAgent.indexOf('Firefox') != -1))
			{
				aux+='<div style="position:absolute; top:'+curtop+'; left:'+curleft+';">'
				
				
				
				//alert(combolargo);
				largo=parseInt(combolargo);
			}
			
			
			
			var selcom=document.createElement("SELECT");
			selcom.size=4;
			selcom.style='width:'+(largo+3)+'px; height:100px';
			selcom.style.height="110px";
			selcom.id="com"+cad;
			
			selcom.onclick='ocultaBus(this.value, \''+cad+'\')';
			selcom.setAttribute('onclick', 'ocultaBus(this.value, \''+cad+'\')');
			
			selcom.onkeydown='teclasEspBus(event ,\''+cad+'\', this)';
			selcom.setAttribute('onkeydown', 'teclasEspBus(event ,\''+cad+'\', this)');
			
			//selcom.onfocus='document.getElementById(\''+tabla+'\').elefoc=\'c'+cad+'\';document.getElementById(\''+tabla+'\').setAttribute(\'elefoc\', \'c'+cad+'\');';
			//selcom.setAttribute('onfocus', 'document.getElementById(\''+tabla+'\').elefoc=\'com'+cad+'\';document.getElementById(\''+tabla+'\').setAttribute(\'elefoc\', \'com'+cad+'\')');
			
			selcom.onblur='OcultaBusSC(\''+cad+'\');';
			selcom.setAttribute('onblur', 'OcultaBusSC(\''+cad+'\')');
			selcom.className='combos';
			
			
			aux+='<select size="4" style="width:'+(largo+3)+'px; height:100px" id="com'+cad+'" onclick="ocultaBus(this.value, \''+cad+'\');" onkeydown="teclasEspBus(event ,\''+cad+'\', this);"';
			//aux+='onfocus="alert(\'?\');document.getElementById(\''+tabla+'\').elefoc=\'com'+cad+'\';document.getElementById(\''+tabla+'\').setAttribute(\'elefoc\', \'com'+cad+'\');"';
			aux+=' onblur="OcultaBusSC(\''+cad+'\');" class="combos">';			
			var valaux=obj.value;
			var rfile=datosdb;
			if(rfile.indexOf('"') != -1 || rfile.indexOf("'") != -1)
				rfile=eval(datosdb);
			//Pasos buscador dependiente
			var depende=cabecera.depende?cabecera.depende:cabecera.getAttribute("depende");
			var multidependencia=cabecera.multidependencia?cabecera.multidependencia:cabecera.getAttribute("multidependencia");
			if(depende!=null && depende!="null" && depende!="" && depende!=0)
			{				
				if(multidependencia=="S")
				{
					arrDepende=depende.split("|");
					var celdaDependencia,valor;
					rfile+="&nodependencias="+arrDepende.length;
					for(var i=0;i<arrDepende.length;i++)
					{
						celdaDependencia=document.getElementById(tabla+"_"+arrDepende[i]+"_"+fila);						
						valor=celdaDependencia.valor?celdaDependencia.valor:celdaDependencia.getAttribute("valor");						
						if(valor!=null&&valor!="null"&&valor!="")
							rfile+="&llaveaux"+i+"="+valor;
					}
				}
				else
				{					
					depende=parseInt(depende)-1;
					var celdaDependencia=document.getElementById(tabla+"_"+depende+"_"+fila);					
					var valor=celdaDependencia.valor?celdaDependencia.valor:celdaDependencia.getAttribute("valor");
					if(valor!=null&&valor!="null"&&valor!="")
						rfile+="&llaveaux="+valor;
				}				
			}			
			var ax=ajaxR(rfile+'&llave='+obj.value);
			var ax=ax.split('|');
			if(ax[0] == 'exito')
			{
				for(i=2;i<ax.length;i++)
				{
					aux+='<option value="'+ax[i]+'">'+ax[i]+'</option>';
					selcom.options[i-2]=new Option(ax[i], ax[i]);
				}	
			}
			else
				alert("No se pudo cargar datos desde "+rfile+'&llave='+obj.value+"\n\n"+ax);
			aux+='</select>';
			if(navigator.appName=="Microsoft Internet Explorer" || (navigator.appName=="Netscape" && navigator.userAgent.indexOf('Firefox') != -1))
				aux+='</div>';
			//alert(aux);
			
			//alert(aux);
			
			//alert(divcom);
			
			//alert(divcom.style);


			//alert(navigator.userAgent);
			if(navigator.appName=="Netscape" && navigator.userAgent.indexOf('Chrome') != -1)
			{
				divcom.appendChild(selcom);
				celda.appendChild(divcom);	
			}
			else
			{
				celda.innerHTML=aux;
			}
			//alert(celda.innerHTML);
			
			//alert(divcom.innerHTML);
			
			
			
			//
			//alert(document.getElementById('com'+cad));
			
			
			setTimeout('document.getElementById(\''+tabla+'\').elefoc=\'com'+cad+'\';document.getElementById(\''+tabla+'\').setAttribute(\'elefoc\', \'com'+cad+'\');', 90);
			setTimeout("document.getElementById('com"+cad+"').focus();", 100);
			//setTimeout("alert(document.getElementById('"+tabla+"').elefoc);", 500);
			/*var obj=document.getElementById(tabla);
			var ax=obj.elefoc?obj.elefoc:obj.getAttribute('elefoc');
			alert(ax);*/
			
			return true;

		}
		var real=tabla+'_'+columna+'_'+sig;
		var ncelda=document.getElementById(real);
		if(ncelda)
		{			
			DesEditaCelda(cad, obj);
			EditarValor(ncelda);			
			ax=document.getElementById('c'+real);
			if(ax)
				ax.focus();			
		}
		else

			InsertaFila(tabla);						
	}		
}

function AplicaFormula(obj)
{
	//Descomponemos los valores del id
	var aux=obj.id.split("_");
	var idcelda=aux;
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	var yreal=yReal(obj.id);
	
	//Obtenemos los datos de la cabecera correspondiente
	var cab=document.getElementById("H_"+tabla+columna);
	if(cab == null)
		return false;
	var formula=cab.formula?cab.formula:cab.getAttribute("formula");
	var tipo=cab.tipo?cab.tipo:cab.getAttribute("tipo");
	var maskara=cab.mascara?cab.mascara:cab.getAttribute('mascara');
	var verSum=cab.verSumatoria?cab.verSumatoria:cab.getAttribute('verSumatoria');
	var funcam=cab.onChange?cab.onChange:cab.getAttribute('onChange');	
	
	//validamos que haya formula que aplicar
	if(formula == 'null' || formula == null || formula.length <= 0)
		return false;	
	
	var aux=formula;
	//alert(aux);
	//Array de caracteres especiales ocupados por la formula
	var ope = new Array('Math.sqrt(','Math.abs(','Math.ceil(','Math.floor(','Math.pow(','(',')','{','}','[',']','+','-','/','*',
						',','1','2','3','4','5','6','7','8','9','0','.');
	var vars = new Array();
	var vals = new Array();																	  
																		  
	//Desconponemos hasta tener solo los nombres de las variables																	  
	for(k=0;k<ope.length;k++)
	{
		while(aux.indexOf(ope[k]) != -1)
			aux=aux.replace(ope[k],'');
	}
	//alert(aux);
	var ax=aux.split('$');	
	for(k=1;k<ax.length;k++)
	{
		for(l=0;l<vars.length;l++)
		{
			if(vars[l] == ax[k])
				break;			
		}
		if(l == vars.length)

			vars[l]=ax[k];
	}		
	
	//Obtenemos los datos de la tabla de cabecera


	var Tabla=document.getElementById("Head_"+tabla);
	var filas=Tabla.getElementsByTagName('tr');
	var cols=filas[0].getElementsByTagName('td');
	
	//Obtenemos los datos de la tabla de datos
	var Tabla=document.getElementById("Body_"+tabla);
	var filas1=Tabla.getElementsByTagName('tr');
	var fil=document.getElementById(tabla+"_Fila"+fila);	
	var pos=fil.rowIndex?fil.rowIndex:fil.getAttribute('rowIndex');
	if(pos == null)
		pos=0;	
	var colsV=filas1[pos].getElementsByTagName('td');	
	
	//Recorremos las columnas de la fila que estamos ocupando
	for(k=1;k<colsV.length;k++)
	{		
		var nomCol=cols[k].valor?cols[k].valor:cols[k].getAttribute("valor");		
		
		//Buscamos si el nombre es igual a uno de los que se encuentran en la formula
		for(l=0;l<vars.length;l++)
		{				
			if(vars[l] == nomCol)
			{				
				var valAux=colsV[k].valor?colsV[k].valor:colsV[k].getAttribute("valor");								
				if(isNaN(parseInt(valAux)))	
					valAux=0;
				if(valAux < 0)	
					valAux="("+valAux+")";
				//Obtenemos el valor
				vals[l]=valAux;
			}
		}
	}	
	
	//reemplazamos nombres por valores	
	//alert(formula);
	var aux=formula;		
	for(k=0;k<vals.length;k++)
	{		
		while(aux.indexOf('$'+vars[k]) != -1)
		aux=aux.replace('$'+vars[k],vals[k]);
	}
	//alert(aux);
	var ax=eval(aux);
	//alert(aux+"\n\n"+ax);
	if(tipo == 'oculto')
	{		
		obj.valor=ax;		
		return true;
	}
	
	if(verSum == "S")
	{
		hobj=document.getElementById('F'+tabla+columna);
		var val=hobj.value;
		if(maskara != "null" && maskara != null && maskara.length > 0)
		{
			for(s=0;s<maskara.length;s++)
			{
				if(maskara.charAt(s) != '.')
				{
					while(val.indexOf(maskara.charAt(s)) != -1)	
						val=val.replace(maskara.charAt(s), '');
				}
			}
		}				
		val=isNaN(parseFloat(val))?0:parseFloat(val);	
		if(!isNaN(parseFloat(obj.valor?obj.valor:obj.getAttribute('valor'))))
			val-=parseFloat(obj.valor?obj.valor:obj.getAttribute('valor'));
		if(!isNaN(parseFloat(ax)))	
			val+=parseFloat(ax);
		if(maskara != "null" && maskara != null && maskara.length > 0)

			hobj.value=Mascara(maskara, ""+val+"");
		else
			hobj.value=val;
	}	
	
	obj.valor=ax;
	obj.setAttribute('valor', ax);
	obj.innerHTML=Mascara(cols[columna+1].mascara?cols[columna+1].mascara:cols[columna+1].getAttribute('mascara'),ax);
	
	if(funcam != 'null' && funcam != null && funcam.length > 0)
	{
		var funaux=funcam;
		while(funaux.indexOf('#') != -1)
			funaux=funaux.replace('#',yreal)
		eval(funaux);
	}
}

function AplicaMascara(obj)
{	
	//desconponemos los datos importantes del id
	var aux=obj.id.split("_");
	var tabla=aux[0];
	var columna=aux[1];
	var fila=aux[2];
	
	//Obtenemos los datos de la cabecera correspondiente
	var cab=document.getElementById("H_"+tabla+columna);
	if(cab == null)
		return false;
	var tipo=cab.tipo?cab.tipo:cab.getAttribute('tipo');
	var modificable=cab.tipo?cab.modificable:cab.getAttribute('modificable');	
	var mascara=cab.mascara?cab.mascara:cab.getAttribute('mascara');
		
	//Revisamos que haya realmente una mascara
	if(mascara == 'null' || mascara == null || mascara.length <= 0)
		return false;
	
	//Aplicamos la mascara al valor
	var val=obj.valor?obj.valor:obj.getAttribute("valor");
	var cadM=Mascara(mascara, val);
	
	alert(tipo);
	if(tipo == 'entero')
	{
		alert('zehaha');	
		cadM=cadM.split('.')[0];	
	}

	//Asignamos al html el valor ya con mascara
	obj.innerHTML=cadM;
}

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

function DesEnmascarar(valorEnmascarado)
{
	var long=valorEnmascarado.length;
	var cadNueva="";
	for(var i=0;i<long;i++)
	{
		caracter=valorEnmascarado.charAt(i);
		exp=/^[0-9]|\.$/;
		if(exp.test(caracter))
			cadNueva+=caracter;
	}
	return cadNueva;
}

function ordena(colOrdena, tabla)
{
	//Obtenemos los valores de la tabla general
	var Tabla=document.getElementById(tabla);	
	/*var simA=String.fromCharCode(9650);
	var simD=String.fromCharCode(9660);*/
	var simA=" ";
	var simD=" ";
	var validaElimina=Tabla.validaElimina?Tabla.validaElimina:Tabla.getAttribute("validaElimina");	
	
	var paginador=Tabla.paginador?Tabla.paginador:Tabla.getAttribute("paginador");
	var datosxPag=Tabla.datosxPag?Tabla.datosxPag:Tabla.getAttribute("datosxPag");
	datosxPag=isNaN(parseFloat(datosxPag))?10:parseFloat(datosxPag);
	var pagMetodo=Tabla.pagMetodo?Tabla.pagMetodo:Tabla.getAttribute("pagMetodo");
	pagMetodo=(pagMetodo == '')?'javascript':pagMetodo;	
	var ordenaPHP=Tabla.ordenaPHP?Tabla.ordenaPHP:Tabla.getAttribute("ordenaPHP");
	
	//Buscamos los datos de la tabla de cabeceras
	Tabla=document.getElementById('Head_'+tabla);
	var Trs=Tabla.getElementsByTagName('tr');	
	var Tds=Trs[0].getElementsByTagName('td');	
	
	//Si esta activo el metodo de paginador php y ordenacion php
	if((ordenaPHP == 'S' || ordenaPHP == 's') && (paginador == 'S' || paginador == 's') && pagMetodo == 'php')
	{
		//Conseguimos los datos de la columna a ordenar	
		campoOr=Tds[colOrdena].campoBD?Tds[colOrdena].campoBD:Tds[colOrdena].getAttribute("campoBD");
		
		var sen=Tds[colOrdena].sentido?Tds[colOrdena].sentido:Tds[colOrdena].getAttribute("sentido");
		
		if(sen != 'Asc')
			var sen='Asc';
		else
			var sen='Desc';
			
		Tabla=document.getElementById(tabla);
		//Obtenemos el campo por el que se encuentra ordenado actualmente
		var ordenAnt=Tabla.ordenCampo?Tabla.ordenCampo:Tabla.getAttribute("ordenCampo");
		//si se ha cambiado el campo de orden volvemos a ordenar ascendentemente
		if(campoOr!=ordenAnt)
			sen='Asc';
		//Asignamos a la tabla los valores de campo a ordenart y sentido	
		Tabla=document.getElementById(tabla);
		Tabla.ordenCampo=campoOr;
		Tabla.setAttribute('ordenCampo', campoOr);		
		Tabla.sentidoOr=sen;
		Tabla.setAttribute('sentidoOr', sen);
		
		RecargaGrid(tabla, '');		
	
		//asignamos el sentido de ordenacion actual
		Tds[colOrdena].sentido=sen;
		Tds[colOrdena].setAttribute("sentido",sen);		
		
		return true;
	}	
	
	
	//variables inciales
	var tipos=new Array();
	for(i=0;i<Tds.length;i++)
	{
		tipos.push(Tds[i].tipo?Tds[i].tipo:Tds[i].getAttribute("tipo"));		
	}
	var valores=new Array();
	var posc=new Array();		
	
	//Asignamos el sentido de ordenacion
	if(Tds[colOrdena].sentido != 'Asc')
		var sen='Asc';
	else
		var sen='Desc';		
	
	//Buscamos la tabla de datos
	Tabla=document.getElementById('Body_'+tabla);
	var Trs=Tabla.getElementsByTagName('tr');
	
	//Asignamos los valores a un arreglo para su facilidad de eso
	for(i=0;i<Trs.length;i++)
	{
		var Tds=Trs[i].getElementsByTagName('td');
		if(tipos[colOrdena] == 'entero' || tipos[colOrdena] == 'decimal' || tipos[colOrdena] == 'formula' || tipos[colOrdena] == 'binario')
			valores.push(parseFloat(Tds[colOrdena].valor?Tds[colOrdena].valor:Tds[colOrdena].getAttribute("valor")));
		else
			valores.push(Tds[colOrdena].innerHTML?Tds[colOrdena].innerHTML:Tds[colOrdena].getAttribute("innerHTML"));
		posc.push(i);		
	}
	var lon=Tds.length;
	if(validaElimina != 'false')
		lon--;
	

	//Ordenamos los datos reordenando las posiciones
	for(i=0;i<valores.length;i++)
	{		
		for(j=(i+1);j<valores.length;j++)

		{			
			if((valores[i] > valores[j] && sen == 'Asc') || (valores[i] < valores[j] && sen == 'Desc'))
			{					
				var vaux=valores[i];
				valores[i]=valores[j];
				valores[j]=vaux;
				var vaux=posc[i];
				posc[i]=posc[j];
				posc[j]=vaux;
			}				
		}					
	}
	
	//Aprtir de la matriz de posiciones reordenamos la tabla de datos
	for(i=0;i<posc.length;i++)
	{		
		if(i != posc[i])
		{			
			var Tds=Trs[i].getElementsByTagName('td');
			var Tds1=Trs[posc[i]].getElementsByTagName('td');			
			for(j=1;j<lon;j++)
			{				
				var vaux=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute("valor");

				var uaux=Tds1[j].valor?Tds1[j].valor:Tds1[j].getAttribute("valor");				
				Tds[j].valor=uaux;
				Tds1[j].valor=vaux;
				if(tipos[j] == 'binario')
				{				
					if(vaux == "1")
					{						
						Tds1[j].innerHTML=Tds1[j].innerHTML.replace('>',' CHECKED>');
					}
					else
					{
						Tds1[j].innerHTML=Tds1[j].innerHTML.replace('CHECKED','');
						Tds1[j].innerHTML=Tds1[j].innerHTML.replace('checked="checked"','');
					}
					if(uaux == "1")
					{
						Tds[j].innerHTML=Tds[j].innerHTML.replace('>',' CHECKED>');	
					}
					else	
					{						
						Tds[j].innerHTML=Tds[j].innerHTML.replace('CHECKED','');
						Tds[j].innerHTML=Tds[j].innerHTML.replace('checked="checked"','');
					}					
				}				
				else
				{
					var vaux=Tds[j].innerHTML?Tds[j].innerHTML:Tds[j].getAttribute("innerHTML");
					var uaux=Tds1[j].innerHTML?Tds1[j].innerHTML:Tds1[j].getAttribute("innerHTML");				
					Tds[j].innerHTML=uaux;
					Tds1[j].innerHTML=vaux;
				}
			}
			for(j=(i+1);j<posc.length;j++)
			{
				if(posc[j] == i)
				{
					posc[j]=posc[i];
					break;
				}
			}
		}		
	}
	
	//Buscamos la tabla de cabeceras
	Tabla=document.getElementById('Head_'+tabla);
	var Trs=Tabla.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	
	//asignamos el sentido de ordenacion actual
	Tds[colOrdena].sentido=sen;
	Tds[colOrdena].setAttribute("sentido",sen);
	
	//Cambiamos la leyenda de los headers
	for(i=1;i<Tds.length;i++)
	{
		obj=document.getElementById('H'+tabla+(i-1));
		if(obj == null)
			continue;
		var val= Tds[i].valor?Tds[i].valor:Tds[i].getAttribute("valor");		
		obj.value=val;
		if(i == colOrdena)
		{
			if(sen == 'Asc')
				obj.value+=' '+simA;
			else
				obj.value+=' '+simD;
			//alert(simA);	
		}
		
	}
}

function InsertaFila(tabla)
{	
	//Conseguimos las tabla general, de cabeceras y de datos; ademas de los datos necesarios
	var Gral=document.getElementById(tabla);
	var Head=document.getElementById('Head_'+tabla);
	var lis=Gral.listado?Gral.listado:Gral.getAttribute('listado');
	if(lis == 'S' || lis == 's')
		return false;
	var Tabla=document.getElementById('Body_'+tabla);	
	var Trs=Head.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	var Trs1=Tabla.getElementsByTagName('tr');	
	var AltoGral=Gral.AltoCelda?Gral.AltoCelda:Gral.getAttribute("AltoCelda");
	var ruta=Gral.ruta?Gral.ruta:Gral.getAttribute("ruta");		
	
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
				
	var validaNuevo=Gral.validaNuevo?Gral.validaNuevo:Gral.getAttribute("validaNuevo");	
	if(eval(validaNuevo) != true)
		return false;
	var lon=Tds.length;
	
	//Encontramos la ultima fila asignada y asignamos la nueva
	if(Trs1.length > 0)
	{
		var aux=Trs1[Trs1.length-1].id?Trs1[Trs1.length-1].id:Trs1[Trs1.length-1].getAttribute('id');
		aux=aux.split('_');
		aux=aux[1].replace('Fila','');
		var newFila=parseInt(aux)+1;
	}
	else
		var newFila=0;

	//Insertamos el nuevo registro y asignamos propiedades
	var newRow=Tabla.insertRow(-1);
	newRow.className="NormalCell";
	newRow.onmouseover=function(){selfil(this);}
	newRow.onmouseout=function(){dselfil(this);}
	newRow.id=tabla+'_Fila'+newFila;	
	
	//Asignamos un nuevo Id
	if(Trs1.length > 1)
	{
		var Tds1=Trs1[Trs1.length-2].getElementsByTagName('td');	
		var aux=Tds1[1].id.split('_');
		var newId=parseInt(aux[2])+1;
	}
	else
		var newId=0;
	
	var datos="";
	
	//newRow.innerHTML="<td>1</td><td>23</td>";
	//newRow.setAttribute(innerHTML, "<td>1</td><td>23</td>");
	
	//Insertamos la celda numerica
	var newCell=newRow.insertCell(0);
	newCell.innerHTML=Trs1.length;
	newCell.className='Contador';
	newCell.align="center";
	newCell.width="21";	
	
	
	//Insertamos las demas celdas
	for(j=1;j<lon;j++)
	{
		//Insertamos la fila y asignamos los atributos
		var newCell=newRow.insertCell(j);		
		var largo=Tds[j].offsetWidth-2;	
		if(largo == -2)		
			largo=Tds[j].width;			
		var idcelda=tabla+'_'+(j-1)+'_'+newId;
		tipo=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");
		mods=Tds[j].modificable?Tds[j].modificable:Tds[j].getAttribute("modificable");
		mask=Tds[j].mascara?Tds[j].mascara:Tds[j].getAttribute("mascara");
		alin=Tds[j].align?Tds[j].align:Tds[j].getAttribute("align");
		inicial=Tds[j].inicial?Tds[j].inicial:Tds[j].getAttribute("inicial");
		if(tipo == 'oculto')
		{	
			largo="0px";
			newCell.style.border="none";			
		}
		if(largo >= 0)
		{
			newCell.width=largo;
			newCell.setAttribute('width', largo);
			//alert('Ok');
			//newCell.setAttribute('offsetWidth', largo);			
		}
		newCell.height=AltoGral;
		newCell.setAttribute('height', AltoGral);
		newCell.onclick=function(){EditarValor(this);}		
		newCell.id=idcelda;
		newCell.setAttribute('id', idcelda);
		newCell.align=alin;	
		
		
		if(inicial != null && inicial != 'null' && inicial != "")		
		{			
			newCell.valor=inicial;
			newCell.setAttribute('valor',inicial);
			if(mask!=""&&mask!="null")
				newCell.innerHTML=Mascara(mask,inicial);
			else if(inicial=='NOW(Y-m-d)'&&tipo=="date")
				newCell.innerHTML="&nbsp;";
			else	
				newCell.innerHTML=inicial;			
		}
		else
		{
			newCell.valor="";
			newCell.setAttribute('valor', "");
			newCell.innerHTML='&nbsp;';
		}

		if(tipo == 'libre')
			newCell.onmouseover=function(){asignaActivo(this);}
		
		//asignamos el valor HTMl dependiendo del tipo de dato
		if(tipo == 'binario')
		{
			newCell.innerHTML+='<input type="checkbox" value="SI" onBlur="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" class="check">';
		}
		else if(tipo == 'eliminador')
		{
			newCell.innerHTML='<img src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+idcelda+'\')" id="c'+idcelda+'" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">';
		}
		else if(tipo == 'libre')
		{
			vht=Tds[j].vht?Tds[j].vht:Tds[j].getAttribute("vht");
			while(vht.indexOf('_COMA_', '"') != -1)
				vht=vht.replace('_COMA_', '"');
			newCell.innerHTML=vht;	
		}
		else if(tipo == 'oculto')
			newCell.innerHTML="";
		/*else
			newCell.innerHTML='&nbsp;';*/
		
	}
	
	//Funcion Post-Insercion
	var pos=Trs1.length-1;//MS
	funcionpost=(Gral.getAttribute("despuesInsertar"))?Gral.getAttribute("despuesInsertar"):"";
	if(funcionpost!="")
		funcionpost=funcionpost.replace('#',pos);
		eval(funcionpost);
	
	//Ponemos el foco a la primera celda de la nueva fila
	for(lk=0;lk<lon;lk++)
	{
		var cabeaux=document.getElementById("H_"+tabla+lk);
		var tipoaux=cabeaux.tipo?cabeaux.tipo:cabeaux.getAttribute('tipo');
		//alert(tipoaux);
		if(tipoaux != 'eliminador' && tipoaux != 'oculto' && tipoaux != 'libre')
		{
			//alert("Final: "+tipoaux);
			break;
		}
	}
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;						
			if(ovFi > ovOr && scroll_activo == 0 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)+17;
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='1';
				Gral.setAttribute('scroll_activo', '1');
			}			
		}
	}
	/**/
	
	obj=document.getElementById(tabla+'_'+lk+'_'+newFila);	
	//alert(tabla+'_'+lk+'_'+newFila);
	EditarValor(obj);
	/*aux=document.getElementById('c'+tabla+'_'+lk+'_'+newFila);
	if(aux)
		aux.focus();*/
}

function InsertaFilaNoVal(tabla)
{	
	//alert('$');
	//Conseguimos las tabla general, de cabeceras y de datos; ademas de los datos necesarios
	var Gral=document.getElementById(tabla);
	var Head=document.getElementById('Head_'+tabla);
	var lis=Gral.listado?Gral.listado:Gral.getAttribute('listado');	
	var Tabla=document.getElementById('Body_'+tabla);	
	var Trs=Head.getElementsByTagName('tr');






	var Tds=Trs[0].getElementsByTagName('td');
	var Trs1=Tabla.getElementsByTagName('tr');	
	var AltoGral=Gral.AltoCelda?Gral.AltoCelda:Gral.getAttribute("AltoCelda");
	var ruta=Gral.ruta?Gral.ruta:Gral.getAttribute("ruta");	
	
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
				
	var validaNuevo=Gral.validaNuevo?Gral.validaNuevo:Gral.getAttribute("validaNuevo");		
	var lon=Tds.length;
	
	//Encontramos la ultima fila asignada y asignamos la nueva
	if(Trs1.length > 0)
	{
		var aux=Trs1[Trs1.length-1].id?Trs1[Trs1.length-1].id:Trs1[Trs1.length-1].getAttribute('id');
		aux=aux.split('_');
		aux=aux[1].replace('Fila','');
		var newFila=parseInt(aux)+1;
	}
	else
		var newFila=0;

	//Insertamos el nuevo registro y asignamos propiedades
	var newRow=Tabla.insertRow(-1);
	newRow.className="NormalCell";
	newRow.onmouseover=function(){selfil(this);}
	newRow.onmouseout=function(){dselfil(this);}
	newRow.id=tabla+'_Fila'+newFila;	
	
	//Asignamos un nuevo Id
	if(Trs1.length > 1)
	{
		var Tds1=Trs1[Trs1.length-2].getElementsByTagName('td');	
		var aux=Tds1[1].id.split('_');
		var newId=parseInt(aux[2])+1;
	}
	else
		var newId=0;
	
	var datos="";
	
	//newRow.innerHTML="<td>1</td><td>23</td>";
	//newRow.setAttribute(innerHTML, "<td>1</td><td>23</td>");
	
	//Insertamos la celda numerica
	var newCell=newRow.insertCell(0);
	newCell.innerHTML=Trs1.length;
	newCell.className='Contador';
	newCell.align="center";
	newCell.width="21";	
	
	
	//Insertamos las demas celdas
	for(j=1;j<lon;j++)
	{
		//Insertamos la fila y asignamos los atributos
		var newCell=newRow.insertCell(j);		
		var largo=Tds[j].offsetWidth-2;	
		if(largo == -2)		
			largo=Tds[j].width;			
		var idcelda=tabla+'_'+(j-1)+'_'+newId;
		tipo=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");
		mods=Tds[j].modificable?Tds[j].modificable:Tds[j].getAttribute("modificable");
		mask=Tds[j].mascara?Tds[j].mascara:Tds[j].getAttribute("mascara");
		alin=Tds[j].align?Tds[j].align:Tds[j].getAttribute("align");
		inicial=Tds[j].inicial?Tds[j].inicial:Tds[j].getAttribute("inicial");
		if(tipo == 'oculto')
		{	
			largo="0px";
			newCell.style.border="none";			
		}
		if(largo >= 0)
		{
			newCell.width=largo;
			newCell.setAttribute('width', largo);
			//alert('Ok');
			//newCell.setAttribute('offsetWidth', largo);			
		}
		newCell.height=AltoGral;
		newCell.setAttribute('height', AltoGral);
		newCell.onclick=function(){EditarValor(this);}		
		newCell.id=idcelda;
		newCell.setAttribute('id', idcelda);
		newCell.align=alin;	
		
		if(inicial != null && inicial != 'null' && inicial != "")
		{
			newCell.valor=inicial;
			newCell.setAttribute('valor',inicial);
		}
		else
		{
			newCell.valor="";
			newCell.setAttribute('valor', "");
		}

		if(tipo == 'libre')
			newCell.onmouseover=function(){asignaActivo(this);}
		
		//asignamos el valor HTMl dependiendo del tipo de dato
		if(tipo == 'binario')
		{
			newCell.innerHTML+='<input type="checkbox" value="SI" onBlur="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" class="check">';
		}
		else if(tipo == 'eliminador')
		{
			newCell.innerHTML='<img src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+idcelda+'\')" id="c'+idcelda+'" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">';
		}
		else if(tipo == 'libre')
		{
			vht=Tds[j].vht?Tds[j].vht:Tds[j].getAttribute("vht");
			while(vht.indexOf('_COMA_', '"') != -1)
				vht=vht.replace('_COMA_', '"');
			newCell.innerHTML=vht;	
		}
		else if(tipo == 'oculto')
			newCell.innerHTML="";
		else
			newCell.innerHTML='&nbsp;';
		
	}
	
	//Ponemos el foco a la primera celda de la nueva fila
	for(lk=0;lk<Trs.length;lk++)
	{
		var cabeaux=document.getElementById("H_"+tabla+lk);
		var tipoaux=cabeaux.tipo?cabeaux.tipo:cabeaux.getAttribute('tipo');
		if(tipoaux != 'eliminador' && tipoaux != 'oculto' && tipoaux != 'libre')
			break;
	}
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;						
			if(ovFi > ovOr && scroll_activo == 0 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)+17;
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='1';
				Gral.setAttribute('scroll_activo', '1');
			}			
		}
	}
	/**/
	
	obj=document.getElementById(tabla+'_'+lk+'_'+newFila);	
	EditarValor(obj);
	aux=document.getElementById('c'+tabla+'_'+lk+'_'+newFila);
	if(aux)
		aux.focus();
}

function InsertaFilaNoValExt(objext,tabla)
{	
	//Conseguimos las tabla general, de cabeceras y de datos; ademas de los datos necesarios
	var Gral=objext.document.getElementById(tabla);
	var Head=objext.document.getElementById('Head_'+tabla);
	var lis=Gral.listado?Gral.listado:Gral.getAttribute('listado');	
	var Tabla=objext.document.getElementById('Body_'+tabla);	
	var Trs=Head.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	var Trs1=Tabla.getElementsByTagName('tr');	
	var AltoGral=Gral.AltoCelda?Gral.AltoCelda:Gral.getAttribute("AltoCelda");
	var ruta=Gral.ruta?Gral.ruta:Gral.getAttribute("ruta");	
	
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
				
	var validaNuevo=Gral.validaNuevo?Gral.validaNuevo:Gral.getAttribute("validaNuevo");		
	var lon=Tds.length;
	
	//Encontramos la ultima fila asignada y asignamos la nueva
	if(Trs1.length > 0)
	{
		var aux=Trs1[Trs1.length-1].id?Trs1[Trs1.length-1].id:Trs1[Trs1.length-1].getAttribute('id');
		aux=aux.split('_');
		aux=aux[1].replace('Fila','');
		var newFila=parseInt(aux)+1;
	}
	else
		var newFila=0;

	//Insertamos el nuevo registro y asignamos propiedades
	var newRow=Tabla.insertRow(-1);
	newRow.className="NormalCell";
	newRow.onmouseover="selfil(this)";	
	newRow.onmouseout="dselfil(this)";
	newRow.id=tabla+'_Fila'+newFila;	
	
	//Asignamos un nuevo Id
	if(Trs1.length > 1)
	{
		var Tds1=Trs1[Trs1.length-2].getElementsByTagName('td');	
		var aux=Tds1[1].id.split('_');
		var newId=parseInt(aux[2])+1;
	}
	else
		var newId=0;
	
	var datos="";
	
	//newRow.innerHTML="<td>1</td><td>23</td>";
	//newRow.setAttribute(innerHTML, "<td>1</td><td>23</td>");
	
	//Insertamos la celda numerica
	var newCell=newRow.insertCell(0);
	newCell.innerHTML=Trs1.length;
	newCell.className='Contador';
	newCell.align="center";
	newCell.width="21";	
	
	
	//Insertamos las demas celdas
	for(j=1;j<lon;j++)
	{
		//Insertamos la fila y asignamos los atributos
		var newCell=newRow.insertCell(j);		
		var largo=Tds[j].offsetWidth-2;	
		if(largo == -2)		
			largo=Tds[j].width;			
		var idcelda=tabla+'_'+(j-1)+'_'+newId;
		tipo=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");
		mods=Tds[j].modificable?Tds[j].modificable:Tds[j].getAttribute("modificable");
		mask=Tds[j].mascara?Tds[j].mascara:Tds[j].getAttribute("mascara");
		alin=Tds[j].align?Tds[j].align:Tds[j].getAttribute("align");
		inicial=Tds[j].inicial?Tds[j].inicial:Tds[j].getAttribute("inicial");
		if(tipo == 'oculto')
		{	
			largo="0px";
			newCell.style.border="none";			
		}
		if(largo >= 0)
		{
			newCell.width=largo;
			newCell.setAttribute('width', largo);
			//alert('Ok');
			//newCell.setAttribute('offsetWidth', largo);			
		}
		newCell.height=AltoGral;
		newCell.setAttribute('height', AltoGral);
		newCell.onclick=function(){EditarValor(this);}		
		newCell.id=idcelda;
		newCell.setAttribute('id', idcelda);
		newCell.align=alin;	
		
		if(inicial != null && inicial != 'null' && inicial != "")
		{
			newCell.valor=inicial;
			newCell.setAttribute('valor',inicial);
		}
		else
		{
			newCell.valor="";
			newCell.setAttribute('valor', "");
		}

		if(tipo == 'libre')
			newCell.onmouseover=function(){asignaActivo(this);}
		
		//asignamos el valor HTMl dependiendo del tipo de dato
		if(tipo == 'binario')
		{
			newCell.innerHTML+='<input type="checkbox" value="SI" onBlur="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" class="check">';
		}
		else if(tipo == 'eliminador')
		{
			newCell.innerHTML='<img src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+idcelda+'\')" id="c'+idcelda+'" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">';
		}
		else if(tipo == 'libre')
		{
			vht=Tds[j].vht?Tds[j].vht:Tds[j].getAttribute("vht");
			while(vht.indexOf('_COMA_', '"') != -1)
				vht=vht.replace('_COMA_', '"');
			newCell.innerHTML=vht;	
		}
		else if(tipo == 'oculto')
			newCell.innerHTML="";
		else
			newCell.innerHTML='&nbsp;';
		
	}
	
	//Ponemos el foco a la primera celda de la nueva fila
	for(lk=0;lk<Trs.length;lk++)
	{
		var cabeaux=objext.document.getElementById("H_"+tabla+lk);
		var tipoaux=cabeaux.tipo?cabeaux.tipo:cabeaux.getAttribute('tipo');
		if(tipoaux != 'eliminador' && tipoaux != 'oculto' && tipoaux != 'libre')
			break;
	}
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;						
			if(ovFi > ovOr && scroll_activo == 0 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)+17;				
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='1';
				Gral.setAttribute('scroll_activo', '1');
			}			
		}
	}
	/**/
	
	obj=objext.document.getElementById(tabla+'_'+lk+'_'+newFila);	
	EditarValor(obj);
	aux=objext.document.getElementById('c'+tabla+'_'+lk+'_'+newFila);
	if(aux)
		aux.focus();
}

function EliminaFila(cad)
{
	//Separamos los elementos del id	
	var aux=cad.split("_");
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	
	//Conseguimos la tabla principal
	var Gral=document.getElementById(tabla);
	var Tabla=document.getElementById('Body_'+tabla);	
	var validaElimina=Gral.validaElimina?Gral.validaElimina:Gral.getAttribute("validaElimina");	
	var despuesEliminar=Gral.despuesEliminar?Gral.despuesEliminar:Gral.getAttribute("despuesEliminar");
	
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
	
	//Obtenemos la fila y la posicion que ocupa
	var obj=document.getElementById(tabla+'_Fila'+fila);	
	var filaB=obj.rowIndex?obj.rowIndex:obj.getAttribute('rowIndex');	
	if(filaB == null)
		filaB=0;
	if(eval(validaElimina.replace('#',filaB)) != true)
		return false;	
		
	
	hobj=document.getElementById("Head_"+tabla);
	var hTrs=hobj.getElementsByTagName('tr');
	var hTds=hTrs[0].getElementsByTagName('td');
	for(i=1;i<hTds.length;i++)	
	{
		var maskara=hTds[i].mascara?hTds[i].mascara:hTds[i].getAttribute("mascara");		
		var verSum=hTds[i].verSumatoria?hTds[i].verSumatoria:hTds[i].getAttribute("verSumatoria");
		if(verSum == "S")
		{
			hobj=document.getElementById('F'+tabla+(i-1));
			var val=hobj.value;
			if(maskara != "null" && maskara != null && maskara.length > 0)
			{
				for(j=0;j<maskara.length;j++)
				{
					if(maskara.charAt(j) != '.')
					{
						while(val.indexOf(maskara.charAt(j)) != -1)	
							val=val.replace(maskara.charAt(j), '');
					}
				}
			}
		    hobj=document.getElementById(tabla+'_'+(i-1)+'_'+fila);
			val=parseFloat(val);	
			val-=isNaN(parseFloat(hobj.valor?hobj.valor:hobj.getAttribute('valor')))?0:parseFloat(hobj.valor?hobj.valor:hobj.getAttribute('valor'));
			hobj=document.getElementById('F'+tabla+(i-1));
			if(maskara != "null" && maskara != null && maskara.length > 0)
				hobj.value=Mascara(maskara, ""+val+"");
			else
				hobj.value=val;			
		}
	}
		
	//Borramos la fila
	Tabla.deleteRow(filaB);	
	
	//Reasignamos los numeros de la izquierda
	var Trs=Tabla.getElementsByTagName('tr');
	if(filaB != Trs.length)
	{			
		for(var i=filaB;i<Trs.length;i++)
		{
			var Tds=Trs[i].getElementsByTagName('td');
			Tds[0].innerHTML=i+1;			
		}
	}	
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;				
			if(ovFi <= ovOr && scroll_activo == 1 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)-17;
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='0';
				Gral.setAttribute('scroll_activo', '0');
			}			
		}
	}
	/**/	
	
	if(despuesEliminar != '')
		eval(despuesEliminar);
}

function selfil(obj)
{
	if(obj.className != 'actCell')	
		obj.className='cellMouse';	

}

function dselfil(obj)
{
	if(obj.className != 'actCell')
		obj.className='NormalCell';
}


function ObtenDatosCombo(metodo, url, parametros , res, pos, fil)
{
	var vals=new Array();
	//alert(url+parametros);
	var aux=ajaxR(url+parametros);
	var ax=aux;
	//alert(ax);
	if(fil == null)
		eval('document.getElementById("'+res+'").auxiliar_'+pos+'="'+aux+'";');
	aux=aux.split('|');
	if(aux[0] != 'exito')	
		alert(ax);
	//alert(aux);	
	for(var f=2;f<aux.length;f++)	
	{
		var ax=aux[f].split('~');
		vals[ax[0]]=ax[1];
	}
	var sdf=document.getElementById('Body_'+res);
	var Tr=sdf.getElementsByTagName('tr');	
	if(fil == null)
	{		
		for(var f=0;f<Tr.length;f++)
		{
			Td=Tr[f].getElementsByTagName('td');
			var vax=Td[pos].valor?Td[pos].valor:Td[pos].getAttribute("valor");
			Td[pos].innerHTML=vals[vax];
		}
	}
	else
	{		
		Td=Tr[fil].getElementsByTagName('td');												
		var vax=Td[pos].valor?Td[pos].valor:Td[pos].getAttribute("valor");												
		Td[pos].innerHTML=vals[vax];
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

function calendario(objeto){
    Calendar.setup({
        inputField     :    objeto.id,
        ifFormat       :    "%Y-%m-%d",
        align          :    "BR",
        singleClick    :    true
	});
}


function ocultaBus(val, id)
{
	var aux=id.split('_');
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);
	var yreal=yReal(id);
	var cab=document.getElementById("H_"+tabla+columna);	
	var funcam=cab.onChange?cab.onChange:cab.getAttribute('onChange');	
	var obj = document.getElementById('c'+id);
	var nver = false;
	
	obj.value=val;
	obj = document.getElementById(id);	
	/*var pos=obj.innerHTML.toUpperCase().indexOf('<div'.toUpperCase());*/
	if(obj.valor != val)
		nver=true;
	obj.innerHTML=val;
	obj.valor=val;
	obj.setAttribute('valor', val);
	obj=document.getElementById(tabla);
	obj.elefoc='';
	obj.setAttribute('elefoc', '');
	var regexp=/^function/;
	if(regexp.test(funcam))
	{
		arrfun=funcam.split("{");
		arrfun=arrfun[1];
		arrfun=arrfun.split("}");
		funcam=arrfun[0];
		funcam=funcam.replace(';\n', "");
		funcam=funcam.replace('\n', "");
		cab.setAttribute("onChange",funcam);
	}
	if(funcam != 'null' && funcam != null && funcam.length > 0 && nver == true)
	{
		aux=funcam;
		while(aux.indexOf('#') != -1)
			aux=aux.replace('#', yreal);		
		eval(aux);
	}
	//alert(id);
	EditarValor(document.getElementById(id));	
	obj=document.getElementById('c'+id);
	obj.focus();
}

function teclasEspBus(eve, cad, obj)
{
	//var pos=obj.innerHTML.toUpperCase().indexOf('<div'.toUpperCase());
	var aux=cad.split('_');
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	var key=(eve.which) ? eve.which : eve.keyCode;
	if(key == 13)
		ocultaBus(obj.value, cad);	
	else if(key == 27)	
	{
		var celda=document.getElementById(cad);			
		var pos=celda.innerHTML.toUpperCase().indexOf('<SELECT'.toUpperCase());
		celda.innerHTML=celda.innerHTML.substring(0,pos);
		setTimeout("document.getElementById('c"+cad+"').focus();", 100);
		aux=document.getElementById(tabla);
		aux.elefoc='';
		aux.setAttribute('elefoc', '');
	}
}

function OcultaBusSC(cad)
{
	//alert(cad);
	var aux=cad.split('_');
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	
	var obj=document.getElementById('c'+cad);
	var ax=obj.value;	
	if(parseInt(obj.extra) == 1)
		return true;	
	var obj=document.getElementById(tabla);	
	obj.elefoc='';
	obj.setAttribute('elefoc', '');
	var celda=document.getElementById(cad);
	celda.innerHTML=ax;
	celda.valor=ax;
	celda.setAttribute('valor', ax);
	//setTimeout("document.getElementById('c"+cad+"').focus();", 100);
}

function RecargaGrid(tabla, filar)
{
	
	var lons=new Array();
	var tipos=new Array();
	var alins=new Array();
	var mask=new Array();
	var mods=new Array();
	var htmls=new Array();
	var depen=new Array();
	var getCom=new Array();	
	var formulas=new Array();
	var verSum=new Array();
	var vht=new Array();	
	var SumGral=new Array();
	var dblclick = new Array();
	var htmldb =new Array();
	var multiseleccion =new Array();
	
	var obj=document.getElementById(tabla);		
	var cellP=obj.cellpadding?obj.cellpadding:obj.getAttribute("cellpadding");
	var cellS=obj.cellspacing?obj.cellspacing:obj.getAttribute("cellspacing");
	var borde=obj.border?obj.border:obj.getAttribute("border");
	var brcolor=obj.bordercolor?obj.bordercolor:obj.getAttribute("bordercolor");
	var AltoGral=obj.Alto?obj.Alto:obj.getAttribute("Alto");
	var alto=obj.AltoCelda?obj.AltoCelda:obj.getAttribute("AltoCelda");
	var conScroll=obj.conScroll?obj.conScroll:obj.getAttribute("conScroll");
	var ruta=obj.ruta?obj.ruta:obj.getAttribute("ruta");
	var validaElimina=obj.validaElimina?obj.validaElimina:obj.getAttribute("validaElimina");
	var validaNuevo=obj.validaElimina?obj.validaElimina:obj.getAttribute("validaElimina");	
	var DatosFile=obj.Datos?obj.Datos:obj.getAttribute("Datos");
	var verFooter=obj.verFooter?obj.verFooter:obj.getAttribute("verFooter");
	
	var paginador=obj.paginador?obj.paginador:obj.getAttribute("paginador");
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute("pagAct");
	var datosxPag=obj.datosxPag?obj.datosxPag:obj.getAttribute("datosxPag");
	var pagMetodo=obj.pagMetodo?obj.pagMetodo:obj.getAttribute("pagMetodo");
	pagMetodo=(pagMetodo == '')?'javascript':pagMetodo;	
	var ordenaPHP=obj.ordenaPHP?obj.ordenaPHP:obj.getAttribute("ordenaPHP");
	var ordenCampo=obj.ordenCampo?obj.ordenCampo:obj.getAttribute("ordenCampo");
	var sentidoOr=obj.sentidoOr?obj.sentidoOr:obj.getAttribute("sentidoOr");
	
	
	
	
	if(filar == '')
		filar=obj.Datos?obj.Datos:obj.getAttribute("Datos");	
	
	//alert(filar);
	
	if(filar.length <= 0 || filar == null)
		return false
		
	obj.setAttribute('Datos', filar);	
	
	if((paginador == 'S' || paginador == 's') && pagMetodo == 'php')
	{
		var iniGl=(parseInt(pagAct)-1)*parseInt(datosxPag);		
		//alert(ordenaPHP);
		if(ordenaPHP == 'S' || ordenaPHP == 's')
			var datosF=ajaxR(filar+'&ini='+iniGl+'&fin='+datosxPag+'&dxp='+datosxPag+'&orderGRC='+ordenCampo+'&sentidoOr='+sentidoOr);
		else
			var datosF=ajaxR(filar+'&ini='+iniGl+'&fin='+datosxPag+'&dxp='+datosxPag);
		var arrF=datosF.split('|');				
		var auxGl=arrF[arrF.length-1];
		auxGl=auxGl.split('~');
		numDatos=parseInt(auxGl[0]);
		numMos=iniGl+parseInt(auxGl[1]);
		if(datosxPag==-1)
			numPag=1;
		else
			numPag=numDatos/datosxPag;
		arrF.pop();
		var indk=iniGl;
		iniGl+=1;		
	}
	else
	{
		var datosF=ajaxR(filar);
		var arrF=datosF.split('|');
		var indk=0;
	}
	if(datosF.length <= 0)
		return false;
	
	var obj=document.getElementById(tabla);	
	
	
	
	var cabs=document.getElementById('Head_'+tabla);	
	Trs=cabs.getElementsByTagName('tr');
	Tds=Trs[0].getElementsByTagName('td');
	for(var i=1;i<Tds.length;i++)
	{
		lons[i-1]=(Tds[i].offsetWidth?Tds[i].offsetWidth:Tds[i].getAttribute('offsetWidth'))-2;
		tipos[i-1]=Tds[i].tipo?Tds[i].tipo:Tds[i].getAttribute('tipo');
		alins[i-1]=Tds[i].align?Tds[i].align:Tds[i].getAttribute('align');
		mask[i-1]=Tds[i].mascara?Tds[i].mascara:Tds[i].getAttribute('mascara');
		if(mask[i-1] == "null")
			mask[i-1]=null;
		verSum[i-1]=Tds[i].verSumatoria?Tds[i].verSumatoria:Tds[i].getAttribute('verSumatoria');
		if(verSum[i-1] == "null")
			verSum[i-1]=null;	
		mods[i-1]=Tds[i].modificable?Tds[i].modificable:Tds[i].getAttribute('modificable');	
		htmls[i-1]=Tds[i].vht?Tds[i].vht:Tds[i].getAttribute('vht');
		if(htmls[i-1] == null)
			htmls[i-1]="";
		while(htmls[i-1].indexOf('_COMA_', '"') != -1)
			htmls[i-1]=htmls[i-1].replace('_COMA_', '"');
		depen[i-1]=Tds[i].depende?Tds[i].depende:Tds[i].getAttribute('depende');
		if(depen[i-1] == "null")
			depen[i-1]=null;
		getCom[i-1]=Tds[i].datosdb?Tds[i].datosdb:Tds[i].getAttribute('datosdb');
		formulas[i-1]=Tds[i].formula?Tds[i].formula:Tds[i].getAttribute('formula');
		vht[i-1]=Tds[i].vht?Tds[i].vht:Tds[i].getAttribute('vht');
		dblclick[i-1]=Tds[i].dobleClick?Tds[i].dobleClick:Tds[i].getAttribute('dobleClick');
		if(formulas[i-1] == "null")
			formulas[i-1]=null;
		htmldb[i-1]=Tds[i].htmldebase?Tds[i].htmldebase:Tds[i].getAttribute('htmldebase');//MS
		if(htmldb[i-1] == "null")
			htmldb[i-1]=null;
	}
	
	var obj=document.getElementById("celda_"+tabla+"_Datos");
	var NewTabla="";
	var altomenos=20;
	if(verFooter == "S")
		altomenos=40;
	if(paginador == 'S' || paginador == 's')	
		altomenos+=25;
		
	if(conScroll == 'S')
	{
		NewTabla='<div id="div_'+tabla+'_overflow_Y" style="overflow:auto; height:'+(AltoGral-altomenos)+'px; padding:0">';		
		var onlyDiv='<div id="div_'+tabla+'_overflow_Y" style="overflow:auto; height:'+(AltoGral-altomenos)+'px; padding:0"></div>';
	}
	NewTabla+='<table cellpadding="'+cellP+'" border="'+borde+'" bordercolor="'+brcolor+'" cellspacing="'+cellS+'" id="Body_'+tabla+'" >';	
	
	
	if(arrF[0] == 'exito')
	{
		var limMos=arrF.length;
		var inicont=1;
		if((paginador == 'S' || paginador == 's') && pagMetodo == 'javascript')
		{			
			limMos=parseInt(datosxPag)+1+(parseInt(pagAct)-1)*parseInt(datosxPag);
			inicont=(parseInt(pagAct)-1)*parseInt(datosxPag)+1;
			iniGl=inicont;
			numPag=(arrF.length-1)/datosxPag;
			numDatos=arrF.length-1;
			if(numDatos < (limMos-1))
			{
				numMos=numDatos;
				limMos=numDatos+1;
			}
			else
				numMos=limMos-1;
			//alert(numDatos+' | '+inicont+' => '+limMos);
		}	
		for(k=inicont;k<limMos;k++)
		{
			var axF=arrF[k].split('~');
			NewTabla+='<tr class="NormalCell" height="'+alto+'" onmouseover="selfil(this);" onmouseout="dselfil(this);" class="NormalCell" id="'+tabla+'_Fila'+((k+indk)-1)+'">';
			NewTabla+='<td width="21px" class="Contador" align="center">'+(k+indk)+'</td>';
			for(l=0;l<axF.length;l++)
			{
				
				if(k == 1)
					SumGral[l]=0;
				var idcelda=tabla+'_'+l+'_'+((k+indk)-1);
				if(verSum[l] == 'S')
					SumGral[l]+=parseFloat(axF[l]);
				
				//alert('ondblclick="analizaDBLCLICK(\''+dblclick[l]+'\', this)"');	
				NewTabla+='<td width="'+lons[l]+'" height="'+alto+'" ondblclick="analizaDBLCLICK(\''+dblclick[l]+'\', this)"';
				if(htmldb[l]!="S"&&axF[l]!=-1&&tipos[l] != 'binario')//MS
					NewTabla+=' onclick="EditarValor(this);"';
				if(tipos[l] == 'libre')
					NewTabla+=" onmouseover='asignaActivo(this);'";
				else if(tipos[l] == 'oculto')
					NewTabla+='style="border-left:none;border-right:none;"';
				NewTabla+='id="'+idcelda+'" align="'+alins[l];
				if(tipos[l]!='libre'&&htmldb[l]!="S")//MS
					NewTabla+='" valor="'+axF[l];
				NewTabla+='">';	///////////////*******************************************************************										
				if(tipos[l] == 'binario')//MS
				{
					if(axF[l] == "0")
					{
						var sel="";
						var valor=0;
					}
					else
					{
						var sel="checked";
						var valor=1
					}
					var extra="";
					if(mods[l] != 'S')

						extra='disabled';
					if(htmldb[l]=="S"&&axF[l]==-1)
						NewTabla+='&nbsp;';
					else	
					NewTabla+='<input type="checkbox" value="SI" '+sel+' onclick="DesEditaCelda(\''+idcelda+'\', this);" id="c'+idcelda+'" onkeydown="TeclasEspeciales(event, this, \''+idcelda+'\');" valor='+valor+'  class="check" '+extra+'>';
				}////////=***********************************************************************
				else if(tipos[l] == 'libre')//MS
				{							
					if(htmldb[l]!="S")
						NewTabla+=htmls[l];
					else
					{
						NewTabla+=axF[l];
					}
				}
				else if(tipos[l] == 'oculto')
					NewTabla+="";
				else
				{
					if(axF[l].length == 0 || axF[l] == " ")
						NewTabla+='&nbsp;';
					else if(mask[l] != null)
						NewTabla+=Mascara(mask[l], axF[l]);			
					else
						NewTabla+=axF[l];
				}								
				NewTabla+="</td>";		
			}
			if(validaElimina != 'false')
				NewTabla+='<td id="'+tabla+'_'+l+'_'+((k+indk)-1)+'" width="15px" align="center"><img id="c'+tabla+'_'+l+'_'+(k-1)+'" src="'+ruta+'borr2.gif" onclick="EliminaFila(\''+tabla+'_'+l+'_'+(k-1)+'\');" onkeydown="TeclasEspeciales(event, this, \''+tabla+'_'+l+'_'+((k+indk)-1)+'\');" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';"></td>';
			NewTabla+="</tr>";
		}
	}
	else
		alert("No se pudo cargar datos desde "+filar+"\nDescripcion del Error:\n\n"+datosF);
	

	NewTabla+="</table>";	
	if(conScroll == 'S')
		NewTabla+="</div>";
		
	obj.innerHTML=onlyDiv;	
	
	if(/*navigator.appName == 'Netscape'*/1)
	{		
		widthAnt=obj.offsetWidth?obj.offsetWidth:obj.getAttribute("offsetWidth");
		if(widthAnt == 0)
			widthAnt=obj.width;
		obj.scroll_activo="0";
		obj.setAttribute('scroll_activo',0);	
		//alert(widthAnt);
	}
	
	obj.innerHTML=NewTabla;
	
	if(paginador == 'S' || paginador == 's')
	{
		
		/*caux+='&nbsp;<img alto="20" height="20" src="'+ruta+'first.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">&nbsp;';
		caux+='<img alto="20" height="20" src="'+ruta+'prev.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">&nbsp;';
		caux+='<td class="paginas_paginador_GridRC">';
		caux+='&nbsp;P&aacute;gina <input type="text" class="textbox_paginador_GridRC" size="1" onkeyup="enteros(this);" value="1" id="pagact_paginador_GridRC"> de ';
		caux+='<span id="numpages_paginador_'+NomId+'">1</span>';
		caux+='<td class="next_paginador_GridRC">';
		caux+='&nbsp;<img alto="20" height="20" src="'+ruta+'next.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">&nbsp;';
		caux+='<img alto="20" height="20" src="'+ruta+'last.png" onmouseover="this.style.cursor=\'hand\'; this.style.cursor=\'pointer\';">&nbsp;';
		caux+='<td class="texo_paginador_GridRC" id="texto_paginador_'+NomId+'">';
		caux+='Mostrando del 1 al '+numMos+' de '+numDatos+' dato(s)';
		caux+='</td></tr></table>';*/

		obj=document.getElementById('numpages_paginador_'+tabla);
		obj.innerHTML=Math.ceil(numPag);		
		
		obj=document.getElementById('numpages_paginador_'+tabla);
		obj.innerHTML=Math.ceil(numPag);
		
		obj=document.getElementById('texto_paginador_'+tabla);
		obj.innerHTML='Mostrando del '+iniGl+' al '+numMos+' de '+numDatos+' dato(s)';
	}
	
	var nver=0;
	var cdg=0;
	var cdp={};
	for(i=0;i<verSum.length;i++)
	{
		//Posiblemente quitar
		nver=0;
		if(verSum[i] == 'S')
		{
			var obj=document.getElementById('F'+tabla+i);	
			SumGral[i]=(isNaN(SumGral[i]))?0:SumGral[i];
			if(mask[i] != null || mask[i] != "null")
				obj.value=Mascara(mask[i], SumGral[i]);	
			else
				obj.value=SumGral[i];
		}
		else if(tipos[i] == 'combo')
		{				
			if((depen[i] == null || depen[i]=="null") && nver == 0)
			{				
				ObtenDatosCombo('POST', getCom[i], '', tabla, i+1, null);
				nver++;
			}
			else if(depen[i] != null)
			{	
				cdp[cdg]=i;
				cdg++;
				/*caux=ajaxR(getCom[i]+'?iniDB=0&finDB=10000&llave='+(Tds[parseInt(depen[i])+1].valor?Tds[parseInt(depen[i])+1].valor:Tds[parseInt(depen[i])+1].getAttribute('valor')));
				alert(Tds[parseInt(depen[i])+1].valor?Tds[parseInt(depen[i])+1].valor:Tds[parseInt(depen[i])+1].getAttribute('valor'));
				var datos=caux.split('|');

				if(datos[0] == 'exito')
				{
					for(k=2;k<datos.length;k++)
					{
						caux=datos[k].split('~');							
						if(parseInt(caux[0]) == parseInt(Tds[j].valor?Tds[j].valor:Tds[j].getAttribute('valor')))
							Tds[j].innerHTML=caux[1];
					}
				}*/
			}
		}
	}
	if(cdg > 0)
	{
		for(i=0;i<NumFilas(tabla);i++)
		{
			for(j=0;j<cdg;j++)
			{
				var cabecera=document.getElementById("H_"+tabla+cdp[j]);
				var vdep=cabecera.depende?cabecera.depende:cabecera.getAttribute('depende');
				var vq=cabecera.datosdb?cabecera.datosdb:cabecera.getAttribute('datosdb');
				var obj=document.getElementById('Body_'+tabla);
				var Trs=obj.getElementsByTagName('tr');
				var Tds=Trs[i].getElementsByTagName('td');
				vdep=parseInt(vdep);
				var val = Tds[vdep+1].valor?Tds[vdep+1].valor:Tds[vdep+1].getAttribute('valor');
				caux=ajaxR(vq+'?iniDB=0&finDB=10000&llave='+val);
				var datos=caux.split('|');				
				if(datos[0] == 'exito')
				{					
					Tds[cdp[j]+1].innerHTML='&nbsp;';
					for(k=2;k<datos.length;k++)
					{
						caux=datos[k].split('~');							
						if(parseInt(caux[0]) == parseInt(Tds[cdp[j]+1].valor?Tds[cdp[j]+1].valor:Tds[cdp[j]+1].getAttribute('valor')))
							Tds[cdp[j]+1].innerHTML=caux[1];
					}
				}				
			}
		}
	}
	
	/*Validamos que con los datos no haya aparecido el scroll*/	
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Tabla=document.getElementById(tabla);
		widthNew=Tabla.offsetWidth?Tabla.offsetWidth:Tabla.getAttribute("offsetWidth");
		if(widthNew == 0)
			widthNew=Tabla.width;
		var obj=document.getElementById('div_'+tabla+'_overflow_Y');
		var shtmp=obj.scrollHeight?obj.scrollHeight:obj.getAttribute('scrollHeight');
		if(shtmp == 0)
			shtmp = obj.style.height.replace('px', '');
		var scract=Tabla.scroll_activo?Tabla.scroll_activo:Tabla.getAttribute('scroll_activo');
		var hOri=Tabla.oFO?Tabla.oFO:Tabla.getAttribute('oFO');		
		if((widthNew-widthAnt) < 17 && shtmp > hOri && scract == 0)
		{				
			var widthNew=parseInt(widthAnt)+17;			
			Tabla.width=widthNew;
			Tabla.setAttribute('width', widthNew);
			Tabla.scroll_activo='1';
			Tabla.setAttribute('scroll_activo', '1');
			
		}
		//Volvemos el ancho al original en caso de estas ampliado y no hay scroll
		if(scract == 1 && shtmp <= hOri)
		{
			var widthNew=parseInt(widthAnt)-17;			
			Tabla.width=widthNew;	
			Tabla.setAttribute('width', widthNew);
			Tabla.scroll_activo='0';
			Tabla.setAttribute('scroll_activo', '0');	
		}			
	}
}

function vaciaGrid(tabla)
{
	var obj=document.getElementById('Body_'+tabla);
	Trs=obj.getElementsByTagName('tr');
	var num=Trs.length;
	for(var i=num-1;i>=0;i--)		
		obj.deleteRow(i);
	obj=document.getElementById('Head_'+tabla);
	Trs=obj.getElementsByTagName('tr');
	Tds=Trs[0].getElementsByTagName('td');
	for(i=0;i<Tds.length;i++)
	{
		if((Tds[i].verSumatoria?Tds[i].verSumatoria:Tds[i].getAttribute('verSumatoria')) == 'S' || (Tds[i].verSumatoria?Tds[i].verSumatoria:Tds[i].getAttribute('verSumatoria')) == 's')
		{
			objF=document.getElementById('F'+tabla+(i-1));
			var mask=Tds[i].mascara?Tds[i].mascara:Tds[i].getAttribute('mascara');
			objF.value=Mascara(mask, 0);
		}
	}
	Gral=document.getElementById(tabla)	
	var wiG=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
	
	Gral.width=wiG;
	Gral.setAttribute('width',wiG);
	Gral.scroll_activo='0';
	Gral.setAttribute('scroll_activo', '0');
	
	
	/*Gral.width=widthNew;
	Gral.setAttribute('width', widthNew);*/
}

function Rebusca(eve, cad, obj)
{	
	var key=(eve.which) ? eve.which : eve.keyCode;
	
	var aux=cad.split("_");
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);			
	
	var cabecera=document.getElementById("H_"+tabla+columna);

	var celda=document.getElementById(cad);	
	var aux= celda.innerHTML;
	
	if(aux.toUpperCase().indexOf('<SELECT'.toUpperCase()) != -1)
	{	
		var datosdb=cabecera.datosdb?cabecera.datosdb:cabecera.getAttribute('datosdb');
		var rfile=datosdb;
		if(rfile.indexOf('"') != -1 || rfile.indexOf("'") != -1)
			rfile=eval(datosdb);
		//Pasos buscador dependiente
		var depende=cabecera.depende?cabecera.depende:cabecera.getAttribute("depende");
		var multidependencia=cabecera.multidependencia?cabecera.multidependencia:cabecera.getAttribute("multidependencia");
		if(depende!=null&&depende!="null"&&depende!=""&&depende!=0)
		{
			if(multidependencia=="S")
			{
				arrDepende=depende.split("|");
				var celdaDependencia,valor;
				rfile+="&nodependencias="+arrDepende.length;
				for(var i=0;i<arrDepende.length;i++)
				{
					celdaDependencia=document.getElementById(tabla+"_"+arrDepende[i]+"_"+fila);
					valor=celdaDependencia.valor?celdaDependencia.valor:celdaDependencia.getAttribute("valor");						
					if(valor!=null&&valor!="null"&&valor!="")
						rfile+="&llaveaux"+i+"="+valor;
				}
			}
			else
			{
				depende=parseInt(depende)-1;
				var celdaDependencia=document.getElementById(tabla+"_"+depende+"_"+fila);
				var valor=celdaDependencia.valor?celdaDependencia.valor:celdaDependencia.getAttribute("valor");
				if(valor!=null&&valor!="null"&&valor!="")
					rfile+="&llaveaux="+valor;
			}
		}
		var res=ajaxR(rfile+'&llave='+obj.value);
		var aux=res.split('|');
		if(aux[0] == 'exito')
		{					
			var combo=document.getElementById('com'+cad);
			combo.options.length=0;
			for(i=2;i<aux.length;i++)
				combo.options[i-2] = new Option(aux[i], aux[i]);

		}
		else
			alert("No se pudo cargar datos desde "+rfile+'&llave='+obj.value+"\n\n"+res);
	}	
}

function celdaValorXY(tabla, px, py)
{
	var rpx=px+1;
	var obj=document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0 && py < Trs.length)
	{
		var Tds=Trs[py].getElementsByTagName('td');
		if(Tds.length > 0 && rpx < Tds.length)
			return(Tds[rpx].valor?Tds[rpx].valor:Tds[rpx].getAttribute('valor'));
		else
			return null;
	}
	else
		return null;
}

function TextXY(tabla, rpx, py)
{
	var px=rpx+1;
	var obj=document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0 && py < Trs.length)
	{
		var Tds=Trs[py].getElementsByTagName('td');
		if(Tds.length > 0 && px < Tds.length)
		{
			obj=document.getElementById('c'+tabla+'_'+px+'_'+py);
			if(obj)
				return(obj.value);
			else	
				return(Tds[px].innerHTML?Tds[px].innerHTML:Tds[px].getAttribute('innerHTML'));
		}
		else
			return null;
	}
	else
		return null;
}

function valorXY(tabla, rpx, py, valor)
{
	var px=rpx+1;	
	
	var cab=document.getElementById("H_"+tabla+rpx);

	var tipo=cab.tipo?cab.tipo:cab.getAttribute('tipo');
	var funcam=cab.onChange?cab.onChange:cab.getAttribute('onChange');	
	var modificable=cab.modificable?cab.modificable:cab.getAttribute('modificable');
	var maskara=cab.mascara?cab.mascara:cab.getAttribute('mascara');
	var verSum=cab.verSumatoria?cab.verSumatoria:cab.getAttribute('verSumatoria');
	var valida=cab.valida?cab.valida:cab.getAttribute('valida');
	var nver=false;	
	
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
	
		
	var obj=document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0 && py < Trs.length)
	{
		var Tds=Trs[py].getElementsByTagName('td');
		if(Tds.length > 0 && rpx < Tds.length)
		{
			var valorAnt=celdaValorXY(tabla, rpx, py);
			
			var obj=document.getElementById('c'+tabla+'_'+rpx+'_'+py);
			if(obj)
			{				
				obj.value=valor;	
				DesEditaCelda(tabla+'_'+rpx+'_'+py, obj);					
			}
			else
			{
				/*var obj=document.getElementById(tabla+'_'+rpx+'_'+py);
				EditarValor(obj);
				var objc=document.getElementById('c'+tabla+'_'+rpx+'_'+py);
				if(objc)
				{
					objc.value=valor;	
					DesEditaCelda(tabla+'_'+rpx+'_'+py, objc);
					var a1=obj.getAttribute("valor");
					if(obj.getAttribute("valor")!=valor)
						obj.setAttribute("valor",valor);					
				}
				else
				{
					Tds[px].valor=valor;
					Tds[px].setAttribute("valor",valor);
				}*/
				
				//alert(tabla+"_"+rpx+"_"+py);
				celda=Tds[px];
				cad=Tds[px].id?Tds[px].id:Tds[px].getAttribute('id');
				var aux=celda.id.split("_");
				var fila=aux[1];
				var columna=aux[2];
				
				switch(tipo)
				{
					case 'texto':
					case 'formula':
					case 'entero':
					case 'decimal':
					case 'fecha':						
								
						if(valorAnt == valor)
						{					
							if(valor > 0 && valor != " ")
								celda.innerHTML=Mascara(maskara,valorAnt);
							return true;
						}			
						if(valorAnt != valor)
							nver=true;				
						
						
						if(valida != null && valida != "null" && valida.length > 0)
						{							
							var valTemp=valida.replace('#',py);
							valTemp=valTemp.replace('$DATO', valor);						
							if(eval(valTemp) != true)
							{
								if(valorAnt.length > 0)
									celda.innerHTML=Mascara(maskara,valorAnt);						
								return false;
							}
						}
									
						if(verSum == "S")
						{
							hobj=document.getElementById('F'+tabla+fila);
							var val=hobj.value;
							if(maskara != "null" && maskara != null && maskara.length > 0)
							{
								for(j=0;j<maskara.length;j++)
								{
									if(maskara.charAt(j) != '.')
									{
										while(val.indexOf(maskara.charAt(j)) != -1)	
											val=val.replace(maskara.charAt(j), '');
									}
								}
							}
							val=isNaN(parseFloat(val))?0:parseFloat(val);	
							if(!isNaN(parseFloat(celda.valor?celda.valor:celda.getAttribute('valor'))))
								val-=parseFloat(celda.valor?celda.valor:celda.getAttribute('valor'));
							if(!isNaN(parseFloat(valor)))	
								val+=parseFloat(valor);
							if(maskara != "null" && maskara != null && maskara.length > 0)
								hobj.value=Mascara(maskara, ""+val+"");
							else
								hobj.value=val;
						}			
							
						celda.valor=valor;			
						if(valor.length > 0)			
							celda.innerHTML=Mascara(maskara,valor);						
						celda.setAttribute("valor",valor);			
						/*-----Buscamos los datos con formulas-----*/
						
						var Tabla=document.getElementById('Head_'+tabla);
						var Trs=Tabla.getElementsByTagName('tr');
						
						//Buscamos las cabecera			
						var Tds=Trs[0].getElementsByTagName('td');
						
						//Buscamos los tipo formula
						for(j=1;j<Tds.length;j++)
						{
							var tipo=Tds[j].tipo?Tds[j].tipo:Tds[j].getAttribute("tipo");
							var formula=Tds[j].formula?Tds[j].formula:Tds[j].getAttribute("formula");					
							if(tipo == "formula"|| (tipo == "oculto" && formula != null && formula != "null" && formula.length > 0))
							{						
								var cH=document.getElementById("H_"+tabla+fila);
								var nombre=cH.valor?cH.valor:cH.getAttribute("valor");
								//alert(formula);
								if(formula.indexOf('$'+nombre) != -1)						
									AplicaFormula(document.getElementById(tabla+'_'+(j-1)+'_'+columna));
							}
						}			
						break;
					case 'binario':
						if((valor == 1 && celda.valor == 0) || (valor == 0 && celda.valor == 1))						
							nver=true;
						if(valor == 1)
						{
							celda.valor=1;
							celda.setAttribute('valor',1);
						}
						else
						{
							celda.valor=0;
							celda.setAttribute('valor',0);
						}			
						break;					
					case 'combo':
						var auxComb=eval('document.getElementById("'+tabla+'").auxiliar_'+px+'?document.getElementById("'+tabla+'").auxiliar_'+px+':document.getElementById("'+tabla+'").getAttribute("auxiliar_'+px+'")');
						if(!auxComb)						
							auxComb=ajaxR(getValueHeader(tabla, rpx, 'datosdb'));							
						auxComb=auxComb.split('|');
						if(valor != celda.valor)
							nver=true;
						if((celda.innerHTML.substring(0,6).toUpperCase()) == '<INPUT'.toUpperCase())
							celda.innerHTML=valor;
						else
						{
							for(var i=2;i<auxComb.length;i++)
							{
								axComb=auxComb[i].split('~');
								if(axComb[0] == valor)
								{
									texto=axComb[1];
									break;
								}
							}				
							celda.valor=valor;
							celda.innerHTML=texto;
						}			
						break;
					case 'buscador':			
						var aux=document.getElementById(tabla);
						var aux=aux.elefoc?aux.elefoc:aux.getAttribute('elefoc');						
						if(aux == 'com'+cad)
							return false;			
						if(celda.valor != valor)
							nver=true;			
						celda.valor=valor;
						if(valor.length == 0)
							celda.innerHTML='&nbsp;';	
						else
							celda.innerHTML=valor;
						break;
					default:
						celda.valor=valor;
						celda.setAttribute('valor',valor);
				}
				if(funcam != 'null' && funcam != null && funcam.length > 0 && nver == true)
				{
					var funaux=funcam;
					//alert(funaux);
					while(funaux.indexOf('#') != -1)
						funaux=funaux.replace('#',py)
					eval(funaux);
				}					
			}			
		}
	}
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;						
			if(ovFi > ovOr && scroll_activo == 0 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)+17;
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='1';
				Gral.setAttribute('scroll_activo', '1');
			}			
		}
	}
}

function valorXYNoOnChange(tabla,cx,cy,valor)
{
	var objH=document.getElementById("H_"+tabla+cx);
	if(!objH)
		return false;
	var objC=document.getElementById(tabla+"_"+cx+"_"+cy);
	if(!objC)
		return false;
	var onchange=(objH.onchange)?objH.onchange:objH.getAttribute("onchange");
	var tipo=(objH.tipo)?objH.tipo:objH.getAttribute("tipo");
	
	objH.setAttribute("onchange","");
	valorXY(tabla,cx,cy,valor);
	if(tipo != 'oculto')
		htmlXY(tabla,cx,cy,valor);
	
	objC.setAttribute("valor",valor)
	objH.setAttribute("onchange",onchange);
	objH.onchange=onchange;
}

function valorCeldaXY(tabla,cx,cy,valor)
{
	var objCelda=document.getElementById(tabla+"_"+cx+"_"+cy);
	if(!objCelda)
		return false;
	objCelda.valor=valor;
	objCelda.setAttribute("valor",valor);
	return true;
}

function htmlXY(tabla, rpx, py, valor)
{
	//Conseguimos los datos originales del overflow
	if(/*navigator.appName == 'Netscape'*/1)
	{
		Gral=document.getElementById(tabla);
		//OverFlow Original
		var ovOr=Gral.oFO?Gral.oFO:Gral.getAttribute('oFO');
		var widthAnt=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");
		var scroll_activo=Gral.scroll_activo?Gral.scroll_activo:Gral.getAttribute("scroll_activo");
	}
	
	var px=rpx+1;
	var obj=document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0 && py < Trs.length)
	{
		var Tds=Trs[py].getElementsByTagName('td');
		if(Tds.length > 0 && rpx < Tds.length)
		{
			//alert(valor+' => ('+rpx+', '+py+')');
			Tds[px].innerHTML = valor;
			Tds[px].setAttribute('innerHTML',valor);
		}		
	}
	
	/*Detectamos si se activo el OverFlow*/
	if(/*navigator.appName == 'Netscape'*/1)
	{
		var Gral=document.getElementById(tabla);	
		var widthAnt1=Gral.offsetWidth?Gral.offsetWidth:Gral.getAttribute("offsetWidth");	
		if(document.getElementById('div_'+tabla+'_overflow_Y'))
		{
			var ovFi=document.getElementById('div_'+tabla+'_overflow_Y').scrollHeight;						
			if(ovFi > ovOr && scroll_activo == 0 && widthAnt1 == widthAnt)
			{				
				var widthNew=parseInt(widthAnt)+17;
				Gral.width=widthNew;
				Gral.setAttribute('width', widthNew);
				Gral.scroll_activo='1';
				Gral.setAttribute('scroll_activo', '1');
			}			
		}
	}
}


function ExtValorXY(objExt, tabla, rpx, py, valor)
{
	var px=rpx+1;
	var obj=objExt.document.getElementById('Head_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	var tipo=Tds[px].tipo?Tds[px].tipo:Tds[px].getAttribute('tipo');
	var obj=objExt.document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0 && py < Trs.length)
	{
		var Tds=Trs[py].getElementsByTagName('td');
		if(Tds.length > 0 && rpx < Tds.length)
		{
			Tds[px].valor=valor;
			Tds[px].setAttribute("valor",valor);			
			if(tipo != "oculto")
				Tds[px].innerHTML=valor;
		}
		else
			return null;
	}
	else
		return null;
}

function HeaderLabel(tabla, pos)
{
	var obj=document.getElementById('Head_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	return(Tds[pos+1].valor?Tds[pos+1].valor:Tds[pos+1].getAttribute('valor'));
}

function NumColumnas(tabla)
{	
	var obj=document.getElementById('Head_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	if(Trs.length > 0)
	{
		var Tds=Trs[0].getElementsByTagName('td');		
		return(Tds.length-1);
	}
	else
		return null;
}

function NumFilas(tabla)
{	
	var obj=document.getElementById('Body_'+tabla);
	if(!obj)
		return null;
	var Trs=obj.getElementsByTagName('tr');
	return Trs.length;
}

function Sumatoria(tabla, pos)
{
	hobj=document.getElementById("Head_"+tabla);
	var Trs=hobj.getElementsByTagName('tr');
	var Tds=Trs[0].getElementsByTagName('td');
	var maskara=Tds[pos+1].mascara?Tds[pos+1].mascara:Tds[pos+1].getAttribute("mascara");		
	var verSum=Tds[pos+1].verSumatoria?Tds[pos+1].verSumatoria:Tds[pos+1].getAttribute("verSumatoria");
	if(verSum == "S")
	{
		hobj=document.getElementById('F'+tabla+pos);
		var val=hobj.value;
		if(maskara != "null" && maskara != null && maskara.length > 0)
		{
			for(j=0;j<maskara.length;j++)
			{
				if(maskara.charAt(j) != '.')
				{
					while(val.indexOf(maskara.charAt(j)) != -1)	
						val=val.replace(maskara.charAt(j), '');
				}
			}
		}
		val=parseFloat(val);
		return val;
	}	
	return null;
}

function ActualizaSum(grid,columna)
{
	var objH=document.getElementById('H_'+grid+columna);
	if(!objH)
		return false;
	var sum=objH.getAttribute('versumatoria');
	var mask=objH.getAttribute('mascara');
	if(sum!="null"&&sum!="N")
	{
		var nfil=NumFilas(grid);
		var cant=0;
		for(var i=0;i<nfil;i++)
		{
			cant+=parseFloat(celdaValorXY(grid,columna,i));
		}
		var objF=document.getElementById("F"+grid+columna);
		if(!objF)
			return false;
		if(mask!="null"&&mask!="")
			cant=Mascara(mask,cant);
		objF.value=cant;
		return true;		
	}
	return false;
}

function clickLibre(tabla, func)
{	
	
	var obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');
	var datosxPag=obj.datosxPag?obj.datosxPag:obj.getAttribute('datosxPag');
	var paginador=obj.paginador?obj.paginador:obj.getAttribute('paginador');
	var activo=obj.LibreActivo?obj.LibreActivo:obj.getAttribute('LibreActivo');
	
	//alert(activo);
	
	if(paginador == 'S' || paginador == 's')
		activo=parseInt(activo)-(parseInt(pagAct)-1)*parseInt(datosxPag);
	var funFin=func.replace('#', activo);
	while(funFin.indexOf("_CS_") != -1)
		funFin=funFin.replace("_CS_", "'");
	while(funFin.indexOf("_CD_") != -1)
		funFin=funFin.replace("_CD_", '"');	
	//alert(funFin);
	eval(funFin);
}

function asignaActivo(celda)
{		
	//alert(celda.innerHTML);
	var aux=celda.id;		
	aux=aux.split("_");
	var tabla=aux[0];
	var columna=parseInt(aux[1]);
	var fila=parseInt(aux[2]);	
	var obj=document.getElementById(tabla);
	obj.LibreActivo=fila;
	obj.setAttribute('LibreActivo', fila);
}

function LimpiaTabla(obj)
{
	var trs=obj.getElementsByTagName('tr');
	var num=trs.length;
	for(var i=(num-footer-1); i >= (footer); i--)
	{
		obj.deleteRow(i);
	}
}

function GuardaGrid(tabla, datosPLL)
{	
	var obj=document.getElementById(tabla);
	var file=obj.guardaEn?obj.guardaEn:obj.getAttribute("guardaEn");
	
	var tipos=new Array();	
	obj=document.getElementById('Head_'+tabla);
	var Trs=obj.getElementsByTagName('tr');	
	var Tds=Trs[0].getElementsByTagName('td');
	for(i=1;i<Tds.length;i++)	
		tipos[i-1]=Tds[i].tipo?Tds[i].tipo:Tds[i].getAttribute("valor");
	
	obj=document.getElementById('Body_'+tabla);
	Trs=obj.getElementsByTagName('tr');
	var numdatos=Trs.length;
	var iteracion=0, numdatAct=0;
	var ruta=file;	
	
	for(var i=0;i<numdatos;i++)
	{
		Tds=Trs[i].getElementsByTagName('td');
		for(var j=1;j<Tds.length;j++)
		{
			if(tipos[j-1] == 'eliminador' || tipos[j-1] == 'libre')
				break;
			else
			{
				ax=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute('valor');
				aux="&dato"+j+"["+numdatAct+"]="+ax;
				ruta+=aux;
			}
		}
		numdatAct++;
		if((numdatAct+1) > datosPLL)
		{
			ruta+="&iteracion="+iteracion+"&numdatos="+numdatAct;
			var axPOP=ajaxR(ruta);			
			if(axPOP.split('|')[0] != 'exito')
				return axPOP;			
			ruta=file;
			numdatAct=0;
			iteracion++;
		}
	}
	ruta+="&iteracion="+iteracion+"&numdatos="+numdatAct;	
	//alert(ruta);
	//window.open(ruta);
	var axPOP=ajaxR(ruta);
	//window.open(ruta);
	return axPOP;
	
}

function Foco(tabla, posx, posy)
{
	//alert(tabla+'_'+posx+'_'+posy);
	var obj=document.getElementById(tabla+'_'+posx+'_'+posy);	
	if(!obj)
		return false; 
	else
		return EditarValor(obj);	
}


function FocoE(obj, tabla, posx, posy)
{
	//alert(tabla+'_'+posx+'_'+posy);
	var obj=obj.document.getElementById(tabla+'_'+posx+'_'+posy);
	if(!obj)
		return false;
	else
		EditarValor(obj);
	return true;	
}

function setValueHeader(tabla, columna, dato, value)
{
	var cab=document.getElementById("H_"+tabla+columna);
	var aux=eval("cab."+dato+"='"+value+"'");
	cab.setAttribute(dato, value)
	//var tipo=cab.tipo?cab.tipo:cab.getAttribute('tipo');
	//cab.setAttribute();
}

function getValueHeader(tabla, rpx, valor)
{
	var px=rpx+1;
	var obj=document.getElementById("H_"+tabla+rpx);
	if(!obj)
		return null;	
	var val=eval("obj."+valor+"?obj."+valor+":obj.getAttribute(valor);");
	return val;	
}

function redond(val, ndec)
{
	return(Math.round(eval(val)*Math.pow(10,ndec))/Math.pow(10,ndec));	
}

function analizaDBLCLICK(func, obj)
{
	var aux=obj.id.split("_");
	var tabla=aux[0];
	var fila=parseInt(aux[1]);
	var columna=aux[2];
	var yreal=yReal(obj.id);
	var ffunc=func.replace('#',yreal);
	eval(ffunc);
}

function actPaginador(tabla, valor)
{
	//alert(tabla+' => '+valor);	
	obj=document.getElementById(tabla);
	obj.setAttribute('datosxPag', valor);
	obj.setAttribute('pagAct', 1);
	obj=document.getElementById("pagact_paginador_"+tabla);
	obj.innerHTML="1";	
	RecargaGrid(tabla, '');
}

function aPagina(tabla, pag)
{
	obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');		
	obj.setAttribute('pagAct', 1);
	obj=document.getElementById('pagact_paginador_'+tabla);
	obj.innerHTML=1;	
}

function sigPaginador(tabla)
{	
	obj=document.getElementById('numpages_paginador_'+tabla);
	var numPags=isNaN(parseFloat(obj.innerHTML))?0:parseFloat(obj.innerHTML);
	//alert(numPags);
	obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');	
	pagAct=parseInt(pagAct);
	if((pagAct + 1) <= numPags)
		pagAct++;
	else
		return false;
	obj.setAttribute('pagAct', pagAct);
	obj=document.getElementById('pagact_paginador_'+tabla);
	obj.innerHTML=pagAct;
	RecargaGrid(tabla, '');
}

function antPaginador(tabla)
{		
	obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');	
	pagAct=parseInt(pagAct);
	if((pagAct - 1) >= 1)
		pagAct--;
	else
		return false;
	obj.setAttribute('pagAct', pagAct);
	obj=document.getElementById('pagact_paginador_'+tabla);
	obj.innerHTML=pagAct;
	RecargaGrid(tabla, '');
}

function firstPaginador(tabla)
{	
	obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');	
	pagAct=parseInt(pagAct);
	if(pagAct == 1)
		return false;
	pagAct=1;	
	obj.setAttribute('pagAct', pagAct);
	obj=document.getElementById('pagact_paginador_'+tabla);
	obj.innerHTML=pagAct;
	RecargaGrid(tabla, '');
}

function lastPaginador(tabla)
{	
	obj=document.getElementById('numpages_paginador_'+tabla);
	var numPags=isNaN(parseFloat(obj.innerHTML))?0:parseFloat(obj.innerHTML);
	obj=document.getElementById(tabla);
	var pagAct=obj.pagAct?obj.pagAct:obj.getAttribute('pagAct');	
	pagAct=parseInt(pagAct);
	if(pagAct == numPags)
		return false;
	pagAct=numPags;	
	obj.setAttribute('pagAct', pagAct);
	obj=document.getElementById('pagact_paginador_'+tabla);
	obj.innerHTML=pagAct;
	RecargaGrid(tabla, '');
}

function multiselecciona(a,grid)//MS
{
	var objhead=document.getElementById("H"+grid+(a-1))
	if(!objhead)
		return false;
	var estado=(objhead.getAttribute("estMulti"))?(objhead.getAttribute("estMulti")):0;
	var tabla=document.getElementById("Body_"+grid);
	if(tabla)
	{
		var Trs=tabla.getElementsByTagName("tr");
		for(var i=0;i<Trs.length;i++)
		{
			var Tds=Trs[i].getElementsByTagName("td");
			var chk=Tds[a].getElementsByTagName("input")[0];
			if(chk)
			{
				if(estado==0)
				{
					var nestado=1;
					chk.checked=true;
					objhead.className="buttonchecktrue";
				}
				else
				{
					var nestado=0;
					chk.checked=false;
					objhead.className="buttoncheckfalse";
				}
				DesEditaCelda(grid+'_'+(a-1)+'_'+i, chk);
			}
		}
	}
	objhead.setAttribute("estMulti",nestado);	
	return false;

}

function borrafilvac(grid)//MS
{
	var objt=document.getElementById("Body_"+grid);
	if(objt)
	{
		for(var i=0;i<objt.rows.length;i++)
		{
			var fila=objt.rows[i];
			var valvac=0;
			for(var j=0;j<fila.cells.length;j++)
			{
				var celda=fila.cells[j];				
				if(j>0)
				{
					var head=document.getElementById("H_"+grid+(j-1));
					if(head)
					{
						var tipo=head.getAttribute("tipo").toUpperCase();
						if(tipo!="OCULTO"&&tipo!="ELIMINADOR")
						{
							if(tipo!="TEXTO")
								var valor=(celda.valor)?celda.valor:celda.getAttribute("valor");
							else
								var valor=celda.innerHTML;
							if(valor!=null&&valor!=""&&valor!="&nbsp;")
							{
								valvac++;
							}
						}
					}					
				}
			}
			if(valvac==0)
				objt.deleteRow(i);
		}
	}
}

//funcion que limpia celdas del grid de una posicion a otra en determinada fila
function borraContenidoCeldas(grid,fila,posxinicio,posxfinal)
{
	var objCelda,objHeader,tipo,objCampo;
	for(var i=posxinicio; i<=posxfinal; i++)
	{
		objCelda=document.getElementById(grid+"_"+i+"_"+fila);
		if(!objCelda)
			return false;
		objHeader=document.getElementById("H_"+grid+i);
		if(!objHeader)
			return false;
		tipo=(objHeader.tipo)?objHeader.tipo:objHeader.getAttribute("tipo");
		if(tipo=="checkbox")
		{
			objCampo=document.getElementById(grid+"_"+i+"_"+fila);
			if(!objCampo)
				return false;
			objCampo.checked=false;
			objCelda.valor="";
			objCelda.setAttribute("valor","");
		}
		else if(tipo == "decimal")
		{
			objCelda.valor=0;
			objCelda.setAttribute("valor",0);
		}
		else if(tipo!="libre" && tipo !="eliminador" && tipo !="formula")
		{
			objCelda.valor="";
			objCelda.setAttribute("valor","");
		}
			
		if(tipo!="libre" && tipo !="eliminador" && tipo != "oculto")
		{
			objCelda.innerHTML="&nbsp;";			
		}
		
		
		AplicaFormula(objCelda);		
	}
	return true;
}

function aplicaCambio(grid,x,y)//MS
{
	var objm=document.getElementById("H_"+grid+x);
	if(!objm)
		return false;
	var modif=objm.getAttribute("modificable");
	objm.setAttribute("modificable","S");
	var obj=document.getElementById(grid+"_"+x+"_"+y);
	if(obj)
	{
		EditarValor(obj);
		var objc=document.getElementById("c"+grid+"_"+x+"_"+y);
		if(objc)
			DesEditaCelda(grid+"_"+x+"_"+y,objc);
	}
	objm.setAttribute("modificable",modif);
	return true;
}

function aplicaValorXY(grid,x,y,valor)
{
	var objm=document.getElementById("H_"+grid+x);
	if(!objm)
		return false;
	var modif=objm.getAttribute("modificable");
	objm.setAttribute("modificable","S");
	var obj=document.getElementById(grid+"_"+x+"_"+y);
	if(obj)
	{
		EditarValor(obj);
		var objc=document.getElementById("c"+grid+"_"+x+"_"+y);
		if(objc)
		{
			if(objc.type=='select-one')
				objc.value=valor;
			else
				objc.value=valor;
			DesEditaCelda(grid+"_"+x+"_"+y,objc);
		}
	}
	objm.setAttribute("modificable",modif);
	return true;
}

function yReal(cad)
{
	var aux=cad.split('_');
	var tabla=aux[0];
	var fila=parseInt(aux[1]);
	var columna=parseInt(aux[2]);
	
	yreal=-1;
	//Buscamos la Y real
	var tabAux=document.getElementById('Body_'+tabla);
	if(!tabAux)
		return null;
	var Trax=tabAux.getElementsByTagName('tr');
	if(Trax.length > 0)
	{
		if(columna > (Trax.length-1))
			inax=Trax.length-1;
		else
			inax=columna;			
		//alert(inax);	
		for(yax=inax;yax>=0;yax--)
		{
			Tdax=Trax[yax].getElementsByTagName('td');
			idax=Tdax[fila+1].id?Tdax[fila+1].id:Tdax[fila+1].getAttribute('id');
			//alert(idax+' = '+cad);
			if(idax == cad)
			{
				yreal=yax;
				break;
			}
		}
	}
	return yreal;
}

function decimales(obj)
{
	var entero;
	var lastdato;
	var puntdec;
	for(var i=obj.value.length-1;i>=0;i--)
	{
		puntodec=0;
		lastdato=obj.value.charAt(i);	
		entero=parseInt(lastdato);
		for(var j=0;j<obj.value.length;j++)
			if(obj.value.charAt(j) == '.')		
				puntodec++;
		if(isNaN(entero) && lastdato != '.' && lastdato != '-')
			obj.value = obj.value.substring(0,obj.value.length-1);
		if(lastdato == '.' && puntodec > 1)	
			obj.value = obj.value.substring(0,obj.value.length-1);
		if(lastdato == '-' && i != 0)
			obj.value = obj.value.substring(0,obj.value.length-1);				
	}
}

function enteros(obj)
{
	var entero;
	var lastdato;	
	for(var i=obj.value.length-1;i>=0;i--)
	{
		lastdato=obj.value.charAt(i);	
		entero=parseInt(lastdato);
		if(isNaN(entero) && obj.value.length == 1)
			obj.value="";
		if(isNaN(entero) && lastdato != '-')
			obj.value = obj.value.substring(0,obj.value.length-1);		
		if(lastdato == '-' && i != 0)
			obj.value = obj.value.substring(0,obj.value.length-1);				
	}
}
