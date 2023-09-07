<?php	
	header("Content-Type: text/plain;charset=utf-8");
	
include("../../conexionDoble.php");
	
	$es_pedido = isset($_GET["pe"]) ? $_GET["pe"] : "0";
	$es_paquete = isset($_GET["pa"]) ? $_GET["pa"] : "0";
	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	$id_pedid2 = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	$folio = "0";
	$nitems = $_GET["nitems"];
	$descuento = 0;
	$prefijo = "";
	$es_nuevo_registro = !($id_pedido > 0);
	$tipo_folio = $es_pedido ? "pedido" : "nv";
	$ix_regalo = isset($_GET["reg"]) ? $_GET["reg"] : null;
	
	try{
		mysql_query("BEGIN",$local);
		
		// Si el ID del pedido = 0, generar nuevo ID
		if ($es_nuevo_registro){
			$cs="SELECT
					IF(
						ISNULL(MAX(id_pedido)),
						1,
						MAX(id_pedido)+1
					) AS maxid
					FROM ec_pedidos
					WHERE id_pedido > 0 ";
			if ($rs = mysql_query($cs,$local)){
				if ($dr = mysql_fetch_assoc($rs))
				{
					$id_pedido = $dr["maxid"];
				}
				mysql_free_result($rs);
			}
			else{
				throw new Exception ("No se consiguió un nuevo ID de pedido.");
			}
		}
		
		// Conseguir algunos datos de la sucursal
		$cs = "	SELECT
				descuento,
				prefijo
				FROM sys_sucursales
				WHERE id_sucursal = '{$user_sucursal}'";
		if ($rs = mysql_query($cs,$local)){
			if ($dr = mysql_fetch_assoc($rs))
			{
				$descuento = $es_paquete ? $dr["descuento"] : 0;
				$prefijo = $dr["prefijo"];
			}
			mysql_free_result($rs);
		}else{
			throw new Exception ("No se consiguió descuento/prefijo de la sucursal.");
		}
		
		// Conseguir un nuevo folio para la venta/pedido
		$cs = "	SELECT
				CONCAT(
					'{$prefijo}',
					IF(
						ISNULL(MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_pedidos
				WHERE REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') REGEXP ('[0-9]')
				AND id_sucursal='{$user_sucursal}'
				AND id_pedido <> '{$_GET["idp"]}'";
		if ($rs = mysql_query($cs,$local))
		{
			if ($dr = mysql_fetch_assoc($rs))
			{
				$folio = $dr["folio"];
			}
			mysql_free_result($rs);
		}
		else
		{
			throw new Exception ("No se consiguió un nuevo folio ({$tipo_folio}).");
		}
		
		# Guardar el encabezado
		if ($es_nuevo_registro)
			$cs = "	INSERT INTO ec_pedidos
					SET
					id_pedido = '{$id_pedido}',
					id_cliente = '1',
					id_estatus = '2',
					id_moneda = '1',
					fecha_alta = NOW(),
					id_direccion = '-1',
					id_razon_social = '-1',
					subtotal = '0',
					iva = '0',
					ieps = '0',
					total = '0',
					pagado = '0',
					surtido = '0',
					enviado = '0',
					id_sucursal = '{$user_sucursal}',
					id_usuario = '{$user_id}', ";
		else
			$cs = "UPDATE ec_pedidos SET ";
		
        
		if($es_pedido)
			$cs.="folio_pedido = '{$folio}',";
		else
			$cs.="folio_nv = '{$folio}',";
		
		$cs.= "	fue_cot = '0',
				facturado = '0',
				id_tipo_envio = '1',
				descuento = '0',
				id_razon_factura = NULL ";
		
		if(!$es_nuevo_registro)
			$cs.= "WHERE id_pedido = '{$id_pedido}' ";
			
		if (!mysql_query($cs,$local))
			throw new Exception("Imposible almacenar este registro (pedidos). $cs" . mysql_error());
        

		//Actualizamos el folio de nota de venta
		if (!$es_nuevo_registro)
		{
			$sql="	UPDATE ec_pedidos
					SET
					folio_nv='{$folio}'
					WHERE id_pedido = '{$id_pedido}'";
			if (!mysql_query($sql,$local))
				throw new Exception("Imposible almacenar este registro (pedidos). " . mysql_error());
		}   
        
		
		# Conseguir el IVA del sistema
		$iva = 0.16;
		$ieps = 0.30;
		$cs = "SELECT iva/100 AS iva, ieps/100 AS ieps FROM ec_conf_gral ";
		if ($rs = mysql_query($cs,$local))
		{
			if ($dr = mysql_fetch_assoc($rs))
			{
				$iva = $dr["iva"];
				$ieps = $dr["ieps"];
			}
			mysql_free_result($rs);
		}
		else
		{
			throw new Exception ("No se consiguió el parámetro IVA/IEPS del sistema.");
		}
		
		$pedido_subtotal = 0.0;
		$pedido_iva = 0.0;
		$pedido_ieps = 0.0;
		$pedido_total = 0.0;
		$pedido_descuento = 0.0;
		
		# Guardar el detalle
		for ($ix=0; $ix<$nitems; ++$ix)
		{
			$existe_detalle = false;
			$id_detalle = "0";
			
			$cs="	SELECT
					id_pedido_detalle
					FROM ec_pedidos_detalle
					WHERE id_pedido = '{$id_pedido}'
					AND id_producto = '{$_GET["idp{$ix}"]}' ";
					
			if ($rs = mysql_query($cs,$local))
			{
				if ($dr = mysql_fetch_assoc($rs))
				{
					$existe_detalle = true;
					$id_detalle = $dr["id_pedido_detalle"];
				}
				mysql_free_result($rs);
			}
			else
			{
				throw new Exception ("No se consiguió el detalle del pedido (pedido: {$id_pedido}, producto: {$_GET["idp{$ix}"]}).");
			}
			
			if (!$existe_detalle)
			{
				$cs="	SELECT
						IF(
							ISNULL(MAX(id_pedido_detalle)),
							1,
							MAX(id_pedido_detalle)+1
						) AS maxid
						FROM ec_pedidos_detalle ";
				if ($rs = mysql_query($cs,$local))
				{
					if ($dr = mysql_fetch_assoc($rs))
					{
						$id_detalle = $dr["maxid"];
					}
					mysql_free_result($rs);
				}
				else
				{
					throw new Exception ("No se consiguió un nuevo ID para el detalle del pedido.");
				}
			}
			
			
			
			# Guardar el registro del detalle 
			if (!$existe_detalle)
				$cs = "	INSERT INTO ec_pedidos_detalle
						SET
						id_pedido = '{$id_pedido}',
						id_producto = '{$_GET["idp{$ix}"]}',
						cantidad_surtida = '0', ";
			else
				$cs = "UPDATE ec_pedidos_detalle SET ";
			
			$cs.="	cantidad = '{$_GET["can{$ix}"]}',
					precio = '{$_GET["pre{$ix}"]}',
					monto = '{$_GET["mon{$ix}"]}',
					iva = '0',
					ieps = '0' ";
			
			if ($existe_detalle)
				$cs .= "WHERE id_pedido_detalle = '{$id_detalle}' ";
				
			if (!mysql_query($cs,$local))
				throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$cs\n\n" . mysql_error());
            
            $pedido_subtotal+=$_GET["mon{$ix}"];	
            
            //Insertamos el movimiento de almacen
			if(!$es_pedido)
            {
                //Buscamos el almacen
                $sql="	SELECT
						a.id_almacen,
						a.prioridad,
						(
							SELECT
                        	SUM(d.cantidad_surtida*tm.afecta)
                        	FROM ec_movimiento_detalle d
                        	JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                        	JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                        	WHERE a.id_almacen = aa.id_almacen
                        	AND d.id_producto={$_GET["idp{$ix}"]}
						)
						FROM ec_almacen a
						WHERE a.id_sucursal=$user_sucursal
						AND a.es_almacen=1
						AND a.id_almacen <> -1 
						ORDER BY prioridad";
                      
				$res=mysql_query($sql,$local);
                if(!$res)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                
                $num=mysql_num_rows($res);                    
                    
                $canSur=$_GET["can{$ix}"];                  
                
                for($i=0;$i<$num;$i++)
                {
                	$row=mysql_fetch_row($res);
                    
                    //print_r($row);
                    
                    if($i == 0)
                        $almacenPri=$row[0];
                    
                    if($row[2] > 0)
                    {
                        $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                         VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_pedido}, -1, '', -1, -1, $row[0])";
                    
                        //echo $sql;
                    
                        if (!mysql_query($sql,$local))
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
                        $id_mov=mysql_insert_id($local);
                        
                        if($canSur > $row[2])
                        {
                            $can=$row[2];
                            $canSur-=$row[2];
                        }
                        else
                        {
                            $can=$canSur;
                            $canSur=0;
                        }
                        
                        $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                         VALUES($id_mov, {$_GET["idp{$ix}"]}, $can, $can, -1, -1)";
                                                         
                        //echo $sql;                                                         
                        if (!mysql_query($sql,$local))
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
                    }
                    else
                    {
                        continue;
                    }    
                        
                    if($canSur == 0)
                        break;                        
                }

                //die("NO");

                 //Insertamos el resto en el alamcen primario
                if($canSur > 0)
                {
                    
                    if(!isset($almacenPri))
                        $almacenPri=-1;
                    
                    $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                             VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_pedido}, -1, '', -1, -1, $almacenPri)";
                        
                    if (!mysql_query($sql,$local)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
                    $id_mov=mysql_insert_id($local);
                    
                    $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                             VALUES($id_mov, {$_GET["idp{$ix}"]}, $canSur, $canSur, -1, -1)";
                    if (!mysql_query($sql,$local)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                }
            }

            //buscamos si hay alerta
            
            $sql="SELECT alertas_resurtimiento FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
            
            $res=mysql_query($sql,$local);
            if(!$res)
                throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                
           $row=mysql_fetch_row($res);
           
           if($row[0] == '1')
           {
            
        
                $sql="SELECT
                      SUM(d.cantidad_surtida*tm.afecta)
                      FROM ec_movimiento_detalle d
                      JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                      JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                      WHERE aa.id_sucursal = $user_sucursal
                      AND d.id_producto={$_GET["idp{$ix}"]}";
                      
                $res=mysql_query($sql,$local);
                if(!$res)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
               $row=mysql_fetch_row($res);
               $existencia=$row[0];     
                
                
                $sql="SELECT
                      minimo
                      FROM ec_estacionalidad_producto ep
                      JOIN ec_estacionalidad e ON ep.id_estacionalidad = ep.id_estacionalidad
                      JOIN ec_periodos p ON p.id_periodos = e.id_periodo
                      JOIN ec_periodos_detalle pd ON p.id_periodos = pd.id_periodo
                      WHERE ep.id_producto={$_GET["idp{$ix}"]}
                      AND pd.fecha = DATE_FORMAT(NOW(), '%Y-%m-%d')";
                      
                $res=mysql_query($sql,$local);
                if(!$res)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
               $row=mysql_fetch_row($res);
               $min=$row[0];
               
               if($existencia <= $min){
                   $sql="INSERT INTO ec_alerta(nombre, fecha, hora, tipo)
                                        VALUES('Producto con existencia urgente en la sucursal ', NOW(), NOW(), 'code/general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfdHJhbnNmZXJlbmNpYXM=&a1de185b82326ad96dec8ced6dad5fbbd=MA==&a01773a8a11c5f7314901bdae5825a190=bnVsbA==&bnVtZXJvX3RhYmxh=MA==')";
                   if (!mysql_query($sql,$local)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
                   
                   $id_alerta=mysql_insert_id($local);
                   
                   //Insertamos al usuario de mercancias
                   $sql="SELECT id_encargado FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
                   
                   $res=mysql_query($sql,$local);
                   if(!$res)
                        throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
                   $row=mysql_fetch_row($res);
                   
                   $sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
                                                 VALUES($id_alerta, $row[0], '', 0)";
                                                 
                   if (!mysql_query($sql,$local)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
                   //Buscamos a los administradores
                   $sql="SELECT id_usuario FROM sys_users WHERE administrador=1 AND id_usuario NOT IN($row[0])";
                   
                   $res=mysql_query($sql,$local);
                   if(!$res)
                        throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
                   $num=mysql_num_rows($res);
                   
                   for($i=0;$i<$num;$i++)
                   {
                       $row=mysql_fetch_row($res);
                       
                       $sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
                                                 VALUES($id_alerta, $row[0], '', 0)";
                                                 
                       if (!mysql_query($sql,$local)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   }
                                                   
               }
            }
            		
		}
		
		
       		      

        #$pedido_iva=$pedido_subtotal/(1+$iva);
        #$pedido_total=$pedido_subtotal;
        #$pedido_subtotal=$pedido_total-$pedido_iva;
        
        
        //$pedido_total=$pedido_subtotal-$pedido_descuento;
        
        $pedido_descuento=$pedido_subtotal-$_GET["totalPed"];
		
		# Actualizar los valores pendientes del encabezado
		$cs = "UPDATE ec_pedidos SET " .
			"subtotal = '{$pedido_subtotal}', " .
			"iva = '0', " .
			"ieps = '0', " .
			"total = '".$_GET["totalPed"]."', " .
			"descuento = '{$pedido_descuento}' " .
			"WHERE id_pedido = '{$id_pedido}' ";
		
		if (!mysql_query($cs,$local)) throw new Exception("Imposible actualizar la segunda parte del pedido.\n\n$cs\n\n" . mysql_error());
			mysql_query("COMMIT",$local);
			echo "OK|IDP:{$id_pedido}|FOLIO:{$folio}";
		
	} catch (Exception $e) {
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK",$local);
		mysql_close();
		exit ();
	}
?>