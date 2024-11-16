<?php
	if( ! include( '../../../../../../conect.php' ) ){
        die( "No se incluyo archivo de conexion : '../../../../../../conect.php'" );
    }
	if( ! include( '../../../../../../conexionMysqli.php' ) ){
        die( "No se incluyo archivo de conexion : '../../../../../../conexionMysqli.php'" );
    }
	echo "<input type=\"hidden\" id=\"add_new_row\" value=\"{$perm['nuevo']}\">";
    $sql = "SELECT
                epsv.id_exclusion_productos_surtimiento_venta AS id,
                epsv.id_producto AS product_id,
                p.orden_lista AS list_order,
                p.nombre AS product_name,
                epsv.fecha_alta AS date_add
            FROM ec_exclusion_productos_surtimiento_venta epsv
            LEFT JOIN ec_productos p
            ON p.id_productos = epsv.id_producto
            WHERE epsv.id_producto IS NOT NULL";
	$eje = $link->query( $sql )or die("Error al consultar los productos excluidos : {$sql} : {$link->error}");
?>
<style>
    #res_busc{
        position : relative;
        width : 50%;
        height : 200px;
        box-shadow : 1px 1px 15px rgba( 0,0,0, .5 );
        overflow : auto;
        display : none;
    }
</style>
<!--script type="text/javascript" src="./ajax/mantenimiento_sistema/js/functions.js"></script-->
        <div class="input-group">
            <input type="text" id="busc" onkeyup="busca(event);">
            <button
                type="button"
                class="btn btn-success"
                onclick="busca( 'intro' )"
            >
                <i class="icon-search"></i>
            </button>
        </div>
        <div id="res_busc"></div>
	<!--contenido-->
		<div id="contenido">
		    <p><b>Listado de Productos Excluidos de Surtimiento</b></p>
			<table class="table">
				<tr>
					<th class="text-center">Orden Lista</th>
					<th class="text-center">Producto</th>
					<th class="text-center">Fecha</th>
					<th class="text-center">Eliminar</th>
					<?php
						echo ($perm['eliminar'] == 1 ? '<th width="15%">Quitar</th>' : '');
					?>
				</tr>
				<tr>
					<td colspan="6">
						<div id="cont_tabla">
							<?php
								echo '<table width="100%" id="tabla_exclusion">';
									$c=0;//inicializamos el contador en 0
									while($r = $eje->fetch_assoc() ){
										$c++;//incrementamos contador
									//asignamos el color
										if($c%2==0){
											$color="#E6E8AB";
										}else{
											$color="#BAD8E6";
										}
										echo "<tr id=\"fila_{$c}\" style=\"background:{$color};\" tabindex=\"{$c}\" onclick=\"resalta_fila( {$c} );\">
										    <td style=\"display:none;\" id=\"0_{$c}\">{$r['id']}</td>
											<td style=\"display:none;\" id=\"1_{$c}\">{$r['product_id']}</td>
											<td width=\"15%\" id=\"2_{$c}\" class=\"text-start\">{$r['list_order']}</td>
										    <td width=\"25%\" id=\"3_{$c}\" class=\"text-start\">{$r['product_name']}</td>
										    <td width=\"5%\" id=\"6_{$c}\">{$r['date_add']}</td>
											<td width=\"14%\" class=\"text-center\">
                                                <a href=\"javascript:elimina({$c});\">
                                                    <i class=\"icon-trash\"></i>
                                                </a></td>
									    </tr>";
									}
								echo '</table>'; 	
							?>
						</div>
					</td> 
				</tr>
			</table>
			<input type="hidden" id="filasTotales" value="<?php echo $c;?>">
		</div>
<script>


//cerrar emergente
	function emergente_close(){
		$( '.emergente' ).css('display', 'none');
	}
//mostrar movimientos de almacen
	function view_movs_types( type ){
		$.ajax({
			type : 'post',
			url : 'helper.php',
			data : { types_movs : type  },
			success : function ( dat ){
				$(".emergente_content").html( dat );
				$( '.emergente' ).css('display', 'block');
			}
		});
	}

