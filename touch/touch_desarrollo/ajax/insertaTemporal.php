<?php
	include("../../conectMin.php");
//recibimos variables por GET
	$id_cabecera=$_GET['id_mov_temp'];
//eliminamos el movimiento temporal
	//$flag=
	if($_GET['fl']==1&&$id_cabecera!=0){//si es eliminar el temporal
		$sql="DELETE FROM ec_movimiento_temporal WHERE id_movimiento_temporal=$id_cabecera";
		$eje=mysql_query($sql)or die("Error al eliminar el movimiento temporal!!!\n\n".$sql."\n\n".mysql_error());
		die('ok');
	}

	$id_cabecera=$_GET['id_mov_temp'];
	$id_producto=$_GET['id_prod'];
	$cantidad=$_GET['cant'];
//die($id_cabecera."\n\n".$id_producto."\n\n".$cantidad);

	mysql_query("BEGIN");//marcamos el inicio de transacción
//insertamos cabecera del movimiento temporal si este no existe
	if($id_cabecera==0){
		$sq="INSERT INTO ec_movimiento_temporal VALUES(null,'$user_sucursal','$user_id',NOW(),NOW())";
		$eje=mysql_query($sq);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar la cabcera del temporal!!!\n\n".$sq."\n\n".$error);
		}
	//capturamos el id asignado a la cabecera del movimiento temporal
		$id_cabecera=mysql_insert_id();
		//mysql_query("COMMIT");
		//die('id_cabecera: '.$id_cabecera);
	}

//verificamos si existe un detalle de movimiento temporal con el producto
	$sql="SELECT id_detalle_temporal FROM ec_movimiento_temporal_detalle WHERE id_producto='$id_producto' AND id_movimiento_temporal='$id_cabecera'";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transacción
		die("Error al consultar si ya había un detalle para este producto en el movimiento temporal\n\n".$sql."\n\n".$error);
	}

//actualizaos la cantidad del detalle de movimiento temporal
	if(mysql_num_rows($eje)==1){
		$r=mysql_fetch_row($eje);
		$sql="UPDATE ec_movimiento_temporal_detalle SET cantidad='$cantidad' WHERE id_detalle_temporal='$r[0]'";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al actualizar el detalle en el movimiento temporal\n\n".$sql."\n\n".$error);
		}
	}else{	
	//insertamos el detalle del movimiento temporal
		$sql="INSERT INTO ec_movimiento_temporal_detalle VALUES(null,'$id_cabecera','$id_producto','$cantidad')";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el detalle en el movimiento temporal\n\n".$sql."\n\n".$error);
		}		
	}
	mysql_query("COMMIT");//autorizamos transacción
	die('ok|'.$id_cabecera);
?>