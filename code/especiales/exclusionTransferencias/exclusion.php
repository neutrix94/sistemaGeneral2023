<?php
	include('../../../conectMin.php');
/*Implementacion Oscar 2021 para permisos en exclusion de transferencias*/
	$sql_prm = "SELECT 
				p.ver, 
				p.modificar, 
				p.eliminar, 
				p.nuevo, 
				p.imprimir, 
				p.generar
			FROM sys_permisos p
			LEFT JOIN sys_users_perfiles prf ON p.id_perfil = prf.id_perfil
			LEFT JOIN sys_users u ON prf.id_perfil = u.tipo_perfil
			WHERE u.id_usuario = '{$user_id}'
			AND p.id_menu = 162";
	$eje_prm = mysql_query( $sql_prm ) or die( "Error al consultar los permisos de la pantalla : " . mysql_error() );
	$perm = mysql_fetch_assoc( $eje_prm );
	if( $perm['ver'] == 1 || $perm['modificar'] == 1 || $perm['eliminar'] == 1 || $perm['nuevo'] == 1 
		|| $perm['imprimir'] == 1 || $perm['generar'] == 1 ){
	}else{
		die( '<script>alert("No tiene permisos para esta pantalla"); location.href="../../../index.php?";</script>' );
	}
	echo '<input type="hidden" id="add_new_row" value="' . $perm['nuevo'] . '"/>';
/*Fin de cambio Oscar 2021*///armammos la consulta
	$sql="SELECT 
			et.id_exclusion_transferencia,/*0*/
			et.id_producto,/*1*/
			p.orden_lista,/*2*/
			p.nombre,/*3*/
			et.observaciones,/*4*/
			CONCAT(et.fecha,'<br>',et.hora),/*5*/
			SUM( IF( 
					ma.id_movimiento_almacen IS NULL OR ma.id_almacen != 1, 
					0, 
					( md.cantidad * tm.afecta ) 
				) 
			)/*6*/
		FROM ec_exclusiones_transferencia et
		LEFT JOIN ec_productos p ON et.id_producto=p.id_productos
		LEFT JOIN ec_movimiento_detalle md ON md.id_producto = et.id_producto
		LEFT JOIN ec_movimiento_almacen ma ON ma.id_movimiento_almacen = md.id_movimiento
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
		WHERE et.id_producto IS NOT NULL 
		AND et.id_producto >0
		GROUP BY et.id_producto";
	$eje=mysql_query($sql)or die("Error al consultar los productos excluidos!!!\n\n".$sql."\n\n".mysql_error());
