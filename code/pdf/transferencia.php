<?php

		$pdf=new PDF($orient_doc,$unid_doc,$tamano_doc);
		
		$pdf->SetFont('helvetica','',$ftam);
		$pdf->AddPage();
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
				t.observaciones,
			/*Fin de cambio Oscar 26.02.2019*/
			/*implementacion Oscar 2021 para mostrar el título de las transferencias*/
				t.titulo_transferencia,
				t.id_tipo
			/*fin de cambio Oscar 2021*/
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
	//generación del código de barras del folio de transferencia
			include('../../include/barcode/barcode.php');
			//$barcode_1 = ( isset( $_POST['code'] ) ? trim( $_POST['code'] ) : ''  );
			$barcode_name = str_replace(' ', '', $row[0] );
			$barcodePath="../../img/codigos_barra/{$barcode_name}.png";
			barcode( $barcodePath, $row[0],'50','horizontal','code128',true,1);
			//die( 'here : ' . $filepath );
		
		$folioTrans = str_replace( ' ', '', $row[0] ) . ".pdf";
	
	/*implementación Oscar 26.02.2019 para insertar al final las observaciones de la transferencia*/
		$observaciones=$row[6];
		$tipo_transfer = $row[8];
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
				ax.ubicacion_almacen,
				ax.clave,
				ax.nombre,
				ax.cantidad,
				ax.observaciones,
				ax.ubic_1,
				ax.ubic_2,
				ax.orden_lista
			FROM(
				SELECT
					p.ubicacion_almacen,
					pp.clave_proveedor AS clave,
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
		    				IF(dp.id_producto is NULL,
		    				/*implementacion Oscar 2021*/
		    					IF(pr.id_producto_presentacion IS NULL,
		    						'',
		    						CONCAT(ROUND((1/pr.cantidad)*tp.cantidad),' ',pr.nombre,IF((1/pr.cantidad)*tp.cantidad>1,'s',''), ' de ', pr.cantidad,' ', pr.unidad_medida)
		    					), 
							/*fin de cambio Oscar 2021*/
		    					CONCAT((1/dp.cantidad)*tp.cantidad,' ',pr.nombre,IF((1/dp.cantidad)*tp.cantidad>1,'s',''), ' de ', pr.cantidad,' ', pr.unidad_medida)
		    				)
		    			) 
		    		)as observaciones,
					p.orden_lista,
					SUBSTRING_INDEX (p.ubicacion_almacen, '*', 1) AS ubic_1,
					SUBSTRING_INDEX (p.ubicacion_almacen, '*', 2) AS ubic_2,
					tp.id_transferencia_producto
			/*Fin de cambio Oscar 26.02.2019*/
				FROM ec_transferencia_productos tp 
				LEFT JOIN ec_productos_detalle dp ON tp.id_producto_or=dp.id_producto_ordigen 
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				JOIN ec_productos p ON tp.id_producto_or=p.id_productos
				JOIN sys_sucursales_producto sp on sp.id_producto=tp.id_producto_or
				LEFT JOIN ec_productos_presentaciones pr ON p.id_productos=pr.id_producto
				RIGHT JOIN ec_transferencias t on t.id_transferencia=tp.id_transferencia AND sp.id_sucursal=t.id_sucursal_destino
				WHERE tp.id_transferencia=$id
				GROUP BY tp.id_transferencia_producto
				ORDER BY p.ubicacion_almacen ASC, p.orden_lista ASC
			)ax
			ORDER BY ax.ubic_1 ASC, ax.ubic_2 ASC, ax.orden_lista ASC";	
			//die($sql);
	$re=mysql_query($sql);
	if(!$re)
		die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());		
	
	$nu=mysql_num_rows($re);
	$aux_counter = $nu - 22;
	$npags= ($aux_counter > 0 ? 1 + ceil($aux_counter/31) : 1);
	$pagact=1;
	
	
	//$pdf->Image('firma_barra.jpg' , 0.5 , 4.4, 20.5, 0.3);
	//$pdf->Image("casalogo.jpg" , 1 , 1, 2.35, 3.28);
	/**/
	
	/**/

