<?php
	include('../../conectMin.php');

	$flag=$_POST['fl'];
//asignamos fechas fechas
	$fmax_del=$_POST['f_1'];
	$fmax_al=$_POST['f_2'];
	$fprom_del=$_POST['f_3'];
	$fprom_al=$_POST['f_4'];
//extraemos todos los productos
	$sql="SELECT  
			p.id_productos,
			p.orden_lista,
			p.nombre,
			SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_sucursal!=1,0,(md.cantidad*tm.afecta))),
			p.observaciones AS observations/*implementacion Oscar 2023/10/16*/
		FROM ec_productos p
		LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
		LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
		WHERE p.id_productos>1
		GROUP BY p.id_productos
		ORDER BY p.orden_lista,p.nombre ASC";
	$eje_1=mysql_query($sql)or die("Error al consultar datos generales e inventarios de los productos!!!\n\n".$sql."\n\n".mysql_error());
	$num=mysql_num_rows($eje_1);

//extraemos las estacionalidades altas	
	$sql="SELECT 
				e.id_estacionalidad,
				e.nombre,
				'',
				'',
				s.id_sucursal,
				s.nombre 
		FROM ec_estacionalidad e
		LEFT JOIN sys_sucursales s ON e.id_sucursal=s.id_sucursal 
		WHERE e.id_estacionalidad>0 
		AND e.es_alta=1 
		ORDER BY s.id_sucursal ASC";
	$eje_2=mysql_query($sql) or die("Error al consultar las estacionalidades altas !!!".$sql."\n\n".mysql_error());

//extraemos las estacionalidades altas	
	$sql="SELECT 
				e.id_estacionalidad,
				e.nombre,
				'',
				'',
				s.id_sucursal,
				s.nombre 
		FROM ec_estacionalidad e
		LEFT JOIN sys_sucursales s ON e.id_sucursal=s.id_sucursal 
		WHERE e.id_estacionalidad>0 
		AND e.es_alta=1 
		ORDER BY s.id_sucursal ASC";
	$eje_final=mysql_query($sql) or die("Error al consultar las estacionalidades altas !!!".$sql."\n\n".mysql_error());

//generamos encabezados para cada estacionalidad
	$sql="SELECT 'venta_mas_alta','promedio','id_registro','id_estacionalidad','id_producto','nombre','maximo alta','maximo final' 
	FROM ec_estacionalidad WHERE id_estacionalidad>0 AND es_alta=1 ORDER BY id_estacionalidad ASC";
	$eje_3=mysql_query($sql)or die("Error al generar la consulta de encabezados!!!");

/**********************************************************Creaci¨®n del CSV********************************************************/
	//regresamos csv

		$nombre='todas_las_estacionalidades.csv';
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-disposition: attachment; filename="'.$nombre.'"');

//formamos la cabecera 
	echo utf8_decode('Datos del producto,'.','.','.',');
//creamos variable que guarda id_sucursal
//$sucs="";
	$num_estac=mysql_num_rows($eje_2);
	for($i=0;$i<$num_estac;$i++){
		$dat_enc=mysql_fetch_row($eje_2);
		echo utf8_decode($dat_enc[1].',,'.$dat_enc[2].','.$dat_enc[3].',,,,,,'.$dat_enc[5]);
		if($i<($num_estac-1)){
			echo utf8_decode(',');
		}else{
			echo utf8_decode("\n");
		}
//		$sucs.=$dat_enc[4]."|";//guardamos las sucursales
	}
	//$dat=mysql_fetch_row($eje_1);
	//echo $dat[0].','.$dat[1].','.$dat[2].','.$dat[3].',';
	echo utf8_decode('id_producto,'.'orden_lista,'.'nombre,'.'inventario_matriz,');
	for($i=0;$i<$num_estac;$i++){
		$dat_enc=mysql_fetch_row($eje_3);
		echo $dat_enc[0].','.$dat_enc[1].','.$dat_enc[2].','.$dat_enc[3].','.$dat_enc[4].','.$dat_enc[5].','.$dat_enc[6].','.$dat_enc[7] . ',Total Ventas,Habilitado sucursal';
		if($i<($num_estac-1)){
			echo utf8_decode(',');
		}else{
			echo utf8_decode(",NOTAS GENERALES\n");
		}
	}

