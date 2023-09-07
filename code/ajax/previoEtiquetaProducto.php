<?php
	include("../../conectMin.php");
	include("../../include/tcpdf/tcpdf.php");
	$numProd = 0;
//recibimos datos por GET
	$nombre_etiqueta=$_GET['datos_etiqueta'];
	$ord_lista=$_GET['ord_lsta'];
	$id_producto=$_GET['id_prod'];
//armamos consulta
	/*$query="SELECT
				ax.nombre,
				IF(pd.precio_venta is null OR ax.id_productos IS NULL,'0000',pd.precio_venta),
				ax.orden_lista
			FROM(
				SELECT 
					'{$nombre_etiqueta}' as nombre,
					'{$ord_lista}' as orden_lista,
					p.id_productos
				FROM ec_productos p
				WHERE p.id_productos=$id_producto
				LIMIT 1
			)ax
			LEFT JOIN ec_precios_detalle pd ON ax.id_productos=pd.id_producto
			LEFT JOIN ec_precios pr on pd.id_precio=pr.id_precio
			/*ORDER BY pd.precio_venta ASC*
			JOIN sys_sucursales s ON pr.id_precio=s.id_precio
			AND s.id_sucursal='{$user_sucursal}'
			LIMIT 1";*/
	
	$query="SELECT 
				'{$nombre_etiqueta}' as nombre,
				CONCAT('????'),
				'{$ord_lista}' as orden_lista,
				p.id_productos
			FROM ec_productos p
			WHERE p.id_productos=$id_producto
			LIMIT 1";

	$result = mysql_query($query) or die ('Error al consultar datos del producto!!!: '.mysql_error());
	$cant   = mysql_num_rows($result);
//die('cantidad:'.$cant);	
	//if($cant > 0){
    //echo $query;
    $plantilla=1;
	if($plantilla== 1){
		$altura      = 51;
		$ancho       = 88;
		$orientacion = 'L';
		$tfuente     = 70;
		$tfuente2    = 15;
		$xx          = 3;
		$xy          = 11;
		$flag        = 5;
	//
		$altura_1      = 98;
		$ancho_1       = 133;
		$orientacion_1 = 'L';
		$tfuente_1     = 110;
		$tfuente2_1    = 30;
		$xx_1          = 2;
		$xy_1          = 3;
	}/*
	if($arr2[1] == 2){
		$altura_1      = 98;
		$ancho_1       = 133;
		$orientacion_1 = 'L';
		$tfuente_1     = 110;
		$tfuente2_1    = 30;
		$xx_1          = 2;
		$xy_1          = 3;
		$flag_1        = 5;
	}*/