/*functionamiento de teclas del grid*/
	var fila_resaltada=0;
	function valida_tca(e,num){
		var tca=e.keyCode;

		if(tca==38){//tecla arriba
			if(num==1){
				$("#busc").focus();
				return true;
			}
			$("#fila_"+parseInt(num-1)).focus();
			$("#4_"+parseInt(num-1)).click();
			return true;
		}
		
		if(tca==40||tca==13){//si es tecla abajo o intro
			if(tca==$("#filasTotales").val()){
				return false;
			}
			$("#fila_"+parseInt(num+1)).focus();
			$("#4_"+parseInt(num+1)).click();
			return true;
		}
	}

	function resalta_fila(num){
		if(fila_resaltada!=0){
			var color='';
			if(num%2==0){
				color="#E6E8AB";
			}else{
				color="#BAD8E6";
			}
			$("#fila_"+fila_resaltada).css("background",color);
		}
	//asignamos la nueva fila resaltada
		fila_resaltada=num;
		$("#fila_"+fila_resaltada).css("background","rgba(0,225,0,.6)");
	}


/*funcionamiento de teclas en buscador*/
	var res_seleccionado=0;
	function valida_mov_resultados(e,num,id_pr){
		var tca=e.keyCode;
		if(tca==38){//tecla arriba
			if(num==1){
				$("#busc").select();return true;
			}
			enfoca_resultado(parseInt(num-1));
			return true;
		}
		if(tca==40){//tecla abajo
			enfoca_resultado(parseInt(num+1));
			return true;
		}
		if(tca==13){//tecla intro
			validaProducto(id_pr);
			return true;
		}
	}

	function enfoca_resultado(num){
		if(res_seleccionado!=0){
		//regresamos el color blanco
			$("#resultado_"+res_seleccionado).css("background","white");
		}
		res_seleccionado=num;
	//resaltamos el sigueinte resultado
		$("#resultado_"+res_seleccionado).css("background","rgba(0,225,0,.5)");
		$("#resultado_"+res_seleccionado).focus();
	}

/*funcion para quitar producto de exclusión*/
	function elimina( num ){
		if(!confirm("Realmente deseas quitar este producto de la exclusión de surtimiento?")){
			return false;
		}
		var row_id = $( `#0_${num}` ).html();
        var product = $( `#1_${num}` ).html().trim();
	//enviamos datos por ajax
		$.ajax({
			type : 'post',
			url : 'ajax/exclusion_surtimiento/db.php',
			cache : false,
			data : { exclusionFl : 'deleteProductExclusion', exclusion_id : row_id, product_id : product },
			success:function(dat){
				var aux = dat.split( "|" );
                if( aux[0].trim() != 'ok' ){
					alert("Error al eliminar el registro de exclusión : " + dat);
				}else{
                    alert( "Producto sacado de la exclusion de surtimiento exitosamente." );
                    getStoresLocationsExcluded( false );
				}
			}
		});
	}

	function busca(e){
		var tca=e.keyCode;
		if( tca == 40 || e == 'intro' ){
			if(document.getElementById("resultado_1")){//document.getElementById("#resultado_1")
				enfoca_resultado(1);
			}else{
				$("#fila_1").focus();
				$("#4_1").click();
			}
			return true;
		}
	//validamos el texto
		var texto=$("#busc").val();
		if(texto.length<=2){
			$("#res_busc").html();
			$("#res_busc").css("display","none");
			return true;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exclusion_surtimiento/db.php',
			cache:false,
			data:{exclusionFl:'seek',clave:texto},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error al buscar productos!!!\n"+dat);
				}else{
					$("#res_busc").html(ax[1]);
					$("#res_busc").css("display","block");
				}
			}
		});
	}

	function validaProducto(id_pr){
		var tam=$("#filasTotales").val();
	//recorremos la tabla en busqueda de ¿l producto
		for(var i=1;i<=tam;i++){
			if($("#fila_"+i)){
				if($("#1_"+i).html()==id_pr){
					$("#fila_"+i).focus();//enfocamos la fila
					$("#4_"+i).click();//damos click en el elemento
					$("#busc").val('');//limpiamos la búsqeda
					$("#res_busc").html('');//limpiamos los resultados de búsqueda
					$("#res_busc").css("display","none");//ocultamos resultados de búsqueda
                    alert( "El producto ya esta excluido del surtimiento." );
                    setTimeout( function(){
                        resalta_fila( i );
                        $( "#fila_" + i ).focus();
                    }, 100 );
					return true;
				}
			}
		}
		if ( 1 == 1 ) {// $( '#add_new_row' ).val().trim()
			if(confirm("Este producto no esta excluido, desea agregarlo a la exclusión?")){
				$("#busc").val('');//limpiamos la búsqeda
				$("#res_busc").html('');//limpiamos los resultados de búsqueda
				$("#res_busc").css("display","none");//ocultamos resultados de búsqueda
				agregaFila(id_pr);
			}
		}else{
			alert( "Producto no encontrado en las Exclusiones" );
			$( '#busc' ).select();
		}
	}

	function agregaFila(id_pr){
		var cont_nvo=parseInt($("#filasTotales").val())+1;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exclusion_surtimiento/db.php',
			cache:false,
			data:{ exclusionFl : 'insertProductExclusion', product_id : id_pr },//, contador:cont_nvo
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error al excluir producto!!!\n"+dat);
				}else{
                    alert( "Producto excluido del surtimiento exitosamente" );
                    getStoresLocationsExcluded( false );
				}
			}
		});
	}

