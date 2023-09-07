<!DOCTYPE html>
<html>
<head>
	<title>Mantenimiento de la BD</title>
<!-- 1. Incluye hoja de estilos CSS, librerias de Calendario y archivo /js/jquery-1.10.2.min.js -->

<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="../../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
</head>
<!-- 2. Estilos CSS -->
<style type="text/css">
	th{background: rgba(225,0,0,.5);color: white;padding: 10px;}
	td{padding: 5px;}
	.numero{position:relative;padding: 5px;width: 100%;font-size: 20px;text-align: right; margin: 0;}
	.agrupar_btn{padding: 3px !important;}
	.editar_btn{padding: 3px !important;}
	#emergente{position: absolute;z-index:10;width: 100%;height: 100%;top:0;left: 0;background: rgba(0,0,0,.6);display: none;}
	#contenido_emergente{position: absolute;width: 70%;height:70%;top:15%;left: 15%;border: 1px solid white;background: rgba(0,0,0,.3);border-radius: 50px;color: white;}
	.desc_inst{
		color: red;
	}
	#global{
		position: absolute;
		max-height: 90%;
		overflow: auto;
	}
	.objeto_1, .objeto{
		display: inline-block;
		width: 100%;
		max-width: 100%;
		margin: 0;
		padding: 0;
		position: relative;
		margin : 0;
		padding: 0;
	}
	.objeto_1{
		width: 100%;
		max-width: 100%;
	}
	.circular{
		border-radius: 50%;
	}
	.descripciones{
		color:blue;
		padding-top: 20px;
	}
	.footer{
		position : absolute; 
		display : block; 
		top : 94%; 
		width : 100%; 
		background: green; 
		text-align:center;
		padding: 5px;
	}
	/*.button>div, .button, tr.headrow{
		padding: 0px !important;
		margin: 0px !important;
		color: red;
	}*/
</style>
<body background="../../../../img/img_casadelasluces/bg8.jpg">
<?php
//3. Incluye archivo /conectMin.php
	include('../../../../conectMin.php');
//4. Consulta configuracion de agrupacion
	$sql="SELECT 
			minimo_agrupar_ma_dia,/*0*/
			minimo_agrupar_ma_ano,/*1*/
			minimo_agrupar_ma_anteriores,/*2*/
			minimo_agrupar_vtas_dias,/*3*/
			minimo_agrupar_vtas_ano,/*4*/	
			minimo_agrupar_vtas_anteriores,/*5*/
			minimo_eliminar_reg_no_usados,/*6*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_ma_dia) day) as fecha_1,/*7*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_ma_ano) day) as fecha_2,/*8*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_ma_anteriores) day) as fecha_3,/*9*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_vtas_dias) day) as fecha_4,/*10*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_vtas_ano) day) as fecha_5,/*11*/
			DATE_ADD(current_date,INTERVAL -(minimo_agrupar_vtas_anteriores) day) as fecha_6,/*12*/
			DATE_ADD(current_date,INTERVAL -(minimo_eliminar_reg_no_usados) day) as fecha_7,/*13*/
			minimo_eliminar_reg_sin_inventario,/*14*/
			DATE_ADD(current_date,INTERVAL -(minimo_eliminar_reg_sin_inventario) day) as fecha_8/*15*/
		FROM sys_configuracion_sistema
		WHERE id_configuracion_sistema=1";
	$eje=mysql_query($sql)or die("Error al consultar parámetros de Agrupación!!!<br>".mysql_error());
	$r=mysql_fetch_row($eje);
?>
<!-- 5. Ventana emergente -->
<div id="emergente">
	<p align="center" id="contenido_emergente"></p>
