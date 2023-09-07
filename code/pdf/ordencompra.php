<?php

	
	$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);
	$pdf->AddPage();
	$pdf->SetFont('helvetica','',$ftam);
	$pdf->SetAutoPageBreak(false);
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	$pdf->Image("fondoorden.png" , 0 , 0, 21.6, 27.9);
	
	//Buscamos el logo de la empresa
	$sql="SELECT logo, nombre, telefono, direccion FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
	$res=mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_assoc($res);
	extract($row);
	
	//4.4X3.5
	
	
	//echo  $logo."<br>".$rooturl."<br>".$rootpath;
	
	$logo=str_replace($rooturl, $rootpath, $logo);
	
	//die("<br>".$logo);
	
	$pdf->Image($logo , 1 , 1, 4.4, 3.5);
	
	$pdf->celpos(3.1, 4.8, 10, $ftam, $nombre,0,"L");
	$pdf->celpos(2.3, 5.5, 10, $ftam, $telefono,0,"L");
	$pdf->celpos(1.8, 6.4, 12, $ftam, $direccion,0,"L");
	
	
	//Buscamos datos de cabcera
	$sql="SELECT 
	      folio AS folio,
	      CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS hechapor,
	      oc.fecha as fecha,
	      p.nombre_comercial AS proveedor,
	      FORMAT(subtotal,2) AS subtotal,
	      FORMAT(iva,2) AS iva,
	      FORMAT(total,2) AS total
	      FROM ec_ordenes_compra oc
	      JOIN ec_proveedor p ON oc.id_proveedor = p.id_proveedor
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
	
	
	$pdf->celpos(13.5, 7.9, 6.7, $ftam, $proveedor,0,"C");
	$pdf->celpos(16, 24.1, 4, $ftam, $subtotal,0,"R");
	$pdf->celpos(16, 24.7, 4, $ftam, $iva,0,"R");
	$pdf->celpos(16, 25.3, 4, $ftam, $total,0,"R");
	
	$pdf->SetDrawColor(31, 151, 208);
	$pdf->Rect(1, 10.9, 19.5, 0.8);
	
	
	
	$sql="SELECT
	      p.nombre AS producto,
	      d.cantidad AS cantidad,
	      CONCAT('$', FORMAT(d.precio, 2)) As precio,
	      CONCAT('$', FORMAT(d.precio*d.cantidad, 2)) AS monto
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
		
		
		$pdf->Rect(1, $yini, 8, 0.8);
		$pdf->Rect(9, $yini, 3.7, 0.8);
		$pdf->Rect(12.7, $yini, 3.7, 0.8);
		$pdf->Rect(16.4, $yini, 4.1, 0.8);
		
		$pdf->celpos(1, $yini+0.2, 8, $ftam, $producto,0,"L");
		$pdf->celpos(9, $yini+0.2, 3.7, $ftam, $cantidad,0,"C");
		$pdf->celpos(12.7, $yini+0.2, 3.7, $ftam, $precio,0,"R");
		$pdf->celpos(16.4, $yini+0.2, 4.1, $ftam, $monto,0,"R");
		
		$yini+=0.8;
	}
	
	$sql="SELECT
	      concepto,
	      CONCAT('$', FORMAT(precio, 2)) AS precio
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
		$pdf->celpos(14, $yini+2.5, 10, $ftam+6, "Precio:",0,"L");
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->Rect(1, $yini+2.35, 19.5, 0.8);
		
		$yini+=2.35+0.8;
		
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_assoc($res);
			extract($row);
			
			
			$pdf->Rect(1, $yini, 11.5, 0.8);
			$pdf->Rect(12.5, $yini, 8, 0.8);
			
			$pdf->celpos(1, $yini+0.2, 11.5, $ftam, $concepto,0,"L");
			$pdf->celpos(12.5, $yini+0.2, 8, $ftam, $precio,0,"R");
			
			
			$yini+=0.8;
		}
		
	}
	
?>