<?php
	$cs="SELECT
			ax.id_producto,
			ax.producto AS producto,
			ax.cantidad,
	       	ax.precio,
	       	ax.monto,
	       	ax.descuentoProds,
		/*implementación Oscar 10.10.2018 para mostrar ubicacion y alfanumerico*/
	       	IF(s.mostrar_ubicacion=1 OR s.mostrar_alfanumericos=1,1,0) as infoAdicional,
          	CONCAT(
          		IF(s.mostrar_ubicacion=1,
                	IF($user_sucursal=1,
                  		CONCAT('Ubicación: ',ax.ubicacion_almacen,' '),
                  		IF(sp.ubicacion_almacen_sucursal!='',
                      		CONCAT('Ubicacion: ',sp.ubicacion_almacen_sucursal,'  '),
                    		''
                  		)
                	),
                	''
              	),
            	IF(s.mostrar_alfanumericos=0,'',CONCAT('Clave: ',ax.clave))
          )as info,
         (SELECT 
			DATE_SUB(CURDATE(), INTERVAL -1 DAY) 
		 ) AS is_special
		/*Fin de cambio 10.10.2018*/
		FROM(
			SELECT
	       		P.id_productos AS id_producto,
	       		P.nombre AS producto,
	       		P.clave,
       			PD.cantidad,
	       		PD.precio,
	       		PD.monto,
	       		PD.descuento AS descuentoProds,
	       		P.ubicacion_almacen
	       FROM ec_productos P
	       INNER JOIN ec_pedidos_detalle PD ON PD.id_producto = P.id_productos
	       WHERE PD.id_pedido = '{$id_pedido}'
	       AND( PD.id_producto = 2758
			OR PD.id_producto = 2854
			OR PD.id_producto = 2759
			OR PD.id_producto = 1918
			OR PD.id_producto = 3317
			OR PD.id_producto = 1820
			OR PD.id_producto = 2760
			OR PD.id_producto = 2761
			OR PD.id_producto = 2767
			OR PD.id_producto =	2768
			OR PD.id_producto = 1956
			OR PD.id_producto = 3813
			OR PD.id_producto = 3814
			OR PD.id_producto = 2769
			OR PD.id_producto = 3628
			OR PD.id_producto = 2736
			OR PD.id_producto = 4118
			OR PD.id_producto = 2007
			OR PD.id_producto = 2764
			OR PD.id_producto = 2008
			OR PD.id_producto = 2113
			OR PD.id_producto = 3318
			OR PD.id_producto = 1826
			OR PD.id_producto = 2762
			OR PD.id_producto = 2763
			OR PD.id_producto = 4104
			OR PD.id_producto = 4105
			OR PD.id_producto = 2770
			OR PD.id_producto = 2771
			OR PD.id_producto = 2715
			OR PD.id_producto = 3821
			OR PD.id_producto = 3822
			OR PD.id_producto = 2772
			OR PD.id_producto = 3629
			OR PD.id_producto = 2735
			OR PD.id_producto = 4117 )
	       GROUP BY PD.id_pedido_detalle/*P.id_productos*/
	       ORDER BY PD.id_pedido_detalle
	    )ax
		LEFT JOIN sys_sucursales_producto sp ON ax.id_producto=sp.id_producto
		JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal AND s.id_sucursal IN($user_sucursal)";
	//die( $cs );
	$descProds=0;
//implementacion Oscar 2023/12/03 para imprimir ticket de productos que se entregan al dia siguiente
	$special_products = 0;
	if ($rs = mysql_query($cs))
	{
	//Sumamos descuentos de productos Oscar(04-11-2017)
		/*while($rw=mysql_fetch_row($rs)){
			$descProds+=$rw[5];
		}*/
	//
		$productos = array();
		while ($dr = mysql_fetch_assoc($rs)){
			
			if($dr["producto"] == "Pocas piezas"){
				$dr["producto"] .= " \${$dr["precio"]}";
				$lineas_productos += ceil(strlen($dr["producto"])/32.0);
				array_push($productosP,$dr);

			}else{
				// Concatenar precio unitario en la descripción
				$dr["producto"] .= " \${$dr["precio"]}";
				$lineas_productos += ceil(strlen($dr["producto"])/32.0);
				array_push($productos, $dr);
			}
			$descProds+=$dr['descuentoProds'];	
		/*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
			if($dr["infoAdicional"]==1){
				$lineas_productos+=.8;
			}

			if($dr["is_special"] != '' ){
				$lineas_productos+=2;
				$special_products ++;//implementacion Oscar 2023/12/03 para imprimir ticket de productos que se entregan al dia siguiente
			}
		/*fin de cambio 10.10.2018*/	
		}
		mysql_free_result($rs);
	}
	
	$cs = "SELECT CONCAT(PP.fecha,' ',TP.nombre,'(pagos)') as nombre, sum(monto) as monto FROM ec_pedido_pagos PP
			INNER JOIN ec_tipos_pago TP ON PP.id_tipo_pago = TP.id_tipo_pago
			WHERE PP.id_pedido = '{$id_pedido}' AND (referencia='' OR referencia=null)
			GROUP BY CONCAT(PP.fecha,' ',PP.hora)
			ORDER BY PP.id_pedido_pago ASC";//TP.nombre
	
	if ($rs = mysql_query($cs))
	{
		while ($dr = mysql_fetch_assoc($rs))
		{
			// Concatenar precio unitario en la descripción
			++$lineas_pagos;
			$total_pagos += $dr["monto"];
			array_push($pagos, $dr);
		}
		mysql_free_result($rs);
	}
