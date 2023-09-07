{include file="_header.tpl" pagetitle="$contentheader"}
<!--link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css"-->
<script language="JavaScript" src="../../../js/papaparse.min.js"></script>
<div id="campos">  
<div id="titulo">1.3 Etiquetas</div>
<br><br>

<div id="filtros">
<div id='cosa2'>
<form action="">
	<ul class ='filters'>
		<li><label for="">Familias:</label></li>
		<li>
			<select id="categoria" class="filters" name='filtros' onchange="cambiaSC(this.value)">
				{html_options values=$vals output=$textos}
				<option value="-2">PAQUETES</option>
			</select>
		</li>
	
	
		<li><label>&nbsp;Tipos:</label></li>
		<li>
			<select id="tip" class="filters"  name='filtros' onclick="cambiaTP(this.value)">
				{html_options values=$vals2 output=$textos2}
			</select>
		</li>
		<li><label>&nbsp;Subtipos:</label></li>
		<li>
			<select id="subtip" class="filters"  name='filtros'>
				{html_options values=$vals3 output=$textos3}
			</select>
		</li>
		
	</ul>
	<div id='pli'>
		<li><label>Precio:</label></li>
		<ul class="filters">
		<li><label>De:</label><input type='number'  name='filtros' value="0" min="0"></li>
		<li><label>A:</label><input type='number'  name='filtros' value="0" min="0"></li>
		</ul>
		</div>
	<div id='buscador'>
		
        <ul><label>Producto:</label></ul>
        <ul class='filters'>
			<li><input type='hidden' id='proId' value='0'><input type="text" id='busca' onkeyup="buscaProd()"></li>
			<li> <input type="button" id='ag' value='Agregar' onclick="agregarListado()" disabled="true" style="padding:10px;"></li>
	<!-- -->
			<li>
				<input type="file" id="file_csv" style="display:none;" accept=".csv"/>
				<button 
					id="import_btn"
					class="btn_import" 
					type="button" 
					onclick="document.getElementById('file_csv').click();"
				>
					Importar CSV
				</button>
			</li>
			<li id="">
				<input type="text" id="csv_description" class="hidden" style="width: 140px !important;" readonly>
			</li>
			<li>
				<button class="btn_import hidden" type="button" id="import_csv">Importar</button>
			</li>
	<!-- -->
		</ul>	
	</div>
	<div id='listaProd'>
		<ul id='proLi'>
		</ul>
	</div>
	<p align="left" style="padding-left : 370px;">
		Productos : <input type="number" id="products_counter" readonly>
	</p>
	<div id='buscProd' class='ob' >
	
    </div>
    <div id='btn'>
    		<li><input type="button" class="btn" value='Generar' onclick='getId()'></li>

    </div>
    <div id='tpl'>
    	<ul style='list-style: none;'>
    	<li><label>N&uacute;mero de etiquetas:</label></li>
    	<li><input type="number" name='parsTpl' value="1" min="1"></li>
    	<li><label >Plantilla:</label></li>
    	<li><select name="parsTpl" id="ticket_plantilla">
    	    <option value="-1">---- Elige plantilla ----</option>
    		<option value="1">Plantilla 1</option>
    		<option value="2">Colgantes</option>
    		<option value="3">Varios precios</option>
    		<option value="4">Precio oferta</option>
    		<option value="5">Colgantes de oferta</option>
    		<option value="6">Productos sin Precio</option>
    	</select></li>
    	</ul>
    </div>
    <div id="ofertas">
    	<!--<span><center><p><b>ofertas</b></p><input type="checkbox" id="ofe" style="padding:10px;"></span>
	Modificación de Oscar 12/02/2017
    	-->
    	<span>
    		<b>Filtrar por:</b>
    			<p align="center">
    				<select id="ofe" align="center">
<!--    					<option value="-1">Con y sin Oferta</option>-->
    					<option value="1">Sin Oferta</option>
    					<option value="2">Con Oferta</option>
    				</select>			
    			</p>
    		</center>
    	</span>
    </div>
</div>
</form>
</div>


{literal}
 <script>