var auxiliar='',ocupado=0;
	function edita_celda(num){
		if(ocupado!=0){
			return false;
		}
	//obtenemos el dato anterior
		auxiliar=$("#4_"+num).html();//sacamos el valor del registro
		var cda_tmp='<input type="text" id="celda_tmp" value="'+auxiliar+'" style="width:99%;height:35px;" onkeyup="valida_tca(event,'+num+');" ';
		cda_tmp+='onblur="desedita_celda('+num+');">';
		$("#4_"+num).html(cda_tmp);
		$("#celda_tmp").select();
		ocupado=1;
	}
	function desedita_celda(num){
		var nvo_val=$("#celda_tmp").val();
		if(nvo_val!=auxiliar){
			var id_reg=$("#0_"+num).html();
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'db.php',
				cache:false,
				data:{fl:'modifica',id:id_reg,dato:nvo_val},
				success:function(dat){
					var ax=dat.split("|");
		
					if(ax[0]!='ok'){
						alert("Error al modificar la observación!!!\n"+dat);
					}else{
					}
				}
			});
		}
		$("#4_"+num).html(nvo_val);
		//setTimeout(,500);
		ocupado=0;
	}
/*implementacion Oscar 2021 para exportar a Excel*/
	function exportarExcel(){
		var data = "Id Producto,Orden de lista,Producto,Inv Matriz,Observaciones,Fecha\n";
		var table_length = $( '#filasTotales' ).val();
		if( table_length <= 0 ){
			alert( "No Hay datos para exportar!" );
			return true;
		}
	//obtener datos
		for (var i = 1; i <= table_length; i++) {
			data += $( '#1_' + i ).html().trim() + ",";
			data += $( '#2_' + i ).html().trim() + ",";
			data += $( '#3_' + i ).html().trim() + ",";
			data += $( '#6_' + i ).html().trim() + ",";
			data += $( '#4_' + i ).html().trim() + ",";
			data += $( '#5_' + i ).html().trim().split('<br>').join(' ');
			data += ( i < table_length ? "\n" : "" );
		}

		$("#datos").val('');
		$("#datos").val(data);
		ventana_abierta=window.open('', 'TheWindow');	
		document.getElementById('TheForm').submit();
		setTimeout(cierra_pestana,5000);			
	}

	function cierra_pestana(){
		ventana_abierta.close();//cerramos la ventana
	}
	
/*Fin de cambio Oscar 2021*/


</script>