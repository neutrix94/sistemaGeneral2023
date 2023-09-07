/*$(document).ready(function() {
    console.log("Hola");

});
*/


/*$("#estado_presentacion").on('click',function() {
	alert("que pedro");
});*/
//funcion que activa todas las presentaciones
function presentacion(){
	var estadoPre;
	estadoPre = $('#estado_presentacion').is(':checked');


	$("#sucursalProducto :checkbox").prop("checked", estadoPre);

//alert('here');
}

function validaSucursalProducto(tabla){
	alert(tabla);
	var contador = 0;
	var valorPresentacion = 0;
	if($('#estado_presentacion').is(':checked') == false){
		return true;
	}
	else{
		/*$("#sucursalProducto tbody tr").each(function (index, element) {
			if (index > 2){
				valorPresentacion = 0;
				valorPresentacion = $(this).find("td").eq(7).html();
				check = $(valorPresentacion).attr("valor");
				if(check == "checked"){
					valor = $(this).find("td").eq(8).html();
					if(valor == 0)
						contador = contador + 1;
				}
			} 
		});*/


		var obj=document.getElementById(tabla);
		
		
		var tipos=new Array();	
		obj=document.getElementById('Head_'+tabla);
		var Trs=obj.getElementsByTagName('tr');	
		var Tds=Trs[0].getElementsByTagName('td');
		
		
		obj=document.getElementById('Body_'+tabla);
		Trs=obj.getElementsByTagName('tr');
		var numdatos=Trs.length;
		var iteracion=0, numdatAct=0;
		
		


		for(var i=0;i<numdatos;i++)
		{
			Tds=Trs[i].getElementsByTagName('td');
			for(var j=1;j<Tds.length;j++)
			{
				if(j == 7)
				{	
					ax=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute('valor');
					if(ax == 1)
					{
						valor = Tds[8].valor?Tds[8].valor:Tds[8].getAttribute('valor');
						if(valor == 0)
							contador = contador + 1;	
					}
				}
				
			}
		}



		if (contador == 0)
			return true;
		else
			return false;
	}
}

function validaSucrsalStock(tabla){
	contador = 0;
	var valorStock;
	var estadoSucursal;
	/*$("#sucursalProducto tbody tr").each(function (index, element) {
			if (index > 2){

				valorStock = 0;
				estadoSucursal = 0;
				estadoSucursal = $(element).find("td").eq(2).html();
				estadoSucursal = $(estadoSucursal).attr("valor");

				if(estadoSucursal == "checked"){
					valorStock = $(element).find("td").eq(6).html();
					if(valorStock == 0)
						contador = contador +1;
				}
			} 
		});*/
	var obj=document.getElementById(tabla);
		
		
		var tipos=new Array();	
		obj=document.getElementById('Head_'+tabla);
		var Trs=obj.getElementsByTagName('tr');	
		var Tds=Trs[0].getElementsByTagName('td');
		
		
		obj=document.getElementById('Body_'+tabla);
		Trs=obj.getElementsByTagName('tr');
		var numdatos=Trs.length;
		var iteracion=0, numdatAct=0;
		
		


		for(var i=0;i<numdatos;i++)
		{
			Tds=Trs[i].getElementsByTagName('td');
			for(var j=1;j<Tds.length;j++)
			{
				if(j == 2)
				{	
					ax=Tds[j].valor?Tds[j].valor:Tds[j].getAttribute('valor');
					if(ax == 1)
					{
						valor = Tds[6].valor?Tds[6].valor:Tds[6].getAttribute('valor');
						if(valor == 0)
							contador = contador + 1;	
					}
				}
				
			}
		}
	if (contador == 0)
		return true;
	else
		return false;
}

function getJsonSucPro(){
	jsonObj = [];
	$("#sucursalProducto tbody tr").each(function (index, element) {
			if (index > 2){

				var id = $(element).find("td").eq(1).val();
				var	estadoSuc = $(element).find("td").eq(2).html();
				estadoSuc = $(estadoSuc).is(':checked');
				var idSuc = $(element).find("td").eq(3).val();
				var idPro = $(element).find("td").eq(4).val();
				var stock = $(element).find("td").eq(6).html();
				var	estadoPre = $(element).find("td").eq(7).html();
				estadoPre = $(estadoPre).is(':checked');
				var numPre = $(element).find("td").eq(8).html();

				item = {}
				item ["id"] = id;
				item ["estadoSuc"] = estadoSuc;
				item ["idSuc"] = idSuc;
				item ["idPro"] = idPro;
				item ["stock"] = stock;
				item ["estadoPre"] = estadoPre;
				item ["numPre"] = numPre;

				jsonObj.push(item);
			} 
		});
	guardaSucPro(jsonObj);
}


function guardaSucPro(listaProductos){
	var productosJSON = JSON.stringify(listaProductos);
	$.getJSON( "sucursalProducto.php", {productos: productosJSON})
    .done(function( data, textStatus, jqXHR ) {
    	console.log( "La solicitud se ha completado correctamente." );
    })
    .fail(function( jqXHR, textStatus, errorThrown ) {
    console.log( "Algo ha fallado:")
	});
}



/*function guardaSucPro(listaProductos){
	var productosJSON = JSON.stringify(listaProductos);
	$.post('sucursalProducto.php', {productos: productosJSON},
    function(respuesta) {
        console.log(JSON.parse(respuesta));
	}).error(function(){
        console.log('Error al ejecutar la petición');
    });
}*/