//	$sucursal=explode("|",$sucs);//extraemos sucuursales
	for($i=0;$i<$num;$i++){
//		$id_suc=$sucursal[$i];//declaramos sucursal
		$dat_prod=mysql_fetch_row($eje_1);
		echo utf8_decode($dat_prod[0].','.$dat_prod[1].','.$dat_prod[2].','.$dat_prod[3].',');
	//sacamos el registro de estacionalidad por producto para cada estacionalidad
		/*$sql="SELECT 
				IF(id_estacionalidad_producto IS NULL,'',id_estacionalidad_producto),
				IF(id_estacionalidad_producto IS NULL,'',id_estacionalidad),
				IF(id_estacionalidad_producto IS NULL,'',id_producto),
				'$dat_prod[2]',
				IF(id_estacionalidad_producto IS NULL,'',maximo)
			FROM ec_estacionalidad_producto 
			WHERE id_producto=$dat_prod[0]
			AND id_estacionalidad IN (SELECT id_estacionalidad FROM ec_estacionalidad WHERE es_alta=1 AND id_estacionalidad>0)
			ORDER BY id_estacionalidad ASC";			
		*/
		$sql="SELECT
				ax3.ventas,
				ax3.promedio,
				ax3.id_estacionalidad_producto,
				ax3.id_productos,
				ax3.id_estacionalidad,
				ax3.nombre,
				ax3.maximo AS estacionalidadAlta, 
				ep2.maximo AS estacionalidadFinal,
				ax3.sales_total,/*Oscar 2023*/
				ax3.estado_suc/*Oscar 2023*/
			FROM(
				SELECT
					MAX(ax2.ventas) as ventas,/*m¨¢ximoFiltrado*/
	    			(SUM(IF(sucs1.id_sucursal=ax2.id_sucursal,ax2.ventasPromedio,0)))/(DATEDIFF('$fprom_al','$fprom_del')+1) AS promedio,/*promedio de la fecha filtrada*/
					ax2.id_estacionalidad_producto,
				    ax2.id_productos,
				    ax2.id_estacionalidad,
					ax2.nombre,
					ax2.maximo,
					ax2.id_sucursal,
					SUM( ax2.ventas ) AS sales_total,/*Oscar 2023*/
	    			ax2.estado_suc/*Oscar 2023*/
				FROM(
	                SELECT
						ax1.id_sucursal,
						ax1.nombre,
	    				ax1.id_productos,
	    				SUM(IF(ped.id_sucursal=ax1.id_sucursal AND pd.es_externo=0 AND (ped.fecha_alta BETWEEN '$fmax_del 00:00:01' AND '$fmax_al 23:59:59'),pd.cantidad,0)) AS ventas,
	    				SUM(IF(ped.id_sucursal=ax1.id_sucursal AND pd.es_externo=0 AND (ped.fecha_alta BETWEEN '$fprom_del 00:00:01' AND '$fprom_al 23:59:59'),pd.cantidad,0)) AS ventasPromedio,
	    				ax1.maximo,
	    				ax1.id_estacionalidad_producto,
	    				ped.fecha_alta,
	    				ax1.id_estacionalidad,
	    				ax1.estado_suc/*Oscar 2023*/
					FROM(
						SELECT
							s.id_sucursal,
					    	p.nombre,
	    				    p.id_productos,
	    		    		ep.maximo,
	        				ep.id_estacionalidad_producto,
	        				ep.id_estacionalidad,
	        				sp.estado_suc/*Oscar 2023*/
	      				FROM ec_productos p
		  				LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
		  				LEFT JOIN sys_sucursales s on s.id_sucursal=sp.id_sucursal
		  				JOIN ec_estacionalidad e ON s.id_sucursal=e.id_sucursal AND e.es_alta=1
		  				JOIN ec_estacionalidad_producto ep ON e.id_estacionalidad=ep.id_estacionalidad AND p.id_productos=ep.id_producto
	      				WHERE s.id_sucursal>1 AND p.id_productos='$dat_prod[0]'
						GROUP BY s.id_sucursal
						ORDER BY s.id_sucursal ASC
					)ax1
	    			LEFT JOIN ec_pedidos_detalle pd ON ax1.id_productos=pd.id_producto
	   				LEFT JOIN ec_pedidos ped ON ax1.id_sucursal=ped.id_sucursal AND pd.id_pedido=ped.id_pedido 
	   				WHERE 1 
	    			GROUP BY ax1.id_sucursal,DATE_FORMAT(ped.fecha_alta, '%Y%m%d'),ax1.id_productos
	    			ORDER BY ax1.id_sucursal ASC
	    		)ax2
				JOIN sys_sucursales sucs1 ON ax2.id_sucursal=sucs1.id_sucursal
	    		GROUP BY ax2.id_sucursal
	    		ORDER BY ax2.id_sucursal ASC
	    	)ax3
			JOIN ec_estacionalidad e2 ON e2.id_sucursal = ax3.id_sucursal 
			AND e2.es_alta=0
			JOIN ec_estacionalidad_producto ep2 ON e2.id_estacionalidad=ep2.id_estacionalidad 
			AND ax3.id_productos=ep2.id_producto
			GROUP BY ax3.id_sucursal
	    	ORDER BY ax3.id_sucursal ASC";
//die($sql);
		$eje_3=mysql_query($sql)or die("Error al extraer estacionalidades por producto!!!\n\n".$sql."\n\n".mysql_error());
		for($j=0;$j<mysql_num_rows($eje_3);$j++){
			$dat_est_pro=mysql_fetch_row($eje_3);
			echo utf8_decode($dat_est_pro[0].','.$dat_est_pro[1].','.$dat_est_pro[2].','
				.$dat_est_pro[4].','.$dat_est_pro[3].','.$dat_est_pro[5].','.$dat_est_pro[6]
				.','.$dat_est_pro[7].','.$dat_est_pro[8].','.$dat_est_pro[9]);/*Oscar 2023 $dat_est_pro[8].','.$dat_est_pro[9]*/
			if($j<(mysql_num_rows($eje_3)-1)){
				echo utf8_decode(',');
			}else{
				echo utf8_decode(",{$dat_prod[4]}\n");//,{$dat_prod[4]}implementacion Oscar 2023/10/16
			}
		}//fin de for j
	}//fin de for i
?>