<?php
	if(include('../../../../../conect.php')){
	//	echo 'archivo de conexion encontrado';
	}else{
		die('No se encontró el archivo de conexion');
	}
	/*echo ($_POST['quita']) . "<br>";
	echo ($_POST['agrega']) . "<br>";
	echo ($_POST['products']);
	die( '' );*/
/*Implementación Oscar 27.02.2019 para modificar la ubicación de almacen de la sucursal*/
	if(isset($_POST['consulta']) && $_POST['consulta']!=''){
		$eje=mysql_query($_POST['consulta'])or die("Error al actualizar la ubicación de Almacén de este producto!!!\n\n".mysql_error()."\n\n".$_POST['consulta']);
		die('ok');
	}
/*Fin de cambio Oscar 27.02.2019*/
//verificacion de contrasena
	if(isset($_POST['fl'])&&$_POST['fl']=='verifica_pass'){
		//extract($_POST);
		$password=md5($_POST['clave']);
		$sql="SELECT s.id_encargado 
			FROM sys_sucursales s
			LEFT JOIN sys_users u on s.id_encargado=u.id_usuario
			WHERE s.id_sucursal = {$user_sucursal} 
			AND u.contrasena='{$_POST['password']}'";
		$eje=mysql_query($sql)or die("Error al verificar el password del encargado : " . mysql_error() );
		if(mysql_num_rows($eje)==1){
			die('ok');
		}else{
			die('no');
		}
	}

	//descarga de csv
	if( isset( $_POST['fl'] ) && $_POST['fl']== 'csv' ){
			//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="exportacion_ajuste_inventario_ceros.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		die('');
	}
	
//ajuste a CERO
	mysql_query("BEGIN");//declaramos el inicio de la transacción
	//echo 'Llega: '.$suc.'//'.$agrega.'//'.$quita;
//sacamos el id de almacen de acuerdo a la sucursal
	$id_almacen=$_POST['warehouse_id'];
	$suc = $_POST['store_id'];

//sumamos productos
	//echo "\nmovimientos de suma:\n";
	
	if( $_POST['sums'] != '' ){
		$sumas=explode('|', $_POST['sums'] );
		$id_mov_1="";
	//inserta cabecera del movimiento de almacén
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(9, $user_id,$suc,NOW(), NOW(), 'SUMA POR IGUALACION DE INVENTARIO A CEROS', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov_1=mysql_insert_id();
		for( $i=0; $i < sizeof( $sumas ); $i++ ){
			$aux=explode(',',$sumas[$i]);
		//inserta detalle de movimiento de almacen
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,
				cantidad_surtida,id_pedido_detalle,id_oc_detalle, id_proveedor_producto)
						VALUES($id_mov_1,$aux[0],$aux[1],$aux[1],-1,-1, NULL)";
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
	if($_POST['substracts'] != '' ){
		$restas=explode( '|',$_POST['substracts'] );
		$mA="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, 
							id_maquila, id_transferencia, id_almacen)
					VALUES(8, $user_id,$suc,NOW(), NOW(), 'RESTA POR IGUALACION DE INVENTARIO A CEROS', -1, -1, '', -1,-1,$id_almacen)";
		$ins=mysql_query($mA);
		if(!$ins){
			die('Error al insertar movimiento de almacen!!!'."\n".$mA."\n".$mA);
		}
		$id_mov_2=mysql_insert_id();
		for($i=0;$i< sizeof( $restas );$i++){
			$aux=explode(',',$restas[$i]);
			$det="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,
				cantidad_surtida,id_pedido_detalle,id_oc_detalle, id_proveedor_producto)
						VALUES($id_mov_2,$aux[0],$aux[1],$aux[1],-1,-1, NULL)";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		}
	}
	if($_POST['sum_details'] != '' ){
		$sumas=explode( '|',$_POST['sum_details'] );
		for($i=0;$i< sizeof( $sumas );$i++){
			$aux=explode(',',$sumas[$i]);
			$det = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle,
				    id_proveedor_producto, cantidad, fecha_registro, id_sucursal, status_agrupacion, id_tipo_movimiento, id_almacen )
				    VALUES( NULL, NULL, {$aux[1]}, {$aux[0]}, NOW(), {$suc}, -1, 9, {$id_almacen}  );";
			$insDet=mysql_query($det);
			if(!$insDet){
				die('Error al insertar detalles en suma!!!'."\n".mysql_error()."\n".$det);
			}
		}
	}
	if($_POST['subtract_details'] != '' ){
		$restas=explode( '|',$_POST['subtract_details'] );
		for($i=0;$i< sizeof( $restas );$i++){
			$aux=explode(',',$restas[$i]);
			$det = "INSERT INTO ec_movimiento_detalle_proveedor_producto ( id_movimiento_detalle_proveedor_producto, id_movimiento_almacen_detalle,
				    id_proveedor_producto, cantidad, fecha_registro, id_sucursal, status_agrupacion, id_tipo_movimiento, id_almacen )
				    VALUES( NULL, NULL, {$aux[1]}, {$aux[0]}, NOW(), {$suc}, -1, 8, {$id_almacen}  );";
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
	if( $id_mov_1!= '' || $id_mov_2 != '' ){
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
	}
//autorizamos la transacción
	mysql_query("COMMIT");

	echo 'ok|'.$r[0];//regresamos ok y folio del ajuste de inventario
/*fin del cambio 17.08.2018*/
?>