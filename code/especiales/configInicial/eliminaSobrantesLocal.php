<?php
	
/*********************************************************Proceso de BD local****************************************/
	if($id_suc!=-1){
	//1 Apagar el acceso de todas las sucursales en la BD
		$sql="UPDATE sys_sucursales 
				SET acceso=0,
				sincronizar=0";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al poner todas las sucursales en 0!!! {$restauration_stm} ");
		}

	//2 Asignar la nueva sucursal 
		$sql="UPDATE sys_sucursales 
				SET acceso=1,
				sincronizar=0 
			WHERE id_sucursal={$id_suc}";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al activar la sucursal!!! {$restauration_stm} ");
		}

	//3 Eliminar los movimientos de almacen que no sean de la sucursal
		$sql="DELETE FROM ec_movimiento_almacen 
			WHERE id_almacen NOT IN(SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$id_suc) 
		AND id_movimiento_almacen!=-1";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los movimientos de almacen que no corresponden a la sucursal!!! {$restauration_stm} ");
		}

	//4 Eliminar las devoluciones que no son de la sucursal
		$sql="DELETE FROM ec_devolucion WHERE id_sucursal!=$id_suc AND id_devolucion!=-1";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar las devoluciones que no corresponden a la sucursal!!! {$restauration_stm} ");
		}

	//5 Eliminar las ventas que no son de la sucursal
		$sql="DELETE FROM ec_pedidos WHERE id_sucursal != {$id_suc} AND id_pedido != -1";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los pedidos que no corresponden a la sucursal_1!!! {$restauration_stm} ");
		}

	/*6 Eliminar los registros de sincronización
		$sql="DELETE FROM sys_sincronizacion_registros WHERE id_sucursal_destino != {$id_suc}";	
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los registros de sincronización que no corresponden a la sucursal!!! {$restauration_stm} ");
		}*/

	//6 Eliminar los registros de gastos que no son de la sucursal
		$sql="DELETE FROM ec_gastos WHERE id_sucursal != {$id_suc}";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los gastos que no son de la sucursal!!! {$restauration_stm} ");
		}

	//7 Eliminar los registros de nomina que no son de la sucursal
		$sql="DELETE FROM ec_registro_nomina WHERE id_sucursal != {$id_suc}";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los registros de nomina que no son de la sucursal!!! {$restauration_stm} ");
		}
		
	//8 Eliminar detalles temporales de ventas
		$sql="DELETE FROM ec_pedidos_detalle_back";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los detalles temporales de venta!!! {$restauration_stm} ");
		}		
	//9 Eliminar temporales de ventas	
		$sql="DELETE FROM ec_pedidos_back";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los temporales de venta!!! {$restauration_stm} ");
		}

	//10 Eliminar los movimientos a nivel proveedor producto que no son de la sucursal
		$sql="DELETE FROM ec_movimiento_detalle_proveedor_producto WHERE id_sucursal != {$id_suc}";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los movimientos proveedor producto!!! {$restauration_stm} ");
		}
		$sql="DELETE FROM ec_pedidos_validacion_usuarios WHERE id_sucursal != $id_suc
		AND id_pedido_validacion > 0";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los registros de validacion de ventas!!! {$restauration_stm} ");
		}

	//11 Actualizar clientes a mostrador de ventas que no son de la sucursal
		$sql="UPDATE ec_pedidos SET id_cliente=1 
			WHERE id_pedido 
			IN(SELECT 
					ax.id_pedido 
				FROM(SELECT 
						p.id_pedido 
					FROM ec_pedidos p 
					LEFT JOIN ec_clientes c 
					ON p.id_cliente=c.id_cliente 
					WHERE c.id_sucursal != {$id_suc}
				AND c.id_sucursal!=-1 AND c.id_cliente>1
			) ax )";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){	
			die("Error al modificar los ids de clientes que no son de la sucursal y el pedido si es de la sucursal!!!\n\n {$restauration_stm}");
		}

	//12 Eliminar clientes que no son de la sucursal
		$sql="DELETE FROM ec_clientes WHERE id_sucursal != {$id_suc} AND id_sucursal != -1 AND id_cliente > 1";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los clientes que no son de la sucursal!!! {$restauration_stm} ");
		}

	/*13 Eliminar los registros de sincronizacion
		$sql="DELETE FROM sys_sincronizacion_registros WHERE 1";
		$restauration_stm = excecuteQuery( $sql, $link );
		if( $restauration_stm != "ok" ){
			die("Error al eliminar los registros de sincronizacion!!! {$restauration_stm} ");
		}*/
	//echo "Pasa ";

	//13 Eliminar los registros de sincronizacion de linea*/
		$sql="DELETE FROM sys_sincronizacion_registros 
			WHERE id_sucursal_destino = {$id_suc} AND fecha<='{$fecha_rsp}'";
		$sendPetition = send_petition( $api_path, $sql );
		if( $sendPetition != 'ok' ){
			die( "Error al eliminar los registros de sincronizacion de linea por API : {$sendPetition}" );
		}
	}

/*///////////////////////////////
			$sql="UPDATE ec_movimiento_almacen SET id_usuario=-1
					WHERE id_movimiento_almacen
					IN(SELECT ax.id_movimiento_almacen 
						FROM(SELECT ma.id_movimiento_almacen 
							FROM ec_movimiento_almacen ma 
							LEFT JOIN sys_users u ON ma.id_usuario=u.id_usuario 
							WHERE ma.id_sucursal=$id_suc AND u.id_sucursal NOT IN(-1,$id_suc))ax)";
			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_query("ROLLBACK",$local);//cancelamos la transacción		
				die("Error al actualizar el id de los usuarios que no son de la sucursal cuando el movimiento de almacen corresponde a la sucursal!!!\n\n".$sql."\n\n".$error);
			}
			
			$sql="UPDATE ec_transferencias SET id_usuario=-1
					WHERE id_transferencia
					IN(SELECT ax.id_transferencia 
						FROM(SELECT t.id_transferencia 
							FROM ec_transferencias t 
							LEFT JOIN sys_users u ON t.id_usuario=u.id_usuario 
							WHERE (t.id_sucursal=$id_suc OR t.id_sucursal_origen=$id_suc OR t.id_sucursal_destino=$id_suc) 
							AND u.id_sucursal NOT IN(-1,$id_suc))ax)";
			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_query("ROLLBACK",$local);//cancelamos la transacción		
				die("Error al actualizar el id de los usuarios que no son de la sucursal cuando la trasfererencia corresponde a la sucursal!!!\n\n".$sql."\n\n".$error);
			}
	
			$sql="UPDATE sys_sucursales SET id_encargado=-1 WHERE id_sucursal NOT IN(-1,$id_suc)";
			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_query("ROLLBACK",$local);//cancelamos la transacción		
				die("Error al actualizar los ids de la sucursal!!!\n\n".$sql."\n\n".$error);
			}

		/*elimina los registros de usuarios*
			$sql="DELETE FROM sys_users WHERE id_sucursal!=$id_suc AND id_sucursal!=-1";
			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_query("ROLLBACK",$local);//cancelamos la transacción		
				die("Error al eliminar los usuarios que no son de la sucursal!!!\n\n".$sql."\n\n".$error);
			}*/
?>