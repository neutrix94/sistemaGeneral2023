<script>

	{literal}

/* Implementacion Oscar 24-09-2010 para validar las fechas de la seccion de venta en linea*/
	function valida_fecha_tienda_linea(){
	//obtiene los valores de las fechas
		var pe_del = $("#pe_del").val();
    	var pe_al =	$("#pe_al").val();
    	var pn_del = $("#pn_del").val();
    	var pn_al = $("#pn_al").val();
    //valida
    	if(pe_del != '' && pe_al == ''){
    		alert("La fecha de precio especial 'hasta' no puede ir vacia si la fecha 'desde' tiene un valor"); return false;
    	}
    	if(pe_al != '' && pe_del == ''){
    		alert("La fecha de precio especial 'desde' no puede ir vacia si la fecha 'hasta' tiene un valor"); return false;
    	}
    	if(pn_del != '' && pn_al == ''){
    		alert("La fecha de precio nuevo 'hasta' no puede ir vacia si la fecha 'desde' tiene un valor"); return false;
    	}
    	if(pn_al != '' && pn_del == ''){
    		alert("La fecha de precio nuevo 'desde' no puede ir vacia si la fecha 'hasta' tiene un valor"); return false;
    	}
    	return true;
	}

/* Implementacion Oscar 23-09-2020 para validar catalogo de atributo*/
	function decide_usuario_elimina_atributo(pos){
		if(!(confirm("Si elimina un catalogo del atributo, en automático elimianará el atributo de los productos que lo contengan "  +
			"\nRealmente desea eliminarlo?"))){
			return false;
		}else{
			return true;
		}
	}

/*Implementacion Oscar 22-09-2020 para validar el tipo de producto*/
	function validacion_tipo_producto(){
		
		var valor_tipo_prod = $("#id_tipo_producto").val();
		var registros_maquila = NumFilas('productosDetalle');
		var registros_relacionados = NumFilas('productosConfigurables');
		var mensaje = "El tipo de producto es '" + $("#id_tipo_producto option:selected").text().trim() + "' y no permite ";
		var invalido = 0;
		if( valor_tipo_prod == 1){//simple
			mensaje += " que existan productos en maquila ni productos Configurables";

			if(registros_maquila > 0){
				mensaje += "\n- > El producto tiene : " + registros_maquila + " productos en Maquila" ;
				invalido = 1;
			}

			if(registros_relacionados > 0){
				mensaje += "\n- > El producto tiene : " + registros_relacionados + " productos Configurables" ;
				invalido = 1;
			}
			mensaje += "\n *Elimine los registros para poder guardar o cambie el tipo de producto y vuelva a intentar!";
		}

		if( valor_tipo_prod == 2){//configurable
			mensaje += " que existan productos en Maquila y debe de contener por lo menos un producto en la seccion (Producto configurable - Relacion de productos)";

			if(registros_maquila > 0){
				mensaje += "\n- > El producto tiene : " + registros_maquila + " productos en maquila, eliminelos si desea continuar o cambie el tipo de producto" ;
				invalido = 1;
			}

			if(registros_relacionados <= 0){
				mensaje += "\n - > El producto debe contener al menos un producto Relacionado" ;
				invalido = 1;
			}
		}

		if( valor_tipo_prod == 3){//maquila
			mensaje += " que existan productos Relacionados y debe de contener por lo menos un producto en la seccion (Maquila)";

			if(registros_relacionados > 0){
				mensaje += "\n- > El producto tiene : " + registros_relacionados + " productos Relacionado, eliminelos si desea continuar o cambie el tipo de producto" ;
				invalido = 1;
			}

			if(registros_maquila <= 0){
				mensaje += "\n- > El producto debe contener al menos un producto en Maquila" ;
				invalido = 1;
			}
		}

		if(invalido == 1){
			alert(mensaje);
			return false;
		}else{
			return true;
		}
	}

/*Implementacion Oscar 22-09-2020 para actualizar el nombre completo de las imagenes adicionales */
	function cambia_nombre_completo_img(pos){
		var nomb_imagen = celdaValorXY('imagenesAdicionales', 2, pos).trim();
		var format_imagen = $('#imagenesAdicionales_3_' + pos).html().trim();
		valorXY('imagenesAdicionales', 4, pos, (nomb_imagen + '.' + format_imagen) );
		//alert(nomb_imagen);
	}

/*Implementacion Oscar 2020 para actualizar el combo de valores de atributo*/
	function cambia_valor_atributo(pos){
		setTimeout(function(){ valorXY('atributosProducto', 3, pos, '');} , '100');
	//asignamos el valor de la categoria
		setTimeout(function(){ valorXY('atributosProducto', 4, pos, $("#id_categoria").val());} , '100');
		$("#atributosProducto_3_" + pos).click();

	}

/*Implemetacion Osscar 19.08.2019 para impresión de la credencial*/
	function imprimeCredencial(){//capturamos el id del usuario
		var id=$("#id_usuario").val();
	//mandamos a hacer la imagen del codigo de barras si no existe
		$.post( "../../touch/inc/img_codigo.php", {flag:'credencial',id_usuario:id},function(dat) {
			alert("Credencial Creada!!!");
		});
	}