/*implementacion Oscar 2021*/
	$('#import_csv').on("click",function(e){
		e.preventDefault();
		$('#file_csv').parse({
			config: {
				delimiter:"auto",
				complete: importProducts,
			},
		 		before: function(file, inputElem){
		 			$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
			//console.log("Parsing file...", file);
			},
				error: function(err, file){
		   			console.log("ERROR:", err, file);
				alert("Error!!!:\n"+err+"\n"+file);
			},
		 		complete: function(){
				//console.log("Done with all files");
			}
		});
	});
	//detectamos archivo cargado
	$("#file_csv").change(function(){
		var fichero_seleccionado = $(this).val();
		var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
		if(nombre_fichero_seleccionado!=""){
			$("#import_btn").css("display","none");//ocultamos botón de importación
			$("#import_csv").removeClass("hidden");//mostramos botón de inserción
			$("#csv_description").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
			$("#csv_description").css("display","block");//volvemos visible el nombre del archivo
		}else{
			alert("No se seleccionó ningun Archivo CSV!!!");
			return false;
		}
	});

	function importProducts( results ){
		var data = results.data;//guardamos en data los valores delarchivo CSV
		var orden_lista_tmp="";
		for(var i=1;i<data.length;i++){
			var row=data[i];
			var cells = row.join(",").split(",");
			cells[0] = cells[0].split('"').join('');
			cells[1] = cells[1].split('"').join('');
			agregarListado( cells[0], cells[0]+'|'+cells[1] );
		}//fin de for i
	}

/*fin de cambio Oscar 2021*/
 function cambiaSC(val){
	//implementación de Oscar 22.05.2018 (paquetes)
		if(val==-20){//si es paquetes
			return true;
		}
	//fn de cambio		
		var url="getSubCat.php?id_categoria="+val;
		var res=ajaxR(url);
		
		var aux=res.split('|');
		if(aux[0] != 'exito')
		{
			alert(res);
			return false;
		}
		
		var obj=document.getElementById("tip");
		obj.options.length=0;
		
		obj.options[0] = new Option('----- Elige un tipo -----', -1);
		
		for(i=1;i<aux.length;i++)
		{
			ax=aux[i].split('~');
			obj.options[i] = new Option(ax[1], ax[0]);	
		}
		
	}

function cambiaTP(val)
	{
		var url="getTipo.php?id_subcategoria="+val;
		var res=ajaxR(url);
		
		var aux=res.split('|');
		if(aux[0] != 'exito')
		{
			alert(res);
			return false;
		}
		
		var obj=document.getElementById("subtip");
		obj.options.length=0;
		
		obj.options[0] = new Option('----- Elige un tipo-----', 0);
		
		for(i=1;i<aux.length;i++)
		{
			ax=aux[i].split('~');
			obj.options[i] = new Option(ax[1], ax[0]);	
		}
	}


 	function buscaProd()
 	{
		var aBusc = document.getElementById('busca').value;
		var url   = "../../../code/ajax/especiales/Etiquetas/etiquetas.php";
		$('#buscProd').html("");
		$.post(
				url,
				{
					texto:aBusc
				},
				function(data)
				{
					var i= 0;
					var datos = jQuery.parseJSON(data);
					document.getElementById('listaProd').style.display='none';
						document.getElementById('buscProd').className='mb';
						jQuery.each(datos, function(i, val) {
							 $('#buscProd').append("<input class='result' type='text'  id='"+datos[i].id_pr+"' value='"+datos[i].nombre+"' onfocus='coloca(this.value,this.id)' readOnly ></input>");
							});
				}
			);

 	}

 	function coloca(valor,id)
 	{
 		document.getElementById('busca').value=valor;
 		document.getElementById('proId').value=id;
 		document.getElementById('ag').disabled=false;
 	}