</div>
<!-- 6. Opciones de agrupamiento -->
	<div id="global">
		<table style="width:48%;float:left;">
			<tr>
				<th colspan="3" align="center" style="text-align : center;"><b>Agrupaciones de Movimientos de Almacén</b></th>
			</tr>
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Movimientos de Almacen por día</td>
			</tr>
	
	<!-- agrupacion de movimientos de almacen por día -->
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input type="text" 
							class="numero form-control" 
							id="calendario_por_dia" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_dia');" value="<?php echo $r[7];?>" 
							disabled
						/>
					</i>
					<i class="objeto">
						<button 
							class="btn btn-primary editar_btn form-control"
							id="editar_dia" 
							onclick="habilitar_campo('calendario_por_dia',1,'editar_dia',1,'por_dia');" 
						>Editar
						</button>
					</i>
				</td>
				<td width="40%">
					<input 
						type="number form-control" 
						id="por_dia" 
						class="numero form-control" 
						value="<?php echo $r[0];?>" 
						onclick="habilitar_campo('por_dia',1);" 
						disabled
					/> 
					<button 
						id="btn_1" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('por_dia',2);"
					>Agrupar por día
					</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,1)"
					>?</button>
				</td>
			</tr>
		
		<!-- agrupacion de movimientos de almacen por año -->
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Movimientos de Almacen por año</td>
			</tr>
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input 
							type="text" 
							class="numero form-control" 
							id="calendario_por_ano" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_ano');" 
							value="<?php echo $r[8];?>" 
							disabled
						/>
					</i>
					<i class="objeto">
						<button  
							id="editar_ano" 
							onclick="habilitar_campo('calendario_por_ano',1,'editar_ano',2,'por_ano');" 
							class="btn btn-primary editar_btn form-control"
						>Editar
						</button>
					</i>
				</td>
				<td width="40%">
					<input 
						type="number" 
						id="por_ano" 
						class="numero form-control" 
						value="<?php echo $r[1];?>"  
						disabled="true"
					/> 
					<button 
						id="btn_2" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('por_ano',3);"
					>Agrupar por Año
					</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,2)"
					>?</button>
				</td>
			</tr>

		<!-- agrupacion de movimientos de almacen por todos los anteriores -->
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Movimientos de Almacen por Anteriores</td>
			</tr>
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input 
							type="text" 
							class="numero form-control" 
							id="calendario_por_anteriores" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_anteriores');" 
							value="<?php echo $r[9];?>" 
							disabled
						/>
					</i>

					<i class="objeto">
						<button  
							id="editar_anteriores" 
							onclick="habilitar_campo('calendario_por_anteriores',1,'editar_anteriores',3,'por_anteriores');" 
							class="btn btn-primary editar_btn form-control"
						>Editar
						</button>
					</i>

				</td>
				<td>
					<input 
						type="number" 
						id="por_anteriores" 
						class="numero form-control" 
						value="<?php echo $r[2];?>" 
						onclick="habilitar_campo('por_anteriores',1);" 
						disabled
					/> 
					<button 
						id="btn_3" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('por_anteriores',4);"
					>Agrupar por Anteriores</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,3)"
					>?</button>
				</td>
			</tr>
			<tr>
				<th colspan="1" style="background: transparent; padding-top:70px;">
					<button class="btn btn-warning form-control" onclick="insertaProcedures('procedures_inserta');">
						Obtener procedures
					</button>
				</th>
	<!-- Implementacion Oscar 20-09-2020 boton para recalcular inventarios en almacen producto-->	
				<th colspan="2" style="background: transparent; padding-top:70px;">
					<button class="btn btn-warning form-control" onclick="insertaProcedures('recalcula_inventario_almacen');">
						Recalcular inventario por almacen
					</button>
				</hd>
			</tr>
		</table>

