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
	$store_id = $_POST['suc'];
	$warehouse_id = $_POST['alm'];
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
//sumamos productos
	//echo "\nmovimientos de suma:\n";
	$sumas=explode('|',$agrega);
	$suma=$sumas[0];
	$id_mov_1="";
	if($suma=='0'){
		//echo 'sin accion';
	}else{
	//inserta cabecera del movimiento de almacén
		/*$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(9, $user_id, {$store_id}, NOW(), NOW(), 'SUMA POR AJUSTE DE INVENTARIO PARA IGUALAR INVENTARIOS', -1, -1, '', -1,-1,{$warehouse_id})";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov_1=mysql_insert_id();*/
		$mA = "CALL spMovimientoAlmacen_inserta( {$user_id}, 'SUMA POR AJUSTE DE INVENTARIO PARA IGUALAR INVENTARIOS', {$store_id}, {$warehouse_id}, 9, -1, -1, -1, -1, 18, NULL )";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
	//consulta el id insertado
		$sql = "SELECT MAX( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen";
		$stm = mysql_query( $sql ) or die( "Error al consultar el id de movimiento por SUMA : {$sql}" );
		$row = mysql_fetch_assoc( $stm );
		$id_mov_1 = $row['id_movimiento_almacen'];
		for($i=1;$i<=$suma;$i++){
			$aux=explode(',',$sumas[$i]);
		//inserta detalle de movimiento de almacen
			/*$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,
				cantidad_surtida,id_pedido_detalle,id_oc_detalle, id_proveedor_producto)
						VALUES($id_mov_1,$aux[1],$aux[0],$aux[0],-1,-1, null)";*/
			$det = "CALL spMovimientoAlmacenDetalle_inserta ( {$id_mov_1}, {$aux[1]}, {$aux[0]}, {$aux[0]}, 
				-1, -1, NULL, 18, NULL )";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		//inserta detalle de productos de movimiento de almacen
			$sql = "";
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
		/*$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(8, $user_id, {$store_id}, NOW(), NOW(), 'RESTA POR AJUSTE DE INVENTARIO PARA IGUALAR INVENTARIOS', -1, -1, '', -1, -1, {$warehouse_id})";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov_2=mysql_insert_id();*/
		
		$mA = "CALL spMovimientoAlmacen_inserta( {$user_id}, 'RESTA POR AJUSTE DE INVENTARIO PARA IGUALAR INVENTARIOS', {$store_id}, {$warehouse_id}, 8, -1, -1, -1, -1, 18, NULL )";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
	//consulta el id insertado
		$sql = "SELECT MAX( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen";
		$stm = mysql_query( $sql ) or die( "Error al consultar el id de movimiento por SUMA : {$sql}" );
		$row = mysql_fetch_assoc( $stm );
		$id_mov_2 = $row['id_movimiento_almacen'];
		for($i=1;$i<=$resta;$i++){
			$aux=explode(',',$restas[$i]);
			/*$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,
				cantidad_surtida,id_pedido_detalle,id_oc_detalle, id_proveedor_producto)
						VALUES($id_mov_2,$aux[1],$aux[0],$aux[0],-1,-1, null)";*/
			$det = "CALL spMovimientoAlmacenDetalle_inserta ( {$id_mov_2}, {$aux[1]}, {$aux[0]}, {$aux[0]}, 
				-1, -1, NULL, 18, NULL )";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		}
	}

/*implementación de Oscar 17.08.2018 para el folio de ajuste de inventario*/
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
	$eje=mysql_query($sql)or die("Error al actualizar los folios de Ajuste de inventario!!!\n\n".$sql."\n\n".mysql_error());

	/*if( $sin_movimientos != '' ){
		$products_providers_ids = str_replace('|', ',', $sin_movimientos );
		$sql = "UPDATE ec_conteo_inventario_tmp
					SET ya_realizo_movimientos = '1'
				WHERE id_proveedor_producto IN ( {$products_providers_ids} )
				AND id_almacen = {$id_almacen}";
		$eje = mysql_query( $sql )or die("Error al actualizar registros  sin movimientos!!!\n\n".$sql."\n\n".mysql_error());
	
	}*/
//autoriza la transacción
	mysql_query("COMMIT");

	echo 'ok|'.$r[0];//regresamos ok y folio del ajuste de inventario
/*fin del cambio 17.08.2018*/
?>