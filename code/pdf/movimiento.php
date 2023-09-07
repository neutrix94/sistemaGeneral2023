<?php

	
	$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);
	$pdf->AddPage();
	$pdf->SetFont('helvetica','',$ftam);
	$pdf->SetAutoPageBreak(false);
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	$pdf->Image("fondomovimiento.png" , 0 , 0, 21.6, 27.9);
	
	
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
	
	
	//Buscamos datos de cabcera
	$sql="SELECT 
	      id_movimiento_almacen AS folio,
	      CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS hechapor,
	      m.fecha as fecha,
	      tm.nombre as tipoMov
	      FROM ec_movimiento_almacen m
	      JOIN sys_users u ON m.id_usuario = u.id_usuario
	      JOIN ec_tipos_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
	      WHERE id_movimiento_almacen=$id";
		  
	$res=mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_assoc($res);
	extract($row);
	
	$pdf->celpos(17, 4.1, 10, $ftam, $fecha,0,"L");
	$pdf->celpos(13.5, 5.6, 6.7, $ftam+2, $hechapor,0,"C");
	
	$pdf->celpos(15.7, 8, 6.7, $ftam, $tipoMov,0,"L");	  
	
	
	$pdf->SetTextColor(255, 255, 255);
	$pdf->celpos(18.5, 3.1, 10, $ftam+4, "$folio",0,"L");
	$pdf->SetTextColor(0, 0, 0);
	
	
	$pdf->SetDrawColor(31, 151, 208);
	$pdf->Rect(1, 10.9, 19.5, 0.8);
	
	
	$sql="SELECT
	      p.nombre AS producto,
	      d.cantidad_surtida AS cantidad
	      FROM ec_movimiento_detalle d
	      JOIN ec_productos p ON d.id_producto = p.id_productos
	      WHERE d.id_movimiento=$id";
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
	
?>