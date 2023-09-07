<?php
	$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);

	
		
		$pdf->AddPage();
		$pdf->SetFont('helvetica','',$ftam);
		$pdf->SetAutoPageBreak(false);
	
		//Conseguimos los datos
	
		$sql="	SELECT
				t.folio AS ID,
				s.nombre AS 'Suc. origen',
				s2.nombre AS 'Suc. destino',
				CONCAT(t.fecha, ' ', t.hora) AS 'Fecha',
				a.nombre,
				a2.nombre,
			/*implementación Oscar 26.02.2019 para insertar al final las observaciones de la transferencia*/
				/*IF(t.es_resolucion=1,'',t.observaciones)*/
				t.observaciones
			/*Fin de cambio Oscar 26.02.2019*/
				FROM ec_transferencias t
				JOIN sys_sucursales s ON t.id_sucursal_origen = s.id_sucursal
				JOIN sys_sucursales s2 ON t.id_sucursal_destino = s2.id_sucursal
				JOIN ec_estatus_transferencia e ON t.id_estado = e.id_estatus
				JOIN ec_almacen a ON t.id_almacen_origen = a.id_almacen
				JOIN ec_almacen a2 ON t.id_almacen_destino = a2.id_almacen
				WHERE t.id_transferencia=$id";
			
		$res=mysql_query($sql);
		if(!$res)
			die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());		
		
		$row=mysql_fetch_row($res);	
		
		
		$folioTrans = $row[0] . ".pdf";
	
	/*implementación Oscar 26.02.2019 para insertar al final las observaciones de la transferencia*/
		$observaciones=$row[6];
	//consultamos si se va a mostrar la ubicación del almacen en las observaciones
		$sql="SELECT IF(imprime_ubicacion_pdf_transf IS NULL,0,imprime_ubicacion_pdf_transf) FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
		$eje=mysql_query($sql)or die("Error al consultar si se imprime la ubicación de la sucursal!!!\n\n".mysql_error()."\n\n".$sql); 
		$muestra_ubicacion_sucursal=mysql_fetch_row($eje);
		if($muestra_ubicacion_sucursal[0]=='' || $muestra_ubicacion_sucursal[0]==null){
			$muestra_ubicacion_sucursal[0]=0;
		}
	/*Fin de cambio Oscar 26.02.2019*/

/*Implementación Oscar 10.05.2019 para sacar el id de la transferencia original*/
	$sql="SELECT REPLACE(t.folio,CONCAT('RESOLUCION',s.prefijo),'')
		FROM ec_transferencias t 	
		LEFT JOIN sys_sucursales s ON t.id_sucursal_origen=s.id_sucursal
		WHERE t.id_transferencia=$id";
	$eje_or=mysql_query($sql)or die("Error al consultar el id de la transferencia original");
	$id_original=mysql_fetch_row($eje_or);
/*Fin de cambio Oscar 10.05.2019*/
	$sql="SELECT
			p.ubicacion_almacen,
			p.clave,
			p.nombre,
			tp.cantidad,
	/*Implementación Oscar 26.02.2019 para agregar la ubicación en el almacén de la sucursal*/
    		IF(t.es_resolucion=1,
    			IF( (SELECT calculo_resolucion FROM ec_transferencia_productos WHERE id_transferencia='$id_original[0]' AND id_producto_or=p.id_productos LIMIT 1)<0,
    				CONCAT('De más'),
    				IF((SELECT calculo_resolucion FROM ec_transferencia_productos WHERE id_transferencia='$id_original[0]' AND id_producto_or=p.id_productos LIMIT 1)>0,
    					CONCAT('Faltaron'),''
    				)
    			),
    			CONCAT(
    				IF($muestra_ubicacion_sucursal[0]=1 AND sp.ubicacion_almacen_sucursal!='',CONCAT(sp.ubicacion_almacen_sucursal,' | '),''),
    				IF(dp.id_producto is NULL,' ', CONCAT((1/dp.cantidad)*tp.cantidad,' ',pr.nombre,IF((1/dp.cantidad)*tp.cantidad>1,'S','')))
    			) 
    		)as observaciones    		
	/*Fin de cambio Oscar 26.02.2019*/
		FROM ec_transferencia_productos tp 
		LEFT JOIN ec_productos_detalle dp ON tp.id_producto_or=dp.id_producto_ordigen 
		JOIN ec_productos p ON tp.id_producto_or=p.id_productos
		JOIN sys_sucursales_producto sp on sp.id_producto=tp.id_producto_or
		LEFT JOIN ec_productos_presentaciones pr ON p.id_productos=pr.id_producto
		RIGHT JOIN ec_transferencias t on t.id_transferencia=tp.id_transferencia AND sp.id_sucursal=t.id_sucursal_destino
		WHERE tp.id_transferencia=$id
		ORDER BY p.ubicacion_almacen ASC, p.orden_lista ASC";	
			
	$re=mysql_query($sql);
	if(!$re)
		die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());		
	
	$nu=mysql_num_rows($re);
	
	$npags=ceil($nu/31);
	$pagact=1;
	
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	//$pdf->Image("casalogo.jpg" , 1 , 1, 2.35, 3.28);
	
	
	$pdf->celpos(4, 1.4, 10, $ftam, "SUC. ORIGEN:",0,"L");
	$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
	$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
	
	$pdf->celpos(4, 2.2, 10, $ftam-2, "ALMACEN ORIGEN:",0,"L");
	$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
	$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
	
	$pdf->celpos(4, 3, 10, $ftam-2, "FECHA:",0,"L");
	$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");
	$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");

	$pdf->celpos(11, 1.4, 10, $ftam, "SUC. DESTINO:",0,"L");
	$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
	$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
	
	$pdf->celpos(11, 2.2, 10, $ftam-2, "ALMACEN DESTINO:",0,"L");
	$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
	$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
		
	$pdf->celpos(11, 3, 10, $ftam-2, "HOJA:",0,"L");
	$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
	$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
	
	$pdf->celpos(16, 3, 10, $ftam-2, "FOLIO:",0,"L");
	$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");
	$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");
	
	
	$pdf->SetFillColor(210, 210, 210);
	
	
	$pdf->SetFont('helvetica','',$ftam-2);
	
