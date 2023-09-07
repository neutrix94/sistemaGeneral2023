<?php
include("../../include/PHPMailer/PHPMailerAutoload.php");
include("../../conect.php");
	enviaMail(6075,1);

function enviaMail($id,$suc)
	{	
		echo 'ok1';
		$mail = new PHPMailer();
		$sql = "SELECT 
				CONCAT(nombre,' ',apellido_paterno,' ',apellido_materno),correo 
				FROM sys_users 
				WHERE administrador = 1";
		$result = mysql_query($sql) or die (mysql_error());

		$sql = "SELECT
				SUM(md.cantidad*tm.afecta),
				p.nombre,
				p.ubicacion_almacen,
				s.nombre
				FROM ec_movimiento_detalle md
				JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen
				JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
				JOIN ec_productos p ON md.id_producto = p.id_productos
				JOIN sys_sucursales s ON ma.id_sucursal = s.id_sucursal
				WHERE md.id_producto=$id
				AND ma.id_sucursal=$suc";
		//echo $sql;		
		$res = mysql_query($sql) or die (mysql_error());
		$num = mysql_num_rows($res);
		$row = mysql_fetch_row($res);
		echo 'ok2';
		if($num == 0)
		{

		}
		if($num != 0)		
		{
			echo 'ok3';
			while($fila = mysql_fetch_row($result))
			{
					
						$mail->isSMTP();
						//Set the hostname of the mail server
						$mail->Host = 'smtp.gmail.com';
						//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
						$mail->Port = 587;
						//Set the encryption system to use - ssl (deprecated) or tls
						$mail->SMTPSecure = 'tls';
						//Whether to use SMTP authentication
						$mail->SMTPAuth = true;
			
						//Username to use for SMTP authentication - use full email address for gmail
						$mail->Username = "agalicia@terminus10.com";
			
						//Password to use for SMTP authentication
						$mail->Password = "/*/*ad12gb34";
			
						//Set who the message is to be sent from
						$mail->setFrom('agalicia@terminus10.com');
			
						//Set an alternative reply-to address
						$mail->addReplyTo('replyto@example.com', 'First Last');
			
						//Set who the message is to be sent to
						$mail->addAddress('agalicia@terminus10.com', 'John Doe');
			
						//Set the subject line
						$mail->Subject = 'Notificación de surtimiento';
				 		$mail->Body = "<html> 
										<head> 
										<title>Mi primera pagina</title> 
										</head> 
										<body> 
										
										<table border='1'>
											<th>Producto</th>
											<th>Sucursal</th>
											<th>Ubicación Almacén</th>
											<th>Existencia</th>
											<tr>
												<td>".$row[1]."</td>
												<td>".$row[3]."</td>
												<td>".$row[2]."</td>
												<td>".$row[0]."</td>												
											</tr>
											<tr colspan = '4'>
												<img src='http://192.168.15.100/devCasa/img/img_casadelasluces/logocasadelasluces-easy.png'>
											</tr>
										</table>	
										</body> 
									</html>";
						$mail->IsHTML(true);
						//send the message, check for errors
						if (!$mail->send()) {
						    echo "Mailer Error: " . $mail->ErrorInfo;
						} else {
						    echo "Message sent!";
						}
			}
		}
	}

	?>