?>
<style type="text/css">
	.global{background-image: url('../../../img/img_casadelasluces/bg8.jpg');width: 100%;height:100%;padding: 0;margin:0;position: absolute;top:0;left:0;}

	.enc{position: relative;top:0;height:60px;background:#83B141;}
	
	#busc{padding:10px;position: relative;top:10px; width:30%; left:1%;border-radius: 8px;}

	#res_busc{position:relative;width:30%;top:20px;left:1%;background: white;height: 300px;z-index:3;display: none;overflow: hidden;}
	
	#contenido{width: 100%;}
	
	th{padding: 10px;background: rgba(225,0,0,.6);color: white;}

	#contenido>p{font-size: 22px; left:15px;position: relative;}

	#cont_tabla{width: 101%;height: 400px;border:1px solid;position: relative;top:-3px;overflow: scroll;}

	.oculto{display: none;}

	.footer{position: absolute;height:50px;width:100%;background:#83B141;bottom:0;}

	.bt_regresar{text-decoration:none;border:1px solid;color:black;padding: 8px;background: gray;border-radius: 5px;}
	.bt_regresar:hover{background:rgba(0,0,0,.8);color: white;}

/*emergente*/
	.emergente{ position: absolute;width: 100%;height: 100%;top:0;left: 0; background-color: rgba( 0,0,0,.8); display: none;}
	.emergente_content{ position: absolute; left: 10% ; width : 80%; top: 20%; height: 50%; border: 1px solid red; 
		background-color: rgba(0,0,0,.4); border-radius: 20px;}
	.emergente_btn_close{ position: absolute;top:19%; left: 89%; z-index: 100; border-radius: 50%; color: white; background: red;}
	.all_movs_types{ position : absolute ;right: 5%; top : 80%;}

	.helper{ position: fixed; top: 80%; right: 2.5%; width: 50px; height: 50px; background-color: blue; border-radius: 50%; color: white;
		font-size: 25px;}
</style>

<!DOCTYPE html>
<html>
<head>
	<title>Exclusiones de Transferencias</title>
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
<script type="text/javascript" src="funciones.js"></script>
</head>
<body>
	<div class="global">
		<div class="enc">
			<input type="text" id="busc" onkeyup="busca(event);"> <img src="../../../img/especiales/buscar.png" width="40px" style="position:relative;top:5px;left:20px;">
			<div id="res_busc"></div>
		</div>
	<!--contenido-->
		<div id="contenido">
		<p><b>Listado de Productos Excluidos</b></p>
		<center>
			<table style="width:80%">
				<tr>
					<th width="15%">Orden Lista</th>
					<th width="25%">Producto</th>
					<th width="5%">Inv Matriz</th>
					<th width="25%">Observaciones</th>
					<th width="15%">Fecha</th>
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
									while($r=mysql_fetch_row($eje)){
										$c++;//incrementamos contador
									//asignamos el color
										if($c%2==0){
											$color="#E6E8AB";
										}else{
											$color="#BAD8E6";
										}
										echo '<tr id="fila_'.$c.'" style="background:'.$color.';" tabindex="'.$c.'" onclick="resalta_fila('.$c.');">';
											echo '<td class="oculto" id="0_'.$c.'">'.$r[0].'</td>';
											echo '<td class="oculto" id="1_'.$c.'">'.$r[1].'</td>';
											echo '<td width="15%" id="2_'.$c.'" align="center">'.$r[2].'</td>';
											echo '<td width="25%" id="3_'.$c.'">'.$r[3].'</td>';
											echo '<td width="5%" id="6_'.$c.'">'.$r[6].'</td>';
											echo '<td width="25%" id="4_'.$c.'" ' . ( $perm['modificar'] == 1 ? 'onclick="edita_celda('.$c.');"' : '') . '>'.$r[4].'</td>';
											echo '<td width="15%" id="5_'.$c.'" align="center">'.$r[5].'</td>';
											echo ( $perm['eliminar'] == 1 ? '<td width="14%" align="center"><a href="javascript:elimina('.$c.');"><img src="../../../img/especiales/delete.png" width="40px;"></a></td>' : '');
										echo '</tr>';
									}
								echo '</table>'; 	
							?>
						</div>
					</td> 
				</tr>
			</table>
			<input type="hidden" id="filasTotales" value="<?php echo $c;?>">
		</center>
		</div>
	<!--fin de contenido-->

		<div class="footer">
			<div style="float : left; display : inline-block; width : 45%; text-align : center;">
				<a href="../../../index.php?" class="btn btn-warning">Regresar al Panel</a>
			</div>
			<!--div style="float : rigth; display : inline-block; width : 33%; text-align : center;"-->
				<button class="helper" onclick="view_movs_types( 1 );">?</button>
			<!--/div-->
			<div style="float : rigth; display : inline-block; width : 45%; text-align : center;">
				<button 
					type="button"
					class="btn btn-info"
					onclick="exportarExcel();"
				>
					Descargar a Excel
				</button>
			</div>
		</div>
	</div>
<!-- Implementacion Oscar 2021 para exportacion en Excel -->
	<form id="TheForm" method="post" action="bd.php" target="TheWindow">
			<input type="hidden" id="fl" name="fl" value="download_data" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>

	<div class="emergente">
		<button class="emergente_btn_close" onclick="emergente_close();">X</button>
		<div class="emergente_content">
			<?php include('helper.php'); ?>
		</div>
	</div>
</body>
</html>
