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
<br>
<br>
<div style="position:absolute;top:20px; left : 1px; width: 100%; height : 92%; overflow : auto; background : white !important; font-size : 120% !important;">
	<br><br><br>
	<!--form-->
		<div class="row cat_paqt"><!-- cat_paqt -->
			<h2 align="center" style="color:;font-size:180% !important;">Datos del paquete</h2>
			<br><br>
			<div class="col-7">
				<div class="row">
					<div class="col-4" class="form_paqt">
						<br><b>ID:</b>
					</div> 
					<div class="col-8"><!-- form_paqt -->
						<br><input 
							type="text" 
							id="id_paq" 
							class="form-control" 
							style="background:rgba(225,0,0,.2);" 
							value="<?php echo $r[0];?>" disabled>
					</div>
				</div>
				<div class="row">
					<div class="col-4">
						<br><b>Nombre:</b>
					</div>	
					<div class="col-8">
						<br><input 
							type="text" 
							id="nom_paq" 
							class="form-control" 
							value="<?php echo $r[1];?>" 
							<?php if($fl==0||$fl==2){echo 'onkeyup="habilitaEdicionPaquete(1);"';}?> 
							<?php echo $deshabilitado;?>
						>
					</div>
					
					<div class="col-4" onclick="habilitaEdicionPaquete();">
						<b>Habilitado:</b>
					</div>
					<div class="col-8 text-center">
						<input 
							type="checkbox" 
							id="stat_paq" 
							<?php echo $estado;?> 
							<?php if($fl==0||$fl==2){echo 'onclick="habilitaEdicionPaquete(1);"';}?>
						>
					</div>
				</div>
			</div>

			<div class="col-5"><!-- form_paqt-->
				<p style=""><!-- position:absolute;border:1px solid red;top:11%;height:70%;-->
					<img src="../img/paquetes/<?php echo $r[2];?>" id="img_paquete" 
					<?php 
						if($fl==0||$fl==2){echo 'onclick="modificar_img('.$r[0].');" title="Click para cambiar imágen"';}
						if($fl==1){echo 'onclick="agrandar_img('.$r[0].');" title="Click para abrir imágen"';}
					?> class="img_normal" onmouseover="editar_emerge(1);" onmouseout="editar_emerge(0);">
				</p>
			</div>

		<div class="row">
			<div class="col-4">
				<br>
				<button 
					class="btn btn-success"
					style="<?php echo $oculta_guardar;?>" 
					id="gda_nvo_pqt" 
					onclick="guardaNuevoRegistro(<?php echo '\''.$acc_pqt.'\'';?>)" 
					disabled
				>
					<i class="icon-ok-circle">Guardar</i>
				</button>
			</div>
		<?php
			if($fl==1||$fl==2){
		?>
			<div class="col-4 text-center">
				<br>
				<button 
					class="btn btn-warning"
					id="sube_img_pqt" 
					style="padding:10px;border-radius:10px;display:none;" 
					onclick="cambia_imagen_bd(<?php echo $id;?>);">
					<i class="icon-file-image">Cambiar Imágen</i>
				</button>
			</div>

			<div class="col-4 text-center">	
			<br>							
		<!--Implementación Oscar 14.03.2019 para guardar un paquete como nuevo-->
			<?php
				if($fl==2){
			?>
				<button 
					class="btn btn-info"
					style="<?php echo $oculta_guardar;?>" 
					id="gda_nvo_pqt_1" 
					onclick="guardaNuevoRegistro('insertar_nuevo',1,1);" 
					disabled
				>
					<i class="icon-doc-add">Guardar Como Nuevo Paquete</i>
				</button>
			<?php
				}//fin de if es editar
			echo '</div>';
			}
			?>

			</div>
		</div>
				<!--<td width="20%" class="form_paqt"><b>Monto:</b></td>
				<td class="form_paqt"><input type="text" id="cost_paq" class="entrada_paq" value="<?php //echo $r[2];?>" disabled></td>-->
			
			
		<!--Detalle del paquete-->
		<div class="row cat_paqt">
		<!--Buscador de productos-->
			<div class="col-7">
				<br>
				<input 
					class="form-control"
					type="text" 
					onkeyup="buscador_detalle(event,this);" 
					id="busc_det_paq" 
					style="<?php echo $deshab_busc;?>"
					placeholder="Buscar productos"
				>
				<!--sección de resultados de búsqueda-->
				<div id="resp_busc"></div>
			</div>
			<div class="col-3">
				<br>
				<input 
					class="form-control"
					type="text" 
					onkeyup="" 
					id="cant_add" 
					style="<?php echo $deshab_busc?>"
					placeholder="Cantidad..."
				>
			</div>
			<div class="col-2">
				<br>
				<button 
					class="btn btn-success"
					id="add_bscdor" 
					onclick="agregaFila('num');" style="<?php echo $deshab_busc?>"
				>
					<i class="icon-ok-circle"></i>
				</button>   
			</div>
		</div>
		<br>
		<div class="row" style="height:350px;overflow:scroll; padding : 20px;">
			<div class="col-1"></div>
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
					echo '<table id="detalle_paquete" class="table table-bordered table-striped">';
				//formamos encabezado
					echo '<thead style="position : sticky; top : -7%; background : red; color : white; z-index : 10; font-size : 120%;">';
					echo '<tr>';
						echo '<th class="text-center">Producto</th>';
						echo '<th class="text-center">Cantidad</th>';
						echo '<th class="text-center" style="'.$oculta_guardar.'">Eliminar</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody id="pack_products_list">';
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
									<input 
										type="text" 
										id="c_4_<?php echo $cont;?>" 
										class="caja_editable form-control" 
										value="<?php echo $rw[3];?>"
										onclick="this.select();"
										<?php if($fl==0||$fl==2){echo 'onkeyup="habilitaEdicionPaquete(1);"';}?> 
										<?php echo $deshabilita_caja;?>
									>
								</td>
								<td align="center" style="<?php echo $oculta_guardar;?>">
									<button 
										class="btn btn-danger"
										type="button" 
										onclick="eliminaDetalle(2,<?php echo $cont;?>)">
										<i class="icon-cancel-circled"></i>
									</button>
								</td>
							</tr>
				<?php
					}//cerramos while
				}//cerramos if($fl==1||$fl==2)
					echo '</tbody></table>';
				?>
				</div>
				</td>	
			</div>
		<!--Fin del detalle de paquete-->

		</div>
		</div>
				<!--Guardamos total de registros en el detalle-->
					<input type="hidden" value="<?php echo $cont;?>" id="total_detalles_paquete">
	<!--/form-->
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
	.ocult{
		display:none;
	}
	#resp_busc{
		box-shadow: 2px 2px 15px rgba( 0,0,0,.3 );
		width: 180%;
		height:200px;
		position: relative;
		background: white;
		left: 0;
		display: none;
		overflow: auto; 
		z-index: : 2000;
	}
	.cat_paqt{background:white;border-collapse: collapse;width: 100%;margin-left: 0%;}
	.form_paqt{padding: 5px;/*text-align: center;*/}
	.caja_editable{background: transparent;border:0;text-align: right;width: 100%;height: 100%;}
	.caja_editable:hover{font-size: 20px;}
	#dtlle_pqte{}
	.tr_detalle{height: 35px;}
	.img_normal{position: relative;left: 0;width: 98%;height:98%;top:1%;}
	.img_ampliada{position: fixed;width: 98%;height: 80%;top:20px;left:1%;}
</style>

