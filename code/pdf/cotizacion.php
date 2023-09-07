<?php

	//buscamos las columnas
	$sql="SELECT
	      (SELECT mostrar FROM eq_columnas WHERE id_columna=1) AS codigo,
	      (SELECT mostrar FROM eq_columnas WHERE id_columna=3) AS dias";
	
	$res=mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_assoc($res);
	extract($row);

	//buscamos los encabezados
	
	$sql="SELECT * FROM eq_configuracion WHERE id_configuracion=1";
	$res=mysql_query($sql) or die(mysql_error());
	
	$rowConf=mysql_fetch_assoc($res);
	
	
	//echo hexdec($rowConf['color_fondo_r'])."<br>";

	$rowConf['color_fondo_r']=hexdec($rowConf['color_fondo_r']);
	$rowConf['color_fondo_g']=hexdec($rowConf['color_fondo_g']);
	$rowConf['color_fondo_b']=hexdec($rowConf['color_fondo_b']);

	//echo $rowConf['color_fondo_r']."<br>";

	if(isset($id))
	{
		$strConsulta="SELECT
		              id_cotizador,
		              fecha,
		              cl.nombre As contacto,
					  CONCAT('$', FORMAT(subtotal, 2)) AS subtotal,
					  CONCAT('$', FORMAT(iva,2)) AS iva,
					  CONCAT('$', FORMAT(total,2)) AS total,
					  obrservaciones,
					  id_usuario,
					  forma_de_pago
					  FROM eq_cotizador c
					  JOIN eq_clientes cl ON c.contacto = cl.id_cliente
					  WHERE id_cotizador='".$id."'";
		$resultado=mysql_query($strConsulta) or die("Consulta:\n$strConsulta\n\nDescripcion:\n".mysql_error());
		$datosFact=mysql_fetch_assoc($resultado);
		extract($datosFact);
		
		
		//buscamos datos del usuario
		
		$sql="SELECT
			  CONCAT(nombre, ' ', apellido_paterno, IF(apellido_materno IS NULL, '', CONCAT(' ', apellido_materno))) as 'nom_user',
			  puesto as 'puesto_user',
			  firma as 'firma_user'
			  FROM sys_users
			  WHERE id_usuario=$id_usuario";
			
		$resultado=mysql_query($sql) or die("Consulta:\n$sql\n\nDescripcion:\n".mysql_error());
		$datosUser=mysql_fetch_assoc($resultado);
		extract($datosUser);	
			
		
	}
	$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);
	$pdf->AddPage();
	$pdf->SetFont('helvetica','',$ftam);
	$pdf->SetAutoPageBreak(false);
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	$pdf->Image($rowConf['encabezado'] , 2.8 , 0.2, 16, 3.5);
	
	$pdf->SetTextColor($rowConf['color_fondo_r'], $rowConf['color_fondo_g'], $rowConf['color_fondo_b']);
	//print_r($rowConf);
	//die();
	//$pdf->SetTextColor(34, 133, 161);
	//$pdf->SetDrawColor(0.13, 0.52, 0.63);
	//$pdf->SetDrawColor(1, 0, 0);
	$pdf->SetFillColor($rowConf['color_fondo_r'], $rowConf['color_fondo_g'], $rowConf['color_fondo_b']);
	
	$pdf->celpos(0.6, 3.8, 10, $ftam, $contacto,0,"L");
	$pdf->celpos(17.8, 3.8, 10, $ftam, "Fecha:",0,"L");
	$pdf->SetTextColor(0, 0, 0);
	$pdf->celpos(19, 3.8, 10, $ftam, $fecha,0,"L");
	
	$pdf->Rect(0.5, 4.8, 20.5, 0.8, 'F');
	
	$pdf->SetTextColor(255, 255, 255);	
	
	/*$pdf->celpos(13, 5, 2, $ftam, "Cantidad",0,"C");
	$pdf->celpos(15, 5, 3, $ftam, "Importe",0,"C");
	$pdf->celpos(18, 5, 3, $ftam, "Total",0,"C");*/
    
    //Conseguimos los datos de columna
    $sql="SELECT
          nombre,
          ancho,
          align,
          id_columna
          FROM eq_columnas
          WHERE mostrar = 1
          ORDER By orden";
     
     $rCol=mysql_query($sql) or die(mysql_error());     
     $nCol=mysql_num_rows($rCol);
     $xCol=0.5;
     
     for($ic=0;$ic<$nCol;$ic++)
     {
         $roCol=mysql_fetch_row($rCol);
         
         $pdf->celpos($xCol, 5, $roCol[1], $ftam, $roCol[0],0, 'C');
         
         $xCol+=$roCol[1];
     }     
          
    
    /*
	if($codigo == '1')
	{
		$pdf->celpos(0.5, 5, 12.5, $ftam, "Código",0,"L");
		$pdf->celpos(2.5, 5, 12.5, $ftam, "Concepto",0,"L");
	}
	else
	{
		$pdf->celpos(0.5, 5, 12.5, $ftam, "Descripción",0,"L");
	}
	
	if($dias == '1')
	{
		$pdf->celpos(7.5, 5, 2, $ftam, "Días",0,"C");
	}*/
	
	$pdf->SetTextColor(0, 0, 0);
	
	if(isset($id))
	{
	    
      
        
        
		$strConsulta="SELECT
					  *
					  FROM
					  (	
					  	(
					  		SELECT
					 		cd.cantidad,
					  		cd.descripcion,
					  		CONCAT('$', FORMAT(cd.precio, 2)) as precio,
					  		CONCAT('$', FORMAT(cd.precio*cantidad, 2)) as importe,
					  		p.nombre AS producto,
					  		p.imagen AS file,
							'' as dias,
					  		orden,
							p.codigo AS codigo
					  		FROM eq_cotizador_detalle cd
					  		JOIN eq_productos p ON cd.id_producto = p.id_producto
					  		WHERE id_cotizador='".$id."'
						)
						UNION
						(
							SELECT 
							cantidad,
							descripcion,
							CONCAT('$', FORMAT(precio, 2)) as precio,
							CONCAT('$', FORMAT(precio*cantidad, 2)) as importe,
							servicio as producto,
							file,
							dias,
							orden,
							codigo
							FROM eq_otros
							WHERE id_cotizador='".$id."'
						)
					)aux	
					ORDER BY orden";
		$resultado=mysql_query($strConsulta) or die("Consulta:\n$strConsulta\n\nDescripcion:\n".mysql_error());
		$yini=5.6;$i=0;
		
		/*$inc=0;	
		$xdesc=9;
				
		if($codigo == '1')
		{
			$inc+=2;
			$xdesc-=2;
		}
		if($dias == '1')
		{
			$xdesc-=2;
		}*/
				
				
				
		while($row=mysql_fetch_assoc($resultado))
		{
			 $xCol=0.5;
			 $ymax=0;
     
             for($ic=0;$ic<$nCol;$ic++)
             {
                 mysql_data_seek($rCol, $ic);
                 $roCol=mysql_fetch_row($rCol);
                 
                 $pdf->SetY($yini);
                 $pdf->SetX($xCol);                 
                 
                 if($roCol[3] == 1)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['codigo'], 0, $roCol[2], 0);
                 }
                 if($roCol[3] == 2)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['producto'], 0, $roCol[2], 0);                     
                     if($row['descripcion'] != '')
                     {
                        $pdf->SetX($xCol);
                        $pdf->SetFont('helvetica','',$ftam-4);
                        $pdf->MultiCell($roCol[1], 0.5, $row['descripcion'], 0, $roCol[2], 0);
                        $pdf->SetFont('helvetica','',$ftam);
                     }   
                 }
                 if($roCol[3] == 3)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['dias'], 0, $roCol[2], 0);
                 }
                 if($roCol[3] == 4)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['cantidad'], 0, $roCol[2], 0);
                 }
                 if($roCol[3] == 5)
                 {
                     if($row['file'] != '')
                     {
                         $pdf->Image($row['file'] , $xCol, $yini, 1.5, 1.5);
                         if($yini+1.5 > $ymax)
                            $ymax=$yini+1.5;
                     }    
                 }
                 if($roCol[3] == 6)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['precio'], 0, $roCol[2], 0);
                 }                 
                 if($roCol[3] == 7)
                 {
                     $pdf->MultiCell($roCol[1], 0.5, $row['importe'], 0, $roCol[2], 0);
                 }
                 
                 $xCol+=$roCol[1];                 
                 
                 if($pdf->GetY() > $ymax)
                    $ymax=$pdf->GetY();
                 
             }
			
            
			$yini=$ymax+0.5;
            
			/*$pdf->MultiCell(2, 0.5, $row['cantidad'], 0, 'C', 0);
			
			$pdf->SetY($yini);
			$pdf->SetX(15);
			$pdf->MultiCell(3, 0.5, $row['precio'], 0, 'C', 0);
			
			$pdf->SetY($yini);
			$pdf->SetX(18);
			$pdf->MultiCell(2.8, 0.5, $row['importe'], 0, 'R', 0);
			
			if($codigo == '1')
			{
				$pdf->SetY($yini);
				$pdf->SetX(0.5);
				$pdf->MultiCell(2, 0.5, $row['codigo'], 0, 'L', 0);
			}
			if($dias == '1')
			{
				$pdf->SetY($yini);
				$pdf->SetX(7.5);
				$pdf->MultiCell(2, 0.5, $row['dias'], 0, 'C', 0);
			}	
			
			$pdf->SetY($yini);
			$pdf->SetX(0.5+$inc);
			$pdf->MultiCell($xdesc, 0.5, $row['producto'], 0, 'L', 0);
			
			$pdf->SetFont('helvetica','',$ftam-4);
			
			$pdf->SetX(0.5+$inc);
			$pdf->MultiCell($xdesc, 0.2, $row['descripcion'], 0, 'L', 0);
			
			
			
			$pdf->SetFont('helvetica','',$ftam);			
			
			$ymax=$pdf->GetY();
			
			if($row['file'] != '')
				$pdf->Image($row['file'] , 12.2 , $yini, 1.5);
			
			if($ymax > $pdf->GetY())
				$yini=$ymax;
			else			
				$yini=$pdf->GetY();*/
			
			
		}		
	}	
	
	
	$pdf->SetDrawColor($rowConf['color_fondo_r'], $rowConf['color_fondo_g'], $rowConf['color_fondo_b']);	
	$pdf->Rect(0.5, 24, 20.5, 0.5, 'F');	
	$pdf->Rect(15, 21.4, 6, 2.1, 'F');
	$pdf->Rect(0.5, 5.6, 20.5, 15.3);
	
	$pdf->SetDrawColor(150, 150, 150);
	$pdf->SetFillColor(200, 200, 200);
	$pdf->Rect(0.5, 24.5, 20.5, 1.5, 'F');
	
	$pdf->SetDrawColor($rowConf['color_fondo_r'], $rowConf['color_fondo_g'], $rowConf['color_fondo_b']);
	$pdf->Rect(0.5, 24, 20.5, 2);	
	
	
	$pdf->SetTextColor(255, 255, 255);
	$pdf->celpos(0.8, 24.1, 10, $ftam, "Condiciones comerciales",0,"L");
	$pdf->celpos(15, 21.6, 3, $ftam, "Subtotal",0,"R");
	$pdf->celpos(15, 22.3, 3, $ftam, "IVA",0,"R");
	$pdf->celpos(15, 23, 3, $ftam, "Total",0,"R");
	$pdf->celpos(18, 21.6, 2.8, $ftam, $subtotal,0,"R");
	$pdf->celpos(18, 22.3, 2.8, $ftam, $iva,0,"R");
	$pdf->celpos(18, 23, 2.8, $ftam, $total,0,"R");
	
		
	$pdf->SetTextColor(0, 0, 0);
	$pdf->celpos(0.8, 24.6, 10, $ftam, "Forma de Pago: ".$forma_de_pago,0,"L");	
	
	$pdf->celpos(0.8, 25.2, 19.9, $ftam, $obrservaciones,0,"L");
	
	if($firma_user != '')
		$pdf->Image($firma_user , 11.2, 26.4, 1.2, 0.75);
	
	
	$pdf->celpos(0, 26.1, 21.6, $ftam, "Atentamente:",0,"C");
	$pdf->SetTextColor(34, 133, 161);
	$pdf->celpos(0, 27.1, 21.6, $ftam, $nom_user,0,"C");
	$pdf->SetTextColor(0, 0, 0);
	$pdf->celpos(0, 27.5, 21.6, $ftam-6, $puesto_user,0,"C");
	
?>