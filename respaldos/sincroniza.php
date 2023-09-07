<?php
	function cierra($rootpath, $link){
		//Actualizamos el registro
		$sql="UPDATE ec_sincronizacion SET ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), en_proceso=0";
		mysql_query($sql, $link);		
	}
	
	function getDateTime($l){
		$s="SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
		$r=mysql_query($s);
		
		$rs=mysql_fetch_row($r);
		
		return $rs[0];
	}

	//Días de sincronizacion
	$dsinc=1;
	/***********************************CONEXIONES BD LOCAL**********************************************/
	$root=str_replace("/respaldos/sincroniza.php", "", $argv[0]);
	include($root."/config.inc.php");	
//Abrimos el archivo log
	$ar=fopen("$root/respaldos/logSincro.txt", "at");
//escribimos informe 
	if($ar){
		fwrite($ar, "\nSe inicio el archivo de Cron(sincroniza.php): ".date('Y-m-d H:i:s'));
	}
	if($ar){
		fwrite($ar, "\nSe inicia con la conexion de la base datos local ".date('Y-m-d H:i:s'));
	}
//Conectamos con la BD local
	$link=@mysql_connect($dbHost, $dbUser, $dbPassword);
//comprobamos conexion local
	if(!$link){	//si no hay conexion
		if($ar){
			fwrite($ar, "\nError al conectar con el servidor local ".date('Y-m-d H:i:s'));//escribimos error
			fclose($ar);//cerramos archivo
		}
		die();//finaliza programa
	}
	$db=@mysql_select_db($dbName);
	if(!$db){	
		if($ar){
			fwrite($ar, "\nError al conectar con la base de datos local ".date('Y-m-d H:i:s'));
			fclose($ar);
		}
		die();
	}
	mysql_query("SET time_zone='-05:00'", $link) or die(mysql_error());

	
	
	/***********************************DATOS GENERALES SINCRONIZACION**********************************************/
	
	//Conseguimos los datos generales
	$sql="	SELECT
			es_server,
			server,
			user,
			password,
			bd_name,
			id_sucursal
			FROM ec_sincronizacion";
			
	$res=mysql_query($sql);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error()."\n a las ".date('Y-m-d H:i:s'));
			fclose($ar);
		}
		
		die();
	}		
	
	$row=mysql_fetch_assoc($res);
	
	if($row[0] == '1')
	{
		if($ar)
		{
			fwrite($ar, "\nEs servidor, por lo que se detiene la sincronizacion ".date('Y-m-d H:i:s'));
		}	
	}
	
	extract($row);
	
	
	
	/***********************************CONEXIONES BD FORANEA**********************************************/
	

	if($ar)
	{
		fwrite($ar, "\nSe inicia con la conexion de la base datos foranea ".date('Y-m-d H:i:s'));
	}
	
	
	
	$lnk=@mysql_connect($server, $user, $password);
	
	if(!$lnk)
	{	
		if($ar)
		{
			fwrite($ar, "\nError al conectar con el servidor foranea ".date('Y-m-d H:i:s'));
			fclose($ar);
		}
	
		die();
	}	
	
	
	$db2=@mysql_select_db($bd_name);
	
	
	if(!$db2)
	{	
		if($ar)
		{
			fwrite($ar, "\nError al conectar con la base de datos foranea ".date('Y-m-d H:i:s'));
			fclose($ar);
		}
		
		die();
	}
	
	mysql_query("SET time_zone='-05:00'", $lnk) or die(mysql_error());

	
	/***********************************NOTAS DE VENTA NUEVAS**********************************************/
	
	
	
	//Buscamos las notas de venta que no se han sincronizado
	if($ar)
	{
		fwrite($ar, "\nSe inicia con la sincronizacion de notas de venta no insertadas ".date('Y-m-d H:i:s'));
	}
	
	
	$sql="	SELECT
			id_pedido,
			folio_pedido,
			folio_nv,
			folio_factura,
			folio_cotizacion,
			id_cliente,
			id_estatus,
			id_moneda,
			fecha_alta,
			fecha_factura,
			id_direccion,
			direccion,
			id_razon_social,
			subtotal,
			iva,
			ieps,
			total,
			dias_proximo,
			pagado,
			surtido,
			enviado,
			id_sucursal,
			id_usuario,
			fue_cot,
			facturado,
			id_tipo_envio,
			descuento,
			id_razon_factura,
			folio_abono,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente
			FROM ec_pedidos
			WHERE id_sucursal=$id_sucursal
			AND(
				id_equivalente IS NULL
				OR ultima_sincronizacion < ultima_actualizacion
			)
			AND id_pedido > 0";
	
	$res=mysql_query($sql, $link);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto notasupdate;
	}
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		if($row['id_equivalente'] != 'NO')
		{
			$sql="	UPDATE ec_pedidos
					SET
					folio_pedido='".$row['folio_pedido']."',
					folio_nv='".$row['folio_nv']."',
					folio_factura='".$row['folio_factura']."',
					folio_cotizacion='".$row['folio_cotizacion']."',
					id_cliente=1,
					id_estatus='".$row['id_estatus']."',
					id_moneda='".$row['id_moneda']."',
					fecha_alta='".$row['fecha_alta']."',
					fecha_factura='".$row['fecha_factura']."',
					id_direccion=-1,
					direccion='".$row['direccion']."',
					id_razon_social=-1,
					subtotal='".$row['subtotal']."',
					iva='".$row['iva']."',
					ieps='".$row['ieps']."',
					total='".$row['total']."',
					dias_proximo='".$row['dias_proximo']."',
					pagado='".$row['pagado']."',
					surtido='".$row['surtido']."',
					enviado='".$row['enviado']."',
					id_sucursal=$id_sucursal,
					id_usuario=1,
					fue_cot='".$row['fue_cot']."',
					facturado='".$row['facturado']."',
					id_tipo_envio='".$row['id_tipo_envio']."',
					descuento='".$row['descuento']."',
					id_razon_factura=NULL,
					folio_abono='".$row['folio_abono']."',
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_equivalente=".$row['id_equivalente'];
		}
		else
		{
			$sql="	INSERT INTO ec_pedidos
					SET
					folio_pedido='".$row['folio_pedido']."',
					folio_nv='".$row['folio_nv']."',
					folio_factura='".$row['folio_factura']."',
					folio_cotizacion='".$row['folio_cotizacion']."',
					id_cliente=1,
					id_estatus='".$row['id_estatus']."',
					id_moneda='".$row['id_moneda']."',
					fecha_alta='".$row['fecha_alta']."',
					fecha_factura='".$row['fecha_factura']."',
					id_direccion=-1,
					direccion='".$row['direccion']."',
					id_razon_social=-1,
					subtotal='".$row['subtotal']."',
					iva='".$row['iva']."',
					ieps='".$row['ieps']."',
					total='".$row['total']."',
					dias_proximo='".$row['dias_proximo']."',
					pagado='".$row['pagado']."',
					surtido='".$row['surtido']."',
					enviado='".$row['enviado']."',
					id_sucursal=$id_sucursal,
					id_usuario=1,
					fue_cot='".$row['fue_cot']."',
					facturado='".$row['facturado']."',
					id_tipo_envio='".$row['id_tipo_envio']."',
					descuento='".$row['descuento']."',
					id_razon_factura=NULL,
					folio_abono='".$row['folio_abono']."',
					id_equivalente='".$row['id_pedido']."',
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
		}			
				
		$re=mysql_query($sql, $lnk);
	
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
		
			continue;
		}
		
		if($row['id_equivalente'] != 'NO')
		{
			$id_pedido_nuevo=$row['id_equivalente'];
			
			
			//Borramos para actualizar
			$sql="DELETE FROM ec_pedidos_detalle WHERE id_pedido=".$row['id_equivalente'];
			$re=mysql_query($sql, $lnk);
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
		
				continue;
			}
			
			
			$sql="DELETE FROM ec_pedido_pagos WHERE id_pedido=".$row['id_equivalente'];
			$re=mysql_query($sql, $lnk);
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
		
				continue;
			}
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE id_pedido=".$row['id_equivalente'];
			$re=mysql_query($sql, $lnk);
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
		
				continue;
			}
			
		}	
		else
			$id_pedido_nuevo=mysql_insert_id($lnk);
		
		//Insertamos el detalle
		if($err == 0)
		{
			$sql="	SELECT
					id_producto,
					cantidad,
					precio,
					monto,
					iva,
					ieps,
					cantidad_surtida
					FROM ec_pedidos_detalle
					WHERE id_pedido=".$row['id_pedido'];
				
			$re=mysql_query($sql, $link);	
		
		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);	
					mysql_query("ROLLBACK", $link);	
					$err++;		
				}
			
				continue;
			}
			$nu=mysql_num_rows($re);
		
			for($j=0;$j<$nu;$j++)
			{
		
				$ro=mysql_fetch_assoc($re);
		
				$sql="	INSERT INTO ec_pedidos_detalle
						SET
						id_pedido='".$id_pedido_nuevo."',
						id_producto='".$ro['id_producto']."',
						cantidad='".$ro['cantidad']."',
						precio='".$ro['precio']."',
						monto='".$ro['monto']."',
						iva='".$ro['iva']."',
						ieps='".$ro['ieps']."',
						cantidad_surtida='".$ro['cantidad_surtida']."'";
					
				$re2=mysql_query($sql, $lnk);
					
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);		
						mysql_query("ROLLBACK", $link);		
						$err++;
					}
		
					$j=$nu+1;
				}						
			}	
		}
		
		//Insertamos los pagos
		if($err == 0)
		{
			$sql="	SELECT
					id_tipo_pago,
					fecha,
					hora,
					monto,
					referencia,
					id_moneda,
					tipo_cambio,
					id_nota_credito,
					id_cxc
					FROM ec_pedido_pagos
					WHERE id_pedido=".$row['id_pedido'];
				
			$re=mysql_query($sql, $link);	
		
		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);	
					mysql_query("ROLLBACK", $link);	
					$err++;		
				}
			
				continue;
			}
			$nu=mysql_num_rows($re);
		
			for($j=0;$j<$nu;$j++)
			{
		
				$ro=mysql_fetch_assoc($re);
		
				$sql="	INSERT INTO ec_pedido_pagos
						SET
						id_pedido='".$id_pedido_nuevo."',
						id_tipo_pago='".$ro['id_tipo_pago']."',
						fecha='".$ro['id_producto']."',
						hora='".$ro['fecha']."',
						monto='".$ro['monto']."',
						referencia='".$ro['referencia']."',
						id_moneda='".$ro['id_moneda']."',
						tipo_cambio='".$ro['tipo_cambio']."',
						id_nota_credito=-1,
						id_cxc=-1";
					
				$re2=mysql_query($sql, $lnk);
					
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);		
						mysql_query("ROLLBACK", $link);		
						$err++;
					}
		
					$j=$nu+1;
				}						
			}
		}
		
		//Insertamos los movimientos de almacen
		
		if($err == 0)
		{
			$sql="	SELECT
					id_movimiento_almacen,
					id_tipo_movimiento,
					id_usuario,
					id_sucursal,
					fecha,
					hora,
					observaciones,
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen
					FROM ec_movimiento_almacen
					WHERE id_pedido=".$row['id_pedido'];
					
			$re=mysql_query($sql, $link);	
		
		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);	
					mysql_query("ROLLBACK", $link);	
					$err++;		
				}
			
				continue;
			}
			$nu=mysql_num_rows($re);
		
			for($j=0;$j<$nu;$j++)
			{
		
				$ro=mysql_fetch_assoc($re);
				
				$sql="	INSERT INTO ec_movimiento_almacen
						SET
						id_tipo_movimiento='".$ro['id_tipo_movimiento']."',
						id_usuario='1',
						id_sucursal='$id_sucursal',
						fecha='".$ro['fecha']."',
						hora='".$ro['hora']."',
						observaciones='".$ro['observaciones']."',
						id_pedido='".$id_pedido_nuevo."',
						id_orden_compra='-1',
						lote='".$ro['lote']."',
						id_maquila='-1',
						id_transferencia='-1',
						id_almacen='".$ro['id_almacen']."',
						id_equivalente='".$ro['id_movimiento_almacen']."',
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
					
				$re2=mysql_query($sql, $lnk);
					
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);		
						mysql_query("ROLLBACK", $link);		
						$err++;
					}
		
					$j=$nu+1;
				}
				
				$id_mov_nuevo=mysql_insert_id($lnk);
				
				//Actualizamos el detalle de movimiento
				if($err == 0)
				{
					$sql="	UPDATE ec_movimiento_almacen
							SET
							id_equivalente=$id_mov_nuevo,
							ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
							WHERE id_movimiento_almacen=".$ro['id_movimiento_almacen'];
							
					$re2=mysql_query($sql, $link);
					
					if(!$re2)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);		
							mysql_query("ROLLBACK", $link);		
							$err++;
						}
		
						$j=$nu+1;
					}		
							
				}
				
				//Insertamos los detalles
				if($err == 0)
				{
					$sql="	SELECT
							id_producto,
							cantidad,
							cantidad_surtida,
							id_pedido_detalle,
							id_oc_detalle
							FROM ec_movimiento_detalle
							WHERE id_movimiento=".$ro['id_movimiento_almacen'];
							
					$re2=mysql_query($sql, $link);
					
					if(!$re2)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);		
							mysql_query("ROLLBACK", $link);		
							$err++;
						}
		
						$j=$nu+1;
					}
					
					$nu2=mysql_num_rows($re2);
					
					for($k=0;$k<$nu2;$k++)
					{
						$ro2=mysql_fetch_assoc($re2);
						
						$sql="	INSERT INTO ec_movimiento_detalle
								SET
								id_movimiento='$id_mov_nuevo',
								id_producto='".$ro2['id_producto']."',
								cantidad='".$ro2['cantidad']."',
								cantidad_surtida='".$ro2['cantidad_surtida']."',
								id_pedido_detalle='-1',
								id_oc_detalle='-1'";
								
						$re3=mysql_query($sql, $lnk);
					
						if(!$re3)
						{
							if($ar)
							{
								fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
								mysql_query("ROLLBACK", $lnk);		
								mysql_query("ROLLBACK", $link);		
								$err++;
							}
		
							$k=$nu2+1;
						}		
						
					}	
				}
				
					
				
			}			
			
		}
		
		if($err == 0)
		{
			//Actualizamos la sincronizacion
				
			$sql="	UPDATE ec_pedidos
					SET
					id_equivalente=$id_pedido_nuevo,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_pedido=".$row['id_pedido'];
						
			$re=mysql_query($sql, $link);
					
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);		
					mysql_query("ROLLBACK", $link);		
					$err++;
				}
			}	
		}
		
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	}
	
	
	
	
	notasupdate:
	
	
	/***********************************Sincronizamos la lista de precios**********************************************/
	
	//Buscamos las notas de venta que no se han sincronizado
	if($ar)
	{
		fwrite($ar, "\nSe inicia con la sincronizacion de las listas de precios ".date('Y-m-d H:i:s'));
	}
	
	
	$sql="	SELECT
			id_precio,
		  	fecha,
			nombre,
			id_usuario,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente,
			ultima_actualizacion,
			IF(
				ultima_actualizacion <> ultima_sincronizacion,
				1,
				0
			) AS actualiza
			FROM ec_precios";
	
	
	$res=mysql_query($sql, $lnk);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto alamcenesin;
	}
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		if($row['id_equivalente'] != 'NO')
		{
			//Buscamos el equivalente
			
			$sql="	SELECT
					id_precio,
					IF(
						TIMEDIFF(ultima_sincronizacion, '".$row['ultima_actualizacion']."') < '00:00:30',
						1,
						0
					)
					FROM ec_precios
					WHERE id_equivalente=".$row['id_precio'];
			$re=mysql_query($sql, $link);
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
		
				continue;
			}
			
			
			if(mysql_num_rows($re) <= 0)
			{
				$row['id_equivalente']='NO';
			}
			else
			{
				$ro=mysql_fetch_row($re);
			
				if($ro[1] == '0')
				{
					mysql_query("COMMIT", $lnk);
					mysql_query("COMMIT", $link);
				
					continue;
				}
				$id_precio_nuevo=$ro[0];
				
				
				//Borramos los datos anteriores
				$sql="DELETE FROM ec_precios_detalle WHERE id_precio=".$id_precio_nuevo;
				$re=mysql_query($sql, $link);
				if(!$re)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
		
					continue;
				}
			}
		}
	
	
		$sql="";
		
	
		if($row['id_equivalente'] != 'NO')
		{
			$sql.="	UPDATE ec_precios
					SET";	
		}
		else
		{
			$sql.="	INSERT INTO ec_precios
					SET";		
		}
	
	
		$sql.="	fecha='".$row['fecha']."',
				nombre='".$row['nombre']."',
				id_usuario='1',
				id_equivalente='".$row['id_precio']."',
				ultima_sincronizacion='".getDateTime($lnk)."'";
				
				
		if($row['id_equivalente'] != 'NO')
		{
			$sql.=" WHERE id_precio=".$id_precio_nuevo;
		}			
		
		
		
		$re=mysql_query($sql, $link);
	
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
		
			continue;
		}
		
		
		if($row['id_equivalente'] == 'NO')
		{
			$id_precio_nuevo=mysql_insert_id($link);
		}
		
		
		//Insertamos el detalle de la lista de precio
		if($err == 0)
		{
			$sql="	SELECT
					de_valor,
					a_valor,
					precio_venta,
					precio_oferta,
					id_producto
					FROM ec_precios_detalle
					WHERE id_precio=".$row['id_precio'];
					
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
		
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_assoc($re);
				
				$sql="	INSERT INTO ec_precios_detalle
						SET
						id_precio='$id_precio_nuevo',
						de_valor='".$ro['de_valor']."',
						a_valor='".$ro['a_valor']."',
						precio_venta='".$ro['precio_venta']."',
						precio_oferta='".$ro['precio_oferta']."',
						id_producto='".$ro['id_producto']."'";
						
				$re2=mysql_query($sql, $link);
					
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);		
						mysql_query("ROLLBACK", $link);		
						$err++;
					}
		
					$j=$nu+1;
				}		
						
			}
					
					
		}
		
		if($err == 0 && $row['actualiza'] == '1')
		{
		
			
		
			//Actualizamos datos
			$sql="	UPDATE ec_precios
					SET
					id_equivalente='-1',
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_precio=".$row['id_precio'];
				
			$re=mysql_query($sql, $lnk);
					
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);		
					mysql_query("ROLLBACK", $link);		
					$err++;
				}
			}		
		}	
	
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	}
	

