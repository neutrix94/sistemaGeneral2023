<?php
	include("../../../../../conectMin.php");
//sacamos la fecha y hora en que se abrió la pantalla de ajuste de inventario
	$arr=explode("~",$_POST['productosEnTemporal']);
	$nuevo_inv="";
	$h_inicio=$_POST['horaDeInicio'];
	//die('hora de inicio: '.$h_inicio);
	$sql="SELECT DATE_FORMAT(current_date(),'%Y-%m-%d')";
	$eje_fcha=mysql_query($sql)or die("Error al sacar la fecha del dia actual!!!");
	//$fecha=date("Y-m-d");//declaramos la fecha
	$r_fcha=mysql_fetch_row($eje_fcha);
	$fecha=$r_fcha[0];

	$sucursal=$_POST['suc'];

//buscamos usuarios que solicitaron un producto en específico en temporal
	if($_POST['fl']==2){
		$id=$_POST['id'];
		$numero=$_POST['celda'];
/*Checanmos si hay alguna transferencia en resolucion que contenga a esta producto*/
		$sql="SELECT
					p.nombre,
					t.folio, 
					tp.cantidad,
					t.ultima_actualizacion,
					s.nombre
			/*/SUM(IF(t.id_transferencia IS NULL OR t.id_sucursal!=$sucursal OR t.id_estado!=3 OR t.ultima_actualizacion<'$fecha $h_inicio',0,-tp.cantidad)) */
			FROM ec_productos p
            LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN sys_sucursales s ON t.id_sucursal_destino=s.id_sucursal
			WHERE t.id_estado=5 /*AND t.ultima_actualizacion>='$fecha $h_inicio'*/
			AND tp.id_producto_or=$id
			GROUP BY t.id_transferencia";

		$eje=mysql_query($sql)or die("Error al consultar usuarios con producto en temporal!!!\n\n".$sql."\n\n".mysql_error());
	if(mysql_num_rows($eje)>0){
	//regresamos tabla con información del temporal del producto
		$respuesta='<div style="width:80%;height:200px;border:1px solid;overflow:auto;">';
		//$repuesta.='<button type="button" style="float:right;" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button>';
		$respuesta.='<p style="color:white;font-size:20px;"><b>El producto *** fue puesto en proceso de resolucion en la(s) siguiente(s) transferencia(s);';
		$respuesta.='Primero se tiene que completar la resolucion para continuar con el proceso</b></p><br>';
		$respuesta.='<table id="info_users_temp" style="background:white;width:100%;">';
			$respuesta.='<tr style="background:rgba(225,0,0,.6);">';
				$respuesta.='<th width="35%">Folio Transferencia</th>';
				$respuesta.='<th width="20%">Destino</th>';
				$respuesta.='<th width="20%">Cantidad</th>';
				$respuesta.='<th width="25%">Hora</th>';
			$respuesta.='</tr>';

		$nombreProd="";
		while($r=mysql_fetch_row($eje)){
			$nombreProd=$r[0];
			$respuesta.='<tr>';
				$respuesta.='<td align="left">'.$r[1].'</td>';
				$respuesta.='<td align="center">'.$r[4].'</td>';
				$respuesta.='<td align="center">'.$r[2].'</td>';
				$respuesta.='<td align="center">'.$r[3].'</td>';
			$respuesta.='</tr>';
			$nombreProd=$r[0];
		}
		$respuesta.='</table></div>';//cerramos el div y tabla
	/*creamos el botón para bloquear el cambio del producto*/
		$respuesta.='<p align="center"><button style="padding:15px;font-size:25px;" onclick="restringe_modificacion('.$numero.');"><b>Aceptar</b></button></p>';
		$respuesta=str_replace("***", $nombreProd,$respuesta);
	/**/
		die('ok|'.$respuesta);
	}
/*Aqui termina el Proceso para ver si alguna transferencia en Resolución contiene al producto*/

/*Enlistamos los productos*/
		$sql="SELECT
					p.nombre,
					t.folio, 
					tp.cantidad,
					t.ultima_actualizacion,
					s.nombre,
					t.observaciones
			/*/SUM(IF(t.id_transferencia IS NULL OR t.id_sucursal!=$sucursal OR t.id_estado!=3 OR t.ultima_actualizacion<'$fecha $h_inicio',0,-tp.cantidad)) */
			FROM ec_productos p
            LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN sys_sucursales s ON t.id_sucursal_destino=s.id_sucursal
			WHERE t.id_estado=3 /*AND t.ultima_actualizacion>='$fecha $h_inicio'*/
			AND tp.id_producto_or=$id
			GROUP BY t.id_transferencia";

		$eje=mysql_query($sql)or die("Error al consultar usuarios con producto en temporal!!!\n\n".$sql."\n\n".mysql_error());
	//regresamos tabla con información del temporal del producto
		$respuesta_1='<button type="button" style="float:right;" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button>';
		$respuesta_1.='<p style="color:white;font-size:20px;"><b>El producto *** fue puesto en proceso de surtimiento en la(s) sigueinte(s)';
		$respuesta_1.=' transferncia(s); verifique si ya fue tomado de su ubicación en la Bodega</b></p><br>';
		$respuesta_1.='<div style="width:80%;height:150px;border:1px solid;overflow:auto;"><table id="info_users_temp" style="background:white;width:100%;">';
			$respuesta_1.='<tr style="background:rgba(225,0,0,.6);">';
				$respuesta_1.='<th width="20%">Folio Transferencia</th>';
				$respuesta_1.='<th width="20%">Surtida por</th>';
				$respuesta_1.='<th width="20%">Destino</th>';
				$respuesta_1.='<th width="20%">Cantidad</th>';
				$respuesta_1.='<th width="20%">Hora</th>';
			$respuesta_1.='</tr>';

		$nombreProd="";
		while($r=mysql_fetch_row($eje)){
/*sacamos nombres de quien surtió transferencias*/
		$nombres_surtir=explode('-Surtida por: ',$r[5]);
		$nombres_surtir_aux=explode(' a las ',$nombres_surtir[1]);
		$nombres_surtir=$nombres_surtir_aux[0];
			$nombreProd=$r[0];
			$respuesta_1.='<tr>';
				$respuesta_1.='<td align="left">'.$r[1].'</td>';
				$respuesta_1.='<td align="left">'.$nombres_surtir.'</td>';
				$respuesta_1.='<td align="center">'.$r[4].'</td>';
				$respuesta_1.='<td align="center">'.$r[2].'</td>';
				$respuesta_1.='<td align="center">'.$r[3].'</td>';
			$respuesta_1.='</tr>';
		}
		$respuesta_1.='</table></div>';//cerramos el div y tabla

/*Productos que estan en salida de Transferencia*/
		$sql="SELECT
					p.nombre,
					t.folio, 
					tp.cantidad,
					t.ultima_actualizacion,
					s.nombre,
					t.observaciones
			/*/SUM(IF(t.id_transferencia IS NULL OR t.id_sucursal!=$sucursal OR t.id_estado!=3 OR t.ultima_actualizacion<'$fecha $h_inicio',0,-tp.cantidad)) */
			FROM ec_productos p
            LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN sys_sucursales s ON t.id_sucursal_destino=s.id_sucursal
			WHERE t.id_estado=4 /*AND t.ultima_actualizacion>='$fecha $h_inicio'*/
			AND tp.id_producto_or=$id
			GROUP BY t.id_transferencia";

		$eje=mysql_query($sql)or die("Error al consultar usuarios con producto en temporal!!!\n\n".$sql."\n\n".mysql_error());
	//regresamos tabla con información del temporal del producto
	//	$respuesta_2='<button type="button" style="float:right;" onclick="document.getElementById(\'emergente\').style.display=\'none\';">X</button>';
		$respuesta_2='<p style="color:white;font-size:20px;"><b>El producto *** fue puesto en proceso de salida en la(s) siguiente(s)';
			$respuesta_2.=' transferncia(s); Verifique en las hojas de transferencia si el producto fue tomado de su ubicación en la Bodega y si no fue tomado descuentelo del recuento físico</b></p><br>';
		if(mysql_num_rows($eje)>0){
				$respuesta_2.='<div style="width:80%;height:150px;border:1px solid;overflow:auto;"><table id="info_users_temp" style="background:white;width:100%;">';
				$respuesta_2.='<tr style="background:rgba(225,0,0,.6);">';
				$respuesta_2.='<th width="20%">Folio Transferencia</th>';
				$respuesta_2.='<th width="20%">En salida por</th>';
				$respuesta_2.='<th width="20%">Destino</th>';
				$respuesta_2.='<th width="20%">Cantidad</th>';
				$respuesta_2.='<th width="25%">Hora</th>';
			$respuesta_2.='</tr>';

			$nombreProd="";
			while($r=mysql_fetch_row($eje)){
				$nombres_surtir=explode('-Puesta en salida por: ',$r[5]);
			//	die($nombres_surtir[1]);
				$nombres_surtir_aux=explode(' a las ',$nombres_surtir[1]);
				$nombres_surtir=$nombres_surtir_aux[0];
				$nombreProd=$r[0];
				$respuesta_2.='<tr>';
					$respuesta_2.='<td align="left">'.$r[1].'</td>';
					$respuesta_2.='<td align="left">'.$nombres_surtir.'</td>';
					$respuesta_2.='<td align="center">'.$r[4].'</td>';
					$respuesta_2.='<td align="center">'.$r[2].'</td>';
					$respuesta_2.='<td align="center">'.$r[3].'</td>';
				$respuesta_2.='</tr>';
				$nombreProd=$r[0];
			}
			$respuesta_2.='</table></div>';//cerramos el div y tabla
			$respuesta_1=str_replace("***", $nombreProd,$respuesta_1);
			$respuesta_2=str_replace("***", $nombreProd,$respuesta_2);
		}
		 echo 'ok|'.$respuesta_1.''.$respuesta_2;
	}/*aqui acaba el if==2*/



	$sql="SELECT p.id_productos,
			SUM(IF(t.id_transferencia IS NULL OR t.id_sucursal_origen!=1 /*OR t.ultima_actualizacion<'$fecha $h_inicio'*/,0, IF(t.id_estado=3 OR t.id_estado=4 OR t.id_estado=5,-tp.cantidad,0) )) 
			FROM ec_productos p
            LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			WHERE 1
			GROUP BY p.id_productos";
