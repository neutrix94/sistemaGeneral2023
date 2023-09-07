	if(isset($_POST['fl']) && $_POST['fl']=='mantenimiento'){
		mysql_query("BEGIN",$local);
		//agrupamos los movimientos de almacen
		$sql="SELECT
			md.id_producto,
			SUM(IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))),
        	ma.id_almacen
		FROM ec_movimiento_detalle md
		LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
        GROUP BY md.id_producto,ma.id_sucursal
		ORDER BY ma.id_sucursal,ma.id_almacen";
		$eje=mysql_query($sql,$local);
		if(!$eje){
			$error=mysql_error($local);
			mysql_error("ROLLBACK",$local);//cancelamos la transacción		
			die("Error al extraer las agrupaciones de movimientos de almacen!!!\n\n".$sql."\n\n".$error);
		}

		$numero_datos=mysql_num_rows($eje);

	//almacenamos los datos de movimientos de almacen en un temporal
		$nombre_archivo="mov_tmp.txt"; 
 		
    	if(file_exists($nombre_archivo)){
    		 unlink($nombre_archivo);//eliminamos el archivo si existe
    	}
 	//escribimos en el archivo
    	if($archivo = fopen($nombre_archivo, "a")){
    	    while($r=mysql_fetch_row($eje)){
        		if(fwrite($archivo, $r[0]."~".$r[1]."~".$r[2]."\n")){//escribimos línea por línea
        		}else{
        		    mysql_query("ROLLBACK",$local);//cancelamos la transacción
        			fclose($archivo);
        		    die("Error al escribir el archivo");
        		}
        	}//fin de while
        	fclose($archivo);
    	}//fin de escritura de temporal	

//borramos los movimientos de almacen que no son de la sucursal
   		$sql="DELETE FROM ec_movimiento_almacen WHERE id_movimiento_almacen!=-1";
    	$eje=mysql_query($sql,$local);
		if(!$eje){
			$error=mysql_error($eje);
			mysql_error("ROLLBACK",$local);//cancelamos la transacción		
			die("Error al eliminar los movimientos de almacen!!!\n\n".$sql."\n\n".$error);
		}

		$sql="ALTER TABLE ec_movimiento_detalle auto_increment =1";
		$eje=mysql_query($sql,$local);
		if(!$eje){
			$error=mysql_error($local);
			mysql_error("ROLLBACK",$local);//cancelamos la transacción		
			die("Error al resetaer el contador del detalle movimientos de almacen!!!\n\n".$sql."\n\n".$error);
		}

		//reinsertamos los movimientos de almacén
		$sql="SELECT 
					s.id_sucursal,
					a.id_almacen  
				FROM sys_sucursales s 
				RIGHT JOIN ec_almacen a ON s.id_sucursal=a.id_sucursal
				WHERE ";
			if($id_suc==-1){
				$sql.="s.id_sucursal>0";
			}else{
				$sql.="s.id_sucursal=4";//.$id_suc/*******************************************************************************************************************/
			}
			$sql.=" ORDER BY a.id_almacen ASC";

			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_error("ROLLBACK",$local);//cancelamos la transacción		
				die("Error al consular las sucursales!!!\n\n".$sql."\n\n".$error);
			}
		//insertamos las cabeceras de los nuevos movimientos de almacen
			$ids='';
			while($r=mysql_fetch_row($eje)){
				$sql="INSERT INTO ec_movimiento_almacen SET
						id_movimiento_almacen=null,
						id_tipo_movimiento='13',
						id_usuario='$user_id',
						id_sucursal=$r[0],
						fecha=now(),
						hora=now(),
						observaciones='Resumen de movimientos de almacen por mantenimiento',
						id_pedido=-1,
						id_orden_compra=-1,
						lote='',
						id_maquila=-1,
						id_transferencia=-1,
						id_almacen=$r[1],
						ultima_sincronizacion='00:00:00 00:00:00',
						ultima_actualizacion='00:00:00 00:00:00'";
				$ins_cbc=mysql_query($sql,$local);
				if(!$ins_cbc){
					$error=mysql_error($local);
					mysql_error("ROLLBACK",$local);//cancelamos la transacción		
					die("Error al insertar las cabeceras de movimientos de almacen!!!\n\n".$sql."\n\n".$error);
				}	
				$ids.=mysql_insert_id()."~".$r[1].'|';	
			}//fin de while

		//insertamos los detalles
			$arr=explode("|", $ids);
			$cont=0;
			for($i=0;$i<sizeof($arr)-1;$i++){
				$mov=explode("~",$arr[$i]);
				$archivo=fopen("mov_tmp.txt", "r") or exit("No se puede abrir el archivo!!!");//abrimos el archivo
				while($linea=fgets($archivo)){
					//echo $linea."<br />";
					$aux=explode("~",$linea);
					//echo '.'.$mov[1];
					echo $aux[2]."=".$mov[1]."|";
					if((int)$aux[2]==(int)$mov[1]){//si corresponde al almacen
						$sql="INSERT INTO ec_movimiento_detalle 
									SET
									id_movimiento_almacen_detalle=null,
									id_movimiento=$mov[0],
									id_producto=$aux[0],
									cantidad=$aux[1],
									cantidad_surtida=$aux[1],
									id_pedido_detalle=-1,
									id_oc_detalle=-1";
						$cont++;				
								//	echo '<br>'.$cont.".- ".$sql;		
						$ins_dt=mysql_query($sql,$local);
						if(!$ins_dt){
							$error=mysql_error($local);
							mysql_error("ROLLBACK",$local);//cancelamos la transacción		
							die("Error al insertar detalles de movimientos de almacen!!!\n\n".$sql."\n\n".$error);
						}
					}
				}//fin de while
		}//fin de for $i				
		fclose($archivo);
	}//fin de si es mantenimiento




