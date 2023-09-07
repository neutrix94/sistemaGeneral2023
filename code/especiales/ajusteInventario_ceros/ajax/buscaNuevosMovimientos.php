<?php
	include("../../../../conectMin.php");

//buscamos usuarios que solicitaron un producto en específico en temporal
	if($_POST['fl']==2){
		$id=$_POST['id'];
	//armamos consulta
		$sql="SELECT p.nombre as nombre_producto,
					CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) AS usuario,
					mtd.cantidad AS cantidad_en_temporal,
					CONCAT(mt.fecha,' ',mt.hora) AS detalle_del_movimiento
				FROM ec_movimiento_temporal_detalle mtd
				LEFT JOIN ec_movimiento_temporal mt ON mtd.id_movimiento_temporal=mt.id_movimiento_temporal
				LEFT JOIN ec_productos p ON mtd.id_producto=p.id_productos
				LEFT JOIN sys_users u ON mt.id_usuario=u.id_usuario
				WHERE p.id_productos=$id";
		$eje=mysql_query($sql)or die("Error al consultar usuarios con producto en temporal!!!\n\n".$sql."\n\n".mysql_error());
	//regresamos tabla con información del temporal del producto
		$respuesta='<table id="info_users_temp" style="background:white;">';
			$respuesta.='<tr style="background:rgba(225,0,0,.6);">';
				$respuesta.='<th>Vendedor(a)</th>';
				$respuesta.='<th>Cantidad solicitada</th>';
				$respuesta.='<th>Fecha y hora</th>';
			$respuesta.='</tr>';

		$nombreProd="";
		while($r=mysql_fetch_row($eje)){
			$nombreProd=$r[0];
			$respuesta.='<tr>';
				$respuesta.='<td>'.$r[1].'</td>';
				$respuesta.='<td>'.$r[2].'</td>';
				$respuesta.='<td>'.$r[3].'</td>';
			$respuesta.='</tr>';
		}
		$respuesta.='</table>';
		die('ok|'.'<button type="button" style="float:right;" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button><p style="color:white;"><b>El producto '.
			$nombreProd.' fue solicitado por vendedores, antes de realizar el ajuste para este producto verifique si ya le fueron entregados a los vendedores las siguientes piezas:</b></p><br>'.$respuesta);
	}
//buscamos productos en temporal
	$sql="SELECT mdt.id_producto,SUM(-mdt.cantidad) 
			FROM ec_movimiento_temporal_detalle mdt 
			LEFT JOIN ec_movimiento_temporal mt ON mdt.id_movimiento_temporal=mt.id_movimiento_temporal
			WHERE mt.id_sucursal=$user_sucursal
			GROUP BY mdt.id_producto";

	$eje=mysql_query($sql)or die("Error al consultar movimientos de almacén temporales!!!\n\n".$sql."\n\n".mysql_error());
	$datosTemp="";
	while($r=mysql_fetch_row($eje)){
		$datosTemp.=$r[0]."~".$r[1]."°";
	}

	$arr=explode("~",$_POST['productosEnTemporal']);
	$nuevo_inv="";
	$fecha=date("Y-m-d");
	$h_inicio=$_POST['horaDeInicio'];
	//die($fecha);
	//for($i=0;$i<sizeof($arr)-1;$i++){
		/*$sql="SELECT 
				IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta)))
			FROM  ec_movimiento_detalle md
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_almacen=(SELECT id_almacen FROM ec_almacen WHERE id_sucursal='$user_sucursal' AND es_almacen=1)
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			WHERE md.id_producto=$arr[$i]";
		*/
		$sql="SELECT 
				p.id_productos, 
				IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))) 
			FROM ec_productos p 
			LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto 
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen 
				AND ma.id_almacen=(SELECT id_almacen 
									FROM ec_almacen 
									WHERE id_sucursal='$user_sucursal' 
									AND es_almacen=1) 
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento 
			WHERE md.id_producto IN(SELECT md1.id_producto 
											FROM ec_movimiento_detalle md1 
											LEFT JOIN ec_movimiento_almacen ma1 ON md1.id_movimiento=ma1.id_movimiento_almacen 
											WHERE ma1.hora>='$h_inicio' and ma1.fecha>='$fecha'
									)
			GROUP by id_producto";
		//	die($sql);
		$eje=mysql_query($sql)or die("Error al consultar el nuevo inventario de productos con registro temporal!!!\n\n".$sql."\n\n".mysql_error());
		while($rw=mysql_fetch_row($eje)){
			$nuevo_inv.=$rw[0]."~".$rw[1]."°";
		}
	
		//die($sql);		
//	}//fin de for i

	die('ok|'.$datosTemp."|".$nuevo_inv);
//buscamos movimientos realizados durante el inventario
	//$sql="SELECT md.id_productos,SUM(md.cantidad)";

?>