/*implementación de Oscar 19.11.2018 para restar devoluciones*/
	$sql="SELECT SUM( IF(dev.id_devolucion IS NULL,0,IF(referencia='' OR referencia=null,dp.monto,0) ) )
			FROM ec_devolucion_pagos dp 
			LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
			WHERE dev.id_pedido=$id_pedido";
	$eje=mysql_query($sql)or die("Error al calcular monto de devoluciones!!!\n\n".$sql."\n\n".mysql_error());
	$res_dev=mysql_fetch_row($eje);
	
	$monto_devolucion=$res_dev[0];//aqui capturamos el monto de la devolucion
//indicador de lineas de devolución
	$lineas_dev=0;
	if($monto_devolucion>0){
		$lineas_dev=5;
	}
/*fin de cambio 06.09.2018*/
	
	//+40+130
	//$ticket = new TicketPDF("P", "mm", array(80,$lineas_dev+$lineas_productos*6+($total!=$subtotal?12:0)+($pagado>0?14:30)+(count($pagos)>0?($lineas_pagos+1)*6:0)+40+40), "{$sucursal}", "{$folio}", 10);
	//$ticket->AliasNbPages();
	//$ticket->AddPage();
	
	$bF=10;

	$ticket->SetXY(5, $ticket->GetY()+50);
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(10, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(15, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(20, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(25, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(30, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(35, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(40, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(45, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(50, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(55, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(60, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(65, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(70, $ticket->GetY());
	$ticket->Cell(3, .2, "", "TB" ,0, "L");
	$ticket->SetXY(75, $ticket->GetY());
	$ticket->Cell(1.5, .2, "", "TB" ,0, "L");

	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(5, $ticket->GetY()+2);
	$ticket->MultiCell(66, 4, utf8_decode("Recortar"), "", "C", false);
	

$ticket->SetFont('Arial','',$bF-2);
/*Deshabiltado por Oscar 2024-05-20*/
	//$ticket->SetXY(5, $ticket->GetY()+4);
	//$ticket->MultiCell(66, 6, utf8_decode('datos:'.$datos_fiscales), "" ,0, "C");
	//$ticket->MultiCell(66, 4, utf8_decode($datos_fiscales), "", "C", false);
/*Oscar 2024-05-20*/
	
	$ticket->SetFont('Arial','',$bF+2);
	
	$ticket->SetXY(5, $ticket->GetY()+4);
	$ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	
	$ticket->SetX(5+66*0.6);
	$ticket->Cell(66*0.4, 6, utf8_decode("{$ticket->pedido}"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+4.5);
/*implementación Oscar 28.02.2019 para que la hora del ticket sea tomada de la MySQL*/
	$ticket->Cell(66, 6, utf8_decode("Estado de México ") . utf8_decode($fecha_tkt), "" ,0, "C");
/*Fin de cambio Oscar 28.02.2019*/
	
	$ticket->SetXY(5, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("VENDEDOR:  {$vendedor}"), "" ,0, "L");
	
	$ticket->SetXY(5, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66*0.63, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.63, $ticket->GetY());
	$ticket->Cell(66*0.12, 6, utf8_decode("CANT"), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, utf8_decode("PRECIO"), "B" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(5, $ticket->GetY()+8);
	
	foreach ($productos as $producto) {
	    	   
	    $y = $ticket->GetY();	
		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
	
		$ticket->SetXY(5+66*0.63, $y);
		$ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
	
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);
	
	/*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
		if($producto['infoAdicional']==1){
			$ticket->SetFont('Arial','',$bF-3.5);
			$ticket->SetXY(5,($ticket->GetY()-1.5));
			$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["info"]}"), "", "L", false);
		}

		

		if( $producto["is_special"] != '' ){
			$ticket->SetXY(5, $ticket->GetY() + 2 );
			$ticket->MultiCell(35, 4, utf8_decode("Fecha entrega : \n {$producto["is_special"]} 18:30 hrs"), "1", "C", false);
			$ticket->SetXY(40, $ticket->GetY() - 8 );
			$ticket->MultiCell(35, 4, utf8_decode("\n\n"), "1", "C", false);
			$ticket->SetXY(5, $ticket->GetY() + 4 );
		}

//http://localhost/pruebas_etiquetas/touch_desarrollo/index.php?scr=ticket&idp=425108

		$ticket->SetFont('Arial','',$bF-2);
	/*fin de cambio 10.10.2018*/
	}
	
	foreach ($productosP as $productoP) {
	    	   
	    $y = $ticket->GetY();	

		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4, "$ " . number_format($productoP["monto"], 2), "", "R", false);
	
		$ticket->SetXY(5+66*0.63, $y);
		$ticket->MultiCell(66*0.12, 4, $productoP["cantidad"], "", "C", false);
	
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.63, 4, utf8_decode("{$productoP["producto"]}"), "", "L", false);


		

		if( $productoP["is_special"] != '' ){
			$ticket->SetXY(5, $ticket->GetY() + 4 );
			$ticket->MultiCell(35, 4, utf8_decode("Fecha entrega"), "", "C", false);
			$ticket->MultiCell(35, 39, utf8_decode("{$productoP["is_special"]}"), "", "C", false);
		}



		

	}

   /* if(file_exists("../img/codigos_barra/".$folio.".png")){
    	$ticket->SetXY(5, $ticket->GetY()+3);
    	$ticket->Image("../img/codigos_barra/".$folio.".png", 15, $ticket->GetY()+5,46);
    }
    */
  //  $nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_nex_day_.pdf";
  // 	$ticket->Output("../cache/ticket/".$nombre_ticket, "F");
		
?>