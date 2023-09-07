<?php

	include("../../conectMin.php");
	
	extract($_GET);
	
	
	$idp=explode(",",$ids);
	$me=explode(",",$mer);
	$v=explode(",",$vu);
	
	
	//Buscamos la transferencia
	$sql="	SELECT
			t.id_transferencia,
			t.folio,
			d.id_devolucion_transferencia,
			d.folio
			FROM ec_transferencia_producto_dev pd
			JOIN ec_devolucion_transferencia d ON pd.id_transferencia = d.id_devolucion_transferencia
			JOIN ec_transferencias t ON d.id_transferencia = t.id_transferencia
			WHERE pd.id_transferencia_producto=".$idp[0];
				
	$res=mysql_query($sql);
	if(!$res)
	{
		echo "Error en:\n$sql\n\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
		
	$row=mysql_fetch_row($res);
	
	$id_transferencia=$row[0];
	$folio_trans=$row[1];
	$folio_dev=$row[3];
	$id_dev=$row[2];
	
	
	//Buscamos el primer almacen de venta
	
	$sql="	SELECT
			id_almacen
			FROM ec_almacen
			WHERE id_sucursal=$user_sucursal
			AND es_almacen=1
			ORDER BY prioridad";
	$res=mysql_query($sql);		
	if(!$res)
	{
		echo "Error en:\n$sql\n\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
		
	$row=mysql_fetch_row($res);
	
	
	
	$almacen=$row[0];
	
	//die($almacen);
	
	
			//Buscamos el primer almacen de no ventas
			$sql="	SELECT
					id_almacen
					FROM ec_almacen
					WHERE id_sucursal=$user_sucursal
					AND es_almacen=0
					ORDER BY prioridad";
					
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			if(mysql_num_rows($res) > 0)
			{
				$row=mysql_fetch_row($res);
				$id_al=$row[0];
			}
			//Buscamos el almacen de origen
			else
			{
				$sql="	SELECT
						id_almacen_origen
						FROM ec_transferencias
						WHERE id_transferencia=$id_transferencia";
				$res=mysql_query($sql);	  
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}		
				$row=mysql_fetch_row($res);
				$id_al=$row[0];
						
			}
	
	
	
	
	
	mysql_query("BEGIN");
	
	
	for($i=0;$i<sizeof($idp);$i++)
	{
		//Buscamos el dato
		$sql="	SELECT
				id_producto
				FROM ec_transferencia_producto_dev
				WHERE id_transferencia_producto=".$idp[$i];
				
		$res=mysql_query($sql);
		if(!$res)
		{
			echo "Error en:\n$sql\n\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}
		
		$row=mysql_fetch_row($res);
		
		//Insertamos la merma
		if($me[$i] >0 )
		{
			$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											VALUES(7, $user_id, $user_sucursal, NOW(), NOW(), 'SALIDA POR MERMA, DEVOLUCION $folio_dev, Transferencia $folio_trans', -1, -1, '', -1, -1, $id_al)";
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}
			
			$id_mov=mysql_insert_id();
			
			$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						VALUES($id_mov, $row[0], $me[$i], $me[$i], -1, -1)";
						
						
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}											
		}
		
		
		//Insertamos la vuelta
		if($v[$i] > 0 )
		{
		
			//Salida vuelta
			$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), 'SALIDA POR VUELTA, DEVOLUCION $folio_dev, Transferencia $folio_trans', -1, -1, '', -1, -1, $id_al)";
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}
			
			$id_mov=mysql_insert_id();
			
			$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						VALUES($id_mov, $row[0], $v[$i], $v[$i], -1, -1)";
						
						
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}
		
		
			//Entrada vuelta
		
			$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											VALUES(1, $user_id, $user_sucursal, NOW(), NOW(), 'ENTRADA POR VUELTA, DEVOLUCION $folio_dev, Transferencia $folio_trans', -1, -1, '', -1, -1, $almacen)";
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}
			
			$id_mov=mysql_insert_id();
			
			$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						VALUES($id_mov, $row[0], $v[$i], $v[$i], -1, -1)";
						
						
			$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}											
		}
				
	}
	
	//Acutalizamos el movimiento
	$sql="UPDATE ec_devolucion_transferencia SET resuelta=1 WHERE id_devolucion_transferencia=$id_dev";
	
	$re=mysql_query($sql);
			if(!$re)
			{
				echo "Error en:\n$sql\n\n".mysql_error();
				mysql_query("ROLLBACK");
				die();
			}
	
	
	mysql_query("COMMIT");

	echo "exito";


?>