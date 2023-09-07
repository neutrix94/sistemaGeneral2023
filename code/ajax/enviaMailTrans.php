<?php

	include("../../include/PHPMailer/PHPMailerAutoload.php");

	//extract($_GET);

	include("../../conectMinTrans.php");


	$sql="	SELECT
				t.folio AS ID,
				s.nombre AS 'Suc. origen',
				s2.nombre AS 'Suc. destino',
				CONCAT(t.fecha, ' ', t.hora) AS 'Fecha',
				a.nombre,
				a2.nombre
				FROM ec_transferencias t
				JOIN sys_sucursales s ON t.id_sucursal_origen = s.id_sucursal
				JOIN sys_sucursales s2 ON t.id_sucursal_destino = s2.id_sucursal
				JOIN ec_estatus_transferencia e ON t.id_estado = e.id_estatus
				JOIN ec_almacen a ON t.id_almacen_origen = a.id_almacen
				JOIN ec_almacen a2 ON t.id_almacen_destino = a2.id_almacen
				WHERE t.id_transferencia=$id";
			
	$res=mysql_query($sql) or die(mysql_error());
	
	if(mysql_num_rows($res) <= 0)
		die("No se encontro el documento a cancelar");
		
	$row=mysql_fetch_row($res);
	
	if($row[1] == '0')
		die("El documento no ha sido facturado");
		
	/*if($row[2] == '1')
		die("El documento esta cancelado");*/
		

//implementación de Oscar 28.02.2018
	$sq="SELECT smtp_user,correo_envios,smtp_server,smtp_pass,puerto
			FROM ec_conf_correo
			WHERE id_configuracion=1";
	$eje=mysql_query($sq)or die("Error al consultar parámetros de envío de correo\n\n".$sq."\n\n".mysql_error());
	$res=mysql_fetch_row($eje);
/*Fin de modificación 28.02.2018*/
	$mail = new PHPMailer();

	$mail->IsSMTP(true);
        $mail->SMTPAuth = true;

	$mail->From =$res[0];//"no-reply@casadelasluces.com.mx"
            $mail->FromName =$res[1];//Sistema general
            $mail->Username =$res[0];//"no-reply@casadelasluces.com.mx"


    $mail->Mailer = "smtp";
        $mail->SMTPSecure = "ssl";
        $mail->Host =$res[2];//"casadelasluces.com.mx"
        #$mail->Username = $mailfrom;
        $mail->Password = $res[3];//"P4ssgr4l@"
        $mail->Port =$res[4] ;//465
        $mail->CharSet = 'UTF-8';

    //$mail->addAddress($row[4], $row[5]);
      // $mail->addAddress('veroes_7934@hotmail.com', 'Veronica Estrada');
      // $mail->addAddress('estrada_pedro78@hotmail.com', 'Pedro Estrada');
    $sqlD="SELECT correo FROM sys_users WHERE recibe_correo=1";
    $eje=mysql_query($sqlD);
    if(!$eje){
    	die('Error al buscar cuentas de correo!!!');
    }
    while($cD=mysql_fetch_row($eje)){
    	$mail->addAddress($cD[0], 'Casa de las luces');
    }
       
       //$mail->addAddress('maryano_dh08@hotmail.com', 'Mario Alberto Manzano');

	$mail->Subject =utf8_decode('Transferencia');
	$mail->Body =utf8_decode("Se realizo una tranferencia:<br><br> Suc. Origen: " . $row[1] ."<br> Suc. Destino: " .$row[2] .  "<br><br>");
	$mail->IsHTML(true);
	
	
	//$mail->AddAttachment("../pdf/".$row[0].".pdf", $row[0].".pdf");
	$mail->AddAttachment("../pdf/transferencias/$folioTrans", "$folioTrans");
	//$mail->AddAttachment("../../../facturas/".$row[0].".xml", $row[0].".xml");
	
	
	
	
	//send the message, check for errors
	if (!$mail->send())
	{
		die("Mailer Error: " . $mail->ErrorInfo);
	}



	echo "Correo exitoso enviado a ".$row[4];		
?>