/*Fin de cambio Oscar 19.08.2019*/

/*implementacion de Oscar 02.08.2019 para la importacion del csv de ordenes de compra*/
	var ventana_abierta_oc_detalle;
	var arr_detalle_oc="";

	function carga_datos_detalle_oc(){
		if($("#ocproductos tr").length>5){
			alert("No se puede importar archivo porque esta orden de compra ya tiene detalle!!!");
			return false;
		}
		document.getElementById('imp_detalle_oc').click();
	}

	function descarga_formato_detalle_oc(){
		ventana_abierta_oc_detalle=window.open('', 'TheWindow');
		document.getElementById('detalle_oc').submit();
		setTimeout(cierra_pestana,1000);
	}

	function cierra_pestana(){
		ventana_abierta_oc_detalle.close();//cerramos la ventana
	}

	function importacion_detalle_oc(results){
		var id_cab_oc=$("#id_orden_compra").val();
		if(id_cab_oc=='(Automático)'){
			alert("Primero debe de guardar la orden de compra y después importar!!!");
			return false;
		}
		var proveedor=$("#id_proveedor").val();
	//obtenemos el id del proveedor
		/*$("#id_proveedor").val();
		var id_estac=$("#id_proveedor").val();*/
		var data = results.data;//guardamos en data los valores delarchivo CSV
		if(data.length<=1){
			alert("El archivo esta vacío!!! Cargue un archivo con datos!!!");
			$("#btn_imp_detalle_oc").css("display","block");//mostramos botón de importación
			$("#submit_file_detalle_oc").css("display","none");//ocultamos botón de inserción
			$("#txt_info_detalle_oc_csv").val("");//limpiamos nombre del archivo seleccionado
			$("#txt_info_detalle_oc_csv").css("display","none");//ocultamos el nombre del archivo seleccionado
			return false;
		}

		for(var i=1;i<data.length;i++){
			var row=data[i];
			var cells = row.join(",").split(",");
			if(cells[0]!=''){
				if(cells.length!=8){
					alert("El archivo no tiene el formato requerido; verifiquelo y vuela a intentar!!!");
					$("#btn_imp_detalle_oc").css("display","block");//mostramos botón de importación
					$("#submit_file_detalle_oc").css("display","none");//ocultamos botón de inserción
					$("#txt_info_detalle_oc_csv").val("");//limpiamos nombre del archivo seleccionado
					$("#txt_info_detalle_oc_csv").css("display","none");//ocultamos el nombre del archivo seleccionado
					return false;
				}
				if(cells[7]!=proveedor){
					alert("El id de proveedor del archivo no corresponde al de la orden de compra, verifique su archivo CSV y vuelva a intentar!!!");
					$("#btn_imp_detalle_oc").css("display","block");//mostramos botón de importación
					$("#submit_file_detalle_oc").css("display","none");//ocultamos botón de inserción
					$("#txt_info_detalle_oc_csv").val("");//limpiamos nombre del archivo seleccionado
					$("#txt_info_detalle_oc_csv").css("display","none");//ocultamos el nombre del archivo seleccionado
					return false;
				}
    			arr_detalle_oc+=cells[0]+"~";//id de producto
    			arr_detalle_oc+=cells[4]+"~";//precio
    			arr_detalle_oc+=cells[5]+"~";//cantidad pedida
    			arr_detalle_oc+=cells[6]+"~";//piezas por caja
    			arr_detalle_oc+=cells[2];//alfanumerico
			/*}*/
				if(i<data.length-1){
					arr_detalle_oc+="|";
				}
			}
		}//fin de for i
	//enviamos datos por ajax
	//alert('a');
		$.ajax({
			type:'post',
			url:'../ajax/importarDetalleOrdenCompra.php',
			data:{fl:'importa_detalle_oc',datos:arr_detalle_oc,oc:id_cab_oc,id_prov:proveedor},
			cache:false,
			success:function(dat){
				if(dat=='ok'){
					alert("Detalle importado exitosamente");
					arr_detalle_oc="";
					location.href="listados.php?tabla=ZWNfb3JkZW5lc19jb21wcmE=&no_tabla=MQ==";
				}else{
					alert(dat);
				}
			}
		});
	//
	}

	//detectamos archivo cargado
	$("#imp_detalle_oc").change(function(){
		var fichero_seleccionado = $(this).val();
		var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
		if(nombre_fichero_seleccionado!=""){
			$("#btn_imp_detalle_oc").css("display","none");//ocultamos botón de importación
			$("#submit_file_detalle_oc").css("display","block");//mostramos botón de inserción
			$("#txt_info_detalle_oc_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
			$("#txt_info_detalle_oc_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
        				//$("#importa_csv_icon").css("display","none");
		}else{
			alert("No se seleccionó ningun Archivo CSV!!!");
			return false;
		}
	});

	$('#submit_file_detalle_oc').on("click",function(e){
		e.preventDefault();
		$('#imp_detalle_oc').parse({
			config: {
				delimiter:"auto",
				complete: importacion_detalle_oc,
			},
	 		before: function(file, inputElem){
	 			$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
			},
			error: function(err, file){
   			console.log("ERROR:", err, file);
			alert("Error!!!:\n"+err+"\n"+file);
			},
 				complete: function(){
			}
		});
	});

/*Fin de cambio Oscar 02.08.2019*/

	function cambia_total_mov(pos,flag){
		//alert(pos);
		if(flag==1){//si es cambio del combo de concepto de movimiento obtenemos el valor que afecta

			var url="../ajax/getCantidadEq.php?flag=combo_conc_mov&id_conc="+$("#movimientosCaja_3_"+pos).attr("valor");
//			alert(url);return false;
			var res=ajaxR(url);
			var aux=res.split('|');
			if(aux[0] != 'exito'){
				alert(res);
				return false;
			}
			$("#movimientosCaja_6_"+pos).attr("valor",aux[1]);
		}

		var afecta='$'+(parseFloat($("#movimientosCaja_6_"+pos).attr("valor"))*parseFloat($("#movimientosCaja_5_"+pos).attr("valor"))).toFixed(2);
		//alert(afecta);
		$("#movimientosCaja_7_"+pos).attr("valor",afecta);
		$("#movimientosCaja_7_"+pos).html(afecta);//,)
	}

	function ver_detalle_mov_caja(pos){
	//extraemos el id del movimiento
		var id_mov=$("#movimientosCaja_0_"+pos).attr("valor");
	//validamos que tenga cambios
		var url="../ajax/getCantidadEq.php?flag=checa_movs&id_mov="+id_mov;
			var res=ajaxR(url);
			var aux=res.split('|');
			if(aux[0] !='exito'){
				alert(res);
				return false;
			}else{
				url='listados.php?tabla=ZWNfYml0YWNvcmFfbW92aW1pZW50b19jYWph&no_tabla=MA==&id='+id_mov;
				location.href=url;
			}
	}
/**/

//	var num_fla=0;//implementado por Oscar 14/02/2017

		function validaUno(grid, nombre)
		{
			var num=NumFilas(grid);

			if(num <= 0)
			{
				alert('Debe insertar al menos un dato en el grid de '+nombre);
				return false;
			}

			return true;
		}
/*Implementacion Oscar 16-09-2020 para validar que no se repitan registros en el grid*/

		function validaRegistrosRespetidos(grid, celda_valida_repetidos, valor_comparacion){
			var elementos_existentes = new Array();
			var tmp;
		//recorre grid
			for(var i_g = 0; i_g <= NumFilas(grid); i_g++){
				tmp = celdaValorXY(grid, celda_valida_repetidos, i_g);

				for(var e_e = 0; e_e < elementos_existentes.length; e_e++){
					if(valor_comparacion == elementos_existentes[e_e] && elementos_existentes[e_e] != null){
						return 1;
					}
				}
				elementos_existentes.push(tmp);
			}
			return 0;
		}

		function cambiaDesc(pos, grid, posOri, posFin,dt,num_div,enf,celda_valida_repetidos )//dt agregado por Oscar 13/02/2017 (por implementacion de buscador)
		/*variable valida_repetidos implementada por Oscar 16-09-2020 */
		{
			//alert(pos + ' -- ' + grid + ' -- ' + posOri + ' -- ' + posFin + ' -- ' + dt + ' -- ' + num_div + ' -- ' + enf);

			var val_cant=document.getElementById("cantidad_"+num_div).value;//obtenemos la cantidad
			var val_busc=document.getElementById("b_g_"+num_div).value;
			if(val_busc==''||val_busc==null){
				alert("Debe seleccionar una opción antes de agregar el registro al Grid!!!");
				$("#b_g_"+num_div).select();
				return false;
			}
			if(val_cant==''||val_cant==null){
				alert("Debe introducir un valor númerico antes de agregar el registro al Grid!!!");
				$("#cantidad_"+num_div).select();
				return false;
			}
			//alert('OK'+' pos:'+pos+', grid:'+grid+', posOri:'+posOri+', posFin:'+posFin);

			var val=celdaValorXY(grid, posOri, pos);
			var aux=dt.split("°");
			var repetido = 0;
		/*implementacion Oscar 2020 para validacion de repetidos*/
			if(celda_valida_repetidos){
				repetido = validaRegistrosRespetidos(grid, celda_valida_repetidos, aux[1]);
				if(repetido == 1){
					alert("El registro ya se encuentra en este grid, pruebe con un registro diferente!");
					$("#b_g_"+num_div).select();
					return false;
				}
			}

			document.getElementById('aux_1_'+num_div).value=parseFloat(document.getElementById("cantidad_"+num_div).value);
			var xD=InsertaFila(grid,dt);


			if(!aux[1])
				aux[1]='';
			//alert(grid);
			valorXY(grid, posFin, pos, aux[1]);
			valorXYNoOnChange(grid, posOri, pos, aux[0]);


			//alert("cantidad_"+num_div+"\n"+grid+"_"+enf+"_"+pos);

			document.getElementById(grid+"_"+enf+"_"+pos).innerHTML=document.getElementById("cantidad_"+num_div).value;
			$('#'+grid+"_"+enf+"_"+pos).attr("valor",document.getElementById("cantidad_"+num_div).value);
			DesEditaCelda(grid+"_"+enf+"_"+pos,'aux_1_'+num_div,document.getElementById("cantidad_"+num_div).value);
			//alert(grid+"_"+parseInt(enf+1)+"_"+pos);
			$("#"+grid+"_"+parseInt(parseInt(enf)+1)+"_"+pos).attr("valor",document.getElementById("cantidad_"+num_div).value);
		//limpiamos buscador
			document.getElementById("b_g_"+num_div).value="";
			document.getElementById("cantidad_"+num_div).value="";
			$("#b_g_"+num_div).select();
			$("#img_add_"+num_div).attr("onclick","alert('Primero Seleccione un producto!!!');document.getElementById('b_g_"+num_div+"').select();");
		//aumentmos 1a l fila
			//num_fla+=1;
/*implementacion Oscar 2020 */
			if(aux[2] && grid != 'productosMovimiento'){
				$("#" + grid + "_" + enf + "_" + pos).html(aux[2]);
			}
		/*implementación Oscar 2022*/
			if( grid == 'productosMovimiento' ){
				//setTimeout( function(){//oscar 2023/11/16
				try{
					valorXY(grid, 8, pos, aux[2]);
					valorXYNoOnChange(grid, 8, pos, aux[2]);
				}catch(error){
					console.log( error );
					//alert( "Error : " + error );
				//	alert_scann( 'error' );
				}
				//}, 300 );
			}
		/*fin de cambio Oscar 2022*/
		}

	{/literal}


	{if $tabla64 eq 'ZWNfdHJhbnNmZXJlbmNpYXM='}
		{literal}





		function cambiaEquivalencia(pos)
		{
			var pres=celdaValorXY('transferenciasProductos', 6, pos);

			//alert(pres);

			var url="../ajax/getCantidadEq.php?id_presentacion="+pres;

			var res=ajaxR(url);

			var aux=res.split('|');

			if(aux[0] != 'exito')
			{
				alert(res);
				return false;
			}


			valorXY('transferenciasProductos', 10, pos, aux[1]);

			var can=celdaValorXY('transferenciasProductos', 7, pos);
			valorXY('transferenciasProductos', 7, pos, 0);
			valorXY('transferenciasProductos', 7, pos, can);

			return true;

		}

		function creaListado()
		{

			//alert('Test');

			url="../ajax/creaListadoProds.php?id_almacen="+document.getElementById('id_almacen_origen').value;

			RecargaGrid('transferenciasProductos', url);


		}

		function EliminaCeros()
		{

			{/literal}
				{if $no_tabla eq '0'}
			{literal}
			var num=NumFilas('transferenciasProductos');

			for(i=num-1;i>=0;i--)
			{
				if(celdaValorXY('transferenciasProductos', 6, i) == '0')
				{
					EliminaFila('transferenciasProductos_0_'+i);
				}

			}


			{/literal}
				{/if}
			{literal}


			var num=NumFilas('transferenciasProductos');

			if(num <= 0)
			{
				alert("Debe insertar productos a transferir");
				return false;
			}


			if(document.getElementById('id_almacen_origen').value == document.getElementById('id_almacen_destino').value)
			{
				alert('El almacen de origen y el de destino no pueden ser iguales');
				return false;
			}



			return true;
		}

		{/literal}
	{/if}


	{if $tabla eq 'ec_transferencias'}
		{literal}



			function verificaAlmacenes(val)
			{
				if(document.getElementById('id_almacen_origen').value == document.getElementById('id_almacen_destino').value)
				{
					alert('El almacen de origen y el de destino no pueden ser iguales');

					if(val == 1)
					{
						var obj =document.getElementById('id_almacen_destino');
						var obj2 =document.getElementById('id_almacen_origen');
					}
					else
					{
						var obj =document.getElementById('id_almacen_origen');
						var obj2 =document.getElementById('id_almacen_destino');
					}

					for(i=0;i<obj.options.length;i++)
					{
						if(obj.options[i].value != obj2.options[obj2.selectedIndex].value)
						{
							obj.options[i].selected=true;
							break;
						}
					}


				}
			}



			function actProdDes(pos)
			{

				var aux=celdaValorXY('transferenciasProductos', 2, pos);

				//alert(aux);

				var ax=aux.split(":");
				var id=ax[0];

				valorXY('transferenciasProductos', 2, pos, id);


				if(document.formaGral.no_tabla.value == '3')
				{

				}
				else
				{
					//valorXY('transferenciasProductos', 6, pos, -1);

					//Conseguimos el inventario en sucursal origen y destino

					var url="../ajax/catalogos/getInventarios.php?id_almacen_origen="+document.getElementById('id_almacen_origen').value;
					url+="&id_sucursal_origen="+document.getElementById('id_sucursal_origen').value;
					url+="&id_sucursal_destino="+document.getElementById('id_sucursal_destino').value;
					url+="&id_almacen_destino="+document.getElementById('id_almacen_destino').value;
					url+="&id_producto="+id;

					var res=ajaxR(url);
					var aux=res.split('|');

					if(aux[0] != 'exito')
					{
						alert(res);
						return false;
					}

					htmlXY('transferenciasProductos', 4, pos, aux[1]);
					htmlXY('transferenciasProductos', 5, pos, aux[2]);


					if(document.getElementById('id_tipo').value == 2)
					{
						valorXY('transferenciasProductos', 6, pos, aux[3]);
						//alert("htmlXY('transferenciasProductos', 6, "+pos+", '"+aux[4]+"');");
						setTimeout("htmlXY('transferenciasProductos', 6, "+pos+", '"+aux[4]+"');", 500);
					}
					else
						valorXY('transferenciasProductos', 6, pos, -1);
				}

			}

			/*$(document).ready(function() {
				$("#id_tipo, #id_sucursal_destino, #id_almacen_destino").on ("change", function () {
					cambiaSeleccionTransferencia ();
				});
				{/literal}

					{if $tipo eq '0'}
						cambiaSeleccionTransferencia ();
					{/if}
				{literal}
			});*/

			function cambiaSeleccionTransferencia ()
			{

				//alert('?');

				var obj=document.getElementById('id_tipo');

				if(obj.value == 2 || obj.value == 5)
					var nver=1;
				else
					var nver=0;

				if(document.getElementById('id_sucursal_origen').value == '' && nver == 0)
				{
					obj.value=2;
					alert('Debe elegir antes la sucursal de origen');
					return false;
				}

				if(document.getElementById('id_almacen_origen').value == '' && nver == 0)
				{
					obj.value=2;
					alert('Debe elegir antes el almacen de origen');
					return false;
				}

				if(document.getElementById('id_sucursal_destino').value == '' && nver == 0)
				{
					obj.value=2;
					alert('Debe elegir antes la sucursal de destino');
					return false;
				}

				if(document.getElementById('id_almacen_destino').value == '' && nver == 0)
				{
					obj.value=2;
					alert('Debe elegir antes el almacen de origen de origen');
					return false;
				}


				var id = '{/literal}{$llave}{literal}';
				if (id.match (/^\d+$/i))
					RecargaGrid ('transferenciasProductos', '../ajax/catalogos/getDatosGridTransferencia.php?id_transferencia=' + id);
				else
					RecargaGrid ('transferenciasProductos', '../ajax/catalogos/getDatosGridTransferencia.php?id_tipo=' + $('#id_tipo').val() + '&id_sucursal_origen=' + $('#id_sucursal_origen').val() + '&id_almacen_origen=' + $('#id_almacen_origen').val() + '&id_sucursal_destino=' + $('#id_sucursal_destino').val() + '&id_almacen_destino=' + $('#id_almacen_destino').val());


				return true;
			}

			function validaNuevoProductoTrans()
			{

				//alert('Ok');



				if(document.getElementById('id_sucursal_origen').value == '')
				{

					alert('Debe elegir antes la sucursal de origen');
					return false;
				}

				if(document.getElementById('id_almacen_origen').value == '')
				{

					alert('Debe elegir antes el almacen de origen');
					return false;
				}

				if(document.getElementById('id_sucursal_destino').value == '')
				{

					alert('Debe elegir antes la sucursal de destino');
					return false;
				}

				if(document.getElementById('id_almacen_destino').value == '')
				{

					alert('Debe elegir antes el almacen de origen de origen');
					return false;
				}

				return true;
			}

		{/literal}
	{/if}


	{if $tabla eq 'ec_conf_oc'}
		{literal}

			function cambiaEd(obj)
			{

				var opre=document.getElementById('prefijo_folio');
				var ocon=document.getElementById('contador_folio');

				if(obj.checked == true)
				{
					opre.readOnly=true;
					ocon.readOnly=true;
					opre.className="barra";
					ocon.className="barra";
					opre.value="";
					ocon.value=0;
				}
				else
				{
					opre.readOnly=false;
					ocon.readOnly=false;
					opre.className="barra_tres";
					ocon.className="barra_tres";
				}
			}


		{/literal}
	{/if}

	{if $tabla eq 'ec_clientes' && $no_tabla eq '0'}
		{literal}

			function cambiaPrecs(pos,tipoD)
			{
				//alert(tipoD)
				if(tipoD == 1)
				{
					//alert("1?");
					valorXYNoOnChange('clientesProductos', 5, pos, 0);
					htmlXY('clientesProductos', 5, pos, '0.00%');
					valorXYNoOnChange('clientesProductos', 6, pos, 0);
					htmlXY('clientesProductos', 6, pos, '$0.00');
				}
				if(tipoD == 2)
				{
					//alert("2?");
					valorXYNoOnChange('clientesProductos', 4, pos, 0);
					htmlXY('clientesProductos', 4, pos, '$0.00');
					valorXYNoOnChange('clientesProductos', 6, pos, 0);
					htmlXY('clientesProductos', 6, pos, '$0.00');
				}
				if(tipoD == 3)
				{
					//alert("3?");
					valorXYNoOnChange('clientesProductos', 4, pos, 0);
					//htmlXY('clientesProductos', 4, pos, '$0.00');
					valorXYNoOnChange('clientesProductos', 5, pos, 0);
					//htmlXY('clientesProductos', 5, pos, '0.00%');
				}
			}



		{/literal}
	{/if}

	{if $tabla eq 'ec_notas_credito' && $no_tabla eq '0'}

		{literal}

			function actPreImp(pos)
			{

				var aux=celdaValorXY('notascreditoProds', 2, pos);

				var ax=aux.split(":");

				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]);

				ax=aux.split('|');

				if(ax[0] == 'exito')
				{


					valorXY('notascreditoProds', 5, pos, ax[4]);

					valorXY('notascreditoProds', 9, pos, ax[2]);

					valorXY('notascreditoProds', 10, pos, ax[3]);

					valorXY('notascreditoProds', 4, pos, 0);

					valorXY('notascreditoProds', 4, pos, 1);
				}
				else
					alert(aux);
				//alert('11');
			}

			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;

				for(var i=0;i<NumFilas('notascreditoProds');i++)
				{
					var aux=celdaValorXY('notascreditoProds', 6, i);
					var can=celdaValorXY('notascreditoProds', 4, i);
					var iva=celdaValorXY('notascreditoProds', 7, i);
					var ieps=celdaValorXY('notascreditoProds', 8, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);

					//alert(iva);

					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;

					//var aux=celdaValorXY('ocproductos', 7, i);
				}


				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				//document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont, 2);
				//cambiaPagado();

			}



		{/literal}


	{/if}



	{if $tabla eq 'ec_rutas'}
		{literal}

			function muestraCliente(pos)
			{
				var aux=celdaValorXY('rutasPedidos', 2, pos);
				var ax=aux.split(':');
				valorXY('rutasPedidos', 3, pos, ax[2]);
				valorXYNoOnChange('rutasPedidos', 2, pos, ax[0]);
			}

		{/literal}
	{/if}

	{if $tabla eq 'ec_maquila'}
		{literal}

			function cambiaMaquila(val)
			{
				//alert('OK');
				RecargaGrid('productosFinal', '../ajax/catalogos/prodMaquila.php?id='+val)
			}

			cambiaMaquila(document.getElementById('id_producto').value);

		{/literal}
	{/if}

	{if $tabla eq 'ec_pedidos'}
		{literal}


			function habilitaDir(val)
			{
				obj=document.getElementById('direccion');
				if(val != -1)
				{
					obj.className='barra';
					obj.readOnly=true;
					obj.value="";
				}
				else
				{
					obj.className='barra_dos';
					obj.readOnly=false;
				}
			}

			function validaNuevaFila()
			{
				var cliente=document.getElementById('id_cliente').value;

				if(cliente == -1 || cliente == '')
				{
					alert("Debe elegir antes un cliente");
					return false;
				}

				return true;
			}


			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;

				for(var i=0;i<NumFilas('pedidoProductos');i++)
				{
					var aux=celdaValorXY('pedidoProductos', 6, i);
					var can=celdaValorXY('pedidoProductos', 4, i);
					var iva=celdaValorXY('pedidoProductos', 8, i);
					var ieps=celdaValorXY('pedidoProductos', 9, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);

					//alert(iva);

					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;

					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				for(var i=0;i<NumFilas('pedidoOtros');i++)
				{
					var aux=celdaValorXY('pedidoOtros', 3, i);
					var iva=celdaValorXY('pedidoOtros', 4, i);



					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);

					iva=aux*(iva/100);

					//alert(iva);

					totmont+=aux;
					totiva+=iva;


					//var aux=celdaValorXY('ocproductos', 7, i);
				}


				//calculamos descuento
				var url="../ajax/catalogos/getDescuentos.php?id_cliente="+document.getElementById('id_cliente').value;
				url+="&subtotal="+totmont+"&iva="+totiva;

				var aux=ajaxR(url);
				var ax=aux.split('|');
				if(ax[0] != 'exito')
				{
					alert(aux);
					return false;
				}

				var descuento=parseFloat(ax[1]);
				var totiva=parseFloat(ax[2]);

				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('descuento').value=redond(descuento, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont-descuento, 2);
				cambiaPagado();

			}

			function calculaTotales2()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;

				for(var i=0;i<NumFilas('pedidoProductos');i++)
				{
					var aux=celdaValorXY('pedidoProductos', 7, i);
					var can=celdaValorXY('pedidoProductos', 4, i);
					var iva=celdaValorXY('pedidoProductos', 8, i);
					var ieps=celdaValorXY('pedidoProductos', 9, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);

					//alert(iva);

					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;

					//var aux=celdaValorXY('ocproductos', 7, i);
				}
				for(var i=0;i<NumFilas('pedidoOtros');i++)
				{
					var aux=celdaValorXY('pedidoOtros', 3, i);
					var iva=celdaValorXY('pedidoOtros', 4, i);



					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);

					iva=aux*(iva/100);

					//alert(iva);

					totmont+=aux;
					totiva+=iva;


					//var aux=celdaValorXY('ocproductos', 7, i);
				}


				//calculamos descuento
				var url="../ajax/catalogos/getDescuentos.php?id_cliente="+document.getElementById('id_cliente').value;
				url+="&subtotal="+totmont+"&iva="+totiva;

				var aux=ajaxR(url);
				var ax=aux.split('|');
				if(ax[0] != 'exito')
				{
					alert(aux);
					return false;
				}

				var descuento=parseFloat(ax[1]);
				var totiva=parseFloat(ax[2]);

				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('descuento').value=redond(descuento, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('ieps').value=redond(totieps, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont-descuento, 2);

				cambiaPagado();

			}



			//calculaTotales();



			function cambiaPagado()
			{
				//alert('si');
				var total=document.getElementById('total').value;
				total=isNaN(parseFloat(total))?0:parseFloat(total);

				totpagos=0;

				for(i=0;i<NumFilas('pedidoPagos');i++)
				{
					var monto=celdaValorXY('pedidoPagos', 6, i);
					monto=isNaN(parseFloat(monto))?0:parseFloat(monto);

					totpagos+=monto;
				}

				if(totpagos >= total)
				{
					document.getElementById('pagado').value=1;
				}
				else
				{
					document.getElementById('pagado').value=0;
				}
			}

			function actPreImp(pos)
			{
				var aux=celdaValorXY('pedidoProductos', 2, pos);
				var ax=aux.split(":");
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]+"&id_cliente="+document.getElementById('id_cliente').value);

				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					valorXY('pedidoProductos', 5, pos, ax[1]);
					valorXY('pedidoProductos', 10, pos, ax[2]);
					valorXY('pedidoProductos', 11, pos, ax[3]);
					valorXY('pedidoProductos', 4, pos, 0);
					valorXY('pedidoProductos', 4, pos, 1);
				}
				else
					alert(aux);
			}


			function actPreImp2(pos)
			{
				var aux=celdaValorXY('pedidoProductos', 2, pos);
				var ax=aux.split(":");
				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?id="+ax[0]+"&id_cliente="+document.getElementById('id_cliente').value);

				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					valorXY('pedidoProductos', 6, pos, ax[1]);
					valorXY('pedidoProductos', 10, pos, ax[2]);
					valorXY('pedidoProductos', 11, pos, ax[3]);
					valorXY('pedidoProductos', 4, pos, 0);
					valorXY('pedidoProductos', 4, pos, 1);
				}
				else
					alert(aux);
			}

		{/literal}
	{/if}


	{if $tabla eq 'ec_ordenes_compra'}
		{literal}

			function validaCant(pos, val)
			{
				//alert(pos);

				var aux=celdaValorXY('ocProductos', 6, pos);

				//alert(aux);

				aux=parseInt(aux);
				aux1=parseInt(val);

				//alert(aux+" > "+aux1);

				if(aux1 < aux)
				{
					alert("No puede poner una cantidad menor a la previamente recibida");
					return false;
				}

				return true;
			}


			var est=parseInt(document.getElementById('id_estatus_oc').value);

			if(est > 3)
			{
				setValueHeader('ocproductos', 4, 'modificable', 'N');
			}

			function validaNuevaFilaOC()
			{

				var aux=parseInt(document.getElementById('id_estatus_oc').value);

				if(aux > 3)
				{
					alert('No es posible aregar mas productos a esta orden de compra');
					return false;
				}

				return true;
			}

			function calculaTotales()
			{
				var totmont=0;
				var totiva=0;
				var totieps=0;

				for(var i=0;i<NumFilas('ocproductos');i++)
				{
					var aux=celdaValorXY('ocproductos', 6, i);
					var can=celdaValorXY('ocproductos', 4, i);
					var iva=celdaValorXY('ocproductos', 8, i);
					var ieps=celdaValorXY('ocproductos', 9, i);

					can=isNaN(parseFloat(can))?0:parseFloat(can);
					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					ieps=isNaN(parseFloat(ieps))?0:parseFloat(ieps);

					//alert(iva);

					totmont+=isNaN(parseFloat(aux))?0:parseFloat(aux);
					totiva+=can*iva;
					totieps+=can*ieps;
					
/*implementacion Oscar 2022 Grid ordenes de compra*/
					var pieces_per_box = celdaValorXY('ocproductos', 7, i);
					valorXY('ocproductos', 8, i, Math.floor( aux/pieces_per_box ) );
					$( '#ocproductos_8_' + i ).html( Math.floor( aux/pieces_per_box ) );

					valorXY('ocproductos', 9, i, ( aux%pieces_per_box ) );
					$( '#ocproductos_9_' + i ).html(  aux%pieces_per_box );
/*fin de cambio Oscar 20202*/

				}
				for(var i=0;i<NumFilas('ocOtros');i++)
				{
					var aux=celdaValorXY('ocOtros', 3, i);
					var iva=celdaValorXY('ocOtros', 4, i);



					iva=isNaN(parseFloat(iva))?0:parseFloat(iva);
					aux=isNaN(parseFloat(aux))?0:parseFloat(aux);

					iva=aux*(iva/100);

					//alert(iva);

					totmont+=aux;
					totiva+=iva;


					var aux=celdaValorXY('ocproductos', 7, i);
				}

				document.getElementById('subtotal').value=redond(totmont, 2);
				document.getElementById('iva').value=redond(totiva, 2);
				document.getElementById('total').value=redond(totiva+totieps+totmont, 2);
				cambiaPagado();

			}



			calculaTotales();



			function cambiaPagado()
			{
				//alert('si');
				var total=document.getElementById('total').value;
				total=isNaN(parseFloat(total))?0:parseFloat(total);

				totpagos=0;

				for(i=0;i<NumFilas('ocpagos');i++)
				{
					var monto=celdaValorXY('ocpagos', 5, i);
					monto=isNaN(parseFloat(monto))?0:parseFloat(monto);

					totpagos+=monto;
				}

				//alert(totpagos+" "+total);

				if(totpagos >= total)
				{
					document.getElementById('pagada').value=1;
				}
				else
				{
					document.getElementById('pagada').value=0;
				}
			}

			function actPreImp(pos, prov)
			{
				var aux=celdaValorXY('ocproductos', 2, pos);
				var ax=aux.split(":");


				var id_prov=document.getElementById('id_proveedor').value;

				var aux=ajaxR("../ajax/catalogos/getDatosProd.php?&oc=1&id="+ax[0]+"&id_proveedor="+id_prov);

				ax=aux.split('|');
				if(ax[0] == 'exito')
				{
					//alert(aux);
					//valorXY('ocproductos', 6, pos, ax[4]);
					valorXY('ocproductos', 10, pos, ax[2]);
					valorXY('ocproductos', 11, pos, ax[3]);
					valorXY('ocproductos', 4, pos, 0);
					valorXY('ocproductos', 4, pos, 1);
				}
				else
					alert(aux);
			}

		{/literal}
	{/if}

	{if $tabla eq 'ec_productos'}

		{literal}


			function validaID(val)
			{
				var f=document.forms.formaGral;


				if(f.tipo.value == '0')
				{
					var res=ajaxR("../ajax/validaIDProd.php?id="+val);

					var aux=res.split('|');
					if(aux[0] != 'exito')
					{
						alert(res);
						f.id_productos.value="";
					}
				}

			}

			function cambiaGI(obj)
			{

				var of=document.getElementById("porc_iva");

				if(!of)
				{
					alert("Error objeto no encontrado");
					return false;
				}

				if(obj.checked == true)
				{
					of.readOnly=false;
					of.className="barra_tres";
				}
				else
				{
					of.readonly=true;
					of.className="barra";
					of.value="0";
				}
			}

			function cambiaGIE(obj)
			{

				var of=document.getElementById("porc_ieps");

				if(!of)
				{
					alert("Error objeto no encontrado");
					return false;
				}

				if(obj.checked == true)
				{
					of.readOnly=false;
					of.className="barra_tres";
				}
				else
				{
					of.readonly=true;
					of.className="barra";
					of.value="0";
				}
			}


		{/literal}

	{/if}

	{if $tabla eq 'ec_movimiento_almacen'}
		{literal}


			function validaProd(pos)
			{

				//alert(pos);
				var aux=celdaValorXY('productosMovimiento', 4, pos);

				var url="../ajax/validaIDProd.php?id="+aux;

				var res=ajaxR(url);

				if(res == 'exito')
				{
					alert('Producto inexistente');
					valorXYNoOnChange('productosMovimiento', 4, pos, '');

					return false;
				}
			}


		{/literal}
	{/if}

	{if $tabla eq 'ec_devolucion_transferencia'}
		{literal}

			function validaResolucionDev()
			{

				var num=NumFilas('transferenciaProducto');
				var prod="";
				var mer="";
				var vu="";

				for(var i=0;i<num;i++)
				{
					var cantidad=celdaValorXY('transferenciaProducto', 4, i);
					var merma=celdaValorXY('transferenciaProducto', 5, i);
					var vuelto=celdaValorXY('transferenciaProducto', 6, i);

					if(prod != '')
					{
						prod+=",";
						mer+=",";
						vu+=",";
					}

					prod+=celdaValorXY('transferenciaProducto', 0, i);
					mer+=merma;
					vu+=vuelto;

					cantidad=parseFloat(cantidad);
					merma=parseFloat(merma);
					vuelto=parseFloat(vuelto);


					if(cantidad != (merma+vuelto))
					{
						alert("Debe resolver todos los casos en la linea de detalle "+(i+1));
						return false;
					}

				}


				var url="../ajax/resuelveDev.php?ids="+prod+"&mer="+mer+"&vu="+vu;

				var res=ajaxR(url);


				if(res != 'exito')
				{
					alert(res);
					return false
				}


				alert('Se ha resuelto satisfctoriamente la devolucion');
				document.getElementById("resuelta").value="1";

				return true;
			}


			function actualizaGridProd()
			{
				val = document.getElementById('id_transferencia').value;


				RecargaGrid('transferenciaProducto', '../ajax/catalogos/Transferencia.php?id='+val)


				//Actualizamos la sucursal

				var url="../ajax/getSucAm.php?id_trans="+val;
				var res=ajaxR(url);

				var aux=res.split('|');

				if(aux[0] != 'exito')
				{
					alert(res);
					return false;
				}

				var obj=document.getElementById("id_sucursal_destino_1");
				/*for(i=0;i<obj.options.length;i++)
				{
					alert(obj.options[i].text+" "+obj.options[i].index);
				}*/
				obj.value=aux[1];
				document.getElementById("id_sucursal_destino").value=aux[1];



			}

			//recargaGrid(document.getElementById('id_transferencia').value);

		{/literal}
	{/if}

</script>
