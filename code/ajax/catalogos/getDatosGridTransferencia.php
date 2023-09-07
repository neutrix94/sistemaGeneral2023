<?php

	if (file_exists ("../../conectMin.php"))
		include("../../conectMin.php");
	elseif
		(file_exists ("../../../conectMin.php")) include("../../../conectMin.php");
	
	header("Content-Type: text/plain;charset=utf-8");
	
	mysql_set_charset("utf8");
	
	if (function_exists("mb_internal_encoding"))
		mb_internal_encoding ('utf-8');

	extract($_GET);
	
	if (isset($id_transferencia))
	{
		$sql="	SELECT
				TP.id_transferencia_producto,
				P.id_productos AS id_producto,
				P.nombre,
				IF(
					ISNULL(ECVID.cantidad),
					0,
					ECVID.cantidad
				) AS cantidad,
				TP.cantidad AS transferencia
				FROM ec_transferencias T 
				INNER JOIN ec_transferencia_productos TP ON TP.id_transferencia = T.id_transferencia 
				INNER JOIN ec_productos P ON P.id_productos = TP.id_producto_or  
				LEFT OUTER JOIN ecv_inventarios ECVID on ECVID.id_producto = P.id_productos AND ECVID.id_sucursal = T.id_sucursal_destino AND ECVID.id_almacen = T.id_almacen_destino 
				WHERE TP.id_transferencia = '{$id_transferencia}' 
				GROUP BY P.id_productos ";
		
	}
	else
	{
	
		$WHERETIPO="";
		
		if($id_tipo == 2 || $id_tipo == 5)
			die("exito");
			
			
		if($id_tipo == 1)	
			$WHERETIPO=" AND InvDes <= minimo";	
		if($id_tipo == 3)	
			$WHERETIPO=" AND InvDes <= medio";		
	
		$sql="	SELECT
				'NO',
				'$"."LLAVE',
				aux.ID,
				aux.Nombre,
				aux.InvOr,
				aux.InvDes,
				IF(p.id_producto_presentacion IS NULL,
					-1,
					p.id_producto_presentacion
				) AS Presentacion,
				IF(p.id_producto_presentacion IS NULL,
					(aux.maximo-aux.InvDes),
					TRUNCATE((aux.maximo-aux.InvDes)/p.cantidad, 0)
				) AS CantidadPresentacion,
				IF(p.id_producto_presentacion IS NULL,
					(aux.maximo-aux.InvDes),
					TRUNCATE((aux.maximo-aux.InvDes)/p.cantidad, 0)*p.cantidad
				) AS cantidadSurtir,
				'',
				IF(p.id_producto_presentacion IS NULL,
					1,
					p.cantidad
				) AS Equivalencia,
				IF(p.id_producto_presentacion IS NULL,
					0,
					ABS((aux.maximo-aux.InvDes)-TRUNCATE((aux.maximo-aux.InvDes)/p.cantidad, 0)*p.cantidad)
				) AS Diferencia
				FROM
				(
					SELECT
					p.id_productos AS ID,
					p.nombre AS Nombre,
					SUM(
						IF(m.id_sucursal=$id_sucursal_origen,
							IF(md.cantidad IS NULL, 0, md.cantidad_surtida*tm.afecta),
							0
						)
					) AS InvOr,
					SUM(
						IF(m.id_sucursal=$id_sucursal_destino,
							IF(md.cantidad IS NULL, 0, md.cantidad_surtida*tm.afecta),
							0
						)
					) AS InvDes,
					IF(ep.id_estacionalidad IS NULL,
						p.maximo_existencia,
						ep.maximo
					) AS maximo,
					IF(ep.id_estacionalidad IS NULL,
						p.existencia_media,
						ep.medio
					) AS medio,
					IF(ep.id_estacionalidad IS NULL,
						p.min_existencia,
						ep.minimo
					) AS minimo
					FROM ec_productos p
					LEFT JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
					LEFT JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen AND m.id_sucursal IN($id_sucursal_origen,$id_sucursal_destino)
					LEFT JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
					JOIN sys_sucursales s ON s.id_sucursal=$id_sucursal_destino
					LEFT JOIN ec_estacionalidad_producto ep ON s.id_estacionalidad = ep.id_estacionalidad AND p.id_productos = ep.id_producto
					WHERE p.habilitado=1
					GROUP BY p.id_productos
				) aux
				LEFT JOIN ec_productos_presentaciones p ON aux.ID = p.id_producto
				WHERE InvDes < maximo
				AND IF(p.id_producto_presentacion IS NULL,
						(aux.maximo-aux.InvDes),
						 TRUNCATE((aux.maximo-aux.InvDes)/p.cantidad, 0)
					) > 0
				$WHERETIPO	
				
				ORDER BY ID, Diferencia, Equivalencia DESC";
				
		
		//die($sql);
					
	}
	
	
	$res=mysql_query($sql) or die(mysql_error());
		
	$num=mysql_num_rows($res);
	
	echo "exito";
	$pant=-10;
	
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		
		if($pant != $row[2])
		{
			echo "|";
			for($j=0;$j<sizeof($row)-1;$j++)
			{
				if($j > 0)
					echo "~";
				echo $row[$j];	
			}
		}	
		
		$pant=$row[2];
	}
	
?>