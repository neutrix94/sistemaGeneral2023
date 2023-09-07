
{include file="_header.tpl" pagetitle="$contentheader"}
<meta charset="UTF-8">
  <div id="campos">  
<div id="titulo">5.8 Reporte de ventas CSV</div>
<br><br>

<div id="filtros">
<div id='cosa2'>
<form action="">
	<ul class ='filters'>
		<li><label for=""></label></li>
		<li>&nbsp;&nbsp;<label >Del:</label><input type="text" name="filtros" class="fechas"></li>		
		<li>&nbsp;&nbsp;<label >Al:</label><input type="text" name="filtros" class="fechas"></li>
		<li>&nbsp;&nbsp;<label >Forma de pago:</label>
			<select id="categoria" name='filtros'>
				{html_options values=$vals output=$textos}
			</select>
		</li>
		<li><input type="button"  value="Filtrar" class="btns" onclicK="buscaInfo()"></li>
	</ul>
	<div id='listaProd'>
		<table id='proLi' border ='1'>
		<th><input type="checkbox" onclick="todos()" id='all'></th>
		<th>Folio</th>
		<th width="300">Cliente</th>
		<th width="100">Fecha</th>
		<th width="100">Monto</th>
		<th>Estatus</th>
		<th>Forma de Pago</th>		
		</table>
	</div>
	<div id='buscProd' class='ob' >
	
    </div>
    <div id='btn'>
    		<li><input type="button" id='exportar' value='Exportar' onclick='exporta()' disabled></li>

    </div>
</div>
</form>
</div>



{literal}
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
}
input#busca {
width: 600px;
}
div#listaProd {
border: solid;
border-width: 2px;
width: 750px;
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
#btn{
    position:relative;
    left:740px;
    list-style:none;
    
}
#cosa2 {
    border-radius: 4px;
    padding: 20px 0px;
    background: none repeat scroll 0% 0% #F7F7F7;
    width: 100%;
    overflow-x: hidden;
    margin: 0px auto;
    display: inline-block;
    position: relative;
    left: 200px;
}
label{
    font-weight:bold
}
.fechas {
width: 200px !important;
}
input.btns {
position: relative;
right: 100px;
top: 14px;
}
 </style>

 <script>
  
  function todos(){
  	var elementos  = document.getElementsByName('si');
  	

         		 for(i=0;i<elementos.length;i++)
         		 {
         		 	elementos[i].checked=document.getElementById('all').checked;
         		 	
         		 }
    }
 	function buscaInfo()
 	{
 		var fechas = document.getElementsByName('filtros');
 		var aFechas = [];
 		var url   = "../../../code/ajax/especiales/CSV/csvInfo.php";
 		
 		for(var i = 0;i<fechas.length;i++)
 		{
 			if(fechas[i].value == (-1))
 				{
 					alert('Elige una forma de pago por favor');
 					fechas[i].focus();
 					return false;
 				}
 			if(fechas[i].value== '')
 			{				
 				alert('Debes ingresar una fecha');
 				fechas[i].focus();
 			}
 			else
 			{				
 				aFechas.push(fechas[i].value);
 			}				
		}
		$.post(
				url,
				{
					'fechas[]':aFechas
				},
				function(data)
				{
					var i= 0;
					var datos =[];
					var datos = jQuery.parseJSON(data);
					
					limpiaCampos();
					if(datos == null)
					{
						alert('No hay registros que coincidan con tu busqueda');

					}
					else
					{
						document.getElementById('exportar').disabled=false;	
						jQuery.each(datos,function(i,val){
							 $('#proLi').append("<tr class='filas'><td><input type='checkbox' value='"+datos[i].id+"' name='si'></input></td><td>"+datos[i].folio+"</td><td>"+datos[i].cliente+"</td><td>"+datos[i].fecha+"</td><td>"+datos[i].monto+"</td><td>"+datos[i].estatus+"</td><td>"+datos[i].exportado+"</td></tr>");
							});	
					}	

						
				}
			);
 	}

 	function coloca(valor,id)
 	{
 		document.getElementById('busca').value=valor;
 		document.getElementById('proId').value=id;
 		document.getElementById('ag').disabled=false;
 	}

 	function agregarListado()
 	{
			var valor = document.getElementById('busca').value;
			var id    = document.getElementById('proId').value;
 		 $('#proLi').append("<li class='proLiC' id='li"+id+"'>"+valor+"<input type='text' name='pro' style='display:none' value='"+id+"'></input><a class='clsEliminarElemento'>&nbsp;</a></li>");
 		 document.getElementById('buscProd').className='ob';
 		 document.getElementById('listaProd').style.display='block';
 		 document.getElementById('busca').value='';
 		 document.getElementById('proId').value='';
 		 document.getElementById('ag').disabled=true;
 	}

 	function exporta()
 	{	
 			var tipo =document.getElementById('categoria').value;
			var elementos = document.getElementsByName('si');	
			var e         = [];
			var count     = 0;
			for(var i =0;i<elementos.length;i++)
			{				
				if(elementos[i].checked)
				{
					e.push(elementos[i].value);
					count++;
				}
			}
			if(count==0)
			{
				alert('Debes seleccionar al menos un registro');
				return false;
			}
			else{
		         var url   = "../../../code/ajax/especiales/CSV/generaCSV.php"; 
		         $.post(
			         	url,
			         	{
			         		'arr[]':e,
			         		 tipo:tipo      		
			         	},
			         	function(data){
			         		//alert("Archivo generado con éxito");
			         		rutaA = data.substring(3,42);
			         		
			         		ventana= window.open(rutaA);
			         		//ventana.close();
			         		limpiaCampos();
			         		 var elementos  = document.getElementsByName('fechas');	
			         		 for(i=0;i<elementos.length;i++)
			         		 {
			         		 	elementos[i].value='';
			         		 }
			         	}						
		         	);
         }
 	}

 	function limpiaCampos()
 	{
 		
 			
 			$('#proLi').html('<th><input type="checkbox" onclick="todos()" id="all"></th><th>Folio</th><th width="300">Cliente</th><th width="100">Fecha</th><th width="100">Monto</th><th>Estatus</th><th>Forma de pago</th>');
 			document.getElementById('exportar').disabled=true;
 	} 
 	
 		 
$(document).ready(function() {

 	$('#listaProd').on('click','.clsEliminarElemento',function(){
 		$liPadre = $($(this).parents().get(0));
 		$liPadre.remove();
 	});

 	$('.fechas').datepicker();
   //Aquí van todas las acciones del documento.
});



 $.datepicker.regional['es'] = {
 closeText: 'Cerrar',
 prevText: '<Ant',
 nextText: 'Sig>',
 currentText: 'Hoy',
 monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
 monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
 dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', "S\u00E1bado"],
 dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
 dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S\u00E1'],
 weekHeader: 'Sm',
 dateFormat: 'yy-mm-dd',
 firstDay: 1,
 isRTL: false,
 showMonthAfterYear: false,
 yearSuffix: ''
 };
 $.datepicker.setDefaults($.datepicker.regional['es']);
$(function () {
$("#fecha").datepicker();
});

 	
 </script>

{/literal}
{include file="_footer.tpl" pagetitle="$contentheader"} 