<!--Mantenimiento de la BD-->
		<table style="width:48%;float:right;">
			<tr>
				<th colspan="3"  style="text-align : center;"><b>Agrupaciones de Ventas/devoluciones</b></th>
			</tr>
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Ventas/Devoluciones por día</td>
			</tr>
	<!-- agrupacion de ventas / devoluciones por día -->
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input 
							type="text" 
							class="numero form-control"
							id="calendario_por_dia_vta" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_dia_vta');" 
							value="<?php echo $r[10];?>" disabled
						/>
					</i>
					<i class="objeto">
						<button 
							id="editar_dia_vta" 
							onclick="habilitar_campo('calendario_por_dia_vta',1,'editar_dia_vta',4,'por_dia_vta');" 
							class="btn btn-primary editar_btn form-control"
						>Editar</button>
					</i>
				</td>
				<td>
					<input 
						type="number" 
						id="por_dia_vta" 
						class="numero form-control" 
						value="<?php echo $r[3];?>" 
						onclick="habilitar_campo('por_dia_vta',1);" 
						disabled
					/> 
					<button id="btn_4" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('por_dia_vta',2,'vta');"
					>Agrupar por día
					</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,1)"
					>?</button>
				</td>
			</tr>
	<!-- agrupacion de ventas / devoluciones por año -->
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Ventas/Devoluciones por año</td>
			</tr>
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input 
							type="text" 
							class="numero form-control" 
							id="calendario_por_ano_vta" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_ano_vta');" 
							value="<?php echo $r[11];?>" 
							disabled
						/>
					</i>
					<i class="objeto">
						<button 
							id="editar_ano_vta" 
							onclick="habilitar_campo('calendario_por_ano_vta',1,'editar_ano_vta',5,'por_ano_vta');" 
							class="btn btn-primary editar_btn form-control"
						>Editar
						</button>
					</i>
				</td>
				<td width="40%">
					<i class="objeto">
						<input 
							type="number" 
							id="por_ano_vta" 
							class="numero form-control" 
							value="<?php echo $r[4];?>"  
							disabled="true"
						/> 
					</i>
					<i class="objeto">
						<button 
							id="btn_5" 
							class="agrupar_btn btn btn-success form-control" 
							onclick="llamar_procedure('por_ano_vta',3,'vta');"
						>Agrupar por Año
						</button>
					</i>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,2)"
					>?</button>
				</td>
			</tr>
	<!-- agrupacion de ventas / devoluciones por anteriores -->
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Ventas/Devoluciones por Anteriores</td>
			</tr>
			<tr>
				<td align="left" width="50%">
					<i class="objeto">
						<input 
							type="text" 
							class="numero form-control" 
							id="calendario_por_anteriores_vta" 
							onfocus="calendario(this);" 
							onchange="cambiar_numero_dias(this,'por_anteriores_vta');" 
							value="<?php echo $r[12];?>" 
							disabled
						/>
					</i>
					<i class="objeto">
						<button  
							id="editar_anteriores_vta" 
							onclick="habilitar_campo('calendario_por_anteriores_vta',1,'editar_anteriores_vta',6,'por_anteriores_vta');" 
							class="btn btn-primary editar_btn form-control"
						>Editar
						</button>
					</i>
				</td>
				<td>
					<input 
						type="number" 
						id="por_anteriores_vta" 
						class="numero" 
						value="<?php echo $r[5];?>" 
						onclick="habilitar_campo('por_anteriores_vta',1);" 
						disabled
					/> 
					<button 
						id="btn_6" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('por_anteriores_vta',4,'vta');"
					>Agrupar por Anteriores
					</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(1,3)"
					>?</button>
				</td>
			</tr>

			<tr>
				<th colspan="3"  style="text-align : center;"><b>Eliminar Registros no necesarios / alertas</b></th>
			</tr>
			
			<tr>
				<td colspan="3" align="center" class="descripciones">Mínimo de días para eliminar registros sin uso</td>
			</tr>
			<tr>
				<td align="left" width="40%">
					<input 
						type="text" 
						class="numero form-control"
						id="calendario_eliminar_sin_uso" 
						onfocus="calendario(this);" 
						onchange="cambiar_numero_dias(this,'eliminar_sin_uso');" 
						value="<?php echo $r[13];?>" 
						disabled
					/>
					<button  
						id="editar_eliminar_sin_uso" 
						onclick="habilitar_campo('calendario_eliminar_sin_uso',1,'editar_eliminar_sin_uso',7,'eliminar_sin_uso');" 
						class="btn btn-primary editar_btn form-control"
					>Editar</button>
				</td>				
				<td>
					<input 
						type="number" 
						id="eliminar_sin_uso" 
						class="numero form-control" 
						value="<?php echo $r[6];?>" 
						onclick="habilitar_campo('eliminar_sin_uso',1);" 
						disabled
					/> 
					<button 
						id="btn_7" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('eliminar_sin_uso',5,'vta');"
					>Eliminar Registros</button>
				</td>
				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(2)"
					>?</button>
				</td>
			</tr>

		<!-- implementación Oscar 2021 para botón de eliminar registro de productos sin inventario en ventas -->			
			<tr>
				<td colspan="2" align="center" class="descripciones">Mínimo de días para eliminar alertas de inventarios insuficientes</td>
			</tr>
			<tr>
				<td align="left" width="40%">
					<input 
						type="text" 
						class="numero form-control" 
						id="calendario_eliminar_alertas_inventario" 
						onfocus="calendario(this);" 
						onchange="cambiar_numero_dias(this,'eliminar_alertas_inventario');" 
						value="<?php echo $r[15];?>" disabled>
					<button  
						id="editar_eliminar_alertas_inventario" 
						onclick="habilitar_campo('calendario_eliminar_alertas_inventario',1,'editar_eliminar_alertas_inventario',8,'eliminar_alertas_inventario');" 
						class="btn btn-primary editar_btn form-control">
						Editar
					</button>
				</td>				
				<td>
					<input 
						type="number" 
						id="eliminar_alertas_inventario" 
						class="numero form-control" 
						value="<?php echo $r[14];?>" 
						onclick="habilitar_campo('eliminar_alertas_inventario',1);" 
						disabled
					/> 
					<button 
						id="btn_8" 
						class="agrupar_btn btn btn-success form-control" 
						onclick="llamar_procedure('eliminar_alertas_inventario',8,'vta');"
					>Eliminar Alertas
					</button>
				</td>

				<td width="10%">
					<button 
						class="btn btn-secondary circular" 
						title="Click para obtener información"
						onclick="activar_instrucciones(3)"
					>?</button>
				</td>
			</tr>
		<!-- Fin de cambio Oscar 2021-->
		</table>
	</div>
