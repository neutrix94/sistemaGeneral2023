<?php
//1. Incluye archivo %/conectMin.php%%
	require('../../../conectMin.php');
	$muestraStatus=0;
/*implementacion Oscar 2023 para revisar la estacionalidad configurada en la sucursal */
	$sql = "SELECT
				CONCAT( 'La sucursal <b>', s.nombre, '</b> tiene configurada estacionalidad : <b>', e.nombre, '</b>' ) AS store_config,
				( SELECT racionar_transferencias_productos FROM sys_configuracion_sistema LIMIT 1 ) AS ration_config
			FROM sys_sucursales s
			LEFT JOIN ec_estacionalidad e
			ON s.id_estacionalidad = e.id_estacionalidad
			WHERE s.id_sucursal = {$sucursal_id}";
	$stm = mysql_query( $sql ) or die( "Error al consultar configuracion de estacionalidad y racion : " . mysql_error() );
	$row = mysql_fetch_assoc( $stm );
/**/
?>
<title>Transferencia</title>
<!--2. Incluye hojas de estilos CSS-->
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">	
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--link href="css2/fontello.css" rel="stylesheet" type="text/css"  media="all" /-->
<link href="css2/estilo.css" rel="stylesheet" type="text/css"  media="all" />
<link href="../../../css/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"  media="all" />
<link href="../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
<!-- 3. Incluye archivo %jquery-1.10.2.min.js%% -->
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<!-- 4. Funciones JavaScript-->
<script type="text/javascript" src="js/transfFunctions.js"></script>


<div class="emergent">
	<button onclick="close_emergent();" class="emergent_btn_close">X</button>
	<div class="emergent_content">
	</div>
</div>
										<!--Comienza cuerpo de pantalla-->
<!-- 5. Ventana que muestra mensaje cargando-->
<input type="hidden" value="<?php echo $sucursal_id;?>" id="suc_actual">
<div id='cargando' style="width:100%;height:100%;position:absolute; background:rgba(0,0,0,.5);display:none;">
	<center><br><br><br><br><br><br>
		<p style="border: 0px solid;width:60%;padding:30px;background:rgba(0,255,0,.3);"><font color="white" size="5px"><span id="proceso">Generando Transferencia</font></span><br>
		<img src="img/load.gif" witdh="20%" height="20%"><!--montamos imagen-->
		</p>
	</center>
</div>

