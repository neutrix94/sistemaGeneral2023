//declaracion de variables
var enfocando=0;
var celda='';
var abajo=0,arriba=0;
var der=0;
var saltos=0;

function activaDependiente(contador,flag){
//alert(contador+','+flag);
	var elemento,opcion,aux,dato,dependiente,opcion;
	if(flag==1){
		elemento=document.getElementById('5,'+contador);
		aux=6;
	}
	if(flag==2){
		elemento=document.getElementById('6,'+contador);
		aux=7;
	}
	dependiente=parseInt(flag+1)+'|'+contador+'|'+aux;
	opcion=elemento.value;
//alert('opcion\n'+opcion);
//enviamos  datos por Ajax
	$.ajax({
		type:'post',
		url:'ajax/getCombos.php',
		cache:false,
		data:{flag:flag,dato:opcion},
		success:function(datos){
			actualizaComboDependiente(datos,dependiente);
		}
	});
}

function actualizaComboDependiente(info,infoCombo){
	var combo,dependiente;
	combo=infoCombo.split('|');
//creamos el objeto a modificar
	dependiente=document.getElementById("combo,"+combo[0]+","+combo[1]);
	//dependiente.innerHTML='aqui mero xD';
	//return false;
	//dependiente.innerHTML='here';

	$.ajax({
		type:'post',
		url:'ajax/combos.php',
		cache:false,
		data:{info:info,combo:infoCombo},
		success:function(datos){
			alert(datos);
			dependiente.innerHTML=datos;
		}
	});
}
function registraNuevo(){
//declaramos variables
	var i,aux,id,che;
	for(i=1;i<=18;i++){
		if(i==1){
			aux=document.getElementById(i+',0').value;
		}else{
		//sin son checkbox
			if(i==9||i>=16&&i<=18){
				if(document.getElementById(i+',0').checked==true){
					che=1;
					//alert(i+',0.1= '+che);
				}else{
					che=0;
					//alert(i+',0.2= '+che);
				}
				aux+='|'+che;
			}//termina if de combos
			else{
				if(document.getElementById(i+',0')){
					aux+='|'+document.getElementById(i+',0').value;					
				}
			}
		}
	}//finaliza for
	alert(aux);
//	return false;
//enviamos ajax
	$.ajax({
		type:'POST',
		url:'ajax/actualiza.php',
		cache:false,
		data:{flag:'agregar',inf:aux},
		success:function(datos){
			if(datos=='ok'){
				alert('Producto insertado Exitosamente!!!');
				location.reload();//regrescamos la pagina
			}else{
				alert("Error!!!\n"+datos);
			}
		}
	});
	return false;
}
function sincScroll(flag){
	//alert(flag);
var maestro=flag,esclavo1,esclavo2,horiz,vert;
//si el movimiento fue en dinamico
	if(maestro=='dinamico'){
		esclavo1='enc2';
		esclavo2='fijo';
	//calculamos movimiento
		horiz=$('#'+maestro).scrollLeft();
		vert=$('#'+maestro).scrollTop();
  		$("#"+esclavo1).scrollLeft(horiz);
		if(vert<17.0){
			//vert=0;
			$("#"+maestro).scrollTop(vert);
			saltos=1;
		}
		if(vert>=17.0){
			saltos++;
			if(saltos==2){
				vert=parseFloat(vert+48);
				$("#"+maestro).scrollTop(vert);	
			abajo=0;
			}
		}
		$("#"+esclavo2).scrollTop(vert);
		if(horiz!=0){
			//alert('yeah:'+horiz);
			if(horiz>40){
				horiz=parseFloat(horiz+150);
			}
			$('#'+maestro).scrollLeft(horiz);
			$('#'+esclavo1).scrollLeft(horiz);
		}
	return false;
	}
//si el movimiento fue en el div de productos(solo se hace scroll en vertivcal)
	if(maestro=='fijo'){
	/*
		esclavo2='dinamico';
		vert=$("#"+maestro).scrollTop();
		if(vert<17.0){
			vert=0;
			$("#"+maestro).scrollTop(vert);
			saltos=1;
		}else if(vert>=17.0){
			saltos++;
			if(saltos==2){
				vert=parseFloat(vert+48);
				$("#"+maestro).scrollTop(vert);	
				$("#"+esclavo2).scrollTop(vert);	
			saltos=0;
			return false;
			}
		}*/
	}
//si el movimiento fue en enc2 (solo hace scroll en horizontal)
	if(maestro=='enc2'){
		escalvo='dinamico';
	}
 	return false;
}
function actualizaDependiente(id_catalogos, id_objetos, valor, val_pre){
	var ids=id_catalogos.split(',');
	var obs=id_objetos.split(',');
	var vpres=val_pre.split(',');
	//alert(ids.length);
	for(var j=0;j<ids.length;j++){
		//alert(i)
		var res=ajaxR('comboDependiente.php?id_catalogo='+ids[j]+'&valor='+valor);
		var aux=res.split('|');
		if(aux[0] == 'exito'){
			if(document.getElementById(vpres[j])){
				var vpred=document.getElementById(vpres[j]).value;
			}else	
				var vpred=vpres[j];
			//alert(vpred);
				var obj=document.getElementById(obs[j]);
				obj.options.length=0;
				for(i=1;i<aux.length;i++){
					var ax=aux[i].split('~');
					obj.options[i-1]=new Option(ax[1], ax[0]);
				}
				if(vpred != 'NO'){
					obj.value=vpred;
				}
				var och=obj.getAttribute("onchange");
				//var och=och.replace("\n", '');
				//alert(och);
				eval(och);	
		}else{
			alert(res);
		}				
	}		
}

