<?php
	
	header("Content-Type: text/plain;charset=utf-8");
	include("../../include/PHPMailer/PHPMailerAutoload.php");
	include("../../conectMin.php");
	
	$es_apartado = isset($_GET["ap"]) ? $_GET["ap"] : "0";
	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	
	$nitems = $_GET["nitems"];
	$face=$_GET["faceb"];
	$corr=$_GET["cor"];
	
	try{
		mysql_query("BEGIN");
		$tipo_folio="nv";
	// Conseguir algunos datos de la sucursal
		$cs = "	SELECT
				descuento,
				prefijo
				FROM sys_sucursales
				WHERE id_sucursal = '{$user_sucursal}'";
		if ($rs = mysql_query($cs)){
			if ($dr = mysql_fetch_assoc($rs)){
				$descuento = $es_paquete ? $dr["descuento"] : 0;
				$prefijo = $dr["prefijo"];
			}
			mysql_free_result($rs);
		}else{
			throw new Exception ("No se consiguió descuento/prefijo de la sucursal.");
		}
	//Conseguir un nuevo folio para la venta/pedido
		$cs ="SELECT
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
				AND id_sucursal='{$user_sucursal}'";
		if ($rs=mysql_query($cs)){
			if($dr = mysql_fetch_assoc($rs)){
				$folio = $dr["folio"];
			}
			mysql_free_result($rs);
		}else{
			throw new Exception ("No se consiguió un nuevo folio ({$tipo_folio}).");
		}
/**********************Insertamos los datos reales******************/


		//Insertamos la cabecera del pedido
		$sql="	INSERT INTO ec_pedidos
			  	(
					SELECT
					/*1*/null,/*2*/folio_pedido,/*3*/'$folio',/*4*/folio_factura,/*5*/folio_cotizacion,/*6*/id_cliente,/*7*/id_estatus,
					/*8*/id_moneda,/*9*/NOW(),/*10*/fecha_factura,/*11*/id_direccion,/*12*/	direccion,/*13*/id_razon_social,/*14*/subtotal,
					/*15*/iva,/*16*/ieps,/*17*/	total,/*18*/dias_proximo,/*19*/pagado,/*20*/surtido,/*21*/enviado,/*22*/id_sucursal,/*23*/id_usuario,
					/*24*/fue_cot,/*25*/facturado,/*26*/id_tipo_envio,/*27*/descuento,/*28*/id_razon_factura,/*29*/folio_abono,/*30*/'$corr',/*31*/'$face',/*32*/NULL,
					/*33*/'0000-00-00 00:00:00',/*34*/NOW()
					/*A PARTIR DE AQUI SON MODIFICACIONES DE IVAN....
					/*33	-1,
					/*34	-1,
					/*35	0,
					/*36	0*/
					FROM ec_pedidos_back
					WHERE id_pedido=$id_pedido
				)";
		

		$res=mysql_query($sql);
		
		if(!$res)
			throw new Exception("No se pudo insertar la nota de venta\n\n$sql\n\n" . mysql_error());	
			
		$id_pedido_r=mysql_insert_id();

	//Insertamos el detalle del pedido
		$sql="	INSERT INTO ec_pedidos_detalle
				(
					SELECT
					null,
					$id_pedido_r,
					id_producto,
					cantidad,
					precio,
					monto,
					iva,
					ieps,
					cantidad_surtida,
					descuento
					FROM ec_pedidos_detalle_back
					WHERE id_pedido=$id_pedido
				)";
		$res=mysql_query($sql);
		
		if(!$res){
			die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
	//Insertamos el movimiento de almacen producto x producto
		$sql="SELECT
				pd.id_producto AS id_prod,
				pd.cantidad AS can_s,
				p.es_maquilado AS maquilado
				FROM ec_pedidos_detalle_back pd
				LEFT JOIN ec_productos p on pd.id_producto=p.id_productos
				WHERE id_pedido=$id_pedido";
		//echo $sql;
		$res=mysql_query($sql);
		if(!$res){
			die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
		$num=mysql_num_rows($res);
		for($i=0;$i<$num;$i++){
			$row=mysql_fetch_assoc($res);
			extract($row);
	//Buscamos el almacen correspondiente
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
        	            AND d.id_producto=$id_prod
					)
					FROM ec_almacen a
					WHERE a.id_sucursal=$user_sucursal
					AND a.id_almacen <> -1 
					ORDER BY es_almacen DESC, prioridad";
      //           die($sql);
			$re=mysql_query($sql);
	        if(!$re){
				die($sql);
				throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
	        }
			$nu=mysql_num_rows($re);
			$canSur=$can_s;
			//Recorremos los almacenes
	//		echo 'num:'.$nu;
			for($j=0;$j<1;$j++){
				$ro=mysql_fetch_row($re);
				if($j == 0){
					$almacenPri=$ro[0];
				}
				//echo 'jkebfkñwebf';
			//Si existe inventario en el almacen (aqui modifique deberia de ser mayor a cero)    
				//echo 'ro2: '.$ro[2];
				//if($ro[2]>-10000){
				//echo'here';
					//Insertamos cabecera
					$sql="	INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
							VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_pedido_r}, -1, '', -1, -1, $ro[0])";
                    
                        //echo $sql;
                    
					if (!mysql_query($sql)){
						
			die($sql);
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
					}
                    
					$id_mov=mysql_insert_id();
                        
					if($canSur > $ro[2]){
						$can=$ro[2];
						$canSur-=$ro[2];
                    }else{
						$can=$canSur;
						$canSur=0;
					}
			//echo 'hasta aqui';
					if($maquilado==1){
						//echo 'here is maquile';
						$aux="SELECT id_producto_ordigen,cantidad 
								FROM ec_productos_detalle WHERE id_producto=$id_prod";
						$ejeAux=mysql_query($aux) or die('ERROR!!<br>'.$aux);
						$nM=mysql_num_rows($ejeAux);
						if($nM>0){
							while($dA=mysql_fetch_row($ejeAux)){
								$suma=$dA[1]*$can_s;
								//echo 'suma:'.$dA[1].' * '. $can_s.' = '.$suma;
								$sqlAux="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
									VALUES('$id_mov','$dA[0]','$suma','$suma',-1,-1)";
								$ejeSqlAux=mysql_query($sqlAux)or die('ERRROR!!!'.$sqlAux);
								//echo $sqlAux.'<br>';
							}
						}
					}else if($maquilado==0){
						//echo'no maquilado';
			//Insertamos detalle   
						$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
								VALUES('$id_mov','$id_prod',$can_s,$can_s,'-1','-1')";
						if (!mysql_query($sql)){
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
						}
					}//echo $sql;
            //si no hay inventario
				/*}else{
					continue;
				}*/   
            //Si se ha terminado de surtir        
				if($canSur == 0){
					break;                        
				}
		}
			
			
		/*
			//Insertamos el excendente en el primer almacen primario
			if($canSur > 0)
			{
                    
				if(!isset($almacenPri))
					$almacenPri=-1;
                    
				$sql="	INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
						VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_pedido_r}, -1, '', -1, -1, $almacenPri)";
                        
				if (!mysql_query($sql))
					throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
				$id_mov=mysql_insert_id();
				
                $sql="	INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
						VALUES($id_mov, {$id_prod}, $canSur, $canSur, -1, -1)";
                if (!mysql_query($sql))
					throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
			}
			
		*/	
			
		//buscamos si hay alerta
            $sql="	SELECT
					alertas_resurtimiento,
					p.omitir_alertas
					FROM sys_sucursales s
					JOIN ec_productos p ON p.id_productos={$id_prod}
					WHERE s.id_sucursal=$user_sucursal";
            
            $re=mysql_query($sql);
            if(!$re)
                throw new Exception("Imposible verificar si la sucursal permite alertas.\n\n$sql\n\n" . mysql_error());
                
			$ro=mysql_fetch_row($re);
           
			//Si permite alertas
			if($ro[0] == '1' && $ro[1] == '0')
			{
            
				$sql="SELECT
                      SUM(d.cantidad_surtida*tm.afecta)
                      FROM ec_movimiento_detalle d
                      JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                      JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                      WHERE aa.id_sucursal = $user_sucursal
                      AND d.id_producto={$id_prod}";
                      
                $re=mysql_query($sql);
                if(!$re)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
				$ro=mysql_fetch_row($re);
				$existencia=$ro[0];     
                
                
                $sql="	SELECT
						IF(
							ep.minimo IS NULL,
							pr.min_existencia,
							ep.minimo
						)
						FROM ec_productos pr
						JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
						JOIN ec_estacionalidad e ON e.id_estacionalidad = s.id_estacionalidad
						JOIN ec_estacionalidad_producto ep ON e.id_estacionalidad = ep.id_estacionalidad AND pr.id_productos = ep.id_producto
						WHERE pr.id_productos={$id_prod}";
                      
                $re=mysql_query($sql);
                if(!$re)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
				$ro=mysql_fetch_row($re);
				$min=$ro[0];
				
				
				//throw new Exception("eRROR");
               
				if($existencia <= $min)
				{
                   
					$sql="	INSERT INTO ec_alerta(nombre, fecha, hora, tipo)
							VALUES('Producto con existencia urgente en la sucursal ', NOW(), NOW(), 'code/general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfdHJhbnNmZXJlbmNpYXM=&a1de185b82326ad96dec8ced6dad5fbbd=MA==&a01773a8a11c5f7314901bdae5825a190=bnVsbA==&bnVtZXJvX3RhYmxh=MA==')";
							
							
					if (!mysql_query($sql))
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                  	
                  
                    
					$id_alerta=mysql_insert_id();

			//Insertamos al usuario de mercancias
					$sql="SELECT id_encargado FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
                   
					$re=mysql_query($sql);
					if(!$re)
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					$ro=mysql_fetch_row($re);
					
					if($ro[0] == '' || $ro[0] == ' ')
						$ro[0]=1;
                   
					$sql="	INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
							VALUES($id_alerta, $ro[0], '', 0)";
                                                 
					if (!mysql_query($sql))
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					//Buscamos a los administradores
					$sql="SELECT id_usuario FROM sys_users WHERE administrador=1 AND id_usuario NOT IN($ro[0])";
                   
					$re=mysql_query($sql);
					if(!$re)
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					$nu=mysql_num_rows($re);
                   
					for($j=0;$j<$nu;$j++)
					{
						$ro=mysql_fetch_row($re);
						
						if($ro[0] == '' || $ro[0] == ' ')
							$ro[0]=1;
                       
						$sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
                                                 VALUES($id_alerta, $ro[0], '', 0)";
                                                 
						if (!mysql_query($sql))
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
					}
					
					$AxMail = enviaMail($id_prod,$user_sucursal,$almacenPri,$user_id);
					
					//echo "AxMail: $AxMail";
					
					/*if($AxMail != "true")
					{
						throw new Exception("Imposible almacenar registro\n\n$AxMail");
					} */ 
				}
            }

			
			
		}	
		
		
		
		
		
		/*****************************FIN INSERCCION DATOS REALES************************************/
			
		
        
        if($_GET["ap"] == '0')
        {
            $sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido={$id_pedido_r}";
            mysql_query($sql);
        }
        
		
		$cs = "DELETE FROM ec_pedido_pagos WHERE id_pedido = '{$id_pedido_r}' ";
		
		if (!mysql_query($cs))
			throw new Exception("Imposible eliminar entradas obsoletas de pagos. " . mysql_error());
		
		for ($ix=0; $ix<$nitems; ++$ix)
		{
		    if($_GET["mon{$ix}"] > 0)
            {
            
    			$cs="	INSERT INTO ec_pedido_pagos SET
						id_pedido = '{$id_pedido_r}',
						id_tipo_pago = '{$_GET["idt{$ix}"]}',
						fecha = CURDATE(),
						hora = CURTIME(),
						monto = '{$_GET["mon{$ix}"]}',
						referencia = '',
						id_moneda = '1',
						tipo_cambio = '1',
						id_nota_credito = '-1',
						id_cxc = '-1' ";
    			
    			if (!mysql_query($cs))
					throw new Exception("Imposible almacenar registro (pago). <br><br>$cs<br><br>" . mysql_error());
            }        
		}
        
        //Actualizamos el estatus del pago
        
        $sql="	SELECT
				p.total,
				(
					SELECT
                	IF(SUM(monto) IS NULL, 0, SUM(monto))
                	FROM ec_pedido_pagos
                	WHERE id_pedido = p.id_pedido
				)
				FROM ec_pedidos p
				WHERE p.id_pedido=$id_pedido_r";
        $res=mysql_query($sql);
        
        if(!$res)
            throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
        
        $row=mysql_fetch_row($res);
        
        if($row[0] <= $row[1])
        {
            $sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido=$id_pedido_r";
            if (!mysql_query($sql))
				throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
        }
        else
        {
            //Creamos el folio de apartado
            $sql="SELECT
                  IF(MAX(folio_abono) IS NULL, 1, MAX(folio_abono)+1)
                  FROM ec_pedidos
                  WHERE id_sucursal=$user_sucursal";
            $res=mysql_query($sql);
        
            if(!$res)   
                throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
                
            $row=mysql_fetch_row($res);
            $sql="UPDATE ec_pedidos SET folio_abono=$row[0] WHERE id_pedido=$id_pedido_r";
            if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
                                  
        }
                      
		
		mysql_query("COMMIT");
		
		echo "OK|".$id_pedido_r;
		
	} catch (Exception $e) {
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK");
		mysql_close();
		exit ();
	}

	function enviaMail($id,$suc,$almacen,$user_id)
	{	
		
		//echo "=";
		
		$mail = new PHPMailer();
		$sql = "SELECT 
				CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno),
				correo 
				FROM sys_users 
				WHERE administrador = 1 OR id_usuario = $user_id";
		$result = mysql_query($sql);
		
		if(!$result)
			return mysql_error();

		$sql = "SELECT
				SUM(md.cantidad*tm.afecta),
				p.nombre,
				p.ubicacion_almacen,
				s.nombre,
				p.existencia_media
				FROM ec_movimiento_detalle md
				JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
				JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
				JOIN ec_productos p ON md.id_producto = p.id_productos
				JOIN sys_sucursales s ON ma.id_sucursal = s.id_sucursal
				WHERE md.id_producto=$id
				AND ma.id_sucursal=$suc";	
		$res = mysql_query($sql);
		if(!$res)
			return mysql_error();
		$num = mysql_num_rows($res);
		$row = mysql_fetch_row($res);
		$cantidad = abs($row[0]-$row[4]);
		$sql = "SELECT nombre FROM ec_almacen WHERE id_almacen = $almacen";
		$res = mysql_query($sql);
		
		if(!$res)
			return mysql_error();
		
		$fi = mysql_fetch_row($res);
		if($num == 0)
		{
			return true;
		}
		if($num != 0)		
		{
			while($fila = mysql_fetch_row($result))
			{
			
						//$mail = new PHPMailer();
						$mail->IsSMTP(true);
				        $mail->SMTPAuth = true;

					$mail->From = "no-reply@casadelasluces.com.mx";
				            $mail->FromName = "Sistema general";
				            $mail->Username = "no-reply@casadelasluces.com.mx";


				    $mail->Mailer = "smtp";
				        $mail->SMTPSecure = "ssl";
				        $mail->Host = "casadelasluces.com.mx";
				        #$mail->Username = $mailfrom;
				        $mail->Password = "C4s42014*";
				        $mail->Port = 465;
				        $mail->CharSet = 'UTF-8';

			
						//Set who the message is to be sent from
						//$mail->setFrom('no-reply@casadelasluces.com.mx');
			
						//Set an alternative reply-to address
						//$mail->addReplyTo('replyto@example.com', 'First Last');
			
						//Set who the message is to be sent to
						
						
						$correos=explode(",",$fila[1]);
						
						for($cc=0;$cc<sizeof($correos);$cc++)
						{
						
							$mail->addAddress($correos[$cc], $fila[0]);
						}	
			
						//Set the subject line
						$mail->Subject =utf8_decode('Notificación de surtimiento');
				 		$mail->Body =utf8_decode("<html> 
										<head> 
										<title>Mi primera pagina</title> 
										</head> 
										<body> 
										<h3>Notificación de existencias</h3>
										<table border='1'>
											<th>Producto</th>
											<th>Sucursal</th>
											<th>Almacén</th>
											<th>Ubicación Almacén</th>
											<th>Existencia</th>
											<th>Resurtir</th>
											<tr>
												<td>".$row[1]."</td>
												<td>".$row[3]."</td>
												<td>".utf8_encode($fi[0])."</td>
												<td>".$row[2]."</td>
												<td>".$row[0]."</td>
												<td>".$cantidad."</td>												
											</tr>
											<tr  align='center'>
											 <td colspan='6'><img src='http://192.168.15.100/devCasa/img/img_casadelasluces/logocasadelasluces-easy.png'></td>	
												
											</tr>
										</table>	
										</body> 
									</html>");
						$mail->IsHTML(true);
						//send the message, check for errors
						if (!$mail->send()) {
						    return "Mailer Error: " . $mail->ErrorInfo;
						} else {
						   // echo "Message sent!";
							return "true";
						}
			}
		}
		
		return "true";
	}
?>