<!-- 6. Filtros para generar la transferencia-->
<div id="general" style="text-align:center; background-image:url(img/bg8.jpg); overflow:scroll; height:90%;border:2px solid;"><!--DIV GENERAL-->
	<div class="encabezado bg-primary" style="height : 80px; position : sticky; top : 0; vertical-align : middle;"><!--DIV DE ENCABEZADO-->
		<div class="" style="width : 50%; display : flex; float : left;">
			<h2 style="color : white; vertical-align : middle;"><!-- padding:15px;background-color: rgba(225,0,0,.8) !important; -->
				<font size="6px"><i class="icon-shuffle-4"></i></font>
					<font color="white" size="6px"> | Transferencia con Ración</font>
			</h2>
		</div>
	<?php
		echo "<div class=\"bg-warning bg-gradient config_info\" style=\"width : 50%; display : flex; float : right; height : 100%;\">
			<div>{$row['store_config']}</div>";
		echo "<div>La racion esta : <b>" . ( $row['ration_config'] == 1 ? "HABILITADA" : "DESHABILITADA" ) . "</b></div>";
		echo "</div><br>";
	?>
	</div><!--SE CIERRA DIV DE ENCABEZADO-->
	<center>
	
	<div class="combos bg-info-subtle" style="text-align:center; width:100%;"><!-- border: 2px solid;border-radius:15px; -->
		<form id="transferencias">
			<table width="100%" border="0" cellspacing="10px" class="table table-striped bg-info-subtle"><!-- style="background:rgba(0,0,0,.3); color:#000000;border-radius:10px;" -->
				<tr>
	<!-- 6.1. Filtro de Familias (checkbox) -->
					<td colspan="4" width="30%" style="">
					<div style="text-align:center;">
						<font size="5px" color="">
							<input type="checkbox" id='0' value="0" onclick="check(0);" checked>
							Familias:
						</font>
					</div>
					<div class="row" style="position : relative; width: 100%; left : 0px; margin : 1px; padding : 1px;">
						<?php 
							$sql="SELECT id_categoria,nombre FROM ec_categoria where nombre!='' and id_categoria>0";
							$ejecuta=mysql_query($sql);//ejecutamos consulta
							$numCateg=mysql_num_rows($ejecuta);//contamos numero de resultados;
							$cont=0;//inicializamos contador en cero
							while($categ=mysql_fetch_row($ejecuta)){//mientras encuentre resultados
								$cont++;//incrementamos contador
							?>	
							<!--Creamos checkbox-->
								<div style="padding:10px; box-shadow : 1px 1px 5px rgba( 111,0,0,.5 );" class="col-3 family_check_container">
									<input type="checkbox" class="family_check" id="<?php echo 'ch_'.$cont;?>" value="<?php echo $categ[0];?>" 
									onclick="check(<?php echo $cont;?>);" checked style="padding:10px;">
									<label for="<?php echo 'ch_'.$cont;?>"><?php echo $categ[1];?></label>
								</div>
							<?php 
							}//cerramos while
							?>
							<!--div class="col-3" style="padding:5px; box-shadow : 1px 1px 5px rgba( 0,0,0,.5 );">
								<input type="checkbox" id='0' value="0" onclick="check(0);" checked>Marcar/Desmarcar
							</div-->
					</div>
						<!--Asignamos numero de categorias existentes-->
							<input type="hidden" id="numCat" value="<?php echo $numCateg;?>">
					</td>
				</tr>
				<tr>
	<!-- 6.2. Id de transferencia -->
					<td width="10%" align="right" style="padding:10px;">ID</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" disabled value="(Automatico)">
					</td>
	<!-- 6.3. Sucursal Origen -->
					<td width="15%" align="right" style="padding:10px;">Sucursal De Origen</td>
					<td width="30%" align="left">
						<select id="origen" style="" class="form-select" onchange="prueba(1);">
							<?php
							$query="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal>0";
							$extraer=mysql_query($query);
							if($extraer){
							while($row=mysql_fetch_assoc($extraer)){
								//echo 'resultado:<br>';
						?>
								<option value="<?php echo $row['id_sucursal'];?>"><?php echo'<font color="#000099">'.$row['nombre'];?></option>
						<?php
							}//fin de while $extraer
						}else{
							echo 'algo anda mal en la consulta';
						}
						?>
                        <input type="hidden" value="<?php echo $row['id_sucursal'];?>" id="<?php echo $row['id_sucursal'];?>"/>
                        
						</select>
					</td>
				</tr>
				<tr>
	<!-- 6.4. Creada por -->
					<td width="10%" align="right" style="padding:5px;">Creada por</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" value="<?php echo $user_fullname;?>" disabled>
					</td>
	<!-- 6.5. Almacen Origen -->
					<td width="20%" align="right" style="padding:5px;">Almacen De Origen</td>
                    <td width="30%" align="left">
                	<div id="almOrigen"><!--DIV ALMORIGEN-->
                    <select class="form-select" id="id_almacen_origen"><!-- padding:10px; border-radius:10px; -->
						<?php 
							$sql="SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal ='1' order by prioridad";
							$consulta=mysql_query($sql);
							while($almacen=mysql_fetch_row($consulta)){
						?>
								<option value="<?php echo $almacen[0];?>"><?php echo $almacen[1];?></option>
						<?php 
							}//fin de while $consulta
						?>
					</select>
					</div>
				</td>
				</div><!--FINALIZA DIV ALORIGEN-->
                </tr>
				<tr>
	<!-- 6.6. Fecha -->
					<td width="10%" align="right" style="padding:10px;">Fecha</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" disabled value="<?php echo date("Y")."-".date("m")."-".date("d");?>"><br />
						<!--span class="text_legend">yy-mm-dd</span></td-->
	<!-- 6.7. Sucursal Destino -->
                    <td width="20%" align="right" style="padding:10px;">Sucursal de Destino</td>
					<td width="30%" align="left">
						<div id="dest">    
	                        <select class="form-select" id="destino" onclick="obtenerCombo();">
	                    	<?php
							$query="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal='".$sucursal_id."'";
							$extraer=mysql_query($query);
							if(!$extraer){
								die("Error!!!\n".mysql_error()."\n".$query);
							}
								while($row=mysql_fetch_assoc($extraer)){
								//echo 'resultado:<br>';
							?>
									<option value="<?php echo $row['id_sucursal'];?>"><?php echo'<font color="#000099">'.$row['nombre'];?></option>
							<?php
								}
							?>          
							</select>
						</div>
					</td>
				</tr>
				<tr>
	<!-- 6.8. Hora -->
					<td width="10%" align="right" style="padding:10px;">Hora</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" disabled value="<?php echo date("H").":".date("i").":".date("s");?>">
					</td>
	<!-- 6.9. Almacen Destino -->
					<td width="20%" align="right" style="padding:10px;">Almacen De Destino</td>
					<td width="30%" align="left">
                    <div id="almDestino"><!--DIV alacenDestino-->	
	                    	<select class="form-select" id="id_almacen_destino">
								<?php 
									$sql="SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal ='$sucursal_id' order by prioridad";
									$consulta=mysql_query($sql);
									while($almacen=mysql_fetch_row($consulta)){
								?>
										<option value="<?php echo $almacen[0];?>"><?php echo $almacen[1];?></option>
								<?php 
									}
							?>
							</select>
	                    </div>
					</td>
				</tr>
				<tr>
	<!-- 6.10. Folio -->
					<td width="10%" align="right" style="padding:5px;">Folio:</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" disabled value="(Automático)">
					</td>
	<!-- 6.11. Combo para mostrar todos los productos o por stock-->
					<td align="right">
	<!-- 6.12. Filtro por complemento / normal -->
						Filtrar por: 
					</td>
					<td>
						<select id="filtroPor" class="form-select"><!---onchange="ejecutar(1);"-->
							<option value="complemento">Transferencia Diaria</option>
							<option value="s_b">Normal</option>
						</select>
					</td>
				</tr>
				<tr>
	<!-- 6.13. Status -->
					<td width="10%" align="right" style="padding:10px;">Status</td>
					<td width="20%" align="left">
						<input type="text" class="form-control" disabled value="No Autorizado">
					</td>
	<!-- 6.14. Tipo de Calculo -->
					<td width="20%" align="right" style="padding:5px;">Tipo</td>
					<td width="30%" align="left">
						<select class="form-select" id="id_tipo" onchange="ejecutar(2);">
							<option value="0">-------------</option>
                    		<?php $tipo="SELECT * FROM(
								SELECT 1 AS id_tipo, 'Urgente' AS nombre
								UNION
								SELECT 3 AS id_tipo, 'Medio' AS nombre
								UNION
								SELECT 4 AS id_tipo, 'Completo' AS nombre
								UNION
								SELECT 2 AS id_tipo, 'Manual' AS nombre
								UNION
								SELECT 5 AS id_tipo, 'Libre' AS nombre
								UNION
								SELECT 6 AS id_tipo, 'Vaciar Almacen' AS nombre) AS tipos
								WHERE 1";			
							$obtener=mysql_query($tipo);
							while($tipos=mysql_fetch_assoc($obtener)){
							?>
								<option value="<?php echo $tipos['id_tipo'];?>"><?php echo $tipos['nombre'];?></option>
                    		<?php
							}//cerramos while
							?>
						</select>
					</td>
				</tr>
			</table>
				<input type="hidden" id="cont_fam" value='0'>
				<input type="hidden" id="filtroFam" value=''>
		</form>
	</div><!--SE CIERRA DIV DE FORMULARIO-->
    	</center><br/><center>
    	<div id="listado" style="width:100%; border:0px solid;">
    	</div>
    </center>
<!--7. Busca almacen principal de sucursal de logueo -->
<?php
	$sql="SELECT id_almacen FROM ec_almacen WHERE id_sucursal ='$sucursal_id' order by prioridad";
	$ejecuta=mysql_query($sql);
	if($ejecuta){
		$almacen=mysql_fetch_row($ejecuta);
		//echo 'almacen local: '.$almacen[0];
	}
?>
<!--Declaramos variables ocultas-->
<input type="hidden" value="<?php echo $almacen[0];?>" id="almacen_local">
</div><!--SE CIERRA DIV GENERAL-->
<?php 
//8. Incluye archivo %pieDePagina.php%%
	include('pieDePagina.php');
?>