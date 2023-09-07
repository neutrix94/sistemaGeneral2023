<p align="right" style="position:absolute;top:0;right: 3%;" onclick="cierra_sub_emergente_existencias();"><a href="" style="text-decoration:none;color:red;">X</a></p>
<br><br>
<?php
	include("../../conexionDoble.php");
	$id_prod=$_POST['id_producto'];
//consultamos el nombre del producto y nombre de la sucursal
	$sql="SELECT 
			p.nombre,
			s.nombre
		FROM ec_productos p 
		JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
		WHERE p.id_productos=$id_prod";
	$eje=mysql_query($sql,$local)or die("Error al consultar los datos de producto y sucursal!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$nombre_producto=$r[0];
	$nombre_sucursal=$r[1];

//consultamos los almacenes de la sucursal
	$sql="(SELECT id_almacen,nombre FROM ec_almacen WHERE es_almacen=1 AND id_sucursal=$user_sucursal LIMIT 1)
		UNION
		(SELECT id_almacen,nombre FROM ec_almacen WHERE es_almacen=0 AND id_sucursal=$user_sucursal LIMIT 1)";
	$eje=mysql_query($sql,$local)or die("Error al consultar los datos de almacenes de la sucursal!!!\n\n".mysql_error()."\n\n".$sql);
//principal
	$r=mysql_fetch_row($eje);
	$alm_principal=$r[0];
	$nombre_alm_princ=$r[1];
//exhibición
	$r=mysql_fetch_row($eje);
	$alm_exhib=$r[0];
	$nombre_alm_exh=$r[1];	
//consultamos el inventario de la sucursal
	$sql="SELECT 
		SUM(IF(ma.id_movimiento_almacen is null OR ma.id_almacen!=$alm_principal,0,md.cantidad*tm.afecta)),
		SUM(IF(ma.id_movimiento_almacen is null OR ma.id_almacen!=$alm_exhib,0,md.cantidad*tm.afecta))		
		FROM ec_productos p
		LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
		LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
		WHERE p.id_productos='$id_prod'";
	$eje=mysql_query($sql,$local)or die("Error al consultar los innvetarios de almacenes sucursal!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$inv_princ=$r[0];
	$inv_exh=$r[1];
	//die($sql);
//consultamos el inventario en matriz
	$sql="SELECT SUM(IF(md.id_movimiento_almacen_detalle is null OR id_almacen!=1,0,md.cantidad*tm.afecta))		
		FROM ec_productos p
		LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
		LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
		WHERE p.id_productos='$id_prod'";
	$eje=mysql_query($sql,$linea)or die("Eror al consultar el inventario de Matriz!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$inv_mat=$r[0];
?>
<table style="width: 90%;">
	<tr colspan="3"><?php echo $nombre_producto;?></tr>
	<tr>
		<td width="33.33%"><?php echo $nombre_alm_princ;?></td>
		<td width="33.33%"><?php echo $nombre_alm_exh;?></td>
		<td width="33.33%">Matriz</td>
	</tr>
	<tr>
		<td align="center"><?php echo $inv_princ;?></td>
		<td align="center"><?php echo $inv_exh;?></td>
		<td align="center"><?php echo $inv_mat;?></td>
	</tr>

</table>