var arr_tmp=[];
var ocupado=0;

	function cambiar(obj,e,id_txt_oculto){
	//obtenemos el etributo id de la caja de texto
		var id_caja_txt=obj.getAttribute('id');
		setTimeout(function(){cambia(id_caja_txt,e,id_txt_oculto);},30);
	}
	
	function cambia(id_element,e,id_txt_oculto){
		var obj=document.getElementById(id_element);
		if(ocupado!=0){
			arr_tmp.push(e);//guardamos el evento
			return false;
		}
		ocupado=1;//ocupamos la función
		var tmp=document.getElementById(id_txt_oculto).value;//declaramos el objeto de la contraseña
		
		var ast='';//declaramos la variable que guarda los asteriscos
		if(e.keyCode==8){//si es eliminar
			tmp=tmp.slice(0,-1); 
		}
		if((obj.value).charAt((obj.value).length-1)!='*'){
			tmp+=(obj.value).charAt((obj.value).length-1);
		}
		for(var i=0;i<tmp.length;i++){//extremos tamaño de la variable temporal y armamos los asteriscos
			ast+='*';//foramos asteriscos
		}
		document.getElementById(id_txt_oculto).value=tmp;//asignamos el valor de la contraseña real a la variable oculta
		obj.value=ast;//asignamos cadena de asterizcos en el campo de contraseña
		if(arr_tmp.length>0){
			var e_tmp=arr_tmp.shift();
			if(ocupado=0){//Desocupamos función
				cambia(obj,e_tmp);//madamos evento en cola
			}
		}else{
			ocupado=0;//desocupamos función
		}
	}//fin de función cambia

	function show_item_content( object_change, obj ){
		if( 	document.getElementById( object_change ).style.color == 'white' ){
			document.getElementById( object_change ).style.color = 'black';
			obj.innerHTML = "ocultar";
		}else{
			document.getElementById( object_change ).style.color = 'white';
			obj.innerHTML = "ver";
		}
	}