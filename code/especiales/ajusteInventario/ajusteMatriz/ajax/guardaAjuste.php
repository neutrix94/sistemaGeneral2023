<?php
	if(include('../../../../../conect.php')){
	//	echo 'archivo de conexion encontrado';
	}else{
		die('No se encontró el archivo de conexion');
	}

/*Implementación Oscar 27.02.2019 para modificar la ubicación de almacen de la sucursal*/
	if(isset($_POST['consulta']) && $_POST['consulta']!=''){
		$eje=mysql_query($_POST['consulta'])or die("Error al actualizar la ubicación de Almacén de este producto!!!\n\n".mysql_error()."\n\n".$_POST['consulta']);
		die('ok');
	}
/*Fin de cambio Oscar 27.02.2019*/

	extract($_POST);
	$password=md5($_POST['clave']);
	if(isset($_POST['fl'])&&$_POST['fl']=='verifica_pass'){
		$sql="SELECT s.id_encargado 
			FROM sys_sucursales s
			LEFT JOIN sys_users u on s.id_encargado=u.id_usuario
			WHERE s.id_sucursal=$user_sucursal and u.contrasena='$password'";
		$eje=mysql_query($sql)or die("Error al verificar el password del encargado");
		if(mysql_num_rows($eje)==1){
			die('ok');
		}else{
			die('no');
		}
	}
	
//declaramos el inicio de la transacción
	mysql_query("BEGIN");
	//echo 'Llega: '.$suc.'//'.$agrega.'//'.$quita;
//sacamos el id de almacen de acuerdo a la sucursal
	$al="SELECT id_almacen from ec_almacen WHERE id_sucursal=$suc ORDER BY prioridad ASC";
	$ejA=mysql_query($al);
	if(!$ejA){
		die('Error al consultar el id de almacen principal de la sucursal'.\n.mysql_error());
	}//

	$rw=mysql_fetch_row($ejA);
	$id_almacen=$rw[0];
//sumamos productos
	//echo "\nmovimientos de suma:\n";
	$sumas=explode('|',$agrega);
	$suma=$sumas[0];
	$id_mov_1="";
	if($suma=='0'){
		//echo 'sin accion';
	}else{
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(9, $user_id,$suc,NOW(), NOW(), '/*SUMA POR AJUSTE DE INVENTARIO*/', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".mysql_error());
		}
		$id_mov_1=mysql_insert_id();
		for($i=1;$i<=$suma;$i++){
			$aux=explode(',',$sumas[$i]);
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
						VALUES($id_mov_1,$aux[1],$aux[0],$aux[0],-1,-1)";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		/*actualizamos el campo de ajuste_realizado a 1 para que ya no aparezca en el ajuste e inventario filtrado por stock bajo*/
			$sql="UPDATE sys_sucursales_producto SET ajuste_realizado=1 WHERE id_producto={$aux[1]}";
			$actAjuste=mysql_query($sql);
			if(!$actAjuste){
				die('Error poner el producto en ajuste de inventario realizado en la tabla de sucursales producto!!!'."\n".mysql_error()."\n".$sql);
			}
		/**/
		}//fin de for $i
	}
/*implementación de Oscar 17.08.2018 para el folio de ajuste de invenmtario*/
	if($id_mov_1!=""){
		$suma_ajuste="S".$id_mov_1;
	}else{
		$suma_ajuste='S0';
	}
/*fin del cambio 17.08.2018*/
	echo 'ok|';
	$id_mov_2='';
//////////////////////tipo_movimiento=8 para restar al almacen
	//echo "\nMovimientos de Resta\n";
	$restas=explode('|',$quita);
	$resta=$restas[0];
	if($resta=='0'){
		//echo 'sin accion';
	}else{
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(8, $user_id,$suc,NOW(), NOW(), '/*RESTA POR AJUSTE DE INVENTARIO*/', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".mysql_error());
		}
		$id_mov_2=mysql_insert_id();
		for($i=1;$i<=$resta;$i++){
			$aux=explode(',',$restas[$i]);
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
						VALUES($id_mov_2,$aux[1],$aux[0],$aux[0],-1,-1)";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		/*actualizamos el campo de ajuste_realizado a 1 para que ya no aparezca en el ajuste e inventario filtrado por stock bajo*/
			$sql="UPDATE sys_sucursales_producto SET ajuste_realizado=1 WHERE id_producto={$aux[1]}";
			$actAjuste=mysql_query($sql);
			if(!$actAjuste){
				die('Error poner el producto en ajuste de inventario realizado en la tabla de sucursales producto!!!'."\n".mysql_error()."\n".$sql);
			}
		/**/
		}
	}

/*implementación de Oscar 17.08.2018 para el folio de ajuste de invenmtario*/
	if($id_mov_2!=""){
		$resta_ajuste="R".$id_mov_2;
	}else{
		$resta_ajuste='R0';
	}
//creamos el folio
	$sql="SELECT CONCAT(prefijo,'$suma_ajuste','$resta_ajuste') FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar datos para generar el folio del Ajuste de Inventario!!!\n\n".$sql."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
//actualizamos el folio de los movimientos de almacén
	$sql="UPDATE ec_movimiento_almacen SET observaciones='$r[0]' WHERE";
	if($id_mov_1!=''&&$id_mov_2==''){
		$sql.=" id_movimiento_almacen=".$id_mov_1;
	}
	if($id_mov_2!=''&&$id_mov_1==''){
		$sql.=" id_movimiento_almacen=".$id_mov_2;
	}
	if($id_mov_1!=''&&$id_mov_2!=''){
		$sql.=" id_movimiento_almacen IN(".$id_mov_1.",".$id_mov_2.")";
	}
	if($id_mov_1!='' || $id_mov_2!=''){
		$eje=mysql_query($sql)or die("Error al actualizar los folios de Ajuste de inventario!!!\n\n".$sql."\n\n".mysql_error());
	}
//autorizamos la transacción
	mysql_query("COMMIT");

	echo 'ok|'.$r[0];//regresamos ok y folio del ajuste de inventario
/*fin del cambio 17.08.2018*/
?>