<!-- implementacion Oscar 2021 -->
	<!-- Modal -->
		<?php 
			require_once('instrucciones.php');
		?>
<!-- Fin de cambio Oscar 2021 -->

	<div class="footer">
			<button class="btn btn-light" onclick="back_index();">
				Panel principal
			</button>
	</div>
<!-- -->
</body>
</html>
<!-- 7. Funciones JavaScript -->			
<script type="text/javascript">
/*7.1.1 implementación Oscar 20201 ara lanzar el modal en automático*/
	$(document).ready(function(){
		$('#lanza_modal').click();
	});
/*fin de cambio*/
//7.1. Funcion para cambiar el numero de dias por medio del archivo bd_sql.php
	function cambiar_numero_dias(obj,flag){
	//obtenemos el valor del objeto
		var fecha_tmp=$(obj).val();
		if(fecha_tmp.length<10){
			alert("Ingrese una fecha correcta");
			$(obj).select();
			return false;
		}
	//enviamos los datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:'obtener_dias',fecha:fecha_tmp},
			success:function(dat){
				$("#"+flag).val(dat);
			}
		});
	}

//7.2. Funcion para habilitar / deshabilitar edicion de dias
	function habilitar_campo(id_campo,flag,id_boton,btn_guarda,campo_cambia){
		//alert(campo_cambia);
		if(flag==1){
			$("#"+id_campo).removeAttr('disabled');
			$("#"+id_campo).select();
		//cambiamos el botón
			$("#"+id_boton).attr('onclick','cambiar_parametro(\''+campo_cambia+'\',0,\''+id_boton+'\','+btn_guarda+');');
			$("#"+id_boton).html('Aceptar');
			$("#btn_"+btn_guarda).prop('disabled',true);
		}else if(flag==0){ 
			//document.getElementById(id_campo).disabled=true;
			$("#"+id_campo).prop('disabled',true);
			$("#"+id_boton).attr('onclick','habilitar_campo(\''+id_campo+'\',1,\''+id_boton+'\','+btn_guarda+');');
			$("#"+id_boton).html('Editar');
			$("#btn_"+btn_guarda).removeAttr('disabled');
		}
	}

