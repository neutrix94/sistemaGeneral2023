<?php
	include("../../../../conectMin.php");

//sacamos la fecha y hora en que se abrió la pantalla de ajuste de inventario
	$arr=explode("~",$_POST['productosEnTemporal']);
//die('ok|' . $_POST['productosEnTemporal']);
	$nuevo_inv="";
	$h_inicio=$_POST['horaDeInicio'];
	//die('hora de inicio: '.$h_inicio);
	$fecha=date("Y-m-d");//declaramos la fecha
	$sucursal=$_POST['suc'];
//buscamos usuarios que solicitaron un producto en específico en temporal
	if($_POST['fl']==2){
		$id=$_POST['id'];
	//armamos consulta
		$sql="SELECT p.nombre as nombre_producto,
					CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) AS usuario,
					mtd.cantidad AS cantidad_en_temporal,
					REPLACE(mt.hora,'$fecha','') AS detalle_del_movimiento
				FROM ec_movimiento_temporal_detalle mtd
				LEFT JOIN ec_movimiento_temporal mt ON mtd.id_movimiento_temporal=mt.id_movimiento_temporal
				LEFT JOIN ec_productos p ON mtd.id_producto=p.id_productos
				LEFT JOIN sys_users u ON mt.id_usuario=u.id_usuario
				WHERE p.id_productos=$id
				AND mt.hora>='$fecha $h_inicio' AND mt.id_sucursal='$sucursal'";
				//die($sql);

		$eje=mysql_query($sql)or die("Error al consultar usuarios con producto en temporal!!!\n\n".$sql."\n\n".mysql_error());
	//regresamos tabla con información del temporal del producto
		$respuesta='<div style="width:80%;height:200px;border:1px solid;overflow:auto;"><table id="info_users_temp" style="background:white;width:100%;">';
			$respuesta.='<tr style="background:rgba(225,0,0,.6);">';
				$respuesta.='<th width="50%">Vendedor(a)</th>';
				$respuesta.='<th width="20%">Cantidad</th>';
				$respuesta.='<th width="30%">Hora</th>';
			$respuesta.='</tr>';

		$nombreProd="";
		while($r=mysql_fetch_row($eje)){
			$nombreProd=$r[0];
			$respuesta.='<tr>';
				$respuesta.='<td align="left">'.$r[1].'</td>';
				$respuesta.='<td align="center">'.$r[2].'</td>';
				$respuesta.='<td align="center">'.$r[3].'</td>';
			$respuesta.='</tr>';
		}
		$respuesta.='</table></div>';//cerramos el div y tabla
		die('ok|'.'<button type="button" style="float:right;" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button>'.
			'<p style="color:white;font-size:20px;"><b>El producto '.
			$nombreProd.' fue capturado para su venta, en caso de que el Vendedor ya lo haya solicitado; sume estas piezas en el conteo Físico</b></p><br>'.$respuesta);
	}

//buscamos productos en temporal
	/*$sql="SELECT mdt.id_producto,
				SUM(-mdt.cantidad) 
			FROM ec_movimiento_temporal_detalle mdt 
			LEFT JOIN ec_movimiento_temporal mt ON mdt.id_movimiento_temporal=mt.id_movimiento_temporal
			WHERE mt.id_sucursal=$user_sucursal
			AND mt.hora>='$fecha $h_inicio'/*con esta condición ya solo toma los temporales que están por encima de la hora en la que se abrió la pantalla*
			GROUP BY mdt.id_producto";*/

/*implementación Oscar 14.09.2018*/
	$sql="SELECT p.id_productos,
			SUM(IF(mdt.id_movimiento_temporal IS NULL OR mt.id_sucursal!=$sucursal OR mt.hora<'$fecha $h_inicio',0,-mdt.cantidad)) 
			FROM ec_productos p
            LEFT JOIN ec_movimiento_temporal_detalle mdt ON p.id_productos=mdt.id_producto
			LEFT JOIN ec_movimiento_temporal mt ON mdt.id_movimiento_temporal=mt.id_movimiento_temporal
			WHERE 1
			GROUP BY p.id_productos";
//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar movimientos de almacén temporales!!!\n\n".$sql."\n\n".mysql_error());
	$datosTemp="";
	while($r=mysql_fetch_row($eje)){
		$datosTemp.=$r[0]."~".$r[1]."°";
	}

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
									WHERE id_sucursal='$sucursal' 
									AND es_almacen=1) 
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento 
			WHERE md.id_producto IN(SELECT md1.id_producto 
											FROM ec_movimiento_detalle md1 
											LEFT JOIN ec_movimiento_almacen ma1 ON md1.id_movimiento=ma1.id_movimiento_almacen 
											WHERE ma1.hora>='$h_inicio' and ma1.fecha>='$fecha'
									)
			GROUP by p.id_productos";
	//die($sql);
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