else if($tipo_bd==2){
		if($id_suc==-1){
			//mysql_query("BEGIN",$local);//declaramos el inicio de transacción
			mysql_query("BEGIN",$linea);//declaramos el inicio de transacción

		//apagamos el acceso de todas las sucursales en la BD
			$sql="UPDATE sys_sucursales set acceso=0,sincronizar=0";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_error("ROLLBACK",$linea);//cancelamos la transacción
				die("Error al poner todas las sucursales en 0!!!".$sql."\n\n".$error);
			}

		//asignamos la nueva sucursal 
			$sql="UPDATE sys_sucursales set acceso=1,sincronizar=0 WHERE id_sucursal=$id_suc";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_error("ROLLBACK",$linea);//cancelamos la transacción
				die("Error al activar la sucursal!!!".$sql."\n\n".$error);
			}

		//creamos registros en BD locales para restaurar movimientos de almacen
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_movimiento_almacen SET id_equivalente=0 WHERE id_sucursal=',id_sucursal,' AND CONCAT(fecha,\' \',hora)>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al insertar los registros de restauración de movimientos de almacen!!!\n\n".$sql."\n\n".$error);
			}

		//creamos registros en BD locales para restaurar devoluciones
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_devolucion SET id_equivalente=0 WHERE id_sucursal=',id_sucursal,' AND CONCAT(fecha,\' \',hora)>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al insertar los registros de restauración de decvolucion!!!\n\n".$sql."\n\n".$error);
			}
		
		//creamos registros en BD locales para restaurar pedidos
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_pedidos SET id_equivalente=0 WHERE id_sucursal=',id_sucursal,' AND fecha_alta>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al insertar los registros de restauración de pedidos!!!\n\n".$sql."\n\n".$error);
			}

		//creamos registros en BD locales para restaurar pagos
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_pedido_pagos pg
									LEFT JOIN ec_pedidos pe ON pg.id_pedido=pe.id_pedido
									SET pg.id_equivalente=0
									WHERE pe.id_sucursal=',id_sucursal,' AND pg.id_equivalente=0 AND CONCAT(pg.fecha,\' \',pg.hora)>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al actualizar el registro de respaldo!!!\n\n".$sql."\n\n".$error);
			}

		//creamos registros en BD locales para restaurar clientes
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_clientes SET id_equivalente=0 WHERE id_sucursal=',id_sucursal,' AND fecha_alta=>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al insertar los registros de restauración de clientes!!!\n\n".$sql."\n\n".$error);
			}

		//creamos registros en BD locales para restaurar registros de sincronización
			$sql="INSERT INTO ec_sincronizacion_registros SELECT NULL,-1,id_sucursal,'sys_respaldos',0,4,7,
							CONCAT('UPDATE ec_sincronizacion_registros SET id_equivalente=0 WHERE id_sucursal=',id_sucursal,' AND id_equivalente=0 AND fecha>\'$fecha_rsp\''),
							now(),0,0
							FROM sys_sucursales WHERE id_sucursal>0
							ORDER BY id_sucursal";
			$eje=mysql_query($sql,$linea);
			if(!$eje){
				$error=mysql_error($linea);
				mysql_query("ROLLBACK",$linea);
				die("Error al insertar los registros de restauración de registros de sincronización!!!\n\n".$sql."\n\n".$error);
			}
			//actualizamos el estatus del respaldo
			$sql="UPDATE sys_respaldos SET realizado=1";
			$eje=mysql_query($sql,$local);
			if(!$eje){
				$error=mysql_error($local);
				mysql_query("ROLLBACK",$local);
				mysql_query("ROLLBACK",$linea);
				die("Error al actualizar el registro de respaldo!!!\n\n".$sql."\n\n".$error);
			}
			mysql_query("COMMIT",$linea);
			die('ok|');//no se borra nada
		
		}

	}
