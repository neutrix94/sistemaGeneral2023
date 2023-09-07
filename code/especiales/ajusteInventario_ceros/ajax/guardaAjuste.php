<?php
	if(include('../../../../conect.php')){
	//	echo 'archivo de conexion encontrado';
	}else{
		die('No se encontró el archivo de conexion');
	}
	extract($_POST);
	//echo 'Llega: '.$suc.'//'.$agrega.'//'.$quita;
//sacamos el id de almacen de acuerdo a la sucursal
/*	$al="SELECT id_almacen from ec_almacen WHERE id_sucursal=$suc ORDER BY prioridad ASC";
	$ejA=mysql_query($al);
	if(!$ejA){
		die('Error al consultar el id de almacen principal de la sucursal'.\n.mysql_error());
	}//

	$rw=mysql_fetch_row($ejA);
*/	$id_almacen=$almacen;
//sumamos productos
	//echo "\nmovimientos de suma:\n";
	$sumas=explode('|',$agrega);
	$suma=$sumas[0];
	if($suma=='0'){
		//echo 'sin accion';
	}else{
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(9, $user_id,$suc,NOW(), NOW(), 'SUMA POR AJUSTE DE INVENTARIO', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov=mysql_insert_id();
		for($i=1;$i<=$suma;$i++){
			$aux=explode(',',$sumas[$i]);
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
						VALUES($id_mov,$aux[1],$aux[0],$aux[0],-1,-1)";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		}
	}
	echo 'ok';
	$id_mov='';
//////////////////////tipo_movimiento=8 para restar al almacen
	//echo "\nMovimientos de Resta\n";
	$restas=explode('|',$quita);
	$resta=$restas[0];
	if($resta=='0'){
		//echo 'sin accion';
	}else{
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(8, $user_id,$suc,NOW(), NOW(), 'RESTA POR AJUSTE DE INVENTARIO', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov=mysql_insert_id();
		for($i=1;$i<=$resta;$i++){
			$aux=explode(',',$restas[$i]);
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
						VALUES($id_mov,$aux[1],$aux[0],$aux[0],-1,-1)";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		}
	}
	echo 'ok';
?>