function combos(col,cont){
	var elemento,dato,id,campo,consulta,dependiente;
//armamos los ids
	elemento=col+","+cont;
	dependiente=parseInt(col+1)+","+cont;
	
	if(document.getElementById('0,'+cont)){
		id=document.getElementById('0,'+cont).value;
	}
//si es categoria
	if(col==5){
		dato=document.getElementById(elemento).value;
	//alert(dato);
		campo='id_categoria';

	}
//si es subctaegoria
	if(col==6){
		dato=document.getElementById(elemento).value;
		campo='id_subcategoria';
	}
	consulta="UPDATE ec_productos SET "+campo+"='"+dato+"' WHERE id_productos="+id;
//enviamos ajax
	$.ajax({
		type:'POST',
		url:'ajax/actualiza.php',
		cache:false,
		data:{sql:consulta},
		success:function(datos){
			//alert(datos);
			if(datos=='ok'){//si se actualizo correctamenete;
				carga(parseInt(col+1),cont,dato);//cargamos combo dependiente 
			}else{
				alert(datos);
			}
		return false;
		}
	});
}

function carga(col,cont,cond){
	//alert(flag+","+col+","+fil);
//creamos el id
	var idElemento=col+','+cont;
	var tabla,campo,dependiente,condicion='';
	if(col==5){
		tabla='ec_categoria';
		campo='id_categoria,nombre';
		dependiente='6,'+cont;
	}
	if(col==6){
		//alert('dependiente xD');
		tabla='ec_subcategoria';
		campo='id_subcategoria,nombre';
		dependiente='7,'+cont;
		if(cond!=''){
			condicion=" WHERE id_categoria="+cond;
		}
	}
//armamos consulta
	consulta='SELECT '+campo+' FROM '+tabla+condicion;
	$.ajax({
		type:'POST',
		url:'ajax/getCombos.php',
		cache:false,
		data:{sql:consulta},
		success:function(datos){
			var aux=datos.split("|");
				var s=document.getElementById(idElemento);
				var option,valor;
			for(var i=1;i<aux.length;i++){
					valor=aux[i].split("~");
					option=document.createElement("option"); 
					option.value=valor[0]; 
					option.text=valor[1];
					s.appendChild(option);
			}
		}
	});

}			

function modifica(col,contador){
	//declaramos variables
	var consulta,campo,dato,id,celda;
	celda=col+','+contador;
	id=document.getElementById('0,'+contador).value;//estraemos id del producto a modififcar
	dato=document.getElementById(celda).value;
	if(col==1){
		campo='nombre';
	}
	if(col==2){
		campo='clave';
	}
	if(col==3){
		campo='orden_lista';
	}
	if(col==4){
		campo='ubicacion_almacen';
	}
	if(col==5){
		campo='id_categoria';
	}
	if(col==6){
		campo='id_subcategoria';
	}
	if(col==7){
		campo='precio_venta';
	}
	if(col==8){
		campo='precio_compra';
	}
	if(col==10){
		campo='nombre_etiqueta';
	}
	if(col==11){
		campo='codigo_barras_1';
	}
	if(col==12){
		campo='codigo_barras_2';
	}
	if(col==13){
		campo='codigo_barras_3';
	}
	if(col==14){
		campo='codigo_barras_4';
	}
	consulta="UPDATE ec_productos SET "+campo+"='"+dato+"' WHERE id_productos="+id;
	//alert(consulta);
	$.ajax({
		type:'POST',
		url:'ajax/actualiza.php',
		cache:false,
		data:{sql:consulta},
		success:function(datos){
			if(datos='ok'){
				alert(datos);
			}else{
				alert('Error!!!'+'\n'+datos);
			}
			//alert(datos);
		}
	});
	return false;
}

