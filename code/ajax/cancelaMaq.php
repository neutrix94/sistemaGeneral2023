<?php


    include("../../conectMin.php" );
    
    extract($_GET);

	//Buscamos los movimientos
	
	mysql_query("BEGIN");
	
	
	$sql="	SELECT
			id_movimiento_almacen
			FROM ec_movimiento_almacen
			WHERE id_maquila=$id_maquila";
			
	$res=mysql_query($sql);
	
	if(!$res)
	{
		echo "Error en\n$sql\n\nDescripcion:\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		
		
		//Insertamos el equivalente
		$sql="	INSERT INTO ec_movimiento_almacen
				(
					SELECT
					NULL,
					IF(id_tipo_movimiento = 3, 4, 3),
					id_usuario,
					id_sucursal,
					NOW(),
					NOW(),
					'CANCELACION DE MAQUILA',
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen,
					NULL,
					'0000-00-00 00:00:00',
					ultima_actualizacion
					FROM ec_movimiento_almacen
					WHERE id_movimiento_almacen=".$row[0]."
				)";
		
		$re=mysql_query($sql);
	
		if(!$re)
		{
			echo "Error en\n$sql\n\nDescripcion:\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}
		
		$id_mov=mysql_insert_id();
		
		//Insertamos el detalle
		$sql="	INSERT INTO ec_movimiento_detalle
				(
					SELECT
					NULL,
					$id_mov,
					id_producto,
					cantidad,
					cantidad_surtida,
					-1,
					-1
					FROM ec_movimiento_detalle
					WHERE id_movimiento=".$row[0]."
				)";
				
		$re=mysql_query($sql);
	
		if(!$re)
		{
			echo "Error en\n$sql\n\nDescripcion:\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}		
		
	}
	
	//Actualizamos la maquila
	
	$sql="UPDATE ec_maquila SET activa=0 WHERE id_maquila=$id_maquila";
	
	$re=mysql_query($sql);
	
		if(!$re)
		{
			echo "Error en\n$sql\n\nDescripcion:\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}		
	

	mysql_query("COMMIT");
	echo "exito";

?>