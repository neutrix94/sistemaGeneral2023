<?php
	include("../../conectMin.php");
	extract($_POST);//recibimos datos por post
	echo 'ok|';
	//die($perfil_usuario);
//consultamos si es administrador para poder cambiar la imagen del paquete
	$cambia_img=0;
	$sql="SELECT admin FROM sys_users_perfiles WHERE id_perfil=".$perfil_usuario;
	$eje=mysql_query($sql)or die("Error al consultar si el usuario es administrador!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	if($r[0]==1 && $fl!=1){
		$cambia_img=1;
	}

	$acc_pqt="";
//inicializamos variables de arrreglos
	$r="";
	$rw="";
	$estado="checked";
//asignamos acción de botón guardar
	if($fl==0){
		$acc_pqt="insertar";
		$r[2]='ejemplo_01.jpg';
	}
	if($fl==2){
		$acc_pqt="actualizar";
	}
//deshailitamos para solo para vizualizar
	if($fl==1){
		$deshabilitado="disabled";
	}
	if($fl==1||$fl==2){
//consultamos datos del paquete
		$sql="SELECT id_paquete,nombre,IF(imagen is null or imagen='','ejemplo_01.jpg',imagen),activo
				precio
				FROM ec_paquetes WHERE id_paquete=$id";
		$eje=mysql_query($sql)or die("Error al consultar cabecera de paquete!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
//deshabilitamos checkbox 
		if($r[3]==0){
			$estado="";
		}
	}
	if($fl==1){
		$deshab_busc="display:none;";
		$oculta_guardar="display:none;";
		$deshabilita_caja="disabled";		
	}
?>
<div style="position:absolute;top:0;width: 100%;">
	<form>
	<p align="center" style="color:white;font-size:150%;">Datos del paquete</p>
		<table class="cat_paqt">
			<tr>
				<td width="20%" class="form_paqt"><b>ID:</b></td> 
				<td class="form_paqt"><input type="text" id="id_paq" class="entrada_paq" style="background:rgba(225,0,0,.2);" value="<?php echo $r[0];?>" disabled></td>
				<td rowspan="4" width="30%" align="center">
					<!--<p id="sub_emerge_img" style="display:none;position:absolute;background:rgba(0,0,0,.5);top:8.5%;right:12.5%;width:25%;height:90%;z-index:20;border:1px solid red;">
						<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
						Editar
					</p>-->
					<p style="position:absolute;border:1px solid red;top:11%;height:70%;">
						<img src="../img/paquetes/<?php echo $r[2];?>" id="img_paquete" 
						<?php 
							if($fl==0||$fl==2){echo 'onclick="modificar_img('.$r[0].');" title="Click para cambiar imágen"';}
							if($fl==1){echo 'onclick="agrandar_img('.$r[0].');" title="Click para abrir imágen"';}
						?> class="img_normal" onmouseover="editar_emerge(1);" onmouseout="editar_emerge(0);">
					</p>
					<table style="position:absolute;bottom:0;border:0;">
						<tr>
						<?php
							if($fl==1||$fl==2){
						?>
							<td align="center">
								<button id="sube_img_pqt" style="padding:10px;border-radius:10px;display:none;" onclick="cambia_imagen_bd(<?php echo $id;?>);">
									<b>Cambiar<br>Imágen</b>
								</button>
							</td>

							<td align="center">								
						<!--Implementación Oscar 14.03.2019 para guardar un paquete como nuevo-->
							<?php
								if($fl==2){
							?>
								<button style="width:80px;height:70px;border-radius:5px;<?php echo $oculta_guardar;?>" id="gda_nvo_pqt_1" onclick="guardaNuevoRegistro('insertar_nuevo',1,1);" disabled>
									Guardar Como<br>Nuevo Paquete
								</button>
							<?php
								}//fin de if es editar
							echo '</td>';
							}
							?>
							<td align="center">
								<button style="width:80px;height:70px;border-radius:5px;<?php echo $oculta_guardar;?>" id="gda_nvo_pqt" onclick="guardaNuevoRegistro(<?php echo '\''.$acc_pqt.'\'';?>)" disabled>
									<img src="../img/especiales/save.png" width="60%"><br>Guardar
								</button>
							</td>
						</tr>
					</table>	

				</td>
				<!--<td width="20%" class="form_paqt"><b>Monto:</b></td>
				<td class="form_paqt"><input type="text" id="cost_paq" class="entrada_paq" value="<?php //echo $r[2];?>" disabled></td>-->
			</tr>
			<tr>
				<td width="20%" class="form_paqt"><b>Nombre:</b></td>	
				<td class="form_paqt"><input type="text" id="nom_paq" class="entrada_paq" value="<?php echo $r[1];?>" <?php if($fl==0||$fl==2){echo 'onkeyup="habilitaEdicionPaquete(1);"';}?> <?php echo $deshabilitado;?>></td>
			</tr>
			<tr>	
				<td width="20%" class="form_paqt" onclick="habilitaEdicionPaquete();"><b>Habilitado:</b></td>
				<td class="form_paqt"><input type="checkbox" id="stat_paq" <?php echo $estado;?> <?php if($fl==0||$fl==2){echo 'onclick="habilitaEdicionPaquete(1);"';}?>></td>
			</tr>
		<!--Detalle del paquete-->
			<tr>
				<td colspan="2">
				<div style="height:350px;overflow:scroll;">
					<center style="top:0;">
					<!--Bucador-->
						<input type="text" onkeyup="buscador_detalle(event,this);" id="busc_det_paq" 
						style="width:20%;padding:10px;border-radius:8px;position:relative;right:23.6%;<?php echo $deshab_busc;?>">
   						<input type="text" onkeyup="" id="cant_add" style="<?php echo $deshab_busc?>">
						<img src="../img/especiales/add.png" width="40px" height="40px" id="add_bscdor" onclick="agregaFila('num');" style="<?php echo $deshab_busc?>">   
					<!--sección de resultados de búsqueda-->
						<div id="resp_busc"></div><br><br>
					<!--Fin de buscador-->

				<?php
					if($fl==1||$fl==2){
					//llenamos el detalle de productos
						$sql="SELECT pd.id_paquete_detalle,
				 				p.id_productos,
				 				p.nombre,
				 				pd.cantidad_producto
							FROM ec_paquete_detalle pd
							LEFT JOIN ec_productos p ON pd.id_producto=p.id_productos
							WHERE pd.id_paquete=$id";
						$eje=mysql_query($sql)or die("Error al consultar detalle de paquete!!!\n\n".$sql."\n\n".mysql_error());
					}

				//formamos la tabla
					echo '<table id="detalle_paquete">';
				//formamos encabezado
					echo '<tr>';
						echo '<th width="50%">Producto</th>';
						echo '<th width="20%">Cantidad</th>';
						echo '<th width="20%" style="'.$oculta_guardar.'">Eliminar</th>';
					echo '</tr>';
					echo '<tbody>';
					$cont=0;//declaramos contador en 0
					if($fl==1||$fl==2){
						while($rw=mysql_fetch_row($eje)){
							$cont++;//incrementamos contador
				?>
							<tr id="detalle_<?php echo $cont;?>" class="tr_detalle">
								<td id="c_1_<?php echo $cont;?>" class="ocult"><?php echo $rw[0];?></td>
								<td id="c_2_<?php echo $cont;?>" class="ocult"><?php echo $rw[1];?></td>
								<td id="c_3_<?php echo $cont;?>" style="color:black;"><?php echo $rw[2];?></td>
								<td>
									<input type="text" id="c_4_<?php echo $cont;?>" class="caja_editable" value="<?php echo $rw[3];?>" <?php if($fl==0||$fl==2){echo 'onkeyup="habilitaEdicionPaquete(1);"';}?> <?php echo $deshabilita_caja;?>>
								</td>
								<td align="center" style="<?php echo $oculta_guardar;?>"><button type="button" onclick="eliminaDetalle(2,<?php echo $cont;?>)"><img src="../img/especiales/del.png" height="30px" width="30px"></button></td>
							</tr>
				<?php
					}//cerramos while
				}//cerramos if($fl==1||$fl==2)
					echo '</tbody></table>';
				?>
				</div>
				</td>	
			</tr>
		<!--Fin del detalle de paquete-->

		</table>
		</div>
				<!--Guardamos total de registros en el detalle-->
					<input type="hidden" value="<?php echo $cont;?>" id="total_detalles_paquete">
				</center>
	</form>
<!--Aqui guardamos el id del paquete-->
	<input type="hidden" id="id_encab_pqt" value="<?php echo $id;?>">
<!--Fin de cambio Oscar 14.03.2019-->
	<!--Div de tabla detalle de paquete-->
<div id="dtlle_pqte">
</div>
<form enctype="multipart/form-data" id="form_img_pqt" target="_blank" action="ajax/buscadorPaquetes.php" method="POST">
	<input name="archivo" id="archivo" type="file" onchange="cambia_img();" style="display:none;" accept="image/*"/>
	<input type="hidden" name="id_paq_img" id="id_paq_img" value="<?php echo $id;?>">
	<input type="hidden" name="fl" id="fl" value="carga_img">
</form>
<style type="text/css">
	.entrada_paq{padding: 5px;}
	#detalle_paquete{width:80%;}	
	.ocult{display:none;}
	#resp_busc{border:1px solid blue;width: 32%;height:200px;position: absolute;background: white;left: 10%;display: none;overflow: auto;}
	.cat_paqt{background:white;border-collapse: collapse;width: 100%;margin-left: 0%;}
	.form_paqt{padding: 5px;/*text-align: center;*/}
	#cant_add{width:4%;padding: 10px;border-radius: 5px;position: relative;right: 23%;}
	#add_bscdor{position: relative;right: 22%;top:15px;}
	.caja_editable{background: transparent;border:0;text-align: right;width: 100%;height: 100%;}
	.caja_editable:hover{font-size: 20px;}
	#dtlle_pqte{}
	.tr_detalle{height: 35px;}
	.img_normal{position: relative;left: 0;width: 98%;height:98%;top:1%;}
	.img_ampliada{position: fixed;width: 98%;height: 80%;top:20px;left:1%;}
</style>