alamcenesin:


	/***********************************Sincronizamos los almacenes**********************************************/


	if($ar)
	{
		fwrite($ar, "\nSe inicia con la sincronizacion de los almacenes ".date('Y-m-d H:i:s'));
	}
	
	$sql="	SELECT
			id_almacen,
			nombre,
			es_almacen,
			prioridad,
			id_sucursal
			FROM ec_almacen
			WHERE ultima_sincronizacion = '0000-00-00 00:00:00'
			OR ultima_actualizacion > ultima_sincronizacion";
	
	$res=mysql_query($sql, $link);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto transferenciasSin;
	}
	
	$num=mysql_num_rows($res);
	$almas="(-1";
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		$almas.=",".$row['id_almacen'];
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		//Buscamos si existe el almacen
		$sql="SELECT id_almacen FROM ec_almacen WHERE id_almacen=".$row['id_almacen'];
		
				
		$re=mysql_query($sql, $lnk);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) > 0)
			$existe=1;
		else
			$existe=0;
			
		//Armamos la consulta
	
		if($existe == 1)
			$sql="	UPDATE ec_almacen
					SET";
		else					
			$sql="	INSERT INTO ec_almacen
					SET";
					
					
		$sql.="	nombre='".$row['nombre']."',
				es_almacen='".$row['es_almacen']."',
				prioridad='".$row['prioridad']."',
				id_sucursal='".$row['id_sucursal']."',
				ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				
		if($existe == 1)
		{
			$sql.="	WHERE id_almacen=".$row['id_almacen'];
		}
		else
			$sql.=",id_almacen=".$row['id_almacen'];
		
		$re=mysql_query($sql, $lnk);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		//Actualizamos el dato
		$sql="UPDATE ec_almacen SET ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_almacen=".$row['id_almacen'];
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}
	
	$almas.=")";
	
	
	//insertamos los cambios de arriba
	$sql="	SELECT
			id_almacen,
			nombre,
			es_almacen,
			prioridad,
			id_sucursal,
			ultima_actualizacion,
			IF(
				ultima_actualizacion <> ultima_sincronizacion,
				1,
				0
			) AS actualiza
			FROM ec_almacen
			WHERE id_almacen NOT IN".$almas;
	
	$res=mysql_query($sql, $lnk);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto transferenciasSin;
	}
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		//Buscamos si existe el almacen
		$sql="	SELECT
				id_almacen,
				IF(
					TIMEDIFF(ultima_sincronizacion, '".$row['ultima_actualizacion']."') < '00:00:30',
					1,
					0
				)
				FROM ec_almacen
				WHERE id_almacen=".$row['id_almacen'];
		
				
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) > 0)
		{
			$existe=1;
			$ro=mysql_fetch_row($re);
			if($ro[1] == '0')
			{
				mysql_query("COMMIT", $lnk);
				mysql_query("COMMIT", $link);
				continue;
			}	
					
		}	
		else
			$existe=0;
			
		//Armamos la consulta
	
		if($existe == 1)
			$sql="	UPDATE ec_almacen
					SET";
		else					
			$sql="	INSERT INTO ec_almacen
					SET";
					
					
		$sql.="	nombre='".$row['nombre']."',
				es_almacen='".$row['es_almacen']."',
				prioridad='".$row['prioridad']."',
				id_sucursal='".$row['id_sucursal']."',
				ultima_sincronizacion='".getDateTime($lnk)."'";
				
		if($existe == 1)
		{
			$sql.="	WHERE id_almacen=".$row['id_almacen'];
		}
		else
			$sql.=",id_almacen=".$row['id_almacen'];
		
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($row['actualiza'] == '1')
		{
		
			//Actualizamos el dato
			$sql="UPDATE ec_almacen SET ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_almacen=".$row['id_almacen'];
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
		}	
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}

	
	