//die($sql);
	$eje=mysql_query($sql)or die("Error al consultar productos en Surtimiento de transferncia!!!\n\n".$sql."\n\n".mysql_error());
	$datosTemp="";
	while($r=mysql_fetch_row($eje)){
		$datosTemp.=$r[0]."~".$r[1]."___";
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
/*
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
			GROUP by p.id_productos";*/
			$sql="SELECT
						aux1.id_productos,
						( aux1.inv+IF(aux1.cantidad_en_transf IS NULL,0,aux1.cantidad_en_transf) ) as Inventario
					FROM(
						SELECT
						aux.id_productos,
						aux.nombre,
						aux.inv,
						aux.orden_lista,
						aux.ubic,
						aux.clave,
						SUM(IF(t.id_transferencia IS NULL,0,IF(t.id_estado=2 OR t.id_estado=3,tp.cantidad,0))) as cantidad_en_transf	 
					FROM(
						SELECT
								p.id_productos,
								p.nombre,
								IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))) AS inv,
								p.orden_lista,
						/*Implementación Oscar 27.02.2019 para agregar ubicación de almacén*/
								/*IF(1=1,p.ubicacion_almacen,sp.ubicacion_almacen_sucursal)as ubic,*/
								p.ubicacion_almacen as ubic,
								p.clave
						/*Fin de cambio Oscar 27.02.2019*/
							FROM ec_productos p /*ON i.id_producto=p.id_productos*/
						/**/
							LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal IN(1) AND sp.estado_suc=1
						/**/
							LEFT JOIN ec_movimiento_detalle md ON sp.id_producto=md.id_producto
							LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_almacen=1
							LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
							WHERE p.id_productos>2 AND p.es_maquilado=0 AND sp.es_externo=0
							GROUP BY p.id_productos ORDER BY p.orden_lista ASC
					)aux
					LEFT JOIN ec_productos prds ON prds.id_productos=aux.id_productos
					LEFT JOIN ec_transferencia_productos tp ON tp.id_producto_or=prds.id_productos
					LEFT JOIN ec_transferencias t ON t.id_transferencia=tp.id_transferencia
					/*WHERE 1 AND t.id_transferencia!=-1*/
					GROUP BY aux.id_productos ORDER BY aux.orden_lista ASC
				)aux1";
			$ids_prds='AND p.id_productos IN(';
			for($i=0;$i<sizeof($arr);$i++){
				if($arr[$i]!=''){
					$ids_prds.=$arr[$i].',';
				}
			}
//die($ids_prds);
			if($ids_prds=='AND p.id_productos IN('){
				$ids_prds.='0';
			}
			$ids_prds.=')';
			$ids_prod=str_replace(",)", ")", $ids_prds);
			//die($ids_prod);
			$sql=str_replace("WHERE p.id_productos>2", "WHERE p.id_productos>2 ".$ids_prod." ",$sql);
	//die($sql);
		$eje=mysql_query($sql)or die("Error al consultar el nuevo inventario de productos con registro temporal!!!\n\n".mysql_error()."\n\n".$sql);
		while($rw=mysql_fetch_row($eje)){
			$nuevo_inv.=$rw[0]."~".$rw[1]."___";
		}
		//die($sql);		
//	}//fin de for i

	die('ok|'.$datosTemp."|".$nuevo_inv.'|ok');
//buscamos movimientos realizados durante el inventario
	//$sql="SELECT md.id_productos,SUM(md.cantidad)";

?>