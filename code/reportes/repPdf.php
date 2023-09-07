<?php
	
	
	include("../../conectMin.php");
	include('class.ezpdf.php');

	extract($_GET);
	$pdf =&  new Cezpdf('a4');
	$pdf->selectFont('courier.afm');

    
	//----------------------- ----DATOS VENTAS ----------------------------------//
  $query = "SELECT Mes,CONCAT('$',FORMAT(Total,2)) AS Total  FROM balance WHERE Tipo = '1'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());


	while ($fila = mysql_fetch_row($res)) {
		
		
		$data[] = array(
						'Mes'        => $fila[0],
						'Total'      => $fila[1]
						
	               );	
	
	}

		$titles = array(
						'Mes'        => 'Mes',
						'Total'      => 'Total'
						
					);
 $query = "SELECT '','',SUM(TOTAL)  FROM balance WHERE Tipo = '1'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
  $fila = mysql_fetch_row($res);
  $ventasTotales=$fila[2];		
	//----------------------- ----DATOS VENTAS ----------------------------------//
   $query = "SELECT Mes,CONCAT('$',FORMAT(Total,2)) AS Total  FROM balance WHERE Tipo = '2'";
    $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
	
    while ($fila = mysql_fetch_row($res)) {
		
		
		$data2[] = array(
						'Mes'        => $fila[0],
						'Total'      => $fila[1]
						
	               );	
	
	}

		$titles = array(
						'Mes'        => 'Mes',
						'Total'      => 'Total'
						
					);

   $query = "SELECT '','',SUM(TOTAL)  FROM balance WHERE Tipo = '2'";
  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
  $fila = mysql_fetch_row($res);
  $comprasTotales=$fila[2];	
$options = array(
						 'justification'=> 'center'
						 
		           );


	//------------------------------- GENERANDO PDF ----------------------------------//                     
	
	$pdf->ezText("<b>Balance Anual</b>",16);
	$pdf->ezText("<b>Fecha:</b> ".date("d/m/Y"),10);
	$pdf->ezText("<b>Ventas Anuales</b>",16,$options);
	$pdf->ezText("\n\n");
	$pdf->ezTable($data);
	$pdf->ezText("\n\n");
	$pdf->ezText("<b>Total Ventas Anuales:</b>$ ".money_format('%i',$ventasTotales),10,$options);
	$pdf->ezText("\n\n");
	$pdf->ezText("<b>Compras Anuales</b>",16,$options);
	$pdf->ezText("\n\n");
	$pdf->ezTable($data2);
	$pdf->ezText("\n\n");
	$pdf->ezText("<b>Total Compras Anuales:</b>$ ".money_format('%i',$comprasTotales),10,$options);
	$pdf->ezText("\n\n");
	$pdf->ezText("<b>Utilidad Neta:</b>$ ".money_format('%i',($ventasTotales-$comprasTotales)),16,$options);
	//$pdf->ezText("<b>Horas Trabajadas:</b> ".$filaHoras[0],10);
	//$pdf->ezText("<b>N".utf8_decode('ú')."mero de Retrasos:</b> ".$contadorRetrasos);
	if($envio == 'SI')
	{
		
		include_once("../../include/class.phpmailer.php");
	
		$pdfcode = $pdf->ezOutput();
$fp=fopen('reporte.pdf','wb');
fwrite($fp,$pdfcode);
fclose($fp);
		
		$mail = new PHPMailer();

				//Luego tenemos que iniciar la validación por SMTP:
				$mail->IsSMTP();

				$mail->SMTPAuth = true;

		
		
				$mail->Host = "localhost"; // SMTP a utilizar. Por ej. smtp.elserver.com

				$mail->Username = "no-reply@thefamosos.com"; // Correo completo a utilizar

				$mail->Password = "F4m2014*"; // Contrase�a
				$mail->From = "no-reply@thefamosos.com";
    $mail->FromName = "Web The Famosos";
	 $body="Reporte generado desde el sistema The Famosos";
           $mail->Body = $body; 
        $mail->AddAddress($correo);
        $mail->IsHTML(true);
        
        
        $mail->Subject = utf8_decode("Reporte de sistema the famosos");
        
        $mail->AddAttachment("reporte.pdf");
        
        
        if(!$mail->Send()) {
           return "Error: " . $mail->ErrorInfo;
        }
		

		echo "Se ha enviado el reporte exitosamente";
	}
	else	
		

	$pdf->ezStream();

	
 
?>