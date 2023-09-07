<?php
	$flag=$_POST['fl'];
//descarga del formato
	if($flag=='formato'){
	//recibimos datos
		$info='Id Producto,Orden Lista,Codigo Proveedor,Producto,Precio,Cantidad,Piezas por caja,Id de Proveedor';
	//creamos el nombre del archivo
		$nombre="formato_detalle_orden_compra_limpio.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}

	if($flag=='importa_detalle_oc'){
		include("../../conectMin.php");
		$id_oc=$_POST['oc'];
		$id_proveedor=$_POST['id_prov'];
		//die($_POST['datos']);
		$dats=explode("|", $_POST['datos']);
		
		mysql_query("BEGIN");//iniciamos la transaccion
		
		for($i=0;$i<sizeof($dats);$i++){
			$tmp=explode("~", $dats[$i]);
			if($tmp[0]!=''){
		//consultamos si el producto ya existe para el proveedor
			$sql="SELECT id_proveedor_producto FROM ec_proveedor_producto WHERE id_proveedor=$id_proveedor AND id_producto=$tmp[0] LIMIT 1";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacion
				die("Error al comprobar si el producto ya existía para este proveedor!!!\n".$error);
			}
			
			if(mysql_num_rows($eje)==1){
			//actualizamos el registro con los nuevos datos
				$r=mysql_fetch_row($eje);
				$sql="UPDATE ec_proveedor_producto SET precio=($tmp[1]*$tmp[3]),presentacion_caja=$tmp[3],precio_pieza=$tmp[1],clave_proveedor='$tmp[4]',ultima_actualizacion=now()
					WHERE id_proveedor_producto=$r[0]";
			}else if(mysql_num_rows($eje)<=0){
			//insertamos el registro de este producto para este proveedor
				$sql="INSERT INTO ec_proveedor_producto VALUES(null,$id_proveedor,$tmp[0],($tmp[1]*$tmp[3]),$tmp[3],$tmp[1],'$tmp[4]',now(),
					'0000-00-00 00:00:00')";
			}

		//ejecutamos la consulta para actualizar/insertar en la tabla de proveedor-producto segun sea el caso
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacion
				die("Error al insertar o actualizar el producto para este proveedor!!!\n".$error);
			}

		//consultamos el id de proveedor producto para este producto
			$sql="SELECT id_proveedor_producto FROM ec_proveedor_producto WHERE id_proveedor=$id_proveedor AND id_producto=$tmp[0] LIMIT 1";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacion
				die("Error al consultar el id de proveedor-producto!!!\n".$error);
			}
			$r=mysql_fetch_row($eje);

		//insertamos el producto en la tabla de detalle de orden de compra
			$sql="INSERT INTO ec_oc_detalle VALUES(null,$id_oc,$tmp[0],$tmp[2],$tmp[1],0,0,0,'',$r[0])";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transaccion
				die("Error al insertar el detalle de orden de compra!!!\n".$error."\n".$sql);
			}
			}
		}
	//actualizamos el mmonto de la orden de compra
		$sql="UPDATE ec_ordenes_compra SET total=(SELECT SUM(IF(id_oc_detalle IS NULL,0,(cantidad*precio))) FROM ec_oc_detalle WHERE id_orden_compra=$id_oc) 
		WHERE id_orden_compra=$id_oc";
		$eje_upd=mysql_query($sql);
		if(!$eje_upd){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transaccion
			die("Error al actualizar el monto de orden de compra!!!\n".$error."\n".$sql);
		}
		
		mysql_query("COMMIT");//autorizamos la transaccion
		
		die('ok');
	}
?>