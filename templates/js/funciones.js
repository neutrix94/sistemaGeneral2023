// JavaScript Document

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

function validaTime(e, id)
{
	var valor="";
	
	tecla_codigo = (document.all) ? e.keyCode : e.which;
	valor=document.getElementById(id).value;
	
	
	if(tecla_codigo==8 || tecla_codigo==0)return true;
	patron =/[0-9\-:]/;
	
		
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

function validaFecha(val)
{
	var url="../ajax/validaFechaHora.php?tipo=1&valor="+val;
	aux=ajaxR(url);
	if(aux == 'exito')
		return true;
	else
		alert(aux);
	return false;	
}

function validaHora(val, obj)
{
	
	var ax=obj.value;
	aux=ax.split(':');
	var hora="";
	
	if(aux.length == 1 && obj.value.length > 2)
		return false;
	if(aux.length == 2 && obj.value.length > 5)
		return false;
	if(aux.length == 3 && obj.value.length > 8)
		return false;
	
	if(aux[0].length == 1)
		hora="0"+aux[0];
	else
		hora=aux[0];
	
	if(aux.length > 1)
	{			
		if(aux[1].length == 1)	
			hora+=":0"+aux[1];
		else
			hora+=":"+aux[1];
	}
	else
		hora+=":00";
	
	if(aux.length > 2)
	{
		if(aux[2].length == 1)	
			hora+=":0"+aux[2];
		else
			hora+=":"+aux[2];	
	}
	else
		hora+=":00";
	
	obj.value=hora;
	
	
	aux=hora.split(':');
	h=parseInt(aux[0]);
	m=parseInt(aux[1]);	
	s=parseInt(aux[2]);
	
	if(h < 0 || h > 23)
		return false;
	if(m < 0 || m > 59)
		return false;
	if(s < 0 || s > 59)
		return false;	
	
	return true;	
}



//	Función para iniciar el calendario
function calendario(objeto){
    Calendar.setup({
        inputField     :    objeto.id,
        ifFormat       :    "%Y-%m-%d",
        align          :    "BR",
        singleClick    :    true
	});
}