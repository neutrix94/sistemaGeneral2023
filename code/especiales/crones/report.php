<?php
	/**
	* Libreria desarrollada e implementada por Oscar 2021
	*/
	class reportByOscar
	{
		function __construct()
		{

		}
		function enviar_email( $contenido_correo ){
			include("../../../include/PHPMailer/PHPMailerAutoload.php");
		/*formacion del email*/
		/*	$mail = new PHPMailer();	
			$mail->IsSMTP(true);
	        $mail->SMTPAuth = true;
			$mail->From = 'facturacion@lacasadelasluces.com';
		    $mail->FromName = "Cron de Verificación de inventarios v1.0";
		    $mail->Username = 'facturacion@lacasadelasluces.com';
		    $mail->Mailer = "smtp";
		    $mail->SMTPSecure = 'ssl';
		    $mail->Host = 'mail.lacasadelasluces.com';
		    $mail->Password = 'Macronet03*';
		    $mail->Port = '465';
		    $mail->CharSet = 'UTF-8';
	    	$mail->addAddress('neutrixsound@gmail.com');
	    	$mail->Subject =utf8_decode('Reporte de inventarios CRON ' . date('Y-m-d H:i:s'));
			$mail->Body =utf8_decode($contenido_correo);
			$mail->IsHTML(true);	*/
			
			$mail = new PHPMailer();
		    $mail->IsSMTP(true);
	        $mail->SMTPAuth = true;
	        $mail->From = 'facturacion2023@sistemageneralcasa.com';//"avisos@lacasadelasluces.com.mx";
	        $mail->FromName = "Resultado del CRON Sistema General";
	        $mail->Username = "facturacion2023@sistemageneralcasa.com";
	        $mail->Mailer = "smtp";
	        $mail->SMTPSecure = "ssl";
	        $mail->Host = "mail.sistemageneralcasa.com";//dedi-268298.casadelasluces.com
	        $mail->Password = "!PgCq&2CkXG~";
	        $mail->Port = 465;
	        $mail->CharSet = 'UTF-8';
	        
	    	$mail->addAddress('neutrixsound@gmail.com');/*$row[4], $row[5] , 'Oscar Mendoza'*/
	    	$mail->addAddress('pedroestrada1978@gmail.com');//, 'Pedro Estrada'
	    	$mail->addAddress('fer.mb131197@gmail.com');//, 'Fernanda Medina'
	    	$mail->Subject =utf8_decode('Reporte de CRON ' . date('Y-m-d H:i:s'));
			$mail->Body =utf8_decode($contenido_correo);
			$mail->IsHTML(true);
			
			if (!$mail->send()){
				echo ("Error al enviar el Correo: " . $mail->ErrorInfo);
			}else{
				echo "Correo exitoso enviado para monitoreo";
			}	

		}

	function genera_descarga_csv( $data ){
	//reemplaza etiquetas
		$data = str_replace("<p>", "\n", $data);
		$data = str_replace("</p>", "\n", $data);
		$data = str_replace("<h2>", "\n", $data);
		$data = str_replace("</h2>", "\n", $data);
		$data = str_replace("<b>", "", $data);
		$data = str_replace("</b>", "", $data);

		//creamos el nombre del archivo
		$nombre="reporteCronInventarios_" . date('Y-m-d H:i:s') . ".csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo( utf8_decode($data) );
	}
//funcion que crea la estructura básica de la tabla
	function crea_tabla_log( $encabezados = null , $titulo = '' ){
		$resp = $titulo; 
		$resp .= "<table border=\"1\">"
				. "<tr>";
		foreach ($encabezados as $key => $head) {
			$resp .= "<th>{$head}</th>";
		}
		$resp .= "</tr>"
				. "|table_content|"
			. "</table>";
		return $resp;
	}

//funcion que crea las filas de la tabla
	function crea_fila_tabla_log( $datos ){
		static $contador = 1;
		$c = 0;
		$resp = "<tr>";
		foreach ($datos as $key => $row) {
			$resp .= ($c > 0 ? "" : "<td>{$contador}</td>");
			$resp .= "<td>{$row}</td>";
		    $c++;
		}
		$resp .= "</tr>";
		$contador ++;
		return $resp;
	}

	//funcion que crea los encabezados para el CSV
		function csv_header_generator( $data ){
			static $contador = 1;
			$c = 0;
			$resp = "";
			foreach ($data as $key => $row) {
				$resp .= ($key > 0 ? ",": "");
				$resp .= $row; 
				$c ++;
			}
			$contador ++;
			return $resp;
		}

	//funcion que crea los registros para el CSV
		function csv_row_generator( $data ){
			static $contador = 1;
			$resp = "\n";//salto de línea
			$c = 0;
			foreach ($data as $key => $row) {
				$resp .= ( $c > 0 ? "," : "{$contador}," );
				$resp .= ($row == null ? 0 : $row ); 
				$c ++;
			}
			$contador ++;
			return $resp;
		}
	}
?>