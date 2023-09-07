<?php
	    $mail->IsSMTP(true);
        $mail->SMTPAuth = true;
        $mail->From = "facturacion@casadelasluces.com";
        $mail->FromName = "Casa de las Luces Facturacion";
        $mail->Username = "facturacion@casadelasluces.com";
        $mail->Mailer = "smtp";
        $mail->SMTPSecure = "ssl";
        $mail->Host = "dedi-268298.casadelasluces.com";
        $mail->Password = "yL}%64WY8Y0[[";
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        
    	$mail->addAddress('neutrixsound@gmail.com', 'estrada_pedro78@hotmail.com', 'oscar_mdp10@hotmail.com');/*$row[4], $row[5]*/
    	$mail->Subject =utf8_decode('Reporte de inventarios CRON ' . date('Y-m-d H:i:s'));
		$mail->Body =utf8_decode($contenido_correo);
		$mail->IsHTML(true);	
		if (!$mail->send()){
			echo("Mailer Error: " . $mail->ErrorInfo);
		}	
		echo "Correo exitoso enviado para monitoreo";

?>