/*implementación Oscar 2018.11.03 para numero consecutivo*/
	$pdf->setXY(1,4.5);
	$pdf->Cell(.7, 1, utf8_decode("#"), 1, 0, 'C', true);
/*Fin de cambio Ocar 2018.11.03*/
	$pdf->setXY(1.7,4.5);//se hace mas chica la celda de ubicacion
	$pdf->Cell(1.8, 1, utf8_decode("Ubicación"), 1, 0, 'C', true);
	
	$pdf->setXY(3.5,4.5);//.7
	$pdf->Cell(4, 1, utf8_decode("Código Proveedor"), 1, 0, 'C', true);

	$pdf->setXY(7.5,4.5);
	$pdf->Cell(7, 1, utf8_decode("Producto"), 1, 0, 'C', true);

	$pdf->setXY(14.5,4.5);
	$pdf->Cell(1.5, 1, utf8_decode("Cantidad"), 1, 0, 'C', true);//-.5
//aqui se agrega ueva columna "recibido"	
	$pdf->setXY(16,4.5);
	$pdf->Cell(1.5, 1, utf8_decode("Recibido"), 1, 0, 'C', true);
	
	$pdf->setXY(17.5,4.5);
	$pdf->Cell(3, 1, utf8_decode("Observaciones"), 1, 0, 'C', true);
	
	
	
	//newPage($pdf, $id);
	
	
	
	
	
	$fill=0;
	$relleno=false;
	$cont=0;

	for($i=0;$i<$nu;$i++){
		$ro=mysql_fetch_row($re);
		if($fill == 1){
			$relleno=true;
		}else{
			$relleno=false;	
		}

	/*implementación Oscar 2018.11.03 para numero consecutivo*/
		$pdf->setXY(1,5.5+$cont*.7);
		//$pdf->setXY(.3,4.5);
		$pdf->Cell(.7, .7, utf8_decode($i+1), 1, 0, 'C', $relleno);
	/*Fin de cambio Ocar 2018.11.03*/
		
		$pdf->Cell(1.8, 0.7, utf8_decode($ro[0]), 1, 0, 'C', $relleno);
		
		//$pdf->setXY(3.7,5.5+$i*.7);
		$pdf->Cell(4, 0.7, utf8_decode($ro[1]), 1, 0, 'C', $relleno);
		
		$pdf->Cell(7, 0.7, utf8_decode($ro[2]), 1, 0, 'L', $relleno);

		$pdf->Cell(1.5, 0.7, utf8_decode($ro[3]), 1, 0, 'R', $relleno);

	//aqui se gregan las filas nuevas
		$pdf->Cell(1.5, 0.7, utf8_decode(""), 1, 0, 'C', $relleno);
		
		$pdf->Cell(3, 0.7, utf8_decode($ro[4]), 1, 0, 'L', $relleno);//aqui se insertan las observaciones
		
		
		if($cont == 30){
		
			$pagact++;
		
			$pdf->AddPage();
			$pdf->SetFont('helvetica','',$ftam);
			$pdf->SetAutoPageBreak(false);
			
			$pdf->celpos(4, 1.4, 10, $ftam, "SUC. ORIGEN:",0,"L");
			$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
			$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
	
			$pdf->celpos(4, 2.2, 10, $ftam-2, "ALMACEN ORIGEN:",0,"L");
			$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
			$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
	
			$pdf->celpos(4, 3, 10, $ftam-2, "FECHA:",0,"L");
			$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");
			$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");


			$pdf->celpos(11, 1.4, 10, $ftam, "SUC. DESTINO:",0,"L");
			$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
			$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
	
			$pdf->celpos(11, 2.2, 10, $ftam-2, "ALMACEN DESTINO:",0,"L");
			$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
			$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
	
	
			$pdf->celpos(11, 3, 10, $ftam-2, "HOJA:",0,"L");
			$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
			$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
	
			$pdf->celpos(16, 3, 10, $ftam-2, "FOLIO:",0,"L");
			$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");
			$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");
	
	
			$pdf->SetFillColor(210, 210, 210);
	
	
			$pdf->SetFont('helvetica','',$ftam-2);
	
		/*implementación Oscar 2018.11.03 para numero consecutivo*/
			$pdf->setXY(1,4.5);
			$pdf->Cell(.7, 1, utf8_decode("#"), 1, 0, 'C', true);
		/*Fin de cambio Ocar 2018.11.03*/
			$pdf->setXY(1.7,4.5);//se hace mas chica la celda de ubicacion
			$pdf->Cell(1.8, 1, utf8_decode("Ubicación"), 1, 0, 'C', true);
	
			$pdf->setXY(3.5,4.5);//.7
			$pdf->Cell(4, 1, utf8_decode("Código Proveedor"), 1, 0, 'C', true);

			$pdf->setXY(7.5,4.5);
			$pdf->Cell(7, 1, utf8_decode("Producto"), 1, 0, 'C', true);
	
			$pdf->setXY(14.5,4.5);
			$pdf->Cell(1.5, 1, utf8_decode("Cantidad"), 1, 0, 'C', true);//-.5
		//aqui se agrega ueva columna "recibido"	
			$pdf->setXY(16,4.5);
			$pdf->Cell(1.5, 1, utf8_decode("Recibido"), 1, 0, 'C', true);
	
			$pdf->setXY(17.5,4.5);
			$pdf->Cell(3, 1, utf8_decode("Observaciones"), 1, 0, 'C', true);
			
			$fill=1;
			$cont=-1;
		}//fin de si el contador llegó a 30
		
		
		$fill=1-$fill;
		$cont++;
	}//fin de for i