/*implementacion Oscar 2021 para mostrar el título de las transferencias*/
	
	$pdf->SetFont('helvetica','',$ftam+8);
	$pdf->SetFillColor(210, 210, 210);
	$pdf->setXY(.7,0.2);
	$pdf->Cell(20, 1, utf8_decode("$row[7]"), 0, 1, 'C', true);

	$pdf->celpos(18.2, .5, 10, $ftam-2, "HOJA:",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
/*Fin de cambio Oscar 2021*/
	
	$pdf->celpos(4, 1.45, 10, $ftam-2, "ORIGEN:",0,"L");
	$pdf->celpos(5.7, 1.33, 10, $ftam+3, $row[4],0,"L");
	$pdf->celpos(5.7, 1.33, 10, $ftam+3, $row[4],0,"L");
	
	$pdf->celpos(11, 1.45, 10, $ftam-2, "DESTINO:",0,"L");
	$pdf->celpos(12.9, 1.33, 10, $ftam+3, $row[5],0,"L");
	$pdf->celpos(12.9, 1.33, 10, $ftam+3, $row[5],0,"L");


	$pdf->celpos(.6, 2.3, 10, $ftam-2, "Surtida por: ___________________________________",0,"L");
	/*$pdf->celpos(7.2, 2, 10, $ftam-2, $row[2],0,"L");
	$pdf->celpos(7.2, 2, 10, $ftam-2, $row[2],0,"L");*/

	$pdf->celpos(8, 2.3, 10, $ftam-2, "FOLIO:",0,"L");
	//$pdf->celpos(9.2, 2.25, 10, $ftam+2, $row[0],0,"L");
	if( file_exists( $barcodePath ) ){
    	$pdf->SetXY(60, $pdf->GetY());
    	$pdf->Image( $barcodePath , 10, $pdf->GetY()-.5,4);
    }

	//$pdf->celpos(7.2, 2, 10, $ftam-2, $row[0],0,"L");
	
	$pdf->celpos(15, 2.3, 10, $ftam-2, "FECHA:",0,"L");
	$pdf->celpos(16.1, 2.3, 10, $ftam-2, $row[3],0,"L");
	$pdf->celpos(16.1, 2.3, 10, $ftam-2, $row[3],0,"L");
/*****************/
/*implementacion Oscar 2021 para meter instrucciones de la transferencia*/
		$texto_instrucciones = " Anotaciones en columna de revisado.   'NO HAY',    'SS' = SE SURTEN (cuando se surte cantidad diferente de la indicada),   Marcar con 'marca textos verde' en la cantidad (cuando el pedido se surte completo), 'OK' (cuando la revisión este correcta).";
		$texto_instrucciones .= "\nErrores o Datos incorrectos: Tacharlos con rojo y escribir el correcto en observaciones.";
		//$pdf->AddPage();
		$pdf->SetFont('helvetica','',$ftam+1);
		//$pdf->setXY(1,5.8+$cont*.7);
		$pdf->setXY(1,3.2);
		$pdf->MultiCell(19.5, .5, utf8_decode("ANOTACIONES PERMITIDAS :".str_replace("", " ",$texto_instrucciones )),"", "L", false);
/**/
		$pdf->setXY(1, 5.6 );
		$pdf->SetFont('helvetica','',$ftam);
		$pdf->MultiCell(19.5, .5, utf8_decode("Observaciones:".str_replace("", " ",$observaciones )),"", "L", true);

		$texto_instrucciones = "Revisó:	______________________	   Fecha y hora de salida: ____________________________	   Transporta:_________________________";
		$texto_instrucciones .= "\n\nHora de llegada a sucursal: _________________   Hora de salida de sucursal: _____________________ Hora de acomodo: _________________";
		//$pdf->AddPage();
		$pdf->SetFont('helvetica','',$ftam-2);
		//$pdf->setXY(1,5.8+$cont*.7);
		$pdf->setXY(1,7.2);
		$pdf->MultiCell(19.5, .5, utf8_decode( str_replace("", " ",$texto_instrucciones ) ),"", "L", false);
	/*if($observaciones!=''){
		//$pdf->setXY(1,(5.8+$cont*.7) );
		$pdf->setXY(1,16 );
		//$pdf->MultiCell(19.5, 2, utf8_decode("Observaciones:\n".$observaciones), 1, 0, 'C', $relleno);
	}*/
/*****************/
	
	$pdf->SetFillColor(210, 210, 210);
	
	
	$pdf->SetFont('helvetica','',$ftam-2);
	
/*implementación Oscar 2018.11.03 para numero consecutivo*/
	$pdf->setXY(0.7,9.5);
	$pdf->Cell(.7, 1, utf8_decode("Caja"), 1, 0, 'C', true);
/*Fin de cambio Ocar 2018.11.03*/
	$pdf->setXY(1.4,9.5);//se hace mas chica la celda de ubicacion
	$pdf->Cell(1.9, 1, utf8_decode("Ubicación"), 1, 0, 'C', true);
	
	$pdf->setXY(3.3,9.5);//.7
	$pdf->Cell(4, 1, utf8_decode("Código Proveedor"), 1, 0, 'C', true);

	$pdf->setXY(7.3,9.5);
	$pdf->Cell(1.5, 1, utf8_decode("Ord. Lista"), 1, 0, 'C', true);

	$pdf->setXY(8.8,9.5);
	$pdf->Cell(5.5, 1, utf8_decode("Producto"), 1, 0, 'C', true);

	$pdf->setXY(14.3,9.5);
	$pdf->Cell(1.5, 1, utf8_decode("Cantidad"), 1, 0, 'C', true);//-.5
//aqui se agrega ueva columna "recibido"	
	$pdf->setXY(15.8,9.5);
	$pdf->Cell(1.5, 1, utf8_decode("Revisado"), 1, 0, 'C', true);
	
	$pdf->setXY(17.3,9.5);
	$pdf->Cell(4, 1, utf8_decode("Observaciones"), 1, 0, 'C', true);
	
	
	
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
		if( $pagact == 1 ){	
			$pdf->setXY(0.7,10.5+$cont*.7);
		}else{
			$pdf->setXY(0.7,4.5+$cont*.7);
		}
		//$pdf->setXY(.3,4.5);

		$pdf->SetFont('helvetica','',$ftam-3);
	
		$pdf->Cell(.7, .7, ( $tipo_transfer == 1 ? 'U-': ($tipo_transfer == 7 ? 'N-' : '')) . utf8_decode($i+1), 1, 0, 'C', $relleno);
	/*Fin de cambio Ocar 2018.11.03*/
		
		$pdf->SetFont('helvetica','',$ftam-2);
		$pdf->Cell(1.9, 0.7, utf8_decode($ro[0]), 1, 0, 'C', $relleno);
		
		//$pdf->setXY(3.7,5.5+$i*.7);
		$pdf->Cell(4, 0.7, utf8_decode($ro[1]), 1, 0, 'C', $relleno);

		$pdf->Cell(1.5, 0.7, utf8_decode($ro[7]), 1, 0, 'C', $relleno);
		
		$pdf->SetFont('helvetica','',$ftam-3);
		$pdf->Cell(5.5, 0.7, utf8_decode($ro[2]), 1, 0, 'L', $relleno);

		$pdf->SetFont('helvetica','',$ftam-2);
		$pdf->Cell(1.5, 0.7, utf8_decode($ro[3]), 1, 0, 'R', $relleno);

	//aqui se gregan las filas nuevas
		$pdf->Cell(1.5, 0.7, utf8_decode(""), 1, 0, 'C', $relleno);
		
		
		$pdf->SetFont('helvetica','',$ftam-4);//aqui se reduce el tamaño de fuente de observaciones
		$pdf->Cell(4, 0.7, utf8_decode($ro[4]), 1, 0, 'L', $relleno);//aqui se insertan las observaciones
		
	    $pdf->SetFont('helvetica','',$ftam-2);
		
		
		if($cont == 30 || ( $pagact == 1 && $cont == 21 )){//modificacion Oscar 2021 para cortr numeroo de celdas en primera hoja
		
			$pagact++;
		
			$pdf->AddPage();
			$pdf->SetFont('helvetica','',$ftam);
			$pdf->SetAutoPageBreak(false);

	/*implementacion Oscar 2021 para mostrar el título de las transferencias*/
			$pdf->SetFont('helvetica','',$ftam+8);
			$pdf->SetFillColor(210, 210, 210);
			$pdf->setXY(.7,0.2);
			$pdf->Cell(20, 1, utf8_decode("$row[7]"), 0, 1, 'C', true);

			

	$pdf->celpos(18.2, .5, 10, $ftam-2, "HOJA:",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
	$pdf->celpos(19.2, .5, 10, $ftam-2, "$pagact DE $npags",0,"L");
/*Fin de cambio Oscar 2021*/
	
	$pdf->celpos(4, 1.45, 10, $ftam-2, "ORIGEN:",0,"L");
	$pdf->celpos(5.7, 1.33, 10, $ftam+3, $row[4],0,"L");
	$pdf->celpos(5.7, 1.33, 10, $ftam+3, $row[4],0,"L");
	
	$pdf->celpos(11, 1.45, 10, $ftam-2, "DESTINO:",0,"L");
	$pdf->celpos(12.9, 1.33, 10, $ftam+3, $row[5],0,"L");
	$pdf->celpos(12.9, 1.33, 10, $ftam+3, $row[5],0,"L");


	$pdf->celpos(.6, 2.3, 10, $ftam-2, "Surtida por: ___________________________________",0,"L");
	/*$pdf->celpos(7.2, 2, 10, $ftam-2, $row[2],0,"L");
	$pdf->celpos(7.2, 2, 10, $ftam-2, $row[2],0,"L");*/

	$pdf->celpos(8, 2.3, 10, $ftam-2, "FOLIO:",0,"L");
	$pdf->celpos(9.2, 2.25, 10, $ftam+2, $row[0],0,"L");
	//$pdf->celpos(7.2, 2, 10, $ftam-2, $row[0],0,"L");
	
	$pdf->celpos(15, 2.3, 10, $ftam-2, "FECHA:",0,"L");
	$pdf->celpos(16.1, 2.3, 10, $ftam-2, $row[3],0,"L");
	$pdf->celpos(16.1, 2.3, 10, $ftam-2, $row[3],0,"L");
/*****************/
	
	
			$pdf->SetFillColor(210, 210, 210);
	
	
			$pdf->SetFont('helvetica','',$ftam-2);
	
		/*implementación Oscar 2018.11.03 para numero consecutivo*/
			$pdf->setXY(0.7,3.5);
			$pdf->Cell(.7, 1, utf8_decode("Caja"), 1, 0, 'C', true);
		/*Fin de cambio Ocar 2018.11.03*/
			$pdf->setXY(1.4,3.5);//se hace mas chica la celda de ubicacion
			$pdf->Cell(1.9, 1, utf8_decode("Ubicación"), 1, 0, 'C', true);
	
			$pdf->setXY(3.3,3.5);//.7
			$pdf->Cell(4, 1, utf8_decode("Código Proveedor"), 1, 0, 'C', true);


			$pdf->setXY(7.3,3.5);
			$pdf->Cell(1.5, 1, utf8_decode("Ord. Lista"), 1, 0, 'C', true);

			$pdf->setXY(8.8,3.5);
			$pdf->Cell(5.5, 1, utf8_decode("Producto"), 1, 0, 'C', true);
	
			$pdf->setXY(14.3,3.5);
			$pdf->Cell(1.5, 1, utf8_decode("Cantidad"), 1, 0, 'C', true);//-.5
		//aqui se agrega ueva columna "recibido"	
			$pdf->setXY(15.8,3.5);
			$pdf->Cell(1.5, 1, utf8_decode("Revisado"), 1, 0, 'C', true);
	
			$pdf->setXY(17.3,3.5);
			$pdf->Cell(4, 1, utf8_decode("Observaciones"), 1, 0, 'C', true);
			
			$fill=1;
			$cont=-1;
		}//fin de si el contador llegó a 30
		
		
		$fill=1-$fill;
		$cont++;
	}//fin de for i

/*Implementación Oscar 27.02.2019 para agregar al final las observaciones de la transferencia
	if($cont>=30 && $observaciones!=''){
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
	/*Fin de cambio Ocar 2018.11.03*
	}//fin de si el contador es igual a 30
	/*Fin de Cambio Oscar 27.02.2019*/
	




?>