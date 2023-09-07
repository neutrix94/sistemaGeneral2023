<?php

	
	$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);
	$pdf->AddPage();
	$pdf->SetFont('helvetica','',$ftam);
	$pdf->SetAutoPageBreak(false);
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	$pdf->Image("fondorequisiscion.png" , 0 , 0, 21.6, 27.9);
	
	
	//Buscamos el logo de la empresa
	$sql="SELECT logo, nombre, telefono, direccion FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$res=mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_assoc($res);
	extract($row);
	
	//4.4X3.5
	
	$logo=str_replace($rooturl, $rootpath, $logo);
	
	$pdf->Image($logo , 1 , 1, 4.4, 3.5);
	
	$pdf->celpos(3.1, 4.8, 10, $ftam, $nombre,0,"L");
	$pdf->celpos(2.3, 5.5, 10, $ftam, $telefono,0,"L");
	$pdf->celpos(1.8, 6.4, 12, $ftam, $direccion,0,"L");
	
	/*$pdf->SetFont('helvetica','',$ftam);
	
	$pdf->SetX(1.5);
	$pdf->SetY(6.3);*/
    
	
	//$pdf->MultiCell(6, 0.5, $direccion, 0, 'L', 0);
	
	
	//Buscamos datos de cabcera
	$sql="SELECT 
	      id_orden_compra AS folio,
	      CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS hechapor,
	      oc.fecha as fecha
	      FROM ec_ordenes_compra oc
	      JOIN sys_users u ON oc.id_usuario = u.id_usuario
	      WHERE id_orden_compra=$id";
		  
	$res=mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_assoc($res);
	extract($row);
	
	$pdf->celpos(17, 4.1, 10, $ftam, $fecha,0,"L");
	$pdf->celpos(13.5, 5.6, 6.7, $ftam+2, $hechapor,0,"C");	  
	
	
	$pdf->SetTextColor(255, 255, 255);
	$pdf->celpos(18.5, 3.1, 10, $ftam+4, "$folio",0,"L");
	$pdf->SetTextColor(0, 0, 0);
	
	$pdf->SetDrawColor(76, 40, 6);
	$pdf->Rect(1, 10.9, 19.5, 0.8);
	
	
	
	$sql="SELECT
	      p.nombre AS producto,
	      d.cantidad AS cantidad
	      FROM ec_oc_detalle d
	      JOIN ec_productos p ON d.id_producto = p.id_productos
	      WHERE d.id_orden_compra=$id";
	$res=mysql_query($sql) or die(mysql_error());
	$num=mysql_num_rows($res);
	
	
	$yini=11.7;
	
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_assoc($res);
		extract($row);
		
		
		$pdf->Rect(1, $yini, 10, 0.8);
		$pdf->Rect(11, $yini, 9.5, 0.8);
		
		$pdf->celpos(1, $yini+0.2, 10, $ftam+1, $producto,0,"L");
		$pdf->celpos(11, $yini+0.2, 9.5, $ftam+1, $cantidad,0,"C");
		
		$yini+=0.8;
	}
	
	$sql="SELECT
	      concepto
	      FROM ec_oc_otros
	      WHERE id_orden_compra=$id";
	
	$res=mysql_query($sql) or die(mysql_error());
	$num=mysql_num_rows($res);
	
	if($num > 0)
	{
		$pdf->SetTextColor(31, 151, 208);
		$pdf->celpos(1.5, $yini+1.5, 10, $ftam+6, "OTROS CONCEPTOS",0,"L");
		$pdf->SetTextColor(233, 188, 47);
		$pdf->celpos(1, $yini+2.5, 10, $ftam+6, "Nombre:",0,"L");
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->Rect(1, $yini+2.35, 19.5, 0.8);
		
		$yini+=2.35+0.8;
		
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_assoc($res);
			extract($row);
			
			
			$pdf->Rect(1, $yini, 19.5, 0.8);
			
			$pdf->celpos(1, $yini+0.2, 19.5, $ftam+1, $concepto,0,"L");
			
			
			$yini+=0.8;
		}
		
	}
	
?>