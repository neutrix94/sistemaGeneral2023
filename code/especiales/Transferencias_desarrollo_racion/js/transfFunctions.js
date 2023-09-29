//4.1. Funcion para generar el calculo de Transferencia por medio del archivo %nuevaTransferencia.php%%


function get_consumables( desinity_warehouse ){
	var url = "ajax/getConsumables.php?fl_type=priorityCount&warehouse_id=" + desinity_warehouse;
	var resp = ajaxR( url );
	return resp;
}
//llamadas asincronas
function ajaxR(url){
	if(window.ActiveXObject)
	{		
		var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else if (window.XMLHttpRequest)
	{		
		var httpObj = new XMLHttpRequest();	
	}
	httpObj.open("POST", url , false, "", "");
	httpObj.send(null);
	return httpObj.responseText;
}
function close_emergent(){
	$(".emergent_content").html( '' );//#proceso
	$('.emergent').css( 'display' , 'none' );//mendamosventana de informe
	$( '#id_tipo' ).val( '0' ); 
}

function save_consumables_inventory_adjustment(){
	var success = true;
	var adjustment = "";
	$( '#consumablesList tr' ).each( function ( index ){
		if( $( '#consumables_2_' + index ).val() == '' ){
			success = index;
			return false;
		}else if( $( '#consumables_2_' + index ).val() != 0 ){
			adjustment += ( adjustment == "" ? "" : "|~|" );
			adjustment += $( '#consumables_3_' + index ).attr( 'value' ) + "|";//id_producto
			adjustment += $( '#consumables_4_' + index ).attr( 'value' ) + "|";//id_proveedor_producto
			adjustment += $( '#consumables_2_' + index ).val();//cantidad
		}
	});
	if( success != true ){
		alert( "Hay productos sin contar, verifica y vuelve a intentar!" );
		$( '#consumables_1_' + success ).focus();
		return false;
	}
//envia datos para hacer el ajuste de inventario
	if( adjustment != "" ){
		$.ajax({
			type:'post',
			url:"ajax/insert_consumables_adjustment.php",
			data:{
				consumables : adjustment,
				store_id : $( '#destino' ).val()
			},
			success:function( dat ){
				var aux = dat.split( '|' );
				if( aux[0] != 'ok' ){
					alert( "Error : \n" + dat );
					return false;
				}else{
					alert( aux[1] );
					ejecutar( 1 );
				}
			}
		});
	}else{
		ejecutar( 1 );
	}
}

function ejecutar( flag ){
//obtenemos valores para enviarlos por ajax
	var sucOrigen=document.getElementById('origen').value;
	var sucDestino=document.getElementById('destino').value;
	var alOrigen="";
	var alDestino=document.getElementById('id_almacen_destino').value;
	var filtrarPor=document.getElementById('filtroPor').value;//obtenemos valor del filtro(mostrar todo o por stock bajo)
//verificamos si es vaciar almacen
	if(tipo==6){
		alOrigen=document.getElementById('id_almacen_origen').value;
		if(filtrarPor=='s_b' || filtrarPor=='full'){
			$('#filtroPor > option[value=""]').attr('selected',true);
			filtrarPor=='';
		}
	}else{
		alOrigen=document.getElementById('id_almacen_origen').value;
	}
//validamos datos de sucursales y almacenes	
	if(sucOrigen=="0"){
		alert("Elija sucursal de origen");
		return null;
	}
	if(tipo==4 && filtrarPor=="" || tipo==3 && filtrarPor=="" || tipo==1 && filtrarPor==""){//verificamos que el friltrado no este vacio en los tipos correspondientes
		alert('selecciona el tipo de filtrado');
		$('#id_tipo').get(0).selectedIndex = 0;
		document.getElementById('filtroPor').focus();
		return false;
	}
	if(sucDestino=="0"){
		alert("Elija sucursal de Destino");
		document.getElementById('id_tipo').value="";
		return null;
	}
	if(alOrigen==alDestino){//si los almacenes son iguales;
		alert("El almacen de origen y destino deben ser diferentes");//mandamos error
		document.getElementById('id_tipo').value="0";//reseteamos el select tipo
		return null;//retornamos false
	}else{//caso contrario;
	var tipo=document.getElementById('id_tipo').value;//obtenemos valor de tipo de transferencia
		if( flag == '2' && ( tipo == 1 || tipo == 3 || tipo == 4 ) ){
			var nota="", titulo_trans = "";
			var revisa_datos=0;
			var transfer_type = $('#tipo').val();
			var consumables = get_consumables( alDestino );
			//alert( consumables );
				var msg = `<div class="row">
					<div class="col-12 text-end">
						<button 
							type="button"
							class="btn btn-danger"
							onclick="close_emergent();">
							X
						</button>
					</div>
				</div>
				<div class="row text-center">
					<h3>Captura el inventario de los consumibles para continuar con la transferencia : </h3>
					<div class="col-12" style="max-height : 500px; overflow : scroll;">
						${consumables}
					</div>

					<div class="row">
							<div class="col-2"></div>
							<div class="col-8">
								<br>
								<button 
									onclick="save_consumables_inventory_adjustment();" 
									class="btn btn-success form-control">
									<i class="icon-floppy">Guardar y calcular transferencia</i>
								</button>
								<br><br>
								<button 
									onclick="ejecutar( 1 );" 
									class="btn btn-warning form-control">
									<i class="icon-warning">Omitir conteo</i>
								</button>
							</div>
						</div>
				</div>`;
				$(".emergent_content").html(msg);//#proceso
				$('.emergent').css( 'display' , 'block' );//mendamosventana de informe
				$('#cargando').css('z-index','10000');//mendamosventana de informe
				$("#cargando img").css("display","none");
				return false;
		}
		var content = `<div class="text-center">
				<h3 style="font-size : 200% !important;">Generando Transferencia</h3>
				<img src="img/load.gif" witdh="30%" height="30%">
		</div>`;
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
		var contCategoria=document.getElementById('cont_fam').value;//obtenemos contador de categorias
		var filtro1;//declaramos filtrado
		if(contCategoria==0){//si no se hicieron filtrados por categoria;
			filtro1='';//dejamos variable de filtrado vacia
		}else{//casocontrario;
			filtro1=document.getElementById('filtroFam').value;//obtenemos condición para filtrar consultapor categoria
			if(filtro1==' AND ()'){
				alert("Debes elegir por lo menos una categoría!!!");
				close_emergent();
				return false;
			}
		}
		$.ajax({
			type:'post',
			url:"nuevaTransferencia.php",
			data:{origen:sucOrigen,
				destino:sucDestino,
				al_origen:alOrigen,
				al_destino:alDestino,
				id_tipo:tipo,
				filtroFam:filtro1,
				filtrarPor:filtrarPor
			},
			success:function(datos){
				$('#general').html(datos);
				document.getElementById("btn_guardar").style.display="block";
				close_emergent();
			}
		});
	}
}
//4.2. Funcion para obtener almacenes de sucursal por medio del archivo %obtenerAlmacen.php%%
function prueba(flag){					
	var accion=flag;
	if(accion==1){
	var id=document.getElementById('origen').value;
	$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{id_sucursal:id,a:accion},
		success: function(datos){
				$('#id_almacen_origen').empty();
				$('#id_almacen_origen').append(datos);
			}	
		});
	}else if(accion==2){
	var id=document.getElementById('destino').value;
		//alert(id);
		$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{id_sucursal:id,a:accion},
		success: function(datos){
				$('#id_almacen_destino').empty();
				$('#id_almacen_destino').append(datos);
			}	
		});
		}
	}

