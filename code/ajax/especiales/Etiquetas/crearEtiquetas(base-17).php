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
				orden_lista,
				id_productos 
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
		$altura      = 45;
		$ancho       = 90;
		$orientacion = 'L';
		$tfuente     = 70;
		$tfuente2    = 15;
		$xx          = 3;
		$xy          = 11;
		$flag        = 5;
	}
	if($arr2[1] == 2){
		$altura      = 90;
		$ancho       = 130;
		$orientacion = 'L';
		$tfuente     = 110;
		$tfuente2    = 30;
		$xx          = 2;
		$xy          = 3;
		$flag        = 5;
	}
	if($arr2[1] == 3){
		$altura      = 130;
		$ancho       = 90;
		$orientacion = 'P';
		$tfuente     = 40;
		$tfuente2    = 25;
		$xx          = 2;
		$xy          = 3;
		$flag        = 0;
	}
	
	if($arr2[1] == 4){
		$altura      = 45;
		$ancho       = 90;
		$orientacion = 'L';
		$tfuente     = 50;
		$tfuente2    = 15;
		$xx          = 3;
		$xy          = 11;
		$flag        = 1;
	}
	if($arr2[1] == 5){
		$altura      = 90;
		$ancho       = 130;
		$orientacion = 'L';
		$tfuente     = 90;
		$tfuente2    = 20;
		$xx          = 2;
		$xy          = 3;
		$flag        = 1;
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
					'xy'          => $xy,
					'flag'		  => $flag		
				  );

	//print_r($datos);
	creaPdf($datos);
	//secho $query;
 }
 else
 {
 	echo 'fail|No hay datos para generar tus etiquetas';
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

			if($flag == 1)
			{
				$query = "SELECT
						  DISTINCT(id_precio), 
						  CONCAT('2',' X ','$',(pd.precio_venta*2)) AS prom,
						  id_producto
						  FROM ec_precios_detalle pd
						  WHERE id_producto = $fila[3]";
				$res = mysql_query($query)	or die ('Precio2: '.mysql_error());
				$row = mysql_fetch_row($res);
				$precio = $row[1];

					$html='
				<span style="text-align:center;">
				<br><br>
				<font face="serif" size="'.$tfuente.'" color="red"><b>'.$precio.'</b></font><br><br><br><font face="calibri" size="'.$tfuente2.'" color="black"><b>'.$fila[0].'</b>(<font face="calibri" size="'.$tfuente2.'" color="green">'.$fila[2].'</font>)</font>
				</span>';	  
			}
			if($flag == 0)
			{
				$query = "SELECT
						de_valor,
						precio_venta*1 AS prom,
						id_producto
						FROM ec_precios_detalle pd
						WHERE de_valor = 1
						AND id_producto = $fila[3]
						UNION
						SELECT
						de_valor,
						(pd.precio_venta) AS prom,
						id_producto
						FROM ec_precios_detalle pd
						WHERE de_valor >= 2
						AND id_producto = $fila[3]";

				$res = mysql_query($query)	or die ('Precio2: '.mysql_error());
				$nume = mysql_num_rows($res); 
				$row = mysql_fetch_row($res);
				$precios = array();
					
					for($i=0;$i<$nume;$i++)
					{
						array_push($precios,$row[1]);
					}
					
				
				$html='
				<span style="text-align:center">
				<br><br>
				<font face="serif" size="'.$tfuente.'" color="red"><b>1&nbsp;&nbsp;&nbsp;X $'.$precios[0].'</b><br><b>6&nbsp;&nbsp;&nbsp;X $'.($precios[1]*6).'</b><br><b>12 X $'.($precios[1]*12).'</b></br></font><br><br><br><br><br><font face="calibri" size="'.$tfuente2.'" color="black"><b>'.$fila[0].'</b>(<font face="calibri" size="'.$tfuente2.'" color="green">'.$fila[2].'</font>)</font>
				</span>';	  
			}	
			else
			{
				$precio = $fila[1];
					$html='
				<span style="text-align:center;">
				<font face="calibri" size="'.$tfuente.'" color="red"><b>'.$precio.'</b></font><br><br><font face="calibri" size="'.$tfuente2.'" color="black"><b>'.$fila[0].'</b>(<font face="calibri" size="'.$tfuente2.'" color="green">'.$fila[2].'</font>)</font>
				</span>';
			}

			
 			
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