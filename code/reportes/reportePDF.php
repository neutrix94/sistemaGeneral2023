<?php


	include("../../conectMin.php");
	require_once('../../include/fpdf153/fpdf.php');
	
	
	extract($_GET);
	
	
	

	class PDF extends FPDF
	{
		function BasicTable($header, $data)
		{
		    // Cabecera
		    foreach($header as $col)
		        $this->Cell(70,7,$col,1);
		    $this->Ln();
		    // Datos
		    foreach($data as $row)
		    {
		        foreach($row as $col)
		            $this->Cell(70,6,$col,1);
		        $this->Ln();
		    }
		}
	}
	
	$pdf = new PDF('L');
	$pdf->SetFont('Arial','',10);
	$pdf->AddPage();
	// Títulos de las columnas
	
	//Buscamos las cabecera
	
	$sql="SELECT
	      campo, 
	      display
	      FROM sys_reportes_columnas
	      WHERE id_reporte=$id_reporte
	      ORDER BY orden";
		  
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	$num=mysql_num_rows($res);
	
	$header = array();
	
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		//echo "<td class='headerReporte'>".$row[1]."</td>";
		//$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$i]."1", $row[1]);
		array_push($header, utf8_decode($row[1]));
	}
	
	$data=Array();
	
	//Buscamos los datos generales
	$sql="SELECT
	      consulta,
	      campo_fecha,
	      ver_sumatorias,
	      consulta_sum,
		  campoSucursal
	      FROM sys_reportes
	      WHERE id_reporte=$id_reporte";
		  
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	$row=mysql_fetch_row($res);
	
	$consulta=$row[0];
	$campoFecha=$row[1];		  
	$sums=$row[2];
	$consulta_sum=$row[3];
	$campoSucursal=$row[4];
	
	$sql=$consulta;
	
	//echo $sql;
	
	$condiciones="";
	
	if($tipoFec == '1' && $campoFecha != 'NO')
	{
		$condiciones.=" AND $campoFecha >= '".date('Y-m-d')."' AND $campoFecha <= '".date('Y-m-d')."'";
	}
	if($tipoFec == '2' && $campoFecha != 'NO')
	{
		
		$semana=date("W");
		$year=date("Y");
		//echo $semana;
		
		for($mes=1;$mes<=12;$mes++)
		{
			//echo "bulto $mes<br>";
    		$limite = date('t',mktime(0,0,0,$mes,1,$year));
    		for($dia=1;$dia<$limite;$dia++)
    		{
        		if(date('W',mktime(0, 0, 0, $mes  , $dia, $year)) == $semana)
        		{
        			//echo "bulo $dia-$mes<br>";
            		if(date('N',mktime(0, 0, 0, $mes  , $dia, $year)) == 1)
            		{
                		//echo 'Lunes '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia, 2010)).' y Domingo '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia+6, 2010));
                		$condiciones.=" AND $campoFecha >= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND $campoFecha <= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
            		}
        		}
    		}
		}  
		
		
	}
	if($tipoFec == '3' && $campoFecha != 'NO')
	{
		$mes=date("m");
		$year=date("Y");
		
		$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
	}
	if($tipoFec == '4' && $campoFecha != 'NO')
	{
		$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
	}
	if($tipoFec == '6' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -1 DAY) AND $campoFecha <= DATE_ADD('$hoy', INTERVAL -1 DAY)";
	}
	if($tipoFec == '7' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -7 DAY)";
	}
	if($tipoFec == '9' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -30 DAY)";
	}
	if($tipoFec == '10' && $campoFecha != 'NO')
	{
		$mes=date("m");
		$year=date("Y");
		
		$mes--;
		
		if($mes <= 0)
		{
			$mes=12;
			$year--;
		}	
		
		$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
	}
	if($tipoFec == '11' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";
	}
	
	if($id_sucursal != -1)
	{
		$condiciones.=" AND $campoSucursal = $id_sucursal";
	}
	
	$condiciones.=" $extras";
	
	//echo $sql;
	
	$sql=str_replace("XXX", $condiciones, $sql);
	
	$sql.=" $adicionales";
	
	//echo $sql;
		
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());

	
	
	$num=mysql_num_rows($res);
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		//echo "<tr>";
		
		for($j=0;$j<sizeof($row);$j++)
		{
			//echo "<td class='datos'>".$row[$j]."</td>";
			$row[$j]=utf8_decode($row[$j]);
		}
		
		array_push($data, $row);
		
		//echo "</tr>";
	}
				
				
	//Sumatorias
	if($sums == '1')
	{
		$sql=$consulta_sum;
	
		//echo $sql;
		
		$condiciones="";
		
		if($tipoFec == '1' && $campoFecha != 'NO')
		{
			$condiciones.=" AND $campoFecha >= '".date('Y-m-d')."' AND $campoFecha <= '".date('Y-m-d')."'";
		}
		if($tipoFec == '2' && $campoFecha != 'NO')
		{
			
			$semana=date("W");
			$year=date("Y");
			//echo $semana;
			
			for($mes=1;$mes<=12;$mes++)
			{
				//echo "bulto $mes<br>";
	    		$limite = date('t',mktime(0,0,0,$mes,1,$year));
	    		for($dia=1;$dia<$limite;$dia++)
	    		{
	        		if(date('W',mktime(0, 0, 0, $mes  , $dia, $year)) == $semana)
	        		{
	        			//echo "bulo $dia-$mes<br>";
	            		if(date('N',mktime(0, 0, 0, $mes  , $dia, $year)) == 1)
	            		{
	                		//echo 'Lunes '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia, 2010)).' y Domingo '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia+6, 2010));
	                		$condiciones.=" AND $campoFecha >= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND $campoFecha <= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
	            		}
	        		}
	    		}
			}  
			
			
		}
		if($tipoFec == '3' && $campoFecha != 'NO')
		{
			$mes=date("m");
			$year=date("Y");
			
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
		}
		if($tipoFec == '4' && $campoFecha != 'NO')
		{
			$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
		}
		if($tipoFec == '6' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -1 DAY) AND $campoFecha <= DATE_ADD('$hoy', INTERVAL -1 DAY)";
		}
		if($tipoFec == '7' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -7 DAY)";
		}
		if($tipoFec == '9' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -30 DAY)";
		}
		if($tipoFec == '10' && $campoFecha != 'NO')
		{
			$mes=date("m");
			$year=date("Y");
			
			$mes--;
			
			if($mes <= 0)
			{
				$mes=12;
				$year--;
			}	
			
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
		}
		if($tipoFec == '11' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";
		}
		
		if($id_sucursal != -1)
		{
			$condiciones.=" AND $campoSucursal = $id_sucursal";
		}
		
		
		$condiciones.=" $extras";
		
		//echo $sql;
		
		$sql=str_replace("XXX", $condiciones, $sql);
		
		//echo $sql;
		
		$sql.=" $adicionales";
			
		$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
		
		$row=mysql_fetch_row($res);
		
		//echo "<tr>";
		for($i=0;$i<sizeof($row);$i++)
		{
			/*if($row[$i] != '')
				echo "<td class='sumatoriasRep'>".$row[$i]."</td>";
			else	
				echo "<td class='sumatoriasRep'>&nbsp;</td>";*/
				
			if($row[$i] != '')	
				$row[$i]=$row[$i];
			else
				$row[$i]="";
				
		}
		//echo "</tr>";
		
		array_push($data, $row);
	}			
	
	$pdf->BasicTable($header,$data);
	
	if($envio == 'SI')
	{
		
		include_once("../../include/class.phpmailer.php");
	
		$pdf->Output("reporte.pdf");
		
		
		 $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true; 

    
        
        //echo "G";
        
     	$body="Reporte generado desde el sistema The Famosos";
        

    
    $mail->Host = "localhost"; // SMTP a utilizar. Por ej. smtp.elserver.com

    $mail->Username = "no-reply@thefamosos.com"; // Correo completo a utilizar
    $mail->Password = "F4m2014*"; // Contrase�a
    $mail->From = "no-reply@thefamosos.com";
    $mail->FromName = "Web The Famosos";
    $mail->CharSet = 'UTF-8'; 
        
        $mail->AddAddress($correo);
        $mail->IsHTML(true);
        
        $mail->Body = $body; 
        $mail->Subject = utf8_decode("Reporte de sistema de famosos");
        
        $mail->AddAttachment("reporte.pdf");
        
        
        if(!$mail->Send()) {
           return "Error: " . $mail->ErrorInfo;
        }
		

		echo "Se ha enviado el reporte exitosamente";
	}
	else	
		$pdf->Output();
?>