<?php
//1. Incluye archivo %/conectMin.php%%
	require('../../../../conectMin.php');
//extraemos variables
	extract($_POST);
//extraemos arreglos
	$id_producto=explode("~",$idPro);
	$presentacion=explode("~",$pres);
	$pedir=explode("~",$ped);
	$total=explode("~",$tot);
//iniciamos transaccion
	mysql_query("begin");
//2. Elimina el detalle anterior de la transferencia
	$sql="DELETE FROM ec_transferencia_productos WHERE id_transferencia = '{$transfer_id}'";
	$borra=mysql_query($sql);
	if(!$borra){
		$error=mysql_error();
		mysql_query('rollback');
		die("Error al eliminar detalle Anterior\n\n".$sql."\n\n".$error);
	}

//3. Inserta el nuevo detalle


	$details = explode( '|~|', $_POST['detail'] );
	foreach ( $details as $key => $detail ) {
		$det = explode( '~', $detail );
		$product_id = $det[0];
		$product_providers_detail = explode( '||', $det[1] );
		foreach ( $product_providers_detail as $key2 => $pp ) {
			$pp_detail = explode( '', $pp );
			if( $pp_detail[7] > 0 || $pp_detail[8] > 0 || $pp_detail[9] > 0){
				$sql = "INSERT INTO ec_transferencia_productos( id_transferencia, id_producto_or, 
					id_presentacion, cantidad_presentacion,cantidad, id_producto_de, 
					referencia_resolucion, cantidad_cajas, cantidad_paquetes, cantidad_piezas, id_proveedor_producto )
				VALUES('{$transfer_id}','{$product_id}','-1','{$pp_detail[10]}','{$pp_detail[10]}',
					'{$product_id}','{$pp_detail[10]}', '{$pp_detail[7]}', '{$pp_detail[8]}', '{$pp_detail[9]}', '{$pp_detail[2]}')";
				$eje=mysql_query($sql);//EJECUTA CONSULTA
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die("Error al insertar detalle de la Transferencia!!!\n\n".$sql."\n\n".$error);
				}
			}
		}
	}

	/*for($i=0;$i<$adic[0];$i++){
		$sqlProd="INSERT INTO ec_transferencia_productos(id_transferencia, id_producto_or,id_presentacion,cantidad_presentacion,cantidad,
						id_producto_de,referencia_resolucion)
					VALUES('$transfer_id','$id_producto[$i]','-1','$pedir[$i]','$total[$i]','$id_producto[$i]','$total[$i]')";
		//echo '<br>'.$sqlProd;
		$inserta=mysql_query($sqlProd);
		if(!$inserta){
			$error=mysql_error();
			mysql_query('rollback');
			die("Error!!!\nEl detalle no pudo ser insertado\n".$inserta."\n\n".$error);
		}else{
			$sqlProd="";
		}
	}*/
//si todo es correcto finalizamos transaccion
	mysql_query('commit');
	echo 'ok';
?>