var accionCheck=0;
//4.3. Funcion que filtra genera filtro de categorias
function check(cont){//recibimos cont
	if(cont==0){//en caso de ser el checkbox de habilitar/deshabilitar
		var nCat=document.getElementById('numCat').value;//obtenemos numero de sucursales
		var aux="";//declaramos variable auxiliar
		for(var i=0;i<nCat;i++){//creamos for para deshabilitar/habilitar checkbox
			aux='ch_'+parseInt(i+1);//generamos ids
			if(accionCheck==1){
			document.getElementById(aux).checked=true;//habilitamos checkbox
			}else{
			document.getElementById(aux).checked=false;//deshabilitamos checkbox
			}
		}
		if(accionCheck==1){
				accionCheck=0;
		}else{
			accionCheck=1;
		}
	}
	var elem='ch_';//creamos prefijo para id de cada checkbox
	var tamaño=document.getElementById('numCat').value;//obtenemos numero de categorias existentes
	var contAccion=document.getElementById('cont_fam').value;//valuamos si hay acciones
	var valores=new Array();//declaramos arreglo que contendra id de cada categoria
	var sql="";//declaramos variable de consulta
	var consultas=0;//declaramos contador de consultas
	for(var i=0;i<tamaño;i++){//condicionamos for e acuerdo a numero de categorias
		var ch='ch_'+parseInt(i+1);//creamos variable temporal de id
		var clave=document.getElementById(ch).value;//extraemos el id de la categoria
	//generamos condiciones
		if($("#"+ch).is(':checked')){//si el checkbox esta marcado
			if(consultas==0){//si contador de consultas esta en ceros
				valores[i]=" p.id_categoria="+clave+"";//guardamos primera condición
				consultas+=1;//incrementamos contador de consultas
			}else{//de lo contrario
				valores[i]=" or p.id_categoria="+clave+"";//guardamos siguientes condiciones
			}
		}else{//de lo contrario
			valores[i]="";//no afectamos a consulta
		}
	}
	//arma consulta
	sql=" AND (";
		for(var i=0;i<tamaño;i++){
			sql=sql+valores[i];//asignamos condiciones a variable de consulta
		}
	sql+=")";
		//alert(sql);
		document.getElementById('filtroFam').value=sql;//asignamos nuevo valor a variable de condicion
		document.getElementById('cont_fam').value=1;//marcamos que se ha generado condicion
}
//4.4. Funcion para obtener combo de sucursales destino
function obtenerCombo(){
	$.ajax({
		type:'post',
		url:'obtenerAlmacen.php',
		data:{a:3},
		success:function(datos){
			$('#dest').html(datos);
		}
	});

}