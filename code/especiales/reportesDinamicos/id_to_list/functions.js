var ventana_abierta;
	function import_csv(){
		if( $( '#list_id' ).val() == 0 ){
			alert( 'Primero debe seleccionar una lista de Precios' );
			$( '#list_id' ).focus();
			return false;
		}
		$('#file_import').parse({
			config: {
				delimiter:"auto",
				complete: dataImport,
			},
		 		before: function(file, inputElem){
		 			//$("#before").css("display","none");//
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
	}
	function dataImport(results){
		var data = results.data;
		var arr="";
		var orden_lista_tmp="";
		for(var i=1;i<data.length;i++){
			var row=data[i];
			var cells = row.join(",").split(",");
    			arr+=cells[0];
			if(i<data.length-2){
				 arr+="|";
			}
		}
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'getList.php',
			data : { products : arr, list : $( '#list_id' ).val() },
			success : function ( dat ){
				$('#results').html( dat );
				$("#after").css("display", "");//
				$("#after_1").css("display", "");//
				$("#before").css("display", "none");
			}
		});	
	}
	function export_grid(){
		var tabla,trs,tds;
		var datos="";

	//obtenemos la tabla
		if(!document.getElementById("grid_resultado")){
			alert("Es necesario que escriba una consulta que retorne resultados!!!");
			return false;
		}
		tabla=document.getElementById("grid_resultado");
	
	//obtenemmos las filas
		trs=tabla.getElementsByTagName('tr');
	//obtenemos encabezado
    	tds=trs[0].getElementsByTagName('th');
		for(var i=0;i<tds.length;i++){
			datos+=tds[i].innerHTML.trim();
			if(i<tds.length-1){
				datos+=",";
			}
		}
		datos+="\n";
	//Obtenemos el cuerpo de la tabla
	    for(var j=1;j<trs.length;j++){	
	    //obtenemos celdas
	        tds=trs[j].getElementsByTagName('td');
	        for(var i=0;i<tds.length;i++){
				datos+=tds[i].innerHTML.trim();
				if(i<tds.length-1){
					datos+=",";//coma delimitadora
				}else if(i==tds.length-1){
					datos+="\n";//salto de línea
				}
			}    
	    }//termina for i
	//asignamos el valor a la variable del formulario
		$("#datos").val(datos);
	//enviamos datos al archivo que genera el archivo en Excel
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana,1500);			
	}

	function cierra_pestana(){
		$("#datos").val("");//resteamos variable de datos
		ventana_abierta.close();//cerramos la ventana
	}
	function panel(){
		if ( !confirm( "Salir de esta pantalla?" ) ) {
			return false;
		}	
		location.href = "../../../../index.php?";
	}