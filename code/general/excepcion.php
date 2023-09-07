<?php


	if($tabla == 'ec_devolucion_transferencia')
	{
	
	
		
		if($accion == 'eliminar')
		{
			
			$sql="DELETE FROM ec_movimiento_almacen WHERE observaciones LIKE '%$folio%'";
			
			//die($sql);
			
			$res=mysql_query($sql);   
            if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
		
		}
	}	



    if($tabla == 'ec_comparacion_inv')
    {
        //echo "$accion - $tipo";
        if($accion == 'insertar')
        {
            
            //echo "Dato: $url_final";
            
            $ruta=str_replace("/var/www/vhosts/easycount.com.mx/httpdocs/billarmex/files/", "../../files/", $url_final);
            
            $ar=fopen($ruta, "rt");
            if($ar)
            {
                $nver=0;
                while(!feof($ar))
               {
                    $cadaux=fgets($ar, 1000);
                    //echo "Fila: $cadaux<br><br>";
                    
                    $arrs=explode(",", $cadaux);
                    $nver++;
                    
                    if($nver > 1 && $cadaux != '')
                    {
                    
                    $sq="INSERT INTO ec_comparacion_detalle
                         (
                            SELECT
                            null,
                            $llave,
                            $arrs[0],
                            ".$arrs[sizeof($arrs)-1].",
                            (
                                SELECT
                                IF(SUM(md.cantidad_surtida*tm.afecta) IS NULL, 0, SUM(md.cantidad_surtida*tm.afecta))
                                FROM ec_movimiento_detalle md
                                JOIN ec_movimiento_almacen m ON md.id_movimiento = m.id_movimiento_almacen
                                JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
                                WHERE md.id_producto=p.id_productos
                                AND m.id_sucursal=$user_sucursal
                            ),
                            0
                            FROM ec_productos p
                            WHERE id_productos=$arrs[0]
                         )";
                         
                    if(!mysql_query($sq))
                    {
                                
                        echo mysql_error();    
                        
                         mysql_query("ROLLBACK");
                        Muestraerror($smarty, "", "", mysql_error(), $sq, "contenido.php");
                    } 
                    
                    $id_det=mysql_insert_id();
                    
                    $sq="UPDATE ec_comparacion_detalle SET diferencia=ABS(cantidad_fisica-cantidad_virtual) WHERE id_comparacion_detalle=$id_det";
                    
                    if(!mysql_query($sq))
                    {
                         mysql_query("ROLLBACK");
                        Muestraerror($smarty, "", "3", mysql_error(), $sq, "contenido.php");
                    } 
                    
                    }                        
                    
               }     
            }
            /*else
                echo "No: $ruta";*/
            fclose($ar);
            
            //die();
        }
        
        if($tipo == 0)
        {
            $sql="SELECT
                  id_productos,
                  nombre,
                  ''
                  FROM ec_productos
                  ORDER BY nombre";
                  
            $res=mysql_query($sql);      
            if(!$res)
            {
                mysql_query("ROLLBACK");
                Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
            }
            
            $ar=fopen("../../files/productos.csv", "wt");
            if($ar)
            {
                $num=mysql_num_rows($res);
                fputs($ar, "ID,Producto,Cantidad\n");
                for($i=0;$i<$num;$i++)
                {
                    $row=mysql_fetch_row($res);
                    fputs($ar, $row[0].",".$row[1].",0\n");
                }
            }
            fclose($ar);
            
            
            $file=$rooturl."/files/productos.csv";
            
            //echo $file;
            
            $smarty->assign("file_muestra", $file);
            
        }
    }


	if($tabla == 'ec_transferencias')
	{
	//	echo "SI $tabla<b>$accion<br>$llave";
		
	//echo 'table:'.$tabla;
	//echo '<br>no_tabla: '.$no_tabla;	
	//	die();	
	}

	if($tabla == 'ec_rutas')
	{
		if($accion == 'actualizar' || $accion == 'insertar')
		{
			if($entregado == '1')
			{
				$sql="UPDATE ec_pedidos p
				      JOIN ec_rutas_pedidos r ON p.id_pedido = r.id_pedido
				      SET
				      p.enviado=1
				      WHERE r.id_ruta='$llave'";
					  
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}	  
			}
		}
	}


	if($tabla == 'ec_pedidos' && $no_tabla == 4)
	{

		if($accion == "actualizar")
		{
			
			//Buscamos la diferencia por partida
			$sql="SELECT 
			      d.id_pedido_detalle,
			      d.id_producto,
			      d.cantidad_surtida,
			      (
			      	SELECT
			      	IF(SUM(cantidad_surtida) IS NULL, 0, SUM(cantidad_surtida))
			      	FROM ec_movimiento_detalle
			      	WHERE id_pedido_detalle = d.id_pedido_detalle
			      ),
			      d.cantidad,
			      (
			      	SELECT id_almacen 
			      	FROM ec_almacen
			      	WHERE es_almacen AND id_almacen>-1 AND id_sucursal = $user_sucursal
			      	HAVING MIN(prioridad) 
			      )
			      FROM ec_pedidos_detalle d
			      WHERE id_pedido=$llave";
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}	  
	      	$num=mysql_num_rows($res);	  
			$camb=1;
			
			for($i=0;$i<$num;$i++)
			{
				$row=mysql_fetch_row($res);
				if($row[2] != $row[3])
				{
					
					if($row[2] > $row[3])
						$tipo_mov=2;
					else
						$tipo_mov=1;
					
					$cantSurt=abs($row[2]-$row[3]);
					
					
					$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											 VALUES($tipo_mov, $user_id, $user_sucursal, NOW(), NOW(), 'Pedido surtido', $llave, -1, '', -1, -1, $row[5])";
											 
					if(!mysql_query($sql))
					{
						mysql_query("ROLLBACK");
						Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
					}
					
					$id_movimiento=mysql_insert_id();
					
					$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						 							 VALUES($id_movimiento, $row[1], $cantSurt, $cantSurt, $row[0], -1)";
													 
													 
					if(!mysql_query($sql))
					{
						mysql_query("ROLLBACK");
						Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
					}
			
					
				}
				if($row[4] > $row[2])
					$camb=0;
			}

			if($camb == 1)
				$sql="UPDATE ec_pedidos SET id_estatus=5, surtido=1 WHERE id_pedido=$llave";
			else	  
				$sql="UPDATE ec_pedidos SET id_estatus=6 WHERE id_pedido=$llave";
			
			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
		}
	}
	
	
	if($tabla == 'ec_ordenes_compra' && $no_tabla == 2)
	{

		if($accion == "actualizar")
		{
			
			//Buscamos la diferencia por partida
			$sql="SELECT 
			      d.id_oc_detalle,
			      d.id_producto,
			      d.cantidad_surtido,
			      (
			      	SELECT
			      	IF(SUM(cantidad_surtida) IS NULL, 0, SUM(cantidad_surtida))
			      	FROM ec_movimiento_detalle
			      	WHERE id_oc_detalle = d.id_oc_detalle
			      ),
			      cantidad,
			      (
			      	SELECT id_almacen 
			      	FROM ec_almacen
			      	WHERE es_almacen AND id_almacen>-1 AND id_sucursal = $user_sucursal
			      	HAVING MIN(prioridad) 
			      )
			      FROM ec_oc_detalle d
			      WHERE id_orden_compra=$llave";
			
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}	  
	      	$num=mysql_num_rows($res);	  
			$camb=1;
			
			for($i=0;$i<$num;$i++)
			{
				$row=mysql_fetch_row($res);
				if($row[2] != $row[3])
				{
					
					if($row[2] > $row[3])
						$tipo_mov=1;
					else
						$tipo_mov=2;
					
					$cantSurt=abs($row[2]-$row[3]);
					
					if($row[1] == '5')
						Muestraerror($smarty, "", "SN", "No hay un almac&eacute;n primo configurado para esta sucursal", "NA", "postexcepcion.php");
					
					$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											 VALUES($tipo_mov, $user_id, $user_sucursal, NOW(), NOW(), 'OC recibida', -1, $llave, '', -1, -1, $row[5])";
					
					
											 
					if(!mysql_query($sql))
					{
						mysql_query("ROLLBACK");
						Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
					}
					
					$id_movimiento=mysql_insert_id();
					
					$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						 							 VALUES($id_movimiento, $row[1], $cantSurt, $cantSurt, -1, $row[0])";
													 
													 
					if(!mysql_query($sql))
					{
						mysql_query("ROLLBACK");
						Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
					}
			
					
				}

				// Si  cantidad > cantidad_surtido
				if($row[4] > $row[2])
					$camb=0;
			}

			if($camb == 1)
			{
				$sql="UPDATE ec_ordenes_compra SET id_estatus_oc=4, surtida=1 WHERE id_orden_compra=$llave";
			}	
			else	  
				$sql="UPDATE ec_ordenes_compra SET id_estatus_oc=5 WHERE id_orden_compra=$llave";
			
			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
		}
	}	



	if($tabla == 'ec_maquila')
	{
		if($accion == 'insertar')
		{
			$sql = "SELECT id_almacen 
			      	FROM ec_almacen
			      	WHERE id_sucursal = $user_sucursal
					AND es_almacen=1
			      	ORDER BY prioridad
					LIMIT 1";
			
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			$row=mysql_fetch_row($res);
			
			if($row[0] == '')
				Muestraerror($smarty, "", "NA", "No hay un almac&eacute;n primo configurado para esta sucursal", "$sql", "excepcion.php");
			
			//Insertamos el movimiento de entrada
			$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											 VALUES(3, $user_id, $user_sucursal, NOW(), NOW(), 'Generado por maquila', -1, -1, '', $llave, -1, $row[0] )";
											 
			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			$id_movimiento=mysql_insert_id();
			
			$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
				 							 VALUES($id_movimiento, $id_producto, $cantidad, $cantidad, -1, -1)";
											 
											 
			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			
			//Insertamos los movimientos de salida
			$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
											 VALUES(4, $user_id, $user_sucursal, NOW(), NOW(), 'Generado por maquila', -1, -1, '', $llave, -1, $row[0])";
											 
			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			$id_movimiento=mysql_insert_id();	
			
			
			$sql="SELECT
	      		  d.id_producto_ordigen,
	      		  cantidad
	      		  FROM ec_productos_detalle d
	      		  WHERE d.id_producto=$id_producto";
	      		  
	      	$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}	  
	      	$num=mysql_num_rows($res);
			for($i=0;$i<$num;$i++)
			{
				$row=mysql_fetch_row($res);
				$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
				 							     VALUES($id_movimiento, ".$row[0].", ".$row[1]*$cantidad.", ".$row[1]*$cantidad.", -1, -1)";
											 
											 
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}
			}	  
	      		  									
														 								 	
		}
	}




	if($tabla == 'ec_pedidos' && $no_tabla == 1)
	{
		if($accion == 'insertar' || $accion == 'actualizar')
		{
			
			//buscamos el total abonado
			$sql="SELECT
			      IF(SUM(monto) IS NULL, 0, SUM(monto)),
			      MAX(fecha)
			      FROM ec_pedido_pagos
			      WHERE id_pedido=$llave";
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			$row=mysql_fetch_row($res);
			$abono=$row[0];
			$fecmax=$row[1];
			
			
			//Creamos la CxP en caso de noexistir
			$sql="SELECT
			      id_cxc
			      FROM ec_cuentas_por_cobrar
			      WHERE id_pedido=$llave";
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			if(mysql_num_rows($res) <= 0)
			{
				//echo $total;
				
				
				
				$sql="INSERT INTO ec_cuentas_por_cobrar(id_cliente, monto, abonado, dias_pago, fecha_ultimo_cobro, id_sucursal, id_pedido, id_nc)
												VALUES($id_cliente, $total, $abono, 0, '$fecmax', $user_sucursal, $llave, -1)";	  
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}
			}	
			else
			{
				$row=mysql_fetch_row($res);
				$idcxp=$row[0];
				
				$sql="UPDATE ec_cuentas_por_cobrar
				      SET
				      monto=$total,
				      abonado=$abono,
				      fecha_ultimo_cobro='$fecmax'
				      WHERE id_cxc=$idcxp";
					  
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}	  
				
			}		  	  
			
		}
	}
	
	
