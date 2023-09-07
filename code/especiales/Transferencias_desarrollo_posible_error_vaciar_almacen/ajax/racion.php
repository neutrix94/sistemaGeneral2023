<?php
		
	//10.3.1. Proceso de la racion por producto
	if($row['raciona']==1 && $id_tipo!=2){
	//ponemos el producto con stock bajo en matriz
		$sql="UPDATE sys_sucursales_producto SET stock_bajo=1 WHERE id_producto={$row['ID']}";/*id_sucursal=1 AND */ 
		$eje_auxiliar_1=mysql_query($sql)or die("Error al marcar en stock bajo los productos para las sucursales!!!\n\n".$sql);

	/*sacamos la presentación del producto si es que tiene*/
		$sql="SELECT IF(pp.id_producto_presentacion IS NULL,1,pp.cantidad) 
			FROM ec_productos p
			LEFT JOIN ec_productos_presentaciones pp ON p.id_productos=pp.id_producto
			WHERE p.id_productos={$row['ID']}";
		$eje_presentacion=mysql_query($sql)or die("Error al consultar la presentación del producto!!!<br>".$sql."<br>".mysql_error());
		$present=mysql_fetch_row($eje_presentacion);
		$presentacion=$present[0];

/************************10.3.1.1. Primera racion **********************************/
	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND /*ma*/alm.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]}/*ventas totales*/)*{$auxiliar[1]}/*inventario almacen principal*/ AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					GROUP BY s.id_sucursal/*agrupamos por sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_1=ROUND(({$row_aux[1]})-({$row_aux[2]}/{$presentacion})) * {$presentacion}  
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
/*if($row['ID']==$producto_prueba){
				echo $sql.'<br><br>';
}*/
			}
//die('');

/************************10.3.1.2.  Segunda racion **********************************/
	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND (sp.racion_1>0 OR sp.id_sucursal=1)
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]})*{$auxiliar[1]} AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					AND (sp_1.racion_1>0 OR sp_1.id_sucursal=1)
					GROUP BY s.id_sucursal/*agrupamos po sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//			die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_2=ROUND(({$row_aux[1]})-({$row_aux[2]}/{$presentacion})) * {$presentacion}  
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
/*if($row['ID']==$producto_prueba){
				echo $sql.'<br><br>';
}*/				//echo $sql.'<br><br>';
			}
	
//die('ok');

/************************10.3.1.3. Tercera racion **********************************/
	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND (sp.racion_2>0 OR sp.id_sucursal=1)
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]})*{$auxiliar[1]} AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					AND (sp_1.racion_2>0 OR sp_1.id_sucursal=1)
					GROUP BY s.id_sucursal/*agrupamos po sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//			die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_3=ROUND( (ROUND( ({$row_aux[1]}) - ({$row_aux[2]}/{$presentacion}) ))*{$presentacion} )
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
//if($row['ID']==$producto_prueba){
//				echo $sql.'<br><br>';
//}
//				echo $sql.'<br><br>';
			}
	/*comparamos la suma de las raciones*/
		$sql="SELECT 
				aux.total_raciones,
				SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_almacen!=1,0,(md.cantidad*tm.afecta))) as inventarioMatriz
			FROM(
				SELECT
					SUM(racion_3) AS total_raciones  
				FROM sys_sucursales_producto 
				WHERE id_producto={$row['ID']}
			)aux
			JOIN ec_productos p ON p.id_productos={$row['ID']}
			LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
			WHERE p.id_productos={$row['ID']}";

		$eje_verif=mysql_query($sql)or die("Error al consultar piezas faltantes en las raciones!!!<br>".mysql_error()."<br>".$sql);
		$row_verif=mysql_fetch_row($eje_verif);
		if($row_verif[0]<$row_verif[1]){
			$diferencia=$row_verif[1]-$row_verif[0];
		//sacamos la sucursal que mas vende
			$sql="SELECT id_sucursal FROM sys_sucursales_producto WHERE id_producto={$row['ID']} ORDER BY racion_3 DESC LIMIT 1";
			$eje_suc_max=mysql_query($sql)or die("Error al consultar la sucursal que más vende!!!<br>".mysql_error()."<br>".$sql);
			$suc_max=mysql_fetch_row($eje_suc_max);
		/**/
			$sql="UPDATE sys_sucursales_producto SET racion_3=(racion_3+{$diferencia}) WHERE id_sucursal={$suc_max[0]} AND id_producto={$row['ID']}";
			$eje_restante=mysql_query($sql)or die("Error al asignar las piezas restantes a la sucursal que más vende!!!<br>".mysql_error()."<br>".$sql);
		}

	//consulta la cantidad racionada desupes de la racion para cambiar el valor en la transferencia
		$sql="SELECT racion_3 FROM sys_sucursales_producto WHERE id_sucursal={$destino} AND id_producto={$row['ID']}";
		$eje_auxiliar_1=mysql_query($sql)or die("Error al consultar la ración del producto para la transferencia!!!\n\n".$sql);
		$row_aux=mysql_fetch_row($eje_auxiliar_1);
		$row['CantidadPresentacion']=$row_aux[0]/$presentacion;
		//die("enttra!!!".$row['ID']);
	}
/*fIN DE IMPLEMENTACIÓN PARA RACIONAR*/
?>