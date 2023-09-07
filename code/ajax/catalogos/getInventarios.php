<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	
	extract($_GET);
	
	
	//Sacamos el primer inventario
	$sql="	SELECT
			IF(
				SUM(md.cantidad*tm.afecta) IS NULL,
				0,
				SUM(md.cantidad*tm.afecta)
			)
			FROM ec_movimiento_detalle md
			JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
			JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE ma.id_sucursal=$id_sucursal_origen
			AND ma.id_almacen=$id_almacen_origen
			AND md.id_producto=$id_producto";
			
			
	$res=mysql_query($sql) or die(mysql_error());		
	
	$row=mysql_fetch_row($res);
	
	$iao=$row[0];
	
	
	
	//Sacamos el segundo inventario
	$sql="	SELECT
			IF(
				SUM(md.cantidad*tm.afecta) IS NULL,
				0,
				SUM(md.cantidad*tm.afecta)
			)
			FROM ec_movimiento_detalle md
			JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
			JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE ma.id_sucursal=$id_sucursal_destino
			AND ma.id_almacen=$id_almacen_destino
			AND md.id_producto=$id_producto";
			
			
	$res=mysql_query($sql) or die(mysql_error());		
	
	$row=mysql_fetch_row($res);
	
	$iad=$row[0];
	
	
	//Buscamos si tiene presentaciones
	$sql="	SELECT
			id_producto_presentacion,
			nombre
			FROM ec_productos_presentaciones
			WHERE id_producto = $id_producto
			OR id_producto = -1
			ORDER BY cantidad DESC
			LIMIT 1";
	
	$res=mysql_query($sql) or die(mysql_error());		
	
	$row=mysql_fetch_row($res);
	
	$pres=$row[0];
	$npre=$row[1];
	
	
	
	echo "exito|$iao|$iad|$pres|$npre";
	
	
?>