	var total = 0;
	
	function dataImport(results){
	//lanzamos la emergente
		$("#mensaje_pres").html('<p align="center" style="color:white;font-size:30px;">Cargando datos<br><img src="../../../img/img_casadelasluces/load.gif" width="120px"></p>');
		$("#cargandoPres").css("display","block");
		
		var id_estac=$("#id_estacionalidad").val();
		var data = results.data;//guardamos en data los valores delarchivo CSV
		var tam_grid=$("#estacionalidadProducto tr").length-3;
		//alert(data);
		//return true;
		var arr="";
		var orden_lista_tmp="";
		for(var i=1;i<data.length;i++){
			//arr+=data[i];
			var row=data[i];
			var cells = row.join(",").split(",");
			/*for(j=0;j<cells.length;j++){*/
    			arr+=cells[0]+",";
    			arr+=cells[7];//se cambia la posición  de 6 a 7 por la implementación de la clave de proveedor Oscar 26.02.2019 
			/*}*/
			if(i<data.length-1){
				 arr+="|";
			}
/*implementación Oscar 30.09.2019 para validar que el archivo CSV este ordenado por orden de lista correctamente*/
			if(parseInt(orden_lista_tmp)>parseInt(cells[1]) && i>1){
				alert("Los productos no están ordenados por orden de lista; verifique su archivo y vuelva a intentar!!!");//+orden_lista_tmp+"|"+cells[1]
				location.reload();
				i=data.length;
				return false;
			}
			orden_lista_tmp=cells[1];
/*Fin de cambio Oscar 30.09.2019*/

		}//fin de for i

		//alert(arr);return false;//0 7 
		$.ajax({
			type : 'post',
			url : 'importToTable.php',
			cache : false,
			data : { file : arr, action : 'import', list : $( '#price_id' ).val() },
			success : function ( dat ){
				var response = dat.split('|');
				if ( response[0] != 'ok' ){
					alert(dat);
					return false;
				}else{
					for (var i = 2; i < response.length; i++) {
						build_row( response[i] );
					}
					$( '#header_id' ).val( response[1] );
					$( '#send_button' ).attr( 'disabled', false );
					$( '#imp_csv_prd' ).css( 'display', 'none' );
					$( '#submit-file' ).css( 'display', 'none' );
				$( '#total' ).val( parseInt(total) );
				}
			}

		});
	}

	function build_row( product ){
		product = product.split('~');
		row = '<tr>';
		for (var i = 0; i < product.length; i++) {
			row += '<td' + ( i == 0 ? ' style="display:none;"' : '' ) + '>' + product[i] +'</td>';
			if ( i == 4 ) { 
				total += parseFloat(product[i]);
			}
		}
		row += '</tr>';
		$( '#previous' ).append( row );
	}

	function redirection(){
		location.href = '../../../touch/index.php?scr=cerrar-venta&idp=' + $( '#header_id' ).val() + '&tv=' + $( '#price_id' ).val() + '&id_pedido_original=#';
	}