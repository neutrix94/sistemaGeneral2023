<?php
	include("../../../../conectMin.php");
	include("../../../../include/tcpdf/tcpdf.php");

	extract($_GET);
	extract($_POST);
	$numProd = 0;
	$numProd = count($arr);
//recibimos variable $ofert
//------------ CONSULTA BASE  (Se anidan para sacar de lista de precios interna, externa Oscar 15.08.2018)-------------//
	if($arr2[1] == 4 || $arr2[1] == 5){
		$query = "SELECT '', p.nombre_etiqueta, p.orden_lista FROM ec_productos p WHERE ";
	}else{//si arr2[1] es 1,2 o 3
		$query = "SELECT '', p.nombre_etiqueta, p.orden_lista FROM ec_productos p WHERE ";	
	}

//filtro de categoría
	if($fil[0] != (-1)){
		$query .= " p.id_categoria ='$fil[0]'";
	}
//filtro de subcategoría
	if($fil[1] != (-1)){
			if($fil[0] != (-1)){
				$query .= " AND p.id_subcategoria = '$fil[1]'";
			}
			else{
				$query .= " p.id_subcategoria = '$fil[1]'";
			}
	}
//filtro de subtipo
	if($fil[2] != (-1) && $fil[2] != 0 ){
			if($fil[1] != (-1)){
				$query .= " AND p.id_subtipo = '$fil[2]'";
			}
			else{
				$query .= "p.id_subtipo = '$fil[2]'";
			}		
	}
	$canProds=array();
//si es desde productos capturados manuelamente
	if($numProd > 0){
		if($arr[0] != null){
		    for($i=0;$i<$numProd;$i++){
				if(isset($canProds[$arr[$i]])){
					$canProds[$arr[$i]]++;
				}else{
					$canProds[$arr[$i]]=1;	
				}
				if($fil[0] != (-1) || $fil[1] !=(-1) || $fil[2] != 0|| $fil[3] != 0 && $fil[4] != 0){
					 $query .= " OR p.id_productos ='{$arr[$i]}'";
				}else{	
					if($i==0){
						$query .= " p.id_productos ='{$arr[$i]}'";
					}else{
						$query .= " OR p.id_productos ='{$arr[$i]}'";
					}
				} 
			}//fin de for $i	
		}
	}
	$result = mysql_query($query) or die ('Productos: '.mysql_error());
	$cant   = mysql_num_rows($result);
	
	if($cant > 0){
		if($arr2[1] == 6 ){
			$altura      = 51;
			$ancho       = 88;
			$orientacion = 'L';
			$tfuente     = 70;
			$tfuente2    = 15;
			$xx          = 3;
			$xy          = 11;
			$flag        = 6;
			$arr2[1] = 1;
		}
/*fin de cambio Oscar 2021*/

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
					'flag'		  => $flag		
				  );

	//print_r($datos);
	creaPdf($datos, $canProds, $arr2[1], $user_sucursal);
 }else{
 	echo 'fail|No hay datos para generar tus etiquetas'.$query;
 }	


	function creaPdf($datos, $prods, $plantilla, $user_sucursal){
		extract($datos);
			$canti   = mysql_num_rows($result);
	
		//echo $cant;
	//-------------- CONFICURACION INICIAL DEL PDF --------------//
		$pdf = new TCPDF($orientacion,'mm','LETTER', true, 'UTF-8', false);
		
		if( $plantilla == 6 ){
			$pdf->SetMargins(0,0,0);
			$pdf->SetAutoPageBreak(false,5);
		}else{
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

	//--------------------- CREACION DEL PDF --------------------//	
		$pdf->AddPage();
			$j=0;
 			$count=0;

		$x0=$pdf->getX()+3;
			$y0=$pdf->getY()+3;
			
			$xor=$pdf->getX()+3;
			$yor=$pdf->getY()+3;
			
			if( $plantilla == '6' ){
				$j=0;
				$k=0;
			}


		for($y=0;$y<$canti;$y++)
		{
			$fila = mysql_fetch_row($result);
			
			if( $flag != 1 && $flag != 0 ) {
				$precio = $fila[1];
			//implementación de Oscar para ajuste de fuente en precios 12-02-2017
					if($precio>999||strlen($precio)>20){
						$tam_fte='30';
						//$marg_arr='line-height:23px;';
					}else{
						$tam_fte='30';
						$marg_arr='';
					}
			//fin cambio
					$html="";
				//corresponde al precio
					$html3='<font face="'.$tw_cent.'" size="'.$tam_fte.'" color="black" style="letter-spacing: 0px;'.$marg_arr.'"><b>'.$precio.'</b></font>';
					$html4='<font face="'.$tw_cent.'" size="35" color="black" style="letter-spacing: 3px;line-height: 10px;font-stretch: extra-condensed"><strong>'
						.$fila[0].'</strong><b>(</b><font face="'.$arial2.'" size="35" color="green" style="letter-spacing: 0px !important;line-height: 15px;font-stretch: condensed">'
						.$fila[2].'</font><b>)</b></font></span>';
			}
			
			if(isset($prods[$fila[3]]))
				$cr=$cant*$prods[$fila[3]];
			else
				$cr=$cant;	

			for( $i=0; $i<$cr; $i++ ) {
				if($plantilla == '1'){	
					$pdf->writeHTMLCell	(
							80,
						 	30,
						 	$xor+5,
						 	$yor+3,
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
										 	$altura-3,
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
			}
		}

		mysql_free_result($result);
		$ruta = '../../../../etiquetas/etiquetas'.date(ymd_his).'.pdf';
		if(!$pdf->Output($ruta,'F'))
		{
			echo 'ok|'.$ruta;
		}
		else
		{
			$pdf->Error('Error al generar las etiquetas,intenta nuevamente profavor');
		}

	}
		
?>