/*Implementación Oscar 27.02.2019 para agregar al final las observaciones de la transferencia*/
	if($cont>=29 && $observaciones!=''){
		$pdf->AddPage();
		$pdf->SetFont('helvetica','',$ftam);
		$pdf->SetAutoPageBreak(false);
		
		$pdf->celpos(4, 1.4, 10, $ftam, "SUC. ORIGEN:",0,"L");
		$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
		$pdf->celpos(6.5, 1.33, 10, $ftam+2, $row[1],0,"L");
	
		$pdf->celpos(4, 2.2, 10, $ftam-2, "ALMACEN ORIGEN:",0,"L");
		$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
		$pdf->celpos(6.7, 2.2, 10, $ftam-2, $row[4],0,"L");
	
		$pdf->celpos(4, 3, 10, $ftam-2, "FECHA:",0,"L");
		$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");
		$pdf->celpos(5.2, 3, 10, $ftam-2, $row[3],0,"L");

		$pdf->celpos(11, 1.4, 10, $ftam, "SUC. DESTINO:",0,"L");
		$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
		$pdf->celpos(13.7, 1.25, 10, $ftam+8, $row[2],0,"L");
	
		$pdf->celpos(11, 2.2, 10, $ftam-2, "ALMACEN DESTINO:",0,"L");
		$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
		$pdf->celpos(13.9, 2.2, 10, $ftam-2, $row[5],0,"L");
	
		$pdf->celpos(11, 3, 10, $ftam-2, "HOJA:",0,"L");
		$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
		$pdf->celpos(12, 3, 10, $ftam-2, "$pagact DE $npags",0,"L");
	
		$pdf->celpos(16, 3, 10, $ftam-2, "FOLIO:",0,"L");
		$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");
		$pdf->celpos(17.1, 3, 10, $ftam-2, $row[0],0,"L");

		$pdf->setXY(1,5.5+$cont*.7);
		//$pdf->MultiCell(19.5, 2, utf8_decode("Observaciones:\n".$observaciones), 1, 0, 'C', $relleno);
		$pdf->MultiCell(19.5, .5, utf8_decode("Observaciones:".str_replace("", " ",$observaciones )),"", "L", false);
	/*Fin de cambio Ocar 2018.11.03*/
	}//fin de si el contador es igual a 30
	if($observaciones!=''){
		$pdf->setXY(1,5.8+$cont*.7);
		//$pdf->MultiCell(19.5, 2, utf8_decode("Observaciones:\n".$observaciones), 1, 0, 'C', $relleno);
		$pdf->MultiCell(19.5, .5, utf8_decode("Observaciones:".str_replace("", " ",$observaciones )),"", "L", true);
	}
/*Fin de Cambio Oscar 27.02.2019*/	
?>