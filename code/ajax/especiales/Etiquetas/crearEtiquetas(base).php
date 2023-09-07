<?php
	include("../../../../conectMin.php");
	include("../../../../include/tcpdf/tcpdf.php");

extract($_GET);
extract($_POST);
$numProd = 0;
$numProd = count($arr);
//------------ CONSULTA BASE -------------//
	$query = "SELECT 
				nombre,
				CONCAT('$',precio_venta),
				orden_lista 
				FROM ec_productos 
				WHERE ";
	if($fil[0] != (-1)){

		$query .= "id_categoria ='$fil[0]'";
	}
	if($fil[1] != (-1)){

			if($fil[0] != (-1)){
				$query .= " AND id_subcategoria = '$fil[1]'";
			}
			else{
				$query .= "id_subcategoria = '$fil[1]'";
			}
		
	}
	if($fil[2] != 0 && $fil[3] != 0){
		if($fil[0] != (-1) || $fil[1] !=(-1)){
		   $query .= " AND precio_venta >= '$fil[2]' AND precio_venta <= '$fil[3]'";
		}
		else{
			 $query .= " precio_venta >= '$fil[2]' AND precio_venta <= '$fil[3]'";
		}
	}
	if($numProd > 0){
		if($arr[0] != null){
	    for($i=0;$i<$numProd;$i++){
				if($fil[0] != (-1) || $fil[1] !=(-1) || $fil[2] != 0 && $fil[3] != 0){
					 $query .= " OR id_productos ='$arr[$i]'";
				}else
				{	
					if($i==0){
						$query .= " id_productos ='$arr[$i]'";
					}
					else
					{
						$query .= " OR id_productos ='$arr[$i]'";
					}
					
				}
			  
			}	
		}
		
	}
	//echo $query;		
	$result = mysql_query($query) or die ('Productos: '.mysql_error());
	$cant   = mysql_num_rows($result);
	
	if($cant > 0){
		

    //echo $query;
	if($arr2[1] == 1){
		$altura = 45;
		$ancho  = 90;
		$orientacion = 'L';
		$tfuente = 70;
		$tfuente2 = 15;
		$xx = 3;
		$xy = 11;
	}
	if($arr2[1] == 2){
		$altura = 90;
		$ancho  = 130;
		$orientacion = 'L';
		$tfuente = 130;
		$tfuente2 = 30;
		$xx = 2;
		$xy = 3;
	}
	if($arr2[1] == 3){
		$altura = 130;
		$ancho  = 100;
		$orientacion = 'P';
	}


	$datos = array(
					'altura'      => $altura,
					'ancho'       => $ancho,
					'orientacion' => $orientacion,
					'result'      => $result,
					'cant'        => $arr2[0],
					'tfuente'     => $tfuente,
					'tfuente2'	  => $tfuente2,
					'xx'	      => $xx,
					'xy'          => $xy
				  );

	//print_r($datos);
	creaPdf($datos);
	//secho $query;
 }


	function creaPdf($datos){
		extract($datos);
	//-------------- CONFICURACION INICIAL DEL PDF --------------//
		$pdf = new TCPDF($orientacion,'mm','A4', true, 'UTF-8', false);
		$pdf->SetMargins(10,10,10);
		$pdf->SetAutoPageBreak(true,10);
		$pdf->SetDrawColor (0,-1,-1,-1, $ret=false, $name='red');
 		$pdf->setPrintHeader(false); //no imprime la cabecera ni la linea 
		$pdf->setPrintFooter(false);

	//--------------------- CREACION DEL PDF --------------------//	
		$pdf->AddPage();
			$j=0;
 			$count=0;
		while($fila = mysql_fetch_row($result)){

			$html='
			<span style="text-align:center;">
			<font face="calibri" size="'.$tfuente.'" color="red"><b>'.utf8_encode($fila[1]).'</b></font><br><br><font face="calibri" size="'.$tfuente2.'" color="black"><b>'.$fila[0].'</b>(<font face="calibri" size="'.$tfuente2.'" color="green">'.$fila[2].'</font>)</font>
			</span>';
 			
			for($i=0;$i<$cant;$i++){

				$pdf->writeHTMLCell	(
										$ancho,
									 	$altura,
									 	$pdf->getX()+3,
									 	'',
									 	$html,
									 	$border = 1,
									 	$ln = 0,
									 	$fill = false,
									 	$reseth = true,
									 	$align = 'C',
									 	$autopadding = true 
									);

				$j++;					
				if($j==$xx)
				{	
					if($count !=$xy)
					{	
						$pdf->Ln($altura+2);
						$j = 0;
					}
					else{
						$pdf->AddPage();
						$count = 0;

						$j= 0;
						continue;
					}
				}

				$count++;

			}
		}

		mysql_free_result($result);
		
		$pdf->Output('../../../example_0018_'.date(his).'.pdf','F');
	}
		
?>