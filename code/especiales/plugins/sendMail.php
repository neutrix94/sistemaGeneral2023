<?php

	class sendMail {
		private $current_path;
		private $db;
		private $smtp_user;
		private $correo_envios;
		private $smtp_server;
		private $smtp_pass;
		private $puerto;

		function __construct( $path ){
			include( $path . 'conexionMysqli.php' );
			$this->db = $link;
		//buscamos parámetros de correo
			$sql = "SELECT 
					smtp_user,
					correo_envios,
					smtp_server,
					smtp_pass,
					puerto
				FROM ec_conf_correo
				WHERE id_configuracion=1";
			$eje = $this->db->query( $sql )or die("Error al consultar parámetros de envío de correo\n\n".$sql."\n\n".mysql_error());
			$res = $eje->fetch_row();
			$this->smtp_user = $res[0];
			$this->correo_envios = $res[1];
			$this->smtp_server = $res[2];
			$this->smtp_pass = $res[3];
			$this->puerto = $res[4];
			$this->current_path = $path;
			//echo $this->smtp_user;
		}

		function sendMailTo( $subject_mail, $msg_mail, $mails_to, $notification = null, $files = null ){
			if(!include( $this->current_path . "include/PHPMailer/PHPMailerAutoload.php")){
				die("No se encontró librería de e-mail");
			}
			$mail = new PHPMailer();
			$mail->IsSMTP(true);
        	$mail->SMTPAuth = true;
			$mail->From = $this->smtp_user;
        	$mail->FromName = $this->correo_envios;
        	$mail->Username = $this->smtp_user;
        	$mail->Mailer = "smtp";
        	$mail->SMTPSecure = "ssl";
        	$mail->Host = $this->smtp_server;
        	$mail->Password = $this->smtp_pass;
        	$mail->Port = $this->puerto;
        	$mail->CharSet = 'UTF-8';
        //direcciones de envio
		    foreach ($mails_to as $key => $mail_to) {
		    	$mail->addAddress( $mail_to[0], $mail_to[1] );
		    }
			$mail->Subject =utf8_decode( $subject_mail );
			$mail->Body =utf8_decode( $msg_mail );
			$mail->IsHTML( true );
			foreach ($files as $key_file => $file) {
				$mail->AddAttachment($file);
			}
			
			if ( !$mail->send() ){
				return ("Mailer Error: ".$mail->ErrorInfo);
			}
			return ($notification != null ? '¡Correo enviado exitosamente!' : null);
		}

		function getSystemEmails( $table ){
			$sql = "SELECT 
						cd.correo,
						cd.nombre_destinatario
					FROM ec_modulos_correo mc
					LEFT JOIN ec_correo_destinatarios cd 
					ON cd.id_modulo = mc.id_modulo_correo
					WHERE mc.tabla_modulo = '{$table}'";
			$eje = $this->db->query( $sql ) or die( "Error al consultar los correos de Aviso de tabla {$table} : {$this->db->error}");
			$resp = array();
			while ( $r = $eje->fetch_row() ) {
				$resp[] = $r;
			}
			return $resp;
		}
	}
?>