/*************************Deshabilitado por Oscar 02.08.2019 porque causa error al guardar la orde de compra***************************/
	/*if($tabla == 'ec_ordenes_compra' && $no_tabla == 1)
	{
		if($accion == 'insertar' || $accion == 'actualizar')
		{
			
		//buscamos el total abonado
			$sql="SELECT
			      IF(SUM(monto) IS NULL, 0, SUM(monto)),
			      MAX(fecha)
			      FROM ec_oc_pagos
			      WHERE id_oc=$llave";
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			$row=mysql_fetch_row($res);
			$abono=$row[0];
			$fecmax=$row[1];
			
			
			//Creamos la CxP en caso de noexistir
			$sql="SELECT
			      id_cxp
			      FROM ec_cuentas_por_pagar
			      WHERE id_oc=$llave";
			$res=mysql_query($sql);	  
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			
			if(mysql_num_rows($res) <= 0)
			{
				//echo $total;
				
				if($dias_pago == '')
					$dias_pago=0;
				
				$sql="INSERT INTO ec_cuentas_por_pagar(id_proveedor, monto, abonado, dias_pago, fecha_ultimo_pago, id_sucursal, id_oc, id_nc)
												VALUES($id_proveedor, $total, $abono, $dias_pago, '$fecmax', $user_sucursal, $llave, -1)";	  
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}
			}	
			else
			{
				$row=mysql_fetch_row($res);
				$idcxp=$row[0];
				
				$sql="UPDATE ec_cuentas_por_pagar
				      SET
				      monto=$total,
				      abonado=$abono,
				      fecha_ultimo_pago='$fecmax'
				      WHERE id_cxp=$idcxp";
					  
				if(!mysql_query($sql))
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}	  
			}		  	  
			
		}
	}
*/

?>