function seleccion(col,contador,flag){
//declaramos variables
	var campo,dato,consulta,elemento,id;
//armamos id de elemento pdonde se dio el click
	elemento=col+','+contador;
//extraemos id del producto
	id=document.getElementById('0,'+contador).value;
//armamos consulta
	if(col==9){//si es maqula
		campo='es_maquilado';
	}
	if(col==16){
		campo='habilitado';
	}
	if(col==17){
		campo='omitir_alertas';
	}
	if(col==18){
		campo='muestra_paleta';
	}
//checamos si habilia o deshabilita
	if(document.getElementById(elemento).checked==true){
		if(flag==1){//si es con enter
			dato=0;
			document.getElementById(elemento).checked=false;
		}else{//si es por click
			dato=1;
		}
	}else{
		if(flag==1){//si es por enter
			dato=1;
			document.getElementById(elemento).checked=true;
		}else{//si es por click
			//alert('se deshabilitará');
			dato=0;
		}
	}
	consulta="UPDATE ec_productos SET "+campo+"="+dato+" WHERE id_productos="+id;
	//alert(consulta);
	$.ajax({
		type:'POST',
		url:'ajax/actualiza.php',
		data:{sql:consulta},
		success:function(datos){
			if(datos=='ok'){
				alert(datos);
			}else{
				alert('Error!!!\n'+datos);
			}
		}
	});
	return false;
}



function resalta(contador){

	if(enfocando!=0){
		var col=color(contador);
		var desenfoca1='fil'+enfocando;
		var desenfoca2='fila'+enfocando;
		document.getElementById(desenfoca1).style.background=col;
		document.getElementById(desenfoca2).style.background=col;
	}
	enfocando=contador;
	var enfoca1='fil'+enfocando;
	var enfoca2='fila'+enfocando;
	document.getElementById(enfoca1).style.background="rgba(0,225,0,.5)";
	document.getElementById(enfoca2).style.background="rgba(0,225,0,.5)";
	document.getElementById('2,'+contador).focus();
	return false;
}

function color(contador){
	var tono;
	if(contador%2==0){
		tono='#FFFF99';
	}else{
		tono='#CCCCCC';
	}
	return tono;
}

function resaltacelda(col,cont){
	//alert('resalta: '+col+','+cont);
//armamos id decelda
	if(celda==''){		
	}else{
		if(document.getElementById(celda)){
			document.getElementById(celda).style.background="transparent";
			document.getElementById(celda).style.fontSize="12px";
			document.getElementById(celda).style.textAlign="right";
			document.getElementById(celda).style.border="0";
		}
	}
	celda=col+','+cont;
	if(col==5||col==6||col==15){
		if(col==6){
			celda='15,'+cont;
		}
		if(col==15){
			alert('here');
			//celda='7,'+cont;
		}
		document.getElementById(celda).focus();
	}else{
		//aqui meto esta condicion de prueba	
		if(document.getElementById(celda)){
			//alert('combo');
			document.getElementById(celda).select();
		}
	}
	document.getElementById(celda).style.background="white";
	document.getElementById(celda).style.fontSize="18px";
	document.getElementById(celda).style.textAlign="left";
	document.getElementById(celda).style.border="2px solid red";
	resalta(cont);
	return false;
}

function valida(e,columna,contador){
	var tecla=(document.all) ? e.keyCode : e.which;//convertimos tecla a valor numerico
	var enfoque='';
	//alert(columna+','+contador);
	if(tecla==1){
		resaltacelda(columna,contador);
		return false;
	}
	if(tecla==13){//si tecla es enter
		//checamos si son check
		if(columna==9||columna==16||columna==17||columna==18){
			seleccion(columna,contador,1);//mandamos llamar metodo que habilita/deshabilita checkbox
			return false;//cortamos funcion
		}
	}
	if(tecla==38){//si tecla es arriba
		if(contador==1){
			//enfocamos buscador
			return false;
		}
		enfoque=parseInt(contador-1);
		resaltacelda(columna,enfoque);
		return false;
	}
	if(tecla==40){//si tecla es abajo
		enfoque=parseInt(contador+1);
		resaltacelda(columna,enfoque);
		return false;
	}
	if(tecla==37){//si tecla es izquierda
		if(columna==1){
			return false;
		}
		enfoque=parseInt(columna-1);
		resaltacelda(enfoque,contador);
		return false;
	}
	if(tecla==39){//si tecla es derecha
			if(columna==18){
				enfoque=parseInt(contador+1);
				resaltacelda(1,enfoque);
				return false;
			}
		enfoque=parseInt(columna+1);
		resaltacelda(enfoque,contador);
		return false;
	}
	else{
		if(contador==0){
		//	alert('hasta aqui xD');
			return false;
		}else{
			modifica(columna,contador);
		}
	}
	return false;
}
/*
function recorrer(){
	$('#contenido').offset().top - container.offset().top + container.scrollTop();
	$('#encabezado').offset().top - container.offset().top + container.scrollTop();
}
*/

//////////scrolls
/*
var isSyncingLeftScroll = false;
var isSyncingRightScroll = false;
var leftDiv = document.getElementById('encabezado');
var rightDiv = document.getElementById('contenido');

leftDiv.onscroll = function() {
  if (!isSyncingLeftScroll) {
    isSyncingRightScroll = true;
    rightDiv.scrollTop = this.scrollTop;
  }
  isSyncingLeftScroll = false;
}

rightDiv.onscroll = function() {
  if (!isSyncingRightScroll) {
    isSyncingLeftScroll = true;
    leftDiv.scrollTop = this.scrollTop;
  }
  isSyncingRightScroll = false;
  */