transferenciasSin:


	/***********************************Sincronizamos las transfeencias**********************************************/


	if($ar)
	{
		fwrite($ar, "\nSe inicia con la sincronizacion de las transferencias ".date('Y-m-d H:i:s'));
	}
	
	
	//Buscamos las transferencias para subir
	$sql="	SELECT
			id_transferencia,
			id_usuario,
			folio,
			fecha,
			hora,
			id_sucursal_origen,
			id_sucursal_destino,
			observaciones,
			id_razon_social_venta,
			id_razon_social_compra,
			facturable,
			porc_ganancia,
			id_almacen_origen,
			id_almacen_destino,
			id_tipo,
			id_estado,
			id_sucursal,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente
			FROM ec_transferencias
			WHERE (id_equivalente IS NULL
			OR ultima_actualizacion > ultima_sincronizacion)
			AND id_transferencia > 0";

	$res=mysql_query($sql, $link);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		//Buscamos si existe el almacen
		
		if($row['id_equivalente'] != 'NO')
		{
			$sql="	SELECT
					id_transferencia,
					id_estado
					FROM ec_transferencias
					WHERE id_transferencia=".$row['id_equivalente'];
		
				
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			if(mysql_num_rows($re) > 0)
			{
				$ro=mysql_fetch_row($re);

				//Validamos que el estatus no sea inferior
				if($ro[1] > $row['id_estado'])
				{
					if($ar)
					{
						fwrite($ar, "\nIntento de baja de estatus en transferencia [".$row['id_equivalente']."]");
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
					continue;
				}

				$id_transferencia_nueva=$ro[0];
			}
			else
			{
				$row['id_equivalente']='NO';
			}	
			
		}
		
		
		if($row['id_equivalente'] == 'NO')		
			$sql="	INSERT INTO ec_transferencias
					SET";
		else	
			$sql="	UPDATE ec_transferencias
					SET";
					
					
		$sql.="	id_usuario=1,
				folio='".$row['folio']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				id_sucursal_origen='".$row['id_sucursal_origen']."',
				id_sucursal_destino='".$row['id_sucursal_destino']."',
				observaciones='".$row['observaciones']."',
				id_razon_social_venta=-1,
				id_razon_social_compra=1,
				facturable='".$row['facturable']."',
				porc_ganancia='".$row['porc_ganancia']."',
				id_almacen_origen='".$row['id_almacen_origen']."',
				id_almacen_destino='".$row['id_almacen_destino']."',
				id_tipo='".$row['id_tipo']."',
				id_estado='".$row['id_estado']."',
				id_sucursal='".$row['id_sucursal']."',
				id_equivalente='".$row['id_transferencia']."',
				ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'),
				ultima_actualizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				
				
		if($row['id_equivalente'] != 'NO')					
		{
			$sql.="	WHERE id_transferencia='$id_transferencia_nueva'";
		}
		
		
		$re=mysql_query($sql, $lnk);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($row['id_equivalente'] == 'NO')					
		{
			$id_transferencia_nueva=mysql_insert_id($lnk);
		}
		
		
		
		if($row['id_equivalente'] != 'NO')					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_transferencia_productos WHERE id_transferencia=".$id_transferencia_nueva;
			
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE id_transferencia=".$id_transferencia_nueva;
			
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
				
		}
		
		//Actualizamos el detalle de la transferencia
		if($err == 0)
		{
		
			$sql="	SELECT
					id_producto_or,
					id_producto_de,
					cantidad,
					id_presentacion,
					cantidad_presentacion,
					cantidad_salida,
					cantidad_salida_pres,
					cantidad_entrada,
					cantidad_entrada_pres,
					resolucion
					FROM ec_transferencia_productos
					WHERE id_transferencia=".$row['id_transferencia'];
					
			$re=mysql_query($sql, $link);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos el detalle
				$sql="	INSERT INTO ec_transferencia_productos
						SET
						id_transferencia='$id_transferencia_nueva',
						id_producto_or='".$ro['id_producto_or']."',
						id_producto_de='".$ro['id_producto_de']."',
						cantidad='".$ro['cantidad']."',
						id_presentacion='".$ro['id_presentacion']."',
						cantidad_presentacion='".$ro['cantidad_presentacion']."',
						cantidad_salida='".$ro['cantidad_salida']."',
						cantidad_salida_pres='".$ro['cantidad_salida_pres']."',
						cantidad_entrada='".$ro['cantidad_entrada']."',
						cantidad_entrada_pres='".$ro['cantidad_entrada_pres']."',
						resolucion='".$ro['resolucion']."'";
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
					$j=$nu+1;
					continue;
				}			
			}
		}
		
		
		//Actualizamos los movimentos de inventario
		if($err == 0)
		{
			$sql="	SELECT
					id_movimiento_almacen,
					id_tipo_movimiento,
					id_usuario,
					id_sucursal,
					fecha,
					hora,
					observaciones,
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen
					FROM ec_movimiento_almacen
					WHERE id_transferencia=".$row['id_transferencia'];
			
			$re=mysql_query($sql, $link);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
			
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos la cabecera del movimiento
				$sql="	INSERT INTO ec_movimiento_almacen
						SET
						id_tipo_movimiento='".$ro['id_tipo_movimiento']."',
						id_usuario=1,
						id_sucursal='".$ro['id_sucursal']."',
						fecha='".$ro['fecha']."',
						hora='".$ro['hora']."',
						observaciones='".$ro['observaciones']."',
						id_pedido=-1,
						id_orden_compra=-1,
						lote='',
						id_maquila=-1,
						id_transferencia='$id_transferencia_nueva',
						id_almacen='".$ro['id_almacen']."',
						id_equivalente='".$ro['id_movimiento_almacen']."',
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$id_mov2=mysql_insert_id($lnk);
				
				
				//Insertamos el detalle
				$sql="	SELECT
						id_producto,
						cantidad,
						cantidad_surtida,
						id_pedido_detalle,
						id_oc_detalle
						FROM ec_movimiento_detalle
						WHERE id_movimiento=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$nu2=mysql_num_rows($re2);
				
				for($k=0;$k<$nu2;$k++)
				{
					$ro2=mysql_fetch_assoc($re2);
					
					
					$sql="	INSERT INTO ec_movimiento_detalle
							SET
							id_movimiento='$id_mov2',
							id_producto='".$ro2['id_producto']."',
							cantidad='".$ro2['cantidad']."',
							cantidad_surtida='".$ro2['cantidad_surtida']."',
							id_pedido_detalle=-1,
							id_oc_detalle=-1";
							
					$re3=mysql_query($sql, $lnk);		
					if(!$re3)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);
							mysql_query("ROLLBACK", $link);	
							$err++;	
							$k=$nu+1;		
						}
						continue;
					}		
				}
						
				//Actualizamos el movimiento local
				$sql="	UPDATE ec_movimiento_almacen
						SET
						id_equivalente=$id_mov2,
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
						WHERE id_movimiento_almacen=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}		
				
			}
					
					
		}
		
		
		if($err == 0)
		{
			//Actualizamos el movimiento
			$sql="	UPDATE ec_transferencias
					SET
					id_equivalente=$id_transferencia_nueva,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_transferencia=".$row['id_transferencia'];
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}		
					
		}	
	
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}
	
	
	//Buscamos las transferencias para bajar
	$sql="	SELECT
			id_transferencia,
			id_usuario,
			folio,
			fecha,
			hora,
			id_sucursal_origen,
			id_sucursal_destino,
			observaciones,
			id_razon_social_venta,
			id_razon_social_compra,
			facturable,
			porc_ganancia,
			id_almacen_origen,
			id_almacen_destino,
			id_tipo,
			id_estado,
			id_sucursal,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente,
			ultima_actualizacion,
			IF(
				ultima_actualizacion <> ultima_sincronizacion,
				1,
				0
			) AS actualiza
			FROM ec_transferencias
			WHERE id_transferencia > 0
			AND DATEDIFF(NOW(), DATE_FORMAT(ultima_actualizacion, '%Y-%m-%d')) <= $dsinc ";

	$res=mysql_query($sql, $lnk);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		//Buscamos si existe localmente
		$sql="	SELECT
				id_transferencia,
				IF(
					TIMEDIFF(ultima_sincronizacion, '".$row['ultima_actualizacion']."') < '00:00:30',
					1,
					0
				),
				id_estado
				FROM ec_transferencias
				WHERE id_equivalente='".$row['id_transferencia']."'";
				
				
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) > 0)
		{
			$existe=1;
			$ro=mysql_fetch_row($re);
			if($ro[1] == '0')
			{
				mysql_query("COMMIT", $lnk);
				mysql_query("COMMIT", $link);
				continue;
			}

			//Validamos que no sea una regresion de estatus
			if($ro[2] > $row['id_estado'])
			{
				continue;
			}


			$id_transferencia_nueva=$ro[0];	
					
		}	
		else
			$existe=0;		
		
		
		if($existe == 0)		
			$sql="	INSERT INTO ec_transferencias
					SET";
		else	
			$sql="	UPDATE ec_transferencias
					SET";
					
					
		$sql.="	id_usuario=1,
				folio='".$row['folio']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				id_sucursal_origen='".$row['id_sucursal_origen']."',
				id_sucursal_destino='".$row['id_sucursal_destino']."',
				observaciones='".$row['observaciones']."',
				id_razon_social_venta=-1,
				id_razon_social_compra=1,
				facturable='".$row['facturable']."',
				porc_ganancia='".$row['porc_ganancia']."',
				id_almacen_origen='".$row['id_almacen_origen']."',
				id_almacen_destino='".$row['id_almacen_destino']."',
				id_tipo='".$row['id_tipo']."',
				id_estado='".$row['id_estado']."',
				id_sucursal='".$row['id_sucursal']."',
				id_equivalente='".$row['id_transferencia']."',
				ultima_sincronizacion='".getDateTime($lnk)."',
				ultima_actualizacion='".getDateTime($lnk)."'";
				
				
		if($existe == 1)					
		{
			$sql.="	WHERE id_transferencia='$id_transferencia_nueva'";
		}
		
		
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($existe == 0)					
		{
			$id_transferencia_nueva=mysql_insert_id($link);
		}
		
		
		
		if($existe == 1)					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_transferencia_productos WHERE id_transferencia=".$id_transferencia_nueva;
			
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE id_transferencia=".$id_transferencia_nueva;
			
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
				
		}
		
		//Actualizamos el detalle de la transferencia
		if($err == 0)
		{
		
			$sql="	SELECT
					id_producto_or,
					id_producto_de,
					cantidad,
					id_presentacion,
					cantidad_presentacion,
					cantidad_salida,
					cantidad_salida_pres,
					cantidad_entrada,
					cantidad_entrada_pres,
					resolucion
					FROM ec_transferencia_productos
					WHERE id_transferencia=".$row['id_transferencia'];
					
			$re=mysql_query($sql, $lnk);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos el detalle
				$sql="	INSERT INTO ec_transferencia_productos
						SET
						id_transferencia='$id_transferencia_nueva',
						id_producto_or='".$ro['id_producto_or']."',
						id_producto_de='".$ro['id_producto_de']."',
						cantidad='".$ro['cantidad']."',
						id_presentacion='".$ro['id_presentacion']."',
						cantidad_presentacion='".$ro['cantidad_presentacion']."',
						cantidad_salida='".$ro['cantidad_salida']."',
						cantidad_salida_pres='".$ro['cantidad_salida_pres']."',
						cantidad_entrada='".$ro['cantidad_entrada']."',
						cantidad_entrada_pres='".$ro['cantidad_entrada_pres']."',
						resolucion='".$ro['resolucion']."'";
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
					$j=$nu+1;
					continue;
				}			
			}
		}
		
		
		//Actualizamos los movimentos de inventario
		if($err == 0)
		{
			$sql="	SELECT
					id_movimiento_almacen,
					id_tipo_movimiento,
					id_usuario,
					id_sucursal,
					fecha,
					hora,
					observaciones,
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen
					FROM ec_movimiento_almacen
					WHERE id_transferencia=".$row['id_transferencia'];
			
			$re=mysql_query($sql, $lnk);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
			
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos la cabecera del movimiento
				$sql="	INSERT INTO ec_movimiento_almacen
						SET
						id_tipo_movimiento='".$ro['id_tipo_movimiento']."',
						id_usuario='1',
						id_sucursal='".$ro['id_sucursal']."',
						fecha='".$ro['fecha']."',
						hora='".$ro['hora']."',
						observaciones='".$ro['observaciones']."',
						id_pedido=-1,
						id_orden_compra=-1,
						lote='',
						id_maquila=-1,
						id_transferencia='$id_transferencia_nueva',
						id_almacen='".$ro['id_almacen']."',
						id_equivalente='".$ro['id_movimiento_almacen']."',
						ultima_sincronizacion='".getDateTime($lnk)."'";
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$id_mov2=mysql_insert_id($link);
				
				
				//Insertamos el detalle
				$sql="	SELECT
						id_producto,
						cantidad,
						cantidad_surtida,
						id_pedido_detalle,
						id_oc_detalle
						FROM ec_movimiento_detalle
						WHERE id_movimiento=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$nu2=mysql_num_rows($re2);
				
				for($k=0;$k<$nu2;$k++)
				{
					$ro2=mysql_fetch_assoc($re2);
					
					
					$sql="	INSERT INTO ec_movimiento_detalle
							SET
							id_movimiento='$id_mov2',
							id_producto='".$ro2['id_producto']."',
							cantidad='".$ro2['cantidad']."',
							cantidad_surtida='".$ro2['cantidad_surtida']."',
							id_pedido_detalle=-1,
							id_oc_detalle=-1";
							
					$re3=mysql_query($sql, $link);		
					if(!$re3)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);
							mysql_query("ROLLBACK", $link);	
							$err++;	
							$k=$nu2+1;		
						}
						continue;
					}		
				}
						
				//Actualizamos el movimiento en linea
				$sql="	UPDATE ec_movimiento_almacen
						SET
						id_equivalente=$id_mov2,
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
						WHERE id_movimiento_almacen=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}		
				
			}
					
					
		}
		
		
		if($err == 0 && $row['actualiza'] == '1')
		{
			//Actualizamos el movimiento
			$sql="	UPDATE ec_transferencias
					SET
					id_equivalente=$id_transferencia_nueva,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_transferencia=".$row['id_transferencia'];
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}		
					
		}	
	
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}
	
	
	
	//Sincronizamos devoluciones
	$sql="	SELECT
			d.id_devolucion_transferencia,
			d.folio,
			t.id_equivalente AS id_transferencia,
			d.fecha,
			d.hora,
			d.id_sucursal_destino,
			d.resuelta,
			d.observaciones,
			d.id_usuario,
			d.id_sucursal,
			IF(d.id_equivalente IS NULL, 'NO', d.id_equivalente) AS id_equivalente
			FROM ec_devolucion_transferencia d
			JOIN ec_transferencias t ON d.id_transferencia = t.id_transferencia
			WHERE
			(
				d.id_equivalente IS NULL
				OR
				d.ultima_actualizacion > d.ultima_sincronizacion
			)";
	
	$res=mysql_query($sql, $link);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		
		//Si no existe un equivalente, esperamos a que se sincronice la transferencia
		if($row['id_transferencia'] == '')
		{
			if($ar)
			{
				fwrite($ar, "\nLa Devolucion de transferencia ".$row['folio'].", hace alusión a una transferencia no sincronizada ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;

		}
		
				
		
		//Buscamos si existe la devolucion
		
		if($row['id_equivalente'] != 'NO')
		{
			$sql="	SELECT
					id_devolucion_transferencia
					FROM ec_devolucion_transferencia
					WHERE id_devolucion_transferencia=".$row['id_equivalente'];
		
				
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			if(mysql_num_rows($re) > 0)
			{
				$ro=mysql_fetch_row($re);
				$id_devolucion_trans=$ro[0];
			}
			else
			{
				$row['id_equivalente']='NO';
			}	
			
		}
		
		
		if($row['id_equivalente'] == 'NO')		
			$sql="	INSERT INTO ec_devolucion_transferencia
					SET";
		else	
			$sql="	UPDATE ec_devolucion_transferencia
					SET";
					
					
		$sql.="	folio='".$row['folio']."',
				id_transferencia='".$row['id_transferencia']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				id_sucursal_destino='".$row['id_sucursal_destino']."',
				resuelta='".$row['resuelta']."',
				observaciones='".$row['observaciones']."',
				id_usuario=1,
				id_sucursal='".$row['id_sucursal']."',
				id_equivalente='".$row['id_devolucion_transferencia']."',
				ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				
				
		if($row['id_equivalente'] != 'NO')					
		{
			$sql.="	WHERE id_devolucion_transferencia='$id_devolucion_trans'";
		}
		
		
		$re=mysql_query($sql, $lnk);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($row['id_equivalente'] == 'NO')					
		{
			$id_devolucion_trans=mysql_insert_id($lnk);
		}
		
		
		
		if($row['id_equivalente'] != 'NO')					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_transferencia_producto_dev WHERE id_transferencia=".$id_devolucion_trans;
			
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
						
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE observaciones LIKE '%".$row['folio']."%'";
			
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
				
		}
		
		//Actualizamos el detalle de la transferencia
		if($err == 0)
		{
		
			$sql="	SELECT
					id_transferencia,
					id_producto,
					cantidad,
					merma,
					vuelta
					FROM ec_transferencia_producto_dev
					WHERE id_transferencia=".$row['id_devolucion_transferencia'];
					
			$re=mysql_query($sql, $link);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos el detalle
				$sql="	INSERT INTO ec_transferencia_producto_dev
						SET
						id_transferencia='$id_devolucion_trans',
						id_producto='".$ro['id_producto']."',
						cantidad='".$ro['cantidad']."',
						merma='".$ro['merma']."',
						vuelta='".$ro['vuelta']."'";
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
					$j=$nu+1;
					continue;
				}			
			}
		}
		
		
		//Actualizamos los movimentos de inventario
		if($err == 0)
		{
			$sql="	SELECT
					id_movimiento_almacen,
					id_tipo_movimiento,
					id_usuario,
					id_sucursal,
					fecha,
					hora,
					observaciones,
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen
					FROM ec_movimiento_almacen
					WHERE observaciones LIKE '%".$row['folio']."%'";
			
			$re=mysql_query($sql, $link);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
			
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos la cabecera del movimiento
				$sql="	INSERT INTO ec_movimiento_almacen
						SET
						id_tipo_movimiento='".$ro['id_tipo_movimiento']."',
						id_usuario=1,
						id_sucursal='".$ro['id_sucursal']."',
						fecha='".$ro['fecha']."',
						hora='".$ro['hora']."',
						observaciones='".$ro['observaciones']."',
						id_pedido=-1,
						id_orden_compra=-1,
						lote='',
						id_maquila=-1,
						id_transferencia=-1,
						id_almacen='".$ro['id_almacen']."',
						id_equivalente='".$ro['id_movimiento_almacen']."',
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$id_mov2=mysql_insert_id($lnk);
				
				
				//Insertamos el detalle
				$sql="	SELECT
						id_producto,
						cantidad,
						cantidad_surtida,
						id_pedido_detalle,
						id_oc_detalle
						FROM ec_movimiento_detalle
						WHERE id_movimiento=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$nu2=mysql_num_rows($re2);
				
				for($k=0;$k<$nu2;$k++)
				{
					$ro2=mysql_fetch_assoc($re2);
					
					
					$sql="	INSERT INTO ec_movimiento_detalle
							SET
							id_movimiento='$id_mov2',
							id_producto='".$ro2['id_producto']."',
							cantidad='".$ro2['cantidad']."',
							cantidad_surtida='".$ro2['cantidad_surtida']."',
							id_pedido_detalle=-1,
							id_oc_detalle=-1";
							
					$re3=mysql_query($sql, $lnk);		
					if(!$re3)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);
							mysql_query("ROLLBACK", $link);	
							$err++;	
							$k=$nu+1;		
						}
						continue;
					}		
				}
						
				//Actualizamos el movimiento local
				$sql="	UPDATE ec_movimiento_almacen
						SET
						id_equivalente=$id_mov2,
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
						WHERE id_movimiento_almacen=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}		
				
			}
					
					
		}
		
		
		if($err == 0)
		{
			//Actualizamos el movimiento
			$sql="	UPDATE ec_devolucion_transferencia
					SET
					id_equivalente=$id_devolucion_trans,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_devolucion_transferencia=".$row['id_devolucion_transferencia'];
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}		
					
		}
		
		
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}
	
	
	//Buscamos las devoluciones para bajar
	$sql="	SELECT
			d.id_devolucion_transferencia,
			d.folio,
			d.id_transferencia,
			d.fecha,
			d.hora,
			d.id_sucursal_destino,
			d.resuelta,
			d.observaciones,
			d.id_usuario,
			d.id_sucursal,
			IF(d.id_equivalente IS NULL, 'NO', d.id_equivalente) AS id_equivalente,
			d.ultima_actualizacion,
			IF(
				d.ultima_actualizacion <> d.ultima_sincronizacion,
				1,
				0
			) AS actualiza
			FROM ec_devolucion_transferencia d
			JOIN ec_transferencias t ON d.id_transferencia = t.id_transferencia
			WHERE DATEDIFF(NOW(), DATE_FORMAT(d.ultima_actualizacion, '%Y-%m-%d')) <= $dsinc";

	$res=mysql_query($sql, $lnk);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		
		//Buscamos la transferencia equivalente
		$sql="SELECT id_transferencia FROM ec_transferencias WHERE id_equivalente=".$row['id_transferencia'];
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) <= 0)
		{
			if($ar)
			{
				fwrite($ar, "\nLa devolucion de transferencia ".$row['folio'].", hace referencia a una transferencia no sincronizada ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		else
		{
			$ro=mysql_fetch_row($re);
			$row['id_transferencia']=$ro[0];
		}
		
		
		
		//Buscamos si existe localmente
		$sql="	SELECT
				id_devolucion_transferencia,
				IF(
					TIMEDIFF(ultima_sincronizacion, '".$row['ultima_actualizacion']."') < '00:00:30',
					1,
					0
				)
				FROM ec_devolucion_transferencia
				WHERE id_equivalente='".$row['id_devolucion_transferencia']."'";
				
				
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) > 0)
		{
			$existe=1;
			$ro=mysql_fetch_row($re);
			if($ro[1] == '0')
			{
				mysql_query("COMMIT", $lnk);
				mysql_query("COMMIT", $link);
				continue;
			}
			$id_devolucion_trans=$ro[0];	
					
		}	
		else
			$existe=0;		
		
		
		if($existe == 0)		
			$sql="	INSERT INTO ec_devolucion_transferencia
					SET";
		else	
			$sql="	UPDATE ec_devolucion_transferencia
					SET";
					
					
		$sql.="	folio='".$row['folio']."',
				id_transferencia='".$row['id_transferencia']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				id_sucursal_destino='".$row['id_sucursal_destino']."',
				resuelta='".$row['resuelta']."',
				observaciones='".$row['observaciones']."',
				id_usuario=1,
				id_sucursal='".$row['id_sucursal']."',
				id_equivalente='".$row['id_devolucion_transferencia']."',
				ultima_sincronizacion='".getDateTime($lnk)."'";
				
				
		if($existe == 1)					
		{
			$sql.="	WHERE id_devolucion_transferencia='$id_devolucion_trans'";
		}
		
		
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($existe == 0)					
		{
			$id_devolucion_trans=mysql_insert_id($link);
		}
		
		
		
		if($existe == 1)					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_transferencia_producto_dev WHERE id_transferencia=".$id_devolucion_trans;
			
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE observaciones LIKE '%".$row['folio']."%'";
			
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
				
		}
		
		//Actualizamos el detalle de la transferencia
		if($err == 0)
		{
		
			$sql="	SELECT
					id_producto,
					cantidad,
					merma,
					vuelta
					FROM ec_transferencia_producto_dev
					WHERE id_transferencia=".$row['id_devolucion_transferencia'];
					
			$re=mysql_query($sql, $lnk);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos el detalle
				$sql="	INSERT INTO ec_transferencia_producto_dev
						SET
						id_transferencia='$id_devolucion_trans',
						id_producto='".$ro['id_producto']."',
						cantidad='".$ro['cantidad']."',
						merma='".$ro['merma']."',
						vuelta='".$ro['vuelta']."'";
						
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;			
					}
					$j=$nu+1;
					continue;
				}			
			}
		}
		
		
		//Actualizamos los movimentos de inventario
		if($err == 0)
		{
			$sql="	SELECT
					id_movimiento_almacen,
					id_tipo_movimiento,
					id_usuario,
					id_sucursal,
					fecha,
					hora,
					observaciones,
					id_pedido,
					id_orden_compra,
					lote,
					id_maquila,
					id_transferencia,
					id_almacen
					FROM ec_movimiento_almacen
					WHERE observaciones LIKE '%".$row['folio']."%'";
			
			$re=mysql_query($sql, $lnk);		
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			$nu=mysql_num_rows($re);
			
			for($j=0;$j<$nu;$j++)
			{
			
				$ro=mysql_fetch_assoc($re);
				
				//Insertamos la cabecera del movimiento
				$sql="	INSERT INTO ec_movimiento_almacen
						SET
						id_tipo_movimiento='".$ro['id_tipo_movimiento']."',
						id_usuario='1',
						id_sucursal='".$ro['id_sucursal']."',
						fecha='".$ro['fecha']."',
						hora='".$ro['hora']."',
						observaciones='".$ro['observaciones']."',
						id_pedido=-1,
						id_orden_compra=-1,
						lote='',
						id_maquila=-1,
						id_transferencia=-1,
						id_almacen='".$ro['id_almacen']."',
						id_equivalente='".$ro['id_movimiento_almacen']."',
						ultima_sincronizacion='".getDateTime($lnk)."'";
				$re2=mysql_query($sql, $link);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$id_mov2=mysql_insert_id($link);
				
				
				//Insertamos el detalle
				$sql="	SELECT
						id_producto,
						cantidad,
						cantidad_surtida,
						id_pedido_detalle,
						id_oc_detalle
						FROM ec_movimiento_detalle
						WHERE id_movimiento=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}
				
				$nu2=mysql_num_rows($re2);
				
				for($k=0;$k<$nu2;$k++)
				{
					$ro2=mysql_fetch_assoc($re2);
					
					
					$sql="	INSERT INTO ec_movimiento_detalle
							SET
							id_movimiento='$id_mov2',
							id_producto='".$ro2['id_producto']."',
							cantidad='".$ro2['cantidad']."',
							cantidad_surtida='".$ro2['cantidad_surtida']."',
							id_pedido_detalle=-1,
							id_oc_detalle=-1";
							
					$re3=mysql_query($sql, $link);		
					if(!$re3)
					{
						if($ar)
						{
							fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
							mysql_query("ROLLBACK", $lnk);
							mysql_query("ROLLBACK", $link);	
							$err++;	
							$k=$nu2+1;		
						}
						continue;
					}		
				}
						
				//Actualizamos el movimiento en linea
				$sql="	UPDATE ec_movimiento_almacen
						SET
						id_equivalente=$id_mov2,
						ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
						WHERE id_movimiento_almacen=".$ro['id_movimiento_almacen'];
						
				$re2=mysql_query($sql, $lnk);		
				if(!$re2)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$j=$nu+1;		
					}
					continue;
				}		
				
			}
					
					
		}
		
		
		if($err == 0 && $row['actualiza'] == '1')
		{
			//Actualizamos el movimiento
			$sql="	UPDATE ec_devolucion_transferencia
					SET
					id_equivalente=$id_devolucion_trans,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_devolucion_transferencia=".$row['id_devolucion_transferencia'];
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}		
					
		}	
	
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}


