<!DOCTYPE html>
<html>
<head>
	<title></title>
<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
</head>
<body>
	<table width="100%" border="0";>
		<tr>
			<td width="70%" align="center">
				<p style="color:white;">Producto:
					<input type="text" style="padding:8px; width:70%; border-radius:5px;z-index:100;" onkeyup="buscar(event);" id="buscador" />
				</p>
				<input type="hidden" id="auxBusqueda">
			</td>
		<!--
			<td width="20%" align="center">
				<span>Cantidad</span>
					<input type="text" style="width:40px; padding:8px; border-radius:5px;" onkeyup="accionar(event);" placeholder="cant." id="agrega">
			</td>
			<td width="10%" align="center">
				<a href="javascript:agregarFila();"><font size="5px" color="#000000"><i class="icon-plus-circled"></i></font></a>
			</td>
		-->
		</tr>
		<tr>
			<td colspan="2">
				<div id="resBus" class="lista_producto" style="display:none; position:fixed; z-index:3;width:40%;height:400px; border: 2px solid blue; 
				background:white;overflow:auto;"></div>  
  		    		<input type="hidden" name="id_productoN" value="" />
			</td>
		</tr>
	</table>
</body>
</html>
<script>
	function buscar(e){
	var tecla=(document.all) ? e.keyCode : e.which;//convertimos evento de teclado en codigo
	var busqueda=document.getElementById('buscador').value;//extraemos contenido existenete en buscador
	//alert(tecla);
	if(tecla==13){//en caso de ser tecla intro
		if(document.getElementById('id_1')){
		var id=document.getElementById('id_1').value;
		//alert(id);
		validaProducto(id);			
		}
	}
	if(tecla==27 || tecla==38){
		document.getElementById('resBus').style.display='none';
		document.getElementById('buscador').select();
		return false;
	}
	if(tecla==40){
		if(busqueda==""){
			var cont=0;
			while(cont<=1000){
				cont++;
				if(document.getElementById('6_'+cont)){
					//alert();
				resaltar(1);//enfocamos la primera fila
					return true;
				}else{
					}
			}	
		}else{
			document.getElementById('resBus').style.display='block';
			document.getElementById('r_1').focus();
			document.getElementById('r_1').style.background='rgba(0,225,0,.5)';
			return false;
		}
	}
	//alert(tecla);
//declaramos variables para asignar datos
	var c=document.getElementById('contador');
	if(busqueda==""){//en caso de que el buscador este vacio;
		document.getElementById('resBus').style.display='none';//ocultamos resultados
		return false;//retorna false
	}
//mandamos datos por ajax
	$.ajax({
		type:"post",
		url:"buscador/buscarProductoTiempoReal.php",
		data:{producto:busqueda,suc:id_sucursal_en_edicion,stock:document.getElementById('tipo_stock').value},
		success: function(datos){
			if(datos=='sin resultados'){//SI DATOS RETORNA 0;
					//alert('sin resultados');
					//$("#"+desc).html('');//LIMPIAMOS CAMPO DESCRIPCION
				}else{//DE LO CONTRARIO;
					//alert(datos);
					$('#resBus').html(datos);
					document.getElementById('resBus').style.display='block';
					}
			}
		});
	}

	function eje(e,c,id){
	var tecla= (document.all) ? e.keyCode : e.which;
	//alert(tecla);
	if(tecla==27){
		document.getElementById('resBus').style.display='none';
		document.getElementById('buscador').select();
		return false;
	}
	if(tecla==40){
		var n=c+1;
	//alert(n);
		var enfoca="r_"+n;
		if(document.getElementById(enfoca)){
			$('#'+enfoca).focus();
			var desenfoca="r_"+parseInt(n-1);
			document.getElementById(desenfoca).style.background='white';
			document.getElementById(enfoca).style.background='rgba(0,225,0,.5)';
		}else{
			//alert('fin');
			return false;
		}
	}

	if(tecla==38){
		if(c==1){
				document.getElementById('buscador').select();
				return false;
			}
			var n=c-1;
			var enfoca="r_"+n;
			$('#'+enfoca).focus();
				document.getElementById(enfoca).style.background='rgba(0,225,0,.5)';
				var desenfoca="r_"+parseInt(n+1);
				document.getElementById(desenfoca).style.background='white';
			//alert(c);
	}
	if(tecla==13){
		//alert(id);
		validaProducto(id);
	}else{
		return true;
	}
}

function validaProducto(id){
	//alert();
	var aux,nFilas;
	if($('#formInv')){
	//	alert('validaProducto 1');
		nFilas=$('#formInv tr').length;//calculamos numero de productos(filas en tabla)	
	}
	if($('#listado')){
	//	alert('validaProducto 2');
		nFilas=$('#listado tr').length;
	}

  	//alert('filas:'+nFilas);
  	for(var i=1;i<=nFilas;i++){//comenzamos for para realizar busqueda
  		if(!document.getElementById('0,'+i)){//en caso de no existir el id
  			//alert('no existe');
  		}else{//de lo contrario
  			fila='0,'+i;//armamos id
  			aux=parseInt(document.getElementById(fila).value);//sacamos valor de id
  			if(aux==id){//en caso de encontrar coincidencias
  				document.getElementById('resBus').style.display='none';
  				document.getElementById('buscador').value="";		
  				if(resalta(i)){
  					//alert('ok');
  				}else{
  			//		alert('no');
  				}
  			//	var x=$('#3,'+i).scrollTop();
  			//	alert(x);
  			//	$('#3,'+i).focus();
  			//	$('#3,'+i).select();
  				//var prueba='fila'+i;
  				//document.getElementById(prueba).focus();
  			//	return false;
  			}
  
  		}
  	}
  	
  	//return false;  	
}
</script>