var rows_counter = 0;
 	function agregarListado( id = null, valor = null )
 	{
 		if( id == null && valor == null ){
 			valor = document.getElementById('busca').value;
 			id = document.getElementById('proId').value
 		}
 		/*var valor = document.getElementById('busca').value;
 		var id = document.getElementById('proId').value;*/
 		 $('#proLi').append("<li class='proLiC' id='li"+id+"'>"+valor+"<input type='text' name='pro' style='display:none' value='"+id+"'></input><a class='clsEliminarElemento'>&nbsp;</a></li>");
 		 document.getElementById('buscProd').className='ob';
 		 document.getElementById('listaProd').style.display='block';
 		 document.getElementById('busca').value='';
 		 document.getElementById('proId').value='';
 		 document.getElementById('ag').disabled=true;
 		 rows_counter ++ ;
 		 $( '#products_counter' ).val( rows_counter );
 	}

 	function getId(){
			var elementos  = document.getElementsByName('pro');
			var elementos2 = document.getElementsByName('parsTpl');
			var filtros1    = document.getElementsByName('filtros');
			var e          = [];
			var e2         = [];
			var filtros    = [];
			var band = 0;

		for(i=0;i<elementos2.length;i++){
        	e2.push(elementos2[i].value);
        }
        for(i=0;i<filtros1.length;i++){
        	filtros.push(filtros1[i].value);
        }

		if(elementos2[0].value == 0){
 			alert('Introduce un número de etiquetas mayor a cero!!!');
 			elementos2[0].focus();
 			return false;
 		}
	
 		if(elementos2[1].value == (-1)){
 			alert('Elige una plantilla!!!');
 			elementos2[1].focus();
 			return false;
 		}
 		//console.log(filtros[3]);
 		if(filtros[2] > 0 )
 		{
 			
 			band = 1;
 	    }

 		if(elementos.length > 0){
 			for(var i=0; i<elementos.length; i++) {
 			e.push(elementos[i].value);

          }

 		}else{
 			if(filtros[0]==(-1) && filtros [1] == (-1) && band == 0)
 			{
 				alert('Elige al menos un criterio de b\u00FAsqueda');
 				document.getElementById('categoria').focus();
 				return false;
 			}
 			{
 				e.push(null);
 			}
 			
 		}
//aqui condicionamos que filtre por oferta o sin oferta
	//implementación Oscar 12-02-2018
        var oferta="";
        if(document.getElementById('ofe').value==1){
        	oferta=" WHERE ax1.oferta=0";
        }
        if(document.getElementById('ofe').value==2){
        	oferta=" WHERE ax1.oferta=1";
        }
        //fin de cambio
    //implemenatción para impresión de paquetes Oscar 22.05.2018
    	var es_pqte=0;
    	if(document.getElementById('categoria').value==-2){
    		es_pqte=1;
    	}
    //fin de cambio
        var url= "../../../code/ajax/especiales/Etiquetas/crearEtiquetas.php"; 
        
        if( $('#ticket_plantilla').val() == 6 ){
        	url= "../../../code/ajax/especiales/Etiquetas/etiquetasSinPrecios.php"; 
        }
        $.post(
         	url,
         	{
         		'arr[]' :e,
         		'arr2[]':e2,
         		'fil[]' :filtros,
         		'ofert':oferta,/*implementado pr Oscar 2018 para filtrar productos con/sin oferta*/
         		'paquete':es_pqte/*implementado pr Oscar 22.05.2018 para  indicar que se trata de impresión de paquetes*/
         	},
         	function(data){
 //alert(data);
         		ax = data.split('|');
         		if(ax[0] == 'fail'||ax[0]!='fail' && ax[0]!='ok')
         		{
         			alert("Sin datos!!!\n");
         			alert(ax[0]);
         			$("#listaProd").html(ax[1]);
         		}
         		if(ax[0] == 'ok')
         		{
         			nuevaRuta = ax[1].substring(3,50);
         		    window.open(nuevaRuta);
         		}	
         		
         	}
         	)

 	}
 	
 		 
$(document).ready(function() {

 	$('#listaProd').on('click','.clsEliminarElemento',function(){
 		$liPadre = $($(this).parents().get(0));
 		$liPadre.remove();
 	});
   //Aquí van todas las acciones del documento.
});

 		
 	
 </script>
 <style>
 	ul.filters {
display: inline-flex;
list-style: none;
}
 	ul.filters2 {
display: inline-flex;
list-style: none;

}
 	ul#proLi{
list-style: none;


}
div#pli {
position: absolute;
left: 500px;
list-style: none;
}
input#busca {
width: 600px;
}
div#listaProd {
border: solid;
border-width: 2px;
width: 500px;
height: 300px;
position: relative;
left: 100px;
border-color: #64A512;
overflow: scroll;
}
div#buscProd.ob {
display: none;
width: 500px;
height: 500px;
top: 600px;
overflow: scroll;
left: 100px;
}
div#buscProd.mb {
position: relative;
width: 500px;
height: 300px;
overflow: scroll;
left: 100px;
}
.proLiC {
border: solid;
border-color: #64A512;
height: 30PX;
border-width: 1px;
background: white;

}

a.clsEliminarElemento {
padding: 5px;
border: solid 1px #ccc;
background: #fff url(imagen.png) center no-repeat;
border-radius: 3px;
width: 16px;
display: inline-block;
margin-right: 10px;
cursor: pointer;
float: right;
}
div#tpl {
position: relative;
float: right;
right: 90px;
bottom: 300px;
left: px;
}
div#ofertas {
position: relative;
float: right;
right: -70px;
bottom: 200px;
left: px;
}
#btn{
    position:relative;
    left:650px;
    list-style:none;
    bottom:23px;
}
#cosa2 {
    border-radius: 4px;
    padding: 20px 0px;
    background: none repeat scroll 0% 0% #F7F7F7;
    width: 120%;
    overflow-x: hidden;
    margin: 0px auto;
    display: inline-block;
    position: relative;
    left: 100px;
}
.btn_import{
	padding: 10px;
	margin-left: 30px;
	border-radius: 5px;
	background-color: gray;
	color: white;
}

.btn_import:hover{
	background-color: green;
}
.hidden{
	display: none;
}
label{
    font-weight:bold
}
#products_counter{
	padding: 8px;
}
.btn{
	padding : 10px;
}
 </style>

{/literal}
{include file="_footer.tpl" pagetitle="$contentheader"} 