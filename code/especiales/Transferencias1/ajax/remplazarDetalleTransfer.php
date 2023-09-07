<?php
	require('../../../../conectMin.php');//INCLUIMOS CLASE DE CONEXION
//extraemos variables
	extract($_POST);
//extraemos arreglos
	$id_producto=explode("~",$idPro);
	$presentacion=explode("~",$pres);
	$pedir=explode("~",$ped);
	$total=explode("~",$tot);
//iniciamos transaccion
	mysql_query("begin");
//eliminamos el detalle anterior de la transferencia
	$sql="DELETE FROM ec_transferencia_productos WHERE id_transferencia=$transfer_id";
	$borra=mysql_query($sql);
	if(!$borra){
		$error=mysql_error();
		mysql_query('rollback');
		die("Error al eliminar detalle Anterior\n\n".$sql."\n\n".$error);
	}

//insertamos el nuevo detalle
	for($i=0;$i<$adic[0];$i++){
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
	}
//si todo es correcto finalizamos transaccion
	mysql_query('commit');
	echo 'ok';
?>