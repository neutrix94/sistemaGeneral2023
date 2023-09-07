<style>
	*{margin: 0;}
	#global{width: 100%;height: 100%;padding: 0;background-image: url('../../../img/img_casadelasluces/bg8.jpg');}
	#contenido{width:60%;height: 65%;}
	.ex{width:100px;height:30px;vertical-align:middle;border-radius:5px;color:red;border: 1px solid white;background:transparennt;top:10px;}
	.ex:hover{background:green;}
	.titulo{color:white;font-size:16px;}
	a{text-decoration: none;color:white;}
	.informa{background: transparent;text-align: right;border:0;width: 100%;height: 100%;color:black;padding: 8px;}
	.guarda{padding: 10px;border:2px solid gray;border-radius: 15px;background: #A59D95;color: white;}
	.guarda:hover{background: white;color:green;}
	.fi{height: 40px;}
	.footer{background: #83B141;width: 100%;bottom: 0;height: 50px;position: fixed;}
	.emerge{background:rgba(0,0,0,.8);width:100%;height:100%;position:absolute;display: none;z-index: 100;}
</style>
<?php
	include('../../../conect.php');//incluimos libreria de conexion
	extract($_GET);
	if($sucursal_id==-1){
		$sucursal=1;	
	}else{
		$sucursal=$sucursal_id;
	}
	if(isset($id_suc_adm)){
		$sucursal=$id_suc_adm;
	}else{
		$id_suc_adm=$sucursal_id;
	}	
	$WHERE=' AND ma.id_sucursal='.$sucursal;
	
//creamos variable oculta del id del allmacen
	echo '<input type="hidden" id="id_alm" value="'.$alm.'">';
?>
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>
<div id="global">
<center>	
	<div id="emergente" class="emerge">
		<center>
		<div style="background:rgba(0,225,0,.5);border:1px solid orange;top:170px;position:relative;width:60%;height:150px;">
			<div id="cont_vta_emerge">
				<span style="color:white;font-size:30px;">Guardando datos...</span>
				<p><img src="../../../img/img_casadelasluces/load.gif" height="100px" width="100px"></p>
			</div>
		</div>
		</center>
	</div>
	<div id="encabezado" style="width:100%;height:80px;background:#83B141;">
		<table style="padding:10px;" width="100%" >
			<tr style="padding:5px;">
				<td width="50%">
					<p>
						<?php include('../buscador/buscador.php');?>
					</p>				
				</td>
				<td width="20%">
					<p style="color:white;">	
						Sucursal:<?php include('controlaSucursales.php');?>
					</p>
				</td>
				<td width="20%">
					Almacen: <SELECT id="almacenes_sucursal" onchange="cambia_almacen(this);">
					<?php 
						if(!isset($alm)){
							echo '<option value="-1">--SELECCIONAR--</option>';
						}else{
							$sql="SELECT id_almacen,nombre FROM ec_almacen WHERE id_almacen=$alm";
							$eje_alm=mysql_query($sql)or die("Error al consultar el almacen seleccionado!!!\n".mysql_error());
							$r_alm=mysql_fetch_row($eje_alm);
							echo '<option value="'.$r_alm[0].'">'.$r_alm[1].'</option>';
						}/*
						$sql="SELECT id_almacen,nombre from ec_almacen where id_sucursal=$sucursal";
						$eje=mysql_query($sql)or die("Error!!!\n\n".$sql."\n\n".mysql_error());
						while($r_a=mysql_fetch_row($eje)){
							echo '<option value="'.$r_a[0].'">'.$r_a[1].'</option>';
						}*/
						$sql="SELECT id_almacen,nombre from ec_almacen where id_sucursal=$sucursal and id_almacen!='$alm'";
						$eje=mysql_query($sql)or die("Error!!!\n\n".$sql."\n\n".mysql_error());
						while($r_a=mysql_fetch_row($eje)){
							echo '<option value="'.$r_a[0].'">'.$r_a[1].'</option>';
						}
					?> 
							</SELECT>
			<!--Filtrado por tipo-->
				<td width="15%">
					<?php
						$sql="SELECT
								ax.id,ax.Descripcion
							FROM(
								(SELECT '1' as id,'Solo Negativos' as Descripcion)
								UNION(
								SELECT '2' as id,'Solo Positivos' as Descripcion)
								UNION(
								SELECT '3' as id,'Todos' as Descripcion)
							)ax
						WHERE ax.id='$id_tipo_filtro'";
						$eje_tip=mysql_query($sql)or die("Error al consultar los tipos de filtros!!!".mysql_error());
						echo '<select id="tipo_filtrado" onchange="cambia_almacen(this);">';
						//die(mysql_num_rows($eje_tipo));
						while($r_tip=mysql_fetch_row($eje_tip)){
							echo '<option value="'.$r_tip[0].'">'.$r_tip[1].'</option>';
						}		
						$sql="SELECT
								ax.id,ax.Descripcion
							FROM(
								(SELECT '1' as id,'Solo Negativos' as Descripcion)
								UNION(
								SELECT '2' as id,'Solo Positivos' as Descripcion)
								UNION(
								SELECT '3' as id,'Todos' as Descripcion)
							)ax
						WHERE ax.id!='$id_tipo_filtro'";
						$eje_tip=mysql_query($sql)or die("Error al consultar los tipos de filtros!!!".mysql_error());
						//die(mysql_num_rows($eje_tipo));
						while($r_tip=mysql_fetch_row($eje_tip)){
							echo '<option value="'.$r_tip[0].'">'.$r_tip[1].'</option>';
						}		
						echo '</select>';
					?>
				</td>
				<td width="20%">
					<p>
						<input type="button" value="Guardar Modificaciones" class="guarda" onclick="<?php echo 'guarda('.$sucursal.');';?>">
						<input type="hidden" id="cambios" >
					</p>
				</td>
			</tr>			
		</table>
	</div>
		<br><!--Damos un espacio-->
	<table border="0" id="enc" width="60%" style="position:absolute;left:20%;border-radius:5px;height:60px;background:rgba(225,0,0,.6);">
				<tr>
					<td width="10%" align="center" class="titulo">Orden Lista</td>
					<td width="44.5%" align="center" class="titulo">Descripcion</td>
					<td width="10%" align="center" class="titulo">Temp</td>
					<td width="10%" align="center" class="titulo">Inv virtual</td>
					<td width="10%" align="center" class="titulo">Inv fisico</td>
					<td align="center" class="titulo">Diferencia</td>
					<td width="20px"></td>
				</tr>
			</table>
			<br><br><br>
	<div id="contenido">
		<div id="listado" style="text-align:center;width:100%;overflow:scroll;height:430px;">
		<center>
			<table id="formInv" width="100%">
			<?php
			if(isset($alm)){
		/*Modificacion Oscar 22.02.2018*/
				//$c_a="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$sucursal AND es_almacen=1";
				//$eje=mysql_query($c_a)or die("Error al consultar almacen primario!!!\n\n".$c_a."\n\n".mysql_error());
				//$alm=mysql_fetch_row($eje);
				$sql="SELECT p.id_productos,p.nombre,
							IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))),
							p.orden_lista
						FROM ec_productos p /*ON i.id_producto=p.id_productos*/
						LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
						LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_almacen=$alm $WHERE
						LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
						WHERE p.id_productos>2 /*AND p.es_maquilado=0*/AND p.muestra_paleta=0";/*se agrega condicion de muestra paleta=0 Oscar 27/06/2018*/
		//fin de modificación 2.02.2018
						
				$sql.=' '.$WHERE." GROUP BY p.id_productos ORDER BY p.orden_lista ASC";
				$cons=mysql_query($sql);
		//die($sql);
				if(!$cons){
					die("Error al consultar inventario...\n".mysql_error());
				}

		//declaramos contador
				$c=0;
				while($row=mysql_fetch_row($cons)){
				if(($row[2]<0 && $id_tipo_filtro==1)||($row[2]>0 && $id_tipo_filtro==2)||($row[2]!=0 && $id_tipo_filtro==3)){//puesto por oscar el 28/06/2018 para ajuste de inventario en linea
					$c++;//incrementamos contador
				//
					if($c%2==0){
						$color='#FFFF99';
					}else{
						$color='#CCCCCC';
					}
			?>	

					<tr id="<?php echo 'fila'.$c;?>" class="fi" style="background:<?php echo $color;?>;" onclick="<?php echo 'resalta('.$c.');';?>"
						value="<?php echo $row[0];?>">
						<td width="10%" id="mov_tmp_<?php echo $c;?>" align="right">
						<?php echo $row[3];?>
							<!--<input type="text" class="informa" style="text-align:center;" disabled>-->
						</td>
						<td width="45%">
							<input type="hidden" id="<?php echo '0,'.$c;?>" value="<?php echo $row[0];?>">
						<!--Implementación para actualización de inventario en tiempo real (Oscar 02.05.2018)-->
							<input type="hidden" id="<?php echo $row[0];?>" value="<?php echo $c;?>"><!--Guardamos el valor del contador y le asignamos id de prod al elemento-->
						<!--Fin de cambio-->
							<input type="text" id="<?php echo '1,'.$c;?>" value="<?php echo $row[1];?>" class="informa" style="text-align:left;" disabled/>
						</td>
					<!--Implementación para actualización de inventario en tiempo real (Oscar 02.05.2018)-->
						<td width="10%" align="right" id="<?php echo 'temporal_'.$c;?>">0</td>
					<!--Fin de cambio-->
						<td width="10%">
							<input type="text" id="<?php echo '2,'.$c;?>" value="<?php echo $row[2];?>" class="informa" disabled/>
						</td>
						<td width="10%">
							<input type="text" id="<?php echo '3,'.$c;?>" value="0" class="informa" 
							onkeyup="<?php echo 'validar(event,'.$c.');';?>" tabindex="<?php echo $c;?>" onclick="verificaTemporal(<?php echo $c;?>);" disabled>
						</td>
						<td width="15%">
							<input type="text" id="<?php echo '4,'.$c;?>" class="informa"  
							value="<?php echo $row[2]*-1;?>" disabled>
						</td>
					</tr>

			<?php
				}//fin de if
				}//fin de while
			}else{
				echo '<p style="font-size:30px;"><b>Seleccione un almacen</b></p>';
			}
			?>
			</table>
		</div><!--cierra div listado-->
<input type="hidden" id="tope" value="<?php echo $c;?>">
	</div><!--Se cierra div #contenido-->
	<br>
<!--TOPE PARA NO GENERAR ERRORES-->
		<div class="footer" id="footer" style="padding:10px;">
			<p>
				<input type="button" class="guarda" id="panel" value="Panel Principal" onclick="link(1);">
			</p>
		</div>
</div>

<script type="text/JavaScript">



</script>