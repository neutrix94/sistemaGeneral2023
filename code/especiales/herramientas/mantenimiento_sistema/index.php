<!DOCTYPE html>
<html>
<head>
	<title>Mantenimiento de la BD</title>
<!-- 1. Incluye hoja de estilos CSS, librerias de Calendario y archivo /js/jquery-1.10.2.min.js -->


<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="../../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../../js/calendar-setup.js"></script>
<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css"/>

<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Funciones JavaScript -->			
<script type="text/javascript" src="js/functions.js"></script>
</head>
<!-- 2. Estilos CSS -->
<style type="text/css">
	/*th{background: rgba(225,0,0,.5);color: white;padding: 10px;}*/
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
		width: 100%;
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
	.header{
		padding:10px;
		background-color : green;
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
	*{
		font-size : 98% ;
	}
	.no_visible{
		display: none;
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
	include('../../../../conexionMysqli.php');

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
		<div class="header">
			<h3 class="text-center text-light">Mantenimiento de la Base de Datos</h3>
		</div>
		<div class="accordion" id="accordionExample">
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_1">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_1"
			    	aria-expanded="true" aria-controls="collapse_1_1" id="herramienta_1_1" class="opc_btn">
			        	1. Agrupaciones de Movimientos de Almacén
			      	</button>
		    	</h2>
		    	<div id="collapse_1_1" class="accordion-collapse collapse description" aria-labelledby="heading_1_1" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<table class="table text-center">
							<tr class="">
								<th colspan="3" align="center" style="text-align : center;"><b>Agrupaciones de Movimientos de Almacén</b></th>
							</tr>
							<tr class="">
								<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Movimientos de Almacen por día</td>
							</tr>
					<!-- agrupacion de movimientos de almacen por día -->
							<tr class="">
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
							<tr class="">
								<td colspan="3" align="center" class="text-primary">Mínimo de días para grupar Movimientos de Almacen por año</td>
							</tr>
							<tr class="">
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
							<tr class="">
								<td colspan="3" align="center" class="descripciones">Mínimo de días para grupar Movimientos de Almacen por Anteriores</td>
							</tr>
							<tr class="">
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
			    		</table>
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_2">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_2"
			    	aria-expanded="true" aria-controls="collapse_1_2" id="herramienta_1_2" class="opc_btn">
			        	2. Agrupaciones de Ventas/devoluciones
			      	</button>
		    	</h2>
		    	<div id="collapse_1_2" class="accordion-collapse collapse description" aria-labelledby="heading_1_2" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<table class="table text-center">
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
			    		</table>
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_3">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_3"
			    	aria-expanded="true" aria-controls="collapse_1_3" id="herramienta_1_3" class="opc_btn">
			        	3. Depuracion de Registros
			      	</button>
		    	</h2>
		    	<div id="collapse_1_3" class="accordion-collapse collapse description" aria-labelledby="heading_1_3" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<table class="table">
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
							<tr>
								<td colspan="3">
									<div class="row">
										<div class="col-4"></div>
										<div class="col-4">
											<label class="text-primary">Minimo de piezas para eliminar</label>
											<input type="number" id="alerts_min_number" class="form-control">
											<button class="btn btn-warning form-control" onclick="delete_alerts( );">
												<i class="icon-alert">Eliminar alertas de inventarios erroneas</i>
											</button>
										</div>
									</div>
								</td>
							</tr>
			    		</table>
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_4">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_4"
			    	aria-expanded="true" aria-controls="collapse_1_4" id="herramienta_1_4" class="opc_btn">
			        	4. Procedures / Triggers / Versionador MySQL
			      	</button>
		    	</h2>
		    	<div id="collapse_1_4" class="accordion-collapse collapse description" aria-labelledby="heading_1_4" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<div class="row">
			    			<div class="col-4">
								<button class="btn btn-warning form-control" onclick="insertaProcedures('procedures_inserta');">
									Obtener procedures
								</button>
			    			</div>
			    			<div class="col-4">
								<button class="btn btn-danger form-control" onclick="insertaProcedures('triggers_movimientos');">
									Reinsertar Triggers de inventario
								</button>
			    			</div>
			    			<div class="col-4">
								<button class="btn btn-info form-control" onclick="insertaProcedures('triggers_sistema');">
									Reinsertar Triggers del sistema
								</button>
			    			</div>
			    			<div class="col-4">
			    				<br>
								<button class="btn btn-info form-control" onclick="insertaProcedures('triggers_transferencias');">
									Reinsertar Triggers de transferencias
								</button>
			    			</div>
			    			<div class="col-4">
			    				<br>
								<button class="btn btn-danger form-control" onclick="insertaProcedures('update_scripts');">
									Insertar cambios en Scripts
								</button>
			    			</div>

			    		</div>
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_5">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_5"
			    	aria-expanded="true" aria-controls="collapse_1_5" id="herramienta_1_5" class="opc_btn">
			        	5. Sincronizacion / Restauracion 
			      	</button>
		    	</h2>
		    	<div id="collapse_1_5" class="accordion-collapse collapse description" aria-labelledby="heading_1_5" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
				    	<div class="row">
				    		<div class="col-4">
				    			<?php
									$sql = "SELECT bloquear_apis_sincronizacion AS api_locked FROM sys_configuracion_sistema WHERE id_configuracion_sistema = 1";
									$stm = $link->query( $sql ) or die( "Error al consultar si las apis estan bloqueadas : {$link->error}" );
									$api_locked = $stm->fetch_assoc();
									if( $api_locked['api_locked'] == 0 ){
								?>
									<button class="btn btn-danger form-control" onclick="insertaProcedures('pause_sinchronization_apis');">
										<i class="icon-pause">Pausar sincronizacion de apis</i>
									</button>
								<?php
									}else{
								?>
									<button class="btn btn-success form-control" onclick="insertaProcedures('renew_sinchronization_apis');">
										<i class="icon-play">Reanudar sincronizacion de apis</i>
									</button>
								<?php
									}
								?>
				    		</div>
				    		<div class="col-4">
								<button class="btn btn-primary form-control" onclick="insertaProcedures('restoration_mode');">
									<i class="icon-arrows-ccw">Generar Folio de Restauracion</i>
								</button>
				    		</div>
				    		<div class="col-4">
								<?php
									$sql = "SELECT IF( permite_sincronizar_manualmente != 1, 'locked', 'unlocked' ) AS permite_sincronizar FROM sys_resumen_sincronizacion_sucursales WHERE id_sucursal = {$user_sucursal}";
									$stm = $link->query( $sql ) or die( "Error al consultar si la sincronizacion de la sucursal esta bloqueada : {$link->error}" );
									$api_locked = $stm->fetch_assoc();
									if( $api_locked['permite_sincronizar'] == 'unlocked' ){
								?>
									<button class="btn btn-danger form-control" onclick="insertaProcedures('pause_sinchronization_apis_store');">
										<i class="icon-pause">Pausar sincronizacion de la Sucursal</i>
									</button>
								<?php
									}else{
								?>
									<button class="btn btn-success form-control" onclick="insertaProcedures('renew_sinchronization_apis_store');">
										<i class="icon-play">Reanudar sincronizacion de la Sucursal</i>
									</button>
								<?php
									}
								?>
									
			    			</div>
			    		</div>
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_6">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_6"
			    	aria-expanded="true" aria-controls="collapse_1_6" id="herramienta_1_6" class="opc_btn">
			        	6. Herramientas Adicionales
			      	</button>
		    	</h2>
		    	<div id="collapse_1_6" class="accordion-collapse collapse description" aria-labelledby="heading_1_6" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<div class="row">
			    			<div class="col-4">
								<button class="btn btn-warning form-control" onclick="insertaProcedures('recalcula_inventario_almacen');">
									Recalcular inventario por almacen
								</button>
			    			</div>
			    			<div class="col-4">
								<button class="btn btn-warning form-control" onclick="insertaProcedures('recorre_productos_por_liberar');">
									Resetear productos con Orden de lista 0
								</button>
			    			</div>
			    			<div class="col-4">
								<button class="btn btn-warning form-control" onclick="insertaProcedures('historico_productos');">
									Pasar notas de productos al histórico
								</button>
			    			</div>
			    			<div class="col-4">
			    				<br>
								<button class="btn btn-warning form-control" onclick="insertaProcedures('reinsertar_almacen_producto');">
									Reinsertar almacen productos faltantes
								</button>
			    			</div>
			    		</div>
			    	</div>
		    	</div>
		  	</div>
	<!-- Sucursales -->		
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_7">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_7"
			    	aria-expanded="true" aria-controls="collapse_1_7" id="herramienta_1_7" class="opc_btn">
			        	7. Herramientas de Sucursales
			      	</button>
		    	</h2>
		    	<div id="collapse_1_7" class="accordion-collapse collapse description" aria-labelledby="heading_1_7" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<div class="row">
			    			<div class="col-4">
								<button 
									type="button"
									class="btn btn-warning form-control" onclick="getStoresLocations();">
									Resetear Ubicaciones de Sucursales
								</button>
			    			</div>
			    			<div class="col-4">
			    			</div>
			    			<div class="col-4">
			    			</div>
			    			<div class="col-4">
			    			</div>
			    		</div>
			    	</div>
		    	</div>
		  	</div>
	<!-- Facturacion -->		
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_8">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_8"
			    	aria-expanded="true" aria-controls="collapse_1_8" id="herramienta_1_8" class="opc_btn">
			        	8. Herramientas de Facturación
			      	</button>
		    	</h2>
		    	<div id="collapse_1_8" class="accordion-collapse collapse description" aria-labelledby="heading_1_8" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		<div class="row">
			    			<div class="col-4">
								<button class="btn btn-warning form-control" onclick="insertaProcedures('barrido_general_productos');">
									Actualizar Productos con barrido General
								</button>
			    			</div>
			    			<div class="col-4">
			    			</div>
			    			<div class="col-4">
			    			</div>
			    			<div class="col-4">
			    			</div>
			    		</div>
			    	</div>
		    	</div>
		  	</div>
	<!-- fin de modulos de facturacion -->
			<!--div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_7">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_7"
			    	aria-expanded="true" aria-controls="collapse_1_7" id="herramienta_1_7" class="opc_btn">
			        texto aqui
			      	</button>
		    	</h2>
		    	<div id="collapse_1_7" class="accordion-collapse collapse description" aria-labelledby="heading_1_7" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		
			    	</div>
		    	</div>
		  	</div>
			<div class="accordion-item">
		    	<h2 class="accordion-header" id="heading_1_8">
			    	<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_1_8"
			    	aria-expanded="true" aria-controls="collapse_1_8" id="herramienta_1_8" class="opc_btn">
			        texto aqui
			      	</button>
		    	</h2>
		    	<div id="collapse_1_8" class="accordion-collapse collapse description" aria-labelledby="heading_1_8" data-bs-parent="#accordionExample">
			    	<div class="accordion-body">
			    		
			    	</div>
		    	</div>
		  	</div-->
		</div>
		<table style="width:48%;float:left;">
			<tr>
				<th colspan="1" style="background: transparent; padding-top:70px;">
				</th>
	<!-- Implementacion Oscar 20-09-2020 boton para recalcular inventarios en almacen producto-->	
				<th colspan="2" style="background: transparent; padding-top:70px;">
				</th>
			</tr>
			<tr>
	<!-- Implementacion Oscar 29-03-2022 boton para liberar productos que tienen orden de lista cero-->	
				<th colspan="1" style="background: transparent; padding-top:20px;">
				</th>
	<!-- Implementacion Oscar 2022 boton para generar historico de precios de productos-->	
				<th colspan="2" style="background: transparent; padding-top:20px;">
				</th>
				<!--th colspan="2" style="background: transparent; padding-top:20px;">
					<button class="btn btn-warning form-control" onclick="insertaProcedures('prefijo_codigos_unicos');">
						Cambiar prefijo de <b>Códigos de Barras Únicos</b>
					</button>
				</th-->
			</tr>
			<tr>
	<!-- Implementacion Oscar 2023 -->	
				<th colspan="1" style="background: transparent; padding-top:20px;">
				</th>
			</tr>
			<tr>
	<!-- Implementacion Oscar 2023 boton para Reinsertar Triggers de inventario -->	
				<th colspan="1" style="background: transparent; padding-top:20px;">
					<br>

				</th>
	<!-- fin de cambio Oscar 2023 -->	
	<!-- Implementacion Oscar 2023	
				<th colspan="1" style="background: transparent; padding-top:20px;">
					<button class="btn btn-warning form-control" onclick="insertaProcedures('reinsertar_almacen_producto');">
						Reinsertar almacen productos faltantes
					</button>
				</th>
	fin de cambio Oscar 2023 -->	
		</table>
		</table>

<!--Mantenimiento de la BD-->
		<table style="width:48%;float:right;" class="">
			

			
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