//fin de cambios
	$datos = array(
					'altura'      => $altura,
					'ancho'       => $ancho,
					'orientacion' => $orientacion,
					'result'      => $result,
					'cant'        => $arr2[0],
					'tfuente'     => $tfuente,
					'tfuente2'	  => $tfuente2,
					'xx'	      => $xx,
					'xy'          => $xy,
					'flag'		  => $flag,	
				//
					'altura_1'      => $altura_1,
					'ancho_1'       => $ancho_1,
					'orientacion_1' => $orientacion_1,
					'result_1'      => $result_1,
					'cant_1'        => $arr2[0],
					'tfuente_1'     => $tfuente_1,
					'tfuente2_1'	=> $tfuente2_1,
					'xx_1'	      	=> $xx_1,
					'xy_1'          => $xy_1
				  );

	//print_r($datos);
	creaPdf($datos, $canProds, $plantilla, $user_sucursal);
	//secho $query;
 //}else{
 	//echo 'fail|No hay datos para generar tus etiquetas'.$query;
 //}	


	function creaPdf($datos, $prods, $plantilla, $user_sucursal){
		extract($datos);
			$canti   = mysql_num_rows($result);
	
		//echo $cant;
		//die("cant: ".$canti);
	//-------------- CONFICURACION INICIAL DEL PDF --------------//
		$pdf = new TCPDF($orientacion,'mm','LETTER', true, 'UTF-8', false);
		
		if($plantilla == 1 || $plantilla == 2 || $plantilla == 3 || $plantilla == 4 || $plantilla == 5)
		{
			$pdf->SetMargins(0,0,0);
			$pdf->SetAutoPageBreak(false,5);
		}
		else
		{
			$pdf->SetMargins(3,3,3);
			$pdf->SetAutoPageBreak(true,5);
		}	
		$pdf->SetDrawColor (0,-1,-1,-1, $ret=false, $name='red');
 		$pdf->setPrintHeader(false); //no imprime la cabecera ni la linea 
		$pdf->setPrintFooter(false);
		$fontname=$pdf->addTTFfont('TobagoPoster.ttf', 'TrueTypeUnicode', '', 32);
		
		$tw_cent=$pdf->addTTFfont('TwCen.ttf', 'TrueTypeUnicode', '', 32);
		$arial_black=$pdf->addTTFfont('ArialBlack.ttf', 'TrueTypeUnicode', '', 32);
		$bernard=$pdf->addTTFfont('BernardMTCondensed.ttf', 'TrueTypeUnicode', '', 32);
		$arial_narrow=$pdf->addTTFfont('ArialNarrow.ttf', 'TrueTypeUnicode', '', 32);
		$rockwell=$pdf->addTTFfont('Rockwell.ttf', 'TrueTypeUnicode', '', 32);
		$arial2=$pdf->addTTFfont('608.ttf', 'TrueTypeUnicode', '', 32);
		
		// set text shadow effect
		
		
		//$fontname="Arial Black";

	//--------------------- CREACION DEL PDF --------------------//	
		$pdf->AddPage();
			$j=0;
 			$count=0;

		$x0=$pdf->getX()+3;
			$y0=$pdf->getY()+3;
			
			$xor=$pdf->getX()+3;
			$yor=$pdf->getY()+3;
			
			if($plantilla == '1' || $plantilla == '2' || $plantilla == '3' || $plantilla == '4' || $plantilla == '5')
			{
				$j=0;
				$k=0;
			}


		for($y=0;$y<$canti;$y++)
		{
			$fila = mysql_fetch_row($result);
				$pre1=round($pre1);
				$pre2=round($pre2);
				$pre3=round($pre3);

				$html='';
				$html2='<font face="'.$bernard.'" size="100" color="red"><b>'.$can1.'</b></font>';
				$html3='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html4='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre1.'</b></font>';
				
				$html5='<font face="'.$bernard.'" size="100" color="red"><b>'.$can2.'</b></font>';
				$html6='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html7='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre2.'</b></font>';
				
				$html8='<font face="'.$bernard.'" size="100" color="red"><b>'.$can3.'</b></font>';
				$html9='<font face="'.$bernard.'" size="90" color="red"><b>x</b></font>';
				$html10='<font face="'.$bernard.'" size="100" color="red"><b>'.$pre3.'</b></font>';
				
				//$html11='<font face="'.$tw_cent.'" size="32" color="black" style="line-height: 10px;font-stretch: condensed"><b>'.$fila[0].'</b>(<font face="'.$tw_cent.'" size="36" color="green">'.$fila[2].'</font>)</font>';
				$html11='<font face="'.$arial2.'" size="31" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="31" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
			}
			
			$precio = $fila[1];
				

			if(isset($prods[$fila[3]])){
				$cr=$cant*$prods[$fila[3]];
			}
			else{
				$cr=$cant;	
			}
			
						
 			
			for($i=0;$i<=1;$i++)
			{
				if($i==0)//if($plantilla == '1')
				{//implementación de Oscar para ajuste de fuente en precios 12-02-2017
					if($precio>999||strlen($precio)>3){
						$tam_fte='75';
						$marg_arr='line-height:23px;';
					}else{
						$tam_fte='104';
						$marg_arr='';
					}
			//fin cambio
					$html="";
					
					$html2='<font face="'.$arial2.'" size="65" color="red" ><b>$</b></font>';
				//corresponde al precio
					$html3='<font face="'.$arial2.'" size="'.$tam_fte.'" color="red" style="letter-spacing: 0px;'.$marg_arr.'"><b>'.$precio.'</b></font>';
					$html4='<font face="'.$arial2.'" size="25" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="25" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
					$plantilla=2;
					
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.5, 'depth_h'=>2, 'color'=>array(0,0,0), 'opacity'=>1, 'blend_mode'=>'Normal'));			
					
					$pdf->writeHTMLCell	(
											18,
										 	30,
										 	$xor-0,
										 	$yor+2,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->writeHTMLCell	(
											80,
										 	30,
										 	$xor+12,
										 	$yor-9,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.8, 'depth_h'=>3.2, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											86,
										 	21,
										 	$xor+1,
										 	$yor+34,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);											
									
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(31, 151, 207)));					
										
										
					$pdf->writeHTMLCell	(
											$ancho,
										 	$altura,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);									
				}
				if($i ==1)
				{
			//implementación de Oscar para ajuste de fuente en precios 12-02-2017
					if($precio>999||strlen($precio)>3){
						$tam_fte='180';
						$marg_arr='line-height:20px;';
					}else{
						$tam_fte='240';
						$marg_arr='';
					}
			//fin cambio
					
					$html="";
					
					$html2='<font face="'.$arial2.'" size="150" color="red" style="font-stretch: extra-condensed;letter-spacing: 6px;"><b>$</b></font>';
					$html3='<font face="'.$arial2.'" size="'.$tam_fte.'" color="red" style="font-stretch: extra-condensed;letter-spacing: 6px;'.$marg_arr.'"><b>'.$precio.'</b></font>';
					//$html4='<font face="'.$tw_cent.'" size="40" color="black" style="line-height: 10px;font-stretch: condensed"><b>'.$fila[0].'</b>(<font face="'.$tw_cent.'" size="52" color="green">'.$fila[2].'</font>)</font></span>';
					$html4='<font face="'.$arial2.'" size="37" color="black" style="letter-spacing: 6px;line-height: 10px;font-stretch: extra-condensed"><strong>'.$fila[0].'</strong>(<font face="'.$arial2.'" size="37" color="green" style="letter-spacing: 3px !important;line-height: 15px;font-stretch: condensed">'.$fila[2].'</font>)</font></span>';
					//#1F97CF
					$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.6, 'depth_h'=>2, 'color'=>array(0,0,0), 'opacity'=>1, 'blend_mode'=>'Normal'));			

					
					$pdf->writeHTMLCell	(
											28,
										 	28,
										 	$xor+1,
										 	$yor+1,
										 	$html2,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = false 
										);

										
					$pdf->writeHTMLCell	(
											100,
										 	30,
										 	$xor+24,
										 	$yor-22,
										 	$html3,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);
										
										
					$pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.4, 'depth_h'=>1.6, 'color'=>array(0,0,0), 'opacity'=>0.8, 'blend_mode'=>'Normal'));					
										
					$pdf->writeHTMLCell	(
											131,
										 	38,
										 	$xor+1,
										 	$yor+71,
										 	$html4,
									 		$border      = 0,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = false,
										 	$align       = 'C',
										 	$autopadding = false 
										);										

					
					$pdf->SetLineStyle(array('width' => 1.2, 'cap' => 'butt', 'join' => 'miter', 'color' => array(31, 151, 207)));
					
					$pdf->writeHTMLCell	(
											$ancho_1,
										 	$altura_1,
										 	$xor,
										 	$yor,
										 	$html,
									 		$border      = 1,
										 	$ln          = 0,
										 	$fill        = false,
										 	$reseth      = true,
										 	$align       = 'C',
										 	$autopadding = true 
										);
				}
				$j++;
				if($plantilla == 1){
					$xor+=90;
					if($j > 2){
						$yor+=$altura+1;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 3){
						$pdf->AddPage();
						$k = 0;
						$yor=$y0;
						$xor=$x0;
						$j= 0;
					}					
				}
				else if($plantilla == 2)
				{
					$xor+=136;
					
					if($j > 1)
					{
						$yor+=$altura+3;
						$xor=$x0;
						$j=0;
						$k++;
					}
					if($k > 1)
					{
						$pdf->AddPage();
						$k = 0;
						
						$yor=$y0;
						$xor=$x0;
	
						$j= 0;
						
					}					
				}					
			}//fin de for i
		//fin de la función crear PDF

		mysql_free_result($result);
		$ruta = '../../etiquetas/etiquetas_previo.pdf';
	//comprobamos si el archivo existe
		if(file_exists($ruta)){
			unlink($ruta);//si el archivo existe lo eliminamos
		}
		if(!$pdf->Output($ruta,'F')){
			//echo 'ok|'.$ruta;
			echo '<object data="'.$ruta.'" type="application/pdf" iid="pdf_previo" width="100%" height="100%"></object>';
		}
		else
		{
			$pdf->Error('Error al generar las etiquetas,intenta nuevamente porfavor');
		}

	}
		
?>