//7.3. Funcion para cambiar parametro por medio del archivo bd_sql.php
	function cambiar_parametro(id_campo,flag,id_boton,btn_guarda){
	//extraemos el nuevo dato
		var dato_nvo=$("#"+id_campo).val();
		if(dato_nvo<=0){
			alert("Este campo tiene que ser positivo!!!");
			$("#"+id_campo).select();
			return false;
		}

	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:id_campo,valor:dato_nvo},
			success:function(dat){
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Error!!!\n'+dat);return false;
				}
				habilitar_campo(id_campo,0,id_boton,btn_guarda);				
			}
		});
	}

//7.4. Funcion para mandar llamar procedure por medio del archivo bd_sql.php
	function llamar_procedure(id_campo,flag,subtipo){
		var cont_emerge='<b style="font-size:50px;">Procesando...</b>';
		cont_emerge+='<br><br><img src="../../../../img/img_casadelasluces/load.gif">';
		var dato_nvo=$("#"+id_campo).val();
		$("#contenido_emergente").html(cont_emerge);
		$("#emergente").css("display","block");
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag,tipo:subtipo},
			success:function(dat){
			//lanzamos emergente
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Error!!!\n'+dat);return false;
				}
				alert("Proceso realizado exitosamente");
				location.reload();			
			}
		});

	}

//7.5. Funcion de Calendario
	function calendario(objeto){
    	Calendar.setup({
        	inputField     :    objeto.id,
        	ifFormat       :    "%Y-%m-%d",
        	align          :    "BR",
        	singleClick    :    true
		});
	}

//7.6. Funcion para instalar procedures
	function insertaProcedures(fl){
		var cont_emerge='<b style="font-size:50px;">Procesando...</b>';
		cont_emerge+='<br><br><img src="../../../../img/img_casadelasluces/load.gif">';
		$("#contenido_emergente").html(cont_emerge);
		$("#emergente").css("display","block");

		var confirmacion = "";
		if( fl == 'procedures_inserta'){ 
			confirmacion = "Procedures insertados/actualizados exitosamente"; }
		if( fl == 'recalcula_inventario_almacen' ){ 
			confirmacion = "Inventarios de almacenes por productos recalculados exitosamente"; }
		if(! confirm( "Desea continuar con esta operación?" )){
			$("#contenido_emergente").html('');
			$("#emergente").css("display","none");
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'bd_sql.php',
			cache:false,
			data:{flag : fl},
			success:function(dat){
			//lanzamos emergente
				var aux=dat.split('|');
				if(aux[0]!='ok'){
					alert('Error!!!\n'+dat);return false;
				}
				alert(confirmacion);
				location.reload();			
			}
		});
	}
/*fin de cambio Oscar 20.12.2019*/

/*implementacion Oscar 2021 para instrucciones de uso y regreso al index*/
	function back_index(){
		if( !confirm('Realmente desea salir?')){
			return false;
		}else{
			location.href="../../../../index.php?";
		}
	}

	function activar_instrucciones( num, num2 = null ){
	/*oculta todas las intrucciones*/
		$('#instrucciones_agrupacion').css('display', 'none');
		$('#instrucciones_eliminar_sin_uso').css('display', 'none');
		$('#instrucciones_eliminar_sin_inventario').css('display', 'none');
		
		$('#1_1').css('display', 'none');
		$('#1_2').css('display', 'none');
		$('#1_3').css('display', 'none');
		
		num == 1 ?$('#instrucciones_agrupacion').css('display', 'block') : null;
		num == 2 ?$('#instrucciones_eliminar_sin_uso').css('display', 'block') : null;
		num == 3 ?$('#instrucciones_eliminar_sin_inventario').css('display', 'block') : null;
		
		num2 != null ? $( '#1_' + num2 ).css('display', 'block') : null;

		$('#lanza_modal').click();
	}

/*fin de cambio Oscar 2021*/
</script>