inventario:



	/*****************************************SINCRONIZACION DE INVENTARIOS******************************************************/

	if($ar)
	{
		fwrite($ar, "\nSe inicia con la sincronizacion de movimientos de inventario ".date('Y-m-d H:i:s'));
	}
	
	
	//Buscamos las transferencias para subir
	$sql="	SELECT
			id_movimiento_almacen,
			id_tipo_movimiento,
			id_usuario,
			id_sucursal,
			fecha,
			hora,
			observaciones,
			id_pedido,
			id_orden_compra,
			lote,
			id_maquila,
			id_transferencia,
			id_almacen,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente
			FROM ec_movimiento_almacen
			WHERE id_pedido = -1
			AND id_transferencia = -1
			AND observaciones NOT LIKE '%SALIDA POR DEVOLUCION%'
			AND observaciones NOT LIKE '%ENTRADA POR DEVOLUCION%'
			AND observaciones NOT LIKE '%DEVOLUCION DT%'
			AND (id_equivalente IS NULL OR ultima_actualizacion > ultima_sincronizacion)";

	$res=mysql_query($sql, $link);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		
		//Buscamos si existe la devolucion
		
		if($row['id_equivalente'] != 'NO')
		{
			$sql="	SELECT
					id_movimiento_almacen
					FROM ec_movimiento_almacen
					WHERE id_movimiento_almacen=".$row['id_equivalente'];
		
				
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			if(mysql_num_rows($re) > 0)
			{
				$ro=mysql_fetch_row($re);
				$id_movimiento_nuevo=$ro[0];
			}
			else
			{
				$row['id_equivalente']='NO';
			}	
			
		}
		
		
		if($row['id_equivalente'] == 'NO')		
			$sql="	INSERT INTO ec_movimiento_almacen
					SET";
		else	
			$sql="	UPDATE ec_movimiento_almacen
					SET";
					
					
		$sql.="	id_tipo_movimiento='".$row['id_tipo_movimiento']."',
				id_usuario=1,
				id_sucursal='".$row['id_sucursal']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				observaciones='".$row['observaciones']."',
				id_pedido=-1,
				id_orden_compra=-1,
				lote='',
				id_maquila=-1,
				id_transferencia=-1,
				id_almacen='".$row['id_almacen']."',
				id_equivalente='".$row['id_movimiento_almacen']."',
				ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
				
				
		if($row['id_equivalente'] != 'NO')					
		{
			$sql.="	WHERE id_movimiento_almacen='$id_movimiento_nuevo'";
		}
		
		
		$re=mysql_query($sql, $lnk);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($row['id_equivalente'] == 'NO')					
		{
			$id_movimiento_nuevo=mysql_insert_id($lnk);
		}
		
		
		
		if($row['id_equivalente'] != 'NO')					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_movimiento_detalle WHERE id_movimiento_almacen=".$id_movimiento_nuevo;
			
			$re=mysql_query($sql, $lnk);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
						
							
		}
		
		if($err == 0)
		{
			//Insertamos el detalle
			$sql="	SELECT
					id_producto,
					cantidad,
					cantidad_surtida,
					id_pedido_detalle,
					id_oc_detalle
					FROM ec_movimiento_detalle
					WHERE id_movimiento=".$row['id_movimiento_almacen'];
						
			$re2=mysql_query($sql, $link);		
			if(!$re2)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;	
					$j=$nu+1;		
				}
				continue;
			}
				
			$nu2=mysql_num_rows($re2);
				
			for($k=0;$k<$nu2;$k++)
			{
				$ro2=mysql_fetch_assoc($re2);
					
				$sql="	INSERT INTO ec_movimiento_detalle
						SET
						id_movimiento='$id_movimiento_nuevo',
						id_producto='".$ro2['id_producto']."',
						cantidad='".$ro2['cantidad']."',
						cantidad_surtida='".$ro2['cantidad_surtida']."',
						id_pedido_detalle=-1,
						id_oc_detalle=-1";
							
				$re3=mysql_query($sql, $lnk);		
				if(!$re3)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$k=$nu+1;		
					}
					continue;
				}		
			}
		
		}
		
		if($err == 0)
		{
			//Actualizamos el movimiento en linea
			$sql="	UPDATE ec_movimiento_almacen
					SET
					id_equivalente=$id_movimiento_nuevo,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_movimiento_almacen=".$row['id_movimiento_almacen'];
						
			$re2=mysql_query($sql, $link);		
			if(!$re2)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;	
					$j=$nu+1;		
				}
				continue;
			}
		}
		
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}	
	
	
	//Descargamos los movimientos
	
	
	$sql="	SELECT
			id_movimiento_almacen,
			id_tipo_movimiento,
			id_usuario,
			id_sucursal,
			fecha,
			hora,
			IF(id_pedido = -1,
				observaciones,
				CONCAT(
					'MOVIMIENTO HECHO DESDE NOTA DE VENTA ',
					(SELECT folio_nv FROM ec_pedidos WHERE id_pedido = ec_movimiento_almacen.id_pedido)
				)
			) AS observaciones,
			id_pedido,
			id_orden_compra,
			lote,
			id_maquila,
			id_transferencia,
			id_almacen,
			IF(id_equivalente IS NULL, 'NO', id_equivalente) AS id_equivalente,
			ultima_actualizacion,
			IF(
				ultima_actualizacion <> ultima_sincronizacion,
				1,
				0
			) AS actualiza
			FROM ec_movimiento_almacen
			WHERE (id_pedido = -1 OR (id_sucursal != $id_sucursal AND id_pedido <> -1))
			AND id_transferencia = -1
			AND observaciones NOT LIKE '%SALIDA POR DEVOLUCION%'
			AND observaciones NOT LIKE '%ENTRADA POR DEVOLUCION%'
			AND observaciones NOT LIKE '%DEVOLUCION DT%'
			AND DATEDIFF(NOW(), DATE_FORMAT(ultima_actualizacion, '%Y-%m-%d')) <= $dsinc";

	$res=mysql_query($sql, $lnk);
	
	if(!$res)
	{
		if($ar)
		{
			fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
		}
		
		goto inventario;
	}
	
	$num=mysql_num_rows($res);
	$trans="(-1";
	
	
	$num=mysql_num_rows($res);
	
	for($i=0;$i<$num;$i++)
	{
	
		$err=0;
		$row=mysql_fetch_assoc($res);
		
		
		mysql_query("BEGIN", $lnk);
		mysql_query("BEGIN", $link);
		
		
		
		//Buscamos si existe localmente
		$sql="	SELECT
				id_movimiento_almacen,
				IF(
					TIMEDIFF(ultima_sincronizacion, '".$row['ultima_actualizacion']."') < '00:00:30',
					1,
					0
				)
				FROM ec_movimiento_almacen
				WHERE id_equivalente='".$row['id_movimiento_almacen']."'";
				
				
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		if(mysql_num_rows($re) > 0)
		{
			$existe=1;
			$ro=mysql_fetch_row($re);
			if($ro[1] == '0')
			{
				mysql_query("COMMIT", $lnk);
				mysql_query("COMMIT", $link);
				continue;
			}
			$id_movimiento_nuevo=$ro[0];	
					
		}	
		else
			$existe=0;		
		
		
		if($existe == 0)		
			$sql="	INSERT INTO ec_movimiento_almacen
					SET";
		else	
			$sql="	UPDATE ec_movimiento_almacen
					SET";
					
					
		$sql.="	id_tipo_movimiento='".$row['id_tipo_movimiento']."',
				id_usuario=1,
				id_sucursal='".$row['id_sucursal']."',
				fecha='".$row['fecha']."',
				hora='".$row['hora']."',
				observaciones='".$row['observaciones']."',
				id_pedido=-1,
				id_orden_compra=-1,
				lote='',
				id_maquila=-1,
				id_transferencia=-1,
				id_almacen='".$row['id_almacen']."',
				id_equivalente='".$row['id_movimiento_almacen']."',
				ultima_sincronizacion='".getDateTime($lnk)."'";
				
				
		if($existe == 1)					
		{
			$sql.="	WHERE id_movimiento_almacen='$id_movimiento_nuevo'";
		}
		
		
		$re=mysql_query($sql, $link);
			
		if(!$re)
		{
			if($ar)
			{
				fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
				mysql_query("ROLLBACK", $lnk);
				mysql_query("ROLLBACK", $link);	
				$err++;			
			}
			continue;
		}
		
		
		if($existe == 0)					
		{
			$id_movimiento_nuevo=mysql_insert_id($link);
		}
		
		
		
		if($existe == 1)					
		{
			//Borramos datos
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE id_movimiento_almacen=".$id_movimiento_nuevo;
			
			$re=mysql_query($sql, $link);
			
			if(!$re)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;			
				}
				continue;
			}
			
			
			
		}
		
		if($err == 0)
		{
			//Insertamos el detalle
			$sql="	SELECT
					id_producto,
					cantidad,
					cantidad_surtida,
					id_pedido_detalle,
					id_oc_detalle
					FROM ec_movimiento_detalle
					WHERE id_movimiento=".$row['id_movimiento_almacen'];
						
			$re2=mysql_query($sql, $lnk);		
			if(!$re2)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;	
				}
				continue;
			}
				
			$nu2=mysql_num_rows($re2);
				
			for($k=0;$k<$nu2;$k++)
			{
				$ro2=mysql_fetch_assoc($re2);
					
				$sql="	INSERT INTO ec_movimiento_detalle
						SET
						id_movimiento='$id_movimiento_nuevo',
						id_producto='".$ro2['id_producto']."',
						cantidad='".$ro2['cantidad']."',
						cantidad_surtida='".$ro2['cantidad_surtida']."',
						id_pedido_detalle=-1,
						id_oc_detalle=-1";
							
				$re3=mysql_query($sql, $link);		
				if(!$re3)
				{
					if($ar)
					{
						fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($link)."\n a las ".date('Y-m-d H:i:s'));
						mysql_query("ROLLBACK", $lnk);
						mysql_query("ROLLBACK", $link);	
						$err++;	
						$k=$nu2+1;		
					}
					continue;
				}		
			}
						
				
		}
		
		if($err == 0){
		
			//Actualizamos el movimiento en linea
			$sql="	UPDATE ec_movimiento_almacen
					SET
					id_equivalente=$id_movimiento_nuevo,
					ultima_sincronizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
					WHERE id_movimiento_almacen=".$row['id_movimiento_almacen'];
						
			$re2=mysql_query($sql, $lnk);		
			if(!$re2)
			{
				if($ar)
				{
					fwrite($ar, "\nError en la consulta:\n$sql\nDescripcion:".mysql_error($lnk)."\n a las ".date('Y-m-d H:i:s'));
					mysql_query("ROLLBACK", $lnk);
					mysql_query("ROLLBACK", $link);	
					$err++;	
				}
				continue;
			}
		}
		
		if($err == 0)
		{
			mysql_query("COMMIT", $lnk);		
			mysql_query("COMMIT", $link);		
		}
	
	}	
	
	
fin:	
	

	fclose($ar);
	
	
	
	
	cierra($root, $link);
	
	
	
	
	


?>